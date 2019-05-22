/**
 * Created by Administrator on 2017/11/6.
 */
$(function () {
    FastClick.attach(document.body);
});
//
// $('#unlock').click(function () {
//     alert(1);
// })
// jQuery(document).ready(function ($) {
//     //弹出菜单
//     $('#unlock').on('click', function () {
//         triggerBouncyNav(true);
//     });
//     //关闭菜单
//     $('.cd-bouncy-nav-modal .cd-close').on('click', function () {
//         triggerBouncyNav(false);
//     });
//     $('.cd-bouncy-nav-modal').on('click', function (event) {
//         if ($(event.target).is('.cd-bouncy-nav-modal')) {
//             triggerBouncyNav(false);
//         }
//     });
//     function triggerBouncyNav($bool) {
//         //切换菜单动画
//         $('.cd-bouncy-nav-modal').toggleClass('fade-in', $bool).toggleClass('fade-out', !$bool).find('li:last-child').one('webkitAnimationEnd oanimationend msAnimationEnd animationend', function () {
//             $('.cd-bouncy-nav-modal').toggleClass('is-visible', $bool);
//             if (!$bool) {
//                 $('.cd-bouncy-nav-modal').removeClass('fade-out');
//             }
//         });
//         //判断css 动画是否开启..
//         if ($('.cd-bouncy-nav-trigger').parents('.no-csstransitions').length > 0) {
//             $('.cd-bouncy-nav-modal').toggleClass('is-visible', $bool);
//         }
//     }
// });

function girdAdd(element) {
    $.each($(element), function (i) {
        var number = $(this).children('.weui-grid').length;
        var news = [];
        var remainder = number % 3;
        if (remainder !== 0) {
            news.push('<a href="" class="weui-grid js_grid"> <div class="weui-grid__icon">  </div> <p class="weui-grid__label"> <br></p></a>');
            for (var i = 0; i < 3 - remainder; ++i) {
                $(this).append(news.join(""));
            }
        }
    })
}


//    获取url参数
function getUrlArgStr() {
    var q = location.search.substr(1);
    var qs = q.split('&');
    var argStr = '';
    if (qs) {
        for (var i = 0; i < qs.length; i++) {
            argStr += qs[i].substring(qs[i].indexOf('=') + 1);
        }
    }
    return argStr;
}
