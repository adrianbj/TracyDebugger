<?php namespace ProcessWire;
// Post processor for Admin Tools panel - delete children, language, template, field, change field type, uninstall module
// ADMIN TOOLS
if(static::$allowedSuperuser && ($this->wire('input')->post->deleteChildren || $this->wire('input')->post->deleteLanguage || $this->wire('input')->post->deleteTemplate || $this->wire('input')->post->deleteField || $this->wire('input')->post->changeFieldType || $this->wire('input')->post->uninstallModule) && $this->wire('session')->CSRF->validate()) {
    // the ids / names come from the request, so each action refuses system items (and anything in the admin
    // tree), not just the ones the panel offers
    // delete children
    if($this->wire('input')->post->deleteChildren) {
        $parent = $this->wire('pages')->get((int)$this->wire('input')->post->adminToolsId);
        if($parent->id && $parent->rootParent->id != $this->wire('config')->adminRootPageID) {
            foreach($parent->children("include=all") as $child) {
                if($child->hasStatus(Page::statusSystem) || $child->hasStatus(Page::statusSystemID)) continue;
                $child->delete(true);
            }
        }
    }
    // delete language
    if($this->wire('input')->post->deleteLanguage) {
        $lang = $this->wire('pages')->get((int)$this->wire('input')->post->adminToolsId);
        if(!$lang instanceof Language || $lang->isDefault()) throw new WireException('Not a language that can be deleted');
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
        $template = $this->wire('templates')->get((int)$this->wire('input')->post->adminToolsId);
        // checked before its pages are deleted: Templates::delete() refuses a system template, but only afterwards
        if(!$template || ($template->flags & Template::flagSystem)) throw new WireException('Not a template that can be deleted');
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
        if(!$field || ($field->flags & (Field::flagSystem | Field::flagPermanent))) throw new WireException('Not a field that can be deleted');
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
        $fieldtype = $this->wire('fieldtypes')->get((string) $this->wire('input')->post->changeFieldType);
        if(!$field || ($field->flags & (Field::flagSystem | Field::flagPermanent)) || !$fieldtype instanceof Fieldtype) throw new WireException('Not a field type change that can be made');
        $field->type = $fieldtype;
        $field->save();
    }
    // uninstall module
    if($this->wire('input')->post->uninstallModule) {
        $moduleName = (string) $this->wire('input')->post->adminToolsName;
        $moduleInfo = $this->wire('modules')->getModuleInfoVerbose($moduleName);
        if(!$this->wire('modules')->isInstalled($moduleName) || !empty($moduleInfo['core']) || $moduleName === 'TracyDebugger') throw new WireException('Not a module that can be uninstalled here');
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
