<?php

namespace App\Action;

//PHPの設定
date_default_timezone_set('Asia/Tokyo');
mb_language("ja");
mb_internal_encoding("UTF-8");

//PHPMailerの使用宣言
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

/**
 * Action
 */
final class MailAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function mail(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();

        $name = $parsedBody['name'];
        $company = $parsedBody['company'];
        $mail = $parsedBody['mail'];
        $tel = $parsedBody['tel'];
        $contact = $parsedBody['contact'];

        //PHPMailerの使用
        $mailer = new PHPMailer(true);    //Passing `true` enables exceptions

        $this->setMailSetting($mailer);

        //Recipients
        $mailer->setFrom('site@air-ship.jp', mb_encode_mimeheader('サイト'));
        $mailer->addAddress('info@air-ship.jp', mb_encode_mimeheader('宛先者'));
        // $mailer->addCC('b-point@ina.bbiq.jp');

        // $mailer->isHTML(true); // Set email format to HTML
        $mailer->Subject = mb_encode_mimeheader('B・POINTから問い合わせがありました。');
        $mailer->Body = $this->getTemplate($name, $company, $mail, $tel, $contact);  // HTML
        // $mailer->AltBody = $this->getTemplate($name, $contact); // TEXT
      
        try {
            $mailer->send();
        } catch (Exception $e) {
            //エラー（例外：Exception）が発生した場合
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        $mailer->clearAddresses();

        // お客様に送信する
        $mailer->addAddress($mail, mb_encode_mimeheader($name));
        // $mailer->addCC('site@air-ship.jp');

        $mailer->Subject = mb_encode_mimeheader('AIR-SHIP.JPからお問い合わせがありました。');
        $mailer->Body = $this->getTemplateReply($name, $company, $mail, $tel, $contact);  // HTML
      
        try {
            $mailer->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        return $this->container->get('view')->render($response, 'contact2.html', ['bodyClass' => '', 'menuSelected' => 'contact']);
    }

    private function setMailSetting($mailer) {
        //Server settings
        $mailer->CharSet = 'UTF-8';
        $mailer->SMTPDebug = 0;         // Enable verbose debug output
        $mailer->isSMTP();              // Set mailer to use SMTP
        $mailer->Host = 'sv82.star.ne.jp';    // Specify main and backup SMTP servers

        $mailer->SMTPAuth = true;       // Enable SMTP authentication
        $mailer->Username = 'site@air-ship.jp';  // SMTP username
        $mailer->Password = 'web-2023-fuck..';// SMTP password

        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;// Enable TLS encryption, `ssl` also accepted
        $mailer->Port = 587;            // TCP port to connect to (ssl:465)
    }


    private function getTemplate($name, $company, $mail, $tel, $contact) {

        return <<<EOM

B・POINT Tradingから問い合わせがありました。

■名前：　${name}
■企業名： ${company}
■Email： ${mail}
■TEL： ${tel}

■お問い合わせ詳細：
＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝
{$contact}
＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝

以上

EOM;

    }


    public function getTemplateReply($name, $company, $mail, $tel, $contact)
    {
        return <<<EOM

${name} 様

この度は、b-pointへお問い合わせをいただき、誠にありがとうございました。
お問い合わせいただきました内容については、確認後に担当者よりご連絡させていただきます。


■ お名前
${name}

■ 企業名_/_団体名
${company}

■ メールアドレス
${mail}

■ 電話番号
${tel}

■ お問い合わせ内容
${contact}

■ 個人情報の取り扱いについて
同意する

＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝

このメールに心当たりの無い場合は、お手数ですが
下記連絡先までお問い合わせください。

＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝

株式会社B・POINT総合商社
https://www.b-point-trading.com

〒813-0032
福岡市東区土井2-25-7
TEL/FAX：092-984-1126
メールアドレスinfo@b-point-trading.com

EOM;
    }


}
