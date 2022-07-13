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

        $col = $this->wire()->session->get('tracyLangSwitcher')
            ? TracyDebugger::COLOR_WARN
            : TracyDebugger::COLOR_NORMAL;
        $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--tabler" width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="'.$col.'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M4 5h7M9 3v2c0 4.418-2.239 8-5 8"></path><path d="M5 9c-.003 2.144 2.952 3.908 6.7 4m.3 7l4-9l4 9m-.9-2h-6.2"></path></g></svg>';

        return "<span title='{$this->label}'>{$this->icon} ".(\TracyDebugger::getDataValue('showPanelLabels') ? $this->label : '')."</span>";
    }

    /**
     * the panel's HTML code
     */
    public function getPanel() {
        $out = "<h1>{$this->icon} {$this->label}</h1>";

        // panel body
        if(!$this->wire()->languages) {
            $out = "No languages installed";
        }
        else {
            $url = $this->wire()->input->url(true);
            $url .= strpos($url, "?") ? '&' : '?';

            $sessionLang = $this->wire()->session->get('tracyLangSwitcher');
            $out .= '<div class="tracy-inner">';
                $out .= '<div style="max-height:300px;overflow-y:scroll;margin:0;padding:0;margin-bottom:10px;">';
                foreach($this->wire()->languages as $lang) {
                    $style = $sessionLang == $lang->id
                        ? "style='border-left: 5px solid ".TracyDebugger::COLOR_WARN.";padding-left: 5px;'"
                        : "style='padding-left: 10px;'";
                    $out .= "<div $style><a href={$url}tracyLangSwitcher=$lang>#$lang {$lang->title} ({$lang->name})</a></div>";
                }
                $out .= "</div>";
                $profile = $this->wire()->pages->get(2)->url."profile/";
                $out .= "<div class='uk-text-small'>Note that the language is set for the session.<br>
                    You can change your language persistantly <a href=$profile>in your profile</a>.</div>";
                $out .= \TracyDebugger::generatePanelFooter($this->name, \Tracy\Debugger::timer($this->name), strlen($out));
            $out .= '</div>';

        }

        return parent::loadResources() . $out;
    }

}
