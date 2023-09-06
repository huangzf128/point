<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;
use App\Model\CategoryModel;
use App\Model\InventoryModel;
use App\Model\WarehouseModel;
use Slim\App;

/**
 * Action
 */
final class CommonAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container, App $app)
    {
		parent::__construct($container);
        $this->container = $container;
    }

	public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'index.html', []);
    }

	public function about(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'about.html', []);
    }

    public function invItem(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		$id = $parsedBody['id'];
		
		if (!empty($id)) {
			$model = new InventoryModel($this->container);
			$inventorys = $model->getInventory($id);

			$payload = json_encode($inventorys);

			$response->getBody()->write($payload);
			return $response->withHeader('Content-Type', 'application/json');			
		}
    }


}
