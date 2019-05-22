<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/6/13 下午8:38
 */
global $_W;
$color = set('p121') ? set('p121') : '#F7624B';
header('Content-type: text/css');
$html = ".weui-btn_primary {
 background-color: ".$color." !important;
}
.vux-header {
    position: relative;
    padding: 3px 0;
    box-sizing: border-box;
    background-color: ".$color." !important;
}
.vux-tab-selected{
color: ".$color." !important;
}
.vux-tab-ink-bar{
background-color: ".$color." !important;
}
.default_bottom{
background-color: ".$color." !important;
}
.shopcar{
background: ".$color." !important;
}
.default_bage {
color: ".$color." !important;
background: white !important;
}

.home_bage .vux-badge{
background: ".$color." !important;
}
.supermarket-order-item{
background: ".$color." !important;

}
.car-box-head-tit{
background: ".$color." !important;
}
.car-box-head-name{
color: ".$color." !important;
}
.default_yd{
color: ".$color." !important;
}

.left_title a {
    float: left;
    text-align: center;
    overflow: hidden;
    text-indent: .5em;
    border-left: 5px solid ".$color." !important;
    font-weight: 400;
    font-size: 14px;
    color: ".$color." !important;
}
.aui-list-title-btn .red-color[data-v-76e89cb7] {
    border: 1px solid".$color." !important;
    background-color: ".$color." !important;
    color: #fff;
}

.lease-detail-preview .weui-form-preview__hd{
    font-size: 14px;
    text-align: left;
    line-height: 18px;
    padding: 8px 15px;
    border-left: 3px solid ".$color." !important;
    margin-top: 10px;
}
.weui-tabbar__item.weui-bar__item_on .weui-tabbar__icon, .weui-tabbar__item.weui-bar__item_on .weui-tabbar__icon>i, .weui-tabbar__item.weui-bar__item_on .weui-tabbar__label {
    color: ".$color." !important;
}
.aui-scroll-nav .aui-crt .aui-scroll-item-text[data-v-baa288da], .aui-scroll-nav .crt .aui-scroll-item-text {
    color: ".$color." !important;
}
.aui-scroll-nav .aui-crt[data-v-baa288da], .aui-scroll-nav .crt[data-v-baa288da] {
    background-color: #fff;
    border-left: 1px solid ".$color." !important;
}
.icon-gonggao{
    font-size: 20px;
    color: ".$color." !important;
}
.default-checkbox-box input[type=checkbox]:checked+span[data-v-7f5e2522] {
    opacity: 1;
    background-color: ".$color." !important;
}
.default-checkbox-box span {
    position: absolute;
    top: -1px;
    left: -1px;
    width: 18px;
    height: 18px;
    border-radius: 3px;
    border: 1px solid ".$color." !important;
}
.vux-header-left,.vux-header-right {
  
    font-size: 16px !important;

}
";
echo $html;