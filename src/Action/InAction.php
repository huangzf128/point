<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;
use App\Model\CategoryModel;
use App\Model\ItemModel;
use App\Model\WarehouseModel;
use App\Model\InventoryModel;
use App\Model\InHistoryModel;

/**
 * Action
 */
final class InAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
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
		$ym = $params['ym'] ?? date('Y-m');
		$ymd = $params['ymd'] ?? date('Y-m-d');

		$itemModel = new ItemModel($this->container);
		$items = $itemModel->getItem();

		$invHistorys = [];
		if (!empty($warehouseId) && !empty($ym)) {

			$invHistoryModel = new InHistoryModel($this->container);
			$invHistorys = $invHistoryModel->getInHistory($warehouseId, $ym, 1);
		}

		$invItems = [];
		$detailId = $params['detailId'] ?? '';
		if (!empty($detailId)) {
			$invHistoryModel = new InHistoryModel($this->container);
			$invItems = $invHistoryModel->getInHistoryDetail($detailId);
		}
		return $this->container->get('view')->render($response, 'in.html', 
				['items' => $items, 'warehouses' => $warehouses, 'warehouseId' => $warehouseId, 'ym' => $ym, 'ymd' => $ymd,
				'invHistorys' => $invHistorys, 'invItems' => $invItems]);
    }


	public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		if ($parsedBody['addData']) {
			$datas = json_decode($parsedBody['addData'], true);
			$session = $this->container->get(Consts::SESSION);

			$info = array(
				"warehouseId" => $parsedBody['warehouseId'],
				"ymd" => $parsedBody['ymd'],
				"userId" => $session['auth']['id'],
				"inoutkbn" => "1",
				"customerId" => ""
			);
			
			$inModel = new InHistoryModel($this->container);
			$inventoryModel = new InventoryModel($this->container);

			$id = $inModel->insertInHistory($info);

			foreach ($datas as $row) {

				$inModel->insertInHistoryDetail($id, $row);

				// 在庫更新
				$param = ["warehouseId" => $info['warehouseId'], "itemId" => $row['itemId'], "quantity" => $row['quantity']];
				$cnt = $inventoryModel->updateInventory($param);
				if ($cnt == 0) {
					$inventoryModel->insertInventory($param);
				}
			}
		}

		$route = $this->container->get('router');
		$url = $route->urlFor("in", [], ["warehouseId" => $parsedBody['warehouseId'], "ym" => $parsedBody['ym']]);
		return $response->withHeader('Location', $url)->withStatus(302);
    }


	public function modify(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		if ($parsedBody['addData']) {
			$datas = json_decode($parsedBody['addData'], true);

			$inModel = new InHistoryModel($this->container);
			$inventoryModel = new InventoryModel($this->container);

			foreach ($datas as $row) {

				$inModel->updateInHistoryDetail($row);

				// 在庫更新
				$param = ["warehouseId" => $parsedBody['warehouseId'], "itemId" => $row['itemId'], "quantity" => ($row['quantity'] - $row['quantityDb'])];
				$cnt = $inventoryModel->updateInventory($param);
				if ($cnt == 0) {
					$inventoryModel->insertInventory($param);
				}
			}

			if (count($datas) > 0) {
				$inModel->updateInHistoryById($datas[0]['id']);
			}
		}

		$route = $this->container->get('router');
		$url = $route->urlFor("in", [], ["warehouseId" => $parsedBody['warehouseId'], "ym" => $parsedBody['ym']]);
		return $response->withHeader('Location', $url)->withStatus(302);
	}

	public function remove(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();

		$detailId = $parsedBody['detailId'];
		if (!empty($detailId)) {
			$invHistoryModel = new InHistoryModel($this->container);
			$invItems = $invHistoryModel->getInHistoryDetail($detailId);

			$inventoryModel = new InventoryModel($this->container);
			foreach ($invItems as $item) {
				// 在庫更新
				$param = ["warehouseId" => $parsedBody['warehouseId'], "itemId" => $item['itemId'], "quantity" => (-1 * $item['quantity'])];
				$cnt = $inventoryModel->updateInventory($param);
				if ($cnt == 0) {
					$inventoryModel->insertInventory($param);
				}
			}

			$invHistoryModel->deleteInHistory($detailId);
			$invHistoryModel->deleteInHistoryDetail($detailId);
		}

		$route = $this->container->get('router');
		$url = $route->urlFor("in", [], ["warehouseId" => $parsedBody['warehouseId'], "ym" => $parsedBody['ym']]);
		return $response->withHeader('Location', $url)->withStatus(302);
	}

}
