<?php

use Tracy\Dumper;

class EventInterceptorPanel extends BasePanel {

    protected $icon;
    protected $iconColor;
    protected $entries;
    protected $eventCount;

    public function getTab() {

        \Tracy\Debugger::timer('eventInterceptor');

        $items = $this->wire('session')->tracyEventItems;
        $this->eventCount = is_array($items) ? count($items) : 0;
        if($this->eventCount > 0) {
            $this->iconColor = $this->wire('input')->cookie->eventInterceptorHook ? '#CD1818' : '#009900';
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
        elseif($this->wire('input')->cookie->eventInterceptorHook) {
            $this->iconColor = '#FF9933';
            $this->entries = 'No Events Intercepted';
        }
        else {
            $this->iconColor = '#009900';
            $this->entries = 'No Events Intercepted';
        }

        $this->icon = '
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="248 248 16 16" enable-background="new 248 248 16 16" xml:space="preserve" width="16px" height="16px">
            <path class="interceptorIconPath" d="M248,256c0,4.4,3.6,8,8,8c4.4,0,8-3.6,8-8s-3.6-8-8-8C251.6,248,248,251.6,248,256z M262,256
                c0,1.1-0.3,2.2-0.9,3.1l-8.2-8.2c0.9-0.6,2-0.9,3.1-0.9C259.3,250,262,252.7,262,256z M250,256c0-1.1,0.3-2.2,0.9-3.1l8.2,8.2
                c-0.9,0.6-2,0.9-3.1,0.9C252.7,262,250,259.3,250,256z" fill="'.$this->iconColor.'"/>
        </svg>
        ';

        return '
        <span title="Event Interceptor">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Event Interceptor' : '') . ' ' . ($this->eventCount > 0 ? '<span class="eventCount">' . $this->eventCount . '</span>' : '') . '
        </span>
        ';
    }


    public function getPanel() {
        $isAdditionalBar = \TracyDebugger::isAdditionalBar();
        $out = '
        <h1>' . $this->icon . ' Event Interceptor' . ($isAdditionalBar ? ' ('.$isAdditionalBar.')' : '') . '</h1>

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

                var icons = document.getElementsByClassName("interceptorIconPath");
                i=0;
                while(i < icons.length) {
                    icons[i].style.fill="#009900";
                    i++;
                }

                var iconCounts = document.getElementsByClassName("eventCount");
                i=0;
                while(i < iconCounts.length) {
                    iconCounts[i].innerHTML="";
                    i++;
                }

            }

            function setEventInterceptorHook(status) {

                if(status === "remove") {
                    document.cookie = "eventInterceptorHook=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                    document.getElementById("eventInterceptorHook").value = "";
                    document.getElementById("eventHookLegend").innerHTML = "Enter an Event Hook";
                }
                else if(document.getElementById("eventInterceptorHook").value != "") {
                    document.cookie = "eventInterceptorHook="+document.getElementById("eventInterceptorHook").value+"; expires=0; path=/";
                    document.getElementById("eventHookLegend").innerHTML = "<strong>" + document.getElementById("eventInterceptorHook").value + "</strong> hook is set";
                }

                if(status === "set") {
                    var fillColor = "#FF9933";
                }
                else if(document.getElementById("tracyEventEntries").innerHTML == "No Events Intercepted" || document.getElementById("tracyEventEntries").innerHTML.trim() == "" || status === "remove") {
                    var fillColor = "#009900";
                }


                var icons = document.getElementsByClassName("interceptorIconPath");
                i=0;
                while(i < icons.length) {
                    icons[i].style.fill=fillColor;
                    i++;
                }

            }
        </script>

        <div class="tracy-inner tracy-DumpPanel" style="min-width:350px !important">

            <fieldset>
                <legend><span id="eventHookLegend">'.($this->wire('input')->cookie->eventInterceptorHook ? '<strong>' . $this->wire('input')->cookie->eventInterceptorHook . '</strong> hook is set' : 'Enter an Event Hook') .'</span></legend><br />';
                $out .= '
                <input type="text" id="eventInterceptorHook" name="eventInterceptorHook" value="'.$this->wire('input')->cookie->eventInterceptorHook.'">
                <input type="submit" onclick="setEventInterceptorHook(\'set\')" value="Set Hook" />&nbsp;
                <input type="submit" onclick="setEventInterceptorHook(\'remove\')" value="Remove Hook" />&nbsp;
            </fieldset>
            <br /><br />
            <div id="tracyEventEntries">'.$this->entries.'</div>';

            $out .= \TracyDebugger::generatedTimeSize('eventInterceptor', \Tracy\Debugger::timer('eventInterceptor'), strlen($out));

            $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}