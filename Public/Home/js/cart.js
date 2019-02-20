	/*
@功能：购物车页面js
@作者：diamondwang
@时间：2018年11月14日
*/

$(function(){
	
	//减少
	$(".reduce_num").click(function(){
		var amount = $(this).parent().find(".amount");
		if (parseInt($(amount).val()) <= 1){
			alert("商品数量最少为1");
		} else{
			$(amount).val(parseInt($(amount).val()) - 1);
			
			//调用函数执行ajax
			cartGoodsNum($(amount).parent().parent().attr('goods_id'),$(amount).parent().parent().attr('goods_attr_id'),parseInt($(amount).val()));
		}
		//小计
		var subtotal = parseFloat($(this).parent().parent().find(".col3 span").text()) * parseInt($(amount).val());
		$(this).parent().parent().find(".col5 span").text(subtotal.toFixed(2));
		//总计金额
		var total = 0;
		$(".col5 span").each(function(){
			total += parseFloat($(this).text());
		});

		$("#total").text(total.toFixed(2));
	});

	//增加
	$(".add_num").click(function(){
		var amount = $(this).parent().find(".amount");
		$(amount).val(parseInt($(amount).val()) + 1);
		
		//调用函数执行ajax
		cartGoodsNum($(amount).parent().parent().attr('goods_id'),$(amount).parent().parent().attr('goods_attr_id'),parseInt($(amount).val()));
		//小计
		var subtotal = parseFloat($(this).parent().parent().find(".col3 span").text()) * parseInt($(amount).val());
		$(this).parent().parent().find(".col5 span").text(subtotal.toFixed(2));
		//总计金额
		var total = 0;
		$(".col5 span").each(function(){
			total += parseFloat($(this).text());
		});

		$("#total").text(total.toFixed(2));
	});

	//直接输入
	$(".amount").blur(function(){
		if (parseInt($(this).val()) < 1){
			alert("商品数量最少为1");
			$(this).val(1);
		}
		cartGoodsNum($(this).parent().parent().attr('goods_id'),$(this).parent().parent().attr('goods_attr_id'),parseInt($(this).val()));

		//小计
		var subtotal = parseFloat($(this).parent().parent().find(".col3 span").text()) * parseInt($(this).val());
		$(this).parent().parent().find(".col5 span").text(subtotal.toFixed(2));
		//总计金额
		var total = 0;
		$(".col5 span").each(function(){
			total += parseFloat($(this).text());
		});

		$("#total").text(total.toFixed(2));

	});
	
	//购物车商品的删除
	$(".col6 a").click(function(){
		if(confirm('确定要删除吗')){
			//获取所在的tr
			var tr=$(this).parent().parent();
			var gid=tr.attr('goods_id');
			var gaid=tr.attr('goods_attr_id');
			//执行Ajax到服务器进行删除
			cartGoodsNum(gid,gaid,0);
			tr.remove();
			var newP=parseFloat($('#total').html())-parseFloat(tr.find('.col5').find('span').html());//将价格解析为浮点数后进行运算
			$('#total').html(newP);
		}
	});
});


//修改购物车商品的数量
function cartGoodsNum(goods_id,goods_attr_id,goods_number){
	var gaAttr="";
	if(goods_attr_id!=""){
		gaAttr="/goods_attr_id/"+goods_attr_id;
	}
	
	$.ajax({
		type:'get',
		url:"/Home/Cart/ajaxGoodsNum/goods_id/"+goods_id+"/goods_number/"+goods_number+gaAttr,
		dataType:"json",
		success:function(msg){
			if(msg.ok==0){
				$('#tishi').html('!'+msg.error);
				$('#tishi').fadeIn(1000);
				$('#tishi').fadeOut(3000);
				$(".add_num").unbind();
			}
		}	
	});
}
