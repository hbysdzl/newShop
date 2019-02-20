//收藏商品js
function collect_goods(fav_id,jstype,jsobj){
    $.get(SHOP_SITE_URL+'/index.php?act=index&op=login', function(result) {
        if (result == '0') {
            showDialog("您还没有登录，请先登录！", '', '', function(){
                window.location.href = SHOP_LOGIN_URL;
            }, 1);
        } else {
            var url = SHOP_SITE_URL+'/index.php?act=member_favorites&op=favoritesgoods';
            $.getJSON(url, {'fid':fav_id}, function(data){
                if(data.state){
                    showDialog(data.msg, 'succ', '', '', 1, '', '', '', '', 2);
                    $('.detail_sc').removeClass("detail_sc_off");
                }else{
                    showDialog(data.msg, '', '', '', 1);
                }
            });
        }
    });
}

/* 加入购物车 */
function addcart(goods_commonid, goods_id, quantity, callbackfunc, event) {
    var url = SHOP_SITE_URL+'/index.php?act=cart&op=add';
    quantity = parseInt(quantity);
    $.getJSON(url, {'goods_id':goods_id, 'quantity':quantity}, function(data) {
        if (data != null) {
            if (data.state == true) {
                if(callbackfunc){
                    eval(callbackfunc + "(data, event)");
                }
                tracecart(goods_commonid, quantity);
            } else {
                if (data.state == '-10') {
                    showDialog(data.msg, '', '', function(){
                        window.location.href = SHOP_LOGIN_URL;
                    }, 1);
                } else {
                    showDialog(data.msg, '', '', '', 1);
                }
            }
        }
    });
}

function addcart_callback(data, event){
    // 加入购物车
    var eleFlyElement = document.querySelector("#flyItem"), eleShopCart = document.querySelector("#shopCart");
    var numberItem = $(".car_num").text();
    // 抛物线运动
    var myParabola = funParabola(eleFlyElement, eleShopCart, {
        speed: 150, //抛物线速度
        curvature: 0.0009, //控制抛物线弧度
        complete: function () {
            eleFlyElement.style.visibility = "hidden";
            $(".fly_item").css({ left: "0" });
            $(".i_car_num,.car_num").html(data.num);
            if(data.num > numberItem)
            {
                $(".icon_car").addClass("ani_icon_car");
                $(".ani_num").text(data.num - numberItem);
                $(".ani_num").animate({ top: "-25px", opacity: "0.6" }, function () {
                    $(".ani_num").css({ top: "3px", opacity: "0" });
                    $(".icon_car").removeClass("ani_icon_car");
                });
            }
        }
    });
    //抛物线图片
    var img_src = $(".zoomPad img").attr("src");
    $("#flyItem img").attr("src", img_src);
    var scrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft || 0,
        scrollTop = document.documentElement.scrollTop || document.body.scrollTop || 0;
    eleFlyElement.style.left = event.clientX + scrollLeft + "px";
    eleFlyElement.style.top = event.clientY + scrollTop + "px";
    eleFlyElement.style.visibility = "visible";
    // 需要重定位
    myParabola.position().move();
}

var hg_common = {
    //购物车内商品数量
    'cart_ajax_load' : function(){
        if (WEB_CUST_NO !== null && WEB_CUST_NO !== undefined && WEB_CUST_NO !== '') { 
            $.getJSON(SHOP_SITE_URL+'/index.php?act=cart&op=ajax_load&callback=?', function(result){
                if(result.cart_goods_num >= 0){
                    $(".i_car_num,.car_num").html(result.cart_goods_num);
                }
            });
        }
    }
}

//购物车内商品数量
$(function(){
    //hg_common.cart_ajax_load();
});