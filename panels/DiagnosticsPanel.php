<?php

class DiagnosticsPanel extends BasePanel {

    protected static $iconColor;

    protected $process_user;
    protected $file_asset_report;
    protected $conf_asset_report;
    protected $file_assets;
    protected $conf_assets;
    protected $file_asset_cols;
    protected $conf_asset_cols;
    protected $status_counters;
    protected $status_bg_colors;
    protected $status_fg_colors;
    protected $is_dedicated_host;



    /**
     *
     */
    public function getTab() {
        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('diagnostics');

        $this->is_dedicated_host = $this->wire('input')->cookie->tracyDedicatedServerMode;

        $this->process_user    = $this->getPHPUser(false);
        $this->file_assets     = $this->getFileAssets();
        $this->file_asset_cols = $this->getFileAssetColumns();
        $this->conf_assets     = $this->getConfigAssets();
        $this->conf_asset_cols = $this->getConfigAssetColumns();
        $this->status_counters = array('OK' => 0, 'NOTE' => 0, 'WARN' => 0, 'FAIL' => 0);
        $this->status_bg_colors   = array('OK' => \TracyDebugger::COLOR_GREEN, 'NOTE' => '#fff2a8', 'WARN' => \TracyDebugger::COLOR_WARN, 'FAIL' => \TracyDebugger::COLOR_ALERT);
        $this->status_fg_colors   = array('OK' => '#ffffff', 'NOTE' => '#333333', 'WARN' => '#ffffff', 'FAIL' => '#ffffff');

        // generate diagnotics now so we can determine the color of the icon in the Debugger Bar
        $this->generateFileAssetReport();
        $this->generateConfigAssetReport();

        // Determine the colour for the panel icon...
        if (isset($this->status_counters['FAIL']) && ($this->status_counters['FAIL'] > 0)) {
            static::$iconColor = $this->status_bg_colors['FAIL'];
        } else if (isset($this->status_counters['WARN']) && ($this->status_counters['WARN'] > 0)) {
            static::$iconColor = $this->status_bg_colors['WARN'];
        }

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



    /**
     * Define the columns of the file asset diagnostics table.
     *
     * Posix function access allows extra columns and extra checks.
     */
    protected function getFileAssetColumns() {
        if(function_exists('posix_getpwuid')) {
            return array(
                'name'      => 'Asset',
                'location'  => 'Path',
                'ownership' => 'Owner<br>Group',
                'exists'    => 'Exists?',
                'readable'  => "{$this->process_user}<br>Readable?",
                'writable'  => "{$this->process_user}<br>Writable?",
                'wreadable' => 'World<br>Readable?',
                'wwritable' => 'World<br>Writable?',
                'perms'     => 'Perms',
                'notes'     => 'Notes'
            );
        } else {
            return array(
                'name'      => 'Asset',
                'location'  => 'Path',
                'exists'    => 'Exists?',
                'readable'  => "{$this->process_user}<br>Readable?",
                'writable'  => "{$this->process_user}<br>Writable?",
                'notes'     => 'Notes'
            );
        }
    }



    /**
     *
     */
    protected function getConfigAssetColumns() {
        return array(
            'name'  => 'Setting',
            'value' => 'Value',
            'notes' => 'Notes',
        );
    }



    /**
     *
     */
    protected function getConfigAssets() {
        $assets = array(
            'debug' => array(
                'checker' => function (&$asset) {
                    $class = '';
                    if ($asset['original_value']) {
                        $note  = '';
                        if (!\TracyDebugger::$isLocal) {
                            $class = 'FAIL';
                            $asset['notes'][] = 'This should be set to <strong>false</strong> on production machines.';
                            $note = $this->formatSuggestion('Edit <em>site/config.php</em> to read...', '$config->debug = false;');
                        } else {
                            $note = 'Make sure this is set to <strong>false</strong> in production contexts.';
                        }
                        $asset['notes'][] = $note;
                    }
                    return $class;
                }
            ),
            'chmodFile' => array(
                'href' => 'https://processwire.com/docs/security/file-permissions/#where-to-modify-processwires-writable-directory-file-settings',
                'checker' => function (&$asset) {
                    return $this->checkPerms($asset);
                }
            ),
            'chmodDir' => array(
                'href' => 'https://processwire.com/docs/security/file-permissions/#where-to-modify-processwires-writable-directory-file-settings',
                'checker' => function (&$asset) {
                    return $this->checkPerms($asset);
                }
            ),
        );

        foreach ($assets as $setting => &$asset) {
            $asset['name']           = $setting;
            $asset['original_value'] = $this->wire('config')->get($setting);
            $asset['value']          = $asset['original_value'];
            $asset['notes']          = array();
        }

        return $assets;
    }



    protected function hasXPermission($octal_char) {
        return in_array($octal_char, array('1', '3', '5', '7'));
    }



    protected function highestClass($current_min, $class) {

        if (!in_array($class, array('', 'OK', 'NOTE', 'WARN', 'FAIL'))) return $current_min;

        switch ($current_min) {
        case '':
            if (!in_array($class, array('OK', 'NOTE', 'WARN', 'FAIL'))) return $current_min;
            break;

        case 'OK':
            if (!in_array($class, array('NOTE', 'WARN', 'FAIL'))) return $current_min;
            break;

        case 'NOTE':
            if (!in_array($class, array('WARN', 'FAIL'))) return $current_min;
            break;

        case 'WARN':
            if ($class !== 'FAIL') return $current_min;
            break;

        case 'FAIL': // Cannot set to a higher level.
            return $current_min;
            break;
        }

        return $class;
    }



    protected function checkPerms(&$asset) {
        $class = '';
        if (!is_string($asset['value']) || 0 !== strpos($asset['value'], '0')) {
            $asset['notes'][] = "Value should be an octal string.";
            $class = 'FAIL';
        } else {
            $perm_string = $this->integerPermsToString(octdec($asset['value']));
            $perm_string = str_replace('u', ($asset['name'] === 'chmodFile') ? '-' : 'd', $perm_string);
            $asset['value'] .= "<br><nobr>$perm_string</nobr>";

            $owner_perms = substr($asset['value'], 1, 1);
            $group_perms = substr($asset['value'], 2, 1);
            $world_perms = substr($asset['value'], 3, 1);

            if ($world_perms > '0') {
                if ($this->is_dedicated_host) {
                    $asset['notes'][] = "Consider removing world access permissions.";
                    $class = "NOTE";
                } else {
                    $asset['notes'][] = "Remove world access permissions for shared servers <em>if possible</em>.";
                    $class = 'FAIL';
                }
                $thing = ($asset['name'] === 'chmodFile') ? 'file' : 'directory';
                $asset['notes'][] = "If a particular $thing needs to be accessible by everyone, you can always adjust its permissions individually.";
                $world_perms = '0';
            }

            if ($asset['name'] === 'chmodFile') {
                if ($this->hasXPermission($owner_perms)) {
                    $asset['notes'][] = "Remove execute permission for owner.";
                    $owner_perms = (int) $owner_perms;
                    $owner_perms--;
                    $class = $this->highestClass($class, 'WARN');
                }
                if ($this->hasXPermission($group_perms)) {
                    $asset['notes'][] = "Remove execute permission for group.";
                    $group_perms = (int) $group_perms;
                    $group_perms--;
                    $class = $this->highestClass($class, 'WARN');
                }
                if ($this->hasXPermission($world_perms)) {
                    $asset['notes'][] = "Remove execute permission for everyone.";
                    $world_perms = (int) $world_perms;
                    $world_perms--;
                    $class = $this->highestClass($class, 'WARN');
                }
            }

            if ($asset['name'] === 'chmodDir') {
            }

            $newval = "0{$owner_perms}{$group_perms}{$world_perms}";
            if ($newval != $asset['original_value']) {
                $asset['notes'][] = $this->formatSuggestion('Edit <em>site/config.php</em> to read...', "\$config->{$asset['name']} = $newval;");
            }
        }
        $asset['value'] = "<span style='font-family: monospace;white-space:nowrap; padding:0 !important'>{$asset['value']}</span>";
        return $class;
    }



    /**
     *
     */
    protected function generateConfigAssetReport() {
        $this->conf_asset_report = $this->sectionHeader($this->conf_asset_cols);

        foreach($this->conf_assets as $asset) {
            $this->conf_asset_report .= $this->buildConfigRow($asset);
        }

        $this->conf_asset_report .= '
                    </tbody>
                </table>
            </div>';
    }



    protected function buildConfigRow($asset) {
        $cols = $this->conf_asset_cols; // The values will hold the output for the table at this asset row/col intersection...

        foreach ($this->conf_asset_cols as $id => $colname) {
            $value  = $this->newCell('');
            if (is_string($id)) {
                $getter = "get_conf_asset_$id";
                if (method_exists($this, $getter)) {
                    $value = $this->$getter($asset);
                }
            }
            $cols[$id] = $value;
        }

        // Now implode the cols to form the asset row...
        $out = '<tr>';
        foreach ($cols as $cell) {
            $out .= $this->renderCell($cell);
        }
        $out .= "</tr>\n";
        return $out;
    }



    protected function get_conf_asset_name($asset) {
        return $this->newCell($asset['name']);
    }




    protected function get_conf_asset_value(&$asset) {
        $class = '';

        if (isset($asset['checker']) && is_callable($asset['checker'])) {
            $class = $asset['checker']($asset);
        }

        $value = $asset['value'];
        $note  = '';

        if (is_bool($value)) {
            $value = ($value) ? 'true' : 'false';
        }


        return $this->newCell($value, $note, $class, $class);
    }



    protected function get_conf_asset_notes($asset) {
        $value = '';
        if (isset($asset['notes'])) {
            if (is_array($asset['notes'])) {
                $value = implode('<br>',$asset['notes']);
            } else if (is_string($asset['notes'])) {
                $value = $asset['notes'];
            }
        }

        // Where possible append link to more documentation...
        if (isset($asset['href']) && !empty($asset['href'])) {
            $href = "<a href='{$asset['href']}' target='_href'>{$asset['name']} Information...</a>";
            if (!empty($value) && empty($chmod)) {
                $value .= "<br>$href";
            } else {
                $value .= $href;
            }
        }
        return $this->newCell($value);
    }



    /**
     *
     */
    protected function generateFileAssetReport() {

        $this->file_asset_report = $this->sectionHeader($this->file_asset_cols);

        foreach($this->file_assets as $asset) {
            $this->file_asset_report .= $this->buildFileAssetRow($asset);
        }


        $this->file_asset_report .= '
                    </tbody>
                </table>
            </div>';
    }



    /**
     * Render a cell's HTML from its tuple.
     */
    protected function renderCell($cell) {
        $style = ' style="line-height:1.3 !important;"';
        $col   = '';
        $txt   = '';
        $title = (isset($cell['title']) && !empty($cell['title'])) ? " title=\"{$cell['title']}\"" : '';
        $class = isset($cell['class']) ? $cell['class'] : '';
        switch($class) {
        case 'WARN':
            $col = $this->status_bg_colors['WARN'];
            $txt = $this->status_fg_colors['WARN'];
            $this->status_counters['WARN']++;
            break;
        case 'FAIL':
            $col = $this->status_bg_colors['FAIL'];
            $txt = $this->status_fg_colors['FAIL'];
            $this->status_counters['FAIL']++;
            break;
        case 'NOTE':
            $col = $this->status_bg_colors['NOTE'];
            $txt = $this->status_fg_colors['NOTE'];
            //$this->status_counters['NOTE']++;
            break;
        case 'OK':
            $col = $this->status_bg_colors['OK'];
            $txt = $this->status_fg_colors['OK'];
            $this->status_counters['OK']++;
            break;
        default:
            break;
        }

        if ('' !== $col) {
            $style = ' style="line-height:1.3 !important; background-color:'.$col.' !important; color:'.$txt.' !important;"';
        }

        return "<td$style$title>{$cell['value']}</td>\n";
    }



    /**
     * Stash tuple of values for a new table cell
     */
    protected function newCell($value, $note = '', $class = '', $title = '') {
        return array(
            'value' => $value,
            'note'  => $note,
            'class' => $class,
            'title' => $title,
        );
    }



    /**
     * Add a warning, note or failure string to the given value based on the current value of Tracy's $isLocal flag and
     * our internal flag signalling dedicated hosting.
     *
     * Essentially allows for a downgrade of severity warning if this is a development machine and/or a dedicated server.
     */
    protected function downgradeFailureIfAppropriate($value, $dedicated = false, $warn = 'WARN', $fail = 'FAIL', $note = 'NOTE') {
        $local = \TracyDebugger::$isLocal;

        if ($local && $dedicated) {
            $value .= " $note";
        } else if ($local || $dedicated) {
            $value .= " $warn";
        } else {
            $value .= " $fail";
        }

        return trim($value);
    }



    protected function downgradeWarningIfAppropriate($value, $dedicated = false, $warn = 'WARN', $note = 'NOTE') {
        $local = \TracyDebugger::$isLocal;

        if ($local || $dedicated) {
            $value .= " $note";
        } else {
            $value .= " $warn";
        }

        return trim($value);
    }



    /**
     * @source https://secure.php.net/manual/en/function.fileperms.php (See Example 2)
     */
    protected function integerPermsToString($perms) {
        switch ($perms & 0xF000) {
        case 0xC000: // socket
            $info = 's';
            break;
        case 0xA000: // symbolic link
            $info = 'l';
            break;
        case 0x8000: // regular
            $info = '-';
            break;
        case 0x6000: // block special
            $info = 'b';
            break;
        case 0x4000: // directory
            $info = 'd';
            break;
        case 0x2000: // character special
            $info = 'c';
            break;
        case 0x1000: // FIFO pipe
            $info = 'p';
            break;
        default: // unknown
            $info = 'u';
        }

        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
            (($perms & 0x0800) ? 's' : 'x' ) :
            (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
            (($perms & 0x0400) ? 's' : 'x' ) :
            (($perms & 0x0400) ? 'S' : '-'));

        // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
            (($perms & 0x0200) ? 't' : 'x' ) :
            (($perms & 0x0200) ? 'T' : '-'));

        return $info;
    }


    protected function makeChmodFromPermFix($perm_fix, $path, $is_file) {
        $chmod = array();
        if (!empty($perm_fix['u-'])) {
            $fix = implode('', $perm_fix['u-']);
            $chmod[] = "u-$fix";
        }
        if (!empty($perm_fix['u+'])) {
            $fix = implode('', $perm_fix['u+']);
            $chmod[] = "u+$fix";
        }
        if (!empty($perm_fix['g-'])) {
            $fix = implode('', $perm_fix['g-']);
            $chmod[] = "g-$fix";
        }
        if (!empty($perm_fix['g+'])) {
            $fix = implode('', $perm_fix['g+']);
            $chmod[] = "g+$fix";
        }
        if (!empty($perm_fix['o-'])) {
            $fix = implode('', $perm_fix['o-']);
            $chmod[] = "o-$fix";
        }
        if (!empty($perm_fix['o+'])) {
            $fix = implode('', $perm_fix['o+']);
            $chmod[] = "o+$fix";
        }

        $chmod     = implode(',', $chmod);
        $recursive = ($is_file) ? '' : ' -R';
        $recursive = '';

        if (!empty($chmod)) {
            $chmod = "chmod$recursive $chmod $path";
        }

        return $chmod;
    }



    /**
     *
     */
    protected function get_asset_name($asset) {
        return $this->newCell($asset['name']);
    }



    /**
     *
     */
    protected function get_asset_location($asset) {
        return $this->newCell($asset['shortpath'], '', '', $asset['path']);
    }



    /**
     *
     */
    protected function get_asset_path($asset) {
        return $asset['path'];
    }



    /**
     * Determines if a column's checks should be skipped.
     *
     * Useful for assets that either are should be present but are not, or that should not be present, yet are.
     */
    protected function skipCheck($asset) {
        $no_perms     = '' === $asset['perms'];
        $is_installer = isset($asset['is_installer']) && $asset['is_installer'];
        /* $needs_world  = $asset['needs_world']; */
        $needs_world  = false; // Don't check yet. Might add this later.

        return $no_perms || $is_installer || $needs_world;
    }



    /**
     * Determines if an asset exists or not.
     *
     * Applies the exists_rule if one is defined.
     */
    protected function get_asset_exists(&$asset) {
        $rule  = isset($asset['exists_rule']) ? strtolower($asset['exists_rule']) : '';
        $note  = '';
        $class = '';
        if ($asset['exists']) {
            $value = '&#10003;';
            switch ($rule) {
            case 'fail':
                $is_installer    = isset($asset['is_installer']) && $asset['is_installer'];
                $dedicated_local = $this->is_dedicated_host && \TracyDebugger::$isLocal;
                if ($is_installer) {
                    $note  = "Remove to prevent accidental/malicious re-install";
                    $note .= (\TracyDebugger::$isLocal) ? ' or upload of installer to production server.' : '.';
                    if ($dedicated_local) {
                        $class = 'WARN';
                    } else {
                        $class = 'FAIL';
                    }
                    $value .= " $class";
                } else {
                    $note  = "Should be removed.";
                    $value = $this->downgradeFailureIfAppropriate($value, $this->is_dedicated_host);
                    $class = $this->downgradeFailureIfAppropriate('', $this->is_dedicated_host);
                    break;
                }
                $asset['notes'][] = $note;
                if ($asset['is_file']) {
                    $suggestion = "rm {$asset['path']}";
                } else {
                    $suggestion = "rm -r {$asset['path']}";
                }
                $asset['notes'][] = $this->formatCLISuggestion($suggestion, $asset['owner']);
            }
        } else {
            $value = '&#10007;';
            switch ($rule) {
            case 'fail':
                break;

            default:
                $note  = "Should be present. Try reinstalling/recreating.";
                $asset['notes'][] = $note;
                $value.= " FAIL";
                $class = "FAIL";
            }
        }

        $title = strip_tags($note);
        return $this->newCell($value, $note, $class, $title);
    }



    /**
     * Attempt to get the asset user and group.
     */
    protected function get_asset_ownership(&$asset) {
        $value = array();
        $puser = $this->process_user;
        $class = '';
        $note  = '';

        if (isset($asset['owner']) && !empty($asset['owner'])) $value[] = "<nobr>{$asset['owner']}</nobr>";
        if (isset($asset['group']) && !empty($asset['group'])) $value[] = "<nobr>{$asset['group']}</nobr>";
        $value = implode("\n", $value) . "\n\n";

        if (true === $asset['needs_world']) {
            /**
             * The asset is not accessible by the current user other than via the world/other file attributes.
             * Whilst possible, this is far from ideal.
             */
            $note = "Important: <strong>$puser</strong> can only access this via world attributes.";
            $asset['notes'][] = $note;
            $suggestion = "chown $puser {$asset['path']}\n";
            $suggestion.= "# if '$puser' should own this asset, or...\n\n";
            $suggestion.= "chgrp $puser {$asset['path']}\n";
            $suggestion.= "# if user '{$asset['owner']}' should retain ownership.";
            $asset['notes'][] = $this->formatCLISuggestion($suggestion, $asset['owner']);
            $class = $this->downgradeFailureIfAppropriate('');
            $value = $this->downgradeFailureIfAppropriate($value);
        }
        $value = trim($value, " \t\n\r\0\x0B:");
        $value = nl2br($value);

        $title = strip_tags($note);
        return $this->newCell($value, $note, $class, $title);
    }



    /**
     * Return the asset user.
     */
    protected function get_asset_owner(&$asset) {
        $value = $asset['owner'];
        return $this->newCell($value);
    }



    /**
     * Return the asset group.
     */
    protected function get_asset_group(&$asset) {
        $value = $asset['group'];
        return $this->newCell($value);
    }



    protected function formatCLISuggestion($suggestion, $owner) {
        $prefix = "Possible fix: As user <strong>$owner</strong>, or as an administrator, try...";
        return $this->formatSuggestion($prefix, $suggestion);
    }



    protected function formatSuggestion($prefix, $suggestion) {
        return "$prefix<pre style='margin-bottom:0.2em !important; padding:0.1em !important; border:1px dotted #666666 !important'><code style='padding: 0.1em !important;'>$suggestion</code></pre>";

    }



    /**
     *
     */
    protected function setMinimumPermissionClass(&$asset, $class) {
        $current_min = $asset['minimum_perm_class'];
        $asset['minimum_perm_class'] = $this->highestClass($current_min, $class);
    }



    /**
     *
     */
    protected function get_asset_perms(&$asset) {
        $value = (!empty($asset['perms'])) ? $asset['perms'] : '-';
        if ('-' !== $value) {
            $perm_string = $this->integerPermsToString($asset['int_perms']);
            $value .= "<br><nobr>$perm_string</nobr>";
        }
        $value = "<span style='font-family: monospace;white-space:nowrap; padding:0 !important'>$value</span>";
        if ($this->skipCheck($asset)) return $this->newCell($value);
        $class = $asset['minimum_perm_class'];
        $note  = '';
        $users = array();
        $int_perms = $asset['int_perms'];

        $user_file_x  = ($asset['is_file']) && ($int_perms & 0x0040) && !($int_perms & 0x0800);
        $group_file_x = ($asset['is_file']) && ($int_perms & 0x0008) && !($int_perms & 0x0400);
        $world_any_x  = ($int_perms & 0x0001) && !($int_perms & 0x0200);

        if ($user_file_x) {
            $asset['perm_fix']['u-'][] = 'x';
            $user = "user:{$asset['owner']}";
            $users[$user] = $user;
        }
        if ($group_file_x) {
            $asset['perm_fix']['g-'][] = 'x';
            $user = "group:{$asset['group']}";
            $users[$user] = $user;
        }
        if ($world_any_x) {
            $asset['perm_fix']['o-'][] = 'x';
            $users['everyone'] = 'everyone';
        }
        if (!empty($users)) {
            $users = implode(' / ', $users);
            $note = ($asset['is_file']) ? "Execute permission probably not needed for <strong>$users</strong>." : "Directory Traversal is probably not needed by <strong>$users</strong>.";
            $asset['notes'][] = $note;
            if ($class === 'OK') {
                $class = 'NOTE';
                if (($world_any_x && !$this->is_dedicated_host) || $asset['needs_world']) {
                    $class = 'WARN';
                }
            }
        }
        if ($class !== 'OK') {
            $value = "$value<br>$class";
        }
        if ($note === '') {
            $title = 'These permissions may need some attention. Please see previous notes/warnings or failures for this asset.';
        } else {
            $title = strip_tags($note);
        }
        return $this->newCell($value, $note, $class, $title);
    }



    /**
     *
     */
    protected function get_asset_readable(&$asset) {
        if ($this->skipCheck($asset)) return $this->newCell('-');
        $value = '&#10003;';
        $note  = '';
        $class = '';
        $needs_world  = $asset['needs_world'];
        $puser = $this->process_user;
        $puser_matches_owner = $puser === $asset['owner'];
        $puser_matches_group = $puser === $asset['group'];
        if (!$asset['readable']) {
            $value = '&#10007; FAIL';
            $class = 'FAIL';
            $note  = "Must be readable by <strong>$puser</strong>.";
            if ($puser_matches_owner) $asset['perm_fix']['u+'][] = 'r';
            if ($puser_matches_group) $asset['perm_fix']['g+'][] = 'r';
            $asset['notes'][] = $note;
        } else if ($needs_world) {
            $note = "Important: <strong>$puser</strong> can only read this via world attributes.";
            $class = $this->downgradeFailureIfAppropriate('');
            $value = $this->downgradeFailureIfAppropriate($value);
        }
        $this->setMinimumPermissionClass($asset, $class);
        $title = strip_tags($note);
        return $this->newCell($value, $note, $class, $title);
    }



    /**
     *
     */
    protected function get_asset_writable(&$asset) {
        if ($this->skipCheck($asset)) return $this->newCell('-');
        $note  = '';
        $handler = '';
        $upgrade_module_installed = false;
        $class = '';
        $puser = $this->process_user;
        $puser_matches_owner = $puser === $asset['owner'];
        $puser_matches_group = $puser === $asset['group'];
        $rule = isset($asset['writable_rule']) ? strtolower($asset['writable_rule']) : '';
        $has_upgrade_module = $this->wire('modules')->isInstalled('ProcessWireUpgrade');
        $is_config = isset($asset['is_config']) && $asset['is_config'];

        if ('pass-note-alt-session-handler' == $rule) {
            $handler = session_module_name();
            $asset['notes'][] = "Session handler: '$handler'.";
        }

        if($asset['writable']) {
            $value = '&#10003;';
            if ($asset['needs_world']) {
                $note = "Important: <strong>$puser</strong> can only write to this via world attributes.";
                $class = $this->downgradeFailureIfAppropriate('');
                $value = $this->downgradeFailureIfAppropriate($value);
            } else if ('pass-with-note-if-dedicated-and-upgrade' == $rule && $this->is_dedicated_host && $has_upgrade_module) {
                $class = 'NOTE';
                $note  = "Being writable by <strong>$puser</strong> allows automatic core upgrades on dedicated machines; convenient, <em>but not necessary</em>.";
                $asset['notes'][] = $note;
                $value .= " $class";
            } else if ($is_config) {
                $value = $this->downgradeFailureIfAppropriate($value, $this->is_dedicated_host);
                $class = $this->downgradeFailureIfAppropriate('', $this->is_dedicated_host);
                $note   = "Config files probably should not be writable by <strong>$puser</strong>.";
                if ($puser_matches_owner) $asset['perm_fix']['u-'][] = 'w';
                if ($puser_matches_group) $asset['perm_fix']['g-'][] = 'w';
                $asset['notes'][] = $note;
            } else {
                switch ($rule) {
                case 'pass':
                case 'pass-required':
                    break;

                case 'pass-with-note-if-dedicated':
                    if ($this->is_dedicated_host) {
                        $class = 'NOTE';
                        $note  = "Being writable by <strong>$puser</strong> is convenient for module installation on dedicated machines. Alternatively, you can remove writability and use SSH or SFTP to install modules.";
                        $value .= " $class";
                    } else {
                        $note  = "Module installation via the Admin interface is a security risk on non-dedicated machines.";
                        $asset['notes'][] = $note;
                        $note  = "Should not be writable by <strong>$puser</strong>.";
                        $value = $this->downgradeFailureIfAppropriate($value);
                        $class = $this->downgradeFailureIfAppropriate('');
                        if ($puser_matches_owner) $asset['perm_fix']['u-'][] = 'w';
                        if ($puser_matches_group) $asset['perm_fix']['g-'][] = 'w';
                    }
                    $asset['notes'][] = $note;
                    break;

                case 'pass-note-alt-session-handler':
                    if ('files' != $handler) {
                        $class = 'NOTE';
                        $value .= " $class";
                        $note = "Can probably remove write access by <strong>$puser</strong>.";
                        if ($puser_matches_owner) $asset['perm_fix']['u-'][] = 'w';
                        if ($puser_matches_group) $asset['perm_fix']['g-'][] = 'w';
                        $asset['notes'][] = $note;
                    }
                    break;

                default:
                    $note  = "Should probably not be writable by <strong>$puser</strong>.";
                    $value = $this->downgradeFailureIfAppropriate($value, $this->is_dedicated_host);
                    $class = $this->downgradeFailureIfAppropriate('', $this->is_dedicated_host);
                    if ($puser_matches_owner) $asset['perm_fix']['u-'][] = 'w';
                    if ($puser_matches_group) $asset['perm_fix']['g-'][] = 'w';
                    $asset['notes'][] = $note;
                    break;
                }
            }
        } else {
            $value = '&#10007;';

            if (($rule === 'pass-note-alt-session-handler') && ('files' == $handler)) {
                $rule = 'pass-required';
            } else if ('pass-with-note-if-dedicated-and-upgrade' == $rule && $this->is_dedicated_host && $has_upgrade_module) {
                $note  = "Make writable by <strong>$puser</strong> for automatic core upgrades on dedicated machines; convenient, <em>but not necessary.</em>";
                $class = 'NOTE';
                $value .= " $class";
                if ($puser_matches_owner) $asset['perm_fix']['u+'][] = 'w';
                if ($puser_matches_group) $asset['perm_fix']['g+'][] = 'w';
                $asset['notes'][] = $note;
            }

            switch ($rule) {
            case 'pass-required':
                $note  = "Must be writable by <strong>$puser</strong>.";
                $value .= ' FAIL';
                $class  = 'FAIL';
                if ($puser_matches_owner) $asset['perm_fix']['u+'][] = 'w';
                if ($puser_matches_group) $asset['perm_fix']['g+'][] = 'w';
                $asset['notes'][] = $note;
                break;

            case 'pass':
                $note  = "Should be writable by <strong>$puser</strong>.";
                $value = $this->downgradeFailureIfAppropriate($value);
                $class = $this->downgradeFailureIfAppropriate('');
                if ($puser_matches_owner) $asset['perm_fix']['u+'][] = 'w';
                if ($puser_matches_group) $asset['perm_fix']['g+'][] = 'w';
                $asset['notes'][] = $note;
                break;

            default:
                break;
            }
        }

        $title = strip_tags($note);
        $this->setMinimumPermissionClass($asset, $class);
        return $this->newCell($value, $note, $class, $title);
    }



    /**
     * Returns a cell representing the world-readable state of the given asset.
     *
     * Marks config assets as a FAIL in all contexts as they may contain secrets that need guarding.
     * Marks other assets as a WARN in a development context and FAIL in a production context.
     */
    protected function get_asset_wreadable(&$asset) {
        if ($this->skipCheck($asset)) return $this->newCell('-');
        $value = '&#10007;';
        $note  = '';
        $class = '';
        if ($asset['wreadable']) {
            if ($asset['needs_world']) {
                $note = "Currently needs to be readable by <strong>everyone</strong>, but it probably shouldn't be.";
                $dedicated = false;
            } else {
                if (isset($asset['is_config']) && $asset['is_config']) {
                    $note  = 'Config files contain secrets that probably shouldn\'t be readable by <strong>everyone</strong>.';
                } else {
                    $note  = 'Should probably not be readable by <strong>everyone</strong>.';
                }
                $dedicated = $this->is_dedicated_host;
                $asset['perm_fix']['o-'][] = 'r';
            }
            $value = $this->downgradeFailureIfAppropriate('&#10003;', $dedicated);
            $class = $this->downgradeFailureIfAppropriate('', $dedicated);
            $asset['notes'][] = $note;
        }
        $title = strip_tags($note);
        $this->setMinimumPermissionClass($asset, $class);
        return $this->newCell($value, $note, $class, $title);
    }



    /**
     * Returns a cell representing the world-writable state of the given asset.
     *
     * Being writable by everyone is a security risk and should not be needed. Only on a dedicated development box is
     * this really acceptable, and even then it's probably a bad habit.
     *
     * Marks all world-writable assets as a FAIL.
     */
    protected function get_asset_wwritable(&$asset) {
        if ($this->skipCheck($asset)) return $this->newCell('-');
        $value = '&#10007;';
        $note  = '';
        $class = '';
        $needs_world = $asset['needs_world'];
        $dedicated = $this->is_dedicated_host;
        if ($asset['wwritable']) {
            $note  = 'Should probably not be writable by <strong>everyone</strong>.';
            if (isset($asset['is_config']) && $asset['is_config']) {
                $note  = 'Config files should probably not be writable by <strong>everyone</strong>.';
            }
            $value = $this->downgradeFailureIfAppropriate('&#10003;', $dedicated && !$needs_world);
            $class = $this->downgradeFailureIfAppropriate('', $dedicated && !$needs_world);
            $asset['notes'][] = $note;
            $asset['perm_fix']['o-'][] = 'w';
        }
        $title = strip_tags($note);
        $this->setMinimumPermissionClass($asset, $class);
        return $this->newCell($value, $note, $class, $title);
    }



    /**
     * Prepare notes for this asset
     */
    protected function get_asset_notes($asset) {
        $value = '';
        if (isset($asset['notes'])) {
            if (is_array($asset['notes'])) {
                $value = implode('<br>',$asset['notes']);
            } else if (is_string($asset['notes'])) {
                $value = $asset['notes'];
            }
        }

        $chmod = $this->makeChmodFromPermFix($asset['perm_fix'], $asset['path'], $asset['is_file']);
        if ('' !== $chmod) {
            $value .= "<br>" . $this->formatCLISuggestion($chmod, $asset['owner']);
        }


        if ('' == $value) {
            /* $value = 'Seems reasonable.'; */
        }

        // Where possible append link to more documentation...
        if (isset($asset['href']) && !empty($asset['href'])) {
            $href = "<a href='{$asset['href']}' target='_href'>{$asset['name']} Information...</a>";
            if (!empty($value) && empty($chmod)) {
                $value .= "<br>$href";
            } else {
                $value .= $href;
            }
        }

        return $this->newCell($value);
    }



    protected function getBareFileAssetArray() {
        $config = $this->wire('config');

        $assets = array(
            'Root Directory' => array(
                'path' => $config->paths->root,
                'writable_rule' => 'pass-with-note-if-dedicated-and-upgrade',
            ),
            '.htaccess File' => array(
                'path' => $config->paths->root . '.htaccess',
                'is_config' => true,
                'writable_rule' => 'pass-with-note-if-dedicated-and-upgrade',
            ),
            'Index File' => array(
                'path' => $config->paths->root . 'index.php',
                'writable_rule' => 'pass-with-note-if-dedicated-and-upgrade',
            ),
            'Wire Directory' => array(
                'path' => $config->paths->root . 'wire/',
                'writable_rule' => 'pass-with-note-if-dedicated-and-upgrade',
            ),
            'Core Directory' => array(
                'path' => $config->paths->core,
                'writable_rule' => 'pass-with-note-if-dedicated-and-upgrade',
            ),
            'Core Modules' => array(
                'path' => $config->paths->modules,
                'writable_rule' => 'pass-with-note-if-dedicated-and-upgrade',
            ),
            'Site Directory' => array(
                'path' => $config->paths->root . 'site/',
            ),
            'Config File' => array(
                'path' => $config->paths->root . 'site/config.php',
                'is_config' => true,
                'href' => 'https://processwire.com/docs/security/file-permissions/#securing-your-site-config.php-file',
            ),
            'Site Modules' => array(
                'path' => $config->paths->siteModules,
                'href' => 'https://processwire.com/docs/security/file-permissions/#should-site-modules-be-writable',
                'writable_rule' => 'pass-with-note-if-dedicated',
            ),
            'Site Templates' => array(
                'path' => $config->paths->templates,
                'href' => 'https://processwire.com/docs/security/template-files/',
            ),
            'Assets Directory' => array(
                'path' => $config->paths->assets,
                'writable_rule' => 'pass',
            ),
            'Files Directory' => array(
                'path' => $config->paths->files,
                'writable_rule' => 'pass-required',
            ),
            'Cache Directory' => array(
                'path' => $config->paths->cache,
                'writable_rule' => 'pass-required',
            ),
            'Logs Directory' => array(
                'path' => $config->paths->logs,
                'writable_rule' => 'pass-required',
            ),
            'Sessions Directory' => array(
                'path' => $config->paths->sessions,
                'writable_rule' => 'pass-note-alt-session-handler',
            ),
            'Install File' => array(
                'path' => $config->paths->root . 'install.php',
                'exists_rule' => 'fail',
                'is_installer' => true,
            ),
            'Installation Directory' => array(
                'path' => $config->paths->root . 'site/install/',
                'exists_rule' => 'fail',
                'is_installer' => true,
            ),
        );

        /**
         * Search for and add any lingering site profiles that might not have been deleted.
         */
        $matches = glob($config->paths->root . 'site-*/');
        if (!empty($matches)) {
            foreach ($matches as $match) {
                $name = ucwords(str_replace($config->paths->root . 'site-', '', $match));
                $name = trim($name, '/\\');

                $assets["$name Profile"] = array(
                    'path' => $match,
                    'exists_rule' => 'fail',
                    'is_installer' => true,
                );
            }
        }

        return $assets;
    }


    /**
     * Builds an array of assets that should be checked.
     *
     * Each asset will have its permissions, owning user+group, readability and writability determined whereever
     * possible. These values will later be checked to determine if everything is OK or what corrective actions may need
     * to be taken.
     */
    protected function getFileAssets() {
        $config = $this->wire('config');
        $assets = $this->getBareFileAssetArray();

        /**
         * Populate each asset's discoverable information...
         */
        foreach ($assets as $name => &$asset) {
            $path                = $asset['path'];
            $exists              = file_exists($path);
            $perms               = null;
            $permission          = '';
            $readable            = null;
            $writable            = null;
            $world_readable      = null;
            $world_writable      = null;
            $owner               = '';
            $group               = '';
            $puser_matches_owner = null;
            $puser_matches_group = null;
            $needs_world_access  = null;
            $is_file             = is_file($path);

            if ($exists) {
                $perms          = fileperms($path);
                $world_readable = $perms & 0x0004;
                $world_writable = $perms & 0x0002;
                $permission     = substr(sprintf('%o', $perms), -4);
                $readable       = is_readable($path);
                $writable       = is_writeable($path);

                if(function_exists('posix_getpwuid')) {
                    $owner     = posix_getpwuid(fileowner($path))['name'];
                    $group     = posix_getgrgid(filegroup($path))['name'];
                }

                $puser_matches_owner = $this->process_user === $owner;
                $puser_matches_group = $this->process_user === $group;

                $needs_world_access = (!$puser_matches_owner && !$puser_matches_group);
            }

            $asset['shortpath']   = str_replace($config->paths->root, '/', $asset['path']);
            $asset['name']        = $name;
            $asset['exists']      = $exists;
            $asset['int_perms']   = $perms;
            $asset['perms']       = $permission;
            $asset['readable']    = $readable;
            $asset['writable']    = $writable;
            $asset['wreadable']   = $world_readable;
            $asset['wwritable']   = $world_writable;
            $asset['owner']       = $owner;
            $asset['group']       = $group;
            $asset['is_file']     = $is_file;
            $asset['needs_world'] = $needs_world_access;
            $asset['perm_fix']    = array(
                'u-' => array(),
                'u+' => array(),
                'g-' => array(),
                'g+' => array(),
                'o-' => array(),
                'o+' => array(),
            );
            $asset['minimum_perm_class'] = '';
        }

        return $assets;
    }



    /**
     *
     */
    protected function sectionHeader($columnNames = array()) {
        $out = '
        <div>
            <table>
                <thead>
                    <tr>';
                        foreach($columnNames as $columnName) {
                            $out .= '<th style="white-space:nowrap; padding:0.5em !important; line-height:1.3 !important">'.$columnName.'</th>';
                        }
                    $out .= '
                    </tr>
                </thead>
            <tbody>
        ';
        return $out;
    }



    /**
     *
     */
    protected function buildFileAssetRow($asset) {

        $cols = $this->file_asset_cols; // The values will hold the output for the table at this asset row/col intersection...

        foreach ($this->file_asset_cols as $id => $colname) {
            $value  = $this->newCell('', '-');
            if (is_string($id)) {
                $getter = "get_asset_$id";
                if (method_exists($this, $getter)) {
                    $value = $this->$getter($asset);
                }
            }
            $cols[$id] = $value;
        }

        // Now implode the cols to form the asset row...
        $out = '<tr>';
        foreach ($cols as $cell) {
            $out .= $this->renderCell($cell);
        }
        $out .= "</tr>\n";
        return $out;
    }



    /**
     * Attempts to discover which group this process is part of.
     */
    protected function getPHPGroup($string = true) {
        if (function_exists('posix_getegid')) {
            $gid = posix_getegid();
            if ($string) {
                $group = posix_getgrgid($gid);
                $gid = $group['name'];
            }
        } else if (!empty(getenv('APACHE_RUN_GROUP'))) {
            $gid = getenv('APACHE_RUN_GROUP');
        } else {
            $group = 'Unknown';
            $tempDir  = new WireTempDir('whoami');
            $tempFile = $tempDir."/test.txt";
            $check = file_put_contents($tempFile, "test");
            if(is_int($check) && $check > 0) {
                $gid = (string) filegroup($tempFile);
            }
        }
        return $gid;
    }



    /**
     * Tries various methods to determine which user this process is running as.
     *
     * Knowing the user that is running the process allows us to determine if filesystem misconfigurations (usually
     * permissions) can be fixed by code running in this process. This can happen when the process' user matches the
     * owner of the filesystem asset.
     */
    protected function getPHPUser($full = true) {
        if(function_exists('posix_geteuid')) {
            $pwu_data = posix_getpwuid(posix_geteuid());
            $username = $pwu_data['name'];
        } else if(function_exists('exec')) {
            $username = exec('whoami');
        } else if(!empty(getenv('APACHE_RUN_USER'))) {
            $username = getenv('APACHE_RUN_USER');
        } else {
            $username = 'Unknown';
            $tempDir  = new WireTempDir('whoami');
            $tempFile = $tempDir."/test.txt";
            $check = file_put_contents($tempFile, "test");
            if(is_int($check) && $check > 0) {
                $username = (string) fileowner($tempFile); // resort to reporting user ID
            }
        }
        return ($full) ? 'PHP is running as user: ' . $username : $username;
    }



    /**
     *
     */
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



    /**
     *
     */
    public function getPanel() {
        $panelSections = \TracyDebugger::getDataValue('diagnosticsPanelSections');
        $is_local  = \TracyDebugger::$isLocal;

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
        </h1><span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'DiagnosticsPanel\')">+</a></span></span>
        <div class="tracy-inner">';

        $i=0;
        if(in_array('filesystemFolders', $panelSections)) {
            $out .= '
            <a href="#" rel="#filesystemFolders" class="tracy-toggle">Filesystem Folders</a>
            <div id="filesystemFolders">
                <p>Please read <a href="https://processwire.com/docs/security/file-permissions/">https://processwire.com/docs/security/file-permissions/</a> for details on how to secure these.</p>';
                if(function_exists('posix_getpwuid')) {
                    $out .= '<p>'.$this->getPHPUser().' and group: '.$this->getPHPGroup().'</p>';
                }
                $context = ($is_local) ? 'Development (local)' : 'Production';
                $type = ($this->is_dedicated_host) ? 'Dedicated' : 'Shared';
                $out .= "<p>Running in a <strong>$context</strong> context on a <strong>$type</strong> server. &nbsp;";
                $out .= '<input type="submit" onclick="toggleDedicatedServerMode()" value="' . ($this->wire('input')->cookie->tracyDedicatedServerMode ? 'Disable' : 'Enable') .' Dedicated Server Mode" /><br><br>';
                $out .= '</p>';
                $out .= '<p>'.$this->file_asset_report.'</p>';
                $out .= '<h3>Config File Settings</h3><p>'.$this->conf_asset_report.'</p>';
                $out .= '
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

            $query = $this->wire('database')->prepare('SHOW STATUS WHERE variable_name LIKE "Threads_%" OR variable_name = "Connections"');
            $query->execute();
            foreach($query->fetchAll() as $row) {
                $dbInfo .= "\n<tr>" .
                    "<td>".$row[0]."</td>" .
                    "<td>".$row[1]."</td>" .
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

        $out .= \TracyDebugger::generatePanelFooter('diagnostics', \Tracy\Debugger::timer('diagnostics'), strlen($out), 'diagnosticsPanel');
        $out .= '</div>';

        $out .= <<< HTML
        <script>
            function getCookie(name) {
                var value = "; " + document.cookie;
                var parts = value.split("; " + name + "=");
                if(parts.length == 2) return parts.pop().split(";").shift();
            }

            function toggleDedicatedServerMode() {
                if(getCookie("tracyDedicatedServerMode")) {
                    document.cookie = "tracyDedicatedServerMode=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                    location.reload();
                }
                else {
                    document.cookie = "tracyDedicatedServerMode=1; expires=0; path=/";
                    location.reload();
                }
            }
        </script>
HTML;

        return parent::loadResources() . $out;
    }
}
