<?php

use Tracy\IBarPanel;

/**
 * Custom PW panel
 */

class DebugModePanel implements IBarPanel {
    public function getTab() {
        return "
        <style>
            .tracy-panel {
                max-height: 90% !important;
                max-width: 95% !important;
                overflow-y: scroll !important;
            }
        </style>
        <span title='ProcessWire Debug Mode Status\nMake sure it is \"Off\" for sites in production.'>
            <img style='height: 15px' src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABDElEQVQ4T6WT3VVCQQyEv1QgHWgJWIFSgViBUIGWgB1IBWIF0oFaAZQAHVwriGf2ZnFZ98oDOWdfMtnJ38QYMIcn4C7gtcGyFWotp8ML8FhhzwaLOj4ROIwNthn05PprBoeE+U8m6IB3g7nDCBgPdLY16BxWwNRgdKgA+BQJPfifzYB74FZVH5UEbE58zvB1bvloiI3eJ/HjoyQuZ3E+Qez7IjLUa2pWwO86O3NY009edlPNYIjgK+K681soMzrsgMvC16pgb3CVY+oKpIWyjRbBm4G0kKzUgZyvwL6ooib4BjTwuYXgshKnocIEeJ9BTxcp03GtApNSH6RGA11puhytTxNV4EmLa1X84gfSBUwucK12ggAAAABJRU5ErkJggg==' />
            <span style='display:inline-block;background:#FF0000; color:#FFFFFF'>&nbsp;Debug Mode ON&nbsp;</span>
        </span>
        ";
    }



    public function getPanel() {
        return false;
    }

}
