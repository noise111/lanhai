<?php
//生成移动端菜单
function nav()
{
    global $_W;
    //导入微信端导航数据
    $navs = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_nav') . "WHERE uniacid= :uniacid", array(':uniacid' => $_W['uniacid']));
    $regions = model_region::region_fetall();
    if (empty($navs)) {
        $data1 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '物业服务', 'url' => '', 'uniacid' => $_W['uniacid'],);
        $data2 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '小区互动', 'url' => '', 'uniacid' => $_W['uniacid'],);
        $data3 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '生活服务', 'url' => '', 'uniacid' => $_W['uniacid'],);
        if ($data1) {
            pdo_insert('xcommunity_nav', $data1);
            $nid1 = pdo_insertid();
            foreach ($regions as $key => $item) {
                $dat1 = array(
                    'nid'      => $nid1,
                    'regionid' => $item['id'],
                );
                pdo_insert('xcommunity_nav_region', $dat1);
            }

            $menu1 = array(
                array('displayorder' => 1, 'status' => 1, 'pcate' => $nid1, 'title' => '小区公告', 'do' => 'announcement', 'icon' => 'glyphicon glyphicon-bullhorn', 'bgcolor' => '#95bd38', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=announcement&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/notice.png"),
                array('displayorder' => 2, 'status' => 1, 'pcate' => $nid1, 'title' => '小区报修', 'do' => 'repair', 'icon' => 'glyphicon glyphicon-wrench', 'bgcolor' => '#3c87c8', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=repair&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/repair.png"),
                array('displayorder' => 3, 'status' => 1, 'pcate' => $nid1, 'title' => '意见建议', 'do' => 'report', 'icon' => 'fa fa-legal', 'bgcolor' => '#dd4b2b', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=report&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/report.png"),
                array('displayorder' => 4, 'status' => 1, 'pcate' => $nid1, 'title' => '物业缴费', 'do' => 'cost', 'icon' => 'glyphicon glyphicon-send', 'bgcolor' => '#3c87c8', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=cost&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/cost.png"),
                array('displayorder' => 5, 'status' => 1, 'pcate' => $nid1, 'title' => '手机开门', 'do' => 'opendoor', 'icon' => 'glyphicon glyphicon-search', 'bgcolor' => '#ec9510', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=opendoor&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/open.png"),
                array('displayorder' => 6, 'status' => 1, 'pcate' => $nid1, 'title' => '便民号码', 'do' => 'phone', 'icon' => 'glyphicon glyphicon-earphone', 'bgcolor' => '#ab5e90', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=phone&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/phone.png"),
                array('displayorder' => 7, 'status' => 1, 'pcate' => $nid1, 'title' => '便民查询', 'do' => 'search', 'icon' => 'glyphicon glyphicon-search', 'bgcolor' => '#ec9510', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=search&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/chaxun.png"),
                array('displayorder' => 8, 'status' => 1, 'pcate' => $nid1, 'title' => '生活缴费', 'do' => 'life', 'icon' => 'glyphicon glyphicon-search', 'bgcolor' => '#ec9510', 'url' => 'https://payapp.weixin.qq.com/life/index?showwxpaytitle=1#wechat_redirect', 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/life.png"),
                array('displayorder' => 9, 'status' => 1, 'pcate' => $nid1, 'title' => '小区停车', 'do' => 'zhpark', 'icon' => 'glyphicon glyphicon-search', 'bgcolor' => '#ec9510', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=zhpark&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/zhpark.png"),
                array('displayorder' => 11, 'status' => 1, 'pcate' => $nid1, 'title' => '问卷调查', 'do' => 'vote', 'icon' => 'glyphicon glyphicon-search', 'bgcolor' => '#ec9510', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=vote&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/vote.png"),
//                    array('displayorder' => 12, 'status' => 1, 'pcate' => $nid1, 'title' => '我要充电', 'do' => 'charging', 'icon' => 'glyphicon glyphicon-search', 'bgcolor' => '#ec9510', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=charging&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/charging.png"),
            );
            foreach ($menu1 as $key => $value1) {
                pdo_insert('xcommunity_nav', $value1);
                $id1 = pdo_insertid();
                foreach ($regions as $key => $item) {
                    $dat1 = array(
                        'nid'      => $id1,
                        'regionid' => $item['id'],
                    );
                    pdo_insert('xcommunity_nav_region', $dat1);
                }
            }
        }
        if ($data2) {
            pdo_insert('xcommunity_nav', $data2);
            $nid2 = pdo_insertid();
            foreach ($regions as $key => $item) {
                $dat1 = array(
                    'nid'      => $nid2,
                    'regionid' => $item['id'],
                );
                pdo_insert('xcommunity_nav_region', $dat1);
            }
            $menu2 = array(
                array('displayorder' => 12, 'status' => 1, 'pcate' => $nid2, 'title' => '小区活动', 'do' => 'activity', 'icon' => 'glyphicon glyphicon-tasks', 'bgcolor' => '#65944e', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=activity&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/huodong.png"),
                array('displayorder' => 13, 'status' => 1, 'pcate' => $nid2, 'title' => '小区家政', 'do' => 'homemaking', 'icon' => 'glyphicon glyphicon-leaf', 'bgcolor' => '#95bd38', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=homemaking&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/jiazheng.png"),
                array('displayorder' => 14, 'status' => 1, 'pcate' => $nid2, 'title' => '房屋租售', 'do' => 'houselease', 'icon' => 'fa fa-info', 'bgcolor' => '#38bfc8', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=houselease&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/zuning.png"),
                array('displayorder' => 15, 'status' => 1, 'pcate' => $nid2, 'title' => '小区拼车', 'do' => 'car', 'icon' => 'fa fa-truck', 'bgcolor' => '#7f6000', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=car&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/pingche.png"),
                array('displayorder' => 16, 'status' => 1, 'pcate' => $nid2, 'title' => '小区集市', 'do' => 'car', 'icon' => 'fa fa-truck', 'bgcolor' => '#7f6000', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=market&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/jishi.png"),
            );
            foreach ($menu2 as $key => $value2) {
                pdo_insert('xcommunity_nav', $value2);
                $id2 = pdo_insertid();
                foreach ($regions as $key => $item) {
                    $dat2 = array(
                        'nid'      => $id2,
                        'regionid' => $item['id'],
                    );
                    pdo_insert('xcommunity_nav_region', $dat2);
                }
            }
        }
        if ($data3) {
            pdo_insert('xcommunity_nav', $data3);
            $nid3 = pdo_insertid();
            foreach ($regions as $key => $item) {
                $dat1 = array(
                    'nid'      => $nid3,
                    'regionid' => $item['id'],
                );
                pdo_insert('xcommunity_nav_region', $dat1);
            }
            $menu3 = array(
                array('displayorder' => 17, 'status' => 1, 'pcate' => $nid3, 'title' => '周边商家', 'do' => 'business', 'icon' => 'glyphicon glyphicon-shopping-cart', 'bgcolor' => '#65944e', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=business&op=list&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/zhoubian.png"),
                array('displayorder' => 18, 'status' => 1, 'pcate' => $nid3, 'title' => '生活超市', 'do' => 'shopping', 'icon' => 'glyphicon glyphicon-shopping-cart', 'bgcolor' => '#65944e', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=shopping&op=list&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/chaoshi.png"),
                array('displayorder' => 19, 'status' => 1, 'pcate' => $nid3, 'title' => '快递代收', 'do' => 'express', 'icon' => 'glyphicon glyphicon-shopping-cart', 'bgcolor' => '#65944e', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=express&op=list&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/express.png"),
//                    array('displayorder' => 20, 'status' => 0, 'pcate' => $nid3, 'title' => '生鲜超市', 'do' => 'counter', 'icon' => 'glyphicon glyphicon-shopping-cart', 'bgcolor' => '#65944e', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=counter&op=list&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => "addons/" . MODULE_NAME . "/template/mobile/default2/static/images/counter.png", 'show' => 0),
            );
            foreach ($menu3 as $key => $value3) {
                pdo_insert('xcommunity_nav', $value3);
                $id3 = pdo_insertid();
                foreach ($regions as $key => $item) {
                    $dat3 = array(
                        'nid'      => $id3,
                        'regionid' => $item['id'],
                    );
                    pdo_insert('xcommunity_nav_region', $dat3);
                }
            }
        }
    }
}

//移动端菜单
function m($uid, $apptype = '', $shopids = '')
{
    global $_W;
    $uid = $uid ? $uid : $_W['uid'];
    $user = util::xquser($uid);
    $con = '';
    if ($user) {
        if (empty($user['xqmenus'])) {
            itoast('没有操作权限,请联系管理员。', referer(), 'error');
            exit();
        }
        $con = " AND id in({$user['xqmenus']})";
    }
//    print_r($user);
    $condition = ' pcate = 0 and uniacid=:uniacid';
    $sql = "SELECT title,url,id,icon FROM" . tablename('xcommunity_appmenu') . "WHERE $condition $con order by displayorder asc";

    $menus = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
//    print_r($menus);exit();
    $navmenus = array();
    if ($menus) {
        foreach ($menus as $key => $val) {
            $params[':pcate'] = $val['id'];
            $childmenu = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_appmenu') . "WHERE pcate = :pcate $con order by displayorder asc", $params);
            foreach ($childmenu as $k => $v) {
                if ($v['show'] == 1) {
                    if ($v['title'] == '待付款') {
                        $status = 0;
                    } elseif ($v['title'] == '待发货') {
                        $status = 1;
                    } elseif ($v['title'] == '待收货') {
                        $status = 2;
                    } elseif ($v['title'] == '已完成') {
                        $status = 3;
                    }
                    if ($shopids) {
                        $childmenu[$k]['total'] = model_shop::getappOrderTotal($status, $uid, $apptype, $shopids);
                    }

                }
            }

            $navmenus[] = array(
                'title' => $val['title'],
                'items' => $childmenu,
                'url'   => $val['url'],
                'id'    => $val['id'],
                'icon'  => $val['icon']
            );

        }
    }
    return $navmenus;

}

function footer()
{
    global $_W;
    //导入微信端导航数据
    $navs = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_footmenu') . "WHERE uniacid= :uniacid", array(':uniacid' => $_W['uniacid']));
    $regions = model_region::region_fetall();
    if (empty($navs)) {
        $menus = array(
            array('displayorder' => 1, 'enable' => 1, 'title' => '主页', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=home&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'icon' => MODULE_URL . 'template/mobile/default2/static/images/icon/index01.png', 'clickIcon' => MODULE_URL . 'template/mobile/default2/static/images/icon/index01-active.png', 'type' => 3),
            array('displayorder' => 2, 'enable' => 1, 'title' => '服务', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=service&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'icon' => MODULE_URL . 'template/mobile/default2/static/images/icon/index02.png', 'clickIcon' => MODULE_URL . 'template/mobile/default2/static/images/icon/index02-active.png', 'type' => 3),
            array('displayorder' => 3, 'enable' => 1, 'title' => '门禁', 'url' => '', 'uniacid' => $_W['uniacid'], 'icon' => MODULE_URL . 'template/mobile/default2/static/images/icon/open01.png', 'clickIcon' => MODULE_URL . 'template/mobile/default2/static/images/icon/open01.png', 'type' => 1),
            array('displayorder' => 4, 'enable' => 1, 'title' => '通知', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=announcement&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'icon' => MODULE_URL . 'template/mobile/default2/static/images/icon/index03.png', 'clickIcon' => MODULE_URL . 'template/mobile/default2/static/images/icon/index03-active.png', 'type' => 2),
            array('displayorder' => 5, 'enable' => 1, 'title' => '我的', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=member&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'icon' => MODULE_URL . 'template/mobile/default2/static/images/icon/index04.png', 'clickIcon' => MODULE_URL . 'template/mobile/default2/static/images/icon/index04-active.png', 'type' => 3),
        );
        foreach ($menus as $key => $value) {
            pdo_insert('xcommunity_footmenu', $value);
            $id = pdo_insertid();
            foreach ($regions as $key => $item) {
                $dat = array(
                    'fid'      => $id,
                    'regionid' => $item['id'],
                );
                pdo_insert('xcommunity_footmenu_region', $dat);
            }
        }
    }

}

function xqmenu()
{
    global $_W, $_GPC;
    $menus = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_menu') . "WHERE 1");
    if (empty($menus)) {
        $data = array(
            array('id' => 1, 'displayorder' => 1, 'pcate' => 0, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&do=setting&method=setting&m=' . MODULE_NAME, 'do' => 'setting', 'method' => 'setting', 'status' => 1, 'icon' => 'fa fa-cog'),
            array('id' => 2, 'displayorder' => 2, 'pcate' => 0, 'title' => '页面管理', 'url' => './index.php?c=site&a=entry&do=manage&method=manage&m=' . MODULE_NAME, 'do' => 'manage', 'method' => 'manage', 'status' => 1, 'icon' => 'fa fa-columns'),
            array('id' => 3, 'displayorder' => 3, 'pcate' => 0, 'title' => '物业服务', 'url' => './index.php?c=site&a=entry&do=fun&method=fun&m=' . MODULE_NAME, 'do' => 'fun', 'method' => 'fun', 'status' => 1, 'icon' => 'fa fa-life-ring'),
            array('id' => 4, 'displayorder' => 4, 'pcate' => 0, 'title' => '收费管理', 'url' => './index.php?c=site&a=entry&do=xqcost&method=xqcost&m=', 'do' => 'xqcost', 'method' => 'xqcost', 'status' => 1, 'icon' => 'fa fa-money'),
            array('id' => 5, 'displayorder' => 6, 'pcate' => 0, 'title' => '小区超市', 'url' => './index.php?c=site&a=entry&do=shop&method=shop&m=' . MODULE_NAME, 'do' => 'shop', 'method' => 'shop', 'status' => 1, 'icon' => 'fa fa-shopping-cart'),
            array('id' => 6, 'displayorder' => 8, 'pcate' => 0, 'title' => '小区商家', 'url' => './index.php?c=site&a=entry&do=seller&method=seller&m=' . MODULE_NAME, 'do' => 'seller', 'method' => 'seller', 'status' => 1, 'icon' => 'fa fa-truck'),
            array('id' => 7, 'displayorder' => 9, 'pcate' => 0, 'title' => '财务中心', 'url' => './index.php?c=site&a=entry&do=cash&method=cash&m=' . MODULE_NAME, 'do' => 'cash', 'method' => 'cash', 'status' => 1, 'icon' => 'fa fa-database'),
            array('id' => 8, 'displayorder' => 10, 'pcate' => 0, 'title' => '报表统计', 'url' => './index.php?c=site&a=entry&do=xqdata&method=xqdata&m=' . MODULE_NAME, 'do' => 'xqdata', 'method' => 'xqdata', 'status' => 1, 'icon' => 'fa fa-line-chart'),
            array('id' => 9, 'displayorder' => 11, 'pcate' => 0, 'title' => '扩展功能', 'url' => './index.php?c=site&a=entry&do=application&method=application&m=' . MODULE_NAME, 'do' => 'application', 'method' => 'application', 'status' => 1, 'icon' => 'fa fa-folder-open'),
            array('id' => 10, 'displayorder' => 12, 'pcate' => 0, 'title' => '员工管理', 'url' => './index.php?c=site&a=entry&do=xqstaff&method=xqstaff&m=' . MODULE_NAME, 'do' => 'xqstaff', 'method' => 'xqstaff', 'status' => 1, 'icon' => 'fa fa-sitemap'),
            array('id' => 11, 'displayorder' => 13, 'pcate' => 0, 'title' => '系统管理', 'url' => './index.php?c=site&a=entry&do=system&method=system&m=' . MODULE_NAME, 'do' => 'system', 'method' => 'system', 'status' => 1, 'icon' => 'fa fa-cloud')
        );
        foreach ($data as $key => $value) {
            pdo_insert('xcommunity_menu', $value);
        }
        $dat = array(
            array('id' => 12, 'pcate' => 2, 'displayorder' => 11, 'title' => '首页公告', 'url' => './index.php?c=site&a=entry&do=sysnotice&m=' . MODULE_NAME, 'do' => 'sysnotice', 'method' => 'manage', 'status' => 1),
            array('id' => 13, 'pcate' => 2, 'displayorder' => 12, 'title' => '首页幻灯', 'url' => './index.php?c=site&a=entry&op=list&do=slide&m=' . MODULE_NAME, 'do' => 'slide', 'method' => 'manage', 'status' => 1),
            array('id' => 14, 'pcate' => 2, 'displayorder' => 13, 'title' => '首页导航', 'url' => './index.php?c=site&a=entry&op=list&do=nav&m=' . MODULE_NAME, 'do' => 'nav', 'method' => 'manage', 'status' => 1),
            array('id' => 15, 'pcate' => 2, 'displayorder' => 14, 'title' => '首页广告', 'url' => './index.php?c=site&a=entry&op=list&do=banner&m=' . MODULE_NAME, 'do' => 'banner', 'method' => 'manage', 'status' => 1),
            array('id' => 16, 'pcate' => 2, 'displayorder' => 15, 'title' => '魔方推荐', 'url' => './index.php?c=site&a=entry&do=cube&m=' . MODULE_NAME, 'do' => 'cube', 'method' => 'manage', 'status' => 1),
            array('id' => 17, 'pcate' => 2, 'displayorder' => 16, 'title' => '商品推荐', 'url' => './index.php?c=site&a=entry&do=recommand&m=' . MODULE_NAME, 'do' => 'recommand', 'method' => 'manage', 'status' => 1),
            array('id' => 18, 'pcate' => 2, 'displayorder' => 18, 'title' => '主页排版', 'url' => './index.php?c=site&a=entry&do=xqsort&m=' . MODULE_NAME, 'do' => 'xqsort', 'method' => 'manage', 'status' => 1),
            array('id' => 19, 'pcate' => 2, 'displayorder' => 19, 'title' => '页面风格', 'url' => './index.php?c=site&a=entry&op=list&do=style&m=' . MODULE_NAME, 'do' => 'style', 'method' => 'manage', 'status' => 1),
            array('id' => 20, 'pcate' => 2, 'displayorder' => 10, 'title' => '入口链接', 'url' => './index.php?c=site&a=entry&do=xqurl&m=' . MODULE_NAME, 'do' => 'xqurl', 'method' => 'manage', 'status' => 1),
            array('id' => 21, 'pcate' => 2, 'displayorder' => 17, 'title' => '底部菜单', 'url' => './index.php?c=site&a=entry&do=footmenu&m=' . MODULE_NAME, 'do' => 'footmenu', 'method' => 'manage', 'status' => 1),
            array('id' => 22, 'pcate' => 3, 'displayorder' => 18, 'title' => '基础设置', 'url' => './index.php?c=site&a=entry&op=set&do=set&m=' . MODULE_NAME, 'do' => 'setset', 'method' => 'setting', 'status' => 1),
            array('id' => 23, 'pcate' => 1, 'displayorder' => 19, 'title' => '平台设置', 'url' => './index.php?c=site&a=entry&op=sys&do=set&m=' . MODULE_NAME, 'do' => 'setsys', 'method' => 'setting', 'status' => 1),
            array('id' => 24, 'pcate' => 1, 'displayorder' => 20, 'title' => '分享设置', 'url' => './index.php?c=site&a=entry&op=xqshare&do=set&m=' . MODULE_NAME, 'do' => 'setxqshare', 'method' => 'setting', 'status' => 1),
            array('id' => 25, 'pcate' => 1, 'displayorder' => 23, 'title' => '短信接口', 'url' => './index.php?c=site&a=entry&do=sms&m=' . MODULE_NAME, 'do' => 'sms', 'method' => 'setting', 'status' => 1),
            array('id' => 26, 'pcate' => 1, 'displayorder' => 24, 'title' => '打印机接口', 'url' => './index.php?c=site&a=entry&op=list&do=print&m=' . MODULE_NAME, 'do' => 'print', 'method' => 'setting', 'status' => 1),
            array('id' => 27, 'pcate' => 1, 'displayorder' => 25, 'title' => '模板消息库', 'url' => './index.php?c=site&a=entry&do=tpl&m=' . MODULE_NAME, 'do' => 'tpl', 'method' => 'setting', 'status' => 1),
            array('id' => 28, 'pcate' => 1, 'displayorder' => 26, 'title' => '全网通设置', 'url' => './index.php?c=site&a=entry&op=qwt&do=set&m=' . MODULE_NAME, 'do' => 'setqwt', 'method' => 'setting', 'status' => 1),
            array('id' => 29, 'pcate' => 3, 'cate' => 22, 'displayorder' => 29, 'title' => '统一小区设置', 'url' => './index.php?c=site&a=entry&op=xqset&do=set&m=' . MODULE_NAME, 'do' => 'setxqset', 'method' => 'setting', 'status' => 1),
            array('id' => 30, 'pcate' => 3, 'cate' => 22, 'displayorder' => 28, 'title' => '统一注册字段', 'url' => './index.php?c=site&a=entry&op=field&do=set&m=' . MODULE_NAME, 'do' => 'setfield', 'method' => 'setting', 'status' => 1),
            array('id' => 31, 'pcate' => 1, 'displayorder' => 29, 'title' => '支付方式设置', 'url' => './index.php?c=site&a=entry&do=pay&m=' . MODULE_NAME, 'do' => 'pay', 'method' => 'setting', 'status' => 1),
//            array('id' => 32, 'pcate' => 1, 'displayorder' => 30, 'title' => '独立支付接口', 'url' => './index.php?c=site&a=entry&do=payapi&m=' . MODULE_NAME, 'do' => 'payapi', 'method' => 'setting', 'status' => 1),
            array('id' => 33, 'pcate' => 3, 'displayorder' => 28, 'title' => '物业管理', 'url' => './index.php?c=site&a=entry&op=list&do=property&m=' . MODULE_NAME, 'do' => 'property', 'method' => 'fun', 'status' => 1),
            array('id' => 34, 'pcate' => 3, 'displayorder' => 29, 'title' => '小区管理', 'url' => './index.php?c=site&a=entry&op=list&do=region&m=' . MODULE_NAME, 'do' => 'region', 'method' => 'fun', 'status' => 1),
            array('id' => 35, 'pcate' => 3, 'displayorder' => 36, 'title' => '住户管理', 'url' => './index.php?c=site&a=entry&op=list&do=member&m=' . MODULE_NAME, 'do' => 'member', 'method' => 'fun', 'status' => 1),
            array('id' => 36, 'pcate' => 3, 'displayorder' => 35, 'title' => '房屋管理', 'url' => './index.php?c=site&a=entry&op=list&do=room&m=' . MODULE_NAME, 'do' => 'room', 'method' => 'fun', 'status' => 1),
            array('id' => 37, 'pcate' => 3, 'displayorder' => 37, 'title' => '小区公告', 'url' => './index.php?c=site&a=entry&op=list&do=announcement&m=' . MODULE_NAME, 'do' => 'announcement', 'method' => 'fun', 'status' => 1),
            array('id' => 38, 'pcate' => 3, 'displayorder' => 38, 'title' => '小区报修', 'url' => './index.php?c=site&a=entry&op=list&do=repair&m=' . MODULE_NAME, 'do' => 'repair', 'method' => 'fun', 'status' => 1),
            array('id' => 39, 'pcate' => 3, 'displayorder' => 39, 'title' => '意见建议', 'url' => './index.php?c=site&a=entry&op=list&do=report&m=' . MODULE_NAME, 'do' => 'report', 'method' => 'fun', 'status' => 1),
            array('id' => 40, 'pcate' => 9, 'displayorder' => 38, 'title' => '家政服务', 'url' => './index.php?c=site&a=entry&op=list&do=homemaking&m=' . MODULE_NAME, 'do' => 'homemaking', 'method' => 'fun', 'status' => 1),
            array('id' => 41, 'pcate' => 9, 'displayorder' => 39, 'title' => '租售服务', 'url' => './index.php?c=site&a=entry&op=list&do=houselease&m=' . MODULE_NAME, 'do' => 'houselease', 'method' => 'fun', 'status' => 1),
            array('id' => 42, 'pcate' => 4, 'displayorder' => 40, 'title' => '费用管理', 'url' => './index.php?c=site&a=entry&op=list&do=cost&m=' . MODULE_NAME, 'do' => 'cost', 'method' => 'xqcost', 'status' => 1),
            array('id' => 43, 'pcate' => 9, 'displayorder' => 41, 'title' => '小区活动', 'url' => './index.php?c=site&a=entry&op=list&do=activity&m=' . MODULE_NAME, 'do' => 'activity', 'method' => 'fun', 'status' => 1),
            array('id' => 44, 'pcate' => 9, 'displayorder' => 42, 'title' => '便民查询', 'url' => './index.php?c=site&a=entry&op=list&do=search&m=' . MODULE_NAME, 'do' => 'search', 'method' => 'fun', 'status' => 1),
            array('id' => 45, 'pcate' => 9, 'displayorder' => 43, 'title' => '便民号码', 'url' => './index.php?c=site&a=entry&op=list&do=phone&m=' . MODULE_NAME, 'do' => 'phone', 'method' => 'fun', 'status' => 1),
            array('id' => 47, 'pcate' => 9, 'displayorder' => 45, 'title' => '小区拼车', 'url' => './index.php?c=site&a=entry&op=list&do=car&m=' . MODULE_NAME, 'do' => 'car', 'method' => 'fun', 'status' => 1),
            array('id' => 48, 'pcate' => 9, 'displayorder' => 21, 'title' => '广告管理', 'url' => './index.php?c=site&a=entry&do=adv&m=' . MODULE_NAME, 'do' => 'adv', 'method' => 'fun', 'status' => 1),
            array('id' => 49, 'pcate' => 3, 'cate' => 160, 'displayorder' => 38, 'title' => '门禁管理', 'url' => './index.php?c=site&a=entry&op=list&do=guard&m=' . MODULE_NAME, 'do' => 'guard', 'method' => 'fun', 'status' => 1),
//            array('id' => 50, 'pcate' => 9, 'displayorder' => 50, 'title' => '黑名单管理', 'url' => './index.php?c=site&a=entry&op=list&do=black&m=' . MODULE_NAME, 'do' => 'black', 'method' => 'fun', 'status' => 1),
            array('id' => 51, 'pcate' => 3, 'displayorder' => 47, 'title' => '二维码管理', 'url' => './index.php?c=site&a=entry&op=list&do=qr&m=' . MODULE_NAME, 'do' => 'qr', 'method' => 'fun', 'status' => 1),
            array('id' => 52, 'pcate' => 5, 'displayorder' => 48, 'title' => '商品分类', 'url' => './index.php?c=site&a=entry&op=list&do=category&type=5&m=' . MODULE_NAME, 'do' => 'category', 'method' => 'shop', 'status' => 1),
            array('id' => 53, 'pcate' => 5, 'displayorder' => 49, 'title' => '商品管理', 'url' => './index.php?c=site&a=entry&op=goods&do=shopping&m=' . MODULE_NAME, 'do' => 'shoppinggoods', 'method' => 'shop', 'status' => 1),
            array('id' => 54, 'pcate' => 5, 'displayorder' => 50, 'title' => '订单管理', 'url' => './index.php?c=site&a=entry&op=order&do=shopping&m=' . MODULE_NAME, 'do' => 'shoppingorder', 'method' => 'shop', 'status' => 1),
            array('id' => 55, 'pcate' => 6, 'displayorder' => 51, 'title' => '店铺分类', 'url' => './index.php?c=site&a=entry&op=list&type=6&do=category&m=' . MODULE_NAME, 'do' => 'category', 'method' => 'seller', 'status' => 1),
            array('id' => 56, 'pcate' => 6, 'displayorder' => 52, 'title' => '店铺管理', 'url' => './index.php?c=site&a=entry&op=dp&do=business&m=' . MODULE_NAME, 'do' => 'businessdp', 'method' => 'seller', 'status' => 1),
            array('id' => 57, 'pcate' => 6, 'displayorder' => 53, 'title' => '商品管理', 'url' => './index.php?c=site&a=entry&op=goods&do=business&m=' . MODULE_NAME, 'do' => 'businessgoods', 'method' => 'seller', 'status' => 1),
            array('id' => 58, 'pcate' => 6, 'displayorder' => 56, 'title' => '券号核销', 'url' => './index.php?c=site&a=entry&op=coupon&do=business&m=' . MODULE_NAME, 'do' => 'businesscoupon', 'method' => 'seller', 'status' => 1),
            array('id' => 59, 'pcate' => 6, 'displayorder' => 54, 'title' => '线上订单', 'url' => './index.php?c=site&a=entry&op=order&do=business&m=' . MODULE_NAME, 'do' => 'businessorder', 'method' => 'seller', 'status' => 1),
            array('id' => 60, 'pcate' => 7, 'displayorder' => 55, 'title' => '提现申请', 'url' => './index.php?c=site&a=entry&op=list&do=cash&m=' . MODULE_NAME, 'do' => 'cashlist', 'method' => 'cash', 'status' => 1),
            array('id' => 61, 'pcate' => 8, 'displayorder' => 56, 'title' => '报修统计', 'url' => './index.php?c=site&a=entry&op=repair&do=data&m=' . MODULE_NAME, 'do' => 'datarepair', 'method' => 'xqdata', 'status' => 1),
            array('id' => 62, 'pcate' => 8, 'displayorder' => 56, 'title' => '投诉统计', 'url' => './index.php?c=site&a=entry&op=report&do=data&m=' . MODULE_NAME, 'do' => 'datareport', 'method' => 'xqdata', 'status' => 1),
            array('id' => 63, 'pcate' => 8, 'displayorder' => 56, 'title' => '住户统计', 'url' => './index.php?c=site&a=entry&op=member&do=data&m=' . MODULE_NAME, 'do' => 'datamember', 'method' => 'xqdata', 'status' => 1),
            array('id' => 64, 'pcate' => 8, 'displayorder' => 56, 'title' => '开门统计', 'url' => './index.php?c=site&a=entry&op=open&do=data&m=' . MODULE_NAME, 'do' => 'dataopen', 'method' => 'xqdata', 'status' => 1),
            array('id' => 65, 'pcate' => 8, 'displayorder' => 56, 'title' => '短信统计', 'url' => './index.php?c=site&a=entry&op=sms&do=data&m=' . MODULE_NAME, 'do' => 'datasms', 'method' => 'xqdata', 'status' => 1),
            array('id' => 66, 'pcate' => 8, 'displayorder' => 56, 'title' => '微信统计', 'url' => './index.php?c=site&a=entry&op=wechat&do=data&m=' . MODULE_NAME, 'do' => 'datawechat', 'method' => 'xqdata', 'status' => 1),
            array('id' => 67, 'pcate' => 8, 'displayorder' => 56, 'title' => '缴费订单统计', 'url' => './index.php?c=site&a=entry&op=cost&do=data&m=' . MODULE_NAME, 'do' => 'datacost', 'method' => 'xqdata', 'status' => 1),
            array('id' => 68, 'pcate' => 8, 'displayorder' => 56, 'title' => '商家订单统计', 'url' => './index.php?c=site&a=entry&op=business&do=data&m=' . MODULE_NAME, 'do' => 'databusiness', 'method' => 'xqdata', 'status' => 1),
            array('id' => 69, 'pcate' => 8, 'displayorder' => 56, 'title' => '超市订单统计', 'url' => './index.php?c=site&a=entry&op=shop&do=data&m=' . MODULE_NAME, 'do' => 'datashop', 'method' => 'xqdata', 'status' => 1),
            array('id' => 70, 'pcate' => 10, 'displayorder' => 57, 'title' => '部门管理', 'url' => './index.php?c=site&a=entry&op=branch&do=staff&m=' . MODULE_NAME, 'do' => 'staffbranch', 'method' => 'xqstaff', 'status' => 1),
            array('id' => 71, 'pcate' => 10, 'displayorder' => 58, 'title' => '通讯录', 'url' => './index.php?c=site&a=entry&op=mail&do=staff&m=' . MODULE_NAME, 'do' => 'staffmail', 'method' => 'xqstaff', 'status' => 1),
            array('id' => 72, 'pcate' => 10, 'displayorder' => 58, 'title' => '外部人员', 'url' => './index.php?c=site&a=entry&p=company&op=worker&do=staff&m=' . MODULE_NAME, 'do' => 'staffworker', 'method' => 'xqstaff', 'status' => 1),
            array('id' => 73, 'pcate' => 10, 'displayorder' => 59, 'title' => '权限设置', 'url' => './index.php?c=site&a=entry&op=perm&do=staff&m=' . MODULE_NAME, 'do' => 'staffperm', 'method' => 'xqstaff', 'status' => 1),
            array('id' => 74, 'pcate' => 10, 'displayorder' => 60, 'title' => '通知设置', 'url' => './index.php?c=site&a=entry&op=notice&do=staff&m=' . MODULE_NAME, 'do' => 'staffnotice', 'method' => 'xqstaff', 'status' => 1),
            array('id' => 75, 'pcate' => 10, 'displayorder' => 60, 'title' => '内部公告', 'url' => './index.php?c=site&a=entry&op=memo&do=staff&m=' . MODULE_NAME, 'do' => 'staffmemo', 'method' => 'xqstaff', 'status' => 1),
//            array('id' => 76, 'pcate' => 10, 'displayorder' => 62, 'title' => '打印机设置', 'url' => './index.php?c=site&a=entry&op=print&do=staff&m=' . MODULE_NAME, 'do' => 'staffprint', 'method' => 'xqstaff', 'status' => 1),
            array('id' => 77, 'pcate' => 11, 'displayorder' => 64, 'title' => '数据维护', 'url' => './index.php?c=site&a=entry&do=updatedata&m=' . MODULE_NAME, 'do' => 'systemupdate', 'method' => 'system', 'status' => 1),
            array('id' => 78, 'pcate' => 11, 'displayorder' => 65, 'title' => '菜单管理', 'url' => './index.php?c=site&a=entry&do=menu&m=' . MODULE_NAME, 'do' => 'systemmenu', 'method' => 'system', 'status' => 1),
            array('id' => 79, 'pcate' => 11, 'displayorder' => 67, 'title' => '系统授权', 'url' => './index.php?c=site&a=entry&op=display&do=system&m=' . MODULE_NAME, 'do' => 'systemdisplay', 'method' => 'system', 'status' => 1),
            array('id' => 80, 'pcate' => 11, 'displayorder' => 68, 'title' => '系统更新', 'url' => './index.php?c=site&a=entry&op=upgrade&do=system&m=' . MODULE_NAME, 'do' => 'systemupgrade', 'method' => 'system', 'status' => 1),
            array('id' => 81, 'pcate' => 11, 'displayorder' => 68, 'title' => '操作日志', 'url' => './index.php?c=site&a=entry&do=xqlog&m=' . MODULE_NAME, 'do' => 'systemxqlog', 'method' => 'system', 'status' => 1),
            array('id' => 82, 'pcate' => 11, 'displayorder' => 69, 'title' => '小区应用组', 'url' => './index.php?c=site&a=entry&do=group&m=' . MODULE_NAME, 'do' => 'systemgroup', 'method' => 'system', 'status' => 1),
        );
        foreach ($dat as $key => $val) {
            pdo_insert('xcommunity_menu', $val);
        }
        $d = array(
            array('id' => 83, 'pcate' => 1, 'cate' => 25, 'displayorder' => 70, 'title' => '基本配置', 'url' => './index.php?c=site&a=entry&do=sms&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 84, 'pcate' => 1, 'cate' => 25, 'displayorder' => 72, 'title' => '乐信通接口', 'url' => './index.php?c=site&a=entry&do=sms&op=wwt&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 85, 'pcate' => 1, 'cate' => 25, 'displayorder' => 71, 'title' => '聚合接口', 'url' => './index.php?c=site&a=entry&do=sms&op=jh&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 86, 'pcate' => 1, 'cate' => 26, 'displayorder' => 73, 'title' => '基本配置', 'url' => './index.php?c=site&a=entry&op=list&do=print&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 87, 'pcate' => 1, 'cate' => 26, 'displayorder' => 74, 'title' => '云联接口', 'url' => './index.php?c=site&a=entry&op=yl&do=print&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 88, 'pcate' => 1, 'cate' => 26, 'displayorder' => 75, 'title' => '飞印接口', 'url' => './index.php?c=site&a=entry&op=fy&do=print&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 89, 'pcate' => 1, 'cate' => 32, 'displayorder' => 76, 'title' => '配置', 'url' => './index.php?c=site&a=entry&do=payapi&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 90, 'pcate' => 1, 'cate' => 32, 'displayorder' => 77, 'title' => '支付宝', 'url' => './index.php?c=site&a=entry&do=payapi&op=alipay&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 91, 'pcate' => 1, 'cate' => 32, 'displayorder' => 78, 'title' => '微信支付', 'url' => './index.php?c=site&a=entry&do=payapi&op=wechat&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 92, 'pcate' => 1, 'cate' => 32, 'displayorder' => 79, 'title' => '服务商支付', 'url' => './index.php?c=site&a=entry&do=payapi&op=service&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 94, 'pcate' => 4, 'cate' => 42, 'displayorder' => 80, 'title' => '费用类型', 'url' => './index.php?c=site&a=entry&do=cost&op=category&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 95, 'pcate' => 4, 'cate' => 42, 'displayorder' => 81, 'title' => '费用列表', 'url' => './index.php?c=site&a=entry&op=list&do=cost&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 96, 'pcate' => 4, 'cate' => 42, 'displayorder' => 82, 'title' => '费用数据', 'url' => './index.php?c=site&a=entry&op=detail&do=cost&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 97, 'pcate' => 4, 'cate' => 40, 'displayorder' => 83, 'title' => '服务项目', 'url' => './index.php?c=site&a=entry&op=category&do=homemaking&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 98, 'pcate' => 4, 'cate' => 40, 'displayorder' => 83, 'title' => '信息管理', 'url' => './index.php?c=site&a=entry&op=list&do=homemaking&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 99, 'pcate' => 4, 'cate' => 40, 'displayorder' => 84, 'title' => '接收员管理', 'url' => './index.php?c=site&a=entry&op=manage&do=homemaking&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 100, 'pcate' => 4, 'cate' => 38, 'displayorder' => 81, 'title' => '服务分类', 'url' => './index.php?c=site&a=entry&op=list&type=2&do=category&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 101, 'pcate' => 4, 'cate' => 38, 'displayorder' => 82, 'title' => '信息管理', 'url' => './index.php?c=site&a=entry&op=list&do=repair&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 102, 'pcate' => 4, 'cate' => 38, 'displayorder' => 85, 'title' => '小区接收员', 'url' => './index.php?c=site&a=entry&op=manage&do=repair&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 103, 'pcate' => 4, 'cate' => 39, 'displayorder' => 83, 'title' => '服务分类', 'url' => './index.php?c=site&a=entry&op=list&type=3&do=category&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 104, 'pcate' => 4, 'cate' => 39, 'displayorder' => 83, 'title' => '信息管理', 'url' => './index.php?c=site&a=entry&op=list&do=report&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 105, 'pcate' => 4, 'cate' => 39, 'displayorder' => 85, 'title' => '小区接收员', 'url' => './index.php?c=site&a=entry&op=manage&do=report&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 108, 'pcate' => 10, 'cate' => 72, 'displayorder' => 83, 'title' => '公司管理', 'url' => './index.php?c=site&a=entry&p=company&op=worker&do=staff&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 109, 'pcate' => 10, 'cate' => 72, 'displayorder' => 83, 'title' => '人员管理', 'url' => './index.php?c=site&a=entry&p=list&op=worker&do=staff&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 110, 'pcate' => 11, 'cate' => 82, 'displayorder' => 83, 'title' => '用户组管理', 'url' => './index.php?c=site&a=entry&op=list&do=group&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 111, 'pcate' => 11, 'cate' => 82, 'displayorder' => 83, 'title' => '用户管理', 'url' => './index.php?c=site&a=entry&op=user&do=group&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 112, 'pcate' => 4, 'cate' => 42, 'displayorder' => 82, 'title' => '订单管理', 'url' => './index.php?c=site&a=entry&op=order&do=cost&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 113, 'pcate' => 3, 'displayorder' => 31, 'title' => '车位管理', 'url' => './index.php?c=site&a=entry&op=list&do=parking&m=' . MODULE_NAME, 'do' => 'parking', 'method' => 'fun', 'status' => 1),
            array('id' => 114, 'pcate' => 3, 'displayorder' => 32, 'title' => '车辆管理', 'url' => './index.php?c=site&a=entry&op=list&do=xqcar&m=' . MODULE_NAME, 'do' => 'xqcar', 'method' => 'fun', 'status' => 1),
//            array('id' => 115, 'pcate' => 3, 'displayorder' => 41, 'title' => '智能车禁', 'url' => './index.php?c=site&a=entry&op=list&do=zhpark&m=' . MODULE_NAME, 'do' => 'zhpark', 'method' => 'fun', 'status' => 1),
//            array('id' => 116, 'pcate' => 3, 'cate' => 115, 'displayorder' => 82, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=set&do=zhpark&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 117, 'pcate' => 3, 'cate' => 115, 'displayorder' => 82, 'title' => '停车场配置', 'url' => './index.php?c=site&a=entry&op=setting&do=zhpark&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 118, 'pcate' => 3, 'cate' => 115, 'displayorder' => 82, 'title' => '停车场管理', 'url' => './index.php?c=site&a=entry&op=parking&do=zhpark&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 119, 'pcate' => 3, 'cate' => 115, 'displayorder' => 82, 'title' => '月租车管理', 'url' => './index.php?c=site&a=entry&op=manage&do=zhpark&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 120, 'pcate' => 3, 'cate' => 115, 'displayorder' => 82, 'title' => '车辆延期记录', 'url' => './index.php?c=site&a=entry&op=record&do=zhpark&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 121, 'pcate' => 2, 'displayorder' => 18, 'title' => '应用入口', 'url' => './index.php?c=site&a=entry&do=cover&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 122, 'pcate' => 3, 'displayorder' => 33, 'title' => '区域管理', 'url' => './index.php?c=site&a=entry&do=area&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 123, 'pcate' => 3, 'displayorder' => 34, 'title' => '楼宇管理', 'url' => './index.php?c=site&a=entry&do=build&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 124, 'pcate' => 4, 'cate' => 147, 'displayorder' => 35, 'title' => '收银台', 'url' => './index.php?c=site&a=entry&op=center&do=fee&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 125, 'pcate' => 4, 'cate' => 147, 'displayorder' => 36, 'title' => '收费项目', 'url' => './index.php?c=site&a=entry&op=category&do=fee&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 126, 'pcate' => 4, 'cate' => 147, 'displayorder' => 37, 'title' => '收费分组', 'url' => './index.php?c=site&a=entry&op=group&do=fee&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 127, 'pcate' => 4, 'cate' => 147, 'displayorder' => 38, 'title' => '物业账单', 'url' => './index.php?c=site&a=entry&op=list&do=fee&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 128, 'pcate' => 4, 'cate' => 147, 'displayorder' => 39, 'title' => '抄表录入', 'url' => './index.php?c=site&a=entry&op=entery&do=fee&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 129, 'pcate' => 4, 'displayorder' => 41, 'title' => '收支管理', 'url' => './index.php?c=site&a=entry&do=balance&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 130, 'pcate' => 1, 'cate' => 129, 'displayorder' => 46, 'title' => '收支项目', 'url' => './index.php?c=site&a=entry&op=category&do=balance&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 131, 'pcate' => 1, 'cate' => 129, 'displayorder' => 47, 'title' => '收支登记', 'url' => './index.php?c=site&a=entry&op=list&do=balance&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 132, 'pcate' => 1, 'cate' => 32, 'displayorder' => 80, 'title' => '微信支付(兴业/农商/中信银行)', 'url' => './index.php?c=site&a=entry&do=payapi&op=swiftpass&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 133, 'pcate' => 9, 'displayorder' => 34, 'title' => '红包营销', 'url' => './index.php?c=site&a=entry&do=advertiser&m=' . MODULE_NAME, 'do' => 'advertiser', 'method' => 'fun', 'status' => 1),
            array('id' => 134, 'pcate' => 1, 'cate' => 133, 'displayorder' => 45, 'title' => '设置', 'url' => './index.php?c=site&a=entry&op=set&do=advertiser&m=' . MODULE_NAME, 'do' => 'advertiser', 'method' => 'fun', 'status' => 1),
            array('id' => 135, 'pcate' => 1, 'cate' => 133, 'displayorder' => 45, 'title' => '广告管理', 'url' => './index.php?c=site&a=entry&op=manage&do=advertiser&m=' . MODULE_NAME, 'do' => 'advertiser', 'method' => 'fun', 'status' => 1),
            array('id' => 136, 'pcate' => 1, 'cate' => 133, 'displayorder' => 45, 'title' => '财务管理', 'url' => './index.php?c=site&a=entry&op=finance&do=advertiser&m=' . MODULE_NAME, 'do' => 'advertiser', 'method' => 'fun', 'status' => 1),
            array('id' => 137, 'pcate' => 9, 'cate' => 45, 'displayorder' => 82, 'title' => '分类管理', 'url' => './index.php?c=site&a=entry&op=list&type=8&do=category&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 138, 'pcate' => 9, 'displayorder' => 45, 'title' => '小区集市', 'url' => './index.php?c=site&a=entry&op=list&do=market&m=' . MODULE_NAME, 'do' => 'market', 'method' => 'fun', 'status' => 1),
            array('id' => 139, 'pcate' => 9, 'cate' => 45, 'displayorder' => 83, 'title' => '号码管理', 'url' => './index.php?c=site&a=entry&op=list&do=phone&m=' . MODULE_NAME, 'do' => 'phone', 'method' => 'fun', 'status' => 1),
            array('id' => 140, 'pcate' => 9, 'cate' => 138, 'displayorder' => 83, 'title' => '分类管理', 'url' => './index.php?c=site&a=entry&type=4&do=category&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 141, 'pcate' => 9, 'cate' => 138, 'displayorder' => 83, 'title' => '信息管理', 'url' => './index.php?c=site&a=entry&op=list&do=market&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 142, 'pcate' => 5, 'displayorder' => 50, 'title' => '地址管理', 'url' => './index.php?c=site&a=entry&op=address&do=shopping&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
            array('id' => 143, 'pcate' => 2, 'displayorder' => 16, 'title' => '住户中心', 'url' => './index.php?c=site&a=entry&do=housecenter&m=' . MODULE_NAME, 'do' => 'housecenter', 'method' => 'manage', 'status' => 1),
            array('id' => 144, 'pcate' => 4, 'cate' => 38, 'displayorder' => 83, 'title' => '手工报修', 'url' => './index.php?c=site&a=entry&op=display&do=repair&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 145, 'pcate' => 11, 'displayorder' => 63, 'title' => '短信记录', 'url' => './index.php?c=site&a=entry&op=sms&do=log&m=' . MODULE_NAME, 'do' => 'systemlog', 'method' => 'system', 'status' => 1),
//            array('id' => 146, 'pcate' => 11, 'displayorder' => 63, 'title' => '分成记录', 'url' => './index.php?c=site&a=entry&op=commission&do=log&m=' . MODULE_NAME, 'do' => 'systemcommission', 'method' => 'system', 'status' => 1),
            array('id' => 147, 'pcate' => 4, 'displayorder' => 39, 'title' => '账单管理', 'url' => '', 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 148, 'pcate' => 9, 'displayorder' => 22, 'title' => '问卷调查', 'url' => './index.php?c=site&a=entry&do=vote&m=' . MODULE_NAME, 'do' => 'vote', 'method' => 'fun', 'status' => 1),
            array('id' => 149, 'pcate' => 9, 'displayorder' => 35, 'title' => '无人超市', 'url' => './index.php?c=site&a=entry&do=supermark&m=' . MODULE_NAME, 'do' => 'supermark', 'method' => 'fun', 'status' => 1),
            array('id' => 150, 'pcate' => 9, 'cate' => 149, 'displayorder' => 45, 'title' => '超市管理', 'url' => './index.php?c=site&a=entry&op=dp&do=supermark&m=' . MODULE_NAME, 'do' => 'supermark', 'method' => 'fun', 'status' => 1),
            array('id' => 151, 'pcate' => 9, 'cate' => 149, 'displayorder' => 46, 'title' => '订单管理', 'url' => './index.php?c=site&a=entry&op=order&do=supermark&m=' . MODULE_NAME, 'do' => 'supermark', 'method' => 'fun', 'status' => 1),
            array('id' => 152, 'pcate' => 3, 'displayorder' => 30, 'title' => '物业风采', 'url' => './index.php?c=site&a=entry&op=list&do=mien&m=' . MODULE_NAME, 'do' => 'mien', 'method' => 'fun', 'status' => 1),
            array('id' => 153, 'pcate' => 4, 'cate' => 147, 'displayorder' => 41, 'title' => '账单记录', 'url' => './index.php?c=site&a=entry&op=log&do=fee&m=' . MODULE_NAME, 'do' => 'fee', 'method' => 'fun', 'status' => 1),
            array('id' => 154, 'pcate' => 4, 'cate' => 147, 'displayorder' => 40, 'title' => '账单统计', 'url' => './index.php?c=site&a=entry&op=order&do=fee&m=' . MODULE_NAME, 'do' => 'fee', 'method' => 'fun', 'status' => 1),
            array('id' => 155, 'pcate' => 3, 'displayorder' => 42, 'title' => '安防巡更', 'url' => './index.php?c=site&a=entry&op=list&do=safety&m=' . MODULE_NAME, 'do' => 'safety', 'method' => 'fun', 'status' => 1),
            array('id' => 156, 'pcate' => 9, 'cate' => 45, 'displayorder' => 84, 'title' => '入驻申请', 'url' => './index.php?c=site&a=entry&op=apply&do=phone&m=' . MODULE_NAME, 'do' => 'phone', 'method' => 'fun', 'status' => 1),
            array('id' => 157, 'pcate' => 3, 'cate' => 155, 'displayorder' => 83, 'title' => '巡更路线', 'url' => './index.php?c=site&a=entry&op=line&do=safety&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 158, 'pcate' => 1, 'displayorder' => 25, 'title' => '小程序设置', 'url' => './index.php?c=site&a=entry&do=wxapp&m=' . MODULE_NAME, 'do' => 'wxapp', 'method' => 'wxapp', 'status' => 1),
//            array('id' => 159, 'pcate' => 3, 'cate' => 115, 'displayorder' => 82, 'title' => '临时车缴费记录', 'url' => './index.php?c=site&a=entry&op=temp&do=zhpark&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 160, 'pcate' => 3, 'displayorder' => 40, 'title' => '智能门禁', 'url' => './index.php?c=site&a=entry&op=list&do=guard&m=' . MODULE_NAME, 'do' => 'guard', 'method' => 'fun', 'status' => 1),
            array('id' => 161, 'pcate' => 3, 'cate' => 160, 'displayorder' => 37, 'title' => '门禁分组', 'url' => './index.php?c=site&a=entry&op=group&do=guard&m=' . MODULE_NAME, 'do' => 'guard', 'method' => 'fun', 'status' => 1),
            array('id' => 162, 'pcate' => 3, 'cate' => 155, 'displayorder' => 82, 'title' => '巡更分布', 'url' => './index.php?c=site&a=entry&op=list&do=safety&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 163, 'pcate' => 10, 'displayorder' => 61, 'title' => '入驻申请', 'url' => './index.php?c=site&a=entry&op=list&do=settled&m=' . MODULE_NAME, 'do' => 'settled', 'method' => 'xqstaff', 'status' => 1),
            array('id' => 164, 'pcate' => 4, 'cate' => 39, 'displayorder' => 83, 'title' => '手工建议', 'url' => './index.php?c=site&a=entry&op=display&do=report&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 165, 'displayorder' => 5, 'pcate' => 0, 'title' => '征信管理', 'url' => './index.php?c=site&a=entry&do=rank&method=rank&m=' . MODULE_NAME, 'do' => 'rank', 'method' => 'rank', 'status' => 1, 'icon' => 'fa fa-commenting-o'),
//            array('id' => 166, 'pcate' => 165, 'displayorder' => 22, 'title' => '物业管理', 'url' => './index.php?c=site&a=entry&do=rank&m=' . MODULE_NAME, 'do' => 'rank', 'method' => 'property', 'status' => 1),
//            array('id' => 167, 'pcate' => 165, 'displayorder' => 23, 'title' => '评分管理', 'url' => './index.php?c=site&a=entry&do=rank&op=mark&m=' . MODULE_NAME, 'do' => 'rank', 'method' => 'mark', 'status' => 1),
//            array('id' => 168, 'pcate' => 165, 'cate' => 166, 'displayorder' => 83, 'title' => '物业资料', 'url' => './index.php?c=site&a=entry&op=property&do=rank&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 169, 'pcate' => 165, 'cate' => 166, 'displayorder' => 85, 'title' => '评分项目', 'url' => './index.php?c=site&a=entry&op=category&do=rank&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 170, 'pcate' => 165, 'cate' => 166, 'displayorder' => 86, 'title' => '接收员管理', 'url' => './index.php?c=site&a=entry&op=manage&do=rank&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 171, 'pcate' => 9, 'displayorder' => 45, 'title' => '智能货柜', 'url' => './index.php?c=site&a=entry&op=list&do=counter&m=' . MODULE_NAME, 'do' => 'counter', 'method' => 'counter', 'status' => 1),
//            array('id' => 172, 'pcate' => 9, 'cate' => 171, 'displayorder' => 80, 'title' => '参数设置', 'url' => './index.php?c=site&a=entry&op=set&do=counter&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 173, 'pcate' => 9, 'cate' => 171, 'displayorder' => 81, 'title' => '柜子管理', 'url' => './index.php?c=site&a=entry&op=list&do=counter&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//废弃的生鲜超市            array('id' => 174, 'pcate' => 9, 'cate' => 171, 'displayorder' => 87, 'title' => '接收员管理', 'url' => './index.php?c=site&a=entry&op=manage&do=counter&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 175, 'pcate' => 9, 'displayorder' => 22, 'title' => '信息发布', 'url' => './index.php?c=site&a=entry&op=list&do=article&m=' . MODULE_NAME, 'do' => 'article', 'method' => 'article', 'status' => 1),
            array('id' => 176, 'pcate' => 9, 'cate' => 175, 'displayorder' => 85, 'title' => '信息管理', 'url' => './index.php?c=site&a=entry&op=list&do=article&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 177, 'pcate' => 9, 'cate' => 175, 'displayorder' => 84, 'title' => '分类管理', 'url' => './index.php?c=site&a=entry&op=category&do=article&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 178, 'pcate' => 1, 'cate' => 32, 'displayorder' => 81, 'title' => '微信支付(华商云付)', 'url' => './index.php?c=site&a=entry&do=payapi&op=hsyunfu&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 179, 'pcate' => 9, 'displayorder' => 22, 'title' => '快递代收', 'url' => './index.php?c=site&a=entry&op=list&do=express&m=' . MODULE_NAME, 'do' => 'express', 'method' => 'express', 'status' => 1),
            array('id' => 180, 'pcate' => 9, 'cate' => 179, 'displayorder' => 80, 'title' => '物流公司', 'url' => './index.php?c=site&a=entry&op=company&do=express&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 181, 'pcate' => 9, 'cate' => 179, 'displayorder' => 81, 'title' => '寄件类型', 'url' => './index.php?c=site&a=entry&op=type&do=express&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 182, 'pcate' => 9, 'cate' => 179, 'displayorder' => 82, 'title' => '代收管理', 'url' => './index.php?c=site&a=entry&op=collect&do=express&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 183, 'pcate' => 9, 'cate' => 179, 'displayorder' => 83, 'title' => '入库管理', 'url' => './index.php?c=site&a=entry&op=save&do=express&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 184, 'pcate' => 9, 'cate' => 179, 'displayorder' => 84, 'title' => '价格管理', 'url' => './index.php?c=site&a=entry&op=price&do=express&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 185, 'pcate' => 9, 'cate' => 179, 'displayorder' => 85, 'title' => '寄件管理', 'url' => './index.php?c=site&a=entry&op=parcel&do=express&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 186, 'pcate' => 9, 'cate' => 179, 'displayorder' => 86, 'title' => '模版管理', 'url' => './index.php?c=site&a=entry&op=tpl&do=express&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 187, 'pcate' => 165, 'cate' => 166, 'displayorder' => 84, 'title' => '小区资料', 'url' => './index.php?c=site&a=entry&op=region&do=rank&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//废弃的生鲜超市            array('id' => 189, 'pcate' => 9, 'cate' => 171, 'displayorder' => 82, 'title' => '商品分类', 'url' => './index.php?c=site&a=entry&op=list&do=category&type=10&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//废弃的生鲜超市            array('id' => 190, 'pcate' => 9, 'cate' => 171, 'displayorder' => 83, 'title' => '商品管理', 'url' => './index.php?c=site&a=entry&op=shopping&p=goods&do=counter&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//废弃的生鲜超市            array('id' => 191, 'pcate' => 9, 'cate' => 171, 'displayorder' => 84, 'title' => '订单管理', 'url' => './index.php?c=site&a=entry&op=shopping&p=order&do=counter&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//废弃的生鲜超市            array('id' => 192, 'pcate' => 9, 'cate' => 171, 'displayorder' => 85, 'title' => '地址管理', 'url' => './index.php?c=site&a=entry&op=shopping&p=address&do=counter&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 193, 'pcate' => 9, 'displayorder' => 46, 'title' => '智能充电桩', 'url' => './index.php?c=site&a=entry&op=list&do=charging&m=' . MODULE_NAME, 'do' => 'charging', 'method' => 'charging', 'status' => 1),
            array('id' => 194, 'pcate' => 9, 'cate' => 193, 'displayorder' => 80, 'title' => '设置', 'url' => './index.php?c=site&a=entry&op=setting&do=charging&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 195, 'pcate' => 9, 'cate' => 193, 'displayorder' => 82, 'title' => '充电桩', 'url' => './index.php?c=site&a=entry&op=station&p=list&do=charging&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 196, 'pcate' => 9, 'cate' => 193, 'displayorder' => 83, 'title' => '充电费用', 'url' => './index.php?c=site&a=entry&op=price&p=list&do=charging&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 197, 'pcate' => 9, 'cate' => 193, 'displayorder' => 85, 'title' => '充电记录', 'url' => './index.php?c=site&a=entry&op=order&p=list&do=charging&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 198, 'pcate' => 3, 'cate' => 160, 'displayorder' => 85, 'title' => '发卡管理', 'url' => './index.php?c=site&a=entry&op=comb&do=guard&m=' . MODULE_NAME, 'do' => 'guard', 'method' => '', 'status' => 1),
            array('id' => 199, 'pcate' => 3, 'cate' => 160, 'displayorder' => 86, 'title' => '刷卡记录', 'url' => './index.php?c=site&a=entry&op=log&do=guard&m=' . MODULE_NAME, 'do' => 'guard', 'method' => '', 'status' => 1),
            array('id' => 200, 'pcate' => 1, 'cate' => 25, 'displayorder' => 85, 'title' => '阿里云接口', 'url' => './index.php?c=site&a=entry&op=aliyun_new&do=sms&m=' . MODULE_NAME, 'do' => 'sms', 'method' => '', 'status' => 1),
            array('id' => 201, 'pcate' => 2, 'displayorder' => 12, 'title' => '头条广告', 'url' => './index.php?c=site&a=entry&op=list&do=headadv&m=' . MODULE_NAME, 'do' => 'headadv', 'method' => 'headadv', 'status' => 1),
//            array('id' => 202, 'pcate' => 165, 'displayorder' => 24, 'title' => '投诉管理', 'url' => './index.php?c=site&a=entry&do=rank&op=report&m=' . MODULE_NAME, 'do' => 'rank', 'method' => 'mark', 'status' => 1),
//            array('id' => 203, 'pcate' => 165, 'cate' => 202, 'displayorder' => 25, 'title' => '信息管理', 'url' => './index.php?c=site&a=entry&op=report&do=rank&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 204, 'pcate' => 165, 'cate' => 202, 'displayorder' => 26, 'title' => '处理管理', 'url' => './index.php?c=site&a=entry&op=grab&do=rank&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 205, 'pcate' => 165, 'cate' => 202, 'displayorder' => 27, 'title' => '房管接收员', 'url' => './index.php?c=site&a=entry&op=notice&do=rank&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 206, 'pcate' => 9, 'displayorder' => 23, 'title' => '话题圈子', 'url' => './index.php?c=site&a=entry&op=list&do=bbs&m=' . MODULE_NAME, 'do' => 'bbs', 'method' => 'bbs', 'status' => 1),
//            array('id' => 207, 'pcate' => 9, 'cate' => 206, 'displayorder' => 80, 'title' => '圈子设置', 'url' => './index.php?c=site&a=entry&op=set&do=bbs&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 208, 'pcate' => 9, 'cate' => 206, 'displayorder' => 80, 'title' => '广告管理', 'url' => './index.php?c=site&a=entry&op=adv&do=bbs&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 209, 'pcate' => 9, 'cate' => 206, 'displayorder' => 80, 'title' => '分类管理', 'url' => './index.php?c=site&a=entry&op=category&do=bbs&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 210, 'pcate' => 9, 'cate' => 206, 'displayorder' => 80, 'title' => '帖子管理', 'url' => './index.php?c=site&a=entry&op=list&do=bbs&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 211, 'pcate' => 9, 'cate' => 206, 'displayorder' => 80, 'title' => '用户管理', 'url' => './index.php?c=site&a=entry&op=user&do=bbs&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 212, 'pcate' => 9, 'cate' => 206, 'displayorder' => 80, 'title' => '打赏管理', 'url' => './index.php?c=site&a=entry&op=gave&do=bbs&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 213, 'pcate' => 9, 'cate' => 206, 'displayorder' => 80, 'title' => '提现管理', 'url' => './index.php?c=site&a=entry&op=cash&do=bbs&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 214, 'pcate' => 9, 'cate' => 43, 'displayorder' => 41, 'title' => '小区活动', 'url' => './index.php?c=site&a=entry&op=list&do=activity&m=' . MODULE_NAME, 'do' => 'activity', 'method' => 'fun', 'status' => 1),
            array('id' => 215, 'pcate' => 9, 'cate' => 43, 'displayorder' => 42, 'title' => '接收员管理', 'url' => './index.php?c=site&a=entry&op=manage&do=activity&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 216, 'pcate' => 4, 'cate' => 38, 'displayorder' => 84, 'title' => '维修费用', 'url' => './index.php?c=site&a=entry&op=order&do=repair&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 217, 'pcate' => 3, 'cate' => 160, 'displayorder' => 64, 'title' => '用户管理', 'url' => './index.php?c=site&a=entry&op=member&do=guard&m=' . MODULE_NAME, 'do' => 'guard', 'method' => '', 'status' => 1),
            array('id' => 218, 'pcate' => 3, 'cate' => 160, 'displayorder' => 86, 'title' => '开门记录', 'url' => './index.php?c=site&a=entry&op=open&do=guard&m=' . MODULE_NAME, 'do' => 'guard', 'method' => '', 'status' => 1),
            array('id' => 219, 'pcate' => 6, 'displayorder' => 57, 'title' => '打印机设置', 'url' => './index.php?c=site&a=entry&op=print&do=business&m=' . MODULE_NAME, 'do' => 'businesscoupon', 'method' => 'seller', 'status' => 1),
            array('id' => 220, 'pcate' => 6, 'displayorder' => 58, 'title' => '接收员管理', 'url' => './index.php?c=site&a=entry&op=wechat&do=business&m=' . MODULE_NAME, 'do' => 'businesscoupon', 'method' => 'seller', 'status' => 1),
            array('id' => 221, 'pcate' => 5, 'displayorder' => 47, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=sets&do=shopping&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 222, 'pcate' => 6, 'displayorder' => 50, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=set&do=business&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 223, 'pcate' => 9, 'cate' => 138, 'displayorder' => 82, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=set&do=market&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 224, 'pcate' => 9, 'cate' => 41, 'displayorder' => 40, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=set&do=houselease&m=' . MODULE_NAME, 'do' => 'houselease', 'method' => 'fun', 'status' => 1),
            array('id' => 225, 'pcate' => 9, 'cate' => 41, 'displayorder' => 41, 'title' => '租售管理', 'url' => './index.php?c=site&a=entry&op=list&do=houselease&m=' . MODULE_NAME, 'do' => 'houselease', 'method' => 'fun', 'status' => 1),
            array('id' => 226, 'pcate' => 9, 'cate' => 47, 'displayorder' => 45, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=set&do=car&m=' . MODULE_NAME, 'do' => 'car', 'method' => 'fun', 'status' => 1),
            array('id' => 227, 'pcate' => 9, 'cate' => 47, 'displayorder' => 45, 'title' => '拼车管理', 'url' => './index.php?c=site&a=entry&op=list&do=car&m=' . MODULE_NAME, 'do' => 'car', 'method' => 'fun', 'status' => 1),
            array('id' => 228, 'pcate' => 3, 'cate' => 160, 'displayorder' => 36, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=set&do=guard&m=' . MODULE_NAME, 'do' => 'guard', 'method' => 'fun', 'status' => 1),
            array('id' => 229, 'pcate' => 3, 'cate' => 22, 'displayorder' => 27, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=set&do=set&m=' . MODULE_NAME, 'do' => 'set', 'method' => 'set', 'status' => 1),
            array('id' => 230, 'pcate' => 4, 'displayorder' => 38, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=set&do=cost&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 231, 'pcate' => 4, 'cate' => 40, 'displayorder' => 82, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=set&do=homemaking&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 232, 'pcate' => 3, 'cate' => 22, 'displayorder' => 27, 'title' => '统一注册方式', 'url' => './index.php?c=site&a=entry&op=register&do=set&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 233, 'pcate' => 1, 'cate' => 25, 'displayorder' => 86, 'title' => '企汇云信接口', 'url' => './index.php?c=site&a=entry&op=qhyx&do=sms&m=' . MODULE_NAME, 'do' => 'sms', 'method' => '', 'status' => 1),
            array('id' => 234, 'pcate' => 6, 'displayorder' => 59, 'title' => '支付接口设置', 'url' => './index.php?c=site&a=entry&op=payapi&do=business&m=' . MODULE_NAME, 'do' => 'businesscoupon', 'method' => 'seller', 'status' => 1),
            array('id' => 235, 'pcate' => 6, 'cate' => 234, 'displayorder' => 59, 'title' => '支付宝', 'url' => './index.php?c=site&a=entry&op=payapi&do=business&p=alipay&m=' . MODULE_NAME, 'do' => 'businesscoupon', 'method' => 'seller', 'status' => 1),
            array('id' => 236, 'pcate' => 6, 'cate' => 234, 'displayorder' => 60, 'title' => '微信支付', 'url' => './index.php?c=site&a=entry&op=payapi&do=business&p=wechat&m=' . MODULE_NAME, 'do' => 'businesscoupon', 'method' => 'seller', 'status' => 1),
            array('id' => 237, 'pcate' => 6, 'cate' => 234, 'displayorder' => 61, 'title' => '服务商支付', 'url' => './index.php?c=site&a=entry&op=payapi&do=business&p=sub&m=' . MODULE_NAME, 'do' => 'businesscoupon', 'method' => 'seller', 'status' => 1),
            array('id' => 238, 'pcate' => 6, 'cate' => 234, 'displayorder' => 62, 'title' => '微信支付（兴业/农商/中信银行）', 'url' => './index.php?c=site&a=entry&op=payapi&do=business&p=swiftpass&m=' . MODULE_NAME, 'do' => 'businesscoupon', 'method' => 'seller', 'status' => 1),
            array('id' => 239, 'pcate' => 6, 'cate' => 234, 'displayorder' => 63, 'title' => '微信支付（华商云付）', 'url' => './index.php?c=site&a=entry&op=payapi&do=business&p=hsyunfu&m=' . MODULE_NAME, 'do' => 'businesscoupon', 'method' => 'seller', 'status' => 1),
            array('id' => 240, 'pcate' => 5, 'displayorder' => 56, 'title' => '支付接口设置', 'url' => './index.php?c=site&a=entry&op=payapi&do=shopping&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
            array('id' => 241, 'pcate' => 5, 'cate' => 240, 'displayorder' => 52, 'title' => '支付宝', 'url' => './index.php?c=site&a=entry&op=payapi&do=shopping&p=alipay&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
            array('id' => 242, 'pcate' => 5, 'cate' => 240, 'displayorder' => 53, 'title' => '微信支付', 'url' => './index.php?c=site&a=entry&op=payapi&do=shopping&p=wechat&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
            array('id' => 243, 'pcate' => 5, 'cate' => 240, 'displayorder' => 54, 'title' => '服务商支付', 'url' => './index.php?c=site&a=entry&op=payapi&do=shopping&p=sub&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
            array('id' => 244, 'pcate' => 5, 'cate' => 240, 'displayorder' => 55, 'title' => '微信支付（兴业/农商/中信银行）', 'url' => './index.php?c=site&a=entry&op=payapi&do=shopping&p=swiftpass&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
            array('id' => 245, 'pcate' => 5, 'cate' => 240, 'displayorder' => 56, 'title' => '微信支付（华商云付）', 'url' => './index.php?c=site&a=entry&op=payapi&do=shopping&p=hsyunfu&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
            array('id' => 246, 'pcate' => 5, 'displayorder' => 47, 'title' => '超市管理', 'url' => './index.php?c=site&a=entry&op=shop&do=shopping&m=' . MODULE_NAME, 'do' => 'shoppinggoods', 'method' => 'shop', 'status' => 1),
            array('id' => 247, 'pcate' => 5, 'displayorder' => 51, 'title' => '打印机设置', 'url' => './index.php?c=site&a=entry&op=print&do=shopping&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
            array('id' => 248, 'pcate' => 5, 'displayorder' => 52, 'title' => '接收员设置', 'url' => './index.php?c=site&a=entry&op=wechat&do=shopping&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
            array('id' => 249, 'pcate' => 9, 'cate' => 47, 'displayorder' => 46, 'title' => '黑名单管理', 'url' => './index.php?c=site&a=entry&op=black&do=car&m=' . MODULE_NAME, 'do' => 'car', 'method' => 'fun', 'status' => 1),
            array('id' => 250, 'pcate' => 9, 'cate' => 138, 'displayorder' => 84, 'title' => '黑名单管理', 'url' => './index.php?c=site&a=entry&op=black&do=market&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 251, 'displayorder' => 5, 'pcate' => 0, 'title' => '会员管理', 'url' => './index.php?c=site&a=entry&do=users&method=users&m=', 'do' => 'users', 'method' => 'users', 'status' => 1, 'icon' => 'fa fa-user'),
            array('id' => 252, 'pcate' => 251, 'displayorder' => 80, 'title' => '会员管理', 'url' => './index.php?c=site&a=entry&op=list&do=address&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 253, 'pcate' => 251, 'displayorder' => 82, 'title' => '地址管理', 'url' => './index.php?c=site&a=entry&op=address&do=address&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 254, 'pcate' => 4, 'displayorder' => 42, 'title' => '接收员管理', 'url' => './index.php?c=site&a=entry&do=cost&op=wechat&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 255, 'pcate' => 9, 'cate' => 193, 'displayorder' => 80, 'title' => '运营商', 'url' => './index.php?c=site&a=entry&op=throw&do=charging&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 256, 'displayorder' => 7, 'pcate' => 0, 'title' => '城乡直通车', 'url' => './index.php?c=site&a=entry&do=direct&method=direct&m=' . MODULE_NAME, 'do' => 'direct', 'method' => 'direct', 'status' => 1, 'icon' => 'fa fa-bus'),
//            array('id' => 257, 'pcate' => 256, 'displayorder' => 40, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=sets&do=direct&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 258, 'pcate' => 256, 'displayorder' => 41, 'title' => '超市管理', 'url' => './index.php?c=site&a=entry&op=shop&do=direct&m=' . MODULE_NAME, 'do' => 'shoppinggoods', 'method' => 'shop', 'status' => 1),
//            array('id' => 259, 'pcate' => 256, 'displayorder' => 42, 'title' => '商品分类', 'url' => './index.php?c=site&a=entry&op=list&do=category&type=10&m=' . MODULE_NAME, 'do' => 'category', 'method' => 'shop', 'status' => 1),
//            array('id' => 260, 'pcate' => 256, 'displayorder' => 43, 'title' => '商品管理', 'url' => './index.php?c=site&a=entry&op=goods&do=direct&m=' . MODULE_NAME, 'do' => 'shoppinggoods', 'method' => 'shop', 'status' => 1),
//            array('id' => 261, 'pcate' => 256, 'displayorder' => 44, 'title' => '订单管理', 'url' => './index.php?c=site&a=entry&op=order&do=direct&m=' . MODULE_NAME, 'do' => 'shoppingorder', 'method' => 'shop', 'status' => 1),
//            array('id' => 262, 'pcate' => 256, 'displayorder' => 45, 'title' => '打印机设置', 'url' => './index.php?c=site&a=entry&op=print&do=direct&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
//            array('id' => 263, 'pcate' => 256, 'displayorder' => 46, 'title' => '接收员设置', 'url' => './index.php?c=site&a=entry&op=wechat&do=direct&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
//            array('id' => 264, 'pcate' => 256, 'displayorder' => 47, 'title' => '支付接口设置', 'url' => './index.php?c=site&a=entry&op=payapi&do=direct&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
//            array('id' => 265, 'pcate' => 5, 'cate' => 264, 'displayorder' => 48, 'title' => '支付宝', 'url' => './index.php?c=site&a=entry&op=payapi&do=direct&p=alipay&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
//            array('id' => 266, 'pcate' => 5, 'cate' => 264, 'displayorder' => 49, 'title' => '微信支付', 'url' => './index.php?c=site&a=entry&op=payapi&do=direct&p=wechat&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
//            array('id' => 267, 'pcate' => 5, 'cate' => 264, 'displayorder' => 50, 'title' => '服务商支付', 'url' => './index.php?c=site&a=entry&op=payapi&do=direct&p=sub&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
//            array('id' => 268, 'pcate' => 5, 'cate' => 264, 'displayorder' => 51, 'title' => '微信支付（兴业银行）', 'url' => './index.php?c=site&a=entry&op=payapi&do=direct&p=swiftpass&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
//            array('id' => 269, 'pcate' => 5, 'cate' => 264, 'displayorder' => 52, 'title' => '微信支付（华商云付）', 'url' => './index.php?c=site&a=entry&op=payapi&do=direct&p=hsyunfu&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
            array('id' => 270, 'pcate' => 5, 'cate' => 240, 'displayorder' => 57, 'title' => '微信支付（银联）', 'url' => './index.php?c=site&a=entry&op=payapi&do=shopping&p=chinaums&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
            array('id' => 271, 'pcate' => 6, 'cate' => 234, 'displayorder' => 64, 'title' => '微信支付（银联）', 'url' => './index.php?c=site&a=entry&op=payapi&do=business&p=chinaums&m=' . MODULE_NAME, 'do' => 'businesscoupon', 'method' => 'seller', 'status' => 1),
//            array('id' => 272, 'pcate' => 5, 'cate' => 264, 'displayorder' => 53, 'title' => '微信支付（银联）', 'url' => './index.php?c=site&a=entry&op=payapi&do=direct&p=chinaums&m=' . MODULE_NAME, 'do' => 'shoppingaddress', 'method' => 'shop', 'status' => 1),
//            array('id' => 273, 'displayorder' => 4, 'pcate' => 0, 'title' => '房屋收费', 'url' => './index.php?c=site&a=entry&do=homefee&method=homefee&m=' . MODULE_NAME, 'do' => 'homefee', 'method' => 'homefee', 'status' => 1, 'icon' => 'fa fa-home'),
//            array('id' => 275, 'pcate' => 273, 'displayorder' => 65, 'title' => '收费分组', 'url' => './index.php?c=site&a=entry&op=group&do=homefee&m=' . MODULE_NAME, 'do' => 'homefee', 'method' => 'homefee', 'status' => 1),
//            array('id' => 276, 'pcate' => 273, 'displayorder' => 65, 'title' => '物业账单', 'url' => './index.php?c=site&a=entry&op=list&do=homefee&m=' . MODULE_NAME, 'do' => 'homefee', 'method' => 'homefee', 'status' => 1),
//            array('id' => 277, 'pcate' => 273, 'displayorder' => 65, 'title' => '账单统计', 'url' => './index.php?c=site&a=entry&op=order&do=homefee&m=' . MODULE_NAME, 'do' => 'homefee', 'method' => 'homefee', 'status' => 1),
//            array('id' => 278, 'pcate' => 273, 'displayorder' => 65, 'title' => '房租合同', 'url' => './index.php?c=site&a=entry&op=pact&do=homefee&m=' . MODULE_NAME, 'do' => 'homefee', 'method' => 'homefee', 'status' => 1),
//            array('id' => 279, 'pcate' => 251, 'cate' => 0, 'displayorder' => 79, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=set&do=address&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
//            array('id' => 280, 'pcate' => 273, 'cate' => 278, 'displayorder' => 65, 'title' => '合同模版', 'url' => './index.php?c=site&a=entry&op=pact&p=list&do=homefee&m=' . MODULE_NAME, 'do' => 'homefee', 'method' => 'homefee', 'status' => 1),
//            array('id' => 281, 'pcate' => 273, 'cate' => 278, 'displayorder' => 66, 'title' => '合同管理', 'url' => './index.php?c=site&a=entry&op=pact&p=plist&do=homefee&m=' . MODULE_NAME, 'do' => 'homefee', 'method' => 'homefee', 'status' => 1),
//            array('id' => 282, 'pcate' => 273, 'cate' => 278, 'displayorder' => 67, 'title' => '合同存档', 'url' => './index.php?c=site&a=entry&op=pact&p=save&do=homefee&m=' . MODULE_NAME, 'do' => 'homefee', 'method' => 'homefee', 'status' => 1),

            array('id' => 283, 'pcate' => 4, 'cate' => 38, 'displayorder' => 86, 'title' => '分管接收员', 'url' => './index.php?c=site&a=entry&op=pmanage&do=repair&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 284, 'pcate' => 4, 'cate' => 39, 'displayorder' => 86, 'title' => '分管接收员', 'url' => './index.php?c=site&a=entry&op=pmanage&do=report&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 285, 'pcate' => 3, 'cate' => 160, 'displayorder' => 87, 'title' => '开门统计', 'url' => './index.php?c=site&a=entry&op=data&do=guard&m=' . MODULE_NAME, 'do' => 'guard', 'method' => '', 'status' => 1),
            array('id' => 286, 'pcate' => 4, 'cate' => 147, 'displayorder' => 42, 'title' => '数据统计', 'url' => './index.php?c=site&a=entry&op=data&do=fee&m=' . MODULE_NAME, 'do' => 'fee', 'method' => 'fun', 'status' => 1),
            array('id' => 287, 'pcate' => 9, 'cate' => 193, 'displayorder' => 86, 'title' => '充值记录', 'url' => './index.php?c=site&a=entry&op=credit&do=charging&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 288, 'pcate' => 9, 'cate' => 193, 'displayorder' => 87, 'title' => '分成统计', 'url' => './index.php?c=site&a=entry&op=comm&do=charging&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 289, 'pcate' => 9, 'cate' => 193, 'displayorder' => 88, 'title' => '故障上报', 'url' => './index.php?c=site&a=entry&op=fault&do=charging&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 290, 'pcate' => 3, 'displayorder' => 41, 'title' => '智能车禁', 'url' => '', 'do' => 'parks', 'method' => 'fun', 'status' => 1),
            array('id' => 291, 'pcate' => 3, 'cate' => 290, 'displayorder' => 81, 'title' => '基本设置', 'url' => './index.php?c=site&a=entry&op=set&do=parks&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 292, 'pcate' => 3, 'cate' => 290, 'displayorder' => 82, 'title' => '车场管理', 'url' => './index.php?c=site&a=entry&op=list&do=parks&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 293, 'pcate' => 3, 'cate' => 290, 'displayorder' => 83, 'title' => '车辆管理', 'url' => './index.php?c=site&a=entry&op=cars&do=parks&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 294, 'pcate' => 3, 'cate' => 290, 'displayorder' => 84, 'title' => '缴费记录', 'url' => './index.php?c=site&a=entry&op=order&do=parks&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 295, 'pcate' => 6, 'displayorder' => 55, 'title' => '线下订单', 'url' => './index.php?c=site&a=entry&op=lineorder&do=business&m=' . MODULE_NAME, 'do' => 'businessorder', 'method' => 'seller', 'status' => 1),
            array('id' => 296, 'pcate' => 7, 'displayorder' => 56, 'title' => '会员积分变更明细', 'url' => './index.php?c=site&a=entry&op=integral&do=cash&m=' . MODULE_NAME, 'do' => 'cashlist', 'method' => 'cash', 'status' => 1),
            array('id' => 297, 'pcate' => 7, 'displayorder' => 57, 'title' => '会员余额变更明细', 'url' => './index.php?c=site&a=entry&op=credit&do=cash&m=' . MODULE_NAME, 'do' => 'cashlist', 'method' => 'cash', 'status' => 1),
            array('id' => 298, 'pcate' => 7, 'displayorder' => 73, 'title' => '商家账户余额统计', 'url' => './index.php?c=site&a=entry&op=account&do=cash&m=' . MODULE_NAME, 'do' => 'cashlist', 'method' => 'cash', 'status' => 1),
            array('id' => 299, 'pcate' => 7, 'displayorder' => 72, 'title' => '商家余额变更明细', 'url' => './index.php?c=site&a=entry&op=account_log&do=cash&m=' . MODULE_NAME, 'do' => 'cashlist', 'method' => 'cash', 'status' => 1),
            array('id' => 300, 'pcate' => 7, 'displayorder' => 71, 'title' => '商家积分变更明细', 'url' => './index.php?c=site&a=entry&op=accountIntegral&do=cash&m=' . MODULE_NAME, 'do' => 'cashlist', 'method' => 'cash', 'status' => 1),
            array('id' => 301, 'pcate' => 7, 'displayorder' => 58, 'title' => '会员积分余额统计', 'url' => './index.php?c=site&a=entry&op=member&do=cash&m=' . MODULE_NAME, 'do' => 'cashlist', 'method' => 'cash', 'status' => 1),
            array('id' => 302, 'pcate' => 7, 'displayorder' => 74, 'title' => '平台余额变更明细', 'url' => './index.php?c=site&a=entry&op=pcCredit&do=cash&m=' . MODULE_NAME, 'do' => 'cashlist', 'method' => 'cash', 'status' => 1),
            array('id' => 303, 'pcate' => 7, 'displayorder' => 63, 'title' => '小区余额变更明细', 'url' => './index.php?c=site&a=entry&op=regionCredit&do=cash&m=' . MODULE_NAME, 'do' => 'cashlist', 'method' => 'cash', 'status' => 1),
            array('id' => 304, 'pcate' => 9, 'cate' => 193, 'displayorder' => 90, 'title' => '用户管理', 'url' => './index.php?c=site&a=entry&op=users&do=charging&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 305, 'pcate' => 7, 'displayorder' => 64, 'title' => '小区积分余额统计', 'url' => './index.php?c=site&a=entry&op=regionData&do=cash&m=' . MODULE_NAME, 'do' => 'cashlist', 'method' => 'cash', 'status' => 1),
            array('id' => 306, 'pcate' => 7, 'displayorder' => 62, 'title' => '小区积分变更明细', 'url' => './index.php?c=site&a=entry&op=regionIntegral&do=cash&m=' . MODULE_NAME, 'do' => 'cashlist', 'method' => 'cash', 'status' => 1),
            array('id' => 307, 'pcate' => 9, 'cate' => 193, 'displayorder' => 92, 'title' => '余额变更明细', 'url' => './index.php?c=site&a=entry&op=creditLog&do=charging&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 308, 'pcate' => 4, 'cate' => 42, 'displayorder' => 83, 'title' => '数据统计', 'url' => './index.php?c=site&a=entry&op=data&do=cost&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 309, 'pcate' => 9, 'cate' => 193, 'displayorder' => 91, 'title' => '数据统计', 'url' => './index.php?c=site&a=entry&op=data&do=charging&m=' . MODULE_NAME, 'do' => '', 'method' => '', 'status' => 1),
            array('id' => 310, 'pcate' => 3, 'cate' => 160, 'displayorder' => 74, 'title' => '人脸授权', 'url' => './index.php?c=site&a=entry&op=face&do=guard&m=' . MODULE_NAME, 'do' => 'guard', 'method' => '', 'status' => 1),
            array('id' => 311, 'pcate' => 3, 'cate' => 160, 'displayorder' => 75, 'title' => '人脸记录', 'url' => './index.php?c=site&a=entry&op=faceLogs&do=guard&m=' . MODULE_NAME, 'do' => 'guard', 'method' => '', 'status' => 1),
        );
        foreach ($d as $k => $v) {
            pdo_insert('xcommunity_menu', $v);
        }
    }
}

function appmenu()
{
    global $_W, $_GPC;
    //导入微信端导航数据
    $navs = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_appmenu') . "where uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
    if (empty($navs)) {
        $data1 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '数据中心', 'uniacid' => $_W['uniacid']);
        $data2 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '物业服务', 'uniacid' => $_W['uniacid']);
        $data3 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '商家管理', 'uniacid' => $_W['uniacid']);
        $data4 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '超市管理', 'uniacid' => $_W['uniacid']);
        $data5 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '其他服务', 'uniacid' => $_W['uniacid']);
        $data6 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '财务中心', 'uniacid' => $_W['uniacid']);
        if ($data1) {
            pdo_insert('xcommunity_appmenu', $data1);
            $nid1 = pdo_insertid();
            $menu1 = array(
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '物业列表', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=property&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-lvhuaxiaoqu
 defalut-color_blue', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '小区列表', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=region&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-lvhuaxiaoqu
 defalut-color_blue', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '楼宇管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=build&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-building-automation defalut-color_yellow', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '房屋管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=room&m=' . MODULE_NAME, 'icon' => 'iconfont icon-icon-test defalut-color_orange', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '车位管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=park&m=' . MODULE_NAME, 'icon' => 'iconfont icon-cheliangguanli defalut-color_green', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '车辆管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=car&m=' . MODULE_NAME, 'icon' => 'iconfont icon-cheliangguanli defalut-color_violet', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '住户管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=member&m=' . MODULE_NAME, 'icon' => 'iconfont icon-guanli defalut-color_red1', 'uniacid' => $_W['uniacid']),
            );
            foreach ($menu1 as $key => $value1) {
                pdo_insert('xcommunity_appmenu', $value1);

            }
        }
        if ($data2) {
            pdo_insert('xcommunity_appmenu', $data2);
            $nid2 = pdo_insertid();
            $pcate1 = array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '报修管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=repair&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-baoxiuguanli defalut-color_red', 'uniacid' => $_W['uniacid']);
            if ($pcate1) {
                pdo_insert('xcommunity_appmenu', $pcate1);
                $pid1 = pdo_insertid();
                $cate1 = array(
                    array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'cate' => $pid1, 'title' => '未处理报修', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=repair&do=xqsys&status=2&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-baoxiuguanli defalut-color_red', 'uniacid' => $_W['uniacid']),
                    array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'cate' => $pid1, 'title' => '处理中报修', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=repair&do=xqsys&status=3&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-baoxiuguanli defalut-color_red', 'uniacid' => $_W['uniacid']),
                    array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'cate' => $pid1, 'title' => '已处理报修', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=repair&do=xqsys&status=1&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-baoxiuguanli defalut-color_red', 'uniacid' => $_W['uniacid']),
                    array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'cate' => $pid1, 'title' => '我处理报修', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=repair&do=xqsys&type=2&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-baoxiuguanli defalut-color_red', 'uniacid' => $_W['uniacid']),
                    array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'cate' => $pid1, 'title' => '维修费订单', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=repair&do=xqsys&p=order&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-baoxiuguanli defalut-color_red', 'uniacid' => $_W['uniacid']),
                    array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'cate' => $pid1, 'title' => '我的派单记录', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=repair&do=xqsys&p=record&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-baoxiuguanli defalut-color_red', 'uniacid' => $_W['uniacid'])
                );
//                foreach ($cate1 as $k => $v) {
//                    pdo_insert('xcommunity_appmenu', $v);
//
//                }
            }
            $pcate2 = array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '建议管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=report&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-yijianjianyi defalut-color_green', 'uniacid' => $_W['uniacid']);
            if ($pcate2) {
                pdo_insert('xcommunity_appmenu', $pcate2);
                $pid2 = pdo_insertid();
                $cate1 = array(
                    array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'cate' => $pid2, 'title' => '未处理建议', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=report&do=xqsys&status=2&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-baoxiuguanli defalut-color_red', 'uniacid' => $_W['uniacid']),
                    array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'cate' => $pid2, 'title' => '处理中建议', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=report&do=xqsys&status=3&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-baoxiuguanli defalut-color_red', 'uniacid' => $_W['uniacid']),
                    array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'cate' => $pid2, 'title' => '已处理建议', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=report&do=xqsys&status=1&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-baoxiuguanli defalut-color_red', 'uniacid' => $_W['uniacid']),
                    array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'cate' => $pid2, 'title' => '我处理建议', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=report&do=xqsys&type=2&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-baoxiuguanli defalut-color_red', 'uniacid' => $_W['uniacid']),
                    array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'cate' => $pid2, 'title' => '我的派单记录', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=report&do=xqsys&p=record&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-baoxiuguanli defalut-color_red', 'uniacid' => $_W['uniacid'])
                );
//                foreach ($cate1 as $k => $v) {
//                    pdo_insert('xcommunity_appmenu', $v);
//
//                }
            }
            $menu2 = array(
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '公告管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=announcement&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont  icon-gonggao defalut-color_red0', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '活动管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=activity&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-huodongguanli defalut-color_violet', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '门禁管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=guard&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-sifaleimenjin defalut-color_yellow', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '费用管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=cost&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-926caidan_feiyongshenqing  defalut-color_pink', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '抄表录入', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=entery&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-lurubaogao defalut-color_red1', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '巡更管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&p=log&op=safety&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-daqia defalut-color_green', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '内部公告', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&p=list&op=notice&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-tongzhi defalut-color_green', 'uniacid' => $_W['uniacid']),
//            array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '合同存档', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&p=pactsave&op=homefee&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-tongzhi defalut-color_green', 'uniacid' => $_W['uniacid']),
            );
            foreach ($menu2 as $key => $value2) {
                pdo_insert('xcommunity_appmenu', $value2);

            }
        }
        if ($data3) {
            pdo_insert('xcommunity_appmenu', $data3);
            $nid3 = pdo_insertid();
            $menu3 = array(
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid3, 'title' => '店铺管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=business&p=list&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-dianpu defalut-color_orange', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid3, 'title' => '商品管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=business&p=goods&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-shangpinguanli defalut-color_green', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid3, 'title' => '线上订单', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=business&p=order&m=' . MODULE_NAME, 'icon' => 'iconfont icon-dingdanguanli defalut-color_violet', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid3, 'title' => '线下订单', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=business&p=xf&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-xiaofeijilu
defalut-color_red0', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid3, 'title' => '卷号管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=business&p=coupon&m=' . MODULE_NAME, 'icon' => 'iconfont icon-wodeyouhuijuan defalut-color_yellow', 'uniacid' => $_W['uniacid']),
//                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid3, 'title' => '参数设置', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=business&p=setting&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-icon-p_mrpcanshushezhi defalut-color_red', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid3, 'title' => '充值记录', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=business&p=cz&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-926caidan_feiyongshenqing defalut-color_pink', 'uniacid' => $_W['uniacid']),

                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid3, 'title' => '支付参数', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=business&p=payset&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-canshu defalut-color_red1', 'uniacid' => $_W['uniacid']),

            );
            foreach ($menu3 as $key => $value3) {
                pdo_insert('xcommunity_appmenu', $value3);
            }
        }
        if ($data4) {
            pdo_insert('xcommunity_appmenu', $data4);
            $nid4 = pdo_insertid();
            $menu4 = array(
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid4, 'title' => '店铺管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=shop&p=store&m=' . MODULE_NAME, 'icon' => 'iconfont icon-shangpinguanli defalut-color_red', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid4, 'title' => '商品管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=shop&p=list&m=' . MODULE_NAME, 'icon' => 'iconfont icon-shangpinguanli defalut-color_red', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid4, 'title' => '待付款', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=shop&p=order&status=0&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-daifukuan defalut-color_green', 'uniacid' => $_W['uniacid'], 'show' => 1),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid4, 'title' => '待发货', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=shop&p=order&status=1&m=' . MODULE_NAME, 'icon' => 'iconfont icon-daifahuo defalut-color_red0', 'uniacid' => $_W['uniacid'], 'show' => 1),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid4, 'title' => '待收货', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=shop&p=order&status=2&m=' . MODULE_NAME, 'icon' => 'iconfont icon-daishouhuo defalut-color_blue', 'uniacid' => $_W['uniacid'], 'show' => 1),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid4, 'title' => '已完成', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=shop&p=order&status=3&m=' . MODULE_NAME, 'icon' => 'iconfont icon-yiwancheng defalut-color_red1', 'uniacid' => $_W['uniacid'], 'show' => 1),
            );
            foreach ($menu4 as $key => $value4) {
                pdo_insert('xcommunity_appmenu', $value4);

            }
        }
        if ($data5) {
            pdo_insert('xcommunity_appmenu', $data5);
            $nid5 = pdo_insertid();
            $menu5 = array(
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid5, 'title' => '集市管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=market&m=' . MODULE_NAME, 'icon' => 'iconfont icon-baimashiji01 defalut-color_green', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid5, 'title' => '拼车管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=carpool&do=xqsys&m=' . MODULE_NAME, 'icon' => 'iconfont icon-cheliangyanse defalut-color_red0', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid5, 'title' => '租赁订单', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=houselease&m=' . MODULE_NAME, 'icon' => 'iconfont icon-fangwuzujin defalut-color_orange', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid5, 'title' => '家政管理', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=homemaking&m=' . MODULE_NAME, 'icon' => 'iconfont icon-cleanhouse defalut-color_red1', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid5, 'title' => '快递代收', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=express&m=' . MODULE_NAME, 'icon' => 'iconfont icon-kuaididaishou defalut-color_green', 'uniacid' => $_W['uniacid']),
            );
            foreach ($menu5 as $key => $value5) {
                pdo_insert('xcommunity_appmenu', $value5);

            }

        }
        if ($data6) {
            pdo_insert('xcommunity_appmenu', $data6);
            $nid6 = pdo_insertid();
            $menu6 = array(
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid6, 'title' => '余额充值', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=finance&m=' . MODULE_NAME, 'icon' => 'iconfont icon-baimashiji01 defalut-color_green', 'uniacid' => $_W['uniacid']),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid6, 'title' => '积分充值', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=xqsys&op=finance&p=credit&m=' . MODULE_NAME, 'icon' => 'iconfont icon-baimashiji01 defalut-color_green', 'uniacid' => $_W['uniacid']),
            );
            foreach ($menu6 as $key => $value6) {
                pdo_insert('xcommunity_appmenu', $value6);

            }

        }
    }

}

function housecenter()
{
    global $_W;
    //导入微信端导航数据
    $navs = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_housecenter') . "WHERE uniacid= :uniacid", array(':uniacid' => $_W['uniacid']));
    $regions = model_region::region_fetall();
    if (empty($navs)) {
        $data1 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '基础服务', 'url' => '', 'uniacid' => $_W['uniacid'],);
        $data2 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '超市订单', 'url' => '', 'uniacid' => $_W['uniacid'],);
        $data3 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '服务订单', 'url' => '', 'uniacid' => $_W['uniacid'],);
        $data4 = array('displayorder' => 0, 'status' => 1, 'pcate' => 0, 'title' => '智能设备', 'url' => '', 'uniacid' => $_W['uniacid'],);
//        $data5 = array('displayorder' => 0, 'status' => 0, 'pcate' => 0, 'title' => '生鲜订单', 'url' => '', 'uniacid' => $_W['uniacid'],);
        if ($data4) {
            pdo_insert('xcommunity_housecenter', $data4);
            $nid4 = pdo_insertid();
            foreach ($regions as $key => $item) {
                $dat1 = array(
                    'nid'      => $nid4,
                    'regionid' => $item['id'],
                );
                pdo_insert('xcommunity_housecenter_region', $dat1);
            }
            $menu4 = array(
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid4, 'title' => '智能充电', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=charging&op=list&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-charging.png"),
                array('displayorder' => 0, 'status' => 0, 'pcate' => $nid4, 'title' => '智能巡更', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=safety&op=list&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-safety.png"),
                array('displayorder' => 0, 'status' => 0, 'pcate' => $nid4, 'title' => '人脸门禁', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=face&op=list&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-face.png"),
            );
            foreach ($menu4 as $key => $value4) {
                pdo_insert('xcommunity_housecenter', $value4);
                $id4 = pdo_insertid();
                foreach ($regions as $key => $item) {
                    $dat4 = array(
                        'nid'      => $id4,
                        'regionid' => $item['id'],
                    );
                    pdo_insert('xcommunity_housecenter_region', $dat4);
                }
            }
        }
        if ($data1) {
            pdo_insert('xcommunity_housecenter', $data1);
            $nid1 = pdo_insertid();
            foreach ($regions as $key => $item) {
                $dat1 = array(
                    'nid'      => $nid1,
                    'regionid' => $item['id'],
                );
                pdo_insert('xcommunity_housecenter_region', $dat1);
            }

            $menu1 = array(
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '我的房屋', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=list&do=room&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-base-house.png"),
                array('displayorder' => 0, 'status' => 0, 'pcate' => $nid1, 'title' => '我的账单', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=bill&do=cost&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-base-bill.png"),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '物业缴费', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=cost&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-base-pay.png"),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '我的报修', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=my&do=repair&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-base-repairs.png"),
                array('displayorder' => 0, 'status' => 0, 'pcate' => $nid1, 'title' => '我的建议', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=my&do=report&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-base-reports.png"),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '我的小区', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=region&do=member&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-base-plot.png"),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '切换小区', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=register&op=region&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/region.png"),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '临时钥匙', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=opendoor&op=key&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/key.png"),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid1, 'title' => '邀请家属', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=family&do=member&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/family.png"),
            );
            foreach ($menu1 as $key => $value1) {
                pdo_insert('xcommunity_housecenter', $value1);
                $id1 = pdo_insertid();
                foreach ($regions as $key => $item) {
                    $dat1 = array(
                        'nid'      => $id1,
                        'regionid' => $item['id'],
                    );
                    pdo_insert('xcommunity_housecenter_region', $dat1);
                }
            }
        }
        if ($data2) {
            pdo_insert('xcommunity_housecenter', $data2);
            $nid2 = pdo_insertid();
            foreach ($regions as $key => $item) {
                $dat1 = array(
                    'nid'      => $nid2,
                    'regionid' => $item['id'],
                );
                pdo_insert('xcommunity_housecenter_region', $dat1);
            }
            $menu2 = array(
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '待付款', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=shopping&op=myorder&status=0&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-drop-obligation.png", 'show' => 1, 'enable' => 1),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '待发货', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=shopping&op=myorder&status=1&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-drop-shipping.png", 'show' => 1, 'enable' => 1),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '待收货', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=shopping&op=myorder&status=2&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-drop-receiving.png", 'show' => 1, 'enable' => 1),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid2, 'title' => '已完成', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=shopping&op=myorder&status=3&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-drop-finish.png", 'show' => 1, 'enable' => 1),

            );
            foreach ($menu2 as $key => $value2) {
                pdo_insert('xcommunity_housecenter', $value2);
                $id2 = pdo_insertid();
                foreach ($regions as $key => $item) {
                    $dat2 = array(
                        'nid'      => $id2,
                        'regionid' => $item['id'],
                    );
                    pdo_insert('xcommunity_housecenter_region', $dat2);
                }
            }
        }
//        if ($data5) {
//            pdo_insert('xcommunity_housecenter', $data5);
//            $nid5 = pdo_insertid();
//            foreach ($regions as $key => $item) {
//                $dat5 = array(
//                    'nid' => $nid5,
//                    'regionid' => $item['id'],
//                );
//                pdo_insert('xcommunity_housecenter_region', $dat5);
//            }
//            $menu5 = array(
//                array('displayorder' => 0, 'status' => 0, 'pcate' => $nid5, 'title' => '待付款', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=counter&op=myorder&status=0&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-drop-obligation.png",'show'=>1,'enable'=>2),
//                array('displayorder' => 0, 'status' => 0, 'pcate' => $nid5, 'title' => '待发货', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=counter&op=myorder&status=1&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-drop-shipping.png",'show'=>1,'enable'=>2),
//                array('displayorder' => 0, 'status' => 0, 'pcate' => $nid5, 'title' => '待收货', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=counter&op=myorder&status=2&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-drop-receiving.png",'show'=>1,'enable'=>2),
//                array('displayorder' => 0, 'status' => 0, 'pcate' => $nid5, 'title' => '已完成', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=counter&op=myorder&status=3&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-drop-finish.png",'show'=>1,'enable'=>2),
//            );
//            foreach ($menu5 as $key => $value5) {
//                pdo_insert('xcommunity_housecenter', $value5);
//                $id5 = pdo_insertid();
//                foreach ($regions as $key => $item) {
//                    $dat5 = array(
//                        'nid' => $id5,
//                        'regionid' => $item['id'],
//                    );
//                    pdo_insert('xcommunity_housecenter_region', $dat5);
//                }
//            }
//        }
        if ($data3) {
            pdo_insert('xcommunity_housecenter', $data3);
            $nid3 = pdo_insertid();
            foreach ($regions as $key => $item) {
                $dat1 = array(
                    'nid'      => $nid3,
                    'regionid' => $item['id'],
                );
                pdo_insert('xcommunity_housecenter_region', $dat1);
            }
            $menu3 = array(
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid3, 'title' => '团购订单', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=business&op=my&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-groupon.png"),
                array('displayorder' => 0, 'status' => 1, 'pcate' => $nid3, 'title' => '我发布的', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&do=my&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/icon-fb.png"),
//                    array('displayorder' => 0, 'status' => 1, 'pcate' => $nid3, 'title' => '充电记录', 'url' => './index.php?i=' . $_W['uniacid'] . '&c=entry&op=record&do=charging&m=' . MODULE_NAME, 'uniacid' => $_W['uniacid'], 'thumb' => MODULE_URL . "template/mobile/default2/static/images/icon/charging-record.png"),
            );
            foreach ($menu3 as $key => $value3) {
                pdo_insert('xcommunity_housecenter', $value3);
                $id3 = pdo_insertid();
                foreach ($regions as $key => $item) {
                    $dat3 = array(
                        'nid'      => $id3,
                        'regionid' => $item['id'],
                    );
                    pdo_insert('xcommunity_housecenter_region', $dat3);
                }
            }
        }
    }
}

// 首次进入小区根据公众号开启注册字段
function regfield()
{
    global $_W;
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    $condition['xqkey'] = array('p55', 'p154', 'p155', 'p36', 'p38', 'p40', 'p42');
    $set = pdo_get('xcommunity_setting', $condition, array());
    if (empty($set)) {
        $sql = "insert into " . tablename('xcommunity_setting') . " (uniacid, value, xqkey)values";
        foreach ($condition['xqkey'] as $k => $v) {
            $sql .= "(" . $_W['uniacid'] . ",1,'" . $v . "'),";
        }
        $sql = xtrim($sql);
        pdo_query($sql);
    }
}

function app_url($segment, $params = array())
{
    global $_W;
    list($do, $op) = explode('/', $segment);
    $url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=' . MODULE_NAME . '&';
    if (!empty($do)) {
        $url .= "do={$do}&";
    }
    if (!empty($op)) {
        $url .= "op={$op}";
    }
    if (!empty($params)) {
        $queryString = http_build_query($params, '', '&');
        $url .= $queryString;
    }
    return $url;
}

function wxapp_url($segment, $params = array())
{
    list($do, $op) = explode('/', $segment);
    if ($do) {
        $params['do'] = $do;
    }
    if ($op) {
        $params['op'] = $op;
    }
    $params['m'] = MODULE_NAME;
    return murl('entry/wxapp', $params, true, true);
}

function set($key, $regionid = '', $uniacid = '')
{
    global $_W;
    $condition = "uniacid=:uniacid and xqkey =:xqkey ";
    $params[':uniacid'] = $uniacid ? $uniacid : $_W['uniacid'];
    $params[':xqkey'] = $key;
    if ($regionid) {
        $condition .= "and regionid=:regionid ";
        $params[':regionid'] = $regionid;
    }

    $sql = "select * from" . tablename('xcommunity_setting') . "where $condition";

    $set = pdo_fetch($sql, $params);
    $v = $set['value'];
    return $v;
}

function strFilter($str)
{
    $str = str_replace('`', '', $str);
    $str = str_replace('·', '', $str);
    $str = str_replace('~', '', $str);
    $str = str_replace('!', '', $str);
    $str = str_replace('！', '', $str);
    $str = str_replace('@', '', $str);
    $str = str_replace('#', '', $str);
    $str = str_replace('$', '', $str);
    $str = str_replace('￥', '', $str);
    $str = str_replace('%', '', $str);
    $str = str_replace('^', '', $str);
    $str = str_replace('……', '', $str);
    $str = str_replace('&', '', $str);
    $str = str_replace('*', '', $str);
    $str = str_replace('(', '', $str);
    $str = str_replace(')', '', $str);
    $str = str_replace('（', '', $str);
    $str = str_replace('）', '', $str);
    $str = str_replace('-', '', $str);
    $str = str_replace('_', '', $str);
    $str = str_replace('——', '', $str);
    $str = str_replace('+', '', $str);
    $str = str_replace('=', '', $str);
    $str = str_replace('|', '', $str);
    $str = str_replace('\\', '', $str);
    $str = str_replace('[', '', $str);
    $str = str_replace(']', '', $str);
    $str = str_replace('【', '', $str);
    $str = str_replace('】', '', $str);
    $str = str_replace('{', '', $str);
    $str = str_replace('}', '', $str);
    $str = str_replace(';', '', $str);
    $str = str_replace('；', '', $str);
    $str = str_replace(':', '', $str);
    $str = str_replace('：', '', $str);
    $str = str_replace('\'', '', $str);
    $str = str_replace('"', '', $str);
    $str = str_replace('“', '', $str);
    $str = str_replace('”', '', $str);
    $str = str_replace(',', '', $str);
    $str = str_replace('，', '', $str);
    $str = str_replace('<', '', $str);
    $str = str_replace('>', '', $str);
    $str = str_replace('《', '', $str);
    $str = str_replace('》', '', $str);
    $str = str_replace('.', '', $str);
    $str = str_replace('。', '', $str);
    $str = str_replace('/', '', $str);
    $str = str_replace('、', '', $str);
    $str = str_replace('?', '', $str);
    $str = str_replace('栋', '', $str);
    $str = str_replace('单元', '', $str);
    $str = str_replace('室', '', $str);
    $str = str_replace('区', '', $str);
    $str = str_replace('#', '', $str);
    $str = str_replace('-', '', $str);
    $str = str_replace('层', '', $str);
    $str = str_replace('号房', '', $str);
    $str = str_replace('座', '', $str);
    return trim($str);
}

/**
 * @return 生成随机英文大写数字32位字符串
 */
function getRandom()
{
    $str = "123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $key = "";
    for ($i = 0; $i < 32; $i++) {
        $key .= $str{mt_rand(0, 32)};    //生成php随机数
    }
    return $key;
}

/**
 * @return 生成随机英文大写数字10位字符串
 */
function getNum()
{
    $str = "123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $key = "";
    for ($i = 0; $i < 10; $i++) {
        $key .= $str{mt_rand(0, 10)};    //生成php随机数
    }
    return $key;
}

/**
 * @return 生成随机英文大小写数字10位字符串
 */
function getNum2()
{
    $str = "123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $key = "";
    for ($i = 0; $i < 10; $i++) {
        $key .= $str{mt_rand(0, 10)};    //生成php随机数
    }
    return $key;
}

function sendTplNotice($openid, $template_id, $content, $url, $topcolor)
{
    load()->classs('weixin.account');
    load()->func('communication');
    $obj = new WeiXinAccount();
    $access_token = $obj->fetch_available_token();
    $data = array(
        'touser'      => $openid,
        'template_id' => $template_id,
        'url'         => $url,
        'topcolor'    => $topcolor,
        'data'        => $content,
    );
    $json = json_encode($data);
    $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token;
    $ret = ihttp_post($url, $json);
    $ret = json_decode($ret['content'], true);
    return $ret;
}

/**
 *创建md5摘要,规则是:按参数名称a-z排序,遇到空值的参数不参加签名。
 */
function createSign($data, $key)
{
    $signPars = "";
    ksort($data);
    foreach ($data as $k => $v) {
        if ("" != $v && "sign" != $k) {
            $signPars .= $k . "=" . $v . "&";
        }
    }
    $signPars .= "key=" . $key;
//    echo $signPars;exit();
    $sign = strtoupper(md5($signPars));
    return $sign;
}

/**
 *创建md5摘要,规则是:按参数名称a-z排序,遇到空值的参数不参加签名。
 */
function createSign2($data, $key)
{
    $signPars = "";
    ksort($data);
    foreach ($data as $k => $v) {
        if ("sign" != $k) {
            $signPars .= $k . "=" . $v . "&";
        }
    }
    $signPars = rtrim($signPars, '&');
    $signPars = $signPars . $key;
    $sign = md5($signPars);
    return $sign;
}

/**
 * 生成rsa签名
 */
function createRSASign($data, $private_rsa_key, $signtype)
{
    $signPars = "";
    ksort($data);
    foreach ($data as $k => $v) {
        if ("" != $v && "sign" != $k) {
            $signPars .= $k . "=" . $v . "&";
        }
    }

    $signPars = substr($signPars, 0, strlen($signPars) - 1);

    $res = openssl_get_privatekey($private_rsa_key);
    if ($signtype == 'RSA_1_1') {
        openssl_sign($signPars, $sign, $res);
    } else if ($signtype == 'RSA_1_256') {
        openssl_sign($signPars, $sign, $res, OPENSSL_ALGO_SHA256);
    }
    openssl_free_key($res);
    $sign = base64_encode($sign);
    return $sign;
}

function arrayToXml($array)
{
    header("Content-type: text/xml");
    $xml = '<xml>';
    foreach ($array as $k => $v) {
        $xml .= '<' . $k . '>' . $v . '</' . $k . '>';
    }
    $xml .= '</xml>';
    return $xml;
}

function xmlToArray($xml)
{

    //禁止引用外部xml实体

    libxml_disable_entity_loader(true);

    $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

    $val = json_decode(json_encode($xmlstring), true);

    return $val;

}

/**
 * 将数据转为XML
 */
function toXml($array)
{
    $xml = '<xml>';
    forEach ($array as $k => $v) {
        $xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
    }
    $xml .= '</xml>';
    return $xml;
}

function dheader($string, $replace = true, $http_response_code = 0)
{
    $islocation = substr(strtolower(trim($string)), 0, 8) == 'location';
    if (defined('IN_MOBILE') && strpos($string, 'mobile') === false && $islocation) {
        if (strpos($string, '?') === false) {
            $string = $string . '?mobile=' . IN_MOBILE;
        } else {
            if (strpos($string, '#') === false) {
                $string = $string . '&mobile=' . IN_MOBILE;
            } else {
                $str_arr = explode('#', $string);
                $str_arr[0] = $str_arr[0] . '&mobile=' . IN_MOBILE;
                $string = implode('#', $str_arr);
            }
        }
    }
    $string = str_replace(array("\r", "\n"), array('', ''), $string);
    if (empty($http_response_code) || PHP_VERSION < '4.3') {
        @header($string, $replace);
    } else {
        @header($string, $replace, $http_response_code);
    }
    if ($islocation) {
        exit();
    }
}

//小区支付调用
function xqwechat_build($params, $wechat)
{
    global $_W;
    load()->func('communication');

    if (empty($wechat['version']) && !empty($wechat['signkey'])) {
        $wechat['version'] = 1;
    }
    $wOpt = array();
    if ($wechat['version'] == 1) {
        $wOpt['appId'] = $wechat['appid'];
        $wOpt['timeStamp'] = TIMESTAMP;
        $wOpt['nonceStr'] = random(8);
        $package = array();
        $package['bank_type'] = 'WX';
        $package['body'] = $params['title'];
        $package['attach'] = $_W['uniacid'];
        $package['partner'] = $wechat['partner'];
        $package['out_trade_no'] = $params['uniontid'];
        $package['total_fee'] = $params['fee'] * 100;
        $package['fee_type'] = '1';
        $package['notify_url'] = $_W['siteroot'] . "addons/" . MODULE_NAME . "/payment/wechat/notify.php";
        $package['spbill_create_ip'] = CLIENT_IP;
        $package['time_start'] = date('YmdHis', TIMESTAMP);
        $package['time_expire'] = date('YmdHis', TIMESTAMP + 600);
        $package['input_charset'] = 'UTF-8';
        if (!empty($wechat['sub_mch_id'])) {
            $package['sub_mch_id'] = $wechat['sub_mch_id'];
        }
        ksort($package);
        $string1 = '';
        foreach ($package as $key => $v) {
            if (empty($v)) {
                continue;
            }
            $string1 .= "{$key}={$v}&";
        }
        $string1 .= "key={$wechat['key']}";
        $sign = strtoupper(md5($string1));

        $string2 = '';
        foreach ($package as $key => $v) {
            $v = urlencode($v);
            $string2 .= "{$key}={$v}&";
        }
        $string2 .= "sign={$sign}";
        $wOpt['package'] = $string2;

        $string = '';
        $keys = array('appId', 'timeStamp', 'nonceStr', 'package', 'appKey');
        sort($keys);
        foreach ($keys as $key) {
            $v = $wOpt[$key];
            if ($key == 'appKey') {
                $v = $wechat['signkey'];
            }
            $key = strtolower($key);
            $string .= "{$key}={$v}&";
        }
        $string = rtrim($string, '&');
        $wOpt['signType'] = 'SHA1';
        $wOpt['paySign'] = sha1($string);
        return $wOpt;
    } else {
        $package = array();
        $package['appid'] = $wechat['appid'];
        $package['mch_id'] = $wechat['mchid'];
        $package['nonce_str'] = random(8);
        $package['body'] = $params['title'];
        $package['attach'] = $_W['uniacid'];
        $package['out_trade_no'] = $params['uniontid'];
        $package['total_fee'] = $params['fee'] * 100;
        $package['spbill_create_ip'] = CLIENT_IP;
        $package['time_start'] = date('YmdHis', TIMESTAMP);
        $package['time_expire'] = date('YmdHis', TIMESTAMP + 600);
        $package['notify_url'] = $_W['siteroot'] . "addons/" . MODULE_NAME . "/payment/wechat/notify.php";
        $package['trade_type'] = 'JSAPI';
        $package['openid'] = empty($wechat['openid']) ? $_W['fans']['openid'] : $wechat['openid'];
        if (!empty($wechat['sub_mch_id'])) {
            $package['sub_mch_id'] = $wechat['sub_mch_id'];
        }
//        print_r($package);exit();
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach ($package as $key => $v) {
            if (empty($v)) {
                continue;
            }
            $string1 .= "{$key}={$v}&";
        }
//        print_r($string1);exit();
        $string1 .= "key={$wechat['signkey']}";
        $package['sign'] = strtoupper(md5($string1));
        $dat = array2xml($package);
        $response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);
        if (is_error($response)) {
            return $response;
        }
        $xml = @isimplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
        if (strval($xml->return_code) == 'FAIL') {
            return error(-1, strval($xml->return_msg));
        }
        if (strval($xml->result_code) == 'FAIL') {
            return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
        }
        $prepayid = $xml->prepay_id;
        $wOpt['appId'] = $wechat['appid'];
        $wOpt['timeStamp'] = TIMESTAMP;
        $wOpt['nonceStr'] = random(8);
        $wOpt['package'] = 'prepay_id=' . $prepayid;
        $wOpt['signType'] = 'MD5';
        ksort($wOpt, SORT_STRING);
        $string = '';
        foreach ($wOpt as $key => $v) {
            $string .= "{$key}={$v}&";
        }
        $string .= "key={$wechat['signkey']}";
        $wOpt['paySign'] = strtoupper(md5($string));

        return $wOpt;
    }
}

function xq_alipay_build($params, $alipay = array())
{
    global $_W;
    $set = array();
    $set['subject'] = $params['title']; //标题
    $set['body'] = $_W['uniacid'];;
    $set['service'] = 'create_direct_pay_by_user';
    $set['partner'] = $alipay['partner'];
    $set['notify_url'] = $_W['siteroot'] . 'addons/xfeng_community/payment/alipay/notify.php';
    $set['return_url'] = $_W['siteroot'] . 'addons/xfeng_community/payment/alipay/notify.php';
    $set['show_url'] = $_W['siteroot'];
    $set['_input_charset'] = 'utf-8';
    $set['out_trade_no'] = $params['tid'];
    $set['price'] = $params['fee'];
    $set['quantity'] = 1;
    $set['seller_email'] = $alipay['account'];
    $set['extend_param'] = 'isv^dz11';
    $set['payment_type'] = 1;
    $secret = $alipay['secret'];
    return trade_returnurl($set, $secret);
}

function trade_returnurl($args, $secret)
{
    global $_W;
    ksort($args);
    $urlstr = $sign = '';
    foreach ($args as $key => $val) {
        $sign .= '&' . $key . '=' . $val;
        $urlstr .= $key . '=' . rawurlencode($val) . '&';
    }
    $sign = substr($sign, 1);
    $sign = md5($sign . $secret);
    return 'https://www.alipay.com/cooperate/gateway.do?' . $urlstr . 'sign=' . $sign . '&sign_type=MD5';
}

function randomFloat($min = 0, $max = 1)
{
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

function sortArrByField(&$array, $field, $desc = false)
{
    $fieldArr = array();
    foreach ($array as $k => $v) {
        $fieldArr[$k] = $v[$field];
    }
    $sort = $desc == false ? SORT_ASC : SORT_DESC;
    array_multisort($fieldArr, $sort, $array);
}

function xtrim($str)
{
    return ltrim(rtrim($str, ','), ',');
}

function str_date($time)
{
    $time = str_replace("年", "-", str_replace("月", "-", str_replace("日", " ", $time)));
    $time = strtotime($time);
    return $time;
}

//创建二维码
function createQr($url, $temp, $tmpdir)
{
    global $_W;
    if (!is_dir($tmpdir)) {
        load()->func('file');
        $tmpdir = convertEncoding($tmpdir);
        mkdirs($tmpdir);
    }
    //生成二维码
    require_once IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
    $errorCorrectionLevel = 'L';//容错级别
    $matrixPointSize = 10;//生成图片大小
    //生成二维码图片
    $imgUrl = $tmpdir . $temp;
    $imgUrl = convertEncoding($imgUrl);
    QRcode::png($url, $imgUrl, $errorCorrectionLevel, $matrixPointSize, 2);
    return "<img src='{$imgUrl}' style='width: 200px'>";
}

/**
 * 转换字符编码
 * @param $string
 * @return string
 */
function convertEncoding($string)
{
    //根据系统进行配置
    $encode = stristr(PHP_OS, 'WIN') ? 'GBK' : 'UTF-8';
    $string = iconv('UTF-8', $encode, $string);
    //$string = mb_convert_encoding($string, $encode, 'UTF-8');
    return $string;
}

/**
 * @param $path
 * @param 打包文件夹
 */
function download2($path, $downzip)
{

    if (file_exists($path)) {
        $zip = new ZipArchive();
        if ($zip->open($downzip, ZIPARCHIVE::CREATE) === TRUE) {
            createZip(opendir($path), $zip, $path);
            $zip->close();
        }
    }
    ob_clean();
    clearstatcache();//清除缓存并再次检查文件大小
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header('Content-disposition: attachment; filename=' . basename($downzip)); //文件名
    header("Content-Type: application/zip"); //zip格式的
    header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
    header('Content-Length: ' . filesize($downzip)); //告诉浏览器，文件大小
    @readfile($downzip);
    exit ();
}

/*压缩多级目录
    $openFile:目录句柄
    $zipObj:Zip对象
    $sourceAbso:源文件夹路径
*/
function createZip($openFile, $zipObj, $sourceAbso, $newRelat = '')
{
    while (($file = readdir($openFile)) != false) {
        if ($file == "." || $file == "..")
            continue;

        /*源目录路径(绝对路径)*/
        $sourceTemp = $sourceAbso . '/' . $file;
        /*目标目录路径(相对路径)*/
        $newTemp = $newRelat == '' ? $file : $newRelat . '/' . $file;
        if (is_dir($sourceTemp)) {
            //echo '创建'.$newTemp.'文件夹<br/>';
            $zipObj->addEmptyDir($newTemp);/*这里注意：php只需传递一个文件夹名称路径即可*/
            createZip(opendir($sourceTemp), $zipObj, $sourceTemp, $newTemp);
        }
        if (is_file($sourceTemp)) {
            //echo '创建'.$newTemp.'文件<br/>';
            $zipObj->addFile($sourceTemp, $newTemp);
        }
    }
}

/**
 * @param $data
 * @param 打包文件
 */
function download($data, $path)
{
    if (!file_exists($path)) {
//重新生成文件
        $zip = new ZipArchive();//使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
        if ($zip->open($path, ZIPARCHIVE::CREATE) !== TRUE) {
            exit('无法打开文件，或者文件创建失败');
        }
        foreach ($data as $val) {
            if (file_exists($val)) {
                $zip->addFile($val, basename($val));//第二个参数是放在压缩包中的文件名称，如果文件可能会有重复，就需要注意一下
            }
        }
        $zip->close();//关闭
    }
    if (!file_exists($path)) {
        exit("无法找到文件"); //即使创建，仍有可能失败。。。。
    }
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header('Content-disposition: attachment; filename=' . basename($path)); //文件名
    header("Content-Type: application/zip"); //zip格式的
    header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
    header('Content-Length: ' . filesize($path)); //告诉浏览器，文件大小
    @readfile($path);
}

function list_dir($dir)
{
    $result = array();
    if (is_dir($dir)) {
        $file_dir = scandir($dir);
        foreach ($file_dir as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            } elseif (is_dir($dir . $file)) {
                $result = array_merge($result, list_dir($dir . $file . '/'));
            } else {
                array_push($result, $dir . $file);
            }
        }
    }
    return $result;
}

function num_to_rmb($num)
{
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    //精确到分后面就不要了，所以只留两个小数位
    $num = round($num, 2);
    $f = explode('.', $num);
    //将数字转化为整数
    $num = $num * 100;
    if (strlen($num) > 10) {
        return "金额太大，请检查";
    }
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            //获取最后一位数字
            $n = substr($num, strlen($num) - 1, 1);
        } else {
            $n = $num % 10;
        }
        //每次将最后一位数字转化为中文
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        //去掉数字最后一位了
        $num = $num / 10;
        $num = (int)$num;
        //结束循环
        if ($num == 0) {
            break;
        }
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        //utf8一个汉字相当3个字符
        $m = substr($c, $j, 6);
        //处理数字中很多0的情况,每次循环去掉一个汉字“零”
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j - 3;
            $slen = $slen - 3;
        }
        $j = $j + 3;
    }
    //这个是为了去掉类似23.0中最后一个“零”字
    if (substr($c, strlen($c) - 3, 3) == '零') {
        $c = substr($c, 0, strlen($c) - 3);
    }
    //将处理的汉字加上“整”
    if (empty($c)) {
        return "零元整";
    } else {
        if ($f[1] == '00' || empty($f[1]) || $f[1] == 0) {
            return $c . "整";
        } else {
            return $c;
        }

    }
}

function createChinaums($mid, $tid, $instMid, $msgSrc, $merOrderId, $fee, $attachedData)
{
    global $_W;
    $data = array(
        'mid'              => $mid,
        'tid'              => $tid,
        'instMid'          => $instMid,
        'msgSrc'           => $msgSrc,
        'requestTimestamp' => date('Y-m-d H:i:s', TIMESTAMP),
        'merOrderId'       => $merOrderId,
        'totalAmount'      => $fee * 100,
        'notifyUrl'        => $_W['siteroot'] . "addons/xfeng_community/payment/chinaums/notify.php",
        'attachedData'     => $attachedData,
        'returnUrl'        => urlencode(app_url('home'))
    );
    $data = array_filter($data);
    $d = [];
    foreach ($data as $k => $v) {
        $d[] = $k . '=' . $v;
    }
    unset ($k, $v);
    $d = implode('&', $d);
    return $d;

}

/**
 * @param $url
 * @param http请求
 * @return mixed
 */
function http_post($url, $data)
{
    $headers = array('Content-Type' => 'text/plain;charset=utf8');
    load()->func('communication');
    return ihttp_request($url, $data, $headers);
}

/**
 * 创建文件夹
 * $path 文件夹路径
 */
function createFileFolder($path)
{
    if (!is_dir($path)) {
        load()->func('file');
        mkdirs($path, '0777');
    }
}

// 根据健名从数组里取对应健值
function array_change_val_keys($input = array(), $keyfield = 'id')
{
    if (isset($input) && is_array($input) && $keyfield) {
        $string = array();
        foreach ($input as $key => $row) {
            if ($row[$keyfield]) {
                $string[] = $row[$keyfield];
            }
        }
        return array_unique($string);
    }
    return $input;
}

// 根据健名对重新组成数组
function array_change_arr_keys($temp = array(), $keyfield = 'id')
{
    $rs = array();
    if (!empty($temp)) {
        foreach ($temp as $key => &$row) {
            if (isset($row[$keyfield])) {
                $rs[$row[$keyfield]] = $row;
            } else {
                $rs[] = $row;
            }
        }
    }
    return $rs;
}

/**
 * array_column() // 不支持低版本;
 * 以下方法兼容PHP低版本
 */
function _array_column(array $array, $column_key, $index_key = null)
{
    $result = [];
    foreach ($array as $arr) {
        if (!is_array($arr)) continue;

        if (is_null($column_key)) {
            $value = $arr;
        } else {
            $value = $arr[$column_key];
        }

        if (!is_null($index_key)) {
            $key = $arr[$index_key];
            $result[$key] = $value;
        } else {
            $result[] = $value;
        }
    }
    return $result;
}

/**
 * 查询后台管理员信息
 */
function xiaoqu_user($uid)
{
    $user = pdo_get('xcommunity_users', array('uid' => $uid), array('staffid', 'uid'));
    if ($user['staffid']) {
        $staff = pdo_get('xcommunity_staff', array('id' => $user['staffid']), array('mobile'));
    }
    $data = array(
        'uid'      => $uid,
        'username' => $staff['mobile']
    );
    return $data;
}

/**
 * $type 4超市 5周边商家
 * 查店铺账号信息
 */
function xiaoqu_dp($dpid, $type)
{
    global $_W;
    $user = pdo_getall('xcommunity_users', array('uniacid' => $_W['uniacid'], 'type' => $type), array('staffid', 'uid', 'store'));
    $uid = 0;
    $staffid = 0;
    foreach ($user as $val) {
        $store = explode(',', $val['store']);
        if (in_array($dpid, $store)) {
            $uid = $val['uid'];
            $staffid = $val['staffid'];
        }
    }
    if ($staffid) {
        $staff = pdo_get('xcommunity_staff', array('id' => $staffid), array('mobile'));
    }
    $data = array(
        'uid'      => $uid,
        'username' => $staff['mobile']
    );
    return $data;
}

/**
 * @param string $time 时间戳
 * @param string $format 日期格式
 * @param string $type 日期类型
 * @param int $timenum 日期时间
 * @param int $num 日期数量
 * @return array 多个日期的数组
 */
function get_date($time = '', $format = 'H:i', $type = 'minute', $timenum = 30, $num = 10)
{
    $time = $time != '' ? $time : time();
    $date = [];
    $j = $num - 1;
    for ($i = 0; $i <= $j; $i++) {
        $date[$i] = date($format, strtotime('-' . (($j - $i) * $timenum) . $type, $time));
    }
    return $date;
}

function send_errors($number, $msg, $obj = array())
{
    $obj = $obj ? $obj : array();
    $obj['errno'] = intval($number);
    $obj['err_msg'] = $msg;
    header('Content-type:application/json');
    $obj = json_encode($obj);
    if ($_GET['callback']) {
        $obj = $_GET['callback'] . '(' . $obj . ')';
    }
    die($obj);
}

function send_results($data = array())
{
    $obj = array();
    $obj['errno'] = 0;
    $obj['err_msg'] = 'success';
    $obj['data'] = $data;
    header('Content-type:application/json');
    $obj = json_encode($obj);
    if ($_GET['callback']) {
        $obj = $_GET['callback'] . '(' . $obj . ')';
    }
    die($obj);
}