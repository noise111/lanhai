<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}
class Store_EweiShopV2Model
{
	public function getStoreInfo($id)
	{
		global $_W;
		return pdo_fetch('select * from ' . tablename('ewei_shop_store') . ' where id=:id and uniacid=:uniacid Limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
	}

	public function getGoodsInfo($id)
	{
		global $_W;
		$sql = 'select * from ' . tablename('ewei_shop_goods') . ' where id=:id and uniacid=:uniacid Limit 1';
		return pdo_fetch($sql, array(':id' => $id, ':uniacid' => $_W['uniacid']));
	}

	public function getStoreGoodsInfo($goodsid, $storeid, $flag = 0)
	{
		global $_W;

		if (empty($flag)) {
			$con = ' and gstatus=1';
		}
		else {
			$con = '';
		}

		$sql = 'select * from ' . tablename('ewei_shop_newstore_goods') . ' where goodsid=:goodsid and storeid=:storeid and uniacid=:uniacid ' . $con . ' Limit 1';
		return pdo_fetch($sql, array(':goodsid' => $goodsid, ':storeid' => $storeid, ':uniacid' => $_W['uniacid']));
	}

	public function getStoreGoodsOption($goodsid, $storeid)
	{
		global $_W;
		$sql = 'select * from ' . tablename('ewei_shop_newstore_goods_option') . ' where goodsid=:goodsid and storeid=:storeid and uniacid=:uniacid';
		return pdo_fetchall($sql, array(':goodsid' => $goodsid, ':storeid' => $storeid, ':uniacid' => $_W['uniacid']));
	}

	public function getOneStoreGoodsOption($optionid, $goodsid, $storeid)
	{
		global $_W;
		$sql = 'select * from ' . tablename('ewei_shop_newstore_goods_option') . ' where goodsid=:goodsid and storeid=:storeid and optionid=:optionid and uniacid=:uniacid Limit 1';
		return pdo_fetch($sql, array(':goodsid' => $goodsid, ':storeid' => $storeid, ':optionid' => $optionid, ':uniacid' => $_W['uniacid']));
	}

	public function getAllStore()
	{
		global $_W;
		$uniacid = $_W['uniacid'];
		$sql = 'select * from ' . tablename('ewei_shop_store') . ' where uniacid=:uniacid';
		return pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
	}

	public function checkStoreid()
	{
		global $_W;
		global $_GPC;
		$newstoreid = intval($_SESSION['newstoreid']);

		if (empty($newstoreid)) {
			$newstoreid = intval($_GPC['storeid']);

			if (!empty($newstoreid)) {
				$_SESSION['newstoreid'] = $newstoreid;
			}
		}

		return $newstoreid;
	}

	public function getStoreName($list, $return = 'all')
	{
		global $_W;

		if (!is_array($list)) {
			return $this->getListUserOne($list);
		}

		$store = array();

		foreach ($list as $value) {
			$storeid = $value['storeid'];

			if (empty($storeid)) {
				$storeid = 0;
			}

			if (empty($store[$storeid])) {
				$store[$storeid] = array();
			}

			array_push($store[$storeid], $value);
		}

		if (!empty($store)) {
			$store_ids = array_keys($store);
			$store_list = pdo_fetchall('select * from ' . tablename('ewei_shop_store') . ' where uniacid=:uniacid and id in(' . implode(',', $store_ids) . ')', array(':uniacid' => $_W['uniacid']), 'id');
			$all = array('store' => $store, 'store_list' => $store_list);
			return $return == 'all' ? $all : $all[$return];
		}

		return array();
	}

	public function getListStoreOne($storeid)
	{
		global $_W;
		$storeid = intval($storeid);

		if ($storeid) {
			$store = pdo_fetch('select * from ' . tablename('ewei_shop_store') . (' where uniacid=:uniacid and id=' . $storeid), array(':uniacid' => $_W['uniacid']));
			return $store;
		}

		return false;
	}
    
    public function getSalerInfo($condition = array()){
        global $_W;
        global $_GPC;      
        $filter = "";
        if(!empty($condition)){
            foreach ($condition as $key => $val){
                $filter .= " AND s.{$key} = '{$val}'";
            }
        } else {
            $filter = " AND s.openid = '{$_W['openid']}' AND s.uniacid = {$_W['uniacid']}";
        }
        $sql = " SELECT s.*, m.avatar, m.nickname, 
                 r.rolename, r.status AS role_status, r.shoppingguide, r.storemanager, r.verify, r.deliver, 
                 st.storename, st.address, st.province, st.city, st.area 
                 FROM " . tablename('ewei_shop_saler') . " s " .
               " LEFT JOIN " . tablename('ewei_shop_saler_role') . " r ON r.id = s.roleid " . 
               " INNER JOIN " . tablename('ewei_shop_member') . " m ON m.openid = s.openid " .
               " LEFT JOIN " . tablename("ewei_shop_store") . " st ON s.storeid = st.id " .
               " WHERE (r.shoppingguide = 1 OR r.storemanager = 1) AND s.is_delete = 0 " . $filter;
        $saler = pdo_fetch($sql);
        return $saler;
    }
    
    /**
     * 分配某导购的客户到其他本门店的导购中
     */
    public function distributeSalerCustomer($openid){
        global $_W;
        $id = intval($openid);
        if($id > 0){
            $saler_info = $this->getSalerInfo(array('id' => $id));
        } else {
            $saler_info = $this->getSalerInfo(array('openid' => $openid));
        }
        //if($saler_info['status'] == 0){
        if(true){
            $sql = "SELECT * FROM " . tablename("ewei_shop_saler") .
                    " WHERE storeid = {$saler_info['storeid']} AND id <> {$saler_info['id']} AND pass = 1 AND status = 1 ORDER BY passtime ASC";
            $salers = pdo_fetchall($sql);
            if($salers){
                $customers = pdo_getall("ewei_shop_coupon_relation", array("saleropenid" => $saler_info['openid'], "uniacid" => $_W['uniacid']), array("id", "customeropenid"));
                $saler_count = count($salers);
                $customer_count = count($customers);
                if($saler_count > $customer_count){
                    foreach($customers as $key => $customer){
                        pdo_update("ewei_shop_coupon_relation", array("saleropenid" => $salers[$key]['openid']), array("id" => $customer['id']));
                    }
                } else {
                    //每人分得的数量
                    $per = floor($customer_count / $saler_count);
                    //余数
                    $mod = $customer_count % $saler_count;
                    $first_saler_num = $per + $mod;
                    foreach($salers as $skey => $saler){
                        if($skey == 0){
                            //客户数组开始位置
                            $start = 0;
                            //客户数组结束位置
                            $end = $first_saler_num - 1;
                        } else {
                            $start = $first_saler_num + ($skey - 1) * $per - 1;
                            $end = $first_saler_num + $skey * $per - 1;
                        }
                        
                        for($i = $start; $i <= $end; $i++){
                            pdo_update("ewei_shop_coupon_relation", array('saleropenid' => $saler['openid']), array('id' => $customers[$i]['id']));
                        }
                    }
                }
            }
        }
    }
    
    /*
     * 获取门店所有导购的openid
     */
    public function getStoreSalerOpenid($storeid, $condition = '', $type = 1){
        if(!empty($condition)){
            $condition = " AND " . $condition;
        }
        $sql = "SELECT openid FROM " . tablename("ewei_shop_saler") . 
               " WHERE storeid = $storeid $condition";
        $salers = pdo_fetchall($sql, array(), 'openid');
        $salers = array_keys($salers);
        if($type == 2){
            $salers = implode(',', array_map(function($str){
                return sprintf("'%s'", $str);
            }, $salers));
        }
        return $salers;
    }
    
    public function getStoreStatus($saler_openid = '', $storeid = 0){
        if(empty($storeid) && !empty($saler_openid)){
            $saler = pdo_get("ewei_shop_saler", array("openid" => $saler_openid));
            if($saler && !empty($saler['storeid'])){
                $storeid = $saler['storeid'];
            }
        }
        $store = pdo_get("ewei_shop_store", array("id" => $storeid));
        return $store ? $store['status'] : 0;
    }
    
    /**
     * 删除导购关系（普通账号添加作为店铺导购时把原有账号的与其他导购的关系删除）
     */
    public function deleteSalerRelation($saler_openid){
        $saler = $this->getSalerInfo(array("openid" => $saler_openid));
        if($saler && $saler['status'] == 1 && $saler['is_delete'] == 0){
            pdo_delete("ewei_shop_coupon_relation", array("customeropenid" => $saler_openid));
        }
    }
    
    public function get_full_saler_info($openid){
        $saler_sql = "SELECT s.*, if(u.agentid IS NULL, 0, u.agentid) as agentid, a.aagentprovinces, a.aagentcitys, a.aagentareas, a.aagenttype,
                     u.merchname, m.avatar, m.nickname, st.storename FROM " . tablename("ewei_shop_saler") . " s " .
                     "LEFT JOIN " . tablename("ewei_shop_merch_user") . " u ON s.merchid = u.id " . 
                     "LEFT JOIN " . tablename("ewei_shop_member") . " m ON m.openid = s.openid " .
                     "LEFT JOIN " . tablename("ewei_shop_store") . " st ON st.id = s.storeid " .
                     "LEFT JOIN " . tablename("ewei_shop_member") . " a ON a.id = u.agentid " .
                     "WHERE s.openid = '$openid'";
        $saler = pdo_fetch($saler_sql);
        if($saler){
            $saler['aagentprovinces']   = iunserializer($saler['aagentprovinces']);
            $saler['aagentcitys']       = iunserializer($saler['aagentcitys']);
            $saler['aagentareas']       = iunserializer($saler['aagentareas']);
        }
        return $saler;
        
    }
}

?>
