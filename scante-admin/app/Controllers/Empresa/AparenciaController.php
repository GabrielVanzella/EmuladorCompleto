<?php
namespace App\Controllers\Empresa;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Empresa;

class AparenciaController extends Controller {

    /** Presets de tema prontos (cores em hex; "" = deixa o padrão/natural do app). */
    private const PRESETS = [
        'classico'       => ['texto' => '#33FF33', 'fundo' => '#000000', 'campo' => '',        'status_texto' => '#FFFFFF', 'status_fundo' => ''],
        'escuro'         => ['texto' => '#E6E6E6', 'fundo' => '#101418', 'campo' => '#1E2A33', 'status_texto' => '#FFFFFF', 'status_fundo' => '#0F2A3D'],
        'claro'          => ['texto' => '#1A1A1A', 'fundo' => '#F5F5F5', 'campo' => '#E3ECF5', 'status_texto' => '#FFFFFF', 'status_fundo' => '#0F2A3D'],
        'alto_contraste' => ['texto' => '#FFFF00', 'fundo' => '#000000', 'campo' => '#003300', 'status_texto' => '#000000', 'status_fundo' => '#FFFF00'],
    ];

    public function index(): void {
        Auth::requireEmpresa();
        $empresa = (new Empresa())->findById((int)Auth::empresaId());

        $tema = json_decode($empresa['config_tema'] ?? '', true) ?: $this->temaPadrao();
        $logoUrl = $this->logoUrl((int)$empresa['id'], (int)($empresa['config_versao'] ?? 0));

        $this->view('empresa.aparencia.index', [
            'empresa'  => $empresa,
            'tema'     => $tema,
            'presets'  => self::PRESETS,
            'logoUrl'  => $logoUrl,
            'flash'    => $this->getFlash(),
        ], 'empresa');
    }

    public function salvar(): void {
        Auth::requireEmpresa();
        if (!$this->isPost()) { $this->redirect('/empresa/aparencia'); }

        $empresaId = (int)Auth::empresaId();

        $tema = [
            'preset' => $this->sanitize($this->input('preset', 'classico')),
            'cores'  => [
                'texto'        => $this->corHex($this->input('cor_texto', '#33FF33')),
                'fundo'        => $this->corHex($this->input('cor_fundo', '#000000')),
                'campo'        => $this->corHex($this->input('cor_campo', '')),
                'status_texto' => $this->corHex($this->input('cor_status_texto', '#FFFFFF')),
                'status_fundo' => $this->corHex($this->input('cor_status_fundo', '')),
            ],
            'cabecalho' => [
                'mostrar' => $this->input('cabecalho_mostrar', '0') === '1',
                'nome'    => $this->sanitize($this->input('cabecalho_nome', '')),
            ],
        ];

        $empresaModel = new Empresa();
        $empresaModel->salvarTema($empresaId, json_encode($tema, JSON_UNESCAPED_UNICODE));

        // Upload/remoção da logo
        $this->tratarLogo($empresaId, $empresaModel);

        $this->flash('success', 'Aparência salva! Os coletores vão aplicar na próxima conexão.');
        $this->redirect('/empresa/aparencia');
    }

    // ------------------------------------------------------------------

    private function tratarLogo(int $empresaId, Empresa $model): void {
        $dir  = __DIR__ . '/../../../public/uploads/logos';
        $path = $dir . '/' . $empresaId . '.png';

        // Remover logo
        if ($this->input('remover_logo', '0') === '1') {
            if (is_file($path)) @unlink($path);
            $model->bumpConfigVersao($empresaId);
            return;
        }

        // Upload nova logo
        if (!empty($_FILES['logo']['tmp_name']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $img = $this->carregarImagem($_FILES['logo']['tmp_name']);
            if ($img === null) {
                $this->flash('error', 'Logo inválida — envie PNG, JPG ou WEBP.');
                return;
            }
            // Redimensiona pra no máximo 512px de largura, salva como PNG (com transparência)
            $img = $this->redimensionar($img, 512);
            imagepng($img, $path);
            imagedestroy($img);
            $model->bumpConfigVersao($empresaId);
        }
    }

    private function carregarImagem(string $tmp): ?\GdImage {
        $info = @getimagesize($tmp);
        if (!$info) return null;
        return match ($info[2]) {
            IMAGETYPE_PNG  => @imagecreatefrompng($tmp) ?: null,
            IMAGETYPE_JPEG => @imagecreatefromjpeg($tmp) ?: null,
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($tmp) ?: null) : null,
            default        => null,
        };
    }

    private function redimensionar(\GdImage $src, int $maxW): \GdImage {
        $w = imagesx($src); $h = imagesy($src);
        if ($w <= $maxW) return $src;
        $novoH = (int)round($h * ($maxW / $w));
        $dst = imagecreatetruecolor($maxW, $novoH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $maxW, $novoH, $w, $h);
        imagedestroy($src);
        return $dst;
    }

    private function logoUrl(int $empresaId, int $versao): ?string {
        $path = __DIR__ . '/../../../public/uploads/logos/' . $empresaId . '.png';
        if (!is_file($path)) return null;
        return APP_URL . '/uploads/logos/' . $empresaId . '.png?v=' . $versao;
    }

    /** Normaliza um valor de cor: "#RRGGBB" válido, ou "" (deixa padrão). */
    private function corHex(mixed $v): string {
        $v = trim((string)$v);
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $v) ? strtoupper($v) : '';
    }

    private function temaPadrao(): array {
        return [
            'preset'    => 'classico',
            'cores'     => self::PRESETS['classico'],
            'cabecalho' => ['mostrar' => false, 'nome' => ''],
        ];
    }
}
