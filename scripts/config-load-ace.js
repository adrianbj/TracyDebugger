window.onload = function () {
    var tse = ace.edit("Inputfield_customPhpCode");
    tse.container.style.lineHeight = '23px';
    tse.setFontSize(13);
    tse.setShowPrintMargin(false);
    tse.$blockScrolling = Infinity;
    tse.setTheme("ace/theme/tomorrow_night");
    tse.session.setMode({path:"ace/mode/php", inline:true});
    ace.config.loadModule('ace/ext/language_tools', function () {
        tse.setOptions({
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true,
            minLines: 5,
            maxLines: 20
        });
    });
};