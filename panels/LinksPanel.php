<?php

class LinksPanel extends BasePanel {

    protected $icon;

    public function getTab() {
        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('links');

        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px"
        viewBox="0 0 16 16" style="enable-background:new 0 0 16 16;" xml:space="preserve">
            <path id="path10" d="M14.9,1.1c-1.5-1.5-3.8-1.5-5.3,0L6.5,4.2C5,5.7,5.1,8.1,6.5,9.5C6.7,9.7,7,9.9,7.3,10.1l0.6-0.6
                C8.2,9.2,8,8.7,8,8.4L7.8,8.2c-0.7-0.7-0.7-1.9,0-2.7l3.1-3.1c0.7-0.7,1.9-0.7,2.7,0c0.7,0.7,0.7,1.9,0,2.7l-2.1,2.1
                c0.1,0.3,0.4,1.1,0.2,2.4l3.1-3.1C16.4,4.9,16.4,2.6,14.9,1.1z" fill="'.\TracyDebugger::COLOR_NORMAL.'" />
            <path id="path12" d="M9.8,6.2C9.5,6,9.3,5.8,9,5.6L8.5,6.2C8.1,6.6,8.2,7,8.2,7.4c0.1,0.1,0.2,0.1,0.2,0.2c0.7,0.7,0.7,1.9,0,2.7
                L5,13.7c-0.7,0.7-1.9,0.7-2.7,0c-0.7-0.7-0.7-1.9,0-2.7l2.3-2.3c0.1-0.4-0.3-1.2-0.1-2.5L1.1,9.6c-1.5,1.5-1.5,3.8,0,5.3
                s3.8,1.5,5.3,0l3.4-3.4C11.3,10,11.2,7.6,9.8,6.2z" fill="'.\TracyDebugger::COLOR_NORMAL.'" />
        </svg>
        ';

        return '
        <span title="Links">' .
            $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Links' : '') . '
        </span>
        ';
    }


    public function getPanel() {

        $out = <<<EOT
<style>
    #tracy-add-link { height:30px; padding-right:75px; position:relative; margin-bottom:10px; }
    #tracy-add-link#tracy-add-link input { width:calc(100% - 10px) !important; outline:none; }
    #tracy-add-link#tracy-add-link input:focus { border-color:#8a9cb1 !important; }
    #tracy-add-link button { width:30px; height:30px; padding:0; border:1px solid #C8CED6; background:#F0F3F7; position:absolute; cursor:pointer; outline:none; vertical-align:middle; }
    #tracy-add-link button:hover { background:white; }
    #tracy-add-link button svg { vertical-align:middle; }
    #tracy-lp-btn-add { right:32px; }
    #tracy-lp-btn-current { right:0; }
    #tracy-link-items { line-height:1.5; }
</style>
<script>
var form = document.getElementById('tracy-add-link');
document.getElementById('tracy-lp-btn-current').addEventListener('click', function(event) {
    var link = window.location.href
    var title = document.title;
    if(title.length) link += ' | ' + title;
    document.getElementById('tracy-lp-input').value = link;
    form.submit();
});
form.addEventListener('submit', function(event) {
    var link_input = document.getElementById('tracy-lp-input');
    if(!link_input.value) {
        event.preventDefault();
        alert('Please add a link URL before submitting.');
    }
});
</script>
<h1>{$this->icon} Links</h1>
<div class="tracy-inner">
<form id="tracy-add-link" action="{$this->wire('config')->urls->admin}module/edit" method="post">
    <input type="hidden" name="name" value="TracyDebugger">
    <input id="tracy-lp-input" name="link" type="text" placeholder="Add link...">
    <button id="tracy-lp-btn-add" title="Add link">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-plus" width="32" height="32" viewBox="0 0 24 24" stroke-width="1.5" stroke="#000000" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <circle cx="12" cy="12" r="9" />
            <line x1="9" y1="12" x2="15" y2="12" />
            <line x1="12" y1="9" x2="12" y2="15" />
        </svg>
    </button>
    <button id="tracy-lp-btn-current" type="button" title="Add link to current page">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-bolt" width="32" height="32" viewBox="0 0 24 24" stroke-width="1.5" stroke="#000000" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <polyline points="13 3 13 10 19 10 11 21 11 14 5 14 13 3" />
        </svg>
    </button>
</form>
<div id="tracy-link-items">
EOT;
        if(\TracyDebugger::getDataValue('linksCode')) {
            foreach(explode("\n", \TracyDebugger::getDataValue('linksCode')) as $link) {
                $link_parts = explode('|', $link);
                $out .= '<a href="'.trim($link_parts[0]).'">'.trim($link_parts[1]).'</a><br />';
            }
        }
        $out .= '</div>';

        $out .= \TracyDebugger::generatePanelFooter('links', \Tracy\Debugger::timer('links'), strlen($out), 'linksPanel');
        $out .= '</div>';

        return parent::loadResources() . $out;
    }

}
