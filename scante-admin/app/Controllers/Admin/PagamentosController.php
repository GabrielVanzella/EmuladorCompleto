<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Pagamento;
use App\Models\Licenca;
use App\Models\Empresa;

class PagamentosController extends Controller {

    public function index(): void {
        Auth::requireAdmin();

        $filtros = [
            'empresa_id' => $this->input('empresa_id'),
            'status'     => $this->input('status'),
            'tipo'       => $this->input('tipo'),
            'de'         => $this->input('de'),
            'ate'        => $this->input('ate'),
        ];
        $filtros = array_filter($filtros);

        $model      = new Pagamento();
        $pagamentos = $model->todos($filtros);
        $stats      = $model->estatisticas($filtros);
        $porEmpresa = $model->porEmpresa();
        $empresas   = (new Empresa())->findAll('nome ASC');

        $this->view('admin.pagamentos.index', [
            'pagamentos' => $pagamentos,
            'stats'      => $stats,
            'porEmpresa' => $porEmpresa,
            'empresas'   => $empresas,
            'filtros'    => $filtros,
            'flash'      => $this->getFlash(),
        ], 'admin');
    }

    public function registrar(): void {
        Auth::requireAdmin();
        if ($this->isPost()) {
            $licencaId = (int)$this->input('licenca_id');
            $valor     = (float)str_replace(',', '.', $this->input('valor', '0'));
            $tipo      = $this->input('tipo', 'manual');
            $status    = $this->input('status', 'approved');

            if ($licencaId && $valor > 0) {
                (new Pagamento())->registrar($licencaId, $valor, $tipo, $status);
                $this->flash('success', 'Pagamento registrado com sucesso.');
            } else {
                $this->flash('warning', 'Informe a licença e um valor válido.');
            }
        }
        $this->redirect('/admin/pagamentos');
    }

    public function excluir(string $id): void {
        Auth::requireAdmin();
        (new Pagamento())->excluir((int)$id);
        $this->flash('success', 'Pagamento removido.');
        $this->redirect('/admin/pagamentos');
    }
}
