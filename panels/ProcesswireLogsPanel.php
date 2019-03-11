<?php

class ProcesswireLogsPanel extends BasePanel {

    protected $icon;
    protected $iconColor;
    protected $logEntries;
    protected $numLogEntries = 0;
    protected $numErrors = 0;
    protected $numOther = 0;

    public function getTab() {

        \Tracy\Debugger::timer('processwireLogs');

        // end for each section
        $sectionEnd = '
                    </tbody>
                </table>
            </div>';

        /**
         * PW log panel sections
         */

        $logs = $this->wire('log')->getLogs();
        if($logs === null) {
            $this->logEntries .= 'Logs directory is not readable.';
        }
        elseif(count($logs) == 0) {
            $this->numLogEntries = 0;
            $this->logEntries .= 'There are no logs in the ProcessWire logs directory.';
        }
        else {
            $this->logEntries = $this->sectionHeader(array('Type', 'Date', 'User', 'URL', 'Text'));
            $logLinesData = $this->wire('cache')->get('TracyLogData.ProcessWire');
            $entriesArr = array();
            $i=0;
            foreach($logs as $log) {
                $x=99;
                if(!$logLinesData || !isset($logLinesData[$log['name']]) || filemtime($this->wire('log')->getFilename($log['name'])) > $logLinesData[$log['name']]['time']) {
                    $logLinesData[$log['name']]['time'] = time();
                    $logLinesData[$log['name']]['lines'] = $this->wire('log')->getEntries($log['name'], array("limit" => \TracyDebugger::getDataValue("numLogEntries")));
                    $this->wire('cache')->save('TracyLogData.ProcessWire', $logLinesData, WireCache::expireNever);
                }
                $logLines = $logLinesData[$log['name']]['lines'];

                foreach($logLines as $entry) {
                    $itemKey = $log['name'] . '_' . $x;
                    $entriesArr[$itemKey]['timestamp'] = @strtotime($entry['date']); // silenced in case timezone is not set
                    $entriesArr[$itemKey]['linenumber'] = 99-$x;
                    $entriesArr[$itemKey]['order'] = $itemKey;
                    $entriesArr[$itemKey]['date'] = $entry['date'];
                    $entriesArr[$itemKey]['text'] = $entry['text'];
                    $entriesArr[$itemKey]['user'] = $entry['user'];
                    $entriesArr[$itemKey]['url'] = $entry['url'];
                    $entriesArr[$itemKey]['log'] = $log['name'];
                    $x--;
                    $i++;

                    // if log entry was in the last 5 seconds (think this is OK for detecting the last page load),
                    // then count the error or other entry type
                    if(time()-5 < @strtotime($entry['date'])) { // silenced in case timezone is not set
                        if($log['name'] == 'errors' || $log['name'] == 'exceptions') {
                            $this->numErrors++;
                        }
                        else {
                            $this->numOther++;
                        }
                    }
                    $this->numLogEntries++;
                }
            }

            // get a list of sort columns and their data to pass to array_multisort
            $sort = array();
            foreach($entriesArr as $key => $row) {
                $timestamp[$key] = $row['timestamp'];
                $order[$key] = $row['order'];
            }
            // sort by event_type desc and then title asc
            array_multisort($timestamp, SORT_DESC, $order, SORT_DESC, $entriesArr);

            //display most recent entries from all log files
            foreach(array_slice($entriesArr, 0, \TracyDebugger::getDataValue("numLogEntries")) as $item) {
                $logInstance = new FileLog($this->wire('config')->paths->logs . $item['log'].'.txt');
                $trimmedText = trim(htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'));
                $this->logEntries .= "
                \n<tr>
                    <td><a title='View \"".$item['log']."\" log file in PW admin' href='".$this->wire('config')->urls->admin."setup/logs/view/".$item['log']."/'>".str_replace('-', '&#8209;', $item['log'])."</a></td>" .
                    "<td>".str_replace('-','&#8209;',str_replace(' ','&nbsp;',$item['date']))."</td>" .
                    "<td>".$item['user']."</td>" .
                    "<td>".$item['url']."</td>" .
                    "<td>".\TracyDebugger::createEditorLink($this->wire('config')->paths->logs . $item['log'] . '.txt', $logInstance->getTotalLines()-$item['linenumber'], (strlen($trimmedText) > 350 ? substr($trimmedText,0, 350)." ... (".strlen($trimmedText).")" : $trimmedText), 'View in your code editor')."</td>" .
                "</tr>";
            }
            $this->logEntries .= $sectionEnd;
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
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 433.494 433.494" style="enable-background:new 0 0 433.494 433.494;" xml:space="preserve">
            <polygon points="353.763,379.942 279.854,234.57 322.024,250.717 253.857,116.637 276.286,127.997 216.747,0 157.209,127.997     179.64,116.636 111.471,250.717 153.642,234.569 79.731,379.942 200.872,337.52 200.872,433.494 232.624,433.494 232.624,337.518       " fill="'.$this->iconColor.'"/>
        </svg>';

        return '
        <span title="ProcessWire Logs">' .
            $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'PW Logs' : '') . '
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


    public function getPanel() {

        // Load all the panel sections
        $isAdditionalBar = \TracyDebugger::isAdditionalBar();
        $out = '
        <h1>
            <a title="ProcessWire Logs" href="'.$this->wire('config')->urls->admin.'setup/logs/">
                ' . $this->icon . ' ProcessWire Logs
            </a>' . ($isAdditionalBar ? ' ('.$isAdditionalBar.')' : '') . '
        </h1>

        <div class="tracy-inner">';
            $out .= $this->logEntries;

            if($this->numLogEntries > 0) {
                $out .= '
                <p>
                    <form method="post" action="'.\TracyDebugger::inputUrl(true).'">
                        <input type="submit" name="deleteProcessWireLogs" value="Delete All Logs" />
                    </form>
                </p>';
            }

            $out .= \TracyDebugger::generatePanelFooter('processwireLogs', \Tracy\Debugger::timer('processwireLogs'), strlen($out), 'processwireAndTracyLogsPanels');

        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}
