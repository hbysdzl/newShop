<?php
//登录后台操作控制器
namespace Admin\Controller;
use Think\Controller;
use Think\Verify;
use Org\Util\Geetestlib;
class LoginController extends Controller{
    public function login(){
        if(IS_POST){
            $model=D('Login');
           //极验验证码验证
           if(!$this->getCode()){              
               $this->ajaxReturn(array('status'=>0,'error'=>'请先完成验证码验证！'));
               die();
           }          
            //采用动态的验证方式！(调用模型类的validate方法)
            if($model->validate($model->_login_validate)->create()){
                if(true===$model->login()){
                    $this->ajaxReturn(array('status'=>1));
                    die();
                    //$this->redirect('Index/index');
                }
            }
            $this->ajaxReturn(array('status'=>0,'error'=>$model->getError()));
        }
        
        $this->display();
    }
    //生成验证码图片
  /*  public function chkcode(){
        //设置验证码生成效果
        $config =	array(
            'useImgBg'  =>  true,           // 使用背景图片
            'fontSize'  =>  25,              // 验证码字体大小(px)
            'useCurve'  =>  true,            // 是否画混淆曲线
            'useNoise'  =>  true,            // 是否添加杂点
            'imageH'    =>  45,               // 验证码图片高度
            'imageW'    =>  200,               // 验证码图片宽度
            'length'    =>  4,               // 验证码位数
        );
        $Verify =new Verify($config);
        $Verify->entry();

    }*/
    
    /*
     * 极验验证码Api1请求接口
     * */
    public function getverify(){
       
        $GtSdk = new Geetestlib(C('ID'), C('KEY'));
        $data = array(
            "user_id" => "test", # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
        );
        
        $status = $GtSdk->pre_process($data, 1);
        $_SESSION['gtserver'] = $status;
        $_SESSION['user_id'] = $data['user_id'];
        echo $GtSdk->get_response_str();
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