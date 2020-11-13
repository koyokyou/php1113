<?php
/**
 * main controller
 */

class mainController extends controller {
	protected $dbModel;

    /**
	 * 初期化
	 */
	function __construct()
    {
		$this->dbModel = new dbModel();
	}

	/**
	 * メイン画面の表示
	 */
	public function IndexAction(){
		$SystemSetting = new userModel();	//システム初期化情報

		//クライアント情報の取得処理
		$this->GetClientInfo($SystemSetting);
		//パラメータ情報の取得処理
		$this->GetInitParamsInfo($SystemSetting);

		$MapPatternID = $SystemSetting->MapPatternID;	//画面構成ID
		$PlotDataID = $SystemSetting->PlotDataID; 		//プロットデータID
		$LoginUserID = $SystemSetting->LoginUserID;		//ユーザーID
		$ClientIsPC = $SystemSetting->ClientIsPC;		//ture:PC  false: PC以外 

		require './application/home/view/index.php';
	}

	/**
	 * メイン画面の初期化
	 */
	public function InitAction(){
		$ret = array();
		$screenMessage = null;	//エラーメッセージ

		$KbnItemList = array();	//区分項目
		$IsAutoDraw = 1;		//自動再描画の設定値
		$FixedValue = 100;		//システム固定値

		$SystemSetting = new userModel();	//システム初期化情報
		//クライアント情報の取得処理
		$this->GetClientInfo($SystemSetting);
		//パラメータ情報の取得処理
		$this->GetCommonParamsInfo($SystemSetting);

		//パラメータのチェック処理
		$retCheckParams= $this->checkParams($SystemSetting);
		if ($retCheckParams['Result'] == 0  || $retCheckParams['ScreenMessage'] != null){
			$ret = array('Result' => 0,'ScreenMessage' => $retCheckParams['ScreenMessage']);
			echo json_encode($ret);
			return;
		}
		//システム利用権限のチェック処理
		$retKengen = $this->dbModel->GetKengen($SystemSetting);
		if ($retKengen['AuthGrupID'] == null || $retKengen['ScreenMessage'] != null){
			$ret = array('Result' => 0,'ScreenMessage' => $retKengen['ScreenMessage']);
			echo json_encode($ret);
			return;
		}
		//画面構成情報の取得処理
		$StartLat = $SystemSetting ->MapPattern['START_LAT'];
		$StartLon = $SystemSetting ->MapPattern['START_LNG'];
		$retMapPattern= $this->dbModel->GetMapPattern($SystemSetting);
		if ($retMapPattern['InvalidError'] == 0 || $retMapPattern['MapPattern'] == null || $retMapPattern['ScreenMessage'] != null){
			$ret = array('Result' => 0,'ScreenMessage' => $retMapPattern['ScreenMessage']);
			echo json_encode($ret);
			return;
		}
		$SystemSetting ->MapPattern['START_LAT'] = ($StartLat != null)? $StartLat:$SystemSetting ->MapPattern['START_LAT'];
		$SystemSetting ->MapPattern['START_LNG'] = ($StartLon != null)? $StartLon:$SystemSetting ->MapPattern['START_LNG'];

		//プロットデータの取得処理
		$retReadPlotData= $this->dbModel->ReadPlotData($SystemSetting);
		if ($retReadPlotData['Result'] == 0  || $retReadPlotData['ScreenMessage'] != null){
			$ret = array('Result' => 0,'ScreenMessage' => $retReadPlotData['ScreenMessage']);
			echo json_encode($ret);
			return;
		}

		//区分項目の取得処理
		$retKbnItem = $this->dbModel->GetKbnItem($SystemSetting);
		if ($retKbnItem['Result'] == 0 ){
			$ret = array('Result' => 0,'ScreenMessage' => $retKbnItem['ScreenMessage']);
			echo json_encode($ret);
			return;
		}
		$KbnItemList = $retKbnItem['KbnItem'];

		//設定値の取得処理
		$retSetting= $this->dbModel->GetSetting($SystemSetting);
		$IsAutoDraw = $retSetting['SettingValue'];

		
		//2020/10/30 システムの固定値の取得処理
		$retSystemFixedValue= $this->dbModel->GetSystemFixedValue($SystemSetting);
		$FixedValue = $retSystemFixedValue['FixedValue'];

		//プロットデータの取得
		$retGetPlotData = $this->GetPlotData($SystemSetting, $KbnItemList);
		if ($retGetPlotData['Result'] == 0 ){
			$ret = array('Result' => 0,'ScreenMessage' => $retGetPlotData['ScreenMessage']);
			echo json_encode($ret);
			return;
		}
		$PlotDataList = $retGetPlotData['PlotData'];
		$screenMessage = $retGetPlotData['ScreenMessage'];

		//GoogleMAP API使用ログを出力する
		$this->dbModel->InsertLog($SystemSetting,1,null);

		//戻り値
		$ret = array( 
			'Result' => 1,
			'ScreenMessage' => $screenMessage,
			'MapPatternID'=> $SystemSetting->MapPatternID,
			'PlotDataID'=> $SystemSetting->PlotDataID,
			'LoginUserID'=> $SystemSetting->LoginUserID,
			'AuthGrupID'=> $SystemSetting->AuthGrupID,
			'MapPattern' =>$SystemSetting->MapPattern,
			'PlotDataInfo'=> $SystemSetting->PlotDataInfo,
			'IsAutoDraw'=> $IsAutoDraw,
			'FixedValue'=> $FixedValue,
			'KbnItemList'=> $KbnItemList,
			'PlotData' =>$PlotDataList
		);

		echo json_encode($ret);
	}
	
	/**
	 * パラメータのチェック処理
	 * メニューやGoogleMAPを表示する前に処理する
	 * 「画面構成ID」のパラメータ名は「MAP_PATTERN_ID」,「データプロットID」のパラメータ名は「PLOTDATA_ID」
	 */
	protected function CheckParams($SystemInfo){
		$map_Pattern_ID = $SystemInfo->MapPatternID;
		$plotData_ID =  $SystemInfo->PlotDataID;
		$result = 1;
		$screenMessage = null;

		if ( !(isset($map_Pattern_ID) && !empty($map_Pattern_ID))
			|| !(isset($plotData_ID) && !empty($plotData_ID)))
    	{
			$screenMessage = array('Type'=> 1,'Content' => 'パラメータが正しくありません。');
			$this->dbModel->InsertLog($SystemInfo,0,'パラメータが正しくありません。');
			$result = 0;
		}
		return  array('Result' => $result,'ScreenMessage' => $screenMessage);
	}

	/**
	 *プロットデータの取得処理
	 */
	private function GetPlotData($SystemInfo,$KbnItemList){
		$arrKbnCode = array();
		foreach($KbnItemList as $item => $KbnItem){
			$arrCode = [];
			foreach($KbnItem as $key => $value){
				array_push($arrCode,$value['RET_ITEM_CODE']);
			}
			array_push($arrKbnCode,implode(',',$arrCode));
		}
		$result = $this->dbModel->GetPlotData($SystemInfo,$arrKbnCode);
		if ($result['Result'] == 1){
			$errorAllCount = $SystemInfo->PlotDataInfo['ErrorAllCount'];
			$warningAllCount = $SystemInfo->PlotDataInfo['warningAllCount'];
			$arrscreenMessage = [];
			if ($errorAllCount > 0){
				array_push($arrscreenMessage,'データ不正によりプロット出来ないデータが' . $errorAllCount . '件ありました。');
			}
			if ($warningAllCount > 0){
				array_push($arrscreenMessage,'データ不備によるプロット済データが' . $warningAllCount . '件ありました。');
			}
			if (sizeof($arrscreenMessage)>0){
				$result['ScreenMessage'] = array('Type'=> 4,'Content' => implode('<br>',$arrscreenMessage));
			}
		}
		return $result;
	}
	
	/**
	 * 設定値の保存処理（AJAXで呼び出す）
	 */
	public function SetSettingAction(){
		try{
			$SystemSetting = new userModel();
			//クライアント情報の取得処理
			$this->GetClientInfo($SystemSetting);
			//パラメータ情報の取得処理
			$this->GetCommonParamsInfo($SystemSetting);
			
			//設定値の保存
			$SettingValue = $_POST["SettingValue"];
			$SettingValue = $SettingValue == 1?1:0;
			$ret = $this->dbModel->SetSetting($SystemSetting,$SettingValue);
			
		}catch(Exception $e){
			if ($SettingValue == 1){
				$this->dbModel->InsertLog($SystemSetting,0,'自動再描画の更新(ON)に失敗しました：'. $this->GetErrorMessage($e));
				$ret = array('Result' => 0,'ScreenMessage' => array('Type'=> 1,'Content' => '自動再描画の更新(ON)に失敗しました。'));
			}else{
				$this->dbModel->InsertLog($SystemSetting,0,'自動再描画の更新(OFF)に失敗しました：'. $this->GetErrorMessage($e));
				$ret = array('Result' => 0,'ScreenMessage' => array('Type'=> 1,'Content' => '自動再描画の更新(OFF)に失敗しました。'));
			}
		}
		echo json_encode($ret);
	}

	/**
	 * プロットデータ（AJAXで呼び出す）
	 */
	public function GetPlotDataAction(){
		try{
			$SystemSetting = new userModel();
			//クライアント情報の取得処理
			$this->GetClientInfo($SystemSetting);
			//パラメータ情報の取得処理
			$this->GetCommonParamsInfo($SystemSetting);

			//画面構成情報の再取得
			$StartLat = $SystemSetting ->MapPattern['START_LAT'];
			$StartLon = $SystemSetting ->MapPattern['START_LNG'];
			$retMapPattern= $this->dbModel->GetMapPattern($SystemSetting);
			if ($retMapPattern['InvalidError'] == 0 || $retMapPattern['MapPattern'] == null  || $retMapPattern['ScreenMessage'] != null){
				$ret = array('Result' => 0,'ScreenMessage' => $retMapPattern['ScreenMessage']);
				echo json_encode($ret);
				return;
			}
			$SystemSetting ->MapPattern['START_LAT'] = ($StartLat != null)? $StartLat:$SystemSetting ->MapPattern['START_LAT'];
			$SystemSetting ->MapPattern['START_LNG'] = ($StartLon != null)? $StartLon:$SystemSetting ->MapPattern['START_LNG'];

			//プロットデータの取得
			$KbnCode = isset($_POST["KbnCode"])?$_POST["KbnCode"]:null;
			$retGetPlotData = $this->dbModel->GetPlotData($SystemSetting,$KbnCode);
			$result = $retGetPlotData['Result'];
			$screenMessage = $retGetPlotData['Result'];

			$ret = array( 
				'Result' => $result,
				'ScreenMessage' => $screenMessage,
				'PlotData' =>$retGetPlotData['PlotData'],
				'MapPattern' =>$SystemSetting->MapPattern
			);
		}catch(Exception $e){
			$this->dbModel->InsertLog($SystemSetting,0,'プロットデータの抽出に失敗しました：' . $this->GetErrorMessage($e));
			$ret = array('Result' => 0,'ScreenMessage' =>  array('Type'=> 1,'Content' => 'プロットデータの抽出に失敗しました。'));
		}
		echo json_encode($ret);
	}

}
