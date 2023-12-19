jQuery(document).ready(function ($) {
    $('select[name=action]').append('<option value="delshort">Remove Shortlink</option>');
    $('select[name=action2]').append('<option value="delshort">Remove Shortlink</option>');


    function do_ajaxshort(pid) {
        var post = {};
        post['action'] = 'urlshortener_act';
        post['pid'] = pid;
        post['_ajax_nonce'] = nonce;
        $.ajaxq('urlyarshortenurl', {
            type: 'POST',
            url: aaurl,
            data: post,
            success: function (data) {
                if (data == '-1') {
                } else {
                    $('#post-' + pid).find('span.shortlink').hide();
                    $('#page-' + pid).find('span.shortlink').hide();
                }
            },
            error: function (data) {
            }
        });
    }

    function do_delshort() {
        $('table input:checkbox').each(function () {
            if ($(this).is(':checked')) {
                var sid = $(this).val();
                do_ajaxshort(sid);
            }
        });
    }


    $('#doaction').click(function () {
        var actval = $('select[name=action]').val();
        if (actval == 'delshort') {
            do_delshort();
            return false;
        }
    });
    $('#doaction2').click(function () {
        var actval = $('select[name=action2]').val();
        if (actval == 'delshort') {
            do_delshort();
            return false;
        }
    });


});