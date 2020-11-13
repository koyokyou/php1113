<?php
/**
 * basic model
 */
class controller {

    /**
	 * パラメータの取得
	 * $SystemInfo: システム初期化情報 
	 * $IsInit: true 初期表示　false 初期表示以外
	 */
	protected function GetInitParamsInfo($SystemInfo){
		//画面構成ID
		$SystemInfo->MapPatternID = (isset($_POST["MAP_PATTERN_ID"]) && !empty($_POST["MAP_PATTERN_ID"]))?$_POST["MAP_PATTERN_ID"]:null;
		//プロットデータID
		$SystemInfo->PlotDataID = (isset($_POST["PLOTDATA_ID"]) && !empty($_POST["PLOTDATA_ID"]))?$_POST["PLOTDATA_ID"]:null;
		//ユーザーID
		$SystemInfo->LoginUserID = (isset($_POST["USER_ID"]) && !empty($_POST["USER_ID"]))?$_POST["USER_ID"]:null;
	}
	
	    /**
	 * パラメータの取得
	 * $SystemInfo: システム初期化情報 
	 * $IsInit: true 初期表示　false 初期表示以外
	 */
	protected function GetCommonParamsInfo($SystemInfo){
		//画面構成ID
		$SystemInfo->MapPatternID = (isset($_POST["MapPatternID"]) && !empty($_POST["MapPatternID"]))?$_POST["MapPatternID"]:null;
		//プロットデータID
		$SystemInfo->PlotDataID = (isset($_POST["PlotDataID"]) && !empty($_POST["PlotDataID"]))?$_POST["PlotDataID"]:null;
		//ユーザーID
		$SystemInfo->LoginUserID = (isset($_POST["LoginUserID"]) && !empty($_POST["LoginUserID"]))?$_POST["LoginUserID"]:null;
		//適用参照権限グループ
		$SystemInfo->AuthGrupID = (isset($_POST["AuthGrupID"]) && !empty($_POST["AuthGrupID"]))?$_POST["AuthGrupID"]:null;
		//全レコード数
		$SystemInfo->PlotDataInfo['PlotAllCount'] = (isset($_POST["PlotAllCount"]) && !empty($_POST["PlotAllCount"]))?$_POST["PlotAllCount"]:0;
		//プロット不可レコード数
		$SystemInfo->PlotDataInfo['ErrorAllCount'] = (isset($_POST["PlotNgCount"]) && !empty($_POST["PlotNgCount"]))?$_POST["PlotNgCount"]:0;
		//プロット不備レコード数
		$SystemInfo->PlotDataInfo['warningAllCount'] = (isset($_POST["PlotWarningCount"]) && !empty($_POST["PlotWarningCount"]))?$_POST["PlotWarningCount"]:0;
		//出発点緯度/経度
		$SystemInfo->MapPattern['START_LAT'] = (isset($_POST["StartLat"]) && !empty($_POST["StartLat"]))?$_POST["StartLat"]:null;
		$SystemInfo->MapPattern['START_LNG'] = (isset($_POST["StartLon"]) && !empty($_POST["StartLon"]))?$_POST["StartLon"]:null;

	}

	/**
	 * アクセス元クライアント情報を取得する
	 * object[CLIENT_MEDIA, CLIENT_IPADDR]
	 * CLIENT_MEDIA:クライアント利用媒体
	 * CLIENT_IPADDR:クライアントIPアドレス
	 * CLIENT_IS_PC: ture:PC  false: PC以外
	 */
	protected function GetClientInfo($SystemInfo){
		//IPアドレス
		$param_ip_php = '';
		if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$param_ip_php = getenv('HTTP_CLIENT_IP');
		} else if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$param_ip_php = getenv('HTTP_X_FORWARDED_FOR');
		} else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$param_ip_php = getenv('REMOTE_ADDR');
		} else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$param_ip_php = $_SERVER['REMOTE_ADDR'];
		}           

		//利用媒体
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$param_device = '';
		$is_PC = false;
		if(strpos($agent, 'windows nt')) {
			$param_device = 'PC\windows';
			$is_PC = true;
		} elseif(strpos($agent, 'macintosh')) {
			$param_device = 'PC\mac';
			$is_PC = true;
		} elseif(strpos($agent, 'ipod')) {
			$param_device = 'iPod';
		} elseif(strpos($agent, 'ipad')) {
			$param_device = 'iPad';
		} elseif(strpos($agent, 'iphone')) {
			$param_device = 'iPhone';
		} elseif (strpos($agent, 'android')) {
			$param_device = 'Android';
		} elseif(strpos($agent, 'unix')) {
			$param_device = 'PC\unix';
			$is_PC = true;
		} elseif(strpos($agent, 'linux')) {
			$param_device = 'PC\linux';
			$is_PC = true;
		} else {
			$param_device = 'other';
		}
		
        //クライアントのリクエスト情報
        $SystemInfo->ClientMedia = $param_device;
        $SystemInfo->ClientIPAddr = $param_ip_php;
		$SystemInfo->ClientIsPC = $is_PC;
		
		//クライアントIPアドレス(テスト用)
		$SystemInfo->ClientIPTest_HTTP_CLIENT_IP = (getenv('HTTP_CLIENT_IP') === false)?'':getenv('HTTP_CLIENT_IP');  
		$SystemInfo->ClientIPTest_HTTP_X_FORWARDED_FOR =  (getenv('HTTP_X_FORWARDED_FOR') === false)?'':getenv('HTTP_X_FORWARDED_FOR');
		$SystemInfo->ClientIPTest_REMOTE_ADDR = (getenv('REMOTE_ADDR') === false)?'':getenv('REMOTE_ADDR');
    }
    

















}