<?php
/**
 * Created by njxiaoqu.
 * User: zhoufeng
 * Time: 2017/5/15 下午3:44
 */
global $_W, $_GPC;
$ops = array('login', 'home', 'region', 'build', 'repair', 'report', 'room', 'announcement', 'business', 'shop', 'activity', 'park', 'car', 'member', 'guard', 'cost', 'entery', 'market', 'carpool', 'houselease', 'homemaking', 'cash', 'recharge', 'register', 'user', 'express', 'safety', 'notice', 'homefee', 'property', 'finance');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'login';
$p = in_array(trim($_GPC['p']), array('add', 'detail', 'list', 'post', 'display', 'goods', 'order', 'setting', 'cz', 'tx', 'xf', 'gooddetail', 'payset', 'coupon', 'grab', 'orderdetail', 'home', 'account', 'send', 'senddetail', 'gojoin', 'goout', 'log', 'costdetail', 'costadd', 'pactsave', 'store', 'storedetail', 'storeadd', 'credit')) ? trim($_GPC['p']) : 'list';
if ($op == 'login') {
    $_W['member']['title'] = '移动端管理中心';
//        isetcookie('__session', '', -10000);
    include $this->template('default2/app/login/login');
} elseif ($op == 'home') {
    $_W['member']['title'] = '移动端管理中心';
    include $this->template('default2/app/home/home');
} elseif ($op == 'region') {
    $_W['member']['title'] = '小区管理中心';
    if ($p == 'add') {

        include $this->template('default2/app/region/add');
    } elseif ($p == 'list') {
        include $this->template('default2/app/region/list');
    }
} elseif ($op == 'build') {
    $_W['member']['title'] = '楼宇管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/build/add');
    } elseif ($p == 'list') {
        include $this->template('default2/app/build/list');
    } elseif ($p == 'display') {
        include $this->template('default2/app/build/display');
    }
} elseif ($op == 'repair') {
    $_W['member']['title'] = '报修管理中心';
    if ($p == 'detail') {
        include $this->template('default2/app/repair/detail');
    } elseif ($p == 'list') {
        include $this->template('default2/app/repair/list');
    }
} elseif ($op == 'report') {
    $_W['member']['title'] = '建议管理中心';
    if ($p == 'detail') {
        include $this->template('default2/app/report/detail');
    } elseif ($p == 'list') {
        include $this->template('default2/app/report/list');
    }
} elseif ($op == 'room') {
    $_W['member']['title'] = '房屋管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/room/add');
    } elseif ($p == 'list') {
        include $this->template('default2/app/room/list');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/room/detail');
    } elseif ($p == 'display') {
        include $this->template('default2/app/room/member');
    }
} elseif ($op == 'announcement') {
    $_W['member']['title'] = '公告管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/announcement/add');
    } elseif ($p == 'list') {
        include $this->template('default2/app/announcement/list');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/announcement/detail');
    }
} elseif ($op == 'business') {

    if ($p == 'add') {
        $_W['member']['title'] = '添加店铺';
        include $this->template('default2/app/business/add');
    } elseif ($p == 'list') {
        $_W['member']['title'] = '商家管理中心';
        include $this->template('default2/app/business/list');
    } elseif ($p == 'post') {
        $_W['member']['title'] = '添加商品';
        include $this->template('default2/app/business/post');
    } elseif ($p == 'goods') {
        $_W['member']['title'] = '商品管理中心';
        include $this->template('default2/app/business/good_list');
    } elseif ($p == 'order') {
        include $this->template('default2/app/business/order');
    } elseif ($p == 'orderdetail') {
        include $this->template('default2/app/business/orderdetail');
    } elseif ($p == 'setting') {
        include $this->template('default2/app/business/setting');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/business/detail');
    } elseif ($p == 'cz') {
        include $this->template('default2/app/business/rechargelog');
    } elseif ($p == 'tx') {
        include $this->template('default2/app/business/cashlog');
    } elseif ($p == 'xf') {
        include $this->template('default2/app/business/record');
    } elseif ($p == 'gooddetail') {
        include $this->template('default2/app/business/gooddetail');
    } elseif ($p == 'payset') {
        echo '正在开发中';
        exit();
    } elseif ($p == 'coupon') {
        include $this->template('default2/app/business/hx');
    }

} elseif ($op == 'shop') {

    if ($p == 'add') {
        $_W['member']['title'] = '商品发布';
        include $this->template('default2/app/shop/add');
    } elseif ($p == 'list') {
        $_W['member']['title'] = '商品管理中心';
        include $this->template('default2/app/shop/list');
    } elseif ($p == 'post') {
        include $this->template('default2/app/shop/post');
    } elseif ($p == 'order') {
        $_W['member']['title'] = '订单管理中心';
        include $this->template('default2/app/shop/order');
    } elseif ($p == 'tx') {
        $_W['member']['title'] = '提现管理';
        include $this->template('default2/app/shop/cashlog');
    } elseif ($p == 'detail') {
        $_W['member']['title'] = '商品详情';
        include $this->template('default2/app/shop/detail');
    } elseif ($p == 'orderdetail') {
        include $this->template('default2/app/shop/orderdetail');
    } elseif ($p == 'store') {
        include $this->template('default2/app/shop/store');
    } elseif ($p == 'storedetail') {
        include $this->template('default2/app/shop/storedetail');
    } elseif ($p == 'storeadd') {
        include $this->template('default2/app/shop/storeadd');
    }
} elseif ($op == 'activity') {
    $_W['member']['title'] = '活动管理中心';
    if ($p == 'add') {

        include $this->template('default2/app/activity/add');

    } elseif ($p == 'list') {
        include $this->template('default2/app/activity/list');
    } elseif ($p == 'post') {
        include $this->template('default2/app/activity/post');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/activity/detail');
    }
} elseif ($op == 'park') {
    $_W['member']['title'] = '车位管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/park/add');
    } elseif ($p == 'list') {
        include $this->template('default2/app/park/list');
    } elseif ($p == 'display') {
        include $this->template('default2/app/park/display');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/park/detail');
    }
} elseif ($op == 'carpool') {
    $_W['member']['title'] = '拼车管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/carpool/add');
    } elseif ($p == 'list') {
        include $this->template('default2/app/carpool/list');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/carpool/detail');
    }
} elseif ($op == 'member') {
    $_W['member']['title'] = '住户管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/member/add');
    } elseif ($p == 'list') {
        include $this->template('default2/app/member/list');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/member/detail');
    }
} elseif ($op == 'guard') {
    $_W['member']['title'] = '门禁管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/guard/add');
    } elseif ($p == 'list') {
        include $this->template('default2/app/guard/list');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/guard/detail');
    }
} elseif ($op == 'cost') {
    $_W['member']['title'] = '费用管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/cost/add');
    } elseif ($p == 'list') {
        include $this->template('default2/app/cost/list');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/cost/detail');
    } elseif ($p == 'display') {
        include $this->template('default2/app/cost/display');
    } elseif ($p == 'costdetail') {
        include $this->template('default2/app/cost/costdetail');
    } elseif ($p == 'costadd') {
        include $this->template('default2/app/cost/costadd');
    } elseif ($p == 'order') {
        include $this->template('default2/app/cost/order');
    }
} elseif ($op == 'entery') {
    $_W['member']['title'] = '抄表管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/entery/add');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/entery/detail');
    } elseif ($p == 'list') {
        include $this->template('default2/app/entery/list');
    }

} elseif ($op == 'market') {
    $_W['member']['title'] = '集市管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/market/add');
    } elseif ($p == 'list') {
        include $this->template('default2/app/market/list');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/market/detail');
    }
} elseif ($op == 'houselease') {
    $_W['member']['title'] = '租赁管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/houselease/add');
    } elseif ($p == 'list') {
        include $this->template('default2/app/houselease/list');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/houselease/detail');
    }
} elseif ($op == 'homemaking') {
    $_W['member']['title'] = '家政管理中心';
    if ($p == 'list') {
        include $this->template('default2/app/homemaking/list');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/homemaking/detail');
    } elseif ($p == 'add') {
        include $this->template('default2/app/homemaking/grab');
    }
} elseif ($op == 'register') {

    include $this->template('default2/app/register');
} elseif ($op == 'car') {
    $_W['member']['title'] = '车辆管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/car/add');
    } elseif ($p == 'list') {
        include $this->template('default2/app/car/list');
    } elseif ($p == 'display') {
        include $this->template('default2/app/car/display');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/car/detail');
    }
} elseif ($op == 'recharge') {
    if (checksubmit('submit')) {
        $fee = $_GPC['fee'];
        $data = array(
            'uniacid' => $_W['uniacid'],
            'uid' => $_SESSION['appuid'],
            'ordersn' => 'LN' . date('YmdHi') . random(10, 1),
            'price' => $fee,
            'status' => 0,
            'createtime' => TIMESTAMP,
            'type' => 'mbusiness',
        );
        if (pdo_insert('xcommunity_order', $data)) {
            $orderid = pdo_insertid();
            $data = array(
                'uniacid' => $_W['uniacid'],
                'fee' => $fee,
                'orderid' => $orderid,
                'createtime' => TIMESTAMP
            );
            $r = pdo_insert('xcommunity_recharge', $data);
            if ($r) {
                $url = $this->createMobileUrl('apppay', array('orderid' => $orderid));
                @header("Location: " . $url);
                exit();
            }
        }
    }

    include $this->template('default2/app/recharge');
} elseif ($op == 'cash') {
    $type = intval($_GPC['type']);
    if ($_SESSION['apptype'] == 2) {
        $condition .= " and t1.uid=:uid";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if ($_SESSION['apptype'] == 3) {
        $condition .= " and t2.regionid in (:regionid)";
        $params[':regionid'] = $_SESSION['appregionids'];
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
//            $data['list'] = array();
//            util::send_result($data);
    }
    $regions = model_region::region_fetall('', $_SESSION['appuid']);
    if (checksubmit('submit')) {
        if ($_GPC['fee'] <= 0) {
            itoast('输入金额不正确,请重新输入', referer(), 'error', true);
            exit();
        }
        $data = array(
            'uniacid' => $_W['uniacid'],
            'ordersn' => 'LN' . date('YmdHi') . random(10, 1),
            'price' => $_GPC['fee'],
            'type' => 'cash',
            'pay' => $_GPC['pay'],
            'createtime' => TIMESTAMP,
            'uid' => $_SESSION['appuid'],
        );
        if ($type == 1) {
            $regionid = intval($_GPC['regionid']);
            $region = model_region::region_check($regionid);
            if ($_GPC['fee'] > $region['commission']) {
                itoast('佣金不足，无法提现', referer(), 'error', true);
            }
            $data['regionid'] = $regionid;
        } else {
            $users = pdo_fetch("SELECT * FROM" . tablename('xcommunity_users') . "WHERE uid=:uid", array(':uid' => $_SESSION['appuid']));
            if ($_GPC['fee'] > $users['balance']) {
                itoast('余额不足，无法提现', referer(), 'error', true);
            }
        }


        $r = pdo_insert('xcommunity_order', $data);
        if ($r) {
            if ($type == 1) {
                pdo_update('xcommunity_region', array('commission -=' => $_GPC['fee']), array('id' => $regionid));
            } else {
                pdo_update('xcommunity_users', array('balance -=' => $_GPC['fee']), array('id' => $users['id']));
                $balance = $users['balance'] - $_GPC['fee'];
                $_SESSION['balance'] = $balance;
            }

            itoast('提交成功', referer(), 'success', true);
        }
    }


    include $this->template('default2/app/cash');


} elseif ($op == 'user') {
    if ($p == 'home') {
        include $this->template('default2/app/user/home');
    } elseif ($p == 'tx') {
        include $this->template('default2/app/user/cashlog');
    } elseif ($p == 'account') {
        include $this->template('default2/app/user/account');
    }
} elseif ($op == 'express') {
    $_W['member']['title'] = '快递管理中心';
    if ($p == 'list') {
        include $this->template('default2/app/express/index');
    } elseif ($p == 'send') {
        include $this->template('default2/app/express/send');
    } elseif ($p == 'senddetail') {
        include $this->template('default2/app/express/sendDetail');
    } elseif ($p == 'gojoin') {
        include $this->template('default2/app/express/manageList');
    } elseif ($p == 'goout') {
        include $this->template('default2/app/express/manageOut');
    }
} elseif ($op == 'safety') {
    $_W['member']['title'] = '巡更管理中心';
    if ($p == 'log') {
        include $this->template('default2/app/safety/log');
    }
} elseif ($op == 'notice') {
    $_W['member']['title'] = '内部公告';
    if ($p == 'list') {
        include $this->template('default2/app/notice/list');
    } elseif ($p == 'detail') {
        include $this->template('default2/app/notice/detail');
    }
} elseif ($op == 'homefee') {
    $_W['member']['title'] = '合同存档';
    if ($p == 'pactsave') {
        include $this->template('default2/app/homefee/pactsave');
    }
} elseif ($op == 'property') {
    $_W['member']['title'] = '物业管理中心';
    if ($p == 'add') {
        include $this->template('default2/app/property/add');

    } elseif ($p == 'list') {
        include $this->template('default2/app/property/list');
    }
}
/**
 * 充值中心
 */
if ($op == 'finance') {
    $_W['member']['title'] = '充值中心';
    /**
     * 余额充值
     */
    if ($p == 'list') {
        include $this->template('default2/app/balance');
    }
    /**
     * 积分充值
     */
    if ($p == 'credit') {
        include $this->template('default2/app/integral');
    }
}