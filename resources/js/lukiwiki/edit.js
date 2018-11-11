$('.btn[type="button"]').on('click', function(){
    const name = $(this).attr('name');
    switch (name) {
    }

    console.log(name)
})
/*
// ヒントのウィジット
if ($('#hint').length === 0){
    $body.append('<div id="hint"></div>');

    $('#hint').dialog({
        title:$.i18n('editor','hint'),
        autoOpen:false,
        bgiframe: true,
        width:470,
        show: 'scale',
        hide: 'scale'
    }).html($.i18n('pukiwiki','hint_text1'));
}

// ここから、イベント割り当て
$('.btn[type="button"]').off('click').on('click',function(){
    var ret = '', v = $(this).attr('name');

    switch (v){
        case 'help' :
            $('#hint').dialog('open');
        break;
        case 'br':
            ret = '&br;'+"\n";
        break;
        case 'emoji' :
            $('#emoji').dialog('open');
        break;
        case 'color' :
            $('#color_palette').dialog('open');
        break;
        case 'flush' :
            if (Modernizr.localstorage && confirm($.i18n('pukiwiki','flush_restore')) === true){
                localStorage.removeItem(PAGE);
            }
        break;
        default:
            ret = '&('+v+');';
        break;
    }
    if (ret !== ''){
        $msg.focus();
        if ($msg.getSelection().text === ''){
            $msg.insertAtCaretPos(ret);
        }else{
            $msg.replaceSelection(ret);
        }
    }
    $('*[role="tooltip]').hide();
    return false;
});

$('.replace').off('click').on('click', function(){
    var ret = '', str = $msg.getSelection().text, v = $(this).attr('name');

    if (str === ''){
        alert( $.i18n('pukiwiki', 'select'));
        return false;
    }

    switch (v){
        case 'size' :
            var val = prompt($.i18n('pukiwiki', 'fontsize'), '100%');
            if (!val || !val.match(/\d+/)){
                return;
            }
            ret = '&size(' + val + '){' + str + '};';
        break;
        case 'ncr':
            var i, len;
            for(i = 0, len = str.length; i < len ; i++ ){
                ret += ("&#"+(str.charCodeAt(i))+";");
            }
        break;
        case 'b':	//mikoadded
            ret = "''" + str + "''";
        break;
        case 'i':
            ret = "'''" + str + "'''";
        break;
        case 'u':
            ret = '__' + str + '__';
        break;
        case 's':
            ret = '%%' + str + '%%';
        break;
        case 'code' :
            ret = '@@' + str + '@@';
        break;
        case 'q' :
            ret = '@@@' + str + '@@@';
        break;

        case 'url':
        //	var regex = "^s?https?://[-_.!~*'()a-zA-Z0-9;/?:@&=+$,%#]+$";
            var my_link = prompt( $.i18n('pukiwiki', 'url'), 'http://');
            if (my_link !== null) {
                ret = '[[' + str + '>' + my_link + ']]';
            }
        break;
    }
    $msg.focus().replaceSelection(ret);
    return false;
});
*/