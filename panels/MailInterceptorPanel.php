<?php

class MailInterceptorPanel extends BasePanel {

    protected $icon;
    protected $iconColor;
    protected $entries;
    protected $mailCount;

    public function getTab() {

        \Tracy\Debugger::timer('mailInterceptor');

        $items = $this->wire('session')->tracyMailItems ? $this->wire('session')->tracyMailItems : array();
        $this->mailCount = count($items);
        if($this->mailCount > 0) {
            $this->iconColor = '#CD1818';
            $this->entries .= '
            <div class="mail-items">
                <p>
                    <form method="post" action="'.\TracyDebugger::inputUrl(true).'">
                        <input type="submit" name="tracyClearMailItems" value="Clear Emails" />
                    </form>
                </p><br />';

            foreach($items as $item) {

                // from @tpr - for dealing with encoded fromName which is necessary for some email clients
                if(function_exists('mb_decode_mimeheader')) {
                    $item['fromName'] = mb_decode_mimeheader($item['fromName']);
                }

                $this->entries .= '
                <table id="mail-item" style="margin-bottom:15px !important; min-width:300px !important">
                    <thead>
                        <tr>
                            <th colspan="2"><strong>'.$item['subject'].'</strong></th>
                        </tr>
                    </thead>
                    <tbody>';

                        $this->entries .= "
                        <tr><td>Sent</td><td>".date('Y-m-d H:i:s', $item['timestamp'])."</td></tr>
                        <tr><td>To</td><td>".$this->formatEmailAddress($item['to'], $item['toName'])."</td></tr>" .
                        ($item['cc'] ? "<tr><td>Cc</td><td>".$this->formatEmailAddress($item['cc'], $item['ccName'])."</td></tr>" : "") .
                        ($item['bcc'] ? "<tr><td>Bcc</td><td>".$this->formatEmailAddress($item['bcc'], null)."</td></tr>" : "") . "
                        <tr><td>From</td><td>".$item['fromName'] . ' &lt;' . $item['from']."&gt;</td></tr>" .
                        ($item['attachments'] ? "<tr><td>Attachments</td><td>".$this->formatAttachments($item['attachments'])."</td></tr>" : "") . "
                        <tr>
                            <td colspan='2'>
                                <div style='position: relative; padding-top: 22px;'>";
                                    if($item['body']) {
                                        $this->entries .= "
                                        <a href='#' rel='#text-body' style='font-size:12px !important; position: absolute; left:0; top:3px;' class='tracy-toggle tracy-collapsed'>Text</a>
                                        <div id='text-body' class='tracy-section tracy-collapsed'>".($item['body'] ? nl2br($item['body']) : "Ø")."</div>";
                                    }
                                    if($item['bodyHTML']) {
                                        $this->entries .= "
                                        <a href='#' rel='#html-body' style='font-size:12px !important; position: absolute; left:56px; top:3px;' class='tracy-toggle tracy-collapsed'>HTML</a>
                                        <div id='html-body' class='tracy-section tracy-collapsed'>".$item['bodyHTML']."</div>
                                        <a href='#' rel='#source-body' style='font-size:12px !important; position: absolute; left:120px; top:3px;' class='tracy-toggle tracy-collapsed'>Source</a>
                                        <div id='source-body' class='tracy-section tracy-collapsed'><pre style='max-width: 800px; white-space: normal;'>".htmlentities($item['bodyHTML'])."</pre></div>";
                                    }
                                    $this->entries .= "
                                    <a href='#' rel='#metadata' style='font-size:12px !important; position: absolute; left:190px; top:3px;' class='tracy-toggle tracy-collapsed'>Metadata</a>
                                    <div id='metadata' class='tracy-section tracy-collapsed'>
                                        <table id='metadata-table' style='margin-bottom:15px; width:100%'>
                                            <tbody>";
                                                if(isset($item['header']['X-Mailer'])) $this->entries .= "<tr><td>X-Mailer</td><td>".$item['header']['X-Mailer']."</td></tr>";
                                                foreach(array('priority' => 'Priority', 'default_charset' => 'Default Charset', 'dispositionNotification' => 'Disposition Notification', 'addSignature' => 'Add Signature', 'sendSingle' => 'Send Single', 'sendBulk' => 'Send Bulk', 'useSentLog' => 'Use Sent Log', 'wrapText' => 'Wrap Text', 'sender_signature' => 'Sender Signature', 'sender_signature_html' => 'Sender Signature HTML') as $param => $label) {
                                                    if(isset($item[$param])) $this->entries .= "<tr><td>".$label."</td><td>".(is_bool($item[$param]) ? ($item[$param] ? 'true' : 'false') : $item[$param])."</td></tr>";
                                                }
                                            $this->entries .= "
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br />";
            }
            $this->entries .= '</div>';
        }
        else {
            $this->iconColor = '#009900';
            $this->entries = 'No emails sent';
        }

        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 14 14" style="enable-background:new 0 0 14 14;" xml:space="preserve" width="16px" height="16px">
            <g>
                <path d="M7,9L5.268,7.484l-4.952,4.245C0.496,11.896,0.739,12,1.007,12h11.986    c0.267,0,0.509-0.104,0.688-0.271L8.732,7.484L7,9z" fill="'.$this->iconColor.'"/>
                <path d="M13.684,2.271C13.504,2.103,13.262,2,12.993,2H1.007C0.74,2,0.498,2.104,0.318,2.273L7,8    L13.684,2.271z" fill="'.$this->iconColor.'"/>
                <polygon points="0,2.878 0,11.186 4.833,7.079" fill="'.$this->iconColor.'"/>
                <polygon points="9.167,7.079 14,11.186 14,2.875" fill="'.$this->iconColor.'"/>
            </g>
        </svg>
        ';
        return '
        <span title="Mail Interceptor">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Mail Interceptor' : '') . ' ' . ($this->mailCount > 0 ? $this->mailCount : '') . '
        </span>
        ';
    }


    public function getPanel() {
        $isAdditionalBar = \TracyDebugger::isAdditionalBar();
        $out = '
        <h1>' . $this->icon . ' Mail Interceptor' . ($isAdditionalBar ? ' ('.$isAdditionalBar.')' : '') . '</h1>

        <div class="tracy-inner">';
            $out .= $this->entries;
            $out .= \TracyDebugger::generatedTimeSize('mailInterceptor', \Tracy\Debugger::timer('mailInterceptor'), strlen($out)) . '
        </div>';

        return parent::loadResources() . $out;
    }

    protected function formatEmailAddress($addresses, $names) {
        $styledAddresses = '';
        if(!is_array($addresses)) $addresses = array($addresses);
        foreach($addresses as $address) {
            $styledAddresses .= $names[$address].' &lt;'.$address.'&gt;<br />';
        }
        return $styledAddresses;
    }

    protected function formatAttachments($attachments) {
        $styledAttachments = '';
        if(!is_array($attachments)) $attachments = array($attachments);
        foreach($attachments as $key => $val) {
            $attachment = is_numeric($key) ? $value : $key;
            $styledAttachments .= '<a href="'.str_replace($this->wire('config')->paths->root, $this->wire('config')->urls->root, $attachment).'">'.str_replace($this->wire('config')->paths->root, '/', $attachment) . '</a><br />';
        }
        return $styledAttachments;
    }

}