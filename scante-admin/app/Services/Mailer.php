<?php
namespace App\Services;

/**
 * Envio de e-mail simples via mail() nativo do PHP (sem dependências —
 * projeto não usa Composer). Falhas só vão pro error_log, sem interromper
 * o fluxo do checkout.
 */
class Mailer {

    /** Avisa a equipe ScanTE que uma compra foi paga e está aguardando aprovação no admin. */
    public static function notificarNovaCompra(string $paraEmail, array $pedido): void {
        if (!$paraEmail) return;

        $assunto = 'Nova compra ScanTE — aguardando aprovação';

        $corpo = "Uma compra foi confirmada pelo site e está aguardando sua aprovação:\n\n"
            . "  Quantidade: {$pedido['quantidade']} licença(s)\n"
            . "  Suporte: {$pedido['anosSuporte']} ano(s)\n"
            . "  Empresa: {$pedido['empresaNome']}\n"
            . "  E-mail do contato: {$pedido['email']}\n"
            . "  Telefone: {$pedido['telefone']}\n\n"
            . "Revise e aprove em:\n{$pedido['linkAdmin']}\n\n"
            . "— ScanTE Admin";

        $headers = "From: ScanTE Admin <" . MAIL_FROM . ">\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n";

        $ok = @mail($paraEmail, $assunto, $corpo, $headers);
        if (!$ok) {
            error_log("[Mailer] Falha ao notificar equipe sobre nova compra (licenca #{$pedido['licencaId']})");
        }
    }
}
