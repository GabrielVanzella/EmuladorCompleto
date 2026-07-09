package com.logisticapp.emuladortelnet

import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.Toolbar

/** Tela estática de ajuda sobre ativação (chave, nome de ativação e compra). */
class AjudaActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        com.logisticapp.emuladortelnet.settings.AppSettings.get(this).applyOrientation(this)
        setContentView(R.layout.activity_ajuda)

        val toolbar = findViewById<Toolbar>(R.id.toolbar)
        setSupportActionBar(toolbar)
        toolbar.setNavigationOnClickListener { finish() }
    }
}
