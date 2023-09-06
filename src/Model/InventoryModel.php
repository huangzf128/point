<?php

namespace App\Model;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;

/**
 * InventoryModel
 */
final class InventoryModel
{
	private $container;

	public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

	public function getInventory($warehouseId) {

		$sql = 'SELECT inv.id, warehouseId, itemId, quantity, inv.updateDt, i.itemName, i.serial, i.price, i.size, w.warehouseName
				 from inventory inv
				 left join item i
				 on i.id = inv.itemId
				 left join warehouse w
				 on w.id = inv.warehouseId
				 where	inv.deleteFlag = 0 
				 and   	inv.warehouseId = :warehouseId
				 order by inv.updateDt desc ';

        $statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':warehouseId', $warehouseId);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function getUnManagedItem($warehouseId) {
		$sql = 'SELECT i.*, c.categoryName 
		from item i
		left join category c
		on c.id = i.categoryId
		where i.deleteFlag = 0
		and not exists ( select 1 from inventory where itemId = i.id and warehouseId = :warehouseId and deleteFlag = 0)
		';

		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':warehouseId', $warehouseId);
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	public function insertInventory($inventoryInfo) {

		$sql = 'INSERT INTO inventory (warehouseId, itemId, quantity) 
				VALUES (:warehouseId, :itemId, :quantity)';
		
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':warehouseId', $inventoryInfo["warehouseId"]);
		$statement->bindparam(':itemId', $inventoryInfo["itemId"]);
		$statement->bindparam(':quantity', $inventoryInfo["quantity"]);

		$result = $statement->execute();
	}

	public function deleteInventory($id) {
		$sql = "update inventory set deleteFlag = 1 where id = :id";

		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
		$result = $statement->execute();
	}

	public function updateInventoryByKey($info) {

		$sql = "update inventory set 
				quantity = :quantity,
				updateDt = now()
				where id = :id
				";

		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':quantity', $info['quantity']);
		$statement->bindparam(':id', $info['id']);
		$statement->execute();
		return $statement->rowCount();
	}

	public function updateInventory($info) {

		$sql = "update inventory set 
					 quantity = quantity + :quantity
					,updateDt = now()
				where itemId = :itemId
				and   warehouseId = :warehouseId
				and	  deleteFlag = 0
				";

		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':quantity', $info['quantity']);
		$statement->bindparam(':itemId', $info['itemId']);
		$statement->bindparam(':warehouseId', $info['warehouseId']);

		$statement->execute();
		return $statement->rowCount();
	}
}
