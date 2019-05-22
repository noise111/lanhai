/**
 * Created by zhoufeng on 2017/6/19.
 */
$(function() {
    $("#checkAll").click(function() {
        var checked = $(this).get(0).checked;
        var group = $(this).data('group');
        $("#regionid[data-group='" +group + "']").each(function(){
            $(this).get(0).checked = checked;
        })
    });
    //省
    $('.tpl-province').change(function(){
        var province =$('.tpl-province').val();
        $.getJSON("../web/index.php?c=site&a=entry&op=add&do=cregion&m=xfeng_community",{province:province},function(data){
            var region ="";
            region += "<label class=\"checkbox-inline\" >";
            region += "<input type=\"checkbox\" id=\"checkAll\" name=\"checkAll\" data-group=\"regionid\" />全部";
            region += "</label>";
            for (var o in data) {
                var che = '';
                if(regionids){
                    if(regionids.indexOf(data[o].id)!=-1){
                        che = 'checked';
                    }
                }
                region += "<label class=\"checkbox-inline\" >";
                region += "<input type='checkbox' id='regionid' value='" + data[o].id + "' data-group='regionid' name='regionid[]'"+che+">" + data[o].title;
                region += "</label>";
            }
            $('.content').html(region);
            $('.region').show();
            $("#checkAll").click(function() {
                var checked = $(this).get(0).checked;
                var group = $(this).data('group');
                $("#regionid[data-group='" +group + "']").each(function(){
                    $(this).get(0).checked = checked;
                })
            });
        })
    })
    //市
    $('.tpl-city').change(function(){
        var city =$('.tpl-city').val();
        $.getJSON("../web/index.php?c=site&a=entry&op=add&do=cregion&m=xfeng_community",{city:city},function(data){
            var region ="";
            region += "<label class=\"checkbox-inline\" >";
            region += "<input type=\"checkbox\" id=\"checkAll\" name=\"checkAll\" data-group=\"regionid\"  />全部";
            region += "</label>";
            for (var o in data) {
                var che = '';
                if(regionids){
                    if(regionids.indexOf(data[o].id)!=-1){
                        che = 'checked';
                    }
                }
                region += "<label class=\"checkbox-inline\" >";
                region += "<input type='checkbox' id='regionid' value='" + data[o].id + "' data-group='regionid' name='regionid[]'"+che+">" + data[o].title;
                region += "</label>";
            }
            $('.content').html(region);
            $('.region').show();
            $("#checkAll").click(function() {
                var checked = $(this).get(0).checked;
                var group = $(this).data('group');
                $("#regionid[data-group='" +group + "']").each(function(){
                    $(this).get(0).checked = checked;
                })
            });
        })
    })
    //区
    $('.tpl-district').change(function(){
        var dist =$('.tpl-district').val();
        $.getJSON("../web/index.php?c=site&a=entry&op=add&do=cregion&m=xfeng_community",{dist:dist},function(data){
            var region ="";
            region += "<label class=\"checkbox-inline\" >";
            region += "<input type=\"checkbox\" id=\"checkAll\" name=\"checkAll\" data-group=\"regionid\"  />全部";
            region += "</label>";
            for (var o in data) {
                var che = '';
                if(regionids){
                    if(regionids.indexOf(data[o].id)!=-1){
                        che = 'checked';
                    }
                }
                region += "<label class=\"checkbox-inline\" >";
                region += "<input type='checkbox' id='regionid' value='" + data[o].id + "' data-group='regionid' name='regionid[]'"+che+">" + data[o].title;
                region += "</label>";
            }
            $('.content').html(region);
            $('.region').show();
            $("#checkAll").click(function() {
                var checked = $(this).get(0).checked;
                var group = $(this).data('group');
                $("#regionid[data-group='" +group + "']").each(function(){
                    $(this).get(0).checked = checked;
                })
            });
        })
    })

});
