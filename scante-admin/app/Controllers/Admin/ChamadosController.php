<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Chamado;

class ChamadosController extends Controller {

    public function index(): void {
        Auth::requireAdmin();
        $this->view('admin.chamados.index', [
            'chamados' => (new Chamado())->todos(),
            'flash'    => $this->getFlash(),
        ], 'admin');
    }

    public function ver(string $id): void {
        Auth::requireAdmin();
        $chamado = (new Chamado())->verComContexto((int)$id);
        if (!$chamado) { $this->redirect('/admin/chamados'); }

        $mensagens = (new Chamado())->mensagens((int)$id);
        $this->view('admin.chamados.ver', [
            'chamado'   => $chamado,
            'mensagens' => $mensagens,
            'flash'     => $this->getFlash(),
        ], 'admin');
    }

    public function responder(string $id): void {
        Auth::requireAdmin();
        $chamado = (new Chamado())->verComContexto((int)$id);
        if (!$chamado) { $this->redirect('/admin/chamados'); }

        if ($this->isPost()) {
            $mensagem = trim((string)$this->input('mensagem', ''));
            if ($mensagem === '') {
                $this->flash('error', 'Escreva uma mensagem.');
            } else {
                (new Chamado())->adicionarMensagem((int)$id, 'admin', Auth::id(), Auth::nome(), $mensagem);
                $this->flash('success', 'Resposta enviada à empresa.');
            }
        }
        $this->redirect('/admin/chamados/' . $id);
    }

    public function status(string $id): void {
        Auth::requireAdmin();
        if ($this->isPost()) {
            $status = (string)$this->input('status', '');
            (new Chamado())->mudarStatus((int)$id, $status);
            $this->flash('success', 'Status atualizado.');
        }
        $this->redirect('/admin/chamados/' . $id);
    }
}
