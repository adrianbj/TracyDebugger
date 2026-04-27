<?php namespace ProcessWire;
// Post processor for User Switcher panel - session management, logout, revert, switch user

        // USER SWITCHER
        // process userSwitcher if panel open and switch initiated
        if(in_array('userSwitcher', static::$showPanels) && $this->wire('input')->post->userSwitcher) {
            $session = $this->wire('session');
            // The outer if() guarantees post->userSwitcher is set; the additional checks
            // below are kept for clarity in case the panel form structure ever changes.
            $hasAction = $this->wire('input')->post->userSwitcher
                || $this->wire('input')->post->logoutUserSwitcher
                || $this->wire('input')->post->endSessionUserSwitcher
                || $this->wire('input')->post->revertOriginalUserSwitcher;

            // Use hasValidToken() rather than validate() so a bad CSRF falls through
            // silently instead of throwing WireCSRFException.
            $csrfOk = $session->CSRF->hasValidToken();

            if(!$hasAction || !$csrfOk) {
                // fall through; no action taken
            }
            // INIT path: only a real superuser may create a new switcher session.
            elseif(static::$allowedSuperuser && !$session->tracyUserSwitcherId) {
                // cleanup expired sessions and their original-user records
                if(isset($this->data['userSwitchSession'])) {
                    foreach($this->data['userSwitchSession'] as $id => $expireTime) {
                        if($expireTime < time()) {
                            unset($this->data['userSwitchSession'][$id]);
                            unset($this->data['originalUserSwitcher'][$id]);
                        }
                    }
                }
                // cleanup orphaned originalUserSwitcher entries whose userSwitchSession
                // counterpart is gone (e.g., left over from before the matched-cleanup fix)
                if(isset($this->data['originalUserSwitcher'])) {
                    foreach($this->data['originalUserSwitcher'] as $id => $name) {
                        if(!isset($this->data['userSwitchSession'][$id])) {
                            unset($this->data['originalUserSwitcher'][$id]);
                        }
                    }
                }

                $pass = new Password();
                $challenge = $pass->randomBase64String(32);
                $session->tracyUserSwitcherId = $challenge;
                // bind this switcher session to the current browser/network fingerprint
                $session->tracyUserSwitcherFp = $session->getFingerprint();

                $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
                $this->data['originalUserSwitcher'][$challenge] = (int) $this->wire('user')->id;
                $this->data['userSwitchSession'][$challenge] = time() + $this->wire('config')->sessionExpireSeconds;
                $configData['originalUserSwitcher'] = $this->data['originalUserSwitcher'];
                $configData['userSwitchSession'] = $this->data['userSwitchSession'];
                $this->wire('modules')->saveModuleConfigData($this, $configData);

                // fall through into the action dispatch below; gate will pass for the active superuser
            }

            // Per-request gate: must hold a valid, non-expired switcher session.
            $switcherId = $session->tracyUserSwitcherId;
            $sessionValid = $switcherId
                && isset($this->data['userSwitchSession'][$switcherId])
                && $this->data['userSwitchSession'][$switcherId] > time();

            // Active superusers bypass the fingerprint check; everyone else must match.
            // Note: don't use isset() on $session->tracyUserSwitcherFp — Session stores values
            // in $_SESSION, but WireData::__isset() checks $this->data, so isset() returns
            // false even when the value is set. Read the value via __get and null-check.
            // Note: if $config->sessionFingerprint is 0, getFingerprint() returns false, both
            // sides cast to "" and hash_equals("", "") is true — the site has explicitly
            // opted out of fingerprinting, so we follow that policy and the gate becomes a no-op.
            $storedFp = $session->tracyUserSwitcherFp;
            $fingerprintOk = static::$allowedSuperuser
                || ($storedFp !== null
                    && hash_equals((string) $storedFp, (string) $session->getFingerprint()));

            if($hasAction && $csrfOk && $sessionValid && $fingerprintOk) {

                // refresh expiry on a valid request when the active user is a superuser
                if(static::$allowedSuperuser) {
                    $this->data['userSwitchSession'][$switcherId] = time() + $this->wire('config')->sessionExpireSeconds;
                    $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
                    $configData['userSwitchSession'] = $this->data['userSwitchSession'];
                    $this->wire('modules')->saveModuleConfigData($this, $configData);
                }

                // logout to guest
                if($this->wire('input')->post->logoutUserSwitcher) {
                    $tracyUserSwitcherId = $switcherId;
                    $tracyUserSwitcherFp = $session->tracyUserSwitcherFp;
                    $session->logout();
                    $session->tracyUserSwitcherId = $tracyUserSwitcherId;
                    $session->tracyUserSwitcherFp = $tracyUserSwitcherFp;
                    $session->redirect($this->httpReferer);
                }
                // end the switcher session entirely
                elseif($this->wire('input')->post->endSessionUserSwitcher) {
                    $session->remove("tracyUserSwitcherId");
                    $session->remove("tracyUserSwitcherFp");
                    $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
                    unset($this->data['userSwitchSession'][$switcherId]);
                    unset($this->data['originalUserSwitcher'][$switcherId]);
                    unset($configData['userSwitchSession'][$switcherId]);
                    unset($configData['originalUserSwitcher'][$switcherId]);
                    $this->wire('modules')->saveModuleConfigData($this, $configData);
                    $session->redirect($this->httpReferer);
                }
                // revert to the original user that started the switcher session
                elseif($this->wire('input')->post->revertOriginalUserSwitcher) {
                    if(isset($this->data['originalUserSwitcher'][$switcherId])) {
                        $tracyUserSwitcherId = $switcherId;
                        $tracyUserSwitcherFp = $session->tracyUserSwitcherFp;
                        // accept either an id (new) or a name (legacy stored value)
                        $originalRef = $this->data['originalUserSwitcher'][$tracyUserSwitcherId];
                        $originalUser = $this->wire('users')->get(is_numeric($originalRef) ? (int) $originalRef : (string) $originalRef);
                        if($originalUser && $originalUser->id) {
                            if($this->wire('user')->isLoggedin()) $session->logout();
                            $session->forceLogin($originalUser);
                            $session->tracyUserSwitcherId = $tracyUserSwitcherId;
                            $session->tracyUserSwitcherFp = $tracyUserSwitcherFp;
                        }
                    }
                    else {
                        // stale state — original-user record missing; tear down silently
                        $session->remove("tracyUserSwitcherId");
                        $session->remove("tracyUserSwitcherFp");
                        $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
                        unset($this->data['userSwitchSession'][$switcherId]);
                        unset($configData['userSwitchSession'][$switcherId]);
                        $this->wire('modules')->saveModuleConfigData($this, $configData);
                        if($this->wire('user')->isLoggedin()) $session->logout();
                    }
                    $session->redirect($this->httpReferer);
                }
                // switch to a user by id — destination must be in the allowed pool,
                // OR equal to the original superuser (so revert works).
                elseif($this->wire('input')->post->userSwitcher) {
                    $rawRequested = (string) $this->wire('input')->post->userSwitcher;
                    $requestedId = is_numeric($rawRequested) ? (int) $rawRequested : 0;
                    $allowedIds = TracyDebugger::getSwitcherSelectableUsers();
                    $originalRef = isset($this->data['originalUserSwitcher'][$switcherId])
                        ? $this->data['originalUserSwitcher'][$switcherId]
                        : null;
                    // resolve legacy name-based originalUserSwitcher entries to an id for comparison
                    $originalId = null;
                    if($originalRef !== null) {
                        if(is_numeric($originalRef)) {
                            $originalId = (int) $originalRef;
                        }
                        else {
                            $originalUser = $this->wire('users')->get((string) $originalRef);
                            if($originalUser && $originalUser->id) $originalId = (int) $originalUser->id;
                        }
                    }

                    $isAllowed = $requestedId > 0
                        && (in_array($requestedId, $allowedIds, true) || $requestedId === $originalId);

                    $requestedUser = $isAllowed ? $this->wire('users')->get($requestedId) : null;

                    if($isAllowed && $requestedUser && $requestedUser->id) {
                        $tracyUserSwitcherId = $switcherId;
                        $tracyUserSwitcherFp = $session->tracyUserSwitcherFp;
                        if($this->wire('user')->isLoggedin()) $session->logout();
                        $session->forceLogin($requestedUser);
                        $session->tracyUserSwitcherId = $tracyUserSwitcherId;
                        $session->tracyUserSwitcherFp = $tracyUserSwitcherFp;
                    }
                    else {
                        // disallowed destination — tear down the switcher session
                        $session->remove("tracyUserSwitcherId");
                        $session->remove("tracyUserSwitcherFp");
                        $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
                        unset($this->data['userSwitchSession'][$switcherId]);
                        unset($this->data['originalUserSwitcher'][$switcherId]);
                        unset($configData['userSwitchSession'][$switcherId]);
                        unset($configData['originalUserSwitcher'][$switcherId]);
                        $this->wire('modules')->saveModuleConfigData($this, $configData);
                        if($this->wire('user')->isLoggedin()) $session->logout();
                    }
                    $session->redirect($this->httpReferer);
                }
            }
            elseif($hasAction && $switcherId) {
                // Action submitted with a switcher id, but the gate failed (CSRF,
                // expired session, or fingerprint mismatch). Tear down silently.
                $session->remove("tracyUserSwitcherId");
                $session->remove("tracyUserSwitcherFp");
                $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
                unset($this->data['userSwitchSession'][$switcherId]);
                unset($this->data['originalUserSwitcher'][$switcherId]);
                unset($configData['userSwitchSession'][$switcherId]);
                unset($configData['originalUserSwitcher'][$switcherId]);
                $this->wire('modules')->saveModuleConfigData($this, $configData);
                if($this->wire('user')->isLoggedin()) $session->logout();
                $session->redirect($this->httpReferer);
            }
        }
