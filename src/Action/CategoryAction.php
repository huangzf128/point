<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;
use App\Model\CategoryModel;

/**
 * Action
 */
final class CategoryAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
		parent::__construct($container);
        $this->container = $container;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$model = new CategoryModel($this->container);
		$categorys = $model->getCategory();

		return $this->container->get('view')->render($response, 'category.html', 
				['categorys' => $categorys]);
    }

	public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		$categoryName = $parsedBody['categoryname'];
		if ($categoryName) {
			$model = new CategoryModel($this->container);
			$model->insertCatetory($categoryName);
		}
		return $response->withHeader('Location', '/category')->withStatus(302);
    }

}
