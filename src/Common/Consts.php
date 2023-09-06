<?php

namespace App\Common;

class Consts
{
	public const CART = "cart";
	public const SESSION = "session";

	public const CATEGORY_PATH = 'images/categorys/';
	public const PRODUCT_PATH = 'images/products/';

	public const ESTIMATE_TOTAL_ROWNO = 415;

	public const WAREHOUSE_NORMAL = 1;
	public const WAREHOUSE_VISUAL = 2;
	public const WAREHOUSE = [Consts::WAREHOUSE_NORMAL => '実倉庫', 
							  Consts::WAREHOUSE_VISUAL => '仮想倉庫'];

}
