(function() {
    'use strict';
    jQuery(document).ready(function($) {

        // add quicklinks to top of config settings (thanks to @Robin S / @Toutouwai)
        var $links_list = $('#tracy-quick-links .InputfieldContent > ul');
        var $links = $('#ModuleEditForm > .Inputfields > .InputfieldFieldset');
        $links.sort(function(a, b) {
            return $(a).text().toUpperCase().localeCompare($(b).text().toUpperCase());
        })
        $links.each(function() {
            $links_list.append('<li><a class="label" href="#' + $(this).attr('id') + '">' + $(this).children('label').text() + '</a></li>');
        });
        var $quick_links = $('#tracy-quick-links');
        var $config_fields = $quick_links.nextUntil('#wrap_uninstall');
        $quick_links.on('click', 'a', function(event) {
            event.preventDefault();
            if($(this).hasClass('active')) {
                $(this).removeClass('active');
                $config_fields.show();
            } else {
                $quick_links.find('a').removeClass('active');
                $(this).addClass('active');
                $config_fields.hide();
                $($(this).attr('href')).show();
            }
        });



        // loading ACE editor for various config fields
        // modified from https://github.com/ryanburnette/textarea-as-ace-editor
        var init, makeContainer, makeId, match;
        makeId = function() {
            var i, opt, str;
            str = "";
            opt = "abcdefghijklmnopqrstuvwxyz";
            i = 1;
            while (i < 16) {
            str += opt.charAt(Math.floor(Math.random() * opt.length));
            i++;
            }
            return 'editor-' + str;
        };
        makeContainer = function($el) {
            var $nu, id;
            id = makeId();
            $nu = $('<div id="' + id + '"></div>');
            $el.after($nu);
            return $nu;
        };
        match = function($textarea, $editor, editor) {
            var height, id;
            id = $editor.attr('id');
            height = editor.getSession().getScreenLength() * editor.renderer.lineHeight;
            $textarea.val(editor.getValue());
            $('#' + id).css({
            height: height
            });
            $('#' + id + '-section').css({
            height: height
            });
            editor.resize();
        };
        init = function(options) {
            var $container, $editor, $textarea, editor, id;
            $textarea = options.textarea;
            $textarea.css({
            display: 'none'
            });
            $container = makeContainer($textarea);
            id = $container.attr('id');
            editor = ace.edit(id);
            $editor = $('#' + id);
            editor.setTheme(options.theme);

            ace.config.loadModule('ace/ext/language_tools', function () {
                editor.setOptions({
                    enableBasicAutocompletion: true,
                    enableLiveAutocompletion: true,
                    minLines: 5,
                    maxLines: 20
                });
            });

            editor.getSession().setUseWrapMode(true);
            editor.getSession().setMode(options.mode);
            editor.setFontSize(14);
            editor.setShowPrintMargin(false);
            editor.$blockScrolling = Infinity;
            editor.setTheme("ace/theme/tomorrow_night_bright");
            editor.session.setMode({path:"ace/mode/php", inline:options.inline});

            editor.setValue($textarea.val(), -1);
            editor.on('change', function() {
            return match($textarea, $editor, editor);
            });
            match($textarea, $editor, editor);
            $('body').click;
            return editor;
        };
        return $.fn.asAceEditor = function(params) {
            var $t, defaults, options;
            $t = $(this).eq(0);
            if ($t.prop("tagName") !== "TEXTAREA") {
            return false;
            }
            defaults = {
            textarea: $t,
            };
            options = $.extend(defaults, params);
            $t.data("ace-editor", init(options));

            return this;
        };
    });

}).call(this);

window.onload = function () {
    // convert fields to ACE
    $('#Inputfield_customPhpCode').asAceEditor({'inline': true});
    $('#Inputfield_consoleCodePrefix').asAceEditor({'inline': false});
};
