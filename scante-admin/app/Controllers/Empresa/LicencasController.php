<?php
namespace App\Controllers\Empresa;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Licenca;

class LicencasController extends Controller {

    public function index(): void {
        Auth::requireEmpresa();
        $licencas = (new Licenca())->findByEmpresa(Auth::empresaId());
        $this->view('empresa.licencas.index', [
            'licencas' => $licencas,
            'flash'    => $this->getFlash(),
        ], 'empresa');
    }

    public function ver(string $id): void {
        Auth::requireEmpresa();
        $licenca = (new Licenca())->findById((int)$id);

        // Garante que a licença pertence à empresa logada
        if (!$licenca || $licenca['empresa_id'] != Auth::empresaId()) {
            $this->redirect('/empresa/licencas');
        }

        $historico = (new Licenca())->historico((int)$id);

        $this->view('empresa.licencas.ver', [
            'licenca'   => $licenca,
            'historico' => $historico,
            'flash'     => $this->getFlash(),
        ], 'empresa');
    }

    public function solicitarTransferencia(string $id): void {
        Auth::requireEmpresa();
        $licenca = (new Licenca())->findById((int)$id);

        if (!$licenca || $licenca['empresa_id'] != Auth::empresaId()) {
            $this->redirect('/empresa/licencas');
        }

        if ($this->isPost()) {
            $motivo = $this->sanitize($this->input('motivo', ''));
            if (!$motivo) {
                $this->flash('error', 'Informe o motivo da transferência.');
                $this->redirect('/empresa/licencas/' . $id);
            }

            (new Licenca())->transferir((int)$id, $motivo, Auth::id());
            $this->flash('success', 'Transferência realizada! A licença pode ser ativada em outro dispositivo.');
        }

        $this->redirect('/empresa/licencas/' . $id);
    }
}
