package com.logisticapp.emuladortelnet

import android.os.Bundle
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.Toolbar
import androidx.lifecycle.lifecycleScope
import com.logisticapp.emuladortelnet.database.SavedConnection
import com.logisticapp.emuladortelnet.database.TelnetRepository
import com.logisticapp.emuladortelnet.settings.AppSettings
import kotlinx.coroutines.launch
import timber.log.Timber

class HostAdvancedActivity : AppCompatActivity() {

    private lateinit var repository: TelnetRepository
    private lateinit var settings: AppSettings
    private var hostId: Int = -1
    private var currentHost: SavedConnection? = null

    private lateinit var inputUsername: EditText
    private lateinit var inputPassword: EditText
    private lateinit var spinnerTerminal: Spinner
    private lateinit var spinnerEncoding: Spinner
    private lateinit var inputTimeout: EditText
    private lateinit var switchKeepalive: Switch
    private lateinit var switchLocalecho: Switch
    private lateinit var inputKey1Label: EditText
    private lateinit var inputKey1Value: EditText
    private lateinit var inputKey2Label: EditText
    private lateinit var inputKey2Value: EditText
    private lateinit var inputKey3Label: EditText
    private lateinit var inputKey3Value: EditText
    private lateinit var btnSave: Button

    private val terminalTypes = listOf("VT100", "VT220", "ANSI", "XTERM")
    private val encodings = listOf("UTF-8", "ISO-8859-1", "CP850")

    companion object {
        const val EXTRA_HOST_ID = "host_id"
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_host_advanced)

        repository = TelnetRepository.getInstance(this)
        settings = AppSettings.get(this)
        hostId = intent.getIntExtra(EXTRA_HOST_ID, -1)

        val toolbar = findViewById<Toolbar>(R.id.toolbar)
        setSupportActionBar(toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        toolbar.setNavigationOnClickListener { finish() }

        inputUsername   = findViewById(R.id.input_username)
        inputPassword   = findViewById(R.id.input_password)
        spinnerTerminal = findViewById(R.id.spinner_terminal)
        spinnerEncoding = findViewById(R.id.spinner_encoding)
        inputTimeout    = findViewById(R.id.input_timeout)
        switchKeepalive = findViewById(R.id.switch_keepalive)
        switchLocalecho = findViewById(R.id.switch_localecho)
        inputKey1Label  = findViewById(R.id.input_key1_label)
        inputKey1Value  = findViewById(R.id.input_key1_value)
        inputKey2Label  = findViewById(R.id.input_key2_label)
        inputKey2Value  = findViewById(R.id.input_key2_value)
        inputKey3Label  = findViewById(R.id.input_key3_label)
        inputKey3Value  = findViewById(R.id.input_key3_value)
        btnSave         = findViewById(R.id.btn_save_advanced)

        setupSpinners()
        loadGlobalValues()

        if (hostId > 0) loadHost(hostId)

        btnSave.setOnClickListener { saveAdvanced() }
    }

    private fun setupSpinners() {
        val termAdapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, terminalTypes)
        termAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinnerTerminal.adapter = termAdapter

        val encAdapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, encodings)
        encAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinnerEncoding.adapter = encAdapter
    }

    /**
     * Tipo de Terminal e Keep-Alive são os mesmos valores da Configuração geral
     * (Configurações > Comunicação > Telnet Opções) — editar aqui, na seta da sessão,
     * ou lá, tem o mesmo efeito.
     */
    private fun loadGlobalValues() {
        val opts = settings.telnetOptions
        val termIdx = terminalTypes.indexOfFirst { it.equals(opts.terminalType, ignoreCase = true) }
            .takeIf { it >= 0 } ?: 1 // default VT220
        spinnerTerminal.setSelection(termIdx)
        switchKeepalive.isChecked = !opts.keepAliveType.equals("Desligado", ignoreCase = true)
    }

    private fun loadHost(id: Int) {
        lifecycleScope.launch {
            val host = repository.getConnectionById(id)
            if (host != null) {
                currentHost = host
                inputUsername.setText(host.username)
                inputPassword.setText(host.password)
                inputTimeout.setText(host.timeoutSeconds.toString())
                switchLocalecho.isChecked = host.localEcho
                inputKey1Label.setText(host.customKey1Label)
                inputKey1Value.setText(host.customKey1Value)
                inputKey2Label.setText(host.customKey2Label)
                inputKey2Value.setText(host.customKey2Value)
                inputKey3Label.setText(host.customKey3Label)
                inputKey3Value.setText(host.customKey3Value)

                val encIdx = encodings.indexOf(host.encoding).takeIf { it >= 0 } ?: 0
                spinnerEncoding.setSelection(encIdx)
            }
        }
    }

    private fun saveAdvanced() {
        val base = currentHost ?: run {
            Toast.makeText(this, "Host nao encontrado", Toast.LENGTH_SHORT).show()
            return
        }

        val timeout = inputTimeout.text.toString().toIntOrNull() ?: 30

        val updated = base.copy(
            username        = inputUsername.text.toString().trim(),
            password        = inputPassword.text.toString(),
            encoding        = encodings[spinnerEncoding.selectedItemPosition],
            timeoutSeconds  = timeout,
            localEcho       = switchLocalecho.isChecked,
            customKey1Label = inputKey1Label.text.toString().ifBlank { "F1" },
            customKey1Value = inputKey1Value.text.toString(),
            customKey2Label = inputKey2Label.text.toString().ifBlank { "F2" },
            customKey2Value = inputKey2Value.text.toString(),
            customKey3Label = inputKey3Label.text.toString().ifBlank { "F3" },
            customKey3Value = inputKey3Value.text.toString()
        )

        // Tipo de Terminal e Keep-Alive são compartilhados com a Configuração geral
        val opts = settings.telnetOptions
        settings.telnetOptions = opts.copy(
            terminalType = terminalTypes[spinnerTerminal.selectedItemPosition],
            keepAliveType = if (switchKeepalive.isChecked)
                opts.keepAliveType.takeIf { !it.equals("Desligado", ignoreCase = true) } ?: "TCP"
            else "Desligado"
        )

        lifecycleScope.launch {
            repository.updateConnection(updated)
            Timber.d("Config avancada salva: ${updated.name}")
            Toast.makeText(this@HostAdvancedActivity, "Configuracoes salvas!", Toast.LENGTH_SHORT).show()
            finish()
        }
    }
}
