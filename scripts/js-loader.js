if(!tracyJSLoader) {
    var tracyJSLoader = {
        // the nonce of the Tracy bar script this file is bundled into
        nonce: document.currentScript ? (document.currentScript.nonce || document.currentScript.getAttribute('nonce')) : '',
        load: function(src, callback) {
            var script = document.createElement("script"),
                    loaded;
            script.setAttribute("src", src);
            if(callback) {
                script.onreadystatechange = script.onload = function() {
                    if(!loaded) {
                        tracyJSLoader.nonceAceStyles();
                        callback();
                    }
                    loaded = true;
                };
            }
            document.getElementsByTagName("head")[0].appendChild(script);
        },
        // Ace adds its CSS in <style> elements made with its dom.createElement(), which a CSP
        // without 'unsafe-inline' blocks; give them the nonce before any editor is created
        nonceAceStyles: function() {
            if(typeof ace === "undefined" || !tracyJSLoader.nonce || ace.tracyNonceStyles) return;
            var dom = ace.require("ace/lib/dom");
            var createElement = dom.createElement;
            dom.createElement = function(tag) {
                var el = createElement.apply(this, arguments);
                if(tag === "style") el.setAttribute("nonce", tracyJSLoader.nonce);
                return el;
            };
            ace.tracyNonceStyles = true;
        }
    };
}
