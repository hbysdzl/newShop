<?php
/**
 * @Author: anchen
 * @Date:   2018-07-10 21:17:40
 * @Last Modified by:   anchen
 * @Last Modified time: 2018-07-10 21:40:53
 * 前台公共函数
 */

/**
  * 发送模板短信
  * @param to 手机号码集合,用英文逗号分开
  * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
  * @param $tempId 模板Id,测试应用和未上线应用使用测试模板请填写1，正式应用上线后填写已申请审核通过的模板ID
  */
function sendTemp($to,$datas,$tempId){

     include("./Api/seed/CCPRestSmsSDK.php");//载入核心类文件

        //主帐号,对应开官网发者主账号下的 ACCOUNT SID
    $accountSid= '8aaf0708646d63ec01647f6f85c50b78';

    //主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
    $accountToken= 'b9aa48508cce4995905065cbe3216a0e';

    //应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
    //在开发调试的时候，可以使用官网自动为您分配的测试Demo的APP ID
    $appId='8a216da8646e949a01647f7298a30784';

    //请求地址
    //沙盒环境（用于应用开发调试）：sandboxapp.cloopen.com
    //生产环境（用户应用上线使用）：app.cloopen.com
    $serverIP='sandboxapp.cloopen.com';


    //请求端口，生产环境和沙盒环境一致
    $serverPort='8883';

    //REST版本号，在官网文档REST介绍中获得。
    $softVersion='2013-12-26';


     $rest = new \REST($serverIP,$serverPort,$softVersion);
     $rest->setAccount($accountSid,$accountToken);
     $rest->setAppId($appId);

     // 发送模板短信
     $result = $rest->sendTemplateSMS($to,$datas,$tempId);

     if($result == NULL ) {
         return false;
     }
     if($result->statusCode!=0) {
         return false;

     }

     return true;

}


//生成随机小数

function randomFloat($min = 0, $max = 1) {
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

