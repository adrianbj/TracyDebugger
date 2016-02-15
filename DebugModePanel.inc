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
        <span title='ProcessWire Debug Mode is ON\nMake sure it is OFF for sites in production.'>
            <span style='display:inline-block;background:#FF0000; color:#FFFFFF'>
                &nbsp;<img style='height: 15px' src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAQAAADlauupAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAAmJLR0QAAKqNIzIAAAAJcEhZcwAAAFoAAABaAHAjuH0AAAAHdElNRQfgAg8QEiJJgWBTAAABUUlEQVQ4y41Sy8pBURhd33YGiozJgLHMeQDlMnAZKgNSQpm6ZK54CY9gpmPC2IwncHkETuqUzvoHO6LD76z62u1vf2t9tw18AClCdrvkeq2t0yFF4BV0ZjO6MJ1+JzCRIJXS5GCQvN/dAvc7GQjoeKXIRAIADF3aZgOYJtlogEqB1ernVD6fTjSfA9ksGYmIzppKQVYrYLkEF4t/+5NKBSgUwGxW1Hb70ncm46rasSw6luX2ZzIfBheLuQNrNTq1mtsfiz14yvNqvsAg220gHgdCIc8sGQzIywU4Hg0gnQajUYjf7z1vMgnaNiQcNkTq9cf+IderN4FcTtTt9jYDUZYFnE6/yYeDiCZ/GOJ+/1vANF9vTwGy19MfZLf7Tj6fgWaTLBbf3GS5rP96q0XHMPS52ZClkrb1+vnmTCakbZP5/IvAaET2+153QI7H5HAIAH84Ix6H3KC7VAAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAxNi0wMi0xNVQxNjoxODozNCswMTowMKwM8OEAAAAldEVYdGRhdGU6bW9kaWZ5ADIwMTYtMDItMTVUMTY6MTg6MzQrMDE6MDDdUUhdAAAAMXRFWHRzdmc6YmFzZS11cmkAZmlsZTovLy90bXAvbWFnaWNrLTIxODUzQVpOUWdQdDZkOGhPZudetAAAAABJRU5ErkJggg==' />
                &nbsp;PW Debug Mode ON&nbsp;
            </span>
        </span>
        ";
    }



    public function getPanel() {
        return false;
    }

}
