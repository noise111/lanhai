<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台打印机设置
 */
global $_W,$_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'set';
if (checksubmit('submit')) {
    foreach ($_GPC['print'] as $key => $val) {
        $item = pdo_get('xcommunity_setting', array('xqkey' => $key,'uniacid'=> $_W['uniacid']), array('id'));
        $data = array(
            'xqkey' => $key,
            'value' => $val,
            'uniacid' => $_W['uniacid']
        );
        if ($item) {
            pdo_update('xcommunity_setting', $data, array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
        } else {
            pdo_insert('xcommunity_setting', $data);
        }
    }
    itoast('操作成功', referer(), 'success',ture);
}
$set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
if($op == 'list'){

    include $this->template('web/core/print/set');
}elseif($op =='fy'){

    include $this->template('web/core/print/fy');
}elseif ($op =='yl'){

    include $this->template('web/core/print/yl');
}
