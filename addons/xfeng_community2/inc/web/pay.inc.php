<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台支付方式设置
 */
global $_W,$_GPC;
$id = intval($_GPC['id']);
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if ($op == 'add') {
	
	if (checksubmit('submit')) {
        $type = intval($_GPC['type']) ? intval($_GPC['type']) : 1;
		$data = array(
            'uniacid' => $_W['uniacid'],
            'type' => $_GPC['type'],
            'wechat' => intval($_GPC['wechat']),
            'alipay' => intval($_GPC['alipay']),
            'balance' => intval($_GPC['balance']),
            'delivery' => intval($_GPC['delivery']),
            'sub' => intval($_GPC['sub']),
            'swiftpass' => intval($_GPC['swiftpass']),
            'hsyunfu' => intval($_GPC['hsyunfu']),
            'chinaums' => intval($_GPC['chinaums']),
			);
		if ($id) {
			pdo_update('xcommunity_pay',$data,array('id' => $id));
		}else{
			if ($data['type']) {
				$item = pdo_fetch("SELECT * FROM".tablename('xcommunity_pay')."WHERE uniacid=:uniacid AND type=:type",array(':uniacid' => $_W['uniacid'],':type' => $data['type']));
				if ($item) {
					itoast('类型已存在，无需在添加',referer(),'error',ture);exit();
				}
			}
			pdo_insert('xcommunity_pay',$data);
		}
			itoast('提交成功',$this->createWebUrl('pay',array('op' => 'list')),'success',ture);
	}
	if ($id) {
		$set = pdo_fetch("SELECT * FROM".tablename('xcommunity_pay')."WHERE id=:id",array(':id' => $id));
	}
	include $this->template('web/core/pay/add');
}elseif ($op == 'list') {
	$list = pdo_fetchall("select * from " . tablename('xcommunity_pay') . ' where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
	include $this->template('web/core/pay/list');
}elseif ($op == 'delete') {
	if (empty($id)) {
		itoast('缺少参数',referer(),'error');
	}
	$r = pdo_delete("xcommunity_pay",array('id'=>$id));
	if ($r) {
		itoast('删除成功',referer(),'success',ture);
	}
}