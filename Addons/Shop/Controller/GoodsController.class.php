<?php

namespace Addons\Shop\Controller;

use Addons\Shop\Controller\BaseController;

class GoodsController extends BaseController {
	var $model;
	function _initialize() {
		$this->model = $this->getModel ( 'shop_goods' );
		parent::_initialize ();
	}
	// 通用插件的列表模型
	public function lists() {
		$map ['token'] = get_token ();
		$map ['shop_id'] = $this->shop_id;
		session ( 'common_condition', $map );
		$list_data = $this->_get_model_list ( $this->model );
		// 分类数据
		$map ['is_show'] = 1;
		$list = M ( 'shop_goods_category' )->where ( $map )->field ( 'id,title' )->select ();
		$cate [0] = '';
		foreach ( $list as $vo ) {
			$cate [$vo ['id']] = $vo ['title'];
		}
		foreach ( $list_data ['list_data'] as &$vo ) {
			$vo ['category_id'] = intval ( $vo ['category_id'] );
			$vo ['category_id'] = $cate [$vo ['category_id']];
		}
		$this->assign ( $list_data );
		$templateFile = $this->model ['template_list'] ? $this->model ['template_list'] : '';
		$this->display ( $templateFile );
	}
	// 通用插件的编辑模型
	public function edit() {
		$model = $this->model;
		$id = I ( 'id' );
		$shop_id = $this->shop_id;
		
		if (IS_POST) {
			if ($_POST ['imgs'] && count ( $_POST ['imgs'] ) > 0) {
				$_POST ['imgs'] = implode ( ',', $_POST ['imgs'] );
			};
			if ($_POST ['shop_detail'] && count ( $_POST ['shop_detail'] ) > 0) {
				$_POST ['shop_detail'] = implode ( ',', $_POST ['shop_detail'] );
			};
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->save ()) {
				D ( 'Common/Keyword' )->set ( $_POST ['keyword'], _ADDONS, $id, $_POST ['keyword_type'], 'custom_reply_news' );
				
				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] . '&shop_id=' . $shop_id ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			$extra = $this->getCateData ();
			if (! empty ( $extra )) {
				foreach ( $fields as &$vo ) {
					if ($vo ['name'] == 'category_id') {
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
			$data ['imgs'] = explode ( ',', $data ['imgs'] );
			$data ['shop_detail'] = explode ( ',', $data ['shop_detail'] );
			//dump($fields);
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
		$shop_id = $_POST ['shop_id'] = $this->shop_id;
		;
		if (IS_POST) {
			if ($_POST ['imgs'] && count ( $_POST ['imgs'] ) > 0) {
				$_POST ['imgs'] = implode ( ',', $_POST ['imgs'] );
			}
			;
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $id = $Model->add ()) {
				D ( 'Common/Keyword' )->set ( $_POST ['keyword'], _ADDONS, $id, $_POST ['keyword_type'], 'custom_reply_news' );
				
				$this->success ( '添加' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] . '&shop_id=' . $shop_id ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			$extra = $this->getCateData ();
			
			if (! empty ( $extra )) {
				foreach ( $fields as &$vo ) {
					if ($vo ['name'] == 'category_id') {
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
	    $id=I('id');
	    $ids=I('ids');
//		dump($id);
//		dump($ids);exit();
	    if (!empty($id)){
	        $key = 'Goods_getInfo_' . $id;
	        S ( $key, null );
	    }else {
	         foreach ($ids as $i){
	           $key = 'Goods_getInfo_' . $i;
	           S ( $key, null );
	         }
	    }
		parent::common_del ( $this->model );
	}
	
	// 获取所属分类
	function getCateData() {
		$map ['is_show'] = 1;
		$map ['token'] = get_token ();
		$map['shop_id']=$this->shop_id;
		$list = M ( 'shop_goods_category' )->where ( $map )->select ();
		foreach ( $list as $v ) {
			$extra .= $v ['id'] . ':' . $v ['title'] . "\r\n";
		}
		return $extra;
	}
	function set_show() {
		$save ['is_show'] = 1 - I ( 'is_show' );
		$map ['shop_id'] = $this->shop_id;
		$map['id']=I('id');
		$res = M ( 'shop_goods' )->where ( $map )->save ( $save );
		$this->success ( '操作成功' );
	}

	//公告
	public function notice()
	{
		$rs = M("Shop_notice")->select();
		$this->assign('lists',$rs);
		$this->display();
	}
	//添加公告页面
	public function notice_add()
	{
		$this->display();
	}
	//添加公告
	public function add_do()
	{
		if(IS_POST){
			$data['content'] = I("content");
			$data['is_show'] = I("is_show");
			$data['Ctime'] = time();
			$data['shop_id'] = $this->shop_id;
			$rs = M("Shop_notice")->add($data);
			if($rs){
				$this->success('成功',addons_url("Shop://Goods/notice"),1);
			}
		}
	}
	//编辑公告页面
	public function notice_edit()
	{
		$id['id'] = I('id');
		$rs = M("Shop_notice")->where($id)->find();
		$this->assign('notice',$rs);
		$this->display();
	}
	//编辑公告
	public function notice_edit_do()
	{
		$id['id'] = I('id');
		$data['content'] = I("content");
		$data['is_show'] = I("is_show");
		$rs = M("Shop_notice")->where($id)->save($data);
		if($rs){
			$this->success('编辑成功',addons_url("Shop://Goods/notice"),1);
		}
	}
	//删除公告
	public function notice_del()
	{
		$id['id'] = I("id");
		$rs = M("Shop_notice")->where($id)->delete();
		if($rs){
			$this->success('删除成功',addons_url("Shop://Goods/notice"),1);
		}
	}
	//评价管理
	public function evaluate()
	{
//		$where['uid'] = $this->uid;
		$where['shop_id'] = $this->shop_id;
		if(I('order_number')){
			$where['order_number'] = array('eq',I('order_number'));
		}
		if(I('nickname')){
			$nickname['nickname'] = I('nickname');
			$data = M('User')->where($nickname)->find();
			$where['uid'] = array('eq',$data['uid']);
		}
		if(I('start') && I('end')){
			$where['time'] = array(array('gt',I('start')),array('lt',I('end')));
		}
		$rs = M("Shop_evaluate")->where($where)->select();
		foreach ($rs as $key=>$val){
			$number['order_number'] = $rs[$key]['order_number'];
			$order = M("Shop_order")->where($number)->find();
			$uidd['uid'] = $order['uid'];
			$uid = M("User")->where($uidd)->find();
			$rs[$key]['order'] = $order;
			$rs[$key]['uid'] = $uid;
		}
		//lastsql();
		//dump($rs);
		$this->assign('lists',$rs);
		$this->display();

	}
	//回复评价页面
	public function evaluate_reply(){
		$where['id'] = I('id');
		$where['shop_id'] = $this->shop_id;
		$rs = M("Shop_evaluate")->where($where)->select();
		foreach ($rs as $key=>$val){
			$number['order_number'] = $rs[$key]['order_number'];
			$order = M("Shop_order")->where($number)->find();
			$uidd['uid'] = $order['uid'];
			$uid = M("User")->where($uidd)->find();
			$rs[$key]['order'] = $order;
			$rs[$key]['uid'] = $uid;
		}
		//dump($rs);
		$this->assign('lists',$rs);
		$this->display();
	}
	//回复评价
	public function evaluate_reply_do()
	{
		$where['id'] = I("id");
		$data['ev_content_reply'] = I("ev_content_reply");
		$data['status'] = 1;
		$data['reply_time'] = time();
		$rs = M("Shop_evaluate")->where($where)->save($data);
		if($rs){
			$this->success('回复成功',addons_url("Shop://Goods/evaluate"),1);
		}
	}
	//删除
	public function evaluate_del()
	{
		$id['id'] = I("id");
		$rs = M("Shop_evaluate")->where($id)->delete();
		if($rs){
			$this->success('删除成功',addons_url("Shop://Goods/evaluate"),1);
		}
	}

}