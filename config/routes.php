<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {

    $condtainer = $app->getContainer();

    $app->any('/', '\App\Action\CommonAction:index')->setName('index');
	$app->any('/about', '\App\Action\CommonAction:about')->setName('about');
	$app->any('/common/invitem', '\App\Action\CommonAction:invItem')->setName('common-invitem');

	// 認証
	$app->get('/auth', '\App\Action\AuthAction:index')->setName('auth');
	$app->post('/login', '\App\Action\AuthAction:login')->setName('login');
	$app->get('/logout', '\App\Action\AuthAction:logout')->setName('logout');
	$app->post('/signup', '\App\Action\AuthAction:signup')->setName('signup');

	// 分類
	$app->any('/category', '\App\Action\CategoryAction:index')->setName('category');
	$app->any('/category/add', '\App\Action\CategoryAction:add')->setName('category-add');
	$app->any('/category/modify', '\App\Action\CategoryAction:modify')->setName('category-modify');
	$app->any('/category/remove', '\App\Action\CategoryAction:remove')->setName('category-remove');

	// 倉庫管理
	$app->any('/warehouse', '\App\Action\WarehouseAction:index')->setName('warehouse');
	$app->any('/warehouse/add', '\App\Action\WarehouseAction:add')->setName('warehouse-add');
	$app->any('/warehouse/modify', '\App\Action\WarehouseAction:modify')->setName('warehouse-modify');
	$app->any('/warehouse/remove', '\App\Action\WarehouseAction:remove')->setName('warehouse-remove');

	// 商品
	$app->any('/item', '\App\Action\ItemAction:index')->setName('item');
	$app->any('/item/add', '\App\Action\ItemAction:add')->setName('item-add');
	$app->any('/item/modify', '\App\Action\ItemAction:modify')->setName('item-modify');
	$app->any('/item/remove', '\App\Action\ItemAction:remove')->setName('item-remove');

	// 取引先管理
	$app->any('/customer', '\App\Action\CustomerAction:index')->setName('customer');
	$app->any('/customer/add', '\App\Action\CustomerAction:add')->setName('customer-add');
	$app->any('/customer/modify', '\App\Action\CustomerAction:modify')->setName('customer-modify');
	$app->any('/customer/remove', '\App\Action\CustomerAction:remove')->setName('customer-remove');

	// ユーザ
	$app->any('/user', '\App\Action\UserAction:index')->setName('user');
	$app->any('/user/add', '\App\Action\UserAction:add')->setName('user-add');
	$app->any('/user/modify', '\App\Action\UserAction:modify')->setName('user-modify');
	$app->any('/user/remove', '\App\Action\UserAction:remove')->setName('user-remove');
	
	// 在庫管理
	$app->any('/inventory', '\App\Action\InventoryAction:index')->setName('inventory');
	$app->any('/inventory/add', '\App\Action\InventoryAction:add')->setName('inventory-add');
	$app->any('/inventory/modify', '\App\Action\InventoryAction:modify')->setName('inventory-modify');
	$app->any('/inventory/remove', '\App\Action\InventoryAction:remove')->setName('inventory-remove');

	// 入庫
	$app->any('/in', '\App\Action\InAction:index')->setName('in');
	$app->any('/in/add', '\App\Action\InAction:add')->setName('in-add');
	$app->any('/in/modify', '\App\Action\InAction:modify')->setName('in-modify');
	$app->any('/in/remove', '\App\Action\InAction:remove')->setName('in-remove');

	// 出庫
	$app->any('/out', '\App\Action\OutAction:index')->setName('out');
	$app->any('/out/add', '\App\Action\OutAction:add')->setName('out-add');
	$app->any('/out/modify', '\App\Action\OutAction:modify')->setName('out-modify');
	$app->any('/out/remove', '\App\Action\OutAction:remove')->setName('out-remove');
	
	// 帳票
	$app->any('/report', '\App\Action\ReportAction:index')->setName('report');
	$app->any('/reportTotal', '\App\Action\ReportAction:indexTotal')->setName('report');
	$app->any('/report/print', '\App\Action\ReportAction:print')->setName('report-print');
	$app->any('/report/getOutHistory', '\App\Action\ReportAction:getOutHistory')->setName('report-getOutHistory');
	$app->any('/report/getOutHistoryDetailRange', '\App\Action\ReportAction:getOutHistoryDetailRange')->setName('report-getOutHistoryDetailRange');

	$app->any('/import', '\App\Action\ImportAction:index')->setName('import');
	$app->any('/import/item', '\App\Action\ImportAction:item')->setName('import-item');
	$app->any('/import/customer', '\App\Action\ImportAction:customer')->setName('import-customer');


    $app->get('/{viewId}', '\App\Action\HomeAction:contact')->setName('contact');
};