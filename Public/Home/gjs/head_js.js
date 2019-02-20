$(function () {
    //居中
    //$(window).resize(function () {
    //    var w = $(window).width();
    //    $(".header_ad").css("left", -((1900 - w) / 2));
    //});
    //$(window).resize();

    var cart_function = {
        'get_cart' : function() {
            var car_title = "最新加入的商品";

            $.ajax({
                url:SHOP_SITE_URL+"/index.php?act=cart&op=ajax_load",
                type:"POST",
                dataType : "json",
                data : { token : 'ok', type : 'info' },
                success: function(data){
                    if ("1" == data.state) {
                        if (0 == data.cart_goods_num) {
                            cart_function.zero_cart();
                        } else {
                            var dataInfo = data.list;
                            var cart_html = "";
                            var car_goods_href = "";
                            var car_goods_name = "";
                            var car_goods_img_src = "";
                            var car_goods_price = "";
                            var car_goods_num = "";
                            var car_goods_id = "";
                            var car_cart_id = "";
                            var cart_all_price = data.cart_all_price;
                            var cart_goods_num = data.cart_goods_num;

                            cart_html += "<h2>"+car_title+"</h2>";
                            cart_html += '<ul class="ul_mycarlist ClearFix">';

                            for(var i = 0; i < dataInfo.length; i++){ 
                                car_goods_href = dataInfo[i]['goods_url'];
                                car_goods_name = dataInfo[i]['goods_name'];
                                car_goods_img_src = dataInfo[i]['goods_image'];
                                car_goods_price = dataInfo[i]['goods_price'];
                                car_goods_num = dataInfo[i]['goods_num'];
                                car_cart_id = dataInfo[i]['cart_id'];
                                car_goods_id = dataInfo[i]['goods_id'] ? dataInfo[i]['goods_id'] : dataInfo[i]['cart_id'];

                                if (null == car_goods_price || null == car_goods_num || null == car_goods_id) {
                                    continue;
                                }
                                cart_html += '<li>';
                                cart_html += '<a href="'+car_goods_href+'" class="car_img" target="_blank">';
                                cart_html += '<img src="'+car_goods_img_src+'" /></a>';
                                cart_html += '<a href="'+car_goods_href+'" class="car_title" target="_blank">'+car_goods_name+'</a>';
                                cart_html += '<div class="car_p">';
                                cart_html += '<p>￥<span>'+car_goods_price+'</span>×'+car_goods_num+'</p>';
                                cart_html += '<a class="car_del" href="javascript:;" data-cart-id="'+car_cart_id+'" data-goods-id="'+car_goods_id+'">删除</a>';
                                cart_html += '</div></li>';
                            }

                            cart_html += '</ul><div class="car_sum">';
                            cart_html += '<span class="sum_l" id="goods_num">共'+cart_goods_num+'件商品</span>';
                            cart_html += '<span class="sum_m" id="all_price">共计￥'+cart_all_price+'</span>';
                            cart_html += '<a class="a_go_car" href="'+SHOP_SITE_URL+'/index.php?act=cart&op=index" target="_blank">去购物车</a></div>';
                            
                            $("#car_goods_list").removeClass("car_no_goods");
                            $("#car_goods_list").addClass("car_goods_d");
                            $("#car_goods_list").html(cart_html);
                            $(".i_car_num").attr('data-goods-num', cart_goods_num);
                            $(".i_car_num").html(cart_goods_num);
                            $(".car_num").html(cart_goods_num);
                        }
                    } else if ("-10" == data.state) {

                    } else {
                        cart_function.zero_cart();
                    }
                },
                error : function(data) {
                    cart_function.zero_cart();
                }
            });
        },
        'del_cart' : function(dataID) {
            var cart_id = dataID.attr('data-cart-id');
            var cart_goods_id = dataID.attr('data-goods-id');
            $.ajax({
                url:SHOP_SITE_URL+"/index.php?act=cart&op=del",
                type:"POST",
                dataType : "json",
                data : { token : 'ok' , cart_id : cart_id, goods_id : cart_goods_id},
                success: function(data){
                    if ("true" == data.state) {
                        var goods_num = data.quantity;
                        if (0 == goods_num) {
                            cart_function.zero_cart();
                        } else {
                            var goods_old_num = $(".i_car_num").attr("data-goods-num");
                            if (goods_old_num - 1 != goods_num) {
                                cart_function.get_cart();
                            } else {
                                var goods_price = data.amount;
                                $("#goods_num").html('共'+goods_num+'件商品');
                                $("#all_price").html('共计￥'+goods_price);
                                dataID.parents('div').parent('li').remove();
                            }
                        }
                        $(".i_car_num").attr('data-goods-num', goods_num);
                        $(".i_car_num").html(goods_num);
                        $(".car_num").html(goods_num);
                    } else {

                    }
                },
                error : function(data) {
                }
            });
        },
        'zero_cart' : function() {
            $("#car_goods_list").removeClass("car_goods_d");
            $("#car_goods_list").addClass("car_no_goods");
            var car_text = "购物车没商品 赶紧选购吧！";
            $("#car_goods_list").html(car_text);
        }
    };

    $(".car_del").live("click",function(){
        cart_function.del_cart($(this));
    });

    //购物车显示列表
    /*
    var TYPE_CART = 0;
    var TYPE_CART_LOOK = 0;
    $(".h_c_right").hover(function() {
        $(this).addClass("c_r_hover").find(".my_car_list").show();
        if (1 == TYPE_CART) {
            return;
        }
        TYPE_CART = 1;
        var car_num = $(".i_car_num").text();
        var data_car_num = $(".i_car_num").attr('data-goods-num');
        if (0 == TYPE_CART_LOOK || data_car_num != car_num) {
            cart_function.get_cart();
        }
        TYPE_CART = 0;
        TYPE_CART_LOOK = 1;
    }, function() {
        $(this).removeClass("c_r_hover").find(".my_car_list").hide();
    });
    */

    //客服中心下拉框
    $(".cus_c_box").hover(function () {
        $(this).addClass("cus_c_border");
        $(".cus_center").show();
    }, function () {
        $(this).removeClass("cus_c_border");
        $(".cus_center").hide();
    });

    //关闭顶部广告
    $(".close_ad").on("click", function () {
        $(".full_ad").hide();
    });

    //右侧侧边栏
    //var wh = $(window).height();
    //$(".r_s_fixed").css({ "height": wh });

    //右侧侧边栏 鼠标移入效果
    $(".pos_relative").hover(function () {
        $(this).addClass("on");
        $(this).find(".f_c_list_tc").show();

        if (WEB_CUST_NO === null || WEB_CUST_NO === undefined || WEB_CUST_NO === '') { 
            var codeimage_src = $("#codeimage").attr("src");
            if (codeimage_src === null || codeimage_src === undefined || codeimage_src === '') { 
                $("#a_codeimage").click();
            }
        }
    }, function () {
        $(this).removeClass("on");
        $(this).find(".f_c_list_tc").hide();
    });

    //文本框获得焦点事件
    $(".onfocustxt,#nloginpwd").focusin(function () {
        $(".err_msg1").hide();
        $(this).parents(".item").find(".index_err_box .err_msg2").show();
    });
    //文本框失去焦点事件
    $(".onfocustxt,#nloginpwd").focusout(function () {
        $(this).parents(".item").find(".index_err_box .err_msg2").hide();
    });
    //注册按钮点击事件
    $(".uc_login").click(function () {
        $(".err_msg1").show();
    });

    //导航栏关闭弹窗按钮
    $(".uc_close").click(function () {
        $(".user_center").hide();
    });
    //下载快乐购app
    $(".d_l_hgp").hover(function () {
        $(".down_load_hpg").show();
    }, function () { $(".down_load_hpg").hide(); });
    
    
    
	//展开分类
	$(".li_all_type").hover(function(){
		$(".index_type").show();
	},function(){
		if(!$(".index_type").is(':hidden'))
		{
			$(".index_type").hide();
		}
	});
	
	$(".index_type").hover(function(){
		$(".index_type").show();
	},function(){
		$(".index_type").hide();
	})
	
    //首页分类下拉框
    $(".index_type_ul .li_has_down").hover(function () {
        $(this).addClass("cur");
        $(this).find(".nav_type_list").show();
    }, function () {
        $(this).removeClass("cur");
        $(this).find(".nav_type_list").hide();
    });
	
});
