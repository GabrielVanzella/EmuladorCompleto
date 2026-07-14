; ============================================================
; ScanTE Relay — Instalador (Inno Setup)
; Compilar com: ISCC.exe scante-relay.iss
; Gera: dist\ScanTE-Relay-Setup.exe
; ============================================================

#define MyAppName "ScanTE Relay"
#define MyAppVersion "1.0.0"
#define MyAppPublisher "ScanTE"
#define MyAppExeName "scante-relay.exe"

[Setup]
AppId={{9F0B7C2A-6A2E-4B0E-9C0A-3E7B7B7C4F10}}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
AppPublisher={#MyAppPublisher}
DefaultDirName={localappdata}\{#MyAppName}
DefaultGroupName={#MyAppName}
DisableProgramGroupPage=yes
PrivilegesRequired=lowest
OutputDir=..\dist
OutputBaseFilename=ScanTE-Relay-Setup
Compression=lzma
SolidCompression=yes
ArchitecturesInstallIn64BitMode=x64compatible
WizardStyle=modern
UninstallDisplayIcon={app}\{#MyAppExeName}
SetupIconFile=scante-relay.ico

[Languages]
Name: "brazilianportuguese"; MessagesFile: "compiler:Languages\BrazilianPortuguese.isl"

[Tasks]
Name: "desktopicon"; Description: "Criar atalho na área de trabalho"; GroupDescription: "Atalhos adicionais:"
Name: "startupicon"; Description: "Iniciar automaticamente com o Windows"; GroupDescription: "Atalhos adicionais:"; Flags: unchecked

[Files]
Source: "..\scante-relay.exe"; DestDir: "{app}"; Flags: ignoreversion
; Não sobrescreve config já existente numa reinstalação/atualização
Source: "..\scante-relay.json"; DestDir: "{app}"; Flags: onlyifdoesntexist uninsneveruninstall

[Icons]
Name: "{group}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; WorkingDir: "{app}"
Name: "{group}\Desinstalar {#MyAppName}"; Filename: "{uninstallexe}"
Name: "{autodesktop}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; WorkingDir: "{app}"; Tasks: desktopicon
Name: "{userstartup}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; WorkingDir: "{app}"; Tasks: startupicon

[Run]
Filename: "{app}\{#MyAppExeName}"; Description: "Iniciar o {#MyAppName} agora"; Flags: postinstall nowait skipifsilent
