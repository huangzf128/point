<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

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

    public function contact(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // return $response->withHeader('Content-Type', 'application/json');
        return $this->container->get('view')->render($response, $args['viewId'] . '.html', ['bodyClass' => '']);
    }
}
