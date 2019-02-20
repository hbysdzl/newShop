
//登录方式切换
$(".radio_box input[type=radio]").on("click",function(){
        var i=$(this).parent().index();
        $(".mc").hide().eq(i).show();
});

//倒计时  
var countdown=60;  
function settime(val) {

    if (countdown == 0) {  
        val.html("重新获取");  
        countdown = 60;  
        return false;  
    } else {  
        val.html("已发送(" + countdown + ")");  
        countdown--;  
    }  
    setTimeout(function() {  
        settime(val);  
    },1000);
}

// //点击按钮发送验证码
function seedcode(obj,tel) {
    //获取手机号码
    var code=$(tel).val();
    $.ajax({
        type:"post",
        url:"/Home/Member/sendcode",
        data:{tel:code,status:'login'},
        dataType:"json",
        success: function(msg){
                if (msg.status==1) {
                    layer.tips('发送成功，注意查收！',tel, {
                          tips: [1, '#3595CC'],
                          time: 2000
                        });
                    settime($(obj));
                }else{
                    layer.tips(msg.error, tel, {
                          tips: [1, '#3595CC'],
                          time: 2000
                        });
                }
        }
    });
}
    
function ajaxform(formdata,urls) {
    //jqureyForm插件提交表单
    $(formdata).submit(function(){
        $(this).ajaxSubmit({
            type:"post",
            url:urls,
            dataType:"json",
            success:function(msg){
                if(msg.status==1){
                    //成功跳转首页
                    var index = layer.load(0, {shade: false}); //0代表加载的风格，支持0-2
                    setTimeout(function(){
                        location.href="/";
                    },1500);
                }else if(msg.status==2){
                    //成功跳转评论页面
                    var index = layer.load(0, {shade: false}); //0代表加载的风格，支持0-2
                    setTimeout(function(){
                        location.href=msg.url;
                    },1500);
                    
                }else{
                    
                    $('#embed-captcha').replaceWith('<div id="embed-captcha"></div>');//元素节点替换  
                    getcode();

                    //错误提示
                    layer.alert('<h4 style="color:red;">'+msg.error+'</h4>', {
                          skin: 'layui-layer-molv', //样式类名
                          closeBtn: 1
                    });
                }
            }       
        });
        
        //阻止表单提交
        return false;
    });

}


//极验验证码
var handlerEmbed = function (captchaObj) {
    $("#embed-submit").click(function (e) {
        var validate = captchaObj.getValidate();
        if (!validate) {
            $("#notice")[0].className = "show";
            setTimeout(function () {
                $("#notice")[0].className = "hide";
            }, 2000);
            e.preventDefault();
        }
    });
    // 将验证码加到id为captcha的元素里，同时会有三个input的值：geetest_challenge, geetest_validate, geetest_seccode
    captchaObj.appendTo("#embed-captcha");
    captchaObj.onReady(function () {
        $("#wait")[0].className = "hide";
    });
    // 更多接口参考：http://www.geetest.com/install/sections/idx-client-sdk.html
};
    
function getcode() {
    $.ajax({
    // 获取id，challenge，success（是否启用failback）
    url: "/Home/Member/getverify/t/" + (new Date()).getTime(), // 加随机数防止缓存
    type: "get",
    dataType: "json",
    success: function (data) {
        //console.log(data);
        // 使用initGeetest接口
        // 参数1：配置参数
        // 参数2：回调，回调的第一个参数验证码对象，之后可以使用它做appendTo之类的事件
        initGeetest({
            gt: data.gt,
            challenge: data.challenge,
            new_captcha: data.new_captcha,
            product: "embed", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
            offline: !data.success // 表示用户后台检测极验服务器是否宕机，一般不需要关注
            // 更多配置参数请参见：http://www.geetest.com/install/sections/idx-client-sdk.html#config
        }, handlerEmbed);
    }
});
}
