<?php

use Tracy\IBarPanel;

/**
 * Custom PW panel
 */

class MethodsInfoPanel implements IBarPanel {
    public function getTab() {
        return '
        <style>
            .tracy-panel {
                max-height: 90% !important;
                max-width: 95% !important;
                overflow-y: scroll !important;
            }
        </style>
        <span title="Methods info">
            <img style="height: 15px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABJ0lEQVQ4T5VT21HDMBC8dQWUECoAOjAVECogkgpIUgGUYBcgiRKSCnAJpgLcAg1oGWksxmOkDPGMP+61p7vdg6w+7/0mhLAH0IrI/RweSQ5N0/RKqWlZgqXhnOtEZL8GXdokO2PMMft+Aay1I4C7S8WL2Ki1foh2Aqh1JvnZNM02hLAD8LoC77XWB8SZSX5VOqekWg6AW/xj7kFENvO/7tOjNDvJbwAnkhOAyMRT5YVjfAELwYnkaIx5nndUykllNYAU1FrnJdcBLtB31lpvvfctyY/SCJGl6hJJHo0xnbX2rUBhxuurNAJ4VEoNzrlTbYmJxpqQSL5H7YvITQjhBcCuKKTsvEbKcXZjTDq0q49JRJI6/xxTdsyyPZBs83GlbQMDgG59zj89yafh2g81MAAAAABJRU5ErkJggg==" />
            Method Info
        </span>
        ';
    }



    public function getPanel() {

        // panel title
        $out = '
        <h1>Method Info</h1>

        <p style="font-size:16px">Tracy Documentation: <a href="https://doc.nette.org/en/2.3/debugging" target="_blank">https://doc.nette.org/en/2.3/debugging</a></p>
        <br />

        <p style="font-size:16px"><strong>dump($var, $return = FALSE)</strong></p>
        <p>
        TD::dump();<br />
        dump();<br />
        d();<br />
        <br />
        </p>

        <p style="font-size:16px"><strong>barDump($var, $title = NULL, array $options = NULL)</strong></p>
        <p>
        TD::barDump();<br />
        barDump();<br />
        bd();<br />
        <br />
        </p>

        <p style="font-size:16px"><strong>log($message, $priority = ILogger::INFO)</strong></p>
        <p>
        TD::log();<br />
        l();<br />
        <br />
        </p>

        <p style="font-size:16px"><strong>timer($name = NULL)</strong></p>
        <p>
        TD::timer();<br />
        timer();<br />
        t();<br />
        <br />
        </p>

        <p style="font-size:16px"><strong>fireLog($message)</strong></p>
        <p>
        TD::fireLog();<br />
        fireLog();<br />
        fl();<br />
        <br />
        </p>

        ';

        return $out;
    }

}
