<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

/**
 * Created by lanniu.
 * User: zhoufeng
 * Time: 2017/6/17 上午11:57
 */
class model_user
{
    // 会员数据表
    public static $TABLE_USER = "xcommunity_member";
    //会员地址表
    public static $TABLE_ADDRESS = "xcommunity_member_room";

    // 通过$_W['member']['uid']去判断有没注册小区
    static function mc_check($do = '')
    {
        global $_W;
        //判断系统是否开启
        if (set('p66')) {
            itoast(set('p67'), '', 'error');
            exit();
        }
        if (empty($_W['fans']['follow'])) {
            if (set('p52')) {
                //开启必须微信使用
                self::alertWechatLogin();
            }
            self::checkauth();
        }
        $condition = "t1.uniacid =:uniacid and  t1.uid=:uid and t1.enable = 1 ";

        //需要考虑游客情况
        $sql = "select t1.visit,t1.status,t1.open_status,t1.regionid,t1.id,t1.uid,t1.license,t2.title,t2.thumb,t2.pid,t2.qq,t2.linkway from" . tablename('xcommunity_member') . "t1 left join" . tablename('xcommunity_region') . "t2 on t2.id = t1.regionid where $condition";

        $params[':uniacid'] = $_W['uniacid'];
        $params[':uid'] = $_W['member']['uid'];
        $item = pdo_fetch($sql, $params);

        if (empty($item)) {
//            if($_W['container'] == 'wechat'){
//                $url = util::murl('register');
//            }else{
//                $url = util::murl('register',array('op'=>'register'));
//            }
            $url = util::murl('register');
            header("Location:" . $url);
            exit();
        }
        else {
            if (empty($item['status'])) {
                $url = util::murl('register', array('op' => 'region'));
                itoast('请耐心等待审核', $url, 'error');
                exit();
            }
            else {
                $tsql = "select t1.enable as benable,t1.id as bindid,t2.build,t2.unit,t2.address,t1.addressid from" . tablename('xcommunity_member_bind') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id where t1.enable=1 and t1.memberid=:memberid";
                $par[':memberid'] = $item['id'];
                $room = pdo_fetch($tsql, $par);
                if ($item) {
                    $item['bindid'] = $room['bindid'];
                    $item['build'] = $room['build'];
                    $item['unit'] = $room['unit'];
                    $item['address'] = $room['address'];
                    $item['addressid'] = $room['addressid'];
                }
                $d = array(
                    'uniacid'    => $_W['uniacid'],
                    'uid'        => $_W['member']['uid'],
                    'regionid'   => $item['regionid'],
                    'createtime' => TIMESTAMP
                );
                pdo_insert('xcommunity_member_visit_log', $d);
                if ($do) {
                    if (set('x4', $item['regionid']) || set('p22')) {
                        if (empty($item['addressid'])) {
                            util::clickmenu($do, $item['id'], $item['regionid']);
                        }
                    }
                }


                $_SESSION['community'] = $item;
                $_W['member']['title'] = $_SESSION['community']['title'];
                $_W['member']['address'] = $_SESSION['community']['address'];
                $_W['member']['regionid'] = $_SESSION['community']['regionid'];
                $_W['member']['addressid'] = $_SESSION['community']['addressid'];
                $_W['member']['memberid'] = $_SESSION['community']['id'];
                $_W['page']['title'] = $_SESSION['community']['title'];
                return $item;
                exit();
            }
        }

    }

    static function fetch_member_one($memberid)
    {
        return pdo_get(self::$TABLE_USER, array('id' => $memberid), array('status', 'open_status', 'regionid', 'id', 'uid'));
    }

    static function get_member_one($regionid)
    {
        global $_W;
        return pdo_get(self::$TABLE_USER, array('regionid' => $regionid, 'uid' => $_W['member']['uid'], 'enable' => 1), array('id'));
    }

    static function fetch_address($addressid = '')
    {
        global $_W;
        if (empty($addressid)) {
            $condition = array('id' => $addressid);

        }
        $address = pdo_get(self::$TABLE_ADDRESS, $condition, array('area', 'build', 'unit', 'room', 'status', 'id', 'address'));
        if ($address) {
            $addr = '';
            if ($address['area']) {
                $addr .= $address['area'] . '区';
            }
            if ($address['build']) {
                $addr .= $address['build'] . '栋';
            }
            if ($address['unit']) {
                $addr .= $address['unit'] . '单元';
            }
            if ($address['room']) {
                $addr .= $address['room'] . '室';
            }
            $addr = $address['address'] ? $address['address'] : $addr;
            return array('id' => $address['id'], 'address' => $addr, 'status' => $address['status'], 'area' => $address['area'], 'build' => $address['build'], 'unit' => $address['unit'], 'room' => $address['room']);

        }
    }

    static function mc_register_region($regionid, $uid = '', $realname = '', $mobile = '', $license = '', $idcard = '', $gender = '')
    {
        global $_W;
        if (empty($_W['openid'])) {
            if (set('p52')) {
                //开启必须微信使用
                self::alertWechatLogin();
            }
        }
        if ($_W['member']['uid']) {
            self::checkauth();
        }
        $_uid = $_W['member']['uid'] ? $_W['member']['uid'] : mc_openid2uid($_W['openid']);
        $uid = $uid ? $uid : $_uid;
        if (empty($regionid)) {
            exit('缺少小区ID');

        }
        if (empty($uid)) {
            exit('缺少会员UID');
        }
        //首次完善资料
        if ($mobile) {
            pdo_update('mc_members', array( 'createtime' => TIMESTAMP, 'realname' => $realname, 'mobile' => $mobile), array('uid' => $uid));
            $_W['member']['realname'] = $realname;
            $_W['member']['mobile'] = $mobile;

        }

        $result = pdo_get('xcommunity_member', array('regionid' => $regionid, 'uid' => $uid), array('id'));
        $user = pdo_getall(self::$TABLE_USER, array('uid' => $uid, 'uniacid' => $_W['uniacid'], 'enable' => 1), array('id'));
        //分情况处理：1.首次注册，没有开启人工审核和游客 2.首次注册，开启人工审核，未开启游客 3.首次注册，开启人工审核，也开启游客
        // 4.首次注册，未开启人工审核，开启游客 5.开启切换审核 6.未开启切换审核
        if (empty($result['id'])) {
            $data = array(
                'uniacid'     => $_W['uniacid'],
                'regionid'    => $regionid,
                'uid'         => $uid,
                'createtime'  => TIMESTAMP,
                'open_status' => set('p32') || set('x14', $regionid) ? 1 : 0,
                'visit'       => 1
            );
            if ($user) {
                //切换小区，也许考虑游客
                $_status = set('p7') ? 0 : 1;
                $status = (set('p18') || set('x1', $regionid)) && !set('p22') && !set('x4', $regionid) ? 0 : 1;
                if ($_status == 1 && $status == 1) {
                    //审核通过，所有绑定小区改0
                    pdo_update('xcommunity_member', array('enable' => 0), array('uid' => $uid));
                }
                $data['status'] = $_status == 1 && $status == 1 ? 1 : 0;
                $data['enable'] = $_status == 1 && $status == 1 ? 1 : 0;

            }
            else {
                //首次注册判断
                $status = (set('p18') || set('x1', $regionid)) && !set('p22') && !set('x4', $regionid) ? 0 : 1;
                $data['status'] = $status;
                $data['enable'] = 1;
            }
            if (pdo_insert(self::$TABLE_USER, $data)) {
                $_SESSION['community']['regionid'] = $regionid;
                $_SESSION['community']['id'] = pdo_insertid();
                return pdo_insertid();
                exit();
            }

        }
        else {
            if (pdo_update('xcommunity_member', array('enable' => 0), array('uid' => $uid))) {
                pdo_update('xcommunity_member', array('enable' => 1), array('regionid' => $regionid, 'uid' => $uid));
            }
            $_SESSION['community']['regionid'] = $regionid;
            $_SESSION['community']['id'] = $result['id'];
            return $result['id'];
        }

    }

    static function member_check($mobile)
    {
        global $_W;
        return pdo_fetchcolumn('SELECT uid FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid AND mobile = :mobile ', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));

    }

    static function mc_register_address($regionid, $area = '', $build = '', $unit = '', $room = '', $memberid, $mobile = '', $addressid = '', $status = '', $house = '', $license = '',$realname='')
    {
        global $_W;
        $member_room = array();
        $address = pdo_get('xcommunity_member_bind', array('memberid' => $memberid, 'uniacid' => $_W['uniacid'], 'enable' => 1), array('id'));
        if ($address) {
            //新增地址
            pdo_query('update ' . tablename('xcommunity_member_bind') . "set enable=0 where id=:id ", array(':id' => $address['id']));
        }
        if ($house) {
            //开启联动
            $addressid = $room;
            $member_room = pdo_get('xcommunity_member_room', array('id' => $addressid), array('address','areaid','buildid','unitid'));
        }
        $enable = '';
        if ($addressid) {
            //判断是否扫码、注册码注册、手机号验证
            $enable = 1;
        }
        if (empty($addressid)) {
            if ($area) {
                $_area = pdo_get('xcommunity_area', array('regionid' => $regionid, 'title' => $area), array('id'));
                $areaid = $_area['id'];
                if (empty($_area)) {
                    $d = array(
                        'uniacid'  => $_W['uniacid'],
                        'regionid' => $regionid,
                        'title'    => $area,
                        'uid'      => $_W['uid']
                    );
                    pdo_insert('xcommunity_area', $d);
                    $areaid = pdo_insertid();
                }
            }
            if ($build) {
                $condition = "regionid=:regionid and buildtitle=:buildtitle";
                $par[':regionid'] = $regionid;
                $par[':buildtitle'] = $build;
                if ($area) {
                    $condition .= " and areaid=:areaid";
                    $par[':areaid'] = $areaid;
                }
                $sql = "select * from" . tablename('xcommunity_build') . "where $condition";
                $_build = pdo_fetch($sql, $par);
                $buildid = $_build['id'];
                if (empty($_build)) {
                    $dat = array(
                        'uniacid'    => $_W['uniacid'],
                        'regionid'   => $regionid,
                        'buildtitle' => $build,
                        'areaid'     => $areaid
                    );
                    pdo_insert('xcommunity_build', $dat);
                    $buildid = pdo_insertid();
                }
            }
            if ($unit) {
                $_unit = pdo_get('xcommunity_unit', array('regionid' => $regionid, 'buildid' => $buildid, 'unit' => $unit), array('id'));
                $unitid = $_unit['id'];
                if (empty($_unit)) {
                    $data = array(
                        'uniacid' => $_W['uniacid'],
                        'buildid' => $buildid,
                        'unit'    => $unit,
                        'uid'     => $_W['uid']
                    );
                    pdo_insert('xcommunity_unit', $data);
                    $unitid = pdo_insertid();
                }
            }
            $addr = '';
            $arr = util::xqzd($regionid);
            $condition = '';
            if ($area) {
                $addr .= $area . $arr['a'];
                $condition .= " and area=:area";
                $params[':area'] = $area;
            }
            if ($build) {
                $addr .= $build . $arr['b'];
                $condition .= " and build=:build";
                $params[':build'] = $build;
            }
            if ($unit) {
                $addr .= $unit . $arr['c'];
                $condition .= " and unit=:unit";
                $params[':unit'] = $unit;
            }
            if ($room) {
                $addr .= $room . $arr['d'];
                $condition .= " and room=:room";
                $params[':room'] = $room;
            }
            $sql = "select id,address from " . tablename('xcommunity_member_room') . "where regionid=:regionid and uniacid=:uniacid $condition";

            $params[':regionid'] = $regionid;
            $params[':uniacid'] = intval($_W['uniacid']);
            $r = pdo_fetch($sql, $params);
            if (empty($r['id'])) {

                $dat = array(
                    'area'       => $area,
                    'build'      => $build,
                    'unit'       => $unit,
                    'room'       => $room,
                    'square'     => '',
                    'address'    => $addr,
                    'createtime' => TIMESTAMP,
                    'regionid'   => $regionid,
                    'uniacid'    => $_W['uniacid'],
                    'code'       => rand(10000000, 99999999),
                    'enable'     => 1,
                    'areaid'     => $areaid,
                    'buildid'    => $buildid,
                    'unitid'     => $unitid

                );
                pdo_insert('xcommunity_member_room', $dat);
                $addressid = pdo_insertid();
                $_SESSION['community']['address'] = $addr;
            }
            else {
                $addressid = $r['id'];
                $member_room = pdo_get('xcommunity_member_room', array('id' => $addressid), array('address','areaid','buildid','unitid'));
                $_SESSION['community']['address'] = $member_room['address'];
            }

        }
        $bind = pdo_get('xcommunity_member_bind', array('memberid' => $memberid, 'addressid' => $addressid), array());
        if ($bind) {
            //地址已经绑定过用户
            return 3;
            exit();
        }

        $d = array(
            'uniacid'    => $_W['uniacid'],
            'memberid'   => $memberid,
            'createtime' => TIMESTAMP,
            'status'     => $status ? $status : 1,
            'enable'     => 1,
            'addressid'  => $addressid
        );
        $memberBind = pdo_insert('xcommunity_member_bind', $d);
        if ($memberBind) {
            if (empty($enable)) {
                if (empty($r['id'])) {
                    $d = array(
                        'uniacid'   => $_W['uniacid'],
                        'regionid'  => $regionid,
                        'realname'  => $_W['member']['realname'],
                        'mobile'    => $_W['member']['mobile'],
                        'addressid' => $addressid,
                        'status'    => $status ? $status : 1,
                    );
                    pdo_insert('xcommunity_member_log', $d);
                }
            }
            $card = array(
                'uniacid'   => $_W['uniacid'],
                'regionid'  => $regionid,
                'realname'  => $realname,
                'mobile'    => $mobile,
                'car_num'   => $license,
                'addressid' => $addressid
            );
            $_SESSION['community']['addressid'] = $addressid;
            if (!empty($license)) {
                pdo_insert('xcommunity_xqcars', $card);
            }
            pdo_update('xcommunity_member', array('visit' => 0, 'createtime' => TIMESTAMP), array('id' => $memberid));
            pdo_update('xcommunity_member_room', array('enable' => 1), array('id' => $addressid));
            $region = pdo_get('xcommunity_region',array('id'=>$regionid),array('title', 'province', 'city', 'dist'));
            $city = $region['province'] ? $region['province'] . ' ' . $region['city'] . ' ' . $region['dist'] : '江苏省 南京市 浦口区';
            //所有地址设置为0
            pdo_update('xcommunity_member_address', array('enable' => 0), array('uid' => $_W['member']['uid']));
            $d = array(
                'uniacid'  => $_W['uniacid'],
                'realname' => $realname,
                'mobile'   => $mobile,
                'address'  => $region['title'] . $_SESSION['community']['address'],
                'city'     => $city,
                'enable'   => 1,
                'uid'      => $_W['member']['uid']
            );
            pdo_insert('xcommunity_member_address', $d);
            return 1;
            exit();
        }
        else {
            pdo_query('update ' . tablename('xcommunity_member_bind') . "set enable=1 where id=:id ", array(':id' => $address['id']));
        }

    }

    // 用户是否登陆，非微信检查是否登录
    static function checkauth()
    {
        global $_W;
        //判断是微信还是浏览器打开
        load()->model('mc');
        if (!empty($_W['member']) && (!empty($_W['member']['mobile']) || !empty($_W['member']['email']))) {
            return true;
        }
        if (!empty($_W['openid'])) {

            $fan = mc_fansinfo($_W['openid'], $_W['acid'], $_W['uniacid']);
            if (_mc_login(array('uid' => intval($fan['uid'])))) {
                return true;
            }
        }
        $forward = base64_encode($_SERVER['QUERY_STRING']);
        if ($_W['isajax'] || ($_GET['ajax'] == 1 && $_GET['callback'])) {
            $result = array();
            $result['url'] = util::murl('auth', array('op' => 'login', 'forward' => $forward));
            $result['act'] = 'redirect';
            util::send_error('10001', $result);
        }
        else {
            @header("location: " . util::murl('auth', array('op' => 'login', 'forward' => $forward)));
            exit();
        }
    }

    static function alertWechatLogin()
    {
        global $_W;
        $qrcode = tomedia('qrcode_' . $_W['acid'] . '.jpg');
        die("<!DOCTYPE html>
            <html><head><meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'>
                <title>提示</title><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'><link rel='stylesheet' type='text/css' href='https://res.wx.qq.com/connect/zh_CN/htmledition/style/wap_err1a9853.css'>
            </head>
            <body>
            <div class='page_msg'><div class='inner'><span class='msg_icon_wrp'><i class='icon80_smile'></i></span><div class='msg_content'><h4>请先用微信扫二维码关注我们</h4><br><img width='200px' src='" . $qrcode . "'></div></body></html></div></div></div>
            </body></html>");
    }
}