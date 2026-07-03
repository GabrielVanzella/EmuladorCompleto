package main

import (
	"bufio"
	"fmt"
	"io"
	"log"
	"net"
	"strings"
	"sync"
	"sync/atomic"
	"time"
)

// IAC NOP — Telnet keepalive (RFC 854)
var iacNop = []byte{0xFF, 0xF1}

// Session representa um túnel: cliente Android ↔ relay ↔ servidor Telnet/ERP.
// O lado do cliente (LAN) nunca é fechado pelo relay.
// Quando o lado do servidor (internet) cai, o relay tenta reconectar continuamente.
type Session struct {
	id     string
	client net.Conn
	target string // "host:port" do servidor Telnet

	mu     sync.Mutex
	server net.Conn // nil enquanto desconectado do servidor

	alive  int32 // 1 = ativo, 0 = encerrado (atomic)
	cfg    Config
}

var sessionCounter int64

func newSession(client net.Conn, target string, cfg Config) *Session {
	id := fmt.Sprintf("S%04d", atomic.AddInt64(&sessionCounter, 1))
	return &Session{
		id:     id,
		client: client,
		target: target,
		alive:  1,
		cfg:    cfg,
	}
}

// Run inicia o túnel e bloqueia até o cliente desconectar.
func (s *Session) Run() {
	defer s.cleanup()

	// Conecta ao servidor Telnet na primeira vez
	srv, err := net.DialTimeout("tcp", s.target, time.Duration(s.cfg.DialTimeoutSec)*time.Second)
	if err != nil {
		log.Printf("[%s] Falha ao conectar a %s: %v", s.id, s.target, err)
		// Mesmo assim mantém o cliente conectado e inicia reconexão
	} else {
		log.Printf("[%s] Conectado a %s", s.id, s.target)
		s.mu.Lock()
		s.server = srv
		s.mu.Unlock()
	}

	// Goroutine: reconecta servidor quando cai
	go s.reconnectLoop()

	// Goroutine: keepalive IAC NOP para o servidor
	if s.cfg.KeepaliveInterval > 0 {
		go s.keepaliveLoop()
	}

	// Goroutine: cliente → servidor
	clientDone := make(chan struct{})
	go s.pipeClientToServer(clientDone)

	// Goroutine: servidor → cliente
	serverDone := make(chan struct{})
	go s.pipeServerToClient(serverDone)

	// Aguarda qualquer dos lados terminar (client disconnect é o sinal de saída)
	select {
	case <-clientDone:
		log.Printf("[%s] Cliente desconectou", s.id)
	case <-serverDone:
		// Servidor fechou a conexão definitivamente (só deve chegar se alive=0)
		log.Printf("[%s] Servidor fechou definitivamente", s.id)
	}

	atomic.StoreInt32(&s.alive, 0)
}

// pipeClientToServer lê do cliente e escreve no servidor.
// Se o servidor não estiver disponível, dados do cliente são descartados (freeze).
func (s *Session) pipeClientToServer(done chan<- struct{}) {
	defer close(done)
	buf := make([]byte, 32*1024)
	for {
		n, err := s.client.Read(buf)
		if err != nil {
			// Cliente Android desconectou — encerra tudo
			atomic.StoreInt32(&s.alive, 0)
			return
		}
		if n == 0 {
			continue
		}
		s.mu.Lock()
		srv := s.server
		s.mu.Unlock()
		if srv == nil {
			// Internet caída — descarta dado do cliente (teclado ignorado)
			continue
		}
		if _, werr := srv.Write(buf[:n]); werr != nil {
			// Falha de escrita: servidor caiu, reconnectLoop vai cuidar
			s.mu.Lock()
			if s.server == srv {
				s.server.Close()
				s.server = nil
			}
			s.mu.Unlock()
		}
	}
}

// pipeServerToClient lê do servidor e escreve no cliente.
// Quando servidor cai, aguarda reconexão (cliente continua conectado, tela congela).
func (s *Session) pipeServerToClient(done chan<- struct{}) {
	defer close(done)
	buf := make([]byte, 32*1024)
	for {
		if atomic.LoadInt32(&s.alive) == 0 {
			return
		}

		s.mu.Lock()
		srv := s.server
		s.mu.Unlock()

		if srv == nil {
			// Aguarda reconexão — não fecha conexão com cliente
			time.Sleep(200 * time.Millisecond)
			continue
		}

		// Define timeout de leitura para detectar servidor morto rapidamente
		srv.SetReadDeadline(time.Now().Add(45 * time.Second))
		n, err := srv.Read(buf)
		if err != nil {
			if netErr, ok := err.(net.Error); ok && netErr.Timeout() {
				// Timeout de leitura — normal (sem dados), continua
				continue
			}
			if err != io.EOF {
				log.Printf("[%s] Servidor caiu: %v — aguardando reconexão", s.id, err)
			}
			// Sinaliza para o reconnectLoop que o servidor precisa de nova conexão
			s.mu.Lock()
			if s.server == srv {
				s.server.Close()
				s.server = nil
			}
			s.mu.Unlock()
			continue
		}
		if n > 0 {
			if _, werr := s.client.Write(buf[:n]); werr != nil {
				// Cliente desconectou
				atomic.StoreInt32(&s.alive, 0)
				return
			}
		}
	}
}

// reconnectLoop tenta reconectar ao servidor quando s.server == nil.
// Usa backoff exponencial: 1s, 2s, 4s, 8s, ... até ReconnectMaxSec.
func (s *Session) reconnectLoop() {
	backoff := 1
	for {
		if atomic.LoadInt32(&s.alive) == 0 {
			return
		}
		s.mu.Lock()
		hasServer := s.server != nil
		s.mu.Unlock()

		if hasServer {
			backoff = 1 // reset backoff quando conectado
			time.Sleep(500 * time.Millisecond)
			continue
		}

		// Servidor caído — aguarda backoff e tenta reconectar
		log.Printf("[%s] Reconectando a %s em %ds...", s.id, s.target, backoff)
		time.Sleep(time.Duration(backoff) * time.Second)

		if atomic.LoadInt32(&s.alive) == 0 {
			return
		}

		conn, err := net.DialTimeout("tcp", s.target, time.Duration(s.cfg.DialTimeoutSec)*time.Second)
		if err != nil {
			log.Printf("[%s] Reconexão falhou: %v", s.id, err)
			// Dobra o backoff até o máximo
			if backoff < s.cfg.ReconnectMaxSec {
				backoff *= 2
				if backoff > s.cfg.ReconnectMaxSec {
					backoff = s.cfg.ReconnectMaxSec
				}
			}
			continue
		}

		s.mu.Lock()
		s.server = conn
		s.mu.Unlock()
		backoff = 1
		log.Printf("[%s] Reconectado a %s com sucesso", s.id, s.target)
	}
}

// keepaliveLoop envia IAC NOP periodicamente para manter sessão viva no ERP.
func (s *Session) keepaliveLoop() {
	ticker := time.NewTicker(time.Duration(s.cfg.KeepaliveInterval) * time.Second)
	defer ticker.Stop()
	for {
		<-ticker.C
		if atomic.LoadInt32(&s.alive) == 0 {
			return
		}
		s.mu.Lock()
		srv := s.server
		s.mu.Unlock()
		if srv == nil {
			continue
		}
		if _, err := srv.Write(iacNop); err != nil {
			log.Printf("[%s] Keepalive falhou: %v", s.id, err)
			s.mu.Lock()
			if s.server == srv {
				s.server.Close()
				s.server = nil
			}
			s.mu.Unlock()
		}
	}
}

func (s *Session) cleanup() {
	atomic.StoreInt32(&s.alive, 0)
	s.client.Close()
	s.mu.Lock()
	if s.server != nil {
		s.server.Close()
		s.server = nil
	}
	s.mu.Unlock()
}

// parseHTTPConnect lê o handshake HTTP CONNECT do cliente e retorna "host:port".
// Protocolo: "CONNECT host:port HTTP/1.x\r\n...\r\n"
func parseHTTPConnect(conn net.Conn) (string, error) {
	conn.SetDeadline(time.Now().Add(15 * time.Second))
	defer conn.SetDeadline(time.Time{}) // remove deadline

	reader := bufio.NewReader(conn)

	// Primeira linha: "CONNECT host:port HTTP/1.x"
	line, err := reader.ReadString('\n')
	if err != nil {
		return "", fmt.Errorf("erro ao ler linha CONNECT: %w", err)
	}
	line = strings.TrimSpace(line)

	parts := strings.Fields(line)
	if len(parts) < 2 || !strings.EqualFold(parts[0], "CONNECT") {
		return "", fmt.Errorf("esperado CONNECT, recebido: %q", line)
	}
	target := parts[1]

	// Consome cabeçalhos restantes até linha em branco
	for {
		hdr, err := reader.ReadString('\n')
		if err != nil {
			break
		}
		if strings.TrimSpace(hdr) == "" {
			break
		}
	}

	// Valida que target tem formato "host:port"
	if !strings.Contains(target, ":") {
		return "", fmt.Errorf("target sem porta: %q", target)
	}

	return target, nil
}
