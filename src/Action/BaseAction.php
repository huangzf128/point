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
class BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
		//$categorys = $this->getCatetory();

        //$container->get("view")->getEnvironment()->addGlobal('categorys', $this->getCatetory());

		$container->get("view")->getEnvironment()->addGlobal('settings', ['pagesizeSm' => 10, 'pagesize' => 20, 'pagesizelong' => 30]);
    }

	private function getCatetory() {

		$sql = 'SELECT * from category order by id ';
        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	protected function saveToFlash($key, $data) {
		$session = $this->container->get(Consts::SESSION);
		$session->set($key, $data);
	}

	protected function getFlash($key, $needClear = false) {
		$session = $this->container->get(Consts::SESSION);
		if ($session->exists($key))	{
			$data = $session->get($key);
			if ($needClear) {
				$session->delete($key);
			}
			return $data;
		}
		return "";
	}
}
