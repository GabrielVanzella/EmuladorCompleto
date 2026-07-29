package com.logisticapp.emuladortelnet.ui

import android.app.Application
import android.os.Build
import androidx.lifecycle.AndroidViewModel
import com.logisticapp.emuladortelnet.BuildConfig
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.viewModelScope
import com.logisticapp.emuladortelnet.license.LicenseApiService
import com.logisticapp.emuladortelnet.license.LicenseManager
import com.logisticapp.emuladortelnet.license.MercadoPagoManager
import kotlinx.coroutines.NonCancellable
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import timber.log.Timber

/**
 * ViewModel da tela de gate (LicenseActivity). Só cuida do que essa tela
 * precisa: sincronizar com o servidor em segundo plano e processar a
 * ativação por chave que pode chegar via deep link de retorno de pagamento.
 * A ativação manual por chave (tela "Ativação") não passa por aqui.
 */
class LicenseViewModel(application: Application) : AndroidViewModel(application) {

    private val licenseManager = LicenseManager(application)
    private val mpManager = MercadoPagoManager()
    private val apiService = LicenseApiService()

    private val _isLoading = MutableLiveData(false)
    val isLoading: LiveData<Boolean> = _isLoading

    // Disparado quando uma ativação por deep link é confirmada -> a Activity navega para HostsActivity
    private val _licenseActivated = MutableLiveData(false)
    val licenseActivated: LiveData<Boolean> = _licenseActivated

    // Mensagem de erro para exibir Toast (consumida uma vez)
    private val _errorMessage = MutableLiveData<String?>(null)
    val errorMessage: LiveData<String?> = _errorMessage

    init {
        licenseManager.initializeLicense()
        syncWithServer()
        pingServidor()
    }

    /**
     * Re-valida a licença salva com o servidor.
     * Se foi revogada/expirada no admin, atualiza o estado local.
     * Se o servidor não responder, mantém o estado local (modo offline).
     */
    private fun syncWithServer() {
        val savedKey = licenseManager.getSavedLicenseKey() ?: return
        viewModelScope.launch {
            try {
                val deviceId = licenseManager.getDeviceId()
                val deviceNome = "${Build.MANUFACTURER} ${Build.MODEL}"
                val result = apiService.validarChave(savedKey, deviceId, deviceNome)
                if (result.isSuccess) {
                    val validacao = result.getOrNull()!!
                    if (validacao.sucesso) {
                        licenseManager.upgradeToPremiumByKey(
                            chave = validacao.chave,
                            tipo = validacao.tipo,
                            diasRestantes = validacao.diasRestantes
                        )
                        // Aplica/atualiza a personalização da empresa (tema + teclas + logo)
                        com.logisticapp.emuladortelnet.settings.CompanyConfigStore
                            .save(getApplication(), validacao.configJson)
                    } else {
                        licenseManager.revokeLicense()
                        Timber.d("Licença inválida no servidor: ${validacao.erro}")
                    }
                }
            } catch (e: Exception) {
                Timber.w(e, "Sem conexão com servidor — usando licença local")
            }
        }
    }

    private fun pingServidor() {
        val deviceId   = licenseManager.getDeviceId()
        val deviceNome = "${Build.MANUFACTURER} ${Build.MODEL}"
        val appVersion = BuildConfig.VERSION_NAME
        val licenseKey = licenseManager.getSavedLicenseKey()
        viewModelScope.launch {
            // NonCancellable garante que o ping termina mesmo se a Activity for destruída
            withContext(NonCancellable) {
                try {
                    apiService.pingServidor(deviceId, deviceNome, appVersion, licenseKey)
                    Timber.d("Ping enviado com sucesso")
                } catch (e: Exception) {
                    Timber.w(e, "Ping ao servidor falhou: ${e.message}")
                }
            }
        }
    }

    /**
     * Chamado quando o deep link de sucesso retorna um payment_id.
     * Verifica o status real no MP antes de ativar.
     */
    fun verifyAndActivateLicense(paymentId: String) {
        viewModelScope.launch {
            _isLoading.value = true
            try {
                val result = mpManager.getPaymentStatus(paymentId)
                if (result.isSuccess) {
                    val info = result.getOrNull()!!
                    if (info.status == "approved") {
                        licenseManager.upgradeToPremium(info.orderId, info.id)
                        _licenseActivated.value = true
                        Timber.d("Licença PREMIUM ativada via pagamento $paymentId")
                    } else {
                        _errorMessage.value = "Pagamento com status: ${info.status}. Aguarde a aprovação."
                    }
                } else {
                    _errorMessage.value = result.exceptionOrNull()?.message
                        ?: "Não foi possível verificar o pagamento."
                }
            } catch (e: Exception) {
                Timber.e(e, "Erro ao verificar pagamento")
                _errorMessage.value = "Erro ao verificar pagamento."
            } finally {
                _isLoading.value = false
            }
        }
    }

    /**
     * Fallback: MP redireciona com status=approved mas sem payment_id.
     * Ativa a licença diretamente pelo status do redirect.
     */
    fun activateLicenseByStatus(status: String) {
        if (status == "approved") {
            licenseManager.upgradeToPremium("mp_redirect", "mp_redirect")
            _licenseActivated.value = true
            Timber.d("Licença PREMIUM ativada via redirect status=approved")
        } else {
            _errorMessage.value = "Pagamento com status: $status."
        }
    }

    /**
     * Ativa a licença via chave do scante-admin (chegando por deep link).
     * Chama POST /api/licenca/validar com a chave, device_id e device_nome.
     */
    fun activateByKey(chave: String) {
        if (chave.isBlank()) return
        viewModelScope.launch {
            _isLoading.value = true
            try {
                val deviceId = licenseManager.getDeviceId()
                val deviceNome = "${Build.MANUFACTURER} ${Build.MODEL}"
                val result = apiService.validarChave(chave, deviceId, deviceNome)
                if (result.isSuccess) {
                    val validacao = result.getOrNull()!!
                    if (validacao.sucesso) {
                        licenseManager.upgradeToPremiumByKey(
                            chave = validacao.chave,
                            tipo = validacao.tipo,
                            diasRestantes = validacao.diasRestantes
                        )
                        _licenseActivated.value = true
                    } else {
                        _errorMessage.value = validacao.erro
                    }
                } else {
                    val msg = result.exceptionOrNull()?.message ?: "Erro de conexão com o servidor."
                    _errorMessage.value = "Não foi possível validar: $msg"
                }
            } catch (e: Exception) {
                Timber.e(e, "Erro ao ativar licença por chave")
                _errorMessage.value = "Erro ao ativar licença."
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun onErrorShown() {
        _errorMessage.value = null
    }
}
