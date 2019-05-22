<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Merchbase_EweiShopV2Page extends AppMobilePage{
    
    public $account;
    protected $is_main = false;
    
    public function __construct() {
        parent::__construct();
        global $_W;
        global $_GPC;
        $merchid = isset($_GPC['merchid']) ? $_GPC['merchid'] : 0;
        if(!$_W['openid']){
            app_error(0, '请先登录');
        }
        $this->account = m("member")->getMerchInfo($_W['openid']);
        if($this->account['is_merch'] !=1){
            app_error(AppError::$MerchNotValid);
        }
        if(empty($merchid) && !empty($this->account['list'])){
            $first = reset($this->account['list']);
            $_W['merchid'] = $first['id'];
        } else {
            $_W['merchid'] = isset($this->account['list'][$merchid]) ? $merchid : app_error(AppError::$MerchNotValid);
        }
        
        if($this->account['list'][$_W['merchid']]['openid'] == $_W['openid']){
            $this->is_main = true;
            $_W['merchisfounder'] = 1;
        } else {
            $_W['merchuid'] = $this->account['list'][$_W['merchid']]['account_id'];
        }
    }
    
    /**
     * 获取经营天数
     */
    public function getMerchTime(){
        global $_W;
        $merchid = $_W['merchid'];
        $user = pdo_get("ewei_shop_merch_user", array("id" => $merchid));
        $days = ceil((time() - $user['jointime']) / (3600*24));
        return $days;
    }
    
    /**
     * 获取商户所有
     * @global type $_W
     * @param type $type
     * @return type
     */
    public function getMerchStore($type = 1){
        global $_W;
        if($type == 1){
            $sql = "SELECT GROUP_CONCAT(id) FROM " . tablename("ewei_shop_store") . 
                   " WHERE merchid = {$_W['merchid']} AND uniacid = {$_W['uniacid']}" .
                   " GROUP BY merchid";
            return pdo_fetchcolumn($sql);
        } else {
            $sql = "SELECT * FROM " . tablename("ewei_shop_store") . 
                   " WHERE merchid = {$_W['merchid']} AND uniacid = {$_W['uniacid']}";
            return pdo_fetchall($sql);
        }
    }
    
    public function getMerchBenefit(){
        global $_W;
        //所有门店的分润
        $sql = "SELECT SUM(benefitbalance) as benefitbalance FROM " . tablename("ewei_shop_store") .
               " WHERE merchid = {$_W['merchid']} AND uniacid = {$_W['uniacid']}";
        $store_sum = pdo_fetchcolumn($sql);
        $sum = floatval($store_sum) + floatval($this->account['list'][$_W['merchid']]['benefitbalance']);
        return empty($sum) ? 0 : $sum;
    }
    
    public function getMerchSaler($type = 1){
        global $_W;
        $stores = $this->getMerchStore();
        if($type == 1){
            $sql = "SELECT GROUP_CONCAT(id) FROM " . tablename("ewei_shop_saler") . 
                   " WHERE merchid = {$_W['merchid']} AND uniacid = {$_W['uniacid']} AND is_delete = 0" .
                   " GROUP BY merchid";
            return pdo_fetchcolumn($sql);
        } else {
            $sql = "SELECT * FROM " . tablename("ewei_shop_saler") . 
                   " WHERE merchid = {$_W['merchid']} AND uniacid = {$_W['uniacid']} AND storeid IN($stores)";
            return pdo_fetchall($sql);
        }
    }
    
    public function check_perm($action){
        $model = p("merch");
        $res = $model->check_perm($action);
        $res = true;
        if(!$res){
            app_error(AppError::$NoPermission);
        }
    }
}