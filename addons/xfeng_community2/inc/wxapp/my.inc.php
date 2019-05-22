<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/23 下午11:22
 */
global $_GPC, $_W;
$ops = array('home', 'market', 'lease','car','finish','del','add');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
$regionid = $_SESSION['community']['regionid'];
$uid = $_W['member']['uid'];
if($op =='home'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $sql = 'SELECT t1.*,t2.name,t3.address FROM' . tablename('xcommunity_homemaking') . "as t1 left join" . tablename('xcommunity_category') . "as t2 on t1.category = t2.id left join".tablename('xcommunity_member_room')."t3 on t3.id = t1.addressid WHERE t1.uniacid=:uniacid AND t1.regionid=:regionid AND t1.uid=:uid order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql,array(':uniacid' => $_W['uniacid'],':uid'=> $uid,':regionid'=> $regionid));
    $data = array();
    $data['list'] = $list;
    util::send_result($data);
}
elseif($op =='market'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $sql = 'SELECT * FROM' . tablename('xcommunity_fled') . "WHERE uniacid=:uniacid AND regionid=:regionid AND uid=:uid order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql,array(':uniacid' => $_W['uniacid'],':uid'=> $uid,':regionid'=> $regionid));
    foreach ($list as $key => $value) {
        $datetime = date('Y-m-d', $value['createtime']);
        $list[$key]['datetime'] = $datetime;
        $images = array();
        $image = explode(',', $value['images']);
        foreach ($image as $k => $val) {
            $images[] = tomedia($val);
        }
        $list[$key]['images'] = $images;
        $list[$key]['url'] = $this->createMobileUrl('market', array('op' => 'detail', 'id' => $value['id']));
        $list[$key]['addurl'] = $this->createMobileUrl('market', array('op' => 'add', 'id' => $value['id'],'type' => $value['type']));
    }
    $data = array();
    $data['list'] = $list;
    util::send_result($data);
}
elseif($op =='lease'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $sql = 'SELECT * FROM' . tablename('xcommunity_houselease') . "WHERE uniacid=:uniacid AND regionid=:regionid AND uid=:uid order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql,array(':uniacid' => $_W['uniacid'],':uid'=> $uid,':regionid'=> $regionid));
    foreach ($list as $key => $value) {
        $datetime = date('Y-m-d', $value['createtime']);
        $list[$key]['datetime'] = $datetime;
        $images = array();
        $image = explode(',', $value['images']);
        foreach ($image as $k => $val) {
            $images[] = tomedia($val);
        }
        $list[$key]['images'] = $images;
        $list[$key]['url'] = $this->createMobileUrl('houselease', array('op' => 'detail', 'id' => $value['id']));
        $list[$key]['addurl'] = $this->createMobileUrl('houselease', array('op' => 'add', 'id' => $value['id']));
    }
    $data = array();
    $data['list'] = $list;
    util::send_result($data);
}
elseif($op =='car'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $sql = 'SELECT * FROM' . tablename('xcommunity_carpool') . "WHERE uniacid=:uniacid AND regionid=:regionid AND uid=:uid order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql,array(':uniacid' => $_W['uniacid'],':uid'=> $uid,':regionid'=> $regionid));
    foreach ($list as $key => $value) {
        $datetime = date('Y-m-d', $value['createtime']);
        $list[$key]['datetime'] = $datetime;
        $images = '';
        $image = explode(',', $value['images']);
        foreach ($image as $k => $val) {
            $images[] = tomedia($val);
        }
        $list[$key]['images'] = $images;
        $list[$key]['url'] = $this->createMobileUrl('car', array('op' => 'detail', 'id' => $value['id']));
    }
    $data = array();
    $data['list'] = $list;
    util::send_result($data);
}
elseif($op =='finish'){
    $ps = array('car', 'market', 'lease','home');
    $p = trim($_GPC['p']);
    if (!in_array($p, $ps)) {
        util::send_error(-1, '参数错误');
    }
    if($p == 'home'){
        $table = 'xcommunity_homemaking';
    }elseif ($p =='market'){
        $table = 'xcommunity_fled';
    }elseif($p =='lease'){
        $table = 'xcommunity_houselease';
    }elseif($p =='car'){
        $table = 'xcommunity_carpool';
    }
    $params =array();
    $sql ="update ".tablename($table)."set status=1 where id=:id";
    $params[':id']= intval($_GPC['id']);
    if(pdo_query($sql,$params)){
        $data = array();
        $data['content'] = '确认完成';
        util::send_result($data);
    }
}
elseif($op =='del'){
    $ps = array('car', 'market', 'lease','home');
    $p = trim($_GPC['p']);
    if (!in_array($p, $ps)) {
        util::send_error(-1, '参数错误');
    }
    if($p == 'home'){
        $table = 'xcommunity_homemaking';
    }elseif ($p =='market'){
        $table = 'xcommunity_fled';
    }elseif($p =='lease'){
        $table = 'xcommunity_houselease';
    }elseif($p =='car'){
        $table = 'xcommunity_carpool';
    }
    if(pdo_delete($table,array('id'=>intval($_GPC['id'])))){
        $data = array();
        $data['content'] = '确认删除';
        util::send_result($data);
    }
}