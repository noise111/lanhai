<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Util_EweiShopV2Page extends MobilePage
{
	public function query()
	{
		global $_W;
		global $_GPC;
		$type = intval($_GPC['type']);
		$money = floatval($_GPC['money']);
		$merchs = $_GPC['merchs'];
		$goods = $_GPC['goods'];
        //查询购物优惠券，这里同时返回社交优惠券的数据
		if ($type == 0) {
			$list = com_run('coupon::getAvailableCoupons', $type, 0, $merchs, $goods);
			$list2 = com_run('wxcard::getAvailableWxcards', $type, 0, $merchs, $goods);
		}
		else {
			if ($type == 1) {
				$list = com_run('coupon::getAvailableCoupons', $type, $money, $merchs);
				$list2 = array();
			}
		}

		show_json(1, array('coupons' => $list, 'wxcards' => $list2));
	}

	public function picker()
	{
		include $this->template();
	}
}

?>
