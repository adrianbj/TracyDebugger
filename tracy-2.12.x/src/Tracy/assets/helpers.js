
function init() {
	// fixes DNA-106639 "Close tabs created for opening external apps" since Opera 99
	if (navigator.userAgent.includes('OPR/')) {
		document.addEventListener(
			'click',
			(e) => {
				e.target.closest('a[href^="editor:"]')?.setAttribute('target', '_blank');
			},
			true,
		);
	}
}


let Tracy = window.Tracy = window.Tracy || {};

// TracyDebugger patch: Trusted Types (nette/tracy#624): passes Tracy's own markup and scripts to sinks that a
// page enforcing Trusted Types (require-trusted-types-for 'script') refuses plain strings at
Tracy.trusted = Tracy.trusted || (() => {
	let policy;
	try {
		policy = window.trustedTypes?.createPolicy('tracy', {
			createHTML: (s) => s,
			createScript: (s) => s,
			createScriptURL: (s) => s,
		});
	} catch {
		// the page's trusted-types directive doesn't allow the name
	}
	return {
		html: (s) => policy ? policy.createHTML(s) : s,
		script: (s) => policy ? policy.createScript(s) : s,
		scriptURL: (s) => policy ? policy.createScriptURL(s) : s,
	};
})();

if (!Tracy.helpers) {
	init();
	Tracy.helpers = true;
}
