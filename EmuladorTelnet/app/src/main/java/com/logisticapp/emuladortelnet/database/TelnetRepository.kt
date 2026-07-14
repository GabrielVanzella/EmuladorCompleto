package com.logisticapp.emuladortelnet.database

import android.content.Context
import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import timber.log.Timber

class TelnetRepository(private val context: Context) {

    private val prefs = context.getSharedPreferences("telnet_hosts", Context.MODE_PRIVATE)
    private val gson = Gson()

    private val _connections = MutableStateFlow<List<SavedConnection>>(emptyList())

    init {
        _connections.value = loadConnectionsFromPrefs()
        Timber.d("TelnetRepository: ${_connections.value.size} hosts carregados")
    }

    // ------------------------------------------------------------------
    // Hosts / Conexoes
    // ------------------------------------------------------------------

    fun getAllConnections(): Flow<List<SavedConnection>> = _connections.asStateFlow()

    suspend fun saveConnection(connection: SavedConnection): Long {
        val list = _connections.value.toMutableList()
        val newId = (list.maxOfOrNull { it.id } ?: 0) + 1
        val toSave = connection.copy(id = newId, createdAt = System.currentTimeMillis())
        list.add(toSave)
        persistAndEmit(list)
        Timber.d("Host salvo: ${toSave.name} (id=$newId)")
        return newId.toLong()
    }

    suspend fun updateConnection(connection: SavedConnection) {
        val list = _connections.value.toMutableList()
        val idx = list.indexOfFirst { it.id == connection.id }
        if (idx >= 0) {
            list[idx] = connection
            persistAndEmit(list)
            Timber.d("Host atualizado: ${connection.name}")
        }
    }

    /** Aplica o mesmo Nome/Host/Porta a todas as sessões salvas (conexão única compartilhada). */
    suspend fun updateAllConnectionsIdentity(name: String, host: String, port: Int) {
        val list = _connections.value.map { it.copy(name = name, host = host, port = port) }
        persistAndEmit(list)
        Timber.d("Nome/Host/Porta aplicados a todas as ${list.size} sessoes")
    }

    suspend fun deleteConnection(id: Int) {
        val list = _connections.value.filter { it.id != id }.toMutableList()
        persistAndEmit(list)
        Timber.d("Host removido: id=$id")
    }

    suspend fun getConnectionById(id: Int): SavedConnection? =
        _connections.value.firstOrNull { it.id == id }

    suspend fun updateLastUsed(connectionId: Int) {
        val list = _connections.value.toMutableList()
        val idx = list.indexOfFirst { it.id == connectionId }
        if (idx >= 0) {
            list[idx] = list[idx].copy(lastUsed = System.currentTimeMillis())
            persistAndEmit(list)
        }
    }

    fun getFavoriteConnections(): Flow<List<SavedConnection>> =
        MutableStateFlow(_connections.value.filter { it.isFavorite }).asStateFlow()

    /** Lista atual (acesso sincrono) */
    fun currentConnections(): List<SavedConnection> = _connections.value

    /** Exporta todas as sessoes como JSON */
    fun exportJson(): String = gson.toJson(_connections.value)

    /** Importa sessoes de um JSON, atribuindo novos ids. Retorna quantas foram importadas. */
    suspend fun importJson(json: String): Int {
        val imported: List<SavedConnection> = try {
            val type = object : TypeToken<List<SavedConnection>>() {}.type
            gson.fromJson(json, type)
        } catch (e: Exception) {
            Timber.e(e, "JSON invalido na importacao")
            null
        } ?: return 0

        val list = _connections.value.toMutableList()
        var nextId = (list.maxOfOrNull { it.id } ?: 0) + 1
        for (conn in imported) {
            list.add(conn.copy(id = nextId))
            nextId++
        }
        persistAndEmit(list)
        Timber.d("Importadas ${imported.size} sessoes")
        return imported.size
    }

    // ------------------------------------------------------------------
    // Persistencia interna
    // ------------------------------------------------------------------

    private fun persistAndEmit(list: List<SavedConnection>) {
        val json = gson.toJson(list)
        prefs.edit().putString("connections", json).apply()
        _connections.value = list
    }

    private fun loadConnectionsFromPrefs(): List<SavedConnection> {
        val json = prefs.getString("connections", null) ?: return emptyList()
        return try {
            val type = object : TypeToken<List<SavedConnection>>() {}.type
            gson.fromJson(json, type) ?: emptyList()
        } catch (e: Exception) {
            Timber.e(e, "Erro ao carregar hosts")
            emptyList()
        }
    }

    // ------------------------------------------------------------------
    // Preferences gerais
    // ------------------------------------------------------------------

    suspend fun setPreference(key: String, value: String) {
        prefs.edit().putString(key, value).apply()
    }

    suspend fun getPreference(key: String, defaultValue: String = ""): String =
        prefs.getString(key, defaultValue) ?: defaultValue

    // ------------------------------------------------------------------
    // Session log
    // ------------------------------------------------------------------

    private val sessionPrefs = context.getSharedPreferences("telnet_session_log", Context.MODE_PRIVATE)

    private val _sessionLogs = MutableStateFlow<List<SessionLog>>(emptyList())
    private val _commandHistory = MutableStateFlow<List<CommandHistory>>(emptyList())

    init {
        _sessionLogs.value = loadSessionLogs()
        _commandHistory.value = loadCommandHistory()
    }

    fun getSessionLogs(): Flow<List<SessionLog>> = _sessionLogs.asStateFlow()

    fun getCommandHistory(sessionId: Int): List<CommandHistory> =
        _commandHistory.value.filter { it.sessionId == sessionId }

    suspend fun startSession(connectionId: Int, host: String, port: Int): Long {
        val logs = _sessionLogs.value.toMutableList()
        val newId = (logs.maxOfOrNull { it.id } ?: 0) + 1
        val session = SessionLog(id = newId, connectionId = connectionId, host = host, port = port)
        logs.add(session)
        val pruned = if (logs.size > MAX_SESSION_LOGS) logs.takeLast(MAX_SESSION_LOGS) else logs
        persistSessionLogs(pruned)
        _sessionLogs.value = pruned
        Timber.d("Sessao iniciada: $host:$port (id=$newId)")
        return newId.toLong()
    }

    suspend fun endSession(sessionId: Int, reason: String = "") {
        val logs = _sessionLogs.value.toMutableList()
        val idx = logs.indexOfFirst { it.id == sessionId }
        if (idx >= 0) {
            logs[idx] = logs[idx].copy(endTime = System.currentTimeMillis(), reason = reason)
            persistSessionLogs(logs)
            _sessionLogs.value = logs
        }
        Timber.d("Sessao encerrada: id=$sessionId, motivo=$reason")
    }

    suspend fun saveCommand(sessionId: Int, command: String, response: String = "") {
        if (command.isBlank()) return
        val cmds = _commandHistory.value.toMutableList()
        val newId = (cmds.maxOfOrNull { it.id } ?: 0) + 1
        cmds.add(CommandHistory(id = newId, sessionId = sessionId, command = command, response = response))
        val pruned = if (cmds.size > MAX_COMMAND_HISTORY) cmds.takeLast(MAX_COMMAND_HISTORY) else cmds
        persistCommandHistory(pruned)
        _commandHistory.value = pruned
        Timber.d("Comando salvo (sessao $sessionId): $command")
    }

    /** Remove todos os logs de sessao e historico de comandos. */
    suspend fun clearSessionLogs() {
        persistSessionLogs(emptyList())
        persistCommandHistory(emptyList())
        _sessionLogs.value = emptyList()
        _commandHistory.value = emptyList()
    }

    private fun loadSessionLogs(): List<SessionLog> {
        val json = sessionPrefs.getString("sessions", null) ?: return emptyList()
        return try {
            val type = object : TypeToken<List<SessionLog>>() {}.type
            gson.fromJson(json, type) ?: emptyList()
        } catch (e: Exception) {
            Timber.e(e, "Erro ao carregar logs de sessao")
            emptyList()
        }
    }

    private fun persistSessionLogs(list: List<SessionLog>) {
        sessionPrefs.edit().putString("sessions", gson.toJson(list)).apply()
    }

    private fun loadCommandHistory(): List<CommandHistory> {
        val json = sessionPrefs.getString("commands", null) ?: return emptyList()
        return try {
            val type = object : TypeToken<List<CommandHistory>>() {}.type
            gson.fromJson(json, type) ?: emptyList()
        } catch (e: Exception) {
            Timber.e(e, "Erro ao carregar historico de comandos")
            emptyList()
        }
    }

    private fun persistCommandHistory(list: List<CommandHistory>) {
        sessionPrefs.edit().putString("commands", gson.toJson(list)).apply()
    }

    // ------------------------------------------------------------------
    // Auth stubs (modulo de login desabilitado)
    // ------------------------------------------------------------------

    suspend fun authenticateUser(email: String, password: String): User? = null

    suspend fun createTestUser() { Timber.d("createTestUser stub") }

    // ------------------------------------------------------------------
    // Singleton
    // ------------------------------------------------------------------

    companion object {
        private const val MAX_SESSION_LOGS   = 100
        private const val MAX_COMMAND_HISTORY = 500

        @Volatile private var instance: TelnetRepository? = null

        fun getInstance(context: Context): TelnetRepository =
            instance ?: synchronized(this) {
                instance ?: TelnetRepository(context.applicationContext).also { instance = it }
            }
    }
}
