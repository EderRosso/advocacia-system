<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../config/conexao.php'; // Para ter acesso à BASE_URL

function enviar_email_boas_vindas($para_email, $para_nome, $senha_provisoria)
{
    $mail = new PHPMailer(true);

    try {
        // --- CONFIGURAÇÕES DO SERVIDOR SMTP ---
        // Aqui o cliente precisará colocar as credenciais reais do email dele (ex: Gmail, Hostgator, Locaweb)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        // ATENÇÃO: Preencher com os dados reais
        $mail->Username = 'contatoadvsystem@gmail.com';
        $mail->Password = 'cfvijrxkrgrmmdek';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Porta 465 (ou TLS porta 587)
        $mail->Port = 465;

        // Remetente e Destinatário
        $mail->setFrom($mail->Username, 'Advocacia System');
        $mail->addAddress($para_email, $para_nome);

        // Detectar o protocolo para gerar o link certinho (http ou https)
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $link_login = $protocol . $_SERVER['HTTP_HOST'] . BASE_URL . "login.php";

        // Conteúdo do Email HTML
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Bem-vindo! Suas credenciais do Advocacia System';

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
            <h2 style='color: #0D8ABC; border-bottom: 2px solid #0D8ABC; padding-bottom: 10px;'>Bem-vindo(a), {$para_nome}!</h2>
            <p>Sua conta de acesso ao nosso sistema jurídico foi criada com sucesso.</p>
            <p>Por favor, guarde suas credenciais em local seguro:</p>
            <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <p style='margin: 0 0 10px;'><strong>📧 E-mail:</strong> {$para_email}</p>
                <p style='margin: 0;'><strong>🔑 Senha temporária:</strong> {$senha_provisoria}</p>
            </div>
            <p style='text-align: center; margin-top: 30px;'>
                <a href='{$link_login}' style='background: #0D8ABC; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; font-weight: bold; display: inline-block;'>Acessar o Sistema Agora</a>
            </p>
            <br>
            <p style='font-size: 11px; color: #888;'><em>Este é um e-mail automático. Por favor, não responda.</em></p>
        </div>
        ";

        $mail->Body = $body;
        $mail->AltBody = "Olá, {$para_nome}!\n\nSua conta foi criada no Advocacia System.\n\nE-mail: {$para_email}\nSenha: {$senha_provisoria}\n\nLink de acesso: {$link_login}";

        $mail->send();
        return true; // Sucesso
    }
    catch (Exception $e) {
        // Como é provável falhar na InfinityFree sem um SMTP configurado pelo usuário, 
        // retornamos o erro para ser exibido amigavelmente sem estourar código PHP n cru.
        return "E-mail não pode ser enviado. Erro do Servidor: {$mail->ErrorInfo}";
    }
}

function enviar_email_link_rastreio($para_email, $para_nome, $numero_processo, $link_rastreio)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        // ATENÇÃO: Contas padrão usadas pelo sistema
        $mail->Username = 'contatoadvsystem@gmail.com';
        $mail->Password = 'cfvijrxkrgrmmdek';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Porta 465
        $mail->Port = 465;

        // Remetente e Destinatário
        $mail->setFrom($mail->Username, 'Advocacia System (Portal do Cliente)');
        $mail->addAddress($para_email, $para_nome);

        // Conteúdo do Email HTML
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Novo Portal de Acompanhamento: Processo Nº ' . $numero_processo;

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
            <h2 style='color: #0D8ABC; border-bottom: 2px solid #0D8ABC; padding-bottom: 10px;'>Boas notícias, {$para_nome}!</h2>
            <p>O escritório acaba de realizar uma nova movimentação no seu processo (<strong>Nº {$numero_processo}</strong>).</p>
            <p>Pensando na sua total comodidade, a partir de hoje você não precisará nos ligar para saber de novidades. Criamos um <strong>Portal de Acompanhamento em Tempo Real</strong> exclusivo para o seu caso!</p>
            <div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #0D8ABC;'>
                <p style='margin: 0 0 10px 0;'><strong>Seu Link de Acesso Exclusivo e Privado:</strong></p>
                <p style='margin: 0; font-size: 13px; font-weight: bold;'><a href='{$link_rastreio}'>{$link_rastreio}</a></p>
            </div>
            <p style='text-align: center; margin-top: 30px;'>
                <a href='{$link_rastreio}' style='background: #28a745; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; font-weight: bold; display: inline-block;'>Acessar o Andamento</a>
            </p>
            <br>
            <p style='font-size: 12px; color: #888;'><em>Dica: Salve este e-mail estrelado ou adicione o link aos favoritos do navegador.</em></p>
        </div>
        ";

        $mail->Body = $body;
        $mail->send();
        return true; 
    }
    catch (Exception $e) {
        return "E-mail não pode ser enviado. Erro do Servidor: {$mail->ErrorInfo}";
    }
}
?>
