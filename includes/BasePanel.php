<?php

use Tracy\IBarPanel;

abstract class BasePanel extends WireData implements IBarPanel {

	function loadResources() {
		$cssUrl = $this->wire('config')->urls->TracyDebugger . 'styles.css';
		return '
		<script>
			function loadCSSIfNotAlreadyLoaded() {
				if(!document.getElementById("tracyStyles")) {
				    var link = document.createElement("link");
				    link.rel = "stylesheet";
				    link.href = "'.$cssUrl.'";
				    document.getElementsByTagName("head")[0].appendChild(link);
				}
			}
			loadCSSIfNotAlreadyLoaded();
		</script>
		';
	}

}