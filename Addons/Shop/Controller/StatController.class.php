<?php

namespace Addons\Shop\Controller;
use Think\Model;
use Think\Page;
use Addons\Shop\Controller\BaseController;

class StatController extends BaseController {
	var $model;
	function _initialize() {
//		$this->model = $this->getModel ( 'shop' );
		parent::_initialize ();
	}
	//订单统计
	function order_stat() {
		$map['shop_id'] = $this->shop_id;
		$map['status_code'] = array(array("gt",4),array("lt",8));
		if(I('start') && I('end')){
			$map['cTime'] = array(array('gt',I('start')),array('lt',I('end')));
		}
		if(I('order_number')){
			$map['order_number'] = array('eq',I("order_number"));
		}
		if(I('nickname')){
			$where['nickname'] = array('eq',I('nickname'));
			$uid = M("User")->where($where)->find();
		}
		if(I('truename')){
			$where['truename'] = array('eq',I('truename'));
			$uid = M("User")->where($where)->find();
		}
		//会员名和收货人搜索
		if($uid || I('nickname') ||I('truename')){
			$map['uid'] = array('eq',$uid['uid']);
		}
/*		if(I('nickname') || I('truename')){
			$is_find = M("User")->where($where)->find();
			if($is_find){
				$rs = M("Shop_order")->where($map)->select();
				$total_money = M("Shop_order")->where($map)->sum('total_price');
			}else{
				$rs = null;
				$total_money = 0;
			}
		}else{
			$rs = M("Shop_order")->where($map)->select();
			$total_money = M("Shop_order")->where($map)->sum('total_price');

		}*/
		$rs = M("Shop_order")->where($map)->order('cTime asc')->select();
		//lastsql();
		$total_money = M("Shop_order")->where($map)->sum('total_price');

		foreach ($rs as $key=>$value){
			$where['uid'] = $value['uid'];
			$rs2 = M("User")->field('nickname,truename')->where($where)->find();
			$rs[$key]['user'] = $rs2;
		}
		//lastsql();
		//dump($rs);
		if(empty($total_money)){
			$total_money = 0;
		}
		$this->assign("lists",$rs);
		$this->assign("total_money",$total_money);
		$this->display();
	}
	//商品销售明细
	function order_details(){
		$map['shop_id'] = $this->shop_id;
		$map['status_code'] = array(array("gt",4),array("lt",8));
		if(I('start') && I('end')){
			$map['cTime'] = array(array('gt',I('start')),array('lt',I('end')));
		}
		$rs = M("Shop_order")->where($map)->order('cTime asc')->select();
		if(I("title")){
			foreach ($rs as $key=>$value){
				$data = json_decode($value['goods_datas'],true);
				$id['id'] = $value['id'];
				if(I("title") == $data[0]['title']){
					$rs2[] = M("Shop_order")->where($id)->find();
					//lastsql();
				}
			}
			//dump($rs2);
			$this->assign("lists",$rs2);
		}else{
			$this->assign("lists",$rs);
		}
		//dump($rs);
		$this->display();
	}
	//销售统计
	function sales_statistics(){
		$Model = new \Think\Model();
		$shop_id = $this->shop_id;

		//交易额总数
		$total_money = M("Shop_order")->field('total_price')->where("shop_id= '".$shop_id."' and status_code>4 and status_code<8 ")->sum('total_price');
		$this->assign('total_money',$total_money);
		//最高交易额
		$max_transacton = M("Shop_order")->field('total_price')->where("shop_id= '".$shop_id."' and status_code>4 and status_code<8 ")->max('total_price');
		$this->assign('max_transacton',$max_transacton);
		$max_time_data = M("Shop_order")->field('cTime')->where("total_price= '".$max_transacton."'")->find();
		$max_time = $max_time_data['cTime'];
		$this->assign('max_time',$max_time);
		//统计年
		//select FROM_UNIXTIME(cTime,'%Y') as cTime,sum(total_price) from wp_shop_order where shop_id=2 and status_code between 4 and 8   group by FROM_UNIXTIME(cTime,'%Y');
		$sql = "select FROM_UNIXTIME(cTime,'%Y') as year,sum(total_price) as total_price from wp_shop_order where shop_id= '".$shop_id."' and status_code between 4 and 8   group by FROM_UNIXTIME(cTime,'%Y')";
		$year = $Model->query($sql);
		foreach ($year as $val){
			$every_year[] = $val['year'].'年';
			$year_total_price[] = (double)$val['total_price'];
		}
		$every_year = json_encode($every_year);
		$year_total_price = json_encode($year_total_price);
		$this->assign('every_year',$every_year);
		$this->assign('year_total_price',$year_total_price);

		//统计月
		//select FROM_UNIXTIME(cTime,'%m') as month,sum(total_price) as total_price from wp_shop_order where shop_id=2 and status_code between 4 and 8 and FROM_UNIXTIME(cTime,'%Y')='2016' group by FROM_UNIXTIME(cTime,'%m');
//		$choose_year = 2016;
		$choose_year = I("is_month_year");
		$sql ="select FROM_UNIXTIME(cTime,'%m') as month,sum(total_price) as total_price from wp_shop_order where shop_id='".$shop_id."' and status_code between 4 and 8 and FROM_UNIXTIME(cTime,'%Y')='".$choose_year."' group by FROM_UNIXTIME(cTime,'%m')";
		$month = $Model->query($sql);
		for($a=1;$a<=12;$a++) {
			$every_month[] = $a.'月';
		}
		for($b=1;$b<=12;$b++) {
			$month_total_price[] = 0;
		}
		foreach ($month as $val){
			$month_total_price[$val['month']-1] = (double)$val['total_price'];
		}

//		$every_month = json_encode($every_month);
//		$month_total_price = json_encode($month_total_price);
//		$this->assign('every_month',$every_month);
//		$this->assign('month_total_price',$month_total_price);
//		$this->assign('choose_year',$choose_year.'年');

		//统计日
		//select FROM_UNIXTIME(cTime,'%d') as cTime,sum(total_price) from wp_shop_order where shop_id=2 and status_code between 4 and 8 and cTime between  UNIX_TIMESTAMP(date_add('2016-10-01', interval - day('2016-10-01') + 1 day)) and UNIX_TIMESTAMP(date_add(date_format(last_day('2016-10-01'),'%Y-%m-%d'),interval 86399 second)) group by FROM_UNIXTIME(cTime,'%d');
//		$choose_day_year = 2016 ;
//		$choose_day_month = 10 ;
		$choose_day_year = I("is_day_year") ;
		$choose_day_month = I("is_day_month") ;
		$choose_day =  $choose_day_year.'-'.$choose_day_month.'-'. "01" ;
		$num  =  cal_days_in_month ( CAL_GREGORIAN ,  $choose_day_month ,  $choose_day_year );

		$sql ="select FROM_UNIXTIME(cTime,'%d') as day,sum(total_price) as total_price from wp_shop_order where shop_id='".$shop_id."' and status_code between 4 and 8 and cTime between  UNIX_TIMESTAMP(date_add('".$choose_day."', interval - day('".$choose_day."') + 1 day)) and UNIX_TIMESTAMP(date_add(date_format(last_day('".$choose_day."'	),'%Y-%m-%d'),interval 86399 second)) group by FROM_UNIXTIME(cTime,'%d')";
		$day = $Model->query($sql);
		for($j=1;$j<=$num;$j++) {
			$every_day[] = $j;
		}
		for($i=1;$i<=$num;$i++) {
			$day_total_price[] = 0;
		}
		foreach ($day as $val){
			$day_total_price[$val['day']-1] = (double)$val['total_price'];
		}
		$choose_day = $choose_day_year.'年'.$choose_day_month.'月';
//		$every_day = json_encode($every_day);
//		$day_total_price = json_encode($day_total_price);
//		$this->assign('every_day',$every_day);
//		$this->assign('day_total_price',$day_total_price);
//		$this->assign('choose_day',$choose_day_year.'年'.$choose_day_month.'月');

		if($choose_year){
			$data['every_month'] = $every_month;
			$data['month_total_price'] = $month_total_price;
			$data['choose_year'] = $choose_year;
			$this->ajaxReturn($data);
		}
		if ($choose_day_year && $choose_day_month){
			$data['every_day'] = $every_day;
			$data['day_total_price'] = $day_total_price;
			$data['choose_day'] = $choose_day;
			$this->ajaxReturn($data);
		}

		$this->display();
	}
	//会员消费排行
	function consumer_rankings(){
		$map['shop_id'] = $this->shop_id;
		$map['status_code'] = array(array("gt",4),array("lt",8));
		//(truename = '".I('name_tel')."' or mobile = '".I('name_tel')."')
		$Model = new \Think\Model();
		$count      = M("Shop_order") ->field("uid")->group('uid')->select();// 查询满足要求的总记录数
		$count = count($count);
		//$sql1 = "select count(*) from wp_shop_order where shop_id = '".$map['shop_id']."' and  status_code between 4 and 8 group by uid";
		//$count = $Model->query($sql1);
		$Page       = new \Think\Page($count,6);// 实例化分页类 传入总记录数和每页显示的记录数()
		$Page->setConfig('next','»');
		$Page->setConfig('prev','«');

		$show       = $Page->show();// 分页显示输出
		$firstRow = $Page->firstRow;
		$listRows = $Page->listRows;
		$sql = "select uid,count(order_number) as order_number,sum(total_price) as total_price from wp_shop_order where shop_id = '".$map['shop_id']."' and  status_code between 4 and 8  group by uid order by total_price desc  limit $firstRow , $listRows ";
		$rs = $Model->query($sql);
		foreach ($rs as $key=>$val){
			$where['uid'] = $val['uid'];
			if(I('name_tel')){
				$where['_query'] = 'mobile'. "=". I('name_tel') ."&".'truename'."=".I('name_tel')."&".' _logic' ."=". 'or';
			}
			$user_list[] = M("User")->where($where)->find();
			if($user_list[$key]) {
				$user_list[$key]['order'] = $val;
			}
		}
		//dump($rs);
		//dump($user_list);
		$this->assign('page',$show);// 赋值分页输出
		$this->assign("lists",$user_list);
		$this->display();
	}
	//商品销售排行
	function sale_rankings(){
		$this->display();
	}

}
