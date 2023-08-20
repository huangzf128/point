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
final class InventoryAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container, App $app)
    {
		parent::__construct($container);
        $this->container = $container;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		
		$warehouseModel = new WarehouseModel($this->container);
		$warehouses = $warehouseModel->getWarehouse();		

		$params = $request->getQueryParams();
		$warehouseId = $params['warehouseId'] ?? '';

		if (empty($warehouseId)) {
			return $this->container->get('view')->render($response, 'inventory.html', 
				['items' => [], 'inventorys' => [], 'warehouses' => $warehouses]);
		}

		$model = new InventoryModel($this->container);
		$inventorys = $model->getInventory($warehouseId);

		// 管理外商品
		$items = $model->getUnManagedItem($warehouseId);

		return $this->container->get('view')->render($response, 'inventory.html', 
				['items' => $items, 'inventorys' => $inventorys, 'warehouses' => $warehouses, 'warehouseId' => $warehouseId]);
    }

	public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		$itemId = $parsedBody['itemId'];
		$warehouseId = $parsedBody['warehouseId'];

		if (!empty($itemId) && !empty($warehouseId)) {
			$model = new InventoryModel($this->container);
			$info = ['itemId' => $itemId, 'quantity' => 0, 'warehouseId' => $warehouseId];
			$model->insertInventory($info);
		}

		$route = $this->container->get('router');
		$url = $route->urlFor("inventory", [], ["warehouseId" => $parsedBody['warehouseId']]);		
		return $response->withHeader('Location', $url)->withStatus(302);
    }

	public function remove(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		if ($parsedBody['id']) {
			$model = new InventoryModel($this->container);
			$model->deleteInventory($parsedBody['id']);
		}

		$route = $this->container->get('router');
		$url = $route->urlFor("inventory", [], ["warehouseId" => $parsedBody['warehouseId']]);
		return $response->withHeader('Location', $url)->withStatus(302);
	}

	public function modify(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		if ($parsedBody['invupd']) {
			$info = json_decode($parsedBody['invupd'], true);
			$model = new InventoryModel($this->container);

			foreach ($info as $row) {
				$model->updateInventoryByKey($row);
			}
		}

		$route = $this->container->get('router');
		$url = $route->urlFor("inventory", [], ["warehouseId" => $parsedBody['warehouseId']]);
		return $response->withHeader('Location', $url)->withStatus(302);
	}
}
