'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});
var app = getApp();
exports.default = Page({
    data: {
        '__code__': {
            readme: ''
        },

        canIUse: wx.canIUse('web-view'),
        url: ''
    },
    onLoad: function onLoad(options) {
        var that = this;
        var url = app.siteInfo.siteroot + '?i=' + app.siteInfo.uniacid + '&c=entry&do=home&m=xfeng_community';
        that.setData({
            url: url
        });
    }
});
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImluZGV4Lnd4cCJdLCJuYW1lcyI6WyJhcHAiLCJnZXRBcHAiLCJkYXRhIiwiY2FuSVVzZSIsInd4IiwidXJsIiwib25Mb2FkIiwib3B0aW9ucyIsInRoYXQiLCJzaXRlSW5mbyIsInNpdGVyb290IiwidW5pYWNpZCIsInNldERhdGEiXSwibWFwcGluZ3MiOiI7Ozs7O0FBQUEsSUFBTUEsTUFBTUMsUUFBWjs7QUFNUUMsVUFBTTtBQUFBO0FBQUE7QUFBQTs7QUFDRkMsaUJBQVNDLEdBQUdELE9BQUgsQ0FBVyxVQUFYLENBRFA7QUFFRkUsYUFBSztBQUZILEs7QUFJTkMsWUFBUSxnQkFBVUMsT0FBVixFQUFtQjtBQUN2QixZQUFNQyxPQUFPLElBQWI7QUFDQSxZQUFJSCxNQUFNTCxJQUFJUyxRQUFKLENBQWFDLFFBQWIsR0FBd0IsS0FBeEIsR0FBZ0NWLElBQUlTLFFBQUosQ0FBYUUsT0FBN0MsR0FBdUQsbUNBQWpFO0FBQ0FILGFBQUtJLE9BQUwsQ0FBYTtBQUNUUCxpQkFBS0E7QUFESSxTQUFiO0FBR0giLCJmaWxlIjoiaW5kZXgud3hwIiwic291cmNlc0NvbnRlbnQiOlsiY29uc3QgYXBwID0gZ2V0QXBwKClcbiAgICBleHBvcnQgZGVmYXVsdCB7XG4gICAgICAgIGNvbmZpZzoge1xuICAgICAgICAgICAgdXNpbmdDb21wb25lbnRzOiB7fSxcbiAgICAgICAgICAgIGVuYWJsZVB1bGxEb3duUmVmcmVzaDogdHJ1ZVxuICAgICAgICB9LFxuICAgICAgICBkYXRhOiB7XG4gICAgICAgICAgICBjYW5JVXNlOiB3eC5jYW5JVXNlKCd3ZWItdmlldycpLFxuICAgICAgICAgICAgdXJsOiAnJ1xuICAgICAgICB9LFxuICAgICAgICBvbkxvYWQ6IGZ1bmN0aW9uIChvcHRpb25zKSB7XG4gICAgICAgICAgICBjb25zdCB0aGF0ID0gdGhpc1xuICAgICAgICAgICAgbGV0IHVybCA9IGFwcC5zaXRlSW5mby5zaXRlcm9vdCArICc/aT0nICsgYXBwLnNpdGVJbmZvLnVuaWFjaWQgKyAnJmM9ZW50cnkmZG89aG9tZSZtPWZlbmdfY29tbXVuaXR5J1xuICAgICAgICAgICAgdGhhdC5zZXREYXRhKHtcbiAgICAgICAgICAgICAgICB1cmw6IHVybFxuICAgICAgICAgICAgfSlcbiAgICAgICAgfVxuICAgIH0iXX0=