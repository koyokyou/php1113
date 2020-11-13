<?php
/**
 * basic model
 */
class model {
	protected $db; //db obejct
	public function __construct(){
		$this->initDB(); // init DB
	}
	private function initDB() {
		$dsn = $GLOBALS['config']['db']['type'] . ':';
		$dsn .= 'server=' . $GLOBALS['config']['db']['server'] . ';';
		$dsn .= 'Database=' . $GLOBALS['config']['db']['dbname'] ;
		$this->db = new PDO($dsn,$GLOBALS['config']['db']['user'],$GLOBALS['config']['db']['pwd']);
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
	}

	/**
	 * ログトラン(GMAP.LogTbl)に出力する。 
	 * LoginUserID  --ユーザーID
     * MapPatternID --画面構成ID
     * PlotDataID  --プロットデータID
     * AuthGrupID   --適用参照権限グループ
     * PlotAllCount --全レコード数
     * PlotNgCount --プロット不可レコード数
     * StartLat   --出発点緯度/経度
     * StartLon   --出発点緯度/経度
     * ClientMedia  --利用媒体
     * ClientIPAddr  --クライアントIPアドレス
     * CResult      --処理結果
     * ErrorMessage  --エラーメッセ―ジ　　
	 */
	public function InsertLog($UserInfo, $CResult, $ErrorMessage){
		$db = $this->db;
		$sp = $db->prepare("EXEC [GMAP].[SP_ENTRY_LOGTBL] :LoginUserID, :MapPatternID, :PlotDataID,  :AuthGrupID,	
								:PlotAllCount, :PlotNgCount, :StartLat, :StartLon, :ClientMedia, :ClientIPAddr, :CResult, :ErrorMessage");

		$LoginUserID = $UserInfo->LoginUserID;
		$MapPatternID = $UserInfo->MapPatternID;
		$PlotDataID = $UserInfo->PlotDataID;
		$AuthGrupID  =$UserInfo->AuthGrupID;
		$ClientMedia = $UserInfo->ClientMedia;
		$ClientIPAddr = $UserInfo->ClientIPAddr;
		$PlotAllCount =  $UserInfo->PlotDataInfo['PlotAllCount'];
		$PlotNgCount =  $UserInfo->PlotDataInfo['ErrorAllCount'];
		$StartLat =  $UserInfo->MapPattern['START_LAT'];
		$StartLon=  $UserInfo->MapPattern['START_LNG'];
		$sp->bindparam(':LoginUserID', $LoginUserID, PDO::PARAM_STR);
		$sp->bindparam(':MapPatternID', $MapPatternID, PDO::PARAM_STR);
		$sp->bindparam(':PlotDataID', $PlotDataID, PDO::PARAM_STR);
		$sp->bindparam(':AuthGrupID', $AuthGrupID, PDO::PARAM_STR);
		$sp->bindparam(':PlotAllCount', $PlotAllCount, PDO::PARAM_STR);
		$sp->bindparam(':PlotNgCount', $PlotNgCount, PDO::PARAM_STR);
		$sp->bindparam(':StartLat', $StartLat, PDO::PARAM_STR);
		$sp->bindparam(':StartLon', $StartLon, PDO::PARAM_STR);
		$sp->bindparam(':ClientMedia', $ClientMedia, PDO::PARAM_STR);
		$sp->bindparam(':ClientIPAddr', $ClientIPAddr, PDO::PARAM_STR);
		$sp->bindparam(':CResult', $CResult, PDO::PARAM_STR);
		$sp->bindparam(':ErrorMessage', $ErrorMessage, PDO::PARAM_STR);
		try {
			$sp->execute();
		} catch (PDOException $e) {
			//echo $e->getMessage();
		}
	}

	/**
     * エラーメッセ―ジの取得処理
	 */
	public function GetErrorMessage($e){
		$code = ''; //'(' . $e->getCode()  .')';
		$message = $e->getMessage() . $code;
		if($e instanceof PDOException)
        {
			$keyword = '[SQL Server]';
			$message = mb_substr($message,mb_strripos($message,$keyword) + mb_strlen($keyword,"utf-8"));
		}
		return $message;
	}


}
