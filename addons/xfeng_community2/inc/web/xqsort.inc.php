<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/7/4 下午8:49
 */
global $_W, $_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$user = util::xquser($_W['uid']);
$condition =" uniacid={$_W['uniacid']} order by displayorder asc";
$page = pdo_getall('xcommunity_home',$condition,array());

if (empty($page) || $_GPC['status'] == 'reset') {
    $page = array( /**array('sort' => "search", 'name' => '搜索'),**/ array('sort' => "adv", 'name' => '幻灯片'), array('sort' => "notice", 'name' => '公告区'), array('sort' => "nav", 'name' => '导航区'), array('sort' => 'house', 'name' => '租房'), array('sort' => 'cube', 'name' => '魔方区'), array('sort' => 'banner', 'name' => '广告区一'), array('sort' => 'activity', 'name' => '活动'), array('sort' => 'bannerTwo', 'name' => '广告区二'), array('sort' => 'market', 'name' => '二手'), array('sort' => 'goods', 'name' => '推荐商品'));
} else {
    foreach ($page as $key => $value) {
        switch ($value['sort']) {
//            case 'search':
//                $page[$key]['name'] = '搜索';
//                break;
            case 'adv':
                $page[$key]['name'] = '幻灯片';
                break;
            case 'notice':
                $page[$key]['name'] = '公告区';
                break;
            case 'nav':
                $page[$key]['name'] = '导航区';
                break;
            case 'house':
                $page[$key]['name'] = '租房';
                break;
            case 'cube':
                $page[$key]['name'] = '魔方区';
                break;
            case 'banner':
                $page[$key]['name'] = '广告区一';
                break;
            case 'activity':
                $page[$key]['name'] = '活动';
                break;
            case 'bannerTwo':
                $page[$key]['name'] = '广告区二';
                break;
            case 'market':
                $page[$key]['name'] = '二手';
                break;
            case 'goods':
                $page[$key]['name'] = '推荐商品';
                break;
            default:
                break;
        }
    }
}
$setting = pdo_getall('xcommunity_setting',array('uniacid' => $_W['uniacid']),array(),'xqkey',array());

if ($_W['ispost']) {
    $page_sort = $_GPC['sort'];
    $page_sort_on = $_GPC['on'];
    $pageedit = array();
    for ($i = 0; $i < count($page_sort); $i++) {
        $pageedit[$i]['sort'] = $page_sort[$i];
        if (in_array($page_sort[$i], $page_sort_on)) {
            $pageedit[$i]['status'] = 1;
            $pageedit[$i]['displayorder'] = $i;
            $pageedit[$i]['uniacid'] = $_W['uniacid'];
        } else {
            $pageedit[$i]['status'] = 0;
            $pageedit[$i]['displayorder'] = $i;
            $pageedit[$i]['uniacid'] = $_W['uniacid'];
        }
    }
    if($pageedit){
        foreach ($pageedit as $k => $v){
            $data = array(
                'sort' => $v['sort'],
                'status' => $v['status'],
                'displayorder' => $v['displayorder'],
                'uniacid' => $_W['uniacid'],
            );
            if($v['sort'] == 'nav'){
                $data['enable'] = intval($_GPC['enable']);
            }
            $item =pdo_get('xcommunity_home',array('sort'=>$v['sort'],'uniacid'=> $_W['uniacid']),array('id'));
            if($item){
                pdo_update('xcommunity_home',$data,array('id'=> $item['id']));
            }else{
                pdo_insert('xcommunity_home',$data);
            }
        }

        foreach ($_GPC['set'] as $key => $val){
            $sql = "select * from".tablename('xcommunity_setting')."where xqkey='{$key}' and uniacid={$_W['uniacid']} ";
            $item = pdo_fetch($sql);
            $data = array(
                'xqkey' => $key,
                'value' => $val,
                'uniacid' => $_W['uniacid']
            );
            if($item){
                pdo_update('xcommunity_setting',$data,array('id' => $item['id'],'uniacid' => $_W['uniacid']));
            }else{
                pdo_insert('xcommunity_setting',$data);
            }
        }
        util::permlog('','首页-主页排版设置');
        itoast('页面保存成功.', $this->createWebUrl('xqsort'), 'success',true);
    }
}
$options=array();
$options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
include $this->template('web/core/xqsort');