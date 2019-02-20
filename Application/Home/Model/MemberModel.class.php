<?php
namespace Home\Model;
use Think\Model;
use Think\Verify;


class MemberModel extends Model{


    //注册允许接收的字段
    protected  $insertFields=array('nikname','mustclick','email','password','face','cpassword','code','tel','telcode','openid');


    //注册时表单的验证规则
    protected  $_validate = array(
        array('mustclick','require',"请阅读并同意注册协议", 1),
        array('nikname','require',"请填写昵称", 1),
        array('email', 'require', '请输入邮箱', 1),
		array('email', '', '该email已注册', 1, 'unique'),
        //array('email', 'email,', 'email格式不正确', 1),
        array('password', 'require', '密码不能为空', 1),
        array('password', '6,20', '密码必须是6-20位字符!', 1, 'length'),
        array('cpassword', 'password', '两次密码输入不一致', 1, 'confirm'),
        array('code', 'require', '验证码不能为空', 1),
        array('code', 'chk_code', '验证码不正确', 1, 'callback',1),
    );

    //用户登录表单验证规则
        public  $_login_validate = array(
            array('email', 'require', '请输入邮箱', 1),
            //array('email', 'email,', 'email格式不正确', 1),
            array('password', 'require', '密码不能为空', 1),
            array('password', '6,20', '密码必须是6-20位字符!', 1, 'length'),
    );

    //调用验证码类进行验证
    protected function chk_code($code){
        $verify=new Verify();
        return $verify->check($code);
    }
    //在会员记录插入到数据库之前
    protected function _before_insert(&$data, $options){
        //获取客户端ip
        $data['registerip'] = get_client_ip();
        $data['registeraddr'] = $this->getregisterAddr($data['registerip']);
        $data['addtime']=time();//注册时间

        if (empty($data['openid'])) {
            
            //正常注册
            $data['email_code']=md5(uniqid());//生成emali用的验证码
            $data['password']=md5($data['password'].C('MD5_KEY'));//对用户的密码进行加密
            $data['telcode']=I('post.telcode');
            //验证短信验证码
            if(!$data['tel']){
                $this->error="请输入短信验证码";
                return false;
                }
            $ga=session('telcode');
            if(!$ga){
                $this->error="请发送短信验证码";
                return false;
            }
            if($ga['time']+$ga['limit']<time()){
                $this->error="验证码已过期，请重新验证!";
                return false;
            }
            if($ga['code']!=$data['telcode']){
                echo $ga['code'];
                echo $data['telcode'];
                die();
                $this->error="短信验证码错误！";
                return false;
            }
            unset($data['telcode']);
        }
        
    }

    //会员注册之后
    protected function _after_insert(&$data, $options){
        //把生成的验证码发送到用户邮箱中完成验证
        $content="欢迎成为本站会员,请点击已下连接完成验证<br/>
                <a href='http://www.myshop.com/Home/Member/emailchk/code/{$data['email_code']}'>点击完成验证</a>";
        sendMail($data['email'],'注册会员验证', $content);
    }

    //会员登录
    public function login(){

        $email=$this->email;
        $password=$this->password;

        $user=$this->where(array('email'=>array('eq',$email),'status'=>array('eq','1')))->find();
        // //调用自定义接口登录
        // $data=array(
        //     'c'=>'User',
        //     'a'=>'login',
        //     'username'=>$email,
        //     'password'=>$password
        // );
        // $res=get_api_data($data);
        // if($res['status']==0){
        //     $this->error=$res['error'];
        //     return false;
        // }
        // //保存用户信息
        // $user=$res['result'];
       
        if ($user){//判断账号是否存在

            if (empty($user['email_code'])){//判断是否通过email验证
                    
                if($user['password']==md5($password.C('MD5_KEY'))){
                    //登录成功
                    return  $this->savaUser($user);
                }else {
                    
                    $this->error='密码错误';
                    return false;
                }
            }else {
                $this->error="请登录邮箱进行email验证";
            }
        }else {
            $this->error="账号不存在";
        }
    }


    //短信验证快捷登录
    public function noteLogin($data) {

        $code = session('telcode');

        if (empty($data['sms_code']) || empty($code['code']) ) {
            $this->error = '请发送短信验证码';
            return false;   
        }

        if ($data['sms_code'] != $code['code']) {
            $this->error = '验证码错误';
            return false;
        }

        if ($code['time'] + $code['limit'] < time()) {
            $this->error = '验证码已过有效期，请重新发送';
            return false;
        }

        $memeber = $this->where('tel='.$data['tel'])->find();
        if($memeber['email_code'] != '') {
            $this->error = '请登录您的邮箱完成验证！';
            return false;
        }

        // 成功
       return  $this->savaUser($memeber);
    }

    //QQ登陆
    public function qqLogin($openid,$userInfo) {

        $data = $this->where("openid='$openid'")->find();

        if(!$data){
            //将信息写入数据库中
            // 生成盐密码
            $salt = rand(100000,999999);
            $pwd  = md5(md5('123456').$salt);
           
            $data = array(
                    'nikname'        =>  $userInfo['nickname'],
                    'sex'            =>  $userInfo['gender'],
                    'birthday'       =>  $userInfo['year'],
                    'password'       =>  $pwd,
                    'face'           =>  $userInfo['figureurl_1'],
                    'openid'         =>  $openid
                );
            
            $id = $this->add($data);
            $data = $this->find($id);
        }

        //保存用户信息
       return $this->savaUser($data);
    }

    
    //将用户信息存入session
    private function savaUser($user) {
        session('id',$user['id']);
        session('email',$user['email']);
        session('nikname',$user['nikname']);
        session('jyz',$user['jyz']);

        //获取当前会员的级别和折扣率
        $ml = M('NumberLevel')->field('level_id,rate')->where("{$user['jyz']} between bottom_num and top_num")->find();

        session('level_id',$ml['level_id']); //当前会员的级别
        session('rate',$ml['rate']/100); //当前会员的折扣率

         //将购物中的COOKIE数据转移到数据库
        $cartModel=D('Cart');
        $cartModel->MoveDataDb();

        /****--------记录登录时间及ip----------****/
        //取出上次登录的信息
        cookie('members',$user);

        //记录本次登录时间及ip
        $user['lasttime'] = date('Y-m-d H:i:s',time());
        $user['lastip'] = get_client_ip();
        $this->save($user);

        return true;
    } 

    // //根据ip获取地区信息
    private function getregisterAddr($ip) {

        $url = "http://apis.juhe.cn/ip/ip2addr";
        $params = array(
                'ip'  => $ip,
                'key' => '3dbb726d00c067ca8d81e708cabba2cc',
            );
        
        $paramstring = http_build_query($params);  //将数组拼接为url字符串
        $content = $this->juhecurl($url,$paramstring);
        $result = json_decode($content,true);
        $addr = '';
        if($result){
            if($result['error_code'] == '0'){
                $addr = $result['result']['area'].'-'.$result['result']['location'];
                
            }else{
                $addr = $result['error_code'].'/'.$result['reason'];
            }
        }

        return $addr;
    }

    
    /**
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    private function juhecurl($url,$params=false,$ispost=0){
        $httpInfo = array();
        $ch = curl_init();
     
        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_USERAGENT , 'JuheData' );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
        curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if( $ispost )
        {
            curl_setopt( $ch , CURLOPT_POST , true );
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
            curl_setopt( $ch , CURLOPT_URL , $url );
        }
        else
        {
            if($params){
                curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
            }else{
                curl_setopt( $ch , CURLOPT_URL , $url);
            }
        }
        $response = curl_exec( $ch );
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );
        return $response;
    }

}