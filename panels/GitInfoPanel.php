<?php

/**
 * Tracy panel showing current branch and some more informations, inspired by Vojtěch Vondra - https://gist.github.com/vvondra/3645108
 * @package Nofutur3\Extensions
 *
 * @author Jakub Vyvážil (https://github.com/nofutur3/tracy-gitpanel)
 *
 * Modified by Adrian Jones to work with ProcessWire TracyDebugger implementation
 *
 */

class GitInfoPanel extends BasePanel
{

    private $currentBranch;
    private $branchName;


    /**
     * Renders HTML code for custom tab.
     * @return string
     */
    function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('gitInfo');

        $this->branchName = $this->getBranchName();

        $this->icon = '<svg viewBox="10 10 512 512"><path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M 502.34111,278.80364 278.79809,502.34216 c -12.86794,12.87712 -33.74784,12.87712 -46.63305,0 l -46.4152,-46.42448 58.88028,-58.88364 c 13.68647,4.62092 29.3794,1.51948 40.28378,-9.38732 10.97012,-10.9748 14.04307,-26.80288 9.30465,-40.537 l 56.75401,-56.74844 c 13.73383,4.73404 29.56829,1.67384 40.53842,-9.31156 15.32297,-15.3188 15.32297,-40.15196 0,-55.48356 -15.3341,-15.3322 -40.16175,-15.3322 -55.50254,0 -11.52454,11.53592 -14.37572,28.47172 -8.53182,42.6722 l -52.93386,52.93048 0,-139.28512 c 3.73267,-1.84996 7.25863,-4.31392 10.37114,-7.41756 15.32295,-15.3216 15.32295,-40.15196 0,-55.49696 -15.32296,-15.3166 -40.16844,-15.3166 -55.48025,0 -15.32296,15.345 -15.32296,40.17536 0,55.49696 3.78727,3.78288 8.17299,6.64472 12.85234,8.5604 l 0,140.57336 c -4.67935,1.91568 -9.05448,4.75356 -12.85234,8.56264 -11.60533,11.60168 -14.39801,28.6378 -8.4449,42.89232 L 162.93981,433.11336 9.6557406,279.83948 c -12.8743209,-12.88768 -12.8743209,-33.768 0,-46.64456 L 233.20978,9.65592 c 12.87017,-12.87456 33.74338,-12.87456 46.63305,0 l 222.49828,222.50316 c 12.87852,12.87876 12.87852,33.76968 0,46.64456"/></svg>';
        return '<span title="Git Info: '.$this->branchName.'">' . $this->icon . ' ' . (\TracyDebugger::getDataValue('showPanelLabels') ? $this->branchName : '') . '</span>';
    }

    /**
     * Renders HTML code for custom panel.
     * @return string
     */
    function getPanel() {
        if($this->isUnderVersionControl()) {
            $out = '<h1>' . $this->icon . ' Git Info</h1>';
            $warning = '';
            $cntTable = '';

            // commit message
            if($this->getLastCommitMessage() != null) {
                $cntTable .= '<tr><td>Last commit</td><td> ' . $this->getLastCommitMessage() . ' </td></tr>';
            }

            // heads
            if($this->getHeads() != null) {
                $cntTable .= '<tr><td>Branches</td><td> ' . $this->getHeads() . ' </td></tr>';
            }

            // remotes
            if($this->getRemotes() != null) {
                $cntTable .= '<tr><td>Remotes</td><td> ' . $this->getRemotes() . ' </td></tr>';
            }

            // tags
            if($this->getTags() != null) {
                $cntTable .= '<tr><td>Tags</td><td> ' . $this->getTags() . ' </td></tr>';
            }

            $out .= '<div class="tracy-inner tracy-InfoPanel"><table><tbody>' .
                $cntTable .
                '</tbody></table>';

            $out .= \TracyDebugger::generatePanelFooter('gitInfo', \Tracy\Debugger::timer('gitInfo'), strlen($out)) . '</div>';

            return parent::loadResources() . $out;
        }
    }

    protected function getBranchName() {
        $dir = $this->getDirectory();

        $head = $dir . '/.git/HEAD';
        if($dir && is_readable($head)) {
            $branch = file_get_contents($head);
            if(strpos($branch, 'ref:') === 0) {
                $parts = explode('/', $branch);
                return substr($parts[2], 0, -1);
            }
            return '(' . substr($branch, 0, 7) . '&hellip;)';
        }

        return 'not versioned';
    }

    protected function getLastCommitMessage() {
        $dir = $this->getDirectory();

        $fileMessage = $dir . '/.git/COMMIT_EDITMSG';

        if($dir && is_readable($fileMessage)) {
            $message = file_get_contents($fileMessage);
            return $message;
        }

        return null;
    }

    protected function getHeads() {
        $dir = $this->getDirectory();

        $files = scandir($dir . '/.git/refs/heads');
        $message = '';

        if($dir && is_array($files)) {
            foreach($files as $file) {
                if($file != '.' && $file != '..') {
                    if($file == $this->branchName) {
                        $message .= '<strong>' . $file . ' </strong>';
                    } else {
                        $message .= $file . ' <br>';
                    }
                }
            }
            return $message;
        }

        return null;
    }

    protected function getRemotes() {
        $dir = $this->getDirectory();

        try {
            $files = scandir($dir . '/.git/refs/remotes');
        } catch (\ErrorException $e) {
            return null;
        }

        $message = '';

        if($dir && is_array($files)) {
            foreach($files as $file) {
                if($file != '.' && $file != '..') {
                    $message .= $file . ' ';
                }
            }
            return $message;
        }

        return null;

    }

    protected function getTags() {
        $dir = $this->getDirectory();

        $files = scandir($dir . '/.git/refs/tags');
        $message = '';

        if($dir && is_array($files)) {
            foreach($files as $file) {
                if($file != '.' && $file != '..') {
                    $message .= $file . ' ';
                }
            }
            return $message;
        }

        return null;

    }

    private function getDirectory() {
        $scriptPath = $_SERVER['SCRIPT_FILENAME'];

        $dir = realpath(dirname($scriptPath));
        while ($dir !== false && !is_dir($dir . '/.git')) {
            flush();
            $currentDir = $dir;
            $dir .= '/..';
            $dir = realpath($dir);

            // Stop recursion to parent on root directory
            if($dir == $currentDir) {
                break;
            }
        }

        return $dir;
    }

    private function isUnderVersionControl() {
        $dir = $this->getDirectory();
        $head = $dir . '/.git/HEAD';

        if($dir && is_readable($head)) {
            return true;
        }
        return false;
    }
}