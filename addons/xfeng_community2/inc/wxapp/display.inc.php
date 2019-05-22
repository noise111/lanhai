<?php
/**
 * 小区首页接口
 * Created by mp.admin9.com.
 * User: fengqiyue
 * Time: 2017/11/27 下午1:34
 */
global $_GPC, $_W;
$ops = array('home', 'swipers', 'links', 'notice', 'cubes', 'banners', 'goods', 'member', 'display');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if(empty($_W['member']['uid'])){
    util::send_error(-1, '缺uid');
    exit();
}
if ($op == 'home') {
    $regionid = $_SESSION['community']['regionid'] ? $_SESSION['community']['regionid'] : 1;
    $arr = array();
    $arr['LnTwonav'] = util::_nav($regionid);
    $arr['LnNav'] = util::_nav($regionid);
    $arr['LnAdv'] = util::xqslide(1, $regionid);
    $arr['LnNotice'] = util::latestnotice(1, $regionid);
    $arr['LnCube'] = util::xqslide(6, $regionid);
    $arr['LnBanner'] = util::xqslide(5, $regionid);
    $arr['LnBannerx'] = util::xqslide(5, $regionid);
    $arr['LnGoods'] = util::tjgoods($regionid);
    $arr['LnHouse'] = util::xqhouse($regionid);
    $arr['LnActivity'] = util::xqactivity($regionid);
    $arr['LnBannerTwo'] = util::xqslide(9, $regionid);
    $arr['LnBannerTwox'] = util::xqslide(9, $regionid);
    $arr['LnMarket'] = util::xqmarket($regionid);
    $sql = "select * from" . tablename('xcommunity_home') . "where uniacid=:uniacid and status =1 order by displayorder asc ";
    $params[':uniacid'] = $_W['uniacid'];
    $pages = pdo_fetchall($sql, $params);
    $data = array();
    $p167 = set("p167");
    foreach ($pages as $k => $v) {
        if ($v['sort'] == 'nav') {
            $v['sort'] = set("p97") ? ucwords('nav') : ucwords('twonav');
        }
        if ($v['sort'] == 'banner') {
            $v['sort'] = set("p110") ? ucwords('bannerx') : ucwords('banner');
        }
        if ($v['sort'] == 'bannerTwo') {
            $v['sort'] = $p167 ? ucwords('bannerTwox') : ucwords('bannerTwo');
        }
        $component = 'Ln' . ucwords($v['sort']);
        $items = $arr[$component];
        $data[] =
            array(
                'component' => $component,
                'items' => $arr[$component]
            );
    }
    $data['headadv'] = util::xqslide(7, $regionid);
    $data['headline'] = (set('p116') && !empty($data['headadv'])) ? 1 : 0;
    $day = date('m月d日');
    $weekday = array('日','一','二','三','四','五','六');
    $data['day'] = $day.',星期'.$weekday[date('w')];
    $data['water'] = set('p119') ? 1 : 0;
    util::send_result($data);
}
if ($op == 'swipers') {
    $data = array();
//    $_SESSION['community'] = model_user::mc_check();

    // 小区换灯
    $data['swipers'] = util::xqslide(1, $regionid);


    util::send_result($data);
} elseif ($op == 'links') {
    $data = array();
    $data['links'] = util::_nav($regionid);
    if ($data) {
        util::send_result($data);
    } else {
        util::send_error(-1, '');
    }
} elseif ($op == 'notice') {
    $data = array();
//    $_SESSION['community'] = model_user::mc_check();
    $data['notice'] = util::latestnotice(1, $regionid);

    util::send_result($data);
} elseif ($op == 'cubes') {
    $data = array();
//    $_SESSION['community'] = model_user::mc_check();
    $data['cubes'] = util::xqslide(6, $regionid);
    util::send_result($data);
} elseif ($op == 'banners') {
    $data = array();
//    $_SESSION['community'] = model_user::mc_check();
    $data['banners'] = util::xqslide(5, $regionid);
    util::send_result($data);
} elseif ($op == 'goods') {
    $data = array();
//    $_SESSION['community'] = model_user::mc_check();

    $data['goods'] = util::tjgoods($regionid);
    util::send_result($data);
} elseif ($op == 'display') {
    $regionid = $_SESSION['community']['regionid'] ? $_SESSION['community']['regionid'] : 1;
    $arr = array();
    $arr['LnNav'] = util::_nav($regionid);
    $arr['LnTwonav'] = util::_nav($regionid);
    $arr['LnAdv'] = util::xqslide(1, $regionid);
    $arr['LnNotice'] = util::latestnotice(1, $regionid);
    $arr['LnCube'] = util::xqslide(6, $regionid);
    $arr['LnBanner'] = util::xqslide(5, $regionid);
    $arr['LnGoods'] = util::tjgoods($regionid);


    util::send_result($arr);
}
