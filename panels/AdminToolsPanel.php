<?php

class AdminToolsPanel extends BasePanel {

    protected $icon;
    private $name = 'adminTools';
    private $label = 'Admin Tools';

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) {
            return;
        }

        \Tracy\Debugger::timer($this->name);

        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="226.6 226.5 16 16">
            <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M233.6 232.7l-6.6 6.6c-.2.2-.4.5-.4.9s.1.6.4.9l1 1.1c.3.2.5.4.9.4.3 0 .6-.1.9-.4l6.6-6.6c-.6-.3-1.2-.6-1.7-1.1-.5-.6-.9-1.1-1.1-1.8zm-3.7 7.4c-.1.1-.3.2-.4.2-.2 0-.3-.1-.4-.2-.1-.1-.2-.3-.2-.4 0-.2.1-.3.2-.4.1-.1.3-.2.4-.2.2 0 .3.1.4.2.1.1.2.3.2.4 0 .1-.1.2-.2.4zM242.5 231.1c-.1-.1-.1-.1-.2-.1s-.3.1-.7.3c-.4.2-.8.5-1.3.8-.5.3-.7.5-.8.5l-1.9-1v-2.2l2.9-1.6c.1-.1.2-.2.2-.3 0-.1-.1-.2-.2-.3-.3-.2-.6-.3-1-.5-.4-.1-.8-.2-1.2-.2-1.2 0-2.2.4-3.1 1.3-.9.9-1.3 1.9-1.3 3.1 0 1.2.4 2.2 1.3 3.1.9.9 1.9 1.3 3.1 1.3.9 0 1.8-.3 2.5-.8.8-.5 1.3-1.2 1.6-2.1.1-.4.2-.8.2-1-.1-.2-.1-.3-.1-.3z"/>
        </svg>
        ';

        return '
        <span title="'.$this->label.'">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? $this->label : '') . '
        </span>
        ';
    }


    public function getPanel() {

        $i=0;

        $out = '
        <script>
            function unhideUnlockFields(restore) {
                if(restore) {
                    document.cookie = "tracyUnhideUnlockFields=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                }
                else {
                    document.cookie = "tracyUnhideUnlockFields=1; expires=0; path=/";
                }
                location.reload();
            }
        </script>';

        $out .= '
        <h1>' . $this->icon . ' ' . $this->label . '</h1>

        <div class="tracy-inner">';

            if(\TracyDebugger::getDataValue('referencePageEdited') && $this->wire('input')->get('id') && $this->wire('process') == 'ProcessPageEdit') {
                $p = $this->wire('process')->getPage();
            }
            else {
                $p = $this->wire('page');
            }

            if(is_null($p)) {
                $p = $this->wire('page');
            }

            if($p->template != 'admin' && $p->hasChildren()) {
                $i++;
                $out .= '
                <p>
                    <form style="display:inline" method="post" action="'.\TracyDebugger::inputUrl(true).'" onsubmit="return confirm(\'Do you really want to delete all children of this page?\');">
                        <input type="hidden" name="adminToolsId" value="'.$p->id.'" />
                        <input type="submit" name="deleteChildren" value="Delete all children" />
                    </form>
                </p>';
            }

            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
                $i++;
                $t = $this->wire('templates')->get((int)$this->wire('input')->get('id'));
                $out .= '
                <p>
                    <form style="display:inline" method="post" action="'.\TracyDebugger::inputUrl(true).'" onsubmit="return confirm(\'Do you really want to delete the '.$t->name.' template and all associated pages?\');">
                        <input type="hidden" name="adminToolsId" value="'.$t->id.'" />
                        <input type="submit" name="deleteTemplate" value="Delete \''.$t->name.'\' template" />
                    </form>
                </p>';
            }

            if($this->wire('input')->get('id') && ($this->wire('page')->process == 'ProcessPageEdit' || $this->wire('page')->process == 'ProcessUser')) {
                $i++;
                if($this->wire('input')->cookie->tracyUnhideUnlockFields == 1) {
                    $out .= '
                    <p>
                        <input type="submit" name="restoreFieldCollapsedStatus" onclick="unhideUnlockFields(true)" value="Restore Field Collapsed Status" />
                    </p>';
                }
                else {
                    $out .= '
                    <p>
                        <input type="submit" name="unhideUnlockFields" onclick="unhideUnlockFields()" value="Unhide / Unlock Fields" />
                    </p>';
                }
            }

            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessField') {
                $f = $this->wire('fields')->get((int)$this->wire('input')->get('id'));
                if($f) {
                    $i++;
                    $out .= '
                    <p>
                        <form style="display:inline" method="post" action="'.\TracyDebugger::inputUrl(true).'" onsubmit="return confirm(\'Do you really want to delete the '. $f->name.' field and remove it from all templates/pages?\');">
                            <input type="hidden" name="adminToolsId" value="'.(int)$this->wire('input')->get('id').'" />
                            <input type="submit" name="deleteField" value="Delete \''.$f->name.'\' field" />
                        </form>
                    </p>';
                    $out .= '
                    <p>
                        <form style="display:inline" method="post" action="'.\TracyDebugger::inputUrl(true).'" onsubmit="return confirm(\'Do you really want to change the type of this field?\');">
                            <input type="hidden" name="adminToolsId" value="'.(int)$this->wire('input')->get('id').'" />
                            <select type="submit" name="changeFieldType">';
                                foreach($this->wire('fieldtypes')->sort('name') as $fieldtype) {
                                    $out .= '<option value="'.$fieldtype.'"'.($f->type === $fieldtype ? " selected='selected'" : "").'>'.str_replace('Fieldtype', '', $fieldtype).'</option>';
                                }
                            $out .= '</select>
                            <input type="submit" value="Change field type" />
                        </form>
                    </p>';
                }
            }

            if($this->wire('input')->get('name') && $this->wire('page')->process == 'ProcessModule') {
                $i++;
                $moduleName = $this->wire('sanitizer')->selectorValue($this->wire('input')->get('name'));
                $confirmSuffix = '';
                $reason = $this->wire('modules')->isUninstallable($this->wire('modules')->get($moduleName), true);
                if($reason !== true) {
                    if(strpos($reason, 'Fieldtype') !== false) {
                        $confirmSuffix .= ' and its associated fields';
                    }
                    elseif(strpos($reason, 'required') !== false) {
                        $confirmSuffix .= ' and any modules that require it';
                    }
                }
                $out .= '
                <p>
                    <form style="display:inline" method="post" action="'.\TracyDebugger::inputUrl(true).'" onsubmit="return confirm(\'Do you really want to uninstall this module' . $confirmSuffix . '?\');">
                        <input type="hidden" name="adminToolsName" value="'.$moduleName.'" />
                        <input type="submit" name="uninstallModule" value="Uninstall module" />
                    </form>
                </p>';
            }

            if($i == 0) {
                $out .= 'No available tools.';
            }


            $out .= \TracyDebugger::generatePanelFooter($this->name, \Tracy\Debugger::timer($this->name), strlen($out));

            $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}