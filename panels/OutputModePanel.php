<?php

class OutputModePanel extends BasePanel {

    protected $outputMode;
    protected $developmentIcon;
    protected $productionIcon;

    protected $productionColor = \TracyDebugger::COLOR_NORMAL;
    protected $developmentColor = \TracyDebugger::COLOR_WARN;

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('outputMode');

        $this->outputMode = \TracyDebugger::getDataValue('outputMode');

        if($this->outputMode == 'detect') {
            $this->outputMode = \TracyDebugger::$isLocal ? 'development' : 'production';
        }

        $iconColor = $this->outputMode == 'development' ? $this->developmentColor : $this->productionColor;

        $this->developmentIcon = '
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 16.539 16.539" style="enable-background:new 0 0 16.539 16.539;" xml:space="preserve" width="16px" height="16px">
            <g>
                <path d="M15.436,0.68H1.104C0.495,0.68,0,1.177,0,1.791V11.97c0,0.614,0.495,1.111,1.104,1.111h5.498    c0,0-0.965,2.223-3.612,2.223v0.556h1.945h4.724h3.612v-0.556c-2.72,0-3.335-2.223-3.335-2.223h5.501    c0.608,0,1.102-0.497,1.102-1.111V1.791C16.537,1.177,16.044,0.68,15.436,0.68z M15.356,11.28H1.182V1.861h14.174V11.28z" fill="'.$iconColor.'"/>
                <polygon points="7.155,8.41 4.503,6.565 4.503,6.542 7.155,4.697 7.155,3.468 3.649,6.055 3.649,7.053     7.155,9.64   " fill="'.$iconColor.'"/>
                <polygon points="12.989,6.02 9.483,3.468 9.483,4.697 12.194,6.542 12.194,6.565 9.483,8.41 9.483,9.64     12.989,7.087   " fill="'.$iconColor.'"/>
            </g>
        </svg>
        ';

        $this->productionIcon = '
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 412.997 412.997" style="enable-background:new 0 0 412.997 412.997;" xml:space="preserve">
            <g>
                <path d="M392.997,28.338H20c-11.046,0-20,8.954-20,20v234c0,11.046,8.954,20,20,20h139.499v45.32h-54.283     c-10.201,0-18.5,8.299-18.5,18.5s8.299,18.5,18.5,18.5h202.566c10.201,0,18.5-8.299,18.5-18.5s-8.299-18.5-18.5-18.5h-54.283     v-45.32h139.498c11.047,0,20-8.954,20-20v-234C412.997,37.292,404.044,28.338,392.997,28.338z M382.997,272.338H30v-214h352.997     V272.338L382.997,272.338z" fill="'.$iconColor.'"/>
                <path d="M62.999,146.276h287c2.762,0,5-2.239,5-5V86.269c0-2.761-2.238-5-5-5h-287c-2.762,0-5,2.239-5,5v55.008     C57.999,144.038,60.237,146.276,62.999,146.276z" fill="'.$iconColor.'"/>
                <path d="M349.999,165.164H285.28c-2.19,0-3.966,1.776-3.966,3.966v76.333c0,2.19,1.773,3.966,3.966,3.966h64.719     c2.19,0,3.966-1.774,3.966-3.966v-76.333C353.965,166.938,352.189,165.164,349.999,165.164z" fill="'.$iconColor.'"/>
                <path d="M251.78,165.164H62.999c-2.19,0-3.967,1.776-3.967,3.966v76.333c0,2.19,1.776,3.966,3.967,3.966H251.78     c2.189,0,3.965-1.774,3.965-3.966v-76.333C255.745,166.938,253.971,165.164,251.78,165.164z M242.391,239.444h-170v-64.296h170     V239.444z" fill="'.$iconColor.'"/>
            </g>
        </svg>
        ';

        $label = \TracyDebugger::getDataValue('showPanelLabels') ? ucfirst($this->outputMode) : '';

        return '
        <span title="Output Mode: '.ucfirst($this->outputMode).'">
            ' . $this->{$this->outputMode.'Icon'} . $label . '
        </span>
        ';
    }


    public function getPanel() {

        $out = '<h1>' . str_replace('#FFFFFF', $this->{$this->outputMode.'Color'}, $this->{$this->outputMode.'Icon'}) . ' '.ucfirst($this->outputMode).' Mode</h1>
        <div class="tracy-inner">';

            if(\TracyDebugger::getDataValue('outputMode') == 'detect') {
                $out .= '<p>This is automatically switched from the configured "DETECT" mode.</p>';
            }
            elseif($this->outputMode == 'development') {
                $out .= '<p>This is forced development mode.</p><p>Use caution when forcing development mode on a live site.</p><p>Remember to limit any authorized non-superusers by IP Address.</p>';
            }
            elseif($this->outputMode == 'production') {
                $out .= '<p>This mode is safe for live sites.</p><p>Only superusers will have access to the debugger tools.</p>';
            }

            $out .= '</p>';

            $out .= \TracyDebugger::generatePanelFooter('outputMode', \Tracy\Debugger::timer('outputMode'), strlen($out));

        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}
