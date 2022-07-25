<?php
/**
 * Tracy Debugger Language Switcher Panel
 * @author Bernhard Baumrock, 12.07.2022
 * @license Licensed under MIT
 * @link https://www.baumrock.com
 */
class LanguageSwitcherPanel extends BasePanel {

    // settings
    private $name = 'languageswitcher';
    private $label = 'Language Switcher';

    /**
     * define the tab for the panel in the debug bar
     */
    public function getTab() {
        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer($this->name);

        $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--tabler" width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="' . ($this->wire('session')->tracyLanguageSwitcher ? TracyDebugger::COLOR_WARN : TracyDebugger::COLOR_NORMAL) . '" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M4 5h7M9 3v2c0 4.418-2.239 8-5 8"></path><path d="M5 9c-.003 2.144 2.952 3.908 6.7 4m.3 7l4-9l4 9m-.9-2h-6.2"></path></g></svg>';

        return "<span title='{$this->label}'>{$this->icon} ".(\TracyDebugger::getDataValue('showPanelLabels') ? $this->label : '')."</span>";
    }

    /**
     * the panel's HTML code
     */
    public function getPanel() {
        $out = "<h1>{$this->icon} {$this->label}</h1>";

        // panel body
        if(!$this->wire('languages')) {
            $out = "No languages installed";
        }
        else {
            $out .= '
            <div class="tracy-inner">
                <form name="languageSwitcherPanel" action="'.\TracyDebugger::inputUrl(true).'" method="post">
                    <select onchange="this.form.submit()" id="tracyLanguageSwitcher" name="tracyLanguageSwitcher" size="5" style="width:100% !important; height:150px !important">';
                        foreach($this->wire('pages')->find('template=language, include=all, sort=name') as $lang) {
                            if($this->wire('session')->tracyLanguageSwitcher && $this->wire('session')->tracyLanguageSwitcher === $lang->id) {
                                $highlight = '; background:'.TracyDebugger::COLOR_WARN.'; color: #FFFFFF;';
                            }
                            elseif($this->wire('user')->language->id === $lang->id) {
                                $highlight = '; background: '.TracyDebugger::COLOR_NORMAL.'; color: #FFFFFF;"';
                            }
                            else {
                                $highlight = '';
                            }
                            $out .= '<option id="lang_'.$lang->id.'" value="'.$lang->id.'" style="padding: 2px'.$highlight.'">'.$lang->title . ' (#'.$lang->id.')</option>';
                        }
                $out .= '
                    </select>';
                $out .= '<p><input type="submit" name="tracyResetLanguageSwitcher" value="Reset" /></p>';
                $out .= \TracyDebugger::generatePanelFooter($this->name, \Tracy\Debugger::timer($this->name), strlen($out));
            $out .= '
                </form>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", (event) => {
                    var selectElement = document.querySelector("#tracyLanguageSwitcher");
                    var langElement = document.querySelector("#lang_'.$this->wire('user')->language->id.'");
                    selectElement.scrollTop = langElement.offsetTop - selectElement.offsetTop;
                });
            </script>
            ';
            
        }

        return parent::loadResources() . $out;
    }

}