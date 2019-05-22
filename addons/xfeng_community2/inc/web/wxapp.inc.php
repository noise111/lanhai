<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/4/4 上午12:41
 */
global $_W, $_GPC;
$ops = array('list', 'add', 'delete', 'download');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 打包小程序列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 'uniacid =:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    $sql = "SELECT * FROM" . tablename('xcommunity_wxapp_setting') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_wxapp_setting') . "WHERE $condition", $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/wxapp/display');
}
/**
 * 打包小程序的添加
 */
if ($op == 'add') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_wxapp_setting', array('id' => $id));
    }
    if ($_W['isajax']) {
        $data = array(
            'uniacid' => $_W['uniacid'],
            'appid' => trim($_GPC['appid']),
            'appsecret' => trim($_GPC['appsecret']),
            'title' => trim($_GPC['title']),
            'url' => trim($_GPC['url']),
            'packtitle' => trim($_GPC['packtitle']),
            'packname' => time(),
            'createtime' => TIMESTAMP
        );
        if ($id) {
            pdo_update('xcommunity_wxapp_setting', $data, array('id' => $id));
        } else {
            pdo_insert('xcommunity_wxapp_setting', $data);
        }
        echo json_encode(array('status' => 1));
        exit();
    }
    include $this->template('web/core/wxapp/display');
}
/**
 * 打包小程序的删除
 */
if ($op == 'delete') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_wxapp_setting', array('id' => $id));
        if ($item) {
            /**
             * 删除小程序包
             */
            load()->func('file');
            $zip = MODULE_ROOT . '/data/wxapp/' . $item['packname'] . ".zip";
            file_delete($zip);
            if (pdo_delete('xcommunity_wxapp_setting', array('id' => $id))) {
                itoast('删除成功', referer(), 'success');
            }
        }
    }
}
/**
 * 打包小程序下载
 */
if ($op == 'download') {
    $id = intval($_GPC['id']);
    if ($id) {
        //$title = pdo_getcolumn('xcommunity_wxapp_setting', array('id' => $id), 'packname');
        $item = pdo_get('xcommunity_wxapp_setting',array('id'=>$id));
//        $url = $_W['siteroot'] . 'app/index.php';
        $str = "
module.exports = {
    'uniacid': " . $_W['uniacid'] . ", 
    'version': '1.0.0',
    'siteroot': '" . $item['url'] . "',
};";
        $path = MODULE_ROOT . '/data/wxapp/dist/siteinfo.js';
        file_put_contents($path, $str);
        unset($path);
        //打包下载
        $path = MODULE_ROOT . '/data/wxapp/dist/';
        $zip = MODULE_ROOT . '/data/wxapp/' . $item['packname'] . ".zip";
        //$zip = "./" . time() . ".zip"; //最终生成的文件名（含路径）
        download2($path, $zip);
    }
}