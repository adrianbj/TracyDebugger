<?php namespace ProcessWire;

use Tracy\Debugger;

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
    protected $failure;

    public function getTab() {

        if(TracyDebugger::isAdditionalBar()) return;
        Debugger::timer('validator');

        $this->validatorUrl = 'https://validator.w3.org/nu/';
        $this->validationUrl = $this->validatorUrl . "?doc=".rawurlencode($this->wire('page')->httpUrl)."&showimagereport=yes&showsource=yes";

        // results as JSON, rendered here with everything escaped, rather than inserting the validator's own HTML
        $pageHtml = (string) TracyDebugger::$pageHtml;
        $messages = null;
        $this->rawResult = '';
        if($pageHtml === '') {
            $this->failure = '<p class="validator-status validator-status-warning">There was no page HTML to validate.</p><p>The panel validates the HTML that <code>Page::render()</code> returned for this request, and there wasn\'t any (eg. the request was handled by a URL hook, or the template exited before rendering).</p>';
        }
        else {
            $http = new WireHttp();
            $http->setHeader('Content-Type', 'text/html; charset=utf-8');
            $http->setHeader('User-Agent', 'ProcessWireTracyDebugger');
            $start = microtime(true);
            // one transport only (PW 3.0.192+ 'use' option; older versions ignore it): when the validator hangs,
            // falling back to fopen and then sockets adds a timeout each, which can push the page past max_execution_time
            $response = $http->post($this->validatorUrl . '?out=json', $pageHtml, array('use' => function_exists('curl_init') ? 'curl' : 'fopen'));
            $seconds = microtime(true) - $start;
            $results = is_string($response) && $response !== '' ? json_decode($response, true) : null;
            if(is_array($results) && isset($results['messages']) && is_array($results['messages'])) {
                $messages = $results['messages'];
                $this->rawResult = $response;
            }
            else {
                $this->failure = $this->describeFailure($http, is_string($response) ? $response : '', $seconds, strlen($pageHtml));
            }
        }

        $showPanelLabels = TracyDebugger::getDataValue('showPanelLabels');

        $e = function($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
        $failed = $messages === null;
        $hasErrors = false;
        $hasWarnings = false;
        $items = '';
        foreach((array) $messages as $message) {
            $type = isset($message['type']) ? (string) $message['type'] : 'info';
            $subType = isset($message['subType']) ? (string) $message['subType'] : '';
            if($type === 'error' || $type === 'non-document-error') $hasErrors = true;
            else $hasWarnings = true;
            $class = $type === 'non-document-error' ? 'error' : $type;
            if($subType !== '') $class .= ' ' . $subType;
            $label = $subType === 'fatal' ? 'Fatal Error' : ($subType === 'warning' ? 'Warning' : ucfirst($type === 'non-document-error' ? 'error' : $type));
            $items .= '<li class="' . $e($class) . '"><p><strong>' . $e($label) . '</strong>: <span>' . $e($message['message'] ?? '') . '</span></p>';
            if(isset($message['lastLine'])) {
                $firstLine = $message['firstLine'] ?? $message['lastLine'];
                $items .= '<p class="location">From line ' . (int) $firstLine . (isset($message['firstColumn']) ? ', column ' . (int) $message['firstColumn'] : '') . '; to line ' . (int) $message['lastLine'] . (isset($message['lastColumn']) ? ', column ' . (int) $message['lastColumn'] : '') . '</p>';
            }
            if(isset($message['extract']) && $message['extract'] !== '') {
                $extract = (string) $message['extract'];
                $start = (int) ($message['hiliteStart'] ?? 0);
                $length = (int) ($message['hiliteLength'] ?? 0);
                $before = mb_substr($extract, 0, $start, 'UTF-8');
                $hilite = mb_substr($extract, $start, $length, 'UTF-8');
                $after = mb_substr($extract, $start + $length, null, 'UTF-8');
                // line breaks shown as the validator's own HTML output shows them, so the extract stays on one line
                $lf = function($value) use ($e) { return str_replace(array("\r\n", "\n", "\r"), '<span class="lf" title="Line break">&#8617;</span>', $e($value)); };
                $items .= '<p class="extract"><code>' . $lf($before) . '<b>' . $lf($hilite) . '</b>' . $lf($after) . '</code></p>';
            }
            $items .= '</li>';
        }
        if($messages !== null) {
            if($hasErrors) {
                $this->message = '<p class="validator-status validator-status-error">There were errors.</p>';
            }
            elseif($hasWarnings) {
                $this->message = '<p class="validator-status validator-status-warning">The document validates, but there were warnings.</p>';
            }
            else {
                $this->message = '<p class="validator-status validator-status-success">The document validates according to the specified schema(s).</p>';
            }
            $this->filteredResult = $items !== '' ? '<ol>' . $items . '</ol>' : '';
        }

        $errors = $hasErrors;

        if($errors) {
            $this->color = TracyDebugger::COLOR_ALERT;
            $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 27.965 27.965" style="enable-background:new 0 0 27.965 27.965;" xml:space="preserve" width="16px" height="16px">
                            <path d="M13.98,0C6.259,0,0,6.261,0,13.983c0,7.721,6.259,13.982,13.98,13.982c7.725,0,13.985-6.262,13.985-13.982    C27.965,6.261,21.705,0,13.98,0z M19.992,17.769l-2.227,2.224c0,0-3.523-3.78-3.786-3.78c-0.259,0-3.783,3.78-3.783,3.78    l-2.228-2.224c0,0,3.784-3.472,3.784-3.781c0-0.314-3.784-3.787-3.784-3.787l2.228-2.229c0,0,3.553,3.782,3.783,3.782    c0.232,0,3.786-3.782,3.786-3.782l2.227,2.229c0,0-3.785,3.523-3.785,3.787C16.207,14.239,19.992,17.769,19.992,17.769z" fill="'.$this->color.'"/>
                        </svg>';
            $this->resultLabel = 'Validation Errors';
            $this->label = $showPanelLabels ? $this->resultLabel : '';
        }
        elseif($hasWarnings || $failed) {
            $this->color = TracyDebugger::COLOR_WARN;
            $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 483.537 483.537" style="enable-background:new 0 0 483.537 483.537;" xml:space="preserve" width="16px" height="16px">
                            <path d="M479.963,425.047L269.051,29.854c-5.259-9.88-15.565-16.081-26.782-16.081h-0.03     c-11.217,0-21.492,6.171-26.782,16.051L3.603,425.016c-5.046,9.485-4.773,20.854,0.699,29.974     c5.502,9.15,15.413,14.774,26.083,14.774H453.12c10.701,0,20.58-5.594,26.083-14.774     C484.705,445.84,484.979,434.471,479.963,425.047z M242.239,408.965c-16.781,0-30.399-13.619-30.399-30.399     c0-16.78,13.619-30.399,30.399-30.399c16.75,0,30.399,13.619,30.399,30.399C272.638,395.346,259.02,408.965,242.239,408.965z      M272.669,287.854c0,16.811-13.649,30.399-30.399,30.399c-16.781,0-30.399-13.589-30.399-30.399V166.256     c0-16.781,13.619-30.399,30.399-30.399c16.75,0,30.399,13.619,30.399,30.399V287.854z" fill="'.$this->color.'"/>
                        </svg>';
            $this->resultLabel = $failed ? 'Validator Unavailable' : 'Validation Warnings';
            $this->label = $showPanelLabels ? $this->resultLabel : '';
        }
        else {
            $this->color = TracyDebugger::COLOR_NORMAL;
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
        <h1>'.str_replace('#FFFFFF', $this->color, $this->icon).' '.$this->resultLabel.'</h1><span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" data-tracy-resize="ValidatorPanel">⛶</a></span></span>
        ' . $this->openPanel() . '
            <style type="text/css"' . TracyDebugger::getNonceAttr() . '>

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

                #results > p.validator-status {
                    margin: 0;
                    padding: 0.6em 0.8em;
                    border-left: 5px solid;
                    border-radius: 4px;
                    color: #000;
                    font-weight: 700;
                    font-family: Arial, sans-serif;
                }

                #results > p.validator-status + p {
                    margin: 1em 0;
                }

                #results table.validator-failure th {
                    width: 90px;
                    font-weight: 700;
                }

                #results > p.validator-status-error {
                    border-color: '.TracyDebugger::COLOR_ALERT.';
                    background-color: #fbe5e5;
                }

                #results > p.validator-status-warning {
                    border-color: '.TracyDebugger::COLOR_WARN.';
                    background-color: #fff0e0;
                }

                #results > p.validator-status-success {
                    border-color: '.TracyDebugger::COLOR_GREEN.';
                    background-color: #e3f3e3;
                }

                #results > ol {
                    margin-top: 10px;
                    padding: 15px;
                    background-color: #efefef;
                    border-radius: 4px;
                }

                #results > ol > li {
                    border: 1px solid #ccc;
                    margin-bottom: 8px;
                    padding-left: 12px;
                    border-radius: 4px;
                    background-color: #fff;
                }

                #results > ol > li > p:first-child,
                #results > ol > li > p:first-child code {
                    font-size: 16px !important;
                    font-weight: 700 !important;
                }

                #results > ol > li > p:first-child code {
                    font-weight: 400 !important;
                }

                #results > ol > li > p:first-child {
                    color: transparent;
                }

                #results > ol > li > p:first-child > strong,
                #results > ol > li > p:first-child > span {
                    color: #000;
                }

                #results > ol > li > p:first-child > strong:first-child {
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

        $validatorLink = '<a href="'.htmlspecialchars($this->validationUrl, ENT_QUOTES, 'UTF-8').'">Results for '.htmlspecialchars($this->wire('page')->httpUrl, ENT_QUOTES, 'UTF-8').' at '.$this->validatorUrl.'</a>';
        $out .= '<h2>'.$validatorLink.'</h2>';
        if($this->rawResult) {
            $out .= '<div id="results">'.$this->message.$this->filteredResult.'</div>';
        }
        else {
            $out .= '<div id="results">'.$this->failure.'</div>';
        }

        return $this->closePanel($out, 'validator');
    }

    /**
     * What went wrong when the validator didn't return a result, as HTML for the panel
     *
     * @param WireHttp $http
     * @param string $response body of the validator's response
     * @param float $seconds how long the request took
     * @param int $bytes size of the page HTML that was sent
     * @return string
     */
    protected function describeFailure(WireHttp $http, $response, $seconds, $bytes) {
        $e = function($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
        $code = method_exists($http, 'getHttpCode') ? (int) $http->getHttpCode() : 0;
        $status = method_exists($http, 'getHttpCode') ? trim((string) $http->getHttpCode(true)) : (string) $code;
        $error = method_exists($http, 'getError') ? trim((string) $http->getError()) : '';
        $retryAfter = $code === 429 && method_exists($http, 'getResponseHeader') ? trim((string) $http->getResponseHeader('retry-after')) : '';
        $timeout = method_exists($http, 'getTimeout') ? (float) $http->getTimeout() : 0;

        if($code === 0) {
            $summary = 'Couldn\'t get a response from validator.w3.org.';
            $detail = $timeout && $seconds >= $timeout - 0.1
                ? 'The request timed out after ' . round($timeout, 1) . ' seconds. The validator may be slow or down, or this page may be too large to check in that time.'
                : 'The request failed before the validator answered. Check that this server can make outgoing HTTPS connections (a firewall, proxy or DNS problem can block them).';
        }
        elseif($code === 429) {
            $summary = 'validator.w3.org is limiting requests from this server (HTTP ' . $status . ').';
            $detail = 'Too many pages have been sent for checking recently. ' . ($retryAfter !== '' ? 'It asks to wait ' . (ctype_digit($retryAfter) ? $retryAfter . ' seconds' : 'until ' . $retryAfter) . ' before trying again.' : 'Try again in a few minutes.');
        }
        elseif($code === 413) {
            $summary = 'validator.w3.org refused the page because it is too large (HTTP ' . $status . ').';
            $detail = 'The page HTML sent was ' . round($bytes / 1024) . ' KB.';
        }
        elseif($code >= 500) {
            $summary = 'validator.w3.org failed while checking this page (HTTP ' . $status . ').';
            $detail = 'This is a problem on the validator\'s side. It can be temporary, and it can also happen with particular pages while others validate fine. Try again later, or open the link above to have the validator fetch the page itself.';
        }
        elseif($code >= 400) {
            $summary = 'validator.w3.org rejected the request (HTTP ' . $status . ').';
            $detail = '';
        }
        else {
            $summary = 'validator.w3.org returned a response that isn\'t a validation result (HTTP ' . $status . ').';
            $detail = 'It may be showing an error or maintenance page instead.';
        }

        $out = '<p class="validator-status validator-status-warning">' . $e($summary) . '</p>';
        if($detail !== '') $out .= '<p>' . $e($detail) . '</p>';
        $facts = array();
        if($error !== '' && $code === 0) $facts['Error'] = $error;
        $facts['Sent'] = round($bytes / 1024, 1) . ' KB of page HTML';
        $facts['Time'] = round($seconds, 2) . ' seconds';
        $body = trim(preg_replace('/\s+/', ' ', strip_tags(str_replace('<', ' <', $response))));
        if($body !== '') $facts['Response'] = mb_strlen($body, 'UTF-8') > 300 ? mb_substr($body, 0, 300, 'UTF-8') . '…' : $body;
        $out .= '<table class="validator-failure">';
        foreach($facts as $label => $value) $out .= '<tr><th>' . $e($label) . '</th><td>' . $e($value) . '</td></tr>';
        $out .= '</table>';
        return $out;
    }

}
