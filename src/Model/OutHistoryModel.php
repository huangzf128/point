<?php

namespace App\Model;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;

/**
 * OutHistoryModel
 */
final class OutHistoryModel
{
	private $container;

	public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

	public function getOutHistory($ym, $customerId) {

		$sql = "SELECT inv.id, inv.insertDt, inv.updateDt, inv.operatorId, u.name, c.name AS customerName
				 from outhistory inv
				 left join user u
				 on 	u.id = inv.operatorId
				 left join customer c
				 on 	c.id = inv.customerId
				 where	inv.deleteFlag = 0 
				 and 	inv.ymd between :ymdFrom and :ymdTo
				 and 	(:customerId = '' or inv.customerId = :customerId)
				 order by inv.insertDt ";

        $statement = $this->container->get('PDO')->prepare($sql);
		$ymdFrom = $ym."-01";
		$statement->bindparam(':ymdFrom', $ymdFrom);
		$ymdTo = $ym."-31";
		$statement->bindparam(':ymdTo', $ymdTo);
		$statement->bindparam(':customerId', $customerId);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function getOutHistoryDetail($id) {

		$sql = 'SELECT o.*, i.serial, i.itemName, i.size, i.unit, w.warehouseName
				 from outhistorydetail o
				 left join item i
	 			 on i.id = o.itemId
			  	 left join warehouse w
	 			 on w.id = o.warehouseId
				 where	o.deleteFlag = 0 
				 and	o.id = :id
				 order by o.insertDt ';

        $statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function getOutHistoryDetailRange($customerId, $ym) {

		$sql = "SELECT sum(IfNull(detail.quantity, 0)) as quantity, detail.price,
						max(i.serial) as serial, max(i.itemName) as itemName, max(i.size) as size, max(i.unit) as unit
				 from outhistory inv
				 inner join outhistorydetail detail
				 on detail.id = inv.id
				 left join item i
	 			 on i.id = detail.itemId
				 where	inv.deleteFlag = 0 
				 and 	inv.ymd between :ymdFrom and :ymdTo
				 and 	(:customerId = '' or inv.customerId = :customerId)
				 group by detail.itemId, detail.price
				 order by detail.itemId, detail.price ";

        $statement = $this->container->get('PDO')->prepare($sql);
		$ymdFrom = $ym."-01";
		$statement->bindparam(':ymdFrom', $ymdFrom);
		$ymdTo = $ym."-31";
		$statement->bindparam(':ymdTo', $ymdTo);
		$statement->bindparam(':customerId', $customerId);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }	

	public function insertOutHistory($info) {

		$sql = "INSERT INTO outhistory (ymd, customerId, operatorId) 
				VALUES (:ymd, :customerId, :operatorId)";
		
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':ymd', $info["ymd"]);
		$statement->bindparam(':customerId', $info["customerId"]);
		$statement->bindparam(':operatorId', $info["userId"]);

		$result = $statement->execute();

		$lastId =  $this->container->get('PDO')->lastInsertId();
		return $lastId;
	}

	public function insertOutHistoryDetail($id, $row) {
		$sql = "INSERT INTO outhistorydetail (id, itemId, price, warehouseId, quantity) 
		VALUES (:id, :itemId, :price, :warehouseId, :quantity)";

		$statement = $this->container->get('PDO')->prepare($sql);

		$statement->bindparam(':id', $id);
		$statement->bindparam(':itemId', $row["itemId"]);
		$statement->bindparam(':price', $row["price"]);
		$statement->bindparam(':warehouseId', $row["warehouseId"]);
		$statement->bindparam(':quantity', $row["quantity"]);

		$result = $statement->execute();
	}

	public function updateOutHistoryById($id) {

		$sql = "update outhistory set 
				updateDt = now()
				where id = :id
				";
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
		$statement->execute();
		return $statement->rowCount();	
	}

	public function updateOutHistoryDetail($row) {
		$sql = "update outhistorydetail set
					quantity = :quantity,
					price 	 = :price,
					updateDt = now()
				where id = :id
				and	  warehouseId = :warehouseId
				and	  itemId 	  = :itemId
				";

		$statement = $this->container->get('PDO')->prepare($sql);

		$statement->bindparam(':id', $row["id"]);
		$statement->bindparam(':warehouseId', $row["warehouseId"]);
		$statement->bindparam(':itemId', $row["itemId"]);
		$statement->bindparam(':quantity', $row["quantity"]);
		$statement->bindparam(':price', $row["price"]);

		$statement->execute();
		return $statement->rowCount();	
	}

	public function deleteOutHistory($id) {
		$sql = "delete from outhistory where id = :id ";
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
		$result = $statement->execute();
		return $result;
	}

	public function deleteOutHistoryDetail($id) {
		$sql = "delete from outhistorydetail where id = :id";
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
		$result = $statement->execute();
		return $result;
	}
}
