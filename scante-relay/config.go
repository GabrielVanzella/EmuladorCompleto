package main

import (
	"encoding/json"
	"fmt"
	"os"
)

// Config contém todas as opções do relay server.
type Config struct {
	// Endereço de escuta. Ex: "0.0.0.0:2323" ou ":2323"
	ListenAddr string `json:"listen_addr"`

	// Intervalo em segundos para enviar IAC NOP ao servidor Telnet (keepalive).
	// 0 = desativado. Recomendado: 30.
	KeepaliveInterval int `json:"keepalive_interval"`

	// Espera máxima entre tentativas de reconexão ao servidor Telnet (segundos).
	// A espera começa em 1s e dobra a cada falha até atingir este limite.
	ReconnectMaxSec int `json:"reconnect_max_sec"`

	// Máximo de sessões simultâneas. 0 = sem limite.
	MaxSessions int `json:"max_sessions"`

	// Timeout de dial ao servidor Telnet (segundos).
	DialTimeoutSec int `json:"dial_timeout_sec"`

	// Escrever log em arquivo (além do console). Vazio = só console.
	LogFile string `json:"log_file"`
}

// defaultConfig retorna configuração padrão caso config.json não exista.
func defaultConfig() Config {
	return Config{
		ListenAddr:        "0.0.0.0:2323",
		KeepaliveInterval: 30,
		ReconnectMaxSec:   60,
		MaxSessions:       0,
		DialTimeoutSec:    15,
		LogFile:           "",
	}
}

// loadConfig lê scante-relay.json do mesmo diretório do executável.
// Se não encontrar, retorna defaultConfig e salva o arquivo de exemplo.
func loadConfig(path string) (Config, error) {
	data, err := os.ReadFile(path)
	if err != nil {
		if os.IsNotExist(err) {
			cfg := defaultConfig()
			if werr := saveDefaultConfig(path, cfg); werr != nil {
				fmt.Printf("Aviso: não foi possível criar %s: %v\n", path, werr)
			}
			return cfg, nil
		}
		return defaultConfig(), fmt.Errorf("erro ao ler %s: %w", path, err)
	}

	var cfg Config
	if err := json.Unmarshal(data, &cfg); err != nil {
		return defaultConfig(), fmt.Errorf("erro ao parsear %s: %w", path, err)
	}

	// Aplicar limites mínimos
	if cfg.KeepaliveInterval < 0 {
		cfg.KeepaliveInterval = 0
	}
	if cfg.ReconnectMaxSec < 5 {
		cfg.ReconnectMaxSec = 5
	}
	if cfg.DialTimeoutSec < 5 {
		cfg.DialTimeoutSec = 5
	}
	if cfg.ListenAddr == "" {
		cfg.ListenAddr = "0.0.0.0:2323"
	}
	return cfg, nil
}

func saveDefaultConfig(path string, cfg Config) error {
	data, err := json.MarshalIndent(cfg, "", "  ")
	if err != nil {
		return err
	}
	return os.WriteFile(path, data, 0644)
}
