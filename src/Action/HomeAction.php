<?php

namespace App\Action;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Action
 */
final class HomeAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
		parent::__construct($container);
        $this->container = $container;
    }

    private $productPath = 'images/products/';

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'index.html', []);
    }

    public function productDetail(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'product-detail.html', []);
    }



    public function changeLanguage(ServerRequestInterface $request, ResponseInterface $response) {

      $parsedBody = $request->getParsedBody();
      // $this->container->set("language", $parsedBody['language']);
      $_SESSION['language'] = $parsedBody['language'];
      $data = ["language", $parsedBody['language']];
      $response->getBody()->write(json_encode($data));
      return $response->withHeader('Content-Type', 'application/json');
    }


    public function about(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'about.html', []);
    }

    public function personal(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'personal.html', []);
    }

    public function specificProduct(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'specificProduct.html', []);
    }

    public function ship(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'ship.html', []);
    }

	public function chartership(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'charter-ship.html', []);
    }

    public function contact(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();

        $name = $parsedBody['name'];
        $mail = $parsedBody['email'];
        $subject = $parsedBody['subject'];
        $contact = $parsedBody['message'];

		if (empty($name) || empty($mail) || empty($subject) || empty($contact)) {
			$payload = "OK";
			$response->getBody()->write($payload);
			return $response
					  ->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

        //PHPMailerの使用
        $mailer = new PHPMailer(true);    //Passing `true` enables exceptions

        $this->setMailSetting($mailer);

        //Recipients
        $mailer->setFrom('site@air-ship.jp', mb_encode_mimeheader('サイト'));
        $mailer->addAddress('info@air-ship.jp', mb_encode_mimeheader('宛先者'));
        // $mailer->addCC('b-point@ina.bbiq.jp');

        // $mailer->isHTML(true); // Set email format to HTML
        $mailer->Subject = mb_encode_mimeheader('Air-shipから問い合わせがありました。');
        $mailer->Body = $this->getTemplate($name, $mail, $subject, $contact);  // HTML
        // $mailer->AltBody = $this->getTemplate($name, $contact); // TEXT
      
        try {
            $mailer->send();

			$payload = "OK";
			$response->getBody()->write($payload);
			return $response
					  ->withHeader('Content-Type', 'application/json')->withStatus(200);
	
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
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

	private function getTemplate($name, $mail, $subject, $contact) {

        return <<<EOM

air-ship.jpから問い合わせがありました。

■名前：　{$name}
■Email： {$mail}

■タイトル： {$subject}

■お問い合わせ詳細：
＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝
{$contact}
＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝

以上

EOM;

    }

}
