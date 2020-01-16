<?php

class CaptainHookPanel extends BasePanel {

    protected $icon;
    protected $apiBaseUrl;

    public function __construct() {
        if($this->wire('modules')->isInstalled('ProcessWireAPI')) {
            $this->apiModuleInstalled = true;
            $apiModuleId = $this->wire('modules')->getModuleID("ProcessWireAPI");
            $this->apiBaseUrl = $this->wire('pages')->get("process=$apiModuleId")->url.'methods/';
        }
        else {
            $this->apiModuleInstalled = false;
            $this->apiBaseUrl = 'https://processwire.com/api/ref/';
        }
        $this->newTab = \TracyDebugger::getDataValue('linksNewTab') ? 'target="_blank"' : '';
    }

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('captainHook');

        $this->icon = '
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="16px" height="16px"
                 viewBox="0 0 16 16" enable-background="new 0 0 16 16" xml:space="preserve">
            <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M10.9,14.7L10.9,14.7l-0.1-2.1c0-1.2-0.7-2.2-1.8-2.6c0.1-0.1,0.1-0.2,0.1-0.3c0-0.2-0.2-0.5-0.4-0.5V8.4
                c0.9-0.2,1.8-0.7,2.4-1.4c0.7-0.8,1-1.8,1-2.8C12.2,1.9,10.3,0,8,0S3.8,1.9,3.8,4.2c0,0.2,0.1,0.3,0.1,0.3c0.1,0,0.2,0,0.2-0.1
                c1.1-2.6,2.6-3,3.9-3c1.5,0,2.8,1.2,2.8,2.8S9.5,7,8,7C7.8,7,7.6,7,7.4,6.9c-0.1,0-0.1,0-0.2,0.1c-0.1,0.1,0,0.1,0,0.1v2
                C7,9.2,6.8,9.4,6.8,9.7c0,0.1,0,0.2,0.1,0.3c-1.1,0.4-1.8,1.5-1.8,2.6v2.1H5.1c-0.4,0-0.7,0.3-0.7,0.7c0,0.4,0.3,0.7,0.7,0.7h5.9
                c0.4,0,0.7-0.3,0.7-0.7C11.6,15,11.3,14.7,10.9,14.7z"/>
            <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M7.6,7.3c0.1,0,0.3,0,0.4,0c1.7,0,3.1-1.4,3.1-3.1S9.7,1.1,8,1.1c-0.9,0-1.7,0.2-2.3,0.7
                c-0.5,0.4-1,0.9-1.5,1.6C4.6,1.7,6.1,0.4,8,0.4c2.1,0,3.9,1.7,3.9,3.9c0,1.9-1.4,3.6-3.3,3.8c-0.1,0-0.2,0.1-0.2,0.2h0v0.8H7.6
                L7.6,7.3L7.6,7.3z"/>
            <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M7.4,9.5h1.3c0.1,0,0.2,0.1,0.2,0.2c0,0.1-0.1,0.2-0.2,0.2H7.4c-0.1,0-0.2-0.1-0.2-0.2
                C7.2,9.6,7.3,9.5,7.4,9.5z"/>
            <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M5.5,12.6c0-1.1,0.7-2.1,1.8-2.4c0,0,0.1,0,0.1,0h1.3c0,0,0.1,0,0.1,0c1,0.3,1.8,1.3,1.8,2.4v2.1h-5V12.6z"
                />
            <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M10.9,15.6H5.1c-0.2,0-0.3-0.1-0.3-0.3c0-0.2,0.1-0.3,0.3-0.3h5.9c0.2,0,0.3,0.1,0.3,0.3
                C11.2,15.5,11.1,15.6,10.9,15.6z"/>
            </svg>';

        return '
        <span title="Captain Hook">' .
            $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Captain Hook' : '') . '
        </span>';
    }

    public function getPanel() {

        $out = '
        <h1>' . $this->icon . ' Captain Hook</h1><span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'CaptainHookPanel\')">+</a></span></span>';

        $tracyModuleUrl = $this->wire('config')->urls->TracyDebugger;
        $out .= <<< HTML
        <script>
            tracyJSLoader.load("{$tracyModuleUrl}scripts/filterbox/filterbox.js", function() {
                tracyJSLoader.load("{$tracyModuleUrl}scripts/captain-hook-search.js");
            });
        </script>
HTML;

        $out .= '
        <div class="tracy-inner">
        ';

        $hooks = \TracyDebugger::getApiData('hooks');

        $lastSection = null;
        $sections = array();
        foreach($hooks as $file => $info) {
            $name = pathinfo($info['filename'], PATHINFO_FILENAME);
            $label = str_replace($this->wire('config')->paths->root, '', $info['filename']);
            $label = \TracyDebugger::forwardSlashPath($label);
            $path = parse_url($label, PHP_URL_PATH);
            $segments = explode('/', $path);
            $currentSection = ucfirst($segments[0]) . ' ' . ucfirst($segments[1]);
            $currentSectionIndex = str_replace(' ', '_', strtolower($currentSection));
            if($currentSection !== $lastSection) {
                $sections[$currentSectionIndex] = '';
                $sections[$currentSectionIndex] .= '<h3>'.$currentSection.'</h3>';
            }
            $sections[$currentSectionIndex] .= '
            <a href="#" rel="'.$name.'" class="tracy-toggle tracy-collapsed">'.str_replace($segments[0].'/'.$segments[1].'/', '', $label).'</a>
            <div id="'.$name.'" class="tracy-collapsed"><p>'.(isset($info['classname']) && (!in_array('site', $segments) || $this->apiModuleInstalled) ? '<a '.$this->newTab.' href="'.$this->apiBaseUrl.$this->convertNamesToUrls($info['classname']).'/">'.$info['classname'].'</a> ' : $info['classname']).(isset($info['extends']) ? ' extends <a '.$this->newTab.' href="'.$this->apiBaseUrl.$this->convertNamesToUrls($info['extends']).'/">'.$info['extends'].'</a>' : '').'</p>'.$this->buildHookTable($info).'</div>';
            $lastSection = $currentSection;
        }

        $out .= $sections['wire_core'] . $sections['wire_modules'] . $sections['site_modules'];

        $out .= \TracyDebugger::generatePanelFooter('captainHook', \Tracy\Debugger::timer('captainHook'), strlen($out), 'captainHookPanel');
        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

    private function buildHookTable($info) {
        $out = '
            <table class="captainHookTable">';
        foreach($info['pwFunctions'] as $hook) {

            $name = $hook['name'];
            $methodName = str_replace(array('___', '__'), '', $name);
            if(strpos($hook['comment'], '#pw-internal') === false && strpos($info['filename'], 'wire') !== false || $this->apiModuleInstalled) {
                $name = "<a ".$this->newTab." href='".$this->apiBaseUrl.$this->convertNamesToUrls(str_replace('$', '', $info['classname']))."/".$this->convertNamesToUrls($hook['rawname'])."/'>" . $name . "</a>";
            }

            $out .= '
                <tr>
                    <td>'.$name.'</td>
                    <td>'.\TracyDebugger::createEditorLink($info['filename'], $hook['lineNumber'], $hook['lineNumber']).'</td>
                    <td class="tracy-force-no-wrap">' . $hook['comment'] . '</td>';
                    if(\TracyDebugger::getDataValue('captainHookShowDescription') && isset($hook['description'])) {
                        $out .= '<td class="tracy-force-no-wrap">' . $hook['description'] . '</td>';
                    }
                    else {
                        $out .= '<td></td>';
                    }
                $out .=
                '</tr>';
        }
        $out .= '
            </table>
        ';
        return $out;
    }

    private function convertNamesToUrls($str) {
        return trim(strtolower(preg_replace('/([A-Z])/', '-$1', $str)), '-');
    }

}
