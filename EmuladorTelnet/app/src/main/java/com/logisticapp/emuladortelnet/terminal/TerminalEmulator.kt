package com.logisticapp.emuladortelnet.terminal

import android.graphics.Color
import timber.log.Timber

/**
 * Emulador de tela VT100/ANSI.
 *
 * Mantém uma grade de caracteres (rows x cols) com atributos de cor por célula.
 * Interpreta escape sequences de posicionamento de cursor, limpeza de tela e cores,
 * permitindo renderizar aplicações de tela cheia (curses-like) como o Protheus/SHARK.
 *
 * O estado de parsing é PERSISTENTE entre chamadas de feed(): as escape sequences
 * podem chegar partidas entre dois read() do socket.
 */
class TerminalEmulator(
    val rows: Int = 24,
    val cols: Int = 80
) {

    private var defaultFg = Color.rgb(0, 255, 0)   // verde terminal (configuravel)
    private val defaultBg = Color.BLACK

    /** Define a cor padrao do texto (Primeiro plano). Deve ser chamado antes de conectar. */
    fun setDefaultForeground(color: Int) {
        defaultFg = color
        curFg = color
        for (r in 0 until rows) {
            for (c in 0 until cols) fg[r][c] = color
        }
    }

    private val chars     = Array(rows) { CharArray(cols) { ' ' } }
    private val fg        = Array(rows) { IntArray(cols) { defaultFg } }
    private val bold      = Array(rows) { BooleanArray(cols) { false } }
    private val dim       = Array(rows) { BooleanArray(cols) { false } }
    private val underline = Array(rows) { BooleanArray(cols) { false } }
    private val blink     = Array(rows) { BooleanArray(cols) { false } }
    // Marca celulas que sao "campo de preenchimento" (video reverso ou cor de fundo)
    private val field = Array(rows) { BooleanArray(cols) { false } }

    private var cursorRow = 0
    private var cursorCol = 0

    private var curFg = defaultFg
    private var curBold = false
    private var curDim = false
    private var curUnderline = false
    private var curBlink = false
    private var curReverse = false
    private var curBgSet = false
    private var curConceal = false   // texto oculto (tipico de campo de senha)

    // Cor de fundo dos campos de preenchimento. 0 = reverso natural (usa a cor do texto).
    private var fieldColor = 0

    /** Define a cor de fundo dos campos de preenchimento (0 = reverso natural). */
    fun setFieldColor(color: Int) {
        fieldColor = color
    }

    // ---- Cursor display settings (Opções de tela) ----
    /** true = cursor visível; false = oculto (blinking toggle). */
    var showCursor: Boolean = true
    /** "Barra", "Bloco", "Sublinhado", "Nenhum" */
    var cursorDisplayType: String = "Bloco"
    /** Cor ARGB do cursor. */
    var cursorDisplayColor: Int = Color.rgb(0, 200, 100)
    /** "Ligado sem atributos", "Ligado com atributos", "Desligado" */
    var fields3dMode: String = "Ligado sem atributos"

    // ---- Color Adjust settings (Ajuste de cor) ----
    /** Cor do texto escuro (SGR 2 = dim). 0 = escurece 50% automaticamente. */
    var dimFgColor: Int = 0
    /** Cor do texto brilhante (bold com modo cor). 0 = clareia 50% automaticamente. */
    var brightFgColor: Int = 0
    /** Fundo para texto escuro (dim). 0 = sem ajuste de fundo. */
    var bgAdjustColor: Int = 0

    // ---- VT Attr Map settings (Mapeamento de atributos VT) ----
    /** "Negrito", "Cor brilhante", "Negrito+Cor", "Nenhum" */
    var boldMode: String = "Negrito"
    /** SGR 4 (sublinhado) habilitado. */
    var underlineEnabled: Boolean = true
    /** Como renderizar piscante (SGR 5): "Negrito", "Cor brilhante", "Nenhum" */
    var blinkMode: String = "Nenhum"

    private enum class State { NORMAL, ESC, CSI }
    private var state = State.NORMAL
    private val csiParams = StringBuilder()

    // ---- VT Options (aplicadas pelo TelnetViewModel via setVtOptions) ----
    var addLfToCr = false           // \r também avança linha
    var noAutoWrap = false          // nenhuma coluna 81 — cursor fica na col 80
    var scrollMode = true           // true=rola, false=wrapa para linha 0
    var silenceHostAlarm = true     // ignorar BEL
    var maxConsecutiveAlarms = Int.MAX_VALUE
    var ignoreUnknownEscapes = true // true=ignora silenciosamente
    var answerbackString = ""       // resposta ao ENQ (0x05)
    var vtDaAlias = "VT100"         // resposta a ESC[c (DA query)
    var onBell: (() -> Unit)? = null
    var onEnq: ((String) -> Unit)? = null
    var onDeviceAttrQuery: ((String) -> Unit)? = null
    private var consecutiveBells = 0

    // ---- Transliteration Options ----
    var useSiso = false                  // SI/SO charset-shift suporte (0x0F / 0x0E)
    private var charsetG1Active = false  // true = G1 charset ativo (pós SO)

    // ---- General Emulation Options ----
    var destructiveBackspace = false     // BS apaga o char na posição anterior
    var captureOnCr = "LF"              // O que fazer ao receber CR: "Desativado", "LF", "CR+LF"

    companion object {
        private const val ESC = '\u001B'
        private const val NUL = '\u0000'
        private const val BEL = '\u0007'
        private val DEL = 127.toChar()   // DEL (0x7F): controle, nunca imprimivel
    }

    /**
     * Alimenta o emulador com texto recebido do servidor.
     */
    fun feed(text: String) {
        for (ch in text) {
            when (state) {
                State.NORMAL -> handleNormal(ch)
                State.ESC    -> handleEsc(ch)
                State.CSI    -> handleCsi(ch)
            }
        }
    }

    private fun handleNormal(ch: Char) {
        when (ch) {
            ESC -> state = State.ESC
            '\n' -> {
                consecutiveBells = 0
                cursorRow++
                if (cursorRow >= rows) { if (scrollMode) scrollUp() else cursorRow = 0 }
            }
            '\r' -> {
                consecutiveBells = 0
                cursorCol = 0
                // addLfToCr (VT Opções) OU captureOnCr (Geral) com LF/CR+LF
                if (addLfToCr || captureOnCr == "LF" || captureOnCr == "CR+LF") {
                    cursorRow++
                    if (cursorRow >= rows) { if (scrollMode) scrollUp() else cursorRow = 0 }
                }
            }
            '\b' -> {
                consecutiveBells = 0
                if (cursorCol > 0) {
                    cursorCol--
                    if (destructiveBackspace) {
                        chars[cursorRow][cursorCol] = ' '
                        fg[cursorRow][cursorCol]    = curFg
                        bold[cursorRow][cursorCol]  = false
                        field[cursorRow][cursorCol] = false
                    }
                }
            }
            '\t' -> { consecutiveBells = 0; cursorCol = ((cursorCol / 8) + 1) * 8; if (cursorCol >= cols) cursorCol = cols - 1 }
            // NUL e DEL são caracteres de controle: nunca viram texto na tela.
            // (Sem isso, um DEL ecoado pelo host aparecia como quadradinho "□".)
            NUL, DEL -> { /* ignorar */ }
            '' -> { if (useSiso) charsetG1Active = true  }  // SO: shift out → G1
            '' -> { if (useSiso) charsetG1Active = false }  // SI: shift in  → G0
            BEL -> {
                consecutiveBells++
                if (!silenceHostAlarm && (maxConsecutiveAlarms == Int.MAX_VALUE || consecutiveBells <= maxConsecutiveAlarms)) {
                    onBell?.invoke()
                }
            }
            '' -> onEnq?.invoke(answerbackString)   // ENQ — servidor pede identificação
            else -> { consecutiveBells = 0; putChar(ch) }
        }
    }

    private fun handleEsc(ch: Char) {
        when (ch) {
            '[' -> { csiParams.setLength(0); state = State.CSI }
            else -> {
                if (!ignoreUnknownEscapes) Timber.d("ESC desconhecido: $ch")
                state = State.NORMAL
            }
        }
    }

    private fun handleCsi(ch: Char) {
        if (ch in '0'..'9' || ch == ';' || ch == '?') {
            csiParams.append(ch)
            return
        }
        // Letra final → executar comando
        executeCsi(ch, csiParams.toString())
        state = State.NORMAL
    }

    private fun executeCsi(command: Char, paramStr: String) {
        // '?' = private mode (ex: ESC[?25h cursor). Ignorar esses comandos.
        if (paramStr.startsWith("?")) return

        val params = paramStr.split(';').map { it.toIntOrNull() }

        when (command) {
            'H', 'f' -> {
                // CUP — linha;coluna (1-based)
                val row = (params.getOrNull(0) ?: 1).coerceAtLeast(1)
                val col = (params.getOrNull(1) ?: 1).coerceAtLeast(1)
                cursorRow = (row - 1).coerceIn(0, rows - 1)
                cursorCol = (col - 1).coerceIn(0, cols - 1)
            }
            'A' -> cursorRow = (cursorRow - (params.getOrNull(0) ?: 1)).coerceAtLeast(0)
            'B' -> cursorRow = (cursorRow + (params.getOrNull(0) ?: 1)).coerceAtMost(rows - 1)
            'C' -> cursorCol = (cursorCol + (params.getOrNull(0) ?: 1)).coerceAtMost(cols - 1)
            'D' -> cursorCol = (cursorCol - (params.getOrNull(0) ?: 1)).coerceAtLeast(0)
            'J' -> eraseDisplay(params.getOrNull(0) ?: 0)
            'K' -> eraseLine(params.getOrNull(0) ?: 0)
            'm' -> applySgr(params)
            'd' -> cursorRow = ((params.getOrNull(0) ?: 1) - 1).coerceIn(0, rows - 1)  // VPA
            'G' -> cursorCol = ((params.getOrNull(0) ?: 1) - 1).coerceIn(0, cols - 1)  // CHA
            'c' -> if ((params.getOrNull(0) ?: 0) == 0) onDeviceAttrQuery?.invoke(vtDaAlias)  // DA
            else -> if (!ignoreUnknownEscapes) Timber.d("CSI nao tratado: $command ($paramStr)")
        }
    }

    private fun putChar(ch: Char) {
        if (cursorCol >= cols) {
            if (noAutoWrap) {
                cursorCol = cols - 1  // fica na última coluna (sem coluna 81)
            } else {
                cursorCol = 0
                cursorRow++
                if (cursorRow >= rows) { if (scrollMode) scrollUp() else cursorRow = 0 }
            }
        }
        if (cursorRow in 0 until rows && cursorCol in 0 until cols) {
            chars[cursorRow][cursorCol]     = ch
            fg[cursorRow][cursorCol]        = curFg
            bold[cursorRow][cursorCol]      = curBold
            dim[cursorRow][cursorCol]       = curDim
            underline[cursorRow][cursorCol] = curUnderline
            blink[cursorRow][cursorCol]     = curBlink
            field[cursorRow][cursorCol]     = curReverse || curBgSet || curConceal
        }
        cursorCol++
    }

    private fun scrollUp() {
        // Rola tudo uma linha para cima e limpa a última
        for (r in 0 until rows - 1) {
            chars[r]     = chars[r + 1].copyOf()
            fg[r]        = fg[r + 1].copyOf()
            bold[r]      = bold[r + 1].copyOf()
            dim[r]       = dim[r + 1].copyOf()
            underline[r] = underline[r + 1].copyOf()
            blink[r]     = blink[r + 1].copyOf()
            field[r]     = field[r + 1].copyOf()
        }
        val last = rows - 1
        chars[last]     = CharArray(cols) { ' ' }
        fg[last]        = IntArray(cols) { defaultFg }
        bold[last]      = BooleanArray(cols) { false }
        dim[last]       = BooleanArray(cols) { false }
        underline[last] = BooleanArray(cols) { false }
        blink[last]     = BooleanArray(cols) { false }
        field[last]     = BooleanArray(cols) { false }
        cursorRow = last
    }

    private fun eraseDisplay(mode: Int) {
        when (mode) {
            2, 3 -> {
                // Tela inteira + cursor home
                for (r in 0 until rows) clearRow(r)
                cursorRow = 0
                cursorCol = 0
            }
            0 -> {
                // Do cursor até o fim
                clearRowFrom(cursorRow, cursorCol)
                for (r in cursorRow + 1 until rows) clearRow(r)
            }
            1 -> {
                // Do início até o cursor
                for (r in 0 until cursorRow) clearRow(r)
                clearRowTo(cursorRow, cursorCol)
            }
        }
    }

    private fun eraseLine(mode: Int) {
        when (mode) {
            0 -> clearRowFrom(cursorRow, cursorCol)
            1 -> clearRowTo(cursorRow, cursorCol)
            2 -> clearRow(cursorRow)
        }
    }

    private fun clearRow(r: Int) {
        for (c in 0 until cols) {
            chars[r][c]     = ' '
            fg[r][c]        = defaultFg
            bold[r][c]      = false
            dim[r][c]       = false
            underline[r][c] = false
            blink[r][c]     = false
            field[r][c]     = false
        }
    }

    private fun clearRowFrom(r: Int, fromCol: Int) {
        for (c in fromCol until cols) {
            chars[r][c]     = ' '
            fg[r][c]        = defaultFg
            bold[r][c]      = false
            dim[r][c]       = false
            underline[r][c] = false
            blink[r][c]     = false
            field[r][c]     = false
        }
    }

    private fun clearRowTo(r: Int, toCol: Int) {
        for (c in 0..toCol.coerceAtMost(cols - 1)) {
            chars[r][c]     = ' '
            fg[r][c]        = defaultFg
            bold[r][c]      = false
            dim[r][c]       = false
            underline[r][c] = false
            blink[r][c]     = false
            field[r][c]     = false
        }
    }

    private fun applySgr(params: List<Int?>) {
        // Sem parâmetros = reset
        val list = if (params.isEmpty() || (params.size == 1 && params[0] == null)) listOf(0) else params
        Timber.d("SGR: $list")
        for (p in list) {
            when (p) {
                0, null -> { curFg = defaultFg; curBold = false; curDim = false; curUnderline = false; curBlink = false; curReverse = false; curBgSet = false; curConceal = false }
                1  -> curBold = true
                2  -> curDim = true
                4  -> curUnderline = true
                5  -> curBlink = true
                22 -> { curBold = false; curDim = false }
                24 -> curUnderline = false
                25 -> curBlink = false
                7  -> curReverse = true       // video reverso (campo)
                27 -> curReverse = false     // fim do reverso
                8  -> curConceal = true       // oculto (campo de senha)
                28 -> curConceal = false     // fim do oculto
                in 40..47, in 100..107 -> curBgSet = true   // cor de fundo (campo)
                49 -> curBgSet = false       // fundo padrao
                30 -> curFg = Color.rgb(80, 80, 80)
                31 -> curFg = Color.rgb(255, 0, 0)
                32 -> curFg = Color.rgb(0, 255, 0)
                33 -> curFg = Color.rgb(255, 255, 0)
                34 -> curFg = Color.rgb(80, 120, 255)
                35 -> curFg = Color.rgb(255, 0, 255)
                36 -> curFg = Color.rgb(0, 255, 255)
                37 -> curFg = Color.WHITE
                90 -> curFg = Color.rgb(128, 128, 128)
                91 -> curFg = Color.rgb(255, 128, 128)
                92 -> curFg = Color.rgb(128, 255, 128)
                93 -> curFg = Color.rgb(255, 255, 128)
                94 -> curFg = Color.rgb(128, 160, 255)
                95 -> curFg = Color.rgb(255, 128, 255)
                96 -> curFg = Color.rgb(128, 255, 255)
                97 -> curFg = Color.WHITE
            }
        }
    }

    /**
     * Renderiza a grade inteira como HTML monoespaçado para exibir em TextView.
     */
    fun renderHtml(): String {
        val sb = StringBuilder()
        sb.append("<html><body>")

        // Última linha com conteúdo (evita altura excessiva de linhas vazias no fim)
        var lastContentRow = rows - 1
        while (lastContentRow > 0 && isRowBlank(lastContentRow)) lastContentRow--

        for (r in 0..lastContentRow) {
            var c = 0
            while (c < cols) {
                val color = fg[r][c]
                val isBold = bold[r][c]
                // Agrupar células consecutivas com mesmo estilo
                val segStart = c
                while (c < cols && fg[r][c] == color && bold[r][c] == isBold) c++
                val segment = buildString {
                    for (i in segStart until c) append(chars[r][i])
                }
                val colorHex = String.format("#%06X", 0xFFFFFF and color)
                sb.append("<span style=\"color:$colorHex")
                if (isBold) sb.append(";font-weight:bold")
                sb.append("\">")
                sb.append(escapeHtml(segment))
                sb.append("</span>")
            }
            if (r < lastContentRow) sb.append("<br/>")
        }

        sb.append("</body></html>")
        return sb.toString()
    }

    /**
     * Renderiza a grade como CharSequence com spans de cor/negrito/campo.
     * Campos de preenchimento (video reverso/fundo) recebem cor de fundo (fieldColor).
     * A célula do cursor sempre recebe destaque para indicar o campo ativo.
     */
    fun renderSpannable(): CharSequence {
        val sb = android.text.SpannableStringBuilder()
        val spannable = android.text.Spannable.SPAN_EXCLUSIVE_EXCLUSIVE

        var lastContentRow = rows - 1
        while (lastContentRow > 0 && isRowBlank(lastContentRow)) lastContentRow--
        // Garante que a linha do cursor seja sempre renderizada (mesmo que esteja em linha vazia)
        if (cursorRow > lastContentRow) lastContentRow = cursorRow.coerceAtMost(rows - 1)

        for (r in 0..lastContentRow) {
            var c = 0
            while (c < cols) {
                val color       = fg[r][c]
                val isBold      = bold[r][c]
                val isDim       = dim[r][c]
                val isUnderline = underline[r][c]
                val isBlink     = blink[r][c]
                val isField     = field[r][c]
                val segStart = c
                while (c < cols
                    && fg[r][c]        == color
                    && bold[r][c]      == isBold
                    && dim[r][c]       == isDim
                    && underline[r][c] == isUnderline
                    && blink[r][c]     == isBlink
                    && field[r][c]     == isField) c++

                val start = sb.length
                for (i in segStart until c) sb.append(chars[r][i])
                val end = sb.length

                if (isField && fields3dMode != "Desligado") {
                    val bg = if (fieldColor != 0) fieldColor else color
                    sb.setSpan(android.text.style.BackgroundColorSpan(bg), start, end, spannable)
                    sb.setSpan(android.text.style.ForegroundColorSpan(contrastOn(bg)), start, end, spannable)
                    if (fields3dMode == "Ligado com atributos") {
                        sb.setSpan(android.text.style.UnderlineSpan(), start, end, spannable)
                    }
                } else {
                    val effectiveFg = when {
                        isDim && dimFgColor != 0  -> dimFgColor
                        isDim                     -> darkenColor(color)
                        isBold && (boldMode == "Cor brilhante" || boldMode == "Negrito+Cor") && brightFgColor != 0 -> brightFgColor
                        isBold && (boldMode == "Cor brilhante" || boldMode == "Negrito+Cor") -> brightenColor(color)
                        else                      -> color
                    }
                    sb.setSpan(android.text.style.ForegroundColorSpan(effectiveFg), start, end, spannable)
                    if (isDim && bgAdjustColor != 0) {
                        sb.setSpan(android.text.style.BackgroundColorSpan(bgAdjustColor), start, end, spannable)
                    }
                }

                // Peso negrito
                if (isBold && (boldMode == "Negrito" || boldMode == "Negrito+Cor")) {
                    sb.setSpan(android.text.style.StyleSpan(android.graphics.Typeface.BOLD), start, end, spannable)
                }

                // Sublinhado (SGR 4)
                if (isUnderline && underlineEnabled) {
                    sb.setSpan(android.text.style.UnderlineSpan(), start, end, spannable)
                }

                // Piscante (SGR 5) — renderizado conforme blinkMode
                if (isBlink) when (blinkMode) {
                    "Negrito" -> sb.setSpan(
                        android.text.style.StyleSpan(android.graphics.Typeface.BOLD), start, end, spannable)
                    "Cor brilhante" -> sb.setSpan(
                        android.text.style.ForegroundColorSpan(brightenColor(color)), start, end, spannable)
                }
            }
            if (r < lastContentRow) sb.append("\n")
        }

        // Cursor: posição = cursorRow * (cols + 1) + cursorCol
        if (showCursor && cursorDisplayType != "Nenhum" && cursorCol < cols) {
            val cursorPos = cursorRow * (cols + 1) + cursorCol
            if (cursorPos < sb.length) {
                when (cursorDisplayType) {
                    "Bloco" -> {
                        sb.setSpan(android.text.style.BackgroundColorSpan(cursorDisplayColor),
                            cursorPos, cursorPos + 1, spannable)
                        sb.setSpan(android.text.style.ForegroundColorSpan(contrastOn(cursorDisplayColor)),
                            cursorPos, cursorPos + 1, spannable)
                    }
                    "Sublinhado" -> {
                        sb.setSpan(android.text.style.UnderlineSpan(), cursorPos, cursorPos + 1, spannable)
                        sb.setSpan(android.text.style.ForegroundColorSpan(cursorDisplayColor),
                            cursorPos, cursorPos + 1, spannable)
                    }
                    else -> { // "Barra" — destaque semi-transparente
                        val semiColor = (cursorDisplayColor and 0x00FFFFFF) or 0x60000000.toInt()
                        sb.setSpan(android.text.style.BackgroundColorSpan(semiColor),
                            cursorPos, cursorPos + 1, spannable)
                    }
                }
            }
        }

        return sb
    }

    private fun darkenColor(color: Int): Int {
        val r = (Color.red(color) * 0.5f).toInt()
        val g = (Color.green(color) * 0.5f).toInt()
        val b = (Color.blue(color) * 0.5f).toInt()
        return Color.rgb(r, g, b)
    }

    private fun brightenColor(color: Int): Int {
        val r = (Color.red(color) + (255 - Color.red(color)) * 0.5f).toInt().coerceAtMost(255)
        val g = (Color.green(color) + (255 - Color.green(color)) * 0.5f).toInt().coerceAtMost(255)
        val b = (Color.blue(color) + (255 - Color.blue(color)) * 0.5f).toInt().coerceAtMost(255)
        return Color.rgb(r, g, b)
    }

    /** Preto ou branco, o que tiver melhor contraste sobre [bg]. */
    private fun contrastOn(bg: Int): Int {
        val r = Color.red(bg); val g = Color.green(bg); val b = Color.blue(bg)
        val luma = (0.299 * r + 0.587 * g + 0.114 * b)
        return if (luma > 140) Color.BLACK else Color.WHITE
    }

    private fun isRowBlank(r: Int): Boolean {
        for (c in 0 until cols) if (chars[r][c] != ' ') return false
        return true
    }

    private fun escapeHtml(s: String): String {
        val out = StringBuilder()
        for (ch in s) {
            when (ch) {
                '&' -> out.append("&amp;")
                '<' -> out.append("&lt;")
                '>' -> out.append("&gt;")
                ' ' -> out.append("&nbsp;")    // preservar alinhamento (Html colapsa espaços)
                else -> out.append(ch)
            }
        }
        return out.toString()
    }

    /** Retorna o conteúdo atual da tela como lista de strings (uma por linha), sem trailing spaces. */
    fun getScreenText(): List<String> {
        return (0 until rows).map { r -> String(chars[r]).trimEnd() }
    }

    fun reset() {
        for (r in 0 until rows) clearRow(r)
        cursorRow = 0
        cursorCol = 0
        curFg = defaultFg
        curBold = false
        curDim = false
        curUnderline = false
        curBlink = false
        curReverse = false
        curBgSet = false
        curConceal = false
        state = State.NORMAL
        csiParams.setLength(0)
        consecutiveBells = 0
    }
}
