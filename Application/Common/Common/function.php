<?php
use Think\Upload;
use Behavior\ShowPageTraceBehavior;

/**
 * 调用接口（通过http_curl）进行数据操作
 * @param array  $data    请求携带的参数
 * @param string  $method    请求方式：get/post
 * @return array
 * @author 段宗来  <xcoolcc@gmail.com>
 */
function get_api_data($data=array(),$method='get'){
    
    //根据实际情况生成url地址，如果未指定具体的接口的控制器和方法则使用与目前同名的控制器和方法
    if(!$data['c']){
        $data['c']=CONTROLLER_NAME;
    }
    if(!$data['a']){
        $data['a']=ACTION_NAME;
    }
    //如果当前接口不是自定义接口则指定具体的接口地址
    if($data['url']){
        $url=$data['url'];
    }else{
        $url="http://api.jaojoozn.com/index.php?m=home&c=".$data['c']."&a=".$data['a'];
    }
    //将控制器方法参数删除
    unset($data['c']);
    unset($data['a']);
    unset($data['url']);
    //开始请求
    $res=http_curl($url,$data,$method='get');
    return $res;
} 

/*
 * 自定义Api接口请求函数
 * @$url:请求的地址
 * @$data:请求所携带的参数信息
 * @$method:请求方式
 * @return :result(array);
 * */
 
function http_curl($url,$data=array(),$method='get'){
    
    if(!function_exists('curl_init')){
        //如果该函数不存在则没有开启php扩展
        echo "php扩展未开启";
        die();
    }
    //1.打开会话
    $ch=curl_init();
    //2.设置参数信息
    if($method=='post'){
        //设置post请求
        //curl_setopt()函数将为一个CURL会话设置选项。CURLOPT_POST参数是你想要的设置，true是这个选项给定的值
        curl_setopt($ch,CURLOPT_POST,true);
        //设置请求的参数
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    }else{
        //get请求将参数直接放在url地址后面
        $url.='&'.http_build_query($data);//将数组参数转换为请求字符串
    }
    //设置请求地址
    curl_setopt($ch,CURLOPT_URL,$url);
    //设置结果不输出
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    
    //3.执行请求
    $res=curl_exec($ch);
    //4.关闭会话
    curl_close($ch);
    
    //返回数组结果集
    return json_decode($res,true);
}

//发送邮件
function sendMail($to, $title, $content){
    require_once ('./Api/PHPMailer_v5.1/class.phpmailer.php');
    $mail = new PHPMailer();
    // 设置为要发邮件
    $mail->IsSMTP();
    // 是否允许发送HTML代码做为邮件的内容
    $mail->IsHTML(TRUE);
    // 是否需要身份验证
    $mail->SMTPAuth=TRUE;
    $mail->CharSet='UTF-8';
    /*  邮件服务器上的账号是什么 */
    $mail->From='hbdzlaa@163.com';
    $mail->FromName='我的商城';         //发件人的昵称
    $mail->Host='smtp.163.com';
    $mail->Username='hbdzlaa@163.com'; //邮箱名
    $mail->Password='duanzonglai123';  //第三方客户端授权码
    // 发邮件端口号默认25
    $mail->Port = 25;
    // 收件人
    $mail->AddAddress($to);
    // 邮件标题
    $mail->Subject=$title;
    // 邮件内容
    $mail->Body=$content;
    return($mail->Send());
}


//选择性的实体转义，防止xxs恶意攻击
function removeXSS($val){
    static $obj = null;
    if($obj === null)
    {
        require('./HTMLPurifier/HTMLPurifier.includes.php');
        $config = HTMLPurifier_Config::createDefault();
        // 保留a标签上的target属性
        $config->set('HTML.TargetBlank', TRUE);
        $obj = new HTMLPurifier($config);
    }
    return $obj->purify($val);
}
//图片上传调用
function UploadOne($imgname,$dirname,$thumb=array()){
    //上传LOGO
    $ret = array();
    $tmpfiel = $_FILES[$imgname]['error'];
    
    getimgError($tmpfiel);
    
    //设置配置信息
    $config = array(
        'exts'          =>  array('jpg','png','gif'), //允许上传的文件后缀
        'autoSub'       =>  true, //自动子目录保存文件
        'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath'      =>     './Public/Upload/', //保存根路径
        'savePath'      =>  $dirname, //保存路径
        'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'       =>  '', //文件保存后缀，空则使用原后缀
    );
    $upload=new Upload($config);
    $info=$upload->uploadOne($_FILES[$imgname]);
    if(!$info){
        //上传失败则获取错误信息
        $ret = array('img'=>0,'error' => $upload->getError());
    }else {
        $ret['img']=1;
        $ret['images'][0]=$info['savepath'].$info['savename'];//原图地址
         //判断是否生成缩略图生成缩略图
        if($thumb){
            $images=new \Think\Image();//实例化
            foreach ($thumb as $k=>$v){
                $images->open($upload->rootPath.$ret['images'][0]);//打开图片地址
                $ret['images'][$k+1]=$info['savepath'].'sm_'.$k.'_'.$info['savename'];//拼凑缩略图文件名
                $images->thumb($v[0], $v[1])->save($upload->rootPath.$ret['images'][$k+1]);//生成并保存

            }
        }
    }


    return $ret;
}
    
 //文件上传获取错误信息
 function getimgError($fileCode) {
   
    if($fileCode != '0') {
        switch ($fileCode) {
            case '1':
                $error = '文件过大，上传失败';
                break;
            case '2':
                $error = '文件过大，上传失败';
                break;
            case '3':
                $error = '文件上传不完整，上传失败';
                break;
            case '4':
                $error ='文件不能为空';
                break;
            case '6':
                $error = '上传目录不存在';
                break;
            case '7':
                $error = '磁盘目录不存在';
                break;
        }

        return array('img'=>0,'error'=>$error); 
        exit;
    }
 
 }

//模板中显示图片
function ShowImage($url,$width='',$height=''){
    $url='/Public/Upload/'.$url;
    if ($width){
        $width="width='$width'";
    }
    if ($height){
        $height="height='$height'";
    }
    echo "<img src='$url' $width $height>";
}
//删除图片
function deleteImage($images){
    //取出图片所在目录
    $rp=C('IMG_ROOTPATH');
    foreach ($images as $v){
        @unlink($rp.$v);
    }
}

//判断批量上传的文件表单中是否存在文件
function hasImage($imgName){
    foreach ($_FILES[$imgName]['error'] as $v){
        if($v==0)
            return true;
    }
    return false;
}