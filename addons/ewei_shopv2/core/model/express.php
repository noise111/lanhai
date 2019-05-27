<?php
class Express_EweiShopV2Model
{
	/**
     * 获取快递列表
     */
	public function getExpressList()
	{
		global $_W;
		$sql = 'select * from ' . tablename('ewei_shop_express') . ' where status=1 order by displayorder desc,id asc';
		$data = pdo_fetchall($sql);
		return $data;
	}

    public function getExpresspersonnelList()
    {
        global $_W;
        $sql = 'select * from ' . tablename('ewei_shop_selfexpress_personnel') . ' where status=1 order by id asc';
        $data = (pdo_fetchall($sql));
//        print_r($data);die;
        return $data;
    }
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

?>
