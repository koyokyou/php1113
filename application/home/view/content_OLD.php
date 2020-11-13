<div id="googleMap" style="width:100%;height:100%;"></div>
  <script type="text/javascript">
    let map;
    let marker = [];
    let markerCircle = [];
    //吹き出しの表示/非表示を制御する
    let infoWindow = [];
    const infoWindowOpen = [];
    //map初期化
    function initialize(mapPattern,markerData,optionData){
      let markerDataNum = markerData.length;

      if (map == undefined){
        optionData['IsRedraw'] = 0;
        //中心点のデータをセットする
        let startLat = !isEmpty(mapPattern)? Number(mapPattern['START_LAT']):0;
        let startLng = !isEmpty(mapPattern)? Number(mapPattern['START_LNG']):0;
        let zoom = Number(mapPattern['ZOOM_SCALE']);
        console.log('zoom=',zoom);
        let mapLatLng = null;
        let mapProp = null;
        //起動時縮尺（zoom）が設定済み（NULL、０以外）の場合、出発点を中心として起動時縮尺でマップを表示する
        if(zoom != 0){
          mapLatLng = new google.maps.LatLng({lat: startLat, lng: startLng});
          mapProp = {
            center: mapLatLng,
            zoom: zoom,
            streetViewControl: false,
            gestureHandling: 'greedy',
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapTypeControlOptions: {
              position:google.maps.ControlPosition.TOP_CENTER
            }
          };
        //起動時縮尺（zoom）が未設定（NULL、０）の場合、マーカーを全プロットするようにズームレベルを自動調整する  
        }else{
          //中心点のデータをセットする
          let maxPlotLat = Math.max.apply(Math, markerData.map(function(item) {return item.lat}));
          let minPlotLat = Math.min.apply(Math, markerData.map(function(item) {return item.lat}));
          let maxPlotLng = Math.max.apply(Math, markerData.map(function(item) {return item.lng}));
          let minPlotLng = Math.min.apply(Math, markerData.map(function(item) {return item.lng}));
          let boundPlotLat = markerDataNum >0? parseFloat((maxPlotLat+minPlotLat)/2):startLat ; 
          let boundPlotLng = markerDataNum >0? parseFloat((maxPlotLng+minPlotLng)/2):startLng ;
          
          if(zoom==0) zoom=15;
          mapLatLng = new google.maps.LatLng({lat: boundPlotLat , lng: boundPlotLng});
          mapProp = {
            center: mapLatLng,
            zoom: zoom,
            streetViewControl: false,
            gestureHandling: 'greedy',
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapTypeControlOptions: {
              position:google.maps.ControlPosition.TOP_CENTER
            }
          };
        }
        //ロードマップ
        map = new google.maps.Map(document.getElementById("googleMap"),mapProp);
      }else{
        optionData['IsRedraw'] = 1;
        //マーカーをクリアする
        if (marker) {
          for (i in marker) {
            marker[i].setMap(null);
          }
        }
        //円をクリアする
        if (markerCircle) {
          for (i in markerCircle) {
            markerCircle[i].setMap(null);
          }
        }
      }
      //プロットする最大件数の制御
      var funcYes=function(){
          allMarkerShow(mapPattern,markerData,optionData,1);
      };
      var funcNo=function(){
          allMarkerShow(mapPattern,markerData,optionData,0);
      };

      if(markerDataNum > optionData['FixedValue'] && optionData['IsAlertConfirm'] == 1) {
          let message = "「プロットするデータが最大件数を超えています。」<br/>「表示に時間がかかる可能性がありますが、このまま表示しますか？」<br/>「いいえの場合は、区分条件を変更してから再プロットしてください。」";
          confirmMessage(message,funcYes,funcNo); 
      }else{
          allMarkerShow(mapPattern,markerData,optionData,1)
      }
    }


    //マーカー表示のイベント
    function allMarkerShow(mapPattern,markerData,optionData,isMarkerDisplay) {
      let bounds = new google.maps.LatLngBounds();
      let zoom = Number(mapPattern['ZOOM_SCALE']);
      if(isMarkerDisplay == 1) {
        for (let i = 0; i < markerData.length; i++) {
            //新しいマーカーをセットする
            markerLatLng = new google.maps.LatLng({lat: markerData[i]['lat'], lng: markerData[i]['lng']});
            //マーカーの位置をセットする
            marker[i] = new MarkerWithLabel({
            position: markerLatLng,
            map: map,
            //アイコンをセットする
            icon: {
              url: '../icon/' + markerData[i]['icon'],
              scaledSize:new google.maps.Size(markerData[i]['size_w'],markerData[i]['size_h']),
              labelOrigin: new google.maps.Point(10, 10)
            },
            //ラベルをセットする
            labelContent: markerLabel(mapPattern,markerData[i],),
            labelAnchor: new google.maps.Point(5, 0),
            labelClass: (optionData['IsLabelDraw'] == 1)?mapPattern['LABEL_CSS']:'labelsoff',
            labelStyle: {"border-color":`${markerData[i]['label_framecolor']}`,"background":`${markerData[i]['label_backcolor']}`},
            visible: true,
            animation: google.maps.Animation.DROP
          });

          //自動調整：現在の緯度位置を含む
          if(zoom==0 && optionData['IsRedraw'] == 0) bounds.extend(markerLatLng);
          //円を描画する
          if (Number(markerData[i]['circle_radius'])==0){
          }else{
            markerCircle[i] = new google.maps.Circle({
              strokeColor: mapPattern['STROKE_COLOR'],
              strokeOpacity: mapPattern['STROKE_OPACITY'],
              strokeWeight: mapPattern['STROKE_WEIGHT'],
              fillColor: mapPattern['FILL_COLOR'],
              fillOpacity: mapPattern['FILL_OPACITY'],
              map: map,
              center:markerLatLng,
              radius: Number(markerData[i]['circle_radius'])
            });
          }

          //マーカーの情報内容を設定する
          infoWindow[i] = new google.maps.InfoWindow({
            content: '<div>' + markerData[i]['infobox'] + '</div>'
          });

          //自動調整：すべてのマーカーを含める
          if(zoom==0 && optionData['IsRedraw'] == 0) map.fitBounds(bounds);

          infoWindowOpen[i]=0;
          //マーカクリックイベントを聞く
          markerEvent(i,markerData);
        }
      }

    }

     //マーカーの内容を表示する
    function markerEvent(i,markerData) {
      marker[i].addListener('click', function() {
        if (markerData[i]['infobox']!==''){
          if (infoWindowOpen[i]==0 ){
              //吹き出しを表示していない場合は「表示」
              infoWindow[i].open(map, marker[i]);
              infoWindowOpen[i]=1;
          }else{
              //吹き出しを表示している場合は「非表示」
              infoWindow[i].close();
              infoWindowOpen[i]=0;
          }
          infoWindow[i].addListener( "closeclick", function() {
              infoWindowOpen[i]=0;
          } ) ;   
        }
      });
    } 

    //案内図のイベントの文字の作成
    function markerLabel(mapPattern, markerDataItem){
      var labelContent = markerDataItem['name'];
      var directions = !isEmpty(mapPattern)? Number(mapPattern['DIRECTIONS']):0;//0:徒歩／1:車
      var lat = Number(markerDataItem['lat']);
      var lng = Number(markerDataItem['lng']);

      if(mapPattern['ANNAI_ONOFF'] == 1){
        var funcName = 'GotoRoutePage' +'('+ lat +',' + lng + ',' + directions + ')';
        ret = '<div onclick="'+ funcName +'">'+ labelContent + '</div>';
        return ret;
      }else{
        ret = labelContent;
        return ret;
      }
    }

    //案内図のイベント
    function GotoRoutePage(destlat,destlng,directions){
      
      var curLat = $('#StartLat').val();
      var curLng = $('#StartLon').val();
      var travelMode;  
      switch(directions){
        case 0:
          travelMode = 'walking';
          break;
        case 1:
          travelMode = 'driving';
          break;
        case 2:
          travelMode = 'transit';
          break;
        case 3:
          travelMode = 'bicycling';
          break;
        default:
          travelMode = '';
          break;
      }

      var url = 'https://www.google.com/maps/dir/?api=1';
      url += '&origin=' + curLat + ',' + curLng; //出発地
      url += '&destination=' + destlat + ',' + destlng; //目的地
      url += '&travelmode=' + travelMode; //経路検索手段

      window.open(url);   
      
      if(window.event && window.event.stopPropagation){
              window.event.stopPropagation();
          }else{
        window.event.cancelBubble =true;
          }
    }

    function isEmpty(obj) {
        return  !Object.getOwnPropertyNames(obj).length &&  !Object.getOwnPropertySymbols(obj).length
    }


</script>