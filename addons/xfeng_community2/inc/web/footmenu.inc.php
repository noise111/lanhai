<?php
/**
 * Created by xqms.cn.
 * User: 蓝牛科技
 * Time: 2017/8/20 下午9:39
 */
global $_W, $_GPC;
$ops = array('list', 'add', 'delete', 'change');
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
 * 底部菜单列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 'uniacid=:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_footmenu') . " WHERE $condition order by displayorder asc,id asc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_footmenu') . " WHERE $condition order by displayorder", $params);
    $pager = pagination($total, $pindex, $psize);
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $id => $displayorder) {
            pdo_update('xcommunity_footmenu', array('displayorder' => $displayorder), array('id' => $id));
        }
        itoast('排序更新成功！', 'refresh', 'success', ture);
    }
    include $this->template('web/core/footmenu/list');
}
/**
 * 底部菜单的添加修改
 */
if ($op == 'add') {
    $id = intval($_GPC['id']);
    $regions = model_region::region_fetall();
    if ($id) {
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_footmenu') . "WHERE id=:id", array(":id" => $id));
        $regs = pdo_getall('xcommunity_footmenu_region', array('fid' => $id), array('regionid'));
        $regionid = '';
        foreach ($regs as $key => $val) {
            $regionid .= $val['regionid'] . ',';
        }
        $regionid = ltrim(rtrim($regionid, ","), ',');
    }
    if ($_W['isajax']) {
        if (empty($_GPC['url'])) {
            $url = '#';
        } else {
            $url = $_GPC['url'];
        }
        $data = array(
            'uniacid' => $_W['uniacid'],
            'displayorder' => $_GPC['displayorder'],
            'title' => $_GPC['title'],
            'url' => $url,
            'enable' => intval($_GPC['enable']),
            'icon' => $_GPC['icon'],
            'click_icon' => $_GPC['click_icon'],
            'type' => intval($_GPC['type']),
        );
        if ($id) {
            pdo_update('xcommunity_footmenu', $data, array('id' => $id));
            pdo_delete('xcommunity_footmenu_region', array('fid' => $id));
            util::permlog('底部菜单管理-修改', '修改菜单名称:' . $data['title']);
        } else {
            pdo_insert('xcommunity_footmenu', $data);
            $id = pdo_insertid();
            util::permlog('底部菜单管理-添加', '添加菜单名称:' . $data['title']);
        }
        $regionids = $_GPC['regionid'];
        foreach ($regionids as $key => $value) {
            $dat = array(
                'fid' => $id,
                'regionid' => $value,
            );
            pdo_insert('xcommunity_footmenu_region', $dat);
        }
        echo json_encode(array('status' => 1));
        exit();
    }
    $options=array();
    $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
    include $this->template('web/core/footmenu/add');
}
/**
 * 底部菜单的删除
 */
if ($op == 'delete') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        itoast('缺少参数', referer(), 'error');
    }
    $item = pdo_fetch("SELECT id,title FROM" . tablename('xcommunity_footmenu') . "WHERE uniacid=:uniacid AND id=:id", array(':id' => $id, ':uniacid' => $_W['uniacid']));
    if (empty($item)) {
        itoast('该信息不存在或已被删除', referer(), 'error', ture);
    }
    $r = pdo_delete("xcommunity_footmenu_region", array('fid' => $id));
    pdo_delete("xcommunity_footmenu", array('id' => $id));
    itoast('删除成功', referer(), 'success', ture);
}
/**
 * 底部菜单的状态改变
 */
if ($op == 'change') {
    $id = intval($_GPC['id']);
    $enable = intval($_GPC['enable']);
    $enable = $enable == 2 ? 1 : 2;
    if ($id) {
        if (pdo_update('xcommunity_footmenu', array('enable' => $enable), array('id' => $id))) {
            echo json_encode(array('status' => 1));
            exit();
        }
    }
}
