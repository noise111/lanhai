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
            $list[$k]['personnelsalername'] = (pdo_getcolumn("ewei_shop_selfexpress_personnel",array('id' => $list[$k]['personnelid']),'salername',1));

		}
//		print_r($list);
		include $this->template();
	}

    public function edit()
    {
        $this->post();
    }

    protected function post()
    {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);
        $item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_selfexpress') . ' WHERE id =:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
		print_r($item);
        if (!empty($item)) {
            $saler = m('member')->getMember($item['openid']);
            $store = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_store') . ' WHERE id =:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $item['storeid']));
        }
        if ($_W['ispost']) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'storeid' => intval($_GPC['storeid']),
                'openid' => trim($_GPC['openid']),
                'status' => intval($_GPC['status']),
                'salername' => trim($_GPC['salername']),
                'mobile' => trim($_GPC['mobile']),
                'roleid' => intval($_GPC['roleid'])
            );
            if (empty($data['storeid'])) {
                show_json(0, '请选择所属门店');
            }

            if (p('newstore')) {
                $data['getnotice'] = intval($_GPC['getnotice']);

                if (empty($item['username'])) {
                    if (empty($_GPC['username'])) {
                        show_json(0, '用户名不能为空!');
                    }

                    $usernames = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('ewei_shop_selfexpress') . ' WHERE username=:username limit 1', array(':username' => $_GPC['username']));

                    if (0 < $usernames) {
                        show_json(0, '该用户名已被使用，请修改后重新提交!');
                    }

                    $data['username'] = $_GPC['username'];
                }

                if (!empty($_GPC['pwd'])) {
                    $salt = random(8);

                    while (1) {
                        $saltcount = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_selfexpress') . ' where salt=:salt limit 1', array(':salt' => $salt));

                        if ($saltcount <= 0) {
                            break;
                        }

                        $salt = random(8);
                    }

                    $pwd = md5(trim($_GPC['pwd']) . $salt);
                    $data['pwd'] = $pwd;
                    $data['salt'] = $salt;
                }
                else {
                    if (empty($item)) {
                        show_json(0, '用户密码不能为空!');
                    }
                }
            }

            $m = m('member')->getMember($data['openid']);

            if (!empty($id)) {
                pdo_update('ewei_shop_selfexpress', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
                plog('shop.verify.saler.edit', '编辑店员 ID: ' . $id . ' <br/>店员信息: ID: ' . $m['id'] . ' / ' . $m['openid'] . '/' . $m['nickname'] . '/' . $m['realname'] . '/' . $m['mobile'] . ' ');
            }
            else {
                $params = array(
                    ':openid'       => $data['openid'],
                    ':uniacid'      => $_W['uniacid']
                );
                //查找店员
                $sql = "SELECT * FROM " . tablename("ewei_shop_selfexpress") . " WHERE openid = :openid AND uniacid = :uniacid";
                $saler_db = pdo_fetch($sql, $params);
                if($saler_db){
                    if($saler_db['is_delete'] == 1){
                        $data['is_delete'] = 0;
                        $data['pass'] = 0;
                        pdo_update('ewei_shop_selfexpress', $data, array('id' => $saler_db['id']));
                        $id = $saler_db['id'];
                    } else {
                        show_json(0, '此会员已经成为店员，没法重复添加');
                    }
                } else {
                    pdo_insert('ewei_shop_selfexpress', $data);
                    $id = pdo_insertid();
                    plog('shop.verify.saler.add', '添加店员 ID: ' . $id . '  <br/>店员信息: ID: ' . $m['id'] . ' / ' . $m['openid'] . '/' . $m['nickname'] . '/' . $m['realname'] . '/' . $m['mobile'] . ' ');
                }
                m("store")->deleteSalerRelation($data['openid']);
            }
            show_json(1, array('url' => webUrl('store/saler')));
        }
        $role = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_selfexpress_role') . ' WHERE uniacid=:uniacid AND merchid = 0', array(':uniacid' => $_W['uniacid']));
        include $this->template();
    }


}

?>
