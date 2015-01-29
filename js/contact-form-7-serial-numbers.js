jQuery(function($) {

    $(':button[id^=CountModify]').on("click", function(){
        // 値の取得
        var $id = $(this).data('id');
        var $count = $('#nklab_wpcf7sn_count_' + $id).val();

        // フォームへのセット
        $('#frmCountModify' + $id + ' [id^=nklab_wpcf7sn_count_]').val($count);

        // submit
        $('#frmCountModify' + $id).submit();
    });

});


