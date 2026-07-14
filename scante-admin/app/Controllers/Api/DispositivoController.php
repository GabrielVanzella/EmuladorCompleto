<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Dispositivo;

class DispositivoController extends Controller {

    private function autenticar(): void {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_X_API_KEY'] ?? '';
        $token  = str_replace('Bearer ', '', $header);
        if ($token !== API_SECRET) {
            $this->json(['erro' => 'Não autorizado.'], 401);
        }
    }

    /** POST /api/dispositivo/ping — chamado pelo app a cada abertura */
    public function ping(): void {
        $this->autenticar();
        $body       = json_decode(file_get_contents('php://input'), true) ?? [];
        $deviceId   = trim($body['device_id']   ?? '');
        $deviceNome = trim($body['device_nome']  ?? '');
        $appVersion = trim($body['app_version']  ?? '');
        $chave      = trim($body['license_key']  ?? '') ?: null;

        if (!$deviceId) {
            $this->json(['ok' => false, 'erro' => 'device_id obrigatório.'], 400);
        }

        (new Dispositivo())->ping($deviceId, $deviceNome, $appVersion, $chave);
        $this->json(['ok' => true]);
    }
}
