<aside class="canvi-navbar canvi-menu">
    <div class="navbar-content">
        <!-- title -->
        <div class="navbar-title">
            <button class="canvi-btn canvi-close-btn menu-close" type="button">
                <span class="icon-bar bar1"></span>
                <span class="icon-bar bar2"></span>
                <span class="icon-bar bar3"></span>
            </button>
            <p><lable id='MapPatternName'></lable></p>
        </div>
        <!-- content -->
        <div class="navbar-detail"></div>
        <!-- footer -->
        <div class="navbar-footer">
            <div class="menu-category-content border-0 m-0">
                <table class="menu-category-table">
                    <thead>
                        <tr>
                            <th class="menu-category-header">
                                物件プロット
                            </th>
                            <th class="menu-category-header">
                                ラベル表示
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="menu-category-detail">
                                <div class="form-check px-1">
                                    <label class="form-check-label"><input type="checkbox" id="chkAutoDraw" name="chkAutoDraw">自動再描画</label>
                                </div>
                            </td>
                            <td class="menu-category-detail">
                                <div class="radio">
                                    <label><input type="radio" name="optradio" value = 1>表示する</label>
                                </div>
                                <div class="radio">
                                    <label><input type="radio" name="optradio" value = 0>表示しない</label>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="menu-category-content border-0 px-auto">
                <button type="button" class="btn btn-block btn-flat btn-menu" id="btnPlot">プロット</button>
            </div>
        </div>
    </div>
    <input type="hidden" id="MapPatternID" value = '<?php echo $MapPatternID ?>'>
    <input type="hidden" id="PlotDataID" value = '<?php echo $PlotDataID ?>'>
    <input type="hidden" id="LoginUserID" value = '<?php echo $LoginUserID ?>'>
    <input type="hidden" id="AuthGrupID">
    <input type="hidden" id="PlotAllCount">
    <input type="hidden" id="PlotNgCount">
    <input type="hidden" id="PlotWarningCount">
    <input type="hidden" id="StartLat">
    <input type="hidden" id="StartLon">
    <input type="hidden" id="FixedValue">
</aside>

<script>
    /**
     *  初期化
     */
    var leftmenu;
    $(function () {
        //メニュー初期化
        leftmenu = new Canvi({
            content: ".main-content",
            navbar: ".canvi-menu",
            openButton: ".menu-open",
            closeButton: ".menu-close",
            position: "left",
            speed: "0.2s",
            width: "320px",
            pushContent:false
        });

        <?php if($ClientIsPC == true) {?>
            leftmenu.toggle();
        <?php } ?>

        //スクロールバー
        $(".canvi-menu .navbar-detail").niceScroll({
            cursorwidth:6,
            cursoropacitymin:0,
            cursorcolor:'#a3a3a3',
            cursorborder:'none',
            cursorborderradius:0,
            autohidemode:'leave'
        });
        
        // 出発地緯度/経度の取得
        doGetCurrentLocation();
    });

    /**
     *  出発地緯度/経度の取得
     */
    function doGetCurrentLocation(){
        var isPC = <?php echo json_encode($ClientIsPC) ?>;
        if (navigator.geolocation && isPC == false){
            navigator.geolocation.getCurrentPosition(function(position){
                $('#StartLat').val(position.coords.latitude);
                $('#StartLon').val(position.coords.longitude);
                doInitFormItems();
            },function(position){
                doInitFormItems();
            });
        }else{
            doInitFormItems();
        }
    }

     /**
     *  画面初期表示
     */
    function doInitFormItems(){
        $.ajax({
            type: "Post",
            url: './main.php?c=main&a=Init',
            data: {
                MapPatternID:$('#MapPatternID').val(),
                PlotDataID:$('#PlotDataID').val(),
                LoginUserID:$('#LoginUserID').val(),
                StartLat:$('#StartLat').val(),
                StartLon:$('#StartLon').val()
            },
            async: true,
            cache: false,
            beforeSend: function () {
                $('#modal-load').addClass('show');
            },
            success: function (data) {
                $('#modal-load').removeClass('show');
                var values = JSON.parse(data);
                if (values["Result"] != 1 || values["ScreenMessage"] != null){
                    alertMessage(values["ScreenMessage"]);
                }
                InitFormItems(values);
                values=null;
                data= null;
            },
            complete: function () {
                
            },
            error: function (jqXHR, status, errorThrown) {
                $('#modal-load').removeClass('show');
                InitFormItems(showOtherMessage(jqXHR, status, errorThrown));
            }
        });
    }
    /**
     *  項目初期化
     */
    function InitFormItems(values){
        var result = values["Result"];
        if (result == 1){
            //項目内容初期化
            InitFormItemsContent(values);
            // 項目イベント初期化
            InitFormItemsEvent();

            //GoogleMap 描画
            var arrPlotData = values["PlotData"];
            var mapPattern = values["MapPattern"];
            RedrawGoogleMap(mapPattern,arrPlotData,1);
        }else{
            $("#chkAutoDraw").parent().html('&nbsp;');
            $("input:radio[name*=optradio]").parent().html('&nbsp;');
            $("#btnPlot").css('visibility','hidden');
        }
    }

    /**
     *  項目内容初期化
     */
    function InitFormItemsContent(values){
        
        var mapPattern = values["MapPattern"];
        var plotDataInfo= values["PlotDataInfo"];
        var isAutoDraw = (values["IsAutoDraw"] == 1)? true:false;
        var kbnItemList = values["KbnItemList"];

        $("#MapPatternID").val(values["MapPatternID"]);
        $("#PlotDataID").val(values["PlotDataID"]);
        $("#LoginUserID").val(values["LoginUserID"]);
        $("#AuthGrupID").val(values["AuthGrupID"]);
        $("#PlotAllCount").val(plotDataInfo["PlotAllCount"]);
        $("#PlotNgCount").val(plotDataInfo["ErrorAllCount"]);
        $("#PlotWarningCount").val(plotDataInfo["warningAllCount"]);
        $("#StartLat").val(mapPattern["START_LAT"]);
        $("#StartLon").val(mapPattern["START_LNG"]);
        $("#FixedValue").val(values["FixedValue"]);

        $("#MapPatternName").text(mapPattern["MAP_PATTERN_NAME"]);
        $("#chkAutoDraw").attr('checked',isAutoDraw);
        $("input:radio[name*=optradio]").each(function(){
           if ($(this).val() == mapPattern["LABEL_ONOFF"]){
                $(this).attr('checked',true);
           }
        });

        CreateKbnItem(kbnItemList);
    }
    /**
     *  区分項目の作成
     */
    function CreateKbnItem(KbnItemList){
        if(KbnItemList == null) return;
        for(var indexGroup in KbnItemList) {
            if (indexGroup > 5) break;
            var KbnItem = KbnItemList[indexGroup];

            var divContent = '';
            divContent +='<div class="menu-category-content">';
            divContent +='  <div class="menu-category-header">';
            divContent += KbnItem[0]["ITEM_TITLENAME"];
            divContent +='  </div>';
            divContent +='  <div class="menu-category-body">';
            divContent +='      <div class="w-auto text-left">';
            
            for (var indexItem in KbnItem){
                var childItem = KbnItem[indexItem];
                divContent +='      <div class="form-check px-0">';
                divContent +='          <label class="form-check-label">';
                divContent +='              <input class="mr-1" type="checkbox"';
                divContent +='                  id="'+ ("chkKbnItem").concat(indexGroup,"_",indexItem) + '"';
                divContent +='                  name="'+ ("chkKbnItem").concat(indexGroup) + '"';
                divContent +='                  value="' + childItem["RET_ITEM_CODE"]  + '" checked>' + childItem["ITEM_VALUE"];
                divContent +='          </label>';
                divContent +='      </div>';
            }
            divContent +='      </div>';
            divContent +='  </div>';
            divContent +='</div>';
            $('.navbar-detail').append(divContent);
        }
    }

    /**
     *  項目イベント初期化
     */
    function InitFormItemsEvent(){
        //区分項目の変更のイベント
        $("input:checkbox[name*=chkKbnItem]").change(function() {
            doChangeKbnItem();
         });

        //ラベル表示/表示しないの変更のイベント
        $("input:radio[name=optradio]").change(function() {
            doChangeLabelDraw();
         });

        //自動再描画の変更のイベント
        $("input:checkbox[name=chkAutoDraw]").change(function() {
            doChangeAutoDraw();
         });

        //プロットのイベント
        $("#btnPlot").bind("click",function(){
            doGetPlotData(1);
        });
    }
    /**
     *  区分項目の変更(イベント)
     */
    function doChangeKbnItem(){
        var isAutoRedraw = $('#chkAutoDraw').is(':checked');
        if (isAutoRedraw == true){
            doGetPlotData(1);
        }
    }
    /**
     *  ラベル表示/表示しないの変更(イベント)
     */
    function doChangeLabelDraw(){
        var isAutoRedraw = $('#chkAutoDraw').is(':checked');
        if (isAutoRedraw == true){
            doGetPlotData(0);
        }
    }

    /**
     *  自動再描画の変更(イベント)
     */
    function doChangeAutoDraw(){
        $.ajax({
            type: "Post",
            url: './main.php?c=main&a=SetSetting',
            data: {
                MapPatternID:$('#MapPatternID').val(),
                PlotDataID:$('#PlotDataID').val(),
                LoginUserID:$('#LoginUserID').val(),
                AuthGrupID:$('#AuthGrupID').val(),
                PlotAllCount:$('#PlotAllCount').val(),
                PlotNgCount:$('#PlotNgCount').val(),
                PlotWarningCount:$('#PlotWarningCount').val(),
                StartLat:$('#StartLat').val(),
                StartLon:$('#StartLon').val(),
                SettingValue: $('#chkAutoDraw').is(':checked')?1:0
            },
            async: true,
            cache: false,
            beforeSend: function () {
                $('#modal-load').addClass('show');
            },
            success: function (data) {
                $('#modal-load').removeClass('show');
                var values = JSON.parse(data);
                if (values["Result"] != 1){
                    alertMessage(values["ScreenMessage"]);
                }
            },
            complete: function () {
           
            },
            error: function (jqXHR, status, errorThrown) {
                $('#modal-load').removeClass('show');
                showOtherMessage(jqXHR, status, errorThrown)
            }
        });
    }


    /**
     *  プロット(イベント)
     */
    function doGetPlotData(isAlertConfirm){
        GetPlotData(isAlertConfirm);
    }

    /**
     *  プロット
     */
    function GetPlotData(isAlertConfirm){
        $.ajax({
            type: "Post",
            url: './main.php?c=main&a=GetPlotData',
            data: {
                MapPatternID:$('#MapPatternID').val(),
                PlotDataID:$('#PlotDataID').val(),
                LoginUserID:$('#LoginUserID').val(),
                AuthGrupID:$('#AuthGrupID').val(),
                PlotAllCount:$('#PlotAllCount').val(),
                PlotNgCount:$('#PlotNgCount').val(),
                PlotWarningCount:$('#PlotWarningCount').val(),
                StartLat:$('#StartLat').val(),
                StartLon:$('#StartLon').val(),
                KbnCode: GetKbnItem()
            },
            async: true,
            cache: false,
            beforeSend: function () {
                $('#modal-load').addClass('show');
            },
            success: function (data) {
                $('#modal-load').removeClass('show');
                var values = JSON.parse(data);
                var arrPlotData = [];
                var mapPattern = [];
                if (values["Result"] == 1){
                    arrPlotData = values["PlotData"];
                    mapPattern = values["MapPattern"];
                }else{
                    arrPlotData = [];
                    mapPattern = [];
                    alertMessage(values["ScreenMessage"]);
                }
                RedrawGoogleMap(mapPattern,arrPlotData,isAlertConfirm);
                values=null;
                data=null;
            },
            complete: function () {
           
            },
            error: function (jqXHR, status, errorThrown) {
                $('#modal-load').removeClass('show');
                showOtherMessage(jqXHR, status, errorThrown)
            }
        });
    }


     /**
     *  GoogleMap 描画
     */
    function RedrawGoogleMap(MapPattern,PlotData,isAlertConfirm){
        var arrMarkerData = [];
        PlotData.forEach(function(val,index,arr){
            arrMarkerData.push({name: val['LABEL_NAME1'],
                                infobox: val['FUKIDASHI'],
                                lat: Number(val['PLOT_LAT']),
                                lng: Number(val['PLOT_LNG']),
                                icon: val['ICON_NAME'],
                                label_backcolor: val['LABEL_BACKCOLOR'],
                                label_framecolor: val['LABEL_FRAMECOLOR'],
                                size_h: val['SIZE_H'],
                                size_w: val['SIZE_W'],
                                circle_radius: val['CIRCLE_RADIUS'],
                                radius:100
                            });
        });
        var optionData =[];
        optionData['IsLabelDraw'] = $("input[name='optradio']:checked").val();
        optionData['IsAlertConfirm'] = isAlertConfirm;
        optionData['FixedValue'] = $("#FixedValue").val();
        initialize(MapPattern,arrMarkerData,optionData);
    }

    /**
     * 画面の選択された区分項目の取得
     * 戻り値　array(size = 5)
     */
    function GetKbnItem(){
        var arrRet = new Array();
        //区分項目のグループの取得
        var arrGroup = [];
        $("input:checkbox[name*=chkKbnItem]").each(function(){
           arrGroup.push($(this).attr('name'));
        });
        $.unique(arrGroup.sort());

        //チェックされた区分項目の取得
        var arrChecked = [];
        for(var i = 0; i < arrGroup.length; i++){
            var name = arrGroup[i];
            var value = $("input:checkbox[name*="+ name +"]:checked").map(function(){return $(this).val();}).get().join(",");
            arrChecked.push({name:name,value:value});
        }

        //戻り値の作成(最大5つまで)
        for(var i = 0; i < arrChecked.length; i++){
            if (i >= 5) break;
            arrRet.push(arrChecked[i].value);
        }
        return arrRet;
    }

    /**
     * Ajaxエラーの表示
     */
    function showOtherMessage(jqXHR, textStatus, errorThrown) {
        var errorText = "";
        switch (jqXHR.status) {
            case 0:     //通信障害
                errorText = "通信状態が不安定、又は接続できません。少し時間をあけて再試行してください。";
                break;
            default:    //そのた
                errorText = jqXHR.statusText;
                break;
        }
        var values = [];
        values["Result"] = 0;
        values["ScreenMessage"] = {'Type':1,'Content':errorText};
        alertMessage(values["ScreenMessage"]);
        return values;
    }
</script>