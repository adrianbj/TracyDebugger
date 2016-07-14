<?php

use Tracy\Debugger;
use Tracy\IBarPanel;
use Tracy\Dumper;

class EventInterceptorPanel implements IBarPanel {

    protected $icon;
    protected $iconColor;
    protected $entries;

    public function getTab() {

        \Tracy\Debugger::timer('eventInterceptor');

        $items = \TracyDebugger::$eventObjects;

        if(count($items) > 0) {
            $this->iconColor = '#CD1818';
            foreach($items as $item) {
                $this->entries .= '<h2>Event Object</h2>';
                $this->entries .= Dumper::toHtml($item->object, array(Dumper::COLLAPSE => false));
                $this->entries .= '<h2>Event Arguments</h2>';
                $this->entries .= Dumper::toHtml($item->arguments, array(Dumper::COLLAPSE => false));
            }
        }
        elseif(wire('input')->cookie->eventInterceptorHook) {
            $this->iconColor = '#FF9933';
        }
        else {
            $this->iconColor = '#009900';
            $this->entries = 'No Events Intercepted';
        }

        $this->icon = '
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="248 248 16 16" enable-background="new 248 248 16 16" xml:space="preserve" width="16px" height="16px">
            <path d="M248,256c0,4.4,3.6,8,8,8c4.4,0,8-3.6,8-8s-3.6-8-8-8C251.6,248,248,251.6,248,256z M262,256
                c0,1.1-0.3,2.2-0.9,3.1l-8.2-8.2c0.9-0.6,2-0.9,3.1-0.9C259.3,250,262,252.7,262,256z M250,256c0-1.1,0.3-2.2,0.9-3.1l8.2,8.2
                c-0.9,0.6-2,0.9-3.1,0.9C252.7,262,250,259.3,250,256z" fill="'.$this->iconColor.'"/>
        </svg>
        ';

        return '
        <span title="Event Interceptor">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Event Interceptor' : '') . ' ' . '
        </span>
        ';
    }


    public function getPanel() {

        $out = '
        <h1>' . $this->icon . ' Event Interceptor</h1>

        <script>
            function setEventInterceptorHook(reset) {
                if(reset === true) {
                    document.cookie = "eventInterceptorHook=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                    document.getElementById("eventInterceptorHook").value = "";
                    document.getElementById("eventHookLegend").innerHTML = "Enter an Event Hook";
                }
                else {
                    document.cookie = "eventInterceptorHook="+document.getElementById("eventInterceptorHook").value+"; expires=0; path=/";
                    document.getElementById("eventHookLegend").innerHTML = "<strong>" + document.getElementById("eventInterceptorHook").value + "</strong> hook is set";
                }
            }
        </script>

        <style class="tracy-debug">
            #tracy-debug .tracy-DumpPanel h2 {
                font: 11pt/1.5 sans-serif;
                margin: 0;
                padding: 2px 8px;
                background: #3484d2;
                color: white;
            }
        </style>

        <div class="tracy-inner tracy-DumpPanel">

            <fieldset>
                <legend><span id="eventHookLegend">'.(wire('input')->cookie->eventInterceptorHook ? '<strong>' . wire('input')->cookie->eventInterceptorHook . '</strong> hook is set' : 'Enter an Event Hook') .'</span></legend><br />';
                $out .= '
                <input type="text" id="eventInterceptorHook" name="eventInterceptorHook" value="'.wire('input')->cookie->eventInterceptorHook.'">
                <input type="submit" onclick="setEventInterceptorHook()" value="Set Hook" />&nbsp;
                <input type="submit" onclick="setEventInterceptorHook(true)" value="Remove Hook" />
            </fieldset>
            <br /><br />';

            \TracyDebugger::$panelGenerationTime['eventInterceptor'] = \Tracy\Debugger::timer('eventInterceptor');
            $out .= $this->entries . \TracyDebugger::generatedTime(\TracyDebugger::$panelGenerationTime['eventInterceptor']) . '
        </div>';

        return $out;
    }

}