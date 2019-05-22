<?php
/**
 * Created by njxiaoqu.
 * User: zhoufeng
 * Time: 2017/5/15 下午3:44
 */
global $_W,$_GPC;
$ops = array('login','index','list','detail','logout','add_notice','cost_order','shop_detail','homemaking_detail','houselease_detail','fled_detail','member_detail','add_guard','shop','business_detail','business','use');
$op = in_array(trim($_GPC['op']),$ops) ? trim($_GPC['op']) : 'login';
if($op == 'login'){
    if($_W['ispost']) {
        load()->model('user');
        $member = array();
        $username = trim($_GPC['username']);
        pdo_query('DELETE FROM'.tablename('users_failed_login'). ' WHERE lastupdate < :timestamp', array(':timestamp' => TIMESTAMP-300));
        $failed = pdo_get('users_failed_login', array('username' => $username, 'ip' => CLIENT_IP));
        if ($failed['count'] >= 5) {
            message('输入密码错误次数超过5次，请在5分钟后再登录',referer(), 'info');
        }
        if(empty($username)) {
            message('请输入要登录的用户名');
        }
        $member['username'] = $username;
        $member['password'] = $_GPC['password'];
        if(empty($member['password'])) {
            message('请输入密码');
        }
        $record = user_single($member);
        if(!empty($record)) {
            if($record['status'] == 1) {
                message('您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！');
            }
            if (!empty($record['endtime']) && $record['endtime'] < TIMESTAMP) {
                message('您的账号有效期限已过，请联系网站管理员解决！');
            }
            $founders = explode(',', $_W['config']['setting']['founder']);
            $_W['isfounder'] = in_array($record['uid'], $founders);
            if (!empty($_W['siteclose']) && empty($_W['isfounder'])) {
                message('站点已关闭，关闭原因：' . $_W['setting']['copyright']['reason']);
            }
            $cookie = array();
            $cookie['uid'] = $record['uid'];
            $cookie['lastvisit'] = $record['lastvisit'];
            $cookie['lastip'] = $record['lastip'];
            $cookie['hash'] = md5($record['password'] . $record['salt']);
//            isetcookie('_uid', $record['uid'], 7 * 86400);
            $_SESSION['uid'] = $record['uid'];
            $session = base64_encode(json_encode($cookie));
            isetcookie('__session', $session, !empty($_GPC['rember']) ? 7 * 86400 : 0, true);
            $status = array();
            $status['uid'] = $record['uid'];
            $status['lastvisit'] = TIMESTAMP;
            $status['lastip'] = CLIENT_IP;
            user_update($status);
            if($record['type'] == ACCOUNT_OPERATE_CLERK) {
                $role = uni_permission($record['uid'], $record['uniacid']);
                isetcookie('__uniacid', $record['uniacid'], 7 * 86400);
                isetcookie('__uid', $record['uid'], 7 * 86400);
                if($_W['role'] == 'clerk' || $role == 'clerk') {
                    message('登陆成功', $this->createMobileUrl('xqsys',array('op' => 'index')), 'success');
                }
            }
            if(empty($forward)) {
                $forward = $_GPC['forward'];
            }
            if(empty($forward)) {
                $forward = $this->createMobileUrl('xqsys',array('op' => 'index'));
            }
            if ($record['uid'] != $_GPC['__uid']) {
                isetcookie('__uniacid', '', -7 * 86400);
                isetcookie('__uid', '', -7 * 86400);
            }
            pdo_delete('users_failed_login', array('id' => $failed['id']));
            message("欢迎回来，{$record['username']}。", $forward);
        } else {
            if (empty($failed)) {
                pdo_insert('users_failed_login', array('ip' => CLIENT_IP, 'username' => $username, 'count' => '1', 'lastupdate' => TIMESTAMP));
            } else {
                pdo_update('users_failed_login', array('count' => $failed['count'] + 1, 'lastupdate' => TIMESTAMP), array('id' => $failed['id']));
            }
            message('登录失败，请检查您输入的用户名和密码！');
        }
    }
    include $this->template('app/login');
}elseif($op =='logout'){
    isetcookie('__session', '', -10000);
    $forward = app_url('xqsys');
    header('Location:' . $forward);
}elseif($op == 'index'){
    if($_SESSION['uid']){
        $tyset = $this->set('','tyset');
        $user = $this->user($_SESSION['uid']);
        $data = array(
            '1' => array('1'=> '报修管理','icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/bx.png'),
            '2' => array('2' => '建议管理','icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/jy.png'),
            '3' => array('3' => '公告管理' ,'icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/tz.png'),
            '4' => array('4' =>'超市管理','icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/cs.png' ),
            '5' => array('5' =>'商家管理','icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/sj.png' ),
            '6' => array('6' => '费用查询' ,'icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/fy.png'),
            '7' => array('7' => '小区活动' ,'icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/hd.png'),
            '8' => array('8' => '家政管理' ,'icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/jz.png'),
            '9' => array('9' => '租赁管理' ,'icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/zn.png'),
            '10' => array('10' => '二手管理' ,'icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/es.png'),
            '11' => array('11' => '拼车管理' ,'icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/pc.png'),
            '12' => array('12' => '住户管理' ,'icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/zh.png'),
            '13' => array('13' => '门禁管理' ,'icon' => $_W['siteroot'].'addons/xfeng_community/template/mobile/app/static/img/mj.png'),
        );
        $menus = explode(',',$user['xqmenus']);
    }else{
        message('请重新登录',$this->createMobileUrl('xqsys',array('op' => 'login')),'error');exit();
    }

    include $this->template('app/index');
}elseif($op == 'list'){
    if(empty($_SESSION['uid'])){
        message('请重新登录',$this->createMobileUrl('xqsys',array('op' => 'login')),'error');exit();
    }
    $xqtype = intval($_GPC['type']);
    if($xqtype){
        $user = $this->user($_SESSION['uid']);
        $pindex = max(1, intval($_GPC['page']));

        if($xqtype == 1 || $xqtype == 2){
            $psize = 15;
            $condition = '';
            if($user['type'] == 3){
                $condition .=" and regionid in({$user['regionid']})";
            }
            $type = $xqtype == 1 ? 1 : 2;
            $condition .=" and type ={$type}";
            //1报修信息 2建议信息
            $sql = "select id,category,createtime,status,content from ".tablename('xcommunity_report')."where uniacid=:uniacid $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql,array(':uniacid' => $_W['uniacid']));
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_report') . "where uniacid=:uniacid $condition order by createtime desc ",array(':uniacid' => $_W['uniacid']));
            $pager = pagination($total, $pindex, $psize);
            include $this->template('app/list_repair');
        }elseif($xqtype == 3){
            //公告管理
            $psize = 15;
            $condition = "uniacid=:uniacid";
            $param[':uniacid'] = $_W['uniacid'];
            if ($user[type] == 2 || $user[type] == 3){
                //普通管理员
                $condition .= " AND uid=:uid";
                $param[':uid'] = $_SESSION['uid'];
            }
            $sql = "select title,createtime,id from ".tablename('xcommunity_announcement')."where $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql,$param);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_announcement') . "where $condition order by createtime desc ",$param);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('app/list_notice');
        }elseif($xqtype == 4){
            //超市
            $condition = "o.uniacid=:uniacid";
            $param[':uniacid'] = $_W['uniacid'];
            if ($user[type] == 2 || $user[type] == 3){
                //普通管理员
                $condition .= " AND o.uid=:uid";
                $param[':uid'] = $_SESSION['uid'];
            }
            $sql = "SELECT o.* , m.realname,m.mobile,m.regionid FROM" . tablename('xcommunity_order') . "as o left join" . tablename('xcommunity_member') . "as m  on o.from_user = m.openid AND o.regionid = m.regionid WHERE $condition  AND o.type = 'shopping' AND m.enable = 1 ORDER BY o.status DESC, o.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql, $param);
            $paytype = array(
                '0' => array('css' => 'default', 'name' => '未支付'),
                '1' => array('css' => 'danger', 'name' => '余额支付'),
                '2' => array('css' => 'info', 'name' => '在线支付'),
                '3' => array('css' => 'warning', 'name' => '货到付款'),
                '4' => array('css' => 'info', 'name' => '后台支付')
            );
            $orderstatus = array(
                '-1' => array('css' => 'default', 'name' => '已关闭'),
                '0' => array('css' => 'danger', 'name' => '待付款'),
                '1' => array('css' => 'info', 'name' => '待发货'),
                '2' => array('css' => 'warning', 'name' => '待收货'),
                '3' => array('css' => 'success', 'name' => '已完成'),
                '4' => array('css' => 'success', 'name' => '已完成')
            );
            foreach ($list as $key => $item) {
                $address = $this->address($item['from_user'], $item['regionid']);
                $list[$key]['address_realname'] = $address['realname'];
                $list[$key]['address_telephone'] = $address['telephone'];
                $region = $this->region($item['regionid']);
                $list[$key]['xqtitle'] = $region['title'];
                $user = pdo_get('users',array('uid' => $item['uid']),array('username'));
                $list[$key]['username'] = $user['username'];
                $list[$key]['cctime'] = date('Y-m-d H:i', $item['createtime']);
                $s = $item['status'];
                $list[$key]['statuscss'] = $orderstatus[$item['status']]['css'];
                $list[$key]['xqstatus'] = $orderstatus[$item['status']]['name'];
                if ($s < 1) {
                    $list[$key]['css'] = $paytype[$s]['css'];
                    $list[$key]['paytype'] = $paytype[$s]['name'];
                    continue;
                }
                $list[$key]['css'] = $paytype[$item['paytype']]['css'];
                if ($value['paytype'] == 2) {
                    if (empty($value['transid'])) {
                        $list[$key]['paytype'] = '支付宝支付';
                    } else {
                        $list[$key]['paytype'] = '微信支付';
                    }
                } else {
                    $list[$key]['paytype'] = $paytype[$item['paytype']]['name'];
                }
//                $list[$key]['ordersn'] = chunk_split($value['ordersn']);
            }
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_order') . "as o left join (" . tablename('xcommunity_region') . "as r left join" . tablename('xcommunity_member') . "as m on m.regionid = r.id ) on o.from_user = m.openid AND o.regionid = r.id WHERE $condition AND o.type = 'shopping' ", $param);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('app/list_shop');
        }elseif($xqtype == 5){
            //商家
            $condition = ' AND o.uniacid=:uniacid';
            $parms[':uniacid'] = $_W['uniacid'];
            if ($user[type]==2 || $user['type'] == 3) {
                $condition .= " AND o.uid=:uid";
                if($user['uuid']&&($user['uuid']!=1)){
                    $parms[':uid'] = $user['uuid'];
                }else{
                    $parms[':uid'] = $_SESSION['uid'];
                }
            }
            $psize = 10;
            $sql = "SELECT o.*,m.realname as realname,m.mobile as mobile,m.address as address,m.regionid as regionid FROM" . tablename('xcommunity_order') . "as o left join (" . tablename('xcommunity_region') . "as r left join" . tablename('xcommunity_member') . "as m on m.regionid = r.id ) on o.from_user = m.openid AND o.regionid = r.id WHERE o.type = 'business' $condition  ORDER BY o.status DESC, o.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql, $parms);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_order') . "as o left join (" . tablename('xcommunity_region') . "as r left join" . tablename('xcommunity_member') . "as m on m.regionid = r.id ) on o.from_user = m.openid AND o.regionid = r.id WHERE o.type = 'business' $condition ORDER BY o.status DESC, o.createtime DESC", $parms);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('app/list_business');
        }elseif($xqtype == 6){
            //费用
            $condition = "c.uniacid=:uniacid";
            $params[':uniacid'] = $_W['uniacid'];
            if ($user[type] == 3) {
                //小区管理员
                $condition .=" and r.id in({$user['regionid']})";
            }
            $list = pdo_fetchall("SELECT c.*,r.title as title FROM".tablename('xcommunity_cost')."as c left join".tablename('xcommunity_region')."as r on c.regionid = r.id WHERE $condition",$params);
            $total  = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_cost')."as c left join".tablename('xcommunity_region')."as r on c.regionid = r.id WHERE $condition",$params);
            $pager = pagination($total, $pindex, $psize);

            include $this->template('app/list_cost');
        }elseif($xqtype == 7){
            //小区活动
            $condition = "uniacid=:uniacid";
            $param[':uniacid'] = $_W['uniacid'];
            if ($user[type] == 2 || $user[type] == 3){
                //普通管理员
                $condition .= " AND uid=:uid";
                $param[':uid'] = $_SESSION['uid'];
            }
            $sql = "select * from ".tablename('xcommunity_activity')."where $condition ";
            $list = pdo_fetchall($sql,$param);
            $data ='';
            foreach ($list as $key => $item){
                $res = pdo_get('xcommunity_res',array('aid' => $item['id']),array('mobile','truename','num','status','createtime'));
                $data[] = array(
                    'title' => $item['title'],
                    'mobile' => $res['mobile'],
                    'truename' => $res['truename'],
                    'num' => $res['num'],
                    'status' => $res['status'],
                    'createtime' => $res['createtime'],
                    'price' => $item['price']
                );
            }
            $total  = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_activity')."where $condition ",$param);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('app/list_activity');
        }elseif($xqtype == 8){
            //家政
            $condition = 'uniacid=:uniacid';
            $params[':uniacid'] = $_W['uniacid'];
            if ($user[type] == 3) {
                $condition .=" and id in({$user['regionid']})";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
            $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_homemaking') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            foreach ($list as $key => $item){
                $category =  pdo_get('xcommunity_category',array('id' => $item['category']),array('name'));
                $list[$key]['cname'] = $category['name'];
            }
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_homemaking') . "WHERE $condition", $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('app/list_homemaking');
        }elseif($xqtype == 9){
            //租赁
            $condition = 'uniacid=:uniacid';
            $params[':uniacid'] = $_W['uniacid'];
            if ($user[type] == 3) {
                $condition .=" and id in({$user['regionid']})";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
            $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_houselease') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_houselease') . "WHERE $condition", $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('app/list_houselease');
        }elseif ($xqtype == 10){
            //二手
            $condition = 'uniacid=:uniacid';
            $params[':uniacid'] = $_W['uniacid'];
            if ($user[type] == 3) {
                $condition .=" and id in({$user['regionid']})";
            }
            $psize = 8;
            $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_fled') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_fled') . "WHERE $condition", $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('app/list_fled');
        }elseif ($xqtype == 11){
            //二手
            $condition = 'uniacid=:uniacid';
            $params[':uniacid'] = $_W['uniacid'];
            if ($user[type] == 3) {
                $condition .=" and id in({$user['regionid']})";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
            $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_carpool') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_carpool') . "WHERE $condition", $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('app/list_car');
        }elseif ($xqtype == 12){
            //住户
            $condition = 'uniacid=:uniacid';
            $params[':uniacid'] = $_W['uniacid'];
            if ($user[type] == 3) {
                $condition .=" and id in({$user['regionid']})";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
            $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_member') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            foreach ($list as $key =>$item){
                $region = pdo_get('xcommunity_region',array('id' => $item['regionid']),array('title'));
                $list[$key]['title'] = $region['title'];
            }
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_member') . "WHERE $condition", $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('app/list_member');
        }elseif($xqtype ==13){
            //门禁
            $pindex = max(1, intval($_GPC['page']));
            $psize  = 10;
            $condition ='';
            if ($user['type']==3) {
                //普通管理员
                $condition .= " AND  regionid in({$user['regionid']})";
            }
            $sql    = "select * from ".tablename("xcommunity_building_device")."where  uniacid =:uniacid $condition LIMIT ".($pindex - 1) * $psize.','.$psize;
            $params[':uniacid'] = $_W['uniacid'];
            $list   = pdo_fetchall($sql,$params);
            foreach ($list as $key => $item){
                $region = pdo_get('xcommunity_region',array('id' => $item['regionid']),array('title'));
                $list[$key]['xqtitle'] = $region['title'];
            }
            $total  = pdo_fetchcolumn('select count(*) from'.tablename("xcommunity_building_device")."where  uniacid =:uniacid $condition ",$params);
            $pager  = pagination($total, $pindex, $psize);
            include $this->template('app/list_guard');
        }
    }
}elseif($op =='detail'){
    //处理报修和建议
    if(empty($_SESSION['uid'])){
        message('请重新登录',$this->createMobileUrl('xqsys',array('op' => 'login')),'error');exit();
    }
    $id = intval($_GPC['id']);
    if(empty($id)){
        message('缺少参数',referer(),'error');exit();
    }
    $item = pdo_get('xcommunity_report',array('id' => $id),array('id','category','uniacid','content','status','openid','images','resolve','address','resolver'));
    if($item['resolver']){
        $user = $this->member($item['resolver']);
    }
    if(empty($item)){
        message('信息不存在或已删除',rferer(),'error');exit();
    }
    if($item['openid']){
        $member = $this->member($item['openid']);
    }
    if($item['images']){
        $imgs = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_images') . "WHERE id in({$item['images']})");
    }
    if($_W['isajax']){
        $id = intval($_GPC['id']);
        if(empty($id)){
            message('缺少参数',$this->createMobileUrl('xqsys',array('op' => 'login')),'error');exit();
        }
        $data = array(
            'status' => intval($_GPC['status']),
            'resolve' => $_GPC['resolve'],
            'resolvetime' => TIMESTAMP,
            'dealing' => $_GPC['resolver']
        );
        if(pdo_update('xcommunity_report', $data, array('id' => $id))){
            $tpl = pdo_fetch("SELECT * FROM".tablename('xcommunity_wechat_tplid')."WHERE uniacid=:uniacid",array(':uniacid' => $_W['uniacid']));
            $type = intval($_GPC['type']);
            if($tpl['grab_wc_tplid']&&$type == 1){
                $content = array(
                    'first'    => array(
                        'value' => '尊敬的业主，您的报修已经完成',
                    ),
                    'keyword1' => array(
                        'value' => $item['address'],
                    ),
                    'keyword2' => array(
                        'value' => $item['content'],
                    ),
                    'keyword3' => array(
                        'value' => $data['dealing'],
                    ),
                    'keyword4' => array(
                        'value' => date('Y-m-d H:i', TIMESTAMP),
                    ),
                    'remark'   => array(
                        'value' => '请到微信我的报修给我们评价，谢谢使用！',
                    ),
                );
                $url = $_W['siteroot'] . "app/index.php?i={$item['uniacid']}&c=entry&id={$item['id']}&op=detail&do=repair&m=xcommunity";
                $result = $this->sendtpl($item['openid'], $url, $tpl['grab_wc_tplid'], $content);
            }
            if($tpl['report_wc_tplid']&&$type == 2){
                $content = array(
                    'first'    => array(
                        'value' => '您的意见建议已处理',
                    ),
                    'keyword1' => array(
                        'value' => $member['realname'],
                    ),
                    'keyword2' => array(
                        'value' => $item['category'],
                    ),
                    'keyword3' => array(
                        'value' => $item['content'],
                    ),
                    'keyword4' => array(
                        'value' => $_GPC['resolve'],
                    ),
                    'keyword5' => array(
                        'value' => $data['dealing']
                    ,
                    ),
                    'remark'   => array(
                        'value' => '请到微信我的意见建议给我们评价，谢谢使用！',
                    ),
                );
                $url = $_W['siteroot'] . "app/index.php?i={$item['uniacid']}&c=entry&id={$item['id']}&op=detail&do=report&m=xcommunity";
                $result = $this->sendtpl($item['openid'], $url, $tpl['report_wc_tplid'], $content);
            }
            echo json_encode(array('result' => 1));exit();
        }
    }
    include $this->template('app/detail');
}elseif($op == 'add_notice'){
    //发布通知
    $id = intval($_GPC['id']);
    if($id){
        $item = pdo_get('xcommunity_announcement',array('id' => $id),array('id','title','reason','regionid'));
        $regs = unserialize($item['regionid']);
    }
    if($_SESSION['uid']){
        $regions = $this->regions($_SESSION['uid']);
    }
    if($_W['isajax']){
        $regionid = rtrim(trim($_GPC['regionid']),',');
        $regionid = ltrim($regionid,',');
        $data = array(
            'title' => trim($_GPC['title']),
            'reason' => trim($_GPC['reason']),
            'uid' => $_SESSION['uid'],
            'createtime' => TIMESTAMP,
            'regionid' => serialize(explode(',',$regionid)),
            'uniacid' => $_W['uniacid']
        );
        if($id){
            $result= pdo_update("xcommunity_announcement", $data, array('id' => $id));
        }else{
            $result = pdo_insert('xcommunity_announcement',$data);
            $id = pdo_insertid();
        }

        $tpl = pdo_fetch("SELECT * FROM".tablename('xcommunity_wechat_tplid')."WHERE uniacid=:uniacid",array(':uniacid' => $_W['uniacid']));

        if($tpl['other_tplid']){
            $members = pdo_fetchall("SELECT openid FROM" . tablename('xcommunity_member') . "WHERE uniacid=:uniacid AND regionid in({$regionid})", array(':uniacid' => $_W['uniacid']));
            foreach ($members as $key => $value) {
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&op=detail&do=announcement&m=xfeng_community";
                $openid = trim($value['openid']);
                $reason = htmlspecialchars_decode($item['reason']);
                $content = str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $reason);
                $content = strip_tags($content, '<a>');
                    $data = array(
                        'first' => array(
                            'value' => '',
                        ),
                        'keyword1' => array(
                            'value' => $item['title'],
                        ),
                        'keyword2' => array(
                            'value' => date('Y-m-d H:i', TIMESTAMP),
                        ),
                        'keyword3' => array(
                            'value' => $content,
                        ),
                        'remark' => array(
                            'value' => '',
                        )
                    );
                    $resp = $this->sendtpl($openid, $url, $tpl['other_tplid'], $data);
            }
        }
        if($result){
            echo json_encode(array('status' => 1));exit();
        }
    }
    include $this->template('app/add_notice');
}elseif($op =='cost_order'){
    //缴费订单
    $id = intval($_GPC['id']);
    if(empty($id)){
        message('缺少参数',$this->createMobileUrl('xqsys',array('op'=>'index')),'error');exit();
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize  = 20;
    $list = pdo_fetchall("SELECT o.* ,p.username as username ,p.mobile as mobile,p.homenumber as homenumber FROM".tablename('xcommunity_order')."as o left join (".tablename('xcommunity_cost_list')."as p left join ".tablename('xcommunity_cost')."as r on p.cid = r.id) on o.pid = p.id WHERE o.uniacid=:uniacid AND r.id = :id LIMIT ".($pindex - 1) * $psize.','.$psize,array(':id' => $id,':uniacid' => $_W['uniacid']));
    $total =pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_order')."as o left join (".tablename('xcommunity_cost_list')."as p left join ".tablename('xcommunity_cost')."as r on p.cid = r.id) on o.pid = p.id  WHERE o.uniacid=:uniacid AND r.id = :id ",array(':id' => $id,':uniacid' => $_W['uniacid']));
    $pager  = pagination($total, $pindex, $psize);
    include $this->template('app/cost_order');
}elseif ($op =='shop_detail'){
    //超市订单详情
    $id = intval($_GPC['id']);
    if(empty($id)){
        message('缺少参数',$this->createMobileUrl('xqsys',array('op'=>'index')),'error');exit();
    }
    $item = pdo_fetch("SELECT o.*,m.realname as realname,m.mobile as mobile,m.address as address FROM " . tablename('xcommunity_order') . "as o left join" . tablename('xcommunity_member') . "as m on o.from_user = m.openid WHERE o.id = :id", array(':id' => $id));
    $region = $this->region($item['regionid']);
    $address = pdo_get('xcommunity_member_address', array('openid' => $item['from_user'], 'regionid' => $item['regionid']), array('realname', 'telephone', 'address'));
    //获取商品信息
    $goods = pdo_fetchall("SELECT g.title,g.marketprice,g.id,o.total as gtotal FROM " . tablename('xcommunity_order_goods') .
        " o left join " . tablename('xcommunity_goods') . " g on o.goodsid=g.id " . " WHERE o.orderid='{$id}'");
    $good ='';
    $total ='';
    foreach($goods as $k => $v){
        $goodcode = $val['goodcode'] ? '编码'.$val['goodcode'].',' : '';
        $good .= $v['title'].','.$goodcode.'数量'. $v['gtotal'].',价格'.$v['marketprice'].'。'.'<br/>';
        $total = $total + $v['marketprice']*$v['gtotal'];
    }
    if (empty($item)) {
        message("抱歉，订单不存在!", referer(), "error");
    }
//    if (checksubmit('confirmsend')) {
//        if (!empty($item['transid'])) {
//            $this->changeWechatSend($id, 1);
//        }
//
//        $expresscom = $_GPC['expresscom'];
//        $expresssn = $_GPC['expresssn'];
//        if(empty($expresscom)){
//            message('请输入发货人',referer,'error');exit();
//        }
//        //发货短信提醒
//        $tysms = $this->set('','tysms');//统一短信接口
//        if(($tysms['status']&&$tysms['shop'])){
//            $sname = $tysms['account'] ;
//            $spwd = $tysms['pwd'];
//            $sign = $tysms['sign'];
//            $smsg ="您的快递是".$expresscom.",快递单号".$expresssn."。有任何问题请随时与我们联系，谢谢。";
//            $scorpid ='';
//            $sprdid ='1012888';
//            $sms = new sms($sname,$spwd,$scorpid,$sprdid);
//            $smsg = $smsg.'【'.$sign.'】';
//            $content = $sms->sendSms($item['mobile'],$smsg);
//            if(!empty($content['State'])){
//                message($content['MsgState'],referer(),'error');exit();
//            }
//        }
//
//        //微信模板通知提醒
//        $tpl = $this->set('','tytpl');
//        if ($tpl['send_good']) {
//            $openid = $item['from_user'];
//            $url = '';
//            $template_id = $tpl['send_good_id'];
//            $createtime = date('Y-m-d H:i:s', $_W['timestamp']);
//            $content = array(
//                'first' => array(
//                    'value' => '发货啦，小主人，我是您的商品呀，老板已经安排发货了，我和您即将团聚了，等我哟！',
//                ),
//                'keyword1' => array(
//                    'value' => $item['goodsprice'] . '元',
//                ),
//                'keyword2' => array(
//                    'value' => $title,
//                ),
//                'keyword3' => array(
//                    'value' => $item['realname'] . ',' . $item['address'] . ',' . $item['mobile'],
//                ),
//                'keyword4' => array(
//                    'value' => $item['ordersn'],
//                ),
//                'keyword5' => array(
//                    'value' => $expresscom . ' ' . $expresssn,
//                ),
//                'remark' => array(
//                    'value' => '有任何问题请随时与我们联系，谢谢。',
//                ),
//            );
//            $this->sendtpl($openid, $url, $template_id, $content);
//        }
//        pdo_update(
//            'xcommunity_order',
//            array(
//                'status' => 2,
//                'remark' => $_GPC['remark'],
//            ),
//            array('id' => $id)
//        );
//        message('发货操作成功！', referer(), 'success');
//    }
//    if (checksubmit('cancelsend')) {
//        $item = pdo_fetch("SELECT transid FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $id));
//        if (!empty($item['transid'])) {
//            $this->changeWechatSend($id, 0, $_GPC['cancelreson']);
//        }
//        pdo_update(
//            'xcommunity_order',
//            array(
//                'status' => 1,
//                'remark' => $_GPC['remark'],
//            ),
//            array('id' => $id)
//        );
//        message('取消发货操作成功！', referer(), 'success');
//    }
//    if (checksubmit('finish')) {
//        pdo_update('xcommunity_order', array('status' => 4, 'remark' => $_GPC['remark']), array('id' => $id));
//        message('订单操作成功！', referer(), 'success');
//    }
//    if (checksubmit('cancel')) {
//        pdo_update('xcommunity_order', array('status' => 1, 'remark' => $_GPC['remark']), array('id' => $id));
//        message('取消完成订单操作成功！', referer(), 'success');
//    }
//    if (checksubmit('cancelpay')) {
//        pdo_update('xcommunity_order', array('status' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
//        //设置库存
//        $this->setOrderStock($id, false);
//        //减少积分
//        $this->setOrderCredit($id, false);
//        message('取消订单付款操作成功！', referer(), 'success');
//    }
//    if (checksubmit('confrimpay')) {
//        pdo_update('xcommunity_order', array('status' => 1, 'paytype' => 4, 'remark' => $_GPC['remark']), array('id' => $id));
//        //设置库存
//        $this->setOrderStock($id);
//        //增加积分
//        $this->setOrderCredit($id);
//        message('确认订单付款操作成功！', referer(), 'success');
//    }
//    if (checksubmit('close')) {
//        $item = pdo_fetch("SELECT transid FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $id));
//        if (!empty($item['transid'])) {
//            $this->changeWechatSend($id, 0, $_GPC['reson']);
//        }
//        pdo_update('xcommunity_order', array('status' => -1, 'remark' => $_GPC['remark']), array('id' => $id));
//        message('订单关闭操作成功！', referer(), 'success');
//    }
//    if (checksubmit('open')) {
//        $item = pdo_fetch("SELECT paytype FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $id));
//        if (!empty($item['paytype']) && $item['paytype'] != 3) {
//            $status = 1;
//        }
//        pdo_update('xcommunity_order', array('status' => $status, 'remark' => $_GPC['remark']), array('id' => $id));
//        message('开启订单操作成功！', referer(), 'success');
//    }
    include $this->template('app/shop_detail');
}elseif($op =='homemaking_detail'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        message('缺少参数',referer(),'error');exit();
    }
    $item = pdo_get('xcommunity_homemaking',array('id'=> $id),array('content','realname','mobile','address','status','servicetime'));
    if(empty($item)){
        message('信息不存在或已被删除',referer(),'error');exit();
    }
    if($_W['isajax']){
        $status = intval($_GPC['status']);
        $id = intval($_GPC['id']);
        if(pdo_update('xcommunity_homemaking',array('status'=>$status),array('id' => $id))){
            echo json_encode(array('status' => 1));exit();
        }
    }
    include $this->template('app/homemaking_detail');
}elseif($op =='houselease_detail'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        message('缺少参数',referer(),'error');exit();
    }
    $item = pdo_get('xcommunity_houselease',array('id'=> $id),array('title','status','realname','mobile','status','model_area','floor_layer','floor_number','price','model_room','model_hall','model_toilet','fitment','house','price_way'));
    if(empty($item)){
        message('信息不存在或已被删除',referer(),'error');exit();
    }
    include $this->template('app/houselease_detail');
}elseif($op =='fled_detail'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        message('缺少参数',referer(),'error');exit();
    }
    $item = pdo_get('xcommunity_fled',array('id'=> $id),array('title','realname','mobile','zprice','description','createtime'));
    if(empty($item)){
        message('信息不存在或已被删除',referer(),'error');exit();
    }
    include $this->template('app/fled_detail');
}elseif($op =='member_detail'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        message('缺少参数',referer(),'error');exit();
    }
    $item = pdo_get('xcommunity_member',array('id'=> $id),array('realname','mobile','address','createtime','status','open_status'));
    if(empty($item)){
        message('信息不存在或已被删除',referer(),'error');exit();
    }
    if($_W['isajax']){
        $status = intval($_GPC['status']);
        $open_status = intval($_GPC['open_status']);
        $id = intval($_GPC['id']);
        if(pdo_update('xcommunity_member',array('status'=>$status,'open_status' => $open_status),array('id' => $id))){
            echo json_encode(array('status' => 1));exit();
        }
    }
    include $this->template('app/member_detail');
}elseif($op == 'add_guard'){
    //发布门禁
    $id = intval($_GPC['id']);
    if($id){
        $item = pdo_get('xcommunity_building_device',array('id' => $id),array('id','title','device_code','regionid','type','unit','openurl','status'));
    }
    if($_SESSION['uid']){
        $regions = $this->regions($_SESSION['uid']);
    }
    if($_W['isajax']){
        $data = array(
            'uniacid' => $_W['uniacid'],
            'title' => $_GPC['title'],
            'api_key' => $_GPC['api_key'],
            'device_code' => $_GPC['device_code'],
            'lock_code' => $_GPC['lock_code'],
            'type' => intval($_GPC['type']),
            'status' => intval($_GPC['status']),
            'openurl' => $_GPC['openurl'],
            'regionid' => intval($_GPC['regionid'])
        );
        if($data['type'] == 1){
            $data['unit'] = $_GPC['unit'];
        }
        if($id){
            $result = pdo_update('xcommunity_building_device',$data,array('id' => $id));
        }else{
            $result = pdo_insert('xcommunity_building_device',$data);
        }
        if($result){
            echo json_encode(array('status' => 1));exit();
        }
    }
    include $this->template('app/add_guard');
}elseif($op =='shop'){
    if($_W['isajax']){
        $type = intval($_GPC['type']);
        $orderid = intval($_GPC['orderid']);
        if($type == 1&&$orderid){
            $data = array(
                'status' => 2,
                'remark' => $_GPC['remark'],
            );
            $result = pdo_update('xcommunity_order',$data,array('id' => $orderid));
            if($result){
                echo json_encode(array('status' => 1));exit();
            }

        }
        if($type == 2&&$orderid){
            $data = array(
                'status' => 1,
                'remark' => $_GPC['remark'],
            );
            $result = pdo_update('xcommunity_order',$data,array('id' => $orderid));
            if($result){
                echo json_encode(array('status' => 1));exit();
            }

        }
        if($type == 3&&$orderid){
            $data = array(
                'status' => 4,
                'remark' => $_GPC['remark'],
            );
            $result = pdo_update('xcommunity_order',$data,array('id' => $orderid));
            if($result){
                echo json_encode(array('status' => 1));exit();
            }

        }
    }
}elseif($op =='business_detail'){
    $orderid = intval($_GPC['id']);
    if(empty($orderid)){
        message('缺少参数',referer(),'error');exit();
    }
    $list = pdo_getall('xcommunity_order',array('orderid' => $orderid),array('couponsn','enable','orderid','id'));
    include $this->template('app/business_detail');
}elseif ($op =='business'){
    $orderid= intval($_GPC['orderid']);
    $id = intval($_GPC['id']);
    if (empty($id)){
        message('缺少参数',referer(),'error');exit();
    }
    $item = pdo_get('xcommunity_order',array('id'=>$orderid),array('ordersn','gid','from_user','createtime','from_user'));
    if($item['gid']){
        $good = pdo_get('xcommunity_goods',array('id'=>$item['gid']),array('title'));
    }
    $member = $this->member($item['from_user']);
    $coupon = pdo_get('xcommunity_order',array('id' => $id),array('couponsn','enable','id'));
    include $this->template('app/business');
}elseif($op =='use'){
    if($_W['isajax']){
        $couponid = intval($_GPC['couponid']);
        if($couponid){
            $result = pdo_update('xcommunity_order', array('enable' => 2, 'usetime' => TIMESTAMP), array('id' => $couponid));
            if($result){
                echo json_encode(array('status' => 1));exit();
            }
        }
    }
}