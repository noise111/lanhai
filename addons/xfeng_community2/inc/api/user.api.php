<?php
global $_W, $_GPC;
$key = $_GPC['key'];
if ($key == 'fa5dadc3e9ee30583a67d0ad7b5cd091') {
    $mobile = $_GPC['mobile'];
    $credit = $_GPC['credit'];
    if ($mobile && $credit) {
        $uid = pdo_fetchcolumn('SELECT uid FROM ' . tablename('mc_members') . ' WHERE mobile = :mobile ', array(':mobile' => $mobile));
        if($uid){
            if (pdo_update('mc_members', array('credit1' => $credit), array('uid' => $uid))) {
                header('Content-type:application/json');
                $obj = array();
                $obj['err_code'] = 1000;
                $obj['err_msg'] = 'success';
                if ($_GET['callback']) {
                    $obj = $_GET['callback'] . '(' . $obj . ')';
                }
                die($obj);
            }
        }else{
            header('Content-type:application/json');
            $obj = array();
            $obj['err_code'] = 1000;
            $obj['err_msg'] = '会员不存在';
            if ($_GET['callback']) {
                $obj = $_GET['callback'] . '(' . $obj . ')';
            }
            die($obj);
        }
    }
}
