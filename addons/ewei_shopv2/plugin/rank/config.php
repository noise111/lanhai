<?php
echo "\r\n";

if (!defined('IN_IA')) {
	exit('Access Denied');
}

return array(
	'version' => '1.0',
	'id'      => 'rank',
	'name'    => '排行统计',
	'v3'      => true,
	'menu'    => array(
		'title'     => '页面',
		'plugincom' => 1,
		'icon'      => 'page',
		'items'     => array(
			array('title' => '榜单列表', 'route' => 'index'),			
			array('title' => '榜单类型', 'route' => 'category')
			)
		)
	);

?>
