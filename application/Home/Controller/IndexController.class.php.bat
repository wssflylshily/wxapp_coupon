<?php
namespace Home\Controller;


class IndexController extends MapiBaseController {
	
	public function banner()
    {
        $data = $this->data;
        $db = M('Banner');
        $rs = $db->select();
        if (empty($rs))$this->ApiReturn(0, '成功');
        $this->ApiReturn(1, '成功', $rs);
    }

    public function category()
    {
        $data = $this->data;

        $rs = M('category')->where(['status'=>1])->field('id, cname, icon_url')->select();
        if (empty($rs))$this->ApiReturn(0, '成功');
        $this->ApiReturn(1, '成功', $rs);
    }

    /**
     * 优惠券列表
     */
    public function coupon()
    {
        $data = $this->data;
        $time = time();
//        $id = S($data['token']);
        $id = 20;
        $userinfo = M('Member')->where(['id'=>$id])->find();
        $cid = $data['cid']??$this->ApiReturn(-1, '分类id不能为空');
        $where=['review'=>2, 'status'=>1, 's_time'=>['lt', $time], 'e_time'=>['gt', $time], 'num'=>['gt', 0]];
        if(!empty($data['keyword'])){
            $where['keyword'] = array("like","%".$data['keyword']."%");
        }

        if(!empty($data['cid'])){
            $where['yhq_coupon.category_id'] = $cid;
        }

        $page = $data['page']??1;
        $rs = M('Coupon')->where($where)->field('id, url, title, view, longitude, latitude')->page($page, 20)->select();
        if (empty($rs))$this->ApiReturn(0, '成功');

        $return=[];
        $radius = M('Config')->where(['id'=>1])->getField('radius');
        foreach ($rs as $k=>$item)
        {
            $distance = getDistance($item['longitude'], $item['latitude'], $userinfo['longitude'], $userinfo['latitude']);
            if ($distance>$radius){
                continue;
            }
            $return[$k]['cid'] = $item['id'];
            $return[$k]['img'] = $item['url'];
            $return[$k]['title'] = $item['title'];
            $return[$k]['view'] = $item['view'];
            $return[$k]['distance'] = round($distance, 2);
        }
        $this->ApiReturn(1, '成功', $return);
    }

    /**
     * 优惠券详情
     */
    public function couponDetail()
    {
        $data = $this->data;
//        $id = S($data['token']);
        $id = 20;
        $cid = $data['cid']??$this->ApiReturn(-1, '优惠券id不能为空');
        $rs = M('Coupon')->where(['id'=>$cid])->field('title, money, unit, desc, s_time, e_time, shop_name, address, mobile, longitude, latitude')->find();
        $rs['getNum'] = M('CouponLog')->where(['coupon_id'=>$cid])->count();

        //添加一条查看记录
        if (!M('CouponLog')->where(['coupon_id'=>$cid, 'user_id'=>$id, 'status'=>3])->find()){
            M('CouponLog')->add(['coupon_id'=>$cid, 'user_id'=>$id, 'cl_time'=>time(), 'status'=>3]);
        }

        $this->ApiReturn(1, '成功', $rs);
    }

    /**
     * 领取优惠券
     */
    public function receiveCoupon()
    {
        $data = $this->data;
//        $id = S($data['token']);
        $id = 20;
        $cid = $data['cid']??$this->ApiReturn(-1, '优惠券id不能为空');
        $info = M('Coupon')->where(['id'=>$cid])->find();
        $time = time();
        if (M('CouponLog')->where(['coupon_id'=>$cid, 'user_id'=>$id, 'status'=>1])->find())$this->ApiReturn(-1, '您已经领取了该优惠券');
        if ($info['s_time']>$time || $info['e_time']<$time)$this->ApiReturn(-1, '优惠券已经不能领取了');
        if ($info['num']<=0)$this->ApiReturn(-1, '优惠券已经被领完了');
        M('CouponLog')->add(['coupon_id'=>$cid, 'user_id'=>$id, 'cl_time'=>time(), 'status'=>1]);
        M('Coupon')->where(['id'=>$cid])->setDec('num',1);
        $this->ApiReturn(1, '领取成功');
    }
}