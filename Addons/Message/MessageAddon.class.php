<?php

namespace Addons\Message;
use Common\Controller\Addon;

/**
 * 留言板插件
 * @author OMGZui
 */

    class MessageAddon extends Addon{

        public $info = array(
            'name'=>'Message',
            'title'=>'留言板',
            'description'=>'这是一个留言板示例',
            'status'=>1,
            'author'=>'OMGZui',
            'version'=>'1.0',
            'has_adminlist'=>1
        );

	public function install() {
		$install_sql = './Addons/Message/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Message/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }