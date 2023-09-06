<?php

namespace App\Model;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;

/**
 * ItemModel
 */
final class ItemModel
{
	private $container;

	public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

	public function getItem($deleteFlag = 0) {

		$sql = 'SELECT i.*, c.categoryName 
				from item i
				left join category c
				on c.id = i.categoryId
				where i.deleteFlag = :deleteFlag
				';

        $statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':deleteFlag', $deleteFlag);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function insertItem($info) {

		$sql = 'INSERT INTO item (serial, itemName, categoryId, price, size, weight, unit ) 
				VALUES (:serial, :itemName, :categoryId, :price, :size, :weight, :unit)';
		
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':serial', $info['serial']);
		$statement->bindparam(':itemName', $info['itemName']);
		$statement->bindparam(':price', $info['price']);
		$statement->bindparam(':size', $info['size']);
		$statement->bindparam(':weight', $info['weight']);
		$statement->bindparam(':unit', $info['unit']);
		$statement->bindparam(':categoryId', $info['categoryId']);

		$result = $statement->execute();
	}

	public function deleteItem($id) {
		$sql = "update item set deleteFlag = 1 where id = :id";

		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
		$result = $statement->execute();
	}

	public function updateItem($info) {
		$sql = "update item set 
				serial 		= :serial,
				itemName 	= :itemName,
				price 		= :price,
				size 		= :size,
				weight 		= :weight,
				unit 		= :unit,
				categoryId 	= :categoryId
				where id 	= :id";

		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':serial', $info['serial']);
		$statement->bindparam(':itemName', $info['itemName']);
		$statement->bindparam(':price', $info['price']);
		$statement->bindparam(':size', $info['size']);
		$statement->bindparam(':weight', $info['weight']);
		$statement->bindparam(':unit', $info['unit']);
		$statement->bindparam(':categoryId', $info['categoryId']);
		$statement->bindparam(':id', $info['id']);
		$result = $statement->execute();
	}
}
