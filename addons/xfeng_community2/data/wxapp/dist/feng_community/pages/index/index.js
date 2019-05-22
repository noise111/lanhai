var app = getApp();
Page({
    onShow(){
        const url = app.siteInfo.siteroot
        wx.redirectTo({
            url: '/wxapp_web/pages/view/index?url=' + encodeURIComponent(url)
        })
    }
})