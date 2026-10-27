if(!tracyJSLoader) {
    var tracyJSLoader = {
        // the nonce of the Tracy bar script this file is bundled into
        nonce: document.currentScript ? (document.currentScript.nonce || document.currentScript.getAttribute('nonce')) : '',
        load: function(src, callback) {
            var script = document.createElement("script"),
                    loaded;
            script.src = tracyTrusted.scriptURL(src);
            if(callback) {
                script.onreadystatechange = script.onload = function() {
                    if(!loaded) {
                        tracyJSLoader.versionAceModules(src);
                        tracyJSLoader.nonceAceStyles();
                        callback();
                    }
                    loaded = true;
                };
            }
            document.getElementsByTagName("head")[0].appendChild(script);
        },
        // Ace loads its modes, themes, extensions, and workers itself: version their URLs the way ace.js was
        // (ace.1792537113.js or ace.js?v=5.1.6, see TracyDebugger::assetUrl()), so a CDN serves an update's copies
        versionAceModules: function(src) {
            var version = /\/ace(\.\d{10,})?\.js(\?[^#]*)?$/.exec(src);
            if(typeof ace === "undefined" || !version) return;
            ace.config.set("suffix", (version[1] || "") + ".js" + (version[2] || ""));
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
