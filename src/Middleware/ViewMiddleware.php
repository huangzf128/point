<?php
namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\App;

class ViewMiddleware
{

    public $app;

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        session_start();
        $container = $this->app->getContainer();
        $view = $container->get("view");
        $view->getEnvironment()->addGlobal('tm', date('Ymdhis'));

        if (array_key_exists('language', $_SESSION)) {
          $view->getEnvironment()->addGlobal('language', $_SESSION['language']);
        }
        
        return $handler->handle($request);
    }

    public function __construct(App $app) {
        $this->app = $app;
    }
}