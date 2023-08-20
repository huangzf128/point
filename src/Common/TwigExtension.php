<?php

namespace App\Common;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use App\Common\consts;

class TwigExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('userType', [$this, 'getUserType']),
			new TwigFilter('getWarehouseType', [$this, 'getWarehouseType'])
        ];
    }

    public function getUserType($type)
    {
        return $type != null ? ($type == "9"? "管理者": "利用者" ) : "未ログイン";
    }

	public function getWarehouseType($type)
    {
        return Consts::WAREHOUSE[$type];
    }
}