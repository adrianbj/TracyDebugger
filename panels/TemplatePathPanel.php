<?php

class TemplatePathPanel extends BasePanel {

    protected $icon;
    protected $userDevTemplatePossible;
    protected $templateOptions;

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('templatePath');

        $userDevTemplateFiles = 0;
        $templateBasename = pathinfo(wire('page')->template->filename, PATHINFO_BASENAME);
        $templateFilenames = array();
        foreach($this->wire('templates') as $t) {
            array_push($templateFilenames, pathinfo($t->filename, PATHINFO_BASENAME));
        }
        foreach(new \DirectoryIterator($this->wire('config')->paths->templates) as $file) {
            if($file->isFile()) {
                $fileName = pathinfo($file, PATHINFO_FILENAME);
                $fileExt = pathinfo($file, PATHINFO_EXTENSION);
                $baseName = pathinfo($file, PATHINFO_BASENAME);
                if(in_array($baseName, $templateFilenames) && $templateBasename != $baseName) continue;
                if(strpos($file, '-'.\TracyDebugger::getDataValue('userDevTemplateSuffix').'.'.$fileExt)) $userDevTemplateFiles++;
                if(strpos($fileName, $this->wire('page')->template->name) !== false && strpos($fileName, '-tracytemp') === false) { // '-tracytemp' is extension from template editor panel
                    $this->templateOptions .= '<option value="'.$file.'"'.(pathinfo($this->wire('page')->template->filename, PATHINFO_BASENAME) == $file ? 'selected="selected"' : '').'>'.$file.'</option>';
                }
            }
        }

        $this->userDevTemplatePossible = \TracyDebugger::getDataValue('userDevTemplate')
            && ($this->wire('permissions')->get("tracy-all-dev")->id || $this->wire('permissions')->get("tracy-".$this->wire('page')->template->name."-dev")->id)
            && $userDevTemplateFiles > 0
            && $this->wire('users')->count("roles.permissions=tracy-all-".\TracyDebugger::getDataValue('userDevTemplateSuffix')."|tracy-".$this->wire('page')->template->name."-".\TracyDebugger::getDataValue('userDevTemplateSuffix'));

        if(\TracyDebugger::$templatePath) {
            $iconColor = \TracyDebugger::COLOR_ALERT;
        }
        elseif($this->wire('input')->cookie->tracyTemplatePathSticky != '' || $this->userDevTemplatePossible) {
            $iconColor = \TracyDebugger::COLOR_WARN;
        }
        else {
            $iconColor = \TracyDebugger::COLOR_NORMAL;
        }

        $this->icon = '
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 86.02 86.02" style="enable-background:new 0 0 86.02 86.02;" xml:space="preserve"  style="height:16px">
                <path d="M0.354,48.874l0.118,25.351c0.001,0.326,0.181,0.624,0.467,0.779l20.249,10.602c0.132,0.071,0.276,0.106,0.421,0.106   c0.001,0,0.001,0,0.002,0c0.061,0.068,0.129,0.133,0.211,0.182c0.14,0.084,0.297,0.126,0.455,0.126   c0.146,0,0.291-0.035,0.423-0.106l19.992-10.842c0.183-0.099,0.315-0.261,0.392-0.445c0.081,0.155,0.203,0.292,0.364,0.379   l20.248,10.602c0.132,0.071,0.277,0.106,0.422,0.106c0.001,0,0.001,0,0.002,0c0.062,0.068,0.129,0.133,0.21,0.182   c0.142,0.084,0.299,0.126,0.456,0.126c0.146,0,0.29-0.035,0.422-0.106L85.2,75.071c0.287-0.154,0.467-0.456,0.467-0.783V47.911   c0-0.008-0.004-0.016-0.004-0.022c0-0.006,0.002-0.013,0.002-0.021c-0.001-0.023-0.01-0.049-0.014-0.072   c-0.007-0.05-0.014-0.098-0.027-0.146c-0.011-0.031-0.023-0.062-0.038-0.093c-0.019-0.042-0.037-0.082-0.062-0.12   c-0.019-0.03-0.04-0.058-0.062-0.084c-0.028-0.034-0.059-0.066-0.092-0.097c-0.025-0.023-0.054-0.045-0.083-0.066   c-0.02-0.012-0.034-0.03-0.056-0.043c-0.02-0.011-0.041-0.017-0.062-0.025c-0.019-0.01-0.03-0.022-0.049-0.029l-20.603-9.978   c-0.082-0.034-0.17-0.038-0.257-0.047V10.865c0-0.007-0.002-0.015-0.002-0.022c-0.001-0.007,0.001-0.013,0.001-0.02   c-0.001-0.025-0.012-0.049-0.015-0.073c-0.007-0.049-0.014-0.098-0.027-0.145c-0.01-0.032-0.024-0.063-0.038-0.093   c-0.02-0.042-0.036-0.083-0.062-0.12c-0.02-0.03-0.041-0.057-0.062-0.084c-0.028-0.034-0.058-0.067-0.091-0.097   c-0.025-0.023-0.055-0.045-0.083-0.065c-0.021-0.014-0.035-0.032-0.056-0.045c-0.021-0.011-0.042-0.016-0.062-0.026   c-0.019-0.009-0.031-0.021-0.048-0.027L43.118,0.07c-0.24-0.102-0.512-0.093-0.746,0.025L22.009,10.71   c-0.299,0.151-0.487,0.456-0.489,0.79c0,0.006,0.002,0.011,0.002,0.016c-0.037,0.099-0.063,0.202-0.063,0.312l0.118,25.233   c-0.106,0.011-0.213,0.03-0.311,0.079L0.903,47.755c-0.298,0.15-0.487,0.456-0.489,0.791c0,0.005,0.003,0.009,0.003,0.015   C0.379,48.659,0.353,48.764,0.354,48.874z M61.321,10.964L43.372,21l-19.005-9.485l18.438-9.646L61.321,10.964z M62.486,37.008   l-18.214,9.586V22.535l18.214-10.18V37.008z M65.674,59.58l18.214-10.179v24.355l-18.214,9.883V59.58z M45.77,48.559l18.438-9.646   l18.515,9.099L64.775,58.045L45.77,48.559z M23.165,59.58L41.38,49.402v24.355l-18.215,9.882V59.58z M3.262,48.559L21.7,38.913   l18.515,9.099L22.266,58.045L3.262,48.559z" fill="'.$iconColor.'"/>
            </svg>';


        return '
            <span title="Template Path">
                ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Template Path' : '') . '
            </span>
        ';
    }


    public function getPanel() {

        $templateIcon = '';
        if(\TracyDebugger::$templatePath && !\TracyDebugger::$templatePathSticky) {
            if(\TracyDebugger::$templatePathOnce) {
                $templateIcon = '
                    <span title="Template Changed Once">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="12px" height="12px" viewBox="0 0 405.07 405.07" style="enable-background:new 0 0 405.07 405.07;" xml:space="preserve">
                            <path d="M202.531,0C90.676,0,0,90.678,0,202.535C0,314.393,90.676,405.07,202.531,405.07c111.859,0,202.539-90.678,202.539-202.535   C405.07,90.678,314.391,0,202.531,0z M243.192,312.198c0,8.284-6.716,15-15,15h-27.629c-8.284,0-15-6.716-15-15v-168.35   l-17.1,9.231c-4.069,2.197-8.924,2.393-13.155,0.536c-4.233-1.858-7.373-5.565-8.51-10.046l-5.459-21.518   c-1.695-6.683,1.383-13.66,7.461-16.913l47.626-25.491c2.177-1.166,4.608-1.775,7.078-1.775h24.688c8.284,0,15,6.716,15,15V312.198   z" fill="'.\TracyDebugger::COLOR_WARN.'" />
                        </svg>
                    </span>';
            }
            elseif(\TracyDebugger::$templatePathPermission) {
                $templateIcon = '
                    <span title="Template Changed by User Permission">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 268.765 268.765" style="enable-background:new 0 0 268.765 268.765;" xml:space="preserve" width="16px" height="16px">
                            <path style="fill-rule:evenodd;clip-rule:evenodd;" d="M267.92,119.461c-0.425-3.778-4.83-6.617-8.639-6.617    c-12.315,0-23.243-7.231-27.826-18.414c-4.682-11.454-1.663-24.812,7.515-33.231c2.889-2.641,3.24-7.062,0.817-10.133    c-6.303-8.004-13.467-15.234-21.289-21.5c-3.063-2.458-7.557-2.116-10.213,0.825c-8.01,8.871-22.398,12.168-33.516,7.529    c-11.57-4.867-18.866-16.591-18.152-29.176c0.235-3.953-2.654-7.39-6.595-7.849c-10.038-1.161-20.164-1.197-30.232-0.08    c-3.896,0.43-6.785,3.786-6.654,7.689c0.438,12.461-6.946,23.98-18.401,28.672c-10.985,4.487-25.272,1.218-33.266-7.574    c-2.642-2.896-7.063-3.252-10.141-0.853c-8.054,6.319-15.379,13.555-21.74,21.493c-2.481,3.086-2.116,7.559,0.802,10.214    c9.353,8.47,12.373,21.944,7.514,33.53c-4.639,11.046-16.109,18.165-29.24,18.165c-4.261-0.137-7.296,2.723-7.762,6.597    c-1.182,10.096-1.196,20.383-0.058,30.561c0.422,3.794,4.961,6.608,8.812,6.608c11.702-0.299,22.937,6.946,27.65,18.415    c4.698,11.454,1.678,24.804-7.514,33.23c-2.875,2.641-3.24,7.055-0.817,10.126c6.244,7.953,13.409,15.19,21.259,21.508    c3.079,2.481,7.559,2.131,10.228-0.81c8.04-8.893,22.427-12.184,33.501-7.536c11.599,4.852,18.895,16.575,18.181,29.167    c-0.233,3.955,2.67,7.398,6.595,7.85c5.135,0.599,10.301,0.898,15.481,0.898c4.917,0,9.835-0.27,14.752-0.817    c3.897-0.43,6.784-3.786,6.653-7.696c-0.451-12.454,6.946-23.973,18.386-28.657c11.059-4.517,25.286-1.211,33.281,7.572    c2.657,2.89,7.047,3.239,10.142,0.848c8.039-6.304,15.349-13.534,21.74-21.494c2.48-3.079,2.13-7.559-0.803-10.213    c-9.353-8.47-12.388-21.946-7.529-33.524c4.568-10.899,15.612-18.217,27.491-18.217l1.662,0.043    c3.853,0.313,7.398-2.655,7.865-6.588C269.044,139.917,269.058,129.639,267.92,119.461z M134.595,179.491    c-24.718,0-44.824-20.106-44.824-44.824c0-24.717,20.106-44.824,44.824-44.824c24.717,0,44.823,20.107,44.823,44.824    C179.418,159.385,159.312,179.491,134.595,179.491z" fill="'.\TracyDebugger::COLOR_NORMAL.'"></path>
                        </svg>
                    </span>';
            }
        }

        $out = '
        <script>
            function getTracyTemplatePath() {
                var e = document.getElementById("tracyTemplatePath");
                return "'.wire("page")->template->name.'|"+e.options[e.selectedIndex].value;
            }

            function changeTemplatePath(type) {
                var existingCookie = getCookie("tracyTemplatePath"+type);
                if(existingCookie === undefined) existingCookie = "";
                if(existingCookie !== "") existingCookie = existingCookie + ",";
                existingCookie = removeCurrentTemplateFromCookie(existingCookie);
                document.cookie = "tracyTemplatePath"+type+"="+existingCookie+getTracyTemplatePath()+"; expires=0; path=/";
                location.reload();
            }

            function resetTemplatePath() {
                var existingCookie = getCookie("tracyTemplatePathSticky");
                existingCookie = removeCurrentTemplateFromCookie(existingCookie);
                document.cookie = "tracyTemplatePathSticky="+existingCookie+"; expires=0; path=/";
                location.reload();
            }

            function removeCurrentTemplateFromCookie(existingCookie) {
                if(existingCookie) {
                    var templateArr = existingCookie.split(",");
                    for(var i = 0; i < templateArr.length; i++) {
                        if(templateArr[i].indexOf("'.wire("page")->template->name.'") > -1) {
                            templateArr.splice(i, 1);
                        }
                    }
                    if(templateArr.length > 0)
                        return templateArr.join(",");
                        return "";
                }
                else {
                    return "";
                }
            }

            function resetAllTemplatePaths() {
                document.cookie = "tracyTemplatePathSticky=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                document.cookie = "tracyTemplatePathOnce=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                location.reload();
            }

            function getCookie(name) {
                var value = "; " + document.cookie;
                var parts = value.split("; " + name + "=");
                if(parts.length == 2) return parts.pop().split(";").shift();
            }
        </script>
        ';


        $out .= '<h1>'.$this->icon.' Template Path</h1>
        <div class="tracy-inner">
            <fieldset>
                <legend>Temporarily use a template file with a suffix.<br />eg. '.$this->wire('page')->template->name.'-dev.php.<br /><br />Select an alternate from the list.<br />Create the file in your templates directory first.</legend><br />';
                $out .= '
                <select id="tracyTemplatePath">';
                    $out .= $this->templateOptions . '
                </select>' . $templateIcon . '<br /><br />
                <input type="submit" onclick="changeTemplatePath(\'Once\')" value="Once" />&nbsp;
                <input type="submit" onclick="changeTemplatePath(\'Sticky\')" value="Sticky" />&nbsp;
                <input type="submit" onclick="resetTemplatePath()" value="Reset" />&nbsp;
                <input type="submit" onclick="resetAllTemplatePaths()" value="Reset All" />
            </fieldset>';

            if($this->userDevTemplatePossible) {
                $out .= '<br /><br />
                <legend>**User Dev Template Reminder**
                    <ul>
                        <li>the "User Dev Template" option is enabled in module settings
                        <li>the "tracy-all-'.\TracyDebugger::getDataValue('userDevTemplateSuffix').'" or "tracy-'.$this->wire('page')->template->name.'-'.\TracyDebugger::getDataValue('userDevTemplateSuffix').'" permission exists</li>
                        <li>the permission has been assigned to at least one user</li>
                        <li>there are template files with a "-'.\TracyDebugger::getDataValue('userDevTemplateSuffix').'" suffix</li>
                    </ul>
                </legend>';
            }

        $out .= \TracyDebugger::generatePanelFooter('templatePath', \Tracy\Debugger::timer('templatePath'), strlen($out));

        $out .= '</div>';

        return parent::loadResources() . $out;
    }

}
