# Tracy Debugger Documentation

## Table of Contents

* [Why Tracy and not Xdebug or some other tool?](#why-tracy-and-not-xdebug-or-some-other-tool)
* [Getting Started](#getting-started)
* [Example Debug Calls](#example-debug-calls)
* [Access Permissions / Restrictions](#access-permissions-restrictions)
* [Debug Bar Panels](#debug-bar-panels)
  * [Captain Hook](#captain-hook)
  * [Console](#console)
  * [Custom PHP](#custom-php)
  * [Debug Mode](#debug-mode)
  * [Diagnostics](#diagnostics)
  * [Dumps](#dumps)
  * [Dumps Recorder](#dumps-recorder)
  * [Errors](#errors)
  * [Event Interceptor](#event-interceptor)
  * [Mail Interceptor](#mail-interceptor)
  * [Methods Info](#methods-info)
  * [Module Disabler](#module-disabler)
  * [Output Mode](#output-mode)
  * [Page Recorder](#page-recorder)
  * [Panel Selector](#panel-selector)
  * [Performance](#performance)
  * [PHP Info](#php-info)
  * [ProcessWire Info](#processwire-info)
  * [ProcessWire Logs](#processwire-logs)
  * [ProcessWire Version](#processwire-version)
  * [Snippet Runner](#snippet-runner)
  * [System Info](#system-info-panel)
  * [Template Editor](#template-editor)
  * [Template Path](#template-path)
  * [Template Resources](#template-resources)
  * [Tracy Logs](#tracy-logs)
  * [Tracy Toggler](#tracy-toggler)
  * [Todo](#todo)
  * [User Switcher](#user-switcher)
  * [Users](#users)
  * [Validator](#validator)
* [Other Information](#other-information)
  * [Editor Protocol Handler](#editor-protocol-handler)
  * [Other Tracy extensions / Custom Panels](other-tracy-extensions-custom-panels)
* [Config Settings](#config-settings)
* [License](#license)

***

## Why Tracy and not Xdebug or some other tool?
Firstly, there is no need to choose one over the other. Tracy actually integrates nicely with Xdebug and there is even an add-on that integrates into the Tracy debug bar and enables you to easily start and stop a Xdebug session. I may include this with the module if there is demand.

While Tracy doesn't provide breakpoints/step through code functionality the way Xdebug does, it does provide a wide variety other features that I think make it a valuable tool, whether you use it in conjunction with Xdebug, or on its own. It is also a lot easier to install, especially if you need to debug something on a server where Xdebug is not supported.

Then there are all the ProcessWire specific panels that come with this module. The TracyDebugger module is much more than just a debugging tool.

***

## Getting Started
You can get started with Tracy by simply installing the module. Immediately you will see the debug bar on the front-end of your site. You can also enable Tracy for the back-end of your site if you desire, which can be useful for module development.

***

## Example Debug Calls
### debugAll()
```
da($var);
```
debugAll is a shortcut for outputting via all the dump/log methods via the one call.

### barDump()
```
bd($page->body, 'body');
bd(array('a' => array(1,2,3), 'b' => array(4,5,6)), 'test array');
```
Note the second optional parameter used to name the outputs in the Dumps panel. Also see how the array is presented in an expandable tree - very handy when you have a very detailed/complex array or object.

You can also adjust the depth of array/objects, and the lengths of strings like this:

```
bd($myArr, 'My Array', array('maxDepth' => 7, 'maxLength' => 0));
```

This can be very handy when you have a deep array or very long string that you need to see more of without changing the defaults of maxDepth:3 and MaxLength:150 which can cause problems with PW objects if you go too high. Setting to '0' means no limit so don't do this on maxDepth when dumping a PW object - it won't be pretty!

### dump()
```
d($page->phone);
```
With dump, the output appears within the context of the web page relative to where you made the dump call. Personally I prefer barDump in most situations.

Tip: Don't forget PW's built-in getIterator() and getArray() methods when dumping objects - it can clean things up considerably by removing all the PW specific information. eg:
```
d($page->phone->getArray());
```

### timer()
```
t();

// insert resource intensive code here
sleep(2);
bd(t());
```
You can also add an optional name parameter to each timer() call and then dump several at once in a single page load. Note the total execution time of the entire page in the debug bar - so we can assume the rest of the page took about 274 ms.


### fireLog()
```
fl('PW Page Object');
fl($page);
```
This dumps to the developer console in Chrome or Firefox.

This is very useful when using PHP to generate a file for output where the other dump methods won't work. It can also be useful dumping variables during AJAX requests.

To make this work you must first install these browser extensions:

*Chrome:*

[https://github.com/MattSkala/chrome-firelogger](https://github.com/MattSkala/chrome-firelogger)

*Firefox:*

[http://www.getfirebug.com/](http://www.getfirebug.com/)

[http://firelogger.binaryage.com/](http://firelogger.binaryage.com/)

### log()
```
l('Message to debug.log file', 'debug');
l($page);
```
log() is useful if you want to store the results over multiple page requests.

***

## Access Permissions / Restrictions
This might seem a little complicated at first, but hopefully with a little explanation it will be clearer :)

Even if it still seems a little confusing, don't worry - the default settings will keep live error reporting and the Tracy debug bar out of the hands of everyone on a live website.

### Detect Mode
If Tracy determines that the site is on a local IP address, then it will function in Development Mode and so everyone, including guests, have access to live error reporting and the debug bar.

If Tracy determines that the site is on a live server, then it will function in Production Mode and so all guests and users will NOT have access to live error reporting and the debug bar. The exception here is for superusers if you have checked the "Superuser Force Development Mode" option.

### Production Mode
Tracy is forced into Production Mode and so all guests and users will NOT have access to live error reporting and the debug bar. Errors will be logged to file and emailed if you have that configured. Again, the exception is for superusers if you have checked the "Superuser Force Development Mode" option.

### Development Mode
If this case you have forced into Development Mode even though you may be on a live server. In this case she behaves a little differently. By default, guests and non-superusers will function as though Tracy was in Production Mode. So if you want to debug something on a live server as a guest or non-superuser, then you need to give their role the "tracy-debugger" permission. This permission is not available automatically, so create it manually and add it to the required user's role. You can even do this with the guest role if you want. The way to make this safe on a live server is to use the "Restrict Non-superusers" option in the config settings. Here you can specify an IP address (exact or regex match) so that it is restricted to your computer.

***

## Debug Bar Panels
The module has a variety of custom panels, most of which can be disabled / reordered via the module's config settings. You can also enable panels "Once" or "Sticky" via the [Panel Selector](#panel-selector).

### Captain Hook
Generates a list of hookable methods from your ProcessWire install, including site modules.

If your editor protocol handler is setup, clicking on the line number will open to the method in your code editor. You can copy and paste the formatted hook directly from the first column.

Results are cached, but will be updated whenever you update your ProcessWire version or install a new module.

***

### Console
Here are the key points/features of this panel:

* Uses the excellent ACE Editor for code highlighting and syntax checking
* Has full access to all PW variables: $pages, $page, $user, etc
* Has access to all template defined variables and functions, although checking this option will load these files so if they contain API write actions, be careful.
* Works with fl(), bd(), d(), and l() calls, or even simply echo()
* All output is generated via ajax, and so is virtually instant
* No need for editing a template file and reloading the page in browser - simply type your code in the console panel for an instant result
* Only available to superusers
* Caches code in the filesystem so it stored between page reloads, navigation to other pages, and even browser restarts
* Use CTRL+Enter or CMD+Enter or the "Run Code" button to run the code
* Remember that this allows you to run any PHP code, so be careful!

![Console example](https://github.com/adrianbj/TracyDebugger/raw/master/docs/images/console-1.png "Console Example")

***

### Custom PHP
This panel lets you output anything you want. Primarily I see this being used for creating links to things like GTmetrix, but use your imagination.

***

### Debug Mode
Provides access to all the information that is available in the back-end "Debug Mode Tools" section of your PW admin. This panel makes it available on the front-end and even when Debug Mode is off. Note that with Debug Mode Off, you won't have access to the "Database Queries", "Timers", and "Autload" sections. This is a ProcessWire core restriction.

The icon color on the debug bar is red when debug mode is on and green when it is off. This may seem opposite, but the idea is that when the icon is green, it is OK for production mode, whereas red indicates something that you need to be alerted to. This is particularly useful if you have the "Superuser Force Development Mode" option enabled because you will see the debug bar even in Production mode.

***

### Diagnostics
#### Filesystem
Overview of the filesystem access permissions for all the key folders and files in your PW install. It also provides status and notes about these. These should not be taken as definitive (especially if you are on a Windows system), but rather as a guide and reminder to check these. The debug bar icon for this panel is colored to match the most serious status level - OK, Warning, or Failure. This is particularly useful if you have the "Superuser Force Development Mode" option enabled because you will see the debug bar even in Production mode.

#### MySQL Info
Some basic details about your MySQL server and client setup.

***

### Dumps
This panel is only displayed when you have called the barDump() method and contains the contents of that dump. No need for an additional screenshot here - you have seen examples in the tutorial above.

***

### Dumps Recorder
If this panel is enabled, any calls to bd() will be sent to this panel instead of the main dumps panel. This is useful in several situations where you want to compare dumps from various page requests. Dumps will be preserved until the session is closed, or until you click the "Clear Dumps" button. It can also be useful in some situations where dumps are not being captured with the regular dumps panel which can sometimes happen with modules, complex redirects, or other scenarios that are hard to pin down.

***

### Errors
The errors panel is only displayed when there are non-fatal errors and you are not in Strict Mode. All PHP notices and warnings will be displayed in this panel.

***

### Event Interceptor
This panel lets you define any Hook that you want to intercept. Much like the Mail panel, this new panel prevents the chosen hook from being executed, but instead, returns that contents of $event->object and $event->arguments in the panel instead. This may be useful for debugging all sorts of things but you MUST USE EXTREME CAUTION - for example, setting Pages::save and then deleting a page can result in some pages ending up having the trashed status, but their parent still listed as the home page. This is a situation that requires some careful database manipulation to fix.

*Icon colors*

* Green - no hook set
* Orange - hook set, but nothing intercepted
* Red - hook set and event intercepted

***

### Mail Interceptor
Intercepts all outgoing emails sent using `wireMail()` and displays them in the panel. Ideal for form submission testing. This panel is activated when enabled, so it's best to enable it from the Panel Selector using the sticky option when needed.

***

### Methods Info
Lists all the available logging methods that you can call within your PHP code.

***

### Module Disabler
This panel makes use of the ProcessWire core "disabled" flag for disabling autoload modules for testing / debugging purposes. It can potentially result in a fatal error on your site (this is a ProcessWire core issue, rather than specific to this panel). Because of this, it is only available when ProcessWire's advanced and debug modes are enabled.

If you do end up with a fatal error after disabling a module, this panel provides a script for automatically restoring the modules database table. Whenever you disable any modules, a backup of the "modules" database table is automatically saved.

To restore you have two choices:

Copy "/site/assets/cache/TracyDebugger/restoremodules.php" to the root of your site and load it in your browser

OR

Execute "/site/assets/cache/TracyDebugger/modulesBackup.sql" manually (via PHPMyAdmin, the command line, etc)

***

### Output Mode
Indicates which mode Tracy is in - Development or Production - this is determined at runtime so if you have configured it to "Detect" mode, you can easily see which mode it has automatically switched to. This is useful if you have the "Superuser Force Development Mode" option enabled because you will see the debug bar even in Production mode.

***


### Page Recorder
This panel records the ID of all pages added whenever it is enabled (so this is one you'll want off by default and just enabled via "Sticky" when you need it).

This is perfect for all sorts of testing, whether you need to create a LOT of pages for performance testing or you are testing a form which is automatically creating pages. Once you are done with the testing session, simply click the "Trash Recorded Pages" button and they will all be moved to the Trash.

***

### Panel Selector
Allows you to set up a default set of panels in the module config settings and then easily enable / disable other panels from the debugger bar. There are three options: Once, Sticky, and Reset. "Once" will only make the selection change for one page load. "Sticky" will keep the changes for the browser session. "Reset" will return to the default set defined in the module config settings.

There are icons indicating if:

the panel is set in the default list in the module config settings ("Tick" icon),
the state of the panel is different for the current view, ie you have made a "Once" change (number "1" icon)

In the following example, you can see that:

Debug Mode is disabled. Default is enabled (checkbox icon). There is no "1" icon so it is a Sticky setting.
ProcesswireInfo is disabled. Default is enabled (checkbox icon), There is a "1" icon so you know it's a "Once" only setting.
Validator is enabled. Default is disabled (no checkbox icon). There is a "1" icon so you know it's a "Once" only setting.

***

### Performance
Performance Panel is a third party extension for Tracy developed by Martin Jirásek. It adds support for inserting named breakpoints in your code and reports execution time and various memory usages stats between the various breakpoints. This is where calls to addBreakpoint() are rendered.

***

### PHP Info
Provides all the output from PHP's `phpinfo()`. Probable best to leave disabled unless you need to check something.

***

### Processwire Info
Provides a wide variety of links, information and search features for all things ProcessWire.

***

### ProcessWire Logs
Displays the most recent entries across all ProcessWire log files with links to view the log in the PW logs viewer, as well as direct links to view each entry in your code editor. By default it shows the last 10, but this can be changed in the config settings. A red icon indicates the last page load contained an errors or exceptions log entry. An orange icon is for all other log types.

***

### ProcessWire Version
Lets you instantly switch your PW version. This is probably most useful for module developers, but can also be helpful for other users to help debug PW core or module problems. It's probably obvious, but the switcher is not recommended for live sites, so don't blame me if a version change breaks your site (especially between the 2.x and 3.x branch)!

The available versions come from Ryan's ProcessWire Upgrades module - so any version that you installed via it will be available.

When you click "Change", it swaps the names of: wire/, .htaccess, and index.php - much easier than manually renaming.

The icon is green when you are using the latest version that is available on your system, and orange for any other version.

***

### Snippet Runner
This is similar to the Console Panel, but instead lets you run snippets stored on the server's filesystem which allows for easier version control, and also for editing snippets in your code editor.

***

### System Info
Provides a table of basic stats about the current page and your system.

***

### Template Editor
This is an alternative to the Template Path panel. It allows you to test changes without affecting other users currently viewing the site.

*Icon colors*

* Red: Test code is being rendered.
* Green: Saved template file is being rendered.

Note that there are three buttons:

* Test: This will reload the page using the code in the editor - no changes are made to the template file or the code served to all other users of the site.

* Push Live: This will save the editor code to the template file, making this a live and permanent change.

* Reset: This will reload the page (and the code in the editor) with the code from the saved template file.

*Possible use scenarios*

* Use this panel similarly to your dev console for tweaking CSS/HTML - it still requires a page reload, but there are likely less clicks than your normal workflow.
* Use it to tweak a live site if you're away from your computer and need a quick way to fix something, but want the ability to test first without breaking something temporarily due to a simple syntax error mistake or more serious code mistakes.
* Use it to add debug statements: `fl()`, `bd()`, `d()` etc to your template file code without ever touching the actual template files.

***

### Template Path
The template path panel allows you to temporarily choose an alternate template file for rendering the current page. It provides a list of files in the site/templates folder that match the name of the default template file, but with a "-suffix" extension. You can have several different versions and quickly test each one. You can make the change last for the browser session (sticky), or just for one reload (once). You can reset to the default template file for the current page, or all changes you may have made to other pages/template files on the site.

Not only is this useful for debugging (especially on a live production server), but it could also be used for sharing different versions of a page among trusted users.

*Icon colors*

* Red: The current page is using a different template file.
* Orange: The current page is using it's default template file, but there are other pages on the site that are using a different template file (obviously via the Sticky option). Use "Reset All" to clear this when you're done testing.
* Green: All pages on the site are using their default template files.

#### User Dev Template Option
This is not reliant on the Template Path Panel, but its functionality is similar and its status is integrated into the panel, so it is presented here.

It makes it really easy to show authorized users development versions of template files. To make this work, all you need to do is enable the checkbox. Then setup a "template-****" permission and assign that to the required users.

Obviously this is not the best approach for major changes (you should set up a dev subdomain for that), but I think it could be quite handy for certain site changes.

In this screenshot, you can see the reminder detailing why the icon is orange. Currently we are not viewing a page with an alternate template, but it is letting us know that:

* the "User Dev Template" option is enabled in module settings
* the "template-dev" permission exists
* the permission has been assigned to at least one user
* there are template files with a "-dev" suffix

So if this is expected then great, but if not, then you can prevent the alternate templates from being rendered by doing one or more of the following:

* disabling the "User Dev Template" option
* removing the template-dev permission from the system
* remove the template-dev permission from all roles
* delete alternate template files with the "-dev" suffix

If you are on a page that is using an alternate template due to user permissions, then you will see the PW permission cog icon:

***

### Template Resources
Displays the names, types, and values of all variables defined in the template file (and any other included files) for the current page. It also shows any defined constants and functions (linked to open in your code editor), as well as a list of included files (also linked to open in your code editor).

***

### Tracy Logs
Displays the most recent entries from the Tracy log files. These log files can be written to automatically when Tracy is in Production mode, or manually using `TD::log()` or `l()` calls. Includes direct links to view each entry in your code editor. By default it shows the last 10, but this can be changed in the config settings. A red icon indicates the last page load contained an error, exception, or critical log entry. An orange icon is for all other log types.

***

### Tracy Toggler
Not really a panel, but this button on the debug bar lets you toggle Tracy on / off without needing to visit the module config settings. If you don't want another button always taking up room, you can also use the "Disable Tracy" button on the Panel Selector. Another alternative is the Hide/Show toggle icon at the far right of the debug bar. This doesn't actually turn Tracy off, but it get the debug out of the way.

***

### Todo
The ToDo Panel report the following comment types: 'todo', 'fixme', 'pending', 'xxx', 'hack', 'bug'. See the config settings for determining which folders and files will be scanned.

If you have your editor configured, the comment text link opens the file to the line of the comment.

The icon reports the number of items in the template file for the current file / the total number of items across all files.

*Icon colors*

* Red: there are items for the current page's template file.
* Orange: there are items in other files, but none in the current page's template file.
* Green: no items in any files under /site/templates/

***

### User Switcher
Allows you to instantly switch to any user in the system without knowing their password. After switching, you will still have full access to the Tracy debug bar, which can be very useful for debugging issues with other users and even guest (not logged in) visitors.

* It is completely disabled unless Tracy is specifically set to Development mode. Detect mode won't work and even the "Superuser Force Development Mode" option won't enable it so you can be sure your live sites are safe unless you specifically enable Development Mode for a debugging session.
* You need to be a superuser to have access to the panel until a session is started, so even when Development mode is enabled, other users still won't be able to use it.
* It only works for the duration of user switcher session (max 60 minutes) and only a superuser can start a session.
* Starting the session makes use of PW's CSRF protection.
* The switcher session has a unique ID and expiry time which is stored in the database and must match the ID in the user's session.
* Once the session has expired, it is no longer possible to switch users. You can manually end the session, or if you forget it will expire automatically based on the session length you set.

As usual, icon colors are meaningful, telling you what type of user is currently logged in:

*Icon colors*

* Green: superuser
* Orange: non-superuser
* Red: guest / logged out

***

### Users
Lists all the users/roles with access to the Tracy Debugger bar. A green debug bar icon indicates that only superusers can access the debug bar. A red icon indicates that others have the tracy-debugger permission and may be able to see the debug bar. Another good reason to have the "Superuser Force Development Mode" option enabled because you will see this warning even in Production mode.

***

### Validator
Validates the HTML of the page using the validator.nu service. This works with local development sites as well as live sites.

***

## Other Information
### Editor Protocol Handler
All links from Tracy error messages and the custom ProcessWire Info Panel can be set up to open the file in your favorite code editor directly to the line of the error. By default the module is configured to work with SublimeText (my editor of choice), but it can be configured to work with pretty much any editor out there. Instructions for these are currently littered throughout the module's support thread, so do a search for your editor in there and if you can't find it, please feel free to add instructions yourself. So far, we have it confirmed as working in Sublime, Eclipse, PHPStorm, and UltraEdit.

In addition to configuring this module, you will most likely also need to register an appropriate protocol handler on your computer. If you happen to be on a Mac and use SublimeText, [this one](https://github.com/saetia/sublime-url-protocol-mac) works perfectly, but a quick Google should find alternatives that suit your platform and code editor.

The Tracy website also has [additional useful information](https://pla.nette.org/en/how-open-files-in-ide-from-debugger) about this.

In addition to the protocol handler setting, there is also a "Local Root Path" option which converts remote links to your local dev files.

### Other Tracy extensions / Custom Panels
Tracy is easily extendable allowing you to add custom panels to the debug bar and extensions to the "bluescreen". You can choose from a [directory of third party plugins](https://componette.com/search/tracy), or [create your own](https://tracy.nette.org/en/extensions). If you come across others that you think should be included, or you create one yourself that you'd like to contribute, please let me know.

***

## Config Settings
There are a lot of settings for this module but hopefully the defaults will suit most users. I won't bore you with all the options here, but I would suggest that you take a thorough look as there are many possibilities that you might find useful. I would definitely recommend unchecking "Show Panel Labels" once you are familiar with things. I also like to use the "Superuser Force Development Mode" option. You'll also want to configure your email address for Production mode logging and the Editor Protocol Handler so that you have direct edit links to your script files.