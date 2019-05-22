<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require(__DIR__ . "/merchbase.php");
class Statistics_EweiShopV2Page extends Merchbase_EweiShopV2Page{
    
    public function main(){
        global $_W;
        global $_GPC;
        
        //门店数量
        $sql = "SELECT COUNT(id) AS num FROM " .
               tablename("ewei_shop_store") . " WHERE merchid = {$_W['merchid']}";
        $store_number = pdo_fetchcolumn($sql);
        $today = array(
            'start_time' => strtotime("today"),
            'end_time' => strtotime("tomorrow") - 1
        );
        $year = date('Y');
        $month = date('m');
        $last_day = get_last_day($year, $month);
        
        $month = array(
            'start_time'    => strtotime(date("$year-$month-1 00:00:00")),
            'end_time'      => strtotime(date("$year-$month-$last_day 23:59:59"))
        );
        $today_order = $this->_order_statistics($today);
        $month_order = $this->_order_statistics($month);
        $all_order = $this->_order_statistics();
        //可结算金额
        $sum = $this->getMerchBenefit();
        $return = array(
            'sotre_number'      => $store_number,
            'today_order'       => $today_order,
            'month_order'       => $month_order,
            'all_order'         => $all_order,
            'benefitbalance'    => $sum
        );
        app_json($return);
    }
    
    private function _order_statistics($condition){
        global $_W;
        $storeids = $this->getMerchStore();
        if($storeids){
            //订单数、成交额统计
            $where = '';
            if(isset($condition['start_time'])){
                $where .= " AND o.createtime >= {$condition['start_time']} ";
            }
            if(isset($condition['end_time'])){
                $where .= " AND o.createtime <= {$condition['end_time']} ";
            }
            if(isset($condition['status'])){
                $where .= " AND o.status = {$condition['status']}";
            } else {
                $where .= " AND o.status = 3";
            }

            $sql = "SELECT COUNT(o.id) as number, SUM(o.price) as total, SUM(ol.storebenefit) as benefit  FROM " .
                   tablename("ewei_shop_order") . " o " .
                   "LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id" . 
                   " WHERE (o.storeid IN($storeids) || ol.storeid IN($storeids)) $where";
            $data = pdo_fetch($sql);
            $data['number'] = empty($data['number']) ? 0 : $data['number'];
            $data['total']  = empty($data['total']) ? 0 : $data['total'];
            $data['benefit']  = empty($data['benefit']) ? 0 : $data['benefit'];
            return $data;
        } else {
            return array('number' => 0, 'total' => 0, 'benefit' => 0);
        }
    }
    
    /**
     * 获取客户列表
     */
    public function get_customer_list(){
        global $_W;
        global $_GPC;
        $this->check_perm("mini.statistics.customer_list");
        $merchid = $_W['merchid'];
        $page = isset($_GPC['page']) ? intval($_GPC['page']) : 1;
        $psize = 10;
        $offset = ($page - 1) * $psize;
        $saler_list = pdo_getall("ewei_shop_saler", array("merchid" => $merchid, "is_delete" => 0), array("openid"), "openid");
        $saler_list = array_keys($saler_list);
        $saler_str = implode(",", array_map(function($val){
            return sprintf("'%s'", $val);
        }, $saler_list));
        $customer_list = array();
        $total = 0;
        if(!empty($saler_list)){
            $select = "SELECT r.customeropenid, r.parentopenid, c.nickname as nickname, c.avatar as avatar, s.salername ";
            $sql = " FROM " . tablename("ewei_shop_coupon_relation") . " r " . 
                   " LEFT JOIN " . tablename("ewei_shop_member") . " c ON c.openid = r.customeropenid " . 
                   " LEFT JOIN " . tablename("ewei_shop_saler") . " s ON s.openid = r.saleropenid " .
                   " WHERE r.saleropenid IN ($saler_str) LIMIT $offset, $psize";
            $customer_list = pdo_fetchall($select . $sql);
            $total = pdo_fetchcolumn("SELECT COUNT(r.id)" . $sql);
            foreach($customer_list as &$customer){
                //购物次数
                $shop_count = pdo_getcolumn("ewei_shop_order", array("openid" => $customer['customeropenid'], "status" => 3), array("COUNT(id)"));
                //最近购物
                $last_order = pdo_getall("ewei_shop_order", array("openid" => $customer['customeropenid'], "status" => 3), 
                                         array("createtime"), "", array("createtime desc"), 1);
                $last_order = date("Y-m-d", $last_order[0]['createtime']);
                $customer['order_count'] = empty($shop_count) ? 0 : $shop_count;
                $customer['last_order'] = empty($last_order) ? 0 : $last_order;
            }
        } 
        app_json(array('list' => $customer_list, 'total' => $total));
    }
    
    /**
     * 年月日订单统计
     */
    public function sales_statistics(){
        global $_W;
        global $_GPC;
        $this->check_perm("mini.statistics.detail");
        $type = isset($_GPC['type']) ? $_GPC['type'] : 1;
        $return = null;
        //近10年
        $current_year = date('Y');
		$year = ((empty($_GPC['year']) ? $current_year : $_GPC['year']));
        
        if($type == 3){
            $years = array();
            $i = $current_year - 10;
            while ($i <= $current_year) 
            {
                $temp = array(
                    'data'          => $i, 
                    'selected'      => $i == $year,
                    'start_time'    => strtotime(date("$i-1-1 00:00:00")),
                    'end_time'      => strtotime(date("$i-12-31 23:59:59"))               
                );
                $temp['statistics'] = $this->_sales_statistics($temp['start_time'], $temp['end_time']);
                $years[] = $temp;
                ++$i;
            }
            $return = $years;
        }
        
        $current_month = date('m');
		$month = empty($_GPC['month']) ? $current_month : $_GPC['month'];
		
        if($type == 2){
            $months = array();
            $i = 1;
            while ($i <= 12) 
            {
                $last_day = get_last_day($year, $i);
                $temp = array(
                    'data'          => $i, 
                    'selected'      => $i == $month,
                    'start_time'    => strtotime(date("$year-$i-1 00:00:00")),
                    'end_time'      => strtotime(date("$year-$i-$last_day 23:59:59"))
                );
                $temp['statistics'] = $this->_sales_statistics($temp['start_time'], $temp['end_time']);
                $months[] = $temp;
                ++$i;
            }
            $return = $months;
        }
        
        $current_day = date('d');
        
        if($type == 1) {
            $days = array();
            $i = 1;
            $last_day = get_last_day($year, $month);
            while($i <= $last_day){
                $temp = array(
                    'data'          => $i,
                    'selected'      => $i == $current_day,
                    'start_time'    => strtotime(date("$year-$month-$i 00:00:00")),
                    'end_time'      => strtotime(date("$year-$month-$i 23:59:59"))
                );
                $temp['statistics'] = $this->_sales_statistics($temp['start_time'], $temp['end_time']);
                $days[] = $temp;
                ++$i;
            }
            $return = $days;
        }
        app_json(array('list' => $return));
    }
    
    public function _sales_statistics($start_time = 0, $end_time = 0){
        global $_W;
        $merchid = $_W['merchid'];
        $stores = $this->getMerchStore();
        //店铺订单
        $sql = "SELECT COUNT(o.id) as number, SUM(o.price) as price, SUM(ol.storebenefit) as benefit FROM " . tablename("ewei_shop_order") . " o " .
               "LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id " . 
               "WHERE (o.storeid IN($stores) OR o.verifystoreid IN($stores) OR ol.storeid IN($stores)) AND o.status = 3 " .
               "AND o.createtime >= $start_time AND o.createtime <= $end_time";
        $res = pdo_fetch($sql);
        $res['number'] = empty($res['number']) ? 0 : $res['number'];
        $res['price'] = empty($res['price']) ? 0 : $res['price'];
        $res['benefit'] = empty($res['benefit']) ? 0 : $res['benefit'];
        $res['percent'] = empty($res['price']) ? "0%" : (round($res['benefit'] / $res['price'], 4) * 100) . "%";
        return $res;
    }
    
    /**
     * 获取客户统计
     */
    public function get_customer_statistics(){
        global $_W;
        $merchid = $_W['merchid'];
        $salers = pdo_getall("ewei_shop_saler", array("merchid" => $merchid, "is_delete" => 0), array("openid"), "openid");
        $salers = array_keys($salers);
        if($salers){
            $salers = implode(",", array_map(function($openid){
                return sprintf("'%s'", $openid);
            }, $salers));
            $sql = "SELECT COUNT(id) FROM " . tablename("ewei_shop_coupon_relation") . 
                   " WHERE uniacid = {$_W['uniacid']} AND saleropenid IN($salers) ";
            //直接客户
            $direct = pdo_fetchcolumn($sql . " AND (parentopenid = '' OR parentopenid IS NULL )");
            //间接客户
            $indirect = pdo_fetchcolumn($sql . " AND (parentopenid <> '' AND parentopenid IS NOT NULL)");
            //所有客户
            $all = pdo_fetchcolumn($sql);
            $result = array(
                'direct'        => empty($direct) ? 0 : $direct,
                'indirect'      => empty($indirect) ? 0 : $indirect,
                'all'           => empty($all) ? 0 : $all
            );
            app_json($result);
        } else {
            app_json();
        }
    }
    
    /**
     * 获取经营天数
     */
    public function get_merch_time(){
        app_json(array('days' => $this->getMerchTime()));
    }
}
