<?php

use Tracy\IBarPanel;

/**
 * Validator panel
 */

class ValidatorPanel implements IBarPanel {

    protected $icon;
    protected $result;
    protected $validationUrl;

    public function getTab() {

        if(\TracyDebugger::additionalBar()) return;

        $this->validationUrl = "https://validator.nu/?doc=".wire('page')->httpUrl."&out=html";

        $this->result = file_get_contents($this->validationUrl);

        $doc = new DOMDocument();
        $doc->loadHTML($this->result);
        $element = $doc->getElementsByTagName('form')->item(0);
        $element->parentNode->removeChild($element);
        $element = $doc->getElementsByTagName('h1')->item(0);
        $element->parentNode->removeChild($element);
        $this->result = $doc->saveHTML();

        $errors = (strpos($this->result, 'class="error"') !== false || strpos($this->result, 'class="failure"') !== false) ? true : false;
        $tab = '
        <span title="Validator">';
        if($errors) {
            $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 612 612" style="enable-background:new 0 0 612 612;" xml:space="preserve">
                        <path d="M420.501,218.79c-0.286-6.942-2.868-13.827-8.186-19.125c-5.297-5.298-12.183-7.898-19.125-8.186     c-7.726-0.325-15.548,2.276-21.438,8.186L306,265.436l-65.752-65.771c-5.909-5.91-13.712-8.492-21.439-8.186     c-6.942,0.287-13.827,2.869-19.125,8.186c-5.297,5.298-7.898,12.183-8.186,19.125c-0.325,7.727,2.276,15.529,8.186,21.439     L265.436,306l-65.752,65.752c-5.91,5.909-8.492,13.713-8.186,21.438c0.287,6.942,2.869,13.828,8.186,19.125     c5.298,5.298,12.183,7.899,19.125,8.186c7.727,0.325,15.53-2.275,21.439-8.186L306,346.564l65.771,65.771     c5.91,5.909,13.713,8.491,21.439,8.186c6.942-0.287,13.827-2.869,19.125-8.186c5.298-5.298,7.898-12.183,8.186-19.125     c0.325-7.727-2.276-15.529-8.186-21.439L346.564,306l65.751-65.752C418.226,234.339,420.826,226.536,420.501,218.79z M306,0     C137.012,0,0,137.012,0,306s137.012,306,306,306s306-137.012,306-306S474.988,0,306,0z M306,554.625     C168.912,554.625,57.375,443.088,57.375,306S168.912,57.375,306,57.375S554.625,168.912,554.625,306S443.088,554.625,306,554.625     z" fill="#CD1818"/>
                    </svg>';
        }
        elseif(strpos($this->result, 'class="warning"') !== false) {
            $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 329.562 329.562" style="enable-background:new 0 0 329.562 329.562;" xml:space="preserve" width="16px" height="16px">
                            <g>
                                <path d="M326.174,272.923l-139.5-241.568c-4.516-7.821-12.861-12.638-21.893-12.638c-9.031,0-17.377,4.816-21.893,12.638   L3.388,272.923c-4.518,7.821-4.518,17.46-0.002,25.282c4.516,7.822,12.862,12.641,21.895,12.641h279   c9.032,0,17.379-4.818,21.895-12.641C330.691,290.383,330.691,280.744,326.174,272.923z M25.281,285.565l139.5-241.568   l139.5,241.568H25.281z" fill="#FF9933"/>
                                <path d="M147.281,131.031l7.25,83c0.423,4.886,4.301,8.913,9.355,9.355c5.661,0.495,10.651-3.694,11.145-9.355l7.25-83   c0.078-0.97,0.088-2.057,0-3.057c-0.844-9.666-9.363-16.816-19.028-15.973C153.588,112.846,146.437,121.367,147.281,131.031z" fill="#FF9933"/>
                                <circle cx="164.781" cy="243.031" r="14.5" fill="#FF9933"/>
                            </g>
                        </svg>';
        }
        else {
            $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 26 26" enable-background="new 0 0 26 26" width="16px" height="16px">
                        <g>
                            <path d="m13,0c-7.2,0-13,5.8-13,13s5.8,13 13,13 13-5.8 13-13-5.8-13-13-13zm0,22c-5,0-9-4-9-9s4-9 9-9 9,4 9,9-4,9-9,9z" fill="#009900"/>
                            <path d="m17.3,9.3c-0.2-0.2-0.5-0.3-0.7-0.3s-0.5,0.1-0.7,0.3l-3.6,3.3c-0.2,0.2-0.5,0.2-0.7,0l-1.5-1.3c-0.4-0.4-1-0.4-1.4,0l-1.4,1.4c-0.2,0.2-0.3,0.5-0.3,0.7s0.1,0.5 0.3,0.7l4,3.6c0.2,0.2 0.4,0.3 0.7,0.3 0.3,0 0.5-0.1 0.7-0.3l6-5.6c0.2-0.2 0.3-0.5 0.3-0.7s-0.1-0.5-0.3-0.7l-1.4-1.4z" fill="#009900"/>
                        </g>
                    </svg>';
        }
        $tab .= $this->icon;
        $tab .= (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Validator' : '') . '
        </span>
        ';
        return $tab;
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
        $out = '
        <h1>
            '.$this->icon.'
            Validator
        </h1>
        <div class="tracy-inner">
            <style type="text/css">

                #validatorBody ol li {
                    list-style: initial !important;
                    list-style-type: decimal !important;
                }

                #validatorBody body {
                    font-family: sans-serif;
                    font-size: inherit;
                    padding: 3%;
                    margin: 0;
                }

                #validatorBody html {
                    margin: 0;
                    padding: 0;
                    font: caption;
                }

                #validatorBody form {
                    font: caption;
                    margin-bottom: 1em;
                }

                #validatorBody input[type=submit] {
                    background: #FFFFFF;
                    margin: 5px;
                    padding: 5px;
                    border: 1px solid #D2D2D2;
                    -webkit-border-radius: 5px;
                    -moz-border-radius: 5px;
                    border-radius: 5px;
                    cursor: pointer;
                }

                #validatorBody fieldset {
                    padding: 1.5%;
                    border: 1px dotted #666666;
                    margin: 0;
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

                #validatorBody fieldeset td, #validatorBody fieldeset th {
                    padding-top: 0.4em;
                    padding-bottom: 0.4em;
                }

                #validatorBody tr:first-child td, #validatorBody tr:first-child th {
                    vertical-align: top;
                }

                #validatorBody fieldset th {
                    padding-left: 0;
                    padding-right: 0.4em;
                    text-align: right;
                    width: 8em;
                    font-weight: inherit;
                }

                #validatorBody fieldset td {
                    padding-left: 0;
                    padding-right: 0;
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
                    margin-left: 0;
                    margin-right: 0;
                    margin-top: 1.5em;
                    margin-bottom: 1.5em;
                    padding-left: 2.5em;
                    padding-right: 2.5em;
                    padding-top: 0;
                    padding-bottom: 0;
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
            <br />
            <div id="validatorBody" style="width:934px"><h2><a href="'.$this->validationUrl.'">Results for '.wire('page')->httpUrl.' at Validator.nu</a></h2>'.$this->result.'</div>
        </div>';

        return $out;
    }

}
