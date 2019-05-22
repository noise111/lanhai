<?php
/**
 * 微小区模块
 *
 * [蓝牛科技] Copyright (c) 2013 njzhsq.com
 */
/**
 * 微信端二手交易
 */
global $_W, $_GPC;
$community = 'community' . $_W['uniacid'];
if($_W['setting'][$community]['styleid'] =='default2'){
    itoast('请切换至default使用',$this->createMobileUrl('home'),'error');
}
$member = model_user::mc_check('fled');

$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';

$id = intval($_GPC['id']);


//查二手子类 二手主类ID=5
$categories = util::fetchall_category(4);
$arr = array();
foreach ($categories as $key => $item) {
    $arr[] = array(
        'title' => $item['name'],
        'value' => $item['id']
    );
}
$data = json_encode($arr);
if ($op == 'list') {
    if ($_W['isajax']) {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid=:uniacid and t1.black=0 and t1.enable=0';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $keyword = "%{$_GPC['keyword']}%";
            $condition .= " AND t1.title LIKE '{$keyword}'";
        }
        $category = intval($_GPC['category']);
        if ($category) {
            $condition .= " AND t1.category =:category";
            $params[':category'] = $category;
        }
        $p4 = set('p4');
        if (empty($p4)) {
            $condition .= " AND t1.regionid=:regionid ";
            $params[':regionid'] = $member['regionid'];
        }
        $price = intval($_GPC['price']);
        if ($price == 1000) {
            $condition .= " AND t1.zprice between 0 and 1000";
        }
        elseif ($price == 2000) {
            $condition .= " AND t1.zprice between 1000 and 2000";
        }
        elseif ($price == 4000) {
            $condition .= " AND t1.zprice between 2000 and 4000";
        }
        elseif ($price == 6000) {
            $condition .= " AND t1.zprice between 4000 and 6000";
        }
        if ($_GPC['cate']) {
            $condition .= " AND t1.cate = '{$_GPC['cate']}'";
        }
        $sql = "select t1.*,t2.name,t3.title as regionname,t5.src from" . tablename('xcommunity_fled') . "t1 left join" . tablename('xcommunity_category') . "t2 on t1.category=t2.id left join" . tablename('xcommunity_region') . "t3 on t1.regionid = t3.id left join" . tablename('xcommunity_fled_images') . "t4 on t1.id = t4.fledid left join" . tablename('xcommunity_images') . "t5 on t5.id= t4.thumbid where $condition group by t1.id order by t1.createtime desc,t1.status desc   LIMIT " . ($pindex - 1) * $psize . ',' . $psize;

        $list = pdo_fetchall($sql, $params);

        foreach ($list as $key => $value) {
            $datetime = date('Y-m-d', $value['createtime']);
            $list[$key]['datetime'] = $datetime;
            $list[$key]['src'] = tomedia($value['src']);
        }
        $data = array();
        $data['list'] = $list;
        die(json_encode($data));
    }
    include $this->template($this->xqtpl('fled/list'));
}
elseif ($op == 'add') {
    if (empty($member['address'])) {
        itoast('请先完善资料', $this->createMobileUrl('register', array('regionid' => $member['regionid'], 'op' => 'member', 'memberid' => $member['id'], 'p' => 1)), 'error');
        exit();
    }
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $good = pdo_fetch("SELECT * FROM" . tablename('xcommunity_fled') . "WHERE id=:id and uid=:uid", array(':id' => $id, ':uid' => $_W['member']['uid']));
        if (empty($good)) {
            itoast('非法访问', referer(), 'error');
            exit();
        }
        $category = pdo_get('xcommunity_category', array('id' => $good['category']), array('name'));
        $images = $good['images'];
        if ($images && $good['images'] != 'N;') {
            $imgs = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_images') . "WHERE id in({$images})");
        }
    }
    if ($_W['isajax']) {
        $data = array(
            'uniacid'     => $_W['uniacid'],
            'uid'         => $_W['member']['uid'],
            'rolex'       => $_GPC['rolex'],
            'title'       => $_GPC['title'],
            'category'    => $_GPC['category'],
            'zprice'      => $_GPC['zprice'],
            'description' => $_GPC['description'],
            'createtime'  => TIMESTAMP,
            'regionid'    => $member['regionid'],
            'addressid'   => $member['addressid'],
            'yprice'      => $_GPC['yprice'],
            'cate'        => intval($_GPC['cate']) ? intval($_GPC['cate']) : 2,
        );
        $pic = substr($_GPC['picIds'], 0, strlen($_GPC['picIds']) - 1);
        $pics =pdo_fetchall('select * from'.tablename('xcommunity_images')."where id in ({$pic})");
        $imgs ='';
        foreach ($pics as $i => $item){
            $imgs .=$item['src'].',';
        }
        $data['images'] = $imgs;
        $data['enable'] = set('x7', $member['regionid']) || set('p27') ? 1 : 0; //0审核通过
        if (empty($id)) {
            if (pdo_insert('xcommunity_fled', $data)) {
                $id = pdo_insertid();
                if ($pic) {
                    $images = explode(',', $pic);
                    foreach ($images as $key => $item) {
                        $dat = array(
                            'fledid'  => $id,
                            'thumbid' => $item,
                        );
                        pdo_insert('xcommunity_fled_images', $dat);
                    }
                }
            }
        }
        else {

            pdo_update('xcommunity_fled', $data, array('id' => $_GPC['id']));
        }
        echo json_encode(array('status' => 1));
        exit();

    }
    include $this->template($this->xqtpl('fled/add'));
}
elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        $sql = " select t1.*,t4.realname,t4.mobile,t4.avatar,t5.name from" . tablename('xcommunity_fled') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t1.uid = t4.uid left join" . tablename('xcommunity_category') . "t5 on t5.id = t1.category where t1.id=:id";
        $item = pdo_fetch($sql, array(':id' => $id));
        $imgs = pdo_fetchall("select * from" . tablename('xcommunity_fled_images') . "as t1 left join" . tablename('xcommunity_images') . "as t2 on t1.thumbid =t2.id where t1.fledid=:fledid and t2.src !=''", array(':fledid' => $item['id']));
        foreach ($imgs as $key => $val) {
            $imgs[$key]['src'] = tomedia($val['src']);
        }
        $images = json_encode($imgs);
    }
    include $this->template($this->xqtpl('fled/detail'));
}
elseif ($op == 'my') {
    if ($_W['isajax']) {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $condition = 't1.uniacid=:uniacid and t1.uid=:uid and t1.regionid=:regionid';
        $params[':uniacid'] = $_W['uniacid'];
        $params[':uid'] = $_W['member']['uid'];
        $params[':regionid'] = $member['regionid'];
        if (!empty($_GPC['keyword'])) {
            $keyword = "%{$_GPC['keyword']}%";
            $condition = " AND t1.title LIKE '{$keyword}'";
        }
        $category = intval($_GPC['category']);
        if ($category) {
            $condition .= " AND t1.category =:category";
            $params[':category'] = $category;
        }
        $list = pdo_fetchall('SELECT t1.*,t2.name as name FROM' . tablename('xcommunity_fled') . "as t1 left join" . tablename('xcommunity_category') . "as t2 on t1.category = t2.id WHERE $condition and t1.black = 0 order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        foreach ($list as $key => $value) {
            $datetime = date('Y-m-d', $value['createtime']);
            $list[$key]['datetime'] = $datetime;
        }
        $data = array();
        $data['list'] = $list;
        die(json_encode($data));
    }
    include $this->template($this->xqtpl('fled/my'));
}
elseif ($op == 'delete') {
    $id = intval($_GPC['id']);
    if (pdo_delete('xcommunity_fled', array('id' => $id))) {
        $result['state'] = 0;
        itoast($result, '', 'ajax');
    }
}
elseif ($op == 'finish') {
    $id = intval($_GPC['id']);
    if ($id) {
        $r = pdo_update('xcommunity_fled', array('status' => 1), array('id' => $id));
        if ($r) {
            echo json_encode(array('result' => 1));
        }
    }
}
elseif ($op == 'del') {
    $id = intval($_GPC['id']);
    pdo_delte('xcommunity_images', array('id' => $id));
}