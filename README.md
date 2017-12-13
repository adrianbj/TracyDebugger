# Tracy Debugger

Tracy Debugger is a module for the ProcessWire CMF/CMS for integrating Nette's awesome Tracy debugging tool.

#### About Tracy

For more information about the core Tracy project from Nette, please visit: https://tracy.nette.org/

#### Support forum

For support for this module, please visit this thread on the ProcessWire forum: https://processwire.com/talk/topic/12208-tracy-debugger/

***

## Table of Contents

* [Panels](#panels)
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
* [License](#license)

***

## Panels

### Captain Hook

Generates a list of hookable methods from your ProcessWire install, including site modules.

If your editor protocol handler is setup, clicking on the line number will open to the method in your code editor. You can copy and paste the formatted hook directly from the first column.

Results are cached, but will be updated whenever you update your ProcessWire version or install a new module.

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


### Custom PHP

***

## License

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

(See included LICENSE file for full license text.)
