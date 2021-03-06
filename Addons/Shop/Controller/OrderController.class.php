<?php

namespace Addons\Shop\Controller;

use Addons\Shop\Controller\BaseController;
use Think\KdApiSerch;
use Think\Kuaidi;

class OrderController extends BaseController {
	var $model;
	function _initialize() {
		$this->model = $this->getModel ( 'shop_order' );
		parent::_initialize ();
	}
	// 通用插件的列表模型
	public function lists() {
		$delmap['is_gongde']=1;
		$delmap['status_code']=0;
		$delmap['pay_status']=0;
		M('shop_order')->where($delmap)->delete();
		D ( 'Addons://Shop/Order' )->autoSetFinish ();
		
		$this->assign ( 'add_button', false );
		$this->assign ( 'del_button', false );
		$this->assign ( 'check_all', false );
		$map ['token'] = get_token ();
		$map ['shop_id'] = $this->shop_id;
		$search=$_REQUEST['order_number'];
		if ($search) {
		    $this->assign ( 'search', $search );
		    $map1 ['nickname'] = array (
		        'like',
		        '%' . htmlspecialchars ( $search ) . '%'
		    );
		    $nickname_follow_ids = D ( 'Common/User' )->where ( $map1 )->getFields ( 'uid' );
		    $nickname_follow_ids = implode ( ',', $nickname_follow_ids );
		    if (! empty ( $nickname_follow_ids )) {
		        $map ['uid'] = array (
		            'exp',
		            ' in (' . $nickname_follow_ids . ') '
		        );
		    } else {
		        $map ['order_number'] = array (
		          'like',
		          '%' . htmlspecialchars ( $search ) . '%'
		        );
		    }
		    unset ( $_REQUEST ['order_number'] );
		}
		session ( 'common_condition', $map );
		$list_data = $this->_get_model_list ( $this->model );
//		dump($list_data);
//		die;
		// 分类数据
		$map ['is_show'] = 1;
		$list = M ( 'weisite_category' )->where ( $map )->field ( 'id,title' )->select ();
		$cate [0] = '';
		foreach ( $list as $vo ) {
			$cate [$vo ['id']] = $vo ['title'];
		}
		$orderDao = D ( 'Addons://Shop/Order' );
		// dump($list_data ['list_data']);
		foreach ( $list_data ['list_data'] as &$vo ) {
			$param ['id'] = $vo ['id'];
			
			$order = $orderDao->getInfo ( $vo ['id'] );
			// dump($order);
			$vo = array_merge ( $vo, $order );
			$follow = get_followinfo ( $vo ['uid'] );
			//dump(D ( 'Common/User' )->getUserInfo($vo ['uid']));
			//dump($follow);
			$param2 ['uid'] = $follow ['uid'];
			$vo ['uid'] = '<a target="_blank" href="' . addons_url ( 'UserCenter://UserCenter/detail', $param2 ) . '">' . $follow ['nickname'] . '</a>';
			$vo ['cate_id'] = intval ( $vo ['cate_id'] );
			$vo ['cate_id'] = $cate [$vo ['cate_id']];
			
			$goods = json_decode ( $order ['goods_datas'], true );
			foreach ( $goods as $vv ) {
				$vo ['goods'] .= '<img width="50" style="vertical-align:middle;margin:0 10px 0 0" src="' .  $vv ['cover'] . '"/>' . $vv ['title'] . '<br><br>';
			}
			$vo ['goods'] = rtrim ( $vo ['goods'], '<br><br>' );
			
			$vo ['order_number'] = '<a href="' . addons_url ( 'Shop://Order/detail', $param ) . '">' . $vo ['order_number'] . '</a>';
			
			$vo ['action'] = '<a href="' . addons_url ( 'Shop://Order/detail', $param ) . '">详情</a>';

			if ($vo ['status_code'] == 1) {
				$vo ['action'] .= '<br><br><a href="' . addons_url ( 'Shop://Order/set_confirm', $param ) . '">商家确认</a>';
			}
		}
		//dump($list_data ['list_data'] );
		$this->assign ( $list_data );
		 //dump ( $list_data );
		
		$templateFile = $this->model ['template_list'] ? $this->model ['template_list'] : '';
		$this->display ( $templateFile );
	}
	// 通用插件的编辑模型
	public function edit() {
		$model = $this->model;
		$id = I ( 'id' );
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->save ()) {
				D ( 'Common/Keyword' )->set ( $_POST ['keyword'], _ADDONS, $id, $_POST ['keyword_type'], 'custom_reply_news' );
				
				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			$extra = $this->getCateData ();
			if (! empty ( $extra )) {
				foreach ( $fields as &$vo ) {
					if ($vo ['name'] == 'cate_id') {
						$vo ['extra'] .= "\r\n" . $extra;
					}
				}
			}
			
			// 获取数据
			$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
			$data || $this->error ( '数据不存在！' );
			
			$token = get_token ();
			if (isset ( $data ['token'] ) && $token != $data ['token'] && defined ( 'ADDON_PUBLIC_PATH' )) {
				$this->error ( '非法访问！' );
			}
			
			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = '编辑' . $model ['title'];
			
			$this->display ();
		}
	}
	
	// 通用插件的增加模型
	public function add() {
		$model = $this->model;
		$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
		
		if (IS_POST) {
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $id = $Model->add ()) {
				D ( 'Common/Keyword' )->set ( $_POST ['keyword'], _ADDONS, $id, $_POST ['keyword_type'], 'custom_reply_news' );
				
				$this->success ( '添加' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			$extra = $this->getCateData ();
			if (! empty ( $extra )) {
				foreach ( $fields as &$vo ) {
					if ($vo ['name'] == 'cate_id') {
						$vo ['extra'] .= "\r\n" . $extra;
					}
				}
			}
			
			$this->assign ( 'fields', $fields );
			$this->meta_title = '新增' . $model ['title'];
			
			$this->display ();
		}
	}
	
	// 通用插件的删除模型
	public function del() {
		parent::common_del ( $this->model );
	}
	
	// 获取所属分类
	function getCateData() {
		$map ['is_show'] = 1;
		$map ['token'] = get_token ();
		$list = M ( 'weisite_category' )->where ( $map )->select ();
		foreach ( $list as $v ) {
			$extra .= $v ['id'] . ':' . $v ['title'] . "\r\n";
		}
		return $extra;
	}
	function set_show() {
		$save ['is_show'] = 1 - I ( 'is_show' );
		$map ['id'] = I ( 'id' );
		$res = M ( 'shop_goods' )->where ( $map )->save ( $save );
		$this->success ( '操作成功' );
	}
	function detail() {
		$id = I ( 'id' );
		$orderDao = D ( 'Addons://Shop/Order' );
		$orderInfo = $orderDao->getInfo ( $id );
		$address_id = $orderInfo ['address_id'];
		$addressInfo = D ( 'Addons://Shop/Address' )->getInfo ( $address_id );
		
		$orderInfo ['goods'] = json_decode ( $orderInfo ['goods_datas'], true );
		 //dump ( $orderInfo );
		$where['order_number'] = $orderInfo['order_number'];
		$refund = M("Shop_refund")->where($where)->find();
		//dump($refund);
		$this->assign ( 'info', $orderInfo );
		$this->assign ( 'refund', $refund );
		$this->assign ( 'addressInfo', $addressInfo );
		$this->display ();
	}
	function close_order(){
//		$where['order_number'] = I("order_number");
//		$data['status_code'] = 10;
//		$rs = M("Shop_order")->where($where)->save();
//		dump($where);
		$map ['id'] = $_GET['order_id'];
//		dump($map);die();
		$orderDao = D ( 'Addons://Shop/Order' );
		$rs = $orderDao->setStatusCode ( $map ['id'], 10 );
		if ($rs) {
			$this->success ( '修改成功' );
		} else {
			$this->success ( '修改失败' );
		}

	}
	function do_send() {
		$map ['id'] = I ( 'order_id' );
		
		$save ['send_code'] = I ( 'send_code' );
		if (empty ( $save ['send_code'] )) {
			$this->error ( '请选择物流公司' );
		}
		
		$save ['send_number'] = I ( 'send_number' );
		if (empty ( $save ['send_number'] )) {
			$this->error ( '请填写快递号' );
		}
		
		$save ['is_send'] = 2;
		
		$orderDao = D ( 'Addons://Shop/Order' );
		$res = $orderDao->where ( $map )->save ( $save );
		if ($res) {
			$orderDao->setStatusCode ( $map ['id'], 3 );
			
			$this->success ( '发货成功' );
		} else {
			$this->success ( '发货失败' );
		}
	}
	function do_refund(){
		$map ['id'] = $_GET['order_id'];
		$orderDao = D ( 'Addons://Shop/Order' );
		$rs = $orderDao->setStatusCode ( $map ['id'], 9 );
		if ($rs) {
			$this->success ( '提交成功' );
		} else {
			$this->success ( '提交失败' );
		}


	}
	function get_send_info() {
		$id = I ( 'id' );
		$res = $this->getSendInfo ( $id );
		$res=json_decode($res,true);
//		dump($res);
		$html = '';
		if (!$res ['Traces']) {
			$html = '<p>' . $res ['Reason'] . '</p>';
		} else {
			$Traces=array_reverse( $res ['Traces']);
			//dump($Traces);
			foreach ($Traces as $vo ) {
				$html .= '<p>' . $vo ['AcceptTime'] . ' ' . $vo ['AcceptStation'] . ' ' . $vo ['remark'] . '</p>';
			}
		}
		echo $html;
	}
	function set_pay_status() {
		$id = I ( 'id' );
		$save ['pay_status'] = 1;
		$res = D ( 'Addons://Shop/Order' )->update ( $id, $save );
		D ( 'Addons://Shop/Order' )->setStatusCode ( $id, 5 );
		$user = M("User")->field('uid,order_money')->select();
		foreach ($user as $key=>$value){
			$where['uid'] = $value['uid'];
			$order = M("Shop_order")->where($where)->order('id asc')->select();
			$data['order_do'] = count($order);
			if(count($order) > 0){
				$data['order_money'] = 0;
				for($i=0;$i<count($order);$i++){
					$data['order_money'] = $data['order_money'] + $order[$i]['total_price'];
				}
				M("User")->where($where)->save($data);
			}

//				dump($data['order_money']);
//				dump($value['order_money']);
		}

		echo 1;
	}
	function set_confirm() {
		$id = I ( 'id' );
		$res = D ( 'Addons://Shop/Order' )->setStatusCode ( $id, 2 );
		if ($res) {
			$this->success ( '设置成功' );
		} else {
			$this->success ( '设置失败' );
		}
	}
	function getSendInfo($orderId)
	{
		$map['id'] = $orderId;
		$info = M('shop_order')->where($map)->field('send_code,send_number')->find();
		if ($info) {
			$kdInfo['OrderCode']='';
			$kdInfo['ShipperCode']=$info['send_code'];
			$kdInfo['LogisticCode']=$info['send_number'];
			$kdJson=json_encode($kdInfo);
			$kuiDi =new KdApiSerch();
			$sendInfo = $kuiDi->getOrderTracesByJson($kdJson);
			return $sendInfo;
		} else {
			$msg = array('code' => 0, 'msg' => '不存在改信息');
		}
	}


}