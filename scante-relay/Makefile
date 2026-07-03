# ScanTE Relay Server — Makefile
# Gera .exe Windows sem dependências (Go stdlib puro)

BINARY  = scante-relay.exe
VERSION = 1.0.0

.PHONY: build build-linux clean

# Build padrão: Windows x64
build:
	GOOS=windows GOARCH=amd64 CGO_ENABLED=0 \
	go build -trimpath -ldflags="-s -w" -o $(BINARY) .
	@echo "Gerado: $(BINARY)"

# Build alternativo: Linux (para servidores Linux)
build-linux:
	GOOS=linux GOARCH=amd64 CGO_ENABLED=0 \
	go build -trimpath -ldflags="-s -w" -o scante-relay .
	@echo "Gerado: scante-relay (Linux)"

# Testa a compilação
test:
	go vet ./...
	go build ./...

clean:
	rm -f $(BINARY) scante-relay
