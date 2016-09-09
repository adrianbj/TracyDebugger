<?php

$userBar = '
<style>
    div#TracyUserBar {
        bottom: 0px;
        right: 0px;
        z-index: 99999;
        position: fixed;
        margin: 0px !important;
        background: #FFFFFF;
        padding: 0 3px;
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

$userBar .= '
</div>';