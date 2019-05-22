<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/1/14 上午3:50
 */
global $_GPC, $_W;
$ops = array('sms', 'commission', 'delete');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'sms';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
/**
 * 短信记录
 */
if ($op == 'sms') {
    $regions = model_region::region_fetall();
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $condition = " t1.uniacid=:uniacid and t1.cid=2";
    $params[':uniacid'] = $_W['uniacid'];
    $regionid = intval($_GPC['regionid']);
    if ($regionid) {
        $condition .= " and t1.regionid=:regionid";
        $params[':regionid'] = $regionid;
    }
    $sql = "select t1.*,t2.title from " . tablename("xcommunity_send_log") . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $total = pdo_fetchcolumn('select count(*) from' . tablename("xcommunity_send_log") . "t1 where $condition", $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/log/sms');
}
/**
 * 分成记录
 */
if ($op == 'commission') {
    $regions = model_region::region_fetall();
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $condition = " t1.uniacid=:uniacid";
    $params[':uniacid'] = $_W['uniacid'];
    $regionid = intval($_GPC['regionid']);
    if ($regionid) {
        $condition .= " and t1.regionid=:regionid";
        $params[':regionid'] = $regionid;
    }
    $cid = intval($_GPC['cid']);
    if ($cid) {
        $condition .= " and t1.cid=:cid";
        $params[':cid'] = $cid;
    }
    $sql = "select t1.*,t2.title from " . tablename("xcommunity_commission_log") . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $total = pdo_fetchcolumn('select count(*) from' . tablename("xcommunity_commission_log") . "t1 where $condition", $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/log/commission');
}
/**
 * 分成记录的删除
 */
if ($op == 'delete') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        itoast('缺少参数');
        exit();
    }
    $item = pdo_get('xcommunity_commission_log', array('id' => $id));
    if ($item) {
        if (pdo_delete('xcommunity_commission_log', array('id' => $id))) {
            itoast('删除成功', referer(), 'success');
        }
    }
}