<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}

class RankModel extends PluginModel {
    
    /**
     * 获取排行数据
     * @param int $pid
     * @param int $member_type 1:门店； 2：零售商； 3：运营中心
     */
    public function get_rank_data($pid, $member_type = 1){
        switch($member_type){
            case 1:
                $salers = pdo_getall("ewei_shop_saler", array("storeid" => $pid), array('openid'), "openid");
                break;
            case 2:
                $salers = pdo_getall("ewei_shop_saler", array("merchid" => $pid), array('openid'), "openid");
                break;
            case 3:
                $merchs = array_keys(pdo_getall("ewei_shop_merch_user", array("agentid" => $pid), array('id'), "id"));   
                if($merchs){
                    $stores = array_keys(pdo_getall("ewei_shop_store" , array("merchid" => $merchs), array("id"), "id"));
                }
                if(!empty($stores)){
                    $salers = pdo_getall("ewei_shop_saler", array("storeid" => $stores), array("openid"), "openid");
                } else {
                    $salers = array();
                }
                break;
        }
        
        if(!empty($salers)){
            $salers = array_unique(array_keys($salers));
            $set = m("common")->getPluginset("rank");
            $where = " AND o.createtime >= " . strtotime($set['starttime']) . " AND o.createtime <= " . strtotime($set['endtime']);
            $list = $this->_get_sales_data($salers, $where);
            $rank = 1;
            foreach($list as $saler => &$item){              
                $saler_info = m("store")->getSalerInfo(array("openid" => $saler));
                $item['storename'] = $saler_info['storename'];
                $item['salername'] = $saler_info['salername'];
                $item['avatar'] = $saler_info['avatar'];
                $item['rolename'] = $saler_info['rolename'];
                $item['rank'] = $rank;
                $rank++;
            }
            return $list;

        }
        return array();
    }
    
    protected function _get_sales_data($saleropenid, $where){
        if(empty($saleropenid)){
            return array();
        }
        $saleropenid_str = implode(",", array_map(function($val){
            return sprintf("'%s'", $val);
        }, $saleropenid));
        $sql = "SELECT SUM(o.price) as sales, ol.saleropenid FROM " .
               tablename("ewei_shop_order") . " o " . 
               "LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id " .
               "WHERE ol.saleropenid IN($saleropenid_str) $where GROUP BY ol.saleropenid ORDER BY sales DESC";
        $result = pdo_fetchall($sql, array(), "saleropenid");
        return $result;
    }
}

?>