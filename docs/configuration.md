# Configuration

## Main setup

##### Enable Tracy Debugger

```
@default: enabled
```
* If disabled, a few tiny dummy classes will be loaded instead to ensure any debug calls, eg `bd()` etc don't cause errors.

##### Output mode

```
@options: DETECT | DEVELOPMENT | PRODUCTION
@default: DETECT
```
**DETECT**
* automatically switches to DEVELOPMEMT or PRODUCTION if server is local or live (by IP address)

**DEVELOPMENT**
* enables the debug bar
* shows the Tracy [BlueScreen](other-tools.md#bluescreen) for fatal errors

**PRODUCTION**
* disables debug bar
* all notices/warnings are hidden from view, but logged to file
* optionally send an email whenever an error is logged
* fatal errors are displayed like this:

![Production mode server error](img/production-mode-server-error.png)

***

## Access permissions

##### Force superusers into development mode

* Check to force DEVELOPMENT mode for superusers even on live sites.

* By default, the Output Mode setting's DETECT option will force a site into PRODUCTION mode when it is live, which hides the DebugBar and sends errors and dumps to log files. However, with this checked, superusers will always be in DEVELOPMENT mode.

##### Force guest users into development mode on localhost

* Check to force DEVELOPMENT mode for guests when server detected as localhost.

* By default, guest users will always be in PRODUCTION mode (no debug bar). However, with this checked, they will always be in DEVELOPMENT mode on localhost.

##### Restrict non-superusers

* IP Address that non-superusers need to use TracyDebugger. Enter IP address or a PCRE regular expression to match IP address of user, eg. /^123\.456\.789\./ would match all IP addresses that started with 123.456.789.

* Non-superusers are already blocked unless they have the "tracy-debugger" permission. But once a user has been given the permission, this option restricts access to the listed IP address. Highly recommended for debugging live sites that you have manually set into DEVELOPMENT mode.

***

## Miscellaneous

##### Strict mode

* Check to enable strict mode which displays notices and warnings like errors.

##### Strict mode AJAX only

* Check to enable strict mode only for AJAX calls.

* Because Tracy intercepts notices and warnings, these will no longer be returned with the AJAX response which may result in a "success" reponse, rather than "failure". Notices and warnings from an AJAX call will be displayed in the AJAX bar's Errors panel, but you still might prefer this option as it provides a more prominent indication of failure.

##### Force scream

* Check to force "scream" of mode which disables the @ (silence/shut-up) operator so that notices and warnings are no longer hidden.

* This is disabled when Strict Mode is enabled because of a [bug](https://forum.nette.org/en/25569-strict-and-scream-modes-together)? in the core Tracy package.

##### Show locations

```
@options: LOCATION_SOURCE | LOCATION_LINK | LOCATION_CLASS
@default: all enabled
```

* Shows the location of dump() and barDump() calls.

* LOCATION_SOURCE adds tooltip with path to the file, where the function was called.

* LOCATION_LINK adds a link to the file which can be opened directly.

* LOCATION_CLASS adds a tooltip to every dumped object containing path to the file, in which the object's class is defined.

![Dumps panel showing link to bd() call](img/dumps-panel-location-links.png)

##### Maximum nesting depth

```
@default: 3
```

* Set the maximum nesting depth of dumped arrays and objects using `dump()` and `barDump()`

* Warning: making this too large can slow your page load down or even crash your browser.

* Rather than adjusting this, consider using `barDumpBig()`, setting the `maxDepth` and `maxLength` in the options for the call, or using `barDumpLive()`

##### Maxiumum string length

```
@default: 150
```

* Set the maximum displayed strings length using `dump()` and `barDump()`

* Rather than adjusting this, consider using `barDumpBig()`, setting the `maxDepth` and `maxLength` in the options for the call, or using `barDumpLive()`

##### Reference page being edited

```
@default: enabled
```

* When editing a page in the admin, the Request Info Panel will show details of the page being edited, and the Consol Panel will assign the $page variable to the page being edited.

* Highly recommended unless you have a reason not to do this.

***

## Error Logging

##### Log severity

```
@options: E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_CORE_ERROR | E_CORE_WARNING |
E_COMPILE_ERROR | E_COMPILE_WARNING | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE |
E_STRICT | E_RECOVERABLE_ERROR | E_DEPRECATED | E_USER_DEPRECATED | E_ALL
@default: none
```

* Determined which PHP errors Tracy will log

* These only affect log file content, not onscreen debug info

##### Email for production errors

* Receive emails at this address when an error occurs in production mode.

* Leave blank to log to file (/site/assets/logs/tracy/) without being notified

##### Clear email sent flag

* Check and save settings to remove the "email-sent" file so that you will start receiving new error emails.

* Once an email has been sent, further errors will not be emailed until you do this. This prevents flooding of your email inbox.

***

## Debug bar and panels

##### Show debug bar

```
@options: Frontend | Backend
@default: both
```

##### No debug bar in ...

```
@options: Regular modal | Inline modal | Overlay panels | Form Builder iframe
@default: Regular modal | Inline modal | Overlay panels
```

##### No debug bar in selected templates

* Disable the debug bar on pages with the selected templates.

##### Hide debug bar by default

* Hide the debug bar by default on page load
* This results in the bar being hidden (unless an error is reported), and replaced with a small "show bar" ⇱ icon.

##### Show panel labels

* Adds labels to each panel icon in the debug bar
* May take up too much horizontal space if you have lots of panels enabled, or on smaller screens

```
@default: false
```

![Debug Bar with labels](img/debug-bar-with-labels.png)

##### Starting zIndex for panels

```
@default: 100
```

* Adjust if you find panels are below/above elements that you don't want.

##### Frontend panels

* Determines which panels are shown in the Debug Bar on the frontend. Sort to match order of panels in Debugger Bar.

##### Backend panels

* Determines which panels are shown in the Debug Bar on the backend. Sort to match order of panels in Debugger Bar.

* Note that some panels are intentionally missing from this list because they have no use in the backend.

##### Disabled panels for restricted users

* Check the panels that should NOT be shown to users with the "tracy-restricted-panels" role or permission.

* Unchecked panels will still only be shown to restricted users if they are selected in the Front/Back-end options above.

***

## Editor links

##### Editor protocol handler

```
@default: subl://open/?url=file://%file&line=%line
```

* All links from Tracy error messages and the custom ProcessWire Info Panel can be set up to open the file in your favorite code editor directly to the line of the error. By default the module is configured to work with SublimeText (my editor of choice), but it can be configured to work with pretty much any editor out there. Instructions for these are currently littered throughout the module's support thread, so do a search for your editor in there and if you can't find it, please feel free to add instructions yourself. So far, we have it confirmed as working in Sublime, Eclipse, PHPStorm, and UltraEdit.

* In addition to configuring this module, you will most likely also need to register an appropriate protocol handler on your computer. If you happen to be on a Mac and use SublimeText, [this one](https://github.com/saetia/sublime-url-protocol-mac) works perfectly, but a quick Google should find alternatives that suit your platform and code editor.

* The Tracy website also has [additional useful information](https://pla.nette.org/en/how-open-files-in-ide-from-debugger) about this.

* This approach only works for OSX. For more instructions on Windows and Linux alternatives, [read here](https://pla.nette.org/en/how-open-files-in-ide-from-debugger).

##### Local root path

* Maps editor links from live site to local dev files. Only used if you are viewing a live site, otherwise it is ignored.

* An example path on MacOS might be: /Users/myname/Sites/sitefolder/

##### Use online editor for links

* This will open links in an online editor instead of your code editor.

##### Online editor

```
@options: Tracy File Editor | ProcessFileEdit
@default: Tracy File Editor
```

* Which online editor to use: Tracy File Editor or [ProcessFileEdit](http://modules.processwire.com/modules/process-file-edit/).

##### Force editor links to use Tracy File Editor

```
@default: enabled
```

* Even if neither of the "Use Online Editor for Links" options are checked, if the File Editor Panel is enabled, all links will be sent to it.

* RECOMMENDED: This is a handy option if you generally want links to use your code editor, but want the option to use the File Editor occasionally.
You can enable it (once or sticky) from the Panel selector on the debug bar without the need to change the above settings.

***

## File Editor panel

##### Base directory

```
@options: Root | Site | Templates
@default: Templates
```

* A more specific selection results in better performance in the File Editor Panel.

##### Allowed extensions

```
@default: php, module, js, css, htaccess, latte
```

* List of extensions that can be opened in the editor. Fewer extensions results in better performance in the File Editor panel.

***

## ProcessWire Info panel

##### Panel sections

##### Custom links

##### Show icon labels

##### Open links in new tab

***

## Request Info panel

##### Panel sections

***

## Debug Mode panel

##### Panel sections

##### Show debug mode tools even if `$config->debug = false;`

***

## Diagnostics panel

##### Panel sections

***

## TODO panel

##### Ignore directories

##### Allowed extensions

##### Scan site modules

***

## ProcessWire and Tracy Log panels

##### Number of log entries

***

## Template Resources panel

##### Show content of ProcessWire objects

***

## Snippet Runner panel

##### Snippets path

***

## Custom PHP panel

##### Custom PHP code

***

## Server type indicator

##### Where

##### Indicator colors

##### Indicator type

***

## User dev template

##### Enable user dev template

##### User dev template suffix

***

## User bar

##### Show user bar

##### Show user bar for Tracy users

##### Features

##### Custom features

##### Top / Bottom

##### Left / Righy

##### Background color

##### Background opacity

##### Icon color

***

## Method shortcuts

##### Enable method shortcuts

##### Enabled shortcuts

