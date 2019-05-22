<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/1/20 下午7:45
 */
global $_GPC, $_W;
$ops = array('dp', 'order', 'qrpl', 'download', 'qrlist', 'qrdel');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'dp';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 无人超市的超市管理
 */
if ($op == 'dp') {
    $p = in_array(trim($_GPC['p']), array('list', 'add', 'del', 'create')) ? trim($_GPC['p']) : 'list';
    /**
     * 无人超市的超市管理列表
     */
    if ($p == 'list') {
        //店铺列表
        $parms = array();
        $condition = 'uniacid=:uniacid';
        $parms[':uniacid'] = $_W['uniacid'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT * FROM" . tablename('xcommunity_supermark') . "WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_supermark') . "WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/supermark/dp/list');
    }
    /**
     * 无人超市的超市添加修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_supermark', array('id' => $id), array());
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'     => $_W['uniacid'],
                'title'       => trim($_GPC['title']),
                'tel'         => trim($_GPC['tel']),
                'copy'        => trim($_GPC['copy']),
                'device_code' => trim($_GPC['device_code']),
                'lat'         => $_GPC['baidumap']['lat'],
                'lng'         => $_GPC['baidumap']['lng'],
                'address'     => trim($_GPC['address']),
                'range'       => intval($_GPC['range'])
            );
            if ($id) {
                pdo_update('xcommunity_supermark', $data, array('id' => $id));
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_supermark', $data);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/plugin/supermark/dp/add');
    }
    /**
     * 无人超市的超市删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_supermark', array('id' => $id), array('id'));
            if ($item) {
                if (pdo_delete('xcommunity_supermark', array('id' => $id))) {
                    echo json_encode(array('status' => 1));
                    exit();
                }
            }
        }
    }
    /**
     * 无人超市的二维码查看
     */
    if ($p == 'create') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_supermark', array('id' => $id), array('device_code', 'title'));
            if ($item) {
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&code={$item['device_code']}&do=supermark&m=" . $this->module['name'];//二维码内容
                $imgUrl = $item['title'] . $item['device_code'] . ".png";
                $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/supermark/" . $_W['uniacid'] . "/";
                $qr = createQr($url, $imgUrl, $tmpdir);
                echo $qr;
                exit();
            }
        }
    }
}
/**
 * 无人超市的订单管理
 */
if ($op == 'order') {
    //批量删除
    if ($_W['ispost']) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_supermark_order', array('id' => $id));
            }
            itoast('删除成功', referer(), 'success', true);
        }
    }
    //超市
    $supers = pdo_getall('xcommunity_supermark', array('uniacid' => $_W['uniacid']), array());
    $parms = array();
    $condition = 'uniacid=:uniacid';
    $parms[':uniacid'] = $_W['uniacid'];
    $code = trim($_GPC['code']);
    if ($code) {
        $condition .= " and code=:code";
        $parms[':code'] = $code;
    }
    $status = intval($_GPC['status']);
    if ($status) {
        $condition .= " and status=:status";
        $parms[':status'] = $status;
    }
    $keyword = trim($_GPC['keyword']);
    if ($keyword) {
        $condition .= " and ordersn=:keyword";
        $parms[':keyword'] = $keyword;
    }
    if ($_GPC['export'] == 1) {
        $sql = "SELECT * FROM" . tablename('xcommunity_supermark_order') . "WHERE $condition order by id desc";
        $rows = pdo_fetchall($sql, $parms);
        foreach ($rows as $k => $v) {
            $rows[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
        }
        model_execl::export($rows, array(
            "title"   => "订单-" . date('Y-m-d', time()),
            "columns" => array(
                array(
                    'title' => 'ID',
                    'field' => 'id',
                    'width' => 10
                ),
                array(
                    'title' => '订单号',
                    'field' => 'ordersn',
                    'width' => 12
                ),
                array(
                    'title' => '金额',
                    'field' => 'price',
                    'width' => 12
                ),
                array(
                    'title' => '下单时间',
                    'field' => 'time',
                    'width' => 18
                ),

            )
        ));
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $sql = "SELECT * FROM" . tablename('xcommunity_supermark_order') . "WHERE $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $parms);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_supermark_order') . "WHERE $condition", $parms);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/supermark/order');
}
/**
 * 无人超市的批量生成二维码
 */
if ($op == 'qrpl') {
    $list = pdo_getall('xcommunity_supermark', array('uniacid' => $_W['uniacid']), array('device_code', 'title', 'id'));
    foreach ($list as $k => $v) {
        $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&code={$v['device_code']}&do=supermark&m=" . $this->module['name'];//二维码内容
        $imgUrl = $v['title'] . $v['device_code'] . ".png";
        $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/supermark/" . $_W['uniacid'] . "/";
        $qr = createQr($url, $imgUrl, $tmpdir);
    }
    itoast('更新成功', referer(), 'success');
}
/**
 * 无人超市的二维码下载
 */
if ($op == 'download') {
    $path = MODULE_ROOT . '/data/qrcode/supermark/' . $_W['uniacid'] . "/";
    $zip = MODULE_ROOT . '/data/qrcode/supermark/' . time() . ".zip";
    download2($path, $zip);
}
/**
 * 二维码的列表
 */
if ($op == 'qrlist') {
    $tmpdir = MODULE_ROOT . '/data/qrcode/supermark/' . $_W['uniacid'] . '/';
    $result = list_dir($tmpdir);
    $list = array();
    foreach ($result as $k => $v) {
        $list[$k]['url'] = $v;
        $list[$k]['title'] = str_replace(MODULE_ROOT . '/data/qrcode/supermark/' . $_W['uniacid'] . '/', '', $v);
    }
    include $this->template('web/plugin/supermark/qrlist');
}
/**
 * 删除批量生成二维码
 */
if ($op == 'qrdel') {
    $title = trim($_GPC['title']);
    load()->func('file');
    $tmpdir = MODULE_ROOT . '/data/qrcode/supermark/' . $_W['uniacid'] . '/' . $title;
    file_delete($tmpdir);
    itoast('删除成功', referer(), 'success');
}