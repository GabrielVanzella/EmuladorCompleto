package com.logisticapp.emuladortelnet

import android.content.Intent
import android.graphics.Color
import android.os.Build
import android.os.Bundle
import android.view.View
import android.widget.Button
import android.widget.EditText
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.Toolbar
import androidx.lifecycle.lifecycleScope
import com.logisticapp.emuladortelnet.license.LicenseApiService
import com.logisticapp.emuladortelnet.license.LicenseManager
import kotlinx.coroutines.launch
import timber.log.Timber

/**
 * Tela "Ativação": ativa o dispositivo por chave (trial ou vitalícia),
 * pedida por e-mail junto à ScanTE. Sem ViewModel — a validação é feita
 * direto contra o LicenseApiService, como em outras telas simples do app.
 */
class ActivationActivity : AppCompatActivity() {

    private lateinit var licenseManager: LicenseManager
    private lateinit var inputKey: EditText
    private lateinit var inputName: EditText
    private lateinit var btnActivate: Button
    private lateinit var progressBar: ProgressBar
    private lateinit var tvResult: TextView

    private lateinit var barcodeManager: BarcodeScannerManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        com.logisticapp.emuladortelnet.settings.AppSettings.get(this).applyOrientation(this)
        setContentView(R.layout.activity_activation)

        licenseManager = LicenseManager(this)

        val toolbar = findViewById<Toolbar>(R.id.toolbar)
        setSupportActionBar(toolbar)
        toolbar.setNavigationOnClickListener { finish() }

        inputKey = findViewById(R.id.input_activation_key)
        inputName = findViewById(R.id.input_activation_name)
        btnActivate = findViewById(R.id.btn_activate)
        progressBar = findViewById(R.id.progress_bar)
        tvResult = findViewById(R.id.tv_activation_result)

        // Sugere o modelo do dispositivo como nome de ativação (editável)
        inputName.setText(Build.MODEL)

        barcodeManager = BarcodeScannerManager(this) { barcode, _ ->
            runOnUiThread { inputKey.setText(barcode) }
        }

        btnActivate.setOnClickListener { activate() }

        findViewById<TextView>(R.id.btn_ajuda).setOnClickListener {
            startActivity(Intent(this, AjudaActivity::class.java))
        }
    }

    override fun onResume() {
        super.onResume()
        barcodeManager.register()
    }

    override fun onPause() {
        super.onPause()
        barcodeManager.unregister()
    }

    private fun activate() {
        val chave = inputKey.text.toString().trim()
        val nome = inputName.text.toString().trim()

        if (chave.isEmpty()) {
            showResult(false, "Digite a chave de ativação.")
            return
        }
        if (nome.isEmpty()) {
            showResult(false, "Insira um nome para identificar este dispositivo.")
            return
        }

        setLoading(true)
        lifecycleScope.launch {
            try {
                val deviceId = licenseManager.getDeviceId()
                val result = LicenseApiService().validarChave(chave, deviceId, nome)
                if (result.isSuccess) {
                    val validacao = result.getOrNull()!!
                    if (validacao.sucesso) {
                        licenseManager.upgradeToPremiumByKey(
                            chave = validacao.chave,
                            tipo = validacao.tipo,
                            diasRestantes = validacao.diasRestantes
                        )
                        // Já aplica a personalização da empresa (tema + teclas + logo)
                        com.logisticapp.emuladortelnet.settings.CompanyConfigStore
                            .save(applicationContext, validacao.configJson)
                        showResult(true, "Licença ativada com sucesso!")
                        startActivity(Intent(this@ActivationActivity, HostsActivity::class.java))
                        finishAffinity()
                    } else {
                        showResult(false, validacao.erro)
                    }
                } else {
                    val msg = result.exceptionOrNull()?.message ?: "Erro de conexão com o servidor."
                    showResult(false, "Não foi possível validar: $msg")
                }
            } catch (e: Exception) {
                Timber.e(e, "Erro ao ativar licença por chave")
                showResult(false, "Erro ao ativar licença.")
            } finally {
                setLoading(false)
            }
        }
    }

    private fun setLoading(loading: Boolean) {
        progressBar.visibility = if (loading) View.VISIBLE else View.GONE
        btnActivate.isEnabled = !loading
    }

    private fun showResult(sucesso: Boolean, mensagem: String) {
        tvResult.visibility = View.VISIBLE
        tvResult.text = mensagem
        tvResult.setTextColor(
            if (sucesso) Color.parseColor("#00A651") else Color.parseColor("#B3261E")
        )
    }
}
