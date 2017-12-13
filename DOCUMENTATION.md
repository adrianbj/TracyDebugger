# Tracy Debugger Documentation

## Table of Contents

* [Why Tracy and not Xdebug or some other tool?](#why-tracy-and-not-xdebug-or-some-other-tool)
* [Getting Started](#getting-started)
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

## Why Tracy and not Xdebug or some other tool?
Firstly, there is no need to choose one over the other. Tracy actually integrates nicely with Xdebug and there is even an add-on that integrates into the Tracy debug bar and enables you to easily start and stop a Xdebug session. I may include this with the module if there is demand.

While Tracy doesn't provide breakpoints/step through code functionality the way Xdebug does, it does provide a wide variety other features that I think make it a valuable tool, whether you use it in conjunction with Xdebug, or on its own. It is also a lot easier to install, especially if you need to debug something on a server where Xdebug is not supported.

Then there are all the ProcessWire specific panels that come with this module. The TracyDebugger module is much more than just a debugging tool.

***

## Getting Started
You can get started with Tracy by simply installing the module. Immediately you will see the debug bar on the front-end of your site. You can also enable Tracy for the back-end of your site if you desire, which can be useful for module development.

***

## Panels

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

***
