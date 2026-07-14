<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Dispositivo;
use App\Models\Empresa;
use App\Models\Licenca;

class DispositivosController extends Controller {

    public function index(): void {
        Auth::requireAdmin();
        $model = new Dispositivo();
        $this->view('admin.dispositivos.index', [
            'dispositivos'        => $model->listar(),
            'stats'               => $model->estatisticas(),
            'empresas'            => (new Empresa())->ativas(),
            'licencasDisponiveis' => (new Licenca())->disponiveis(),
            'flash'               => $this->getFlash(),
        ], 'admin');
    }

    public function atribuirEmpresa(): void {
        Auth::requireAdmin();
        $deviceId  = trim((string)$this->input('device_id', ''));
        $empresaId = (int)$this->input('empresa_id', 0);

        if ($deviceId === '' || $empresaId <= 0) {
            $this->flash('danger', 'Selecione uma empresa.');
            $this->redirect('/admin/dispositivos');
        }

        $empresa = (new Empresa())->findById($empresaId);
        if (!$empresa) {
            $this->flash('danger', 'Empresa não encontrada.');
            $this->redirect('/admin/dispositivos');
        }

        (new Dispositivo())->atribuirEmpresa($deviceId, $empresaId, $empresa['nome']);
        $this->flash('success', 'Empresa "' . $empresa['nome'] . '" atribuída ao dispositivo.');
        $this->redirect('/admin/dispositivos');
    }

    public function atribuirLicenca(): void {
        Auth::requireAdmin();
        $deviceId  = trim((string)$this->input('device_id', ''));
        $licencaId = (int)$this->input('licenca_id', 0);

        if ($deviceId === '' || $licencaId <= 0) {
            $this->flash('danger', 'Selecione uma licença.');
            $this->redirect('/admin/dispositivos');
        }

        $licModel = new Licenca();
        $lic = $licModel->findByIdComEmpresa($licencaId);
        if (!$lic) {
            $this->flash('danger', 'Licença não encontrada.');
            $this->redirect('/admin/dispositivos');
        }
        if (!empty($lic['device_id']) && $lic['device_id'] !== $deviceId) {
            $this->flash('danger', 'Essa licença já está vinculada a outro dispositivo.');
            $this->redirect('/admin/dispositivos');
        }

        $dispModel = new Dispositivo();
        $disp = $dispModel->findBy('device_id', $deviceId);
        if (!$disp) {
            $this->flash('danger', 'Dispositivo não encontrado.');
            $this->redirect('/admin/dispositivos');
        }

        $licModel->vincularDispositivo($licencaId, $deviceId, $disp['device_nome'] ?? '');
        $dispModel->sincronizarLicenca($deviceId, $lic);
        $this->flash('success', 'Licença ' . $lic['chave'] . ' atribuída ao dispositivo.');
        $this->redirect('/admin/dispositivos');
    }

    public function excluir(): void {
        Auth::requireAdmin();
        $deviceId = trim((string)$this->input('device_id', ''));
        if ($deviceId !== '') {
            (new Dispositivo())->excluir($deviceId);
            $this->flash('success', 'Dispositivo removido.');
        }
        $this->redirect('/admin/dispositivos');
    }
}
