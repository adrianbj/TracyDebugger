<?php

class ValidatorPanel extends BasePanel {

    protected $icon;
    protected $color;
    protected $label;
    protected $rawResult;
    protected $filteredResult;
    protected $message;
    protected $resultLabel;
    protected $validationUrl;
    protected $validatorUrl;

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('validator');

        $this->validatorUrl = \TracyDebugger::getDataValue('validatorUrl');
        $this->validationUrl = $this->validatorUrl . "?doc=".$this->wire('page')->httpUrl."&out=html&showimagereport=yes&showsource=yes";

        // get results from validator and convert any entities to UTF-8
        $http = new WireHttp();
        $http->setHeader('Content-Type', 'text/html; charset=utf-8');
        $http->setHeader('User-Agent', 'ProcessWireTracyDebugger');

        $this->rawResult = $http->post($this->validatorUrl, \TracyDebugger::$pageHtml);
        if(function_exists('mb_convert_encoding')) {
            $this->rawResult = mb_convert_encoding($this->rawResult, 'HTML-ENTITIES', 'UTF-8');
        }
        else {
            $this->rawResult = htmlspecialchars_decode(utf8_decode(htmlentities($this->rawResult, ENT_COMPAT, 'UTF-8', false)));
        }

        $showPanelLabels = \TracyDebugger::getDataValue('showPanelLabels');

        if($this->rawResult) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($this->rawResult);
            libxml_use_internal_errors(false);

            $xpath = new DOMXPath($doc);
            $success = $xpath->query("//*[@class='success']");
            $failure = $xpath->query("//*[@class='failure']");

            if($success->length > 0) {
                $this->message = $success->item(0)->nodeValue;
            }
            if($failure->length > 0) {
                $this->message = $failure->item(0)->nodeValue;
            }

            $resultsList = $doc->getElementsByTagName('ol');
            if($resultsList && $resultsList->length > 0) {
                $resultsList = $resultsList->item(0);
                $this->filteredResult = $doc->savehtml($resultsList);
            }
        }

        $errors = (!$this->rawResult ||
            strpos($this->rawResult, 'class="error"') !== false ||
            strpos($this->rawResult, 'class="failure"') !== false ||
            strpos($this->rawResult, 'Connection refused') !== false) ? true : false;

        if($errors) {
            $this->color = \TracyDebugger::COLOR_ALERT;
            $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 27.965 27.965" style="enable-background:new 0 0 27.965 27.965;" xml:space="preserve" width="16px" height="16px">
                            <path d="M13.98,0C6.259,0,0,6.261,0,13.983c0,7.721,6.259,13.982,13.98,13.982c7.725,0,13.985-6.262,13.985-13.982    C27.965,6.261,21.705,0,13.98,0z M19.992,17.769l-2.227,2.224c0,0-3.523-3.78-3.786-3.78c-0.259,0-3.783,3.78-3.783,3.78    l-2.228-2.224c0,0,3.784-3.472,3.784-3.781c0-0.314-3.784-3.787-3.784-3.787l2.228-2.229c0,0,3.553,3.782,3.783,3.782    c0.232,0,3.786-3.782,3.786-3.782l2.227,2.229c0,0-3.785,3.523-3.785,3.787C16.207,14.239,19.992,17.769,19.992,17.769z" fill="'.$this->color.'"/>
                        </svg>';
            $this->resultLabel = 'Validation Errors';
            $this->label = $showPanelLabels ? $this->resultLabel : '';
        }
        elseif(strpos($this->rawResult, 'class="warning"') !== false || strpos($this->rawResult, 'class="info"') !== false || strpos($this->rawResult, 'class="info warning"') !== false) {
            $this->color = \TracyDebugger::COLOR_WARN;
            $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 483.537 483.537" style="enable-background:new 0 0 483.537 483.537;" xml:space="preserve" width="16px" height="16px">
                            <path d="M479.963,425.047L269.051,29.854c-5.259-9.88-15.565-16.081-26.782-16.081h-0.03     c-11.217,0-21.492,6.171-26.782,16.051L3.603,425.016c-5.046,9.485-4.773,20.854,0.699,29.974     c5.502,9.15,15.413,14.774,26.083,14.774H453.12c10.701,0,20.58-5.594,26.083-14.774     C484.705,445.84,484.979,434.471,479.963,425.047z M242.239,408.965c-16.781,0-30.399-13.619-30.399-30.399     c0-16.78,13.619-30.399,30.399-30.399c16.75,0,30.399,13.619,30.399,30.399C272.638,395.346,259.02,408.965,242.239,408.965z      M272.669,287.854c0,16.811-13.649,30.399-30.399,30.399c-16.781,0-30.399-13.589-30.399-30.399V166.256     c0-16.781,13.619-30.399,30.399-30.399c16.75,0,30.399,13.619,30.399,30.399V287.854z" fill="'.$this->color.'"/>
                        </svg>';
            $this->resultLabel = 'Validation Warnings';
            $this->label = $showPanelLabels ? $this->resultLabel : '';
        }
        else {
            $this->color = \TracyDebugger::COLOR_NORMAL;
            $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 191.667 191.667" style="enable-background:new 0 0 191.667 191.667;" xml:space="preserve" width="16px" height="16px">
                            <path d="M95.833,0C42.991,0,0,42.99,0,95.833s42.991,95.834,95.833,95.834s95.833-42.991,95.833-95.834S148.676,0,95.833,0z   M150.862,79.646l-60.207,60.207c-2.56,2.56-5.963,3.969-9.583,3.969c-3.62,0-7.023-1.409-9.583-3.969l-30.685-30.685  c-2.56-2.56-3.97-5.963-3.97-9.583c0-3.621,1.41-7.024,3.97-9.584c2.559-2.56,5.962-3.97,9.583-3.97c3.62,0,7.024,1.41,9.583,3.971  l21.101,21.1l50.623-50.623c2.56-2.56,5.963-3.969,9.583-3.969c3.62,0,7.023,1.409,9.583,3.969  C156.146,65.765,156.146,74.362,150.862,79.646z" fill="'.$this->color.'"/>
                        </svg>';
            $this->resultLabel = 'Validated';
            $this->label = $showPanelLabels ? $this->resultLabel : '';
        }

        $tabPadding = $showPanelLabels ? '5px' : '0';
        $background = $showPanelLabels ? $this->color : 'none';

        $tab = '
        <span title="'.$this->resultLabel.'">
            ' . $this->icon . '
            ' . $this->label . '
        </span>
        ';
        return $tab;
    }

    public function getPanel() {

        $out = '
        <h1>'.str_replace('#FFFFFF', $this->color, $this->icon).' '.$this->resultLabel.'</h1><span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'ValidatorPanel\')">+</a></span></span>
        <div class="tracy-inner">
            <style type="text/css">

                #validatorBody ol li {
                    list-style: initial !important;
                    list-style-type: decimal !important;
                }

                #validatorBody p, #validatorBody ol li p {
                    font-size: 14px !important;
                }

                #validatorBody #schema, #validatorBody #doc, #validatorBody #nsfilter {
                    width: 99%;
                }

                #validatorBody table {
                    width: 100%;
                    table-layout: fixed;
                }

                #validatorBody h1, #validatorBody h2 {
                    font-size: 1.2em;
                    font-weight: bold;
                }

                #validatorBody h3 {
                    font-size: 1em;
                    font-weight: bold;
                    margin-top: 2em;
                }

                #validatorBody h1 span {
                    color: #AAAAAA;
                }

                #validatorBody tr:first-child td, #validatorBody tr:first-child th {
                    vertical-align: top;
                }

                #validatorBody textarea, #validatorBody #doc[type="url"], #validatorBody #schema, #validatorBody #nsfilter {
                    font-family: Monaco, Consolas, Andale Mono, monospace;
                }

                #validatorBody .stats, #validatorBody .details {
                    font-size: 0.85em;
                }

                #validatorBody .success {
                    padding: 0.5em;
                    border: double 6px green;
                }

                #validatorBody .failure {
                    padding: 0.5em;
                    border: double 6px red;
                }

                #validatorBody ol {
                    width: calc(100% - 25px) !important;
                    list-style-position: inside;
                    padding: 0 !important;
                    margin: 25px !important;
                    font-size: 13px !important;
                }

                #validatorBody li {
                    margin: 0;
                    padding: 0.5em;
                }

                #validatorBody li ol {
                    padding-right: 0;
                    margin-top: 0.5em;
                    margin-bottom: 0;
                }

                #validatorBody li li {
                    padding-right: 0;
                    padding-bottom: 0.2em;
                    padding-top: 0.2em;
                }

                #validatorBody .info {
                    color: black;
                    background-color: #CCFFFF;
                }

                #validatorBody .warning {
                    color: black;
                    background-color: #FFFFCC;
                }

                #validatorBody .error {
                    color: black;
                    background-color: #FFCCCC;
                }

                #validatorBody .io, #validatorBody .fatal, #validatorBody .schema {
                    color: black;
                    background-color: #FF9999;
                }

                #validatorBody .internal {
                    color: black;
                    background-color: #FF6666;
                }


                #validatorBody hr {
                    border-top: 1px dotted #666666;
                    border-bottom: none;
                    border-left: none;
                    border-right: none;
                    height: 0;
                }

                #validatorBody p {
                    margin: 0.5em 0 0.5em 0;
                }

                #validatorBody li p {
                    margin: 0;
                }

                #validatorBody .stats, #validatorBody .details {
                    margin-top: 0.75em;
                }

                #validatorBody .details p {
                    margin: 0;
                }

                #validatorBody .lf {
                    color: #222222;
                }

                #validatorBody b {
                    color: black;
                    background-color: #FF6666;
                }

                #validatorBody ol.source li {
                    padding-top: 0;
                    padding-bottom: 0;
                }

                #validatorBody ol.source b, #validatorBody ol.source .b {
                    color: black;
                    background-color: #FFFFCC;
                    font-weight: bold;
                }

                #validatorBody code {
                    white-space: pre;
                    white-space: -pre-wrap;
                    white-space: -o-pre-wrap;
                    white-space: -moz-pre-wrap;
                    white-space: -hp-pre-wrap;
                    white-space: pre-wrap;
                    word-wrap: break-word;
                }

                #validatorBody dl {
                    margin-top: 0.5em;
                    font-size: 14px;
                    font-family: sans-serif;
                    font-weight: normal;
                    color: #333333;
                }

                #validatorBody dd {
                    margin-left: 1.5em;
                    padding-left: 0;
                }

                #validatorBody table.imagereview {
                    width: 100%;
                    table-layout: auto;
                    border-collapse: collapse;
                    border-spacing: 0;
                }

                #validatorBody col.img {
                    width: 180px;
                }

                #validatorBody col.alt {
                    color: black;
                    background-color: #FFFFCC;
                }

                #validatorBody td.alt span {
                    color: black;
                    background-color: #FFFFAA;
                }

                #validatorBody .imagereview th {
                    font-weight: bold;
                    text-align: left;
                    vertical-align: bottom;
                }

                #validatorBody .imagereview td {
                    vertical-align: middle;
                }

                #validatorBody td.img {
                    padding-right: 0.5em;
                    padding-left: 0;
                    padding-top: 0;
                    padding-bottom: 0.5em;
                    text-align: right;
                }

                #validatorBody img {
                    max-height: 180px;
                    max-width: 180px;
                    -ms-interpolation-mode: bicubic;
                }

                #validatorBody th.img {
                    padding-right: 0.5em;
                    padding-left: 0;
                    padding-top: 0;
                    padding-bottom: 0.5em;
                    vertical-align: bottom;
                    text-align: right;
                }

                #validatorBody td.alt, #validatorBody td.location {
                    text-align: left;
                    padding-right: 0.5em;
                    padding-left: 0.5em;
                    padding-top: 0;
                    padding-bottom: 0.5em;
                }

                #validatorBody th.alt, #validatorBody th.location {
                    padding-right: 0.5em;
                    padding-left: 0.5em;
                    padding-top: 0;
                    padding-bottom: 0.5em;
                    vertical-align: bottom;
                }

                #validatorBody dd code ~ span {
                    color: #666;
                }

                #validatorBody dl.inputattrs {
                    display: table;
                }

                #validatorBody dl.inputattrs dt {
                    display: table-caption;
                }

                #validatorBody dl.inputattrs dd {
                    display: table-row;
                }

                #validatorBody dl.inputattrs > dd > a,
                #validatorBody dl.inputattrs .inputattrname,
                #validatorBody dl.inputattrs .inputattrtypes {
                    display: table-cell;
                    padding-top: 2px;
                    padding-left: 1.5em;
                    padding-right: 1.5em;
                    word-wrap: normal;
                }

                #validatorBody dl.inputattrs .inputattrtypes {
                    padding-left: 4px;
                    padding-right: 4px;
                }

                #validatorBody .inputattrtypes > a {
                    color: #666;
                }

                #validatorBody dl.inputattrs .highlight {
                    background-color: #FFC;
                    padding-bottom: 2px;
                    font-weight: normal;
                    color: #666;
                }

                #validatorBody *[irrelevant], #validatorBody .irrelevant {
                    display: none;
                }

                @media all and (max-width: 24em) {
                    #validatorBody body {
                        padding: 3px;
                    }
                    #validatorBody table,
                    #validatorBody thead,
                    #validatorBody tfoot,
                    #validatorBody tbody,
                    #validatorBody tr,
                    #validatorBody th,
                    #validatorBody td {
                        display: block;
                        width: 100%;
                    }
                    #validatorBody th {
                        text-align: left;
                        padding-bottom: 0;

                    }
                }

                #validatorBody #outline h2 {
                    margin-bottom: 0;
                }

                #validatorBody #outline .heading {
                    color: #BF4F00;
                    font-weight: bold;
                }

                #validatorBody #outline ol {
                    margin-top: 0;
                    padding-top: 3px;
                }

                #validatorBody #outline li {
                    padding: 3px 0 3px 0;
                    margin: 0;
                    list-style: none;
                    position: relative;
                }

                #validatorBody #outline li li {
                    list-style: none;
                }

                #validatorBody #outline li:first-child::before {
                    position: absolute;
                    top: 0;
                    height: 0.6em;
                    left: -0.75em;
                    width: 0.5em;
                    border-color: #bbb;
                    border-style: none none solid solid;
                    content: "";
                    border-width: 0.1em;
                }

                #validatorBody #outline li:not(:last-child)::after {
                    position: absolute;
                    top: 0;
                    bottom: -0.6em;
                    left: -0.75em;
                    width: 0.5em;
                    border-color: #bbb;
                    border-style: none none solid solid;
                    content: "";
                    border-width: 0.1em;
                }
            </style>
            <br />';

        $validatorLink = '<a href="'.$this->validationUrl.'">Results for '.$this->wire('page')->httpUrl.' at '.$this->validatorUrl.'</a>';
        if($this->rawResult) {
            $out .= '<div id="validatorBody"><h2>'.$validatorLink.'</h2>'.$this->message.$this->filteredResult.'</div>';
        }
        else {
            $out .= '<div id="validatorBody"><h2>Sorry, but there was a problem accessing the validation server at '.$this->validatorUrl.'</h2><h2>'.$validatorLink.'</h2></div>';
        }

        $out .= \TracyDebugger::generatePanelFooter('validator', \Tracy\Debugger::timer('validator'), strlen($out));

        $out .= '</div>';

        return parent::loadResources() . $out;
    }

}
