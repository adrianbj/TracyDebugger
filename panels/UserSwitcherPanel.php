<?php namespace ProcessWire;

use Tracy\Debugger;

class UserSwitcherPanel extends BasePanel {

    protected $icon;
    protected $switchedUser = false;

    public function getTab() {

        if(TracyDebugger::isAdditionalBar()) return;
        Debugger::timer('userSwitcher');

        if(TracyDebugger::$allowedSuperuser) {
            $iconColor = TracyDebugger::COLOR_NORMAL;
        }
        elseif($this->wire('user')->isLoggedin()) {
            $iconColor = TracyDebugger::COLOR_WARN;
            $this->switchedUser = true;
        }
        else {
            $iconColor = TracyDebugger::COLOR_ALERT;
        }

        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 612 612" style="enable-background:new 0 0 612 612;" xml:space="preserve" width="16px" height="16px">
            <g>
                <path d="M306.001,325.988c90.563-0.005,123.147-90.682,131.679-165.167C448.188,69.06,404.799,0,306.001,0    c-98.782,0-142.195,69.055-131.679,160.82C182.862,235.304,215.436,325.995,306.001,325.988z" fill="'.$iconColor.'"/>
                <path d="M550.981,541.908c-0.99-28.904-4.377-57.939-9.421-86.393c-6.111-34.469-13.889-85.002-43.983-107.465    c-17.404-12.988-39.941-17.249-59.865-25.081c-9.697-3.81-18.384-7.594-26.537-11.901c-27.518,30.176-63.4,45.962-105.186,45.964    c-41.774,0-77.652-15.786-105.167-45.964c-8.153,4.308-16.84,8.093-26.537,11.901c-19.924,7.832-42.461,12.092-59.863,25.081    c-30.096,22.463-37.873,72.996-43.983,107.465c-5.045,28.454-8.433,57.489-9.422,86.393    c-0.766,22.387,10.288,25.525,29.017,32.284c23.453,8.458,47.666,14.737,72.041,19.884c47.077,9.941,95.603,17.582,143.921,17.924    c48.318-0.343,96.844-7.983,143.921-17.924c24.375-5.145,48.59-11.424,72.041-19.884    C540.694,567.435,551.747,564.297,550.981,541.908z" fill="'.$iconColor.'"/>
            </g>
        </svg>
        ';

        return '
        <span title="User Switcher">
            ' . $this->icon . (TracyDebugger::getDataValue('showPanelLabels') ? $this->wire('user')->name : '') . '
        </span>
        ';
    }



    public function getPanel() {

        $userRoles = array();
        foreach($this->wire('user')->roles as $r) {
            $userRoles[] = '<a href="'.$this->wire('config')->urls->admin.'access/roles/edit/?id='.$r->id.'">'.$r->name.'</a>';
        }

        $out = '
        <h1>' . $this->icon . ' User Switcher</h1>
        <div id="user-switcher-wrapper" class="tracy-inner">
            <h2>' . $this->wire('user')->name . '</h2>
            <p>
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 548.172 548.172" style="enable-background:new 0 0 548.172 548.172;" xml:space="preserve" style="height:16px">
                    <g>
                        <path d="M333.186,376.438c0-1.902-0.668-3.806-1.999-5.708c-10.66-12.758-19.223-23.702-25.697-32.832    c3.997-7.803,7.043-15.037,9.131-21.693l44.255-6.852c1.718-0.194,3.241-1.19,4.572-2.994c1.331-1.816,1.991-3.668,1.991-5.571    v-52.822c0-2.091-0.66-3.949-1.991-5.564s-2.95-2.618-4.853-2.993l-43.4-6.567c-2.098-6.473-5.331-14.281-9.708-23.413    c2.851-4.19,7.139-9.902,12.85-17.131c5.709-7.234,9.713-12.371,11.991-15.417c1.335-1.903,1.999-3.713,1.999-5.424    c0-5.14-13.706-20.367-41.107-45.683c-1.902-1.52-3.901-2.281-6.002-2.281c-2.279,0-4.182,0.659-5.712,1.997L245.815,150.9    c-7.801-3.996-14.939-6.945-21.411-8.854l-6.567-43.68c-0.187-1.903-1.14-3.571-2.853-4.997c-1.714-1.427-3.617-2.142-5.713-2.142    h-53.1c-4.377,0-7.232,2.284-8.564,6.851c-2.286,8.757-4.473,23.416-6.567,43.968c-8.183,2.664-15.511,5.71-21.982,9.136    l-32.832-25.693c-1.903-1.335-3.901-1.997-5.996-1.997c-3.621,0-11.138,5.614-22.557,16.846    c-11.421,11.228-19.229,19.698-23.413,25.409c-1.334,1.525-1.997,3.428-1.997,5.712c0,1.711,0.662,3.614,1.997,5.708    c10.657,12.756,19.221,23.7,25.694,32.832c-3.996,7.808-7.04,15.037-9.132,21.698l-44.255,6.848    c-1.715,0.19-3.236,1.188-4.57,2.993C0.666,243.35,0,245.203,0,247.105v52.819c0,2.095,0.666,3.949,1.997,5.564    c1.334,1.622,2.95,2.525,4.857,2.714l43.396,6.852c2.284,7.23,5.618,15.037,9.995,23.411c-3.046,4.191-7.517,9.999-13.418,17.418    c-5.905,7.427-9.805,12.471-11.707,15.133c-1.332,1.903-1.999,3.717-1.999,5.421c0,5.147,13.706,20.369,41.114,45.687    c1.903,1.519,3.899,2.275,5.996,2.275c2.474,0,4.377-0.66,5.708-1.995l33.689-25.406c7.801,3.997,14.939,6.943,21.413,8.847    l6.567,43.684c0.188,1.902,1.142,3.572,2.853,4.996c1.713,1.427,3.616,2.139,5.711,2.139h53.1c4.38,0,7.233-2.282,8.566-6.851    c2.284-8.949,4.471-23.698,6.567-44.256c7.611-2.275,14.938-5.235,21.982-8.846l32.833,25.693    c1.903,1.335,3.901,1.995,5.996,1.995c3.617,0,11.091-5.66,22.415-16.991c11.32-11.317,19.175-19.842,23.555-25.55    C332.518,380.53,333.186,378.724,333.186,376.438z M234.397,325.626c-14.272,14.27-31.499,21.408-51.673,21.408    c-20.179,0-37.406-7.139-51.678-21.408c-14.274-14.277-21.412-31.505-21.412-51.68c0-20.174,7.138-37.401,21.412-51.675    c14.272-14.275,31.5-21.411,51.678-21.411c20.174,0,37.401,7.135,51.673,21.411c14.277,14.274,21.413,31.501,21.413,51.675    C255.81,294.121,248.675,311.349,234.397,325.626z" fill="'.TracyDebugger::COLOR_NORMAL.'"/>
                        <path d="M505.628,391.29c-2.471-5.517-5.329-10.465-8.562-14.846c9.709-21.512,14.558-34.646,14.558-39.402    c0-0.753-0.373-1.424-1.14-1.995c-22.846-13.322-34.643-19.985-35.405-19.985l-1.711,0.574    c-7.803,7.807-16.563,18.463-26.266,31.977c-3.805-0.379-6.656-0.574-8.559-0.574c-1.909,0-4.76,0.195-8.569,0.574    c-2.655-4-7.61-10.427-14.842-19.273c-7.23-8.846-11.611-13.277-13.134-13.277c-0.38,0-3.234,1.522-8.566,4.575    c-5.328,3.046-10.943,6.276-16.844,9.709c-5.906,3.433-9.229,5.328-9.992,5.711c-0.767,0.568-1.144,1.239-1.144,1.992    c0,4.764,4.853,17.888,14.559,39.402c-3.23,4.381-6.089,9.329-8.562,14.842c-28.363,2.851-42.544,5.805-42.544,8.85v39.968    c0,3.046,14.181,5.996,42.544,8.85c2.279,5.141,5.137,10.089,8.562,14.839c-9.706,21.512-14.559,34.646-14.559,39.402    c0,0.76,0.377,1.431,1.144,1.999c23.216,13.514,35.022,20.27,35.402,20.27c1.522,0,5.903-4.473,13.134-13.419    c7.231-8.948,12.18-15.413,14.842-19.41c3.806,0.373,6.66,0.564,8.569,0.564c1.902,0,4.754-0.191,8.559-0.564    c2.659,3.997,7.611,10.462,14.842,19.41c7.231,8.946,11.608,13.419,13.135,13.419c0.38,0,12.187-6.759,35.405-20.27    c0.767-0.568,1.14-1.235,1.14-1.999c0-4.757-4.855-17.891-14.558-39.402c3.426-4.75,6.279-9.698,8.562-14.839    c28.362-2.854,42.544-5.804,42.544-8.85v-39.968C548.172,397.098,533.99,394.144,505.628,391.29z M464.37,445.962    c-7.128,7.139-15.745,10.715-25.834,10.715c-10.092,0-18.705-3.576-25.837-10.715c-7.139-7.139-10.712-15.748-10.712-25.837    c0-9.894,3.621-18.466,10.855-25.693c7.23-7.231,15.797-10.849,25.693-10.849c9.894,0,18.466,3.614,25.7,10.849    c7.228,7.228,10.849,15.8,10.849,25.693C475.078,430.214,471.512,438.823,464.37,445.962z" fill="'.TracyDebugger::COLOR_NORMAL.'"/>
                        <path d="M505.628,98.931c-2.471-5.52-5.329-10.468-8.562-14.849c9.709-21.505,14.558-34.639,14.558-39.397    c0-0.758-0.373-1.427-1.14-1.999c-22.846-13.323-34.643-19.984-35.405-19.984l-1.711,0.57    c-7.803,7.808-16.563,18.464-26.266,31.977c-3.805-0.378-6.656-0.57-8.559-0.57c-1.909,0-4.76,0.192-8.569,0.57    c-2.655-3.997-7.61-10.42-14.842-19.27c-7.23-8.852-11.611-13.276-13.134-13.276c-0.38,0-3.234,1.521-8.566,4.569    c-5.328,3.049-10.943,6.283-16.844,9.71c-5.906,3.428-9.229,5.33-9.992,5.708c-0.767,0.571-1.144,1.237-1.144,1.999    c0,4.758,4.853,17.893,14.559,39.399c-3.23,4.38-6.089,9.327-8.562,14.847c-28.363,2.853-42.544,5.802-42.544,8.848v39.971    c0,3.044,14.181,5.996,42.544,8.848c2.279,5.137,5.137,10.088,8.562,14.847c-9.706,21.51-14.559,34.639-14.559,39.399    c0,0.757,0.377,1.426,1.144,1.997c23.216,13.513,35.022,20.27,35.402,20.27c1.522,0,5.903-4.471,13.134-13.418    c7.231-8.947,12.18-15.415,14.842-19.414c3.806,0.378,6.66,0.571,8.569,0.571c1.902,0,4.754-0.193,8.559-0.571    c2.659,3.999,7.611,10.466,14.842,19.414c7.231,8.947,11.608,13.418,13.135,13.418c0.38,0,12.187-6.757,35.405-20.27    c0.767-0.571,1.14-1.237,1.14-1.997c0-4.76-4.855-17.889-14.558-39.399c3.426-4.759,6.279-9.707,8.562-14.847    c28.362-2.853,42.544-5.804,42.544-8.848v-39.971C548.172,104.737,533.99,101.787,505.628,98.931z M464.37,153.605    c-7.128,7.139-15.745,10.708-25.834,10.708c-10.092,0-18.705-3.569-25.837-10.708c-7.139-7.135-10.712-15.749-10.712-25.837    c0-9.897,3.621-18.464,10.855-25.697c7.23-7.233,15.797-10.85,25.693-10.85c9.894,0,18.466,3.621,25.7,10.85    c7.228,7.232,10.849,15.8,10.849,25.697C475.078,137.856,471.512,146.47,464.37,153.605z" fill="'.TracyDebugger::COLOR_NORMAL.'"/>
                    </g>
                </svg>&nbsp;<span style="font-size:12px">'. implode(', ', $userRoles) .'</span>
            </p>
            <p>';

            $remainingSessionLength = $this->wire('config')->sessionExpireSeconds;
            if(TracyDebugger::getDataValue('userSwitchSession') != '') {
                $userSwitchSession = TracyDebugger::getDataValue('userSwitchSession');
                $sessionSwitcherId = $this->wire('session')->tracyUserSwitcherId;
                if(isset($userSwitchSession[$sessionSwitcherId])) {
                    $remainingSessionLength = ($userSwitchSession[$sessionSwitcherId] - time()) / 60;
                }
                if($remainingSessionLength <= 0) $remainingSessionLength = 0;
            }

            if(!$this->wire('user')->isLoggedin() && $this->wire('page')->template != 'admin' && $remainingSessionLength <= 0) {
                $out .= '<form action="'.$this->wire('pages')->get($this->wire('config')->loginPageID)->url.'">
                            <input type="submit" value="Login">';
            }
            else {
                $out .= '<form id="userSwitcherPanel" name="userSwitcherPanel" action="'.TracyDebugger::inputUrl(true).'" method="post">';
            }

            if(TracyDebugger::$allowedSuperuser || $remainingSessionLength > 0) {
                $out .= '
                    <select id="userSwitcher" onchange="this.form.submit()" name="userSwitcher" size="5" style="width:100% !important; min-height:105px !important">';
                        if(!$this->wire('user')->isLoggedin()) $out .= '<option value="guest" style="padding: 2px; background:'.TracyDebugger::COLOR_WARN.'; color: #FFFFFF;" selected="selected">guest</option>';

                        if(TracyDebugger::getDataValue('userSwitcherSelector')) {
                            $selectableUsers = $this->wire('users')->find(TracyDebugger::getDataValue('userSwitcherSelector'));
                        }
                        elseif(TracyDebugger::getDataValue('userSwitcherRestricted') && count(TracyDebugger::getDataValue('userSwitcherRestricted')) > 0) {
                            $selectableUsers = $this->wire('users')->find('roles!='.implode(', roles!=', TracyDebugger::getDataValue('userSwitcherRestricted')));
                        }
                        elseif(TracyDebugger::getDataValue('userSwitcherIncluded') && count(TracyDebugger::getDataValue('userSwitcherIncluded')) > 0) {
                            $selectableUsers = $this->wire('users')->find('roles='.implode('|', TracyDebugger::getDataValue('userSwitcherIncluded')));
                        }
                        else {
                            $selectableUsers = $this->wire('users')->find('');
                        }

                        if(count($selectableUsers) > 10) {
                            $tracyModuleUrl = $this->wire('config')->urls->TracyDebugger;
                            $out .= <<< HTML
                                <script>
                                    tracyJSLoader.load("{$tracyModuleUrl}scripts/filterbox/filterbox.js", function() {
                                        tracyJSLoader.load("{$tracyModuleUrl}scripts/user-switcher-search.js");
                                    });
                                </script>
HTML;
                        }

                        foreach($selectableUsers->sort('name') as $u) {
                            if($u->hasStatus('unpublished')) continue;
                            $user_label = str_replace('()', '', wirePopulateStringTags(TracyDebugger::getDataValue('userSwitcherUserLabel'), $u));
                            if(count($u->roles)>1) $out .= '<option id="user_'.$u->id.'" data-label="'.$user_label.'" value="'.$u->name.'" style="padding: 2px; ' . ($this->wire('user')->name === $u->name ? 'background:'.TracyDebugger::COLOR_WARN.'; color: #FFFFFF;" selected="selected"' : '"') . '>'.$user_label.'</option>';
                        }
                $out .= '
                    </select>
                </p>
                <script>
                    (new MutationObserver(() => {
                        document.getElementById("userSwitcher").setAttribute("style","width:100% !important; min-height:105px !important; height:" + (document.getElementById("user-switcher-wrapper").clientHeight - 175) + "px !important");
                    })).observe(document.getElementById("tracy-debug-panel-ProcessWire-UserSwitcherPanel"), { attributes: true, attributeFilter: ["style"] });
                </script>
                ';
            }

            if($this->wire('user')->isLoggedin()) $out .= '<input type="submit" name="logoutUserSwitcher" value="Logout to Guest" />&nbsp;';
            if($this->switchedUser && TracyDebugger::getDataValue('originalUserSwitcher')) $out .= '<input type="submit" name="revertOriginalUserSwitcher" value="Original User" />&nbsp;';
            if($this->switchedUser) $out .= '<input type="submit" name="endSessionUserSwitcher" value="End Session" />';

            $out .= '
                <input type="hidden" id="_post_token" name="' . $this->wire('session')->CSRF->getTokenName() . '" value="' . $this->wire('session')->CSRF->getTokenValue() . '"/>
            </form>';

            if(TracyDebugger::$allowedSuperuser || $remainingSessionLength > 0) {
                $out .= '
                <script>
                    document.addEventListener("DOMContentLoaded", (event) => {
                        var selectElement = document.querySelector("#userSwitcherPanel");
                        var langElement = document.querySelector("#user_'.$this->wire('user')->id.'");
                        selectElement.scrollTop = langElement.offsetTop - selectElement.offsetTop;
                    });
                </script>';
            }

        $out .= TracyDebugger::generatePanelFooter('userSwitcher', Debugger::timer('userSwitcher'), strlen($out), 'userSwitcherPanel');

        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }
}
