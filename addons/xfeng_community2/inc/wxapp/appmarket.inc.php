<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 下午3:02
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add','del','toblack');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if($op =='list'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = 't1.uniacid=:uniacid';
    $params[':uniacid'] = $_SESSION['appuniacid'];
    $keyword = trim($_GPC['keyword']);
    if($keyword){
        $condition .= " and t1.title like '%{$keyword}%'";
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
    $sql = "select t1.*,t2.name,t3.title as regionname,t4.realname,t4.avatar,t4.mobile from" . tablename('xcommunity_fled') . "t1 left join" . tablename('xcommunity_category') . "t2 on t1.category=t2.id left join" . tablename('xcommunity_region') . "t3 on t1.regionid = t3.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid where $condition  order by t1.createtime desc,t1.status desc   LIMIT " . ($pindex - 1) * $psize . ',' . $psize;

    $list = pdo_fetchall($sql, $params);

    foreach ($list as $key => $value) {
        $datetime = date('Y-m-d H:i', $value['createtime']);
        $list[$key]['datetime'] = $datetime;
        $images = '';
        $image = explode(',', $value['images']);
        foreach ($image as $k => $val) {
            $images[] = tomedia($val);
        }
        $list[$key]['images'] = $images;
        $list[$key]['src'] = $images[0] ? tomedia($images[0]) : MODULE_URL.'template/mobile/default2/static/images/icon-zanwu.png';
        $list[$key]['link'] = $this->createMobileUrl('xqsys', array('op' => 'market','p' => 'detail', 'id' => $value['id']));
    }
    $data = array();
    $data['list'] = $list;
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
            $data['images'] = $images;
        }
        $data['realname'] = $item['realname'];
        $data['zprice'] = $item['zprice'];
        $data['yprice'] = $item['yprice'];
        $data['avatar'] = $item['avatar'] ? $item['avatar'] : MODULE_URL . "template/mobile/default2/static/images/my/personal.png";
        $data['createtime'] = date('H:i', $item['createtime']);
        $data['regionname'] = $item['regionname'];
        $data['description'] = $item['description'];
        $data['title'] = $item['title'];
        $data['name'] = $item['name'];
        $data['mobile'] = $item['tmobile'];
        $data['category'] = $item['category'];
        $data['black']  = $item['black'];
        $data['status'] = set('p96') ? 1 :0;
        util::send_result($data);
    }
}
elseif($op == 'del'){
    $id = intval($_GPC['id']);
    $item = pdo_getcolumn('xcommunity_fled',array('id' => $id),'id');
    if (empty($item)){
        util::send_error(-1,'参数错误');
    }
    if (pdo_delete('xcommunity_fled',array('id' => $id))){
        util::send_result();
    }
}
elseif($op == 'toblack'){
    $id = intval($_GPC['id']);
    $item = pdo_get('xcommunity_fled',array('id' => $id),array('id','black'));
    if (empty($item)){
        util::send_error(-1,'参数错误');
    }
    $black = $item['black']==1 ? 0 : 1;
    if (pdo_update('xcommunity_fled',array('black' => $black),array('id' => $id))){
        util::send_result();
    }
}