<?php
        	
namespace Addons\Message\Model;
use Home\Model\WeixinModel;
        	
/**
 * Message的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'Message' ); // 获取后台插件的配置参数

		// 其中token和openid这两个参数一定要传，否则程序不知道是哪个微信用户进入了系统
		$param ['token'] = get_token ();
		$param ['openid'] = get_openid ();
		$url = addons_url ( 'Message://Message/index', $param );
		$picurl = $config['cover'] ? get_cover_url($config['cover']) : '' ;

		// 组装微信需要的图文数据，格式是固定的
		$articles [0] = array (
			'Title' => $config ['title'],
			'Description' => $config ['desc'],
			'PicUrl' => $picurl,
			'Url' => $url
		);
		//$time = date('Y-m-d H:i:s',time());
		//$this->replyText('Hello World!'.$time);
		$this->replyNews($articles);
	}
}
        	