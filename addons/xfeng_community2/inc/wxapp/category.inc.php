<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/8 下午4:29
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'app', 'cates', 'tabList');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $type = intval($_GPC['type']);
    $condition = "uniacid=:uniacid and parentid=0";
    $params[':uniacid'] = $_W['uniacid'];
    if ($type) {
        $condition .= " and type=:type";
        $params[':type'] = $type;
    }
    $sql = "select * from" . tablename('xcommunity_category') . "where $condition order by displayorder desc";
    $category = pdo_fetchall($sql, $params);
    $data = array();
//    if($type ==4){
//        foreach ($category as $key => $val) {
//            $data[] = array(
//                'title' => $val['name'],
//                'value' => $val['id']
//            );
//        }
//    }else
    $cid = intval($_GPC['cid']);
    if ($cid == 1) {
        foreach ($category as $key => $val) {
            $data[] = array(
                'value' => $val['name'],
                'key'   => $val['id']
            );
        }
        util::send_result($data);
        exit();
    }
    if (($type == 5 || $type == 1 || $type == 8 || $type == 6 || $type == 10) && empty($cid)) {
        $data['list'] = $category;
    }
    elseif ($type == 2 || $type == 3 || $type == 4) {
        foreach ($category as $key => $val) {
            $data[] = array(
                'value' => $val['name'],
                'key'   => $val['id']
            );
        }
    }
    util::send_result($data);

}
if ($op == 'app') {
    $type = intval($_GPC['type']);
    $condition = "uniacid=:uniacid and parentid=0";
    $params[':uniacid'] = $_W['uniacid'];
    if ($type) {
        $condition .= " and type=:type";
        $params[':type'] = $type;
    }
    $sql = "select id,name from" . tablename('xcommunity_category') . "where $condition order by displayorder desc";
    $parents = pdo_fetchall($sql, $params);
    foreach ($parents as $k => $v) {
        $parents[$k]['key'] = $v['id'];
        $parents[$k]['value'] = $v['name'];
        $childs[$v['id']] = pdo_getall('xcommunity_category', array('parentid' => $v['id']), array('id', 'name'));
        foreach ($childs[$v['id']] as $ke => $va) {
            $childs[$v['id']][$ke]['key'] = $va['id'];
            $childs[$v['id']][$ke]['value'] = $va['name'];
        }
    }
    $data = array();
    $data['parents'] = $parents;
    $data['childs'] = $childs;
    util::send_result($data);
}
if ($op == 'cates') {
    $type = intval($_GPC['type']);
    $condition = "uniacid=:uniacid and parentid=0";
    $params[':uniacid'] = $_W['uniacid'];
    if ($type) {
        $condition .= " and type=:type";
        $params[':type'] = $type;
    }
    $sql = "select id,name from" . tablename('xcommunity_category') . "where $condition order by displayorder desc";
    $parents = pdo_fetchall($sql, $params);
    foreach ($parents as $k => $v) {
        $parents[$k]['key'] = $v['id'];
        $parents[$k]['value'] = $v['name'];
        $childs[$v['id']] = pdo_getall('xcommunity_category', array('parentid' => $v['id']), array('id', 'name'));
        foreach ($childs[$v['id']] as $key => $val) {
            $childs[$v['id']][$key]['key'] = $val['id'];
            $childs[$v['id']][$key]['value'] = $val['name'];
        }
    }
    $data = array();
    $data['parents'] = $parents;
    $data['childs'] = $childs;
    util::send_result($data);
}
/**
 * 分类的列表
 */
if ($op == 'tabList') {
    $type = intval($_GPC['type']);
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    $condition['parentid'] = 0;
    $condi = array();
    $condi['uniacid'] = $_W['uniacid'];
    $condi['parentid <>'] = 0;
    if ($type) {
        $condition['type'] = $type;
        $condi['type'] = $type;
    }
    $parents = pdo_getall('xcommunity_category', $condition, array('id', 'name'));// 一级分类
    $childs = pdo_getall('xcommunity_category', $condi, array('id', 'name', 'parentid'));// 二级分类
//    $list = array();
//    $list1 = array();
//    $list2 = array();
//    foreach ($parents as $k => $v) {
//        $list1[] = array(
//            'id'    => $v['id'],
//            'name'  => $v['name'],
//            'tabid' => 1
//        );
//    }
//    foreach ($childs as $k => $v) {
//        $list2[] = array(
//            'id'       => $v['id'],
//            'name'     => $v['name'],
//            'parentid' => $v['parentid']
//        );
//    }
//    $list = array_merge_recursive($list1, $list2);
    $data = array(
        'name'          => '菜系',
        'value'         => 'food',
        'type'          => '',
        'showTabHeader' => false,
        'selectIndex'   => 0,
        'tabs'          => array(
            array(
                'name'        => "",
                'selectIndex' => 0,
                'detailList'  => array(
                    array(
                        'name'        => '全部',
                        'value'       => '全部',
                        'selectIndex' => 0,
                        'list'        => array(
                            array(
                                'name'  => '全部',
                                'value' => 'all'
                            )
                        )
                    ),
                    array(
                        'name'        => '中餐馆',
                        'value'       => '中餐馆',
                        'selectIndex' => 1,
                        'list'        => array(
                            array(
                                'name'  => '全部',
                                'value' => 'all'
                            ),
                            array(
                                'name'  => '火锅',
                                'value' => 'hot pot'
                            ),
                            array(
                                'name'  => '川菜',
                                'value' => 'sichuan cuisine'
                            )
                        )
                    ),
                    array(
                        'name'        => '西餐馆',
                        'value'       => '西餐馆',
                        'selectIndex' => 2,
                        'list'        => array(
                            array(
                                'name'  => '全部',
                                'value' => 'all'
                            ),
                            array(
                                'name'  => '披萨',
                                'value' => 'pizza'
                            ),
                            array(
                                'name'  => '牛排',
                                'value' => 'steak'
                            )
                        )
                    )
                )
            )
        )
    );
    // 将搜索的分类进行重组
    $detailList = array();
    $detailList[0] = array(
        'name'        => '全部',
        'value'       => '全部',
        'selectIndex' => 0,
        'list'        => array(
            array(
                'name'  => '全部',
                'value' => 'all-all'
            )
        )
    );
    foreach ($parents as $key => $value) {
        $list = array();
        $list[0] = array(
            'name'  => '全部',
            'value' => 'all-' . $value['id']
        );
        foreach ($childs as $k => $v) {
            if ($v['parentid'] == $value['id']) {
                $list[] = array(
                    'name'  => $v['name'],
                    'value' => 'child-' . $v['id']
                );
            }
        }
        $detailList[] = array(
            'name'        => $value['name'],
            'value'       => 'pcate-' . $value['id'],
            'selectIndex' => $key + 1,
            'list'        => $list
        );
    }
    $data = array(
        array(
            'name'          => '分类',
            'value'         => 'category',
            'type'          => '',
            'showTabHeader' => true,
            'selectIndex'   => 0,
            'tabs'          => array(
                array(
                    'name'        => "全部",
                    'value'       => "all-all",
                    'selectIndex' => 0,
                    'detailList'  => $detailList
                )
            )
        ),
        array(
            'name'          => '智能排序',
            'value'         => 'compositor',
            'type'          => '',
            'showTabHeader' => true,
            'selectIndex'   => 0,
            'tabs'          => array(
                array(
                    'name'        => "",
                    'value'       => "",
                    'selectIndex' => 0,
                    'detailList'  => array(
                        array(
                            'name'        => '智能排序',
                            'value'       => 'compositor',
                            'selectIndex' => 0
                        ),
                        array(
                            'name'        => '离我最近',
                            'value'       => 'juli',
                            'selectIndex' => 1
                        ),
                        array(
                            'name'        => '好评优先',
                            'value'       => 'rank',
                            'selectIndex' => 2
                        ),
                        array(
                            'name'        => '人均低到高',
                            'value'       => 'priceasc',
                            'selectIndex' => 3
                        ),
                        array(
                            'name'        => '人均高到低',
                            'value'       => 'pricedesc',
                            'selectIndex' => 4
                        )
                    )
                ),
            )
        ),
        array(
            'name'          => '销量排序',
            'value'         => 'order',
            'type'          => '',
            'showTabHeader' => true,
            'selectIndex'   => 0,
            'tabs'          => array(
                array(
                    'name'        => "",
                    'value'       => "",
                    'selectIndex' => 0,
                    'detailList'  => array(
                        array(
                            'name'        => '销量排序',
                            'value'       => 'order',
                            'selectIndex' => 0
                        ),
                        array(
                            'name'        => '销量高',
                            'value'       => 'ordermax',
                            'selectIndex' => 0
                        ),
                        array(
                            'name'        => '销量低',
                            'value'       => 'ordermin',
                            'selectIndex' => 0
                        )
                    )
                ),
            )
        )
    );
    util::send_result($data);
}