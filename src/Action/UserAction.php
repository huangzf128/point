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
final class UserAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
		parent::__construct($container);
        $this->container = $container;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$model = new UserModel($this->container);
		$users = $model->getAllUser();

		return $this->container->get('view')->render($response, 'user.html', ['users' => $users]);
    }

	public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		$info = [
			'id' => $parsedBody['id'],
			'name' => $parsedBody['name'],
			'password' => $parsedBody['password'],
			'type' => $parsedBody['type']
		];

		if ($this->checkRequired($info)) {

			$info['password'] = sha1($info['password']);
			$model = new UserModel($this->container);
			$model->insertUser($info);
		}
		return $response->withHeader('Location', '/user')->withStatus(302);
    }

	public function remove(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		if (!empty($parsedBody['id'])) {
			$model = new UserModel($this->container);
			$model->deleteUser($parsedBody['id']);
		}
		return $response->withHeader('Location', '/user')->withStatus(302);
	}

	public function modify(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		$info = [
			'id' => $parsedBody['id'],
			'name' => $parsedBody['name'],
			'password' => $parsedBody['password'] ?? "",
			'type' => $parsedBody['type'],
		];

		if (!empty($info['password'])) {
			$info['password'] = sha1($info['password']);
		}

		if (!empty($info['id']) && !empty($info['name']) && !empty($info['type'])) {
			$model = new UserModel($this->container);
			$model->updateUser($info);
		}
		return $response->withHeader('Location', '/user')->withStatus(302);
    }

	private function checkRequired($param) {
		return !empty($param['id']) && !empty($param['name']) && !empty($param['password']) && !empty($param['type']);
	}
}
