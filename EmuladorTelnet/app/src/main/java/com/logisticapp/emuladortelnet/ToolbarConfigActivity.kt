package com.logisticapp.emuladortelnet

import android.os.Bundle
import android.text.InputFilter
import android.util.TypedValue
import android.view.Gravity
import android.view.Menu
import android.view.MenuItem
import android.view.View
import android.view.ViewGroup
import android.widget.EditText
import android.widget.LinearLayout
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.Toolbar
import com.logisticapp.emuladortelnet.settings.AppSettings
import com.logisticapp.emuladortelnet.toolbar.ToolbarCatalog

class ToolbarConfigActivity : AppCompatActivity() {

    private lateinit var settings: AppSettings
    private lateinit var container: LinearLayout

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        settings = AppSettings.get(this)
        settings.applyOrientation(this)
        setContentView(R.layout.activity_toolbar_config)

        // Teclas definidas pela empresa: tela travada neste aparelho.
        if (com.logisticapp.emuladortelnet.settings.CompanyConfigStore.hasKeys(this)) {
            android.widget.Toast.makeText(
                this,
                "As teclas são definidas pela sua empresa e não podem ser alteradas neste aparelho.",
                android.widget.Toast.LENGTH_LONG
            ).show()
            finish()
            return
        }

        val toolbar = findViewById<Toolbar>(R.id.toolbar)
        setSupportActionBar(toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        toolbar.setNavigationOnClickListener { finish() }

        container = findViewById(R.id.bars_container)
    }

    override fun onResume() {
        super.onResume()
        buildBars()
    }

    override fun onCreateOptionsMenu(menu: Menu?): Boolean {
        menuInflater.inflate(R.menu.menu_toolbar_config, menu)
        return true
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        return when (item.itemId) {
            R.id.menu_add_button -> { chooseBarToAdd(); true }
            else -> super.onOptionsItemSelected(item)
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Construção da lista
    // ──────────────────────────────────────────────────────────────────────────

    private fun buildBars() {
        container.removeAllViews()
        val bars = settings.toolbars
        for ((barIndex, bar) in bars.withIndex()) {
            container.addView(sectionHeader(barIndex, bars.size))
            if (bar.isEmpty()) {
                container.addView(emptyHint("(sem botões — use ADICIONAR no topo)"))
            } else {
                for ((btnIndex, btn) in bar.withIndex()) {
                    container.addView(buttonRow(barIndex, btnIndex, btn.label,
                        ToolbarCatalog.describe(btn.action), btn.color))
                }
            }
            container.addView(divider())
        }
        container.addView(newBarRow())
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Cabeçalho da barra (título + ▲ ▼ 🗑)
    // ──────────────────────────────────────────────────────────────────────────

    private fun sectionHeader(barIndex: Int, totalBars: Int): View {
        val row = LinearLayout(this).apply {
            orientation = LinearLayout.HORIZONTAL
            gravity = Gravity.CENTER_VERTICAL
            setPadding(dp(16), dp(18), dp(8), dp(6))
            layoutParams = LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT)
        }

        val titleView = TextView(this).apply {
            text = "Barra de ferramentas ${barIndex + 1}"
            setTextColor(0xFF555555.toInt())
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 13f)
            layoutParams = LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f)
        }

        row.addView(titleView)

        // ▲ Subir
        if (barIndex > 0) row.addView(iconBtn("▲", 0xFF0F7ABF.toInt()) {
            settings.moveBarUp(barIndex); buildBars()
        })

        // ▼ Descer
        if (barIndex < totalBars - 1) row.addView(iconBtn("▼", 0xFF0F7ABF.toInt()) {
            settings.moveBarDown(barIndex); buildBars()
        })

        // 🗑 Excluir barra (só aparece se há mais de 1 barra)
        if (totalBars > 1) row.addView(iconBtn("🗑", 0xFFCC0000.toInt()) {
            confirmDeleteBar(barIndex)
        })

        return row
    }

    private fun iconBtn(icon: String, color: Int, onClick: () -> Unit): TextView {
        return TextView(this).apply {
            text = icon
            setTextColor(color)
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 17f)
            gravity = Gravity.CENTER
            minWidth = dp(38)
            setPadding(dp(4), dp(4), dp(4), dp(4))
            isClickable = true
            isFocusable = true
            background = selectableItemBg()
            setOnClickListener { onClick() }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Linha "＋ Nova barra" no final da lista
    // ──────────────────────────────────────────────────────────────────────────

    private fun newBarRow(): View {
        val row = LinearLayout(this).apply {
            orientation = LinearLayout.HORIZONTAL
            gravity = Gravity.CENTER_VERTICAL
            minimumHeight = dp(52)
            setPadding(dp(16), dp(8), dp(16), dp(8))
            isClickable = true
            isFocusable = true
            background = themedSelectableBackground()
            layoutParams = LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT)
        }
        row.addView(TextView(this).apply {
            text = "＋  Nova barra de ferramentas"
            setTextColor(getColor(R.color.primary))
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 15f)
        })
        row.setOnClickListener {
            if (settings.toolbars.size >= ToolbarCatalog.MAX_BARS) {
                Toast.makeText(this, "Máximo de ${ToolbarCatalog.MAX_BARS} barras atingido",
                    Toast.LENGTH_SHORT).show()
            } else {
                settings.addBar()
                buildBars()
            }
        }
        return row
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Adicionar botão a uma barra (dinâmico)
    // ──────────────────────────────────────────────────────────────────────────

    private fun chooseBarToAdd() {
        val bars = settings.toolbars
        val names = Array(bars.size) { "Barra de ferramentas ${it + 1}" }
        AlertDialog.Builder(this)
            .setTitle("Adicionar botões a qual barra?")
            .setItems(names) { _, which ->
                val intent = android.content.Intent(this, ToolbarAddActivity::class.java)
                intent.putExtra(ToolbarAddActivity.EXTRA_BAR_INDEX, which)
                startActivity(intent)
            }
            .show()
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Linha de botão (chip + descrição + ✎ + ✕)
    // ──────────────────────────────────────────────────────────────────────────

    private fun buttonRow(barIndex: Int, btnIndex: Int, label: String, desc: String, color: String): View {
        val row = LinearLayout(this).apply {
            orientation = LinearLayout.HORIZONTAL
            gravity = Gravity.CENTER_VERTICAL
            minimumHeight = dp(52)
            setPadding(dp(16), dp(6), dp(8), dp(6))
            layoutParams = LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT)
        }

        val chipColor = if (color.isNotEmpty()) parseColor(color) else 0xFF2E5C6E.toInt()
        val chip = TextView(this).apply {
            text = label
            setTextColor(0xFFFFFFFF.toInt())
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 13f)
            gravity = Gravity.CENTER
            minWidth = dp(56)
            setPadding(dp(12), dp(6), dp(12), dp(6))
            background = chipBackground(chipColor)
        }

        val descView = TextView(this).apply {
            text = desc
            setTextColor(0xFF888888.toInt())
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 14f)
            setPadding(dp(14), 0, 0, 0)
            layoutParams = LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f)
        }

        val colorView = iconBtn("🎨", 0xFF777777.toInt()) {
            showColorDialog(barIndex, btnIndex, label)
        }

        val renameView = iconBtn("✎", 0xFF0F7ABF.toInt()) {
            showRenameDialog(barIndex, btnIndex, label)
        }

        val removeView = iconBtn("✕", 0xFFCC0000.toInt()) {
            confirmRemove(barIndex, btnIndex, label)
        }

        row.addView(chip)
        row.addView(descView)
        row.addView(colorView)
        row.addView(renameView)
        row.addView(removeView)
        return row
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Diálogos
    // ──────────────────────────────────────────────────────────────────────────

    private fun showRenameDialog(barIndex: Int, btnIndex: Int, currentLabel: String) {
        val et = EditText(this).apply {
            setText(currentLabel)
            setSelection(currentLabel.length)
            filters = arrayOf(InputFilter.LengthFilter(10))
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 16f)
            hint = "Nome do botão"
        }
        val wrapper = android.widget.FrameLayout(this).apply {
            setPadding(dp(24), dp(12), dp(24), 0); addView(et)
        }
        AlertDialog.Builder(this)
            .setTitle("Renomear botão")
            .setMessage("Ação: ${ToolbarCatalog.describe(settings.toolbars[barIndex][btnIndex].action)}")
            .setView(wrapper)
            .setPositiveButton("Salvar") { _, _ ->
                val novo = et.text.toString().trim()
                if (novo.isNotEmpty()) { settings.renameButton(barIndex, btnIndex, novo); buildBars() }
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    private fun confirmRemove(barIndex: Int, btnIndex: Int, label: String) {
        AlertDialog.Builder(this)
            .setTitle("Remover botão")
            .setMessage("Remover \"$label\" da Barra de ferramentas ${barIndex + 1}?")
            .setPositiveButton("Remover") { _, _ ->
                settings.removeButton(barIndex, btnIndex); buildBars()
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Paleta de cores
    // ──────────────────────────────────────────────────────────────────────────

    private val palette = listOf(
        "" to "Padrão",
        "#FF2E5C6E" to "Petrol",
        "#FFC62828" to "Vermelho",
        "#FF2E7D32" to "Verde",
        "#FF1565C0" to "Azul",
        "#FFE65100" to "Laranja",
        "#FF6A1B9A" to "Roxo",
        "#FF00695C" to "Verde-água",
        "#FF424242" to "Cinza",
        "#FFF57F17" to "Amarelo",
        "#FFAD1457" to "Rosa",
        "#FF37474F" to "Azul-cinza",
        "#FF4E342E" to "Marrom",
    )

    private fun showColorDialog(barIndex: Int, btnIndex: Int, label: String) {
        val cols = 4
        val grid = android.widget.GridLayout(this).apply {
            columnCount = cols
            setPadding(dp(16), dp(12), dp(16), dp(4))
        }

        palette.forEachIndexed { idx, (hex, name) ->
            val bgColor = if (hex.isEmpty()) 0xFF2E5C6E.toInt() else parseColor(hex)
            val cell = android.widget.FrameLayout(this).apply {
                val size = dp(52)
                val m = dp(6)
                val spec = android.widget.GridLayout.spec(android.widget.GridLayout.UNDEFINED, 1f)
                layoutParams = android.widget.GridLayout.LayoutParams(spec, spec).also {
                    it.width = size; it.height = size
                    it.setMargins(m, m, m, m)
                }
            }
            val circle = android.view.View(this).apply {
                background = android.graphics.drawable.GradientDrawable().apply {
                    shape = android.graphics.drawable.GradientDrawable.OVAL
                    setColor(bgColor)
                    if (hex.isEmpty()) setStroke(dp(2), 0xFF888888.toInt())
                }
                layoutParams = android.widget.FrameLayout.LayoutParams(dp(44), dp(44)).also {
                    it.gravity = Gravity.CENTER
                }
            }
            val tick = TextView(this).apply {
                text = if (idx == 0 && settings.toolbars.getOrNull(barIndex)?.getOrNull(btnIndex)?.color.isNullOrEmpty()) "✓"
                       else if (hex == settings.toolbars.getOrNull(barIndex)?.getOrNull(btnIndex)?.color) "✓" else ""
                setTextColor(0xFFFFFFFF.toInt())
                setTextSize(TypedValue.COMPLEX_UNIT_SP, 18f)
                gravity = Gravity.CENTER
                layoutParams = android.widget.FrameLayout.LayoutParams(
                    android.widget.FrameLayout.LayoutParams.MATCH_PARENT,
                    android.widget.FrameLayout.LayoutParams.MATCH_PARENT)
            }
            cell.addView(circle)
            cell.addView(tick)
            cell.isClickable = true
            cell.isFocusable = true
            cell.background = selectableItemBg()
            cell.setOnClickListener {
                settings.recolorButton(barIndex, btnIndex, hex)
                buildBars()
                Toast.makeText(this, "Cor \"$name\" aplicada em \"$label\"", Toast.LENGTH_SHORT).show()
            }
            grid.addView(cell)
        }

        AlertDialog.Builder(this)
            .setTitle("Cor do botão \"$label\"")
            .setView(grid)
            .setNegativeButton("Fechar", null)
            .show()
    }

    private fun confirmDeleteBar(barIndex: Int) {
        AlertDialog.Builder(this)
            .setTitle("Excluir barra")
            .setMessage("Excluir a Barra de ferramentas ${barIndex + 1} e todos os seus botões?")
            .setPositiveButton("Excluir") { _, _ ->
                settings.removeBar(barIndex); buildBars()
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers visuais
    // ──────────────────────────────────────────────────────────────────────────

    private fun emptyHint(text: String): TextView {
        return TextView(this).apply {
            this.text = text
            setTextColor(0xFFAAAAAA.toInt())
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 14f)
            setPadding(dp(16), dp(6), dp(16), dp(6))
        }
    }

    private fun divider(): View {
        return View(this).apply {
            setBackgroundColor(0xFFEEEEEE.toInt())
            layoutParams = LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, dp(1)).also {
                it.setMargins(0, dp(4), 0, 0)
            }
        }
    }

    private fun selectableItemBg(): android.graphics.drawable.Drawable? {
        val ov = TypedValue()
        theme.resolveAttribute(android.R.attr.selectableItemBackgroundBorderless, ov, true)
        return getDrawable(ov.resourceId)
    }

    private fun themedSelectableBackground(): android.graphics.drawable.Drawable? {
        val ov = TypedValue()
        theme.resolveAttribute(android.R.attr.selectableItemBackground, ov, true)
        return getDrawable(ov.resourceId)
    }

    private fun chipBackground(bgColor: Int = 0xFF2E5C6E.toInt()): android.graphics.drawable.Drawable {
        return android.graphics.drawable.GradientDrawable().apply {
            cornerRadius = dp(6).toFloat()
            setColor(bgColor)
            setStroke(dp(1), darken(bgColor))
        }
    }

    private fun darken(color: Int): Int {
        val f = 0.75f
        val r = ((android.graphics.Color.red(color) * f).toInt()).coerceIn(0, 255)
        val g = ((android.graphics.Color.green(color) * f).toInt()).coerceIn(0, 255)
        val b = ((android.graphics.Color.blue(color) * f).toInt()).coerceIn(0, 255)
        return android.graphics.Color.argb(255, r, g, b)
    }

    private fun parseColor(hex: String): Int = try {
        android.graphics.Color.parseColor(hex)
    } catch (e: Exception) {
        0xFF2E5C6E.toInt()
    }

    private fun dp(v: Int): Int = (v * resources.displayMetrics.density).toInt()
}
