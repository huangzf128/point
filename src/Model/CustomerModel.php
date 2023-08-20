<?php

namespace App\Model;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;

/**
 * CustomerModel
 */
final class CustomerModel
{
	private $container;

	public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

	public function getCustomer($kana = "") {

		$sql = "SELECT * from customer where deleteFlag = 0 
				and (:kana = '' or kana = :kana)
				order by kana ";

        $statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':kana', $kana);

        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function getCustomerById($id) {

		$sql = "SELECT * from customer where deleteFlag = 0 
				and id = :id";

        $statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);

        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function insertCustomer($info) {

		$sql = 'INSERT INTO customer (name, kana, managerName, postcode, address, tel, fax, remark) 
				VALUES (:name, :kana, :managerName, :postcode, :address, :tel, :fax, :remark)';
		
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':name', $info['name']);
		$statement->bindparam(':kana', $info['kana']);
		$statement->bindparam(':managerName', $info['managerName']);
		$statement->bindparam(':postcode', $info['postcode']);
		$statement->bindparam(':address', $info['address']);
		$statement->bindparam(':tel', $info['tel']);
		$statement->bindparam(':fax', $info['fax']);
		$statement->bindparam(':remark', $info['remark']);

		$result = $statement->execute();
	}

	public function deleteCustomer($id) {

		$sql = "update customer set deleteFlag = 1 where id = :id";

		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
		$result = $statement->execute();
	}

	public function updateCustomer($info) {
		$sql = "update customer set 
				name 		= :name,
				kana 		= :kana,
				managerName	= :managerName,
				postcode 	= :postcode,
				address 	= :address,
				tel 		= :tel,
				fax 		= :fax,
				remark 		= :remark
				where id = :id";

		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':name', $info['name']);
		$statement->bindparam(':kana', $info['kana']);
		$statement->bindparam(':managerName', $info['managerName']);
		$statement->bindparam(':postcode', $info['postcode']);
		$statement->bindparam(':address', $info['address']);
		$statement->bindparam(':tel', $info['tel']);
		$statement->bindparam(':fax', $info['fax']);
		$statement->bindparam(':remark', $info['remark']);
		$statement->bindparam(':id', $info['id']);
		$result = $statement->execute();
	}
}
