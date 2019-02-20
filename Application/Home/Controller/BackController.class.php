<?php

 namespace Home\Controller;
 use Think\Controller;
 use Org\Util\Geetestlib;
 
 class BackController extends Controller{
 
     //设置页面信息
     protected  function setPageInfo($keywords,$description,$title,$showNav=0,$css=array(),$js=array()){
         $this->assign('page_keywords',$keywords);//关键字
         $this->assign('page_description',$description);//网站描述
         $this->assign('page_title',$title);//网站标题
         $this->assign('show_nav',$showNav);//导航菜单闭合
         $this->assign('page_css',$css); 
         $this->assign('page_js',$js);
     }
     
     
     /*
      * 极验验证码Api2验证
      * */
     public function getCode(){
         $GtSdk = new Geetestlib(C('ID'), C('KEY'));
         $data = array(
             "user_id" => $_SESSION['user_id'], # 网站用户id
             "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
             "ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
         );
     
     
         if ($_SESSION['gtserver'] == 1) {   //服务器正常
             $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
             if ($result) {
                 //echo '{"status":"success"}';
                 return true;
             } else{
                 // echo '{"status":"fail"}';
                 return false;
             }
         }else{  //服务器宕机,走failback模式
             if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
                 // echo '{"status":"success"}';
                 return true;
             }else{
                 //echo '{"status":"fail"}';
                 return false;
             }
         }
     }
 }
