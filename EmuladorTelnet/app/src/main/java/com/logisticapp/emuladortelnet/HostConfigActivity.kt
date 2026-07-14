package com.logisticapp.emuladortelnet

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.Toolbar
import androidx.lifecycle.Observer
import androidx.lifecycle.lifecycleScope
import com.logisticapp.emuladortelnet.data.ConnectionState
import com.logisticapp.emuladortelnet.database.SavedConnection
import com.logisticapp.emuladortelnet.database.TelnetRepository
import kotlinx.coroutines.launch
import timber.log.Timber

class HostConfigActivity : AppCompatActivity() {

    private lateinit var repository: TelnetRepository
    private var existingHost: SavedConnection? = null
    private var editAllMode = false

    private lateinit var inputName: EditText
    private lateinit var inputHost: EditText
    private lateinit var inputPort: EditText
    private lateinit var btnSave: Button
    private lateinit var btnConnect: Button

    companion object {
        const val EXTRA_PREFILL_NAME = "prefill_name"
        const val EXTRA_PREFILL_HOST = "prefill_host"
        const val EXTRA_PREFILL_PORT = "prefill_port"
        const val EXTRA_LOCK_HOST_PORT = "lock_host_port"
        const val EXTRA_EDIT_ALL = "edit_all"
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_host_config)

        repository = TelnetRepository.getInstance(this)

        val toolbar = findViewById<Toolbar>(R.id.toolbar)
        setSupportActionBar(toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        toolbar.setNavigationOnClickListener { finish() }

        inputName = findViewById(R.id.input_name)
        inputHost = findViewById(R.id.input_host)
        inputPort = findViewById(R.id.input_port)
        btnSave = findViewById(R.id.btn_save)
        btnConnect = findViewById(R.id.btn_connect)

        inputPort.setText("23")

        editAllMode = intent.getBooleanExtra(EXTRA_EDIT_ALL, false)
        if (editAllMode) {
            supportActionBar?.title = "Editar Conexão"
            loadFirstHostForEditAll()
        } else {
            supportActionBar?.title = "Novo Host"
            // Pre-fill from template (if launched from TemplatesActivity)
            intent.getStringExtra(EXTRA_PREFILL_NAME)?.let { inputName.setText(it) }
            intent.getStringExtra(EXTRA_PREFILL_HOST)?.let { inputHost.setText(it) }
            val prefillPort = intent.getIntExtra(EXTRA_PREFILL_PORT, -1)
            if (prefillPort > 0) inputPort.setText(prefillPort.toString())

            if (intent.getBooleanExtra(EXTRA_LOCK_HOST_PORT, false)) {
                lockHostAndPort()
            }
        }

        btnSave.setOnClickListener { saveHost() }
        btnConnect.setOnClickListener { saveAndConnect() }
    }

    /** Host/Porta vêm do host já existente e não podem ser editados — só o Nome fica livre. */
    private fun lockHostAndPort() {
        inputHost.isEnabled = false
        inputHost.isFocusable = false
        inputHost.alpha = 0.5f
        inputPort.isEnabled = false
        inputPort.isFocusable = false
        inputPort.alpha = 0.5f
    }

    private fun loadFirstHostForEditAll() {
        lifecycleScope.launch {
            val host = repository.currentConnections().firstOrNull()
            if (host != null) {
                existingHost = host
                inputName.setText(host.name)
                inputHost.setText(host.host)
                inputPort.setText(host.port.toString())
            } else {
                Toast.makeText(this@HostConfigActivity, "Nenhuma sessão cadastrada ainda", Toast.LENGTH_SHORT).show()
                finish()
            }
        }
    }

    private fun buildConnection(): SavedConnection? {
        val name = inputName.text.toString().trim()
        val host = inputHost.text.toString().trim()
        val portStr = inputPort.text.toString().trim()

        if (name.isEmpty()) { showError("Informe o nome do host"); return null }
        if (host.isEmpty()) { showError("Informe o IP ou hostname"); return null }
        if (portStr.isEmpty()) { showError("Informe a porta"); return null }
        val port = portStr.toIntOrNull() ?: run { showError("Porta invalida"); return null }

        return SavedConnection(
            id = existingHost?.id ?: 0,
            name = name,
            host = host,
            port = port,
            createdAt = existingHost?.createdAt ?: System.currentTimeMillis()
        )
    }

    private fun saveHost(then: ((SavedConnection) -> Unit)? = null) {
        val connection = buildConnection() ?: return
        lifecycleScope.launch {
            if (editAllMode) {
                repository.updateAllConnectionsIdentity(connection.name, connection.host, connection.port)
                existingHost = connection
                Timber.d("Nome/Host/Porta aplicados a todas as sessões: ${connection.name}")
            } else {
                val newId = repository.saveConnection(connection).toInt()
                existingHost = connection.copy(id = newId)
                Timber.d("Host salvo: ${connection.name} id=$newId")
            }
            if (then != null) {
                then.invoke(existingHost ?: connection)
            } else {
                Toast.makeText(this@HostConfigActivity, "Salvo com sucesso!", Toast.LENGTH_SHORT).show()
                finish()
            }
        }
    }

    private fun saveAndConnect() {
        saveHost { saved ->
            setConnectingUi(true)

            val jaAtiva = SessionStore.isActive(saved.id)
            val result = SessionStore.openOrResume(this, saved.id, saved.name, saved.host, saved.port)
            if (result == null) {
                setConnectingUi(false)
                Toast.makeText(this, "Máximo de 2 sessões ativas. Desconecte uma para abrir outra.", Toast.LENGTH_LONG).show()
                return@saveHost
            }
            val (slotId, vm) = result

            // Sessão já ativa/conectada: só retoma, sem validar de novo.
            if (jaAtiva || vm.connectionState.value == ConnectionState.CONNECTED) {
                goToMain(slotId)
                return@saveHost
            }

            vm.connect(saved.host, saved.port.toString())
            vm.connectionState.observe(this, object : Observer<ConnectionState> {
                override fun onChanged(state: ConnectionState) {
                    when (state) {
                        ConnectionState.CONNECTED -> {
                            vm.connectionState.removeObserver(this)
                            goToMain(slotId)
                        }
                        ConnectionState.ERROR -> {
                            vm.connectionState.removeObserver(this)
                            SessionStore.close(slotId)
                            setConnectingUi(false)
                            Toast.makeText(this@HostConfigActivity,
                                "Não foi possível conectar a ${saved.host}:${saved.port}", Toast.LENGTH_LONG).show()
                        }
                        else -> { /* CONNECTING / DISCONNECTED: aguarda */ }
                    }
                }
            })
        }
    }

    private fun goToMain(slotId: Int) {
        val intent = Intent(this, MainActivity::class.java).apply {
            putExtra(MainActivity.EXTRA_SLOT_ID, slotId)
        }
        startActivity(intent)
        finish()
    }

    private fun setConnectingUi(connecting: Boolean) {
        btnConnect.isEnabled = !connecting
        btnSave.isEnabled = !connecting
        btnConnect.text = if (connecting) "Conectando..." else "Conectar"
    }

    private fun showError(msg: String) {
        Toast.makeText(this, msg, Toast.LENGTH_SHORT).show()
    }
}
