<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;

/**
 * Action
 */
final class ProductAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
		parent::__construct($container);
        $this->container = $container;
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$params = $request->getQueryParams();
        // $categoryId = Util::getQueryParam($params, 'id');

		$categoryId = $params['id'] ?? null;

        $products = $this->getProduct($categoryId);
		$products = $this->isExistsInCart($products);
		$categorys = $this->container->get("view")->getEnvironment()->getGlobals()["categorys"];
		$categoryName = $this->getCategoryName($categorys, $categoryId);

		return $this->container->get('view')->render($response, 'products.html', ['products' => $products, 'categoryName' => $categoryName, 'category_id' => $categoryId]);
    }

	/**
	 * 商品詳細
	 */
	public function detail(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$params = $request->getQueryParams();
		$categoryId = $params['categoryid'] ?? null;
		$itemId = $params['itemid'] ?? null;

		$productInfo = $this->getProductOne($itemId);

		if (count($productInfo) > 0) {
			return $this->container->get('view')->render($response, 'detail.html', ['categoryid' => $categoryId, 'product' => $productInfo[0]]);
		}
		return $response->withHeader('Location', 'product?id='.$categoryId)->withStatus(302);
	}

	private function getProductOne($id) {

		$sql = 'SELECT * from products where id = :id';

        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':id', $id);
        
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	private function getCategoryName($categorys, $categoryId) {
		foreach($categorys as $c) {
			if ($c['id'] === $categoryId) {
				return $c['name'];
			}
		}
		return "すべての商品";
	}

	private function getProduct($categoryId) {

		$sql = 'SELECT p.*, c.name as categoryName, 
					-1 AS row_number ,
					0 as inCart
				from products as p 
				left join category as c 
				on p.category_id = c.id 
				where (:categoryid is null || p.category_id = :categoryid)
				order by p.category_id, p.id ';

        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':categoryid', $categoryId);
        
        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
		$lastCid = -1;
		foreach ($rows as $key => $row) {
			if ($lastCid != $row["category_id"]) {
				$rows[$key]["row_number"] = 1;
				$lastCid = $row["category_id"];
			}
		}
		return $rows;
    }

	private function isExistsInCart($products) {
		$session = $this->container->get(Consts::SESSION);
		if (false === $session->exists(Consts::CART)) return $products;

		$items = $session[Consts::CART];
		foreach($products as $key => $p) {
			if (array_key_exists($p['id'], $items) != null) {
				$products[$key]['inCart'] = 1;
			}
		}
		return $products;
	}
}
