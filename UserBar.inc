<?php

$userBar = '
<style>
    div#TracyUserBar {
        bottom: 0px;
        right: 0px;
        z-index: 99999;
        position: fixed;
        margin: 0px !important;
        background: '.\TracyDebugger::getDataValue("userBarBackgroundColor").';
        padding: 3px 15px 0 6px;
        opacity: '.\TracyDebugger::getDataValue("userBarBackgroundOpacity").';
    }
    div#TracyUserBar a {
        border:none !important;
        cursor:pointer;
    }
</style>

<div id="TracyUserBar">';

foreach($this->data['userBarFeatures'] as $barFeature) {
    require_once __DIR__ . '/UserBar'.ucfirst($barFeature).'.inc';
}

$user = wire('user');
$page = wire('page');
$pages = wire('pages');
$config = wire('config');
$iconColor = \TracyDebugger::getDataValue("userBarIconColor");
$userBar .= eval($this->data['userBarCustomFeatures']);

$userBar .= '
</div>';