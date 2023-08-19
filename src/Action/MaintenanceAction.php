<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;
use App\Common\Image;

/**
 * Action
 */
final class MaintenanceAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
		parent::__construct($container);
        $this->container = $container;
    }

	public function userList(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$users = $this->getUserList();
		return $this->container->get('view')->render($response, 'maint-user.html', ['users' => $users]);
	}

	private function getUserList() {

		$sql = 'SELECT * from users ';

        $statement = $this->container->get('PDO')->prepare($sql);
        
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
	// ---------------------
	// カテゴリ
	// ---------------------
    public function categoryList(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		return $this->container->get('view')->render($response, 'maint-category.html', []);
	}

    public function categoryAdd(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
		$cid = $parsedBody['cid'] ?? "";
        $name = $parsedBody['name'] ?? "";

		$imgPath = "";
		if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
			$imgPath =  Consts::CATEGORY_PATH. date('mdHis'). "_". $_FILES['image']['name'];
		}

		if (!file_exists(Consts::CATEGORY_PATH)) {
			mkdir(Consts::CATEGORY_PATH, 0777, true);
		}

		if (empty($cid)) { // 追加
			$this->insertCategory($name, $imgPath);
		} else {
			$category = $this->getCategoryOne($cid);
			if (count($category) > 0) {
				$this->modifyCategory($cid, $name, $imgPath);
				// remove image file
				if ($imgPath != "" && !empty($category[0]["image"])) @unlink($category[0]["image"]);
			}
		}
		return $response->withHeader('Location', 'maint')->withStatus(302);
	}
	
	private function modifyCategory($cid, $name, $imgPath) {

        $sql = 'UPDATE category SET '.
				'	name = :name ';
		if ($imgPath != "") {
			$sql .= '	,image = :image';
		}
		$sql.= 	' WHERE id = :cid ';

        // SQL実行準備
        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':cid', $cid);
        $statement->bindparam(':name', $name);
		if ($imgPath != "") {
			$statement->bindparam(':image', $imgPath);
		}
        // 値を渡して実行
        $result = $statement->execute();

        if ($imgPath != "") {
            // move_uploaded_file($_FILES["image"]["tmp_name"], $imgPath);
            Image::compressImage($_FILES["image"]["tmp_name"], $imgPath, 100);
        }
	}
	
	public function categoryDelete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$params = $request->getQueryParams();
		$id = $params['id'] ?? null;

		$products = $this->getProduct($id);

		if (count($products) > 0) {
			return $response->withHeader('Location', 'maint')->withStatus(302);
		}

		$category = $this->getCategoryOne($id);

		if (count($category)) {
			$this->deleteCategory($id);
			// remove image file
			if (!empty($category[0]["image"])) unlink($category[0]["image"]);
		}
		return $response->withHeader('Location', 'maint')->withStatus(302);
	}

	private function insertCategory($name, $imgPath) {

        $sql = 'INSERT INTO category (name, image
        ) VALUES (
            :name, :image)';

        // SQL実行準備
        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':name', $name);
        $statement->bindparam(':image', $imgPath);
        // 値を渡して実行
        $result = $statement->execute();

        if ($result) {
            // move_uploaded_file($_FILES["image"]["tmp_name"], $imgPath);
            Image::compressImage($_FILES["image"]["tmp_name"], $imgPath, 100);
        }
	}

	private function getCategoryName($categorys, $categoryId) {
		foreach($categorys as $c) {
			if ($c['id'] === $categoryId) {
				return $c['name'];
			}
		}
		return "すべての商品";
	}

	private function deleteCategory($id) {
		$sql = 'DELETE FROM category where id = :id';
        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':id', $id);
        $statement->execute();
	}

	private function getCategoryOne($id) {

		$sql = 'SELECT * from category where id = :id';

        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':id', $id);
        
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	// ---------------------
	// 商品
	// ---------------------
    public function productList(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$params = $request->getQueryParams();
		$categoryId = $params['id'] ?? null;

        $products = $this->getProduct($categoryId);
		$categorys = $this->container->get("view")->getEnvironment()->getGlobals()["categorys"];
		$categoryName = $this->getCategoryName($categorys, $categoryId);

		return $this->container->get('view')->render($response, 'maint-product.html', ['products' => $products, 'categoryName' => $categoryName, 'category_id' => $categoryId]);
    }

	public function productAdd(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
		$cid = $parsedBody['cid'] ?? 0;
		$pid = $parsedBody['pid'] ?? 0;
        $name = $parsedBody['name'] ?? "";
        $maker = $parsedBody['maker'] ?? "";
        $price = $parsedBody['price'] ?? "";
        $weight = $parsedBody['weight'] ?? "";
        $size = $parsedBody['size'] ?? "";
        $introduction = trim($parsedBody['introduction'] ?? "");
		
		$imgPath = [];
		$countfiles = count($_FILES['image']['name']);
		for ($i = 0; $i < $countfiles; $i++) {
			$filename = $_FILES['image']['name'][$i];
			if ($_FILES['image']['error'][$i] === UPLOAD_ERR_OK) {
				$imgPath[] =  Consts::PRODUCT_PATH. date('mdHis'). "_". $filename;
			} else {
				$imgPath[] = null;
			}
		}

		if (!file_exists(Consts::PRODUCT_PATH)) {
			mkdir(Consts::PRODUCT_PATH, 0777, true);
		}

		$param = array("categoryId" => $cid, "productId" => $pid, "name" => $name, "maker" => $maker, "price" => $price, 
						"weight" => $weight, "size" => $size, "image" => $imgPath, "introduction" => $introduction);

		if ($pid == 0) { // 追加
			$this->insertProduct($param);
		} else {
			$product = $this->getProductOne($pid);

			$this->modifyProduct($param);

			for ($i = 0; $i < count($imgPath); $i++) {
				
				if (!empty($imgPath[$i])) {
					Image::compressImage($_FILES["image"]["tmp_name"][$i], $imgPath[$i], 100);

					if (!empty($product[0]["image".ltrim($i, '0')])) @unlink($product[0]["image".ltrim($i, '0')]);
				}
			}
		}

		return $response->withHeader('Location', 'maint-product?id='.$cid)->withStatus(302);
	}

	public function productDelete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$params = $request->getQueryParams();
		$id = $params['id'] ?? null;
		$cid = $params['cId'] ?? null;

		$productInfo = $this->getProductOne($id);

		if (count($productInfo) > 0) {
			$this->deleteProduct($id);

			// remove image file
			if (!empty($productInfo[0]["image"])) unlink($productInfo[0]["image"]);
			if (!empty($productInfo[0]["image1"])) unlink($productInfo[0]["image1"]);
			if (!empty($productInfo[0]["image2"])) unlink($productInfo[0]["image2"]);
		}

		return $response->withHeader('Location', 'maint-product?id='.$cid)->withStatus(302);
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

	private function getProductOne($id) {

		$sql = 'SELECT * from products where id = :id';

        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':id', $id);
        
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	private function deleteProduct($id) {
		$sql = 'DELETE FROM products where id = :id';
        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':id', $id);
        $statement->execute();
	}

	private function insertProduct($param) {

        $sql = 'INSERT INTO products (category_id, name, maker, price, weight, size, image, image1, image2, introduction
        ) VALUES (
            :category_id, :name, :maker, :price, :weight, :size, :image, :image1, :image2, :introduction)';

        // SQL実行準備
        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':category_id', $param["categoryId"]);
        $statement->bindparam(':name', $param["name"]);
        $statement->bindparam(':maker', $param["maker"]);
        $statement->bindparam(':price', $param["price"]);
        $statement->bindparam(':weight', $param["weight"]);
        $statement->bindparam(':size', $param["size"]);
        $statement->bindparam(':introduction', $param["introduction"]);
        $statement->bindparam(':image', $param["image"][0]);
        $statement->bindparam(':image1', $param["image"][1]);
        $statement->bindparam(':image2', $param["image"][2]);
        // 値を渡して実行
        $result = $statement->execute();

        if ($result) {
			for ($i = 0; $i < count($param["image"]); $i++) {
				$imgPath = $param["image"][$i];
				if (!empty($imgPath)) {
					Image::compressImage($_FILES["image"]["tmp_name"][$i], $imgPath, 100);
				}
			}
        }
	}

	private function modifyProduct($param) {

        $sql = 'UPDATE products set 
					name = :name, 
					maker = :maker, 
					price = :price, 
					weight = :weight, 
					size = :size,';
		for ($i = 0; $i < count($param["image"]); $i++) {
			if (!empty($param["image"][$i])) {
				$sql .= 'image'.ltrim($i, '0').' = :image'.$i.', ';
			}	
		}
		$sql .= ' introduction = :introduction '.
				' WHERE id = :id ';

        // SQL実行準備
        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':id', $param["productId"]);
        $statement->bindparam(':name', $param["name"]);
        $statement->bindparam(':maker', $param["maker"]);
        $statement->bindparam(':price', $param["price"]);
        $statement->bindparam(':weight', $param["weight"]);
        $statement->bindparam(':size', $param["size"]);
		$statement->bindparam(':introduction', $param["introduction"]);
		for($i = 0; $i < count($param["image"]); $i++) {
			if (!empty($param["image"][$i])) {
				$statement->bindparam(':image'.$i, $param["image"][$i]);
			}
		}

        // 値を渡して実行
        $result = $statement->execute();
	}

}
