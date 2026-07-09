package com.logisticapp.emuladortelnet

import android.app.Application
import com.logisticapp.emuladortelnet.license.LicenseManager
import timber.log.Timber

class EmuladorTelnetApplication : Application() {

    override fun onCreate() {
        super.onCreate()

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
