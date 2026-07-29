package com.logisticapp.emuladortelnet.settings

import android.content.Context
import android.graphics.Color
import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import com.logisticapp.emuladortelnet.toolbar.ToolbarButton
import org.json.JSONObject
import timber.log.Timber
import java.io.File
import java.net.HttpURLConnection
import java.net.URL

/**
 * Guarda a personalização vinda da EMPRESA (tema + teclas + logo), entregue pela
 * API de licença e cacheada localmente (funciona offline). Quando ativo, o app
 * APLICA e TRAVA — o funcionário não consegue mudar cores/teclas no aparelho.
 *
 * O formato bate com o que o portal da empresa gera:
 *   config = { versao, tema:{cores,cabecalho}, teclas:[[{label,action,color}]], logo_url }
 */
object CompanyConfigStore {

    private const val PREFS = "company_config"
    private const val K_ACTIVE = "active"
    private const val K_VERSAO = "versao"
    private const val K_TEMA = "tema_json"
    private const val K_TECLAS = "teclas_json"
    private const val K_LOGO = "logo_path"
    private const val K_LOGO_VERSAO = "logo_versao"
    private const val LOGO_FILE = "empresa_logo.png"

    private val gson = Gson()
    private fun prefs(c: Context) = c.applicationContext.getSharedPreferences(PREFS, Context.MODE_PRIVATE)

    /** Salva o bloco "config" vindo da API. Vazio/null = limpa (volta ao padrão do app). */
    fun save(context: Context, configJson: String?) {
        val c = context.applicationContext
        if (configJson.isNullOrBlank() || configJson == "null") { clear(c); return }
        try {
            val obj    = JSONObject(configJson)
            val versao = obj.optInt("versao", 0)
            val tema   = obj.optJSONObject("tema")?.toString() ?: ""
            val teclas = obj.optJSONArray("teclas")?.toString() ?: ""
            val logoUrl = obj.optString("logo_url", "")

            prefs(c).edit()
                .putBoolean(K_ACTIVE, true)
                .putInt(K_VERSAO, versao)
                .putString(K_TEMA, tema)
                .putString(K_TECLAS, teclas)
                .apply()

            if (logoUrl.isNotBlank()) baixarLogoSeNecessario(c, logoUrl, versao) else removerLogo(c)
            Timber.d("Config da empresa aplicado (v$versao)")
        } catch (e: Exception) {
            Timber.w(e, "Config da empresa inválido — ignorado")
        }
    }

    fun clear(context: Context) {
        val c = context.applicationContext
        removerLogo(c)
        prefs(c).edit().clear().apply()
    }

    fun isActive(context: Context): Boolean = prefs(context).getBoolean(K_ACTIVE, false)

    /** A empresa definiu um tema (cores/cabeçalho)? Trava a tela de cores no app. */
    fun hasTheme(context: Context): Boolean = isActive(context) && tema(context) != null

    /** A empresa definiu teclas? Trava a tela de configuração das barras no app. */
    fun hasKeys(context: Context): Boolean = isActive(context) && toolbars(context) != null

    // ------------------------------------------------------------------
    // Cores — ARGB Int, ou null quando a empresa não definiu (usa o padrão do app)
    // ------------------------------------------------------------------

    private fun tema(context: Context): JSONObject? {
        val s = prefs(context).getString(K_TEMA, "") ?: ""
        return if (s.isBlank()) null else runCatching { JSONObject(s) }.getOrNull()
    }

    private fun cor(context: Context, key: String): Int? {
        val cores = tema(context)?.optJSONObject("cores") ?: return null
        val hex = cores.optString(key, "")
        return if (hex.isBlank()) null else runCatching { Color.parseColor(hex) }.getOrNull()
    }

    fun colorForeground(c: Context)       = cor(c, "texto")
    fun colorBackground(c: Context)       = cor(c, "fundo")
    fun colorField(c: Context)            = cor(c, "campo")
    fun colorStatusForeground(c: Context) = cor(c, "status_texto")
    fun colorStatusBackground(c: Context) = cor(c, "status_fundo")

    // ------------------------------------------------------------------
    // Cabeçalho (logo + nome)
    // ------------------------------------------------------------------

    fun headerShow(c: Context): Boolean =
        tema(c)?.optJSONObject("cabecalho")?.optBoolean("mostrar", false) ?: false

    fun headerName(c: Context): String =
        tema(c)?.optJSONObject("cabecalho")?.optString("nome", "") ?: ""

    fun logoFile(c: Context): File? {
        val p = prefs(c).getString(K_LOGO, "") ?: ""
        if (p.isBlank()) return null
        val f = File(p)
        return if (f.exists()) f else null
    }

    // ------------------------------------------------------------------
    // Teclas (mesmo formato que AppSettings.toolbars)
    // ------------------------------------------------------------------

    fun toolbars(context: Context): List<List<ToolbarButton>>? {
        val s = prefs(context).getString(K_TECLAS, "") ?: ""
        if (s.isBlank()) return null
        return runCatching {
            val type = object : TypeToken<List<List<ToolbarButton>>>() {}.type
            gson.fromJson<List<List<ToolbarButton>>>(s, type)?.takeIf { it.isNotEmpty() }
        }.getOrNull()
    }

    // ------------------------------------------------------------------
    // Logo (baixada e cacheada em filesDir)
    // ------------------------------------------------------------------

    private fun baixarLogoSeNecessario(c: Context, url: String, versao: Int) {
        val dest = File(c.filesDir, LOGO_FILE)
        val salvo = prefs(c).getInt(K_LOGO_VERSAO, -1)
        if (dest.exists() && salvo == versao) return
        Thread {
            try {
                val conn = (URL(url).openConnection() as HttpURLConnection).apply {
                    connectTimeout = 10_000; readTimeout = 10_000
                }
                conn.inputStream.use { input -> dest.outputStream().use { input.copyTo(it) } }
                prefs(c).edit().putString(K_LOGO, dest.absolutePath).putInt(K_LOGO_VERSAO, versao).apply()
                Timber.d("Logo da empresa baixada (v$versao)")
            } catch (e: Exception) {
                Timber.w(e, "Falha ao baixar logo da empresa")
            }
        }.start()
    }

    private fun removerLogo(c: Context) {
        File(c.filesDir, LOGO_FILE).takeIf { it.exists() }?.delete()
        prefs(c).edit().remove(K_LOGO).remove(K_LOGO_VERSAO).apply()
    }
}
