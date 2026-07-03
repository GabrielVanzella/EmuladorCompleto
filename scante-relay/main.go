package main

import (
	"fmt"
	"io"
	"log"
	"net"
	"os"
	"os/signal"
	"path/filepath"
	"sync/atomic"
	"syscall"
	"time"
)

const version = "1.0.0"

var activeSessions int64

func main() {
	fmt.Printf("ScanTE Relay Server v%s\n", version)
	fmt.Println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━")
	fmt.Println("  Relay de persistência de sessão para o ScanTE")
	fmt.Println("  Protocolo: HTTP CONNECT (compatível com proxy)")
	fmt.Println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━")

	// Carrega configuração do mesmo diretório do executável
	exeDir := execDir()
	cfgPath := filepath.Join(exeDir, "scante-relay.json")
	cfg, err := loadConfig(cfgPath)
	if err != nil {
		log.Fatalf("Erro na configuração: %v", err)
	}

	// Configura log com arquivo opcional
	setupLogger(cfg, exeDir)

	log.Printf("Configuração carregada de: %s", cfgPath)
	log.Printf("Escutando em:    %s", cfg.ListenAddr)
	log.Printf("Keepalive:       %ds", cfg.KeepaliveInterval)
	log.Printf("Reconexão max:   %ds", cfg.ReconnectMaxSec)
	if cfg.MaxSessions > 0 {
		log.Printf("Sessões máx:     %d", cfg.MaxSessions)
	} else {
		log.Printf("Sessões máx:     ilimitado")
	}

	listener, err := net.Listen("tcp", cfg.ListenAddr)
	if err != nil {
		log.Fatalf("Não foi possível escutar em %s: %v", cfg.ListenAddr, err)
	}
	defer listener.Close()

	log.Printf("✓ Relay aguardando conexões em %s", cfg.ListenAddr)

	// Goroutine: status a cada 60s
	go statusPrinter()

	// Goroutine: trata SIGINT/SIGTERM
	go func() {
		c := make(chan os.Signal, 1)
		signal.Notify(c, os.Interrupt, syscall.SIGTERM)
		<-c
		fmt.Println("\nEncerrando relay...")
		listener.Close()
		os.Exit(0)
	}()

	for {
		conn, err := listener.Accept()
		if err != nil {
			if ne, ok := err.(net.Error); ok && ne.Temporary() {
				time.Sleep(10 * time.Millisecond)
				continue
			}
			log.Printf("Listener encerrado: %v", err)
			return
		}

		// Verifica limite de sessões
		if cfg.MaxSessions > 0 && int(atomic.LoadInt64(&activeSessions)) >= cfg.MaxSessions {
			log.Printf("Limite de sessões atingido (%d), recusando %s", cfg.MaxSessions, conn.RemoteAddr())
			conn.Write([]byte("HTTP/1.0 503 Service Unavailable\r\n\r\n"))
			conn.Close()
			continue
		}

		go handleConn(conn, cfg)
	}
}

func handleConn(conn net.Conn, cfg Config) {
	atomic.AddInt64(&activeSessions, 1)
	defer atomic.AddInt64(&activeSessions, -1)

	remote := conn.RemoteAddr().String()
	log.Printf("Nova conexão de %s", remote)

	// Lê o handshake HTTP CONNECT
	target, err := parseHTTPConnect(conn)
	if err != nil {
		log.Printf("Handshake inválido de %s: %v", remote, err)
		conn.Write([]byte("HTTP/1.0 400 Bad Request\r\n\r\n"))
		conn.Close()
		return
	}

	log.Printf("CONNECT %s ← %s", target, remote)

	// Responde HTTP 200 — de agora em diante é túnel transparente
	if _, err := conn.Write([]byte("HTTP/1.0 200 Connection established\r\n\r\n")); err != nil {
		log.Printf("Erro ao enviar 200 para %s: %v", remote, err)
		conn.Close()
		return
	}

	// Inicia sessão com reconexão automática
	sess := newSession(conn, target, cfg)
	log.Printf("[%s] Sessão iniciada: %s → %s", sess.id, remote, target)
	sess.Run()
	log.Printf("[%s] Sessão encerrada. Ativas: %d", sess.id, atomic.LoadInt64(&activeSessions))
}

func statusPrinter() {
	ticker := time.NewTicker(60 * time.Second)
	defer ticker.Stop()
	for range ticker.C {
		n := atomic.LoadInt64(&activeSessions)
		log.Printf("Status: %d sessão(ões) ativa(s)", n)
	}
}

func setupLogger(cfg Config, exeDir string) {
	if cfg.LogFile == "" {
		return
	}
	logPath := cfg.LogFile
	if !filepath.IsAbs(logPath) {
		logPath = filepath.Join(exeDir, logPath)
	}
	f, err := os.OpenFile(logPath, os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0644)
	if err != nil {
		log.Printf("Aviso: não foi possível abrir log %s: %v", logPath, err)
		return
	}
	log.SetOutput(io.MultiWriter(os.Stdout, f))
	log.Printf("Log também em: %s", logPath)
}

func execDir() string {
	exe, err := os.Executable()
	if err != nil {
		return "."
	}
	return filepath.Dir(exe)
}
