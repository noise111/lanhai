<?php

if (!defined('IN_IA')) {
	exit('Access Denied');
}

require(__DIR__ . "/storebase.php");
class Saler_EweiShopV2Page extends Storebase_EweiShopV2Page{
    
    /**
     * 获取导购相关的客户信息
     */
    public function get_member(){
        global $_W;
        global $_GPC;
        $openid = isset($_GPC['saleropenid']) && !empty(trim($_GPC['saleropenid'])) ? $_GPC['saleropenid'] : $_W['openid'];
        $type = isset($_GPC['type']) ? intval($_GPC['type']) : 1;
        $customer_type = isset($_GPC['customer_type']) ? intval($_GPC['customer_type']) : 0;
        $saleropenid = "r.saleropenid = '$openid'";
        $saler_info = m('store')->getSalerInfo(array('openid' => $openid));
        $condition = "";
        
        if($type == 1){
            //$saleropenid = $openid;
        } else if($type == 2 && $saler_info['storemanager'] == 1){
            $saleropenid = '';
            $sql = "SELECT openid FROM " . tablename("ewei_shop_saler") . 
                   " WHERE storeid = {$saler_info['storeid']} ";
            $salers = pdo_fetchall($sql, array(), 'openid');
            $salers = array_keys($salers);
            if($salers){
                $saleropenid = implode(',', array_map(function($str){
                    return sprintf("'%s'", $str);
                }, $salers));
            } 
            $saleropenid = "r.saleropenid  IN ($saleropenid)";
        }
        
        switch ($customer_type){
            case 0:
                break;
            //直接
            case 1:
                $condition .= " AND (r.parentopenid IS NULL OR r.parentopenid = '') "; 
                break;
            //间接
            case 2:
                $condition .= " AND (r.parentopenid IS NOT NULL AND r.parentopenid <> '') "; 
                break;
        }
        $select = "SELECT m.openid, m.realname, m.mobile, m.nickname, m.avatar, m.province, m.city, m.area, r.createtime, r.parentopenid ";
        $sqlcondition = "FROM " . tablename('ewei_shop_coupon_relation') . " r " .
                        "INNER JOIN " . tablename('ewei_shop_member') . " m ON m.openid = r.customeropenid " . 
                        "WHERE r.uniacid = {$_W['uniacid']} AND $saleropenid $condition ";
        $list = pdo_fetchall($select . $sqlcondition);
        foreach($list as &$row){
            //获取每个客户的消费次数
            $sql = "SELECT COUNT(id) as number, createtime FROM " . tablename('ewei_shop_order') . " WHERE openid = '{$row['openid']}' AND status = 3 AND uniacid = {$_W['uniacid']} ORDER BY createtime DESC ";         
            //$row['times'] = pdo_fetch($sql);
            $res = pdo_fetch($sql);
            $row['times'] = $res['number'];
            $row['last_time'] = date("Y-m-d", $res['createtime']);
            if(!empty($row['mobile'])){
                $row['mobile'] = substr_replace($row['mobile'], '****', 3, 4);
            }
            
        }
        $select = "SELECT count(r.id) ";
        $total = pdo_fetchcolumn($select . $sqlcondition);
        app_json(array('list' => $list, 'total' => (int)$total));
    }
    
    /**
     * 获取门店导购员列表
     */
    public function get_saler_list(){
        global $_W;
        global $_GPC;
        $type = isset($_GPC['type']) ? $_GPC['type'] : 1;
        $uniacid = $_W['uniacid'];
        $openid = $_W['openid'];
        $saler = m('store')->getSalerInfo(array('openid' => $openid));
        if($saler){
            if($saler['storemanager'] && $saler['storeid']){
                $params = array(
                    ':uniacid'          => $uniacid,
                    ':storeid'          => $saler['storeid']
                );
                $sql = " SELECT s.*, m.avatar, r.rolename, r.status AS role_status, r.shoppingguide, r.storemanager, r.verify  FROM " .
                       tablename('ewei_shop_saler') . " s " .
                       " LEFT JOIN " . tablename('ewei_shop_saler_role') . " r ON r.id = s.roleid " . 
                       " INNER JOIN " . tablename('ewei_shop_member') . " m ON m.openid = s.openid " .
                       " WHERE s.uniacid = :uniacid AND s.storeid = :storeid AND r.shoppingguide = 1 "; 
                $list = pdo_fetchall($sql, $params);
                //当日
                $today = array(
                    'start_time'    => strtotime("today"),
                    'end_time'      => strtotime("tomorrow") - 1
                );
                //当月
                $month = $this->get_the_month();
                //业绩统计
                if($type == 1){
                    foreach ($list as &$row){
                        $sql = "SELECT COUNT(id) FROM" . tablename('ewei_shop_coupon_relation') . " WHERE saleropenid = '{$row['openid']}' AND uniacid = {$uniacid}";
                        $number = pdo_fetchcolumn($sql);
                        $number ? $number : 0;
                        $row['customer_number'] = $number;
                        $sql = "SELECT SUM(price) as total, createtime FROM " . tablename("ewei_shop_order") . 
                               " WHERE saleropenid = '{$row['openid']}' AND uniacid = {$uniacid} AND status = 3 ORDER BY createtime DESC";
                        $res = pdo_fetch($sql);
                        $row['performance'] = empty($res['total']) ? 0 : floatval($res['total']);
                        $row['last_order'] = empty($res['createtime']) ? 0 : date('Y-m-d', $res['createtime']);
                        //获取导购业绩
                        $today_performance = $this->get_customer_order_statistics($row['openid'], $today['start_time'], $today['end_time']);
                        //当月业绩
                        $month = $this->get_the_month();
                        $month_performance = $this->get_customer_order_statistics($row['openid'], $month['start_time'], $month['end_time']);
                        $all_performance = $this->get_customer_order_statistics($row['openid']);
                        $row['today_performance'] = $today_performance;
                        $row['month_performance'] = $month_performance;
                        $row['all_performance']   = $all_performance;
                    }
                //客户统计
                } else {
                    foreach($list as &$row){
                        $customer_statistics = $this->get_saler_customer_statistics($row['openid'], 1, true);
                        $row['indirect'] = $customer_statistics['indirect'];
                        $row['direct'] = $customer_statistics['direct'];
                        $row['all'] = $customer_statistics['all'];
                    }
                }
                app_json(array('list' => $list));
            }
        }
        app_error();
    }   
    
    /**
     * 获取导购业绩记录
     * @global array $_W
     * @global array $_GPC
     */
    public function get_benefit_log(){
        global $_W;
		global $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
        
        //获取佣金记录（benefit）
        $sql = "(
                SELECT o.ordersn, o.openid, o.saleropenid, ol.salerbenefit, ol.createtime, m.avatar, m.nickname, 1 as type 
                FROM ims_ewei_shop_order  o
                INNER JOIN ims_ewei_shop_order_benefit_log ol ON o.id = ol.orderid
                LEFT JOIN ims_ewei_shop_member m ON m.openid = o.openid
                WHERE ol.saleropenid = '{$_W['openid']}' AND o.uniacid = {$_W['uniacid']} 
                )
                UNION ALL
                (
                SELECT o.ordersn, o.openid, o.saleropenid, cl.number as salerbenefit, cl.createtime, m.avatar, m.nickname, 2 as type
                FROM ims_ewei_shop_order o
                INNER JOIN ims_ewei_shop_coupon_benefit_log cl on cl.orderid = o.id
                LEFT JOIN ims_ewei_shop_member m ON m.openid = o.openid
                WHERE cl.openid = '{$_W['openid']}' AND o.uniacid = {$_W['uniacid']} AND cl.type = 0
                )
                ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . " , $psize";
        $list = pdo_fetchall($sql);
        foreach($list as &$row){
            if($row['type'] == 1){
                if(!empty($row['saleropenid'])){
                    $row['type_str'] = "销售返佣";
                } else {
                    $row['type_str'] = "分享返佣";
                }
            } else if($row['type'] == 2){
                $row['type_str'] = "分享券返";
            } 
            $row['createtime'] = date('Y-m-d H:i', $row['createtime']);
        }
        $sql_count = "SELECT SUM(cnt) FROM ((
                SELECT COUNT(o.id) AS cnt 
                FROM ims_ewei_shop_order  o
                INNER JOIN ims_ewei_shop_order_benefit_log ol ON o.id = ol.orderid
                LEFT JOIN ims_ewei_shop_member m ON m.openid = o.openid
                WHERE ol.saleropenid = '{$_W['openid']}' AND o.uniacid = {$_W['uniacid']} 
                )
                UNION ALL
                (
                SELECT COUNT(o.id) AS cnt
                FROM ims_ewei_shop_order o
                INNER JOIN ims_ewei_shop_coupon_benefit_log cl on cl.orderid = o.id
                LEFT JOIN ims_ewei_shop_member m ON m.openid = o.openid
                WHERE cl.openid = '{$_W['openid']}' AND o.uniacid = {$_W['uniacid']} AND cl.type = 0
                )) AS temp";
        $total = pdo_fetchcolumn($sql_count);
        app_json(array('list' => $list, 'total' => $total));
    }
    
    /**
     * 导购订单业绩记录
     * @global array $_W
     * @global array $_GPC
     */
    public function get_customer_order_list(){
        global $_W;
        global $_GPC;
        $pindex = max(1, intval($_GPC['page']));
		$psize = 10;        
        $openid = isset($_GPC['saleropenid']) && !empty(trim($_GPC['saleropenid'])) ? $_GPC['saleropenid'] : $_W['openid'];
        $type = isset($_GPC['type']) ? intval($_GPC['type']) : 1;
        $saler_info = m('store')->getSalerInfo(array('openid' => $openid));
        $saleropenid = " (o.saleropenid = '$openid' OR ol.saleropenid = '$openid')";
        //导购本人
        if($type == 1){
            //$saleropenid = $openid;
        //门店内所有导购
        } else if($type == 2 && $saler_info['storemanager'] == 1){
            $saleropenid = m('store')->getStoreSalerOpenid($saler_info['storeid'], '', 2);
            $saleropenid = "(o.saleropenid  IN ($saleropenid) OR ol.saleropenid IN ($saleropenid))";
        }
        $select = "SELECT o.id, o.openid, o.ordersn, o.createtime, o.paytype, o.status, " . 
               "ol.storeid as b_storeid, ol.storebenefit, ol.saleropenid as b_saleropenid, ol.salerbenefit, ol.agentid as b_agentid, ol.agentbenefit, ol.team as b_team, ol.teambenefit, ol.createtime as b_createtime ";
         
        $sql = " FROM " . tablename('ewei_shop_order'). " o " . 
               " LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol on ol.orderid = o.id" .
               " WHERE o.uniacid = {$_W['uniacid']} AND $saleropenid ORDER BY o.createtime desc ";
        $limit = " LIMIT " . ($pindex - 1) * $psize . " , $psize";
        $list = pdo_fetchall($select . $sql . $limit);
        //获取总数
        $select = "SELECT COUNT(o.id) AS total ";
        $total = pdo_fetchcolumn($select . $sql);
        foreach($list as &$row){
            $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
            $member = m('member')->getInfo($row['openid']);
            $row['nickname'] = $member['nickname'];
            $row['avatar'] = $member['avatar'];
            $row['buytype'] = m('order')->getBuyType($row['id']);
            switch($row['buytype']){
                case 1:
                    $row['buystr'] = '平台支付 - 平台发货';
                    break;
                case 2:
                    if($row['paytype'] == 4){
                        $row['buystr'] = "收银台支付 - 门店购物";
                    } else {
                        $row['buystr'] = "平台支付 - 门店自提";
                    }
                    break;
                case 3:
                    $row['buystr'] = "收银台支付 - 门店自提";
                    break;
                    
            }
            //查商品
            $sql = "SELECT GROUP_CONCAT(DISTINCT g.title) AS title FROM " . tablename("ewei_shop_order_goods") . " og " . 
                    "LEFT JOIN " . tablename("ewei_shop_order") . " o ON og.orderid = o.id " . 
                    "LEFT JOIN " . tablename("ewei_shop_goods") . " g ON g.id = og.goodsid " .
                    "WHERE o.id = {$row['id']} GROUP BY og.goodsid ";
            $title = pdo_fetchcolumn($sql);
            $row['title'] = $title;
        }
        app_json(array('list' => $list, 'total' => $total));
    }
    
    /**
     * 获取导购业绩统计
     * @global array $_W
     */
    public function get_saler_statistics(){
        global $_W;
        global $_GPC;
        $type = isset($_GPC['type']) ? $_GPC['type'] : 1;
        $openid = $_W['openid'];
        $saler_info = m('store')->getSalerInfo(array('openid' => $openid));
        if($type == 1){           
        //门店业绩
        } else if($type == 2 && $saler_info['storemanager'] == 1){
            $openid = m('store')->getStoreSalerOpenid($saler_info['storeid']);
        }
        //今日业绩
        $today_performance = $this->get_customer_order_statistics($openid, strtotime("today"), strtotime("tomorrow") - 1);
        //当月业绩
        $month = $this->get_the_month();
        $month_performance = $this->get_customer_order_statistics($openid, $month['start_time'], $month['end_time']);
        //累计业绩
        $all_performance = $this->get_customer_order_statistics($openid);
        if(empty($saler_info['passtime'])){
            $work_days = 0;
        } else {
            $work_days = ceil((time() - $saler_info['passtime']) / (24*3600));
        }
        app_json(array(
            'today_performance'     => $today_performance,
            'month_performance'     => $month_performance,
            'all_performance'       => $all_performance,
            'work_days'             => $work_days
        ));
    }
    
    public function get_customer_order_statistics($openid, $start_time = 0, $end_time = 0){
        if(is_array($openid)){
            $openid = array_map(function($str){
                return sprintf("'%s'", $str);
            }, $openid);
            $saleropenid = " saleropenid IN(" . implode(',', $openid) . ")";
        } else {
            $saleropenid = " saleropenid = '$openid' ";
        }
        $condition = '';
        if($start_time){
            $condition .= " AND createtime >= $start_time ";
        }
        if($end_time){
            $condition .= " AND createtime <= $end_time ";
        }
        $sql = "SELECT SUM(price) FROM " . tablename("ewei_shop_order") . 
               "WHERE status = 3 AND $saleropenid $condition";
        $sum = pdo_fetchcolumn($sql);
        return empty($sum) ? 0 : $sum;
    }
    
    public function get_the_month(){
        $year = date("Y");
        $month = date("m");
        $allday = date("t");
        $start_time = strtotime($year."-".$month."-1");
        $end_time = strtotime($year."-".$month."-".$allday) + 3600*24 - 1;
        return array('start_time' => $start_time, 'end_time' => $end_time);
    }
    
    public function get_saler_customer_statistics($openid = '', $type = 0, $return = false){
        global $_W;
        global $_GPC;
        if(empty($type)){
            $type = isset($_GPC['type']) ? $_GPC['type'] : 1;
        }
        if(empty($openid)){
            $openid = $_W['openid'];
        }
        $saler_info = m('store')->getSalerInfo(array('openid' => $openid));
        if($type == 1){     
            $saleropenid = " saleropenid = '$openid' ";
        //店内所有导购
        } else if($type == 2){
            $salers = m('store')->getStoreSalerOpenid($saler_info['storeid'], '', 2);
            $saleropenid = " saleropenid IN($salers) ";
        }
        $sql = "SELECT COUNT(id) FROM " . tablename("ewei_shop_coupon_relation") . 
               " WHERE $saleropenid ";
        //间接客户
        $where = " AND (parentopenid IS NOT NULL AND parentopenid <> '')";
        $indirect = pdo_fetchcolumn($sql . $where);
        //直接客户
        $where = " AND (parentopenid IS NULL || parentopenid = '') ";
        $direct = pdo_fetchcolumn($sql . $where);
        //所有客户
        $all = pdo_fetchcolumn($sql);
        $result = array(
            'indirect'      => $indirect,
            'direct'        => $direct,
            'all'           => $all
        );
        if($return){
            return $result;
        } else {
            app_json($result);
        }
    }
    
    public function edit_saler(){
        $this->post_saler();
    }
    
    public function add_saler(){
        $this->post_saler();
    }
    
    public function post_saler(){
        global $_W;
        global $_GPC;
        $id = $_GPC['id'];
        $openid = $_W['openid'];
        $saler_info = m('store')->getSalerInfo(array('openid' => $openid));
        
        $data = array();
        if($saler_info && $saler_info['storemanager']){
            //修改(是否离职状态)
            if($id){
                isset($_GPC['status']) ? $data['status'] = $_GPC['status']: false;
                if(isset($data['status'])){
                    pdo_update("ewei_shop_saler" , $data, array('id' => $id));
                    if($data['status'] == 0){
                        //分配已绑定的客户到其他店员
                        m('store')->distributeSalerCustomer($id);
                    }
                }
                app_json();
            //新增
            } else {
                $data['uniacid'] = $_W['uniacid'];
                $data['openid'] =  $_GPC['saleropenid'];
                $data['salername'] = trim($_GPC['salername']);
                $data['mobile'] = trim($_GPC['mobile']);
                $data['roleid'] = $_GPC['roleid'];
                $data['storeid'] = $_GPC['storeid'];
                $data['status'] = isset($_GPC['status']) ? $_GPC['status'] : 0;
                if(empty($data['openid'])){
                    app_error(-1, "请选择会员");
                } else {
                    $db_saler = m('store')->getSalerInfo(array('openid' => $data['openid']));
                    if($db_saler){
                        if($db_saler['is_delete'] == 1){
                            pdo_update("ewei_shop_saler", $data, array('id' => $db_saler['id']));
                        } else {
                            app_error(-1, "该会员已经是店员");
                        }
                    } else {
                        $data['is_delete'] = 0;
                        $data['pass'] = 1;
                        $data['passtime'] = time();
                        $data['merchid'] = $saler_info['merchid'];
                        pdo_insert("ewei_shop_saler", $data);
                        //默认送券
                        $check_data = array(
                            'passtime'      => '',
                            'pass'          => 1
                        );
                        com('coupon')->checkSocialPoint($data['openid'], 'newpass', $check_data);
                        app_json();
                    }
                }                             
            }
        } else {
            app_error(-1, "你没有对应的权限");
        }
    }
    
    /**
     * 获取发货员订单列表
     */
    public function get_deliver_order_list(){
        global $_W;
        global $_GPC;
        $page = isset($_GPC['page']) ? intval($_GPC['page']) : 1;
        $psize = 10;
        $offset = ($page - 1) * $psize;
        
        $list = pdo_getall("ewei_shop_order", array("status" => 1, "addressid >" => 0, "isverify" => 0, "uniacid" => $_W['uniacid']), array(), array(), array(), "$offset, $psize");
        foreach ($list as &$order){
            $order_goods = pdo_getall("ewei_shop_order_goods", array("orderid" => $order['id'], "uniacid" => $_W['uniacid']));
            $order['order_goods'] = empty($order_goods) ? array() : $order_goods;
        }
        app_json(array("list" => $list));
    }
}