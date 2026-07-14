<?php
namespace App\Controllers\Empresa;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Licenca;
use App\Models\Empresa;

class DashboardController extends Controller {

    public function index(): void {
        Auth::requireEmpresa();
        $empresaId = Auth::empresaId();
        $empresa   = (new Empresa())->findById($empresaId);
        $licencas  = (new Licenca())->findByEmpresa($empresaId);

        $ativas    = array_filter($licencas, fn($l) => $l['status'] === 'ativa');
        $trial     = array_filter($licencas, fn($l) => $l['status'] === 'trial');
        $expiradas = array_filter($licencas, fn($l) => $l['status'] === 'expirada');

        $this->view('empresa.dashboard', [
            'empresa'   => $empresa,
            'licencas'  => $licencas,
            'ativas'    => count($ativas),
            'trial'     => count($trial),
            'expiradas' => count($expiradas),
            'flash'     => $this->getFlash(),
        ], 'empresa');
    }
}
