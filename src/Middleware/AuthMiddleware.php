<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\App;
use Slim\Psr7\Factory\ResponseFactory;
use App\Common\Consts;

/**
 * session[auth]
 * 	userid, username
 *  role: anonymous, user, admin
 */
class AuthMiddleware
{
    public $app;

    /**
     * Constructor
     *
     * @param   App  $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
		$uri = $request->getUri();
		$noAuthPath = ['/auth', '/login', '/signup'];

		if (in_array($uri->getPath(), $noAuthPath)) {
			return $handler->handle($request);
		}

		if(!isset($_SESSION)) { 
			session_start(); 
		}
		
        $container = $this->app->getContainer();
		$session = $container->get(Consts::SESSION);
		if ($session->exists('auth')) {

			$container->get("view")->getEnvironment()->addGlobal('auth', $session->get('auth'));
			return $handler->handle($request);
		} else {
			$response = new \Slim\Psr7\Response();
			return $response->withHeader('Location', '/auth')->withStatus(302);
		}
    }

}
