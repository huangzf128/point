<?php

namespace App\Model;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;

/**
 * CategoryModel
 */
final class CategoryModel
{
	private $container;

	public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

	public function getCategory() {

		$sql = 'SELECT id, categoryName from category ';

        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function insertCatetory($categoryName) {

		$sql = 'INSERT INTO category (categoryName) VALUES (:categoryName)';
		
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':categoryName', $categoryName);

		$result = $statement->execute();
	}
	
}
