package com.logisticapp.emuladortelnet

import android.graphics.Color
import android.graphics.Typeface
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.text.Html
import android.view.Menu
import android.view.MenuItem
import android.view.View
import android.view.WindowManager
import android.view.inputmethod.InputMethodManager
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.lifecycleScope
import com.logisticapp.emuladortelnet.data.ConnectionState
import com.logisticapp.emuladortelnet.database.SavedConnection
import com.logisticapp.emuladortelnet.database.TelnetRepository
import com.logisticapp.emuladortelnet.databinding.ActivityMainBinding
import com.logisticapp.emuladortelnet.settings.AppSettings
import com.logisticapp.emuladortelnet.ui.TelnetViewModel
import com.logisticapp.emuladortelnet.ui.TelnetViewModelFactory
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.MainScope
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import timber.log.Timber

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var viewModel: TelnetViewModel
    private lateinit var repository: TelnetRepository
    private lateinit var settings: AppSettings
    private var hasConnected = false
    private var manualDisconnect = false
    private var slotId = -1

    // Dados da conexao atual (para reconexao automatica)
    private var currentHost = ""
    private var currentPort = 23
    private var currentName = ""

    // Receiver para detectar bloqueio de tela
    private var screenOffReceiver: BroadcastReceiver? = null

    // Gerenciador de leitura de código de barras
    private lateinit var barcodeManager: BarcodeScannerManager

    // Cursor piscando
    private val cursorBlinkHandler = Handler(Looper.getMainLooper())
    private val cursorBlinkRunnable = object : Runnable {
        override fun run() {
            viewModel.toggleCursor()
            cursorBlinkHandler.postDelayed(this, 500)
        }
    }

    // Detecção de duplo toque
    private var lastTapTime = 0L

    // Barra de teclas personalizadas oculta manualmente pelo usuário (botão no topo)
    private var toolbarHidden = false

    // Redimensionar o terminal com pinça (dois dedos)
    private lateinit var scaleDetector: android.view.ScaleGestureDetector
    private var scaling = false

    companion object {
        const val EXTRA_HOST    = "extra_host"
        const val EXTRA_PORT    = "extra_port"
        const val EXTRA_NAME    = "extra_name"
        const val EXTRA_HOST_ID = "extra_host_id"
        const val EXTRA_SLOT_ID = "extra_slot_id"

        // Faixa de tamanho de fonte do terminal (sp)
        private const val MIN_FONT_SP = 6f
        private const val MAX_FONT_SP = 72f
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        settings = AppSettings.get(this)
        settings.applyOrientation(this)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Nunca bloquear a tela quando conectado
        if (settings.keepScreenOn) {
            window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
        }

        // Garante que o controlKeysBar fique colado acima do teclado (Android 11+)
        ViewCompat.setOnApplyWindowInsetsListener(binding.root) { view, insets ->
            val imeInsets = insets.getInsets(WindowInsetsCompat.Type.ime())
            val navInsets = insets.getInsets(WindowInsetsCompat.Type.navigationBars())
            // Padding bottom = altura do teclado (quando aberto) ou barra de navegação
            view.setPadding(0, 0, 0, maxOf(imeInsets.bottom, navInsets.bottom))
            insets
        }

        // Tamanho e fonte do terminal (Opções de tela)
        binding.terminalOutput.textSize = settings.fontSize.toFloat()
        binding.terminalOutput.typeface = fontFromName(settings.fontName)
        setupPinchZoom()

        // Limitar visualização
        val limitParts = settings.limitView.split(",")
        val limitLines = limitParts.getOrNull(1)?.trim()?.toIntOrNull()
        if (limitLines != null) binding.terminalOutput.maxLines = limitLines
        else binding.terminalOutput.maxLines = Int.MAX_VALUE

        // Cores da tela (a personalização da empresa, quando ativa, sobrepõe)
        binding.terminalOutput.setBackgroundColor(effBg())
        binding.scrollView.setBackgroundColor(effBg())
        binding.statusText.setTextColor(effStatusFg())
        if (effStatusBg() != 0) {
            binding.appBar.setBackgroundColor(effStatusBg())
        }
        aplicarCabecalhoEmpresa()

        repository = TelnetRepository.getInstance(this)

        // Determina a origem da sessão: SessionStore (multi-sessão) ou intent direto
        slotId = intent.getIntExtra(EXTRA_SLOT_ID, -1)
        val slot = if (slotId >= 0) SessionStore.get(slotId) else null

        if (slot != null) {
            currentHost = slot.host
            currentPort = slot.port
            currentName = slot.hostName
            viewModel = slot.viewModel
        } else {
            currentHost = intent.getStringExtra(EXTRA_HOST) ?: ""
            currentPort = intent.getIntExtra(EXTRA_PORT, 23)
            currentName = intent.getStringExtra(EXTRA_NAME) ?: currentHost
            val factory = TelnetViewModelFactory(repository)
            viewModel = ViewModelProvider(this, factory).get(TelnetViewModel::class.java)
        }

        barcodeManager = BarcodeScannerManager(this) { barcode, action ->
            sendBarcodeToServer(barcode, action)
        }
        barcodeManager.updateOptions(settings.barcodeOptions)

        applyViewModelSettings()
        setupListeners()
        observeViewModel()
        registerScreenOffReceiver()

        if (currentName.isNotEmpty()) binding.statusText.text = currentName

        if (slot != null) {
            if (viewModel.connectionState.value == ConnectionState.CONNECTED) hasConnected = true
            if (viewModel.connectionState.value == ConnectionState.DISCONNECTED && currentHost.isNotEmpty()) {
                viewModel.connect(currentHost, currentPort.toString())
                Timber.d("Conectando (slot $slotId): $currentName -> $currentHost:$currentPort")
            } else {
                Timber.d("Retomando sessão (slot $slotId): $currentName [${viewModel.connectionState.value}]")
            }
        } else if (currentHost.isNotEmpty()) {
            viewModel.connect(currentHost, currentPort.toString())
            Timber.d("Auto-conectando: $currentName -> $currentHost:$currentPort")
        }
    }

    private fun registerScreenOffReceiver() {
        screenOffReceiver = object : BroadcastReceiver() {
            override fun onReceive(context: Context?, intent: Intent?) {
                if (intent?.action == Intent.ACTION_SCREEN_OFF && settings.disconnectOnLock) {
                    Timber.d("Tela bloqueada -> desconectando")
                    manualDisconnect = true
                    viewModel.disconnect()
                }
            }
        }
        registerReceiver(screenOffReceiver, IntentFilter(Intent.ACTION_SCREEN_OFF))
    }

    override fun onResume() {
        super.onResume()
        if (slotId >= 0) binding.btnBackSessions.visibility = View.VISIBLE
        updateSessionBadge()
        // Re-aplica configurações que podem ter sido alteradas em telas de configuração
        applyViewModelSettings()
        viewModel.refreshDisplay()
    }

    override fun onDestroy() {
        super.onDestroy()
        screenOffReceiver?.let { runCatching { unregisterReceiver(it) } }
        cursorBlinkHandler.removeCallbacks(cursorBlinkRunnable)
        barcodeManager.unregister()
    }

    private fun updateSessionBadge() {
        if (slotId >= 0 && SessionStore.activeCount() > 1) {
            binding.tvSessionBadge.text = "⇄"
            binding.tvSessionBadge.visibility = View.VISIBLE
        } else {
            binding.tvSessionBadge.visibility = View.GONE
        }
    }

    private fun applyViewModelSettings() {
        viewModel.setForegroundColor(effFg())
        viewModel.setFieldColor(effField())
        viewModel.setTerminalType(settings.telnetOptions.terminalType)
        viewModel.setBinaryMode(settings.telnetOptions.binaryMode)
        viewModel.setSimulateParity(settings.telnetOptions.simulateParity)
        viewModel.setKeepAlive(
            settings.telnetOptions.keepAliveType,
            settings.telnetOptions.keepAliveInterval.toIntOrNull() ?: 0
        )
        viewModel.setAutoLogin(
            settings.telnetOptions.waitLoginPrompt,
            settings.telnetOptions.loginWith,
            settings.telnetOptions.waitPasswordPrompt,
            settings.telnetOptions.password,
            settings.telnetOptions.waitCommandPrompt,
            settings.telnetOptions.doCommand
        )
        val sslOpts = settings.telnetOptions
        val certBytes: ByteArray? = if (sslOpts.useSsl && sslOpts.clientCertFile.isNotBlank()) {
            try { java.io.File(sslOpts.clientCertFile).readBytes() } catch (e: Exception) { null }
        } else null
        viewModel.setSsl(sslOpts.useSsl, certBytes, sslOpts.clientCertPassword)
        val keyBytes: ByteArray? = if (sslOpts.useSsh && sslOpts.sshPrivateKey.isNotBlank()) {
            try { java.io.File(sslOpts.sshPrivateKey).readBytes() } catch (e: Exception) { null }
        } else null
        val sshHostStr = sslOpts.sshServer.ifBlank { currentHost }
        val sshPortParsed = sslOpts.sshServer.substringAfter(":", "22").toIntOrNull() ?: currentPort
        val sshHostParsed = sslOpts.sshServer.substringBefore(":").ifBlank { sshHostStr }
        viewModel.setSshConfig(
            sslOpts.useSsh, sshHostParsed, sshPortParsed,
            sslOpts.sshUsername, sslOpts.sshPassword, keyBytes,
            sslOpts.sshKeepAlive.toIntOrNull() ?: 0
        )
        val proxyOpts = settings.proxyOptions
        val proxyPortParsed = proxyOpts.port.toIntOrNull() ?: 3128
        viewModel.setProxy(
            proxyOpts.useServer, proxyOpts.address.trim(), proxyPortParsed,
            proxyOpts.secureComm, proxyOpts.username, proxyOpts.password
        )
        viewModel.setCursorSettings(settings.cursorType, cursorColorFromName(settings.cursorColor))
        viewModel.setFields3dMode(settings.fields3D)
        viewModel.setColorAdjust(settings.colorFgDark, settings.colorFgBright, settings.colorBgAdjust)
        viewModel.setVtAttrMap(settings.vtAttrMap)
        binding.terminalOutput.lineTerminator = lineTerminatorBytes()
        val vtOpts = settings.vtOptions
        viewModel.setVtOptions(vtOpts)
        binding.terminalOutput.backspaceAsDel = vtOpts.backspaceAction == "DEL"
        binding.terminalOutput.f5PuttySequence = vtOpts.f5PuttySequence
        viewModel.setTransliterationOptions(settings.transliterationOptions)
        viewModel.setGeneralEmulationOptions(settings.generalEmulationOptions)
    }

    private fun setupListeners() {
        binding.disconnectButton.setOnClickListener {
            manualDisconnect = true
            if (slotId >= 0) SessionStore.close(slotId) else viewModel.disconnect()
            finish()
        }

        binding.btnBackSessions.apply {
            visibility = if (slotId >= 0) View.VISIBLE else View.GONE
            setOnClickListener { finish() }
        }

        binding.tvSessionBadge.setOnClickListener {
            val other = SessionStore.otherSession(slotId)
            if (other != null) {
                startActivity(Intent(this, MainActivity::class.java).apply {
                    putExtra(EXTRA_SLOT_ID, other.slotId)
                })
            }
            finish()
        }

        // Digitacao vai direto ao servidor (via TerminalView), como num emulador real
        binding.terminalOutput.onInput = { bytes -> viewModel.sendRaw(bytes) }

        // Botao de mostrar/ocultar teclado (ao lado de Desconectar)
        binding.keyboardToggle.setOnClickListener { toggleKeyboard() }
        binding.keysToggle.setOnClickListener { toggleKeysBar() }

        // Toque simples: abre teclado e rola ao fim; duplo toque: ação configurada
        binding.terminalOutput.setOnClickListener {
            val now = System.currentTimeMillis()
            if (now - lastTapTime < 300) {
                handleDoubleTap()
            } else {
                openKeyboard()
                binding.scrollView.post {
                    binding.scrollView.fullScroll(android.widget.ScrollView.FOCUS_DOWN)
                }
            }
            lastTapTime = now
        }
    }

    private fun observeViewModel() {
        viewModel.connectionState.observe(this) { state ->
            when (state) {
                ConnectionState.DISCONNECTED -> {
                    barcodeManager.unregister()
                    binding.statusText.text = getString(R.string.status_disconnected)
                    binding.statusText.setTextColor(getColor(android.R.color.darker_gray))
                    binding.disconnectButton.isEnabled = false
                    binding.keyboardToggle.visibility = android.view.View.GONE
                    binding.keysToggle.visibility = android.view.View.GONE
                    cursorBlinkHandler.removeCallbacks(cursorBlinkRunnable)
                    // "Sempre" mantém a barra visível mesmo desconectado
                    if (settings.showToolbar != "Sempre") {
                        binding.controlKeysBar.visibility = android.view.View.GONE
                    }
                    if (hasConnected) {
                        if (!manualDisconnect && settings.autoReconnect && currentHost.isNotEmpty()) {
                            Timber.d("Conexao perdida -> reconectando automaticamente")
                            viewModel.connect(currentHost, currentPort.toString())
                        } else {
                            finish()
                        }
                    }
                }
                ConnectionState.CONNECTING -> {
                    binding.statusText.text = getString(R.string.status_connecting)
                    binding.statusText.setTextColor(getColor(android.R.color.holo_orange_dark))
                    binding.disconnectButton.isEnabled = false
                    if (settings.showToolbar != "Sempre") {
                        binding.controlKeysBar.visibility = android.view.View.GONE
                    }
                }
                ConnectionState.CONNECTED -> {
                    barcodeManager.updateOptions(settings.barcodeOptions)
                    barcodeManager.register()
                    hasConnected = true
                    manualDisconnect = false
                    binding.statusText.text = getString(R.string.status_connected)
                    binding.statusText.setTextColor(getColor(android.R.color.holo_green_dark))
                    binding.disconnectButton.isEnabled = true
                    binding.keyboardToggle.visibility = android.view.View.VISIBLE
                    binding.keysToggle.visibility = android.view.View.VISIBLE
                    binding.keysToggle.alpha = if (toolbarHidden) 0.5f else 1f
                    if (settings.showToolbar != "Nunca") {
                        // Respeita a escolha manual do usuário (botão de ocultar teclas)
                        binding.controlKeysBar.visibility =
                            if (toolbarHidden) android.view.View.GONE else android.view.View.VISIBLE
                    }
                    if (settings.cursorBlinking) {
                        cursorBlinkHandler.removeCallbacks(cursorBlinkRunnable)
                        cursorBlinkHandler.postDelayed(cursorBlinkRunnable, 500)
                    }
                    buildToolbars()
                    // Tamanho da fonte ao conectar:
                    //  - Pinça ligada e ainda sem tamanho fixado pelo usuário → auto-ajusta à tela.
                    //  - Caso contrário (pinça desligada, ou usuário já definiu) → usa o tamanho salvo.
                    if (settings.pinchZoomEnabled && settings.fontAutoFit) {
                        applyAutoFitFontSize()
                    } else {
                        binding.terminalOutput.textSize = settings.fontSize.toFloat()
                    }
                    // Teclado habilitado: abre automaticamente ao conectar
                    if (settings.keyboardEnabled) {
                        openKeyboard()
                    }
                }
                ConnectionState.ERROR -> {
                    binding.statusText.text = getString(R.string.status_error)
                    binding.statusText.setTextColor(getColor(android.R.color.holo_red_dark))
                    binding.disconnectButton.isEnabled = false
                    binding.controlKeysBar.visibility = android.view.View.GONE
                }
            }
        }

        viewModel.terminalOutputStyled.observe(this) { styled ->
            binding.terminalOutput.text = styled
            binding.scrollView.post {
                binding.scrollView.fullScroll(android.widget.ScrollView.FOCUS_DOWN)
            }
        }

        // Alarme do host (BEL): toca som + vibra quando não silenciado
        viewModel.bellEvent.observe(this) { ev ->
            if (ev != null) {
                playBell()
                viewModel.onBellHandled()
            }
        }
    }

    /** Toca o som de notificação e vibra (alarme do host / BEL). */
    private fun playBell() {
        try {
            val tone = android.media.ToneGenerator(
                android.media.AudioManager.STREAM_NOTIFICATION, 100
            )
            tone.startTone(android.media.ToneGenerator.TONE_PROP_BEEP, 200)
            binding.terminalOutput.postDelayed({ tone.release() }, 300)
        } catch (e: Exception) {
            Timber.w(e, "Falha ao tocar alarme")
        }
        try {
            val vib = getSystemService(Context.VIBRATOR_SERVICE) as? android.os.Vibrator
            if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
                vib?.vibrate(android.os.VibrationEffect.createOneShot(120, android.os.VibrationEffect.DEFAULT_AMPLITUDE))
            } else {
                @Suppress("DEPRECATION") vib?.vibrate(120)
            }
        } catch (e: Exception) {
            Timber.w(e, "Falha ao vibrar")
        }
    }

    override fun onCreateOptionsMenu(menu: Menu?): Boolean {
        menuInflater.inflate(R.menu.menu_main, menu)
        return true
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        return when (item.itemId) {
            R.id.menu_calculadora -> { abrirCalculadora(); true }
            R.id.menu_logout      -> { sair(); true }
            else -> super.onOptionsItemSelected(item)
        }
    }

    private fun abrirCalculadora() {
        FloatingCalculatorHelper(this) { resultado ->
            viewModel.sendRaw(resultado.toByteArray(Charsets.ISO_8859_1))
        }.show()
    }

    private fun sair() {
        finish()
    }

    private fun hideKeyboard() {
        val imm = getSystemService(INPUT_METHOD_SERVICE) as InputMethodManager
        imm.hideSoftInputFromWindow(binding.root.windowToken, 0)
    }

    /** Abre o teclado virtual focando o terminal (a digitacao vai direto ao servidor). */
    private fun openKeyboard() {
        binding.terminalOutput.requestFocus()
        val imm = getSystemService(INPUT_METHOD_SERVICE) as InputMethodManager
        imm.showSoftInput(binding.terminalOutput, InputMethodManager.SHOW_IMPLICIT)
    }

    /** Mostra/oculta o teclado virtual. */
    private fun toggleKeyboard() {
        binding.terminalOutput.requestFocus()
        val imm = getSystemService(INPUT_METHOD_SERVICE) as InputMethodManager
        imm.toggleSoftInput(InputMethodManager.SHOW_FORCED, 0)
    }

    /** Mostra/oculta a barra de teclas personalizadas (botão no topo, ao lado do teclado). */
    private fun toggleKeysBar() {
        toolbarHidden = !toolbarHidden
        binding.controlKeysBar.visibility =
            if (toolbarHidden) android.view.View.GONE else android.view.View.VISIBLE
        // Feedback visual no botão: meio apagado quando a barra está oculta
        binding.keysToggle.alpha = if (toolbarHidden) 0.5f else 1f
    }

    /** Gera as barras de ferramentas com scroll horizontal quando há muitos botões. */
    private fun buildToolbars() {
        binding.controlKeysBar.removeAllViews()
        // Teclas da empresa (quando definidas) têm prioridade sobre as locais
        val bars = cc().toolbars(this) ?: settings.toolbars
        val density = resources.displayMetrics.density
        val screenW = resources.displayMetrics.widthPixels
        val btnH = (44 * density).toInt()
        val margin = (2 * density).toInt()
        val minBtnW = (72 * density).toInt()

        for (bar in bars) {
            if (bar.isEmpty()) continue

            // Largura por botão: distribui igualmente se couber, senão usa mínimo e rola
            val btnW = if (bar.size * minBtnW <= screenW) screenW / bar.size else minBtnW

            val row = android.widget.LinearLayout(this).apply {
                orientation = android.widget.LinearLayout.HORIZONTAL
                layoutParams = android.widget.LinearLayout.LayoutParams(
                    android.view.ViewGroup.LayoutParams.WRAP_CONTENT,
                    android.view.ViewGroup.LayoutParams.WRAP_CONTENT)
            }

            for (btn in bar) {
                val btnColor = if (btn.color.isNotEmpty()) {
                    try { android.graphics.Color.parseColor(btn.color) } catch (e: Exception) { 0xFF2E5C6E.toInt() }
                } else 0xFF2E5C6E.toInt()
                val b = android.widget.Button(this).apply {
                    text = btn.label
                    isAllCaps = false
                    textSize = 11f
                    setTextColor(android.graphics.Color.WHITE)
                    setBackgroundColor(btnColor)
                    setPadding(0, 0, 0, 0)
                    minWidth = 0
                    minimumWidth = 0
                    val lp = android.widget.LinearLayout.LayoutParams(btnW, btnH)
                    lp.setMargins(margin, margin, margin, margin)
                    layoutParams = lp
                }
                b.setOnClickListener { sendAction(btn.action) }
                row.addView(b)
            }

            val scroll = android.widget.HorizontalScrollView(this).apply {
                isHorizontalScrollBarEnabled = false
                layoutParams = android.widget.LinearLayout.LayoutParams(
                    android.view.ViewGroup.LayoutParams.MATCH_PARENT,
                    android.view.ViewGroup.LayoutParams.WRAP_CONTENT)
            }
            scroll.addView(row)
            binding.controlKeysBar.addView(scroll)
        }
    }

    /** Bytes do terminador de linha configurado (Telnet Opcoes). */
    private fun lineTerminatorBytes(): ByteArray = when (settings.telnetOptions.lineTerminator) {
        "CR+LF" -> byteArrayOf(13, 10)
        "LF"    -> byteArrayOf(10)
        else    -> byteArrayOf(13)   // CR
    }

    /** Executa a acao de um botao de barra de ferramentas. */
    private fun sendAction(action: String) {
        // Enter respeita o terminador de linha configurado
        if (action == "ENTER") {
            viewModel.sendRaw(lineTerminatorBytes())
            return
        }
        val bytes = com.logisticapp.emuladortelnet.toolbar.ToolbarCatalog.bytesFor(action)
        if (bytes != null) {
            viewModel.sendRaw(bytes)
            return
        }
        // Acoes especiais (tratadas no app)
        when (action) {
            "DISCONNECT" -> { manualDisconnect = true; viewModel.disconnect() }
            "CONNECT" -> {
                if (currentHost.isNotEmpty()) viewModel.connect(currentHost, currentPort.toString())
            }
            "BREAK" -> {
                // "Usar IP para BRK": envia Interrupt Process (244) em vez de Break (243)
                val cmd = if (settings.telnetOptions.useIpForBrk) 244 else 243
                viewModel.sendRaw(byteArrayOf(255.toByte(), cmd.toByte()))
            }
            "COPY"  -> copyTerminalToClipboard()
            "PASTE" -> pasteFromClipboard()
            "PRINT" -> printTerminalScreen()
            else -> Timber.d("Acao nao tratada: $action")
        }
    }

    private fun copyTerminalToClipboard() {
        val text = binding.terminalOutput.text?.toString() ?: ""
        if (text.isBlank()) {
            android.widget.Toast.makeText(this, "Nada para copiar", android.widget.Toast.LENGTH_SHORT).show()
            return
        }
        val clipboard = getSystemService(CLIPBOARD_SERVICE) as android.content.ClipboardManager
        clipboard.setPrimaryClip(android.content.ClipData.newPlainText("Terminal ScanTE", text))
        android.widget.Toast.makeText(this, "Copiado para a área de transferência", android.widget.Toast.LENGTH_SHORT).show()
    }

    private fun pasteFromClipboard() {
        val clipboard = getSystemService(CLIPBOARD_SERVICE) as android.content.ClipboardManager
        val text = clipboard.primaryClip
            ?.takeIf { it.itemCount > 0 }
            ?.getItemAt(0)
            ?.coerceToText(this)
            ?.toString()
        if (text.isNullOrEmpty()) {
            android.widget.Toast.makeText(this, "Área de transferência vazia", android.widget.Toast.LENGTH_SHORT).show()
            return
        }
        viewModel.sendRaw(text.toByteArray(Charsets.ISO_8859_1))
    }

    private fun printTerminalScreen() {
        val opts = settings.printOptions
        if (opts.connectionType == "Bluetooth" && opts.bluetoothAddress.isBlank()) {
            android.widget.Toast.makeText(this,
                "Configure a impressora em Configurações → Dispositivos → Impressão",
                android.widget.Toast.LENGTH_LONG).show()
            return
        }
        if (opts.connectionType == "WiFi" && opts.wifiHost.isBlank()) {
            android.widget.Toast.makeText(this,
                "Configure o IP da impressora em Configurações → Dispositivos → Impressão",
                android.widget.Toast.LENGTH_LONG).show()
            return
        }
        val lines = viewModel.getScreenLines()
        val printer = EscPosPrinter()
        android.widget.Toast.makeText(this, "Enviando para a impressora…", android.widget.Toast.LENGTH_SHORT).show()
        MainScope().launch(Dispatchers.IO) {
            try {
                if (printer.connect(opts)) {
                    printer.printLines(lines, opts)
                    printer.disconnect()
                    withContext(Dispatchers.Main) {
                        android.widget.Toast.makeText(this@MainActivity,
                            "Impressão enviada!", android.widget.Toast.LENGTH_SHORT).show()
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        android.widget.Toast.makeText(this@MainActivity,
                            "Não foi possível conectar na impressora", android.widget.Toast.LENGTH_LONG).show()
                    }
                }
            } catch (e: Exception) {
                Timber.e(e, "Erro ao imprimir tela")
                withContext(Dispatchers.Main) {
                    android.widget.Toast.makeText(this@MainActivity,
                        "Erro: ${e.message}", android.widget.Toast.LENGTH_LONG).show()
                }
                printer.disconnect()
            }
        }
    }

    private fun sendBarcodeToServer(barcode: String, actionAfterScan: String) {
        if (barcode.isEmpty()) return
        // Envia o texto do código de barras
        viewModel.sendRaw(barcode.toByteArray(Charsets.ISO_8859_1))
        // Ação pós-leitura
        val actionBytes: ByteArray? = when (actionAfterScan) {
            "Enter"      -> lineTerminatorBytes()
            "Tab"        -> byteArrayOf(9)
            "Enter + Tab" -> lineTerminatorBytes() + byteArrayOf(9)
            else          -> null
        }
        actionBytes?.let { viewModel.sendRaw(it) }
        // Mostrar na barra de status (se configurado)
        if (settings.barcodeOptions.showOnStatusBar) {
            val prev = binding.statusText.text
            val prevColor = binding.statusText.currentTextColor
            binding.statusText.text = "▣ $barcode"
            binding.statusText.setTextColor(android.graphics.Color.CYAN)
            binding.statusText.postDelayed({
                binding.statusText.text = prev
                binding.statusText.setTextColor(prevColor)
            }, 2000)
        }
        Timber.d("Barcode enviado ao servidor: $barcode (ação=$actionAfterScan)")
    }

    /**
     * Configura o gesto de pinça (dois dedos) pra redimensionar a fonte do terminal.
     * Só age quando "Redimensionar com pinça" está ligado nas Opções de tela.
     */
    private fun setupPinchZoom() {
        scaleDetector = android.view.ScaleGestureDetector(this,
            object : android.view.ScaleGestureDetector.SimpleOnScaleGestureListener() {
                override fun onScaleBegin(detector: android.view.ScaleGestureDetector): Boolean {
                    if (!settings.pinchZoomEnabled) return false
                    scaling = true
                    return true
                }
                override fun onScale(detector: android.view.ScaleGestureDetector): Boolean {
                    if (!settings.pinchZoomEnabled) return false
                    val currentSp = binding.terminalOutput.textSize / resources.displayMetrics.scaledDensity
                    val newSp = (currentSp * detector.scaleFactor).coerceIn(MIN_FONT_SP, MAX_FONT_SP)
                    binding.terminalOutput.textSize = newSp
                    return true
                }
                override fun onScaleEnd(detector: android.view.ScaleGestureDetector) {
                    if (!settings.pinchZoomEnabled) return
                    val finalSp = (binding.terminalOutput.textSize / resources.displayMetrics.scaledDensity)
                        .toInt().coerceIn(MIN_FONT_SP.toInt(), MAX_FONT_SP.toInt())
                    settings.fontSize = finalSp        // guarda o tamanho escolhido pelos dedos
                    settings.fontAutoFit = false       // fixa: o auto-fit não sobrescreve mais
                    // pequeno atraso pra o ACTION_UP do gesto não disparar o clique (abrir teclado)
                    binding.terminalOutput.postDelayed({ scaling = false }, 120)
                }
            })

        binding.terminalOutput.setOnTouchListener { v, event ->
            if (settings.pinchZoomEnabled) {
                // Com 2+ dedos, impede que os ScrollViews pais roubem o gesto de pinça.
                if (event.pointerCount >= 2) v.parent?.requestDisallowInterceptTouchEvent(true)
                scaleDetector.onTouchEvent(event)
                if (scaling) return@setOnTouchListener true  // consome: não rola nem clica
            }
            false  // toque simples/scroll seguem normais (click e scroll preservados)
        }
    }

    private fun handleDoubleTap() {
        when (settings.doubleTapAction) {
            "Zoom in" -> {
                val sp = binding.terminalOutput.textSize / resources.displayMetrics.scaledDensity
                binding.terminalOutput.textSize = (sp + 2f).coerceAtMost(MAX_FONT_SP)
                settings.fontSize = binding.terminalOutput.textSize.let { (it / resources.displayMetrics.scaledDensity).toInt() }
                settings.fontAutoFit = false
            }
            "Zoom out" -> {
                val sp = binding.terminalOutput.textSize / resources.displayMetrics.scaledDensity
                binding.terminalOutput.textSize = (sp - 2f).coerceAtLeast(MIN_FONT_SP)
                settings.fontSize = binding.terminalOutput.textSize.let { (it / resources.displayMetrics.scaledDensity).toInt() }
                settings.fontAutoFit = false
            }
            "Redefinir tamanho da tela" -> {
                settings.fontAutoFit = true    // volta ao ajuste automático à tela
                applyAutoFitFontSize()
            }
            // "Nenhum" → sem ação
        }
    }

    /**
     * Calcula o tamanho de fonte ideal para que a grade fixa (largura x altura
     * definidas em Opções gerais de emulação) preencha a área real disponível na
     * tela do aparelho, em vez de usar sempre o mesmo tamanho fixo. Roda após o
     * primeiro layout, quando as dimensões reais da tela já estão disponíveis.
     */
    private fun applyAutoFitFontSize() {
        // Espera o layout realmente terminar (barra de ferramentas já visível) antes de medir —
        // um post{} simples pode rodar antes da barra entrar, medindo uma altura maior do que a real.
        val scrollView = binding.scrollView
        scrollView.viewTreeObserver.addOnGlobalLayoutListener(object : android.view.ViewTreeObserver.OnGlobalLayoutListener {
            override fun onGlobalLayout() {
                scrollView.viewTreeObserver.removeOnGlobalLayoutListener(this)

                val rows = settings.generalEmulationOptions.initialHeight.coerceAtLeast(1)

                val availableHeightPx = (scrollView.height -
                    binding.terminalOutput.paddingTop - binding.terminalOutput.paddingBottom).toFloat()
                if (availableHeightPx <= 0f) return

                val paint = android.graphics.Paint().apply {
                    typeface = binding.terminalOutput.typeface
                    textSize = 100f
                }
                val fm = paint.fontMetrics
                val lineHeightAt100 = fm.descent - fm.ascent
                if (lineHeightAt100 <= 0f) return

                // Prioriza preencher a altura (24 linhas): a largura (80 colunas) quase sempre é a
                // dimensão mais apertada num celular estreito, e forçar caber nela deixa a fonte
                // minúscula. A HorizontalScrollView já existe pra rolar o excesso horizontal.
                val idealPx = availableHeightPx / rows / lineHeightAt100 * 100f
                val idealSp = (idealPx / resources.displayMetrics.scaledDensity).coerceIn(8f, MAX_FONT_SP)
                binding.terminalOutput.textSize = idealSp
                // Reflete o tamanho calculado no painel (útil se o cliente desligar a pinça depois).
                // Não mexe em fontAutoFit: o cálculo é determinístico (altura fixa), então não "encolhe" a cada vez.
                settings.fontSize = idealSp.toInt()
            }
        })
    }

    // ------------------------------------------------------------------
    // Personalização da empresa (CompanyConfigStore) — quando ativa, sobrepõe
    // as cores locais e mostra o cabeçalho com a marca.
    // ------------------------------------------------------------------
    private fun cc() = com.logisticapp.emuladortelnet.settings.CompanyConfigStore
    private fun effFg()       = cc().colorForeground(this)       ?: settings.colorForeground
    private fun effBg()       = cc().colorBackground(this)       ?: settings.colorBackground
    private fun effField()    = cc().colorField(this)            ?: settings.colorInputField
    private fun effStatusFg() = cc().colorStatusForeground(this) ?: settings.colorStatusForeground
    private fun effStatusBg() = cc().colorStatusBackground(this) ?: settings.colorStatusBackground

    /** Mostra logo + nome da empresa no topo, se ela configurou o cabeçalho. */
    private fun aplicarCabecalhoEmpresa() {
        val logo = binding.companyLogo
        if (cc().headerShow(this)) {
            val file = cc().logoFile(this)
            if (file != null) {
                logo.setImageURI(android.net.Uri.fromFile(file))
                logo.visibility = View.VISIBLE
            } else {
                logo.visibility = View.GONE
            }
        } else {
            logo.visibility = View.GONE
        }
    }

    private fun fontFromName(name: String): Typeface = when (name) {
        "Courier New"    -> Typeface.create("Courier New", Typeface.NORMAL).takeIf { it != Typeface.DEFAULT }
                             ?: Typeface.MONOSPACE
        "Droid Sans Mono" -> Typeface.create("Droid Sans Mono", Typeface.NORMAL).takeIf { it != Typeface.DEFAULT }
                             ?: Typeface.MONOSPACE
        else             -> Typeface.MONOSPACE
    }

    private fun cursorColorFromName(name: String): Int = when (name) {
        "Branco"   -> Color.WHITE
        "Ciano"    -> Color.CYAN
        "Amarelo"  -> Color.YELLOW
        "Vermelho" -> Color.RED
        "Azul"     -> Color.rgb(64, 128, 255)
        "Laranja"  -> Color.rgb(255, 128, 0)
        else       -> Color.rgb(0, 200, 100)   // "Verde" / "Padrão"
    }
}
