<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {

    $condtainer = $app->getContainer();

    $app->any('/', '\App\Action\HomeAction:index')->setName('index');
    $app->get('/index', '\App\Action\HomeAction:index')->setName('index');
    $app->get('/product-detail', '\App\Action\HomeAction:productDetail')->setName('product-detail');
    $app->any('/contact', '\App\Action\HomeAction:contact')->setName('contact');

	$app->any('/about', '\App\Action\HomeAction:about')->setName('about');
	$app->any('/personal', '\App\Action\HomeAction:personal')->setName('personal');
	$app->any('/specificProduct', '\App\Action\HomeAction:specificProduct')->setName('specificProduct');
	$app->any('/ship', '\App\Action\HomeAction:ship')->setName('ship');
	$app->any('/chartership', '\App\Action\HomeAction:chartership')->setName('chartership');


	$app->post('/login', '\App\Action\AuthAction:login')->setName('login');
	$app->get('/logout', '\App\Action\AuthAction:logout')->setName('logout');
	$app->post('/signup', '\App\Action\AuthAction:signup')->setName('signup');
	

	$app->any('/product', '\App\Action\ProductAction:list')->setName('product-list');
	$app->any('/detail', '\App\Action\ProductAction:detail')->setName('detail');

	$app->any('/cart', '\App\Action\CartAction:list')->setName('cart-list');
	$app->any('/cart-add', '\App\Action\CartAction:add')->setName('cart-add');
	$app->any('/cart-remove', '\App\Action\CartAction:remove')->setName('cart-remove');

	$app->any('/order', '\App\Action\OrderAction:add')->setName('order');
	$app->any('/order-list', '\App\Action\OrderAction:list')->setName('order-list');


	$app->any('/maint', '\App\Action\MaintenanceAction:categoryList')->setName('maint-category-list');
	$app->any('/maint-category-add', '\App\Action\MaintenanceAction:categoryAdd')->setName('maint-category-add');
	$app->any('/maint-category-delete', '\App\Action\MaintenanceAction:categoryDelete')->setName('maint-category-delete');
	$app->any('/maint-product', '\App\Action\MaintenanceAction:productList')->setName('maint-product-list');
	$app->any('/maint-product-add', '\App\Action\MaintenanceAction:productAdd')->setName('maint-product-add');
	$app->any('/maint-product-delete', '\App\Action\MaintenanceAction:productDelete')->setName('maint-product-delete');
	$app->any('/maint-user', '\App\Action\MaintenanceAction:userList')->setName('maint-user-list');


    // $app->post('/language', '\App\Action\HomeAction:changeLanguage')->setName('language');
    // $app->post('/mail', '\App\Action\MailAction:mail')->setName('mail');

    $app->get('/{viewId}', '\App\Action\HomeAction:contact')->setName('contact');


};
