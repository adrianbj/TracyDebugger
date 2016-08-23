<?php

use Tracy\IBarPanel;
use Tracy\Dumper;

/**
 * Custom PW panel
 */

class TemplateResourcesPanel implements IBarPanel {

    protected $sectionEnd;

    public function getTab() {
        if(\TracyDebugger::additionalBar()) return;
        \Tracy\Debugger::timer('templateResources');

        // end for each section
        $this->sectionEnd = '
                    </tbody>
                </table>
            </div>';

        return '
        <span title="Template Resources">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 492 492" style="enable-background:new 0 0 492 492;" xml:space="preserve" width="16px" height="16px">
                <path d="M370.5,254c-14.667-11.333-36.667-20.667-66-28l-23-5v-97c0.667,0.667,1.333,1,2,1s1,0.333,1,1    c19.334,9.333,29.668,25.333,31,48h77c-1.333-40-16.333-70.667-45-92c-18.667-14-40.667-23.333-66-28V0h-71v52    c-33.333,4-59.667,14.667-79,32c-25.333,22.667-38,51.334-38,86c0,37.333,13,65,39,83c14,10,40,19.333,78,28v104    c-14-4-24.667-10.333-32-19c-8-9.333-13-22-15-38h-76c0,39.333,14.333,70.333,43,93c20.667,16,47.333,26.333,80,31v40h71v-39    c34.667-4.667,62.333-15.667,83-33c26.667-23.333,40-52.667,40-88C404.5,298,393.167,272,370.5,254z M210.5,204    c-11.333-3.333-19.333-6.333-24-9c-12.667-6.667-19-17-19-31c0-15.333,6.333-27,19-35c6.667-4,14.667-7.333,24-10V204z M293.5,383    c-3.333,1.333-7.333,2.333-12,3v-89c12.667,4,22.333,8,29,12c11.333,7.333,17,17.333,17,30C327.5,360.333,316.167,375,293.5,383z" fill="#444444"/>
            </svg>' . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Template Resources' : '') . '
        </span>
        ';
    }


    protected function sectionHeader($columnNames = array()) {
        $out = '
        <div>
            <table>
                <thead>
                    <tr>';
        foreach($columnNames as $columnName) {
            $out .= '<th>'.$columnName.'</th>';
        }

        $out .= '
                    </tr>
                </thead>
            <tbody>
        ';
        return $out;
    }


    protected function formatVariables($vars, $type) {
        $out = $this->sectionHeader(array('Name', 'Returns', 'Class', 'Value'));
        foreach($vars as $var => $value) {

            if(is_object($var) || is_array($var)) {
                $varArray = array();
                foreach($var as $key => $value) {
                    $varArray[$key] = $value;
                }
                $value = $varArray;
            }

            if(!\TracyDebugger::getDataValue('variablesShowPwObjects') && is_object($value)) {
                $outValue = method_exists($value,'getArray') ? $value->getArray() : $value;
                // run getValue() on as many levels as the Max Nesting Depth config setting
                for($i=0;$i<=\TracyDebugger::getDataValue('maxDepth');$i++) {
                    if(is_array($outValue)) {
                        array_walk_recursive($outValue, function (&$val) {
                            $val = is_object($val) && method_exists($val,'getArray') ? $val->getArray() : $val;
                        });
                    }
                }
            }
            else {
                $outValue = $value;
            }

            // if it's an object or multidimensional array, then collapse it
            $outValue = Dumper::toHtml($outValue, array(Dumper::COLLAPSE => (count($outValue) !== count($outValue, COUNT_RECURSIVE) || is_object($outValue) ? true : false)));

            $out .= "\n<tr>" .
                '<td>'.($type == 'var' ? '$' : '').$var.'</td>' .
                "<td>".gettype($value)."</td>" .
                "<td>".(gettype($value) == "object" ? get_class($value) : "")."</td>" .
                "<td>".$outValue."</td>" .
                "</tr>";
        }
        $out .= $this->sectionEnd;
        return $out;
    }


    protected function get_defined_functions_in_file($file) {
        $source = file_get_contents($file);
        $tokens = token_get_all($source);

        $functions = array();
        $nextStringIsFunc = false;
        $inClass = false;
        $bracesCount = 0;

        foreach($tokens as $token) {
            switch($token[0]) {
                case T_CLASS:
                    $inClass = true;
                    break;
                case T_FUNCTION:
                    if(!$inClass) $nextStringIsFunc = true;
                    break;

                case T_STRING:
                    if($nextStringIsFunc) {
                        $nextStringIsFunc = false;
                        $functions[$token[1]]['file'] = $file;
                        $functions[$token[1]]['line'] = $token[2];
                    }
                    break;

                // Anonymous functions
                case '(':
                case ';':
                    $nextStringIsFunc = false;
                    break;

                // Exclude Classes
                case '{':
                    if($inClass) $bracesCount++;
                    break;

                case '}':
                    if($inClass) {
                        $bracesCount--;
                        if($bracesCount === 0) $inClass = false;
                    }
                    break;
            }
        }
        return $functions;
    }


    public function getPanel() {

        // panel title
        $out = '
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 492 492" style="enable-background:new 0 0 492 492;" xml:space="preserve" width="16px" height="16px">
                <path d="M370.5,254c-14.667-11.333-36.667-20.667-66-28l-23-5v-97c0.667,0.667,1.333,1,2,1s1,0.333,1,1    c19.334,9.333,29.668,25.333,31,48h77c-1.333-40-16.333-70.667-45-92c-18.667-14-40.667-23.333-66-28V0h-71v52    c-33.333,4-59.667,14.667-79,32c-25.333,22.667-38,51.334-38,86c0,37.333,13,65,39,83c14,10,40,19.333,78,28v104    c-14-4-24.667-10.333-32-19c-8-9.333-13-22-15-38h-76c0,39.333,14.333,70.333,43,93c20.667,16,47.333,26.333,80,31v40h71v-39    c34.667-4.667,62.333-15.667,83-33c26.667-23.333,40-52.667,40-88C404.5,298,393.167,272,370.5,254z M210.5,204    c-11.333-3.333-19.333-6.333-24-9c-12.667-6.667-19-17-19-31c0-15.333,6.333-27,19-35c6.667-4,14.667-7.333,24-10V204z M293.5,383    c-3.333,1.333-7.333,2.333-12,3v-89c12.667,4,22.333,8,29,12c11.333,7.333,17,17.333,17,30C327.5,360.333,316.167,375,293.5,383z" fill="#444444"/>
            </svg>
            Template Resources
        </h1>
        <div class="tracy-inner">
            <p>These are all the non-PW resources that are available in the template for this page.</p>
            <p>If you are looking for the fields and their values for this page, look in the ProcessWire Info panel under "Fields List & Values"</p>
            ';

        // Variables
        $out .= '<h3>Variables</h3>';
        if(count(\TracyDebugger::$templateVars) > 0) {
            $out .= $this->formatVariables(\TracyDebugger::$templateVars, 'var');
        }
        else {
            $out .= 'There are no defined template file variables.';
        }

        // Constants
        $out .= '<h3>Constants</h3>';
        if(count(\TracyDebugger::$templateConsts) > 0) {
            $out .= $this->formatVariables(\TracyDebugger::$templateConsts, 'const');
        }
        else {
            $out .= 'There are no defined template constants.';
        }

        // Included Files
        $functions = array();
        $includedFilesOut = '<h3>Included Files</h3>';
        if(count(\TracyDebugger::$includedFiles) > 0) {
            $includedFilesOut .= $this->sectionHeader(array('Path'));
            foreach(\TracyDebugger::$includedFiles as $key => $path) {
                $path = str_replace(wire('config')->paths->cache . 'FileCompiler/', wire('config')->paths->root, $path);
                $functions[] = $this->get_defined_functions_in_file($path);
                $fileEditorPath = str_replace('%file', $path, str_replace('%line', '1', \TracyDebugger::getDataValue('editor')));
                if(\TracyDebugger::getDataValue('localRootPath') != '') $fileEditorPath = str_replace(wire('config')->paths->root, \TracyDebugger::getDataValue('localRootPath'), $fileEditorPath);

                $includedFilesOut .= "\n<tr>" .
                    '<td><a title="Edit File" href="'.$fileEditorPath.'">'.str_replace(wire('config')->paths->root, '/', $path).'</a></td>' .
                    "</tr>";
            }
            $includedFilesOut .= $this->sectionEnd;
        }
        else {
            $includedFilesOut .= 'There are no included files.';
        }

        // Functions
        $out .= '<h3>Functions</h3>';
        if(count(\TracyDebugger::$templateFuncs) > 0) {
            $out .= $this->sectionHeader(array('Name', 'File', 'Line'));
            $funcNames = array();
            foreach($functions as $func) {
                foreach($func as $name => $details) {
                    $funcNames[] = strtolower($name);
                    if(in_array(strtolower($name), array_map('strtolower', array_values(\TracyDebugger::$templateFuncs)))) {
                        if(isset($details['file'])) {
                            $path = str_replace(wire('config')->paths->cache . 'FileCompiler/', wire('config')->paths->root, $details['file']);
                            $fileEditorPath = str_replace('%file', $path, str_replace('%line', $details['line'], \TracyDebugger::getDataValue('editor')));
                            if(\TracyDebugger::getDataValue('localRootPath') != '') $fileEditorPath = str_replace(wire('config')->paths->root, \TracyDebugger::getDataValue('localRootPath'), $fileEditorPath);
                            $out .= "\n<tr>" .
                                '<td><a href="'.$fileEditorPath.'">'.$name.'</a></td>' .
                                '<td>'.str_replace(wire('config')->paths->root, '/', $path).'</td>' .
                                '<td>'.$details['line'].'</td>' .
                                "</tr>";
                        }
                    }
                }
            }
            foreach(\TracyDebugger::$templateFuncs as $key => $name) {
                if(!in_array($name, $funcNames)) {
                    $out .= "\n<tr>" .
                        '<td>'.$name.'</td>' .
                        "</tr>";
                }
            }
            $out .= $this->sectionEnd;
        }
        else {
            $out .= 'There are no defined template file functions.';
        }

        $out .= $includedFilesOut;

        \TracyDebugger::$panelGenerationTime['templateResources']['time'] = \Tracy\Debugger::timer('templateResources');
        \TracyDebugger::$panelGenerationTime['templateResources']['size'] = strlen($out);
        $out .= \TracyDebugger::generatedTimeSize(\TracyDebugger::$panelGenerationTime['templateResources']['time'], \TracyDebugger::$panelGenerationTime['templateResources']['size']);

        $out .= '</div>';

        return $out;
    }

}
