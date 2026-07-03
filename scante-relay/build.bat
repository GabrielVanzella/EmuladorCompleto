@echo off
:: ScanTE Relay Server — Script de Build
:: Requer Go instalado: https://go.dev/dl/
:: Após compilado, o scante-relay.exe roda em qualquer Windows sem dependências.

echo ============================================
echo  ScanTE Relay Server - Build
echo ============================================

where go >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERRO: Go nao encontrado.
    echo.
    echo Instale o Go em: https://go.dev/dl/
    echo Reinicie o terminal apos instalar e rode este script novamente.
    echo.
    pause
    exit /b 1
)

echo Go encontrado:
go version
echo.

echo Compilando scante-relay.exe para Windows x64...
set GOOS=windows
set GOARCH=amd64
set CGO_ENABLED=0
go build -trimpath -ldflags="-s -w" -o scante-relay.exe .

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERRO: Compilacao falhou. Veja mensagem acima.
    pause
    exit /b 1
)

echo.
echo ============================================
echo  Build concluido: scante-relay.exe
echo ============================================
echo.
echo Para iniciar o relay:
echo   scante-relay.exe
echo.
echo Configuracoes em: scante-relay.json
echo.
pause
