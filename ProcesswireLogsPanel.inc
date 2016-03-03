<?php

use Tracy\IBarPanel;

/**
 * Processwire logs panel
 */

class ProcesswireLogsPanel implements IBarPanel {
    public function getTab() {
        return '
        <span title="ProcessWire logs">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 433.494 433.494" style="enable-background:new 0 0 433.494 433.494;" xml:space="preserve">
                <polygon points="353.763,379.942 279.854,234.57 322.024,250.717 253.857,116.637 276.286,127.997 216.747,0 157.209,127.997     179.64,116.636 111.471,250.717 153.642,234.569 79.731,379.942 200.872,337.52 200.872,433.494 232.624,433.494 232.624,337.518       " fill="#ee1d62"/>
            </svg>' . (\TracyDebugger::getDataValue('showPanelLabels') ? 'PW Logs' : '') . '
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

        $PwVersion = wire('config')->version;

        // end for each section
        $sectionEnd = '
                    </tbody>
                </table>
            </div>';


        /**
         * PW log panel sections
         */

        $logEntries = $this->sectionHeader(array('Type', 'Date', 'User', 'URL', 'Text'));
        $entriesArr = array();
        foreach(wire('log')->getLogs() as $log) {
            //if(!in_array($log['name'], array('errors', 'exceptions', 'messages', 'warnings'))) continue;
            $i=0;
            foreach(wire('log')->getEntries($log['name'], array("limit" => 10)) as $entry) {
                $itemKey = strtotime($entry['date']) + $i;
                $entriesArr[$itemKey] = $entry;
                $entriesArr[$itemKey]['log'] = $log['name'];
                $i++;
            }
        }

        //sort by key which is a timestamp from the date
        krsort($entriesArr, SORT_NUMERIC);

        //display 25 most recent entries from all log files
        foreach(array_slice($entriesArr, 0, 25) as $item) {
            $logEntries .= "\n<tr>" .
            "<td><a href='".wire('config')->urls->admin."setup/logs/view/".$item['log']."/'>".$item['log']."</a></td>" .
            "<td>".str_replace('-','&#8209;',str_replace(' ','&nbsp;',$item['date']))."</td>" .
            "<td>".$item['user']."</td>" .
            "<td>".$item['url']."</td>";
            $trimmedText = trim(strip_tags($item['text']));
            $logEntries .= "<td>".(strlen($trimmedText) > 350 ? substr($trimmedText,0, 350)." ... (".strlen($trimmedText).")" : $trimmedText)."</td>" .
            "</tr>";
        }
        $logEntries .= $sectionEnd;



        // Load all the panel sections
        $out = '
        <h1>
            <a title="ProcessWire Logs" href="'.wire('config')->urls->admin.'setup/logs/">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 433.494 433.494" style="enable-background:new 0 0 433.494 433.494;" xml:space="preserve" style="height:16px">
                    <polygon points="353.763,379.942 279.854,234.57 322.024,250.717 253.857,116.637 276.286,127.997 216.747,0 157.209,127.997     179.64,116.636 111.471,250.717 153.642,234.569 79.731,379.942 200.872,337.52 200.872,433.494 232.624,433.494 232.624,337.518       " fill="#ee1d62"/>
                </svg>
                ProcessWire Logs
            </a>
        </h1>

        <div class="tracy-inner">';
            $out .= $logEntries . '
        </div>';

        return $out;
    }

}
