<?php

if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/mobile/agent/agentbase.php';
class Merch_EweiShopV2Page extends Agentbase_EweiShopV2Page{
    public function get_list(){
        global $_W;

        $groups = pdo_fetchall("select id,groupname from " . tablename("ewei_shop_merch_group") . " where uniacid=:uniacid order by isdefault desc , id asc", array( ":uniacid" => $_W["uniacid"] ), "id");
        $cates = pdo_fetchall("select id,catename from " . tablename("ewei_shop_merch_category") . " where uniacid=:uniacid ", array( ":uniacid" => $_W["uniacid"] ), "id");

        $list = pdo_fetchall("select * from " . tablename("ewei_shop_merch_user") . " where uniacid=:uniacid and agentid=:agentid order by  id desc",
                array("agentid" => $_W['agentid'], "uniacid" => $_W['uniacid']));
        foreach($list as &$merch){
            //分组
            if($groups[$merch['groupid']]['groupname']){
                $merch['groupname'] = $groups[$merch['groupid']]['groupname'];
            }
            
            //分类
            if($cates[$merch['cateid']]['catename']){
                $merch['catename'] = $cates[$merch['cateid']]['catename'];
            }

            //到期时间
            $merch['accounttime'] = date('Y-m-d',$merch['accounttime']);

            //门店数
            $store_number = pdo_getcolumn("ewei_shop_store", 
                    array("merchid" => $merch['id'], "status" => 1), array("COUNT(id)"));
            $merch['store_number'] = empty($store_number) ? 0 : $store_number;
            //客户数
            $sql = "SELECT COUNT(r.id) FROM " . tablename("ewei_shop_coupon_relation") . " r " .
                   "INNER JOIN " . tablename("ewei_shop_saler") . " s ON s.openid = r.saleropenid " .
                   "WHERE s.merchid = {$merch['id']}";
            $customer_number = pdo_fetchcolumn($sql);
            $merch['customer_number'] = empty($customer_number) ? 0 : $customer_number;
            //销售额、业绩额
            $merch_stores = $this->getMerchStore($merch['id']);
            $sql = "SELECT COUNT(o.id) as number, SUM(o.price) as total, SUM(ol.storebenefit) as benefit  FROM " .
                   tablename("ewei_shop_order") . " o " .
                   "LEFT JOIN " . tablename("ewei_shop_order_benefit_log") . " ol ON ol.orderid = o.id" . 
                   " WHERE o.status = 3 AND (o.storeid IN($merch_stores) || ol.storeid IN($merch_stores))";
            $merch_stores_statistics = pdo_fetch($sql);     
            $merch['sales_performance'] = empty($merch_stores_statistics['total']) ? 0 : $merch_stores_statistics['total'];
            $merch['bonus_performance'] = empty($merch_stores_statistics['benefit']) ? 0 : $merch_stores_statistics['benefit'];
            $merch = set_medias($merch, "logo");
            
        }
        app_json(array('list' =>$list));
    }
    
    public function get_detail(){
        global $_W;
        global $_GPC;

        $groups = pdo_fetchall("select id,groupname from " . tablename("ewei_shop_merch_group") . " where uniacid=:uniacid order by isdefault desc , id asc", array( ":uniacid" => $_W["uniacid"] ), "id");
        $id = intval($_GPC["id"]);

        $merch_detail = pdo_fetch("select * from " . tablename("ewei_shop_merch_user") . " where id=:id and uniacid=:uniacid limit 1", array( ":id" => $id, ":uniacid" => $_W["uniacid"] ));
        $account = pdo_fetch("select * from " . tablename("ewei_shop_merch_account") . " where id=:id and uniacid=:uniacid limit 1", array( ":id" => $merch_detail["accountid"], ":uniacid" => $_W["uniacid"] ));
        if($merch_detail){
            if(!(empty($account)) ) 
            {
                $merch_detail['username'] = $account['username'];
            }
            $logo = $merch_detail['logo'];
            $merch_detail = set_medias($merch_detail, "logo");
            $merch_detail['image'] = $merch_detail['logo'];
            $merch_detail['logo'] = $logo;
            $merch_detail['accounttime'] = date('Y-m-d', $merch_detail['accounttime']);
            $member = m("member")->getMember($merch_detail["openid"]);
            $merch_detail['openid_text'] = $member['nickname'];
            app_json(array('detail' => $merch_detail));
        }else{
            app_error(-1, "该特约零售商不存在");
        }

        app_json(array('detail' => $merch_detail));
    }

    public function add_merch(){
        $this->post_merch();
    }
    
    public function edit_merch(){
        $this->post_merch();
    }
    
    public function post_merch(){
        global $_W;
        global $_GPC;
        
        $id = $_GPC['id'];
        $data = array();
        if($id){
            isset($_GPC['merchname']) ?         $data['merchname'] = trim($_GPC['merchname']) : false;
            isset($_GPC['logo']) ?              $data['logo'] = trim($_GPC['logo']) : false;
            isset($_GPC['salecate']) ?          $data['salecate'] = trim($_GPC['salecate']) : false;
            isset($_GPC['cateid']) ?            $data['cateid'] = trim($_GPC['cateid']) : false;
            isset($_GPC['realname']) ?          $data['realname'] = trim($_GPC['realname']) : false;
            isset($_GPC['mobile']) ?            $data['mobile'] = trim($_GPC['mobile']) : false;
            isset($_GPC['address']) ?           $data['address'] = trim($_GPC['address']) : false;
            isset($_GPC['lat']) ?               $data['lat'] = trim($_GPC['lat']) : false;
            isset($_GPC['lng']) ?               $data['lng'] = trim($_GPC['lng']) : false;
            
            isset($_GPC['status']) ?            $data['status'] = $_GPC['status'] : false;
            isset($_GPC['remark']) ?            $data['remark'] = $_GPC['remark'] : false;
            isset($_GPC['accounttime']) ?       $data['accounttime'] = strtotime($_GPC['accounttime']) : false;
            isset($_GPC['openid']) ?            $data['openid'] = trim($_GPC['merch_openid']) : false;
            isset($_GPC['accountid']) ?         $data['accountid'] = trim($_GPC['accountid']) : false;

            $account = array();
            $account['username']  = trim($_GPC['username']);
            if($_GPC['pwd'] && $_GPC['pwd']!=''){
                $salt = random(8);
                $account['salt'] = $salt;
                $account['pwd']  = md5($_GPC['pwd'] . $salt);
            }

            if($data){
                if( !empty($_GPC['accountid']) ) 
                {
                    $old_user_db = pdo_get("ewei_shop_merch_account", array("uniacid" => $_W['uniacid'], "id" => $_GPC['accountid']));
                    if( trim($old_user_db['username']) != trim($account['username']) ){
                        $user_db = pdo_get("ewei_shop_merch_account", array("uniacid" => $_W['uniacid'], "username" => $account['username']));
                        if($user_db && trim($user_db['username']) == trim($account['username']) ){
                            app_error(-1, "该账号名称已存在");
                        }
                    }
                    pdo_update("ewei_shop_merch_account", $account, array( "id" => $_GPC["accountid"] ));
                }
                pdo_update("ewei_shop_merch_user", $data, array("id" => $id));
                app_json();
            }
            
        } else {
            $data['merchname']      = isset($_GPC['merchname']) ? trim($_GPC['merchname']) : '';
            $data['logo']           = isset($_GPC['logo']) ? trim($_GPC['logo']) : '';
            $data['salecate']       = isset($_GPC['salecate']) ? trim($_GPC['salecate']) : '';
            $data['cateid']         = isset($_GPC['cateid']) ? $_GPC['cateid'] : 0;
            $data['realname']       = isset($_GPC['realname']) ? trim($_GPC['realname']) : '';
            $data['mobile']         = isset($_GPC['mobile']) ? trim($_GPC['mobile']) : '';
            $data['address']        = isset($_GPC['address']) ? trim($_GPC['address']) : '';
            $data['lat']            = isset($_GPC['lat']) ? $_GPC['lat'] : '';
            $data['lng']            = isset($_GPC['lng']) ? $_GPC['lng'] : '';

            $data['status']         = isset($_GPC['status']) ? $_GPC['status'] : 1;
            $data['remark']           = isset($_GPC['remark']) ? trim($_GPC['remark']) : '';
            $data['accounttime']    = isset($_GPC['accounttime']) ? strtotime($_GPC['accounttime']) : strtotime("+365 day");
           
            $data['openid']         = isset($_GPC['merch_openid']) ? $_GPC['merch_openid'] : '';
            $data['jointime']       = time();
            $data['agentid']        = $_W['agentid'];
            $data['uniacid']        = $_W['uniacid'];
            $data['agent_account_id'] = $this->member['id'];
            
            $account = array();
            $account['username']       = trim($_GPC['username']);
            if(!empty($account['username'])){
                $user_db = pdo_get("ewei_shop_merch_account", array("uniacid" => $_W['uniacid'], "username" => $account['username']));
                if($user_db){
                    app_error(-1, "该账号名称已存在");
                }
                $account['pwd'] = trim($_GPC['pwd']);
                if(empty($account['pwd'])){
                    app_error(-1, "请输入密码");
                }
                $salt = random(8);
                $account['pwd']         = md5($account['pwd'] . $salt);
                $account['uniacid']     = $_W['uniacid'];
                $account['openid']      = trim($_GPC['merch_openid']);
                $account['salt']        = $salt;
                $account['status']      = 1;
                $account['isfounder']   = 1;
                $account['perms']       = serialize(array(  ));
            }
            
            if(!empty($data['merchname']) && !empty($data['lat']) && !empty($data['lng'])){
                pdo_insert("ewei_shop_merch_user", $data);
                $merchid = pdo_insertid();
                if($account){
                    $account['merchid'] = $merchid;
                    pdo_insert("ewei_shop_merch_account", $account);
                    $accountid = pdo_insertid();
                    pdo_update("ewei_shop_merch_user", array( "accountid" => $accountid ), array( "id" => $merchid ));
                }
                //创建默认的角色
                $saler_role_data = array(
                    "uniacid"           => $_W['uniacid'],
                    "rolename"          => "导购",
                    "rolekey"           => '',
                    "status"            => 1,
                    "merchid"           => $merchid,
                    "shoppingguide"     => 1,
                    "storemanager"      => 0
                );
                pdo_insert("ewei_shop_saler_role", $saler_role_data);
                $manager_role_data = array(
                    "uniacid"           => $_W['uniacid'],
                    "rolename"          => "店长",
                    "rolekey"           => '',
                    "status"            => 1,
                    "merchid"           => $merchid,
                    "shoppingguide"     => 1,
                    "storemanager"      => 1,
                    "verify"            => 1,
                    "deliver"           => 1
                );
                pdo_insert("ewei_shop_saler_role", $manager_role_data);
                app_json();
            } else {
                app_error(-1, "请填写必要的信息");
            }
        }
    }
    
    public function get_merch_cate(){
        global $_W;
        $list = pdo_getall("ewei_shop_merch_category", array("uniacid" => $_W['uniacid']));
        app_json(array('list' => $list));
    }
}
