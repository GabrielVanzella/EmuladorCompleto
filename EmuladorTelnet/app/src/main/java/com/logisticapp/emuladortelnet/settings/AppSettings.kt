package com.logisticapp.emuladortelnet.settings

import android.app.Activity
import android.content.Context
import android.content.pm.ActivityInfo
import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import com.logisticapp.emuladortelnet.toolbar.ToolbarButton
import com.logisticapp.emuladortelnet.toolbar.ToolbarCatalog

/**
 * Configuracoes gerais do app, persistidas em SharedPreferences.
 * Singleton acessado via AppSettings.get(context).
 */
class AppSettings private constructor(context: Context) {

    private val gson = Gson()

    private val prefs = context.applicationContext
        .getSharedPreferences("app_settings", Context.MODE_PRIVATE)

    // Orientacao da tela
    var orientation: Int
        get() = prefs.getInt(K_ORIENTATION, ORIENTATION_AUTO)
        set(v) { prefs.edit().putInt(K_ORIENTATION, v).apply() }

    // Conexao automatica na inicializacao
    var autoConnect: Boolean
        get() = prefs.getBoolean(K_AUTO_CONNECT, false)
        set(v) { prefs.edit().putBoolean(K_AUTO_CONNECT, v).apply() }

    // Reconectar automaticamente apos conexao perdida
    var autoReconnect: Boolean
        get() = prefs.getBoolean(K_AUTO_RECONNECT, false)
        set(v) { prefs.edit().putBoolean(K_AUTO_RECONNECT, v).apply() }

    // Desconectar na tela de bloqueio
    var disconnectOnLock: Boolean
        get() = prefs.getBoolean(K_DISCONNECT_ON_LOCK, false)
        set(v) { prefs.edit().putBoolean(K_DISCONNECT_ON_LOCK, v).apply() }

    // Nunca bloquear a tela quando conectado (manter tela ligada)
    var keepScreenOn: Boolean
        get() = prefs.getBoolean(K_KEEP_SCREEN_ON, false)
        set(v) { prefs.edit().putBoolean(K_KEEP_SCREEN_ON, v).apply() }

    // Ignorar otimizacao de bateria
    var ignoreBattery: Boolean
        get() = prefs.getBoolean(K_IGNORE_BATTERY, false)
        set(v) { prefs.edit().putBoolean(K_IGNORE_BATTERY, v).apply() }

    // Teclado ativado no terminal
    var keyboardEnabled: Boolean
        get() = prefs.getBoolean(K_KEYBOARD_ENABLED, true)
        set(v) { prefs.edit().putBoolean(K_KEYBOARD_ENABLED, v).apply() }

    // ----- Opcoes de tela (Configuracoes > Tela > Opcoes de tela) -----
    var fontSize: Int
        get() = prefs.getInt(K_FONT_SIZE, 12)
        set(v) { prefs.edit().putInt(K_FONT_SIZE, v).apply() }

    var fontName: String
        get() = prefs.getString(K_FONT_NAME, "Padrão") ?: "Padrão"
        set(v) { prefs.edit().putString(K_FONT_NAME, v).apply() }

    var cursorType: String
        get() = prefs.getString(K_CURSOR_TYPE, "Barra") ?: "Barra"
        set(v) { prefs.edit().putString(K_CURSOR_TYPE, v).apply() }

    var cursorColor: String
        get() = prefs.getString(K_CURSOR_COLOR, "Padrão") ?: "Padrão"
        set(v) { prefs.edit().putString(K_CURSOR_COLOR, v).apply() }

    var fields3D: String
        get() = prefs.getString(K_FIELDS_3D, "Ligado sem atributos") ?: "Ligado sem atributos"
        set(v) { prefs.edit().putString(K_FIELDS_3D, v).apply() }

    var cursorBlinking: Boolean
        get() = prefs.getBoolean(K_CURSOR_BLINK, false)
        set(v) { prefs.edit().putBoolean(K_CURSOR_BLINK, v).apply() }

    var fields3DWhiteBg: Boolean
        get() = prefs.getBoolean(K_FIELDS_3D_WHITE, false)
        set(v) { prefs.edit().putBoolean(K_FIELDS_3D_WHITE, v).apply() }

    var showToolbar: String
        get() = prefs.getString(K_SHOW_TOOLBAR, "Automático") ?: "Automático"
        set(v) { prefs.edit().putString(K_SHOW_TOOLBAR, v).apply() }

    var limitView: String
        get() = prefs.getString(K_LIMIT_VIEW, "26,20") ?: "26,20"
        set(v) { prefs.edit().putString(K_LIMIT_VIEW, v).apply() }

    var doubleTapAction: String
        get() = prefs.getString(K_DOUBLE_TAP, "Redefinir tamanho da tela") ?: "Redefinir tamanho da tela"
        set(v) { prefs.edit().putString(K_DOUBLE_TAP, v).apply() }

    // ----- Cores da tela (ARGB). 0 = nao definido (usa padrao) -----
    var colorForeground: Int
        get() = prefs.getInt(K_COLOR_FG, DEFAULT_FG)
        set(v) { prefs.edit().putInt(K_COLOR_FG, v).apply() }

    var colorBackground: Int
        get() = prefs.getInt(K_COLOR_BG, DEFAULT_BG)
        set(v) { prefs.edit().putInt(K_COLOR_BG, v).apply() }

    var colorStatusForeground: Int
        get() = prefs.getInt(K_COLOR_STATUS_FG, DEFAULT_STATUS_FG)
        set(v) { prefs.edit().putInt(K_COLOR_STATUS_FG, v).apply() }

    var colorStatusBackground: Int
        get() = prefs.getInt(K_COLOR_STATUS_BG, 0)   // 0 = padrao
        set(v) { prefs.edit().putInt(K_COLOR_STATUS_BG, v).apply() }

    var colorInputField: Int
        get() = prefs.getInt(K_COLOR_INPUT_FIELD, 0) // 0 = reverso natural
        set(v) { prefs.edit().putInt(K_COLOR_INPUT_FIELD, v).apply() }

    // ----- Ajuste de cor (0 = automático) -----
    var colorFgDark: Int
        get() = prefs.getInt(K_COLOR_FG_DARK, 0)
        set(v) { prefs.edit().putInt(K_COLOR_FG_DARK, v).apply() }

    var colorFgBright: Int
        get() = prefs.getInt(K_COLOR_FG_BRIGHT, 0)
        set(v) { prefs.edit().putInt(K_COLOR_FG_BRIGHT, v).apply() }

    var colorBgAdjust: Int
        get() = prefs.getInt(K_COLOR_BG_ADJUST, 0)
        set(v) { prefs.edit().putInt(K_COLOR_BG_ADJUST, v).apply() }

    // ----- VT Mapeamento de atributos -----
    var vtAttrMap: VtAttrMapOptions
        get() {
            val json = prefs.getString(K_VT_ATTR_MAP, null) ?: return VtAttrMapOptions()
            return try {
                gson.fromJson(json, VtAttrMapOptions::class.java) ?: VtAttrMapOptions()
            } catch (e: Exception) {
                VtAttrMapOptions()
            }
        }
        set(v) { prefs.edit().putString(K_VT_ATTR_MAP, gson.toJson(v)).apply() }

    // ----- Modelos de sessao -----
    var sessionTemplates: List<SessionTemplate>
        get() {
            val json = prefs.getString(K_SESSION_TEMPLATES, null) ?: return emptyList()
            return try {
                val type = object : TypeToken<List<SessionTemplate>>() {}.type
                gson.fromJson<List<SessionTemplate>>(json, type) ?: emptyList()
            } catch (e: Exception) {
                emptyList()
            }
        }
        set(v) { prefs.edit().putString(K_SESSION_TEMPLATES, gson.toJson(v)).apply() }

    // ----- Barras de ferramentas (4 barras de botoes) -----
    var toolbars: List<List<ToolbarButton>>
        get() {
            val json = prefs.getString(K_TOOLBARS, null) ?: return ToolbarCatalog.defaultToolbars
            return try {
                val type = object : TypeToken<List<List<ToolbarButton>>>() {}.type
                gson.fromJson<List<List<ToolbarButton>>>(json, type) ?: ToolbarCatalog.defaultToolbars
            } catch (e: Exception) {
                ToolbarCatalog.defaultToolbars
            }
        }
        set(v) { prefs.edit().putString(K_TOOLBARS, gson.toJson(v)).apply() }

    fun addButtonToBar(barIndex: Int, button: ToolbarButton) {
        val bars = toolbars.map { it.toMutableList() }.toMutableList()
        if (barIndex in bars.indices) {
            bars[barIndex].add(button)
            toolbars = bars
        }
    }

    fun removeButton(barIndex: Int, buttonIndex: Int) {
        val bars = toolbars.map { it.toMutableList() }.toMutableList()
        if (barIndex in bars.indices && buttonIndex in bars[barIndex].indices) {
            bars[barIndex].removeAt(buttonIndex)
            toolbars = bars
        }
    }

    fun renameButton(barIndex: Int, buttonIndex: Int, newLabel: String) {
        val bars = toolbars.map { it.toMutableList() }.toMutableList()
        if (barIndex in bars.indices && buttonIndex in bars[barIndex].indices) {
            val old = bars[barIndex][buttonIndex]
            bars[barIndex][buttonIndex] = ToolbarButton(newLabel, old.action, old.color)
            toolbars = bars
        }
    }

    fun recolorButton(barIndex: Int, buttonIndex: Int, color: String) {
        val bars = toolbars.map { it.toMutableList() }.toMutableList()
        if (barIndex in bars.indices && buttonIndex in bars[barIndex].indices) {
            val old = bars[barIndex][buttonIndex]
            bars[barIndex][buttonIndex] = ToolbarButton(old.label, old.action, color)
            toolbars = bars
        }
    }

    fun addBar() {
        val bars = toolbars.map { it.toMutableList() }.toMutableList()
        if (bars.size < 6) { bars.add(mutableListOf()); toolbars = bars }
    }

    fun removeBar(barIndex: Int) {
        val bars = toolbars.map { it.toMutableList() }.toMutableList()
        if (bars.size > 1 && barIndex in bars.indices) { bars.removeAt(barIndex); toolbars = bars }
    }

    fun moveBarUp(barIndex: Int) {
        val bars = toolbars.map { it.toMutableList() }.toMutableList()
        if (barIndex > 0 && barIndex in bars.indices) {
            val tmp = bars[barIndex]; bars[barIndex] = bars[barIndex - 1]; bars[barIndex - 1] = tmp
            toolbars = bars
        }
    }

    fun moveBarDown(barIndex: Int) {
        val bars = toolbars.map { it.toMutableList() }.toMutableList()
        if (barIndex < bars.size - 1 && barIndex in bars.indices) {
            val tmp = bars[barIndex]; bars[barIndex] = bars[barIndex + 1]; bars[barIndex + 1] = tmp
            toolbars = bars
        }
    }

    // ----- Telnet Opcoes -----
    var telnetOptions: TelnetOptions
        get() {
            val json = prefs.getString(K_TELNET_OPTIONS, null) ?: return TelnetOptions()
            return try {
                gson.fromJson(json, TelnetOptions::class.java) ?: TelnetOptions()
            } catch (e: Exception) {
                TelnetOptions()
            }
        }
        set(v) { prefs.edit().putString(K_TELNET_OPTIONS, gson.toJson(v)).apply() }

    // ----- VT Opcoes -----
    var vtOptions: VtOptions
        get() {
            val json = prefs.getString(K_VT_OPTIONS, null) ?: return VtOptions()
            return try {
                gson.fromJson(json, VtOptions::class.java) ?: VtOptions()
            } catch (e: Exception) {
                VtOptions()
            }
        }
        set(v) { prefs.edit().putString(K_VT_OPTIONS, gson.toJson(v)).apply() }

    // ----- Configuracao do leitor de codigo de barras -----
    var barcodeOptions: BarcodeOptions
        get() {
            val json = prefs.getString(K_BARCODE_OPTIONS, null) ?: return BarcodeOptions()
            return try {
                gson.fromJson(json, BarcodeOptions::class.java) ?: BarcodeOptions()
            } catch (e: Exception) {
                BarcodeOptions()
            }
        }
        set(v) { prefs.edit().putString(K_BARCODE_OPTIONS, gson.toJson(v)).apply() }

    // ----- Configuracao de impressao -----
    var printOptions: PrintOptions
        get() {
            val json = prefs.getString(K_PRINT_OPTIONS, null) ?: return PrintOptions()
            return try {
                gson.fromJson(json, PrintOptions::class.java) ?: PrintOptions()
            } catch (e: Exception) {
                PrintOptions()
            }
        }
        set(v) { prefs.edit().putString(K_PRINT_OPTIONS, gson.toJson(v)).apply() }

    // ----- Geral de emulacao -----
    var generalEmulationOptions: GeneralEmulationOptions
        get() {
            val json = prefs.getString(K_GENERAL_EMULATION, null) ?: return GeneralEmulationOptions()
            return try {
                gson.fromJson(json, GeneralEmulationOptions::class.java) ?: GeneralEmulationOptions()
            } catch (e: Exception) {
                GeneralEmulationOptions()
            }
        }
        set(v) { prefs.edit().putString(K_GENERAL_EMULATION, gson.toJson(v)).apply() }

    // ----- Teclado personalizado (IME) -----
    var keyboardSettings: KeyboardSettings
        get() {
            val json = prefs.getString(K_KEYBOARD_SETTINGS, null) ?: return KeyboardSettings()
            return try {
                gson.fromJson(json, KeyboardSettings::class.java) ?: KeyboardSettings()
            } catch (e: Exception) {
                KeyboardSettings()
            }
        }
        set(v) { prefs.edit().putString(K_KEYBOARD_SETTINGS, gson.toJson(v)).apply() }

    fun addKeyboardPage(page: List<ToolbarButton>) {
        val s = keyboardSettings
        s.extraPages = s.extraPages + listOf(page)
        keyboardSettings = s
    }

    fun removeKeyboardPage(index: Int) {
        val s = keyboardSettings
        s.extraPages = s.extraPages.toMutableList().also { it.removeAt(index) }
        keyboardSettings = s
    }

    // ----- Transliteracao -----
    var transliterationOptions: TransliterationOptions
        get() {
            val json = prefs.getString(K_TRANSLIT_OPTIONS, null) ?: return TransliterationOptions()
            return try {
                gson.fromJson(json, TransliterationOptions::class.java) ?: TransliterationOptions()
            } catch (e: Exception) {
                TransliterationOptions()
            }
        }
        set(v) { prefs.edit().putString(K_TRANSLIT_OPTIONS, gson.toJson(v)).apply() }

    // ----- Servidor proxy -----
    var proxyOptions: ProxyOptions
        get() {
            val json = prefs.getString(K_PROXY_OPTIONS, null) ?: return ProxyOptions()
            return try {
                gson.fromJson(json, ProxyOptions::class.java) ?: ProxyOptions()
            } catch (e: Exception) {
                ProxyOptions()
            }
        }
        set(v) { prefs.edit().putString(K_PROXY_OPTIONS, gson.toJson(v)).apply() }

    /** Aplica a orientacao escolhida na Activity informada. */
    fun applyOrientation(activity: Activity) {
        activity.requestedOrientation = when (orientation) {
            ORIENTATION_PORTRAIT  -> ActivityInfo.SCREEN_ORIENTATION_PORTRAIT
            ORIENTATION_LANDSCAPE -> ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE
            else                  -> ActivityInfo.SCREEN_ORIENTATION_UNSPECIFIED
        }
    }

    companion object {
        const val ORIENTATION_AUTO = 0
        const val ORIENTATION_PORTRAIT = 1
        const val ORIENTATION_LANDSCAPE = 2

        private const val K_ORIENTATION = "orientation"
        private const val K_AUTO_CONNECT = "auto_connect"
        private const val K_AUTO_RECONNECT = "auto_reconnect"
        private const val K_DISCONNECT_ON_LOCK = "disconnect_on_lock"
        private const val K_KEEP_SCREEN_ON = "keep_screen_on"
        private const val K_IGNORE_BATTERY = "ignore_battery"
        private const val K_KEYBOARD_ENABLED = "keyboard_enabled"

        // Opcoes de tela
        private const val K_FONT_SIZE = "font_size"
        private const val K_FONT_NAME = "font_name"
        private const val K_CURSOR_TYPE = "cursor_type"
        private const val K_CURSOR_COLOR = "cursor_color"
        private const val K_FIELDS_3D = "fields_3d"
        private const val K_CURSOR_BLINK = "cursor_blink"
        private const val K_FIELDS_3D_WHITE = "fields_3d_white"
        private const val K_SHOW_TOOLBAR = "show_toolbar"
        private const val K_LIMIT_VIEW = "limit_view"
        private const val K_DOUBLE_TAP = "double_tap"

        // Cores da tela
        private const val K_COLOR_FG = "color_fg"
        private const val K_COLOR_BG = "color_bg"
        private const val K_COLOR_STATUS_FG = "color_status_fg"
        private const val K_COLOR_STATUS_BG = "color_status_bg"
        private const val K_COLOR_INPUT_FIELD = "color_input_field"
        private const val K_COLOR_FG_DARK = "color_fg_dark"
        private const val K_COLOR_FG_BRIGHT = "color_fg_bright"
        private const val K_COLOR_BG_ADJUST = "color_bg_adjust"
        private const val K_VT_ATTR_MAP = "vt_attr_map"
        private const val K_SESSION_TEMPLATES = "session_templates"

        // Barras de ferramentas
        private const val K_TOOLBARS = "toolbars"

        // Telnet Opcoes
        private const val K_TELNET_OPTIONS = "telnet_options"

        // VT Opcoes
        private const val K_VT_OPTIONS = "vt_options"

        // Configuracao do leitor de codigo de barras
        private const val K_BARCODE_OPTIONS = "barcode_options"

        // Configuracao de impressao
        private const val K_PRINT_OPTIONS = "print_options"

        // Geral de emulacao
        private const val K_GENERAL_EMULATION = "general_emulation"

        // Teclado personalizado (IME)
        private const val K_KEYBOARD_SETTINGS = "keyboard_settings"

        // Transliteracao
        private const val K_TRANSLIT_OPTIONS = "translit_options"

        // Servidor proxy
        private const val K_PROXY_OPTIONS = "proxy_options"

        // Cores padrao (ARGB)
        const val DEFAULT_FG = 0xFF00FF00.toInt()        // verde
        const val DEFAULT_BG = 0xFF000000.toInt()        // preto
        const val DEFAULT_STATUS_FG = 0xFF00FFFF.toInt() // ciano

        @Volatile private var instance: AppSettings? = null

        fun get(context: Context): AppSettings =
            instance ?: synchronized(this) {
                instance ?: AppSettings(context).also { instance = it }
            }
    }
}
