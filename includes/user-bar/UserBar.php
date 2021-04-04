<?php

$userBarStyles = '
<style>
    div#tracyUserBar {
        '.\TracyDebugger::getDataValue("userBarTopBottom").': 0px;
        '.\TracyDebugger::getDataValue("userBarLeftRight").': 0px;
        z-index: '.\TracyDebugger::getDataValue("panelZindex").';
        position: fixed;
        margin: 0px !important;
        background: '.\TracyDebugger::getDataValue("userBarBackgroundColor").';
        padding: 1px;
        opacity: '.\TracyDebugger::getDataValue("userBarBackgroundOpacity").';
        line-height: 0;
    }
    div#tracyUserBar a {
        border: none !important;
        cursor: pointer;
    }
    div#tracyUserBar svg {
        width: 16px !important;
        height: 16px !important;
        margin: 2px !important;
    }
    div#tracyUserBar span {
        display: inline-block;
        vertical-align: top;
        margin-left: 2px;
    }
</style>';

$userBar = '
<div id="tracyUserBar">';

foreach($this->data['userBarFeatures'] as $barFeature) {
    require_once __DIR__ . '/UserBar'.ucfirst($barFeature).'.php';
}

$user = $this->wire('user');
$page = $this->wire('page');
$pages = $this->wire('pages');
$config = $this->wire('config');
$iconColor = \TracyDebugger::getDataValue("userBarIconColor");
$userBar .= eval($this->data['userBarCustomFeatures']);

$userBar .= '
</div>';