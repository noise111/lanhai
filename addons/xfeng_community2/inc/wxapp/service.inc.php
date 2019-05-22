<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/7 下午3:04
 */
global $_GPC, $_W;
$ops = array('list', 'detail');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
/**
 * 小区服务
 */
if ($op == 'list') {
    $regionid = $_SESSION['community']['regionid'];
    $sql = "select t1.id,t1.title from" . tablename('xcommunity_nav') . "as t1 left join " . tablename('xcommunity_nav_region') . "as t2 on t1.id=t2.nid where t1.uniacid=:uniacid and t2.regionid=:regionid and t1.status = 1 and t1.pcate = 0 order by t1.displayorder asc,t1.id asc ";
    $category = pdo_fetchall($sql, array(":uniacid" => $_W['uniacid'], ':regionid' => $regionid));
    $children = array();

    if (!empty($category)) {
        $children = '';
        foreach ($category as $cid => $cate) {
            $tsql = "select t1.id,t1.title,t1.url,t1.thumb from" . tablename('xcommunity_nav') . "as t1 left join " . tablename('xcommunity_nav_region') . "as t2 on t1.id=t2.nid where t1.uniacid=:uniacid and t2.regionid=:regionid and t1.status = 1 and t1.pcate =:pcate order by t1.displayorder asc,t1.id asc ";
            $children = pdo_fetchall($tsql, array(":uniacid" => $_W['uniacid'], ':regionid' => $regionid, ':pcate' => $cate['id']));
            if ($children) {
                foreach ($children as $key => $val) {
                    $children[$key]['thumb'] = tomedia($val['thumb']);
                    $children[$key]['url'] = $val['url'];

                }
                $category[$cid]['children'] = $children;
            }
        }
    }

    $data = array();
    $data['list'] = $category;
    $swipers = pdo_fetchall("select t1.* from".tablename('xcommunity_slide')."t1 left join".tablename('xcommunity_slide_region')."t2 on t1.id=t2.sid where t1.type=2 and t1.uniacid=:uniacid and t1.status=1 and t2.regionid=:regionid and t1.starttime<=:nowtime and t1.endtime>=:nowtime",array(':uniacid' => $_W['uniacid'],':regionid' => $_SESSION['community']['regionid'],':nowtime' => time()));
    foreach ($swipers as $k => $v) {
        $swipers[$k]['img'] = tomedia($v['thumb']);
    }
    $data['swiper'] = $swipers;
    util::send_result($data);
}