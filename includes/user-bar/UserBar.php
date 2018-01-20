<?php

$userBar = '
<style>
    div#tracyUserBar {
        '.\TracyDebugger::getDataValue("userBarTopBottom").': 0px;
        '.\TracyDebugger::getDataValue("userBarLeftRight").': 0px;
        z-index: 19999;
        position: fixed;
        margin: 0px !important;
        background: '.\TracyDebugger::getDataValue("userBarBackgroundColor").';
        padding: 5px;
        opacity: '.\TracyDebugger::getDataValue("userBarBackgroundOpacity").';
    }
    div#tracyUserBar a {
        border:none !important;
        cursor:pointer;
    }
    div#tracyUserBar svg {
        width: 16px !important;
        height: 16px !important;
        margin: 2px !important;
    }
</style>

<div id="tracyUserBar">';

foreach($this->data['userBarFeatures'] as $barFeature) {
    require_once __DIR__ . '/UserBar'.ucfirst($barFeature).'.php';
}

$user = wire('user');
$page = wire('page');
$pages = wire('pages');
$config = wire('config');
$iconColor = \TracyDebugger::getDataValue("userBarIconColor");
$userBar .= eval($this->data['userBarCustomFeatures']);

$userBar .= '
</div>';