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

    // the svg icon shown in the bar and in the panel header
    private $icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--tabler" width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M4 5h7M9 3v2c0 4.418-2.239 8-5 8"></path><path d="M5 9c-.003 2.144 2.952 3.908 6.7 4m.3 7l4-9l4 9m-.9-2h-6.2"></path></g></svg>';

    /**
     * define the tab for the panel in the debug bar
     */
    public function getTab() {
        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer($this->name);
        return "<span title='{$this->label}'>{$this->icon} ".(\TracyDebugger::getDataValue('showPanelLabels') ? $this->label : '')."</span>";
    }

    /**
     * the panel's HTML code
     */
    public function getPanel() {
        $out = "<h1>{$this->icon} {$this->label}</h1>";

        // example of a maximize button
        $out .= '<span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'' . $this->className . '\')">+</a></span></span>';

        // panel body
        if(!$this->wire()->languages) {
            $out = "No languages installed";
        }
        else {
            $url = $this->wire()->input->url(true);
            $url .= strpos($url, "?") ? '&' : '?';
            $out .= '<div class="tracy-inner">';
                foreach($this->wire()->languages as $lang) {
                    $out .= "<div><a href={$url}tracyLangSwitcher=$lang>{$lang->title} ({$lang->name})</a></div>";
                }
                $profile = $this->wire()->pages->get(2)->url."profile/";
                $out .= "<p class='notes'>Note that the language is set for the session.<br>
                    You can change your language persistantly <a href=$profile>in your profile</a>.</p>";
                $out .= \TracyDebugger::generatePanelFooter($this->name, \Tracy\Debugger::timer($this->name), strlen($out));
            $out .= '</div>';

        }

        return parent::loadResources() . $out;
    }

}
