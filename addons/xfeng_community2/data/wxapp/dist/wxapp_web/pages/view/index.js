import { urlAddCode, getUrl, getShareUrl } from '../../../we7/resource/js/webview'
var app = getApp();
Page({
    data: {
        canIUse: wx.canIUse('web-view'),
        url: ''
    },
    onLoad: function (options) {
        let url = '';
        // 分享url
        if (options.url) {
            url = decodeURIComponent(options.url)
        }
        // 小程序内跳转url
        if (!url) {
            try {
                url = wx.getStorageSync('we7_webview');
                if (url) {
                    wx.removeStorageSync('we7_webview');
                }
            } catch (e) {
            }
        }
        if (!url) {
            url = app.siteInfo.siteroot + '?i=' + app.siteInfo.uniacid + '&c=entry&do=home&m=xfeng_community'
        }
        if (url) {
            this.setData({
                url: url
            })
        }
    },
    onShow() {

    },
    onShareAppMessage: function (options) {
        const url = '/wxapp_web/pages/view/index?url=' + encodeURIComponent(options.webViewUrl)
        console.log(url)
        return {
            path: url,
            success: function (res) {
            },
            fail: function (res) {
            }
        }
    }
});