var lock = 1;
var page = 1;
var myScroll = null;
function showLoader(msg) {
    $("#loading").show();
    $(".loading").html(msg).show();
}
function hideLoader() {
    $("#loading").hide();
    $(".loading").hide();
}

function loaddata(url, obj, object, sc, callback) {
    showLoader('正在加载中....');
    $.get(url, function (data) {
        if (data.err_code == -1) {
            hideLoader();
            $("#hideLoader").show();
            if (typeof callback == "function") {
                callback(data)
            }
            return false;
        }
        if (obj) {
            var result = data.data;
            if(result.list) {
                var gettpl = document.getElementById(object).innerHTML;
                laytpl(gettpl).render(result, function (html) {
                    obj.append(html);
                    if (typeof callback == "function") {
                        callback(data)
                    }
                });
            }
        }
        lock = 0;
        hideLoader();
    }, 'json');
    if (sc === true) {
        $(window).scroll(function () {
            if (!lock && $(window).scrollTop() == $(document).height() - $(window).height()) {
                lock = 1;
                page++;
                var link = url + '&page=' + page;
                showLoader('正在加载中....');
                $.get(link, function (data) {
                    if (data.err_code == -1) {
                        hideLoader();
                        $("#hideLoader").show();
                        if (typeof callback == "function") {
                            callback(data)
                        }
                        return false;
                    }
                    if (obj) {
                        var result = data.data;
                        if(result.list){
                            var gettpl = document.getElementById(object).innerHTML;
                            laytpl(gettpl).render(result, function (html) {
                                obj.append(html);
                            });
                        }

                    }

                    lock = 0;
                    hideLoader();
                }, 'json');
            }
        });
    }
}
function _loaddata(url, obj, object, sc, callback) {
    $.get(url, function (data) {
        if (obj) {
            var result = data.data;
            var gettpl = document.getElementById(object).innerHTML;
            laytpl(gettpl).render(result, function (html) {
                obj.append(html);
            });
        }
        if (typeof callback == "function") {
            callback(data)
        }
    }, 'json');
}