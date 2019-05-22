<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/mobile/agent/agentbase.php';
class Statistics_EweiShopV2Page extends Agentbase_EweiShopV2Page{
    
    public function main(){
        global $_W;
        $merchs = $this->getMerch();
        //门店数量
        $sql = "SELECT COUNT(id) AS num FROM " .
               tablename("ewei_shop_store") . " WHERE merchid IN($merchs)";
        $store_number = pdo_fetchcolumn($sql);
        $today = array(
            'start_time' => strtotime("today"),
            'end_time' => strtotime("tomorrow") - 1
        );
        //当日数据
        $today_order = $this->_order_statistics($today);
        //当月数据
        $year = date('Y');
        $month = date('m');
        $last_day = get_last_day($year, $month);
        
        $month = array(
            'start_time'    => strtotime(date("$year-$month-1 00:00:00")),
            'end_time'      => strtotime(date("$year-$month-$last_day 23:59:59"))
        );
        $month_order = $this->_order_statistics($month);
        //总数据
        $all_order = $this->_order_statistics();       
        $return = array(
            'sotre_number'      => $store_number,
            'today_order'       => $today_order,
            'month_order'       => $month_order,
            'all_order'         => $all_order,
            'benefitbalance'    => $this->account['benefitbalance']
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
                $where .= " AND o.status = 3 ";
            }
            
            if($this->is_main){
                $sql = "SELECT COUNT(o.id) as number, SUM(o.price) as total, SUM(ol.agentbenefit) as benefit  FROM " .
                       tablename("ewei_shop_order") . " o " .
                       "LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id " . 
                       "WHERE ((o.storeid IN ($storeids) OR ol.storeid IN ($storeids)) OR (o.agentid = {$_W['agentid']} OR ol.agentid = {$_W['agentid']})) $where";
            } else {
                $sql = "SELECT COUNT(o.id) as number, SUM(o.price) as total, SUM(ol.agentbenefit) as benefit  FROM " .
                       tablename("ewei_shop_order") . " o " .
                       "LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id " . 
                       "WHERE (o.storeid IN ($storeids) OR ol.storeid IN ($storeids)) $where";
            }
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
     * 年月日订单统计
     */
    public function sales_statistics(){
        global $_W;
        global $_GPC;
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
        $agentid = $_W['agentid'];
        $storeids = $this->getMerchStore();
        //店铺订单
        if($this->is_main){
            $sql = "SELECT COUNT(o.id) as number, SUM(o.price) as price, SUM(ol.agentbenefit) as benefit FROM " . tablename("ewei_shop_order") . " o " .
                   "LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id " . 
                   "WHERE ((o.storeid IN($storeids) OR ol.storeid IN($storeids)) OR (o.agentid = $agentid OR ol.agentid = $agentid)) AND o.status = 3 " .
                   "AND o.createtime >= $start_time AND o.createtime <= $end_time";
        } else {
            $sql = "SELECT COUNT(o.id) as number, SUM(o.price) as price, SUM(ol.agentbenefit) as benefit FROM " . tablename("ewei_shop_order") . " o " .
                   "LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id " . 
                   "WHERE (o.storeid IN($storeids) OR ol.storeid IN($storeids)) AND o.status = 3 " .
                   "AND o.createtime >= $start_time AND o.createtime <= $end_time";
        }
        $res = pdo_fetch($sql);
        $res['number'] = empty($res['number']) ? 0 : $res['number'];
        $res['price'] = empty($res['price']) ? 0 : $res['price'];
        $res['benefit'] = empty($res['benefit']) ? 0 : $res['benefit'];
        $res['percent'] = empty($res['price']) ? "0%" : (round($res['benefit'] / $res['price'], 4) * 100) . "%";
        return $res;
    }
}