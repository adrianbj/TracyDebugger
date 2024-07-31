<?php

class TracyExceptionsPanel extends BasePanel {

    private $icon;
    private $iconColor;
    private $tracyExceptionFile;
    private $errorMessage = null;
    private $encoding = 'auto';
    private $tracyPwApiData;
    private $filesArray = array();
    private $newFiles = array();

    public function getTab() {

        \Tracy\Debugger::timer('tracyExceptions');

        $this->tracyExceptionFile = $this->wire('input')->cookie->tracyExceptionFile;

        $this->filesArray = $this->getFilesArray($this->wire('config')->paths->logs . 'tracy/', array('html'));
        $tracyExeceptionsData = $this->wire('cache')->get('TracyExceptionsData');

        $this->newFiles = array();
        if($tracyExeceptionsData) {
            foreach ($this->filesArray as $file) {
                if(!in_array($file, $tracyExeceptionsData)) {
                    $this->newFiles[] = $file;
                }
            }
        }

        if(count($this->newFiles) > 0) {
            $this->iconColor = \TracyDebugger::COLOR_ALERT;
        }
        else {
            $this->iconColor = \TracyDebugger::COLOR_NORMAL;
        }

        $this->icon = '
        <svg height="16px" stroke-miterlimit="10" style="fill-rule:nonzero;clip-rule:evenodd;stroke-linecap:round;stroke-linejoin:round;" version="1.1" viewBox="0 0 27.965 27.965" width="16px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <path fill="'.$this->iconColor.'" d="M13.98 0C6.259 0 0 6.261 0 13.983C0 21.704 6.259 27.965 13.98 27.965C21.705 27.965 27.965 21.703 27.965 13.983C27.965 6.261 21.705 0 13.98 0ZM4.25218 15.775L4.19488 11.907L23.6416 11.9299L23.7405 15.759L4.25218 15.775Z" fill-rule="nonzero" opacity="1" stroke="none"/>
        </svg>';

        return '
        <span title="Tracy Exceptions">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Tracy Exceptions' : '') . '
        </span>
        ';
    }


    public function getPanel() {

        $tracyModuleUrl = $this->wire('config')->urls->TracyDebugger;
        $rootUrl = $this->wire('config')->urls->root;

        $filePath = $this->wire('config')->paths->root . $this->tracyExceptionFile;

        $maximizeSvg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="282.8 231 16 15.2" enable-background="new 282.8 231 16 15.2" xml:space="preserve"><polygon fill="#AEAEAE" points="287.6,233.6 298.8,231 295.4,242 "/><polygon fill="#AEAEAE" points="293.9,243.6 282.8,246.2 286.1,235.3 "/></svg>';

        $out = <<< HTML
        <script>

            var tracyExceptionsViewer = {
                tracyModuleUrl: "$tracyModuleUrl",
                rootURL: "$rootUrl",
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

        $out .= '<h1>'.$this->icon.' Tracy Exceptions <span id="panelTitleFilePath" style="font-size:14px"></span></h1><span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'TracyExceptionsPanel\')">+</a></span></span>
        <div class="tracy-inner">
            <div id="tracyExceptionsViewerContainer" style="height: 100%;">
            <span style="float:right"><input style="display: none !important" type="submit" id="clearException" name="clearException" onclick="clearTracyExceptionsViewer()" value="Unload" /></span>
                <div style="float: left; height: calc(100% - 38px);">
                    <div id="tracyExceptionFiles" style="padding: 0 !important; margin:0 !important; width: 512px !important; height: calc(100% - 40px) !important; overflow-y: auto; overflow-x: hidden; z-index: 1">';
                        $out .= "<div class='fe-file-tree'>";
                        $out .= $this->php_file_tree($this->wire('config')->paths->logs . 'tracy/', array('html'));
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

            $out .= \TracyDebugger::generatePanelFooter('tracyExceptions', \Tracy\Debugger::timer('tracyExceptions'), strlen($out), 'tracyExceptionsPanel');

        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

    /**
     * Generates a valid HTML list of all directories, sub-directories and files
     *
     * @param string $directory starting point, valid path, with or without trailing slash
     * @param array $extensions array of strings with extension types (without dot), default: empty array, show all files
     * @param bool $extFilter to include (false) or exclude (true) files with that extension, default: false
     * @return string html markup
     *
     */
     public function php_file_tree($directory, $extensions = array(), $extFilter = false) {

        if(!function_exists("scandir")) {
            $msg = $this->_('Error: scandir function does not exist.');
            $this->error($msg);
            return;
        }

        $directory = rtrim($directory, '/\\'); // strip both slash and backslash at the end

        $tree  = "<div class='tracy-exceptions-file-tree'>";
        $tree .= $this->php_file_tree_dir($directory, $extensions, (bool) $extFilter);
        $tree .= "</div>";

        return $tree;
    }

    /**
     * Recursive function to generate the list of directories/files.
     *
     * @param string $directory starting point, full valid path, without trailing slash
     * @param array $extensions array of strings with extension types (without dot), default: empty array
     * @param bool $extFilter to include (false) or exclude (true) files with that extension, default: false (include)
     * @param string $parent relative directory path, for internal use only
     * @return string html markup
     *
     */
    private function php_file_tree_dir($directory, $extensions = array(), $extFilter = false, $parent = "") {

        $tree = "";

        if(count($this->filesArray) > 0) {
            // Sort directories/files
            natcasesort($this->filesArray);
            $this->filesArray = array_reverse($this->filesArray, false);

            // Make directories first, then files
            $fls = $dirs = array();
            foreach($this->filesArray as $f) {
                if(@is_dir("$directory/$f")) $dirs[] = $f; else $fls[] = $f;
            }
            $this->filesArray = array_merge($dirs, $fls);
            $this->wire('cache')->save('TracyExceptionsData', $this->filesArray, WireCache::expireNever);

            $tree .= "<ul>";

            foreach($this->filesArray as $file) {
                $fileName = $this->toUTF8($file, $this->encoding);

                $parentDir = "/" . str_replace($this->wire('config')->paths->root, "", $directory . "/"); // directory is without trailing slash
                $dirPath = $this->toUTF8("$parentDir/$file/", $this->encoding);
                $dirPath = str_replace("//", "/", $dirPath);

                $ext = strtolower(substr($file, strrpos($file, ".") + 1));
                $link = str_replace("%2F", "/", rawurlencode("$dirPath")); // to overcome bug/feature on apache
                $link = trim($link, '/');
                $link = str_replace('//', '/', $link);
                $link = str_replace($this->wire('config')->paths->root, '', $link);
                $tree .= "<li style='padding: 3px 5px".(in_array($fileName, $this->newFiles) ? '; background: '.\TracyDebugger::COLOR_ALERT : '')."'><a onclick='showUnloadButton()' ".(in_array($fileName, $this->newFiles) ? ' style="color: #FFFFFF !important"' : '')." href='tracyexception://?f=$link&l=1'>$fileName</a></li>";

            }

            $tree .= "</ul>";
        }
        return $tree;
    }

    private function getFilesArray($directory, $extensions = array(), $extFilter = false) {
        $filesArray = array();
        // Get directories/files
        $filesArray = array_diff(@scandir($directory, SCANDIR_SORT_DESCENDING), array('.', '..')); // array_diff removes . and ..

        // Filter unwanted extensions
        // currently empty extensions array returns all files in folders
        // comment if statement if you want empty extensions array to return no files at all
        if(!empty($extensions)) {
            foreach(array_keys($filesArray) as $key) {

                // exclude dotfiles, but leave files in extensions filter
                // substr($filesArray[$key], 1) removes first char
                if($filesArray[$key][0] == '.' && !in_array(substr($filesArray[$key], 1), $extensions) ) unset($filesArray[$key]);

                if(!@is_dir("$directory/$filesArray[$key]")) {
                    $ext = substr($filesArray[$key], strrpos($filesArray[$key], ".") + 1);
                    if($extFilter == in_array($ext, $extensions)) unset($filesArray[$key]);
                }
            }
        }
        return $filesArray;
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

        if(PHP_VERSION_ID >= 70100) return $str;

        // http://stackoverflow.com/questions/7979567/php-convert-any-string-to-utf-8-without-knowing-the-original-character-set-or
        if(extension_loaded('mbstring') && function_exists('iconv')) {
            if($encoding == 'auto') {
                if(DIRECTORY_SEPARATOR != '/') {
                    // windows
                    $str = @iconv(mb_detect_encoding($str, mb_detect_order(), true), 'UTF-8', $str);
                } else {
                    // linux
                    $str = @iconv('Windows-1250', 'UTF-8', $str); // wild guess!!! could be ISO-8859-2, UTF-8, ...
                }
            } else {
                if($encoding == 'urldecode') $str = @urldecode($str);
                else if($encoding == 'none') $str = $str;
                else if($encoding != 'UTF-8') $str = @iconv($encoding, 'UTF-8', $str);
            }
        }
        // replacement of % must be first!!!
        if($c) $str = str_replace(array("%", "#", " ", "{", "}", "^", "+"), array("%25", "%23", "%20", "%7B", "%7D", "%5E", "%2B"), $str);
        return $str;
    }

    /**
     * Convert $config->paths->key to $config->urls->key
     *
     * @param string $path eg. $config->paths->templates
     * @param array $pathTypes eg. array('site','templates'), if not specified, array is constructed from $config->paths
     * @return string path converted to url, empty string if path not found
     *
     */
    private function convertPathToUrl($path, $pathTypes = array()) {
        $path = rtrim($path, '/\\') . '/'; // strip both slash and backslash at the end and then re-add separator
        $url = '';

        if(!$pathTypes) {
            $pathTypes = array('root'); // root is missing
            foreach($this->wire('config')->paths as $pathType => $dummy) $pathTypes[] = $pathType;
        }

        foreach($pathTypes as $pathType) {
            if($this->wire('config')->paths->{$pathType} == $path) {
                $url = $this->wire('config')->urls->{$pathType};
                break;
            }
        }
        return $url;
    }

    /**
     * Convert string delimited by delimiter into an array. Removes empty array keys.
     *
     * @param string $extensions string with delimiters
     * @param string $delimiter, default is comma
     * @return array
     *
     */
    private function toArray($extensions, $delimiter = ',') {
        $ext = preg_replace('# +#', '', $extensions); // remove all spaces
        $ext = array_filter(explode($delimiter, $ext), 'strlen'); // convert to array splitting by delimiter
        return $ext;
    }


    private function strposa($haystack, $needle, $offset=0) {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $query) {
            if(strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
        }
        return false;
    }

}
