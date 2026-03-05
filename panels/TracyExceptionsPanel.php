<?php namespace ProcessWire;

use Tracy\Debugger;

class TracyExceptionsPanel extends BasePanel {

    private $icon;
    private $iconColor;
    private $tracyExceptionFile;
    private $filesArray = array();
    private $newFiles = array();
    private $newFilesMap = array();
    private $needsUtf8 = null;

    private function needsUtf8Convert() {
        if($this->needsUtf8 === null) {
            $this->needsUtf8 = PHP_VERSION_ID < 70100
                && extension_loaded('mbstring')
                && function_exists('iconv');
        }
        return $this->needsUtf8;
    }

    public function getTab() {

        Debugger::timer('tracyExceptions');

        $this->tracyExceptionFile = $this->wire('input')->cookie->tracyExceptionFile;

        $this->filesArray = $this->getFilesArray($this->wire('config')->paths->logs . 'tracy/');
        $tracyExceptionsData = $this->wire('cache')->get('TracyExceptionsData');

        if($tracyExceptionsData) {
            $this->newFiles = array_diff($this->filesArray, $tracyExceptionsData);
        }

        $this->wire('cache')->save('TracyExceptionsData', $this->filesArray, WireCache::expireNever);

        $this->newFilesMap = array_flip($this->newFiles);
        $this->iconColor = count($this->newFiles) > 0 ? TracyDebugger::COLOR_ALERT : TracyDebugger::COLOR_NORMAL;

        $this->icon = '
        <svg height="16px" stroke-miterlimit="10" style="fill-rule:nonzero;clip-rule:evenodd;stroke-linecap:round;stroke-linejoin:round;" version="1.1" viewBox="0 0 27.965 27.965" width="16px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <path fill="'.$this->iconColor.'" d="M13.98 0C6.259 0 0 6.261 0 13.983C0 21.704 6.259 27.965 13.98 27.965C21.705 27.965 27.965 21.703 27.965 13.983C27.965 6.261 21.705 0 13.98 0ZM4.25218 15.775L4.19488 11.907L23.6416 11.9299L23.7405 15.759L4.25218 15.775Z" fill-rule="nonzero" opacity="1" stroke="none"/>
        </svg>';

        return '
        <span title="Tracy Exceptions">
            ' . $this->icon . (TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Tracy Exceptions' : '') . '
        </span>
        ';
    }


    public function getPanel() {

        $tracyModuleUrl = $this->wire('config')->urls->TracyDebugger;
        $currentUrl = $_SERVER['REQUEST_URI'];

        $filePath = $this->wire('config')->paths->root . $this->tracyExceptionFile;

        $out = <<< HTML
        <script>

            var tracyExceptionsViewer = {
                tracyModuleUrl: "$tracyModuleUrl",
                currentURL: "$currentUrl",
            };

            function clearTracyExceptionsViewer() {
                document.getElementById("clearException").style = 'display: none !important';
                document.getElementById("panelTitleFilePath").innerHTML = '';
                if(document.getElementById('tracy-bs')) {
                    var bs = document.getElementById('tracy-bs');
                    while (bs.firstChild) {
                        bs.removeChild(bs.firstChild);
                    }
                    const footer = document.createElement("footer");
                    bs.appendChild(footer);
                }
            }

            function showUnloadButton() {
                document.getElementById("clearException").style.display = "inline-block";
            }

            tracyJSLoader.load(tracyExceptionsViewer.tracyModuleUrl + "scripts/exception-loader.js");

        </script>

HTML;

        $out .= '<h1>'.$this->icon.' Tracy Exceptions <span id="panelTitleFilePath" style="font-size:14px"></span></h1><span class="tracy-icons"></span>
        <div class="tracy-inner">
            <div id="tracyExceptionsViewerContainer" style="height: 100%;">
            <span style="float:right"><input style="display: none !important" type="submit" id="clearException" name="clearException" onclick="clearTracyExceptionsViewer()" value="Unload" /></span>
                <div style="float: left; height: calc(100% - 38px);">
                    <div id="tracyExceptionFiles" style="padding: 0 !important; margin:0 !important; height: calc(100% - 40px) !important; overflow-y: auto; overflow-x: hidden; z-index: 1">';
                        $out .= "<div class='fe-file-tree'>";
                        $out .= $this->php_file_tree($this->wire('config')->paths->logs . 'tracy/');
                        $out .= "</div>";
                    $out .= '
                        <input type="hidden" id="tracyExceptionFilePath" name="tracyExceptionFilePath" value="'.$this->tracyExceptionFile.'" />
                    </div>
                </div>
                <div id="tracyExceptionsViewerCodeContainer">
                    <div id="tracyExceptionsViewerCode" style="display:none"></div>
                </div>
            </div>
            ';

            $out .= TracyDebugger::generatePanelFooter('tracyExceptions', Debugger::timer('tracyExceptions'), strlen($out), 'tracyExceptionsPanel');

        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

    /**
     * Generates a valid HTML list of all files
     *
     * @param string $directory starting point, valid path, with or without trailing slash
     * @return string html markup
     *
     */
    public function php_file_tree($directory) {

        $directory = rtrim($directory, '/\\');

        $tree  = "<div class='tracy-exceptions-file-tree'>";
        $tree .= $this->php_file_tree_dir($directory);
        $tree .= "</div>";

        return $tree;
    }

    /**
     * Generate the list of exception files.
     *
     * @param string $directory starting point, full valid path, without trailing slash
     * @return string html markup
     *
     */
    private function php_file_tree_dir($directory) {

        if(count($this->filesArray) === 0) {
            return "";
        }

        $rootPath = $this->wire('config')->paths->root;
        $parentDir = "/" . str_replace($rootPath, "", $directory . "/");
        $parentDir = str_replace("//", "/", $parentDir);
        $needsUtf8 = $this->needsUtf8Convert();

        $parts = array('<ul>');

        foreach($this->filesArray as $file) {
            $fileName = $needsUtf8 ? $this->toUTF8($file) : $file;
            $isNew = isset($this->newFilesMap[$fileName]);

            $link = str_replace('%2F', '/', rawurlencode($parentDir . $file));
            $link = trim($link, '/');
            $link = str_replace($rootPath, '', $link);

            $liStyle = 'padding: 3px 5px' . ($isNew ? '; background: ' . TracyDebugger::COLOR_ALERT : '');
            $aStyle = $isNew ? ' style="color: #FFFFFF !important"' : '';

            $parts[] = "<li style='{$liStyle}'><a onclick='showUnloadButton()'{$aStyle} href='tracyexception://?f={$link}&l=1'>{$fileName}</a></li>";
        }

        $parts[] = '</ul>';
        return implode("\n", $parts);
    }

    /**
     * Get the latest exception files sorted newest first.
     *
     * @param string $directory path to tracy log directory
     * @return array filenames (basename only)
     *
     */
    private function getFilesArray($directory) {
        $files = glob($directory . '*.html');
        if(!$files) return array();
        rsort($files);
        return array_map('basename', array_slice($files, 0, TracyDebugger::getDataValue("numExceptions")));
    }

    /**
     * Try to convert string to UTF-8, far from bulletproof, requires mbstring and iconv support
     *
     * @param string $str string to convert to UTF-8
     * @param string $encoding auto|ISO-8859-2|Windows-1250|Windows-1252|urldecode
     * @param boolean $c
     * @return string
     *
     */
    private function toUTF8($str, $encoding = 'auto', $c = false) {

        if(!$this->needsUtf8Convert()) return $str;

        if($encoding == 'auto') {
            if(DIRECTORY_SEPARATOR != '/') {
                $str = @iconv(mb_detect_encoding($str, mb_detect_order(), true), 'UTF-8', $str);
            } else {
                $str = @iconv('Windows-1250', 'UTF-8', $str);
            }
        } else {
            if($encoding == 'urldecode') $str = @urldecode($str);
            else if($encoding == 'none') $str = $str;
            else if($encoding != 'UTF-8') $str = @iconv($encoding, 'UTF-8', $str);
        }

        if($c) $str = str_replace(array("%", "#", " ", "{", "}", "^", "+"), array("%25", "%23", "%20", "%7B", "%7D", "%5E", "%2B"), $str);
        return $str;
    }

}
