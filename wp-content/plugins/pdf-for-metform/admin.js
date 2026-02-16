jQuery(document).ready(function($){
    $('.pdf4m .install').click(function(e){
        $(this).attr('disabled', true);
        $('.pdf4m .progress-bar').css('display', 'block');
        $('.pdf4m .progress-bar-fill').css('width', '0');
        var nonce = $(this).data('nonce');

        $.ajax({
            url: pdf4metform.ajax_url,
            method: 'POST',
            data: {
                action: 'pdf4metform_download_action',
                nonce: nonce
            },
            success: function(data){
                $('.pdf4m .list-fonts').css('display', 'block');
                var data = JSON.parse( data );
                download_font(data[0], 0, data);
            }
        });

        function download_font( font, i, font_list ){
            $.ajax({
                url: pdf4metform.ajax_url,
                method: 'POST',
                data: {
                    action: 'pdf4metform_download_font',
                    font: font, 
                    nonce: nonce
                },
                success:function(data){
                    var fonts = $('.pdf4m .list-fonts').val();
                    $('.pdf4m .list-fonts').val( fonts + '\n' + font );
                    var width = (i/(font_list.length - 1)) * 100;
                    $('.pdf4m .progress-bar-fill').css('width', width+'%');
                    if( i < font_list.length - 1 ){
                        var next = i + 1;
                        download_font( font_list[next], next, font_list )
                    }
                    if( i >= font_list.length - 1 ){
                        window.location = '?page=pdf-for-metform-page';
                    }
                    return true
                }
            });
        }
    });
});


 