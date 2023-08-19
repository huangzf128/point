<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;

/**
 * Action
 */
final class OrderAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
		parent::__construct($container);
        $this->container = $container;
    }

    public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
		$session = $this->container->get(Consts::SESSION);
		$items = [];
        if ($session->exists(Consts::CART)) {
            $items = $session[Consts::CART];
		}

		if (count($items) == 0 || empty($parsedBody['orders'])) {
			return $response->withHeader('Location', '/')->withStatus(302);
		}

		// save  quantity
        $orders = json_decode($parsedBody['orders']);
		foreach($orders as $itemid => $quantity) {
			if (array_key_exists($itemid, $items)) {
				$items[$itemid]["quantity"] = $quantity;
			}
		}
		$session->set(Consts::CART, $items);

		// 未ログイン
		if (!$session->exists('id')) {
			// return $response->withHeader('Location', '/cart')->withStatus(302);
			return $this->container->get('view')->render($response, 'cart.html', ['items' => $items, 'message' => "ログインしてください。"]);
		}

		$userid = $session['id'];
		$sql = 'INSERT INTO orders (user_id) VALUES (:userid)';
		// SQL実行準備
		$statement = $this->container->get('PDO')->prepare($sql);
		$statement->bindparam(':userid', $userid);

		// 値を渡して実行
		$result = $statement->execute();
		$orderid = $this->container->get('PDO')->lastInsertId();

		$sql = 'INSERT INTO order_detail (order_id, item_id, quantity) VALUES (:orderid, :itemid, :quantity)';
		$statement = $this->container->get('PDO')->prepare($sql);
		foreach($orders as $itemid => $quantity) {
			$statement->bindparam(':orderid', $orderid);
			$statement->bindparam(':itemid', $itemid);
			$statement->bindparam(':quantity', $quantity);
			// 値を渡して実行
			$result = $statement->execute();
		}
		$session->set(Consts::CART, []);
		return $this->container->get('view')->render($response, 'success.html', ['orderid'=> date("Ymd").str_pad($orderid, 6, 0, STR_PAD_LEFT)]);
    }

	public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$session = $this->container->get(Consts::SESSION);
		$orders = $this->getOrders($session["id"]);
		foreach($orders as $i => $order) {
			$orders[$i]['id'] = date( 'Ymd', strtotime($order['insert_dt']) ).str_pad($order['id'], 6, 0, STR_PAD_LEFT);
		}
		return $this->container->get('view')->render($response, 'order.html', ['orders' => $orders]);
	}

	private function getOrders($user_id) {

		$sql = 'SELECT  ord.insert_dt,
						ord.id,
						item.name,
						det.quantity,
						det.price
				from orders as ord
				left join order_detail as det
				  on ord.id = det.order_id
				 and det.delete_flag = 0
				left join products as item
				  on item.id = det.item_id
				 and item.delete_flag = 0
				where ord.user_id= :user_id 
				 and ord.delete_flag = 0 ';

        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':user_id', $user_id);
        
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	private function getUser($email, $password) {

		$sql = 'SELECT id, email_id, password from users where email_id= :email_id and  password= :password ';

        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':email_id', $email);
        $statement->bindparam(':password', $password);
        
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
