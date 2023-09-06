<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;
use App\Model\WarehouseModel;

/**
 * Action
 */
final class WarehouseAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
		parent::__construct($container);
        $this->container = $container;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$model = new WarehouseModel($this->container);
		$warehouses = $model->getWarehouse();

		return $this->container->get('view')->render($response, 'warehouse.html', 
				['warehouses' => $warehouses]);
    }

	public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		$warehouseInfo = [
			'warehouseName' => $parsedBody['warehouseName'],
			'address' => $parsedBody['address'],
			'tel' => $parsedBody['tel'],
			'type' => $parsedBody['type']
		];

		if ($parsedBody['warehouseName']) {
			$model = new WarehouseModel($this->container);
			$model->insertWarehouse($warehouseInfo);
		}
		return $response->withHeader('Location', '/warehouse')->withStatus(302);
    }

	public function remove(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		if ($parsedBody['id']) {
			$model = new WarehouseModel($this->container);
			$model->deleteWarehouse($parsedBody['id']);
		}
		return $response->withHeader('Location', '/warehouse')->withStatus(302);
	}

	public function modify(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		$warehouseInfo = [
			'warehouseName' => $parsedBody['warehouseName'],
			'address' => $parsedBody['address'],
			'tel' => $parsedBody['tel'],
			'id' => $parsedBody['id'],
			'type' => $parsedBody['type']
		];

		if ($parsedBody['warehouseName']) {
			$model = new WarehouseModel($this->container);
			$model->updateWarehouse($warehouseInfo);
		}
		return $response->withHeader('Location', '/warehouse')->withStatus(302);
    }
}
