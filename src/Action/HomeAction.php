<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

/**
 * Action
 */
final class HomeAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private $productPath = 'images/products/';

    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @return ResponseInterface The response
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // $response->getBody()->write((string)json_encode(['success' => true]));

        $list = ['username', 'ryu', 'password', '123456789', 'xyz'];
        $map = ['username' => 'ryu', 'password' => '5095757', 'age' => '1'];
        $age = ['0' => '<16', '1' => '16-30', '2' => '31-50', '3' => '51-80'];
        $str = 'test';

        // return $response->withHeader('Content-Type', 'application/json');
        return $this->container->get('view')->render($response, 'index.html', ['bodyClass' => 'home', 'menuSelected' => 'index']);
    }

    public function company(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // return $response->withHeader('Content-Type', 'application/json');
        return $this->container->get('view')->render($response, 'company.html', ['bodyClass' => '', 'menuSelected' => 'company']);
    }

    public function changeLanguage(ServerRequestInterface $request, ResponseInterface $response) {

      $parsedBody = $request->getParsedBody();
      // $this->container->set("language", $parsedBody['language']);
      $_SESSION['language'] = $parsedBody['language'];
      $data = ["language", $parsedBody['language']];
      $response->getBody()->write(json_encode($data));
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function service(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // return $response->withHeader('Content-Type', 'application/json');
        $imgArray[] = NULL;
        $imgPath = 'images';
        $directory = dir($imgPath);
        $i = 0;
        while ($dir = $directory->read()) {
            if ($dir == '.' || $dir == '..') {
                continue;
            }
            $imgArray[$i] = $imgPath . $dir;
        }
        return $this->container->get('view')->render($response, 'service.html', ['bodyClass' => '', 'menuSelected' => 'service', 'img' => $imgArray]);
    }

    public function access(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'access.html', ['bodyClass' => '', 'menuSelected' => 'access']);
    }

    public function faq(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'contact.html', ['bodyClass' => '', 'menuSelected' => 'contact']);
    }

    public function info(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'info.html', ['bodyClass' => '']);
    }

    public function charge(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'charge.html', ['bodyClass' => '']);
    }

    public function confirm(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'confirm.html', ['bodyClass' => '']);
    }

    public function contact(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // return $response->withHeader('Content-Type', 'application/json');
        return $this->container->get('view')->render($response, $args['viewId'] . '.html', ['bodyClass' => '']);
    }

    // public function contact2(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    // {    
    //     // return $response->withHeader('Content-Type', 'application/json');
    //     return $this->container->get('view')->render($response, 'contact2.html', ['bodyClass' => '']);
    // }

    // public function finsh(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    // {    
    //     // return $response->withHeader('Content-Type', 'application/json');
    //     return $this->container->get('view')->render($response, 'finsh.html', ['bodyClass' => '']);
    // }

    // public function form(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    // {    
    //     // return $response->withHeader('Content-Type', 'application/json');
    //     return $this->container->get('view')->render($response, 'form.html', ['bodyClass' => '']);
    // }


    // public function recruit(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    // {    
    //     // return $response->withHeader('Content-Type', 'application/json');
    //     return $this->container->get('view')->render($response, 'recruit.html', ['bodyClass' => '']);
    // }

    // public function service2(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    // {    
    //     // return $response->withHeader('Content-Type', 'application/json');
    //     return $this->container->get('view')->render($response, 'service2.html', ['bodyClass' => '']);
    // }

    // public function companyabout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    // {    
    //     // return $response->withHeader('Content-Type', 'application/json');
    //     return $this->container->get('view')->render($response, 'company.html#about', ['bodyClass' => '']);
    // }
}
