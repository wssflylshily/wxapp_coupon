<?php
/**
 * Created by PhpStorm.
 * User: sunfan
 * Date: 2017/7/25
 * Time: 14:19
 */

namespace Home\Controller;


class AuthController extends MapiBaseController
{
    public function WeLogin()
    {
        $data = $this->data;
        $code = $data['code'];
//        $code = "003uy0bF1OvH840JbN7F1etYaF1uy0bO";
        $state = $data['state'];

        $appid = C('WxAppPayConf_pub.APPID');    //我配置在config文件里了
        $secret = C('WxAppPayConf_pub.APPSECRET');
//        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code";
        $token_url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";

        $token_res = file_get_contents($token_url);
        $token_res = json_decode($token_res,true);

        if($token_res['errcode']){
            file_put_contents('/Public/wei_log.logs',json_encode($token_res,JSON_UNESCAPED_UNICODE),FILE_APPEND);
            $this->ApiReturn(-1,'获取token失败');
        }

        $openid=$token_res['openid'];


        $this->ApiReturn(1,"获取成功",$openid);
    }

    public function Login()
    {
        $user_res = $this->data;
        $openid = $user_res['openid']?$user_res['openid']:$this->ApiReturn(-1, 'openid不能为空');
        $wuser = M('Member');
        $same = $wuser->where(['openid'=>$openid])->find();
        if(!$same){
            $data1['openid'] = $openid;
            $data1['nickname'] = $user_res['nickname']?$user_res['nickname']:$this->ApiReturn(-1, '昵称不能为空');
            $data1['img'] = $user_res['headimgurl']?$user_res['headimgurl']:$this->ApiReturn(-1, '头像不能为空');
            $data1['longitude'] = $user_res['longitude']?$user_res['longitude']:$this->ApiReturn(-1, '经度不能为空');
            $data1['latitude'] = $user_res['latitude']?$user_res['latitude']:$this->ApiReturn(-1, '纬度不能为空');
            $data1['create_time'] = time();
//            $data1['sex'] = $user_res['sex'];
            $wuser->add($data1);

            $wres = $wuser->where(['openid'=>$openid])->find();


        }else{
            if (empty($same['headimg'])){
                $data2['headimg'] = $user_res['headimgurl'];
                $data2['nickname'] = $user_res['nickname']?$user_res['nickname']:$this->ApiReturn(-1, '昵称不能为空');
                $data2['img'] = $user_res['headimgurl']?$user_res['headimgurl']:$this->ApiReturn(-1, '头像不能为空');
                $data2['longitude'] = $user_res['longitude']?$user_res['longitude']:$this->ApiReturn(-1, '经度不能为空');
                $data2['latitude'] = $user_res['latitude']?$user_res['latitude']:$this->ApiReturn(-1, '纬度不能为空');
                $wuser->where(['openid'=>$openid])->save($data2);
            }

            $wres = $wuser->where(['openid'=>$openid])->find();

        }

        $arr = $this->userInfo($wres['id']);
        $token = $this->get_token($wres['openid'],$wres['nickname'],$wres['id']);
        $arr['token']=$token;
        $this->ApiReturn(1,"登陆成功",$arr);
    }

    //用户信息
    protected function userInfo($user_id)
    {
        try {
            $db_user=M("Member");
            $id=$user_id;
            $info = $db_user->where('id='.$id)->find();

            $arr=array();
            $arr['uid'] = $info['id'];
            $arr['nickname'] = $info['nickname'];
//            $arr['sex'] = $info['sex']??1;
            $arr['headImg'] = $info['img']?$info['img']:'/public/Home/img/zhanweihead.png';

            return $arr;
        } catch (\Exception $e) {
            $this->ApiReturn(10002,'系统错误');
        }
    }

    /**
     * 生成小程序二维码
     */
    public function wxappQrcode()
    {
        $appid = C('WxAppPayConf_pub.APPID');    //我配置在config文件里了
        $secret = C('WxAppPayConf_pub.APPSECRET');
//        $tokenUrl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxc864c8fdf622a19e&secret=0314bf2d278bd7d7bd4e3e23a473e08d";
        $tokenUrl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
        $getArr=[];
        $tokenArr = json_decode(send_post($tokenUrl, $getArr, 'GET'));
        $access_token = $tokenArr->access_token;

        $post_data = json_encode(['path'=>"pages/index/index"]);
        $url="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token;
        $result=api_notice_increment($url,$post_data);
        echo $result;
    }

}