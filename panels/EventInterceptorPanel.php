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
            $this->iconColor = $this->wire('input')->cookie->eventInterceptorHook ? \TracyDebugger::COLOR_ALERT : \TracyDebugger::COLOR_NORMAL;
            $this->entries .= '
            <div class="event-items">
                <p><input type="submit" onclick="clearEvents()" value="Clear Events" /></p><br />';
            foreach($items as $item) {
                $this->entries .= '<h2><strong>' . date("Y-m-d H:i:s", $item['timestamp']) . '</strong></h2>';
                $this->entries .= '<h2>Event Object</h2>';
                $this->entries .= $item['object'];
                $this->entries .= '<h2>Event Arguments</h2>';
                $this->entries .= $item['arguments'];
            }
            $this->entries .= '</div>';
        }
        elseif($this->wire('input')->cookie->eventInterceptorHook) {
            $this->iconColor = \TracyDebugger::COLOR_WARN;
            $this->entries = 'No Events Intercepted';
        }
        else {
            $this->iconColor = \TracyDebugger::COLOR_NORMAL;
            $this->entries = 'No Events Intercepted';
        }

        $this->icon = '
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="248 248 16 16" enable-background="new 248 248 16 16" xml:space="preserve" width="16px" height="16px">
            <path class="eventInterceptorIconPath" d="M248,256c0,4.4,3.6,8,8,8c4.4,0,8-3.6,8-8s-3.6-8-8-8C251.6,248,248,251.6,248,256z M262,256
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

        $hookSettings = json_decode($this->wire('input')->cookie->eventInterceptorHook, true);

        $out = '
        <h1>' . $this->icon . ' Event Interceptor' . ($isAdditionalBar ? ' ('.$isAdditionalBar.')' : '') . '</h1>

        <script>
            function clearEvents() {
                document.cookie = "tracyClearEventItems=true;expires=0;path=/";

                var elements = document.getElementsByClassName("event-items");
                while(elements.length > 0) {
                    elements[0].parentNode.removeChild(elements[0]);
                }

                var icons = document.getElementsByClassName("eventInterceptorIconPath");
                i=0;
                while(i < icons.length) {
                    icons[i].style.fill="'.\TracyDebugger::COLOR_NORMAL.'";
                    i++;
                }

                var eventCounts = document.getElementsByClassName("eventCount");
                i=0;
                while(i < eventCounts.length) {
                    eventCounts[i].innerHTML="";
                    i++;
                }
            }

            function setEventInterceptorHook(status) {
                if(status === "remove") {
                    document.cookie = "eventInterceptorHook=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                    document.getElementById("eventInterceptorHook").value = "";
                    document.getElementById("eventHookLegend").innerHTML = "Enter an Event Hook (eg. PageRender::renderPage)";
                }
                else if(document.getElementById("eventInterceptorHook").value != "") {
                    var interceptEvent = {};
                    interceptEvent.hook = document.getElementById("eventInterceptorHook").value;
                    interceptEvent.when = document.getElementById("tracyEventWhenBefore").checked ? "before" : "after";
                    interceptEvent.return = document.getElementById("tracyEventReturnFalse").checked ? "false" : "default";
                    document.cookie = "eventInterceptorHook="+JSON.stringify(interceptEvent)+"; expires=0; path=/";
                    document.getElementById("eventHookLegend").innerHTML = "<strong>" + document.getElementById("eventInterceptorHook").value + "</strong> <em>" + interceptEvent.when + "</em> hook is set to return <em>" + interceptEvent.return + "</em>";
                }

                if(status === "set") {
                    var fillColor = "'.\TracyDebugger::COLOR_WARN.'";
                }
                else if(document.getElementById("tracyEventEntries").innerHTML == "No Events Intercepted" || document.getElementById("tracyEventEntries").innerHTML.trim() == "" || status === "remove") {
                    var fillColor = "'.\TracyDebugger::COLOR_NORMAL.'";
                }

                var icons = document.getElementsByClassName("eventInterceptorIconPath");
                i=0;
                while(i < icons.length) {
                    icons[i].style.fill=fillColor;
                    i++;
                }
            }
        </script>

        <div class="tracy-inner tracy-DumpPanel" style="min-width:350px !important">

            <fieldset id="eventInterceptor">
                <legend><span id="eventHookLegend">'.(isset($hookSettings['hook']) ? '<strong>' . $hookSettings['hook'] . '</strong> <em>' . $hookSettings['when'] . '</em> hook is set to return <em>' . $hookSettings['return'] . '</em>' : 'Enter an Event Hook (eg. PageRender::renderPage)') .'</span></legend><br />';
                $out .= '
                <input type="text" style="width:250px !important" id="eventInterceptorHook" name="eventInterceptorHook" value="'.(isset($hookSettings['hook']) ? $hookSettings['hook'] : '') .'">
                <p>
                    Hook: <label style="display:inline-block !important"><input type="radio" id="tracyEventWhenBefore" name="when" value="before" '. (isset($hookSettings['when']) && $hookSettings['when'] == 'before' ? ' checked="checked"' : '') .' /> Before</label>
                    <label style="display:inline-block !important"><input type="radio" id="tracyEventWhenAfter" name="when" value="after" '. (!isset($hookSettings) || $hookSettings['when'] == 'after' ? ' checked="checked"' : '') .' /> After</label>
                </p>
                <p>
                    Return: <label style="display:inline-block !important"><input type="radio" id="tracyEventReturnDefault" name="return" value="default" '. (!isset($hookSettings) || $hookSettings['return'] == 'default' ? ' checked="checked"' : '') .' /> Default</label>
                    <label style="display:inline-block !important"><input type="radio" id="tracyEventReturnFalse" name="return" value="false" '. (isset($hookSettings['return']) && $hookSettings['return'] == 'false' ? ' checked="checked"' : '') .' /> False</label>

                </p>
                <br />
                <input type="submit" onclick="setEventInterceptorHook(\'set\')" value="Set Hook" />&nbsp;
                <input type="submit" onclick="setEventInterceptorHook(\'remove\')" value="Remove Hook" />&nbsp;
            </fieldset>
            <br /><br />
            <div id="tracyEventEntries">'.$this->entries.'</div>';

            $out .= \TracyDebugger::generatePanelFooter('eventInterceptor', \Tracy\Debugger::timer('eventInterceptor'), strlen($out));

            $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}