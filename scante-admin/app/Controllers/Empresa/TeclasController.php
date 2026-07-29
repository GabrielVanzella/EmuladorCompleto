<?php
namespace App\Controllers\Empresa;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Empresa;

/**
 * Editor das barras de ferramentas (teclas) do app, por empresa.
 * O formato salvo é idêntico ao que o app guarda: uma lista de barras,
 * cada barra é uma lista de botões { label, action, color }.
 * O catálogo aqui espelha o ToolbarCatalog do app (ToolbarModel.kt).
 */
class TeclasController extends Controller {

    private const MAX_BARS = 6;

    /** Catálogo de ações prontas, por seção (espelha availableSections do app). */
    private function catalogo(): array {
        $ctrl = [];
        foreach (range('A', 'Z') as $l) $ctrl[] = ['label' => "Ctrl+$l", 'action' => "CTRL_$l"];
        $fkeys = [];
        for ($i = 1; $i <= 12; $i++) $fkeys[] = ['label' => "F$i", 'action' => "F$i"];

        return [
            'Navegação' => [
                ['label' => '↑', 'action' => 'UP'],   ['label' => '↓', 'action' => 'DOWN'],
                ['label' => '←', 'action' => 'LEFT'], ['label' => '→', 'action' => 'RIGHT'],
                ['label' => 'Home', 'action' => 'HOME'], ['label' => 'End', 'action' => 'END'],
                ['label' => 'PgUp', 'action' => 'PREVS'], ['label' => 'PgDn', 'action' => 'NEXTS'],
            ],
            'Edição' => [
                ['label' => 'Esc', 'action' => 'ESC'], ['label' => 'Enter', 'action' => 'ENTER'],
                ['label' => 'Tab', 'action' => 'TAB'], ['label' => '⇤', 'action' => 'BACKTAB'],
                ['label' => '⌫', 'action' => 'BACKSPACE'], ['label' => 'Del', 'action' => 'DELCHAR'],
                ['label' => 'Ins', 'action' => 'INSERT'], ['label' => 'Copy', 'action' => 'COPY'],
                ['label' => 'Paste', 'action' => 'PASTE'],
            ],
            'Teclas de função' => $fkeys,
            'Ctrl' => $ctrl,
            'Conexão' => [
                ['label' => 'Conn.', 'action' => 'CONNECT'],
                ['label' => 'Break', 'action' => 'BREAK'],
                ['label' => 'Desc.', 'action' => 'DISCONNECT'],
            ],
        ];
    }

    /** Barras padrão (espelha defaultToolbars do app) — ponto de partida se a empresa nunca configurou. */
    private function barrasPadrao(): array {
        $nums = [];
        foreach (['1','2','3','4','5','6','7','8','9','0'] as $n) $nums[] = ['label' => $n, 'action' => "TEXT:$n", 'color' => ''];
        return [
            $nums,
            [
                ['label' => '↑', 'action' => 'UP', 'color' => ''],   ['label' => '↓', 'action' => 'DOWN', 'color' => ''],
                ['label' => '←', 'action' => 'LEFT', 'color' => ''], ['label' => '→', 'action' => 'RIGHT', 'color' => ''],
                ['label' => 'Esc', 'action' => 'ESC', 'color' => ''], ['label' => 'Enter', 'action' => 'ENTER', 'color' => ''],
                ['label' => 'Tab', 'action' => 'TAB', 'color' => ''], ['label' => '⌫', 'action' => 'BACKSPACE', 'color' => ''],
                ['label' => 'Del', 'action' => 'DELCHAR', 'color' => ''],
            ],
            [
                ['label' => 'Ctrl+C', 'action' => 'CTRL_C', 'color' => ''], ['label' => 'Ctrl+I', 'action' => 'CTRL_I', 'color' => ''],
                ['label' => 'Ctrl+E', 'action' => 'CTRL_E', 'color' => ''], ['label' => 'Ctrl+K', 'action' => 'CTRL_K', 'color' => ''],
                ['label' => 'Ctrl+P', 'action' => 'CTRL_P', 'color' => ''], ['label' => 'Ctrl+Y', 'action' => 'CTRL_Y', 'color' => ''],
            ],
        ];
    }

    public function index(): void {
        Auth::requireEmpresa();
        $empresa = (new Empresa())->findById((int)Auth::empresaId());

        $barras = json_decode($empresa['config_teclas'] ?? '', true);
        if (!is_array($barras) || empty($barras)) $barras = $this->barrasPadrao();

        $this->view('empresa.teclas.index', [
            'empresa'  => $empresa,
            'barras'   => $barras,
            'catalogo' => $this->catalogo(),
            'maxBars'  => self::MAX_BARS,
            'flash'    => $this->getFlash(),
        ], 'empresa');
    }

    public function salvar(): void {
        Auth::requireEmpresa();
        if (!$this->isPost()) { $this->redirect('/empresa/teclas'); }

        $raw    = (string)$this->input('barras_json', '');
        $barras = json_decode($raw, true);

        if (!is_array($barras)) {
            $this->flash('error', 'Não foi possível salvar as teclas (dados inválidos).');
            $this->redirect('/empresa/teclas');
        }

        // Sanitiza: mantém só barras/botões válidos e limita a MAX_BARS
        $limpo = [];
        foreach (array_slice($barras, 0, self::MAX_BARS) as $barra) {
            if (!is_array($barra)) continue;
            $botoes = [];
            foreach ($barra as $b) {
                $label  = trim((string)($b['label']  ?? ''));
                $action = trim((string)($b['action'] ?? ''));
                $color  = trim((string)($b['color']  ?? ''));
                if ($label === '' || $action === '') continue;
                if (!preg_match('/^#[0-9A-Fa-f]{8}$/', $color) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) $color = '';
                $botoes[] = [
                    'label'  => mb_substr($label, 0, 12),
                    'action' => mb_substr($action, 0, 40),
                    'color'  => $color,
                ];
            }
            $limpo[] = $botoes;
        }

        (new Empresa())->salvarTeclas((int)Auth::empresaId(), json_encode($limpo, JSON_UNESCAPED_UNICODE));
        $this->flash('success', 'Teclas salvas! Os coletores vão aplicar na próxima conexão.');
        $this->redirect('/empresa/teclas');
    }
}
