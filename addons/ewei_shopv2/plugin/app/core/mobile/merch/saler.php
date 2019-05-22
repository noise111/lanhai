<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require(__DIR__ . "/merchbase.php");
class Saler_EweiShopV2Page extends Merchbase_EweiShopV2Page{
    
    /**
     * 获取零售商店员列表
     */
    public function get_list(){
        global $_W;
        $this->check_perm("mini.saler.list");
        $uniacid = $_W['uniacid'];
        $merchid = $_W['merchid'];
        $saler_list = pdo_getall("ewei_shop_saler", array(
            "merchid"       => $merchid,
            "uniacid"       => $uniacid,
            "is_delete"     => 0
        ));
        foreach($saler_list as &$saler){
            //客户数
            $sql = "SELECT COUNT(id) FROM " . tablename("ewei_shop_coupon_relation") . 
                   " WHERE  saleropenid = '{$saler['openid']}'";
            $customer_count = pdo_fetchcolumn($sql);
            //业绩额
            $sql = "SELECT SUM(price) FROM " . tablename("ewei_shop_order") . 
                   " WHERE status = 3 AND saleropenid = '{$saler['openid']}'";
            $performance = pdo_fetchcolumn($sql);
            //最近成交
            $sql = "SELECT createtime FRIM " . tablename("ewei_shop_order") . 
                   " WHERE status = 3 AND saleropenid = '{$saler['openid']}' ORDER BY createtime DESC LIMIT 1";
            $last_order = pdo_fetchcolumn($sql);
            if($last_order){
                $last_order = date("Y-m-d", $last_order);
            } else {
                $last_order = 0;
            }
            
            $saler_info = m("store")->getSalerInfo(array("openid" => $saler['openid']));
            if($saler_info){
                $saler['avatar'] = $saler_info['avatar'];
            } else {
                $saler['avatar'] = "";
            }
            $saler['storename'] = $saler_info['storename'];
            $saler['customer_number'] = empty($customer_count) ? 0 : $customer_count;
            $saler['all_performance'] = empty($performance) ? 0 : $performance;
            $saler['last_order'] = $last_order;
        }
        app_json(array("list" => $saler_list));
    }
    
    public function get_detail(){
        global $_W;
        global $_GPC;
        $this->check_perm("mini.saler.detail");
        $merchid = $_W['merchid'];
        $openid = $_GPC['saleropenid'];
        $saler_detail = m("store")->getSalerInfo(array("openid" => $openid));
        if($saler_detail){
            app_json(array('detail' => $saler_detail));
        }else{
            app_error(-1, "该店员不存在");
        }
    }

    public function add_saler(){
        $this->check_perm("mini.saler.add");
        $this->post_saler();
    }
    
    public function edit_saler(){
        $this->check_perm("mini.saler.edit");
        $this->post_saler();
    }
    
    public function post_saler(){
        global $_W;
        global $_GPC;
        $merchid = $_W['merchid'];
        $id = $_GPC['id'];
        //修改(是否离职状态)
        if($id){
            isset($_GPC['status']) ? $data['status'] = $_GPC['status']: false;
            isset($_GPC['openid']) ? $data['openid'] = $_GPC['saleropenid']: false;
            isset($_GPC['salername']) ? $data['salername'] = $_GPC['salername']: false;
            isset($_GPC['mobile']) ? $data['mobile'] = $_GPC['mobile']: false;
            isset($_GPC['roleid']) ? $data['roleid'] = $_GPC['roleid']: false;
            isset($_GPC['storeid']) ? $data['storeid'] = $_GPC['storeid']: false;

            if(isset($data['status'])){
                pdo_update("ewei_shop_saler" , $data, array('id' => $id, 'merchid' => $merchid));
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
                    $data['merchid'] = $merchid;
                    pdo_insert("ewei_shop_saler", $data);
                    //默认送券
                    $check_data = array(
                        'passtime'      => '',
                        'pass'          => 1
                    );
                    com('coupon')->checkSocialPoint($data['openid'], 'newpass', $check_data);
                    m("store")->deleteSalerRelation($data['openid']);
                    app_json();
                }
            }                             
        }
    }
    
}
