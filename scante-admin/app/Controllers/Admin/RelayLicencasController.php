<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\RelayLicenca;
use App\Services\RelayLicenseService;

class RelayLicencasController extends Controller {

    public function index(): void {
        Auth::requireAdmin();
        $this->view('admin.relay_licencas.index', [
            'licencas' => (new RelayLicenca())->findAll('criada_em DESC'),
            'flash'    => $this->getFlash(),
        ], 'admin');
    }

    public function gerar(): void {
        Auth::requireAdmin();
        if ($this->isPost()) {
            $cliente     = trim($this->input('cliente', ''));
            $maxSessions = max(0, (int)$this->input('max_sessions', 0));
            $maxDevices  = max(0, (int)$this->input('max_devices', 0));
            $expiraEm    = trim($this->input('expira_em', ''));
            $release     = trim($this->input('release_suportado', '1.0'));
            $serverHost  = trim($this->input('server_host', ''));

            if (!$cliente || !$expiraEm) {
                $this->flash('warning', 'Informe pelo menos o cliente e a validade.');
                $this->redirect('/admin/relay-licencas');
                return;
            }

            try {
                $serial = RelayLicenseService::gerarSerial();
                $texto  = RelayLicenseService::gerar([
                    'customer'     => $cliente,
                    'serial'       => $serial,
                    'max_sessions' => $maxSessions,
                    'max_devices'  => $maxDevices,
                    'expiry'       => $expiraEm,
                    'release'      => $release,
                    'server_host'  => $serverHost,
                ]);

                (new RelayLicenca())->criar([
                    'cliente'           => $cliente,
                    'serial'            => $serial,
                    'max_sessions'      => $maxSessions,
                    'max_devices'       => $maxDevices,
                    'expira_em'         => $expiraEm,
                    'release_suportado' => $release,
                    'server_host'       => $serverHost,
                    'licenca_texto'     => $texto,
                    'criado_por'        => Auth::id(),
                ]);

                $this->flash('success', 'Licença gerada com sucesso — copie o texto ou baixe o arquivo abaixo.');
            } catch (\Throwable $e) {
                error_log('RelayLicencasController::gerar falhou: ' . $e->getMessage());
                $motivo = str_contains($e->getMessage(), 'sodium')
                    ? 'A extensão PHP "sodium" não está habilitada neste servidor.'
                    : (str_contains(strtolower($e->getMessage()), 'relay_licencas')
                        ? 'A tabela relay_licencas não existe no banco (rode a migração 2026_07_relay_licencas.sql).'
                        : 'Erro: ' . $e->getMessage());
                $this->flash('danger', 'Não foi possível gerar a licença. ' . $motivo);
            }
        }
        $this->redirect('/admin/relay-licencas');
    }

    /** Baixa o texto da licença como arquivo .lic (pra usar o botão "Importar arquivo" no relay). */
    public function baixar(string $id): void {
        Auth::requireAdmin();
        $licenca = (new RelayLicenca())->findById((int)$id);
        if (!$licenca) { $this->redirect('/admin/relay-licencas'); return; }

        $nomeArquivo = 'scante-relay-' . $licenca['serial'] . '.lic';
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        echo $licenca['licenca_texto'];
        exit;
    }
}
