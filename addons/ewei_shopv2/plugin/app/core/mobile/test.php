<?php  if( !defined("IN_IA") ) 
{
	exit( "Access Denied" );
}
require(EWEI_SHOPV2_PLUGIN . "app/core/page_mobile.php");
class Test_EweiShopV2Page extends AppMobilePage {
    
    public function main(){
        global $_W;
        $model = m("goods");
        $res = $model->get_auto_goods_coupon($_W['openid'], 8);
        
    }
    
    public function test(){
        global $_GPC;
        $value = array(
            8 => 2,
            10 => 3
        );
        echo "<pre>";
        print_r($_GPC);
        echo "</pre>";
    }
}
