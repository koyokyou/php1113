<div class="modal fade" id="modal-load"></div>
<!-- confirm Message-->
<div class="modal fade" id="modal-confirm">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"></h6>
                <button class="close" aria-label="Close" type="button" data-dismiss="modal">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-message">
                    <table class="no-border">
                        <tr>
                            <td><i class="fa fa-question-circle text-left"></i></td>
                            <td id="modal-message"></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary yes-choice text-center" style="width:80px;" data-dismiss="modal" >はい</button>
                <button type="button" class="btn btn-primary no-choice text-center" style="width:80px;" data-dismiss="modal" >いいえ</button>
            </div>
        </div>
    </div>
</div>
<!-- alert Message-->
<div class="modal fade" id="modal-alert">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"></h6>
                <button type="button" class="close text-white" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                <div class="modal-message">
                    <table class="no-border">
                        <tr>
                            <td><i class="fa fa-times-circle text-danger text-left"></i></td>
                            <td id="modal-message"></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">確認</button>
            </div>
        </div>
    </div>
</div>
<!-- toastr Message -->
<div class="modal fade" id="modal-toastr">
    <div class="modal-dialog" style='position:absolute;right:10px;'>
        <div class="modal-content">
            <div class="modal-header " style='background-color:#516e17'>
                <h6 class="modal-title"></h6>
                <button type="button" class="close text-white" data-dismiss="modal">&nbsp;</button>
            </div>
            <div class="modal-body">
                <div class="modal-message">
                    <table class="no-border">
                        <tr>
                            <td><i class="fa fa-info-circle text-left"></i></td>
                            <td id="modal-message"></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="modal-footer">&nbsp;</div>
        </div>
    </div>
</div>
<script>
     /**
     * test message
     */
    function testMessage(message){
        re = new RegExp('\r\n','g'); 
        message = message.replace(re, '<br>');
        $('#modal-test #modal-message').html(message);
        $('#modal-test').modal('show');
    }

     /**
     * alert message
     */
    function alertMessage(messageObj){
        var type = messageObj.Type;
        var message = messageObj.Content;
        re = new RegExp('\r\n','g'); 
        message = message.replace(re, '<br>');
        switch(type){
            case 3:
                $('#modal-alert i').removeClass();
                $('#modal-alert i').addClass('fa fa-exclamation-triangle text-warning text-left');
                $('#modal-alert #modal-message').html(message);
                $('#modal-alert').modal('show');
                break;
            case 4://情報 - 今トースト表示
                $('#modal-toastr #modal-message').html(message);
                $('#modal-toastr').modal('show');
                setTimeout(function(){$('#modal-toastr').modal('hide');},5000);
                break;
            case 1: //エラー
            default:
                $('#modal-alert i').removeClass();
                $('#modal-alert i').addClass('fa fa-times-circle text-danger text-left');
                $('#modal-alert #modal-message').html(message);
                $('#modal-alert').modal('show');
                break;
        }
    }
    /**
     * confirm message
     */
    function confirmMessage(message,func_yes,func_no){
        re = new RegExp('\r\n','g'); 
        message = message.replace(re, '<br>');
        $('#modal-confirm #modal-message').html(message);
        $('#modal-confirm').modal('show');
        $("#modal-confirm .yes-choice").unbind('click').one("click", function () {
            if (typeof func_yes === "function"){
                func_yes();
            }
        });
        $("#modal-confirm .no-choice").unbind('click').one("click", function () {
            if (typeof func_no === "function"){
                func_no();
            }
        });
    }
</script>

