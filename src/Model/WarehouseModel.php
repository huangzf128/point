<?php

namespace App\Model;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;

/**
 * WarehouseModel
 */
final class WarehouseModel
{
	private $container;

	public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

	public function getWarehouse() {

		$sql = 'SELECT * from warehouse ';

        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function insertWarehouse($info) {

		$sql = 'INSERT INTO warehouse (warehouseName, address, tel, type) VALUES (:warehouseName, :address, :tel, :type)';
		
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':warehouseName', $info['warehouseName']);
		$statement->bindparam(':address', $info['address']);
		$statement->bindparam(':tel', $info['tel']);
		$statement->bindparam(':type', $info['type']);

		$result = $statement->execute();
	}

	public function deleteWarehouse($id) {

		$sql = "update warehouse set deleteFlag = 1 where id = :id";

		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
		$result = $statement->execute();
	}

	public function updateWarehouse($info) {
		$sql = "update warehouse set 
				warehouseName 	= :warehouseName,
				address 	= :address,
				tel 		= :tel,
				type 		= :type
				where id = :id";

		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':warehouseName', $info['warehouseName']);
		$statement->bindparam(':address', $info['address']);
		$statement->bindparam(':tel', $info['tel']);
		$statement->bindparam(':type', $info['type']);
		$statement->bindparam(':id', $info['id']);
		$result = $statement->execute();
	}
}

