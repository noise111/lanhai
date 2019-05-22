<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/6 下午2:04
 */
spl_autoload_register(function ($class) {
    $file = MODULE_ROOT . "/class/" . strtolower($class) . ".class.php";
    if (is_file($file)) {
        require_once $file;
    }
    else {
        throw new Exception("model file is not exist", 0);
    }
});