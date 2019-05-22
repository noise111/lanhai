<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/11 下午11:09
 */

global $_GPC, $_W;
$op = in_array(trim($_GPC['op']),array('list','detail','add')) ? trim($_GPC['op']) : 'list';
$member = model_user::mc_check();
if($op =='list'){
    $_share = array(
        'title' => $_SESSION['community']['title'].'小区集市',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('plugin/market/list'));
}elseif($op =='detail'){

    include $this->template($this->xqtpl('plugin/market/detail'));
}elseif($op =='add'){

    include $this->template($this->xqtpl('plugin/market/add'));


}

