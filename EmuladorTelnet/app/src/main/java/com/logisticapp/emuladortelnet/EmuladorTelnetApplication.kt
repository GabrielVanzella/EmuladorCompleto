package com.logisticapp.emuladortelnet

import android.app.Application
import androidx.appcompat.app.AppCompatDelegate
import com.logisticapp.emuladortelnet.license.LicenseManager
import timber.log.Timber

class EmuladorTelnetApplication : Application() {

    override fun onCreate() {
        super.onCreate()

        // App foi desenhado só com tema claro (cores fixas em todas as telas/diálogos).
        // Forçar modo claro sempre, independente do tema do sistema, senão diálogos/popups
        // ficam escuros (fundo escuro + texto escuro hardcoded = ilegível).
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO)

        // Inicializar Timber para logging
        if (BuildConfig.DEBUG) {
            Timber.plant(Timber.DebugTree())
        }

        Timber.d("App inicializado - Sistema de Licença")

        // Inicializar dados de licença na primeira execução do app
        // (não concede mais trial automático - ativação é sempre por chave)
        try {
            val licenseManager = LicenseManager(applicationContext)
            licenseManager.initializeLicense()
            Timber.d("Dispositivo inicializado")
        } catch (e: Exception) {
            Timber.e(e, "Erro ao inicializar licença")
        }
    }
}
