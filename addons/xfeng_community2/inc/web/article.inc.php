<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/5/11 0011
 * Time: 下午 5:44
 */
global $_GPC, $_W;
$ops = array('list', 'category');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 信息发布
 */
if ($op == 'list') {
    /**
     * 信息发布的列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' uniacid =:uniacid';
        $params['uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
        }
//        $createtime = $_GPC['createtime'];//日期
//        if (empty($createtime)) {
//            $createtime['start'] = date('Y-m-d', TIMESTAMP - 86400 * 366);
//            $createtime['end'] = date('Y-m-d', TIMESTAMP);
//        }
//        else {
//            $condition .= " AND createtime >= :start AND createtime < :end";
//            $params[':start'] = strtotime($createtime['start']);
//            $params[':end'] = strtotime($createtime['end']) + 86399;
//        }
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']);
        if (!empty($starttime)) {
            $endtime = $endtime + 86400 - 1;
        }
        if ($starttime && $endtime) {
            $condition .= " AND t1.createtime between '{$starttime}' and '{$endtime}'";
        }
        $list = pdo_fetchall("SELECT * FROM " . tablename("xcommunity_plugin_article_message") . " WHERE $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        foreach ($list as $k => $v) {
            $pics = explode(',', $v['pic']);
            $list[$k]['pic'] = $pics[0];
        }
        $tsql = 'SELECT COUNT(*) FROM ' . tablename("xcommunity_plugin_article_message") . " WHERE $condition";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/article/list');
    }
    /**
     * 信息发布的添加修改
     */
    if ($p == 'add') {
        $regionids = '[]';
        $regions = model_region::region_fetall();
        $id = intval($_GPC['id']);
        $class = pdo_getall('xcommunity_plugin_article_class', array('uniacid' => $_W['uniacid']));
        if ($id) {
            $item = pdo_get('xcommunity_plugin_article_message', array('id' => $id));
            if (!empty($item['pic'])) {
                $item['pic'] = explode(',', $item['pic']);
            }
            $regs = pdo_getall('xcommunity_plugin_article_region', array('articleid' => $id), array('regionid'));
            $regionid = array();
            foreach ($regs as $key => $val) {
                $regionid[] = $val['regionid'];
            }
            $regionids = json_encode($regionid);
        }
        if ($_W['isajax']) {
            if ($_GPC['pic']) {
                $img = implode(',', $_GPC['pic']);
            }
            $birth = $_GPC['birth'];
            $allregion = intval($_GPC['allregion']);
            if ($allregion == 1){

            }
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'title'      => trim($_GPC['title']),
                'content'    => htmlspecialchars_decode($_GPC['content']),
                'pic'        => $img,
                'cid'        => intval($_GPC['classid']),
                'createtime' => time(),
                'province'   => $birth['province'],
                'city'       => $birth['city'],
                'dist'       => $birth['district'],
                'allregion' => $allregion
            );
            $message = pdo_get('xcommunity_plugin_article_message', array('uniacid' => $_W['uniacid'], 'title' => $_GPC['title']));

            if (!empty($id)) {
                $result = pdo_update('xcommunity_plugin_article_message', $data, array('id' => $id));
                pdo_delete('xcommunity_plugin_article_region', array('articleid' => $id));
            }
            else {
                if (!empty($message)) {
                    echo json_encode(array('content'=>'标题已存在！'));exit();
                }
                $result = pdo_insert('xcommunity_plugin_article_message', $data);
                $id = pdo_insertid();
            }
            if ($allregion == 1){
                $regions = model_region::region_fetall();
                foreach ($regions as $k => $v){
                    $dat = array(
                        'articleid' => $id,
                        'regionid' => $v['id'],
                    );
                    pdo_insert('xcommunity_plugin_article_region', $dat);
                }
            }else {
                $regionids = explode(',', $_GPC['regionids']);
                foreach ($regionids as $key => $value) {
                    $dat = array(
                        'articleid' => $id,
                        'regionid' => $value,
                    );
                    pdo_insert('xcommunity_plugin_article_region', $dat);
                }
            }
            if (set('p53')) {
                $regionids = implode(',', $_GPC['regionid']);
                $sql = "select * from" . tablename('xcommunity_member') . "where regionid in({$regionids}) group by uid";
                $users = pdo_fetchall($sql);
                foreach ($users as $key => $val) {
                    util::app_send($val['uid'], $data['title']);
                }

            }
            echo json_encode(array('status'=>1));exit();
        }
        load()->func('tpl');
        $options=array();
        $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
        include $this->template('web/plugin/article/add');
    }
    /**
     * 信息发布的删除
     */
    if ($p == 'delete') {
        $id = intval($_GPC['id']);
        if ($id) {
            $result = pdo_delete('xcommunity_plugin_article_message', array('id' => $id));
            if (!empty($result)) {
                itoast('删除成功', referer(), 'error');
            }
        }
    }
}
/**
 * 信息发布的分类
 */
if ($op == 'category') {
    /**
     * 分类的列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = ' uniacid =:uniacid';
        $params['uniacid'] = $_W['uniacid'];
        $list = pdo_fetchall("SELECT * FROM " . tablename("xcommunity_plugin_article_class") . " WHERE $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        $tsql = 'SELECT COUNT(*) FROM ' . tablename("xcommunity_plugin_article_class") . " WHERE $condition";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/article/category_list');
    }
    /**
     * 分类的添加修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_plugin_article_class', array('id' => $id), array());
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'name'    => trim($_GPC['name']),
                'pic'       => $_GPC['pic']
            );
            if ($id) {
                $result = pdo_update('xcommunity_plugin_article_class', $data, array('id' => $id));
            }
            else {
                $result = pdo_insert('xcommunity_plugin_article_class', $data);
            }

            echo json_encode(array('status'=>1));exit();

        }
        $options=array();
        $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
        include $this->template('web/plugin/article/category_add');
    }
    /**
     * 分类的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
        }
        $item = pdo_get('xcommunity_plugin_article_class', array('id' => $id), array());
        if (empty($item)) {
            itoast('信息不存在', referer(), 'error');
        }
        if (pdo_delete('xcommunity_plugin_article_class', array('id' => $id))) {
            itoast('删除成功', referer(), 'success');
        }
    }
}