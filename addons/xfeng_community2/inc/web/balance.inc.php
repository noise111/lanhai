<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/18 下午6:16
 */
global $_W, $_GPC;
$op = in_array(trim($_GPC['op']), array('category', 'list')) ? trim($_GPC['op']) : 'category';
$p = in_array(trim($_GPC['p']), array('list', 'add', 'del')) ? trim($_GPC['p']) : 'list';
$regions = model_region::region_fetall();
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if ($op == 'category') {
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t2.title LIKE '%{$_GPC['keyword']}%'";
        }
        if (intval($_GPC['regionid'])) {
            $condition .= " and t1.regionid =:regionid";
            $params[':regionid'] = intval($_GPC['regionid']);
        }
        if ($user&&$user[type] != 1) {
            //普通管理员
            $condition .= " AND t1.uid=:uid";
            $params[':uid'] = $_W['uid'];
        }
        $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_balance_category') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "SELECT count(*) FROM" . tablename('xcommunity_balance_category') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id WHERE $condition order by t1.id desc ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/balance/category/list');
    }
    elseif ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_balance_category', array('id' => $id), array());
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'uid'      => $_W['uid'],
                'regionid' => intval($_GPC['regionid']),
                'category' => trim($_GPC['category']),
                'remark'   => trim($_GPC['remark']),
                'status'   => intval($_GPC['status']),
                'type'     => intval($_GPC['type'])
            );
            if ($id) {
                $result = pdo_update('xcommunity_balance_category', $data, array('id' => $id));
            }
            else {
                $result = pdo_insert('xcommunity_balance_category', $data);
            }
            if ($result) {
                echo json_encode(array('status'=>1));exit();
            }
        }
        include $this->template('web/core/balance/category/add');
    }elseif ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
        }
        $item = pdo_get('xcommunity_balance_category', array('id' => $id), array('id'));
        if ($item) {
            if (pdo_delete('xcommunity_balance_category', array('id' => $id))) {
                itoast('删除成功', referer(), 'success');
            }
        }
    }
}
elseif ($op == 'list') {
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t2.title LIKE '%{$_GPC['keyword']}%'";
        }
        if (intval($_GPC['regionid'])) {
            $condition .= " and t1.regionid =:regionid";
            $params[':regionid'] = intval($_GPC['regionid']);
        }
        if ($user[type] != 1) {
            //普通管理员
            $condition .= " AND t1.uid=:uid";
            $params[':uid'] = $_W['uid'];
        }
        $sql = "SELECT t1.id,t2.title,t3.build,t3.unit,t3.room,t1.price,t1.username,t1.usertime,t1.status,t1.remark,t1.category FROM" . tablename('xcommunity_balance') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id left join" . tablename('xcommunity_member_room') . "t3 on t1.room=t3.id WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "SELECT count(*) FROM" . tablename('xcommunity_balance') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id WHERE $condition order by t1.id desc ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/balance/list/list');
    }
    elseif ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_balance', array('id' => $id), array());
            $arr = util::xqset($item['regionid']);
            if ($arr[a]) {
                $areas = pdo_getall('xcommunity_area', array('regionid' => $regionid), array('id', 'title'));

            }
            if ($arr[b]) {
                $condition = " regionid=:regionid";
                $param[':regionid'] = $item['regionid'];
                if($item['area']){
                    $condition .= " and areaid=:areaid";
                    $param[':areaid'] = $item['area'];
                }
                $builds = pdo_fetchall("select * from" . tablename('xcommunity_build') . "where $condition", $param);
            }
            if ($arr[c]) {
                $condition_unit = " buildid=:buildid";
                $param_unit[':buildid'] = $item['build'];
                $units = pdo_fetchall("select * from" . tablename('xcommunity_unit') . "where $condition_unit", $param_unit);
            }
            if ($arr[d]) {
                $condition_room = " unitid=:unitid";
                $param_room[':unitid'] = $item['unit'];
                $rooms = pdo_fetchall("select * from" . tablename('xcommunity_member_room') . "where $condition_room", $param_room);
            }
            $categories = pdo_getall('xcommunity_balance_category', array('regionid'=>$item['regionid']), array('category', 'id'));
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'uid'      => $_W['uid'],
                'regionid' => intval($_GPC['regionid']),
                'area'     => intval($_GPC['area']),
                'build'    => intval($_GPC['build']),
                'unit'     => intval($_GPC['unit']),
                'room'     => intval($_GPC['addressid']),
                'price'    => $_GPC['price'],
                'category' => $_GPC['category'],
                'username' => $_GPC['username'],
                'usertime' => strtotime($_GPC['usertime']),
                'remark'   => $_GPC['remark'],
                'type'     => intval($_GPC['type']),

            );
            if ($id) {
                $result = pdo_update('xcommunity_balance', $data, array('id' => $id));
            }
            else {
                $result = pdo_insert('xcommunity_balance', $data);
            }
            echo json_encode(array('status'=>1));exit();
        }
        include $this->template('web/core/balance/list/add');
    }
    elseif ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
        }
        $item = pdo_get('xcommunity_balance', array('id' => $id), array('id'));
        if ($item) {
            if (pdo_delete('xcommunity_balance', array('id' => $id))) {
                itoast('删除成功', referer(), 'success');
            }
        }
    }
}
