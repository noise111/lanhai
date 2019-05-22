'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _base = require('./base64.js');

var _md = require('./md5.js');

var _md2 = _interopRequireDefault(_md);

var _siteinfo = require('../../siteinfo.js');

var _siteinfo2 = _interopRequireDefault(_siteinfo);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var util = {};

util.base64Encode = function (str) {
	return (0, _base.base64_encode)(str);
};

util.base64Decode = function (str) {
	return (0, _base.base64_decode)(str);
};

util.md5 = function (str) {
	return (0, _md2.default)(str);
};

util.siteInfo = _siteinfo2.default;

/**
	构造微擎地址, 
	@params action 微擎系统中的controller, action, do，格式为 'wxapp/home/navs'
	@params querystring 格式为 {参数名1 : 值1, 参数名2 : 值2}
*/
util.url = function (action, querystring) {
	// var app = getApp();
	var url = util.siteInfo.siteroot + '?i=' + util.siteInfo.uniacid + '&t=' + util.siteInfo.multiid + '&v=' + util.siteInfo.version + '&from=wxapp&';

	if (action) {
		action = action.split('/');
		if (action[0]) {
			url += 'c=' + action[0] + '&';
		}
		if (action[1]) {
			url += 'a=' + action[1] + '&';
		}
		if (action[2]) {
			url += 'do=' + action[2] + '&';
		}
	}
	if (querystring && (typeof querystring === 'undefined' ? 'undefined' : _typeof(querystring)) === 'object') {
		for (var param in querystring) {
			if (param && querystring.hasOwnProperty(param) && querystring[param]) {
				url += param + '=' + querystring[param] + '&';
			}
		}
	}
	return url.substring(0, url.lastIndexOf('&')); // 去掉最后的&
};

function getQuery(url) {
	var theRequest = [];
	if (url.indexOf("?") != -1) {
		var str = url.split('?')[1];
		var strs = str.split("&");
		for (var i = 0; i < strs.length; i++) {
			if (strs[i].split("=")[0] && unescape(strs[i].split("=")[1])) {
				theRequest[i] = {
					'name': strs[i].split("=")[0],
					'value': unescape(strs[i].split("=")[1])
				};
			}
		}
	}
	return theRequest;
}
/*
* 获取链接某个参数
* url 链接地址
* name 参数名称
*/
function getUrlParam(url, name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象  
	var r = url.split('?')[1].match(reg); //匹配目标参数  
	if (r != null) return unescape(r[2]);return null; //返回参数值  
}
/**
 * 获取签名 将链接地址的所有参数按字母排序后拼接加上token进行md5
 * url 链接地址
 * date 参数{参数名1 : 值1, 参数名2 : 值2} *
 * token 签名token 非必须
 */
function getSign(url, data, token) {
	var _ = require('./underscore.js');
	var md5 = require('./md5.js');
	var querystring = '';
	var sign = getUrlParam(url, 'sign');
	if (sign || data && data.sign) {
		return false;
	} else {
		if (url) {
			querystring = getQuery(url);
		}
		if (data) {
			var theRequest = [];
			for (var param in data) {
				if (param && data[param]) {
					theRequest = theRequest.concat({
						'name': param,
						'value': data[param]
					});
				}
			}
			querystring = querystring.concat(theRequest);
		}
		//排序
		querystring = _.sortBy(querystring, 'name');
		//去重
		querystring = _.uniq(querystring, true, 'name');
		var urlData = '';
		for (var i = 0; i < querystring.length; i++) {
			if (querystring[i] && querystring[i].name && querystring[i].value) {
				urlData += querystring[i].name + '=' + querystring[i].value;
				if (i < querystring.length - 1) {
					urlData += '&';
				}
			}
		}
		token = token ? token : util.siteInfo.token;
		sign = md5(urlData + token);
		return sign;
	}
}
util.getSign = function (url, data, token) {
	return getSign(url, data, token);
};
/**
	二次封装微信wx.request函数、增加交互体全、配置缓存、以及配合微擎格式化返回数据

	@params option 弹出参数表，
	{
		url : 同微信,
		data : 同微信,
		header : 同微信,
		method : 同微信,
		success : 同微信,
		fail : 同微信,
		complete : 同微信,

		cachetime : 缓存周期，在此周期内不重复请求http，默认不缓存
	}
*/
util.request = function (option) {
	var _wx$request;

	var _ = require('./underscore.js');
	var md5 = require('./md5.js');
	// var app = getApp();
	var option = option ? option : {};
	option.cachetime = option.cachetime ? option.cachetime : 0;
	option.showLoading = typeof option.showLoading != 'undefined' ? option.showLoading : false; //默认关闭
	var sessionid = wx.getStorageSync('userInfo').sessionid;
	var url = option.url;
	if (url.indexOf('http://') == -1 && url.indexOf('https://') == -1) {
		url = util.url(url);
	}
	var state = getUrlParam(url, 'state');
	if (!state && !(option.data && option.data.state) && sessionid) {
		url = url + '&state=we7sid-' + sessionid;
	}
	if (!option.data || !option.data.m) {
		if (typeof util.siteInfo.modulename != 'undefined') {
			url = url + '&m=' + util.siteInfo.modulename;
		} else {
			var nowPage = getCurrentPages();
			if (nowPage.length) {
				nowPage = nowPage[getCurrentPages().length - 1];
				if (nowPage && nowPage.__route__) {
					url = url + '&m=' + nowPage.__route__.split('/')[0];
				}
			}
		}
	}

	var sign = getSign(url, option.data);
	if (sign) {
		url = url + "&sign=" + sign;
	}
	if (!url) {
		return false;
	}
	wx.showNavigationBarLoading();
	if (option.showLoading) {
		util.showLoading();
	}
	if (option.cachetime) {
		var cachekey = md5(url);
		var cachedata = wx.getStorageSync(cachekey);
		var timestamp = Date.parse(new Date());

		if (cachedata && cachedata.data) {
			if (cachedata.expire > timestamp) {
				if (option.complete && typeof option.complete == 'function') {
					option.complete(cachedata);
				}
				if (option.success && typeof option.success == 'function') {
					option.success(cachedata);
				}
				console.log('cache:' + url);
				wx.hideLoading();
				wx.hideNavigationBarLoading();
				return true;
			} else {
				wx.removeStorageSync(cachekey);
			}
		}
	}
	wx.request((_wx$request = {
		'url': url,
		'data': option.data ? option.data : {},
		'header': option.header ? option.header : {},
		'method': option.method ? option.method : 'GET'
	}, _defineProperty(_wx$request, 'header', {
		'content-type': 'application/x-www-form-urlencoded'
	}), _defineProperty(_wx$request, 'success', function success(response) {
		wx.hideNavigationBarLoading();
		wx.hideLoading();
		if (response.data.errno) {
			if (response.data.errno == '41009') {
				wx.setStorageSync('userInfo', '');
				util.getUserInfo(function () {
					util.request(option);
				});
				return;
			} else {
				if (option.fail && typeof option.fail == 'function') {
					option.fail(response);
				} else {
					if (response.data.message) {
						if (response.data.data != null && response.data.data.redirect) {
							var redirect = response.data.data.redirect;
						} else {
							var redirect = '';
						}
						util.message(response.data.message, redirect, 'error');
					}
				}
				return;
			}
		} else {
			if (option.success && typeof option.success == 'function') {
				option.success(response);
			}
			//写入缓存，减少HTTP请求，并且如果网络异常可以读取缓存数据
			if (option.cachetime) {
				var cachedata = { 'data': response.data, 'expire': timestamp + option.cachetime * 1000 };
				wx.setStorageSync(cachekey, cachedata);
			}
		}
	}), _defineProperty(_wx$request, 'fail', function fail(response) {
		wx.hideNavigationBarLoading();
		wx.hideLoading();

		//如果请求失败，尝试从缓存中读取数据
		var md5 = require('./md5.js');
		var cachekey = md5(url);
		var cachedata = wx.getStorageSync(cachekey);
		if (cachedata && cachedata.data) {
			if (option.success && typeof option.success == 'function') {
				option.success(cachedata);
			}
			console.log('failreadcache:' + url);
			return true;
		} else {
			if (option.fail && typeof option.fail == 'function') {
				option.fail(response);
			}
		}
	}), _defineProperty(_wx$request, 'complete', function complete(response) {
		// wx.hideNavigationBarLoading();
		// wx.hideLoading();
		if (option.complete && typeof option.complete == 'function') {
			option.complete(response);
		}
	}), _wx$request));
};
/*
* 获取用户信息
*/
util.getUserInfo = function (cb) {
	var login = function login() {
		console.log('start login');
		var userInfo = {
			'sessionid': '',
			'wxInfo': '',
			'memberInfo': ''
		};
		wx.login({
			success: function success(res) {
				util.request({
					url: 'auth/session/openid',
					data: { code: res.code },
					cachetime: 0,
					showLoading: false,
					success: function success(session) {
						if (!session.data.errno) {
							userInfo.sessionid = session.data.data.sessionid;
							wx.setStorageSync('userInfo', userInfo);
							wx.getUserInfo({
								success: function success(wxInfo) {
									userInfo.wxInfo = wxInfo.userInfo;
									wx.setStorageSync('userInfo', userInfo);
									util.request({
										url: 'auth/session/userinfo',
										data: {
											signature: wxInfo.signature,
											rawData: wxInfo.rawData,
											iv: wxInfo.iv,
											encryptedData: wxInfo.encryptedData
										},
										method: 'POST',
										header: {
											'content-type': 'application/x-www-form-urlencoded'
										},
										cachetime: 0,
										success: function success(res) {
											if (!res.data.errno) {
												userInfo.memberInfo = res.data.data;
												wx.setStorageSync('userInfo', userInfo);
											}
											typeof cb == "function" && cb(userInfo);
										}
									});
								},
								fail: function fail() {
									typeof cb == "function" && cb(userInfo);
								},
								complete: function complete() {}
							});
						}
					}
				});
			},
			fail: function fail() {
				wx.showModal({
					title: '获取信息失败',
					content: '请允许授权以便为您提供给服务',
					success: function success(res) {
						if (res.confirm) {
							util.getUserInfo();
						}
					}
				});
			}
		});
	};

	var app = wx.getStorageSync('userInfo');
	if (app.sessionid) {
		wx.checkSession({
			success: function success() {
				typeof cb == "function" && cb(app);
			},
			fail: function fail() {
				app.sessionid = '';
				console.log('relogin');
				wx.removeStorageSync('userInfo');
				login();
			}
		});
	} else {
		//调用登录接口
		login();
	}
};

util.navigateBack = function (obj) {
	var delta = obj.delta ? obj.delta : 1;
	if (obj.data) {
		var pages = getCurrentPages();
		var curPage = pages[pages.length - (delta + 1)];
		if (curPage.pageForResult) {
			curPage.pageForResult(obj.data);
		} else {
			curPage.setData(obj.data);
		}
	}
	wx.navigateBack({
		delta: delta, // 回退前 delta(默认为1) 页面
		success: function success(res) {
			// success
			typeof obj.success == "function" && obj.success(res);
		},
		fail: function fail(err) {
			// fail
			typeof obj.fail == "function" && obj.fail(err);
		},
		complete: function complete() {
			// complete
			typeof obj.complete == "function" && obj.complete();
		}
	});
};

util.footer = function ($this) {
	var app = getApp();
	var that = $this;
	var tabBar = app.tabBar;
	for (var i in tabBar['list']) {
		tabBar['list'][i]['pageUrl'] = tabBar['list'][i]['pagePath'].replace(/(\?|#)[^"]*/g, '');
	}
	that.setData({
		tabBar: tabBar,
		'tabBar.thisurl': that.__route__
	});
};
/*
 * 提示信息
 * type 为 success, error 当为 success,  时，为toast方式，否则为模态框的方式
 * redirect 为提示后的跳转地址, 跳转的时候可以加上 协议名称  
 * navigate:/we7/pages/detail/detail 以 navigateTo 的方法跳转，
 * redirect:/we7/pages/detail/detail 以 redirectTo 的方式跳转，默认为 redirect
*/
util.message = function (title, redirect, type) {
	if (!title) {
		return true;
	}
	if ((typeof title === 'undefined' ? 'undefined' : _typeof(title)) == 'object') {
		redirect = title.redirect;
		type = title.type;
		title = title.title;
	}
	if (redirect) {
		var redirectType = redirect.substring(0, 9),
		    url = '',
		    redirectFunction = '';
		if (redirectType == 'navigate:') {
			redirectFunction = 'navigateTo';
			url = redirect.substring(9);
		} else if (redirectType == 'redirect:') {
			redirectFunction = 'redirectTo';
			url = redirect.substring(9);
		} else {
			url = redirect;
			redirectFunction = 'redirectTo';
		}
	}
	console.log(url);
	if (!type) {
		type = 'success';
	}

	if (type == 'success') {
		wx.showToast({
			title: title,
			icon: 'success',
			duration: 2000,
			mask: url ? true : false,
			complete: function complete() {
				if (url) {
					setTimeout(function () {
						wx[redirectFunction]({
							url: url
						});
					}, 1800);
				}
			}
		});
	} else if (type == 'error') {
		wx.showModal({
			title: '系统信息',
			content: title,
			showCancel: false,
			complete: function complete() {
				if (url) {
					wx[redirectFunction]({
						url: url
					});
				}
			}
		});
	}
};

util.user = util.getUserInfo;

//封装微信等待提示，防止ajax过多时，show多次
util.showLoading = function () {
	var isShowLoading = wx.getStorageSync('isShowLoading');
	if (isShowLoading) {
		wx.hideLoading();
		wx.setStorageSync('isShowLoading', false);
	}

	wx.showLoading({
		title: '加载中',
		complete: function complete() {
			wx.setStorageSync('isShowLoading', true);
		},
		fail: function fail() {
			wx.setStorageSync('isShowLoading', false);
		}
	});
};

util.showImage = function (event) {
	var url = event ? event.currentTarget.dataset.preview : '';
	if (!url) {
		return false;
	}
	wx.previewImage({
		urls: [url]
	});
};

/**
 * 转换内容中的emoji表情为 unicode 码点，在Php中使用utf8_bytes来转换输出
*/
util.parseContent = function (string) {
	if (!string) {
		return string;
	}

	var ranges = ['\uD83C[\uDF00-\uDFFF]', // U+1F300 to U+1F3FF
	'\uD83D[\uDC00-\uDE4F]', // U+1F400 to U+1F64F
	'\uD83D[\uDE80-\uDEFF]' // U+1F680 to U+1F6FF
	];
	var emoji = string.match(new RegExp(ranges.join('|'), 'g'));

	if (emoji) {
		for (var i in emoji) {
			string = string.replace(emoji[i], '[U+' + emoji[i].codePointAt(0).toString(16).toUpperCase() + ']');
		}
	}
	return string;
};

util.date = function () {
	/**
  * 判断闰年
  * @param date Date日期对象
  * @return boolean true 或false
  */
	this.isLeapYear = function (date) {
		return 0 == date.getYear() % 4 && (date.getYear() % 100 != 0 || date.getYear() % 400 == 0);
	};

	/**
  * 日期对象转换为指定格式的字符串
  * @param f 日期格式,格式定义如下 yyyy-MM-dd HH:mm:ss
  * @param date Date日期对象, 如果缺省，则为当前时间
  *
  * YYYY/yyyy/YY/yy 表示年份  
  * MM/M 月份  
  * W/w 星期  
  * dd/DD/d/D 日期  
  * hh/HH/h/H 时间  
  * mm/m 分钟  
  * ss/SS/s/S 秒  
  * @return string 指定格式的时间字符串
  */
	this.dateToStr = function (formatStr, date) {
		formatStr = arguments[0] || "yyyy-MM-dd HH:mm:ss";
		date = arguments[1] || new Date();
		var str = formatStr;
		var Week = ['日', '一', '二', '三', '四', '五', '六'];
		str = str.replace(/yyyy|YYYY/, date.getFullYear());
		str = str.replace(/yy|YY/, date.getYear() % 100 > 9 ? (date.getYear() % 100).toString() : '0' + date.getYear() % 100);
		str = str.replace(/MM/, date.getMonth() > 9 ? date.getMonth() + 1 : '0' + (date.getMonth() + 1));
		str = str.replace(/M/g, date.getMonth());
		str = str.replace(/w|W/g, Week[date.getDay()]);

		str = str.replace(/dd|DD/, date.getDate() > 9 ? date.getDate().toString() : '0' + date.getDate());
		str = str.replace(/d|D/g, date.getDate());

		str = str.replace(/hh|HH/, date.getHours() > 9 ? date.getHours().toString() : '0' + date.getHours());
		str = str.replace(/h|H/g, date.getHours());
		str = str.replace(/mm/, date.getMinutes() > 9 ? date.getMinutes().toString() : '0' + date.getMinutes());
		str = str.replace(/m/g, date.getMinutes());

		str = str.replace(/ss|SS/, date.getSeconds() > 9 ? date.getSeconds().toString() : '0' + date.getSeconds());
		str = str.replace(/s|S/g, date.getSeconds());

		return str;
	};

	/**
 * 日期计算  
 * @param strInterval string  可选值 y 年 m月 d日 w星期 ww周 h时 n分 s秒  
 * @param num int
 * @param date Date 日期对象
 * @return Date 返回日期对象
 */
	this.dateAdd = function (strInterval, num, date) {
		date = arguments[2] || new Date();
		switch (strInterval) {
			case 's':
				return new Date(date.getTime() + 1000 * num);
			case 'n':
				return new Date(date.getTime() + 60000 * num);
			case 'h':
				return new Date(date.getTime() + 3600000 * num);
			case 'd':
				return new Date(date.getTime() + 86400000 * num);
			case 'w':
				return new Date(date.getTime() + 86400000 * 7 * num);
			case 'm':
				return new Date(date.getFullYear(), date.getMonth() + num, date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds());
			case 'y':
				return new Date(date.getFullYear() + num, date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds());
		}
	};

	/**
 * 比较日期差 dtEnd 格式为日期型或者有效日期格式字符串
 * @param strInterval string  可选值 y 年 m月 d日 w星期 ww周 h时 n分 s秒  
 * @param dtStart Date  可选值 y 年 m月 d日 w星期 ww周 h时 n分 s秒
 * @param dtEnd Date  可选值 y 年 m月 d日 w星期 ww周 h时 n分 s秒 
 */
	this.dateDiff = function (strInterval, dtStart, dtEnd) {
		switch (strInterval) {
			case 's':
				return parseInt((dtEnd - dtStart) / 1000);
			case 'n':
				return parseInt((dtEnd - dtStart) / 60000);
			case 'h':
				return parseInt((dtEnd - dtStart) / 3600000);
			case 'd':
				return parseInt((dtEnd - dtStart) / 86400000);
			case 'w':
				return parseInt((dtEnd - dtStart) / (86400000 * 7));
			case 'm':
				return dtEnd.getMonth() + 1 + (dtEnd.getFullYear() - dtStart.getFullYear()) * 12 - (dtStart.getMonth() + 1);
			case 'y':
				return dtEnd.getFullYear() - dtStart.getFullYear();
		}
	};

	/**
 * 字符串转换为日期对象 // eval 不可用
 * @param date Date 格式为yyyy-MM-dd HH:mm:ss，必须按年月日时分秒的顺序，中间分隔符不限制
 */
	this.strToDate = function (dateStr) {
		var data = dateStr;
		var reCat = /(\d{1,4})/gm;
		var t = data.match(reCat);
		t[1] = t[1] - 1;
		eval('var d = new Date(' + t.join(',') + ');');
		return d;
	};

	/**
 * 把指定格式的字符串转换为日期对象yyyy-MM-dd HH:mm:ss
 * 
 */
	this.strFormatToDate = function (formatStr, dateStr) {
		var year = 0;
		var start = -1;
		var len = dateStr.length;
		if ((start = formatStr.indexOf('yyyy')) > -1 && start < len) {
			year = dateStr.substr(start, 4);
		}
		var month = 0;
		if ((start = formatStr.indexOf('MM')) > -1 && start < len) {
			month = parseInt(dateStr.substr(start, 2)) - 1;
		}
		var day = 0;
		if ((start = formatStr.indexOf('dd')) > -1 && start < len) {
			day = parseInt(dateStr.substr(start, 2));
		}
		var hour = 0;
		if (((start = formatStr.indexOf('HH')) > -1 || (start = formatStr.indexOf('hh')) > 1) && start < len) {
			hour = parseInt(dateStr.substr(start, 2));
		}
		var minute = 0;
		if ((start = formatStr.indexOf('mm')) > -1 && start < len) {
			minute = dateStr.substr(start, 2);
		}
		var second = 0;
		if ((start = formatStr.indexOf('ss')) > -1 && start < len) {
			second = dateStr.substr(start, 2);
		}
		return new Date(year, month, day, hour, minute, second);
	};

	/**
 * 日期对象转换为毫秒数
 */
	this.dateToLong = function (date) {
		return date.getTime();
	};

	/**
 * 毫秒转换为日期对象
 * @param dateVal number 日期的毫秒数 
 */
	this.longToDate = function (dateVal) {
		return new Date(dateVal);
	};

	/**
 * 判断字符串是否为日期格式
 * @param str string 字符串
 * @param formatStr string 日期格式， 如下 yyyy-MM-dd
 */
	this.isDate = function (str, formatStr) {
		if (formatStr == null) {
			formatStr = "yyyyMMdd";
		}
		var yIndex = formatStr.indexOf("yyyy");
		if (yIndex == -1) {
			return false;
		}
		var year = str.substring(yIndex, yIndex + 4);
		var mIndex = formatStr.indexOf("MM");
		if (mIndex == -1) {
			return false;
		}
		var month = str.substring(mIndex, mIndex + 2);
		var dIndex = formatStr.indexOf("dd");
		if (dIndex == -1) {
			return false;
		}
		var day = str.substring(dIndex, dIndex + 2);
		if (!isNumber(year) || year > "2100" || year < "1900") {
			return false;
		}
		if (!isNumber(month) || month > "12" || month < "01") {
			return false;
		}
		if (day > getMaxDay(year, month) || day < "01") {
			return false;
		}
		return true;
	};

	this.getMaxDay = function (year, month) {
		if (month == 4 || month == 6 || month == 9 || month == 11) return "30";
		if (month == 2) if (year % 4 == 0 && year % 100 != 0 || year % 400 == 0) return "29";else return "28";
		return "31";
	};
	/**
 *	变量是否为数字
 */
	this.isNumber = function (str) {
		var regExp = /^\d+$/g;
		return regExp.test(str);
	};

	/**
 * 把日期分割成数组 [年、月、日、时、分、秒]
 */
	this.toArray = function (myDate) {
		myDate = arguments[0] || new Date();
		var myArray = Array();
		myArray[0] = myDate.getFullYear();
		myArray[1] = myDate.getMonth();
		myArray[2] = myDate.getDate();
		myArray[3] = myDate.getHours();
		myArray[4] = myDate.getMinutes();
		myArray[5] = myDate.getSeconds();
		return myArray;
	};

	/**
 * 取得日期数据信息  
 * 参数 interval 表示数据类型  
 * y 年 M月 d日 w星期 ww周 h时 n分 s秒  
 */
	this.datePart = function (interval, myDate) {
		myDate = arguments[1] || new Date();
		var partStr = '';
		var Week = ['日', '一', '二', '三', '四', '五', '六'];
		switch (interval) {
			case 'y':
				partStr = myDate.getFullYear();break;
			case 'M':
				partStr = myDate.getMonth() + 1;break;
			case 'd':
				partStr = myDate.getDate();break;
			case 'w':
				partStr = Week[myDate.getDay()];break;
			case 'ww':
				partStr = myDate.WeekNumOfYear();break;
			case 'h':
				partStr = myDate.getHours();break;
			case 'm':
				partStr = myDate.getMinutes();break;
			case 's':
				partStr = myDate.getSeconds();break;
		}
		return partStr;
	};

	/**
 * 取得当前日期所在月的最大天数  
 */
	this.maxDayOfDate = function (date) {
		date = arguments[0] || new Date();
		date.setDate(1);
		date.setMonth(date.getMonth() + 1);
		var time = date.getTime() - 24 * 60 * 60 * 1000;
		var newDate = new Date(time);
		return newDate.getDate();
	};
};

module.exports = util;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbInV0aWwuanMiXSwibmFtZXMiOlsidXRpbCIsImJhc2U2NEVuY29kZSIsInN0ciIsImJhc2U2NERlY29kZSIsIm1kNSIsInNpdGVJbmZvIiwidXJsIiwiYWN0aW9uIiwicXVlcnlzdHJpbmciLCJzaXRlcm9vdCIsInVuaWFjaWQiLCJtdWx0aWlkIiwidmVyc2lvbiIsInNwbGl0IiwicGFyYW0iLCJoYXNPd25Qcm9wZXJ0eSIsInN1YnN0cmluZyIsImxhc3RJbmRleE9mIiwiZ2V0UXVlcnkiLCJ0aGVSZXF1ZXN0IiwiaW5kZXhPZiIsInN0cnMiLCJpIiwibGVuZ3RoIiwidW5lc2NhcGUiLCJnZXRVcmxQYXJhbSIsIm5hbWUiLCJyZWciLCJSZWdFeHAiLCJyIiwibWF0Y2giLCJnZXRTaWduIiwiZGF0YSIsInRva2VuIiwiXyIsInJlcXVpcmUiLCJzaWduIiwiY29uY2F0Iiwic29ydEJ5IiwidW5pcSIsInVybERhdGEiLCJ2YWx1ZSIsInJlcXVlc3QiLCJvcHRpb24iLCJjYWNoZXRpbWUiLCJzaG93TG9hZGluZyIsInNlc3Npb25pZCIsInd4IiwiZ2V0U3RvcmFnZVN5bmMiLCJzdGF0ZSIsIm0iLCJtb2R1bGVuYW1lIiwibm93UGFnZSIsImdldEN1cnJlbnRQYWdlcyIsIl9fcm91dGVfXyIsInNob3dOYXZpZ2F0aW9uQmFyTG9hZGluZyIsImNhY2hla2V5IiwiY2FjaGVkYXRhIiwidGltZXN0YW1wIiwiRGF0ZSIsInBhcnNlIiwiZXhwaXJlIiwiY29tcGxldGUiLCJzdWNjZXNzIiwiY29uc29sZSIsImxvZyIsImhpZGVMb2FkaW5nIiwiaGlkZU5hdmlnYXRpb25CYXJMb2FkaW5nIiwicmVtb3ZlU3RvcmFnZVN5bmMiLCJoZWFkZXIiLCJtZXRob2QiLCJyZXNwb25zZSIsImVycm5vIiwic2V0U3RvcmFnZVN5bmMiLCJnZXRVc2VySW5mbyIsImZhaWwiLCJtZXNzYWdlIiwicmVkaXJlY3QiLCJjYiIsImxvZ2luIiwidXNlckluZm8iLCJyZXMiLCJjb2RlIiwic2Vzc2lvbiIsInd4SW5mbyIsInNpZ25hdHVyZSIsInJhd0RhdGEiLCJpdiIsImVuY3J5cHRlZERhdGEiLCJtZW1iZXJJbmZvIiwic2hvd01vZGFsIiwidGl0bGUiLCJjb250ZW50IiwiY29uZmlybSIsImFwcCIsImNoZWNrU2Vzc2lvbiIsIm5hdmlnYXRlQmFjayIsIm9iaiIsImRlbHRhIiwicGFnZXMiLCJjdXJQYWdlIiwicGFnZUZvclJlc3VsdCIsInNldERhdGEiLCJlcnIiLCJmb290ZXIiLCIkdGhpcyIsImdldEFwcCIsInRoYXQiLCJ0YWJCYXIiLCJyZXBsYWNlIiwidHlwZSIsInJlZGlyZWN0VHlwZSIsInJlZGlyZWN0RnVuY3Rpb24iLCJzaG93VG9hc3QiLCJpY29uIiwiZHVyYXRpb24iLCJtYXNrIiwic2V0VGltZW91dCIsInNob3dDYW5jZWwiLCJ1c2VyIiwiaXNTaG93TG9hZGluZyIsInNob3dJbWFnZSIsImV2ZW50IiwiY3VycmVudFRhcmdldCIsImRhdGFzZXQiLCJwcmV2aWV3IiwicHJldmlld0ltYWdlIiwidXJscyIsInBhcnNlQ29udGVudCIsInN0cmluZyIsInJhbmdlcyIsImVtb2ppIiwiam9pbiIsImNvZGVQb2ludEF0IiwidG9TdHJpbmciLCJ0b1VwcGVyQ2FzZSIsImRhdGUiLCJpc0xlYXBZZWFyIiwiZ2V0WWVhciIsImRhdGVUb1N0ciIsImZvcm1hdFN0ciIsImFyZ3VtZW50cyIsIldlZWsiLCJnZXRGdWxsWWVhciIsImdldE1vbnRoIiwiZ2V0RGF5IiwiZ2V0RGF0ZSIsImdldEhvdXJzIiwiZ2V0TWludXRlcyIsImdldFNlY29uZHMiLCJkYXRlQWRkIiwic3RySW50ZXJ2YWwiLCJudW0iLCJnZXRUaW1lIiwiZGF0ZURpZmYiLCJkdFN0YXJ0IiwiZHRFbmQiLCJwYXJzZUludCIsInN0clRvRGF0ZSIsImRhdGVTdHIiLCJyZUNhdCIsInQiLCJldmFsIiwiZCIsInN0ckZvcm1hdFRvRGF0ZSIsInllYXIiLCJzdGFydCIsImxlbiIsInN1YnN0ciIsIm1vbnRoIiwiZGF5IiwiaG91ciIsIm1pbnV0ZSIsInNlY29uZCIsImRhdGVUb0xvbmciLCJsb25nVG9EYXRlIiwiZGF0ZVZhbCIsImlzRGF0ZSIsInlJbmRleCIsIm1JbmRleCIsImRJbmRleCIsImlzTnVtYmVyIiwiZ2V0TWF4RGF5IiwicmVnRXhwIiwidGVzdCIsInRvQXJyYXkiLCJteURhdGUiLCJteUFycmF5IiwiQXJyYXkiLCJkYXRlUGFydCIsImludGVydmFsIiwicGFydFN0ciIsIldlZWtOdW1PZlllYXIiLCJtYXhEYXlPZkRhdGUiLCJzZXREYXRlIiwic2V0TW9udGgiLCJ0aW1lIiwibmV3RGF0ZSIsIm1vZHVsZSIsImV4cG9ydHMiXSwibWFwcGluZ3MiOiI7Ozs7QUFBQTs7QUFDQTs7OztBQUNBOzs7Ozs7OztBQUVBLElBQUlBLE9BQU8sRUFBWDs7QUFFQUEsS0FBS0MsWUFBTCxHQUFvQixVQUFVQyxHQUFWLEVBQWU7QUFDbEMsUUFBTyx5QkFBY0EsR0FBZCxDQUFQO0FBQ0EsQ0FGRDs7QUFJQUYsS0FBS0csWUFBTCxHQUFvQixVQUFVRCxHQUFWLEVBQWU7QUFDbEMsUUFBTyx5QkFBY0EsR0FBZCxDQUFQO0FBQ0EsQ0FGRDs7QUFJQUYsS0FBS0ksR0FBTCxHQUFXLFVBQVVGLEdBQVYsRUFBZTtBQUN6QixRQUFPLGtCQUFJQSxHQUFKLENBQVA7QUFDQSxDQUZEOztBQUlBRixLQUFLSyxRQUFMOztBQUVBOzs7OztBQUtBTCxLQUFLTSxHQUFMLEdBQVcsVUFBVUMsTUFBVixFQUFrQkMsV0FBbEIsRUFBK0I7QUFDdEM7QUFDQSxLQUFJRixNQUFNTixLQUFLSyxRQUFMLENBQWNJLFFBQWQsR0FBeUIsS0FBekIsR0FBaUNULEtBQUtLLFFBQUwsQ0FBY0ssT0FBL0MsR0FBeUQsS0FBekQsR0FBaUVWLEtBQUtLLFFBQUwsQ0FBY00sT0FBL0UsR0FBeUYsS0FBekYsR0FBaUdYLEtBQUtLLFFBQUwsQ0FBY08sT0FBL0csR0FBeUgsY0FBbkk7O0FBRUgsS0FBSUwsTUFBSixFQUFZO0FBQ1hBLFdBQVNBLE9BQU9NLEtBQVAsQ0FBYSxH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QVYsQ0FBZ0M7QUFDMUMsUUFBSyxJQUFMO0FBQVc4QyxjQUFVTCxPQUFPTSxhQUFQLEVBQVYsQ0FBaUM7QUFDNUMsUUFBSyxHQUFMO0FBQVVELGNBQVVMLE9BQU92QyxRQUFQLEVBQVYsQ0FBNEI7QUFDdEMsUUFBSyxHQUFMO0FBQVU0QyxjQUFVTCxPQUFPdEMsVUFBUCxFQUFWLENBQThCO0FBQ3hDLFFBQUssR0FBTDtBQUFVMkMsY0FBVUwsT0FBT3JDLFVBQVAsRUFBVixDQUE4QjtBQVR6QztBQVdBLFNBQU8wQyxPQUFQO0FBQ0EsRUFqQkQ7O0FBbUJBOzs7QUFHQSxNQUFLRSxZQUFMLEdBQW9CLFVBQVN6RCxJQUFULEVBQ3BCO0FBQ0NBLFNBQU9LLFVBQVUsQ0FBVixLQUFnQixJQUFJbEYsSUFBSixFQUF2QjtBQUNBNkUsT0FBSzBELE9BQUwsQ0FBYSxDQUFiO0FBQ0ExRCxPQUFLMkQsUUFBTCxDQUFjM0QsS0FBS1EsUUFBTCxLQUFrQixDQUFoQztBQUNBLE1BQUlvRCxPQUFPNUQsS0FBS2lCLE9BQUwsS0FBaUIsS0FBSyxFQUFMLEdBQVUsRUFBVixHQUFlLElBQTNDO0FBQ0EsTUFBSTRDLFVBQVUsSUFBSTFJLElBQUosQ0FBU3lJLElBQVQsQ0FBZDtBQUNBLFNBQU9DLFFBQVFuRCxPQUFSLEVBQVA7QUFDQSxFQVJEO0FBU0EsQ0FsUUQ7O0FBb1FBb0QsT0FBT0MsT0FBUCxHQUFpQnZNLElBQWpCIiwiZmlsZSI6InV0aWwuanMiLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgeyBiYXNlNjRfZW5jb2RlLCBiYXNlNjRfZGVjb2RlIH0gZnJvbSAnYmFzZTY0LmpzJztcbmltcG9ydCBtZDUgZnJvbSAnbWQ1LmpzJztcbmltcG9ydCBzaXRlSW5mbyBmcm9tICcuLi8uLi9zaXRlaW5mby5qcyc7XG5cbnZhciB1dGlsID0ge307XG5cbnV0aWwuYmFzZTY0RW5jb2RlID0gZnVuY3Rpb24gKHN0cikge1xuXHRyZXR1cm4gYmFzZTY0X2VuY29kZShzdHIpXG59O1xuXG51dGlsLmJhc2U2NERlY29kZSA9IGZ1bmN0aW9uIChzdHIpIHtcblx0cmV0dXJuIGJhc2U2NF9kZWNvZGUoc3RyKVxufTtcblxudXRpbC5tZDUgPSBmdW5jdGlvbiAoc3RyKSB7XG5cdHJldHVybiBtZDUoc3RyKVxufTtcblxudXRpbC5zaXRlSW5mbyA9IHNpdGVJbmZvO1xuXG4vKipcblx05p6E6YCg5b6u5pOO5Zyw5Z2ALCBcblx0QHBhcmFtcyBhY3Rpb24g5b6u5pOO57O757uf5Lit55qEY29udHJvbGxlciwgYWN0aW9uLCBkb++8jOagvOW8j+S4uiAnd3hhcHAvaG9tZS9uYXZzJ1xuXHRAcGFyYW1zIHF1ZXJ5c3RyaW5nIOagvOW8j+S4uiB75Y+C5pWw5ZCNMSA6IOWAvDEsIOWPguaVsOWQjTIgOiDlgLwyfVxuKi9cbnV0aWwudXJsID0gZnVuY3Rpb24gKGFjdGlvbiwgcXVlcnlzdHJpbmcpIHtcbiAgICAvLyB2YXIgYXBwID0gZ2V0QXBwKCk7XG4gICAgdmFyIHVybCA9IHV0aWwuc2l0ZUluZm8uc2l0ZXJvb3QgKyAnP2k9JyArIHV0aWwuc2l0ZUluZm8udW5pYWNpZCArICcmdD0nICsgdXRpbC5zaXRlSW5mby5tdWx0aWlkICsgJyZ2PScgKyB1dGlsLnNpdGVJbmZvLnZlcnNpb24gKyAnJmZyb209d3hhcHAmJztcblxuXHRpZiAoYWN0aW9uKSB7XG5cdFx0YWN0aW9uID0gYWN0aW9uLnNwbGl0KCcvJyk7XG5cdFx0aWYgKGFjdGlvblswXSkge1xuXHRcdFx0dXJsICs9ICdjPScgKyBhY3Rpb25bMF0gKyAnJic7XG5cdFx0fVxuXHRcdGlmIChhY3Rpb25bMV0pIHtcblx0XHRcdHVybCArPSAnYT0nICsgYWN0aW9uWzFdICsgJyYnO1xuXHRcdH1cblx0XHRpZiAoYWN0aW9uWzJdKSB7XG5cdFx0XHR1cmwgKz0gJ2RvPScgKyBhY3Rpb25bMl0gKyAnJic7XG5cdFx0fVxuXHR9XG5cdGlmIChxdWVyeXN0cmluZyAmJiB0eXBlb2YgcXVlcnlzdHJpbmcgPT09ICdvYmplY3QnKSB7XG5cdFx0Zm9yIChsZXQgcGFyYW0gaW4gcXVlcnlzdHJpbmcpIHtcbiAgICAgICAgICAgIGlmIChwYXJhbSAmJiBxdWVyeXN0cmluZy5oYXNPd25Qcm9wZXJ0eShwYXJhbSkgJiYgcXVlcnlzdHJpbmdbcGFyYW1dKSB7XG5cdFx0XHRcdHVybCArPSBwYXJhbSArICc9JyArIHF1ZXJ5c3RyaW5nW3BhcmFtXSArICcmJztcblx0XHRcdH1cblx0XHR9XG5cdH1cbiAgICByZXR1cm4gdXJsLnN1YnN0cmluZygwLCB1cmwubGFzdEluZGV4T2YoJyYnKSk7IC8vIOWOu+aOieacgOWQjueahCZcbn1cblxuZnVuY3Rpb24gZ2V0UXVlcnkodXJsKSB7XG5cdHZhciB0aGVSZXF1ZXN0ID0gW107XG5cdGlmICh1cmwuaW5kZXhPZihcIj9cIikgIT0gLTEpIHtcblx0XHR2YXIgc3RyID0gdXJsLnNwbGl0KCc/JylbMV07XG5cdFx0dmFyIHN0cnMgPSBzdHIuc3BsaXQoXCImXCIpO1xuXHRcdGZvciAodmFyIGkgPSAwOyBpIDwgc3Rycy5sZW5ndGg7IGkrKykge1xuXHRcdFx0aWYgKHN0cnNbaV0uc3BsaXQoXCI9XCIpWzBdICYmIHVuZXNjYXBlKHN0cnNbaV0uc3BsaXQoXCI9XCIpWzFdKSkge1xuXHRcdFx0XHR0aGVSZXF1ZXN0W2ldID0ge1xuXHRcdFx0XHRcdCduYW1lJzogc3Ryc1tpXS5zcGxpdChcIj1cIilbMF0sXG5cdFx0XHRcdFx0J3ZhbHVlJzogdW5lc2NhcGUoc3Ryc1tpXS5zcGxpdChcIj1cIilbMV0pXG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9XG5cdH1cblx0cmV0dXJuIHRoZVJlcXVlc3Q7XG59XG4vKlxuKiDojrflj5bpk77mjqXmn5DkuKrlj4LmlbBcbiogdXJsIOmTvuaOpeWcsOWdgFxuKiBuYW1lIOWPguaVsOWQjeensFxuKi9cbmZ1bmN0aW9uIGdldFVybFBhcmFtKHVybCwgbmFtZSkge1xuXHR2YXIgcmVnID0gbmV3IFJlZ0V4cChcIihefCYpXCIgKyBuYW1lICsgXCI9KFteJl0qKSgmfCQpXCIpOyAvL+aehOmAoOS4gOS4quWQq+acieebruagh+WPguaVsOeahOato+WImeihqOi+vuW8j+WvueixoSAgXG5cdHZhciByID0gdXJsLnNwbGl0KCc/JylbMV0ubWF0Y2gocmVnKTsgIC8v5Yy56YWN55uu5qCH5Y+C5pWwICBcblx0aWYgKHIgIT0gbnVsbCkgcmV0dXJuIHVuZXNjYXBlKHJbMl0pOyByZXR1cm4gbnVsbDsgLy/ov5Tlm57lj4LmlbDlgLwgIFxufVxuLyoqXG4gKiDojrflj5bnrb7lkI0g5bCG6ZO+5o6l5Zyw5Z2A55qE5omA5pyJ5Y+C5pWw5oyJ5a2X5q+N5o6S5bqP5ZCO5ou85o6l5Yqg5LiKdG9rZW7ov5vooYxtZDVcbiAqIHVybCDpk77mjqXlnLDlnYBcbiAqIGRhdGUg5Y+C5pWwe+WPguaVsOWQjTEgOiDlgLwxLCDlj4LmlbDlkI0yIDog5YC8Mn0gKlxuICogdG9rZW4g562+5ZCNdG9rZW4g6Z2e5b+F6aG7XG4gKi9cbmZ1bmN0aW9uIGdldFNpZ24odXJsLCBkYXRhLCB0b2tlbikge1xuXHR2YXIgXyA9IHJlcXVpcmUoJ3VuZGVyc2NvcmUuanMnKTtcblx0dmFyIG1kNSA9IHJlcXVpcmUoJ21kNS5qcycpO1xuXHR2YXIgcXVlcnlzdHJpbmcgPSAnJztcblx0dmFyIHNpZ24gPSBnZXRVcmxQYXJhbSh1cmwsICdzaWduJyk7XG5cdGlmIChzaWduIHx8IChkYXRhICYmIGRhdGEuc2lnbikpIHtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH0gZWxzZSB7XG5cdFx0aWYgKHVybCkge1xuXHRcdFx0cXVlcnlzdHJpbmcgPSBnZXRRdWVyeSh1cmwpO1xuXHRcdH1cblx0XHRpZiAoZGF0YSkge1xuXHRcdFx0dmFyIHRoZVJlcXVlc3QgPSBbXTtcblx0XHRcdGZvciAobGV0IHBhcmFtIGluIGRhdGEpIHtcblx0XHRcdFx0aWYgKHBhcmFtICYmIGRhdGFbcGFyYW1dKSB7XG5cdFx0XHRcdFx0dGhlUmVxdWVzdCA9IHRoZVJlcXVlc3QuY29uY2F0KHtcblx0XHRcdFx0XHRcdCduYW1lJzogcGFyYW0sXG5cdFx0XHRcdFx0XHQndmFsdWUnOiBkYXRhW3BhcmFtXVxuXHRcdFx0XHRcdH0pXG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHRcdHF1ZXJ5c3RyaW5nID0gcXVlcnlzdHJpbmcuY29uY2F0KHRoZVJlcXVlc3QpO1xuXHRcdH1cblx0XHQvL+aOkuW6j1xuXHRcdHF1ZXJ5c3RyaW5nID0gXy5zb3J0QnkocXVlcnlzdHJpbmcsICduYW1lJyk7XG5cdFx0Ly/ljrvph41cblx0XHRxdWVyeXN0cmluZyA9IF8udW5pcShxdWVyeXN0cmluZywgdHJ1ZSwgJ25hbWUnKTtcblx0XHR2YXIgdXJsRGF0YSA9ICcnO1xuXHRcdGZvciAobGV0IGkgPSAwOyBpIDwgcXVlcnlzdHJpbmcubGVuZ3RoOyBpKyspIHtcblx0XHRcdGlmIChxdWVyeXN0cmluZ1tpXSAmJiBxdWVyeXN0cmluZ1tpXS5uYW1lICYmIHF1ZXJ5c3RyaW5nW2ldLnZhbHVlKSB7XG5cdFx0XHRcdHVybERhdGEgKz0gcXVlcnlzdHJpbmdbaV0ubmFtZSArICc9JyArIHF1ZXJ5c3RyaW5nW2ldLnZhbHVlO1xuXHRcdFx0XHRpZiAoaSA8IChxdWVyeXN0cmluZy5sZW5ndGggLSAxKSkge1xuXHRcdFx0XHRcdHVybERhdGEgKz0gJyYnO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fVxuICAgICAgICB0b2tlbiA9IHRva2VuID8gdG9rZW4gOiB1dGlsLnNpdGVJbmZvLnRva2VuO1xuXHRcdHNpZ24gPSBtZDUodXJsRGF0YSArIHRva2VuKTtcblx0XHRyZXR1cm4gc2lnbjtcblx0fVxufVxudXRpbC5nZXRTaWduID0gZnVuY3Rpb24gKHVybCwgZGF0YSwgdG9rZW4pIHtcblx0cmV0dXJuIGdldFNpZ24odXJsLCBkYXRhLCB0b2tlbik7XG59O1xuLyoqXG5cdOS6jOasoeWwgeijheW+ruS/oXd4LnJlcXVlc3Tlh73mlbDjgIHlop7liqDkuqTkupLkvZPlhajjgIHphY3nva7nvJPlrZjjgIHku6Xlj4rphY3lkIjlvq7mk47moLzlvI/ljJbov5Tlm57mlbDmja5cblxuXHRAcGFyYW1zIG9wdGlvbiDlvLnlh7rlj4LmlbDooajvvIxcblx0e1xuXHRcdHVybCA6IOWQjOW+ruS/oSxcblx0XHRkYXRhIDog5ZCM5b6u5L+hLFxuXHRcdGhlYWRlciA6IOWQjOW+ruS/oSxcblx0XHRtZXRob2QgOiDlkIzlvq7kv6EsXG5cdFx0c3VjY2VzcyA6IOWQjOW+ruS/oSxcblx0XHRmYWlsIDog5ZCM5b6u5L+hLFxuXHRcdGNvbXBsZXRlIDog5ZCM5b6u5L+hLFxuXG5cdFx0Y2FjaGV0aW1lIDog57yT5a2Y5ZGo5pyf77yM5Zyo5q2k5ZGo5pyf5YaF5LiN6YeN5aSN6K+35rGCaHR0cO+8jOm7mOiupOS4jee8k+WtmFxuXHR9XG4qL1xudXRpbC5yZXF1ZXN0ID0gZnVuY3Rpb24gKG9wdGlvbikge1xuXHR2YXIgXyA9IHJlcXVpcmUoJ3VuZGVyc2NvcmUuanMnKTtcblx0dmFyIG1kNSA9IHJlcXVpcmUoJ21kNS5qcycpO1xuICAgIC8vIHZhciBhcHAgPSBnZXRBcHAoKTtcblx0dmFyIG9wdGlvbiA9IG9wdGlvbiA/IG9wdGlvbiA6IHt9O1xuXHRvcHRpb24uY2FjaGV0aW1lID0gb3B0aW9uLmNhY2hldGltZSA/IG9wdGlvbi5jYWNoZXRpbWUgOiAwO1xuXHRvcHRpb24uc2hvd0xvYWRpbmcgPSB0eXBlb2Ygb3B0aW9uLnNob3dMb2FkaW5nICE9ICd1bmRlZmluZWQnID8gb3B0aW9uLnNob3dMb2FkaW5nIDogZmFsc2U7IC8v6buY6K6k5YWz6ZetXG5cdHZhciBzZXNzaW9uaWQgPSB3eC5nZXRTdG9yYWdlU3luYygndXNlckluZm8nKS5zZXNzaW9uaWQ7XG5cdHZhciB1cmwgPSBvcHRpb24udXJsO1xuXHRpZiAodXJsLmluZGV4T2YoJ2h0dHA6Ly8nKSA9PSAtMSAmJiB1cmwuaW5kZXhPZignaHR0cHM6Ly8nKSA9PSAtMSkge1xuXHRcdHVybCA9IHV0aWwudXJsKHVybCk7XG5cdH1cblx0dmFyIHN0YXRlID0gZ2V0VXJsUGFyYW0odXJsLCAnc3RhdGUnKTtcblx0aWYgKCFzdGF0ZSAmJiAhKG9wdGlvbi5kYXRhICYmIG9wdGlvbi5kYXRhLnN0YXRlKSAmJiBzZXNzaW9uaWQpIHtcblx0XHR1cmwgPSB1cmwgKyAnJnN0YXRlPXdlN3NpZC0nICsgc2Vzc2lvbmlkXG5cdH1cblx0aWYgKCFvcHRpb24uZGF0YSB8fCAhb3B0aW9uLmRhdGEubSkge1xuICAgICAgICBpZih0eXBlb2YgdXRpbC5zaXRlSW5mby5tb2R1bGVuYW1lICE9ICd1bmRlZmluZWQnKXtcbiAgICAgICAgICAgIHVybCA9IHVybCArICcmbT0nICsgdXRpbC5zaXRlSW5mby5tb2R1bGVuYW1lO1xuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgdmFyIG5vd1BhZ2UgPSBnZXRDdXJyZW50UGFnZXMoKTtcbiAgICAgICAgICAgIGlmIChub3dQYWdlLmxlbmd0aCkge1xuICAgICAgICAgICAgICAgIG5vd1BhZ2UgPSBub3dQYWdlW2dldEN1cnJlbnRQYWdlcygpLmxlbmd0aCAtIDFdO1xuICAgICAgICAgICAgICAgIGlmIChub3dQYWdlICYmIG5vd1BhZ2UuX19yb3V0ZV9fKSB7XG4gICAgICAgICAgICAgICAgICAgIHVybCA9IHVybCArICcmbT0nICsgbm93UGFnZS5fX3JvdXRlX18uc3BsaXQoJy8nKVswXTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG5cdFx0fVxuXHR9XG5cblx0dmFyIHNpZ24gPSBnZXRTaWduKHVybCwgb3B0aW9uLmRhdGEpO1xuXHRpZiAoc2lnbikge1xuXHRcdHVybCA9IHVybCArIFwiJnNpZ249XCIgKyBzaWduO1xuXHR9XG5cdGlmICghdXJsKSB7XG5cdFx0cmV0dXJuIGZhbHNlO1xuXHR9XG5cdHd4LnNob3dOYXZpZ2F0aW9uQmFyTG9hZGluZygpO1xuXHRpZiAob3B0aW9uLnNob3dMb2FkaW5nKSB7XG5cdFx0dXRpbC5zaG93TG9hZGluZygpO1xuXHR9XG5cdGlmIChvcHRpb24uY2FjaGV0aW1lKSB7XG5cdFx0dmFyIGNhY2hla2V5ID0gbWQ1KHVybCk7XG5cdFx0dmFyIGNhY2hlZGF0YSA9IHd4LmdldFN0b3JhZ2VTeW5jKGNhY2hla2V5KTtcblx0XHR2YXIgdGltZXN0YW1wID0gRGF0ZS5wYXJzZShuZXcgRGF0ZSgpKTtcblxuXHRcdGlmIChjYWNoZWRhdGEgJiYgY2FjaGVkYXRhLmRhdGEpIHtcblx0XHRcdGlmIChjYWNoZWRhdGEuZXhwaXJlID4gdGltZXN0YW1wKSB7XG5cdFx0XHRcdGlmIChvcHRpb24uY29tcGxldGUgJiYgdHlwZW9mIG9wdGlvbi5jb21wbGV0ZSA9PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHRcdFx0b3B0aW9uLmNvbXBsZXRlKGNhY2hlZGF0YSk7XG5cdFx0XHRcdH1cblx0XHRcdFx0aWYgKG9wdGlvbi5zdWNjZXNzICYmIHR5cGVvZiBvcHRpb24uc3VjY2VzcyA9PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHRcdFx0b3B0aW9uLnN1Y2Nlc3MoY2FjaGVkYXRhKTtcblx0XHRcdFx0fVxuXHRcdFx0XHRjb25zb2xlLmxvZygnY2FjaGU6JyArIHVybCk7XG5cdFx0XHRcdHd4LmhpZGVMb2FkaW5nKCk7XG5cdFx0XHRcdHd4LmhpZGVOYXZpZ2F0aW9uQmFyTG9hZGluZygpO1xuXHRcdFx0XHRyZXR1cm4gdHJ1ZTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdHd4LnJlbW92ZVN0b3JhZ2VTeW5jKGNhY2hla2V5KVxuXHRcdFx0fVxuXHRcdH1cblx0fVxuXHR3eC5yZXF1ZXN0KHtcblx0XHQndXJsJzogdXJsLFxuXHRcdCdkYXRhJzogb3B0aW9uLmRhdGEgPyBvcHRpb24uZGF0YSA6IHt9LFxuXHRcdCdoZWFkZXInOiBvcHRpb24uaGVhZGVyID8gb3B0aW9uLmhlYWRlciA6IHt9LFxuXHRcdCdtZXRob2QnOiBvcHRpb24ubWV0aG9kID8gb3B0aW9uLm1ldGhvZCA6ICdHRVQnLFxuXHRcdCdoZWFkZXInOiB7XG5cdFx0XHQnY29udGVudC10eXBlJzogJ2FwcGxpY2F0aW9uL3gtd3d3LWZvcm0tdXJsZW5jb2RlZCdcblx0XHR9LFxuXHRcdCdzdWNjZXNzJzogZnVuY3Rpb24gKHJlc3BvbnNlKSB7XG5cdFx0XHR3eC5oaWRlTmF2aWdhdGlvbkJhckxvYWRpbmcoKTtcblx0XHRcdHd4LmhpZGVMb2FkaW5nKCk7XG5cdFx0XHRpZiAocmVzcG9uc2UuZGF0YS5lcnJubykge1xuXHRcdFx0XHRpZiAocmVzcG9uc2UuZGF0YS5lcnJubyA9PSAnNDEwMDknKSB7XG5cdFx0XHRcdFx0d3guc2V0U3RvcmFnZVN5bmMoJ3VzZXJJbmZvJywgJycpO1xuXHRcdFx0XHRcdHV0aWwuZ2V0VXNlckluZm8oZnVuY3Rpb24gKCkge1xuXHRcdFx0XHRcdFx0dXRpbC5yZXF1ZXN0KG9wdGlvbilcblx0XHRcdFx0XHR9KTtcblx0XHRcdFx0XHRyZXR1cm47XG5cdFx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdFx0aWYgKG9wdGlvbi5mYWlsICYmIHR5cGVvZiBvcHRpb24uZmFpbCA9PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHRcdFx0XHRvcHRpb24uZmFpbChyZXNwb25zZSk7XG5cdFx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHRcdGlmIChyZXNwb25zZS5kYXRhLm1lc3NhZ2UpIHtcblx0XHRcdFx0XHRcdFx0aWYgKHJlc3BvbnNlLmRhdGEuZGF0YSAhPSBudWxsICYmIHJlc3BvbnNlLmRhdGEuZGF0YS5yZWRpcmVjdCkge1xuXHRcdFx0XHRcdFx0XHRcdHZhciByZWRpcmVjdCA9IHJlc3BvbnNlLmRhdGEuZGF0YS5yZWRpcmVjdDtcblx0XHRcdFx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHRcdFx0XHR2YXIgcmVkaXJlY3QgPSAnJztcblx0XHRcdFx0XHRcdFx0fVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHV0aWwubWVzc2FnZShyZXNwb25zZS5kYXRhLm1lc3NhZ2UsIHJlZGlyZWN0LCAnZXJyb3InKTtcblx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHR9XG4gICAgICAgICAgICB9IGVsc2Uge1xuXHRcdFx0XHRpZiAob3B0aW9uLnN1Y2Nlc3MgJiYgdHlwZW9mIG9wdGlvbi5zdWNjZXNzID09ICdmdW5jdGlvbicpIHtcblx0XHRcdFx0XHRvcHRpb24uc3VjY2VzcyhyZXNwb25zZSk7XG5cdFx0XHRcdH1cblx0XHRcdFx0Ly/lhpnlhaXnvJPlrZjvvIzlh4/lsJFIVFRQ6K+35rGC77yM5bm25LiU5aaC5p6c572R57uc5byC5bi45Y+v5Lul6K+75Y+W57yT5a2Y5pWw5o2uXG5cdFx0XHRcdGlmIChvcHRpb24uY2FjaGV0aW1lKSB7XG5cdFx0XHRcdFx0dmFyIGNhY2hlZGF0YSA9IHsgJ2RhdGEnOiByZXNwb25zZS5kYXRhLCAnZXhwaXJlJzogdGltZXN0YW1wICsgb3B0aW9uLmNhY2hldGltZSAqIDEwMDAgfTtcblx0XHRcdFx0XHR3eC5zZXRTdG9yYWdlU3luYyhjYWNoZWtleSwgY2FjaGVkYXRhKTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdH0sXG5cdFx0J2ZhaWwnOiBmdW5jdGlvbiAocmVzcG9uc2UpIHtcblx0XHRcdHd4LmhpZGVOYXZpZ2F0aW9uQmFyTG9hZGluZygpO1xuXHRcdFx0d3guaGlkZUxvYWRpbmcoKTtcblx0XHRcdFxuXHRcdFx0Ly/lpoLmnpzor7fmsYLlpLHotKXvvIzlsJ3or5Xku47nvJPlrZjkuK3or7vlj5bmlbDmja5cblx0XHRcdHZhciBtZDUgPSByZXF1aXJlKCdtZDUuanMnKTtcblx0XHRcdHZhciBjYWNoZWtleSA9IG1kNSh1cmwpO1xuXHRcdFx0dmFyIGNhY2hlZGF0YSA9IHd4LmdldFN0b3JhZ2VTeW5jKGNhY2hla2V5KTtcblx0XHRcdGlmIChjYWNoZWRhdGEgJiYgY2FjaGVkYXRhLmRhdGEpIHtcblx0XHRcdFx0aWYgKG9wdGlvbi5zdWNjZXNzICYmIHR5cGVvZiBvcHRpb24uc3VjY2VzcyA9PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHRcdFx0b3B0aW9uLnN1Y2Nlc3MoY2FjaGVkYXRhKTtcblx0XHRcdFx0fVxuXHRcdFx0XHRjb25zb2xlLmxvZygnZmFpbHJlYWRjYWNoZTonICsgdXJsKTtcblx0XHRcdFx0cmV0dXJuIHRydWU7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRpZiAob3B0aW9uLmZhaWwgJiYgdHlwZW9mIG9wdGlvbi5mYWlsID09ICdmdW5jdGlvbicpIHtcblx0XHRcdFx0XHRvcHRpb24uZmFpbChyZXNwb25zZSk7XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9LFxuXHRcdCdjb21wbGV0ZSc6IGZ1bmN0aW9uIChyZXNwb25zZSkge1xuXHRcdFx0Ly8gd3guaGlkZU5hdmlnYXRpb25CYXJMb2FkaW5nKCk7XG5cdFx0XHQvLyB3eC5oaWRlTG9hZGluZygpO1xuXHRcdFx0aWYgKG9wdGlvbi5jb21wbGV0ZSAmJiB0eXBlb2Ygb3B0aW9uLmNvbXBsZXRlID09ICdmdW5jdGlvbicpIHtcblx0XHRcdFx0b3B0aW9uLmNvbXBsZXRlKHJlc3BvbnNlKTtcblx0XHRcdH1cblx0XHR9XG5cdH0pO1xufVxuLypcbiog6I635Y+W55So5oi35L+h5oGvXG4qL1xudXRpbC5nZXRVc2VySW5mbyA9IGZ1bmN0aW9uIChjYikge1xuXHR2YXIgbG9naW4gPSBmdW5jdGlvbigpIHtcblx0XHRjb25zb2xlLmxvZygnc3RhcnQgbG9naW4nKTtcblx0XHR2YXIgdXNlckluZm8gPSB7XG5cdFx0XHQnc2Vzc2lvbmlkJzogJycsXG5cdFx0XHQnd3hJbmZvJzogJycsXG5cdFx0XHQnbWVtYmVySW5mbyc6ICcnLFxuXHRcdH07XG5cdFx0d3gubG9naW4oe1xuXHRcdFx0c3VjY2VzczogZnVuY3Rpb24gKHJlcykge1xuXHRcdFx0XHR1dGlsLnJlcXVlc3Qoe1xuXHRcdFx0XHRcdHVybDogJ2F1dGgvc2Vzc2lvbi9vcGVuaWQnLFxuXHRcdFx0XHRcdGRhdGE6IHsgY29kZTogcmVzLmNvZGUgfSxcblx0XHRcdFx0XHRjYWNoZXRpbWU6IDAsXG5cdFx0XHRcdFx0c2hvd0xvYWRpbmc6IGZhbHNlLFxuXHRcdFx0XHRcdHN1Y2Nlc3M6IGZ1bmN0aW9uIChzZXNzaW9uKSB7XG5cdFx0XHRcdFx0XHRpZiAoIXNlc3Npb24uZGF0YS5lcnJubykge1xuXHRcdFx0XHRcdFx0XHR1c2VySW5mby5zZXNzaW9uaWQgPSBzZXNzaW9uLmRhdGEuZGF0YS5zZXNzaW9uaWRcblx0XHRcdFx0XHRcdFx0d3guc2V0U3RvcmFnZVN5bmMoJ3VzZXJJbmZvJywgdXNlckluZm8pO1xuXHRcdFx0XHRcdFx0XHR3eC5nZXRVc2VySW5mbyh7XG5cdFx0XHRcdFx0XHRcdFx0c3VjY2VzczogZnVuY3Rpb24gKHd4SW5mbykge1xuXHRcdFx0XHRcdFx0XHRcdFx0dXNlckluZm8ud3hJbmZvID0gd3hJbmZvLnVzZXJJbmZvXG5cdFx0XHRcdFx0XHRcdFx0XHR3eC5zZXRTdG9yYWdlU3luYygndXNlckluZm8nLCB1c2VySW5mbyk7XG5cdFx0XHRcdFx0XHRcdFx0XHR1dGlsLnJlcXVlc3Qoe1xuXHRcdFx0XHRcdFx0XHRcdFx0XHR1cmw6ICdhdXRoL3Nlc3Npb24vdXNlcmluZm8nLFxuXHRcdFx0XHRcdFx0XHRcdFx0XHRkYXRhOiB7XG5cdFx0XHRcdFx0XHRcdFx0XHRcdFx0c2lnbmF0dXJlOiB3eEluZm8uc2lnbmF0dXJlLFxuXHRcdFx0XHRcdFx0XHRcdFx0XHRcdHJhd0RhdGE6IHd4SW5mby5yYXdEYXRhLFxuXHRcdFx0XHRcdFx0XHRcdFx0XHRcdGl2OiB3eEluZm8uaXYsXG5cdFx0XHRcdFx0XHRcdFx0XHRcdFx0ZW5jcnlwdGVkRGF0YTogd3hJbmZvLmVuY3J5cHRlZERhdGFcblx0XHRcdFx0XHRcdFx0XHRcdFx0fSxcblx0XHRcdFx0XHRcdFx0XHRcdFx0bWV0aG9kOiAnUE9TVCcsXG5cdFx0XHRcdFx0XHRcdFx0XHRcdGhlYWRlcjoge1xuXHRcdFx0XHRcdFx0XHRcdFx0XHRcdCdjb250ZW50LXR5cGUnOiAnYXBwbGljYXRpb24veC13d3ctZm9ybS11cmxlbmNvZGVkJ1xuXHRcdFx0XHRcdFx0XHRcdFx0XHR9LFxuXHRcdFx0XHRcdFx0XHRcdFx0XHRjYWNoZXRpbWU6IDAsXG5cdFx0XHRcdFx0XHRcdFx0XHRcdHN1Y2Nlc3M6IGZ1bmN0aW9uIChyZXMpIHtcblx0XHRcdFx0XHRcdFx0XHRcdFx0XHRpZiAoIXJlcy5kYXRhLmVycm5vKSB7XG5cdFx0XHRcdFx0XHRcdFx0XHRcdFx0XHR1c2VySW5mby5tZW1iZXJJbmZvID0gcmVzLmRhdGEuZGF0YTtcblx0XHRcdFx0XHRcdFx0XHRcdFx0XHRcdHd4LnNldFN0b3JhZ2VTeW5jKCd1c2VySW5mbycsIHVzZXJJbmZvKTtcblx0XHRcdFx0XHRcdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0XHRcdFx0XHRcdFx0dHlwZW9mIGNiID09IFwiZnVuY3Rpb25cIiAmJiBjYih1c2VySW5mbyk7XG5cdFx0XHRcdFx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHRcdFx0XHRcdH0pO1xuXHRcdFx0XHRcdFx0XHRcdH0sXG5cdFx0XHRcdFx0XHRcdFx0ZmFpbDogZnVuY3Rpb24gKCkge1xuXHRcdFx0XHRcdFx0XHRcdFx0dHlwZW9mIGNiID09IFwiZnVuY3Rpb25cIiAmJiBjYih1c2VySW5mbyk7XG5cdFx0XHRcdFx0XHRcdFx0fSxcblx0XHRcdFx0XHRcdFx0XHRjb21wbGV0ZTogZnVuY3Rpb24gKCkge1xuXHRcdFx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHRcdFx0fSlcblx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHR9XG5cdFx0XHRcdH0pO1xuXHRcdFx0fSxcblx0XHRcdGZhaWw6IGZ1bmN0aW9uICgpIHtcblx0XHRcdFx0d3guc2hvd01vZGFsKHtcblx0XHRcdFx0XHR0aXRsZTogJ+iOt+WPluS/oeaBr+Wksei0pScsXG5cdFx0XHRcdFx0Y29udGVudDogJ+ivt+WFgeiuuOaOiOadg+S7peS+v+S4uuaCqOaPkOS+m+e7meacjeWKoScsXG5cdFx0XHRcdFx0c3VjY2VzczogZnVuY3Rpb24gKHJlcykge1xuXHRcdFx0XHRcdFx0aWYgKHJlcy5jb25maXJtKSB7XG5cdFx0XHRcdFx0XHRcdHV0aWwuZ2V0VXNlckluZm8oKTtcblx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHR9XG5cdFx0XHRcdH0pXG5cdFx0XHR9XG5cdFx0fSk7XG5cdH07XG5cblx0dmFyIGFwcCA9IHd4LmdldFN0b3JhZ2VTeW5jKCd1c2VySW5mbycpO1xuXHRpZiAoYXBwLnNlc3Npb25pZCkge1xuXHRcdHd4LmNoZWNrU2Vzc2lvbih7XG5cdFx0XHRzdWNjZXNzOiBmdW5jdGlvbigpe1xuXHRcdFx0XHR0eXBlb2YgY2IgPT0gXCJmdW5jdGlvblwiICYmIGNiKGFwcCk7XG5cdFx0XHR9LFxuXHRcdFx0ZmFpbDogZnVuY3Rpb24oKXtcblx0XHRcdFx0YXBwLnNlc3Npb25pZCA9ICcnO1xuXHRcdFx0XHRjb25zb2xlLmxvZygncmVsb2dpbicpO1xuXHRcdFx0XHR3eC5yZW1vdmVTdG9yYWdlU3luYygndXNlckluZm8nKTtcblx0XHRcdFx0bG9naW4oKTtcblx0XHRcdH1cblx0XHR9KVxuXHR9IGVsc2Uge1xuXHRcdC8v6LCD55So55m75b2V5o6l5Y+jXG5cdFx0bG9naW4oKTtcblx0fVxufVxuXG51dGlsLm5hdmlnYXRlQmFjayA9IGZ1bmN0aW9uIChvYmopIHtcblx0bGV0IGRlbHRhID0gb2JqLmRlbHRhID8gb2JqLmRlbHRhIDogMTtcblx0aWYgKG9iai5kYXRhKSB7XG5cdFx0bGV0IHBhZ2VzID0gZ2V0Q3VycmVudFBhZ2VzKClcblx0XHRsZXQgY3VyUGFnZSA9IHBhZ2VzW3BhZ2VzLmxlbmd0aCAtIChkZWx0YSArIDEpXTtcblx0XHRpZiAoY3VyUGFnZS5wYWdlRm9yUmVzdWx0KSB7XG5cdFx0XHRjdXJQYWdlLnBhZ2VGb3JSZXN1bHQob2JqLmRhdGEpO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHRjdXJQYWdlLnNldERhdGEob2JqLmRhdGEpO1xuXHRcdH1cblx0fVxuXHR3eC5uYXZpZ2F0ZUJhY2soe1xuXHRcdGRlbHRhOiBkZWx0YSwgLy8g5Zue6YCA5YmNIGRlbHRhKOm7mOiupOS4ujEpIOmhtemdolxuXHRcdHN1Y2Nlc3M6IGZ1bmN0aW9uIChyZXMpIHtcblx0XHRcdC8vIHN1Y2Nlc3Ncblx0XHRcdHR5cGVvZiBvYmouc3VjY2VzcyA9PSBcImZ1bmN0aW9uXCIgJiYgb2JqLnN1Y2Nlc3MocmVzKTtcblx0XHR9LFxuXHRcdGZhaWw6IGZ1bmN0aW9uIChlcnIpIHtcblx0XHRcdC8vIGZhaWxcblx0XHRcdHR5cGVvZiBvYmouZmFpbCA9PSBcImZ1bmN0aW9uXCIgJiYgb2JqLmZhaWwoZXJyKTtcblx0XHR9LFxuXHRcdGNvbXBsZXRlOiBmdW5jdGlvbiAoKSB7XG5cdFx0XHQvLyBjb21wbGV0ZVxuXHRcdFx0dHlwZW9mIG9iai5jb21wbGV0ZSA9PSBcImZ1bmN0aW9uXCIgJiYgb2JqLmNvbXBsZXRlKCk7XG5cdFx0fVxuXHR9KVxufTtcblxudXRpbC5mb290ZXIgPSBmdW5jdGlvbiAoJHRoaXMpIHtcblx0bGV0IGFwcCA9IGdldEFwcCgpO1xuXHRsZXQgdGhhdCA9ICR0aGlzO1xuXHRsZXQgdGFiQmFyID0gYXBwLnRhYkJhcjtcblx0Zm9yIChsZXQgaSBpbiB0YWJCYXJbJ2xpc3QnXSkge1xuXHRcdHRhYkJhclsnbGlzdCddW2ldWydwYWdlVXJsJ10gPSB0YWJCYXJbJ2xpc3QnXVtpXVsncGFnZVBhdGgnXS5yZXBsYWNlKC8oXFw/fCMpW15cIl0qL2csICcnKVxuXHR9XG5cdHRoYXQuc2V0RGF0YSh7XG5cdFx0dGFiQmFyOiB0YWJCYXIsXG5cdFx0J3RhYkJhci50aGlzdXJsJzogdGhhdC5fX3JvdXRlX19cblx0fSlcbn07XG4vKlxuICog5o+Q56S65L+h5oGvXG4gKiB0eXBlIOS4uiBzdWNjZXNzLCBlcnJvciDlvZPkuLogc3VjY2VzcywgIOaXtu+8jOS4unRvYXN05pa55byP77yM5ZCm5YiZ5Li65qih5oCB5qGG55qE5pa55byPXG4gKiByZWRpcmVjdCDkuLrmj5DnpLrlkI7nmoTot7PovazlnLDlnYAsIOi3s+i9rOeahOaXtuWAmeWPr+S7peWKoOS4iiDljY/orq7lkI3np7AgIFxuICogbmF2aWdhdGU6L3dlNy9wYWdlcy9kZXRhaWwvZGV0YWlsIOS7pSBuYXZpZ2F0ZVRvIOeahOaWueazlei3s+i9rO+8jFxuICogcmVkaXJlY3Q6L3dlNy9wYWdlcy9kZXRhaWwvZGV0YWlsIOS7pSByZWRpcmVjdFRvIOeahOaWueW8j+i3s+i9rO+8jOm7mOiupOS4uiByZWRpcmVjdFxuKi9cbnV0aWwubWVzc2FnZSA9IGZ1bmN0aW9uKHRpdGxlLCByZWRpcmVjdCwgdHlwZSkge1xuXHRpZiAoIXRpdGxlKSB7XG5cdFx0cmV0dXJuIHRydWU7XG5cdH1cblx0aWYgKHR5cGVvZiB0aXRsZSA9PSAnb2JqZWN0Jykge1xuXHRcdHJlZGlyZWN0ID0gdGl0bGUucmVkaXJlY3Q7XG5cdFx0dHlwZSA9IHRpdGxlLnR5cGU7XG5cdFx0dGl0bGUgPSB0aXRsZS50aXRsZTtcblx0fVxuXHRpZiAocmVkaXJlY3QpIHtcblx0XHR2YXIgcmVkaXJlY3RUeXBlID0gcmVkaXJlY3Quc3Vic3RyaW5nKDAsIDkpLCB1cmwgPSAnJywgcmVkaXJlY3RGdW5jdGlvbiA9ICcnO1xuXHRcdGlmIChyZWRpcmVjdFR5cGUgPT0gJ25hdmlnYXRlOicpIHtcblx0XHRcdHJlZGlyZWN0RnVuY3Rpb24gPSAnbmF2aWdhdGVUbyc7XG5cdFx0XHR1cmwgPSByZWRpcmVjdC5zdWJzdHJpbmcoOSk7XG5cdFx0fSBlbHNlIGlmIChyZWRpcmVjdFR5cGUgPT0gJ3JlZGlyZWN0OicpIHtcblx0XHRcdHJlZGlyZWN0RnVuY3Rpb24gPSAncmVkaXJlY3RUbyc7XG5cdFx0XHR1cmwgPSByZWRpcmVjdC5zdWJzdHJpbmcoOSk7XG5cdFx0fSBlbHNlIHtcblx0XHRcdHVybCA9IHJlZGlyZWN0O1xuXHRcdFx0cmVkaXJlY3RGdW5jdGlvbiA9ICdyZWRpcmVjdFRvJztcblx0XHR9XG5cdH1cblx0Y29uc29sZS5sb2codXJsKVxuXHRpZiAoIXR5cGUpIHtcblx0XHR0eXBlID0gJ3N1Y2Nlc3MnO1xuXHR9XG5cblx0aWYgKHR5cGUgPT0gJ3N1Y2Nlc3MnKSB7XG5cdFx0d3guc2hvd1RvYXN0KHtcblx0XHRcdHRpdGxlOiB0aXRsZSxcblx0XHRcdGljb246ICdzdWNjZXNzJyxcblx0XHRcdGR1cmF0aW9uOiAyMDAwLFxuXHRcdFx0bWFzayA6IHVybCA/IHRydWUgOiBmYWxzZSxcblx0XHRcdGNvbXBsZXRlIDogZnVuY3Rpb24oKSB7XG5cdFx0XHRcdGlmICh1cmwpIHtcblx0XHRcdFx0XHRzZXRUaW1lb3V0KGZ1bmN0aW9uKCl7XG5cdFx0XHRcdFx0XHR3eFtyZWRpcmVjdEZ1bmN0aW9uXSh7XG5cdFx0XHRcdFx0XHRcdHVybDogdXJsLFxuXHRcdFx0XHRcdFx0fSk7XG5cdFx0XHRcdFx0fSwgMTgwMCk7XG5cdFx0XHRcdH1cblx0XHRcdFx0XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH0gZWxzZSBpZiAodHlwZSA9PSAnZXJyb3InKSB7XG5cdFx0d3guc2hvd01vZGFsKHtcblx0XHRcdHRpdGxlOiAn57O757uf5L+h5oGvJyxcblx0XHRcdGNvbnRlbnQgOiB0aXRsZSxcblx0XHRcdHNob3dDYW5jZWwgOiBmYWxzZSxcblx0XHRcdGNvbXBsZXRlIDogZnVuY3Rpb24oKSB7XG5cdFx0XHRcdGlmICh1cmwpIHtcblx0XHRcdFx0XHR3eFtyZWRpcmVjdEZ1bmN0aW9uXSh7XG5cdFx0XHRcdFx0XHR1cmw6IHVybCxcblx0XHRcdFx0XHR9KTtcblx0XHRcdFx0fVxuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG59XG5cbnV0aWwudXNlciA9IHV0aWwuZ2V0VXNlckluZm87XG5cbi8v5bCB6KOF5b6u5L+h562J5b6F5o+Q56S677yM6Ziy5q2iYWpheOi/h+WkmuaXtu+8jHNob3flpJrmrKFcbnV0aWwuc2hvd0xvYWRpbmcgPSBmdW5jdGlvbigpIHtcblx0dmFyIGlzU2hvd0xvYWRpbmcgPSB3eC5nZXRTdG9yYWdlU3luYygnaXNTaG93TG9hZGluZycpO1xuXHRpZiAoaXNTaG93TG9hZGluZykge1xuXHRcdHd4LmhpZGVMb2FkaW5nKCk7XG5cdFx0d3guc2V0U3RvcmFnZVN5bmMoJ2lzU2hvd0xvYWRpbmcnLCBmYWxzZSk7XG5cdH1cblxuXHR3eC5zaG93TG9hZGluZyh7XG5cdFx0dGl0bGUgOiAn5Yqg6L295LitJyxcblx0XHRjb21wbGV0ZSA6IGZ1bmN0aW9uKCkge1xuXHRcdFx0d3guc2V0U3RvcmFnZVN5bmMoJ2lzU2hvd0xvYWRpbmcnLCB0cnVlKTtcblx0XHR9LFxuXHRcdGZhaWwgOiBmdW5jdGlvbigpIHtcblx0XHRcdHd4LnNldFN0b3JhZ2VTeW5jKCdpc1Nob3dMb2FkaW5nJywgZmFsc2UpO1xuXHRcdH1cblx0fSk7XG59XG5cbnV0aWwuc2hvd0ltYWdlID0gZnVuY3Rpb24oZXZlbnQpIHtcblx0dmFyIHVybCA9IGV2ZW50ID8gZXZlbnQuY3VycmVudFRhcmdldC5kYXRhc2V0LnByZXZpZXcgOiAnJztcblx0aWYgKCF1cmwpIHtcblx0XHRyZXR1cm4gZmFsc2U7XG5cdH1cblx0d3gucHJldmlld0ltYWdlKHtcblx0XHR1cmxzOiBbdXJsXVxuXHR9KTtcbn1cblxuLyoqXG4gKiDovazmjaLlhoXlrrnkuK3nmoRlbW9qaeihqOaDheS4uiB1bmljb2RlIOeggeeCue+8jOWcqFBocOS4reS9v+eUqHV0ZjhfYnl0ZXPmnaXovazmjaLovpPlh7pcbiovXG51dGlsLnBhcnNlQ29udGVudCA9IGZ1bmN0aW9uKHN0cmluZykge1xuXHRpZiAoIXN0cmluZykge1xuXHRcdHJldHVybiBzdHJpbmc7XG5cdH1cblxuXHR2YXIgcmFuZ2VzID0gW1xuXHRcdFx0J1xcdWQ4M2NbXFx1ZGYwMC1cXHVkZmZmXScsIC8vIFUrMUYzMDAgdG8gVSsxRjNGRlxuXHRcdFx0J1xcdWQ4M2RbXFx1ZGMwMC1cXHVkZTRmXScsIC8vIFUrMUY0MDAgdG8gVSsxRjY0RlxuXHRcdFx0J1xcdWQ4M2RbXFx1ZGU4MC1cXHVkZWZmXScgIC8vIFUrMUY2ODAgdG8gVSsxRjZGRlxuXHRcdF07XG5cdHZhciBlbW9qaSA9IHN0cmluZy5tYXRjaChcblx0XHRuZXcgUmVnRXhwKHJhbmdlcy5qb2luKCd8JyksICdnJykpO1xuXG5cdGlmIChlbW9qaSkge1xuXHRcdGZvciAodmFyIGkgaW4gZW1vamkpIHtcblx0XHRcdHN0cmluZyA9IHN0cmluZy5yZXBsYWNlKGVtb2ppW2ldLCAnW1UrJyArIGVtb2ppW2ldLmNvZGVQb2ludEF0KDApLnRvU3RyaW5nKDE2KS50b1VwcGVyQ2FzZSgpICsgJ10nKTtcblx0XHR9XG5cdH1cblx0cmV0dXJuIHN0cmluZztcbn1cblxudXRpbC5kYXRlID0gZnVuY3Rpb24oKXtcblx0LyoqXG5cdCAqIOWIpOaWremXsOW5tFxuXHQgKiBAcGFyYW0gZGF0ZSBEYXRl5pel5pyf5a+56LGhXG5cdCAqIEByZXR1cm4gYm9vbGVhbiB0cnVlIOaIlmZhbHNlXG5cdCAqL1xuXHR0aGlzLmlzTGVhcFllYXIgPSBmdW5jdGlvbihkYXRlKXtcblx0XHRyZXR1cm4gKDA9PWRhdGUuZ2V0WWVhcigpJTQmJigoZGF0ZS5nZXRZZWFyKCklMTAwIT0wKXx8KGRhdGUuZ2V0WWVhcigpJTQwMD09MCkpKTsgXG5cdH1cblx0XG5cdC8qKlxuXHQgKiDml6XmnJ/lr7nosaHovazmjaLkuLrmjIflrprmoLzlvI/nmoTlrZfnrKbkuLJcblx0ICogQHBhcmFtIGYg5pel5pyf5qC85byPLOagvOW8j+WumuS5ieWmguS4iyB5eXl5LU1NLWRkIEhIOm1tOnNzXG5cdCAqIEBwYXJhbSBkYXRlIERhdGXml6XmnJ/lr7nosaEsIOWmguaenOe8uuecge+8jOWImeS4uuW9k+WJjeaXtumXtFxuXHQgKlxuXHQgKiBZWVlZL3l5eXkvWVkveXkg6KGo56S65bm05Lu9ICBcblx0ICogTU0vTSDmnIjku70gIFxuXHQgKiBXL3cg5pif5pyfICBcblx0ICogZGQvREQvZC9EIOaXpeacnyAgXG5cdCAqIGhoL0hIL2gvSCDml7bpl7QgIFxuXHQgKiBtbS9tIOWIhumSnyAgXG5cdCAqIHNzL1NTL3MvUyDnp5IgIFxuXHQgKiBAcmV0dXJuIHN0cmluZyDmjIflrprmoLzlvI/nmoTml7bpl7TlrZfnrKbkuLJcblx0ICovXG5cdHRoaXMuZGF0ZVRvU3RyID0gZnVuY3Rpb24oZm9ybWF0U3RyLCBkYXRlKXtcblx0XHRmb3JtYXRTdHIgPSBhcmd1bWVudHNbMF0gfHwgXCJ5eXl5LU1NLWRkIEhIOm1tOnNzXCI7XG5cdFx0ZGF0ZSA9IGFyZ3VtZW50c1sxXSB8fCBuZXcgRGF0ZSgpO1xuXHRcdHZhciBzdHIgPSBmb3JtYXRTdHI7ICAgXG5cdFx0dmFyIFdlZWsgPSBbJ+aXpScsJ+S4gCcsJ+S6jCcsJ+S4iScsJ+WbmycsJ+S6lCcsJ+WFrSddOyAgXG5cdFx0c3RyPXN0ci5yZXBsYWNlKC95eXl5fFlZWVkvLGRhdGUuZ2V0RnVsbFllYXIoKSk7ICAgXG5cdFx0c3RyPXN0ci5yZXBsYWNlKC95eXxZWS8sKGRhdGUuZ2V0WWVhcigpICUgMTAwKT45PyhkYXRlLmdldFllYXIoKSAlIDEwMCkudG9TdHJpbmcoKTonMCcgKyAoZGF0ZS5nZXRZZWFyKCkgJSAxMDApKTsgICBcblx0XHRzdHI9c3RyLnJlcGxhY2UoL01NLyxkYXRlLmdldE1vbnRoKCk+OT8oZGF0ZS5nZXRNb250aCgpICsgMSk6JzAnICsgKGRhdGUuZ2V0TW9udGgoKSArIDEpKTsgICBcblx0XHRzdHI9c3RyLnJlcGxhY2UoL00vZyxkYXRlLmdldE1vbnRoKCkpOyAgIFxuXHRcdHN0cj1zdHIucmVwbGFjZSgvd3xXL2csV2Vla1tkYXRlLmdldERheSgpXSk7ICAgXG5cdCAgXG5cdFx0c3RyPXN0ci5yZXBsYWNlKC9kZHxERC8sZGF0ZS5nZXREYXRlKCk+OT9kYXRlLmdldERhdGUoKS50b1N0cmluZygpOicwJyArIGRhdGUuZ2V0RGF0ZSgpKTsgICBcblx0XHRzdHI9c3RyLnJlcGxhY2UoL2R8RC9nLGRhdGUuZ2V0RGF0ZSgpKTsgICBcblx0ICBcblx0XHRzdHI9c3RyLnJlcGxhY2UoL2hofEhILyxkYXRlLmdldEhvdXJzKCk+OT9kYXRlLmdldEhvdXJzKCkudG9TdHJpbmcoKTonMCcgKyBkYXRlLmdldEhvdXJzKCkpOyAgIFxuXHRcdHN0cj1zdHIucmVwbGFjZSgvaHxIL2csZGF0ZS5nZXRIb3VycygpKTsgICBcblx0XHRzdHI9c3RyLnJlcGxhY2UoL21tLyxkYXRlLmdldE1pbnV0ZXMoKT45P2RhdGUuZ2V0TWludXRlcygpLnRvU3RyaW5nKCk6JzAnICsgZGF0ZS5nZXRNaW51dGVzKCkpOyAgIFxuXHRcdHN0cj1zdHIucmVwbGFjZSgvbS9nLGRhdGUuZ2V0TWludXRlcygpKTsgICBcblx0ICBcblx0XHRzdHI9c3RyLnJlcGxhY2UoL3NzfFNTLyxkYXRlLmdldFNlY29uZHMoKT45P2RhdGUuZ2V0U2Vjb25kcygpLnRvU3RyaW5nKCk6JzAnICsgZGF0ZS5nZXRTZWNvbmRzKCkpOyAgIFxuXHRcdHN0cj1zdHIucmVwbGFjZSgvc3xTL2csZGF0ZS5nZXRTZWNvbmRzKCkpOyAgIFxuXHQgIFxuXHRcdHJldHVybiBzdHI7ICAgXG5cdH1cbiBcblx0XG5cdC8qKlxuXHQqIOaXpeacn+iuoeeulyAgXG5cdCogQHBhcmFtIHN0ckludGVydmFsIHN0cmluZyAg5Y+v6YCJ5YC8IHkg5bm0IG3mnIggZOaXpSB35pif5pyfIHd35ZGoIGjml7YgbuWIhiBz56eSICBcblx0KiBAcGFyYW0gbnVtIGludFxuXHQqIEBwYXJhbSBkYXRlIERhdGUg5pel5pyf5a+56LGhXG5cdCogQHJldHVybiBEYXRlIOi/lOWbnuaXpeacn+WvueixoVxuXHQqL1xuXHR0aGlzLmRhdGVBZGQgPSBmdW5jdGlvbihzdHJJbnRlcnZhbCwgbnVtLCBkYXRlKXtcblx0XHRkYXRlID0gIGFyZ3VtZW50c1syXSB8fCBuZXcgRGF0ZSgpO1xuXHRcdHN3aXRjaCAoc3RySW50ZXJ2YWwpIHsgXG5cdFx0XHRjYXNlICdzJyA6cmV0dXJuIG5ldyBEYXRlKGRhdGUuZ2V0VGltZSgpICsgKDEwMDAgKiBudW0pKTsgIFxuXHRcdFx0Y2FzZSAnbicgOnJldHVybiBuZXcgRGF0ZShkYXRlLmdldFRpbWUoKSArICg2MDAwMCAqIG51bSkpOyAgXG5cdFx0XHRjYXNlICdoJyA6cmV0dXJuIG5ldyBEYXRlKGRhdGUuZ2V0VGltZSgpICsgKDM2MDAwMDAgKiBudW0pKTsgIFxuXHRcdFx0Y2FzZSAnZCcgOnJldHVybiBuZXcgRGF0ZShkYXRlLmdldFRpbWUoKSArICg4NjQwMDAwMCAqIG51bSkpOyAgXG5cdFx0XHRjYXNlICd3JyA6cmV0dXJuIG5ldyBEYXRlKGRhdGUuZ2V0VGltZSgpICsgKCg4NjQwMDAwMCAqIDcpICogbnVtKSk7ICBcblx0XHRcdGNhc2UgJ20nIDpyZXR1cm4gbmV3IERhdGUoZGF0ZS5nZXRGdWxsWWVhcigpLCAoZGF0ZS5nZXRNb250aCgpKSArIG51bSwgZGF0ZS5nZXREYXRlKCksIGRhdGUuZ2V0SG91cnMoKSwgZGF0ZS5nZXRNaW51dGVzKCksIGRhdGUuZ2V0U2Vjb25kcygpKTsgIFxuXHRcdFx0Y2FzZSAneScgOnJldHVybiBuZXcgRGF0ZSgoZGF0ZS5nZXRGdWxsWWVhcigpICsgbnVtKSwgZGF0ZS5nZXRNb250aCgpLCBkYXRlLmdldERhdGUoKSwgZGF0ZS5nZXRIb3VycygpLCBkYXRlLmdldE1pbnV0ZXMoKSwgZGF0ZS5nZXRTZWNvbmRzKCkpOyAgXG5cdFx0fSAgXG5cdH0gIFxuXHRcblx0LyoqXG5cdCog5q+U6L6D5pel5pyf5beuIGR0RW5kIOagvOW8j+S4uuaXpeacn+Wei+aIluiAheacieaViOaXpeacn+agvOW8j+Wtl+espuS4slxuXHQqIEBwYXJhbSBzdHJJbnRlcnZhbCBzdHJpbmcgIOWPr+mAieWAvCB5IOW5tCBt5pyIIGTml6Ugd+aYn+acnyB3d+WRqCBo5pe2IG7liIYgc+enkiAgXG5cdCogQHBhcmFtIGR0U3RhcnQgRGF0ZSAg5Y+v6YCJ5YC8IHkg5bm0IG3mnIggZOaXpSB35pif5pyfIHd35ZGoIGjml7YgbuWIhiBz56eSXG5cdCogQHBhcmFtIGR0RW5kIERhdGUgIOWPr+mAieWAvCB5IOW5tCBt5pyIIGTml6Ugd+aYn+acnyB3d+WRqCBo5pe2IG7liIYgc+enkiBcblx0Ki9cblx0dGhpcy5kYXRlRGlmZiA9IGZ1bmN0aW9uKHN0ckludGVydmFsLCBkdFN0YXJ0LCBkdEVuZCkgeyAgIFxuXHRcdHN3aXRjaCAoc3RySW50ZXJ2YWwpIHsgICBcblx0XHRcdGNhc2UgJ3MnIDpyZXR1cm4gcGFyc2VJbnQoKGR0RW5kIC0gZHRTdGFydCkgLyAxMDAwKTsgIFxuXHRcdFx0Y2FzZSAnbicgOnJldHVybiBwYXJzZUludCgoZHRFbmQgLSBkdFN0YXJ0KSAvIDYwMDAwKTsgIFxuXHRcdFx0Y2FzZSAnaCcgOnJldHVybiBwYXJzZUludCgoZHRFbmQgLSBkdFN0YXJ0KSAvIDM2MDAwMDApOyAgXG5cdFx0XHRjYXNlICdkJyA6cmV0dXJuIHBhcnNlSW50KChkdEVuZCAtIGR0U3RhcnQpIC8gODY0MDAwMDApOyAgXG5cdFx0XHRjYXNlICd3JyA6cmV0dXJuIHBhcnNlSW50KChkdEVuZCAtIGR0U3RhcnQpIC8gKDg2NDAwMDAwICogNykpOyAgXG5cdFx0XHRjYXNlICdtJyA6cmV0dXJuIChkdEVuZC5nZXRNb250aCgpKzEpKygoZHRFbmQuZ2V0RnVsbFllYXIoKS1kdFN0YXJ0LmdldEZ1bGxZZWFyKCkpKjEyKSAtIChkdFN0YXJ0LmdldE1vbnRoKCkrMSk7ICBcblx0XHRcdGNhc2UgJ3knIDpyZXR1cm4gZHRFbmQuZ2V0RnVsbFllYXIoKSAtIGR0U3RhcnQuZ2V0RnVsbFllYXIoKTsgIFxuXHRcdH0gIFxuXHR9XG4gXG5cdC8qKlxuXHQqIOWtl+espuS4sui9rOaNouS4uuaXpeacn+WvueixoSAvLyBldmFsIOS4jeWPr+eUqFxuXHQqIEBwYXJhbSBkYXRlIERhdGUg5qC85byP5Li6eXl5eS1NTS1kZCBISDptbTpzc++8jOW/hemhu+aMieW5tOaciOaXpeaXtuWIhuenkueahOmhuuW6j++8jOS4remXtOWIhumalOespuS4jemZkOWItlxuXHQqL1xuXHR0aGlzLnN0clRvRGF0ZSA9IGZ1bmN0aW9uKGRhdGVTdHIpe1xuXHRcdHZhciBkYXRhID0gZGF0ZVN0cjsgIFxuXHRcdHZhciByZUNhdCA9IC8oXFxkezEsNH0pL2dtOyAgIFxuXHRcdHZhciB0ID0gZGF0YS5tYXRjaChyZUNhdCk7XG5cdFx0dFsxXSA9IHRbMV0gLSAxO1xuXHRcdGV2YWwoJ3ZhciBkID0gbmV3IERhdGUoJyt0LmpvaW4oJywnKSsnKTsnKTtcblx0XHRyZXR1cm4gZDtcblx0fVxuIFxuXHQvKipcblx0KiDmiormjIflrprmoLzlvI/nmoTlrZfnrKbkuLLovazmjaLkuLrml6XmnJ/lr7nosaF5eXl5LU1NLWRkIEhIOm1tOnNzXG5cdCogXG5cdCovXG5cdHRoaXMuc3RyRm9ybWF0VG9EYXRlID0gZnVuY3Rpb24oZm9ybWF0U3RyLCBkYXRlU3RyKXtcblx0XHR2YXIgeWVhciA9IDA7XG5cdFx0dmFyIHN0YXJ0ID0gLTE7XG5cdFx0dmFyIGxlbiA9IGRhdGVTdHIubGVuZ3RoO1xuXHRcdGlmKChzdGFydCA9IGZvcm1hdFN0ci5pbmRleE9mKCd5eXl5JykpID4gLTEgJiYgc3RhcnQgPCBsZW4pe1xuXHRcdFx0eWVhciA9IGRhdGVTdHIuc3Vic3RyKHN0YXJ0LCA0KTtcblx0XHR9XG5cdFx0dmFyIG1vbnRoID0gMDtcblx0XHRpZigoc3RhcnQgPSBmb3JtYXRTdHIuaW5kZXhPZignTU0nKSkgPiAtMSAgJiYgc3RhcnQgPCBsZW4pe1xuXHRcdFx0bW9udGggPSBwYXJzZUludChkYXRlU3RyLnN1YnN0cihzdGFydCwgMikpIC0gMTtcblx0XHR9XG5cdFx0dmFyIGRheSA9IDA7XG5cdFx0aWYoKHN0YXJ0ID0gZm9ybWF0U3RyLmluZGV4T2YoJ2RkJykpID4gLTEgJiYgc3RhcnQgPCBsZW4pe1xuXHRcdFx0ZGF5ID0gcGFyc2VJbnQoZGF0ZVN0ci5zdWJzdHIoc3RhcnQsIDIpKTtcblx0XHR9XG5cdFx0dmFyIGhvdXIgPSAwO1xuXHRcdGlmKCAoKHN0YXJ0ID0gZm9ybWF0U3RyLmluZGV4T2YoJ0hIJykpID4gLTEgfHwgKHN0YXJ0ID0gZm9ybWF0U3RyLmluZGV4T2YoJ2hoJykpID4gMSkgJiYgc3RhcnQgPCBsZW4pe1xuXHRcdFx0aG91ciA9IHBhcnNlSW50KGRhdGVTdHIuc3Vic3RyKHN0YXJ0LCAyKSk7XG5cdFx0fVxuXHRcdHZhciBtaW51dGUgPSAwO1xuXHRcdGlmKChzdGFydCA9IGZvcm1hdFN0ci5pbmRleE9mKCdtbScpKSA+IC0xICAmJiBzdGFydCA8IGxlbil7XG5cdFx0XHRtaW51dGUgPSBkYXRlU3RyLnN1YnN0cihzdGFydCwgMik7XG5cdFx0fVxuXHRcdHZhciBzZWNvbmQgPSAwO1xuXHRcdGlmKChzdGFydCA9IGZvcm1hdFN0ci5pbmRleE9mKCdzcycpKSA+IC0xICAmJiBzdGFydCA8IGxlbil7XG5cdFx0XHRzZWNvbmQgPSBkYXRlU3RyLnN1YnN0cihzdGFydCwgMik7XG5cdFx0fVxuXHRcdHJldHVybiBuZXcgRGF0ZSh5ZWFyLCBtb250aCwgZGF5LCBob3VyLCBtaW51dGUsIHNlY29uZCk7XG5cdH1cbiBcbiBcblx0LyoqXG5cdCog5pel5pyf5a+56LGh6L2s5o2i5Li65q+r56eS5pWwXG5cdCovXG5cdHRoaXMuZGF0ZVRvTG9uZyA9IGZ1bmN0aW9uKGRhdGUpe1xuXHRcdHJldHVybiBkYXRlLmdldFRpbWUoKTtcblx0fVxuIFxuXHQvKipcblx0KiDmr6vnp5LovazmjaLkuLrml6XmnJ/lr7nosaFcblx0KiBAcGFyYW0gZGF0ZVZhbCBudW1iZXIg5pel5pyf55qE5q+r56eS5pWwIFxuXHQqL1xuXHR0aGlzLmxvbmdUb0RhdGUgPSBmdW5jdGlvbihkYXRlVmFsKXtcblx0XHRyZXR1cm4gbmV3IERhdGUoZGF0ZVZhbCk7XG5cdH1cbiBcblx0LyoqXG5cdCog5Yik5pat5a2X56ym5Liy5piv5ZCm5Li65pel5pyf5qC85byPXG5cdCogQHBhcmFtIHN0ciBzdHJpbmcg5a2X56ym5LiyXG5cdCogQHBhcmFtIGZvcm1hdFN0ciBzdHJpbmcg5pel5pyf5qC85byP77yMIOWmguS4iyB5eXl5LU1NLWRkXG5cdCovXG5cdHRoaXMuaXNEYXRlID0gZnVuY3Rpb24oc3RyLCBmb3JtYXRTdHIpe1xuXHRcdGlmIChmb3JtYXRTdHIgPT0gbnVsbCl7XG5cdFx0XHRmb3JtYXRTdHIgPSBcInl5eXlNTWRkXCI7XHRcblx0XHR9XG5cdFx0dmFyIHlJbmRleCA9IGZvcm1hdFN0ci5pbmRleE9mKFwieXl5eVwiKTtcdCBcblx0XHRpZih5SW5kZXg9PS0xKXtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cdFx0dmFyIHllYXIgPSBzdHIuc3Vic3RyaW5nKHlJbmRleCx5SW5kZXgrNCk7XHQgXG5cdFx0dmFyIG1JbmRleCA9IGZvcm1hdFN0ci5pbmRleE9mKFwiTU1cIik7XHQgXG5cdFx0aWYobUluZGV4PT0tMSl7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXHRcdHZhciBtb250aCA9IHN0ci5zdWJzdHJpbmcobUluZGV4LG1JbmRleCsyKTtcdCBcblx0XHR2YXIgZEluZGV4ID0gZm9ybWF0U3RyLmluZGV4T2YoXCJkZFwiKTtcdCBcblx0XHRpZihkSW5kZXg9PS0xKXtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9XG5cdFx0dmFyIGRheSA9IHN0ci5zdWJzdHJpbmcoZEluZGV4LGRJbmRleCsyKTtcdCBcblx0XHRpZighaXNOdW1iZXIoeWVhcil8fHllYXI+XCIyMTAwXCIgfHwgeWVhcjwgXCIxOTAwXCIpe1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblx0XHRpZighaXNOdW1iZXIobW9udGgpfHxtb250aD5cIjEyXCIgfHwgbW9udGg8IFwiMDFcIil7XG5cdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0fVxuXHRcdGlmKGRheT5nZXRNYXhEYXkoeWVhcixtb250aCkgfHwgZGF5PCBcIjAxXCIpe1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblx0XHRyZXR1cm4gdHJ1ZTsgICBcblx0fVxuXHRcblx0dGhpcy5nZXRNYXhEYXkgPSBmdW5jdGlvbih5ZWFyLG1vbnRoKSB7XHQgXG5cdFx0aWYobW9udGg9PTR8fG1vbnRoPT02fHxtb250aD09OXx8bW9udGg9PTExKVx0IFxuXHRcdFx0cmV0dXJuIFwiMzBcIjtcdCBcblx0XHRpZihtb250aD09MilcdCBcblx0XHRcdGlmKHllYXIlND09MCYmeWVhciUxMDAhPTAgfHwgeWVhciU0MDA9PTApXHQgXG5cdFx0XHRcdHJldHVybiBcIjI5XCI7XHQgXG5cdFx0XHRlbHNlXHQgXG5cdFx0XHRcdHJldHVybiBcIjI4XCI7XHQgXG5cdFx0cmV0dXJuIFwiMzFcIjtcdCBcblx0fVx0IFxuXHQvKipcblx0Klx05Y+Y6YeP5piv5ZCm5Li65pWw5a2XXG5cdCovXG5cdHRoaXMuaXNOdW1iZXIgPSBmdW5jdGlvbihzdHIpXG5cdHtcblx0XHR2YXIgcmVnRXhwID0gL15cXGQrJC9nO1xuXHRcdHJldHVybiByZWdFeHAudGVzdChzdHIpO1xuXHR9XG5cdFxuXHQvKipcblx0KiDmiorml6XmnJ/liIblibLmiJDmlbDnu4QgW+W5tOOAgeaciOOAgeaXpeOAgeaXtuOAgeWIhuOAgeenkl1cblx0Ki9cblx0dGhpcy50b0FycmF5ID0gZnVuY3Rpb24obXlEYXRlKSAgXG5cdHsgICBcblx0XHRteURhdGUgPSBhcmd1bWVudHNbMF0gfHwgbmV3IERhdGUoKTtcblx0XHR2YXIgbXlBcnJheSA9IEFycmF5KCk7ICBcblx0XHRteUFycmF5WzBdID0gbXlEYXRlLmdldEZ1bGxZZWFyKCk7ICBcblx0XHRteUFycmF5WzFdID0gbXlEYXRlLmdldE1vbnRoKCk7ICBcblx0XHRteUFycmF5WzJdID0gbXlEYXRlLmdldERhdGUoKTsgIFxuXHRcdG15QXJyYXlbM10gPSBteURhdGUuZ2V0SG91cnMoKTsgIFxuXHRcdG15QXJyYXlbNF0gPSBteURhdGUuZ2V0TWludXRlcygpOyAgXG5cdFx0bXlBcnJheVs1XSA9IG15RGF0ZS5nZXRTZWNvbmRzKCk7ICBcblx0XHRyZXR1cm4gbXlBcnJheTsgIFxuXHR9ICBcblx0XG5cdC8qKlxuXHQqIOWPluW+l+aXpeacn+aVsOaNruS/oeaBryAgXG5cdCog5Y+C5pWwIGludGVydmFsIOihqOekuuaVsOaNruexu+WeiyAgXG5cdCogeSDlubQgTeaciCBk5pelIHfmmJ/mnJ8gd3flkaggaOaXtiBu5YiGIHPnp5IgIFxuXHQqL1xuXHR0aGlzLmRhdGVQYXJ0ID0gZnVuY3Rpb24oaW50ZXJ2YWwsIG15RGF0ZSkgIFxuXHR7ICAgXG5cdFx0bXlEYXRlID0gYXJndW1lbnRzWzFdIHx8IG5ldyBEYXRlKCk7XG5cdFx0dmFyIHBhcnRTdHI9Jyc7ICBcblx0XHR2YXIgV2VlayA9IFsn5pelJywn5LiAJywn5LqMJywn5LiJJywn5ZubJywn5LqUJywn5YWtJ107ICBcblx0XHRzd2l0Y2ggKGludGVydmFsKSAgXG5cdFx0eyAgIFxuXHRcdFx0Y2FzZSAneScgOnBhcnRTdHIgPSBteURhdGUuZ2V0RnVsbFllYXIoKTticmVhazsgIFxuXHRcdFx0Y2FzZSAnTScgOnBhcnRTdHIgPSBteURhdGUuZ2V0TW9udGgoKSsxO2JyZWFrOyAgXG5cdFx0XHRjYXNlICdkJyA6cGFydFN0ciA9IG15RGF0ZS5nZXREYXRlKCk7YnJlYWs7ICBcblx0XHRcdGNhc2UgJ3cnIDpwYXJ0U3RyID0gV2Vla1tteURhdGUuZ2V0RGF5KCldO2JyZWFrOyAgXG5cdFx0XHRjYXNlICd3dycgOnBhcnRTdHIgPSBteURhdGUuV2Vla051bU9mWWVhcigpO2JyZWFrOyAgXG5cdFx0XHRjYXNlICdoJyA6cGFydFN0ciA9IG15RGF0ZS5nZXRIb3VycygpO2JyZWFrOyAgXG5cdFx0XHRjYXNlICdtJyA6cGFydFN0ciA9IG15RGF0ZS5nZXRNaW51dGVzKCk7YnJlYWs7ICBcblx0XHRcdGNhc2UgJ3MnIDpwYXJ0U3RyID0gbXlEYXRlLmdldFNlY29uZHMoKTticmVhazsgIFxuXHRcdH0gIFxuXHRcdHJldHVybiBwYXJ0U3RyOyAgXG5cdH0gIFxuXHRcblx0LyoqXG5cdCog5Y+W5b6X5b2T5YmN5pel5pyf5omA5Zyo5pyI55qE5pyA5aSn5aSp5pWwICBcblx0Ki9cblx0dGhpcy5tYXhEYXlPZkRhdGUgPSBmdW5jdGlvbihkYXRlKSAgXG5cdHsgICBcblx0XHRkYXRlID0gYXJndW1lbnRzWzBdIHx8IG5ldyBEYXRlKCk7XG5cdFx0ZGF0ZS5zZXREYXRlKDEpO1xuXHRcdGRhdGUuc2V0TW9udGgoZGF0ZS5nZXRNb250aCgpICsgMSk7XG5cdFx0dmFyIHRpbWUgPSBkYXRlLmdldFRpbWUoKSAtIDI0ICogNjAgKiA2MCAqIDEwMDA7XG5cdFx0dmFyIG5ld0RhdGUgPSBuZXcgRGF0ZSh0aW1lKTtcblx0XHRyZXR1cm4gbmV3RGF0ZS5nZXREYXRlKCk7XG5cdH1cbn07XG5cbm1vZHVsZS5leHBvcnRzID0gdXRpbDsiXX0=