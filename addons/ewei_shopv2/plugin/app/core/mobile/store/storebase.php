<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Storebase_EweiShopV2Page extends AppMobilePage{
    
    public function __construct() {
        parent::__construct();
        //获取店员信息
        $saler = m('store')->getSalerInfo();
        if($saler){
            $store = m('store')->getStoreInfo($saler['storeid']);
            if(empty($store) || $store['status'] != 1){
                app_error(AppError::$StoreNotValid);
            }
        } else {
            app_error(-1, "你不是店员");
        }
    }
}

