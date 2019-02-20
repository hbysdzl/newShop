<?php
namespace Home\Controller;
use Org\Util\Geetestlib;
use Home\Controller\BackController;
use Think\Verify;
class MemberController extends BackController{

    //发送短信验证码方法
    public function sendcode(){
        $tel=I('post.tel');
        $status = I('post.status');
        if (!$tel) {
            $this->ajaxReturn(array('status'=>0,'error'=>'请填写注册的手机号码！'));
        }

        if(!empty($status)){
            //登录
            $where['tel'] = $tel;
            $where['status'] = '1';
            $user = D('Member')->where($where)->find();
            if(!$user){
                $this->ajaxReturn(array('status'=>0,'error'=>'该用户不存在，请先完成注册！'));
            }
        }

        //开始发送短息
        $code=rand(1000,9999);
        $resurl=sendTemp($tel,array($code,'30'),'1');
        if(!$resurl){
            $this->ajaxReturn(array('status'=>0,'error'=>'发送失败，请稍后重试！'));
        }

        //发送成功保存到session中，并指定有效期
        $data=array(
                'code'=>$code,
                'time'=>time(),
                'limit'=>120
            );
        session('telcode',$data);
        $this->ajaxReturn(array('status'=>1));
    }

    public function test(){
       // var_dump(session('telcode'));
       //$res=http_curl('http://www.api.com/index.php?m=home&c=User&a=login',array('username'=>'382709807@qq.com','password'=>'123456'));
       $res=get_api_data(array('c'=>'User','a'=>'login','username'=>'382709807@qq.com','password'=>'123456'),$method='get');
       var_dump($res);
      
    }
    
    //会员注册
    public function regist(){

            if(IS_POST){//提交表单
                /*var_dump(I('post.'));
                die();*/
               $model=D('Member');
               if($model->create(I('post.'),1)){
                   if ($model->add()){
                       $this->ajaxReturn(array('status'=>'1'));
                       die();
                    }
                }
                $this->ajaxReturn(array('status'=>0,'error'=>$model->getError()));
            }

        //设置页面基本信息
        $this->setPageInfo('放心购','放心购','免费注册会员',0,array('login'));
        $this->display();
    }

    //完成会员注册的验证
    public function emailchk(){
        //接收验证码
        $code=I('get.code');
        if ($code){
            $MemberModel=M('member');
            $num=$MemberModel->where(array('email_code'=>array('eq',$code)))->find();
            if ($num){
                //设置这个账号已验证
                $MemberModel->where('id='.$num['id'])->setField('email_code','');
                $this->success('验证成功，现在可以去登录了！',U('login'));
            }
        }

    }

    //会员登录
    public function login(){
        if (IS_POST){
            $model=D('Member');
            //极验验证
           if(!$this->getCode()){
               $this->ajaxReturn(array('status'=>0,'error'=>'请先完成验证码验证'));
               die();
           }
            if ($model->validate($model->_login_validate)->create(I('post.'),9)){
                if ($model->login()){
                   //判断session中是否有跳转地址
                   $returl=session('returnUrl');
                    if($returl){
                        session('returnUrl',null);
                        $this->ajaxReturn(array('status'=>2,'url'=>$returl));
                    }else {
                        $this->ajaxReturn(array('status'=>1));
                    }

                }
            }
            
            $this->ajaxReturn(array('status'=>0,'error'=>$model->getError()));
        }

        //设置页面基本信息
        $this->setPageInfo('会员登录','会员登录','放心购商城-快速登录',0,array('login'));
        $this->display();
    }

    //短信快捷登录
    public function noteLogin() {

        $data = I('post.');
        $model=D('Member');
        $result = $model->noteLogin($data);

        if($result){
            $url = session('returnUrl');
            if($url){
                session('returnUrl',null);
                $this->ajaxReturn(array('status'=>2,'url'=>$url));
            }else{
                $this->ajaxReturn(array('status'=>1));
            }

            exit;
        }

        //获取错误信息
        $this->ajaxReturn(array('status'=>0,'error'=>$model->getError()));
    }

    //QQ登陆请求界面
    public function auth() {
        require_once("./Api/qq/API/qqConnectAPI.php");
        $qc = new \QC();
        $qc->qq_login();
    }

    //QQ登陆
    public function qqLogin() {
        

        require_once("./Api/qq/API/qqConnectAPI.php");
        $qc = new \QC();

        // 获取access_token
        $accessToken = $qc->qq_callback();
        //获取用户openid
        $openid = $qc->get_openid();
        
        // 获取用户信息之前再次实例化 防止网络不好或者修改了qq资料后报错
        $qc = new \QC($accessToken,$openid);
        $userInfo = $qc->get_user_info();

        $model = D('Member');

        $result = $model->qqLogin($openid,$userInfo);
        if($result){
            redirect('/');
        }
    }

    //登录状态
    public function ajaxChkLogin(){
            if (session('id')){
                $arr=array(
                    'ok'=>1,
                    'email'=>session('email')
                );
            }else {
                $arr=array(
                  'ok'=>0
                );
            }
            echo json_encode($arr);
    }

    //登录退出

    public function logout(){
        cookie('members',null);
        session('id',null);
        redirect('/');
    }
    //生成验证码
    public function chkcode(){
        $verfiy=new Verify(
            array(
                'length'=>4,
                'useNoise'=>false,
                'imageH'    =>  45,               
                'imageW'    =>  165, 
                'fontSize'  =>  22,  
            ));
        $verfiy->entry();
    }

    //商品评论时登录
    public function saveAndLogin(){
        //获取ajax从哪里来的，并保存到session中
        session('returnUrl',$_SERVER['HTTP_REFERER']);
    }
    
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
}