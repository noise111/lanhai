<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/12/5 上午10:37
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
    $price = trim($_GPC['price']);
    $category = intval($_GPC['category']);
    $condition = 't1.uniacid=:uniacid and t1.enable =0';
    $params[':uniacid'] = $_W['uniacid'];
    $p5 = set('p5');
    $regionid = $_SESSION['community']['regionid'] ? $_SESSION['community']['regionid'] : 1;
    if (empty($p5)) {
        $condition .= " and t1.regionid=:regionid";
        $params[':regionid'] = $regionid;
    }

    if ($category) {
        $condition .= " and t1.category =:category";
        $params[':category'] = $category;
    }
    $status = intval($_GPC['status']);
    if ($status) {
        $condition .= " and t1.status =:status";
        $params[':status'] = $status;
    }

    if ($price == '1000') {
        $condition .= " and t1.price <= 1000 and (t1.category =1 or t1.category=2)";
    }
    elseif ($price == '2000') {
        $condition .= " and t1.price > 1000 and price <= 2000 and (t1.category =1 or t1.category=2)";
    }
    elseif ($price == '4000') {
        $condition .= " and t1.price > 2000 and price < 4000 and (t1.category =1 or t1.category=2)";
    }
    elseif ($price == '6000') {
        $condition .= " and t1.price >= 4000 and (t1.category =1 or t1.category=2)";
    }
    $sql = "select t1.*,t2.title as region from" . tablename('xcommunity_houselease') . 't1 left join' . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where $condition order by t1.createtime desc,t1.status desc limit " . ($pindex - 1) * $psize . ',' . $psize;

    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $val) {
        $images = explode(',', $val['images']);
        $list[$k]['image'] = tomedia($images[0]);
        $list[$k]['createtime'] = date('Y-m-d H:i', $val['createtime']);
        $list[$k]['src'] = $images[0] ? tomedia($images[0]) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
        $list[$k]['link'] = $this->createMobileUrl('xqsys', array('op' => 'houselease', 'p' => 'detail', 'id' => $val['id']));
        $list[$k]['url'] = $this->createMobileUrl('houselease', array('op' => 'detail', 'id' => $val['id']));
    }

    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);


}
elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    $sql = "select t1.*,t2.mobile as ttmobile,t3.title as rtitle,t3.city,t3.dist,t1.mobile from" . tablename('xcommunity_houselease') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "t3 on t3.id = t1.regionid where t1.id=:id";
    //$data = pdo_get('xcommunity_houselease', array('id' => $id), array());
    $data = pdo_fetch($sql, array(':id' => $id));
    $images = explode(',', $data['images']);
    $pics = array();
    if ($data['images']) {
        foreach ($images as $k => $val) {
            $pics[] = array(
                tomedia($val)
//                'src'  => tomedia($val),
//                'msrc' => tomedia($val),
            );
        }
    }


    $all = array();
    $allocations = explode(',', $data['allocation']);
    foreach ($allocations as $key => $value) {
        $value = explode('|', $value);
        $all[] = array(
            'icon'  => $value[0],
            'title' => $value[1],
        );
        $list[] = $value[0] . '|' . $value[1];
        $spec .= $value[1] . ',';
    }
    $data['images'] = $pics ? $pics : array();
    $mobile = '';
    $p21 = set('p21');
    if ($p21) {
        $mobile = set('p23');
    }
    $tmobile = $mobile ? $mobile : $data['ttmobile'];
    $data['tmobile'] = $tmobile ? $tmobile : $data['mobile'];
//    $data['tmobile'] = $data['mobile'] ? $data['mobile'] : $data['ttmobile'];
    $data['createtime'] = date("Y-m-d", $data['createtime']);
    $data['list'] = $all;
    $data['location'] = $list;
    $data['spec'] = xtrim($spec);
    $data['hstatus'] = set('p96') ? 1 : 0;
    $content = strip_tags($data['content']);//去除html标签
    $pattern = '/\s/';//去除空白
    $data['content'] = preg_replace($pattern, '', $content);
    $data['region'] = $_SESSION['community']['title'];
    if ($data['category'] == 1) {
        $data['categoryname'] = array('出租');
    }
    elseif ($data['category'] == 2) {
        $data['categoryname'] = array('求租');
    }
    elseif ($data['category'] == 3) {
        $data['categoryname'] = array('出售');
    }
    elseif ($data['category'] == 4) {
        $data['categoryname'] = array('求购');
    }
    util::send_result($data);
}
elseif ($op == 'add') {
    $pics = xtrim($_GPC['pics']);
    $regionid = $_SESSION['community']['regionid'];
    $data = array(
        'uniacid'      => $_W['uniacid'],
        'uid'          => $_W['member']['uid'],
        'regionid'     => $regionid,
        'category'     => intval($_GPC['category']),
        'way'          => $_GPC['way'],
        'model_room'   => $_GPC['model_room'],
        'model_hall'   => $_GPC['model_hall'],
        'model_toilet' => $_GPC['model_toilet'],
        'model_area'   => $_GPC['model_area'],
        'floor_layer'  => $_GPC['floor_layer'],
        'floor_number' => $_GPC['floor_number'],
        'fitment'      => $_GPC['fitment'],
        'house'        => $_GPC['house'],
        'allocation'   => trim($_GPC['allocation']),
        'price_way'    => $_GPC['price_way'],
        'price'        => $_GPC['price'],
        'checktime'    => $_GPC['checktime'],
        'title'        => $_GPC['title'],
        'content'      => $_GPC['content'],
        'createtime'   => TIMESTAMP,
        'house_aspect' => trim($_GPC['house_aspect']),
        'house_model'  => trim($_GPC['house_model']),
        'house_floor'  => trim($_GPC['house_floor']),
        'images'       => $pics
    );
    $data['enable'] = set('x8', $regionid) || set('p28') ? 1 : 0; //0审核通过

    $id = intval($_GPC['hid']);
    if ($id) {
        if (pdo_update('xcommunity_houselease', $data, array('id' => $id))) {
            $data = array();
            $data['content'] = '修改成功';
            util::send_result($data);
        }
    }
    else {
        if (pdo_insert('xcommunity_houselease', $data)) {
            //兼容老版本图片
            $houseid = pdo_insertid();
            $images = explode(',', $pics);
            foreach ($images as $key => $item) {
                $dat = array(
                    'src' => $item,
                );
                pdo_insert('xcommunity_images', $dat);
                $thumbid = pdo_insertid();
                $dat = array(
                    'houseid' => $houseid,
                    'thumbid' => $thumbid,
                );
                pdo_insert('xcommunity_houselease_images', $dat);
            }
            $data = array();
            $data['content'] = '发布成功';
            util::send_result($data);
        }
    }
}
