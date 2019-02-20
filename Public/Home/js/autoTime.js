// 时钟倒计时
function start(daytime,vis) {
   // var stringTime = daytime;    //"2019-01-3 00:00:00";
    var endtimes=getTs(daytime);
    setInterval(function() {
        var now = getTimeMillin();
        if (now < endtimes) {
            show(now,endtimes,vis);
         } else {
             show(now,now,vis);
         }
    }, 1000);
}

//获取当前时间的时间戳
function getTimeMillin() {
    return new Date().getTime();
}

// ios 时间转时间戳
// 兼容所有浏览器
// ios 使用 new Date("2010-03-15 10:30:00").getTime() 获取时间戳报错
// @time "2010-03-15 10:30:00"
function getTs(time){
    var arr = time.split(/[- :]/),
    _date = new Date(arr[0], arr[1]-1, arr[2], arr[3], arr[4], arr[5]),
    timeStr = Date.parse(_date)
    return timeStr
}



function show(a,b,vis) {
    var date1 = new Date(a); //开始时间
    var date2 = new Date(b); //结束时间
    var date3 = date2.getTime() - date1.getTime() //时间差的毫秒数
    //计算出相差天数
    var days = Math.floor(date3 / (24 * 3600 * 1000))

    //计算出小时数

    var leave1 = date3 % (24 * 3600 * 1000) //计算天数后剩余的毫秒数
    var hours = Math.floor(leave1 / (3600 * 1000))
    //计算相差分钟数
    var leave2 = leave1 % (3600 * 1000) //计算小时数后剩余的毫秒数
    var minutes = Math.floor(leave2 / (60 * 1000))


    //计算相差秒数
    var leave3 = leave2 % (60 * 1000) //计算分钟数后剩余的毫秒数
    var seconds = Math.round(leave3 / 1000)


    // log("倒计时： "+days+"天 "+hours+"小时 "+minutes+" 分钟"+seconds+" 秒");
    // var str = "倒计时： " + days + "天 " + hours + "小时 " + minutes + " 分钟" + seconds + " 秒";
     var spans=document.querySelector('.'+vis);

    //spans[0].innerHTML=days;
	// spans[1].innerHTML=hours;
	// spans[2].innerHTML=minutes;
	// spans[3].innerHTML=seconds;

    var rettime = days+' 天 '+hours+' : '+ minutes+' : '+ seconds;
    spans.innerHTML = rettime;
}
// 倒计时end

