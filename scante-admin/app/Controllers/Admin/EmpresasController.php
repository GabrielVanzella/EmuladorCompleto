<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\Licenca;

class EmpresasController extends Controller {

    public function index(): void {
        Auth::requireAdmin();
        $model    = new Empresa();
        $empresas = $model->comEstatisticas();
        $this->view('admin.empresas.index', ['empresas' => $empresas, 'flash' => $this->getFlash()], 'admin');
    }

    public function criar(): void {
        Auth::requireAdmin();

        if ($this->isPost()) {
            $data = [
                'nome'     => $this->sanitize($this->input('nome', '')),
                'cnpj'     => $this->sanitize($this->input('cnpj', '')),
                'email'    => $this->sanitize($this->input('email', '')),
                'telefone' => $this->sanitize($this->input('telefone', '')),
                'contato'  => $this->sanitize($this->input('contato', '')),
            ];

            if (!$data['nome'] || !$data['email']) {
                $erro = 'Nome e e-mail são obrigatórios.';
            } else {
                $empresaModel = new Empresa();
                $empresaId    = $empresaModel->create($data);

                // Cria usuário de acesso da empresa
                $senhaGerada = bin2hex(random_bytes(4));
                $usuarioModel = new Usuario();
                $usuarioModel->create([
                    'nome'       => $data['nome'],
                    'email'      => $data['email'],
                    'senha'      => $senhaGerada,
                    'tipo'       => 'empresa',
                    'empresa_id' => $empresaId,
                ]);

                $this->flash('success', "Empresa criada! Login: {$data['email']} / Senha: {$senhaGerada}");
                $this->redirect('/admin/empresas');
            }
        }

        $this->view('admin.empresas.form', ['empresa' => null, 'erro' => $erro ?? null], 'admin');
    }

    public function editar(string $id): void {
        Auth::requireAdmin();
        $model  = new Empresa();
        $empresa = $model->findById((int)$id);
        if (!$empresa) { $this->redirect('/admin/empresas'); }

        if ($this->isPost()) {
            $data = [
                'nome'     => $this->sanitize($this->input('nome', '')),
                'cnpj'     => $this->sanitize($this->input('cnpj', '')),
                'email'    => $this->sanitize($this->input('email', '')),
                'telefone' => $this->sanitize($this->input('telefone', '')),
                'contato'  => $this->sanitize($this->input('contato', '')),
                'ativo'    => $this->input('ativo', 0) ? 1 : 0,
            ];
            $model->update((int)$id, $data);
            $this->flash('success', 'Empresa atualizada.');
            $this->redirect('/admin/empresas');
        }

        $this->view('admin.empresas.form', ['empresa' => $empresa, 'erro' => null], 'admin');
    }

    public function ver(string $id): void {
        Auth::requireAdmin();
        $empresa  = (new Empresa())->findById((int)$id);
        if (!$empresa) { $this->redirect('/admin/empresas'); }

        $licencas = (new Licenca())->findByEmpresa((int)$id);
        $usuarios = (new Usuario())->findByEmpresa((int)$id);

        $this->view('admin.empresas.ver', [
            'empresa'  => $empresa,
            'licencas' => $licencas,
            'usuarios' => $usuarios,
            'flash'    => $this->getFlash(),
        ], 'admin');
    }

    public function gerarLicencas(string $id): void {
        Auth::requireAdmin();
        $empresa = (new Empresa())->findById((int)$id);
        if (!$empresa) { $this->redirect('/admin/empresas'); }

        if ($this->isPost()) {
            $tipo      = $this->input('tipo', 'trial');
            $quantidade = min((int)$this->input('quantidade', 1), 50);
            $dias      = (int)$this->input('dias', 30);

            $model = new Licenca();
            for ($i = 0; $i < $quantidade; $i++) {
                $model->gerar((int)$id, $tipo, $dias);
            }
            $this->flash('success', "$quantidade licença(s) gerada(s) para {$empresa['nome']}.");
        }
        $this->redirect('/admin/empresas/' . $id);
    }

    public function excluir(string $id): void {
        Auth::requireAdmin();
        (new Empresa())->delete((int)$id);
        $this->flash('success', 'Empresa removida.');
        $this->redirect('/admin/empresas');
    }
}
