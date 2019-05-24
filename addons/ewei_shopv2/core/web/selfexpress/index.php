<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Index_EweiShopV2Page extends WebPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
        $list = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_selfexpress') . ' WHERE uniacid=:uniacid ', array(':uniacid' => $_W['uniacid']));
        foreach ($list as $k => $v){
            $list[$k]['createtime'] = date("Y-m-d H:i:s",$list[$k]['createtime']);

		}

		include $this->template();
	}


}

?>
