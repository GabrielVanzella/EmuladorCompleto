package main

import (
	"crypto/ed25519"
	"encoding/base64"
	"encoding/json"
	"errors"
	"os"
	"path/filepath"
	"strings"
	"time"
)

// Chave PÚBLICA Ed25519 do ScanTE (corresponde à privada guardada só no
// scante-admin, em RELAY_LICENSE_PRIVATE_KEY). Com isso o relay consegue
// CONFERIR uma licença, mas nunca consegue gerar uma nova sozinho.
var relayPublicKey = ed25519.PublicKey([]byte{
	224, 135, 58, 69, 247, 179, 114, 79, 174, 24, 219, 160, 46, 80, 143, 177,
	84, 178, 242, 193, 78, 97, 184, 40, 33, 124, 227, 95, 140, 24, 62, 96,
})

const licenseFileName = "license.lic"

// License é o conteúdo assinado pelo scante-admin (RelayLicenseService::gerar).
type License struct {
	Customer    string `json:"customer"`
	Serial      string `json:"serial"`
	MaxSessions int    `json:"max_sessions"`
	MaxDevices  int    `json:"max_devices"`
	Expiry      string `json:"expiry"` // YYYY-MM-DD
	Release     string `json:"release"`
	ServerHost  string `json:"server_host"`
	IssuedAt    string `json:"issued_at"`
}

// ParseLicense decodifica e verifica a assinatura de um texto de licença no
// formato "SRL1.<payload_base64>.<assinatura_base64>".
func ParseLicense(text string) (*License, error) {
	text = strings.TrimSpace(text)
	parts := strings.Split(text, ".")
	if len(parts) != 3 || parts[0] != "SRL1" {
		return nil, errors.New("formato de licença inválido")
	}

	payload, err := base64.StdEncoding.DecodeString(parts[1])
	if err != nil {
		return nil, errors.New("licença corrompida (dados)")
	}
	sig, err := base64.StdEncoding.DecodeString(parts[2])
	if err != nil {
		return nil, errors.New("licença corrompida (assinatura)")
	}
	if len(sig) != ed25519.SignatureSize || !ed25519.Verify(relayPublicKey, payload, sig) {
		return nil, errors.New("assinatura da licença inválida")
	}

	var lic License
	if err := json.Unmarshal(payload, &lic); err != nil {
		return nil, errors.New("dados da licença corrompidos")
	}
	return &lic, nil
}

// IsExpired retorna true se a validade (fim do dia informado) já passou.
func (l *License) IsExpired() bool {
	expiry, err := time.Parse("2006-01-02", l.Expiry)
	if err != nil {
		return true
	}
	return time.Now().After(expiry.Add(24 * time.Hour))
}

func licenseFilePath(exeDir string) string {
	return filepath.Join(exeDir, licenseFileName)
}

// loadLicenseFromFile lê e valida a licença salva junto do executável.
// Retorna erro se o arquivo não existir, estiver corrompido/inválido ou expirado.
func loadLicenseFromFile(path string) (*License, error) {
	data, err := os.ReadFile(path)
	if err != nil {
		return nil, err
	}
	lic, err := ParseLicense(string(data))
	if err != nil {
		return nil, err
	}
	if lic.IsExpired() {
		return nil, errors.New("licença expirada em " + lic.Expiry)
	}
	return lic, nil
}

func saveLicenseToFile(path, text string) error {
	return os.WriteFile(path, []byte(strings.TrimSpace(text)), 0644)
}

func readFileText(path string) (string, error) {
	data, err := os.ReadFile(path)
	if err != nil {
		return "", err
	}
	return strings.TrimSpace(string(data)), nil
}
