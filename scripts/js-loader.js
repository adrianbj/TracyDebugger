if(!tracyJSLoader) {
    var tracyJSLoader = {
        load: function(src, callback) {
            var script = document.createElement("script"),
                    loaded;
            script.setAttribute("src", src);
            if(callback) {
                script.onreadystatechange = script.onload = function() {
                    if(!loaded) {
                        callback();
                    }
                    loaded = true;
                };
            }
            document.getElementsByTagName("head")[0].appendChild(script);
        }
    };
}