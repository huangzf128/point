<?php

namespace App\Action;

use PDO;
use Exception;
use App\Common\Consts;
use App\Common\Cart;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action
 */
final class CartAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
		parent::__construct($container);
        $this->container = $container;
    }

    public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $id = $params['id'];

        $session = $this->container->get(Consts::SESSION);
		$items = [];
        if ($session->exists(Consts::CART)) {
            $items = $session[Consts::CART];
		}
		CART::addItem($items, $id);
		$session->set(Consts::CART, $items);

        return $response->withHeader('Location', 'product')->withStatus(302);
    }
 
    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$session = $this->container->get('session');
		$items = [];
        if ($session->exists(Consts::CART)) {
            $items = $session[Consts::CART];
			CART::updateItems($items, $this->container->get('PDO'));
			$session->set(Consts::CART, $items);
		}
		
		return $this->container->get('view')->render($response, 'cart.html', ['items' => $items]);
    }

	public function remove(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
        $params = $request->getQueryParams();
        $id = $params['id'];

        $session = $this->container->get(Consts::SESSION);
		$items = [];
		if ($session->exists(Consts::CART)) {
            $items = $session[Consts::CART];
			CART::removeItem($items, $id);
			$session->set(Consts::CART, $items);
		}

		return $this->container->get('view')->render($response, 'cart.html', ['items' => $items]);
	}
}
