<?php

use Tracy\Debugger;
use Tracy\IBarPanel;

class MailPanel implements IBarPanel {

    protected $icon;
    protected $iconColor;
    protected $entries;

    public function getTab() {

        \Tracy\Debugger::timer('mail');

        $items = \TracyDebugger::$mailParams;

        if(count($items) > 0) {
            $this->iconColor = '#CD1818';
            foreach($items as $item) {

                $this->entries .= '
                <table id="mail-item" style="margin-bottom:15px">
                    <thead>
                        <tr>
                            <th colspan="2"><strong>'.$item->subject.'</strong></th>
                        </tr>
                    </thead>
                    <tbody>';

                $this->entries .= "
                        \n<tr><td>To</td><td>".$this->formatEmailAddress($item->to, $item->toName)."</td></tr>
                        \n<tr><td>Cc</td><td>".$this->formatEmailAddress($item->cc, $item->ccName)."</td></tr>
                        \n<tr><td>Bcc</td><td>".$this->formatEmailAddress($item->bcc, $item->bccName)."</td></tr>
                        \n<tr><td>From</td><td>".$item->fromName . ' &lt;' . $item->from."&gt;</td></tr>
                        \n<tr><td>Attachments</td><td>".$this->formatAttachments($item->attachments)."</td></tr>
                        \n<tr>
                            <td colspan='2'>
                                <a href='#' rel='#text-body' style='font-size:12px !important' class='tracy-toggle tracy-collapsed'>Text</a>&nbsp;&nbsp;
                                \n<div id='text-body' class='tracy-collapsed'>".$item->body."</div>
                                <a href='#' rel='#html-body' style='font-size:12px !important' class='tracy-toggle tracy-collapsed'>HTML</a>&nbsp;&nbsp;
                                \n<div id='html-body' class='tracy-collapsed'>".$item->bodyHTML."</div>
                                <a href='#' rel='#source-body' style='font-size:12px !important' class='tracy-toggle tracy-collapsed'>Source</a>
                                \n<div id='source-body' class='tracy-collapsed'><pre>".htmlentities($item->bodyHTML)."</pre></div>
                            </td>
                        </tr>
                    </tbody>
                </table>";
            }
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

        <div class="tracy-inner">';
            \TracyDebugger::$panelGenerationTime['mail'] = \Tracy\Debugger::timer('mail');
            $out .= $this->entries . \TracyDebugger::generatedTime(\TracyDebugger::$panelGenerationTime['mail']) . '
        </div>';

        return $out;
    }

    protected function formatEmailAddress($addresses, $names) {
        $styledAddresses = '';
        foreach($addresses as $address) {
            $styledAddresses .= $names[$address].' &lt;'.$address.'&gt;<br />';
        }
        return $styledAddresses;
    }

    protected function formatAttachments($attachments) {
        $styledAttachments = '';
        foreach($attachments as $attachment) {
            $styledAttachments .= str_replace(wire('config')->paths->root, '/', $attachment) . '<br />';
        }
        return $styledAttachments;
    }

}