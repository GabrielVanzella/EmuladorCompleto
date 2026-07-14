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

[Code]
// Libera a porta 2323 (TCP, entrada) no Firewall do Windows para que os coletores
// consigam alcançar o relay pela rede local. Como o instalador roda sem privilégios
// de administrador, esse passo dispara o UAC (elevação) só pra criar a regra —
// se o usuário recusar, a instalação continua normalmente (só sem a regra).
procedure LiberarFirewall();
var
  ResultCode: Integer;
  Cmd: string;
begin
  Cmd :=
    '/c netsh advfirewall firewall delete rule name="ScanTE Relay 2323" >nul 2>&1 & ' +
    'netsh advfirewall firewall add rule name="ScanTE Relay 2323" ' +
    'dir=in action=allow protocol=TCP localport=2323 profile=any';
  // 'runas' pede elevação (UAC) apenas para este comando.
  ShellExec('runas', ExpandConstant('{sys}\cmd.exe'), Cmd, '', SW_HIDE, ewWaitUntilTerminated, ResultCode);
end;

procedure CurStepChanged(CurStep: TSetupStep);
begin
  if CurStep = ssPostInstall then
    LiberarFirewall();
end;
