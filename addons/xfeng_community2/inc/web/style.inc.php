<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台菜单和风格设置
 */

	global $_W,$_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
	load()->model('setting');
	$community = 'community'.$_W['uniacid'];
	$style = $_W['setting'][$community]['styleid'];
	if(checksubmit('submit')) {
		$data = array(
			'styleid' => $_GPC['styleid'],
		);
		setting_save($data, $community);
		itoast('更新设置成功！', referer(),'success');
	}
	$path = IA_ROOT . '/addons/'.$this->module['name'].'/template/mobile/';
	if(is_dir($path)) {  //判定文件名是否是一个目录
		if ($handle = opendir($path)) { //打开指定目录
			while (false !== ($templatepath = readdir($handle))) { //读取指定目录
				if ($templatepath != '.' && $templatepath != '..' && $templatepath != 'app' && $templatepath != 'app2' && $templatepath != 'public') {
					if(is_dir($path.$templatepath)){
						$template[] = $templatepath;
					}
				}
			}
		}
	}
util::permlog('','修改手机端风格');
	include $this->template('web/core/style/style');