<?php

namespace App\Common;

use PDO;
use App\Common\Consts;

class Cart
{
	public static function addItem (&$items, $id, $name = "", $price = "-1", $number = "1") {
		// array_push($this->items, $this->createItem($id, $name, $price, $number));
		$items[$id] = self::createItem($name, $price, $number);
	}

	public static function updateItems(&$items, $pdo) {
		if (count($items) == 0) return;

		$itemInfos = self::getCartItem($pdo, array_keys($items));
		foreach ($itemInfos as $info) {
			$item = &$items[$info["id"]];
			$item['name'] = $info["name"];
			$item['price'] = $info["price"];
		}
	}

	public static function removeItem(&$items, $id) {
		unset($items[$id]);
	}

	private static function createItem($name, $price, $number) {
		return ["name" => $name, "price" => $price, "number" => $number];
	}

	private static function getCartItem($pdo, $ids) {

		$sql = 'SELECT * 
				from products 
				where id in ('. implode(',', $ids) .')';

        $statement = $pdo->prepare($sql);
        
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
