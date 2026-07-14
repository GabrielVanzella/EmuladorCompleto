<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Licenca;
use App\Models\Empresa;

class LicencasController extends Controller {

    public function index(): void {
        Auth::requireAdmin();
        $model    = new Licenca();
        $licencas = $model->comEmpresa();
        $empresas = (new Empresa())->findAll('nome ASC');
        $stats    = $model->estatisticas();
        $this->view('admin.licencas.index', [
            'licencas' => $licencas,
            'empresas' => $empresas,
            'stats'    => $stats,
            'flash'    => $this->getFlash(),
        ], 'admin');
    }

    public function gerar(): void {
        Auth::requireAdmin();
        if ($this->isPost()) {
            $empresaId = (int)$this->input('empresa_id');
            $tipo      = $this->input('tipo', 'trial');
            $quantidade = min((int)$this->input('quantidade', 1), 50);
            $dias      = (int)$this->input('dias', 30);

            $model = new Licenca();
            for ($i = 0; $i < $quantidade; $i++) {
                $model->gerar($empresaId, $tipo, $dias);
            }

            $this->flash('success', "$quantidade licença(s) gerada(s) com sucesso.");
        }
        $this->redirect('/admin/licencas');
    }

    public function ver(string $id): void {
        Auth::requireAdmin();
        $licenca  = (new Licenca())->findById((int)$id);
        if (!$licenca) { $this->redirect('/admin/licencas'); }

        $historico = (new Licenca())->historico((int)$id);

        $this->view('admin.licencas.ver', [
            'licenca'   => $licenca,
            'historico' => $historico,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    /** Aprova uma solicitação/compra do checkout em lote: só aqui as chaves
     *  passam a valer de fato — ativa a licença mestre e gera as demais da
     *  mesma quantidade (ver Licenca::aprovar). */
    public function aprovar(string $id): void {
        Auth::requireAdmin();
        $model   = new Licenca();
        $licenca = $model->findById((int)$id);

        if (!$licenca || $licenca['status'] !== 'pendente') {
            $this->flash('warning', 'Essa solicitação já foi processada ou não existe mais.');
            $this->redirect('/admin/licencas/' . $id);
            return;
        }

        $model->aprovar((int)$id);

        $qtd = max(1, (int)$licenca['quantidade']);
        $this->flash('success', "Solicitação aprovada! $qtd licença(s) gerada(s).");
        $this->redirect('/admin/licencas/' . $id);
    }

    public function revogar(string $id): void {
        Auth::requireAdmin();
        (new Licenca())->revogar((int)$id);
        $this->flash('warning', 'Licença revogada.');
        $this->redirect('/admin/licencas/' . $id);
    }

    public function reativar(string $id): void {
        Auth::requireAdmin();
        (new Licenca())->reativar((int)$id);
        $this->flash('success', 'Licença reativada.');
        $this->redirect('/admin/licencas/' . $id);
    }

    public function estender(string $id): void {
        Auth::requireAdmin();
        if ($this->isPost()) {
            $dias = (int)$this->input('dias', 30);
            (new Licenca())->estender((int)$id, $dias);
            $this->flash('success', "Licença estendida em $dias dias.");
        }
        $this->redirect('/admin/licencas/' . $id);
    }

    public function alterarTipo(string $id): void {
        Auth::requireAdmin();
        if ($this->isPost()) {
            $novoTipo = $this->input('tipo');
            $dias     = ($novoTipo !== 'vitalicia' && $this->input('dias')) ? (int)$this->input('dias') : null;
            (new Licenca())->alterarTipo((int)$id, $novoTipo, $dias);
            $this->flash('success', 'Tipo da licença alterado para ' . ucfirst($novoTipo) . '.');
        }
        $this->redirect('/admin/licencas/' . $id);
    }

    public function transferir(string $id): void {
        Auth::requireAdmin();
        if ($this->isPost()) {
            $motivo = $this->sanitize($this->input('motivo', 'Transferência manual pelo admin'));
            (new Licenca())->transferir((int)$id, $motivo, Auth::id());
            $this->flash('success', 'Dispositivo desvinculado. A licença pode ser usada em outro aparelho.');
        }
        $this->redirect('/admin/licencas/' . $id);
    }
}
