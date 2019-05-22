###小区小程序WEB打包

1. siteinfo.js 里填写相关信息

~~~javascript
module.exports = {
    uniacid: 1,  // 公众账号ID
    version: '1.0.0', // 版本
    siteroot: 'https://xf.njlanniu.cn/app/index.php' // 网址
}
~~~

2. 小程序后台增加 web-view 业务域名
   
3. 微信开发者工具里新建项目，指向小区小程序目录
4. 预览调试完成后，上传发布。