<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 下午1:33
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'del');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " t1.uniacid=:uniacid ";
    $params[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] == 2) {
        $condition .= " and t1.uid=:uid";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if ($_SESSION['apptype'] == 3) {
        $condition .= " and t2.regionid in (:regionid)";
        $params[':regionid'] = $_SESSION['appregionids'];
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $data['list'] = array();
        util::send_result($data);
    }
    $sql = "select t1.* from " . tablename("xcommunity_announcement") . "t1 left join" . tablename('xcommunity_announcement_region') . "t2 on t1.id=t2.aid where $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $row = pdo_fetchall($sql, $params);
//    $tsql = "select count(*) from " . tablename("xcommunity_announcement") . " as t1 left join " . tablename('xcommunity_announcement_region') . "as t2 on t1.id = t2.aid left join" . tablename('xcommunity_region') . "t3 on t3.id = t2.regionid where t1.uniacid=:uniacid and t2.regionid=:regionid and t1.enable= 1 and t1.status !=3 order by t1.createtime desc ";
//    $total = pdo_fetchcolumn($tsql, $params);
//    $pager = pagination($total, $pindex, $psize);
    $list = array();
    if ($row) {
        foreach ($row as $key => $val) {
//            $list[] = array(
//                'title'      => $val['title'],
//                'createtime' => date('Y-m-d H:i', $val['createtime']),
//                'url'        => ,
//                'content'    => str_replace('&nbsp;', '', strip_tags($val['reason'])),
//                'rtitle'     => $val['rtitle'],
//                'id'         => $val['id']
//            );
//            if (strlen($val['reason']) > 90) {
//                $desc = mb_substr(str_replace('&nbsp;', '', strip_tags($val['reason'])), 0, 90, 'UTF-8') . '...';
//            } else {
//                $desc = str_replace('&nbsp;', '', strip_tags($val['reason']));
//            }
            if (strlen($val['reason']) > 90) {
                $desc = mb_substr(str_replace('&nbsp;', '', strip_tags($val['reason'])), 0, 90, 'UTF-8') . '...';
            }
            else {
                $desc = str_replace('&nbsp;', '', strip_tags($val['reason']));
            }
            $list[] = array(
                'title' => $val['title'],
                'desc'  => $desc,
                'url'   => $this->createMobileUrl('xqsys', array('op' => 'announcement', 'p' => 'detail', 'id' => $val['id'])),
                'meta'  => array(
                    'source' => $val['rtitle'],
                    'date'   => date('Y-m-d H:i', $val['createtime'])
                )
            );
        }
    }

    $data = array();
    $data['list'] = $list;

    util::send_result($data);
}
elseif ($op == 'del') {
    $id = intval($_GPC['id']);
    $item = pdo_get('xcommunity_announcement', array('id' => $id), array());
    if ($item) {
        if (pdo_delete("xcommunity_announcement", array('id' => $id))) {
            util::permlog('小区公告-删除', '信息标题:' . $item['title']);
            pdo_delete('xcommunity_announcement_region', array('aid' => $id));
            util::send_result();

        }
    }
}
elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_announcement', array('id' => $id), array());
        $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
        if ($item['id'] && $_W['member']['uid']) {
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
        $regions = pdo_fetchall("select t1.id,t1.title from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_announcement_region') . "t2 on t1.id=t2.regionid where t2.aid=:aid", array(":aid" => $item['id']));

        $region = '';
        $regionids = '';
        $all = array();
        foreach ($regions as $k => $v) {
            $region .= $v['title'] . ',';
            $regionids .= $v['id'] . ',';
            $all[] = $v['id'];
        }
        $item['regions'] = $regions;
        $item['region'] = xtrim($region);
        $item['regionids'] = xtrim($regionids);
        $item['reason'] = strip_tags($item['reason']);
        $item['thumb'] = explode(',', $item['pic']);
        $item['list'] = $all;
        foreach ($item['thumb'] as $ke => $va) {
            $item['thumb'][$ke] = tomedia($va);
        }
        util::send_result($item);
    }
}
elseif ($op == 'add') {
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        util::send_error(2, '权限不足');
    }
    $regionid = explode(',', xtrim($_GPC['regionid']));
    $region = pdo_get('xcommunity_region', array('id' => $regionid[0]), array('province', 'city', 'dist'));
    $data = array(
        'title'      => trim($_GPC['title']),
        'reason'     => trim($_GPC['reason']),
        'createtime' => TIMESTAMP,
        'uniacid'    => $_W['uniacid'],
        'status'     => 2,
        'enable'     => 1,//1显示2隐藏
        'pic'        => trim($_GPC['morepic']),
        'type'       => 1,
        'province'   => $region['province'],
        'city'       => $region['city'],
        'dist'       => $region['dist']
    );
    $id = intval($_GPC['id']);
    if (empty($id)) {
        $data['uid'] = $_SESSION['appuid'];
        pdo_insert("xcommunity_announcement", $data);
        $id = pdo_insertid();
    }
    else {
        pdo_update("xcommunity_announcement", $data, array('id' => $id));
        pdo_delete('xcommunity_announcement_region', array('aid' => $id));
    }


    foreach ($regionid as $key => $value) {
        $dat = array(
            'aid'      => $id,
            'regionid' => $value,
        );
        pdo_insert('xcommunity_announcement_region', $dat);
    }
    if (set('t1')) {
        $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&op=detail&do=announcement&m=" . $this->module['name'];
        $reason = htmlspecialchars_decode($data['reason']);
        $content = str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $reason);
        $content = strip_tags($content, '<a>');
        $tplid = set('t2');
        $ddata = array(
            'first'    => array(
                'value' => '',
            ),
            'keyword1' => array(
                'value' => $data['title'],
            ),
            'keyword2' => array(
                'value' => date('Y-m-d H:i', TIMESTAMP),
            ),
            'keyword3' => array(
                'value' => $content,
            ),
            'remark'   => array(
                'value' => '',
            )
        );


        //可发送业主
        $regionids = ltrim(rtrim($_GPC['regionid'], ","), ',');
        $sql = "select t1.uid,t2.openid from" . tablename('xcommunity_member') . "t1 left join" . tablename('mc_mapping_fans') . "t2 on t1.uid = t2.uid where t1.regionid in({$regionids}) group by t2.openid";
        $members = pdo_fetchall($sql);
        foreach ($members as $key => $val) {
            $user = pdo_get('xcommunity_send_log', array('uid' => $val['uid'], 'sendid' => $id), array('status', 'id'));
            if (empty($user) || $user['status'] == 2) {
                if ($val['openid']) {
                    $resp = util::sendTplNotice($val['openid'], $tplid, $ddata, $url, $topcolor = '#FF683F');
                }
                $d = array(
                    'uniacid' => $_W['uniacid'],
                    'sendid'  => $id,
                    'uid'     => $val['uid'],
                    'type'    => 1,
                    'cid'     => 1,
                );
                if (empty($resp['errcode'])) {
                    $d['status'] = 1;
                    pdo_insert('xcommunity_send_log', $d);
                }
                else {
                    $d['status'] = 2;
                    pdo_insert('xcommunity_send_log', $d);
                }
            }
        }
    }
    util::send_result();
}