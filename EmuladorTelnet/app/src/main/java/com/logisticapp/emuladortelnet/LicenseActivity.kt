package com.logisticapp.emuladortelnet

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.widget.Button
import android.widget.LinearLayout
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.ViewModelProvider
import com.logisticapp.emuladortelnet.license.LicenseManager
import com.logisticapp.emuladortelnet.ui.LicenseViewModel
import timber.log.Timber

/**
 * Gate de licença. Se o dispositivo já tem acesso, vai direto para
 * HostsActivity. Caso contrário, mostra o aviso "ScanTE não está ativado"
 * com atalho para a tela de Ativação.
 */
class LicenseActivity : AppCompatActivity() {

    private lateinit var viewModel: LicenseViewModel
    private lateinit var licenseManager: LicenseManager
    private var navigatingToHosts = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        com.logisticapp.emuladortelnet.settings.AppSettings.get(this).applyOrientation(this)

        licenseManager = LicenseManager(this)
        if (licenseManager.hasAccess()) {
            goToHosts()
            return
        }

        setContentView(R.layout.activity_license)

        viewModel = ViewModelProvider(this).get(LicenseViewModel::class.java)
        observeViewModel()
        setupClickListeners()

        // Chegou aqui via deep link (pagamento concluído)?
        handleDeepLink(intent)
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        // Recebe o deep link quando a activity já estava aberta (singleTop)
        handleDeepLink(intent)
    }

    private fun goToHosts() {
        if (navigatingToHosts) return
        navigatingToHosts = true
        startActivity(Intent(this, HostsActivity::class.java))
        finish()
    }

    private fun handleDeepLink(intent: Intent) {
        val data: Uri? = intent.data
        if (data == null || data.scheme != "emuladortelnet") return

        Timber.d("Deep link recebido: $data")

        when (data.host) {
            "payment" -> {
                val path      = data.pathSegments.firstOrNull()
                val chave     = data.getQueryParameter("chave")       // retorno do scante-admin
                val paymentId = data.getQueryParameter("payment_id")  // retorno do Mercado Pago
                val status    = data.getQueryParameter("status")

                Timber.d("Path=$path chave=$chave paymentId=$paymentId status=$status")

                when (path) {
                    "sucesso",   // rota do scante-admin
                    "success" -> {
                        when {
                            !chave.isNullOrEmpty()     -> viewModel.activateByKey(chave)
                            !paymentId.isNullOrEmpty() -> viewModel.verifyAndActivateLicense(paymentId)
                            else                       -> viewModel.activateLicenseByStatus(status ?: "approved")
                        }
                    }
                    "failure" -> {
                        Toast.makeText(this, "Pagamento não aprovado. Tente novamente.", Toast.LENGTH_LONG).show()
                    }
                    "pending" -> {
                        Toast.makeText(this, "Pagamento pendente. Aguarde a confirmação.", Toast.LENGTH_LONG).show()
                    }
                }
            }
        }
    }

    private fun observeViewModel() {
        viewModel.licenseActivated.observe(this) { activated ->
            if (activated) goToHosts()
        }

        viewModel.errorMessage.observe(this) { msg ->
            if (!msg.isNullOrEmpty()) {
                Toast.makeText(this, msg, Toast.LENGTH_LONG).show()
                viewModel.onErrorShown()
            }
        }
    }

    private fun setupClickListeners() {
        findViewById<TextView>(R.id.btn_activate_now).setOnClickListener {
            startActivity(Intent(this, ActivationActivity::class.java))
        }

        // Painel de debug — só aparece em builds DEBUG
        if (BuildConfig.DEBUG) {
            val debugPanel = findViewById<LinearLayout>(R.id.debug_panel)
            debugPanel.visibility = android.view.View.VISIBLE

            findViewById<Button>(R.id.btn_debug_unlock).setOnClickListener {
                licenseManager.debugUnlock()
                Toast.makeText(this, "Liberado (bypass de teste)", Toast.LENGTH_SHORT).show()
                goToHosts()
            }
        }
    }

    override fun onResume() {
        super.onResume()
        // Se a licença foi ativada em outra tela (ex: Ativação) enquanto essa
        // ficou em segundo plano, reavalia o gate ao voltar pra ela.
        if (::licenseManager.isInitialized && licenseManager.hasAccess()) {
            goToHosts()
        }
    }
}
