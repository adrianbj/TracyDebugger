<?php

/**
 * Custom PW panel
 */

class DiagnosticsPanel extends BasePanel {

    protected static $color;
    protected $filesystem;
    protected static $iconColor;

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('diagnostics');

        // generate Filesystem diagnotics now so we can determine the color of the icon in the Debugger Bar
        $this->generateFilesystem();

        return '
        <span title="ProcessWire Diagnostics">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 511.626 511.627" style="enable-background:new 0 0 511.626 511.627;" xml:space="preserve">
                <g>
                    <path d="M18.842,128.478C6.28,141.041,0,156.078,0,173.586v237.54c0,17.512,6.28,32.549,18.842,45.111    c12.563,12.562,27.6,18.843,45.111,18.843h9.136V109.632h-9.136C46.438,109.632,31.402,115.916,18.842,128.478z" fill="'.static::$iconColor.'"/>
                    <path d="M365.446,63.953c0-7.614-2.663-14.084-7.994-19.414c-5.325-5.33-11.8-7.993-19.411-7.993H173.589    c-7.612,0-14.083,2.663-19.414,7.993c-5.33,5.327-7.994,11.799-7.994,19.414v45.679H100.5V475.08h310.625V109.632h-45.679V63.953z     M182.725,73.089h146.179v36.543H182.725V73.089z M365.446,319.765c0,2.67-0.855,4.853-2.567,6.571    c-1.711,1.707-3.9,2.566-6.563,2.566h-63.953v63.953c0,2.662-0.862,4.853-2.573,6.563c-1.704,1.711-3.895,2.567-6.561,2.567    H228.41c-2.667,0-4.854-0.856-6.567-2.567c-1.711-1.711-2.568-3.901-2.568-6.563v-63.953h-63.953    c-2.668,0-4.854-0.859-6.567-2.566c-1.714-1.719-2.57-3.901-2.57-6.571v-54.815c0-2.67,0.856-4.859,2.57-6.571    c1.709-1.709,3.899-2.564,6.562-2.564h63.953V191.86c0-2.666,0.856-4.853,2.57-6.567c1.713-1.713,3.899-2.568,6.567-2.568h54.818    c2.665,0,4.855,0.855,6.563,2.568c1.711,1.714,2.573,3.901,2.573,6.567v63.954h63.953c2.663,0,4.853,0.855,6.563,2.564    c1.708,1.712,2.563,3.901,2.563,6.571v54.815H365.446z" fill="'.static::$iconColor.'"/>
                    <path d="M492.785,128.478c-12.563-12.562-27.601-18.846-45.111-18.846h-9.137V475.08h9.137c17.511,0,32.548-6.28,45.111-18.843    c12.559-12.562,18.842-27.6,18.842-45.111v-237.54C511.626,156.078,505.343,141.041,492.785,128.478z" fill="'.static::$iconColor.'"/>
                </g>
            </svg>' . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Diagnostics' : '') . '
        </span>
        ';
    }


    protected function generateFilesystem() {
        // end for each section
        $sectionEnd = '
                    </tbody>
                </table>
            </div>';

        /**
         * Diagnostics panel sections
         */

        $attributes = array(
            'Root Directory',
            'Wire Directory',
            'Core Directory',
            'Core Modules',
            'Site Directory',
            'Site Modules',
            'Site Templates',
            'Installation Directory',
            'Install File',
            'Config File',
            'Assets Directory',
            'Files Directory',
            'Cache Directory',
            'Logs Directory',
            'Sessions Directory'
        );

        // posix_getpwuid doesn't exist on Windows so don't add Owner & Permission columns
        if(function_exists('posix_getpwuid')) {
            $this->filesystem = $this->sectionHeader(array('Attribute', 'Path', 'Exists', 'Readable', 'Writeable', 'Permissions', 'Owner (User:Group)', 'Status', 'Notes'));
        }
        else {
            $this->filesystem = $this->sectionHeader(array('Attribute', 'Path', 'Exists', 'Readable', 'Writeable', 'Status', 'Notes'));
        }

        foreach($attributes as $attribute) {
            $this->filesystem .= $this->buildAttrRow($attribute);
        }
        $this->filesystem .= $sectionEnd;

    }


    protected function sectionHeader($columnNames = array()) {
        $out = '
        <div>
            <table>
                <thead>
                    <tr>';
                        foreach($columnNames as $columnName) {
                            $out .= '<th>'.$columnName.'</th>';
                        }
                    $out .= '
                    </tr>
                </thead>
            <tbody>
        ';
        return $out;
    }

    protected function getPaths($attribute, $shortened = false) {
        $config = $this->wire('config');
        switch ($attribute) {
            case 'Root Directory':
                $path = $config->paths->root;
                break;
            case 'Wire Directory':
                $path = $config->paths->root . 'wire/';
                break;
            case 'Core Directory':
                $path = $config->paths->core;
                break;
            case 'Core Modules':
                $path = $config->paths->modules;
                break;
            case 'Site Directory':
                $path = $config->paths->root . 'site/';
                break;
            case 'Site Modules':
                $path = $config->paths->siteModules;
                break;
            case 'Site Templates':
                $path = $config->paths->templates;
                break;
            case 'Installation Directory':
                $path = $config->paths->root . 'site/install/';
                break;
            case 'Install File':
                $path = $config->paths->root . 'install.php';
                break;
            case 'Config File':
                $path = $config->paths->root . 'site/config.php';
                break;
            case 'Assets Directory':
                $path = $config->paths->assets;
                break;
            case 'Files Directory':
                $path = $config->paths->files;
                break;
            case 'Cache Directory':
                $path = $config->paths->cache;
                break;
            case 'Logs Directory':
                $path = $config->paths->logs;
                break;
            case 'Sessions Directory':
                $path = $config->paths->sessions;
                break;
        }
        return $shortened ? str_replace($config->paths->root, '/', $path) : $path;
    }

    protected function buildAttrRow($attribute) {

        $path = $this->getPaths($attribute);
        $permission = !file_exists($path) ? '' : substr(sprintf('%o', fileperms($path)), -4);
        $exists = file_exists($path) ? '&#10003;' : '';
        $readable = is_readable($path) ? '&#10003;' : '';
        $writeable = is_writeable($path) ? '&#10003;' : '';

        // posix_getpwuid doesn't exist on Windows
        if(function_exists('posix_getpwuid')) {
            $owner = !file_exists($path) ? '' : posix_getpwuid(fileowner($path));
            $group = !isset($owner['gid']) ? '' : posix_getgrgid($owner['gid']);
        }

        $statusNotes = $this->getStatusNotes($attribute, $exists, $readable, $writeable, $permission);

        $okColor = '#009900';
        $warningColor = '#FF9933';
        $failureColor = '#CD1818';

        switch ($statusNotes[0]) {
            case 'OK':
                static::$color = $okColor;
                break;
            case 'Warning':
                static::$color = $warningColor;
                break;
            case 'Failure':
                static::$color = $failureColor;
                break;
        }


        if(!static::$iconColor) static::$iconColor = static::$color;
        if(static::$color == $okColor) {
            //do nothing
        }
        elseif(static::$color == $warningColor) {
            if(static::$iconColor == $failureColor) {
                // do nothing
            }
            else {
                static::$iconColor = static::$color;
            }
        }
        else {
            static::$iconColor = static::$color;
        }


        $out = "
        \n<tr>
            <td>".$attribute."</td>
            <td title='".$this->getPaths($attribute)."'>".$this->getPaths($attribute, true)."</td>
            <td>".$exists."</td>
            <td>".$readable."</td>
            <td>".$writeable."</td>";
            // posix_getpwuid doesn't exist on Windows so don't add Permission column
            if(function_exists('posix_getpwuid')) {
                $out .= "<td>".$permission."</td>";
            }
            if(isset($owner)) {
                $out .= "<td>".(!empty($owner) ? $owner['name'].":".$group['name'] : '')."</td>";
            }
            $out .= "
            <td style='background-color:".static::$color." !important; color:#FFFFFF !important'>".$statusNotes[0]."</td>
            <td>".str_replace(' ', '&nbsp;', $statusNotes[1])."</td>
        </tr>";

        return $out;

    }

    /**
    * return an array with status and notes items
    */
    protected function getStatusNotes($attribute, $exists, $readable, $writeable, $permission) {
        // also check if NOT on Windows by using presence of posix_getpwuid
        if($permission == '0777' && function_exists('posix_getpwuid')) {
            return array('Failure', '0777 is usually unsafe');
        }
        else {
            switch ($attribute) {
                case 'Root Directory':
                case 'Wire Directory':
                case 'Core Directory':
                case 'Core Modules':
                    if(!$readable) return array('Failure','Needs to be readable');
                        else return $writeable ? array('Warning', 'For added security, make read-only') : array('OK', 'Needs to be writeable for automatic core upgrades');
                case 'Site Templates':
                    if(!$readable) return array('Failure','Needs to be readable');
                        else return $writeable ? array('Warning', 'For added security, make read-only') : array('OK', '');
                case 'Site Directory':
                    if(!$readable) return array('Failure','Needs to be readable');
                        else return $writeable ? array('Warning', 'For added security, make read-only') : array('OK', '');
                case 'Config File':
                    if($permission > 600) return array('Warning', 'Readable by more than just the owner');
                        else return !$readable ? array('Failure', 'Needs to be readable') : array('OK', '');
                case 'Installation Directory':
                case 'Install File':
                    return $exists ? array('Failure', 'Should be deleted for security') : array('OK', '');
                case 'Site Modules':
                    if(!$readable) return array('Failure','Needs to be readable');
                        else return $writeable ? array('Warning', 'For added security, make read-only') : array('OK', 'Needs to be writeable for automatic installation of modules');
                case 'Assets Directory':
                case 'Cache Directory':
                case 'Files Directory':
                case 'Logs Directory':
                case 'Sessions Directory':
                    return $readable && $writeable ? array('OK', '') : array('Failure', 'Needs to be readable and writeable');
            }
        }
    }

    protected function getPHPUser() {
        if(function_exists('exec')) {
            return 'PHP is running as user: ' . exec('whoami');
        }
        else {
            $tempDir = new  WireTempDir('whoami');
            $check = file_put_contents($tempDir."/test.txt", "test");
            if(is_int($check) && $check > 0) {
                return 'PHP appears to be running as you.';
            }
            else {
                return 'PHP appears to be running as another user, ie. not you.';
            }
        }
    }

    protected function incorrectPermissionFiles() {

        $this->incorrectFiles = $this->sectionHeader(array('File', 'Permissions'));

        foreach($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->wire('config')->paths->root, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $fileinfo) {
            if((strpos($fileinfo->getPathname(), 'assets'.DIRECTORY_SEPARATOR.'sessions'.DIRECTORY_SEPARATOR) === false && !$fileinfo->isDir() && strpos($fileinfo->getPathname(), DIRECTORY_SEPARATOR.'.') === false) || $fileinfo->getFilename() == ".htaccess") {
                $octal_perms = substr(sprintf('%o', $fileinfo->getPerms()), -4);
                if($octal_perms != $this->wire('config')->chmodFile) $this->incorrectFiles .= '<tr><td>'.$fileinfo->getPathname() . "</td><td>" . $octal_perms . "</tr>\n";
            }
        }
        $this->incorrectFiles .= '
                    </tbody>
                </table>
            </div>';

        return $this->incorrectFiles;
    }

    public function getPanel() {

        $panelSections = \TracyDebugger::getDataValue('diagnosticsPanelSections');

        // end for each section
        $sectionEnd = '
                    </tbody>
                </table>
            </div>';


        // Load all the panel sections
        $out = '
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 511.626 511.627" style="enable-background:new 0 0 511.626 511.627;" xml:space="preserve">
                <g>
                    <path d="M18.842,128.478C6.28,141.041,0,156.078,0,173.586v237.54c0,17.512,6.28,32.549,18.842,45.111    c12.563,12.562,27.6,18.843,45.111,18.843h9.136V109.632h-9.136C46.438,109.632,31.402,115.916,18.842,128.478z" fill="'.static::$iconColor.'"/>
                    <path d="M365.446,63.953c0-7.614-2.663-14.084-7.994-19.414c-5.325-5.33-11.8-7.993-19.411-7.993H173.589    c-7.612,0-14.083,2.663-19.414,7.993c-5.33,5.327-7.994,11.799-7.994,19.414v45.679H100.5V475.08h310.625V109.632h-45.679V63.953z     M182.725,73.089h146.179v36.543H182.725V73.089z M365.446,319.765c0,2.67-0.855,4.853-2.567,6.571    c-1.711,1.707-3.9,2.566-6.563,2.566h-63.953v63.953c0,2.662-0.862,4.853-2.573,6.563c-1.704,1.711-3.895,2.567-6.561,2.567    H228.41c-2.667,0-4.854-0.856-6.567-2.567c-1.711-1.711-2.568-3.901-2.568-6.563v-63.953h-63.953    c-2.668,0-4.854-0.859-6.567-2.566c-1.714-1.719-2.57-3.901-2.57-6.571v-54.815c0-2.67,0.856-4.859,2.57-6.571    c1.709-1.709,3.899-2.564,6.562-2.564h63.953V191.86c0-2.666,0.856-4.853,2.57-6.567c1.713-1.713,3.899-2.568,6.567-2.568h54.818    c2.665,0,4.855,0.855,6.563,2.568c1.711,1.714,2.573,3.901,2.573,6.567v63.954h63.953c2.663,0,4.853,0.855,6.563,2.564    c1.708,1.712,2.563,3.901,2.563,6.571v54.815H365.446z" fill="'.static::$iconColor.'"/>
                    <path d="M492.785,128.478c-12.563-12.562-27.601-18.846-45.111-18.846h-9.137V475.08h9.137c17.511,0,32.548-6.28,45.111-18.843    c12.559-12.562,18.842-27.6,18.842-45.111v-237.54C511.626,156.078,505.343,141.041,492.785,128.478z" fill="'.static::$iconColor.'"/>
                </g>
            </svg>
            Diagnostics
        </h1>
        <div class="tracy-inner">';

        $i=0;
        if(in_array('filesystemFolders', $panelSections)) {
            $out .= '
            <a href="#" rel="#filesystemFolders" class="tracy-toggle">Filesystem Folders</a>
            <div id="filesystemFolders">
                <p>Please read <a href="https://processwire.com/docs/security/file-permissions/">https://processwire.com/docs/security/file-permissions/</a> for details on how to secure these.</p>';
                if(function_exists('posix_getpwuid')) {
                    $out .= '<p>'.$this->getPHPUser().'</p><br />';
                }
                $out .= '<p>'.$this->filesystem.'
            </div>
            <br />';
            $i++;
        }

        if(in_array('filesystemFiles', $panelSections) && substr(PHP_OS, 0, 3) != 'WIN') {
            $out .= '
            <a href="#" rel="#filesystemFiles" class="tracy-toggle '.($i>0 ? ' tracy-collapsed' : '').'">Filesystem Files</a>
            <div id="filesystemFiles"'.($i>0 ? ' class="tracy-collapsed"' : '').'>
                <p><strong>Files</strong> with permissions that don\'t match the '. $this->wire('config')->chmodFile .' octal from the $config->chmodFile setting.<br />These may be fine as is - this is just meant to provide an easy means to identify any possible inconsistencies.</p>'.$this->incorrectPermissionFiles() . '
            </div>
            <br />';
            $i++;
        }

        if(in_array('mysqlInfo', $panelSections)) {
            $attributes = array(
                "CLIENT_VERSION",
                "SERVER_VERSION",
                "CONNECTION_STATUS",
                "DRIVER_NAME",
                "SERVER_INFO",
                "ORACLE_NULLS",
                "PERSISTENT",
                "AUTOCOMMIT",
                "ERRMODE",
                "CASE"
            );

            $dbInfo = $this->sectionHeader(array('Attribute', 'Value'));

            foreach($attributes as $val) {
                $dbInfo .= "\n<tr>" .
                    "<td>PDO::ATTR_$val</td>" .
                    "<td>".$this->wire('database')->getAttribute(constant("PDO::ATTR_$val"))."</td>" .
                    "</tr>";
            }
            $dbInfo .= $sectionEnd;

            $out .= '
            <a href="#" rel="#mysqlinfo" class="tracy-toggle '.($i>0 ? ' tracy-collapsed' : '').'">MySQL Info</a>
            <div id="mysqlinfo"'.($i>0 ? ' class="tracy-collapsed"' : '').'>
                '.$dbInfo.'
            </div><br />';
            $i++;
        }

        $out .= \TracyDebugger::generatedTimeSize('diagnostics', \Tracy\Debugger::timer('diagnostics'), strlen($out));

        $out .= '</div>';

        return parent::loadResources() . $out;
    }

}
