<?php

global $_GPC, $_W;
$ops = array('list', 'detail', 'add');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
/**
 * 问卷列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $cond['uniacid'] = $_W['uniacid'];
    $cond['enable'] = 1;
    //是否开启强制绑定小区
    $condition = '';
    $status = set('p159');
    if ($status) {
        $condition['regionid'] = $_SESSION['community']['regionid'];
        $region = pdo_getall('xcommunity_vote_region', $condition, array('voteid'));
        $region_voteids = _array_column($region, 'voteid');
        $cond['id'] = $region_voteids;
    }
    $rows = pdo_getslice('xcommunity_vote', $cond, array($pindex, $psize), $total, array(), '', array('id DESC'));
    $list = array();
    foreach ($rows as $k => $val) {
        $show = '';
        if ($val['starttime'] <= time() && $val['endtime'] >= time()) {
            $show = 1;
        }
        $list[] = array(
            'title'     => $val['title'],
            'id'        => $val['id'],
            'thumb'     => tomedia($val['thumb']),
            'show'      => $show,
            'datetime'  => date('Y-m-d', $val['starttime']) . '~' . date('Y-m-d', $val['endtime']),
            'url'       => $this->createMobileUrl('vote', array('op' => 'add', 'id' => $val['id'])),
            'content'   => strip_tags($val['content']),
            'starttime' => date('Y/m/d', $val['starttime']),
            'endtime'   => date('Y/m/d', $val['endtime']),
            'total'     => $val['num']
        );
    }
    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);
} elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    $data = array();
    $data['status'] = set('p96') ? 1 : 0;
    if (pdo_getcolumn('xcommunity_vote_user', array('uid' => $_W['member']['uid'], 'voteid' => $id), 'id')) {
        $data['auth'] = 1;
    }
    $weekarray = array("日", "一", "二", "三", "四", "五", "六");
    if ($id) {
        $item = pdo_get('xcommunity_vote', array('id' => $id), array());
        $starttime = date('Y年m月d日H:i', $item['starttime']) . '(周' . $weekarray[date('w', $item['starttime'])] . ')';
        $endtime = date('Y年m月d日H:i', $item['endtime']) . '(周' . $weekarray[date('w', $item['endtime'])] . ')';
        $user = pdo_getcolumn('xcommunity_vote_user', array('uid' => $_W['member']['uid'], 'voteid' => $id), 'id');
    }

    if ($item['endtime'] < TIMESTAMP) {
        $data['auth'] = 2;
    }
    if ($item) {
        $li = pdo_fetchall("select id,title,remark,type,sort,maxnum from" . tablename('xcommunity_vote_question') . " where voteid=:voteid order by sort asc", array('voteid' => $id));
        foreach ($li as $k => $v) {
//            $li[$k]['options'] = pdo_fetchall("select id,title,sort from" . tablename('xcommunity_vote_options') . " where questionid=:questionid order by sort asc", array('questionid' => $v['id']));
            $li[$k]['num'] = (int)$v['maxnum'] ? (int)$v['maxnum'] : 1000;
            $options = pdo_fetchall("select id,title,sort from" . tablename('xcommunity_vote_options') . " where questionid=:questionid order by sort asc", array('questionid' => $v['id']));
            foreach ($options as $ke => $va) {
                $li[$k]['options'][$ke]['key'] = $v['id'] . '-' . $va['id'];
                $li[$k]['options'][$ke]['value'] = $va['title'];
            }
        }
        $list = array(
            'title'     => $item['title'],
            'content'   => $item['content'],
            'starttime' => $starttime,
            'endtime'   => $endtime,
            'question'  => $li,
            'userid'    => $user,
            'thumb'     => tomedia($item['thumb']),
        );
        $data['list'] = $list;
        util::send_result($data);
    }

}
/**
 * 问卷调查提交
 */
if ($op == 'add') {
    $data = $_GPC['data'];
//    $dxdas = explode(',', xtrim(trim($_GPC['dxdas'])));
//    $szdas = explode(',', xtrim(trim($_GPC['szdas'])));
//    $tkdas = explode(',', xtrim(trim($_GPC['tkdas'])));
    $dxdas = $_GPC['dxdas'];
    $szdas = $_GPC['szdas'];
    $tkdas = $_GPC['tkdas'];
    $id = intval($_GPC['id']);

    $data = array(
        'uid'        => $_W['member']['uid'],
        'voteid'     => $id,
        'openid'     => $_W['openid'],
        'createtime' => TIMESTAMP,
        'addressid'  => $_SESSION['community']['addressid'],
        'enable'     => intval($_GPC['enable']) ? intval($_GPC['enable']) : 0
    );
    if (pdo_insert('xcommunity_vote_user', $data)) {
        $userid = pdo_insertid();
        pdo_update('xcommunity_vote', array('num +=' => 1), array('id' => $id));
        if ($dxdas) {
            foreach ($dxdas as $ke => $va) {
                foreach ($va as $k => $v) {
                    $v = explode('-', $v);
                    $d = array(
                        'userid'     => $userid,
                        'questionid' => $v[0],
                        'optionsid'  => $v[1],
                        'createtime' => TIMESTAMP,
                        'type'       => 1
                    );
                    pdo_insert('xcommunity_vote_user_answer', $d);
                }
            }
        }
        if ($szdas) {
            foreach ($szdas as $ke => $va) {
                foreach ($va as $k => $v) {
                    $v = explode('-', $v);
                    $d = array(
                        'userid'     => $userid,
                        'questionid' => $v[0],
                        'optionsid'  => $v[1],
                        'createtime' => TIMESTAMP,
                        'type'       => 2
                    );
                    pdo_insert('xcommunity_vote_user_answer', $d);
                }
            }
        }
        if ($tkdas) {
            foreach ($tkdas as $k => $v) {
                $d = array(
                    'userid'     => $userid,
                    'questionid' => $k,
                    'content'    => $v,
                    'createtime' => TIMESTAMP,
                    'type'       => 3
                );
                pdo_insert('xcommunity_vote_user_answer', $d);
            }
        }
        util::send_result();
    }
}