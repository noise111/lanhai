<?php
/**
 * 蓝牛科技 Copyright (c) app.xqms.cn
 */

if (!defined('ES_PATH')) {
	exit('Access Denied');
}

class EmptyController extends Controller
{
	public function index()
	{
		global $controller;
		trigger_error(' Controller <b>' . $controller . '</b> Not Found !');
	}
}


?>