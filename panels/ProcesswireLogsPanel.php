<?php

class ProcesswireLogsPanel extends BasePanel {

    protected $icon;
    protected $iconColor;
    protected $logEntries;
    protected $numLogEntries = 0;

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

        $isNew = 0;
        $isNewErrors = 0;
        $entriesArr = array();

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
            $cachedLogLinesData = $logLinesData;

            $errorLogs = array('errors', 'exceptions', 'files-errors');
            foreach($logs as $log) {

                $lines = \TracyDebugger::tailCustom($this->wire('config')->paths->logs.$log['name'].'.txt', \TracyDebugger::getDataValue("numLogEntries"));
                $lines = mb_convert_encoding($lines, 'UTF-8');
                $lines = explode("\n", $lines);
                foreach($lines as $key => $line) {
                    $entry = $this->wire('log')->lineToEntry($line);
                    $lines[$key] = $entry;
                }

                $x=99;
                if(!$logLinesData || !isset($logLinesData[$log['name']]) || filemtime($this->wire('log')->getFilename($log['name'])) > $logLinesData[$log['name']]['time']) {
                    $logLinesData[$log['name']]['time'] = time();
                    $logLinesData[$log['name']]['lines'] = $lines;
                    $isNew++;
                    if(in_array($log['name'], $errorLogs)) {
                        $isNewErrors++;
                    }
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
                    $entriesArr[$itemKey]['url'] = "<a href='".$entry['url']."'>".$entry['url']."</a>";
                    $entriesArr[$itemKey]['log'] = $log['name'];
                    $x--;
                    $this->numLogEntries++;
                }

            }

            if(count($entriesArr)) {
                $timestamp = array();
                $order = array();
                // get a list of sort columns and their data to pass to array_multisort
                foreach($entriesArr as $key => $row) {
                    $timestamp[$key] = $row['timestamp'];
                    $order[$key] = $row['order'];
                }

                array_multisort($timestamp, SORT_DESC, $order, SORT_ASC, SORT_NATURAL, $entriesArr);

                //display most recent entries from all log files
                foreach(array_slice($entriesArr, 0, \TracyDebugger::getDataValue("numLogEntries")) as $item) {

                    if(in_array($item['log'], $errorLogs)) {
                        $isError = true;
                        $color = \TracyDebugger::COLOR_ALERT;
                    }
                    else {
                        $isError = false;
                        $color = \TracyDebugger::COLOR_WARN;
                    }

                    $trimmedText = trim(htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'));
                    $lineIsNew = !isset($cachedLogLinesData[$item['log']]) || (isset($cachedLogLinesData[$item['log']]) && strtotime($item['date']) > $cachedLogLinesData[$item['log']]['time']);
                    $this->logEntries .= "
                    \n<tr>
                        <td ".($lineIsNew ? 'style="background: '.$color.' !important; color: #FFFFFF !important"' : '')."><a ".($lineIsNew ? 'style="color: #FFFFFF !important"' : '')." title='View \"".$item['log']."\" log file in PW admin' href='".$this->wire('config')->urls->admin."setup/logs/view/".$item['log']."/'>".str_replace('-', '&#8209;', $item['log'])."</a></td>" .
                        "<td>".str_replace('-','&#8209;',str_replace(' ','&nbsp;',$item['date']))."</td>" .
                        "<td>".$item['user']."</td>" .
                        "<td>".$item['url']."</td>" .
                        "<td>".\TracyDebugger::createEditorLink($this->wire('config')->paths->logs . $item['log'] . '.txt', 1, (strlen($trimmedText) > 350 ? substr($trimmedText,0, 350)." ... (".strlen($trimmedText).")" : $trimmedText), 'View in your code editor')."</td>" .
                    "</tr>";
                }
                $this->logEntries .= $sectionEnd;
            }
        }

        // color icon based on errors/other log entries
        if($isNewErrors > 0) {
            $this->iconColor = \TracyDebugger::COLOR_ALERT;
        }
        elseif($isNew > 0) {
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
