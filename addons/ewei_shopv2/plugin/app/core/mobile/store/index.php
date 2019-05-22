<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Index_EweiShopV2Page extends AppMobilePage{
    
    /**
     * 获取门店列表
     */
    public function get_list($type = 1, $return = false){
        global $_W;
        global $_GPC;
        $search = trim($_GPC['search']);
        $lng = floatval($_GPC['longitude']);
        $lat = floatval($_GPC['latitude']);
        $type = isset($_GPC['type']) ? intval($_GPC['type']) : 0;
        
        if($type == 1){
            //默认范围10KM
            $distance = isset($_GPC['distance']) ? floatval($_GPC['distance']) : 10000;
            $range = $this->getRange($lng, $lat, $distance);

            if(!empty($lng) && !empty($lat)){
                $condition = " AND lng BETWEEN {$range['min_lng']} AND {$range['max_lng']} AND lat BETWEEN {$range['min_lat']} AND {$range['max_lat']}";
            } else {
                $condition = "";
            }
            if($search){
                $condition .= " AND (storename LIKE '%$search%' OR address LIKE '%$search%')";
            }
            $params = array(
                ':uniacid'      => $_W['uniacid']
            );
            $sql = "SELECT * FROM " . tablename('ewei_shop_store') . " WHERE uniacid = :uniacid AND status = 1" . $condition;
            $list = pdo_fetchall($sql, $params);
        //所有门店
        } else if($type == 2) {
            $condition = '';
            if($search){
                $condition = " AND (s.storename LIKE '%$search%' OR s.address LIKE '%$search%')";
            }
            //服务过我的门店
            $sql = "(SELECT s.* FROM " . tablename("ewei_shop_order") . " o " . 
                   "INNER JOIN " . tablename("ewei_shop_store") . " s on s.id = o.storeid " .
                   "WHERE o.uniacid = {$_W['uniacid']} AND o.openid = '{$_W['openid']}' AND o.status = 3 AND s.status = 1 $condition) " . 
                   "UNION " . 
                   "(SELECT s.* FROM " . tablename("ewei_shop_order") . " o " . 
                   "INNER JOIN " . tablename("ewei_shop_store") . " s on s.id = o.verifystoreid " .
                   "WHERE o.uniacid = {$_W['uniacid']} AND o.openid = '{$_W['openid']}' AND o.status = 3 AND s.status = 1 $condition) " ;
            $list = pdo_fetchall($sql); 
        } else {
            $condition = "";
            $params = array(
                ':uniacid'      => $_W['uniacid']
            );
            if($search){
                $condition .= " AND (storename LIKE '%$search%' OR address LIKE '%$search%')";
            }
            $sql = "SELECT * FROM " . tablename('ewei_shop_store') . " WHERE uniacid = :uniacid AND status = 1 $condition";
            $list = pdo_fetchall($sql, $params);
        }
        $distance_array = array();
        foreach($list as $key => &$store){
            if(empty($store['lng']) && empty($store['lat'])){
                $store['distance'] = 99999;
            }
            else if($lng && $lat){
                $store['distance'] = $this->getDistance($lng, $lat, $store['lng'], $store['lat']);
            } else {
                $store['distance'] = 0;
            }
            $distance_array[$key] = $store['distance'];
        }
        array_multisort($distance_array, SORT_ASC, $list);
        if($return){
            return $list;
        }
        app_json(array('list' => $list));
    }
    
    public function get_saler_range(){
        global $_W;
        global $_GPC;
        $type = $_GPC['type']; //1:附近导购；2：服务我的导购
        $result = array();
        switch ($type){
            case 1:
                $store_list = $this->get_list(1, true);
                $saler_list = array();
                foreach($store_list as &$store){
                    $saler = m('store')->getSalerInfo(array('storeid' => $store['id']));
                    if($saler){
                        $saler_list[] = $saler;
                    }
                }
                $result['list'] = $saler_list;
                break;
            case 2:
                //我的导购
                $sql = "SELECT saleropenid FROM " . tablename("ewei_shop_coupon_relation") .
                       "WHERE customeropenid = '{$_W['openid']}' ";
                $my_saler = pdo_fetchcolumn($sql);
                if($my_saler){
                    $my_saler_condition = " AND saleropenid <> '$my_saler'";
                } else {
                    $my_saler_condition = "";
                }
                //导购openid
                $sql = "SELECT saleropenid, COUNT(id) as number FROM " . tablename("ewei_shop_order") .
                       " WHERE openid = :openid AND saleropenid IS NOT NULL $my_saler_condition AND uniacid = :uniacid 
                         GROUP BY saleropenid";
                $salers = pdo_fetchall($sql, array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
                foreach($salers as &$saler){
                    if($saler['saleropenid']){
                        $saler_info = m('store')->getSalerInfo(array('openid' => $saler['saleropenid']));
                        $saler['avatar'] = $saler_info['avatar'];
                        $saler['salername'] = $saler_info['salername'];
                        $saler['mobile'] = $saler_info['mobile'];
                        $saler['storename'] = $saler_info['storename'];
                        $saler['store_address'] = $saler_info['address'];
                        $saler['storeid'] = $saler_info['storeid'];
                        $saler['merchid'] = $saler_info['merchid'];
                    }
                }
                $result['list'] = $salers;
                break;
        }
        app_json($result);
    }
    
    /**
     * 计算某个经纬度的周围某段距离的正方形的四个点
     * @param lng float 经度
     * @param lat float 纬度      
     * @param distance float 该点所在圆的半径，该圆与此正方形内切，默认值为1千米      
     * @param radius 地球半径 平均6371km
     * @return array 正方形的四个点的经纬度坐标
     */
    public function getRange($lng, $lat, $distance = 1, $radius = 6371)
    {
        $dlng = 2 * asin(sin($distance / (2 * $radius)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);
        
        $dlat = $distance / $radius;
        $dlat = rad2deg($dlat);
        
        return array(
            'min_lng'   => $lng - $dlng,
            'max_lng'   => $lng + $dlng,
            'min_lat'   => $lat - $dlat,
            'max_lat'   => $lat + $dlat 
        );
    }       
    
    /**
    *  计算两组经纬度坐标 之间的距离
    *   params ：lat1 纬度1； lng1 经度1； lat2 纬度2； lng2 经度2； len_type （1:km or 2:m);
    *   return m or km
    */
    public function getDistance($lng1, $lat1, $lng2, $lat2, $len_type = 1, $decimal = 2){
        $radLat1 = $lat1 * PI()/ 180.0;   //PI()圆周率
        $radLat2 = $lat2 * PI() / 180.0;
        $a = $radLat1 - $radLat2;
        $b = ($lng1 * PI() / 180.0) - ($lng2 * PI() / 180.0);
        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $s = $s * 6378.137;
        $s = round($s * 1000);
        if ($len_type == 1)
        {
            $s /= 1000;
        }
        return round($s, $decimal);
    }
    
    public function query(){
        global $_W;
		global $_GPC;
		$kwd = trim($_GPC['keyword']);
		$limittype = empty($_GPC['limittype']) ? 0 : intval($_GPC['limittype']);
		$params = array();
		$params[':uniacid'] = $_W['uniacid'];
		$condition = ' and uniacid=:uniacid and merchid =0  and status=1 ';

		if ($limittype == 0) {
			$condition .= '  and type in (1,2,3) ';
		}

		if (!empty($kwd)) {
			$condition .= ' AND `storename` LIKE :keyword';
			$params[':keyword'] = '%' . $kwd . '%';
		}
		$list = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_store') . (' WHERE 1 ' . $condition . ' order by id asc'), $params);
        app_json(array('list' => $list));
    }
    
    public function get_role(){
        global $_W;
        $openid = $_W['openid'];
        $saler_info = m('store')->getSalerInfo(array('openid' => $openid));
        $merchid = $saler_info['merchid'];
        $roles = pdo_getall("ewei_shop_saler_role", array('merchid' => $merchid, 'status' => 1));
        app_json(array('list' => $roles));
    }
	
	public function get_detail(){
        global $_W;
        global $_GPC;
        $storeid = $_GPC['id'];
        $store_detail = pdo_getall("ewei_shop_store", array('id' => $storeid, 'uniacid' => $_W['uniacid']));
        if($store_detail[0]){
            app_json(array('detail' => $store_detail[0]));
        }else{
            app_error(-1, "该门店不存在");
        }
    }
}