<?php namespace ProcessWire;

use Tracy\IBarPanel;
use Tracy\Debugger;

abstract class BasePanel extends WireData implements IBarPanel {

    /**
     * Build the standard tab span for the debug bar.
     *
     * @param string $title The tooltip title
     * @param string|null $label The visible label (defaults to $title)
     * @param string $badge Optional HTML badge (e.g. count span)
     * @return string
     */
    protected function buildTab($title, $label = null, $badge = '') {
        if($label === null) $label = $title;
        return '<span title="' . $title . '">' . $this->icon
            . (TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;' . $label : '')
            . $badge . '</span>';
    }

    /**
     * Build the panel header (h1 + optional resize button).
     *
     * @param string $title The panel title text
     * @param bool $showResize Whether to include the maximize/restore button
     * @param bool $showAdditionalBar Whether to append the additional bar identifier
     * @return string
     */
    protected function buildPanelHeader($title, $showResize = false, $showAdditionalBar = false) {
        $isAdditionalBar = $showAdditionalBar ? TracyDebugger::isAdditionalBar() : false;
        $out = '<h1>' . $this->icon . ' ' . $title
            . ($isAdditionalBar ? ' (' . $isAdditionalBar . ')' : '') . '</h1>';
        if($showResize) {
            $className = (new \ReflectionClass($this))->getShortName();
            $out .= '<span class="tracy-icons"><span class="resizeIcons">'
                . '<a href="#" title="Maximize / Restore" data-tracy-resize="'
                . $className . ($isAdditionalBar ? '-' . $isAdditionalBar : '')
                . '">⛶</a></span></span>';
        }
        return $out;
    }

    /**
     * Open the panel inner div.
     *
     * @param string $extraClass Additional CSS class(es)
     * @param string $style Inline style string
     * @return string
     */
    protected function openPanel($extraClass = '', $style = '') {
        return '<div class="tracy-inner' . ($extraClass ? ' ' . $extraClass : '') . '"'
            . ($style ? ' style="' . $style . '"' : '') . '>';
    }

    /**
     * Close the panel with footer and closing div.
     *
     * @param string $out The accumulated panel HTML (used for size calculation)
     * @param string $panelName The panel identifier for timer/footer
     * @param string|null $settingsFieldsetId Optional settings fieldset link ID
     * @return string The complete panel HTML ready to return from getPanel()
     */
    protected function closePanel($out, $panelName, $settingsFieldsetId = null) {
        $out .= TracyDebugger::generatePanelFooter($panelName, Debugger::timer($panelName), strlen($out), $settingsFieldsetId);
        $out .= '</div>';
        return $out;
    }

    /**
     * Generate a CSRF hidden input field.
     *
     * @return string
     */
    protected function csrfInput() {
        return '<input type="hidden" name="' . $this->wire('session')->CSRF->getTokenName()
            . '" value="' . $this->wire('session')->CSRF->getTokenValue() . '" />';
    }

    /**
     * Get the reference page (edited page in admin, or current page on frontend).
     *
     * @param array|null $processTypes Process class names to check (defaults to common set)
     * @return Page
     */
    protected function getReferencePage($processTypes = null) {
        if($processTypes === null) {
            $processTypes = array('ProcessPageEdit', 'ProcessUser', 'ProcessRole', 'ProcessPermission', 'ProcessLanguage');
        }
        if(TracyDebugger::getDataValue('referencePageEdited')
            && $this->wire('input')->get('id')
            && in_array((string)$this->wire('process'), $processTypes)
        ) {
            $p = $this->wire('process')->getPage();
            if(!$p || $p instanceof NullPage) {
                $p = $this->wire('pages')->get((int) $this->wire('input')->get('id'));
            }
            if(!$p || $p instanceof NullPage) {
                return $this->wire('page');
            }
            return $p;
        }
        return $this->wire('page');
    }

    /**
     * Build a section header with a table and column headings.
     *
     * @param array $columnNames Column heading labels
     * @param string $thStyle Optional inline style for th elements
     * @return string
     */
    protected function sectionEnd() {
        return '</tbody></table></div>';
    }

    protected function sectionHeader($columnNames = array()) {
        $out = '<div><table><thead><tr>';
        foreach($columnNames as $columnName) {
            $out .= '<th>' . $columnName . '</th>';
        }
        $out .= '</tr></thead><tbody>';
        return $out;
    }

    /**
     * Strip the site root path from an absolute path.
     *
     * @param string $path The absolute file path
     * @param string $prefix What to replace the root with (default '/')
     * @return string
     */
    protected function stripRootPath($path, $prefix = '/') {
        return TracyDebugger::stripRootPath($path, $prefix);
    }

}
