<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/2/28 下午9:58
 * 移动管理中心菜单接口
 */

$sql = "select id,title from" . tablename('xcommunity_appmenu') . "where status = 1 and pcate = 0 order by displayorder asc,id asc ";
$category = pdo_fetchall($sql);
$children = array();

if (!empty($category)) {
    $children = '';
    foreach ($category as $cid => $cate) {
        $tsql = "select * from" . tablename('xcommunity_appmenu')."where status = 1 and pcate =:pcate order by displayorder asc,id asc ";
        $children = pdo_fetchall($tsql, array(':pcate' => $cate['id']));
        if ($children) {
            $category[$cid]['children'] = $children;
        }
    }
}


$data = array();
$data['list'] = $category;
util::send_result($data);