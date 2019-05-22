<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 下午3:23
 */
global $_GPC,$_W;
$ops = array('list', 'detail', 'add','del');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $price = trim($_GPC['price']);
    $category = intval($_GPC['category']);
    $condition = 't1.uniacid=:uniacid';
    $params[':uniacid'] = $_SESSION['appuniacid'];
    $keyword = trim($_GPC['keyword']);
    if($keyword){
        $condition .= " and t1.content like '%{$keyword}%'";
    }
    if ($_SESSION['apptype'] == 2) {
        $condition .= " and t1.uid=:uid ";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if($_SESSION['apptype'] == 3){
        $condition .= " and t1.regionid in (:regionid)";
        $params[':regionid'] = $_SESSION['appregionids'];
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $data['list'] = array();
        util::send_result($data);
    }
    $sql = "select t1.*,t2.title as region from" . tablename('xcommunity_houselease') . 't1 left join' . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where $condition order by t1.createtime desc,t1.status desc limit " . ($pindex - 1) * $psize . ',' . $psize;

    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $val) {
        $images = explode(',', $val['images']);
        $list[$k]['image'] = tomedia($images[0]);
        $list[$k]['createtime'] = date('Y-m-d H:i',$val['createtime']);
        $list[$k]['src'] = $images[0] ? tomedia($images[0]) : MODULE_URL.'template/mobile/default2/static/images/icon-zanwu.png';
        $list[$k]['link'] = $this->createMobileUrl('xqsys', array('op' => 'houselease','p' => 'detail', 'id' => $val['id']));
    }
    $data = array();
    $data['list'] = $list;
    util::send_result($data);
}
elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        util::send_error(-1, 'id null');
    }
    $sql = "select t1.*,t2.mobile,t3.title,t3.city,t3.dist,t2.realname,t1.mobile as ttmobile from" . tablename('xcommunity_houselease') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "t3 on t3.id = t1.regionid where t1.id=:id";
    $data = pdo_fetch($sql, array(':id' => $id));
    $images = explode(',', $data['images']);
    $pics = array();
    foreach ($images as $k => $val) {
        $pics[] = tomedia($val);
    }

    $all = array();
    $allocations = explode(',', $data['allocation']);
    foreach ($allocations as $key => $value) {
        $value = explode('|', $value);
        $all[] = array(
            'icon'  => $value[0],
            'title' => $value[1]
        );
        $list[] = $value[0] . '|' . $value[1];
        $spec .= $value[1] . ',';
    }
    $data['images'] = $pics;
    $data['mobile'] = $data['mobile'] ? $data['mobile'] : $data['ttmobile'];
    $data['createtime'] = date("Y-m-d", $data['createtime']);
    $data['list'] = $all;
    $data['location'] = $list;
    $data['spec'] = xtrim($spec);
    $data['ctitle'] = '房屋租赁详情';
    $data['_status'] = set('p96') ? 1 :0;
    $data['url'] = $this->createMobileUrl('xqsys', array('op' => 'houselease', 'p' => 'add','id' => $data['id']));
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
elseif($op == 'del'){
    $id = intval($_GPC['id']);
    $item = pdo_getcolumn('xcommunity_houselease',array('id' => $id),'id');
    if (empty($item)){
        util::send_error(-1,'参数错误');
    }
    if (pdo_delete('xcommunity_houselease',array('id' => $id))){
        util::send_result();
    }
}
elseif ($op == 'add') {
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        util::send_error(2, '权限不足');
    }
    $data = array(
        'uniacid' => $_W['uniacid'],
        'uid' => $_W['member']['uid'],
        'regionid' => intval($_GPC['regionid']),
        'category' => intval($_GPC['category']),
        'way' => $_GPC['way'],
        'model_room' => $_GPC['model_room'],
        'model_hall' => $_GPC['model_hall'],
        'model_toilet' => $_GPC['model_toilet'],
        'model_area' => $_GPC['model_area'],
        'floor_layer' => $_GPC['floor_layer'],
        'floor_number' => $_GPC['floor_number'],
        'fitment' => $_GPC['fitment'],
        'house' => $_GPC['house'],
        'allocation' => trim($_GPC['allocation']),
        'price_way' => $_GPC['price_way'],
        'price' => $_GPC['price'],
        'checktime' => $_GPC['checktime'],
        'title' => $_GPC['title'],
        'content' => $_GPC['content'],
        'createtime' => TIMESTAMP,
        'house_aspect' => trim($_GPC['house_aspect']),
        'house_model' => trim($_GPC['house_model']),
        'house_floor' => trim($_GPC['house_floor'])
    );
    $data['enable'] = set('x8', $regionid) || set('p28') ? 1 : 0; //0审核通过
    $pics = xtrim($_GPC['pics']);
    if ($pics) {
        $pics = explode(',', $pics);
        $pic = '';
        if (!empty($pics)) {
            foreach ($pics as $k => $v) {
                if ($v) {
                    if ($_W['container'] == 'wechat') {
                        $pic .= util::get_media($v) . ',';
                    } else {
                        $pic .= $v . ',';//修改为H5上传图片
                    }


                }
            }
        }
        $pic = ltrim(rtrim($pic, ','), ',');
        $data['images'] = $pic;
    }
    $id = intval($_GPC['hid']);
    if ($id) {
        if (pdo_update('xcommunity_houselease', $data, array('id' => $id))) {
            $data = array();
            $data['content'] = '修改成功';
            util::send_result($data);
        }
    } else {
        if (pdo_insert('xcommunity_houselease', $data)) {
            //兼容老版本图片
            $houseid = pdo_insertid();
            $images = explode(',', $pic);
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