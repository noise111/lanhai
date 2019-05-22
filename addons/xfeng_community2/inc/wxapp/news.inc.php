<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/29 下午2:41
 * 消息
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if($op =='list'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $regionid = intval($_GPC['regionid']);
    $list = pdo_fetchall("select * from" . tablename('xcommunity_user_news') . "where uniacid=:uniacid and regionid=:regionid and uid=:uid order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':uniacid' => $_W['uniacid'], ':regionid' => $regionid,':uid'=> $_W['member']['uid']));
    foreach ($list as $k => $v){
        $list[$k]['createtime'] = date('Y/m/d',$v['createtime']);
    }
    if ($list) {
        $data = array();
        $data['list'] = $list;
        util::send_result($data);
    }
    else {
        util::send_error(-1, '');
    }
}