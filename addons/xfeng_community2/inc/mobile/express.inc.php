<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/5/14 0014
 * Time: 下午 2:06
 */
global $_W, $_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$member = model_user::mc_check();
if ($op == 'list') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'智能快递代收',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('plugin/express/list'));
}
elseif ($op == 'in') {
    include $this->template($this->xqtpl('plugin/express/in'));
}
elseif ($op == 'pick') {
    if ($p == 'list') {
        include $this->template($this->xqtpl('plugin/express/pick'));
    }
    elseif ($p == 'arrive') {

    }
    elseif ($p == 'out') {

        include $this->template($this->xqtpl('plugin/express/out'));
    }
    elseif ($p == 'over') {

    }
    elseif ($p == 'parcel') {

        include $this->template($this->xqtpl('plugin/express/pick_parcel'));
    }
}
elseif ($op == 'send') {
    if ($p == 'list') {

        include $this->template($this->xqtpl('plugin/express/send'));
    }
    elseif ($p == 'sendAdd') {

    }
    elseif ($p == 'address') {

        include $this->template($this->xqtpl('plugin/express/sender_info'));
    }
    elseif ($p == 'addressAdd') {

    }
    elseif ($p == 'price') {

    }
}
elseif ($op == 'collect') {
    if ($p == 'list') {

        include $this->template($this->xqtpl('plugin/express/collect'));
    }
    elseif ($p == 'detail') {

        include $this->template($this->xqtpl('plugin/express/detail'));
    }
}
elseif ($op == 'parcel') {

    include $this->template($this->xqtpl('plugin/express/parcel'));
}
elseif ($op == 'store') {
    if ($p == 'list') {
        include $this->template($this->xqtpl('plugin/express/store'));
    }
    elseif ($p == 'arrive') {


    }
}
elseif ($op == 'grey') {
    if ($p == 'detail') {

        include $this->template($this->xqtpl('plugin/express/grey'));
    }
}