<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台模板消息设置
 */
global $_W,$_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (checksubmit('submit')) {
    foreach ($_GPC['tpl'] as $key => $val) {
        $item = pdo_get('xcommunity_setting', array('xqkey' => $key,'uniacid'=>$_W['uniacid']), array('id'));
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
    itoast('操作成功', referer(), 'success',true);
    util::permlog('','修改模板消息库');
}
$set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
include $this->template('web/core/set/tpl');