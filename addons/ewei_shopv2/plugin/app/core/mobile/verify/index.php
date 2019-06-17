<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'app/core/page_mobile.php';
class Index_EweiShopV2Page extends AppMobilePage
{
	public function qrcode()
	{
		global $_W;
		global $_GPC;
		$orderid = intval($_GPC['id']);
//		//$verifycode = $_GPC['verifycode'];
		$query = array('id' => $orderid);
		$verifycode = (pdo_getcolumn("ewei_shop_order",array('id' => $orderid),'verifycode',1));
//		$url = "{$_W['siteroot']}app/ewei_shopv2_api.php?i={$_W['uniacid']}&r=verify.createOrderVerifyImg&id=$orderid";
		//获取 核销入口二维码
//		$url = "{$_W['siteroot']}app/ewei_shopv2_api.php?i={$_W['uniacid']}&r=verify.detail&verifycode=$verifycode";//人人原版
		$url = "{$_W['siteroot']}app/ewei_shopv2_api.php?i={$_W['uniacid']}&r=verify.detail&id=$orderid";//现在改成了用id
//        app_json(array("list"=>$url));
        $qrcodeimg = m("qrcode")->createQrcode($url);
		app_json(array('url' => $url,'qrcodeimg'=>$qrcodeimg));
	}
    
    public function detail(){
		global $_W;
		global $_GPC;
		$openid = $_W['openid'];
		$uniacid = $_W['uniacid'];
		$orderid = intval($_GPC['id']);
		$data = com('verify')->allow2($orderid);
//		print_r($data);die;

        if(isset($data['errno'])){
            app_error(-1, $data['message']);
        }

		app_json($data);
	}
    
    public function complete(){
        global $_W;
		global $_GPC;
		$orderid = intval($_GPC['id']);
		$data = com('verify')->verify($orderid);

		if ($data['errno'] == -1) {
			app_error(-1, $data['message']);
		}
		else {
			app_json();
		}
    }
    
    public function createOrderVerifyImg(){
        global $_GPC;
        $orderid = intval($_GPC['id']);
        $model = com('appqrcode');
        $content = $model->getOrderVerifyCode($orderid);
        header("Content-type: image/png");
        app_json(array("list"=>$content));
        die($content);
    }
}

?>
