<?php
/**
 * mapModel
 */
class dbModel extends model{

	/**
	 * システム利用権限のチェック処理
	 * 戻り値  権限グループID 
	 *         エラーメッセ―ジ(画面表示用)
	 */
	public function GetKengen($SystemInfo){
		try {

			$SystemInfo->AuthGrupID = $authGrpID = null;//権限グループID 
			$mapPatternID = $SystemInfo->MapPatternID;	//画面構成ID
			$plotDataID = $SystemInfo->PlotDataID;		//クライアントの情報の取得
			$loginUserID = $SystemInfo->LoginUserID;	//ユーザーID
			$screenMessage = null;	//エラーメッセージ

			$db = $this->db;
			$sp = $db->prepare("EXEC [GMAP].[SP_GET_KENGEN] :MapPatternID , :PlotDataID , :LoginUserID ");
			$sp->bindparam(':MapPatternID', $mapPatternID, PDO::PARAM_STR);
			$sp->bindparam(':PlotDataID', $plotDataID, PDO::PARAM_STR);
			$sp->bindparam(':LoginUserID', $loginUserID, PDO::PARAM_STR);

			$sp->execute();
			if ($sp->columnCount()>0){
				$authGrpID = $sp->fetchColumn();	//複数マッチしたとしてもそのうちの一つ
			}
			while($sp->nextRowset()){};
			if ($authGrpID === false){
				$authGrpID = null;
				$screenMessage = array('Type'=> 1,'Content' => '権限が無いため本システムは利用できません。');
				$this->InsertLog($SystemInfo,0,$screenMessage['Content']);
			}
			$SystemInfo->AuthGrupID = $authGrpID;
		} catch (Exception $e) { 
			$screenMessage = array('Type'=> 1,'Content' => '権限情報の取得に失敗しました。');
			$this->InsertLog($SystemInfo,0,'権限情報の取得に失敗しました：'.$this->GetErrorMessage($e));
		}
		return  $array = array('AuthGrupID' => (string)$authGrpID,'ScreenMessage' => $screenMessage);
	}

	/**
	 * 画面構成情報の取得処理
	 * 戻り値  画面構成情報
	 * 		 （画面構成名,システム識別子,プロットデータVIEW名,出発地緯度,出発地経度,起動時縮尺,ラベル表示ON/OFF,案内図表示ON/OFF,ラベルCSS名,円CSS名,経路検索手段）
	 * 		  エラーメッセ―ジ(画面表示用)	
	 */
	public function GetMapPattern($SystemInfo){
		try {
			
			$SystemInfo->InitMapPattern();
			$MapPatternInfo = null;	 //画面構成情報
			$mapPatternID = $SystemInfo->MapPatternID;	//画面構成ID
			$plotDataID = $SystemInfo->PlotDataID;		//クライアントの情報の取得
			$loginUserID = $SystemInfo->LoginUserID;	//ユーザーID
			$invalidError = 0;
			$screenMessage = null;

			$db = $this->db;
			$sp = $db->prepare("EXEC [GMAP].[SP_GET_MAPPATTERN] :MapPatternID , :PlotDataID , :LoginUserID , :InvalidError");
			$sp->bindparam(':MapPatternID', $mapPatternID, PDO::PARAM_STR);
			$sp->bindparam(':PlotDataID', $plotDataID, PDO::PARAM_STR);
			$sp->bindparam(':LoginUserID', $loginUserID, PDO::PARAM_STR);
			$sp->bindparam(':InvalidError', $invalidError, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT,1);

			$sp->execute();
			if ($sp->columnCount()>0){
				while($row=$sp->fetch(PDO::FETCH_ASSOC)){
					$MapPatternInfo = $row;
				}
			}
			while($sp->nextRowset()){};

			$invalidError = (int)$invalidError;
			if ($invalidError == 0){
				$screenMessage = array('Type'=> 1,'Content' => '無効な画面構成IDです。');
				$this->InsertLog($SystemInfo,0,$screenMessage['Content']);
			}else if ($MapPatternInfo == null){
				$screenMessage = array('Type'=> 1,'Content' => '画面構成情報が無いため本システムは利用できません。');
				$this->InsertLog($SystemInfo,0,$screenMessage['Content']);
			}
			$SystemInfo->MapPattern = $MapPatternInfo;
		} catch (Exception $e) { 
			$MapPatternInfo = null;	
			$screenMessage = array('Type'=> 1,'Content' => '画面構成情報の取得に失敗しました。');
			$this->InsertLog($SystemInfo,0,'画面構成情報の取得に失敗しました：'. $this->GetErrorMessage($e));
		}
		return  $array = array('InvalidError' => $invalidError,'MapPattern' => $MapPatternInfo,'ScreenMessage' => $screenMessage);
	}

	/**
	 * プロットデータを取得する
	 * 戻り値  処理結果
	 *        エラーメッセ―ジ(画面表示用)
	 */
	public function ReadPlotData($SystemInfo){
		try {
			$SystemInfo->InitPlotDataInfo();
			$mapPatternID = $SystemInfo->MapPatternID;	//画面構成ID
			$plotDataID = $SystemInfo->PlotDataID;		//クライアントの情報の取得
			$loginUserID = $SystemInfo->LoginUserID;	//ユーザーID
			$viewName  =$SystemInfo->MapPattern['VIEW_NAME'];	//プロットデータVIEW名
			$plotAllCount = 0;
			$errorAllCount = 0;
			$warningAllCount = 0;
			$screenMessage = null;

			$db = $this->db;
			$sp = $db->prepare("EXEC [GMAP].[SP_READ_PLOTDATA] :MapPatternID , :PlotDataID , :LoginUserID ,:ViewName , 
								:PlotAllCount ,:ErrorAllCount ,:warningAllCount ,
								:Result ");
			$sp->bindparam(':MapPatternID', $mapPatternID, PDO::PARAM_STR);
			$sp->bindparam(':PlotDataID', $plotDataID, PDO::PARAM_STR);
			$sp->bindparam(':LoginUserID', $loginUserID, PDO::PARAM_STR);
			$sp->bindparam(':ViewName', $viewName, PDO::PARAM_STR);
			$sp->bindparam(':PlotAllCount', $plotAllCount, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT,PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
			$sp->bindparam(':ErrorAllCount', $errorAllCount, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT,PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
			$sp->bindparam(':warningAllCount', $warningAllCount, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT,PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
			$sp->bindparam(':Result', $result, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT,PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);

			$sp->execute();
			while($sp->nextRowset()){};
			$SystemInfo->PlotDataInfo = array(	'PlotAllCount'=> $plotAllCount,
												'ErrorAllCount'=>$errorAllCount,
												'warningAllCount'=>$warningAllCount
										   );
			//プロットするデータが存在しません。
			if ($plotAllCount ==0){
				$result = 0;
				$screenMessage = array('Type'=> 3,'Content' => 'プロットするデータが存在しません。');
				$this->InsertLog($SystemInfo,0,'プロットするデータが存在しません。');
			}
			//不正データの件数が1件でも存在する場合はログ出力する
			if ($errorAllCount <>0){
				$this->InsertLog($SystemInfo,0,'データ不正によりプロット出来ないデータが'. (string)$errorAllCount .'件ありました。');
			}
		} catch (Exception $e) { 
			$result = 0;
			$screenMessage = array('Type'=> 1,'Content' => 'プロットデータの取得に失敗しました。');
			$this->InsertLog($SystemInfo,0, $this->GetErrorMessage($e));
		}
		return  $array = array( 
								'PlotAllCount'=> $plotAllCount,
								'ErrorAllCount'=> $errorAllCount,
								'warningAllCount'=>$warningAllCount,
								'Result' => $result,
								'ScreenMessage' => $screenMessage
							);
	}

	/**
	 * 区分項目の取得処理
	 * 戻り値   区分項目情報
	 * 		   エラーメッセ―ジ(画面表示用)	
	 */
	public function GetKbnItem($SystemInfo){
		try {
			$KbnItemList = [];	//区分項目情報

			$mapPatternID = $SystemInfo->MapPatternID;	//画面構成ID
			$plotDataID = $SystemInfo->PlotDataID;		//クライアントの情報の取得
			$loginUserID = $SystemInfo->LoginUserID;	//ユーザーID
			$result = 0;
			$screenMessage = null;
			
			$db = $this->db;
			$sp = $db->prepare("EXEC [GMAP].[SP_GET_KBNITEM] :MapPatternID , :PlotDataID , :LoginUserID ");
			$sp->bindparam(':MapPatternID', $mapPatternID, PDO::PARAM_STR);
			$sp->bindparam(':PlotDataID', $plotDataID, PDO::PARAM_STR);
			$sp->bindparam(':LoginUserID', $loginUserID, PDO::PARAM_STR);

			$sp->execute();
			if ($sp->columnCount()>0){
				$KbnItemList = $sp->fetchAll();
			}
			while($sp->nextRowset()){};
			$result = 1;
		} catch (Exception $e) { 
			$KbnItemList = [];	//区分項目情報
			$result = 0;
			$screenMessage = array('Type'=> 1,'Content' => '区分項目情報の取得に失敗しました。');
			$this->InsertLog($SystemInfo,0,'区分項目情報の取得に失敗しました：'. $this->GetErrorMessage($e));
		}

		//グループ処理		
		$grpKbnItemList = [];
		if ($KbnItemList != null && is_array($KbnItemList)){
			foreach ($KbnItemList as $k => $val) {  
				$grpKbnItemList[$val['ITEM_TITLESORT']][] = $val;
			}
		}
		return  $array = array(
							'KbnItem' => $grpKbnItemList,
							'Result' => $result,
							'ScreenMessage' => $screenMessage
						);
	}

	/**
	 * 設定値の取得処理
	 * 戻り値   設定値
	 * 		   エラーメッセ―ジ(画面表示用)	
	 */
	public function GetSetting($SystemInfo){
		try {
			$settingValue = '1';	//設定値
			$columnID = '001';	//項目ID
			$loginUserID = $SystemInfo->LoginUserID;	//ユーザーID
			$screenMessage = null;
			
			$db = $this->db;
			$sp = $db->prepare("EXEC [GMAP].[SP_GET_SETTING] :ColumnID , :LoginUserID");
			$sp->bindparam(':ColumnID', $columnID, PDO::PARAM_STR);
			$sp->bindparam(':LoginUserID', $loginUserID, PDO::PARAM_STR);

			$sp->execute();
			if ($sp->columnCount() > 0){
				$settingValue = $sp->fetchColumn(); //設定値
				$settingValue = ($settingValue === false)?'1':$settingValue;
			}else{
				$settingValue = '1';	//設定値
			}
			while($sp->nextRowset()){};
		} catch (Exception $e) { 
			$settingValue = '1';	//設定値情報
			$screenMessage = array('Type'=> 1,'Content' => '設定値の取得に失敗しました。');
			$this->InsertLog($SystemInfo,0,'設定値の取得に失敗しました：'. $this->GetErrorMessage($e));
		}
		return  $array = array('SettingValue' => (int)$settingValue);
	}

		/**
	 * 設定値の保存処理
	 * 戻り値  処理結果
	 * 		  エラーメッセ―ジ(画面表示用)	
	 */
	public function SetSetting($SystemInfo,$SettingValue){
		try {
			$columnID = '001';	//項目ID
			$loginUserID = $SystemInfo->LoginUserID;	//ユーザーID
			$result = 0;
			$screenMessage = null;
			
			$db = $this->db;
			$sp = $db->prepare("EXEC [GMAP].[SP_SET_SETTING] :ColumnID , :LoginUserID , :SetValue");
			$sp->bindparam(':ColumnID', $columnID, PDO::PARAM_STR);
			$sp->bindparam(':LoginUserID', $loginUserID, PDO::PARAM_STR);
			$sp->bindparam(':SetValue', $SettingValue, PDO::PARAM_STR);

			$sp->execute();
			$result = 1;
		} catch (Exception $e) { 
			if ($SettingValue == 1){
				$screenMessage = array('Type'=> 1,'Content' => '自動再描画の更新(ON)に失敗しました。');
				$this->InsertLog($SystemInfo,0,'自動再描画の更新(ON)に失敗しました：'. $this->GetErrorMessage($e));
			}else{
				$screenMessage = array('Type'=> 1,'Content' => '自動再描画の更新(OFF)に失敗しました。');
				$this->InsertLog($SystemInfo,0,'自動再描画の更新(OFF)に失敗しました：'. $this->GetErrorMessage($e));
			}
		}
		return  $array = array('Result' => $result,'ScreenMessage' => $screenMessage);
	}

	/**
	 * プロットデータの取得処理
	 * 戻り値   プロットデータ
	 * 		   処理結果
	 * 		   エラーメッセ―ジ(画面表示用)	
	 */
	public function GetPlotData($SystemInfo, $KbnCode){
		try {
			$PlotData = array();
			$mapPatternID = $SystemInfo->MapPatternID;	//画面構成ID
			$plotDataID = $SystemInfo->PlotDataID;		//クライアントの情報の取得
			$loginUserID = $SystemInfo->LoginUserID;	//ユーザーID

			$result = 0;
			$screenMessage = null;

			$db = $this->db;
			$sp = $db->prepare("EXEC [GMAP].[SP_GET_PLOTDATA] :MapPatternID , :PlotDataID , :LoginUserID , 
							:Kbn1Code , :Kbn2Code , :Kbn3Code , :Kbn4Code , :Kbn5Code , 
							:Result");
			$sp->bindparam(':MapPatternID', $mapPatternID, PDO::PARAM_STR);
			$sp->bindparam(':PlotDataID', $plotDataID, PDO::PARAM_STR);
			$sp->bindparam(':LoginUserID', $loginUserID, PDO::PARAM_STR);

			$Kbn1Code = isset($KbnCode[0])?$KbnCode[0]:null;
			$Kbn2Code = isset($KbnCode[1])?$KbnCode[1]:null;
			$Kbn3Code = isset($KbnCode[2])?$KbnCode[2]:null;
			$Kbn4Code = isset($KbnCode[3])?$KbnCode[3]:null;
			$Kbn5Code = isset($KbnCode[4])?$KbnCode[4]:null;
			$sp->bindparam(':Kbn1Code', $Kbn1Code, PDO::PARAM_STR);
			$sp->bindparam(':Kbn2Code', $Kbn2Code, PDO::PARAM_STR);
			$sp->bindparam(':Kbn3Code', $Kbn3Code, PDO::PARAM_STR);
			$sp->bindparam(':Kbn4Code', $Kbn4Code, PDO::PARAM_STR);
			$sp->bindparam(':Kbn5Code', $Kbn5Code, PDO::PARAM_STR);
			
			$sp->bindparam(':Result', $result, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT,PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
			$sp->execute();
			if ($sp->columnCount()>0){
				$PlotDataList = $sp->fetchAll(PDO :: FETCH_OBJ);
			}
			while($sp->nextRowset()){};
			$result = 1;
		} catch (PDOException $e) { 
			//echo $e->getMessage();
			$result = 0;
			$PlotDataList = array();	//プロットデータ情報
			$screenMessage = array('Type'=> 1,'Content' => 'プロットデータの抽出に失敗しました。');
			$this->InsertLog($SystemInfo,0,'プロットデータの抽出に失敗しました：' . $this->GetErrorMessage($e));
		}
		return  $array = array(
								'Result' => $result,
								'ScreenMessage' => $screenMessage,
								'PlotData' => $PlotDataList
							);
	}

    //2020/10/30 表示件数制御
    /**
     * システムの固定の値
     * 戻り値   システムの固定の値
     *          表示件数制御用
     */
    public function GetSystemFixedValue($SystemInfo){
        try {
            $screenMessage = null;
            $fixedCode ='001';
            $db = $this->db;
            $sp = $db->prepare("EXEC [GMAP].[SP_GET_FIXEDCODE] :FixedCode");
            $sp->bindparam(':FixedCode', $fixedCode, PDO::PARAM_STR);
            
            $sp->execute();
            if ($sp->columnCount() > 0){
                $fixedValue = $sp->fetchColumn(); //システム固定値
                $fixedValue = ($fixedValue === false)?'100':$fixedValue;
            }else{
                $fixedValue = '100';    //
            }
            while($sp->nextRowset()){};
        } catch (Exception $e) { 
            $fixedValue = '100';    //システム固定値
			$screenMessage = array('Type'=> 1,'Content' => 'システム固定値の取得に失敗しました。');
            $this->InsertLog($SystemInfo,0,'システム固定値の取得に失敗しました：'. $this->GetErrorMessage($e));
        }
        return  $array = array('FixedValue' => (int)$fixedValue);    
    }
}

