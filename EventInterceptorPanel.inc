<?php

use Tracy\Debugger;
use Tracy\IBarPanel;
use Tracy\Dumper;

class EventInterceptorPanel implements IBarPanel {

    protected $icon;
    protected $iconColor;
    protected $entries;
    protected $eventCount;

    public function getTab() {

        \Tracy\Debugger::timer('eventInterceptor');

        $items = wire('session')->tracyEventItems;
        $this->eventCount = count($items);
        if($this->eventCount > 0) {
            $this->iconColor = '#CD1818';
            $this->entries .= '
            <div class="event-items">
                <p><input type="submit" onclick="clearEvents()" value="Clear Events" /></p>';
            foreach($items as $item) {
                $this->entries .= '<h2>' . date("Y-m-d H:i:s", $item['timestamp']) . '</h2>';
                $this->entries .= '<h3>Event Object</h3>';
                $this->entries .= $item['object'];
                $this->entries .= '<h3>Event Arguments</h3>';
                $this->entries .= $item['arguments'];
            }
            $this->entries .= '</div>';
        }
        elseif(wire('input')->cookie->eventInterceptorHook) {
            $this->iconColor = '#FF9933';
            $this->entries = 'No Events Intercepted';
        }
        else {
            $this->iconColor = '#009900';
            $this->entries = 'No Events Intercepted';
        }

        $this->icon = '
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="248 248 16 16" enable-background="new 248 248 16 16" xml:space="preserve" width="16px" height="16px">
            <path id="interceptorIconPath" d="M248,256c0,4.4,3.6,8,8,8c4.4,0,8-3.6,8-8s-3.6-8-8-8C251.6,248,248,251.6,248,256z M262,256
                c0,1.1-0.3,2.2-0.9,3.1l-8.2-8.2c0.9-0.6,2-0.9,3.1-0.9C259.3,250,262,252.7,262,256z M250,256c0-1.1,0.3-2.2,0.9-3.1l8.2,8.2
                c-0.9,0.6-2,0.9-3.1,0.9C252.7,262,250,259.3,250,256z" fill="'.$this->iconColor.'"/>
        </svg>
        ';

        return '
        <span title="Event Interceptor">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Event Interceptor' : '') . ' ' . ($this->eventCount > 0 ? $this->eventCount : '') . '
        </span>
        ';
    }


    public function getPanel() {

        $out = '
        <h1>' . $this->icon . ' Event Interceptor</h1>

        <script>
            function clearEvents() {
                document.cookie = "tracyClearEventItems=true;expires=0;path=/";
                if(document.getElementById("eventInterceptorHook").value == "") {
                    var fillColor = "#009900";
                }
                else {
                    var fillColor = "#FF9933";
                }
                var elements = document.getElementsByClassName("event-items");
                while(elements.length > 0){
                    elements[0].parentNode.removeChild(elements[0]);
                }
                document.getElementById("interceptorIconPath").style.fill=fillColor;
            }

            function setEventInterceptorHook(reset) {
                if((document.getElementById("tracyEventEntries").innerHTML == "No Events Intercepted" || document.getElementById("tracyEventEntries").innerHTML.trim() == "") && reset === true) {
                    var fillColor = "#009900";
                }
                else {
                    var fillColor = "#FF9933";
                }

                if(reset === true) {
                    document.cookie = "eventInterceptorHook=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                    document.getElementById("eventInterceptorHook").value = "";
                    document.getElementById("eventHookLegend").innerHTML = "Enter an Event Hook";
                }
                else if(document.getElementById("eventInterceptorHook").value != "") {
                    document.cookie = "eventInterceptorHook="+document.getElementById("eventInterceptorHook").value+"; expires=0; path=/";
                    document.getElementById("eventHookLegend").innerHTML = "<strong>" + document.getElementById("eventInterceptorHook").value + "</strong> hook is set";
                }
                document.getElementById("interceptorIconPath").style.fill=fillColor;
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

        <div class="tracy-inner tracy-DumpPanel" style="width:350px !important">

            <fieldset>
                <legend><span id="eventHookLegend">'.(wire('input')->cookie->eventInterceptorHook ? '<strong>' . wire('input')->cookie->eventInterceptorHook . '</strong> hook is set' : 'Enter an Event Hook') .'</span></legend><br />';
                $out .= '
                <input type="text" id="eventInterceptorHook" name="eventInterceptorHook" value="'.wire('input')->cookie->eventInterceptorHook.'">
                <input type="submit" onclick="setEventInterceptorHook()" value="Set Hook" />&nbsp;
                <input type="submit" onclick="setEventInterceptorHook(true)" value="Remove Hook" />&nbsp;
            </fieldset>
            <br /><br />
            <div id="tracyEventEntries">'.$this->entries.'</div>';

            \TracyDebugger::$panelGenerationTime['eventInterceptor']['time'] = \Tracy\Debugger::timer('eventInterceptor');
            \TracyDebugger::$panelGenerationTime['eventInterceptor']['size'] = strlen($out);
            $out .= \TracyDebugger::generatedTimeSize(\TracyDebugger::$panelGenerationTime['eventInterceptor']['time'], \TracyDebugger::$panelGenerationTime['eventInterceptor']['size']);

            $out .= '
        </div>';

        return $out;
    }

}