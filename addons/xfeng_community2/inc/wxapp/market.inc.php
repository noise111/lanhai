<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/12/5 下午4:15
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add','del','toblack');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
//$_SESSION['community'] = model_user::mc_check();
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = 't1.uniacid=:uniacid and t1.black=0 and t1.enable=0 and t1.type=:type';
    $params[':uniacid'] = $_W['uniacid'];
    $params[':type'] = intval($_GPC['type']) ? intval($_GPC['type']) : 1;
    $p4 = set('p4');
    if (empty($p4)) {
        $condition .= " AND t1.regionid=:regionid ";
        $params[':regionid'] = $_SESSION['community']['regionid'];
    }


    $sql = "select t1.*,t2.name,t3.title as regionname,t4.realname,t4.avatar,t4.mobile from" . tablename('xcommunity_fled') . "t1 left join" . tablename('xcommunity_category') . "t2 on t1.category=t2.id left join" . tablename('xcommunity_region') . "t3 on t1.regionid = t3.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid where $condition  order by t1.createtime desc,t1.status desc   LIMIT " . ($pindex - 1) * $psize . ',' . $psize;

    $list = pdo_fetchall($sql, $params);

    foreach ($list as $key => $value) {
        $datetime = date('m-d', $value['createtime']);
        $list[$key]['datetime'] = $datetime;
        $images = '';
        $image = explode(',', $value['images']);
        foreach ($image as $k => $val) {
            $images[] = tomedia($val);
        }
        $list[$key]['images'] = $images;
        $list[$key]['src'] = $images[0] ? tomedia($images[0]) : MODULE_URL.'template/mobile/default2/static/images/icon-zanwu.png';
        $list[$key]['link'] = $this->createMobileUrl('market', array('op' => 'detail', 'id' => $value['id']));
        $list[$key]['avatar'] =  $value['avatar'] ? $value['avatar'] : MODULE_URL.'template/mobile/default2/static/images/my/personal.png';
        $list[$key]['realname'] = $value['realname'] ? $value['realname'] : '邻居'.rand(10000000,99999999);
        $list[$key]['status'] = $value['realname'] ? '认证住户' : '游客';
        $list[$key]['finishStatus'] = $value['status'] ? $value['status'] : 0;
    }
    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);
}
elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        $condition = "t1.id=:id";
        $params[':id'] = $id;
        $sql = "select t1.*,t2.name,t3.title as regionname,t4.realname,t4.avatar,t4.mobile as tmobile from" . tablename('xcommunity_fled') . "t1 left join" . tablename('xcommunity_category') . "t2 on t1.category=t2.id left join" . tablename('xcommunity_region') . "t3 on t1.regionid = t3.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid where $condition ";
        $item = pdo_fetch($sql, $params);
        $images = array();
        $data = array();
        if ($item['images']) {
            $image = explode(',', $item['images']);
            foreach ($image as $key => $val) {
                $images[] = tomedia($val);
            }

        }
        $data['images'] = $images;
        $data['realname'] = $item['realname'];
        $data['zprice'] = $item['zprice'];
        $data['yprice'] = $item['yprice'];
        $data['avatar'] = $item['avatar'] ? $item['avatar'] : MODULE_URL . "template/mobile/default2/static/images/my/personal.png";
        $data['createtime'] = date('H:i', $item['createtime']);
        $data['regionname'] = $item['regionname'];
        $data['description'] = $item['description'];
        $data['title'] = $item['title'];
        $data['name'] = $item['name'];
        $data['tmobile'] = $item['tmobile'];
        $data['category'] = $item['category'];
        $data['black']  = $item['black'];
        $data['hstatus'] = set('p96') ? 1 :0;
        $data['region'] = $_SESSION['community']['title'];
        util::send_result($data);
    }
}
elseif ($op == 'add') {
//    $pics = xtrim($_GPC['pics']);
//    if (!empty($pics)) {
//        $pics = explode(',', $pics);
//        if (!empty($pics)) {
//            foreach ($pics as $k => $v) {
//                if ($v) {
//                    if($_W['container']=='wechat'){
//                        $pic .= util::get_media($v) . ',';
//                    }else{
//                        $pic .=$v . ',';//修改为H5上传图片
//                    }
//                }
//
//            }
//        }
//        $pic = ltrim(rtrim($pic, ','), ',');
//    }
    $pics = xtrim($_GPC['pics']);
    $data = array(
        'uniacid'     => $_W['uniacid'],
        'uid'         => $_W['member']['uid'],
        'title'       => $_GPC['title'],
        'category'    => $_GPC['cid'],
        'zprice'      => $_GPC['zprice'],
        'yprice'      => $_GPC['yprice'],
        'description' => $_GPC['description'],
        'createtime'  => TIMESTAMP,
        'regionid'    => $_SESSION['community']['regionid'],
        'addressid'   => $_SESSION['community']['addressid'],
        'images'      => $pics,
        'type'        => intval($_GPC['type'])
    );
    $data['enable'] = set('x7', $_SESSION['community']['addressid']) || set('p27') ? 1 : 0; //0审核通过
    $id = intval($_GPC['id']);
    if($id){
        if (pdo_update('xcommunity_fled', $data,array('id'=>$id))) {
            $data = array();
            $data['content'] = '修改成功';
            util::send_result($data);
        }
    }else{
        if (pdo_insert('xcommunity_fled', $data)) {
            //兼容老版本图片
            $fledid = pdo_insertid();
            $images = explode(',',$pics);
            foreach ($images as $key => $item) {
                $dat = array(
                    'src' => $item,
                );
                pdo_insert('xcommunity_images', $dat);
                $thumbid = pdo_insertid();
                $dat = array(
                    'fledid' => $fledid,
                    'thumbid' => $thumbid,
                );
                pdo_insert('xcommunity_fled_images', $dat);
            }
            $data = array();
            $data['content'] = '发布成功';
            util::send_result($data);
        }
    }

}
