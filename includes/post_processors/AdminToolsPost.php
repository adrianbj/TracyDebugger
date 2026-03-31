// Post processor for Admin Tools panel - delete children, language, template, field, change field type, uninstall module
// ADMIN TOOLS
if(static::$allowedSuperuser && ($this->wire('input')->post->deleteChildren || $this->wire('input')->post->deleteLanguage || $this->wire('input')->post->deleteTemplate || $this->wire('input')->post->deleteField || $this->wire('input')->post->changeFieldType || $this->wire('input')->post->uninstallModule) && $this->wire('session')->CSRF->validate()) {
    // delete children
    if($this->wire('input')->post->deleteChildren) {
        foreach($this->wire('pages')->get((int)$this->wire('input')->post->adminToolsId)->children("include=all") as $child) {
            $child->delete(true);
        }
    }
    // delete language
    if($this->wire('input')->post->deleteLanguage) {
        $lang = $this->wire('pages')->get((int)$this->wire('input')->post->adminToolsId);
        $lang_parent = $lang->parent;
        $lang->addStatus(Page::statusSystemOverride);
        $lang->removeStatus('system');
        $lang->save();
        $lang->removeStatus(Page::statusSystem);
        $lang->save();
        $this->wire('pages')->delete($lang);
        $this->wire('session')->redirect($lang_parent->url);
    }
    // delete template
    if($this->wire('input')->post->deleteTemplate) {
        foreach($this->wire('pages')->find("template=".(int)$this->wire('input')->post->adminToolsId.", include=all") as $p) {
            $p->delete();
        }
        $template = $this->wire('templates')->get((int)$this->wire('input')->post->adminToolsId);
        $this->wire('templates')->delete($template);
        $templateName = $template->name;
        $fieldgroup = $this->wire('fieldgroups')->get($templateName);
        if($fieldgroup) $this->wire('fieldgroups')->delete($fieldgroup);
        $this->wire('session')->redirect($this->wire('config')->urls->admin);
    }
    // delete field
    if($this->wire('input')->post->deleteField) {
        $field = $this->wire('fields')->get((int)$this->wire('input')->post->adminToolsId);
        foreach($this->wire('templates') as $template) {
            if(!$template->hasField($field)) continue;
            $template->fields->remove($field);
            $template->fields->save();
        }
        $this->wire('fields')->delete($field);
        $this->wire('session')->redirect($this->wire('config')->urls->admin.'setup/field');
    }
    // change field type
    if($this->wire('input')->post->changeFieldType) {
        $field = $this->wire('fields')->get((int)$this->wire('input')->post->adminToolsId);
        $field->type = $this->wire('input')->post->changeFieldType;
        $field->save();
    }
    // uninstall module
    if($this->wire('input')->post->uninstallModule) {
        $moduleName = $this->wire('input')->post->adminToolsName;
        $reason = $this->wire('modules')->isUninstallable($moduleName, true);
        $class = $this->wire('modules')->getModuleClass($moduleName);
        if($reason !== true) {
            if(strpos($reason, 'Fieldtype') !== false) {
                foreach($this->wire('fields') as $field) {
                    $fieldtype = wireClassName($field->type, false);
                    if($fieldtype == $class) {
                        foreach($this->wire('templates') as $template) {
                            if(!$template->hasField($field)) continue;
                            $template->fields->remove($field);
                            $template->fields->save();
                        }
                        $this->wire('fields')->delete($field);
                    }
                }
            }
            elseif(strpos($reason, 'required') !== false) {
                $dependents = $this->wire('modules')->getRequiresForUninstall($class);
                foreach($dependents as $dependent) {
                    $this->wire('modules')->uninstall($dependent);
                }
            }
        }
        $this->wire('modules')->uninstall($moduleName);
        $this->wire('session')->redirect($this->wire('config')->urls->admin.'module');
    }
}
