(function() {
    'use strict';

    function getNextSiblings(el, untilId) {
        var siblings = [];
        var sibling = el.nextElementSibling;
        while (sibling) {
            if (sibling.id === untilId) break;
            siblings.push(sibling);
            sibling = sibling.nextElementSibling;
        }
        return siblings;
    }

    function makeId() {
        var str = "";
        var opt = "abcdefghijklmnopqrstuvwxyz";
        for (var i = 1; i < 16; i++) {
            str += opt.charAt(Math.floor(Math.random() * opt.length));
        }
        return 'editor-' + str;
    }

    function makeContainer(el) {
        var id = makeId();
        var container = document.createElement('div');
        container.id = id;
        el.after(container);
        return container;
    }

    function matchEditor(textarea, editorEl, editor) {
        var id = editorEl.id;
        var height = editor.getSession().getScreenLength() * editor.renderer.lineHeight;
        textarea.value = editor.getValue();
        document.getElementById(id).style.height = height + 'px';
        var sectionEl = document.getElementById(id + '-section');
        if (sectionEl) sectionEl.style.height = height + 'px';
        editor.resize();
    }

    function initAceEditor(options) {
        var textarea = options.textarea;
        textarea.style.display = 'none';
        var container = makeContainer(textarea);
        var id = container.id;
        var editor = ace.edit(id);
        var editorEl = document.getElementById(id);
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

        editor.setValue(textarea.value, -1);
        editor.on('change', function() {
            return matchEditor(textarea, editorEl, editor);
        });
        matchEditor(textarea, editorEl, editor);
        return editor;
    }

    function asAceEditor(selector, params) {
        var textarea = document.querySelector(selector);
        if (!textarea || textarea.tagName !== 'TEXTAREA') return false;
        var options = Object.assign({ textarea: textarea }, params);
        textarea.dataset.aceEditor = true;
        initAceEditor(options);
    }

    document.addEventListener('DOMContentLoaded', function() {

        // add quicklinks to top of config settings (thanks to @Robin S / @Toutouwai)
        var linksList = document.querySelector('#tracy-quick-links .InputfieldContent > ul');
        var links = Array.from(document.querySelectorAll('#ModuleEditForm > .Inputfields > .InputfieldFieldset'));
        links.sort(function(a, b) {
            return a.textContent.toUpperCase().localeCompare(b.textContent.toUpperCase());
        });
        links.forEach(function(link) {
            var label = link.querySelector('label');
            var li = document.createElement('li');
            li.innerHTML = '<a class="label" href="#' + link.id + '">' + (label ? label.textContent : '') + '</a>';
            linksList.appendChild(li);
        });
        var quickLinks = document.getElementById('tracy-quick-links');
        var configFields = getNextSiblings(quickLinks, 'wrap_uninstall');
        quickLinks.addEventListener('click', function(event) {
            var target = event.target.closest('a');
            if (!target) return;
            event.preventDefault();
            if (target.classList.contains('active')) {
                target.classList.remove('active');
                configFields.forEach(function(el) { el.style.display = ''; });
            } else {
                quickLinks.querySelectorAll('a').forEach(function(a) { a.classList.remove('active'); });
                target.classList.add('active');
                configFields.forEach(function(el) { el.style.display = 'none'; });
                var targetEl = document.querySelector(target.getAttribute('href'));
                if (targetEl) targetEl.style.display = '';
            }
        });
    });

    window.addEventListener('load', function () {
        asAceEditor('#Inputfield_customPhpCode', {inline: true});
        asAceEditor('#Inputfield_consoleCodePrefix', {inline: false});
    });

}).call(this);
