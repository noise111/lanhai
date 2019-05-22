<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Test_EweiShopV2Page extends WebPage
{
    public function main(){
        global $_W;
        //$model = m('order');
        com("coupon")->sendcouponsbytask(35);
    }
    
    public function appQrCode(){
        $model = com("appqrcode");
        $model->getOrderVerifyCode(55);
    }
    
    public function ttt(){
        $str = '';
        $arr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','y','z', 1,2,3,4,5,6,7,8,9,0];
        for($i=0;$i<100;$i++):
            $str .= $arr[intval(rand(0,26))];
        endfor;
        printf("%s",$str);
        $child = $this->getStrOnlyLengthReturn($str);
        echo "<br/>" . $child;
    }
    
    public function getStrOnlyLengthReturn($str){
        $arr = [];
        $returnArr = [];
        $strLen = strlen($str);
        for($i=0;$i<$strLen;$i++):
            $arr [$str{$i}] = $str{$i};
            if(isset($str{$i+1}) && isset($arr[$str{$i+1}]))://防止出现notice提醒
                $returnArr[count($arr)] = implode($arr,'');
                $arr = [];
            endif;
        endfor;
        krsort($returnArr);
        return array_shift($returnArr);
    }

}