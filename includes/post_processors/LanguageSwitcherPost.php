// Post processor for Language Switcher panel - switch, reset, and persist language selection

        // LANGUAGE SWITCHER PANEL
        // process languageSwitcher if panel open and switch initiated
        if(in_array('languageSwitcher', static::$showPanels) && (($this->wire('input')->post->tracyLanguageSwitcher && $this->wire('session')->CSRF->validate()) || $this->wire('session')->tracyLanguageSwitcher)) {
            $langId = $this->wire('input')->post->int('tracyLanguageSwitcher');
            if($langId) {
                // compare language setting from session with users profile
                if($this->wire('user')->language->id === $langId) {
                    // language is users profile language -> reset session
                    $this->wire('session')->remove('tracyLanguageSwitcher');
                }
                else {
                    // language is different from profile -> save it
                    $this->wire('session')->set('tracyLanguageSwitcher', $langId);
                }
            }

            // reset cache for nav
            // thx @toutouwai https://github.com/Toutouwai/CustomAdminMenus/blob/8dfdfa7d07c40ab2d93e3191d2d960e317738169/CustomAdminMenus.module#L35
            $this->wire('session')->removeFor('AdminThemeUikit', 'prnav');
            $this->wire('session')->removeFor('AdminThemeUikit', 'sidenav');

            if($this->wire('input')->post->tracyResetLanguageSwitcher) {
                $this->wire('session')->remove('tracyLanguageSwitcher');
            }
            // set users language dynamically from session value
            elseif($sessionLangId = $this->wire('session')->get('tracyLanguageSwitcher')) {
                $this->wire('user')->language = $this->wire('languages')->get($sessionLangId);
            }
        }
