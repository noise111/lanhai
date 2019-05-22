<?php

/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/10/17 下午8:33
 */
class util
{
    /**
     * 计算某个经纬度的周围某段距离的正方形的四个点
     *
     * @param lng float 经度
     * @param lat float 纬度
     * @param distance float 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
     * @return array 正方形的四个点的经纬度坐标
     */
    static function squarePoint($lng, $lat, $distance = 0.5)
    {

        $dlng = 2 * asin(sin($distance / (2 * EARTH_RADIUS)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);

        $dlat = $distance / EARTH_RADIUS; //EARTH_RADIUS地球半径
        $dlat = rad2deg($dlat);

        return array(
            'left-top'     => array('lat' => $lat + $dlat, 'lng' => $lng - $dlng),
            'right-top'    => array('lat' => $lat + $dlat, 'lng' => $lng + $dlng),
            'left-bottom'  => array('lat' => $lat - $dlat, 'lng' => $lng - $dlng),
            'right-bottom' => array('lat' => $lat - $dlat, 'lng' => $lng + $dlng)
        );
    }

    //测算距离
    static function getDistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2)
    {
        $radLat1 = $lat1 * (float)M_PI / 180;
        $radLat2 = $lat2 * (float)M_PI / 180;
        $a = $lat1 * (float)M_PI / 180 - $lat2 * (float)M_PI / 180;
        $b = $lng1 * (float)M_PI / 180 - $lng2 * (float)M_PI / 180;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * EARTH_RADIUS;
        $s = round($s * 1000);
        if ($len_type > 1) {
            $s /= 1000;
        }
        return round($s, $decimal);
    }

    // url生成
    static function murl($do, $query = array(), $noredirect = true)
    {
        global $_W;
        $query['do'] = $do;
        $query['m'] = strtolower(MODULE_NAME);
        $url = murl('entry', $query, $noredirect);
        return $_W['siteroot'] . 'app' . trim($url, '.');
    }

    static function xqslide($type, $regionid)
    {
        global $_W;
        $slides = pdo_fetchall("SELECT s.thumb,s.url FROM" . tablename('xcommunity_slide') . "as s left join " . tablename('xcommunity_slide_region') . "as r on s.id = r.sid WHERE s.uniacid=:uniacid and s.type=:type and s.status = 1 and r.regionid =:regionid and s.starttime <= :nowtime and s.endtime >=:nowtime order by displayorder desc ", array(':uniacid' => $_W['uniacid'], ":type" => $type, ":regionid" => $regionid, ':nowtime' => time()));

        foreach ($slides as $key => $val) {
            $slides[$key]['img'] = tomedia($val['thumb']);
        }
        return $slides;
    }

    static function readnotice($regionid)
    {
        global $_W;
        $count = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_announcement') . "as a left join" . tablename('xcommunity_announcement_region') . "as r on a.id=r.aid where a.uniacid =:uniacid and r.regionid=:regionid and a.enable= 1 ", array(':uniacid' => $_W['uniacid'], ':regionid' => $regionid));

        $total = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_reading_member') . "where uid=:uid and regionid=:regionid", array(':uid' => $_W['member']['uid'], ':regionid' => $regionid));

        return $count - $total > 0 ? $count - $total : 0;
    }

    static function sendnotice($content, $openid = '')
    {
        global $_W;
        $message = array(
            'msgtype' => 'text',
            'text'    => array('content' => urlencode($content)),
            'touser'  => $openid ? $openid : $_W['fans']['from_user'],
        );
        $account_api = WeAccount::create();
        $status = $account_api->sendCustomNotice($message);
    }

    static function clickmenu($do, $memberid = '', $regionid = '')
    {
        global $_W;

        $menu = pdo_get('xcommunity_nav', array('do' => $do, 'uniacid' => $_W['uniacid']), array('view'));
        if (empty($menu['view'])) {
//            $regionid = $_SESSION['community']['regionid'];
            if (empty($regionid)) {
                itoast('请选择小区并完善资料，再进行下一步操作', self::murl('register', array('op' => 'region')), 'success');
                exit();
            }
            $community = 'community' . $_W['uniacid'];
            $style = $_W['setting'][$community]['styleid'] ? $_W['setting'][$community]['styleid'] : 'default2';
            $x61 = set('x61', $regionid);
            $content = set('x63', $regionid) ? set('x63', $regionid) : '该小区暂未与本平台合作，请你建议物业尽快入驻本平台';
            if ($x61) {
                itoast($content, self::murl('home', array('regionid' => $regionid)), 'success');
            }
            if ($style == 'default') {
                itoast('请先完善资料，再进行下一步操作！', self::murl('register', array('regionid' => $regionid, 'op' => 'member', 'p' => 1, 'memberid' => $memberid)), 'success');
                exit();
            }
            elseif ($style == 'default2') {
                itoast('请先完善资料，再进行下一步操作！', self::murl('register', array('regionid' => $regionid, 'op' => 'guide', 'p' => 1, 'memberid' => $memberid)), 'success');
                exit();
            }
            else {
                itoast('请先完善资料，再进行下一步操作！', self::murl('register', array('regionid' => $regionid, 'op' => 'register', 'p' => 1, 'memberid' => $memberid)), 'success');
                exit();
            }

        }
    }

    static function sendtpl($regionid = '', $content, $data, $url, $tplid, $type = '', $stype = 0, $reportid = 0)
    {
        global $_W;
        if ($type) {
            //商家是按UID来查询
            $condition = "t1.uniacid=:uniacid and t1.type in(1,3) $data";
            $parms[':uniacid'] = $_W['uniacid'];

            $sql = 'select distinct t3.openid from' . tablename('xcommunity_wechat_notice') . "as t1 left join" . tablename('xcommunity_staff') . "t3 on t1.staffid = t3.id where $condition";
            $notice = pdo_fetchall($sql, $parms);
//            print_r($notice);exit();
        }
        else {
            //按小区来查询
            $condition = "t1.uniacid=:uniacid and t1.type in(1,3)";
            $parms[':uniacid'] = $_W['uniacid'];
            if ($regionid) {
                $condition .= " and t2.regionid=:regionid";
                $parms[':regionid'] = $regionid;
            }
            if ($data) {
                $condition .= $data;
            }
            $sql = 'select distinct t3.openid from' . tablename('xcommunity_wechat_notice') . "as t1 left join" . tablename('xcommunity_wechat_notice_region') . "as t2 on t1.id=t2.nid left join" . tablename('xcommunity_staff') . "t3 on t1.staffid = t3.id where $condition";
            $notice = pdo_fetchall($sql, $parms);
        }

        if ($notice) {
            foreach ($notice as $key => $item) {
                $ret = util::sendTplNotice($item['openid'], trim($tplid), $content, $url, $topcolor = '#FF683F');

                $uid = pdo_getcolumn('mc_mapping_fans', array('openid' => $item['openid']), 'uid');
                if ($reportid && $stype) {
                    $dat = array(
                        'uniacid'  => $_W['uniacid'],
                        'uid'      => $uid,
                        'sendtime' => TIMESTAMP,
                        'reportid' => $reportid,
                        'type'     => $stype
                    );
                    pdo_insert('xcommunity_report_send_log', $dat);
                }

            }

        }

    }

    static function sendxqtpl($regionid, $content, $data, $url, $tplid, $enable = '', $type = 0, $reportid = 0)
    {
        global $_W;
        $condition = " t1.uniacid={$_W['uniacid']} and t2.regionid={$regionid} and t3.cid ={$data} and t1.type in(1,3)";
        if ($enable) {
            $condition .= " and t1.enable={$enable}";
        }
        $sql = 'select t4.openid,t5.uid from' . tablename('xcommunity_notice') . "as t1 left join" . tablename('xcommunity_notice_region') . "as t2 on t1.id=t2.nid left join " . tablename('xcommunity_notice_category') . "as t3 on t1.id=t3.nid left join" . tablename('xcommunity_staff') . "t4 on t1.staffid=t4.id left join" . tablename('mc_mapping_fans') . "t5 on t5.openid=t4.openid where $condition";
        $notice = pdo_fetchall($sql);
        if ($notice) {
            foreach ($notice as $key => $item) {
                $ret = util::sendTplNotice($item['openid'], $tplid, $content, $url, $topcolor = '#FF683F');
                if ($reportid && $type) {
                    $dat = array(
                        'uniacid'  => $_W['uniacid'],
                        'uid'      => $item['uid'],
                        'sendtime' => TIMESTAMP,
                        'reportid' => $reportid,
                        'type'     => $type
                    );
                    pdo_insert('xcommunity_report_send_log', $dat);
                }
            }
        }
    }

    static function sendxqsms($regionid, $data, $type, $api, $tpl_id, $cid, $reportid)
    {
        global $_W;
        $condition = " t1.uniacid={$_W['uniacid']} and t2.regionid={$regionid} and t3.cid ={$cid}";
        $sql = 'select t4.mobile from' . tablename('xcommunity_notice') . "as t1 left join" . tablename('xcommunity_notice_region') . "as t2 on t1.id=t2.nid left join " . tablename('xcommunity_notice_category') . "as t3 on t1.id=t3.nid left join" . tablename('xcommunity_staff') . "t4 on t1.staffid=t4.id where $condition";

        $notice = pdo_fetchall($sql);

        if ($notice) {
            foreach ($notice as $key => $item) {
                $ret = sms::send($item['mobile'], $data, $type, '', $api, $tpl_id);
                $d = array(
                    'uniacid'    => $_W['uniacid'],
                    'sendid'     => $reportid,
                    'uid'        => $_W['member']['uid'],
                    'type'       => 5,
                    'cid'        => 2,
                    'status'     => 1,
                    'createtime' => TIMESTAMP,
                    'regionid'   => $regionid
                );
                $d['status'] = $api == 2 ? $regionid : 0;
                if ($ret) {
                    pdo_insert('xcommunity_send_log', $d);
                }

            }
        }
    }

    static function payset($type)
    {
        global $_W;
        return pdo_get('xcommunity_pay', array('type' => $type, 'uniacid' => $_W['uniacid']), array());
    }

    static function latestnotice($status, $regionid)
    {
        global $_W;
        $notice = pdo_fetchall("select a.* from" . tablename('xcommunity_announcement') . "as a left join" . tablename('xcommunity_announcement_region') . "as r on a.id = r.aid where r.regionid=:regionid and status = :status order by a.createtime desc limit 3 ", array(':regionid' => $regionid, ':status' => $status));
        foreach ($notice as $k => $v) {
            $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=detail&id={$v['id']}&do=announcement&m=" . MODULE_NAME;
            $notice[$k]['url'] = $url;
        }
        return $notice;
    }

    static function xquser($uid)
    {
        global $_W;
        $uid = $uid && $uid != 1 ? $uid : $_W['uid'];
        $sql = "select t1.*,t2.realname,t2.mobile from" . tablename('xcommunity_users') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid = t2.id where t1.uid=:uid and t1.type!=6";
        $user = pdo_fetch($sql, array(':uid' => $uid));
        $role = pdo_get('xcommunity_menu_role', array('id' => $user['roleid']), array());
        if ($role['menus']) {
            $user['menus'] = $role['menus'];
        }
        $regionids = pdo_getall('xcommunity_users_region', array('usersid' => $uid), array('regionid'));
        $reg = array();
        $parkids = array();
        if ($regionids) {
            foreach ($regionids as $key => $val) {
                $reg[] = $val['regionid'];
            }
            $reg = implode(',', $reg);
            $regionIds = _array_column($regionids, 'regionid');
            $regionParks = pdo_getall('xcommunity_parks', array('regionid' => $regionIds), array('id'), 'id');
            $parkids = _array_column($regionParks, 'id');
        }

        if ($user) {
            $u = array(
                'type'         => $user['type'],
                'regionid'     => $reg,
                'pid'          => $user['pid'],
                'departmentid' => $user['departmentid'],
                'staffid'      => $user['staffid'],
                'menus'        => $user['menus'],
                'xqmenus'      => $user['xqmenus'],
                'groupid'      => $user['groupid'],
                'realname'     => $user['realname'],
                'mobile'       => $user['mobile'],
                'xqtype'       => $user['xqtype'],
                'balance'      => $user['balance'],
                'credit'       => $user['credit'],
                'uid'          => $user['uid'],
                'uuid'         => $user['uuid'],
                'uniacid'      => $user['uniacid'],
                'txpay'        => $user['txpay'],
                'txcid'        => $user['txcid'],
                'creditstatus' => $user['creditstatus'],
                'integral'     => $user['integral'],
                'store'        => $user['store'],
                'id'           => $user['id'],
                'menu_ops'     => $role['menu_ops'] ? $role['menu_ops'] : '',
                'parkids'      => $parkids,
            );
            return $u;
        }


    }

    static function fetchall_category($type, $stat = 0)
    {
        global $_W;
        if (empty($type)) {
            return false;
        }
        if ($stat == 1 && $type == 1) {
            $category = pdo_fetchall("select t1.* from" . tablename('xcommunity_category') . "t1 left join" . tablename('xcommunity_category_region') . "t2 on t1.id=t2.cid where t1.uniacid=:uniacid and t1.type=:type and t2.regionid=:regionid", array(':uniacid' => $_W['uniacid'], ':type' => $type, ':regionid' => $_SESSION['community']['regionid']));
        }
        else {
            $category = pdo_getall('xcommunity_category', array('uniacid' => $_W['uniacid'], 'type' => $type), array(), 'id');
        }
        return $category;
    }

    static function fetch_category_one($cid = '', $regionid = '', $type = '')
    {
        global $_W;
        $condition = " uniacid ={$_W['uniacid']}";
        $condition = $cid ? $condition . ' and id=' . $cid : $condition;
        $condition = $regionid ? $condition . ' and regionid=' . $regionid : $condition;
        $condition = $type ? $condition . ' and type=' . $type : $condition;
        $category = pdo_get('xcommunity_category', $condition, array());
        return $category;
    }

    static function fetch_rank_one($id, $type)
    {
        global $_W;
        if (empty($id)) {
            return false;
        }
        $rank = pdo_get('xcommunity_rank', array('uniacid' => $_W['uniacid'], 'rankid' => $id, 'type' => $type), array());
        return $rank;
    }

    static function send_error($number, $msg, $obj = array())
    {
        $obj = $obj ? $obj : array();
        $obj['err_code'] = intval($number);
        $obj['err_msg'] = $msg;
        header('Content-type:application/json');
        $obj = json_encode($obj);
        if ($_GET['callback']) {
            $obj = $_GET['callback'] . '(' . $obj . ')';
        }
        die($obj);
    }

    static function send_result($data = array())
    {
        $obj = array();
        $obj['err_code'] = 0;
        $obj['err_msg'] = 'success';
        $obj['data'] = $data;
        header('Content-type:application/json');
        $obj = json_encode($obj);
        if ($_GET['callback']) {
            $obj = $_GET['callback'] . '(' . $obj . ')';
        }
        die($obj);
    }

    static function sendTplNotice($openid, $template_id, $content, $url, $topcolor = '')
    {
        global $_GPC, $_W;
        $account_api = WeAccount::create();
        $status = $account_api->sendTplNotice($openid, $template_id, $content, $url);
        return $status;
    }

    static function tjgoods($regionid, $page = '')
    {
        global $_W;
        //查出所有的小区超市商品
        $regions = pdo_getall('xcommunity_goods_region', array('regionid' => $regionid), array('gid'), 'gid');
        $regions_gids = _array_column($regions, 'gid');
        $condition['uniacid'] = $_W['uniacid'];
        $condition['isrecommand'] = 1;
        $condition['status'] = 1;
        $condition['isshow'] = 0;
        $condition['type <>'] = 2;
        $condition['id'] = $regions_gids;
        $shop_list = pdo_getall('xcommunity_goods', $condition, array(), '', array('sort DESC'));
        foreach ($shop_list as $k => $v){
            $shop_list[$k]['url'] = $v['wlinks'] ? $v['wlinks'] : $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=detail&id={$v['id']}&do=shopping&m=" . MODULE_NAME;
        }
        //查所有小区商家的商品
        $cond['uniacid'] = $_W['uniacid'];
        $cond['isrecommand'] = 1;
        $cond['status'] = 1;
        $cond['isshow'] = 0;
        $cond['type'] = 2;
        $cond['endtime >'] = TIMESTAMP;
        $bus_list = pdo_getall('xcommunity_goods', $cond, array(), '', array('sort DESC'));
        foreach ($bus_list as $k => $v){
            $bus_list[$k]['url'] = $v['wlinks'] ? $v['wlinks'] : $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=list&do=business&m=" . MODULE_NAME . "#/couponDetail/" . $v['id'];
        }
        $list = array_merge_recursive($shop_list, $bus_list);
        $goods = array();
        foreach ($list as $val) {
            $imgs = explode(',', $val['thumb_url']);
            $goods[] = array(
                'title'        => $val['title'],
                'sort'         => $val['sort'],
                'marketprice'  => round($val['marketprice'], 2),
                'productprice' => round($val['productprice'], 2),
                'thumb'        => $val['thumb'] ? tomedia($val['thumb']) : tomedia($imgs[0]),
                'url'          => $val['url']
            );
        }
        sortArrByField($goods,'sort',true);
        return $goods;
    }

    static function auth($deviceid)
    {
        global $_W;
        load()->func('communication');

        if ($deviceid) {
            $device = pdo_get('xcommunity_building_device', array('device_code' => $deviceid, 'uniacid' => $_W['uniacid']), array('category'));
            if ($device['category'] == 1) {
                $data = array(
                    'id'     => $deviceid,
                    'action' => 'open',
                    't'      => TIMESTAMP
                );
                $url = "http://door.njlanniu.com/cooperation/openlock/servlet.jspx";
                $resp = ihttp_post($url, $data);
                $resp = $resp['content'];
                if ($resp == 'ok') {
                    $content = array(
                        'code'   => 0,
                        'info'   => '成功开门',
                        'status' => 'ok'
                    );
                }
                else {
                    $content = array(
                        'code'   => 1,
                        'info'   => '设备离线',
                        'status' => 'no'
                    );
                }
            }
            elseif ($device['category'] == 2) {
                $result = ihttp_request('http://api.njlanniu.cn/addons/lanniu/api.php', array('type' => 'auth', 'deviceid' => $deviceid), null, 5);
                $result = @json_decode($result['content'], true);
                $content = $result['data'];
            }
            elseif ($device['category'] == 3) {
                $data = array(
                    'type'     => 'door',
                    'deviceid' => $deviceid
                );
                $result = ihttp_post('http://193.112.16.186/addons/lanniu/api.php', $data);
                $result = @json_decode($result['content'], true);
                $content = $result['data'];
            }
            elseif ($device['category'] == 4 || $device['category'] == 5 || $device['category'] == 6 || $device['category'] == 7 || $device['category'] == 8) {
                $data = array(
                    'open'     => 1,
                    'identity' => $deviceid
                );
                $param = json_encode($data);
                $result = http_post('http://122.114.58.8:8018/cp/ly/remoteOpenDoor.ext', $param);
                $result = @json_decode($result['content'], true);
                if ($result['success'] == 1) {
                    $content = array(
                        'code'   => 0,
                        'info'   => '成功开门',
                        'status' => 'ok'
                    );
                }
                else {
                    $content = array(
                        'code'   => 1,
                        'info'   => '设备离线',
                        'status' => 'no'
                    );
                }

            }

            return $content;
        }
    }

    static function app_send($uid, $itoast)
    {
        global $_W;
        $itoast = str_replace('%', '!', urlencode($itoast));
        $url = "http://127.0.0.1:8080/cooperation/lanniu/servlet.jspx?action=push&id=" . $uid . "&itoast=" . $itoast;
        load()->func('communication');
        $resp = ihttp_get($url);
    }

    //操作日志
    static function permlog($logname, $op)
    {
        global $_GPC, $_W;
        $data = array(
            'uniacid'    => $_W['uniacid'],
            'uid'        => $_W['uid'],
            'name'       => $logname,
            'op'         => $op,
            'createtime' => TIMESTAMP,
            'ip'         => CLIENT_IP
        );

        pdo_insert('xcommunity_users_log', $data);
    }

    //从微信服务器上获取媒体文件
    static function get_media($media_id)
    {
        global $_W;
        load()->func('communication');
        load()->classs('weixin.account');
        $obj = new WeiXinAccount();
        $access_token = $obj->fetch_available_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token=' . $access_token . '&media_id=' . $media_id;
        $data = ihttp_get($url);
        if ($data['headers']['Content-Type'] == 'image/jpeg') {
            load()->func('file');
            $imgname = 'images/xiaoqu/' . $_W['uniacid'] . '/XQ' . time() . rand(10000, 99999) . '.' . 'jpg';
            if (!file_write($imgname, $data['content'])) {
                return error(-1, '图片保存失败.');
            }
            if (!empty($_W['setting']['remote']['type'])) { // 判断系统是否开启了远程附件
                $remotestatus = file_remote_upload($imgname); //上传图片到远程
                if (is_error($remotestatus)) {
                    return error(-1, '远程附件上传失败，请检查配置并重新上传');
                }
                else {
                    $remoteurl = tomedia($imgname);  // 远程图片的访问URL
                }
            }
            else {
                $remoteurl = tomedia($imgname);
            }
            return $remoteurl;
        }
        else {
            return '';
        }


    }

    static function xqzd($regionid = '')
    {
        global $_W;
        $p55 = self::set('p55');
        $p37 = self::set('p37');
        $p39 = self::set('p39');
        $p41 = self::set('p41');
        $p43 = self::set('p43');
        if ($p55) {
            $a = $p37;
            $b = $p39;
            $c = $p41;
            $d = $p43;
        }
        else {
            $a = self::set('x46', array('regionid' => $regionid));
            $b = self::set('x47', array('regionid' => $regionid));
            $c = self::set('x48', array('regionid' => $regionid));
            $d = self::set('x49', array('regionid' => $regionid));
        }
        return array('a' => $a, 'b' => $b, 'c' => $c, 'd' => $d);
    }

    static function set($key, $data = '')
    {
        global $_W;
        if ($data) {
            $uniacid = $data['uniacid'] ? $data['uniacid'] : $_W['uniacid'];
            $regionid = $data['regionid'];
            $condition = "uniacid=:uniacid and regionid=:regionid and xqkey =:xqkey  and value !=''";
            $params[':uniacid'] = $uniacid;
            $params[':regionid'] = $regionid;
            $params[':xqkey'] = $key;
        }
        else {
            $condition = "uniacid=:uniacid and xqkey =:xqkey  and value !=''";
            $params[':uniacid'] = $_W['uniacid'];
            $params[':xqkey'] = $key;
        }
        $sql = "select value from" . tablename('xcommunity_setting') . "where $condition";
        $set = pdo_fetch($sql, $params);
        return $set['value'];
    }

    static function xqregister($regionid)
    {
        global $_W;
        $p55 = util::set('p55');
        $p36 = util::set('p36');
        $p38 = util::set('p38');
        $p40 = util::set('p40');
        $p42 = util::set('p42');
        if ($p55) {
            $a = $p36 ? 1 : 0;
            $b = $p38 ? 1 : 0;
            $c = $p40 ? 1 : 0;
            $d = $p42 ? 1 : 0;
        }
        else {
            $a = util::set('x17', array('regionid' => $regionid)) ? 1 : 0;
            $b = util::set('x18', array('regionid' => $regionid)) ? 1 : 0;
            $c = util::set('x19', array('regionid' => $regionid)) ? 1 : 0;
            $d = util::set('x20', array('regionid' => $regionid)) ? 1 : 0;
        }
        return array('a' => $a, 'b' => $b, 'c' => $c, 'd' => $d);
    }

    static function xqset($regionid)
    {
        global $_W;
        $p55 = util::set('p55');
        $p36 = util::set('p36');
        $p38 = util::set('p38');
        $p40 = util::set('p40');
        $p42 = util::set('p42');
        if ($p55) {
            $a1 = util::set('p37');
            $b1 = util::set('p39');
            $c1 = util::set('p41');
            $d1 = util::set('p43');
            $a = $p36 ? 1 : 0;
            $b = $p38 ? 1 : 0;
            $c = $p40 ? 1 : 0;
            $d = $p42 ? 1 : 0;
        }
        else {
            $a1 = util::set('x46', array('regionid' => $regionid));
            $b1 = util::set('x47', array('regionid' => $regionid));
            $c1 = util::set('x48', array('regionid' => $regionid));
            $d1 = util::set('x49', array('regionid' => $regionid));

            $a = util::set('x17', array('regionid' => $regionid)) ? 1 : 0;
            $b = util::set('x18', array('regionid' => $regionid)) ? 1 : 0;
            $c = util::set('x19', array('regionid' => $regionid)) ? 1 : 0;
            $d = util::set('x20', array('regionid' => $regionid)) ? 1 : 0;
        }

        return array('a' => $a, 'b' => $b, 'c' => $c, 'd' => $d, 'a1' => $a1, 'b1' => $b1, 'c1' => $c1, 'd1' => $d1);
    }

    //获取小区电话
    static function tel($regionid)
    {
        global $_W;
        return pdo_fetchcolumn('SELECT linkway FROM ' . tablename('xcommunity_region') . ' WHERE uniacid = :uniacid AND id = :id ', array(':uniacid' => $_W['uniacid'], ':id' => $regionid));
    }

    static function _nav($regionid)
    {
        global $_W;
        $sql = "select url,title,thumb,do from" . tablename('xcommunity_nav') . "as t1 left join " . tablename('xcommunity_nav_region') . "as t2 on t1.id=t2.nid where t1.uniacid=:uniacid and t2.regionid=:regionid and t1.pcate != 0 and t1.show=1 order by t1.displayorder asc,t1.id asc ";
        $xqmenu = pdo_fetchall($sql, array(":uniacid" => $_W['uniacid'], ':regionid' => $regionid));
        foreach ($xqmenu as $key => $val) {
            $xqmenu[$key]['thumb'] = tomedia($val['thumb']);
        }
        return $xqmenu;
    }

    static function _page()
    {
        global $_W;
        $sql = "select * from" . tablename('xcommunity_home') . "where uniacid=:uniacid and status =1 order by displayorder asc ";
        $params[':uniacid'] = $_W['uniacid'];
        $page = pdo_fetchall($sql, $params);
        return $page;
    }

    /**
     * 获取租赁推荐
     */
    static function xqhouse($regionid)
    {
        global $_W;
        $p5 = set('p5');
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['enable'] = 0;
        $condition['recommand'] = 1;
        if (empty($p5)) {
            $condition['regionid'] = $regionid;
        }
        $houses = pdo_getall('xcommunity_houselease', $condition, array());
        $list = array();
        foreach ($houses as $key => $val) {
            if ($val['images']) {
                $images = explode(',', $val['images']);
            }
            $list[] = array(
                'id'         => $val['id'],
                'title'      => $val['title'],
                'houseModel' => $val['house_model'],
                'price'      => $val['price'],
                'modelArea'  => $val['model_area'],
                'way'        => $val['way'],
                'category'   => $val['category'],
                'src'        => $images[0] ? tomedia($images[0]) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png',
                'url'        => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=houselease&op=detail&id=' . $val["id"] . '&m=' . MODULE_NAME
            );
        }
        return $list;
    }

    /**
     * 获取集市推荐
     */
    static function xqmarket($regionid)
    {
        global $_W;
        $p4 = set('p4');
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['enable'] = 0;
        $condition['black'] = 0;
        $condition['recommand'] = 1;
        if (empty($p4)) {
            $condition['regionid'] = $regionid;
        }
        $fleds = pdo_getall('xcommunity_fled', $condition, array());
        $fleds_uids = _array_column($fleds, 'uid');
        $members = pdo_getall('mc_members', array('uid' => $fleds_uids), array('realname', 'avatar', 'uid'), 'uid');
        $list = array();
        foreach ($fleds as $key => $val) {
            if ($val['images']) {
                $images = explode(',', $val['images']);
            }
            $list[] = array(
                'id'       => $val['id'],
                'title'    => $val['title'],
                'realname' => $members[$val['uid']]['realname'],
                'avatar'   => $members[$val['uid']]['avatar'] ? $members[$val['uid']]['avatar'] : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png',
                'zprice'   => $val['zprice'],
                'detetime' => date('m-d', $val['createtime']),
                'images'   => $images,
                'url'      => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=market&op=detail&id=' . $val["id"] . '&m=' . MODULE_NAME
            );
        }
        return $list;
    }

    /**
     * 获取活动推荐
     */
    static function xqactivity($regionid)
    {
        global $_W;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['recommand'] = 1;
        $aregion = pdo_getall('xcommunity_activity_region', array('regionid' => $regionid));
        $ids = _array_column($aregion, 'activityid');
        $condition['id'] = $ids;
        $activitys = pdo_getall('xcommunity_activity', $condition, array());
        $activitys_ids = _array_column($activitys, 'id');
        $res = pdo_getall('xcommunity_res', array('aid' => $activitys_ids), array('num', 'aid'));
        $list = array();
        foreach ($activitys as $key => $val) {
            $num = 0;
            if ($val['images']) {
                $images = explode(',', $val['images']);
            }
            foreach ($res as $k => $v) {
                if ($val['id'] == $v['aid']) {
                    $num++;
                }
            }
            $list[] = array(
                'id'        => $val['id'],
                'title'     => $val['title'],
                'starttime' => date('Y/m/d', $val['starttime']),
                'endtime'   => date('Y/m/d', $val['endtime']),
                'src'       => $val['picurl'] ? tomedia($val['picurl']) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png',
                'total'     => $num,
                'url'       => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=activity&op=detail&id=' . $val["id"] . '&m=' . MODULE_NAME
            );
        }
        return $list;
    }
}