package com.logisticapp.emuladortelnet

import android.os.Bundle
import android.view.View
import android.widget.CheckBox
import android.widget.TextView
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.Toolbar
import com.logisticapp.emuladortelnet.settings.AppSettings

/**
 * Configuracoes > Tela > Opcoes de tela.
 *
 * Por enquanto: tamanho da fonte e os dois checkboxes sao funcionais e persistidos.
 * Os demais seletores exibem o valor atual e serao refinados depois.
 */
class ScreenOptionsActivity : AppCompatActivity() {

    private lateinit var settings: AppSettings

    private lateinit var valFontSize: TextView
    private lateinit var valFontName: TextView
    private lateinit var valCursorType: TextView
    private lateinit var valCursorColor: TextView
    private lateinit var valFields3d: TextView
    private lateinit var valShowToolbar: TextView
    private lateinit var valLimitView: TextView
    private lateinit var valDoubleTap: TextView
    private lateinit var checkCursorBlink: CheckBox
    private lateinit var checkFields3dWhite: CheckBox
    private lateinit var checkPinchZoom: CheckBox

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        settings = AppSettings.get(this)
        settings.applyOrientation(this)
        setContentView(R.layout.activity_screen_options)

        val toolbar = findViewById<Toolbar>(R.id.toolbar)
        setSupportActionBar(toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        toolbar.setNavigationOnClickListener { finish() }

        valFontSize     = findViewById(R.id.val_font_size)
        valFontName     = findViewById(R.id.val_font_name)
        valCursorType   = findViewById(R.id.val_cursor_type)
        valCursorColor  = findViewById(R.id.val_cursor_color)
        valFields3d     = findViewById(R.id.val_fields_3d)
        valShowToolbar  = findViewById(R.id.val_show_toolbar)
        valLimitView    = findViewById(R.id.val_limit_view)
        valDoubleTap    = findViewById(R.id.val_double_tap)
        checkCursorBlink   = findViewById(R.id.check_cursor_blink)
        checkFields3dWhite = findViewById(R.id.check_fields_3d_white)
        checkPinchZoom     = findViewById(R.id.check_pinch_zoom)

        loadValues()
        setupListeners()
    }

    private fun loadValues() {
        valFontSize.text    = settings.fontSize.toString()
        valFontName.text    = settings.fontName
        valCursorType.text  = settings.cursorType
        valCursorColor.text = settings.cursorColor
        valFields3d.text    = settings.fields3D
        valShowToolbar.text = settings.showToolbar
        valLimitView.text   = settings.limitView
        valDoubleTap.text   = settings.doubleTapAction
        checkCursorBlink.isChecked   = settings.cursorBlinking
        checkFields3dWhite.isChecked = settings.fields3DWhiteBg
        checkPinchZoom.isChecked     = settings.pinchZoomEnabled
        updateFontSizeRowState()
    }

    /** Quando a pinça está ligada, o tamanho é controlado pelos dedos — o painel fica secundário. */
    private fun updateFontSizeRowState() {
        val painelManda = !settings.pinchZoomEnabled
        findViewById<View>(R.id.row_font_size).alpha = if (painelManda) 1f else 0.4f
        valFontSize.text = if (painelManda) settings.fontSize.toString() else "pinça"
    }

    private fun setupListeners() {
        // Tamanho da fonte (funcional)
        findViewById<View>(R.id.row_font_size).setOnClickListener { pickFontSize() }

        // Checkboxes (funcionais) — clique na linha alterna
        findViewById<View>(R.id.row_cursor_blink).setOnClickListener {
            checkCursorBlink.isChecked = !checkCursorBlink.isChecked
            settings.cursorBlinking = checkCursorBlink.isChecked
        }
        findViewById<View>(R.id.row_fields_3d_white).setOnClickListener {
            checkFields3dWhite.isChecked = !checkFields3dWhite.isChecked
            settings.fields3DWhiteBg = checkFields3dWhite.isChecked
        }
        findViewById<View>(R.id.row_pinch_zoom).setOnClickListener {
            checkPinchZoom.isChecked = !checkPinchZoom.isChecked
            settings.pinchZoomEnabled = checkPinchZoom.isChecked
            updateFontSizeRowState()
        }

        findViewById<View>(R.id.row_font_name).setOnClickListener { pickOption("Nome da fonte",
            arrayOf("Padrão", "Courier New", "Droid Sans Mono"), settings.fontName) {
            settings.fontName = it; valFontName.text = it
        }}
        findViewById<View>(R.id.row_cursor_type).setOnClickListener { pickOption("Tipo de cursor",
            arrayOf("Bloco", "Barra", "Sublinhado", "Nenhum"), settings.cursorType) {
            settings.cursorType = it; valCursorType.text = it
        }}
        findViewById<View>(R.id.row_cursor_color).setOnClickListener { pickOption("Cor do cursor",
            arrayOf("Verde", "Branco", "Ciano", "Amarelo", "Vermelho", "Azul", "Laranja"), settings.cursorColor) {
            settings.cursorColor = it; valCursorColor.text = it
        }}
        findViewById<View>(R.id.row_fields_3d).setOnClickListener { pickOption("Campos variáveis 3D",
            arrayOf("Ligado sem atributos", "Ligado com atributos", "Desligado"), settings.fields3D) {
            settings.fields3D = it; valFields3d.text = it
        }}
        findViewById<View>(R.id.row_show_toolbar).setOnClickListener { pickOption("Mostrar barra de ferramentas",
            arrayOf("Automático", "Sempre", "Nunca"), settings.showToolbar) {
            settings.showToolbar = it; valShowToolbar.text = it
        }}
        findViewById<View>(R.id.row_limit_view).setOnClickListener { pickOption("Limitar visualização da tela",
            arrayOf("Sem limite", "26,20", "40,24", "80,24", "132,24"), settings.limitView) {
            settings.limitView = it; valLimitView.text = it
        }}
        findViewById<View>(R.id.row_double_tap).setOnClickListener { pickOption("Toque duas vezes",
            arrayOf("Redefinir tamanho da tela", "Zoom in", "Zoom out", "Nenhum"), settings.doubleTapAction) {
            settings.doubleTapAction = it; valDoubleTap.text = it
        }}
    }

    private fun pickFontSize() {
        // Com a pinça ligada, o tamanho é definido pelos dedos — evita confusão.
        if (settings.pinchZoomEnabled) {
            AlertDialog.Builder(this)
                .setTitle("Tamanho da fonte")
                .setMessage("O redimensionar com pinça está ligado, então o tamanho é ajustado com dois dedos na tela do terminal.\n\nPara escolher o tamanho aqui no painel, desligue \"Redimensionar com pinça\".")
                .setPositiveButton("Entendi", null)
                .show()
            return
        }
        val sizes = arrayOf("8", "10", "12", "14", "16", "18", "20", "24", "28", "32", "36", "40", "44", "48")
        val current = settings.fontSize.toString()
        val checked = sizes.indexOf(current).takeIf { it >= 0 } ?: 2
        AlertDialog.Builder(this)
            .setTitle("Tamanho da fonte")
            .setSingleChoiceItems(sizes, checked) { dialog, which ->
                val size = sizes[which].toInt()
                settings.fontSize = size
                settings.fontAutoFit = false   // escolha manual: não deixa o auto-fit sobrescrever
                valFontSize.text = size.toString()
                dialog.dismiss()
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    private fun pickOption(title: String, options: Array<String>, current: String, onPick: (String) -> Unit) {
        val checked = options.indexOf(current).takeIf { it >= 0 } ?: 0
        AlertDialog.Builder(this)
            .setTitle(title)
            .setSingleChoiceItems(options, checked) { dialog, which ->
                onPick(options[which])
                dialog.dismiss()
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }
}
