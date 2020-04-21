<?php

$aceKeyboardShortcuts = '
<table>
    <th colspan="2">ACE Editor</th>
    <tr>
        <td><a href="https://github.com/ajaxorg/ace/wiki/Default-Keyboard-Shortcuts" target="_blank">https://github.com/ajaxorg/ace/wiki/Default-Keyboard-Shortcuts</a></td>
    </tr>
</table>
<br />';

if($panel == 'console') {
    $aceKeyboardShortcuts .= '
    <table>
        <th colspan="2">Code Execution</th>
        <tr>
            <td>CTRL/CMD + Enter</td>
            <td>Run</td>
        </tr>
        <tr>
            <td>ALT/OPT + Enter</td>
            <td>Clear & Run</td>
        </tr>
        <tr>
            <td>CTRL/CMD + ALT/OPT + Enter</td>
            <td>Reload from Disk, Clear & Run</td>
        </tr>
    </table>
    <br />
    <table>
        <th colspan="2">Execution History</th>
        <tr>
            <td>ALT + PageUp</td>
            <td>Back</td>
        </tr>
        <tr>
            <td>ALT + PageDown</td>
            <td>Forward</td>
        </tr>
    </table>
    <br />';
}

$aceKeyboardShortcuts .= '
<table>
    <th colspan="2">Screen / Pane Manipulation</th>
    <tr>
        <td>CTRL + SHFT + Enter</td>
        <td>Toggle fullscreen</td>
    </tr>
    <tr>
        <td>CTRL + SHFT + ↑</td>
        <td>Collapse code pane</td>
    </tr>
    <tr>
        <td>CTRL + SHFT + ↓</td>
        <td>Collapse results pane</td>
    </tr>
    <tr>
        <td>CTRL + SHFT + ←</td>
        <td>Restore split to last saved position</td>
    </tr>
    <tr>
        <td>CTRL + SHFT + →</td>
        <td>Split to show all the lines of code</td>
    </tr>
    <tr>
        <td>CTRL + SHFT + PageUp</td>
        <td>One less row in code pane (saves position)</td>
    </tr>
    <tr>
        <td>CTRL + SHFT + PageDown</td>
        <td>One more row in code pane (saves position)</td>
    </tr>
    <tr>
        <td>SHFT + Enter</td>
        <td>Expand to fit all code and add new line (saves position)</td>
    </tr>
    <tr>
        <td>SHFT + Backspace</td>
        <td>Contract to fit all code and remove line (saves position)</td>
    </tr>
</table>
<br />
<table>
    <th colspan="2">Miscellaneous</th>
    <tr>
        <td>CTRL + +</td>
        <td>Font Size Increase</td>
    </tr>
    <tr>
        <td>CTRL + -</td>
        <td>Font Size Decrease</td>
    </tr>
</table>
';