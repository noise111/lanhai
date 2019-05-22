<?php

if (!defined('IN_IA')) {
	exit('Access Denied');
}

require(__DIR__ . "/merchbase.php");
class Store_EweiShopV2Page extends Merchbase_EweiShopV2Page{
    
    public function get_list(){
        global $_W;
        $this->check_perm("mini.store.list");
        $merchid = $_W['merchid'];
        $store_list = pdo_getall("ewei_shop_store", array('merchid' => $merchid, 'uniacid' => $_W['uniacid']));
        foreach($store_list as &$store){
            //店长
            $sql = "SELECT s.* FROM " . tablename("ewei_shop_saler") . " s " .
                   " LEFT JOIN " . tablename("ewei_shop_saler_role") . " r on r.id = s.roleid " .
                   " WHERE s.merchid = $merchid AND r.merchid = $merchid AND r.storemanager = 1 AND s.status = 1 AND s.storeid = {$store['id']}";
            $managers = pdo_fetchall($sql);
            $store['manager_name'] = $managers[0]['salername'];
            $store['manager_mobile'] = $managers[0]['mobile'];
            $statistics = $this->get_store_statistics($store['id']);
            $store = array_merge($store, $statistics);
            $store = set_medias($store, "logo");
        }
        app_json(array('list' => $store_list));
    }

    public function get_detail(){
        global $_W;
        global $_GPC;
        $this->check_perm("mini.store.detail");
        $merchid = $_W['merchid'];
        $storeid = $_GPC['id'];
        $store_detail = pdo_getall("ewei_shop_store", array('id' => $storeid, 'uniacid' => $_W['uniacid']));
        if($store_detail[0]){
            $logo = $store_detail[0]['logo'];
            $store_detail = set_medias($store_detail, "logo");
            $store_detail[0]['image'] = $store_detail[0]['logo'];
            $store_detail[0]['logo'] = $logo;
            app_json(array('detail' => $store_detail[0]));
        }else{
            app_error(-1, "该门店不存在");
        }
    }
    
    /**
     * 获取店铺统计数据
     * @param int $storeid 店铺id
     */
    public function get_store_statistics($storeid){
        global $_W;
        $merchid = $_W['merchid'];
        $uniacid = $_W['uniacid'];
        //店员数
        $saler_count = pdo_getcolumn("ewei_shop_saler", array(
            "merchid"   => $merchid, 
            "status"    => 1, 
            "uniacid"   => $uniacid, 
            "storeid"   => $storeid
        ), array("COUNT(id) as number"));
        //客户数
        $sql = "SELECT COUNT(r.id) FROM " . tablename("ewei_shop_coupon_relation") . " r " .
               "INNER JOIN " . tablename("ewei_shop_saler") . " s on s.openid = r.saleropenid " . 
               "WHERE s.merchid = $merchid AND s.uniacid = $uniacid AND s.is_delete = 0 AND s.storeid = $storeid";
        $customer_count = pdo_fetchcolumn($sql);
        //销售额
        $sql = " FROM " . tablename("ewei_shop_order") . " o " . 
               " LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id " .
               " WHERE (o.storeid = $storeid OR o.verifystoreid = $storeid OR ol.storeid = $storeid)";
        $sale_count = pdo_fetchcolumn("SELECT SUM(o.price)" . $sql);
        //业绩额
        $performance_count = pdo_fetchcolumn("SELECT SUM(o.goodsprice)" . $sql);
        $return = array(
            'saler_count'           => empty($saler_count) ? 0 : $saler_count,
            'customer_count'        => empty($customer_count) ? 0 : $customer_count,
            'sale_count'            => empty($sale_count) ? 0 : $sale_count,
            'performance_count'     => empty($performance_count) ? 0 : $performance_count,
        );
        return $return;
    }
    
    /**
     * 获取零售商门店，店员统计
     */
    public function get_merch_staff_statistics($return = false){
        global $_W;
        $merchid = $_W['merchid'];
        $uniacid = $_W['uniacid'];
        //店铺统计
        $store_count = pdo_getcolumn("ewei_shop_store", array("merchid" => $merchid, "uniacid" => $uniacid, "status" => 1), array("SELECT COUNT(id)"));
        //店员统计
        $saler_count = pdo_getcolumn("ewei_shop_saler", array("merchid" => $merchid, "uniacid" => $uniacid, "is_delete" => 0), array("SELECT COUNT(id)"));
        $result = array(
            'store_count'           => empty($store_count) ? 0 : $store_count,
            'saler_count'           => empty($saler_count) ? 0 : $saler_count
        );
        if($return){
            return $result;
        }
        app_json($result);
    }
    
    public function add_store(){
        $this->check_perm("mini.store.add");
        $this->post_store();
    }
    
    public function edit_store(){
        $this->check_perm("mini.store.edit");
        $this->post_store();
    }
    
    public function post_store(){
        global $_W;
        global $_GPC;
        $uniacid = $_W['uniacid'];
        $merchid = $_W['merchid'];
        $id = $_GPC['id'];
        $data = array();
        if($id){
            isset($_GPC['storename']) ? $data['storename'] = trim($_GPC['storename']) : false;
            isset($_GPC['logo']) ?      $data['logo'] = trim($_GPC['logo']) : false;
            isset($_GPC['province']) ?  $data['province'] = $_GPC['province'] : false;
            isset($_GPC['city']) ?      $data['city'] = $_GPC['city'] : false;
            isset($_GPC['area']) ?      $data['area'] = $_GPC['area'] : false;
            isset($_GPC['provincecode'])?$data['provincecode'] = $_GPC['provincecode'] : false;
            isset($_GPC['citycode']) ?  $data['citycode'] = $_GPC['citycode'] : false;
            isset($_GPC['areacode']) ?  $data['areacode'] = $_GPC['areacode'] : false;
            isset($_GPC['address']) ?   $data['address'] = trim($_GPC['address']) : false;
            isset($_GPC['tel']) ?       $data['tel'] = trim($_GPC['tel']) : false;
            isset($_GPC['saletime']) ?  $data['saletime'] = trim($_GPC['saletime']) : false;
            isset($_GPC['lat']) ?       $data['lat'] = $_GPC['lat'] : false;
            isset($_GPC['lng']) ?       $data['lng'] = $_GPC['lng'] : false;
            isset($_GPC['type']) ?      $data['type'] = $_GPC['type'] : false;
            isset($_GPC['status']) ?    $data['status'] = $_GPC['status'] : false;
            isset($_GPC['realname']) ?  $data['realname'] = trim($_GPC['realname']) : false;
            isset($_GPC['mobile']) ?    $data['mobile'] = trim($_GPC['mobile']) : false;
            isset($_GPC['fetchtime']) ? $data['fetchtime'] = trim($_GPC['fetchtime']) : false;
            isset($_GPC['desc']) ?      $data['desc'] = trim($_GPC['desc']) : false;
            
            if($data['storename']){
                $store = pdo_get("ewei_shop_store", array(
                    "uniacid"       => $uniacid,
                    "merchid"       => $merchid,
                    "storename"     => $data['storename']
                ));
                if($store && $store['id'] != $id){
                    app_error(0, "该门店名称已存在");
                }
            }
            pdo_update("ewei_shop_store", $data, array("id" => $id, "merchid" => $merchid));
            if(isset($data['status'])){
                pdo_update("ewei_shop_saler", array("status" => $data['status']), array("storeid" => $id));
            }
            app_json();
        } else {
            $data['storename'] = trim($_GPC['storename']);
            $data['logo'] = trim($_GPC['logo']);
           
            $data['province'] = trim($_GPC['province']);
            $data['city'] = trim($_GPC['city']);
            $data['area'] = trim($_GPC['area']);
            $data['provincecode'] = trim($_GPC['provincecode']);
            $data['citycode'] = trim($_GPC['citycode']);
            $data['areacode'] = trim($_GPC['areacode']);
            $data['address'] = trim($_GPC['address']);

            $data['tel'] = trim($_GPC['tel']);
            $data['saletime'] = trim($_GPC['saletime']);
            
            $data['lat'] = $_GPC['lat'];
            $data['lng'] = $_GPC['lng'];
            $data['type'] = $_GPC['type'];
            $data['status'] = $_GPC['status'];
            
            isset($_GPC['realname']) ?    $data['realname'] = trim($_GPC['realname']) : false;
            isset($_GPC['mobile']) ?    $data['mobile'] = trim($_GPC['mobile']) : false;
            isset($_GPC['fetchtime']) ?  $data['fetchtime'] = trim($_GPC['fetchtime']) : false;
            isset($_GPC['desc']) ?      $data['desc'] = trim($_GPC['desc']) : false;
            
            $data['uniacid'] = $uniacid;
            $data['merchid'] = $merchid;
            if(!empty($data['storename']) && !empty($data['lat']) && !empty($data['lng'])){
                $res = pdo_insert("ewei_shop_store", $data);
                if($res){
                    app_json();
                } else {
                    app_error(0, "添加店铺失败！");
                }
            } else {
                app_error(0, "请填写必要的信息");
            }
        }
    }
    
    public function query(){
        global $_W;
		global $_GPC;
        $merchid = $_W['merchid'];
		$kwd = trim($_GPC['keyword']);
		$limittype = empty($_GPC['limittype']) ? 0 : intval($_GPC['limittype']);
		$params = array();
		$params[':uniacid'] = $_W['uniacid'];
		$condition = " and uniacid=:uniacid and merchid =$merchid  and status=1 ";

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
        $merchid = $_W['merchid'];
        $roles = pdo_getall("ewei_shop_saler_role", array('merchid' => $merchid, 'status' => 1));
        app_json(array('list' => $roles));
    }
}
