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

        $this->validatorUrl = 'https://validator.w3.org/nu/';
        $this->validationUrl = $this->validatorUrl . "?doc=".$this->wire('page')->httpUrl."&out=html&showimagereport=yes&showsource=yes";

        // get results from validator and convert any entities to UTF-8
        $http = new WireHttp();
        $http->setHeader('Content-Type', 'text/html; charset=utf-8');
        $http->setHeader('User-Agent', 'ProcessWireTracyDebugger');

        $this->rawResult = $http->post($this->validatorUrl, \TracyDebugger::$pageHtml);
        //$this->rawResult = preg_replace('/[^(\x20-\x7F)]*/','', $this->rawResult);
        $this->rawResult = mb_encode_numericentity($this->rawResult, [0x80, 0x10FFFF, 0, ~0], 'UTF-8');

        $showPanelLabels = \TracyDebugger::getDataValue('showPanelLabels');

        if($this->rawResult) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($this->rawResult, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
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

                #results {
                    font-size: 14px;
                    margin-top: 50px;
                    padding-left: 3%;
                    margin-right: 3%;
                }

                .alert {
                    color: #000;
                    background-color: #ff0;
                }

                .success,
                .failure,
                .fatalfailure {
                    border-radius: 4px;
                    padding: 0.5em;
                    font-weight: 700;
                    font-family: Arial, sans-serif;
                }

                .success {
                    color: #000;
                    background-color: #cfc;
                    border: 1px solid #ccc;
                }

                .failure {
                    color: #fff;
                    background-color: #365d95;
                }

                .fatalfailure {
                    color: #fff;
                    background-color: #f00;
                }

                #results > ol:first-child {
                    margin-top: 10px;
                    padding: 15px;
                    background-color: #efefef;
                    border-radius: 4px;
                }

                #results > ol:first-child > li {
                    border: 1px solid #ccc;
                    margin-bottom: 8px;
                    padding-left: 12px;
                    border-radius: 4px;
                    background-color: #fff;
                }

                #results > ol:first-child > li > p:first-child,
                #results > ol:first-child > li > p:first-child code {
                    font-size: 16px !important;
                    font-weight: 700 !important;
                }

                #results > ol:first-child > li > p:first-child code {
                    font-weight: 400 !important;
                }

                #results > ol:first-child > li > p:first-child {
                    color: transparent;
                }

                #results > ol:first-child > li > p:first-child > strong,
                #results > ol:first-child > li > p:first-child > span {
                    color: #000;
                }

                #results > ol:first-child > li > p:first-child > strong:first-child {
                    padding: 1px 6px;
                    border-radius: 6px;
                    border: 1px solid #ccc;
                    font: caption;
                    font-weight: 700 !important;
                }

                .info,
                .warning,
                .error,
                .io,
                .fatal,
                .schema,
                .internal {
                    color: #000;
                }

                .info > p:first-child > strong:first-child {
                    background-color: #cfc;
                }

                .warning > p:first-child > strong:first-child {
                    background-color: #ffc;
                }

                .error > p:first-child > strong:first-child,
                .io > p:first-child > strong:first-child,
                .fatal > p:first-child > strong:first-child,
                .schema > p:first-child > strong:first-child,
                .internal > p:first-child > strong:first-child {
                    background-color: #fcc;
                }

                hr {
                    border-top: 1px dotted #666;
                    border-bottom: none;
                    border-left: none;
                    border-right: none;
                    height: 0;
                }

                p {
                    margin: 0.5em 0 0.5em 0;
                }

                li p {
                    margin: 0;
                }

                .stats,
                .details {
                    margin-top: 0.75em;
                }

                .lf {
                    color: #222;
                }

                .extract {
                    overflow: hidden;
                    max-height: 5.5em;
                }

                .extract b,
                .source b {
                    color: #000;
                    background-color: #ffff80;
                }

                .extract b {
                    font-weight: 400;
                }

                ol.source li {
                    padding-top: 0;
                    padding-bottom: 0;
                }

                ol.source b,
                ol.source .b {
                    color: #000;
                    background-color: #ffff80;
                    font-weight: 700;
                }

                code {
                    white-space: pre;
                    white-space: -pre-wrap;
                    white-space: -o-pre-wrap;
                    white-space: -moz-pre-wrap;
                    white-space: -hp-pre-wrap;
                    white-space: pre-wrap;
                    word-wrap: break-word;
                }

                .error p,
                .info p,
                .warning p,
                .error dd,
                .info dd,
                .warning dd {
                    line-height: 1.8;
                }

                .error p code {
                    border: 1px dashed #999;
                    padding: 2px;
                    padding-left: 4px;
                    padding-right: 4px;
                }

                .warning code {
                    border: 1px dashed #999;
                    padding: 2px;
                    padding-left: 4px;
                    padding-right: 4px;
                }

            </style>
            <br />';

        $validatorLink = '<a href="'.$this->validationUrl.'">Results for '.$this->wire('page')->httpUrl.' at '.$this->validatorUrl.'</a>';
        $out .= '<h2>'.$validatorLink.'</h2>';
        if($this->rawResult) {
            $out .= '<div id="results">'.$this->message.$this->filteredResult.'</div>';
        }
        else {
            $out .= '<h2>Sorry, but there was a problem accessing the validation server at '.$this->validatorUrl.'</h2><h2>'.$validatorLink.'</h2>';
        }

        $out .= \TracyDebugger::generatePanelFooter('validator', \Tracy\Debugger::timer('validator'), strlen($out));

        $out .= '</div>';

        return parent::loadResources() . $out;
    }

}
