package main

import (
	"strconv"

	"github.com/lxn/walk"
	. "github.com/lxn/walk/declarative"
)

// showLicenseDialog exibe a tela de ativação (campos somente-leitura preenchidos
// ao colar/importar uma licença válida). Retorna a licença aceita e o texto bruto
// (pra salvar em disco), ou accepted=false se o usuário cancelou/fechou a janela.
func showLicenseDialog(currentText string) (lic *License, rawText string, accepted bool) {
	var (
		dlg                                          *walk.Dialog
		keyEdit                                      *walk.TextEdit
		statusLabel                                  *walk.Label
		customerEdit, serialEdit, expiryEdit         *walk.LineEdit
		sessionsEdit, devicesEdit, releaseEdit        *walk.LineEdit
		hostEdit                                      *walk.LineEdit
		okBtn                                         *walk.PushButton
	)

	var parsed *License

	clearFields := func() {
		customerEdit.SetText("")
		serialEdit.SetText("")
		sessionsEdit.SetText("")
		devicesEdit.SetText("")
		expiryEdit.SetText("")
		releaseEdit.SetText("")
		hostEdit.SetText("")
		okBtn.SetEnabled(false)
	}

	validate := func() {
		text := keyEdit.Text()
		if text == "" {
			statusLabel.SetText("")
			clearFields()
			return
		}
		l, err := ParseLicense(text)
		if err != nil {
			statusLabel.SetText("✗ " + err.Error())
			statusLabel.SetTextColor(walk.RGB(200, 40, 40))
			clearFields()
			parsed = nil
			return
		}
		if l.IsExpired() {
			statusLabel.SetText("✗ Licença expirada em " + l.Expiry)
			statusLabel.SetTextColor(walk.RGB(200, 40, 40))
			clearFields()
			parsed = nil
			return
		}

		parsed = l
		customerEdit.SetText(l.Customer)
		serialEdit.SetText(l.Serial)
		if l.MaxSessions == 0 {
			sessionsEdit.SetText("Ilimitado")
		} else {
			sessionsEdit.SetText(strconv.Itoa(l.MaxSessions))
		}
		if l.MaxDevices == 0 {
			devicesEdit.SetText("Ilimitado")
		} else {
			devicesEdit.SetText(strconv.Itoa(l.MaxDevices))
		}
		expiryEdit.SetText(l.Expiry)
		releaseEdit.SetText(l.Release)
		hostEdit.SetText(l.ServerHost)
		statusLabel.SetText("✓ Licença válida")
		statusLabel.SetTextColor(walk.RGB(0, 150, 80))
		okBtn.SetEnabled(true)
	}

	dialog := Dialog{
		AssignTo: &dlg,
		Title:    "ScanTE Relay — informações de licença",
		MinSize:  Size{Width: 480, Height: 480},
		Layout:   VBox{},
		Children: []Widget{
			Label{Text: "Informe a chave de licença do ScanTE Relay pra continuar. Cole o texto recebido da ScanTE ou importe o arquivo .lic."},

			Composite{
				Layout: Grid{Columns: 2},
				Children: []Widget{
					Label{Text: "Nome do cliente:"}, LineEdit{AssignTo: &customerEdit, ReadOnly: true},
					Label{Text: "Número de série:"}, LineEdit{AssignTo: &serialEdit, ReadOnly: true},
					Label{Text: "Sessões máximas:"}, LineEdit{AssignTo: &sessionsEdit, ReadOnly: true},
					Label{Text: "Dispositivos máximos:"}, LineEdit{AssignTo: &devicesEdit, ReadOnly: true},
					Label{Text: "Validade:"}, LineEdit{AssignTo: &expiryEdit, ReadOnly: true},
					Label{Text: "Versão suportada:"}, LineEdit{AssignTo: &releaseEdit, ReadOnly: true},
					Label{Text: "Endereço do servidor:"}, LineEdit{AssignTo: &hostEdit, ReadOnly: true},
				},
			},

			Label{Text: "Chave de licença:"},
			TextEdit{AssignTo: &keyEdit, VScroll: true, MinSize: Size{Height: 90}, Text: currentText,
				OnTextChanged: func() { validate() }},

			Label{AssignTo: &statusLabel, Text: ""},

			Composite{
				Layout: HBox{},
				Children: []Widget{
					PushButton{
						Text: "Importar arquivo de licença...",
						OnClicked: func() {
							dlgOpen := walk.FileDialog{Title: "Selecionar arquivo de licença", Filter: "Licença ScanTE (*.lic)|*.lic|Todos os arquivos (*.*)|*.*"}
							ok, err := dlgOpen.ShowOpen(dlg)
							if err != nil || !ok {
								return
							}
							data, err := readFileText(dlgOpen.FilePath)
							if err == nil {
								keyEdit.SetText(data)
								validate()
							}
						},
					},
					HSpacer{},
					PushButton{Text: "Cancelar", OnClicked: func() { dlg.Cancel() }},
					PushButton{AssignTo: &okBtn, Text: "OK", Enabled: false, OnClicked: func() {
						rawText = keyEdit.Text()
						lic = parsed
						dlg.Accept()
					}},
				},
			},
		},
	}

	owner, _ := walk.NewMainWindow()
	code, err := dialog.Run(owner)
	if err != nil {
		return nil, "", false
	}
	if code == walk.DlgCmdOK && lic != nil {
		return lic, rawText, true
	}
	return nil, "", false
}
