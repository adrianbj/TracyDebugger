<?php

/**
 * PHP Info panel
 */

class PhpInfoPanel extends BasePanel {

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('phpInfo');

        return '
        <span title="PHP Info">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 16.172 16.172" style="enable-background:new 0 0 16.172 16.172;" xml:space="preserve" width="16px" height="16px">
                <g>
                    <path d="M13.043,6.367c-0.237,0-0.398,0.022-0.483,0.047v1.523c0.101,0.022,0.223,0.03,0.391,0.03    c0.621,0,1.004-0.313,1.004-0.842C13.954,6.651,13.625,6.367,13.043,6.367z" fill="#8892bf"/>
                    <path d="M15.14,0H1.033C0.463,0,0,0.462,0,1.032v14.108c0,0.568,0.462,1.031,1.033,1.031H15.14    c0.57,0,1.032-0.463,1.032-1.031V1.032C16.172,0.462,15.71,0,15.14,0z M4.904,8.32C4.506,8.695,3.916,8.863,3.227,8.863    c-0.153,0-0.291-0.008-0.398-0.023v1.846H1.673V5.594c0.36-0.061,0.865-0.107,1.578-0.107c0.719,0,1.233,0.139,1.577,0.414    C5.158,6.162,5.38,6.59,5.38,7.095S5.211,8.029,4.904,8.32z M10.382,10.686H9.218v-2.16H7.297v2.16H6.125V5.526h1.172v1.983h1.921    V5.526h1.164C10.382,5.526,10.382,10.686,10.382,10.686z M14.635,8.32c-0.397,0.375-0.987,0.543-1.677,0.543    c-0.152,0-0.291-0.008-0.398-0.023v1.846h-1.155V5.594c0.359-0.061,0.864-0.107,1.577-0.107c0.72,0,1.232,0.139,1.577,0.414    c0.33,0.261,0.552,0.689,0.552,1.194C15.11,7.6,14.942,8.029,14.635,8.32z" fill="#8892bf"/>
                    <path d="M3.312,6.367c-0.238,0-0.398,0.022-0.483,0.047v1.523c0.1,0.022,0.222,0.03,0.391,0.03    c0.62,0,1.003-0.313,1.003-0.842C4.223,6.651,3.894,6.367,3.312,6.367z" fill="#8892bf"/>
                </g>
            </svg>' . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;PHP Info' : '') . '
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


    function removeElementsByTagName($tagName, $document) {
        $nodeList = $document->getElementsByTagName($tagName);
        for($nodeIdx = $nodeList->length; --$nodeIdx >= 0; ) {
            $node = $nodeList->item($nodeIdx);
            $node->parentNode->removeChild($node);
        }
    }

    public function getPanel() {

        // data output from phpinfo()
        ob_start();
        phpinfo();
        $phpInfo = ob_get_clean();

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        if(function_exists('mb_convert_encoding')) {
            $dom->loadHTML(mb_convert_encoding($phpInfo, 'HTML-ENTITIES', 'UTF-8'));
        }
        else {
            $dom->loadHTML(htmlspecialchars_decode(utf8_decode(htmlentities($phpInfo, ENT_COMPAT, 'UTF-8', false))));
        }
        libxml_use_internal_errors(false);
        $body = $dom->getElementsByTagName('body')->item(0);
        $this->removeElementsByTagName('img', $body);
        $phpInfo = $dom->saveHTML($body);


        // Load all the panel sections
        $out = '
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 16.172 16.172" style="enable-background:new 0 0 16.172 16.172;" xml:space="preserve" width="16px" height="16px">
                <g>
                    <path d="M13.043,6.367c-0.237,0-0.398,0.022-0.483,0.047v1.523c0.101,0.022,0.223,0.03,0.391,0.03    c0.621,0,1.004-0.313,1.004-0.842C13.954,6.651,13.625,6.367,13.043,6.367z" fill="#8892bf"/>
                    <path d="M15.14,0H1.033C0.463,0,0,0.462,0,1.032v14.108c0,0.568,0.462,1.031,1.033,1.031H15.14    c0.57,0,1.032-0.463,1.032-1.031V1.032C16.172,0.462,15.71,0,15.14,0z M4.904,8.32C4.506,8.695,3.916,8.863,3.227,8.863    c-0.153,0-0.291-0.008-0.398-0.023v1.846H1.673V5.594c0.36-0.061,0.865-0.107,1.578-0.107c0.719,0,1.233,0.139,1.577,0.414    C5.158,6.162,5.38,6.59,5.38,7.095S5.211,8.029,4.904,8.32z M10.382,10.686H9.218v-2.16H7.297v2.16H6.125V5.526h1.172v1.983h1.921    V5.526h1.164C10.382,5.526,10.382,10.686,10.382,10.686z M14.635,8.32c-0.397,0.375-0.987,0.543-1.677,0.543    c-0.152,0-0.291-0.008-0.398-0.023v1.846h-1.155V5.594c0.359-0.061,0.864-0.107,1.577-0.107c0.72,0,1.232,0.139,1.577,0.414    c0.33,0.261,0.552,0.689,0.552,1.194C15.11,7.6,14.942,8.029,14.635,8.32z" fill="#8892bf"/>
                    <path d="M3.312,6.367c-0.238,0-0.398,0.022-0.483,0.047v1.523c0.1,0.022,0.222,0.03,0.391,0.03    c0.62,0,1.003-0.313,1.003-0.842C4.223,6.651,3.894,6.367,3.312,6.367z" fill="#8892bf"/>
                </g>
            </svg>
            PHP Info
        </h1>
        <div class="tracy-inner">
            <style type="text/css">
                #phpinfoBody pre {margin: 0; font-family: monospace;}
                #phpinfoBody a:link {color: #009; text-decoration: none; background-color: #fff;}
                #phpinfoBody a:hover {text-decoration: underline;}
                #phpinfoBody table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
                #phpinfoBody .center {text-align: center;}
                #phpinfoBody .center table {margin: 1em auto; text-align: left; width:100% !important;}
                #phpinfoBody .center th {text-align: center !important;}
                #phpinfoBody td, th {border: 1px solid #666; font-size: 100%; vertical-align: baseline; padding: 4px 5px;}
                #phpinfoBody h1 {font-size: 175%;}
                #phpinfoBody h2 {font-size: 150%;}
                #phpinfoBody .p {text-align: left;}
                #phpinfoBody .e {background-color: #ccf !important; width: 300px; font-weight: bold; }
                #phpinfoBody .h th {background-color: #99c !important; font-weight: bold; }
                #phpinfoBody .v {background-color: #ddd !important; max-width: 300px;}
                #phpinfoBody .v i {color: #999 !important; }
                #phpinfoBody img {float: right; border: 0;}
                #phpinfoBody hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
                #phpinfoBody td {border: 1px solid #000 !important;}
                #phpinfoBody th {border: 1px solid #000 !important; color:#000 !important;}
            </style>
            <br />
            <div id="phpinfoBody" style="width:934px">'.$phpInfo.'</div>';

            $out .= \TracyDebugger::generatedTimeSize('phpInfo', \Tracy\Debugger::timer('phpInfo'), strlen($out));
        $out .= '</div>
        ';

        return parent::loadResources() . $out;
    }

}
