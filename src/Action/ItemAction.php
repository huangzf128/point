<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;
use App\Model\ItemModel;
use App\Model\CategoryModel;

/**
 * ItemAction
 */
final class ItemAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
		parent::__construct($container);
        $this->container = $container;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$model = new ItemModel($this->container);
		$items = $model->getItem();

		$categoryModel = new CategoryModel($this->container);
		$categorys = $categoryModel->getCategory();

		return $this->container->get('view')->render($response, 'item.html', ['items' => $items, 'categorys' => $categorys]);
    }

	public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();

		if (!empty($parsedBody['serial']) && !empty($parsedBody['itemName'])) {
			$iteminfo = ['serial' => $parsedBody['serial'],
						 'itemName' => $parsedBody['itemName'], 
						 'price' => $parsedBody['price'], 
						 'size' => $parsedBody['size'],
						 'weight' => $parsedBody['weight'],
						 'unit' => $parsedBody['unit'],
						 'categoryId' => $parsedBody['categoryId']
						];
	
			$model = new ItemModel($this->container);
			$model->insertItem($iteminfo);
		}

		return $response->withHeader('Location', '/item')->withStatus(302);
	}

	public function remove(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		if ($parsedBody['id']) {
			$model = new ItemModel($this->container);
			$model->deleteItem($parsedBody['id']);
		}
		return $response->withHeader('Location', '/item')->withStatus(302);
	}

	public function modify(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();

		if (!empty($parsedBody['serial']) && !empty($parsedBody['itemName']) && !empty($parsedBody['id'])) {

			$info = [
				'serial' => $parsedBody['serial'],
				'itemName' => $parsedBody['itemName'],
				'price' => $parsedBody['price'],
				'size' => $parsedBody['size'],
				'weight' => $parsedBody['weight'],
				'unit' => $parsedBody['unit'],
				'categoryId' => $parsedBody['categoryId'],
				'id' => $parsedBody['id']
			];

			$model = new ItemModel($this->container);
			$model->updateItem($info);
		}
		return $response->withHeader('Location', '/item')->withStatus(302);
    }
}