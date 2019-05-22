<?php

define("RANK_TARGET_SALER", 1);
define("RANK_TARGET_STORE", 2);
define("RANK_TARGET_MERCH", 3);
define("RANK_TARGET_AGENT", 4);

define("RANK_TYPE_SALES", 1);
define("RANK_TYPE_MEMBER", 2);

define("RANK_RANGE_ALL", 1); //全部都能看到
define("RANK_RANGE_PROVINCE", 2); //省级运营中心
define("RANK_RANGE_MERCH", 3); // 特约零售商

class Rank_EweiShopV2ComModel extends ComModel{      
  
    /**
     * 获取排行榜数据
     * @param int $rank_id
     * @param int $pid 获取数据的用户id
     * @param int $type 获取数据的用户类型 1:导购；2：门店；3：零售商；4：代理； 5：全部
     */
    function get_data($rank_id, $pid, $type){
        $rank = pdo_get("ewei_shop_rank", array("id" => $rank_id));
        if($rank['range'] == RANK_RANGE_ALL || $rank['range_id'] == 0){
            $type = 5;
        }
        $saler_list = $this->get_saler_list($pid, $type);
        if(!empty($saler_list)){
            $saler_list_str = implode(',', array_map(function($val){
                return sprintf("'%s'", $val);
            }, $saler_list));
        } else {
            $saler_list_str = '';
        }
        $saler_sql = "SELECT s.*, if(u.agentid IS NULL, 0, u.agentid) as agentid, a.aagentprovinces, a.aagentcitys, a.aagentareas, a.aagenttype,
                     u.merchname, m.avatar, m.nickname, st.storename FROM " . tablename("ewei_shop_saler") . " s " .
                     "LEFT JOIN " . tablename("ewei_shop_merch_user") . " u ON s.merchid = u.id " . 
                     "LEFT JOIN " . tablename("ewei_shop_member") . " m ON m.openid = s.openid " .
                     "LEFT JOIN " . tablename("ewei_shop_store") . " st ON st.id = s.storeid " .
                     "LEFT JOIN " . tablename("ewei_shop_member") . " a ON a.id = u.agentid " .
                     "WHERE s.openid IN ($saler_list_str)";
        $salers = pdo_fetchall($saler_sql, array(), "openid");
        
        if($rank['goodsid']){
            $list = $this->get_goods_rank($rank, $saler_list_str);
        } else {
            //1：销售业绩；2：新增会员数
            switch($rank['data_type']){
                case 1:
                    $time_where = '';
                    if(!$rank['long_time']){
                        $time_where .= " AND o.createtime > " . $rank['starttime'] . " AND o.createtime < " . $rank['endtime'];
                    }
                    $sql = "SELECT SUM(o.price) as total, ol.saleropenid FROM " . tablename("ewei_shop_order") . " o " . 
                           "LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id " . 
                           "WHERE ol.saleropenid IN ($saler_list_str) $time_where GROUP BY ol.saleropenid ORDER BY total DESC";
                    $list = pdo_fetchall($sql, array(), "saleropenid");
                    break;
                case 2:
                    $time_where = '';
                    if(!$rank['long_time']){
                        $time_where .= " AND createtime > " . $rank['starttime'] . " AND createtime < " . $rank['endtime'];
                    }
                    $sql = "SELECT COUNT(id) as total, saleropenid FROM " . tablename("ewei_shop_coupon_relation") . 
                           " WHERE saleropenid IN ($saler_list_str) $time_where GROUP BY saleropenid ORDER BY total DESC";
                    $list = pdo_fetchall($sql, array(), "saleropenid");
                    break;
            }
        }
        
        $return_list = array();
        foreach($salers as $openid => $item){
            if(isset($list[$openid])){
                $item['total'] = $list[$openid]['total'];
            } else {
                $item['total'] = 0;
            }
            
            switch($rank['data_target']){
                case RANK_TARGET_SALER:
                    $return_list[$openid] = array(
                        'id'            => $item['openid'],
                        'name'          => $item['salername'],
                        'total'         => $item['total'],
                        'avatar'        => $item['avatar']
                    );
                    break;
                case RANK_TARGET_STORE;
                    if(isset($return_list[$item['storeid']])){
                        $return_list[$item['storeid']]['total'] += $item['total'];
                    } else {
                        $return_list[$item['storeid']] = array(
                            'id'        => $item['storeid'],
                            'name'      => $item['storename'],
                            'total'     => $item['total']
                        );
                    }
                    break;
                case RANK_TARGET_MERCH:
                    if(isset($return_list[$item['merchid']])){
                        $return_list[$item['merchid']]['total'] += $item['total'];
                    } else {
                        $return_list[$item['merchid']] = array(
                            'id'        => $item['merchid'],
                            'name'      => $item['merchname'],
                            'total'     => $item['total']
                        );
                    }
                    break;
                case RANK_TARGET_AGENT:
                    if(isset($return_list[$item['agentid']])){
                        $return_list[$item['agentid']]['total'] += $item['total'];
                    } else {
                        $name = '';
                        if($item['agentid'] && $item['aagenttype']){
                            switch($item['aagenttype']){
                                case 1:
                                    $name = implode('-', iunserializer($item['aagentprovinces']));
                                    break;
                                case 2:
                                    $name = implode('-', iunserializer($item['aagentcitys']));
                                    break;
                                case 3:
                                    $name = implode('-', iunserializer($item['aagentareas']));
                                    break;
                            }
                        }
                        $return_list[$item['agentid']] = array(
                            'id'        => $item['agentid'],
                            'name'      => $name . "运营中心",
                            'total'     => $item['total']
                        );
                    }
                    break;
            } 
        }
        
        //进行排序
        foreach($return_list as $key => $val){
            $total[$key] = $val['total'];
        }
        array_multisort($total, SORT_DESC, $return_list);
        return $return_list;
    }
    
    function get_range_list($range){
        $list = array();
        switch($range){
            case RANK_RANGE_ALL:
                break;
            case RANK_RANGE_PROVINCE:
                //获取省级代理列表
                $tmp = pdo_getall("ewei_shop_member", array("isaagent" => 1, "aagenttype" => 1, "aagentstatus" => 1));
                if(!empty($tmp)){
                    foreach($tmp as $item){
                        $province = iunserializer($item['aagentprovinces']); 
                        $list[] = array(
                            'id'    => $item['id'],
                            'name'  => $item['nickname'] . ' - ' . implode(',', $province)
                        );
                    }
                }
                break;
            case RANK_RANGE_MERCH:
                //获取零售商列表
                $tmp = pdo_getall("ewei_shop_merch_user", array("status" => 1, "accounttime >" => time()));
                if(!empty($tmp)){
                    foreach ($tmp as $item){
                        $list[] = array(
                            'id'    => $item['id'],
                            'name'  => $item['merchname']
                        );
                    }
                }
                break;
        }
        return $list;
    }
    
    public function get_saler_list($pid, $type){
        $list = array();
        switch($type){
            case 1:
                $member = m("member")->getMemberBase($pid);
                $member ? $list[] = $member['openid'] : false;
                break;
            case 2:
                $salers = m("store")->getStoreSalerOpenid($pid);
                $salers ? $list = $salers : false;
                break;
            case 3:
                $salers = pdo_getall("ewei_shop_saler", array("merchid" => $pid), array("openid"), "openid");
                $salers ? $list = array_keys($salers) : false;
                break;
            case 4:
                $merchs = pdo_getall("ewei_shop_merch_user", array("agentid" => $pid), array("id"), "id");
                if($merchs){
                    $merchs = array_keys($merchs);
                    $salers = pdo_getall("ewei_shop_saler", array("merchid" => $merchs), array("openid"), "openid");
                    $salers ? $list = array_keys($salers) : false;
                }
                break;
            case 5:
                $salers = pdo_getall("ewei_shop_saler", array(), array("*"), "openid");
                $salers ? $list = array_keys($salers) : false;
        }
               
        return $list;
    }
    
    public function get_goods_rank($rank, $saler_list_str){
        $goodsid = $rank['goodsid'];
        $time_where = '';
        if(!$rank['long_time']){
            $time_where .= " AND o.createtime > " . $rank['starttime'] . " AND o.createtime < " . $rank['endtime'];
        }
        
        $sql = "SELECT SUM(og.total) as total, ol.saleropenid FROM " . tablename("ewei_shop_order_goods") . " og " .
               "LEFT JOIN " . tablename("ewei_shop_order") . " o ON o.id = og.orderid " .
               "LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id " . 
               "WHERE ol.saleropenid IN($saler_list_str) AND og.goodsid = $goodsid $time_where GROUP BY ol.saleropenid";
        $list = pdo_fetchall($sql, array(), "saleropenid");
        return $list;
    }
}