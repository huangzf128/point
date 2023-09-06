<?php

namespace App\Model;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;

/**
 * UserModel
 */
final class UserModel
{
	private $container;

	public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

	public function getUser($info) {

		$sql = 'SELECT id, name, type from user
				where id = :id and  password = :password and deleteFlag = "0" ';

        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':id', $info['id']);
        $statement->bindparam(':password', $info['password']);
        
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function getAllUser() {

		$sql = 'SELECT * from user';

        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function insertUser($info) {

		$sql = 'INSERT INTO user (id, name, password, type) VALUES (:id, :name, :password, :type)';
		
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $info['id']);
		$statement->bindparam(':name', $info['name']);
		$statement->bindparam(':password', $info['password']);
		$statement->bindparam(':type', $info['type']);

		$result = $statement->execute();
	}

	public function deleteUser($id) {

		$sql = "update user set deleteflag = 1 where id = :id";

		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':id', $id);
		$result = $statement->execute();
	}

	public function updateUser($info) {
		$sql = "update user set 
				name 		= :name,
				type 	= :type,
				password = case when :password = '' then password else :password end
				where id = :id";

		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':name', $info['name']);
		$statement->bindparam(':type', $info['type']);
		$statement->bindparam(':password', $info['password']);
		$statement->bindparam(':id', $info['id']);
		$result = $statement->execute();
	}
}
