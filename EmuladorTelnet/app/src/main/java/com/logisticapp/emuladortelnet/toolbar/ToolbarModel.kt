package com.logisticapp.emuladortelnet.toolbar

/**
 * Um botao de barra de ferramentas: rotulo exibido + acao a executar.
 * color: cor de fundo em hex ARGB (ex: "#FFCC0000"). Vazio = cor padrão petrol.
 */
data class ToolbarButton(
    val label: String,
    val action: String,
    val color: String = ""
)

/**
 * Catalogo de acoes, barras padrao e botoes disponiveis para adicionar.
 */
object ToolbarCatalog {

    const val MAX_BARS = 6

    /**
     * Bytes a enviar ao servidor para a acao. Retorna null para acoes
     * especiais tratadas no app (CONNECT, DISCONNECT, BREAK, COPY, PASTE).
     */
    fun bytesFor(action: String): ByteArray? {
        val esc = 27.toByte()
        fun ss3(c: Char) = byteArrayOf(esc, 'O'.code.toByte(), c.code.toByte())
        fun csi(seq: String): ByteArray = byteArrayOf(esc, '['.code.toByte()) + seq.map { it.code.toByte() }.toByteArray()
        return when (action) {
            "UP"      -> byteArrayOf(esc, '['.code.toByte(), 'A'.code.toByte())
            "DOWN"    -> byteArrayOf(esc, '['.code.toByte(), 'B'.code.toByte())
            "RIGHT"   -> byteArrayOf(esc, '['.code.toByte(), 'C'.code.toByte())
            "LEFT"    -> byteArrayOf(esc, '['.code.toByte(), 'D'.code.toByte())
            "ESC"     -> byteArrayOf(esc)
            "ENTER"   -> byteArrayOf(13)
            "TAB"     -> byteArrayOf(9)
            "BACKTAB" -> byteArrayOf(esc, '['.code.toByte(), 'Z'.code.toByte())
            "HOME"    -> byteArrayOf(esc, '['.code.toByte(), 'H'.code.toByte())
            "END"     -> byteArrayOf(esc, '['.code.toByte(), 'F'.code.toByte())
            "DELCHAR" -> byteArrayOf(127)
            "INSERT"  -> csi("2~")
            "PREVS"   -> csi("5~")
            "NEXTS"   -> csi("6~")
            // Teclas de função (sequências VT220)
            "F1"  -> ss3('P')
            "F2"  -> ss3('Q')
            "F3"  -> ss3('R')
            "F4"  -> ss3('S')
            "F5"  -> csi("15~")
            "F6"  -> csi("17~")
            "F7"  -> csi("18~")
            "F8"  -> csi("19~")
            "F9"  -> csi("20~")
            "F10" -> csi("21~")
            "F11" -> csi("23~")
            "F12" -> csi("24~")
            else -> when {
                action.startsWith("CTRL_") -> {
                    val ch = action.removePrefix("CTRL_").firstOrNull() ?: return null
                    byteArrayOf((ch.uppercaseChar().code - 64).toByte())
                }
                action.startsWith("TEXT:") ->
                    action.removePrefix("TEXT:").toByteArray(Charsets.ISO_8859_1)
                else -> null
            }
        }
    }

    /** Descricao curta da acao (mostrada na tela de configuracao, ao lado do rotulo). */
    fun describe(action: String): String = when {
        action.startsWith("TEXT:") -> "Texto \"${action.removePrefix("TEXT:")}\""
        action.startsWith("CTRL_") -> action.replace("_", "+").replaceFirstChar { 'C' }
        action == "UP"         -> "↑ Seta cima"
        action == "DOWN"       -> "↓ Seta baixo"
        action == "LEFT"       -> "← Seta esquerda"
        action == "RIGHT"      -> "→ Seta direita"
        action == "ESC"        -> "Esc"
        action == "ENTER"      -> "Enter"
        action == "TAB"        -> "Tab"
        action == "HOME"       -> "Home"
        action == "END"        -> "End"
        action == "DELCHAR"    -> "Del (apaga char)"
        action == "BACKTAB"    -> "Shift+Tab"
        action == "INSERT"     -> "Insert"
        action == "PREVS"      -> "Page Up"
        action == "NEXTS"      -> "Page Down"
        action == "COPY"       -> "Copiar"
        action == "PASTE"      -> "Colar"
        action == "CONNECT"    -> "Conectar"
        action == "DISCONNECT" -> "Desconectar"
        action == "BREAK"      -> "Break"
        action.matches(Regex("F\\d{1,2}")) -> "Tecla $action"
        else -> action
    }

    /** Presets prontos de barras que o usuário pode inserir rapidamente na configuração. */
    val presets: Map<String, List<ToolbarButton>> = mapOf(
        "Números (0-9)" to listOf(
            ToolbarButton("1","TEXT:1"), ToolbarButton("2","TEXT:2"),
            ToolbarButton("3","TEXT:3"), ToolbarButton("4","TEXT:4"),
            ToolbarButton("5","TEXT:5"), ToolbarButton("6","TEXT:6"),
            ToolbarButton("7","TEXT:7"), ToolbarButton("8","TEXT:8"),
            ToolbarButton("9","TEXT:9"), ToolbarButton("0","TEXT:0")
        ),
        "Símbolos" to listOf(
            ToolbarButton("!","TEXT:!"), ToolbarButton("@","TEXT:@"),
            ToolbarButton("#","TEXT:#"), ToolbarButton("$","TEXT:$"),
            ToolbarButton("%","TEXT:%"), ToolbarButton("&","TEXT:&"),
            ToolbarButton("*","TEXT:*"), ToolbarButton("-","TEXT:-"),
            ToolbarButton("+","TEXT:+"), ToolbarButton("=","TEXT:="),
            ToolbarButton("/","TEXT:/"), ToolbarButton("\\","TEXT:\\"),
            ToolbarButton(".","TEXT:."), ToolbarButton(",","TEXT:,"),
            ToolbarButton(":","TEXT::"), ToolbarButton(";","TEXT:;"),
            ToolbarButton("_","TEXT:_"), ToolbarButton("?","TEXT:?"),
            ToolbarButton("(","TEXT:("), ToolbarButton(")","TEXT:)"),
            ToolbarButton("[","TEXT:["), ToolbarButton("]","TEXT:]"),
            ToolbarButton("{","TEXT:{"), ToolbarButton("}","TEXT:}")
        ),
        "Navegação" to listOf(
            ToolbarButton("↑","UP"),   ToolbarButton("↓","DOWN"),
            ToolbarButton("←","LEFT"), ToolbarButton("→","RIGHT"),
            ToolbarButton("Home","HOME"), ToolbarButton("End","END"),
            ToolbarButton("PgUp","PREVS"), ToolbarButton("PgDn","NEXTS")
        ),
        "Teclas F" to listOf(
            ToolbarButton("F1","F1"),  ToolbarButton("F2","F2"),
            ToolbarButton("F3","F3"),  ToolbarButton("F4","F4"),
            ToolbarButton("F5","F5"),  ToolbarButton("F6","F6"),
            ToolbarButton("F7","F7"),  ToolbarButton("F8","F8"),
            ToolbarButton("F9","F9"),  ToolbarButton("F10","F10"),
            ToolbarButton("F11","F11"),ToolbarButton("F12","F12")
        ),
        "Ctrl (A-Z)" to ('A'..'Z').map { ToolbarButton("^$it", "CTRL_$it") }
    )

    /** As 4 barras padrao. */
    val defaultToolbars: List<List<ToolbarButton>> = listOf(
        // Barra 1: números scrolláveis
        listOf(
            ToolbarButton("1","TEXT:1"), ToolbarButton("2","TEXT:2"),
            ToolbarButton("3","TEXT:3"), ToolbarButton("4","TEXT:4"),
            ToolbarButton("5","TEXT:5"), ToolbarButton("6","TEXT:6"),
            ToolbarButton("7","TEXT:7"), ToolbarButton("8","TEXT:8"),
            ToolbarButton("9","TEXT:9"), ToolbarButton("0","TEXT:0")
        ),
        // Barra 2: navegação + ações comuns
        listOf(
            ToolbarButton("↑","UP"),  ToolbarButton("↓","DOWN"),
            ToolbarButton("←","LEFT"),ToolbarButton("→","RIGHT"),
            ToolbarButton("Esc","ESC"), ToolbarButton("Enter","ENTER"),
            ToolbarButton("Tab","TAB"), ToolbarButton("Del","DELCHAR")
        ),
        // Barra 3: Ctrl + teclas de sistema
        listOf(
            ToolbarButton("Ctrl+C","CTRL_C"), ToolbarButton("Ctrl+I","CTRL_I"),
            ToolbarButton("Ctrl+E","CTRL_E"), ToolbarButton("Ctrl+K","CTRL_K"),
            ToolbarButton("Ctrl+P","CTRL_P"), ToolbarButton("Ctrl+Y","CTRL_Y"),
            ToolbarButton("Ctrl+Z","CTRL_Z"), ToolbarButton("Ctrl+W","CTRL_W")
        ),
        // Barra 4: vazia (usuário configura)
        emptyList()
    )

    /** Botoes disponiveis na tela "Adicionar botoes". */
    val available: List<ToolbarButton> = listOf(
        // Navegação
        ToolbarButton("↑", "UP"),
        ToolbarButton("↓", "DOWN"),
        ToolbarButton("←", "LEFT"),
        ToolbarButton("→", "RIGHT"),
        ToolbarButton("Home", "HOME"),
        ToolbarButton("End", "END"),
        ToolbarButton("PgUp", "PREVS"),
        ToolbarButton("PgDn", "NEXTS"),
        // Edição
        ToolbarButton("Esc", "ESC"),
        ToolbarButton("Enter", "ENTER"),
        ToolbarButton("Tab", "TAB"),
        ToolbarButton("⇤", "BACKTAB"),
        ToolbarButton("Del", "DELCHAR"),
        ToolbarButton("Ins", "INSERT"),
        ToolbarButton("Copy", "COPY"),
        ToolbarButton("Paste", "PASTE"),
        // Funções F
        ToolbarButton("F1", "F1"),
        ToolbarButton("F2", "F2"),
        ToolbarButton("F3", "F3"),
        ToolbarButton("F4", "F4"),
        ToolbarButton("F5", "F5"),
        ToolbarButton("F6", "F6"),
        ToolbarButton("F7", "F7"),
        ToolbarButton("F8", "F8"),
        ToolbarButton("F9", "F9"),
        ToolbarButton("F10", "F10"),
        ToolbarButton("F11", "F11"),
        ToolbarButton("F12", "F12"),
        // Ctrl
        ToolbarButton("Ctrl+A", "CTRL_A"),
        ToolbarButton("Ctrl+C", "CTRL_C"),
        ToolbarButton("Ctrl+D", "CTRL_D"),
        ToolbarButton("Ctrl+E", "CTRL_E"),
        ToolbarButton("Ctrl+I", "CTRL_I"),
        ToolbarButton("Ctrl+K", "CTRL_K"),
        ToolbarButton("Ctrl+P", "CTRL_P"),
        ToolbarButton("Ctrl+W", "CTRL_W"),
        ToolbarButton("Ctrl+Y", "CTRL_Y"),
        ToolbarButton("Ctrl+Z", "CTRL_Z"),
        // Conexão
        ToolbarButton("Conn.", "CONNECT"),
        ToolbarButton("Break", "BREAK"),
        ToolbarButton("Desc.", "DISCONNECT")
    )
}
