<?php
namespace App\Controllers\Empresa;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Chamado;
use App\Models\Licenca;
use App\Models\Configuracao;
use App\Services\Mailer;

class ChamadosController extends Controller {

    public function index(): void {
        Auth::requireEmpresa();
        $chamados = (new Chamado())->porEmpresa(Auth::empresaId());
        $this->view('empresa.chamados.index', [
            'chamados' => $chamados,
            'flash'    => $this->getFlash(),
        ], 'empresa');
    }

    public function criar(): void {
        Auth::requireEmpresa();
        $empresaId = Auth::empresaId();
        $licencas  = (new Licenca())->findByEmpresa($empresaId);

        if ($this->isPost()) {
            $assunto  = $this->sanitize($this->input('assunto', ''));
            $mensagem = trim((string)$this->input('mensagem', ''));
            $licencaRaw = $this->input('licenca_id', '');
            $licencaId  = ($licencaRaw === '' || $licencaRaw === 'geral') ? null : (int)$licencaRaw;

            // Se escolheu uma licença, garante que é da própria empresa
            if ($licencaId !== null) {
                $lic = (new Licenca())->findById($licencaId);
                if (!$lic || (int)$lic['empresa_id'] !== (int)$empresaId) {
                    $licencaId = null;
                }
            }

            if (!$assunto || $mensagem === '') {
                $erro = 'Preencha o assunto e a mensagem.';
            } else {
                $chamado = new Chamado();
                $chamadoId = $chamado->abrir($empresaId, $licencaId, Auth::id(), Auth::nome(), $assunto, $mensagem);

                // Notifica a equipe ScanTE por e-mail
                $emailEquipe = (new Configuracao())->get('email_notificacoes', 'scante@scante.com.br');
                $licTxt = 'Assunto geral';
                if ($licencaId !== null && isset($lic)) {
                    $licTxt = $lic['chave'] . ' (' . $lic['tipo'] . ')';
                }
                Mailer::notificarNovoChamado($emailEquipe, [
                    'chamadoId'   => $chamadoId,
                    'empresaNome' => Auth::nome(),
                    'licenca'     => $licTxt,
                    'assunto'     => $assunto,
                    'mensagem'    => $mensagem,
                    'linkAdmin'   => APP_URL . '/admin/chamados/' . $chamadoId,
                ]);

                $this->flash('success', 'Chamado aberto! Nossa equipe vai responder por aqui.');
                $this->redirect('/empresa/chamados/' . $chamadoId);
            }
        }

        $this->view('empresa.chamados.criar', [
            'licencas' => $licencas,
            'erro'     => $erro ?? null,
            'flash'    => $this->getFlash(),
        ], 'empresa');
    }

    public function ver(string $id): void {
        Auth::requireEmpresa();
        $chamado = (new Chamado())->verComContexto((int)$id);

        // Só o dono do chamado pode ver
        if (!$chamado || (int)$chamado['empresa_id'] !== (int)Auth::empresaId()) {
            $this->redirect('/empresa/chamados');
        }

        $mensagens = (new Chamado())->mensagens((int)$id);
        $this->view('empresa.chamados.ver', [
            'chamado'   => $chamado,
            'mensagens' => $mensagens,
            'flash'     => $this->getFlash(),
        ], 'empresa');
    }

    public function responder(string $id): void {
        Auth::requireEmpresa();
        $chamado = (new Chamado())->verComContexto((int)$id);

        if (!$chamado || (int)$chamado['empresa_id'] !== (int)Auth::empresaId()) {
            $this->redirect('/empresa/chamados');
        }

        if ($this->isPost()) {
            $mensagem = trim((string)$this->input('mensagem', ''));
            if ($mensagem === '') {
                $this->flash('error', 'Escreva uma mensagem.');
            } else {
                (new Chamado())->adicionarMensagem((int)$id, 'empresa', Auth::id(), Auth::nome(), $mensagem);
                $this->flash('success', 'Mensagem enviada.');
            }
        }
        $this->redirect('/empresa/chamados/' . $id);
    }
}
