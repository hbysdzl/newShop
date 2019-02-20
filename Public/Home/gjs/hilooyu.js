;$(function(){
    //var script = document.createElement('script');
    //script.type = "text/javascript";
    //script.src = "http://looyu.happigo.com/j.jsp?c=2&f=160";
    //script.src = "http://webcall.happigo.com:8080/webchat/chat.html";
    //document.body.appendChild(script);
});

var hilooyu = {
    setting : function (obj) {
        var getTimestamp = function () {
            return Math.floor(new Date().getTime()/1000);
        };
        var getRandom = function () {
            return Math.floor(Math.random() * 10000);
        };
        var getSign = function (param) {
            var data = "";
            if ("" != param.c) {
                data += "c="+param.c;
            }
            if ("" != param.g) {
                data += "&jId="+param.g;
            }
            if ("" != param.bp) {
                data += "&cust_no="+param.bp;
            }
            if ("" != param.pids) {
                data += "&item_code="+param.pids;
            }
            if ("" != param.store_id) {
                data += "&bbcno="+param.store_id;
            }
            data += "&nonstr="+getRandom()+"&timestamp="+getTimestamp();
            data += "&sign="+hex_md5(data+"&acc_key=3sp6GftoplQH71fj");

            return data;
        }
        var login_url = '/login/';
        var param       = {};
        param.c         = Number(obj.c);
        param.g         = Number(obj.g);
        param.is_login  = obj.is_login || 1;
        param.bp        = obj.bp || '0';
        param.pid       = obj.pid || '';
        param.ly        = obj.ly || '';
        param.img       = obj.img || '';
        param.pids      = obj.pids || '';
        // param.timestamp = obj.timestamp;
        // param.nonce     = obj.nonce;
        // param.sign      = obj.sign;
        param.ulr       = obj.ulr || login_url;
        param.store_id       = obj.store_id || '';

        var fn = {};
        fn.main = function () {
            if (!param.c || !param.g) {
                return false;
            }

            if(1 == param.is_login && (!param.bp || '0' == param.bp)){
                showDialog("请您先登录！", '', '', function(){
                    if (param.ulr) {
                        window.location.href = param.ulr;
                    }
                }, 1);
                return false;
            }
          
            var sChatUrl = "http://webcall.happigo.com:8080/webchat/chat.html?"+getSign(param);
            //var sChatUrl = "http://looyu.happigo.com/live/p.do?c="+param.c+"&g="+param.g+"&hly="+param.ly+"&huid="+param.bp+"&partner="+param.bp+"&hpid="+param.pid+"&pid="+param.pids+"&img="+param.img+'&timestamp='+param.timestamp+'&nonce='+param.nonce+'&sign='+param.sign;
            try{
                 var oW=window.open(sChatUrl, 'looyu', 'toolbar=no,location=no,directories=no,scrollbars=no,menubar=no,width=800,height=600,resizable=yes,left=0,top=0,status=no');
                 if (typeof(oW)!="undefined") oW.focus;
            }catch(e){

            }

            return false;
        }
        return fn;
    }
}
