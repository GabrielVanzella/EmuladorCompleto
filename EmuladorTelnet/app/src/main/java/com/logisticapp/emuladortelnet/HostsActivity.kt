package com.logisticapp.emuladortelnet

import android.content.Intent
import android.os.Bundle
import android.view.Menu
import android.view.MenuItem
import android.view.View
import android.widget.EditText
import android.widget.ImageButton
import android.widget.PopupMenu
import android.widget.TextView
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.widget.Toolbar
import androidx.lifecycle.Observer
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.lifecycleScope
import com.logisticapp.emuladortelnet.data.ConnectionState
import androidx.recyclerview.widget.DividerItemDecoration
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.google.android.material.floatingactionbutton.FloatingActionButton
import com.logisticapp.emuladortelnet.database.SavedConnection
import com.logisticapp.emuladortelnet.database.TelnetRepository
import com.logisticapp.emuladortelnet.ui.HostsViewModel
import com.logisticapp.emuladortelnet.ui.HostsViewModelFactory
import kotlinx.coroutines.launch
import timber.log.Timber

class HostsActivity : AppCompatActivity() {

    private lateinit var viewModel: HostsViewModel
    private lateinit var adapter: HostsAdapter
    private var currentHosts: List<SavedConnection> = emptyList()

    companion object {
        // Garante a conexao automatica apenas uma vez por inicializacao do app
        private var didAutoConnect = false
    }

    // Seletor de arquivo para importacao de sessoes (JSON)
    private val importLauncher = registerForActivityResult(
        ActivityResultContracts.GetContent()
    ) { uri ->
        if (uri == null) return@registerForActivityResult
        try {
            val json = contentResolver.openInputStream(uri)?.bufferedReader()?.use { it.readText() }
            if (json.isNullOrBlank()) {
                toast("Arquivo vazio ou invalido")
                return@registerForActivityResult
            }
            viewModel.importJson(json) { count ->
                toast(if (count > 0) "$count sessao(oes) importada(s)" else "Nenhuma sessao valida no arquivo")
            }
        } catch (e: Exception) {
            Timber.e(e, "Erro ao importar")
            toast("Erro ao ler o arquivo")
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        com.logisticapp.emuladortelnet.settings.AppSettings.get(this).applyOrientation(this)
        setContentView(R.layout.activity_hosts)

        val toolbar = findViewById<Toolbar>(R.id.toolbar)
        setSupportActionBar(toolbar)

        val repository = TelnetRepository.getInstance(this)
        viewModel = ViewModelProvider(this, HostsViewModelFactory(repository))
            .get(HostsViewModel::class.java)

        setupRecyclerView()
        observeHosts()

        findViewById<FloatingActionButton>(R.id.fab_add).setOnClickListener {
            openNewSessionConfig()
        }

        maybeAutoConnect()
    }

    /** Conecta automaticamente na sessao mais recente, se a opcao estiver ligada (1x por inicializacao). */
    private fun maybeAutoConnect() {
        val settings = com.logisticapp.emuladortelnet.settings.AppSettings.get(this)
        if (didAutoConnect || !settings.autoConnect) return
        val hosts = viewModel.currentHosts()
        if (hosts.isEmpty()) return
        didAutoConnect = true
        val target = hosts.maxByOrNull { it.lastUsed } ?: hosts.first()
        Timber.d("Conexao automatica na inicializacao: ${target.name}")
        connectToHost(target)
    }

    private fun setupRecyclerView() {
        adapter = HostsAdapter(
            // Tocar numa sessão sempre conecta/retoma — editar Nome/Host/Porta agora é só
            // pelo menu de 3 pontinhos ("Editar Conexão"), que vale pra todas de uma vez.
            onItemClick = { host -> connectToHost(host) },
            onMenuClick = { host, anchor -> showPopupMenu(host, anchor) }
        )
        val recycler = findViewById<RecyclerView>(R.id.hosts_recycler)
        recycler.layoutManager = LinearLayoutManager(this)
        recycler.addItemDecoration(DividerItemDecoration(this, DividerItemDecoration.VERTICAL))
        recycler.adapter = adapter
    }

    private fun observeHosts() {
        lifecycleScope.launch {
            viewModel.hosts.collect { hosts ->
                currentHosts = hosts
                adapter.submitList(hosts)
                val empty = findViewById<View>(R.id.empty_state)
                val recycler = findViewById<View>(R.id.hosts_recycler)
                if (hosts.isEmpty()) {
                    empty.visibility = View.VISIBLE
                    recycler.visibility = View.GONE
                } else {
                    empty.visibility = View.GONE
                    recycler.visibility = View.VISIBLE
                }
                Timber.d("Hosts atualizados: ${hosts.size}")
            }
        }
    }

    // ------------------------------------------------------------------
    // Menu geral da toolbar (3 pontos) — configuracoes gerais
    // ------------------------------------------------------------------

    override fun onCreateOptionsMenu(menu: Menu?): Boolean {
        menuInflater.inflate(R.menu.menu_sessions, menu)
        return true
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        return when (item.itemId) {
            R.id.menu_settings -> { startActivity(Intent(this, ConfigMenuActivity::class.java)); true }
            R.id.menu_new      -> { openNewSessionConfig(); true }
            R.id.menu_edit_connection -> { editarConexaoCompartilhada(); true }
            R.id.menu_remove   -> { menuRemove(); true }
            R.id.menu_rename   -> { menuRename(); true }
            R.id.menu_help     -> { menuHelp(); true }
            R.id.menu_import   -> { menuImport(); true }
            R.id.menu_export   -> { menuExport(); true }
            R.id.menu_templates   -> { startActivity(Intent(this, TemplatesActivity::class.java)); true }
            R.id.menu_calculadora -> { abrirCalculadora(); true }
            R.id.menu_options  -> { startActivity(Intent(this, GeneralOptionsActivity::class.java)); true }
            R.id.menu_about    -> { menuAbout(); true }
            else -> super.onOptionsItemSelected(item)
        }
    }

    private fun abrirCalculadora() {
        FloatingCalculatorHelper(this).show()
    }

    /** Mostra a lista de sessoes para o usuario escolher um alvo */
    private fun pickSession(title: String, onPick: (SavedConnection) -> Unit) {
        if (currentHosts.isEmpty()) {
            toast("Nenhuma sessão cadastrada")
            return
        }
        if (currentHosts.size == 1) {
            onPick(currentHosts[0])
            return
        }
        val names = currentHosts.map { it.name }.toTypedArray()
        AlertDialog.Builder(this)
            .setTitle(title)
            .setItems(names) { _, which -> onPick(currentHosts[which]) }
            .show()
    }

    private fun menuRemove() {
        pickSession("Remover qual sessão?") { host -> confirmDelete(host) }
    }

    private fun menuRename() {
        pickSession("Renomear qual sessão?") { host -> showRenameDialog(host) }
    }

    private fun showRenameDialog(host: SavedConnection) {
        val input = EditText(this).apply {
            setText(host.name)
            setSelection(host.name.length)
        }
        val container = android.widget.FrameLayout(this).apply {
            setPadding(48, 16, 48, 0)
            addView(input)
        }
        AlertDialog.Builder(this)
            .setTitle("Renomear sessão")
            .setView(container)
            .setPositiveButton("Salvar") { _, _ ->
                val novo = input.text.toString().trim()
                if (novo.isEmpty()) {
                    toast("Nome não pode ficar vazio")
                } else {
                    viewModel.renameHost(host, novo)
                    toast("Renomeado para \"$novo\"")
                }
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    private fun menuExport() {
        if (currentHosts.isEmpty()) {
            toast("Nenhuma sessão para exportar")
            return
        }
        val json = viewModel.exportJson()
        val intent = Intent(Intent.ACTION_SEND).apply {
            type = "application/json"
            putExtra(Intent.EXTRA_SUBJECT, "sessoes_tellx.json")
            putExtra(Intent.EXTRA_TEXT, json)
        }
        startActivity(Intent.createChooser(intent, "Exportar sessões"))
    }

    private fun menuImport() {
        // Aceita qualquer arquivo de texto/json
        try {
            importLauncher.launch("*/*")
        } catch (e: Exception) {
            toast("Nenhum gerenciador de arquivos disponível")
        }
    }

    private fun menuHelp() {
        AlertDialog.Builder(this)
            .setTitle("Ajuda")
            .setMessage(
                "ScanTE — Conecta · Sincroniza · Simplifica\n\n" +
                "• Toque em + para criar uma nova sessão (nome, IP e porta).\n" +
                "• Toque numa sessão para editar seus dados.\n" +
                "• Use os 3 pontinhos de cada sessão para Conectar, Editar, " +
                "Configuração avançada ou Remover.\n" +
                "• Este menu (3 pontos no topo) traz ações gerais: Novo, Remover, " +
                "Renomear, Importação e Exportação de sessões.\n\n" +
                "Na tela do terminal, use a barra inferior para enviar comandos e " +
                "as teclas de atalho."
            )
            .setPositiveButton("Fechar", null)
            .show()
    }

    private fun menuAbout() {
        val version = try {
            packageManager.getPackageInfo(packageName, 0).versionName
        } catch (e: Exception) { "1.0.0" }
        AlertDialog.Builder(this)
            .setTitle("Sobre ScanTE")
            .setMessage(
                "ScanTE\nConecta · Sincroniza · Simplifica\n\n" +
                "Versão $version\n\n" +
                "Software de comunicação entre coletores de dados e sistemas " +
                "corporativos via terminal (Protheus, AS/400 e outros), com " +
                "emulação de tela VT100."
            )
            .setPositiveButton("Fechar", null)
            .show()
    }

    private fun toast(msg: String) {
        Toast.makeText(this, msg, Toast.LENGTH_SHORT).show()
    }

    override fun onResume() {
        super.onResume()
        adapter.notifyDataSetChanged()
    }

    private fun showPopupMenu(host: SavedConnection, anchor: View) {
        val popup = PopupMenu(this, anchor)
        val ativa = SessionStore.isActive(host.id)
        val connectLabel = if (ativa) "Retomar" else "Conectar"
        popup.menu.add(0, 1, 0, connectLabel)
        popup.menu.add(0, 3, 2, "Configuracao")
        popup.menu.add(0, 4, 3, "Remover")
        popup.setOnMenuItemClickListener { item ->
            when (item.itemId) {
                1 -> connectToHost(host)
                3 -> openHostAdvanced(hostId = host.id)
                4 -> confirmDelete(host)
            }
            true
        }
        popup.show()
    }

    private fun connectToHost(host: SavedConnection) {
        val jaAtiva = SessionStore.isActive(host.id)
        val result = SessionStore.openOrResume(this, host.id, host.name, host.host, host.port)
        if (result == null) {
            toast("Máximo de 2 sessões ativas. Desconecte uma para abrir outra.")
            return
        }
        val (slotId, vm) = result

        // Sessão já ativa/conectada: só retoma, sem validar de novo.
        if (jaAtiva || vm.connectionState.value == ConnectionState.CONNECTED) {
            goToMain(slotId)
            return
        }

        toast("Conectando a ${host.host}:${host.port}...")
        vm.connect(host.host, host.port.toString())
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
                        toast("Não foi possível conectar a ${host.host}:${host.port}")
                    }
                    else -> { /* CONNECTING / DISCONNECTED: aguarda */ }
                }
            }
        })
    }

    private fun goToMain(slotId: Int) {
        val intent = Intent(this, MainActivity::class.java).apply {
            putExtra(MainActivity.EXTRA_SLOT_ID, slotId)
        }
        startActivity(intent)
    }

    /**
     * Edita Nome/Host/Porta de uma vez só, aplicando a mesma conexão a todas as sessões
     * salvas — não existe mais "Editar" por sessão individual no menu da seta.
     */
    private fun editarConexaoCompartilhada() {
        if (currentHosts.isEmpty()) {
            toast("Nenhuma sessão cadastrada. Use o + para criar a primeira.")
            return
        }
        val intent = Intent(this, HostConfigActivity::class.java)
        intent.putExtra(HostConfigActivity.EXTRA_EDIT_ALL, true)
        startActivity(intent)
    }

    /**
     * Nova sessão via "+": se já existe algum host salvo, reaproveita o mesmo Host/Porta
     * (bloqueados) — só o Nome fica livre. Evita erro de digitação de quem for criar
     * sessões novas no dia a dia.
     */
    private fun openNewSessionConfig() {
        val referencia = currentHosts.maxByOrNull { it.lastUsed }
        val intent = Intent(this, HostConfigActivity::class.java)
        if (referencia != null) {
            intent.putExtra(HostConfigActivity.EXTRA_PREFILL_HOST, referencia.host)
            intent.putExtra(HostConfigActivity.EXTRA_PREFILL_PORT, referencia.port)
            intent.putExtra(HostConfigActivity.EXTRA_LOCK_HOST_PORT, true)
        }
        startActivity(intent)
    }

    private fun openHostAdvanced(hostId: Int) {
        val intent = Intent(this, HostAdvancedActivity::class.java)
        intent.putExtra(HostAdvancedActivity.EXTRA_HOST_ID, hostId)
        startActivity(intent)
    }

    private fun confirmDelete(host: SavedConnection) {
        AlertDialog.Builder(this)
            .setTitle("Remover Host")
            .setMessage("Deseja remover \"${host.name}\"?")
            .setPositiveButton("Remover") { _, _ -> viewModel.deleteHost(host.id) }
            .setNegativeButton("Cancelar", null)
            .show()
    }
}

// ------------------------------------------------------------------
// Adapter
// ------------------------------------------------------------------

class HostsAdapter(
    private val onItemClick: (SavedConnection) -> Unit,
    private val onMenuClick: (SavedConnection, View) -> Unit
) : RecyclerView.Adapter<HostsAdapter.VH>() {

    private var list: List<SavedConnection> = emptyList()

    fun submitList(newList: List<SavedConnection>) {
        list = newList
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: android.view.ViewGroup, viewType: Int): VH {
        val view = android.view.LayoutInflater.from(parent.context)
            .inflate(R.layout.item_host, parent, false)
        return VH(view)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {
        holder.bind(list[position])
    }

    override fun getItemCount() = list.size

    inner class VH(view: View) : RecyclerView.ViewHolder(view) {
        private val name: TextView = view.findViewById(R.id.host_name)
        private val address: TextView = view.findViewById(R.id.host_address)
        private val badge: TextView = view.findViewById(R.id.tv_active_badge)
        private val btnMenu: ImageButton = view.findViewById(R.id.btn_menu)

        fun bind(host: SavedConnection) {
            name.text = host.name
            address.text = "${host.host}:${host.port}"
            badge.visibility = if (SessionStore.isActive(host.id)) View.VISIBLE else View.GONE
            itemView.setOnClickListener { onItemClick(host) }
            btnMenu.setOnClickListener { onMenuClick(host, btnMenu) }
        }
    }
}
