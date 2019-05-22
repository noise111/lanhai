<?php
global $_GPC, $_W;
$ops = array('list', 'detail', 'getCategory');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " t1.uniacid=:uniacid and t2.regionid=:regionid";
    $params[':uniacid'] = $_W['uniacid'];
    $params[':regionid'] = $_SESSION['community']['regionid'] ? $_SESSION['community']['regionid'] : 1;
    if ($_GPC['cid']) {
        $condition .= " and t1.cid=:cid";
        $params[':cid'] = intval($_GPC['cid']);
    }
    $sql = "select t1.* from" . tablename('xcommunity_plugin_article_message') . "t1 left join" . tablename('xcommunity_plugin_article_region') . "t2 on t1.id=t2.articleid where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $row = pdo_fetchall($sql, $params);
    $list = array();
    foreach ($row as $k => $val) {
        $pic = explode(',', $val['pic']);
        $list[] = array(
            'title' => $val['title'],
            'id' => $val['id'],
            'url' => $this->createMobileUrl('article', array('op' => 'detail', 'id' => $val['id'])),
            'desc' => $val['content'],
            'src' => $pic[0] ? tomedia($pic[0]) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png',
        );
    }
    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);
} elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        $detail = pdo_get('xcommunity_plugin_article_message', array('id' => $id), array('title', 'pic', 'content', 'cid', 'id'));

        $pic = explode(',', $detail['pic']);
        $pics = array();
        foreach ($pic as $k => $v) {
            $pics[$k]['img'] = tomedia($v);
        }
        $data = array(
            'title' => $detail['title'],
            'pic' => $pics,
            'content' => $detail['content'],
            'articleid' => $detail['id'],
            'cid' => $detail['cid'],
            'hstatus' => set('p96') ? 1 : 0
        );
        util::send_result($data);

    }
}

/**
 * 获取分类信息
 */
if ($op == 'getCategory') {
    $cid = intval($_GPC['cid']);
    if ($cid) {
        $item = pdo_get('xcommunity_plugin_article_class', array('id' => $cid));
        $item['pic'] = tomedia($item['pic']);
        util::send_result($item);
    }
}