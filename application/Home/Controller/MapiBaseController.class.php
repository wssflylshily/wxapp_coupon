<?php
/**
 * Created by PhpStorm.
 * User: sunfan
 * Date: 2017/2/4
 * Time: 9:55
 */

namespace Home\Controller;
use Think\Controller;


class MapiBaseController extends Controller {
    //返回
    protected $data;

    protected function _initialize(){
        header("Access-Control-Allow-Origin: *");
        $data = $_REQUEST;
        $this->writeLog("收到用户:".json_encode($data));
        if(is_null($data)){
            $this->ApiReturn(10001,'数据格式错误');
        }
        $this->data=$data;
    }
    //api返回
    protected function ApiReturn($code,$msg,$data=null) {
        $arr['code']=$code;
        $arr['msg']=$msg;
        if(empty($data)&&!is_array($data)){
            $data=array();
        }
        //$data = str_replace(null,"",$data);
        $arr['data']=$data;
        $arr['time']=time();
        $a = $this->data;
        // echo json_encode($arr);
        // die();
        //----------------------@author baiwei 2017/6/23 10:39----------------------------
        header('Content-Type:application/json; charset=utf-8'); //postman自动识别类型
        //---------------------------------------------------------------------------------
        echo str_replace('\/',"/",json_encode($arr));

        $this->writeLog("发出:".json_encode($arr));
        die();
    }

    protected function JsReturn($code,$msg,$data=null,$num=-1){
        $arr['code']=$code;
        $arr['msg']=$msg;
        $arr['num']=$num;
        if(empty($data)&&!is_array($data)){
            $data=array();
        }
        //$data = str_replace(null,"",$data);
        $arr['data']=$data;
        $arr['time']=time();
        $a = $this->data;

        $callback = isset($_GET['callback']) ? trim($_GET['callback']) : ''; //jsonp回调参数，必需
        echo $callback.'('.str_replace('\/',"/",json_encode($data,JSON_UNESCAPED_UNICODE)).')';

        $this->writeLog("发出:".json_encode($arr));
        die();
    }
    //获得TOKEN
    protected function get_token($phone,$password,$id){
        $token=md5($phone.md5($password).time().$this->createNoncestr());
        S($token,$id,86400);
        // S('name',null);
//        S($token,$id,2592000);
        return $token;
    }
    function inputFilter($content)
    {
        if(is_string($content) ) {
            return $this->clean($content);
        }
        elseif(is_array($content)){
            foreach ( $content as $key => $val ) {
                $content[$key] = inputFilter($val);
            }
            return $content;
        }
        elseif(is_object($content)) {
            $vars = get_object_vars($content);
            foreach($vars as $key=>$val) {
                $content->$key = inputFilter($val);
            }
            return $content;
        }
        else{
            return $content;
        }
    }
    function clean($v) {
        //判断magic_quotes_gpc是否为打开
        if (!get_magic_quotes_gpc()) {
            //进行magic_quotes_gpc没有打开的情况对提交数据的过滤
            $v = addslashes($v);
        }
        $v=str_replace("update", "", $v);
        //把'_'过滤掉
        $v = str_replace("_", "\_", $v);
        //把'%'过滤掉
        $v = str_replace("%", "\%", $v);
        //把'*'过滤掉
        $v = str_replace("*", "\*", $v);
        //回车转换
        $v = nl2br($v);
        //html标记转换
        $v = htmlspecialchars($v);
        return $v;
    }

    function writeLog($mes)
    {
        $myfile = file_put_contents("log.txt", date('Y-m-d H:i:s')." ".$mes."\n", FILE_APPEND);
        //fwrite($myfile, date('Y-m-d H:i:s')." ".$mes);
        fclose($myfile);
    }
    function createNoncestr( $length = 32 )
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
    /**
     * 检查手机号码格式
     * @param $mobile 手机号码
     */
    function check_mobile($mobile){
        if(preg_match('/1[34578]\d{9}$/',$mobile))
            return true;
        return false;
    }

    /**
     * 检查邮箱地址格式
     * @param $email 邮箱地址
     */
    function check_email($email){
        if(filter_var($email,FILTER_VALIDATE_EMAIL))
            return true;
        return false;
    }

    protected function get_department_name($department_id){
        $department_name = M('Department')->where(array('id'=>$department_id))->field('d_name')->find();
        return $department_name['d_name'];
    }
}