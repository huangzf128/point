<?php

namespace App\Model;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;

/**
 * InHistoryModel
 */
final class InHistoryModel
{
	private $container;

	public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

	public function getInHistory($warehouseId, $ym) {

		$sql = "SELECT i.id, i.insertDt, i.updateDt, i.operatorId, u.name, w.warehouseName
				 from inhistory i
				 left join user u
				 on 	u.id = i.operatorId
				 left join warehouse w
				 on 	w.id = i.warehouseId
				 where	i.deleteFlag = 0 
				 and   	(:warehouseId = '' or i.warehouseId = :warehouseId)
				 and 	i.ymd between :ymdFrom and :ymdTo
				 order by i.insertDt ";

        $statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':warehouseId', $warehouseId);
		$ymdFrom = $ym."-01";
		$statement->bindparam(':ymdFrom', $ymdFrom);
		$ymdTo = $ym."-31";
		$statement->bindparam(':ymdTo', $ymdTo);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function getInHistoryDetail($id) {

		$sql = 'SELECT inv.*, i.serial, i.itemName, i.price, i.size, i.unit
				 from inhistorydetail inv
				 left join item i
	 			 on i.id = inv.itemId
				 where	inv.deleteFlag = 0 
				 and	inv.id = :id
				 order by inv.insertDt ';

        $statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function insertInHistory($info) {

		$sql = "INSERT INTO inhistory (warehouseId, ymd, operatorId) 
				VALUES (:warehouseId, :ymd, :operatorId)";
		
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':warehouseId', $info["warehouseId"]);
		$statement->bindparam(':ymd', $info["ymd"]);
		$statement->bindparam(':operatorId', $info["userId"]);

		$result = $statement->execute();

		$lastId =  $this->container->get('PDO')->lastInsertId();
		return $lastId;
	}

	public function insertInHistoryDetail($id, $row) {
		$sql = "INSERT INTO inhistorydetail (id, itemId, quantity) 
		VALUES (:id, :itemId, :quantity)";

		$statement = $this->container->get('PDO')->prepare($sql);

		$statement->bindparam(':id', $id);
		$statement->bindparam(':itemId', $row["itemId"]);
		$statement->bindparam(':quantity', $row["quantity"]);

		$result = $statement->execute();
	}

	public function updateInHistoryDetail($row) {
		$sql = "update inhistorydetail set
					quantity = :quantity,
					updateDt = now()
				where id = :id
				and	  itemId = :itemId
				";

		$statement = $this->container->get('PDO')->prepare($sql);

		$statement->bindparam(':id', $row["id"]);
		$statement->bindparam(':itemId', $row["itemId"]);
		$statement->bindparam(':quantity', $row["quantity"]);

		$result = $statement->execute();
	}

	public function updateInHistoryById($id) {

		$sql = "update inhistory set 
				updateDt	= now()
				where id = :id
				";
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
		$result = $statement->execute();
		return $result;		
	}

	public function deleteInHistory($id) {
		$sql = "delete from inhistory where id = :id ";
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
		$result = $statement->execute();
		return $result;
	}

	public function deleteInHistoryDetail($id) {
		$sql = "delete from inhistorydetail where id = :id ";
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
		$result = $statement->execute();
		return $result;
	}
}
