;
    var Ztool = {
        getTimestamp : function () {
            return timestamp = new Date().getTime();
        },
        getIsNull : function (str) {
            if ("" == str || "undefined" == typeof(str) || undefined == str|| null == str) {
                return "1";
            } else {
                return "0";
            }
        },
        xor_enc : function(str, key) {
          var crytxt = '';
          var k, keylen = key.length;
          for(var i=0; i<str.length; i++) {
            k = i % keylen;
            crytxt += String.fromCharCode(str.charCodeAt(i) ^ key.charCodeAt(k));
          }
          return encodeURIComponent(crytxt);
        }
    };
    var Zajax = {
        parameter : function (obj) {
            var prData           = {};
            prData.version       = "1.0";
            prData.key           = obj.key;
            prData.ajaxToken     = obj.ajaxToken;
            prData.url           = obj.url;            //链接
            prData.type          = obj.type ? obj.type : "GET";           //请求方式
            prData.dataType      = obj.dataType ? obj.dataType : "json";       //参数格式
            prData.jsonpCallback = obj.jsonpCallback;       //JSONP参数
            prData.timeout       = obj.timeout ? obj.timeout : 5000;        //超时时间，毫秒
            prData.fromdata      = obj.fromdata;       //请求数据
            prData.forefun       = obj.beforefun;        //点击时触发函数
            prData.succfun       = obj.succfun;        //成功返回函数
            prData.errorfun      = obj.errorfun;       //失败返回函数

            var ajaxData = {};
            ajaxData.main = function () {
                var ajaxParm = {
                    url     : prData.url,
                    type    : prData.type,
                    dataType : prData.dataType,
                    timeout : prData.timeout,
                    data    : prData.fromdata
                };

                var t = Ztool.getTimestamp();   //时间戳
                ajaxParm.data.v = prData.version;  //版本号
                if ("GET" != prData.type) {
                    ajaxParm.data.t = t;
                }

                if (0 == Ztool.getIsNull(prData.key) && 0 == Ztool.getIsNull(prData.ajaxToken)) {
                    //ajaxParm.data.key = prData.key;                                         //key
                    ajaxParm.data.ajaxToken = Ztool.xor_enc(prData.ajaxToken, prData.key);  //加密参数
                }

                if ("jsonp" == prData.dataType) {
                    ajaxParm.jsonp = prData.jsonpCallback;
                }
                if (0 == Ztool.getIsNull(prData.forefun)) {
                    ajaxParm.beforeSend = prData.forefun;
                }
                if (0 == Ztool.getIsNull(prData.succfun)) {
                    ajaxParm.success = prData.succfun;
                }
                if (0 == Ztool.getIsNull(prData.errorfun)) {
                    ajaxParm.error = prData.errorfun;
                }

                $.ajax(ajaxParm);
            };

            return ajaxData;
        }
    };