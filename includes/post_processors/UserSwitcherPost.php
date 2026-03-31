// Post processor for User Switcher panel - session management, logout, revert, switch user

        // USER SWITCHER
        // process userSwitcher if panel open and switch initiated
        if(in_array('userSwitcher', static::$showPanels) && $this->wire('input')->post->userSwitcher) {
            // if user is superuser and session length is set, save to config settings
            if(static::$allowedSuperuser && ($this->wire('input')->post->userSwitcher || $this->wire('input')->post->logoutUserSwitcher) && $this->wire('session')->CSRF->validate()) {
                // cleanup expired sessions
                if(isset($this->data['userSwitchSession'])) {
                    foreach($this->data['userSwitchSession'] as $id => $expireTime) {
                        if($expireTime < time()) unset($this->data['userSwitchSession'][$id]);
                    }
                }
                // if no existing session ID, start a new session
                if(!$this->wire('session')->tracyUserSwitcherId) {
                    $pass = new Password();
                    $challenge = $pass->randomBase64String(32);
                    $this->wire('session')->tracyUserSwitcherId = $challenge;

                    $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
                    $this->data['originalUserSwitcher'][$this->wire('session')->tracyUserSwitcherId] = $this->wire('user')->name;
                    $configData['originalUserSwitcher'] = $this->data['originalUserSwitcher'];
                    $this->wire('modules')->saveModuleConfigData($this, $configData);

                }
                // save session ID and expiry time in module config settings
                $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
                $this->data['userSwitchSession'][$this->wire('session')->tracyUserSwitcherId] = time() + $this->wire('config')->sessionExpireSeconds;
                $configData['userSwitchSession'] = $this->data['userSwitchSession'];
                $this->wire('modules')->saveModuleConfigData($this, $configData);
            }

            // if logout button clicked
            if($this->wire('input')->post->logoutUserSwitcher && $this->wire('session')->CSRF->validate()) {
                if($this->wire('session')->tracyUserSwitcherId) {
                    // if session variable exists, grab it and add to the new session after logging out
                    $tracyUserSwitcherId = $this->wire('session')->tracyUserSwitcherId;
                    $this->wire('session')->logout();
                    $this->wire('session')->tracyUserSwitcherId = $tracyUserSwitcherId;
                }
                else {
                    $this->wire('session')->logout();
                }
                $this->wire('session')->redirect($this->httpReferer);
            }
            // if end session clicked, remove session variable and config settings entry
            elseif($this->wire('input')->post->endSessionUserSwitcher && $this->wire('session')->CSRF->validate()) {
                $this->wire('session')->remove("tracyUserSwitcherId");
                $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
                unset($this->data['userSwitchSession'][$this->wire('session')->tracyUserSwitcherId]);
                unset($configData['userSwitchSession'][$this->wire('session')->tracyUserSwitcherId]);
                $this->wire('modules')->saveModuleConfigData($this, $configData);
                $this->wire('session')->redirect($this->httpReferer);
            }
            // if session not expired, switch to original user
            elseif($this->wire('input')->post->revertOriginalUserSwitcher && $this->wire('session')->CSRF->validate()) {
                if(isset($this->data['userSwitchSession'][$this->wire('session')->tracyUserSwitcherId]) && $this->data['userSwitchSession'][$this->wire('session')->tracyUserSwitcherId] > time() && $this->wire('session')->tracyUserSwitcherId) {
                    // if session variable exists, grab it and add to the new session after logging out
                    // and forceLogin the original user
                    $tracyUserSwitcherId = $this->wire('session')->tracyUserSwitcherId;
                    if($this->wire('user')->isLoggedin()) $this->wire('session')->logout();
                    $this->wire('session')->forceLogin($this->data['originalUserSwitcher'][$tracyUserSwitcherId]);
                    $this->wire('session')->tracyUserSwitcherId = $tracyUserSwitcherId;
                }
                $this->wire('session')->redirect($this->httpReferer);
            }
            // if session not expired, switch to requested user
            elseif($this->wire('input')->post->userSwitcher && $this->wire('session')->CSRF->validate()) {
                if(isset($this->data['userSwitchSession'][$this->wire('session')->tracyUserSwitcherId]) && $this->data['userSwitchSession'][$this->wire('session')->tracyUserSwitcherId] > time() && $this->wire('session')->tracyUserSwitcherId) {
                    // if session variable exists, grab it and add to the new session after logging out
                    // and forceLogin the new switched user
                    $tracyUserSwitcherId = $this->wire('session')->tracyUserSwitcherId;
                    if($this->wire('user')->isLoggedin()) $this->wire('session')->logout();
                    $this->wire('session')->forceLogin($this->wire('input')->post->userSwitcher);
                    $this->wire('session')->tracyUserSwitcherId = $tracyUserSwitcherId;
                }
                $this->wire('session')->redirect($this->httpReferer);
            }
        }
