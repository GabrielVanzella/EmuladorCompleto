package com.logisticapp.emuladortelnet

import android.os.Bundle
import android.text.InputFilter
import android.util.TypedValue
import android.view.Gravity
import android.view.ViewGroup
import android.widget.*
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.Toolbar
import com.logisticapp.emuladortelnet.settings.AppSettings
import com.logisticapp.emuladortelnet.toolbar.ToolbarButton
import com.logisticapp.emuladortelnet.toolbar.ToolbarCatalog

/**
 * Tela "Adicionar botões": lista os botões predefinidos + criador de botão personalizado.
 * Os selecionados são anexados à barra informada via EXTRA_BAR_INDEX.
 */
class ToolbarAddActivity : AppCompatActivity() {

    private lateinit var settings: AppSettings
    private var barIndex = 0
    private val checks = mutableListOf<Pair<ToolbarButton, CheckBox>>()

    companion object {
        const val EXTRA_BAR_INDEX = "bar_index"
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        settings = AppSettings.get(this)
        settings.applyOrientation(this)
        setContentView(R.layout.activity_toolbar_add)

        barIndex = intent.getIntExtra(EXTRA_BAR_INDEX, 0)

        val toolbar = findViewById<Toolbar>(R.id.toolbar)
        setSupportActionBar(toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        toolbar.subtitle = "Barra ${barIndex + 1} — selecione ou crie"
        toolbar.setNavigationOnClickListener { finish() }

        buildList()

        findViewById<Button>(R.id.btn_add_selected).setOnClickListener { addSelected() }
    }

    private fun buildList() {
        val container = findViewById<LinearLayout>(R.id.buttons_container)
        container.removeAllViews()

        // ---- Botão "Criar personalizado" no topo ----
        val createRow = LinearLayout(this).apply {
            orientation = LinearLayout.HORIZONTAL
            gravity = Gravity.CENTER_VERTICAL
            minimumHeight = dp(52)
            setPadding(dp(16), dp(6), dp(16), dp(6))
            isClickable = true
            background = selectableBg()
            layoutParams = LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT)
        }

        val plusIcon = TextView(this).apply {
            text = "＋"
            setTextColor(getColor(R.color.primary))
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 22f)
            gravity = Gravity.CENTER
            minWidth = dp(64)
        }
        val createLabel = TextView(this).apply {
            text = "Criar botão personalizado"
            setTextColor(getColor(R.color.primary))
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 14f)
            setPadding(dp(16), 0, 0, 0)
        }
        createRow.addView(plusIcon)
        createRow.addView(createLabel)
        createRow.setOnClickListener { showCustomButtonDialog() }
        container.addView(createRow)

        // Divisor
        container.addView(divider())

        // ---- Seções do catálogo (Navegação, Edição, Teclas F, Ctrl, Conexão) ----
        for ((titulo, botoes) in ToolbarCatalog.availableSections) {
            addSectionHeader(container, titulo)
            addButtons(container, botoes)
        }
    }

    private fun addSectionHeader(container: LinearLayout, title: String) {
        container.addView(TextView(this).apply {
            text = title
            setTextColor(0xFF888888.toInt())
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 12f)
            setPadding(dp(16), dp(14), dp(16), dp(4))
            layoutParams = LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT)
        })
    }

    private fun addButtons(container: LinearLayout, buttons: List<ToolbarButton>) {
        for (btn in buttons) {
            val row = LinearLayout(this).apply {
                orientation = LinearLayout.HORIZONTAL
                gravity = Gravity.CENTER_VERTICAL
                minimumHeight = dp(48)
                setPadding(dp(16), dp(4), dp(16), dp(4))
                isClickable = true
                background = selectableBg()
                layoutParams = LinearLayout.LayoutParams(
                    ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT)
            }
            val chip = TextView(this).apply {
                text = btn.label
                setTextColor(0xFFFFFFFF.toInt())
                setTextSize(TypedValue.COMPLEX_UNIT_SP, 13f)
                gravity = Gravity.CENTER
                minWidth = dp(56)
                setPadding(dp(10), dp(5), dp(10), dp(5))
                background = chipBg()
            }
            val desc = TextView(this).apply {
                text = ToolbarCatalog.describe(btn.action)
                setTextColor(0xFF666666.toInt())
                setTextSize(TypedValue.COMPLEX_UNIT_SP, 13f)
                setPadding(dp(14), 0, 0, 0)
                layoutParams = LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f)
            }
            val check = CheckBox(this).apply {
                isClickable = false
                isFocusable = false
                buttonTintList = android.content.res.ColorStateList.valueOf(getColor(R.color.primary))
            }
            row.addView(chip)
            row.addView(desc)
            row.addView(check)
            row.setOnClickListener { check.isChecked = !check.isChecked }
            container.addView(row)
            checks.add(btn to check)
        }
    }

    /** Diálogo para criar um botão personalizado. */
    private fun showCustomButtonDialog() {
        val layout = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(dp(24), dp(8), dp(24), dp(8))
        }

        val etLabel = EditText(this).apply {
            hint = "Rótulo (ex: OK, ↵, F10)"
            filters = arrayOf(InputFilter.LengthFilter(8))
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 16f)
        }

        val actionTypes = arrayOf("Texto livre", "Ctrl + letra", "Tecla F (F1-F12)", "Sequência de escape")
        var selectedType = 0

        val tvTypeLabel = TextView(this).apply {
            text = "Tipo de ação"
            setTextColor(0xFF666666.toInt())
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 12f)
            setPadding(0, dp(12), 0, dp(2))
        }
        val spinnerType = Spinner(this)
        spinnerType.adapter = ArrayAdapter(this, android.R.layout.simple_spinner_dropdown_item, actionTypes)
        spinnerType.setSelection(0)

        val etValue = EditText(this).apply {
            hint = "Texto a enviar"
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 16f)
        }

        spinnerType.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onNothingSelected(p: AdapterView<*>?) {}
            override fun onItemSelected(p: AdapterView<*>?, v: android.view.View?, pos: Int, id: Long) {
                selectedType = pos
                etValue.hint = when (pos) {
                    0 -> "Texto a enviar (ex: S, abc, 123)"
                    1 -> "Letra (ex: C para Ctrl+C)"
                    2 -> "Número (ex: 5 para F5)"
                    3 -> "Sequência (ex: \\e[H)"
                    else -> ""
                }
                etValue.inputType = if (pos == 2) android.text.InputType.TYPE_CLASS_NUMBER
                else android.text.InputType.TYPE_CLASS_TEXT
            }
        }

        layout.addView(etLabel)
        layout.addView(tvTypeLabel)
        layout.addView(spinnerType)
        layout.addView(TextView(this).apply {
            text = "Valor"
            setTextColor(0xFF666666.toInt())
            setTextSize(TypedValue.COMPLEX_UNIT_SP, 12f)
            setPadding(0, dp(12), 0, dp(2))
        })
        layout.addView(etValue)

        AlertDialog.Builder(this)
            .setTitle("Criar botão personalizado")
            .setView(layout)
            .setPositiveButton("Criar") { _, _ ->
                val label = etLabel.text.toString().trim()
                val value = etValue.text.toString().trim()
                if (label.isEmpty() || value.isEmpty()) {
                    Toast.makeText(this, "Preencha o rótulo e o valor", Toast.LENGTH_SHORT).show()
                    return@setPositiveButton
                }
                val action = when (selectedType) {
                    0 -> "TEXT:$value"
                    1 -> "CTRL_${value.first().uppercaseChar()}"
                    2 -> "F${value.toIntOrNull()?.coerceIn(1, 12) ?: 1}"
                    3 -> "TEXT:${value.replace("\\e", "").replace("\\n", "\n").replace("\\r", "\r")}"
                    else -> "TEXT:$value"
                }
                settings.addButtonToBar(barIndex, ToolbarButton(label, action))
                Toast.makeText(this, "Botão \"$label\" criado!", Toast.LENGTH_SHORT).show()
                finish()
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    private fun addSelected() {
        val selected = checks.filter { it.second.isChecked }.map { it.first }
        if (selected.isEmpty()) {
            Toast.makeText(this, "Nenhum botão selecionado", Toast.LENGTH_SHORT).show()
            return
        }
        for (btn in selected) settings.addButtonToBar(barIndex, btn)
        Toast.makeText(this, "${selected.size} botão(ões) adicionado(s)", Toast.LENGTH_SHORT).show()
        finish()
    }

    private fun divider(): android.view.View {
        return android.view.View(this).apply {
            setBackgroundColor(0xFFEEEEEE.toInt())
            layoutParams = LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, dp(1)).also {
                it.setMargins(0, dp(4), 0, dp(4))
            }
        }
    }

    private fun selectableBg(): android.graphics.drawable.Drawable? {
        val outValue = TypedValue()
        theme.resolveAttribute(android.R.attr.selectableItemBackground, outValue, true)
        return getDrawable(outValue.resourceId)
    }

    private fun chipBg(): android.graphics.drawable.Drawable {
        return android.graphics.drawable.GradientDrawable().apply {
            cornerRadius = dp(6).toFloat()
            setColor(0xFF2E5C6E.toInt())   // mesma cor dos botões no terminal
            setStroke(dp(1), 0xFF1B4A63.toInt())
        }
    }

    private fun dp(v: Int): Int = (v * resources.displayMetrics.density).toInt()
}
