<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/12/4 上午10:20
 */
global $_GPC, $_W;
$ops = array('list', 'detail');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}

if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " t1.uniacid=:uniacid and t2.regionid=:regionid and t1.enable= 1 and t1.status !=3";
    $params[':uniacid'] = $_W['uniacid'];
    $params[':regionid'] = intval($_GPC['regionid']) ? intval($_GPC['regionid']) : $_SESSION['community']['regionid'];
    $sql = "select t1.id,t1.title,t1.createtime,t3.title as rtitle,t1.reason from " . tablename("xcommunity_announcement") . " as t1 left join " . tablename('xcommunity_announcement_region') . "as t2 on t1.id = t2.aid left join" . tablename('xcommunity_region') . "t3 on t3.id = t2.regionid where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;


    $row = pdo_fetchall($sql, $params);

    $list = array();
    if ($row) {
        foreach ($row as $key => $val) {
            if (strlen($val['reason']) > 90) {
                $desc = mb_substr(str_replace('&nbsp;', '', strip_tags($val['reason'])), 0, 90, 'UTF-8') . '...';
            }
            else {
                $desc = str_replace('&nbsp;', '', strip_tags($val['reason']));
            }
            $num = pdo_fetchcolumn('select count(*) from' . tablename('xcommunity_reading_member') . "where aid=:aid", array(':aid' => $val['id']));
            $read = pdo_get('xcommunity_reading_member',array('aid'=>$val['id'],'uid'=>$_W['member']['uid']));
            $status = $read ? '':'&nbsp;&nbsp;<span style="color:red" class="default_yd">未读</span>';
            $list[] = array(
                'title' => $val['title'],
                'desc'  => $desc,
                'url'   => $this->createMobileUrl('announcement', array('op' => 'detail', 'id' => $val['id'])),
                'meta'  => array(
                    'source' => '',
                    'date'   => date('Y-m-d H:i', $val['createtime']),
                    'other'  => '阅读数:' . $num.$status
                ),
            );

        }
    }

    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);


}
elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_announcement', array('id' => $id), array());
        if (empty($item)) {
            util::send_result(array('code' => 10003, 'content' => '该公告不存在或已经删除', 'url' => $this->createMobileUrl('home')));
        }
        $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
        if ($item['id'] && $_W['member']['uid']) {
            //写入阅读
            $reading = pdo_get('xcommunity_reading_member', array('aid' => $item['id'], 'uid' => $_W['member']['uid'], 'regionid' => $_SESSION['community']['regionid']), array('id'));
            if (empty($reading)) {
                $data = array(
                    'uniacid'  => $_W['uniacid'],
                    'aid'      => $item['id'],
                    'uid'      => $_W['member']['uid'],
                    'regionid' => $_SESSION['community']['regionid']
                );
                pdo_insert('xcommunity_reading_member', $data);
            }
        }
//        $regions = pdo_fetchall("select t1.id,t1.title from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_announcement_region') . "t2 on t1.id=t2.regionid where t2.aid=:aid", array(":aid" => $item['id']));
//
//        $region = '';
//        $regionids = '';
//        foreach ($regions as $k => $v) {
//            $region .= $v['title'] . ',';
//            $regionids .= $v['id'] . ',';
//        }
//        $item['regions'] = $regions;
//        $item['region'] = xtrim($region);
//        $item['regionids'] = xtrim($regionids);
        $item['reason'] = $item['reason'];
        $thumb = explode(',', $item['pic']);
        foreach($thumb as $k => $v){
            $thumb[$k] = tomedia($v);
        }
        $item['thumb'] = $thumb;
        $item['hstatus'] = set('p96') ? 1 : 0;
        $item['regiontitle'] = $_SESSION['community']['title'];
        util::send_result($item);
    }
}
