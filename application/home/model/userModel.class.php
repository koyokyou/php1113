<?php
/**
 * User info model
 */
class userModel{
    public $LoginUserID;    //ユーザーID
    public $MapPatternID;  //画面構成ID
    public $PlotDataID;     //プロットデータID
    public $AuthGrupID;     //適用参照権限グループ
    public $ClientMedia;    //利用媒体
    public $ClientIPAddr;   //クライアントIPアドレス
    public $ClientIPTest_HTTP_CLIENT_IP;    //クライアントIPアドレス(テスト用)
    public $ClientIPTest_HTTP_X_FORWARDED_FOR;//クライアントIPアドレス(テスト用)
    public $ClientIPTest_REMOTE_ADDR;       //クライアントIPアドレス(テスト用)

    public $ClientIsPC;     //ture:PC  false: PC以外

    public $MapPattern;     //画面構成情報
                            //MAP_PATTERN_NAME  画面構成名
                            //SYSTEM_ID システム識別子
                            //VIEW_NAME プロットデータVIEW名
                            //START_LAT 出発地緯度
                            //START_LNG 出発地経度
                            //ZOOM_SCALE 起動時縮尺
                            //LABEL_ONOFF ラベル表示ON/OFF
                            //ANNAI_ONOFF 案内図表示ON/OFF
                            //LABEL_CSS ラベルCSS名
                            //CIRCLE_CSS 円CSS名
                            //STROKE_COLOR 線の色
                            //STROKE_WEIGHT 線の太さ
                            //STROKE_OPACITY 線の不透明度
                            //FILL_COLOR 塗りつぶし色
                            //FILL_OPACITY 塗りつぶし不透明度
                            //DIRECTIONS 経路検索手段
    public $PlotDataInfo;   //プロットデータ情報   
                            //PlotAllCount 取込み総件(全レコード数)   
                            //ErrorAllCount 不正なデータ件数  
                            //warningAllCount 不備なデーア件数

   

	/**
	 * 初期化
	 */
	public function __construct(){
        $this->MapPattern = array(
                                    'MAP_PATTERN_NAME'=>null,
                                    'SYSTEM_ID'=>null,
                                    'VIEW_NAME'=>null,
                                    'START_LAT'=>0,
                                    'START_LNG'=>0,
                                    'ZOOM_SCALE'=>null,
                                    'LABEL_ONOFF'=>null,
                                    'ANNAI_ONOFF'=>null,
                                    'LABEL_CSS'=>null,
                                    'CIRCLE_CSS'=>null,
                                    'STROKE_COLOR'=>null,
                                    'STROKE_WEIGHT'=>null,
                                    'STROKE_OPACITY'=>null,
                                    'FILL_COLOR'=>null,
                                    'FILL_OPACITY'=>null,
                                    'DIRECTIONS'=>null
                                );
        $this->PlotDataInfo = array(
                                    'PlotAllCount'=>0,
                                    'ErrorAllCount'=>0,
                                    'warningAllCount'=>0
                                );
	}

    public function InitMapPattern(){
        $this->MapPattern = array(
            'MAP_PATTERN_NAME'=>null,
            'SYSTEM_ID'=>null,
            'VIEW_NAME'=>null,
            'START_LAT'=>null,
            'START_LNG'=>null,
            'ZOOM_SCALE'=>null,
            'LABEL_ONOFF'=>null,
            'ANNAI_ONOFF'=>null,
            'LABEL_CSS'=>null,
            'CIRCLE_CSS'=>null,
            'STROKE_COLOR'=>null,
            'STROKE_WEIGHT'=>null,
            'STROKE_OPACITY'=>null,
            'FILL_COLOR'=>null,
            'FILL_OPACITY'=>null,
            'DIRECTIONS'=>null
        );
    }

    public function InitPlotDataInfo(){
        $this->PlotDataInfo = array(
            'PlotAllCount'=>0,
            'ErrorAllCount'=>0,
            'warningAllCount'=>0
        );
    }

}

