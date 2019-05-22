<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/10/17 下午8:33
 */
defined('IN_IA') or exit('Access Denied');
if (!(function_exists('getIsSecureConnection'))) {
    function getIsSecureConnection()
    {
        if (isset($_SERVER['HTTPS']) && (('1' == $_SERVER['HTTPS']) || ('on' == strtolower($_SERVER['HTTPS'])))) {
            return true;
        }


        if (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }


        return false;
    }
}
if (function_exists('getIsSecureConnection')) {
    $secure = getIsSecureConnection();
    $http = (($secure ? 'https' : 'http'));
    $_W['siteroot'] = ((strexists($_W['siteroot'], 'https://') ? $_W['siteroot'] : str_replace('http', $http, $_W['siteroot'])));
}
require_once IA_ROOT . '/addons/xfeng_community/defines.php';
require COMMUNITY_ADDON_ROOT . 'class/autoload.php';
require_once IA_ROOT . '/addons/xfeng_community/model.php';
define('EARTH_RADIUS', 6378.137);//地球半径
define('PI', 3.1415926);
define('M_PI', 3.1415926);

class xfeng_communityModuleSite extends WeModuleSite
{
    function __construct()
    {
        global $_W, $_GPC;
        if ($_GET['do'] == 'ext') {
            @header("Location: " . wurl('site/entry/manage', array('m' => 'picvote')));
            exit();
        }
    }

    //后台菜单
    public function NavMenu()
    {
        global $_W;
        $user = util::xquser($_W['uid']);
        $condition = ' pcate = 0 and status= 1';
        $con = '';
        if ($user && $user['type'] != 6) {//判断是否设置用了小区用户组
            if (empty($user['menus'])) {
                itoast('没有操作权限,请联系管理员。', referer(), 'error');
                exit();
            }
            $con = " AND id in({$user['menus']})";
        }
        $menus = pdo_fetchall("SELECT title,url,id,icon FROM" . tablename('xcommunity_menu') . "WHERE $condition $con order by displayorder asc");
        $navmenus = array();
        if ($menus) {
            foreach ($menus as $key => $val) {
//                {if ($_W['isfounder'])
                $params[':pcate'] = $val['id'];
                $childmenu = pdo_fetchall("SELECT title,url,id,pcate FROM" . tablename('xcommunity_menu') . "WHERE pcate = :pcate and cate =0 and status =1 $con order by displayorder asc", $params);
                $menu = array();
                foreach ($childmenu as $k => $v) {
                    if ($_W['role'] == 'founder') {
                        $par[':cate'] = $v['id'];
                        $m = pdo_fetchall("SELECT title,url,id,cate,pcate FROM" . tablename('xcommunity_menu') . "WHERE cate = :cate $con order by displayorder asc", $par);
                        $menu[] = array(
                            'title' => $v['title'],
                            'url' => $v['url'],
                            'm' => $m,
                            'pcate' => $v['pcate'],
                            'id' => $v['id']
                        );
                    } else {
                        if ($v['id'] != 78 && $v['id'] != 79 && $v['id'] != 80 && $v['id'] != 82) {
                            $par[':cate'] = $v['id'];
                            $m = pdo_fetchall("SELECT title,url,id,cate,pcate FROM" . tablename('xcommunity_menu') . "WHERE cate = :cate $con order by displayorder asc", $par);
                            $menu[] = array(
                                'title' => $v['title'],
                                'url' => $v['url'],
                                'm' => $m,
                                'pcate' => $v['pcate'],
                                'id' => $v['id']
                            );
                        }
                    }
                }
                $navmenus[] = array(
                    'title' => $val['title'],
                    'items' => $menu,
                    'url' => $val['url'],
                    'id' => $val['id'],
                    'icon' => $val['icon']
                );

            }
        }
        return $navmenus;

    }

    //获取模板名称
    public function xqtpl($tpl)
    {
        global $_W;
        $community = 'community' . $_W['uniacid'];
        $style = $_W['setting'][$community]['styleid'];
        $styleid = $style ? $style : 'default2';
        $tpl = $styleid . '/' . $tpl;
        return $tpl;
    }

    //处理支付回调
    public function payResult($params)
    {
        global $_W;
        $ordersn = $params['tid'];
        $data = array('status' => $params['result'] == 'success' ? 1 : 0);
        $paytype = array('credit' => '1', 'wechat' => '2', 'sub' => '2', 'alipay' => '4', 'swiftpass' => '2', 'hsyunfu' => '2', 'chinaums' => '2', 'delivery' => '3');
        $data['paytype'] = $paytype[$params['type']];
        if ($params['type'] == 'delivery') {
            $result = $this->checkpay($ordersn, $data);
            if ($result['code'] == 1) {
                message('支付成功！', $this->createMobileUrl('home'), 'success');
            }
        }
        if ($params['type'] == 'wechat' || $params['type'] == 'swiftpass' || $params['type'] == 'hsyunfu') {
            $data['transid'] = $params['tag']['transaction_id'];
        }
        if ($params['type'] == 'credit' || $params['type'] == 'delivery') {
            if (empty($params['tid'])) {
                return false;
            }
        }
        if ($params['result'] == 'success' && $params['from'] == 'notify') {
            //判断生成重复支付信息
            if ($params['type'] == 'alipay') {
                if (empty($params['tid'])) {
                    return false;
                }
            }
            if ($params['wechat'] || $params['type'] == 'swiftpass' || $params['type'] == 'hsyunfu' || $params['type'] == 'chinaums') {
                if (empty($params['tag']['transaction_id'])) {
                    return false;
                }
            }
            $result = $this->checkpay($ordersn, $data);
        }
        if (empty($params['result']) || $params['result'] != 'success') {
            load()->model('account');
            $setting = uni_setting($_W['uniacid'], array('payment'));
            if ($params['type'] == 'wechat' || $params['type'] == 'swiftpass' || $params['type'] == 'hsyunfu' || $params['type'] == 'chinaums') {
                if (empty($params['tag']['transaction_id'])) {
                    exit();
                }
                $res3 = $this->checkWechatTran($setting, $params['tag']['transaction_id']);
                $res3['fee'] = round($res3['fee'], 2);
                $rpay = round($params['fee'], 2);
                if ($res3['code'] == 1 && $res3['fee'] == $rpay) {
                    $result = $this->checkpay($ordersn, $data);
                    if ($result['code'] == 1) {
                        message('支付成功！', $this->createMobileUrl('home'), 'success');
                    }
                }
            }
        }
        if ($params['from'] == 'return') {
            if ($params['result'] == 'success') {
//                $order = pdo_get('xcommunity_order', array('ordersn' => $ordersn));
//                if (empty($order['status'])) {
//                    $result = $this->checkpay($ordersn, $data);
//                }
                message('支付成功！', $this->createMobileUrl('home'), 'success');
            } else {
                message('支付失败！', $this->createMobileUrl('home'), 'error');
            }
        }
    }

    public function checkpay($ordersn, $data)
    {
        global $_W;
        $order = pdo_get('xcommunity_order', array('ordersn' => $ordersn));
        if (empty($order['status'])) {
            $member = pdo_get('mc_members', array('uid' => $order['uid']), array('realname', 'mobile','nickname'));
            /**
             * 小区超市
             */
            if ($order['type'] == 'shopping') {
                load()->model('mc');
                //超市支付处理
                $address = pdo_get('xcommunity_member_address', array('id' => $order['addressid']));
                //修改商品销售数量
//                model_shop::setOrderStock($order['id']);
                $gsql = "select distinct t2.id,t1.uid,t1.title,t2.total,t2.price,t1.id as goodid,t1.shopid,t1.credit,t1.shopid from" . tablename('xcommunity_goods') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id = t2.goodsid where t2.orderid =:orderid";
                $shopgoods = pdo_fetchall($gsql, array(':orderid' => $order['id']));
                $printcontent = '';
                $totalprice = '';
                $shopgoods_ids = _array_column($shopgoods, 'shopid');
                // 获取所有的超市
                $shops = pdo_getall('xcommunity_shop', array('uniacid' => $_W['uniacid'], 'id' => $shopgoods_ids));
                $shoplist = _array_column($shops, NULL, 'id');
                // 获取小区接收员及小区
                $shop_ids = _array_column($shops, 'id');
                $snotice = pdo_getall('xcommunity_shop_wechat', array('shopid' => $shop_ids, 'enable' => 1, 'shoptype' => 1));
                $snotice_list = _array_column($snotice, NULL, 'id');
                $snotice_ids = _array_column($snotice, 'id');
                $regionids = pdo_getall('xcommunity_shop_wechatregion', array('wechatid' => $snotice_ids, 'regionid' => $order['regionid']), array());
                $regionids_ids = _array_column($regionids, NULL, 'wechatid');
                // 总的积分
                $credits = 0;
                $set =set('p109');//是否开启一个小区多超市模式，默认关闭是1个小区1个超市
                $tplid =set('t14');//模板id
                foreach ($shopgoods as $k => $v) {
                    $printcontent .= $v['title'] . ',数量:' . $v['total'] . ',单价:' . $v['price'];
                    // 计算每种商品的价格
                    $price = $v['price'] * $v['total'];
                    $totalprice += $price;
                    $users = xiaoqu_dp($v['shopid'], 4);
                    // 判断是否开启提成
                    if (!set('p46','',$order['uniacid'])) {
                        $commission = explode(',', $shoplist[$v['shopid']]['commission']);
                        if ($commission) {
                            /**
                             * 设置提成
                             */
                            $a = 1 - $commission[0] - $commission[1] > 0 ? 1 - $commission[0] - $commission[1] : 0;
                            $balance = $price * $a;
                            $xqbalance = $price * $commission[1];
                            pdo_update('xcommunity_region', array('commission +=' => $xqbalance), array('id' => $order['regionid']));
                        } else {
                            $balance = $price;
                        }
                        //修改商家余额
                        if (pdo_update('xcommunity_users', array('balance +=' => $balance), array('uid' => $users['uid']))) {
                            // 商家余额记录
                            $creditdata1 = array(
                                'uid' => $users['uid'],
                                'uniacid' => $_W['uniacid'],
                                'realname' => $users['username'],
                                'mobile' => $users['username'],
                                'content' => "订单号：" . $order['ordersn'] . "，" . $v['title'],
                                'credit' => $balance,
                                'creditstatus' => 1,
                                'createtime' => TIMESTAMP,
                                'type' => 3,
                                'typeid' => $users['uid'],
                                'category' => 4,
                                'usename' => '系统'
                            );
                            pdo_insert('xcommunity_credit', $creditdata1);
                        }
                        //小区提成
                        $d1 = array(
                            'uniacid' => $_W['uniacid'],
                            'uid' => $order['uid'],
                            'realname' => $member['realname'] . '-' . $member['mobile'],
                            'type' => 1,
                            'regionid' => $order['regionid'],
                            'createtime' => TIMESTAMP,
                            'orderid' => $order['id'],
                            'good' => $v['title'],
                            'cid' => 1,
                            'price' => $v['price'] * $v['total'] * $commission[1],
                            'category' => 2,
                            'creditstatus' => 1
                        );

                        pdo_insert('xcommunity_commission_log', $d1);
                        //平台提成
                        $d2 = array(
                            'uniacid' => $_W['uniacid'],
                            'uid' => $order['uid'],
                            'realname' => $member['realname'] . '-' . $member['mobile'],
                            'type' => 1,
                            'regionid' => $order['regionid'],
                            'createtime' => TIMESTAMP,
                            'orderid' => $order['id'],
                            'good' => $v['title'],
                            'cid' => 2,
                            'price' => $v['price'] * $v['total'] * $commission[0],
                            'category' => 2,
                            'creditstatus' => 1
                        );

                        pdo_insert('xcommunity_commission_log', $d2);
                    }
                    //一个小区多个超市打印
                    if ($set) {
                        $createtime = date('Y-m-d H:i:s', $_W['timestamp']);
                        $yl = "^N1^F1\n";
                        $yl .= "^B2 超市新订单通知\n";
                        $yl .= "内容：" . $v['title'] . ',数量:' . $v['total'] . ',单价:' . $v['price'] . "\n";
                        $yl .= "总价：" . $price . "\n";
                        $yl .= "地址：" . $address['city'] . $address['address'] . "\n";
                        $yl .= "业主：" . $address['realname'] . "\n";
                        $yl .= "电话：" . $address['mobile'] . "\n";
                        $yl .= "时间：" . $createtime;
                        $fy = array(
                            'msgDetail' =>
                                '
                        超市新订单通知

                    内容：' . $v['title'] . ',数量:' . $v['total'] . ',单价:' . $v['price'] . '
                    总价：' . $price . '
                    -------------------------

                    地址：' . $address['city'] . $address['address'] . '
                    业主：' . $address['realname'] . '
                    电话：' . $address['mobile'] . '
                    时间：' . $createtime . '
                    ',
                        );

                        if (set('d6','',$order['uniacid'])) {
                            if (set('d2','',$order['uniacid'])) {
                                //统一打印
                                $type = set('d1','',$order['uniacid']) == 1 ? 'yl' : 'fy';
                                $content = set('d1','',$order['uniacid']) == 1 ? $yl : $fy;
                                xq_print::xqprint($type, 1, $content);
                            }
                            $print = pdo_get('xcommunity_print', array('uid' => $v['uid']), array());
                            if ($print['type'] == 1) {
                                //独立打印
                                xq_print::yl($print['deviceNo'], $print['api_key'], $yl);
                            }

                        }

                    }
                    //一个小区多个超市通知
                    if ($set) {
                        $t13 = set('t13','',$order['uniacid']);
                        if (@$t13) {
                            $content = array(
                                'first' => array(
                                    'value' => '超市新订单通知',
                                ),
                                'keyword1' => array(
                                    'value' => $v['title'] . ',数量:' . $v['total'] . ',单价:' . $v['price'],
                                ),
                                'keyword2' => array(
                                    'value' => $price . '元',
                                ),
                                'keyword3' => array(
                                    'value' => $address['realname'] . ' ' . $address['city'] . $address['address'],
                                ),
                                'keyword4' => array(
                                    'value' => $address['mobile'],
                                ),
                                'keyword5' => array(
                                    'value' => $order['ordersn'],
                                ),
                                'remark' => array(
                                    'value' => $order['remark'] . '请点击查看详情，进行订单发货',
                                ),
                            );
                            $url = $_SERVER['SERVER_NAME'] . "/app/index.php?i={$_W['uniacid']}&c=entry&op=grab&id={$order['id']}&do=shopping&m=" . $this->module['name'];
                            util::sendtpl('', $content, " and t1.shopping=1 and t1.uid={$v['uid']}", $url, $tplid, 1);
                            foreach ($snotice_list as $key => $val) {
                                if ($regionids_ids[$val['id']]['regionid'] && $val['shopid'] == $v['shopid']) {
                                    if ($val['type'] == 1 || $val['type'] == 3) {
                                        $status = util::sendTplNotice($val['openid'], $tplid, $content, $url, $topcolor = '#FF683F');
                                    }
                                }
                            }
                        }
                        $sprint = pdo_get('xcommunity_shop_print', array('shopid' => $v['shopid'], 'type' => 1, 'shoptype' => 1), array());
                        if ($sprint) {
                            xq_print::yl($sprint['deviceNo'], $sprint['api_key'], $yl);
                        }
                    }
                    // 判断商品是否有积分
                    $credit = $v['credit'];
                    $credits += $v['total'] * $credit;
                    // 小区用户增加积分
                    if (set('p12','',$order['uniacid']) && $credit) {
                        $creditdata1 = array(
                            'uid' => $order['uid'],
                            'uniacid' => $_W['uniacid'],
                            'realname' => $member['realname'],
                            'mobile' => $member['mobile'],
                            'content' => "订单号：" . $order['ordersn'] . "," . $v['title'] . "赠送积分",
                            'credit' => $v['total'] * $credit,
                            'creditstatus' => 1,
                            'createtime' => TIMESTAMP,
                            'type' => 3,
                            'typeid' => $v['shopid'],
                            'category' => 1,
                            'usename' => '系统'
                        );
                        pdo_insert('xcommunity_credit', $creditdata1);
                        unset($creditdata1);
                        // 减少商家积分
                        if (pdo_update('xcommunity_users', array('credit -=' => $v['total'] * $credit), array('uid' => $users['uid']))) {
                            $creditdata1 = array(
                                'uid' => $users['uid'],
                                'uniacid' => $_W['uniacid'],
                                'realname' => $users['username'],
                                'mobile' => $users['username'],
                                'content' => "订单号：" . $order['ordersn'] . "," . $v['title'] . "赠送积分",
                                'credit' => $v['total'] * $credit,
                                'creditstatus' => 2,
                                'createtime' => TIMESTAMP,
                                'type' => 3,
                                'typeid' => $v['shopid'],
                                'category' => 3,
                                'usename' => '系统'
                            );
                            pdo_insert('xcommunity_credit', $creditdata1);
                            unset($creditdata1);
                        }
                    }
                    // 修改用户余额记录
                    if ($data['paytype'] == 1) {
                        $creditdata2 = array(
                            'uid' => $order['uid'],
                            'uniacid' => $_W['uniacid'],
                            'realname' => $member['realname'],
                            'mobile' => $member['mobile'],
                            'content' => "订单号：" . $order['ordersn'] . "," . $v['title'],
                            'credit' => $price,
                            'creditstatus' => 2,
                            'createtime' => TIMESTAMP,
                            'type' => 3,
                            'typeid' => $v['shopid'],
                            'category' => 2,
                            'usename' => '系统'
                        );
                        pdo_insert('xcommunity_credit', $creditdata2);
                    }
                }
                if($tplid){
                    $tcontent = array(
                        'first' => array(
                            'value' => '订单购买成功通知',
                        ),
                        'keyword1' => array(
                            'value' => $printcontent,
                        ),
                        'keyword2' => array(
                            'value' => $totalprice . '元',
                        ),
                        'keyword3' => array(
                            'value' => $address['realname'] . ' ' . $address['city'] . $address['address'],
                        ),
                        'keyword4' => array(
                            'value' => $address['mobile'],
                        ),
                        'keyword5' => array(
                            'value' => $order['ordersn'],
                        ),
                        'remark' => array(
                            'value' => '请耐心等待发货',
                        ),
                    );
                    $status=util::sendTplNotice($order['openid'], $tplid, $tcontent, $url='', $topcolor = '#FF683F');
                }
                //抵扣积分
                if ($order['credit']) {
                    //记录用户积分的操作日志
                    mc_credit_update($order['uid'], 'credit1', -$order['credit'], array($order['uid'], '购买超市商品抵扣积分'));
                    $creditdata1 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . ",购买商品：" . $printcontent . ",减少积分",
                        'credit' => $order['credit'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 3,
                        'typeid' => $shopgoods[0]['shopid'],
                        'category' => 1,
                        'usename' => '系统'
                    );
                    pdo_insert('xcommunity_credit', $creditdata1);
                }
                //1个小区1个超市模式
                if (empty($set)) {
                    //开启微信模板消息推送,针对超市中的接收员
                    $t13 = set('t13','',$order['uniacid']);
                    if (@$t13) {
                        $content = array(
                            'first' => array(
                                'value' => '超市新订单通知',
                            ),
                            'keyword1' => array(
                                'value' => $printcontent,
                            ),
                            'keyword2' => array(
                                'value' => $totalprice . '元',
                            ),
                            'keyword3' => array(
                                'value' => $address['realname'] . ' ' . $address['city'] . $address['address'],
                            ),
                            'keyword4' => array(
                                'value' => $address['mobile'],
                            ),
                            'keyword5' => array(
                                'value' => $order['ordersn'],
                            ),
                            'remark' => array(
                                'value' => $order['remark'] . '请点击查看详情，进行订单发货',
                            ),
                        );

                        $url =$_SERVER['SERVER_NAME'] . "/app/index.php?i={$_W['uniacid']}&c=entry&op=grab&id={$order['id']}&do=shopping&m=" . $this->module['name'];
                        util::sendtpl('', $content, " and t1.shopping=1 and t1.uid={$shopgoods[0]['uid']}", $url, $tplid, 1);
                        $snotice = pdo_getall('xcommunity_shop_wechat', array('shopid' => $shopgoods[0]['shopid'], 'enable' => 1, 'shoptype' => 1));
                        $snotice_ids = _array_column($snotice, 'id');
                        $regionids = pdo_getall('xcommunity_shop_wechatregion', array('wechatid' => $snotice_ids, 'regionid' => $order['regionid']), array(),'wechatid');
                        foreach ($snotice as $key => $val) {
                            if ($regionids[$val['id']]['regionid']) {
                                if ($val['type'] == 1 || $val['type'] == 3) {
                                    $status=util::sendTplNotice($val['openid'], $tplid, $content, $url, $topcolor = '#FF683F');
                                }
                            }
                        }

                    }
                    $createtime = date('Y-m-d H:i:s', $_W['timestamp']);
                    $yl = "^N1^F1\n";
                    $yl .= "^B2 超市新订单通知\n";
                    $yl .= "内容：" . $printcontent . "\n";
                    $yl .= "总价：" . $totalprice . "\n";
                    $yl .= "地址：" . $address['city'] . $address['address'] . "\n";
                    $yl .= "业主：" . $address['realname'] . "\n";
                    $yl .= "电话：" . $address['mobile'] . "\n";
                    $yl .= "时间：" . $createtime;
                    $fy = array(
                        'msgDetail' =>
                            '
                            超市新订单通知

                        内容：' . $printcontent . '
                        总价：' . $totalprice . '
                        -------------------------

                        地址：' . $address['city'] . $address['address'] . '
                        业主：' . $address['realname'] . '
                        电话：' . $address['mobile'] . '
                        时间：' . $createtime . '
                        ',
                    );
//                    if (set('d2') && set('d3')) {
//                        $type = set('d1') == 1 ? 'yl' : 'fy';
//                        $content = set('d1') == 1 ? $yl : $fy;
//                        xq_print::xqprint($type, 1, $content);
//
//                    }
                    if (set('d6','',$order['uniacid'])) {
                        if (set('d2','',$order['uniacid'])) {
                            //统一打印
                            $type = set('d1','',$order['uniacid']) == 1 ? 'yl' : 'fy';
                            $content = set('d1','',$order['uniacid']) == 1 ? $yl : $fy;
                            xq_print::xqprint($type, 1, $content);
                        }
                        $print = pdo_get('xcommunity_print', array('uid' => $shopgoods[0]['uid']), array());
                        if ($print['type'] == 1) {
                            //独立打印
                            xq_print::yl($print['deviceNo'], $print['api_key'], $yl);
                        }

                    }
                    $sprint = pdo_get('xcommunity_shop_print', array('shopid' => $shopgoods[0]['shopid'], 'type' => 1, 'shoptype' => 1), array());
                    if ($sprint) {
                        xq_print::yl($sprint['deviceNo'], $sprint['api_key'], $yl);
                    }
                }

                $p12 = set('p12');
                if ($p12 && $credits) {
                    // 用户增加积分
                    mc_credit_update($order['uid'], 'credit1', $credits, array($order['uid'], '小区超市赠送积分'));
                }
                if ($order['littleid']) {
                    pdo_update('xcommunity_counter_little', array('enable' => 2), array('id' => $order['littleid']));
                }
                $data['code'] = rand(1000, 9999);
            }
            /**
             * 周边商家
             */
            if ($order['type'] == 'business') {
                //商家信息
                $dp = pdo_get('xcommunity_dp', array('id' => $order['dpid']), array('id', 'uid', 'commission', 'setting'));
                //线上商家支付处理
                $address = pdo_get('xcommunity_member_address', array('id' => $order['addressid']));
                $gsql = "select t1.*,t2.price as goodprice,t2.goodsid,t2.total as gtotal from" . tablename('xcommunity_goods') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id = t2.goodsid where t2.orderid =:orderid";
                $goods = pdo_fetch($gsql, array(':orderid' => $order['id']));
                pdo_update('xcommunity_goods', array('sold' => $goods['sold'] + $goods['gtotal']), array('id' => $goods['id']));
//                pdo_update('xcommunity_goods', array('sold' => $goods['sold'] + $goods['gtotal'], 'total' => $goods['total'] - $goods['gtotal']), array('id' => $goods['id']));
//积分抵扣，会员减少积分
                if ($order['credit']) {
                    //pdo_update('mc_members', array('credit1 -=' => $order['credit']), array('uid' => $_W['member']['uid']));
                    mc_credit_update($order['uid'], 'credit1', -$order['credit'], array($order['uid'], '交易商户'.$dp['sjname'].'购买商品'.$goods['title'].'抵扣积分'));
                    $creditdata1 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . ",购买商品：" . $goods['title'] . ",减少积分",
                        'credit' => $order['credit'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 2,
                        'typeid' => $order['dpid'],
                        'category' => 1,
                        'usename' => '系统'
                    );
                    pdo_insert('xcommunity_credit', $creditdata1);
                }

                /**
                 * 获取商家账户信息
                 */
                $user = xiaoqu_dp($dp['id'], 5);
                /**
                 * 获取商家配置
                 */
                $setting = unserialize($dp['setting']);
                if ($setting['online']) {
                    /**
                     * 赠送积分数量
                     */
                    $credit = $order['price'] * $setting['online'];
                    /**
                     * 扣除商家积分数量
                     */
                    pdo_update('xcommunity_users', array('credit -=' => $credit), array('uid' => $user['uid']));
                    /**
                     * 扣除商家积分数量记录
                     */
                    $dat = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $user['username'], //商家账户账号
                        'content' => "订单号：" . $order['ordersn'] . "," . $goods['title'],
                        'credit' => $credit,
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 2,
                        'typeid' => $dp['id'],
                        'category' => 3,
                        'usename' => '系统'
                    );
                    pdo_insert('xcommunity_credit', $dat);
                    unset($dat);
                    /**
                     * 增加用户积分数量
                     */
                    mc_credit_update($order['uid'], 'credit1', $credit, array($user['uid'], '交易商户'.$dp['sjname'].'购买商品'.$goods['title'].'赠送积分'));
                    $data1 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . "," . $goods['title'] . "赠送积分",
                        'credit' => $credit,
                        'creditstatus' => 1,
                        'createtime' => TIMESTAMP,
                        'type' => 2,
                        'typeid' => $order['dpid'],
                        'category' => 1,
                        'usename' => '系统'
                    );
                    pdo_insert('xcommunity_credit', $data1);
                    unset($data1);
                }
                //商家一条数据
                $t13 = set('t13');
                if ($t13) {
                    $content = array(
                        'first' => array(
                            'value' => '团购新订单通知',
                        ),
                        'keyword1' => array(
                            'value' => $goods['title'] . ',数量:' . $goods['gtotal'],
                        ),
                        'keyword2' => array(
                            'value' => $order['price'] . '元',
                        ),
                        'keyword3' => array(
                            'value' => $address['realname'] . ' ' . $address['address'],
                        ),
                        'keyword4' => array(
                            'value' => $address['mobile'],
                        ),
                        'keyword5' => array(
                            'value' => $order['ordersn'],
                        ),
                        'remark' => array(
                            'value' => $order['remark'],
                        ),
                    );
                    $t14 = set('t14');
                    $tplid = $t14;
                    $result = util::sendtpl('', $content, " and t1.business=1 and t1.uid={$goods['uid']}", $url = '', $tplid, 1);
                    $wechat = pdo_fetchall("select openid,type,mobile from" . tablename('xcommunity_business_wechat') . " where dpid=:dpid and enable=1", array(':dpid' => $order['dpid']));
                    foreach ($wechat as $k => $v) {
                        if ($v['type'] == 1 || $v['type'] == 3) {
                            util::sendTplNotice($v['openid'], $tplid, $content, $url = '', $topcolor = '#FF683F');
                        }
                        if ($v['type'] == 2 || $v['type'] == 3) {
                            //是否开启统一短信
                            if (set('s2')) {
                                $api = 1;
                                $type = set('s1');
                                if ($type == 1) {
                                    $type = 'wwt';
                                } elseif ($type == 2) {
                                    $type = 'juhe';
                                    $tpl_id = set('s34');
                                } else {
                                    $type = 'aliyun_new';
                                    $tpl_id = set('s27');
                                }
                                if ($type == 'wwt') {
                                    $remark = $order['remark'] ? ',备注:' . $order['remark'] : '';
                                    $smsg = '有新团购订单通知,标题:' . $goods['title'] . ',数量:' . $goods['gtotal'] . ',购买价格:' . $order['price'] . '元,订单号:' . $order['ordersn'] . $remark;
                                } elseif ($type == 'juhe') {
                                    $title = $goods['title'];
                                    $total = $goods['gtotal'];
                                    $price = $order['price'];
                                    $remark = $order['remark'];
                                    $smsg = urlencode("#title#=$title&#total#=$total&#price#=$price&#ordersn#=$ordersn&#remark#=$remark&");
                                } else {
                                    $smsg = json_encode(array('title' => $goods['title'], 'total' => $goods['gtotal'], 'price' => $order['price'], 'ordersn' => $ordersn, 'remark' => $order['remark']));
                                }
                                if ($v['mobile']) {
                                    sms::send($v['mobile'], $smsg, $type, '', $api, $tpl_id);
                                }
                            }

                        }

                    }
                }

                $print = pdo_get('xcommunity_business_print', array('uniacid' => $_W['uniacid'], 'dpid' => $order['dpid']));
                if ($print['type'] == 1) {
                    $gsql = "select distinct t2.id,t1.uid,t1.title,t2.total,t2.price,t1.id from" . tablename('xcommunity_goods') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id = t2.goodsid where t2.orderid =:orderid";
                    $shopgoods = pdo_fetchall($gsql, array(':orderid' => $order['id']));
                    $printcontent = '';
                    $totalprice = '';
                    foreach ($shopgoods as $key => $val) {
                        $printcontent .= $val['title'] . ',数量:' . $val['total'] . ',单价:' . $val['price'];
                        $price = $val['price'] * $val['total'];
                        $totalprice += $price;
                    }
                    $createtime = date('Y-m-d H:i:s', $_W['timestamp']);
                    $yl = "^N1^F1\n";
                    $yl .= "^B2 新订单通知\n";
                    $yl .= "内容：" . $printcontent . "\n";
                    $yl .= "总价：" . $totalprice . "\n";
                    $yl .= "业主：" . $address['realname'] . "\n";
                    $yl .= "电话：" . $address['mobile'] . "\n";
                    $yl .= "时间：" . $createtime;
                    xq_print::yl($print['deviceNo'], $print['api_key'], $yl);
                }
                //关闭独立支付，有提成和余额增加
                if (!set('p45')) {
                    if ($user['uid']) {
                        //判断是否开启设置提成
                        $commission = explode(',', $dp['commission']);
                        if ($commission) {
                            /**
                             * 设置提成
                             */
                            $a = 1 - $commission[0] - $commission[1] > 0 ? 1 - $commission[0] - $commission[1] : 0;
                            $balance = $order['price'] * $a;
                            $xqbalance = $order['price'] * $commission[1];
                            pdo_update('xcommunity_region', array('commission +=' => $xqbalance), array('id' => $order['regionid']));
                            $d1 = array(

                                'uniacid' => $_W['uniacid'],
                                'uid' => $order['uid'],
                                'realname' => "订单号：" . $order['ordersn'] . "," . $goods['title'],
                                'type' => 2,
                                'regionid' => $order['regionid'],
                                'createtime' => TIMESTAMP,
                                'orderid' => $order['id'],
                                'good' => $goods['title'],
                                'cid' => 1,
                                'price' => $xqbalance,
                                'category' => 2,
                                'creditstatus' => 1
                            );
                            pdo_insert('xcommunity_commission_log', $d1);
                            $d2 = array(
                                'uniacid' => $_W['uniacid'],
                                'uid' => $order['uid'],
                                'realname' => "订单号：" . $order['ordersn'] . "," . $goods['title'],
                                'type' => 2,
                                'regionid' => $order['regionid'],
                                'createtime' => TIMESTAMP,
                                'orderid' => $order['id'],
                                'good' => $goods['title'],
                                'cid' => 2,
                                'price' => $order['price'] * $commission[0],
                                'category' => 2,
                                'creditstatus' => 1

                            );
                            pdo_insert('xcommunity_commission_log', $d2);
                        } else {
                            $balance = $order['price'];
                        }

                        pdo_update('xcommunity_users', array('balance +=' => $balance), array('uid' => $user['uid']));
                        // 商家余额增加记录
                        $creditdata = array(
                            'uid' => $order['uid'],
                            'uniacid' => $_W['uniacid'],
                            'realname' => $user['username'],
                            'mobile' => $user['username'],
                            'content' => "订单号：" . $order['ordersn'] . "," . $goods['title'],
                            'credit' => $balance,
                            'creditstatus' => 1,
                            'createtime' => TIMESTAMP,
                            'type' => 2,
                            'typeid' => $user['uid'],
                            'category' => 4,
                            'usename' => '系统'
                        );
                        pdo_insert('xcommunity_credit', $creditdata);

                    }
                }

                for ($i = 0; $i < $goods['gtotal']; $i++) {
                    $dat = array(
                        'orderid' => $order['id'],
                        'couponsn' => date('md') . random(5, 1),
                        'status' => 1,
                        'createtime' => TIMESTAMP,
                    );
                    pdo_insert('xcommunity_coupon_order', $dat);
                }
                //记录余额的日志
                if ($data['paytype'] == 1) {
                    $creditdata2 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . "," . $goods['title'],
                        'credit' => $order['price'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 2,
                        'typeid' => $order['dpid'],
                        'category' => 2,
                        'usename' => '系统'
                    );
                    pdo_insert('xcommunity_credit', $creditdata2);
                }
            }
            /**
             * 商家线下扫码付款
             */
            if ($order['type'] == 'xxbusiness') {
                // 查询商家信息
                $dp = pdo_get('xcommunity_dp', array('id' => $order['dpid']), array('id', 'uid', 'setting', 'commission', 'sjname'));
                //线下商家支付处理
                //积分抵扣，会员减少积分
                if ($order['credit']) {
                    mc_credit_update($order['uid'], 'credit1', -$order['credit'], array($order['uid'], '交易商户'.$dp['sjname'].'优惠买单抵扣'));
                    $creditdata1 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "扫码支付订单号：" . $order['ordersn'] . ",减少积分",
                        'credit' => $order['credit'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 2,
                        'typeid' => $order['dpid'],
                        'category' => 1,
                        'usename' => '系统'
                    );
                    pdo_insert('xcommunity_credit', $creditdata1);
                }

                /**
                 * 获取商家账户信息
                 */
                $user = xiaoqu_dp($dp['id'], 5);
                /**
                 * 获取商家配置
                 */
                $setting = unserialize($dp['setting']);
                if ($setting['offline']) {
                    /**
                     * 赠送积分
                     */
                    $credit = $order['price'] * $setting['offline'];
                    /**
                     * 扣除商家积分
                     */
                    pdo_update('xcommunity_users', array('credit -=' => $credit), array('uid' => $user['uid']));
                    /**
                     * 扣除商家积分记录
                     */
                    $dat = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $user['username'],
                        'content' => "扫码支付订单号：" . $order['ordersn'] . ",减少积分",
                        'credit' => $credit,
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 2,
                        'typeid' => $order['dpid'],
                        'category' => 3,
                        'usename' => '系统'
                    );
                    pdo_insert('xcommunity_credit', $dat);
                    unset($dat);
                    /**
                     * 增加用户积分
                     */
                    mc_credit_update($order['uid'], 'credit1', $credit, array($order['uid'], '交易商户'.$dp['sjname'].'扫码支付赠送积分'));
                    if ($credit) {
                        $data1 = array(
                            'uid' => $order['uid'],
                            'uniacid' => $_W['uniacid'],
                            'realname' => $member['realname'],
                            'mobile' => $member['mobile'],
                            'content' => "扫码支付订单号：" . $order['ordersn'] . ",赠送积分",
                            'credit' => $credit,
                            'creditstatus' => 1,
                            'createtime' => TIMESTAMP,
                            'type' => 2,
                            'typeid' => $order['dpid'],
                            'category' => 1,
                            'usename' => '系统'
                        );
                        pdo_insert('xcommunity_credit', $data1);
                        unset($data1);
                    }
                }
                /**
                 * 开启独立支付
                 */
                if (!set('p45')) {
                    if ($user['uid']) {
                        //判断是否开启设置提成
                        $commission = explode(',', $dp['commission']);
                        if ($commission) {
                            $a = 1 - $commission[0] - $commission[1] > 0 ? 1 - $commission[0] - $commission[1] : 0;
                            $balance = $order['price'] * $a;
                            $xqbalance = $order['price'] * $commission[1];
                            pdo_update('xcommunity_region', array('commission +=' => $xqbalance), array('id' => $order['regionid']));
                            $d1 = array(
                                'uniacid' => $_W['uniacid'],
                                'uid' => $order['uid'],
                                'realname' => "订单号：" . $order['ordersn'] . "," . $dp['sjname'],
                                'type' => 2,
                                'regionid' => $order['regionid'],
                                'createtime' => TIMESTAMP,
                                'orderid' => $order['id'],
                                'good' => $dp['title'],
                                'cid' => 1,
                                'price' => $xqbalance,
                                'category' => 2,
                                'creditstatus' => 1
                            );
                            pdo_insert('xcommunity_commission_log', $d1);
                            $d2 = array(
                                'uniacid' => $_W['uniacid'],
                                'uid' => $order['uid'],
                                'realname' => "订单号：" . $order['ordersn'] . "," . $dp['sjname'],
                                'type' => 2,
                                'regionid' => $order['regionid'],
                                'createtime' => TIMESTAMP,
                                'orderid' => $order['id'],
                                'good' => $dp['sjname'],
                                'cid' => 2,
                                'price' => $order['price'] * $commission[0],
                                'category' => 2,
                                'creditstatus' => 1

                            );
                            pdo_insert('xcommunity_commission_log', $d2);
                        } else {
                            $balance = $order['price'];
                        }

                        pdo_update('xcommunity_users', array('balance +=' => $balance), array('uid' => $user['uid']));

                        $creditdata = array(
                            'uid' => $order['uid'],
                            'uniacid' => $_W['uniacid'],
                            'realname' => $user['username'],
                            'mobile' => $user['username'],
                            'content' => "订单号：" . $order['ordersn'] . "," . $dp['sjname'],
                            'credit' => $balance,
                            'creditstatus' => 1,
                            'createtime' => TIMESTAMP,
                            'type' => 2,
                            'typeid' => $user['uid'],
                            'category' => 4,
                            'usename' => '系统'
                        );
                        pdo_insert('xcommunity_credit', $creditdata);

                    }
                }
                $print = pdo_get('xcommunity_business_print', array('uniacid' => $_W['uniacid'], 'dpid' => $order['dpid']));
                if ($print['type'] == 1) {
                    $createtime = date('Y-m-d H:i:s', $_W['timestamp']);
                    $yl = "^N1^F1\n";
                    $yl .= "^B2 商家小票\n\n";
                    $yl .= "商户名称:" . $dp['sjname'] . "\n";
                    $yl .= "商品名称:线下扫码\n";
                    if($data['transid']){
                        $yl .= "微信单号:" . $data['transid'] . "\n";
                    }
                    $yl .= "平台单号:" . $order['ordersn'] . "\n";
                    $yl .= "订单信息:您本次共消费" . $order['total'] . "元" . "\n";
                    $yl .= "付款信息:实际在线支付" . $order['price'] . "元,积分抵扣".$order['offsetprice'] ."元". "\n";
                    if($data['paytype'] == 1){
                        $yl .= "交易方式:余额". "\n";
                    }elseif($data['paytype'] == 4){
                        $yl .= "交易方式:支付宝". "\n";
                    }else{
                        $yl .= "交易方式:微信支付". "\n";
                    }
                    $yl .= "付款时间:" . $createtime . "\n";
                    $status = xq_print::yl($print['deviceNo'], $print['api_key'], $yl);
                }
                //微信通知
                //商家一条数据
                $t13 = set('t13');
                if ($t13) {
                    $content = array(
                        'first' => array(
                            'value' => '扫码支付订单',
                        ),
                        'keyword1' => array(
                            'value' => $dp['sjname'],
                        ),
                        'keyword2' => array(
                            'value' => $order['price'] . '元',
                        ),
                        'keyword3' => array(
                            'value' => $member['realname'],
                        ),
                        'keyword4' => array(
                            'value' => $member['mobile'],
                        ),
                        'keyword5' => array(
                            'value' => $order['ordersn'],
                        ),
                        'remark' => array(
                            'value' => $order['remark'],
                        ),
                    );
                    $t14 = set('t14');
                    $tplid = $t14;
                    $wechat = pdo_fetchall("select openid,type,mobile from" . tablename('xcommunity_business_wechat') . " where dpid=:dpid and enable=1", array(':dpid' => $order['dpid']));
                    foreach ($wechat as $k => $v) {
                        if ($v['type'] == 1 || $v['type'] == 3) {
                            util::sendTplNotice($v['openid'], $tplid, $content, $url = '', $topcolor = '#FF683F');
                        }
                    }
                }
                if ($data['paytype'] == 1) {
                    $creditdata2 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . ",扫码付款",
                        'credit' => $order['price'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 2,
                        'typeid' => $order['dpid'],
                        'category' => 2,
                        'usename' => '系统'
                    );
                    pdo_insert('xcommunity_credit', $creditdata2);
                }
            }
            /**
             * 小区活动
             */
            if ($order['type'] == 'activity') {
                //小区活动报名预约
                $regiontitle = pdo_getcolumn('xcommunity_region', array('id' => $order['regionid']), 'title');
                pdo_update('xcommunity_res', array('status' => 1), array('orderid' => $order['id']));
                if ($data['paytype'] == 1) {
                    $creditdata2 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . "," . $regiontitle,
                        'credit' => $order['price'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 4,
                        'typeid' => $order['regionid'],
                        'category' => 2,
                        'usename' => $member['realname']
                    );
                    pdo_insert('xcommunity_credit', $creditdata2);
                }
            }
            /**
             * 导入物业费
             */
            if ($order['type'] == 'pfree') {
                //批量导入物业缴费
                $roomsql = "select t1.*,t2.title from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where t1.id=:id";
                $room = pdo_fetch($roomsql, array(':id' => $order['addressid']));
                if (!empty($order['credit'])) {
                    //积分抵扣
                    load()->model('mc');
                    mc_credit_update($order['uid'], 'credit1', -$order['credit'], array($order['uid'], '扣除物业费抵扣积分'));
                    //业主积分变更记录
                    $creditdata = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . "," . $room['title'] . $room['address'] . "抵扣积分",
                        'credit' => $order['credit'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 1,
                        'typeid' => $room['regionid'],
                        'category' => 1,
                        'usename' => $member['realname']
                    );
                    pdo_insert('xcommunity_credit', $creditdata);
                    //小区积分变更记录
                    pdo_update('xcommunity_region', array('credit +=' => $order['credit']), array('id' => $room['regionid']));
                    $creditdata2 = array(
                        'uniacid' => $_W['uniacid'],
                        'good' => "订单号：" . $order['ordersn'] . "," . $room['title'] . $room['address'] . "抵扣积分",
                        'price' => $order['credit'],
                        'creditstatus' => 1,
                        'createtime' => TIMESTAMP,
                        'cid' =>1,
                        'regionid' => $room['regionid'],
                        'category' => 1,
                        'type'=>3,
                        'realname' =>'系统'
                    );
                    pdo_insert('xcommunity_commission_log', $creditdata2);
                }
                $costsql = "select t2.goodsid,t1.username from" . tablename('xcommunity_cost_list') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id = t2.goodsid where t2.orderid =:orderid";
                $cost = pdo_fetchall($costsql, array(':orderid' => $order['id']));

                foreach ($cost as $k => $v) {
                    pdo_update('xcommunity_cost_list', array('status' => 1, 'credit1' => $order['credit'], 'paytype' => $data['paytype'], 'paytime' => TIMESTAMP), array('id' => $v['goodsid']));
                }
                $data['createtime'] = TIMESTAMP;
                $p51 = set('p51') ? set('p51') : '您已成功缴费，可登陆我-我的账单查看';
                $result = util::sendnotice($p51, $order['openid']);
                $t11 = set('t11');
                if ($t11) {
                    //微信通知
                    $createtime = date('Y-m-d H:i', TIMESTAMP);
                    $sn = $data['transid'] ? '微信订单号:' . $data['transid'] : '';
                    $content = array(
                        'first' => array(
                            'value' => '您好，有新的缴费订单。',
                        ),
                        'userName' => array(
                            'value' => $member['realname'],
                        ),
                        'address' => array(
                            'value' => $room['title'] . $room['address'],
                        ),
                        'pay' => array(
                            'value' => $order['price'],
                        ),
                        'remark' => array(
                            'value' => '请尽快消单处理，并开具收据发票。缴费时间:' . $createtime . $sn,
                        ),
                    );
                    $t12 = set('t12');
                    $tplid = $t12;
                    if($tplid){
                        //是否填写模板消息ID
                        unset($ret);
                        $ret = util::sendtpl($room['regionid'], $content, ' and t1.cost=1', $url = '', $tplid);
                        $cnotice = pdo_fetchall("select t1.* from" . tablename('xcommunity_cost_wechat') . "t1 left join" . tablename('xcommunity_cost_wechat_region') . "t2 on t1.id=t2.cid where t1.enable=1 and t2.regionid=:regionid", array('regionid' => $room['regionid']));
                        if (!empty($cnotice)) {
                            foreach ($cnotice as $key => $val) {
                                if ($val['type'] == 1 || $val['type'] == 3) {
                                    unset($ret);
                                    $ret=util::sendTplNotice($val['openid'], $tplid, $content, $url = '', $topcolor = '#FF683F');
                                }
                            }
                        }
                    }

                }
                $p101 = set('p101');
                if ($p101) {
                    //增加积分
                    load()->model('mc');
                    $p102 = set('p102');
                    $credit = $order['price'] * $p102;
                    mc_credit_update($order['uid'], 'credit1', $credit, array($order['uid'], '物业缴费赠送积分'));
                    $creditdata = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . "," . $room['title'] . $room['address'] . "赠送积分",
                        'credit' => $credit,
                        'creditstatus' => 1,
                        'createtime' => TIMESTAMP,
                        'type' => 1,
                        'typeid' => $room['regionid'],
                        'category' => 1,
                        'usename' => $member['realname']
                    );
                    pdo_insert('xcommunity_credit', $creditdata);
                }
                //记录余额的日志
                if ($data['paytype'] == 1) {
                    $creditdata2 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . "," . $room['title'] . $room['address'] . "缴纳物业费",
                        'credit' => $order['price'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 1,
                        'typeid' => $room['regionid'],
                        'category' => 2,
                        'usename' => $member['realname']
                    );
                    pdo_insert('xcommunity_credit', $creditdata2);
                }
            }
            /**
             * 自定义账单
             */
            if ($order['type'] == 'bill') {
                //我的账单支付
                $roomsql = "select t1.*,t2.title from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where t1.id=:id";
                $room = pdo_fetch($roomsql, array(':id' => $order['addressid']));
                if (!empty($order['credit'])) {
                    //积分抵扣
                    load()->model('mc');
                    mc_credit_update($order['uid'], 'credit1', -$order['credit'], array($order['uid'], '扣除物业费抵扣积分'));
                    $creditdata = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . "," . $room['title'] . $room['address'] . "抵扣积分",
                        'credit' => $order['credit'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 5,
                        'typeid' => $order['regionid'],
                        'category' => 1,
                        'usename' => $member['realname'],
                    );
                    pdo_insert('xcommunity_credit', $creditdata);
                }
                $costsql = "select t2.goodsid,t1.username from" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id = t2.goodsid where t2.orderid =:orderid";
                $cost = pdo_fetchall($costsql, array(':orderid' => $order['id']));
                foreach ($cost as $k => $v) {
//                    $paytype = $data['transid'] ? 2 : 3;
                    $paytype = $data['paytype'];
                    pdo_update('xcommunity_fee', array('pay_price' => $order['price'], 'status' => 2, 'paytype' => $paytype, 'paytime' => TIMESTAMP,'username'=>'系统'), array('id' => $v['goodsid']));
                }
                $data['createtime'] = TIMESTAMP;
                $p51 = set('p51') ? set('p51') : '您已成功缴费，可登陆我-我的账单查看';
                if($p51){
                    util::sendnotice($p51,$order['openid']);
                }
                $t11 = set('t11');
                if ($t11) {
                    //微信通知
                    $createtime = date('Y-m-d H:i', TIMESTAMP);
                    $sn = $data['transid'] ? '微信订单号:' . $data['transid'] : '';
                    $content = array(
                        'first' => array(
                            'value' => '您好，有新的缴费订单。',
                        ),
                        'userName' => array(
                            'value' => $member['realname'],
                        ),
                        'address' => array(
                            'value' => $room['title'] . $room['address'],
                        ),
                        'pay' => array(
                            'value' => $order['price'],
                        ),
                        'remark' => array(
                            'value' => '请尽快消单处理，并开具收据发票。缴费时间:' . $createtime . $sn,
                        ),
                    );
                    $t12 = set('t12');
                    $tplid = $t12;
                    util::sendtpl($room['regionid'], $content, ' and t1.cost=1', $url = '', $tplid);
                    $cnotice = pdo_fetchall("select t1.* from" . tablename('xcommunity_cost_wechat') . "t1 left join" . tablename('xcommunity_cost_wechat_region') . "t2 on t1.id=t2.cid where t1.enable=1 and t2.regionid=:regionid", array('regionid' => $room['regionid']));
                    foreach ($cnotice as $key => $val) {
                        if ($val['type'] == 1 || $val['type'] == 3) {
                            util::sendTplNotice($val['openid'], $tplid, $content, $url = '', $topcolor = '#FF683F');
                        }
                    }
                }
                $p101 = set('p101');
                if ($p101) {
                    //增加积分
                    load()->model('mc');
                    $p102 = set('p102');
                    $credit = $order['price'] * $p102;
                    mc_credit_update($order['uid'], 'credit1', $credit, array($order['uid'], '物业缴费赠送积分'));
                    $creditdata = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . "," . $room['title'] . $room['address'] . "赠送积分",
                        'credit' => $credit,
                        'creditstatus' => 1,
                        'createtime' => TIMESTAMP,
                        'type' => 5,
                        'typeid' => $order['regionid'],
                        'category' => 1,
                        'usename' => $member['realname']
                    );
                    pdo_insert('xcommunity_credit', $creditdata);
                }
                if ($data['paytype'] == 1) {
                    $creditdata2 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . "," . $room['title'] . $room['address'],
                        'credit' => $order['price'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 5,
                        'typeid' => $order['regionid'],
                        'category' => 2,
                        'usename' => $member['realname']
                    );
                    pdo_insert('xcommunity_credit', $creditdata2);
                }
            }
            /**
             * 报修维修费
             */
            if ($order['type'] == 'report') {
                //报修维修费
                $room = pdo_get('xcommunity_member_room', array('id' => $order['addressid']), array());
                $regiontitle = pdo_getcolumn('xcommunity_region', array('id' => $room['regionid']), 'title');
                if ($data['paytype'] == 1) {
                    $creditdata2 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . "," . $regiontitle . $room['address'],
                        'credit' => $order['price'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 6,
                        'typeid' => $order['regionid'],
                        'category' => 2,
                        'usename' => $member['realname']
                    );
                    pdo_insert('xcommunity_credit', $creditdata2);
                }
            }
            /**
             * 充电桩充电
             */
            if ($order['type'] == 'charging') {
                //充电桩
                $sql = "select t1.socket,t1.code,t1.cdtime,t2.id,t3.enable,t2.type,t2.appid,t2.appsecret from" . tablename('xcommunity_charging_order') . "t1 left join" . tablename('xcommunity_charging_station') . "t2 on t1.code=t2.code left join" . tablename('xcommunity_charging_socket') . "t3 on t3.lock = t1.socket where t1.orderid =:orderid ";
                $item = pdo_fetch($sql, array(':orderid' => $order['id']));
                if ($item) {
                    if ($item['type'] == 1) {
                        //支付完成，上电 途电  按时计费
                        $pid = $item['code'] . '_' . (string)$item['socket'];
                        $seconds = $item['cdtime'] * 60;
                        require_once IA_ROOT . '/addons/xfeng_community/plugin/tudian/function.php';
                        $cuid = getPowerUp($item['appid'], $item['appsecret'], $pid, $seconds);
                        if ($cuid) {
                            $logids = explode('_', $cuid);
                            $logid = $logids[2];
                            pdo_update('xcommunity_charging_order', array('status' => 1, 'cuid' => $cuid, 'logid' => $logid), array('orderid' => $order['id']));
                        }
                    } elseif ($item['type'] == 2) {
                        //支付完成，上电 威威充
                        require_once IA_ROOT . '/addons/xfeng_community/plugin/avive/charge/function.php';
                        $pushtime = $item['cdtime'];
                        $socket = (string)$item['socket'] + 1;
                        $result = pushPower($item['code'], $socket, $pushtime);
                        if ($result['status'] == 'OK') {
                            //插座显示为充电中
                            pdo_update('xcommunity_charging_order', array('status' => 1), array('orderid' => $order['id']));
                        }
                    }
                }
                if ($data['paytype'] == 1) {
                    $creditdata2 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'] ? $member['realname'] : $member['nickname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . ",设备编号：" . $item['code'],
                        'credit' => $order['price'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 7,
                        'typeid' => $order['regionid'],
                        'category' => 2,
                        'usename' => $member['realname'] ? $member['realname'] : $member['nickname']
                    );
                    pdo_insert('xcommunity_credit', $creditdata2);
                }
            }
            /**
             * 商家充值赠送积分
             */
            if ($order['type'] == 'mbusiness') {
                //小区商家充值送积分,平台支付，待修改
                $credit = set('p94') * $order['price'];
                $user = pdo_get('xcommunity_users', array('uid' => $order['uid']), array('creditstatus', 'integral', 'staffid', 'uid'));
                $staff = pdo_get('xcommunity_staff', array('id' => $user['staffid']), array('mobile'));
                if (pdo_update('xcommunity_recharge', array('status' => 1, 'credit +=' => $credit), array('orderid' => $order['id']))) {
                    //更新积分
                    pdo_update('xcommunity_users', array('credit +=' => $credit), array('uid' => $order['uid']));
                    $creditdata2 = array(
                        'uid' => $user['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $staff['mobile'],
                        'mobile' => $staff['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . ",商家充值购买积分",
                        'credit' => $credit,
                        'creditstatus' => 1,
                        'createtime' => TIMESTAMP,
                        'type' => 2,
                        'typeid' => $user['uid'],
                        'category' => 3,
                        'usename' => '后台'
                    );
                    pdo_insert('xcommunity_credit', $creditdata2);

                }
            }
            if ($order['type'] == 'super') {
                //无人超市支付，待修改
                if (pdo_update('xcommunity_supermark_order', array('status' => 1, 'uid' => $_W['member']['uid'], 'openid' => $_W['openid']), array('ordersn' => $ordersn))) {
                    load()->func('communication');
                    $resp = ihttp_post('https://api.njlanniu.cn/addons/lanniu/api.php', array(
                        'type' => 'update',
                        'orderid' => $ordersn,
                        'status' => 1,
                        'op' => 'kh'
                    ));
                    itoast('支付成功', $this->createMobileUrl('home'), 'success');
                }
            }
            if ($order['type'] == 'park') {

            }
            /**
             * 充电桩充值
             */
            if ($order['type'] == 'chargrecharge') {
                //增加余额

                $result = pdo_update('mc_members', array('chargecredit +=' => $order['price']), array('uid' => $order['uid']));
                $creditdata2 = array(
                    'uid' => $order['uid'],
                    'uniacid' => $_W['uniacid'],
                    'realname' => $member['realname'] ? $member['realname'] : $member['nickname'],
                    'mobile' => $member['mobile'],
                    'content' => "订单号：" . $order['ordersn'] . ",充电桩余额充值",
                    'credit' => $order['price'],
                    'creditstatus' => 1,
                    'createtime' => TIMESTAMP,
                    'type' => 7,
                    'typeid' => $order['regionid'],
                    'category' => 7,
                    'usename' => $member['realname'] ? $member['realname'] : $member['nickname']
                );
                pdo_insert('xcommunity_credit', $creditdata2);
                if ($data['paytype'] == 1) {
                    $creditdata1 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'] ? $member['mobile'] : $order['openid'],
                        'content' => "订单号：" . $order['ordersn'] . ",充电桩余额充值",
                        'credit' => $order['price'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 7,
                        'typeid' => $order['regionid'],
                        'category' => 2,
                        'usename' => $member['realname']
                    );
                    pdo_insert('xcommunity_credit', $creditdata1);
                }
            }
            /**
             * 云柜支付
             */
            if ($order['type'] == 'counter') {
                $item = pdo_fetch("select t1.lock,t2.device,t2.id as deviceid,t1.enable,t2.stat,t2.rule,t2.price from" . tablename('xcommunity_counter_little') . "t1 left join" . tablename('xcommunity_counter_main') . "t2 on t1.deviceid=t2.id where t1.id=:littleid", array(':littleid' => $order['littleid']));
                $log = pdo_fetch("select id,status from" . tablename('xcommunity_counter_log') . " where deviceid=:deviceid and littleid=:littleid and uid=:uid order by createtime desc", array(':deviceid' => $item['deviceid'], ':littleid' => $order['littleid'], 'uid' => $order['uid']));
                $did = $item['device'] . '-' . ($item['lock'] + 1);
                require_once IA_ROOT . '/addons/xfeng_community/plugin/mijia/function.php';
                $info = getDeviceInfo($did);//online
                $openinfo = OpenDevice($did);//suc
                if ($info == 'online' && $openinfo == 'suc') {
                    $data1 = array(
                        'uniacid' => $_W['uniacid'],
                        'deviceid' => $item['deviceid'],
                        'littleid' => $order['littleid'],
                        'uid' => $_W['member']['uid'],
                        'openid' => $_W['openid'],
                        'createtime' => TIMESTAMP,
                        'type' => 2
                    );
                    if ($log['status'] == 2) {
                        $data1['status'] = 1;
                        $enable = 0;
                    } else {
                        $enable = 2;
                    }
                    pdo_update('xcommunity_counter_little', array('enable' => $enable), array('id' => $order['littleid']));
                    pdo_insert('xcommunity_counter_log', $data1);
                    unset($data1);
                }
                if ($data['paytype'] == 1) {
                    $creditdata2 = array(
                        'uid' => $order['uid'],
                        'uniacid' => $_W['uniacid'],
                        'realname' => $member['realname'],
                        'mobile' => $member['mobile'],
                        'content' => "订单号：" . $order['ordersn'] . ",云柜编号" . $item['device'],
                        'credit' => $order['price'],
                        'creditstatus' => 2,
                        'createtime' => TIMESTAMP,
                        'type' => 8,
                        'typeid' => $order['regionid'],
                        'category' => 2,
                        'usename' => $member['realname']
                    );
                    pdo_insert('xcommunity_credit', $creditdata2);
                }
            }
            /**
             * 智能车禁支付
             */
            if ($order['type'] == 'parks') {
                $parkData = array();
                $parkOrder = pdo_get('xcommunity_parks_order', array('orderid' => $order['id']), array());
                require_once IA_ROOT . '/addons/xfeng_community/plugin/lanniu/cp/function.php';
                // 月租车
                if ($parkOrder['category'] == 1) {
                    // 停车场的设备
                    $list = pdo_getall('xcommunity_parks_device', array('parkid' => $parkOrder['parkid']), array('id', 'parkid', 'identity'));
                    $parkData['enable'] = 1;
                    $carEndtime = $parkOrder['endtime'];
                    $end = $carEndtime + $parkOrder['num'] * 86400;
                    $endtime = date('YmdHis', $end); //到期时间
                    $carlist = array();
                    $carlist[0] = array(
                        'content' => $parkOrder['carno'],
                        'expiretime' => $endtime
                    );
                    foreach ($list as $k => $v) {
                        // 加入白名单
                        $result = addWhiteCarnos($v['identity'], $carlist);
                        if (empty($result['success'])) {
                            $parkData['enable'] = 2;
                        }
                    }
                    // 修改车牌号下发的到期时间
                    pdo_update('xcommunity_parks_cars', array('endtime' => $end), array('carno' => $parkOrder['carno'], 'parkid' => $parkOrder['parkid'], 'type' => 1));
                    $keyword2 = '月租车缴费';
                }
                // 临时车
                if ($parkOrder['category'] == 2) {
                    if ($parkOrder['pay_status'] == 2) {
                        //出口付费，直接开闸
                        open($parkOrder['identity']);
                        pdo_update('xcommunity_parks_log', array('open_status' => 1, 'total_price ' => $parkOrder['price'], 'price ' => $parkOrder['price'], 'paytime' => TIMESTAMP, 'paytype' => $data['paytype']), array('id' => $parkOrder['logid']));
                    } else {
                        pdo_update('xcommunity_parks_log', array('total_price' => $parkOrder['price'], 'price' => $parkOrder['price'], 'paytime' => TIMESTAMP, 'paytype' => $data['paytype']), array('id' => $parkOrder['logid']));
                    }
                    $keyword2 = '临时车缴费';
                }
                $parkData['status'] = 1;
                $parkData['paytime'] = TIMESTAMP;
                $parkData['paytype'] = $data['paytype'];
                $result = pdo_update('xcommunity_parks_order', $parkData, array('orderid' => $order['id']));
            }
            /**
             * 更新订单状态
             */
            if (pdo_update('xcommunity_order', $data, array('ordersn' => $ordersn))) {
                return array('code' => 1);
                exit();
            } else {
                return array('code' => 2);
                exit();
            }
        }

    }

    private function checkWechatTran($setting, $transid)
    {
        $wechat = $setting['payment']['wechat'];

        $sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `acid`=:acid';
        $row = pdo_fetch($sql, array(':acid' => $wechat['account']));
        $wechat['appid'] = $row['key'];

        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        $random = random(8);

        $post = array(
            'appid' => $wechat['appid'],
            'transaction_id' => $transid,
            'nonce_str' => $random,
            'mch_id' => $wechat['mchid'],
        );
        ksort($post);

        $string = $this->ToUrlParams($post);
        $string .= "&key={$wechat['signkey']}";
        $sign = md5($string);
        $post['sign'] = strtoupper($sign);
        // return $post;
        $resp = $this->postXmlCurl($this->ToXml($post), $url);
        libxml_disable_entity_loader(true);
        $resp = json_decode(json_encode(simplexml_load_string($resp, 'SimpleXMLElement', LIBXML_NOCDATA)), true);


        if ($resp['result_code'] != 'SUCCESS') {
            exit('fail');
        }
        if ($resp['trade_state'] == 'SUCCESS')
            return array('code' => 1, 'fee' => $resp['total_fee'] / 100);
    }

    private function ToUrlParams($post)
    {
        $buff = "";
        foreach ($post as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    private function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($useCert == true) {
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        }
    }

    private function ToXml($post)
    {
        $xml = "<xml>";
        foreach ($post as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
    function xqmessage($msg, $redirect = '', $type = '', $tips = false, $extend = array()) {
        global $_W, $_GPC;

        if($redirect == 'refresh') {
            $redirect = $_W['script_name'] . '?' . $_SERVER['QUERY_STRING'];
        }
        if($redirect == 'referer') {
            $redirect = referer();
        }
        $redirect = safe_gpc_url($redirect);
        if($redirect == '') {
            $type = in_array($type, array('success', 'error', 'info', 'warning', 'ajax', 'sql')) ? $type : 'info';
        } else {
            $type = in_array($type, array('success', 'error', 'info', 'warning', 'ajax', 'sql')) ? $type : 'success';
        }
        if ($_W['isajax'] || !empty($_GET['isajax']) || $type == 'ajax') {
            if($type != 'ajax' && !empty($_GPC['target'])) {
                exit("
<script type=\"text/javascript\">
	var url = ".(!empty($redirect) ? 'parent.location.href' : "''").";
	var modalobj = util.message('".$msg."', '', '".$type."');
	if (url) {
		modalobj.on('hide.bs.modal', function(){\$('.modal').each(function(){if(\$(this).attr('id') != 'modal-message') {\$(this).modal('hide');}});top.location.reload()});
	}
</script>");
            } else {
                $vars = array();
                $vars['message'] = $msg;
                $vars['redirect'] = $redirect;
                $vars['type'] = $type;
                exit(json_encode($vars));
            }
        }
        if (empty($msg) && !empty($redirect)) {
            header('Location: '.$redirect);
            exit;
        }
        $label = $type;
        if($type == 'error') {
            $label = 'danger';
        }
        if($type == 'ajax' || $type == 'sql') {
            $label = 'warning';
        }

        if ($tips) {
            if (is_array($msg)){
                $message_cookie['title'] = 'MYSQL 错误';
                $message_cookie['msg'] = 'php echo cutstr(' . $msg['sql'] . ', 300, 1);';
            } else{
                $message_cookie['title'] = $caption;
                $message_cookie['msg'] = $msg;
            }
            $message_cookie['type'] = $label;
            $message_cookie['redirect'] = $redirect ? $redirect : referer();
            $message_cookie['msg'] = rawurlencode($message_cookie['msg']);
            $extend_button = array();
            if (!empty($extend) && is_array($extend)) {
                foreach ($extend as $button) {
                    if (!empty($button['title']) && !empty($button['url'])) {
                        $button['url'] = safe_gpc_url($button['url']);
                        $button['title'] = rawurlencode($button['title']);
                        $extend_button[] = $button;
                    }
                }
            }
            $message_cookie['extend'] = !empty($extend_button) ? $extend_button : '';

            isetcookie('message', stripslashes(json_encode($message_cookie, JSON_UNESCAPED_UNICODE)));
            header('Location: ' . $message_cookie['redirect']);
        } else {
            include $this->template('web/common/message');
        }
        exit;
    }
}



