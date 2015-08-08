$(document).ready(function () {
    init();
});

function init() {
    $('input[type=text]#long-url').keypress(function(event){        
        var keycode = (event.keyCode ? event.keyCode : event.which);
        event.stopPropagation();
        if(keycode == '13'){ //enter
            make_short_url();
        }        
    });
}

function make_short_url() {
    var long_url = $('input[name=long_url]').val();
    $('#error').html('');
    $('input#short-url').val('');
    
    $.get('/ajax_make_short_url', {
        long_url: long_url
    }, function(data) {        
        var decoded = JSON.parse(data);        
        if (decoded.error.length > 0) {            
            var txt = '';
            for (var i=0; i<decoded.error.length; i++) {
                txt += decoded.error[i] + '<br />';
            }
            $('#error').html(txt);
            return;
        }
        var short_url = decoded.short_url;
        var $input = $('input#short-url');
        $input.val(short_url);
        $input[0].setSelectionRange(0, $input.val().length);         
    });
}
