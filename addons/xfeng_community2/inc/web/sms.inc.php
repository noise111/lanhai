<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台短信设置
 */
global $_W, $_GPC;
$op = in_array($_GPC['op'], array('jh', 'list', 'wwt','aliyun_new','qhyx')) ? $_GPC['op'] : 'list';
if (checksubmit('submit')) {
    foreach ($_GPC['sms'] as $key => $val) {
        $item = pdo_get('xcommunity_setting', array('xqkey' => $key,'uniacid' => $_W['uniacid']), array('id'));
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
    itoast('操作成功', referer(), 'success');
}
$set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
if ($op == 'list') {

    include $this->template('web/core/set/sms');
} elseif ($op == 'jh') {

    include $this->template('web/core/set/juhe');
} elseif ($op == 'wwt') {

    include $this->template('web/core/set/wwt');
}
elseif ($op == 'aliyun_new') {

    include $this->template('web/core/set/aliyun_new');
}
elseif ($op == 'qhyx') {

    include $this->template('web/core/set/qhyx');
}
