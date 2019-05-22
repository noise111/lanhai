<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台系统更新
 */
require XQ_PATH .'version.php';
global $_W,$_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'update';
$operation = !empty($_GPC['operation']) ? $_GPC['operation'] : 'list';
load()->func('communication');
if($op == 'display'){
    $domain = $_SERVER['SERVER_NAME'];
    $siteid = $_W['setting']['site']['key'];
    $ip = $_W['clientip'];
//    $result = ihttp_request('https://api.njlanniu.cn/addons/lanniu/api.php', array('type' => 'checkauth','module' => $this->module['name']),null,5);
    $result = ihttp_request('https://api.njlanniu.cn/addons/lanniu/api.php', array('type' => 'checkauth','module' => $this->module['name']),null,5);
    $result = @json_decode($result['content'], true);
    $result = $result['data'];
    if (checksubmit()) {
        $resp = ihttp_request('https://api.njlanniu.cn/addons/lanniu/api.php', array('type' => 'grant','module' => $this->module['name'],'code' => trim($_GPC['code']),'domain' => $domain,'siteid' => $siteid,'ip'=>$ip),null,1);
        $resp = @json_decode($resp['content'], true);
        if($resp['err_code'] == 1){
            itoast($resp['err_msg']);
        }else{
            itoast($resp['err_msg']);
        }
    }
    include $this->template('web/core/system/auth');
}elseif($op == 'upgrade'){
    $version = XQ_VERSION;
    $versionfile = XQ_PATH . 'version.php';
    $release = date('YmdHis', filemtime($versionfile));
    $resp = ihttp_post('https://api.njlanniu.cn/addons/lanniu/api.php', array(
        'type' => 'check',
        'module' => $this->module['name'],
        'version' => $version
    ));

    $upgrade = @json_decode($resp['content'], true);

    if($upgrade['status'] == 1){
        itoast('您还未授权，请授权后再试！',$this->createWebUrl('system',array('op'=> 'display')),'warning');exit();
    }
    if($upgrade['status'] == 2){
        itoast('您的服务已过期，请联系开发者续费！',$this->createWebUrl('system',array('op'=> 'display')),'warning');exit();
    }
    if($upgrade['status'] == 5){
        itoast('系统维护，暂停升级！',$this->createWebUrl('system',array('op'=> 'display')),'warning');exit();
    }
    $upgrade = $upgrade['data'];
    if (is_array($upgrade)) {
        if ($upgrade['result'] == 1) {
            $files = array();
            if (!empty($upgrade['files'])) {
//                print_r($upgrade['files']);exit();
                foreach ($upgrade['files'] as $file) {
//                    if($file['path'] != 'inc/web/system.inc.php'){
                        $entry = IA_ROOT . '/addons/'.$this->module['name'].'/' . $file['path'];
                        if (!is_file($entry) || md5_file($entry) != $file['md5']) {
                            $files[] = array(
                                'path' => $file['path'],
                                'download' => 0,
                                'entry'=>$entry,
                            );
                        }
//                    }

                }
            }
            if(!empty($files)){
                $upgrade['files'] = $files;
                $tmpdir = IA_ROOT . '/addons/'.$this->module['name'].'/temp';
                if (!is_dir($tmpdir)) {
                    load()->func('file');
                    mkdirs($tmpdir);
                }
                @file_put_contents($tmpdir . '/file.txt', json_encode($upgrade));
            }else{
                unset($upgrade);
            }
        }else{
            itoast($upgrade['message'],referer(),'success');
        }
    }else{
        itoast($resp['content'],$this->createWebUrl('system',array('op'=> 'display')),'error');
    }
    include $this->template('web/core/system/upgrade');
}elseif ($op =='process'){

    include $this->template('web/core/system/process');
}elseif ($op =='download'){
    load()->func('file');
    $tmpdir = IA_ROOT . '/addons/'.$this->module['name'].'/temp';
    $f = file_get_contents($tmpdir . '/file.txt');
    $upgrade = json_decode($f, true);
    $files = $upgrade['files'];

    //判断是否存在需要更新的文件
    $path = "";
    foreach ($files as $f) {
        if (empty($f['download'])) {
            $path = $f['path'];
            break;
        }
    }

    if (!empty($path)) {

        $resp = ihttp_post('https://api.njlanniu.cn/addons/lanniu/api.php', array(
            'type' => 'download',
            'module' =>$this->module['name'],
            'path' => $path
        ));

        $ret = @json_decode($resp['content'], true);
        $ret = $ret['data'];
        if ($ret&&is_array($ret)) {
            //检查路径
            $path = $ret['path'];
            $dirpath = dirname($path);

            if (!is_dir(IA_ROOT . '/addons/'.$this->module['name'].'/' . $dirpath)) {

                mkdirs(IA_ROOT . '/addons/'.$this->module['name'].'/' . $dirpath, 0755);
            }
            //获取更新文件

            $content = $ret['content'] ? base64_decode($ret['content']) : 'abc';
            @file_put_contents(IA_ROOT . '/addons/'.$this->module['name'].'/' . $path, $content);

            $success = 1;
            foreach ($files as & $f) {
                if ($f['path'] == $path) {
                    $f['download'] = 1;
                    break;
                }
                if ($f['download']) {
                    $success++;
                }
            }
            unset($f);
            $upgrade['files'] = $files;
            $tmpdir = IA_ROOT . '/addons/'.$this->module['name'].'/temp';
            if (!is_dir($tmpdir)) {
                mkdirs($tmpdir);
            }
            file_put_contents($tmpdir . '/file.txt', json_encode($upgrade));
            die(json_encode(array(
                'result' => 1,
                'total' => count($files) ,
                'success' => $success,
                'path' => $path
            )));
        }else{

            $success++;
            die(json_encode(array(
                'result' => 1,
                'total' => count($files) ,
                'success' => $success,
                'path' => $path
            )));
        }
    } else {

        $updatefile = IA_ROOT . '/addons/'.$this->module['name'].'/upgrade.php';
        require $updatefile;
        $tmpdir = IA_ROOT . '/addons/'.$this->module['name'].'/temp';
        rmdirs($tmpdir);
//        load()->func('file');
//        @file_delete($updatefile);
        itoast('恭喜您，系统更新成功！',$this->createWebUrl('system',array('op' => 'upgrade')),'success');
    }
}

