<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/10/22 下午3:49
 */
global $_GPC,$_W;
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$condition = 't1.uniacid =:uniacid';
$params[':uniacid'] = $_W['uniacid'];
$sql = "SELECT t1.*,t2.username FROM" . tablename('xcommunity_users_log') . "t1 left join".tablename('users')."t2 on t1.uid = t2.uid WHERE $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;

$list = pdo_fetchall($sql,$params);

$tsql = "SELECT count(*) FROM" . tablename('xcommunity_users_log') . "t1 left join".tablename('users')."t2 on t1.uid = t2.uid WHERE $condition order by t1.createtime desc ";
$total = pdo_fetchcolumn($sql,$params);
$pager = pagination($total, $pindex, $psize);
include $this->template('web/core/xqlog');