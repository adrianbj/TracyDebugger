<?php

class TracyLogsPanel extends BasePanel {

    protected $icon;
    protected $iconColor;
    protected $logEntries;
    protected $numLogEntries = 0;
    protected $numErrors = 0;
    protected $numOther = 0;

    public function getTab() {

        \Tracy\Debugger::timer('tracyLogs');

        // end for each section
        $sectionEnd = '
                    </tbody>
                </table>
            </div>';

        /**
         * Tracy log panel sections
         */

        $this->logEntries = '';
        $entriesArr = array();
        $i=0;
        $logs = $this->getLogs();
        if($logs === null) {
            $this->logEntries .= 'Tracy logs directory is not readable.';
        }
        elseif(count($logs) == 0) {
            $this->numLogEntries = 0;
            $this->logEntries .= 'There are no logs in the Tracy logs directory.';
        }
        else {
            $this->logEntries = $this->sectionHeader(array('Type', 'Date', 'URL', 'Text'));
            $logLinesData = $this->wire('cache')->get('TracyLogData.Tracy');
            foreach($logs as $log) {
                $x=99;
                if(!$logLinesData || !isset($logLinesData[$log['name']]) || filemtime($this->getFilename($log['name'])) > $logLinesData[$log['name']]['time']) {
                    $logLinesData[$log['name']]['time'] = time();
                    $logLinesData[$log['name']]['lines'] = $this->getLines($log['name'], array("limit" => \TracyDebugger::getDataValue("numLogEntries")));
                    $this->wire('cache')->save('TracyLogData.Tracy', $logLinesData, WireCache::expireNever);
                }
                $logLines = $logLinesData[$log['name']]['lines'];

                foreach($logLines as $entry) {
                    $logDateTime = str_replace(array('[',']'), '', substr($entry, 0 , 21)); // get the date - first 21 chars
                    $logDateParts = explode(" ", $logDateTime);
                    $logDate = $logDateParts[0];
                    $logTime = str_replace('-',':', $logDateParts[1]);
                    $logDateTime = $logDate . ' ' . $logTime;
                    $itemKey = $log['name'] . '_' . $x;
                    $entryUrlAndText = explode('@', substr($entry, 22)); // get the rest of the line after the date;
                    if(isset($entryUrlAndText[1])) {
                        $entriesArr[$itemKey]['url'] = trim($entryUrlAndText[1]);
                    }
                    else {
                        continue; //bit of a hack - some entries getting duplicated but with empty URL, so ignore
                    }
                    $trimmedText = trim($entryUrlAndText[0]);
                    $entriesArr[$itemKey]['text'] = strlen($trimmedText) > 350 ? substr($trimmedText,0, 350)." ... (".strlen($trimmedText).")" : $trimmedText;
                    $entriesArr[$itemKey]['timestamp'] = @strtotime($logDateTime); // silenced in case timezone is not set
                    $entriesArr[$itemKey]['linenumber'] = 99-$x;
                    $entriesArr[$itemKey]['order'] = $itemKey;
                    $entriesArr[$itemKey]['date'] = $logDateTime;
                    $entriesArr[$itemKey]['log'] = $log['name'];
                    $x--;
                    $i++;

                    // if log entry was in the last 5 seconds (think this is OK for detecting the last page load),
                    // then count the error or other entry type
                    if(time()-5 < @strtotime($logDateTime)) { // silenced in case timezone is not set
                        if($log['name'] == 'error' || $log['name'] == 'exception' || $log['name'] == 'critical') {
                            $this->numErrors++;
                        }
                        else {
                            $this->numOther++;
                        }
                    }
                    $this->numLogEntries++;
                }
            }

            if(count($entriesArr)) {
                # get a list of sort columns and their data to pass to array_multisort
                foreach($entriesArr as $key => $row) {
                    $timestamp[$key] = $row['timestamp'];
                    $order[$key] = $row['order'];
                }

                # sort by event_type desc and then title asc
                array_multisort($timestamp, SORT_DESC, $order, SORT_DESC, $entriesArr);

                //display most recent entries from all log files
                foreach(array_slice($entriesArr, 0, \TracyDebugger::getDataValue("numLogEntries")) as $item) {
                    $logInstance = new TracyFileLog($this->wire('config')->paths->logs.'tracy/' . $item['log'].'.log');
                    $trimmedText = trim(htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'));
                    $this->logEntries .= "
                    \n<tr>" .
                        "<td>".$item['log']."</td>" .
                        "<td>".str_replace('-','&#8209;',str_replace(' ','&nbsp;',$item['date']))."</td>" .
                        "<td>".(isset($item['url']) ? $item['url'] : '')."</td>" .
                        "<td>".\TracyDebugger::createEditorLink($this->wire('config')->paths->logs.'tracy/' . $item['log'] . '.log', $logInstance->getTotalLines()-$item['linenumber'], (strlen($trimmedText) > 350 ? substr($trimmedText,0, 350)." ... (".strlen($trimmedText).")" : $trimmedText), 'View in your code editor')."</td>" .
                    "</tr>";
                }
                $this->logEntries .= $sectionEnd;
            }
        }

        // color icon based on errors/other log entries
        if($this->numErrors > 0) {
            $this->iconColor = \TracyDebugger::COLOR_ALERT;
        }
        elseif($this->numOther > 0) {
            $this->iconColor = \TracyDebugger::COLOR_WARN;
        }
        else {
            $this->iconColor = \TracyDebugger::COLOR_NORMAL;
        }

        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 503.924 503.924" style="enable-background:new 0 0 503.924 503.924;" xml:space="preserve">
        <g>
            <g>
                <path d="M193.932,339.267c15.08,4.858,17.193-0.373,8.568-13.664c-14.736-22.721-28.104-64.069,1.473-125.872    c31.652-66.89,75.525-93.502,83.433-94.946c6.302-1.147-43.28,74.683-45.604,114.463c-1.052,17.93,27.043,31.375,43.959,20.894    c21.793-13.512,20.177-56.801,19.632-63.428c-0.641-7.717,41.291,95.616-10.06,152.254c-10.644,11.743-9.343,17.796,6.101,14.239    c30.218-6.952,74.626-27.272,81.645-86.665c6.761-83.548-69.901-201.196-118.192-250.337c-11.102-11.303-16.572-6.55-16.027,9.285    c0.812,23.801-6.618,60.808-45.594,103.667c-61.114,67.205-74.855,80.354-83.079,135.941    C120.176,284.685,119.296,315.246,193.932,339.267z" fill="'.$this->iconColor.'"/>
                <path d="M74.85,485.497c3.481,11.016,13.579,18.427,25.121,18.427c2.687,0,5.364-0.411,7.956-1.233l144.03-45.509l144.031,45.509    c2.582,0.812,5.26,1.233,7.947,1.233c11.551,0,21.649-7.401,25.12-18.418l6.503-20.568c2.123-6.723,1.501-13.856-1.75-20.101    c-3.242-6.244-8.74-10.854-15.443-12.977l-43.127-13.636l4.284-1.358c6.713-2.122,12.201-6.731,15.452-12.976    c3.242-6.254,3.863-13.388,1.741-20.11l-6.503-20.568c-3.48-11.017-13.579-18.428-25.121-18.428c-2.687,0-5.364,0.411-7.955,1.233    l-105.179,33.239L146.77,346.018c-2.582-0.812-5.259-1.233-7.947-1.233c-11.551,0-21.649,7.401-25.121,18.418L107.2,383.78    c-4.379,13.856,3.328,28.697,17.193,33.077l4.284,1.357L85.56,431.851c-6.713,2.123-12.202,6.732-15.453,12.977    c-3.242,6.244-3.863,13.378-1.741,20.101L74.85,485.497z" fill="'.$this->iconColor.'"/>
            </g>
        </svg>';

        return '
        <span title="Tracy Logs">' .
            $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Tracy Logs' : '') . '
        </span>
        ';
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


    /**
     * Returns instance of FileLog for given log name
     *
     * @param $name
     * @param array $options
     * @return FileLog
     *
     */
    public function getFileLog($name, array $options = array()) {
        $log = new TracyFileLog($this->getFilename($name));
        if(isset($options['delimiter'])) $log->setDelimeter($options['delimiter']);
            else $log->setDelimeter(" ");
        $log->setFileExtension('log');
        return $log;
    }


    /**
     * Get the full filename (including path) for the given log name
     *
     * @param string $name
     * @return string
     * @throws WireException
     *
     */
    public function getFilename($name) {
        if($name !== $this->wire('sanitizer')->pageName($name)) {
            throw new WireException("Log name must contain only [-_.a-z0-9] with no extension");
        }
        return $this->wire('config')->paths->logs.'tracy/' . $name . '.log';
    }


    /**
     * Return array of all logs
     *
     * Each log entry is an array that includes the following:
     *  - name (string): Name of log file, excluding extension.
     *  - file (string): Full path and filename of log file.
     *  - size (int): Size in bytes
     *  - modified (int): Last modified date (unix timestamp)
     *
     * @return array
     *
     */
    public function getLogs() {

        $logs = array();
        $dir = new DirectoryIterator($this->wire('config')->paths->logs.'tracy/');
        if(!@file_exists($this->wire('config')->paths->logs.'tracy/.')) {
            return null;
        }
        else {
            foreach($dir as $file) {
                if($file->isDot() || $file->isDir()) continue;
                if($file->getExtension() != 'log') continue;
                $name = basename($file, '.log');
                if($name != $this->wire('sanitizer')->pageName($name)) continue;
                $logs[$name] = array(
                    'name' => $name,
                    //'file' => $file->getPathname(),
                    //'size' => $file->getSize(),
                    //'modified' => $file->getMTime(),
                );
            }
            ksort($logs);
            return $logs;
        }
    }


    /**
     * Return the given number of entries from the end of log file
     *
     * This method is pagination aware.
     *
     * @param string $name Name of log
     * @param array $options Specify any of the following:
     *  - limit (integer): Specify number of lines.
     *  - text (string): Text to find.
     *  - dateFrom (int|string): Oldest date to match entries.
     *  - dateTo (int|string): Newest date to match entries.
     *  - reverse (bool): Reverse order (default=true)
     *  - pageNum (int): Pagination number 1 or above (default=0 which means auto-detect)
     * @return array
     *
     */
    public function getLines($name, array $options = array()) {
        $pageNum = !empty($options['pageNum']) ? $options['pageNum'] : $this->wire('input')->pageNum;
        unset($options['pageNum']);
        $log = $this->getFileLog($name);
        $limit = isset($options['limit']) ? (int) $options['limit'] : 100;
        return $log->find($limit, $pageNum, $options);
    }


    public function getPanel() {

        // Load all the panel sections
        $isAdditionalBar = \TracyDebugger::isAdditionalBar();
        $out = '<h1>' . $this->icon . ' Tracy Logs' . ($isAdditionalBar ? ' ('.$isAdditionalBar.')' : '') . '</h1>

        <div class="tracy-inner">';
            $out .= $this->logEntries;
            if($this->numLogEntries > 0) {
                $out .= '
                <p>
                    <form method="post" action="'.\TracyDebugger::inputUrl(true).'">
                        <input type="submit" name="deleteTracyLogs" value="Delete All Logs" />
                    </form>
                </p>';
            }

            $out .= \TracyDebugger::generatePanelFooter('tracyLogs', \Tracy\Debugger::timer('tracyLogs'), strlen($out), 'processwireAndTracyLogsPanels');

        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}


class TracyFileLog extends FileLog {

    /**
     * Returns whether the given log line is valid to be considered a log entry
     *
     * @param $line
     * @param array $options
     * @param bool $stopNow Populates this with true when it can determine no more lines are necessary.
     * @return bool|int Returns boolean true if valid, false if not.
     *  If valid as a result of a date comparison, the unix timestmap for the line is returned.
     *
     */

    // this is a replacement of the version in FileLog - we don't want to validate the same way for Tracy Logs so always return true
    protected function isValidLine($line, array $options, &$stopNow) {
        return true;
    }

}
