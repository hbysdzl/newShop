;
var spec_value = {};
var max_nums = 99;
var min_nums = 1;
var box_top = [];
var check_box = -1;
var value_add = 0;
var goods_detail = {
    //主图小图切换和大图放大镜
    'big_image' : function(){
        $('.jqzoom').jqzoom({
            zoomWidth: 380,
            zoomHeight: 380,
            zoomType: 'standard',
            lens: true,
            preloadImages: false,
            alwaysOn: false
        });
        var big_count = $("#thumblist li").length;
        if (big_count > 5) {
            $(".box-tab").als({
                visible_items: 5,
                scrolling_items: 1,
                orientation: "horizontal",
                circular: "yes",
                autoscroll: "no",
                interval: 500,
                speed: 300,
                easing: "linear",
                direction: "right",
                start_from: 0
            });
        }
    },
    //购买数量增减
    'buy_num' : function () {
        // 增加
        $('.btn-add').click(function () {
            var buy_num = goods_detail.check_quantity();
            if(buy_num == goods_storage && goods_storage > 0)
            {
                showDialog("当前库存不足，最多可购买"+goods_storage+"件！", '', '', '', 1, '', '', '', '', 2);
            }else if (buy_num < max_nums && buy_num != false){
                $('#buy_num').val(buy_num+1);
            }
        });
        //减少
        $('.btn-reduce').click(function () {
            var buy_num = goods_detail.check_quantity();
            if(buy_num > min_nums && buy_num != false){
                $('#buy_num').val(buy_num-1);
            }
        });
        $('#buy_num').blur(function(){
            var buy_num = goods_detail.check_quantity();
            if(buy_num != false && buy_num > 0){
                $('#buy_num').val(buy_num);
            }
        });
    },
    //检测购买数量
    'check_quantity' : function(){
        var num = parseInt($('#buy_num').val());
        if(num > goods_storage && goods_storage > 0){
            $('#buy_num').val(goods_storage);
            showDialog("当前库存不足，最多可购买"+goods_storage+"件！", '', '', '', 1, '', '', '', '', 2);
            return false;
        }else if(num > max_nums) {
            $('#buy_num').val(max_nums);
            showDialog("单笔每人次最多购买"+max_nums+"件！", '', '', '', 1, '', '', '', '', 2);
            return false;
        }else if(num < min_nums) {
            $('#buy_num').val(min_nums);
            showDialog("单笔每人次最少购买"+min_nums+"件！", '', '', '', 1, '', '', '', '', 2);
            return false;
        }else if(num > 0){
        }else{
            num = 1;
        }
        return num;
    },
    //加入购物车
    'add_cart' : function(){
        //加入购物车单击事件
        $(".a_add_car,.fixed_add_car").click(function(event) {
            var buy_num = goods_detail.check_quantity();
            if(buy_num != false && buy_num > 0) {
                addcart(goods_commonid, goods_id, buy_num, 'addcart_callback', event);
            }
        });
    },
    //到货通知
    'arrival_notice' : function(){
        //弹出号码输入框
        $('#arrival_notice').click(function(){
            $.get('index.php?act=index&op=login', function(result) {
                if (result == '0') {
                    showDialog("您还没有登录，请先登录！", '', '', function(){
                        window.location.href = SHOP_LOGIN_URL;
                    }, 1);
                } else {
                    $('#notice_tc').show();
                }
            });
        });
        //关闭号码输入框
        $('#notice_tc_close').click(function(){
            $('#notice_tc').hide();
        });
        //点击确定按钮
        $('#notice_sure').click(function(){
            var notice_phone = $('#notice_phone').val();
            if(notice_phone){
                $.ajax({
                    url: "index.php?act=goods&op=arrival_notice",
                    type: "POST",
                    dataType: "json",
                    data : {'goods_id': goods_id, 'notice_phone' : notice_phone},
                    success: function(data) {
                        if(data.state){
                            $('#notice_tc_close').click();
                            showDialog(data.msg, 'succ', '', '', 1, '', '', '', '', 2);
                        }else{
                            alert(data.msg);
                        }
                    },
                    error: function(e) {
                        alert("系统错误，请刷新后重试！");
                    }
                });
            }else{
                alert("请输入您的联系方式，以便到货后我们第一时间通知您哦！");
            }
        });
    },
    //规格选择
    'spec_select' : function(){
        //初始化已选中的值
        spec_value = {};
        $.each($('.spec_ul li.on'),function(i,n){
            var data_param = '';
            eval('data_param =' + $(this).find('a').attr('data-param'));
            spec_value[data_param.key] = data_param.valid;
        });
        //点击选择
        $('.spec_ul li').click(function(){
            var data_param = '';
            eval('data_param =' + $(this).find('a').attr('data-param'));
            goods_detail.spec_link(data_param.key, data_param.valid);
        });
    },
    //跳转对应规格地址
    'spec_link' : function(key, valid){
        if(valid != spec_value[key]){
            spec_value[key] = valid;
            $('#spec_ul_'+key+' li').removeClass('on');
            $('#spec_li_'+valid).addClass('on');

            var spec_value1 = new Array();
            $.each(spec_value, function(i,n){
                spec_value1.push(n);
            });
            var spec_value2 = spec_value1.sort(function(a,b){
                return a-b;
            });
            var spec_sign = spec_value2.join('|');
            $.each(spec_param, function(i, n){
                if (n.sign == spec_sign) {
                    window.location.href = n.url;
                }
            });

        }
    },
    //立即购买
    'buy_now' : function(){
        $('#buy_now,.fixed_buy_now').click(function(){
            $.get('index.php?act=index&op=login', function(result) {
                if (result == '0') {
                    showDialog("您还没有登录，请先登录！", '', '', function(){
                        window.location.href = SHOP_LOGIN_URL;
                    }, 1);
                } else {
                    var quantity = goods_detail.check_quantity();
                    if (!quantity) {
                        return false;
                    }
                    $("#cart_id_1").val(goods_id+'|'+quantity);
                    if($('.value_added li.on').size() > 0){
                        var temp_goods_id = $('.value_added li.on').eq(0).attr('data');
                        $("#cart_id_2").val(temp_goods_id+'|'+quantity+'|'+goods_id);
                    }
                    $("#buynow_form").submit();
                }
            });
        });
    },
    //导航滚动
    'nav_scroll' : function(){
        //导航点击和选中
        $(".p_relative ul").onePageNav({
            scrollThreshold: 0.3,
            currentClass:"on"
        });

        //导航固定
        var dv = $('.detail_nav_c');
        var dvtop = dv.offset().top;
        var st;
        $(window).scroll(function () {
            st = Math.max(document.body.scrollTop || document.documentElement.scrollTop);
            if (st > parseInt(dvtop)) {
                $(".detail_nav_c").addClass("navfixed");
            }else {
                $(".detail_nav_c").removeClass("navfixed");
            }
        });
        //导航选中
        $(".detail_nav_c ul li").click(function () {
            $(this).parent().find("li").removeClass("on");
            $(this).addClass("on");
        });
    },
    //评论图片点击
    'comment_img' : function(){
        $(".appr_img_list li").on("click", function () {
            var div_obj = $(this).parents("div.appraise_img");
            if ($(this).hasClass("on")) {
                $(this).removeClass("on");
                div_obj.find('.big_img').hide(500);
            }else {
                div_obj.find(".appr_img_list li").removeClass("on");
                $(this).addClass("on");
                div_obj.find('.big_img img').attr({ "src": $(this).attr("data") });
                div_obj.find('.big_img').show(500);
            }
        })
    },
    //推荐商品
    'recommend' : function(){
        var d_l_con4_count = $(".detail_l_con4 .als-wrapper li").length;
        if (d_l_con4_count > 5) {
            $(".detail_l_con4").als({
                visible_items: 5,
                scrolling_items: 5,
                orientation: "horizontal",
                circular: "yes",
                autoscroll: "no",
                interval: 5000,
                speed: 500,
                easing: "linear",
                direction: "right",
                start_from: 0
            });
        }
    },
    'value_added_select' : function () {
        $('.value_added li.optional_li').click(function(){
            $('.value_added li.optional_li').removeClass('on');
            var this_value = $(this).attr('data');
            if(this_value != value_add){
                $(this).addClass('on');
                value_add = this_value;
            }else{
                value_add = 0;
            }
        });
    }
};

$(function(){
    //最大购买数
    max_nums = parseInt($('.btn-add').attr('data-param'));
    if(max_nums <= 0) max_nums = 99;
    //最小购买数
    min_nums = parseInt($('.btn-reduce').attr('data-param'));
    if(min_nums <= 0) min_nums = 1;

    //导航滚动
    goods_detail.nav_scroll();
    //商品大图
    goods_detail.big_image();
    //购买数量变更
    goods_detail.buy_num();
    //到货通知
    goods_detail.arrival_notice();
    //规格选择
    goods_detail.spec_select();
    //立即购买
    goods_detail.buy_now();
    //加入购物车
    goods_detail.add_cart();
    //评论图片
    goods_detail.comment_img();
    //推荐商品
    //goods_detail.recommend();
    //延保选择
    goods_detail.value_added_select();
});
