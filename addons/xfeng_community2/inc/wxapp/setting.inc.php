<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/20 下午2:49
 */
global $_GPC, $_W;
$ops = array('set', 'detail', 'add', 'footer');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'set') {
    //判断是否开启验证码
    $regionid = intval($_GPC['regionid']) ? intval($_GPC['regionid']) : $_SESSION['community']['regionid'];
    $status = set('s4') || set('x23', $regionid) ? 1 : 0; //1开启短信
    $xy = set('p80') ? 1 : 0;//1开启注册协议
    $xy_content = set('p49');
    $house = set("p91") || set('x58', $regionid) ? 1 : 0;//1开启选择房号
    $verity_mobile = set("p19") || set('x2', $regionid) ? 1 : 0;//1开启手机号验证
    $verity_code = set("p20") || set('x3', $regionid) ? 1 : 0;//1开启注册码验证
    //注册字段配置
    if (set('p55')) {
        $area = set('p36') ? 1 : 0;
        $area_zd = set('p37');
        $build = set('p38') ? 1 : 0;
        $build_zd = set('p39');
        $unit = set('p40') ? 1 : 0;
        $unit_zd = set('p41');
        $room = set('p42') ? 1 : 0;
        $room_zd = set('p43');
        $license = set('p81') ? 1 : 0;
        $idcard = set('p83') ? 1 : 0;
        $gender = set('p84') ? 1 : 0;
        $realname = set('p154') ? 1 : 0;
        $mobile = set('p155') ? 1 : 0;
    }
    else {
        $area = set('x17', $regionid) ? 1 : 0;
        $area_zd = set('x46', $regionid);
        $build = set('x18', $regionid) ? 1 : 0;
        $build_zd = set('x47', $regionid);
        $unit = set('x19', $regionid) ? 1 : 0;
        $unit_zd = set('x48', $regionid);
        $room = set('x20', $regionid) ? 1 : 0;
        $room_zd = set('x49', $regionid);
        $license = set('x52', $regionid) ? 1 : 0;
        $idcard = set('x55', $regionid) ? 1 : 0;
        $gender = set('x56', $regionid) ? 1 : 0;
        $realname = set('x81') ? 1 : 0;
        $mobile = set('x82') ? 1 : 0;
    }

    $data = array();
    $data['code'] = $status;
    $data['tycode'] = set('s4') ? 1 : 0;//统一注册短信接口
    $data['xy'] = $xy;
    $data['house'] = $house;
    $data['area'] = intval($area);
    $data['area_zd'] = $area_zd;
    $data['build'] = intval($build);
    $data['build_zd'] = $build_zd;
    $data['unit'] = intval($unit);
    $data['unit_zd'] = $unit_zd;
    $data['room'] = intval($room);
    $data['room_zd'] = $room_zd;
    $data['license'] = intval($license);
    $data['idcard'] = intval($idcard);
    $data['gender'] = intval($gender);
    $data['realname'] = intval($realname);
    $data['mobile'] = intval($mobile);
    $data['xy_content'] = htmlspecialchars_decode($xy_content);
    $data['verity_mobile'] = $verity_mobile;
    $data['verity_code'] = $verity_code;
    $data['copyright'] = set('p68');
    $data['copyright_url'] = set('p69');
    $data['company'] = set('p74');
    $data['sysmobile'] = set('p1');
    $data['sysmobile'] = set('p1');
    $region = pdo_get('xcommunity_region', array('id' => $regionid), array('commission'));
    $data['commission'] = $region['commission'] ? $region['commission'] : '0.00';
    $data['xqstatus'] = set('x59', $regionid) ? 1 : 0;
    $tel1 = set('p1');
    $tel2 = util::tel($regionid);
    $data['tel'] = $tel2 ? 'tel:' . $tel2 : 'tel:' . $tel1;
    $data['rechargestatus'] = set('p44') ? 1 : 0;
    $data['rechargeurl'] = url('entry', array('m' => 'recharge', 'do' => 'pay'));
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['headimg'] = tomedia('headimg_' . $_W['account']['acid'] . '.jpg');
    $data['qrcodeimg'] = tomedia('qrcode_' . $_W['account']['acid'] . '.jpg');
    $data['regiontitle'] = $_SESSION['community']['title'];
    $data['follow'] = (!$_W['fans']['follow'] && set('p113')) ? 1 : 0;
    $data['bannerstatus'] = set('p110') ? 1 : 0;
    $data['followtitle'] = set('p111') ? set('p111') : '请关注公众号';
    $data['followdesc'] = set('p112') ? set('p112') : '赶快关注该公众号';
    $data['appstatus'] = $_W['container'] == 'wechat' ? 0 : 1;
    $data['redbg'] = tomedia(set('p117')) ? tomedia(set('p117')) : MODULE_URL . 'template/mobile/default2/static/images/redBg.jpg';
    $data['redimg'] = tomedia(set('p118')) ? tomedia(set('p118')) : MODULE_URL . 'template/mobile/default2/static/images/img1.jpg';
    $data['topimg'] = tomedia(set('p123')) ? tomedia(set('p123')) : MODULE_URL . 'template/mobile/default2/static/img/cheng.png';
    $data['costimg'] = tomedia(set('p124')) ? tomedia(set('p124')) : MODULE_URL . 'template/mobile/default2/static/img/cheng.png';
    $data['txkey'] = set('p122');
    $data['agreement'] = set('p153');
    $data['count_status'] = set('p105');
    $data['uid'] = $_W['member']['uid'];
    $data['openLbs'] = set('p86') ? 1:0;//是否开启开门定位
    $data['carDesc'] = set('p166') ? set('p166'):0;// 拼车的免责条款
    // 报修提交时间段提交报修
    $p168 = set('p168') ? set('p168') : 0;
    $start = set('p169') ? set('p169') : '8:00';
    $end = set('p170') ? set('p170') : '20:00';
    $nowDate = date('Y/m/d', time());
    $data['repairAdd'] = $p168;
    $data['repairStart'] = $nowDate . ' ' . $start;
    $data['repairEnd'] = $nowDate . ' ' . $end;
    util::send_result($data);
}
elseif ($op == 'footer') {
    $data = array();
    if (set('b4')) {
        $data[] = array_filter(
            array('img'    => tomedia(set('b1')) ? tomedia(set('b1')) : MODULE_URL . 'template/mobile/default2/static/images/icon/index01.png',
                  'url'    => set('b2') ? set('b2') : $this->createMobileUrl('home'),
                  'title'  => set('b3') ? set('b3') : '社区',
                  'type'   => 'link',
                  'active' => tomedia(set('b1')) ? tomedia(set('b1')) : MODULE_URL . 'template/mobile/default2/static/images/icon/index01-active.png',
            )
        );
    }
    if (set('b8')) {
        $data[] = array_filter(
            array('img'    => tomedia(set('b5')) ? tomedia(set('b5')) : MODULE_URL . 'template/mobile/default2/static/images/icon/index02.png',
                  'url'    => set('b6') ? set('b6') : $this->createMobileUrl('service'),
                  'title'  => set('b7') ? set('b7') : '服务',
                  'type'   => 'link',
                  'active' => tomedia(set('b5')) ? tomedia(set('b5')) : MODULE_URL . 'template/mobile/default2/static/images/icon/index02-active.png',

            )
        );
    }
    if (set('b12')) {
        $data[] = array_filter(
            array('img'    => tomedia(set('b9')) ? tomedia(set('b9')) : MODULE_URL . 'template/mobile/default2/static/images/icon/open01.png',
                  'title'  => set('b11') ? set('b11') : '门禁',
                  'url'    => set('b10') ? set('b10') : '',
                  'type'   => set('b10') ? 'link' : 'lock',
                  'active' => tomedia(set('b9')) ? tomedia(set('b9')) : MODULE_URL . 'template/mobile/default2/static/images/icon/open01.png',

            )
        );
    }
    if (set('b24')) {
        $tel1 = set('p1');
        $tel2 = util::tel($_SESSION['community']['regionid']);
        $tel = $tel2 ? 'tel:' . $tel2 : 'tel:' . $tel1;
        $data[] = array_filter(
            array('img'    => tomedia(set('b21')) ? tomedia(set('b21')) : MODULE_URL . 'template/mobile/default2/static/images/icon/index05.png',
                  'url'    => set('b22') ? set('b22') : $tel,
                  'title'  => set('b23') ? set('b23') : '电话',
                  'type'   => 'link',
                  'active' => tomedia(set('b21')) ? tomedia(set('b21')) : MODULE_URL . 'template/mobile/default2/static/images/icon/index05-active.png',
            )
        );
    }
    if (set('b16')) {
        $total = util::readnotice($_SESSION['community']['regionid']);
        $data[] = array_filter(
            array('img'    => tomedia(set('b13')) ? tomedia(set('b13')) : MODULE_URL . 'template/mobile/default2/static/images/icon/index03.png',
                  'url'    => set('b14') ? set('b14') : $this->createMobileUrl('announcement'),
                  'title'  => set('b15') ? set('b15') : '通知',
                  'type'   => 'link',
                  'active' => tomedia(set('b13')) ? tomedia(set('b13')) : MODULE_URL . 'template/mobile/default2/static/images/icon/index03-active.png',
                  'total'  => set('b14') ? 0 : (string)$total

            )
        );
    }

    if (set('b20')) {
        $data[] = array_filter(
            array('img'    => tomedia(set('b17')) ? tomedia(set('b17')) : MODULE_URL . 'template/mobile/default2/static/images/icon/index04.png',
                  'url'    => set('b18') ? set('b18') : $this->createMobileUrl('member'),
                  'title'  => set('b19') ? set('b19') : '我的',
                  'type'   => 'link',
                  'active' => tomedia(set('b17')) ? tomedia(set('b17')) : MODULE_URL . 'template/mobile/default2/static/images/icon/index04-active.png',
            )
        );
    }

    util::send_result($data);
}