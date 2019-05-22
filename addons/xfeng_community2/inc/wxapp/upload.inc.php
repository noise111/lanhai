<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/3/18 下午11:59
 */

global $_GPC, $_W;

//$data = array();
//$data['result'] = 0;
//$data['message'] = '';
//$data['data'] = array(
//    array('url' => 'http://image.url.com/image.png')
//);
//$data['$_FILES'] = $_FILES;
//$data['$_REQUEST'] = $_REQUEST;
//$data['$_GPC'] = $_GPC;
//$data['$_POST'] = $_POST;
//echo json_encode($data);
//exit();
//print_r($_FILES);exit();
$file = $_FILES['img'];
if (empty($file)) {
    util::send_error(-1, '参数错误');
}

//$imgname = 'bl' . time() . rand(10000, 99999) . '.' . 'jpg';
$pathname = 'images/xiaoqu/'.$_W['uniacid'].'/XQ' . time() . rand(10000, 99999) . '.' . 'jpg';
$path = IA_ROOT . '/' . $_W['config']['upload']['attachdir'] . '/';

//$f = fopen($path . $imgname, 'w+');
//fwrite($f, $file['tmp_name']);
//fclose($f);
move_uploaded_file($file['tmp_name'], $path . $pathname);
//load()->func('file');
//if (!file_write($pathname, $file['tmp_name'])) {
//    return error(-1, '图片保存失败.');
//}
/** @var  $pathname */
if (!empty($_W['setting']['remote']['type'])) { // 判断系统是否开启了远程附件
    load()->func('file');
    $remotestatus = file_remote_upload($pathname); //上传图片到远程
    if (is_error($remotestatus)) {
        itoast('远程附件上传失败，请检查配置并重新上传');
    }
    else {
//        file_delete($pathname);
        $imgurl = tomedia($pathname);  // 远程图片的访问URL
    }
}
else {
    $imgurl = tomedia($pathname);
}

//$data['data'] = $imgurl;
if ($_GPC['type'] == 'upavatar') {
    pdo_update('mc_members', array('avatar' => $imgUrl), array('uid' => $_W['member']['uid']));
    $_W['member']['avatar'] = $imgUrl;
}
if($imgurl){
//    $data=array(
//        'url' =>  $imgurl
//    );
    $obj = array();
    $obj['result'] = 0;
    $obj['message'] = '上传成功';
    $obj['data'] = $imgurl;
    header('Content-type:application/json');
    $obj = json_encode($obj);
    if ($_GET['callback']) {
        $obj = $_GET['callback'] . '(' . $obj . ')';
    }
    die($obj);
}

