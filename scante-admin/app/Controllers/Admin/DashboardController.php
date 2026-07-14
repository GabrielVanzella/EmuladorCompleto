<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Licenca;
use App\Models\Empresa;
use App\Models\Usuario;

class DashboardController extends Controller {

    public function index(): void {
        Auth::requireAdmin();

        $licenca = new Licenca();
        $empresa = new Empresa();

        $stats     = $licenca->estatisticas();
        $empresas  = $empresa->count();
        $recentes  = (new Licenca())->comEmpresa();
        $recentes  = array_slice($recentes, 0, 10);

        $this->view('admin.dashboard', [
            'stats'    => $stats,
            'empresas' => $empresas,
            'recentes' => $recentes,
            'flash'    => $this->getFlash(),
        ], 'admin');
    }
}
