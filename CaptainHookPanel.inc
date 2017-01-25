<?php

use Tracy\Debugger;
use Tracy\IBarPanel;
use Tracy\Dumper;

/**
 * Custom PW panel
 */

class CaptainHookPanel implements IBarPanel {

    protected $icon;
    public $hooks;

    public function getTab() {

        if(\TracyDebugger::additionalBar()) return;
        \Tracy\Debugger::timer('captainHook');

        $this->icon = '
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="16px" height="16px"
                 viewBox="0 0 16 16" enable-background="new 0 0 16 16" xml:space="preserve">
            <path fill="#EE1D62" d="M10.9,14.7L10.9,14.7l-0.1-2.1c0-1.2-0.7-2.2-1.8-2.6c0.1-0.1,0.1-0.2,0.1-0.3c0-0.2-0.2-0.5-0.4-0.5V8.4
                c0.9-0.2,1.8-0.7,2.4-1.4c0.7-0.8,1-1.8,1-2.8C12.2,1.9,10.3,0,8,0S3.8,1.9,3.8,4.2c0,0.2,0.1,0.3,0.1,0.3c0.1,0,0.2,0,0.2-0.1
                c1.1-2.6,2.6-3,3.9-3c1.5,0,2.8,1.2,2.8,2.8S9.5,7,8,7C7.8,7,7.6,7,7.4,6.9c-0.1,0-0.1,0-0.2,0.1c-0.1,0.1,0,0.1,0,0.1v2
                C7,9.2,6.8,9.4,6.8,9.7c0,0.1,0,0.2,0.1,0.3c-1.1,0.4-1.8,1.5-1.8,2.6v2.1H5.1c-0.4,0-0.7,0.3-0.7,0.7c0,0.4,0.3,0.7,0.7,0.7h5.9
                c0.4,0,0.7-0.3,0.7-0.7C11.6,15,11.3,14.7,10.9,14.7z"/>
            <path fill="#EE1D62" d="M7.6,7.3c0.1,0,0.3,0,0.4,0c1.7,0,3.1-1.4,3.1-3.1S9.7,1.1,8,1.1c-0.9,0-1.7,0.2-2.3,0.7
                c-0.5,0.4-1,0.9-1.5,1.6C4.6,1.7,6.1,0.4,8,0.4c2.1,0,3.9,1.7,3.9,3.9c0,1.9-1.4,3.6-3.3,3.8c-0.1,0-0.2,0.1-0.2,0.2h0v0.8H7.6
                L7.6,7.3L7.6,7.3z"/>
            <path fill="#EE1D62" d="M7.4,9.5h1.3c0.1,0,0.2,0.1,0.2,0.2c0,0.1-0.1,0.2-0.2,0.2H7.4c-0.1,0-0.2-0.1-0.2-0.2
                C7.2,9.6,7.3,9.5,7.4,9.5z"/>
            <path fill="#EE1D62" d="M5.5,12.6c0-1.1,0.7-2.1,1.8-2.4c0,0,0.1,0,0.1,0h1.3c0,0,0.1,0,0.1,0c1,0.3,1.8,1.3,1.8,2.4v2.1h-5V12.6z"
                />
            <path fill="#EE1D62" d="M10.9,15.6H5.1c-0.2,0-0.3-0.1-0.3-0.3c0-0.2,0.1-0.3,0.3-0.3h5.9c0.2,0,0.3,0.1,0.3,0.3
                C11.2,15.5,11.1,15.6,10.9,15.6z"/>
            </svg>';

        return '
        <span title="Captain Hook">' .
            $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Captain Hook' : '') . '
        </span>';
    }

    public function getPanel() {

        $out = '
        <h1>' . $this->icon . ' Captain Hook</h1>
        <div class="tracy-inner">
        ';


        $cachePath = wire('config')->paths->cache . 'TracyDebugger/';
        $hooksFile = $cachePath.'hooks.txt';

        $configData = wire('modules')->getModuleConfigData("TracyDebugger");
        if(!file_exists($hooksFile) || !isset($configData['hooksPwVersion']) || wire('config')->version != $configData['hooksPwVersion']) {
            $configData['hooksPwVersion'] = wire('config')->version;
            wire('modules')->saveModuleConfigData(wire('modules')->get("TracyDebugger"), $configData);

            require_once wire('config')->paths->TracyDebugger . 'CaptainHook/generate-html.php';

            if(!is_dir($cachePath)) if(!wireMkdir($cachePath)) {
                throw new WireException("Unable to create cache path: $cachePath");
            }

            if(!file_put_contents($hooksFile, serialize($this->hooks), LOCK_EX)) throw new WireException("Unable to write file: $hooksFile");
            if(wire('config')->chmodFile) chmod($hooksFile, octdec(wire('config')->chmodFile));

        }

        $cachedHooks = unserialize(file_get_contents($hooksFile));
        foreach($cachedHooks as $file => $info) {
            $name = pathinfo($info['filename'], PATHINFO_FILENAME);
            $label = str_replace(wire('config')->paths->root, '/', $info['filename']);
            $out .= '
            <a href="#" rel="'.$name.'" class="tracy-toggle tracy-collapsed">'.$label.'</a>
            <div id="'.$name.'" class="tracy-collapsed">'.$this->buildHookTable($info).'</div><br />';
        }


        $out .= \TracyDebugger::generatedTimeSize('captainHook', \Tracy\Debugger::timer('captainHook'), strlen($out));
        $out .= '</div>';

        return $out;
    }

    private function buildHookTable($info) {
        $out = '
        <div>
            <table>';
        foreach($info['hooks'] as $hook) {
            $out .= '
                <tr>
                    <th><a href="'. \TracyDebugger::makePathLocal(str_replace("%file", $info['filename'], str_replace("%line", $hook['lineNumber'], \TracyDebugger::getDataValue("editor")))).'">'.$hook['lineNumber'].'</a></th>
                    <td>' . $hook['line'] . '</td>
                </tr>';
        }
        $out .= '
            </table>
        </div>';
        return $out;
    }

}
