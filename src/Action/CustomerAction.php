<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;
use App\Model\CustomerModel;

/**
 * Action
 */
final class CustomerAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
		parent::__construct($container);
        $this->container = $container;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {

		$params = $request->getQueryParams();
		$kana = $params['kana'] ?? '';

		$model = new CustomerModel($this->container);
		$customers = $model->getCustomer($kana);

		$kanas = $this->getKanas();

		return $this->container->get('view')->render($response, 'customer.html', 
				['customers' => $customers, 'kanas' => $kanas, 'kana' => $kana]);
    }

	public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		$info = [
			'name' => $parsedBody['name'],
			'kana' => $parsedBody['kana'],
			'managerName' => $parsedBody['managerName'],
			'postcode' => $parsedBody['postcode'],
			'address' => $parsedBody['address'],
			'tel' => $parsedBody['tel'],
			'fax' => $parsedBody['fax'],
			'remark' => $parsedBody['remark']
		];

		if ($this->checkRequired($info)) {
			$model = new CustomerModel($this->container);
			$model->insertCustomer($info);
		}
		return $response->withHeader('Location', '/customer')->withStatus(302);
    }

	public function remove(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		if (!empty($parsedBody['id'])) {
			$model = new CustomerModel($this->container);
			$model->deleteCustomer($parsedBody['id']);
		}
		return $response->withHeader('Location', '/customer')->withStatus(302);
	}

	public function modify(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		$info = [
			'name' => $parsedBody['name'],
			'kana' => $parsedBody['kana'],
			'managerName' => $parsedBody['managerName'],
			'postcode' => $parsedBody['postcode'],
			'address' => $parsedBody['address'],
			'tel' => $parsedBody['tel'],
			'fax' => $parsedBody['fax'],
			'remark' => $parsedBody['remark'],
			'id' => $parsedBody['id']
		];

		if ($this->checkRequired($info)) {
			$model = new CustomerModel($this->container);
			$model->updateCustomer($info);
		}
		return $response->withHeader('Location', '/customer')->withStatus(302);
    }

	private function getKanas() {
		$kanasStr = "あ,い,う,え,お,か,き,く,け,こ,さ,し,す,せ,そ,た,ち,つ,て,と,な,に,ぬ,ね,の,は,ひ,ふ,へ,ほ,ま,み,む,め,も,や,ゆ,よ,ら,り,る,れ,ろ,わ";
		$kanas = explode(",", $kanasStr);
		return $kanas;
	}

	private function checkRequired($params) {
		return !empty($params['name']) && !empty($params['kana']);
	}
}
