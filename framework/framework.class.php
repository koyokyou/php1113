<?php
/**
 * Base framework 
 */
class framework{
	public function runApp(){
		$this->loadConfig();        //load config 
		$this->registerAutoLoad();  //regist base class
		$this->getRequestParams();  //get params of request
		$this->dispatch();          //Request dispatch
	}
	/**
	 * load config 
	 */
	private function registerAutoLoad(){
		spl_autoload_register(array($this,'user_autoload'));
	}
	/**
	 * regist basic class
	 * $param $class_name string classname
	 */
	public function user_autoload($class_name){
		//Define basic class list
		$base_classes = array(
			//class => path	
			'model'			=> './framework/model.class.php',
			'controller'	=> './framework/controller.class.php'
		);
		//basic class, model class and controller class
		if (isset($base_classes[$class_name])){
			require $base_classes[$class_name];
		}elseif (substr($class_name,-5) == 'Model'){
			require './application/'.PLATFORM."/model/{$class_name}.class.php";
		}elseif (substr($class_name, -10) == 'Controller'){
			require './application/'.PLATFORM."/controller/{$class_name}.class.php";
		}
	}
	/**
	 * load config file
	 */ 
	private function loadConfig(){
		//config
		$GLOBALS['config'] = require './application/config/app.conf.php';

	}
	/**
	 * get params of request
	 */
	private function getRequestParams(){
		//platform name
		define('PLATFORM', isset($_GET['p']) ? $_GET['p'] : $GLOBALS['config']['app']['default_platform']);
		//controller name
		define('CONTROLLER', isset($_GET['c']) ? $_GET['c'] : $GLOBALS['config'][PLATFORM]['default_controller']);
		//action name
		define('ACTION', isset($_GET['a']) ? $_GET['a'] : $GLOBALS['config'][PLATFORM]['default_action']);
	}
	/**
	 * Request dispatch
	 */
	private function dispatch(){
		//Controller 
		$controller_name = CONTROLLER.'Controller';
		$controller = new $controller_name;
		//action
		$action_name = ACTION . 'Action';
		$controller->$action_name();
	}
}
