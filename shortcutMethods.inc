<?php

// short alias methods for lazy typists :)
if(!function_exists('barDump') && in_array('barDump', $this->data['enabledShortcutMethods'])) {
    function barDump() {
        return call_user_func_array('TD::barDump', func_get_args());
    }
}

if(!function_exists('dump') && in_array('dump', $this->data['enabledShortcutMethods'])) {
    function dump() {
        return call_user_func_array('TD::dump', func_get_args());
    }
}

// really short alias methods for really lazy typists :)
if(!function_exists('bd') && in_array('bd', $this->data['enabledShortcutMethods'])) {
    function bd() {
        return call_user_func_array('TD::barDump', func_get_args());
    }
}

if(!function_exists('d') && in_array('d', $this->data['enabledShortcutMethods'])) {
    function d() {
        return call_user_func_array('TD::dump', func_get_args());
    }
}

if(!function_exists('l') && in_array('l', $this->data['enabledShortcutMethods'])) {
    function l() {
        return call_user_func_array('TD::log', func_get_args());
    }
}

