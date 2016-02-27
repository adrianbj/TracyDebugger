<?php

use Tracy\IBarPanel;

/**
 * Tracy logs panel
 */

class TracyLogsPanel implements IBarPanel {
    public function getTab() {
        return '
        <span title="Tracy logs">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 433.494 433.494" style="enable-background:new 0 0 433.494 433.494;" xml:space="preserve">
                <polygon points="353.763,379.942 279.854,234.57 322.024,250.717 253.857,116.637 276.286,127.997 216.747,0 157.209,127.997     179.64,116.636 111.471,250.717 153.642,234.569 79.731,379.942 200.872,337.52 200.872,433.494 232.624,433.494 232.624,337.518       " fill="#444444"/>
            </svg>' . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Tracy Logs' : '') . '
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
        if($name !== wire('sanitizer')->pageName($name)) {
            throw new WireException("Log name must contain only [-_.a-z0-9] with no extension");
        }
        return wire('config')->paths->logs.'tracy/' . $name . '.log';
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
        $dir = new DirectoryIterator(wire('config')->paths->logs.'tracy/');

        foreach($dir as $file) {
            if($file->isDot() || $file->isDir()) continue;
            if($file->getExtension() != 'log') continue;
            $name = basename($file, '.log');
            if($name != wire('sanitizer')->pageName($name)) continue;
            $logs[$name] = array(
                'name' => $name,
                'file' => $file->getPathname(),
                'size' => $file->getSize(),
                'modified' => $file->getMTime(),
            );
        }

        ksort($logs);
        return $logs;
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
        $pageNum = !empty($options['pageNum']) ? $options['pageNum'] : wire('input')->pageNum;
        unset($options['pageNum']);
        $log = $this->getFileLog($name);
        $limit = isset($options['limit']) ? (int) $options['limit'] : 100;
        return $log->find($limit, $pageNum);
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

        $logEntries = $this->sectionHeader(array('Type', 'Date', 'URL', 'Text'));
        $entriesArr = array();
        foreach($this->getLogs() as $log) {
            foreach($this->getLines($log['name'], array("limit" => 10)) as $entry) {
                $logDateTime = str_replace(array('[',']'), '', substr($entry, 0 , 21)); // get the date - first 21 chars
                $logDateParts = explode(" ", $logDateTime);
                $logDate = $logDateParts[0];
                $logTime = str_replace('-',':', $logDateParts[1]);
                $logDateTime = $logDate . ' ' . $logTime;
                $entriesArr[strtotime($logDateTime)]['date'] = $logDateTime;
                $entriesArr[strtotime($logDateTime)]['log'] = $log['name'];
                $entryUrlAndText = explode('@', substr($entry, 22)); // get the rest of the line after the date;
                $entriesArr[strtotime($logDateTime)]['url'] = trim($entryUrlAndText[1]);
                $entriesArr[strtotime($logDateTime)]['text'] = trim($entryUrlAndText[0]);
            }
        }

        //sort by key which is a timestamp from the date
        krsort($entriesArr, SORT_NUMERIC);
        //display 25 most recent entries from all log files
        foreach(array_slice($entriesArr, 0, 25) as $item) {
            $logEntries .= "\n<tr>" .
            "<td>".$item['log']."</td>" .
            "<td>".$item['date']."</td>" .
            "<td>".$item['url']."</td>" .
            "<td>".strip_tags($item['text'])."</td>" .
            "</tr>";
        }
        $logEntries .= $sectionEnd;



        // Load all the panel sections
        $out = '
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 433.494 433.494" style="enable-background:new 0 0 433.494 433.494;" xml:space="preserve" style="height:16px">
                <polygon points="353.763,379.942 279.854,234.57 322.024,250.717 253.857,116.637 276.286,127.997 216.747,0 157.209,127.997     179.64,116.636 111.471,250.717 153.642,234.569 79.731,379.942 200.872,337.52 200.872,433.494 232.624,433.494 232.624,337.518       " fill="#444444"/>
            </svg>
            Tracy Logs
        </h1>

        <div class="tracy-inner">';
            $out .= $logEntries . '
        </div>';

        return $out;
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
