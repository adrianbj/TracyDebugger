<?php

use Tracy\Debugger;
use Tracy\IBarPanel;

class MailPanel implements IBarPanel {

    protected $icon;
    protected $iconColor;
    protected $entries;

    public function getTab() {

        \Tracy\Debugger::timer('mail');

        $items = wire('session')->tracyMailItems ? wire('session')->tracyMailItems : array();

        if(count($items) > 0) {
            $this->iconColor = '#CD1818';
            $this->entries .= '
            <div class="mail-items">
                <p><input type="submit" onclick="clearEmails()" value="Clear Emails" /></p>';
            foreach($items as $item) {

                // from @tpr - for dealing with encoded fromName which is necessary for some email clients
                if (function_exists('mb_decode_mimeheader')) {
                    $item['fromName'] = mb_decode_mimeheader($item['fromName']);
                }

                $this->entries .= '
                <table id="mail-item" style="margin-bottom:15px">
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
                                    <div id='text-body' class='tracy-collapsed'>".($item['body'] ? $item['body'] : "Ø")."</div>";
                                }
                                if($item['bodyHTML']) {
                                    $this->entries .= "
                                    <a href='#' rel='#html-body' style='font-size:12px !important; position: absolute; left:56px; top:3px;' class='tracy-toggle tracy-collapsed'>HTML</a>
                                    <div id='html-body' class='tracy-collapsed'>".$item['bodyHTML']."</div>
                                    <a href='#' rel='#source-body' style='font-size:12px !important; position: absolute; left:120px; top:3px;' class='tracy-toggle tracy-collapsed'>Source</a>
                                    <div id='source-body' class='tracy-collapsed'><pre style='max-width: 800px; white-space: normal;'>".htmlentities($item['bodyHTML'])."</pre></div>";
                                }
                                $this->entries .= "
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>";
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
        <span title="Mail">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Mail' : '') . ' ' . '
        </span>
        ';
    }


    public function getPanel() {

        $out = '
        <h1>' . $this->icon . ' Mail</h1>

        <script>
            function clearEmails() {
                document.cookie = "tracyClearMailItems=true;expires=0;path=/";
                var elements = document.getElementsByClassName("mail-items");
                while(elements.length > 0){
                    elements[0].parentNode.removeChild(elements[0]);
                }
            }
        </script>

        <div class="tracy-inner">';
            \TracyDebugger::$panelGenerationTime['mail'] = \Tracy\Debugger::timer('mail');
            $out .= $this->entries . \TracyDebugger::generatedTime(\TracyDebugger::$panelGenerationTime['mail']) . '
        </div>';

        return $out;
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
        foreach($attachments as $attachment) {
            $styledAttachments .= str_replace(wire('config')->paths->root, '/', $attachment) . '<br />';
        }
        return $styledAttachments;
    }

}