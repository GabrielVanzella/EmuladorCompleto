package com.logisticapp.emuladortelnet

import android.graphics.Color
import android.graphics.drawable.GradientDrawable
import android.os.Bundle
import android.view.View
import android.widget.GridLayout
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.Toolbar
import com.logisticapp.emuladortelnet.settings.AppSettings

/**
 * Configuracoes > Tela > Cores da tela.
 *
 * Secao "Selecao de cores": Primeiro plano, Plano de fundo, Status em primeiro plano,
 * Plano de fundo do status — cada um com um seletor de cores (funcional, persistido).
 * Secao "Ajuste de cor": seletores a refinar depois.
 */
class ScreenColorsActivity : AppCompatActivity() {

    private lateinit var settings: AppSettings

    private lateinit var swatchFg: View
    private lateinit var swatchBg: View
    private lateinit var swatchStatusFg: View
    private lateinit var swatchStatusBg: View
    private lateinit var swatchInputField: View
    private lateinit var swatchFgDark: View
    private lateinit var swatchFgBright: View
    private lateinit var swatchBgAdjust: View

    // Paleta de cores oferecida no seletor
    private val palette = intArrayOf(
        0xFF000000.toInt(), 0xFFFFFFFF.toInt(), 0xFF808080.toInt(), 0xFFC0C0C0.toInt(),
        0xFF00FF00.toInt(), 0xFF008000.toInt(), 0xFF00FFFF.toInt(), 0xFF008080.toInt(),
        0xFF0000FF.toInt(), 0xFF000080.toInt(), 0xFFFFFF00.toInt(), 0xFFFF8000.toInt(),
        0xFFFF0000.toInt(), 0xFF800000.toInt(), 0xFFFF00FF.toInt(), 0xFF800080.toInt()
    )

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        settings = AppSettings.get(this)
        settings.applyOrientation(this)
        setContentView(R.layout.activity_screen_colors)

        // Cores definidas pela empresa: tela travada neste aparelho.
        if (com.logisticapp.emuladortelnet.settings.CompanyConfigStore.hasTheme(this)) {
            android.widget.Toast.makeText(
                this,
                "As cores são definidas pela sua empresa e não podem ser alteradas neste aparelho.",
                android.widget.Toast.LENGTH_LONG
            ).show()
            finish()
            return
        }

        val toolbar = findViewById<Toolbar>(R.id.toolbar)
        setSupportActionBar(toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        toolbar.setNavigationOnClickListener { finish() }

        swatchFg          = findViewById(R.id.swatch_fg)
        swatchBg          = findViewById(R.id.swatch_bg)
        swatchStatusFg    = findViewById(R.id.swatch_status_fg)
        swatchStatusBg    = findViewById(R.id.swatch_status_bg)
        swatchInputField  = findViewById(R.id.swatch_input_field)
        swatchFgDark      = findViewById(R.id.swatch_fg_dark)
        swatchFgBright    = findViewById(R.id.swatch_fg_bright)
        swatchBgAdjust    = findViewById(R.id.swatch_adjust_bg)

        refreshSwatches()
        setupListeners()
    }

    private fun refreshSwatches() {
        applySwatch(swatchFg, settings.colorForeground)
        applySwatch(swatchBg, settings.colorBackground)
        applySwatch(swatchStatusFg, settings.colorStatusForeground)
        applySwatch(swatchStatusBg, settings.colorStatusBackground)
        applySwatch(swatchInputField, settings.colorInputField)
        applySwatch(swatchFgDark, settings.colorFgDark)
        applySwatch(swatchFgBright, settings.colorFgBright)
        applySwatch(swatchBgAdjust, settings.colorBgAdjust)
    }

    private fun setupListeners() {
        findViewById<View>(R.id.row_fg).setOnClickListener {
            showColorPicker("Primeiro plano", false) {
                settings.colorForeground = it; applySwatch(swatchFg, it)
            }
        }
        findViewById<View>(R.id.row_bg).setOnClickListener {
            showColorPicker("Plano de fundo", false) {
                settings.colorBackground = it; applySwatch(swatchBg, it)
            }
        }
        findViewById<View>(R.id.row_status_fg).setOnClickListener {
            showColorPicker("Status em primeiro plano", false) {
                settings.colorStatusForeground = it; applySwatch(swatchStatusFg, it)
            }
        }
        findViewById<View>(R.id.row_status_bg).setOnClickListener {
            showColorPicker("Plano de fundo do status", true) {
                settings.colorStatusBackground = it; applySwatch(swatchStatusBg, it)
            }
        }
        findViewById<View>(R.id.row_input_field).setOnClickListener {
            showColorPicker("Campos de preenchimento", true) {
                settings.colorInputField = it; applySwatch(swatchInputField, it)
            }
        }

        // Ajuste de cor — cor do texto escuro (dim/SGR 2)
        findViewById<View>(R.id.row_fg_dark).setOnClickListener {
            showColorPicker("Primeiro plano escuro", true) {
                settings.colorFgDark = it; applySwatch(swatchFgDark, it)
            }
        }
        // Ajuste de cor — cor do texto brilhante (bold)
        findViewById<View>(R.id.row_fg_bright).setOnClickListener {
            showColorPicker("Primeiro plano brilhante", true) {
                settings.colorFgBright = it; applySwatch(swatchFgBright, it)
            }
        }
        // Ajuste de cor — fundo para texto escuro
        findViewById<View>(R.id.row_adjust_bg).setOnClickListener {
            showColorPicker("Plano de fundo (ajuste)", true) {
                settings.colorBgAdjust = it; applySwatch(swatchBgAdjust, it)
            }
        }
    }

    /** Pinta o quadradinho de amostra. color == 0 => "padrao" (borda tracejada vermelha). */
    private fun applySwatch(view: View, color: Int) {
        val d = GradientDrawable()
        d.cornerRadius = dp(4f)
        if (color == 0) {
            d.setColor(Color.TRANSPARENT)
            d.setStroke(dp(2f).toInt(), Color.RED, dp(4f), dp(3f))
        } else {
            d.setColor(color)
            d.setStroke(dp(1f).toInt(), Color.parseColor("#888888"))
        }
        view.background = d
    }

    private fun showColorPicker(title: String, allowNone: Boolean, onPick: (Int) -> Unit) {
        val pad = dp(16f).toInt()
        val cell = dp(52f).toInt()
        val margin = dp(6f).toInt()

        val grid = GridLayout(this).apply {
            columnCount = 4
            setPadding(pad, pad, pad, pad)
        }

        val colors = if (allowNone) intArrayOf(0) + palette else palette
        val dialogRef = arrayOfNulls<AlertDialog>(1)

        for (c in colors) {
            val sw = View(this)
            sw.layoutParams = GridLayout.LayoutParams().apply {
                width = cell; height = cell
                setMargins(margin, margin, margin, margin)
            }
            applySwatch(sw, c)
            sw.setOnClickListener {
                onPick(c)
                dialogRef[0]?.dismiss()
            }
            grid.addView(sw)
        }

        dialogRef[0] = AlertDialog.Builder(this)
            .setTitle(title)
            .setView(grid)
            .setNegativeButton("Cancelar", null)
            .create()
        dialogRef[0]?.show()
    }

    private fun dp(v: Float): Float = v * resources.displayMetrics.density
}
