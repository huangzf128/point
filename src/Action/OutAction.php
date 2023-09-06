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
use App\Model\CustomerModel;
use App\Model\WarehouseModel;
use App\Model\InventoryModel;
use App\Model\OutHistoryModel;

/**
 * Action
 */
final class OutAction extends BaseAction
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
		// $warehouseId = $params['warehouseId'] ?? '';
		$ym = $params['ym'] ?? date('Y-m');
		$ymd = $params['ymd'] ?? date('Y-m-d');

		// $itemModel = new ItemModel($this->container);
		// $items = $itemModel->getItem();

		$customerModel = new CustomerModel($this->container);
		$customers = $customerModel->getCustomer();

		$invHistorys = [];
		if (!empty($ym)) {
			$outModel = new OutHistoryModel($this->container);
			$invHistorys = $outModel->getOutHistory($ym, "");
		}

		$invItems = [];
		$detailId = $params['detailId'] ?? '';
		if (!empty($detailId)) {
			$outModel = new OutHistoryModel($this->container);
			$invItems = $outModel->getOutHistoryDetail($detailId);
		}

		return $this->container->get('view')->render($response, 'out.html', 
				['warehouses' => $warehouses, 'ym' => $ym, 'ymd' => $ymd,
				'invHistorys' => $invHistorys, 'invItems' => $invItems, 'customers' => $customers]);
    }

	public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		if ($parsedBody['addData']) {
			$datas = json_decode($parsedBody['addData'], true);
			$session = $this->container->get(Consts::SESSION);

			$info = array(
				"ymd" => $parsedBody['ymd'],
				"userId" => $session['auth']['id'],
				"customerId" => $parsedBody['customerId']
			);

			$model = new OutHistoryModel($this->container);
			$inventoryModel = new InventoryModel($this->container);

			$id = $model->insertOutHistory($info);

			foreach ($datas as $row) {
				
				$model->insertOutHistoryDetail($id, $row);

				// 在庫更新
				$param = ["warehouseId" => $row['warehouseId'], "itemId" => $row['itemId'], "price" => $row['price'], "quantity" => -1 * $row['quantity']];
				$cnt = $inventoryModel->updateInventory($param);
				if ($cnt == 0) {
					$inventoryModel->insertInventory($param);
				}
			}
		}

		$route = $this->container->get('router');
		$url = $route->urlFor("out", [], ["ym" => $parsedBody['ym']]);
		return $response->withHeader('Location', $url)->withStatus(302);
    }

	public function modify(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		if ($parsedBody['addData']) {
			$datas = json_decode($parsedBody['addData'], true);

			$outModel = new OutHistoryModel($this->container);
			$inventoryModel = new InventoryModel($this->container);

			foreach ($datas as $row) {

				$outModel->updateOutHistoryDetail($row);

				// 在庫更新
				$param = ["warehouseId" => $row['warehouseId'], "itemId" => $row['itemId'], "price" => $row['price'], "quantity" => -1 * ($row['quantity'] - ($row['quantityDb'] ?? 0))];
				$cnt = $inventoryModel->updateInventory($param);
				if ($cnt == 0) {
					$inventoryModel->insertInventory($param);
				}
			}
			if (count($datas) > 0) {
				$outModel->updateOutHistoryById($datas[0]['id']);
			}			
		}

		$route = $this->container->get('router');
		$url = $route->urlFor("out", [], ["ym" => $parsedBody['ym']]);
		return $response->withHeader('Location', $url)->withStatus(302);
	}

	public function remove(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();

		$detailId = $parsedBody['detailId'];
		if (!empty($detailId)) {
			$outHistoryModel = new OutHistoryModel($this->container);
			$invItems = $outHistoryModel->getOutHistoryDetail($detailId);

			$inventoryModel = new InventoryModel($this->container);
			foreach ($invItems as $item) {
				// 在庫更新
				$param = ["warehouseId" => $item['warehouseId'], "itemId" => $item['itemId'], "quantity" => $item['quantity']];
				$cnt = $inventoryModel->updateInventory($param);
				if ($cnt == 0) {
					$inventoryModel->insertInventory($param);
				}
			}

			$outHistoryModel->deleteOutHistory($detailId);
			$outHistoryModel->deleteOutHistoryDetail($detailId);
		}

		$route = $this->container->get('router');
		$url = $route->urlFor("out", [], ["ym" => $parsedBody['ym']]);
		return $response->withHeader('Location', $url)->withStatus(302);
	}
}
