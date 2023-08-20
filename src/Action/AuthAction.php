<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;
use App\Model\UserModel;

/**
 * Action
 */
final class AuthAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

	public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$errMsg = $this->getFlash('login_err_msg', true);
		return $this->container->get('view')->render($response, 'auth.html', ['errMsg' => $errMsg]);
	}

	// ajax
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();

        $info = ['id' => $parsedBody['id'], "password" => sha1($parsedBody['password'])];

		$model = new UserModel($this->container);
		$user = $model->getUser($info);

        if (count($user) == 0) {

			$errMsg = "ユーザーID、もしくはパスワードの入力が不正です。";
			$this->saveToFlash('login_err_msg', $errMsg);

			return $response->withHeader('Location', '/auth')->withStatus(302);

        } else {
			$session = $this->container->get(Consts::SESSION);
			$userInfo = ['id' => $user[0]['id'], 
						 'name' => $user[0]['name'], 
						 'type' => $user[0]['type']];

			$session->set('auth', $userInfo);
			return $response->withHeader('Location', '/')->withStatus(302);        
		}
    }

	public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$session = $this->container->get(Consts::SESSION);
		// Destroy session
		if ($session != null) {
			$session::destroy();
		}
		return $response->withHeader('Location', '/')->withStatus(302);
	}

	// ajax
	public function signup(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();

		$email = $parsedBody['email'];
		$pass = $parsedBody['password'];
		$pass = sha1($pass);
		
		$first = $parsedBody['firstName'];
		$phone = $parsedBody['phone'];
		
		$exists = $this->checkUser($email);
		if ($exists) {
		
			$data = array('error' => "このメールアドレスは既に使われています。");
			
		} else {
			$sql = "INSERT INTO users(email_id, first_name, phone, password) 
					values( :email, :first_name, :phone, :pass)";
			// SQL実行準備
			$statement = $this->container->get('PDO')->prepare($sql);
			$statement->bindparam(':email', $email);
			$statement->bindparam(':first_name', $first);
			$statement->bindparam(':phone', $phone);
			$statement->bindparam(':pass', $pass);
	
			// 値を渡して実行
			$result = $statement->execute();
			$user_id = $this->container->get('PDO')->lastInsertId();

			$session = $this->container->get(Consts::SESSION);
			$session->set('email', $email);
			$session->set('id', $user_id);

			$data = array('email' => $email, 'id' => $user_id);
		}

		$payload = json_encode($data);
		$response->getBody()->write($payload);
		return $response
				  ->withHeader('Content-Type', 'application/json');		
    }

	private function checkUser($email) {

		$sql = 'SELECT id from users where email_id= :email_id ';

        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':email_id', $email);
        
        $statement->execute();
        $user = $statement->fetchAll(PDO::FETCH_ASSOC);
		return count($user) > 0;
    }

}
