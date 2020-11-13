<?php
return array(
	//DB settings
	//ms sql server
	'db' => array(
		'type' => 'sqlsrv',
		'server' => '203.137.92.93',
		'dbname' => 'GMap',
		'user' => 'sa',
		'pwd' => 'Jss-0624',
	),
	//Project 
	'app' => array(
		'default_platform' => 'home',//default platform
	),
	//Page
	'home' => array(
		'default_controller' => 'main',//default controller
		'default_action' => 'index',//default action
	),

);
