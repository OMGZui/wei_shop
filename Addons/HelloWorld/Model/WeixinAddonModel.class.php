<?php
        	
namespace Addons\HelloWorld\Model;
use Home\Model\WeixinModel;
        	
/**
 * HelloWorld的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$this->replyText('欢迎您来到WeiPHP的世界=>骗子');
		//$config = getAddonConfig ( 'HelloWorld' ); // 获取后台插件的配置参数
		//dump($config);
	}
}
        	