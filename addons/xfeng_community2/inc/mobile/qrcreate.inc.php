<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/8/25 上午11:30
 */
global $_GPC,$_W;
$id = intval($_GPC['id']);//设备id
$did = intval($_GPC['did']);//分享数据id
require_once IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
$time = TIMESTAMP;
$url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&did={$did}&do=lock&m=".MODULE_NAME;//二维码内容

$errorCorrectionLevel = 'L';//容错级别
$matrixPointSize = 10;//生成图片大小
//生成二维码图片
QRcode::png($url, 'qrcode.png', $errorCorrectionLevel, $matrixPointSize, 2);
//    $logo = $user['tag']['avatar'];//准备好的logo图片
$QR = 'qrcode.png';//已经生成的原始二维码图
//    echo "<div style='font-size: 48px;text-align:center;margin:0 auto '>长按二维码转发给朋友哦！</div><img src='qrcode.png' style='width: 100%;margin-top: 20px'>";
$device = pdo_fetch("SELECT t1.*,t2.title as rtitle FROM" . tablename('xcommunity_building_device') . "t1 left join".tablename('xcommunity_region')."t2 on t1.regionid = t2.id WHERE t1.id=:id", array(':id' => $id));
$door =$device['unit'] ? $device['title'] . $device['unit'] : $device['title'];;
$_share = array(
    'title'  => $_W['member']['realname'].'邀请你扫码开门',
    'desc'   => $device['rtitle'].$door.'临时开门二维码',
    'imgUrl' => tomedia($_W['fans']['tag']['avatar']),
    'link'   => $_W['siteroot'] . 'app' . substr($this->createMobileUrl('qrcreate',array('id'=> $id,'did'=>$did)), 1)
);
include $this->template($this->xqtpl('core/opendoor/qrcreate'));