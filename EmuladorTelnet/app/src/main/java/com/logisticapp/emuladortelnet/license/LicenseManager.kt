package com.logisticapp.emuladortelnet.license

import android.content.Context
import android.content.SharedPreferences
import android.os.Build
import android.provider.Settings
import java.security.MessageDigest
import java.text.SimpleDateFormat
import java.util.*
import timber.log.Timber

/**
 * Gerenciador de Licença com Integração Mercado Pago
 * Armazenamento: SharedPreferences (não usa Room Database para evitar problemas de compilação)
 * Sistema: Trial 30 dias → Premium vitalício
 */
class LicenseManager(private val context: Context) {

    private val prefs: SharedPreferences = context.getSharedPreferences(
        "com.logisticapp.emuladortelnet.license",
        Context.MODE_PRIVATE
    )

    companion object {
        private const val KEY_DEVICE_ID = "license_device_id"
        private const val KEY_LICENSE_KEY = "license_key"
        private const val KEY_LICENSE_TYPE = "license_type"
        private const val KEY_LICENSE_SUBTYPE = "license_subtype"   // "vitalicia" | "mensal" | etc
        private const val KEY_TRIAL_START_DATE = "trial_start_date"
        private const val KEY_TRIAL_END_DATE = "trial_end_date"
        private const val KEY_PURCHASE_DATE = "purchase_date"
        private const val KEY_IS_ACTIVE = "is_active"
        private const val KEY_MERCADO_PAGO_ORDER_ID = "mercado_pago_order_id"
        private const val KEY_MERCADO_PAGO_PAYMENT_ID = "mercado_pago_payment_id"
        private const val KEY_IS_INITIALIZED = "is_initialized"
    }

    /**
     * Obter Device ID
     */
    fun getDeviceId(): String {
        return try {
            Settings.Secure.getString(
                context.contentResolver,
                Settings.Secure.ANDROID_ID
            ) ?: "DEVICE_${UUID.randomUUID()}"
        } catch (e: Exception) {
            "DEVICE_${UUID.randomUUID()}"
        }
    }

    /**
     * Inicializar licença na primeira execução.
     * Não concede mais trial automático — a ativação agora é sempre por
     * chave (pedida por e-mail). Instalações antigas que já tinham um TRIAL
     * concedido continuam com ele intacto (ver hasAccess()).
     */
    fun initializeLicense() {
        if (prefs.getBoolean(KEY_IS_INITIALIZED, false)) {
            Timber.d("Licença já foi inicializada")
            return
        }

        prefs.edit().apply {
            putString(KEY_DEVICE_ID, getDeviceId())
            putBoolean(KEY_IS_INITIALIZED, true)
            apply()
        }

        Timber.d("Dispositivo inicializado - sem licença ativa")
    }

    /**
     * Verificar se trial é válido
     */
    fun isTrialValid(): Boolean {
        val licenseType = prefs.getString(KEY_LICENSE_TYPE, "TRIAL") ?: "TRIAL"

        if (licenseType == "PREMIUM") {
            return false
        }

        val trialEndDate = prefs.getLong(KEY_TRIAL_END_DATE, System.currentTimeMillis())
        val now = System.currentTimeMillis()
        return now < trialEndDate
    }

    /**
     * Obter dias restantes do trial
     */
    fun getTrialDaysRemaining(): Int {
        val licenseType = prefs.getString(KEY_LICENSE_TYPE, "TRIAL") ?: "TRIAL"

        if (licenseType == "PREMIUM") {
            return -1
        }

        val trialEndDate = prefs.getLong(KEY_TRIAL_END_DATE, System.currentTimeMillis())
        val now = System.currentTimeMillis()
        val daysRemaining = (trialEndDate - now) / (24 * 60 * 60 * 1000)

        return maxOf(0, daysRemaining.toInt())
    }

    /**
     * Verificar se tem acesso ao app
     */
    fun hasAccess(): Boolean {
        val licenseType = prefs.getString(KEY_LICENSE_TYPE, "") ?: ""
        val isActive = prefs.getBoolean(KEY_IS_ACTIVE, false)

        // Premium ativo (chave vitalícia ou com prazo ainda não vencido)
        if (licenseType == "PREMIUM" && isActive) {
            val expiryDate = prefs.getLong(KEY_TRIAL_END_DATE, 0L)
            val expired = expiryDate > 0L && System.currentTimeMillis() > expiryDate
            return !expired
        }

        // Trial válido (compatibilidade com instalações antigas que já
        // receberam um trial automático antes da remoção desse fluxo)
        if (licenseType == "TRIAL" && isTrialValid()) {
            return true
        }

        return false
    }

    /**
     * Gerar chave de licença vitalícia
     */
    fun generateLicenseKey(): String {
        val timestamp = System.currentTimeMillis()
        val deviceId = getDeviceId().take(12).uppercase()
        return "LIC-${timestamp}-${deviceId}"
    }

    /**
     * Upgrade para Premium após pagamento
     */
    fun upgradeToPremium(
        mercadoPagoOrderId: String,
        mercadoPagoPaymentId: String
    ) {
        val licenseKey = generateLicenseKey()

        prefs.edit().apply {
            putString(KEY_LICENSE_KEY, licenseKey)
            putString(KEY_LICENSE_TYPE, "PREMIUM")
            putLong(KEY_PURCHASE_DATE, System.currentTimeMillis())
            putString(KEY_MERCADO_PAGO_ORDER_ID, mercadoPagoOrderId)
            putString(KEY_MERCADO_PAGO_PAYMENT_ID, mercadoPagoPaymentId)
            putBoolean(KEY_IS_ACTIVE, true)
            apply()
        }

        Timber.d("Licença atualizada para PREMIUM - Ordem: $mercadoPagoOrderId")
    }

    /**
     * Ativa Premium via chave fornecida pelo servidor scante-admin.
     */
    fun upgradeToPremiumByKey(chave: String, tipo: String, diasRestantes: Int) {
        prefs.edit().apply {
            putString(KEY_LICENSE_KEY, chave)
            putString(KEY_LICENSE_TYPE, "PREMIUM")
            putString(KEY_LICENSE_SUBTYPE, tipo)
            putLong(KEY_PURCHASE_DATE, System.currentTimeMillis())
            putBoolean(KEY_IS_ACTIVE, true)
            if (diasRestantes > 0) {
                val expiry = System.currentTimeMillis() + (diasRestantes.toLong() * 24 * 60 * 60 * 1000)
                putLong(KEY_TRIAL_END_DATE, expiry)
            } else {
                putLong(KEY_TRIAL_END_DATE, 0L) // vitalicia: sem expiração
            }
            apply()
        }
        Timber.d("Licença PREMIUM ativada via chave $chave - tipo: $tipo - dias: $diasRestantes")
    }

    /**
     * Revoga a licença local (chamado quando o servidor retorna revogada/expirada).
     */
    fun revokeLicense() {
        prefs.edit().apply {
            putString(KEY_LICENSE_TYPE, "TRIAL")
            putBoolean(KEY_IS_ACTIVE, false)
            putLong(KEY_TRIAL_END_DATE, 0L)  // zera para isTrialValid() retornar false
            // Mantém KEY_LICENSE_KEY para poder re-verificar no servidor depois
            apply()
        }
        Timber.d("Licença revogada localmente por sincronização com servidor")
    }

    /**
     * Retorna a chave de licença salva localmente (pode ser null se nunca ativado via servidor).
     */
    fun getSavedLicenseKey(): String? = prefs.getString(KEY_LICENSE_KEY, null)?.takeIf { it.startsWith("SCTE-") }

    fun getLicenseSubtype(): String = prefs.getString(KEY_LICENSE_SUBTYPE, "vitalicia") ?: "vitalicia"

    /**
     * Atualizar ID do pedido Mercado Pago
     */
    fun setMercadoPagoOrderId(orderId: String) {
        prefs.edit().putString(KEY_MERCADO_PAGO_ORDER_ID, orderId).apply()
    }

    /**
     * Obter informações formatadas da licença
     */
    fun getLicenseInfo(): LicenseDisplayInfo {
        val licenseType = prefs.getString(KEY_LICENSE_TYPE, "TRIAL") ?: "TRIAL"
        val isActive = prefs.getBoolean(KEY_IS_ACTIVE, true)
        val trialStartDate = prefs.getLong(KEY_TRIAL_START_DATE, System.currentTimeMillis())
        val purchaseDate = prefs.getLong(KEY_PURCHASE_DATE, 0L)

        val subtype = prefs.getString(KEY_LICENSE_SUBTYPE, "vitalicia") ?: "vitalicia"
        val expiryDate = prefs.getLong(KEY_TRIAL_END_DATE, 0L)
        val premiumExpired = expiryDate > 0L && System.currentTimeMillis() > expiryDate

        return when {
            licenseType == "PREMIUM" && isActive && !premiumExpired && subtype == "vitalicia" -> {
                LicenseDisplayInfo(
                    status = "PREMIUM",
                    message = "Licença Vitalícia Ativa",
                    purchaseDate = formatDate(purchaseDate),
                    daysRemaining = -1,
                    isExpired = false
                )
            }
            licenseType == "PREMIUM" && isActive && !premiumExpired -> {
                val days = ((expiryDate - System.currentTimeMillis()) / 86400000).toInt().coerceAtLeast(0)
                LicenseDisplayInfo(
                    status = "PREMIUM",
                    message = "Licença Ativa — $days dias restantes (expira ${formatDate(expiryDate)})",
                    purchaseDate = formatDate(purchaseDate),
                    daysRemaining = days,
                    isExpired = false
                )
            }
            licenseType == "TRIAL" && isTrialValid() -> {
                val days = getTrialDaysRemaining()
                LicenseDisplayInfo(
                    status = "TRIAL",
                    message = "Teste Gratuito - $days dias restantes",
                    purchaseDate = formatDate(trialStartDate),
                    daysRemaining = days,
                    isExpired = false
                )
            }
            else -> {
                LicenseDisplayInfo(
                    status = "EXPIRED",
                    message = "Trial expirado - Compre agora",
                    purchaseDate = formatDate(trialStartDate),
                    daysRemaining = 0,
                    isExpired = true
                )
            }
        }
    }

    // ----------------------------------------------------------------
    // Utilitários de DEBUG — usar apenas em builds de desenvolvimento
    // ----------------------------------------------------------------

    /** Expira o trial imediatamente (define end_date = 1 ms atrás). */
    fun debugExpireTrial() {
        prefs.edit().apply {
            putString(KEY_LICENSE_TYPE, "TRIAL")
            putLong(KEY_TRIAL_END_DATE, System.currentTimeMillis() - 1)
            putBoolean(KEY_IS_ACTIVE, true)
            apply()
        }
    }

    /** Libera o app localmente (PREMIUM vitalício), sem passar pelo servidor. Só para testes. */
    fun debugUnlock() {
        prefs.edit().apply {
            putString(KEY_LICENSE_KEY, "DEBUG-UNLOCK")
            putString(KEY_LICENSE_TYPE, "PREMIUM")
            putString(KEY_LICENSE_SUBTYPE, "vitalicia")
            putBoolean(KEY_IS_ACTIVE, true)
            putBoolean(KEY_IS_INITIALIZED, true)
            putLong(KEY_TRIAL_END_DATE, 0L) // vitalicia: sem expiracao
            apply()
        }
    }

    /** Reseta o trial para N dias a partir de agora. */
    fun debugSetTrialDays(days: Int) {
        val ms = days.toLong() * 24 * 60 * 60 * 1000
        prefs.edit().apply {
            putString(KEY_LICENSE_TYPE, "TRIAL")
            putLong(KEY_TRIAL_START_DATE, System.currentTimeMillis())
            putLong(KEY_TRIAL_END_DATE, System.currentTimeMillis() + ms)
            putBoolean(KEY_IS_ACTIVE, true)
            putBoolean(KEY_IS_INITIALIZED, true)
            // Remove licença premium se houver
            remove(KEY_LICENSE_KEY)
            remove(KEY_LICENSE_SUBTYPE)
            remove(KEY_MERCADO_PAGO_PAYMENT_ID)
            apply()
        }
    }

    /** Apaga tudo — próxima abertura reinicia como primeira instalação. */
    fun debugClearAll() {
        prefs.edit().clear().apply()
    }

    /**
     * Formatar timestamp para data legível
     */
    private fun formatDate(timestamp: Long): String {
        return try {
            val date = Date(timestamp)
            val format = SimpleDateFormat("dd/MM/yyyy", Locale("pt", "BR"))
            format.format(date)
        } catch (e: Exception) {
            "Data inválida"
        }
    }

    /**
     * Obter informações do device
     */
    fun getDeviceInfo(): String {
        return """
            Device: ${Build.MODEL}
            Manufacturer: ${Build.MANUFACTURER}
            Android: ${Build.VERSION.RELEASE} (API ${Build.VERSION.SDK_INT})
            Device ID: ${getDeviceId()}
        """.trimIndent()
    }

    /**
     * Data class para exibição de informações da licença
     */
    data class LicenseDisplayInfo(
        val status: String,
        val message: String,
        val purchaseDate: String,
        val daysRemaining: Int,
        val isExpired: Boolean
    )
}
