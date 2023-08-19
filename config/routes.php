<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {

    $condtainer = $app->getContainer();

    $app->get('/', '\App\Action\HomeAction:index')->setName('index');
    $app->get('/index', '\App\Action\HomeAction:index')->setName('index');

    $app->get('/access', '\App\Action\HomeAction:access')->setName('access');
    $app->get('/charge', '\App\Action\HomeAction:charge')->setName('charge');
    $app->get('/company', '\App\Action\HomeAction:company')->setName('company');
    $app->get('/confirm', '\App\Action\HomeAction:confirm')->setName('homconfirme');
    $app->get('/contact', '\App\Action\HomeAction:faq')->setName('faq');
    $app->get('/info', '\App\Action\HomeAction:info')->setName('info');
    $app->get('/service', '\App\Action\HomeAction:service')->setName('service');

    $app->get('/product', '\App\Action\ProductionAction:product')->setName('product');
    $app->any('/productSelect', '\App\Action\ProductionAction:productSelect')->setName('productSelect')->add('csrf');
    $app->get('/productInsertInit', '\App\Action\ProductionAction:productInsertInit')->setName('productInsertInit')->add('csrf');
    $app->post('/productInsert', '\App\Action\ProductionAction:productInsert')->setName('productInsert')->add('csrf');
    $app->post('/productUpdate', '\App\Action\ProductionAction:productUpdate')->setName('productUpdate')->add('csrf');
    $app->post('/productDelete', '\App\Action\ProductionAction:productDelete')->setName('productDelete')->add('csrf');

    $app->post('/language', '\App\Action\HomeAction:changeLanguage')->setName('language');
    $app->post('/mail', '\App\Action\MailAction:mail')->setName('mail');

    $app->get('/{viewId}', '\App\Action\HomeAction:contact')->setName('contact');
    //$app->get('/contact2', '\App\Action\HomeAction:contact2')->setName('home');
    // $app->get('/finsh', '\App\Action\HomeAction:finsh')->setName('home');
    // $app->get('/form', '\App\Action\HomeAction:form')->setName('home');
    // $app->get('/index', '\App\Action\HomeAction:index')->setName('home');
    // $app->get('/recruit', '\App\Action\HomeAction:recruit')->setName('home');
    // $app->get('/service2', '\App\Action\HomeAction:service2')->setName('home');
    // $app->get('/companyabout', '\App\Action\HomeAction:companyabout')->setName('home');

};
