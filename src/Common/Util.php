<?php
namespace App\Common;

use App\Common\Consts;

class Util
{
    /**
     * Convert all applicable characters to HTML entities.
     *
     * @param string $text The string
     *
     * @return string The html encoded string
     */
    public static function html(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

	public static function getQueryParam($params, $key) 
	{
		if (empty($params) || array_key_exists($key, $params)) return null;
		
		return $params[$key];
	}

	public static function getUserId($container) {
		$session = $container->get(Consts::SESSION);
		$auth = $session->get('auth');
		return $auth['userId'];
	}

	public static function sanitizeTelorPost($s) {
		$s = Util::full2HalfNumber($s);
		$s = Util::full2Halfhyphen($s);
		$s = Util::removeSpace($s);
		return $s;
	}

	public static function full2HalfNumber($str) {
		return mb_convert_kana($str ?? "", "n");
	}

	public static function full2Halfhyphen($str) {
		return preg_replace('/－/', '-', ($str ?? ""));
	}

	public static function removeSpace($str) {
		return preg_replace('/\s+/', '', ($str ?? ""));
	}

}
