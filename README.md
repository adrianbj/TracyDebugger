Tracy Debugger
================

[Processwire](https://processwire.com) module for running the [Tracy debugger from Nette](https://tracy.nette.org/).

#### Blog Post Documentation

https://processwire.com/blog/posts/introducing-tracy-debugger/

#### Support forum

https://processwire.com/talk/topic/12208-tracy-debugger/

### About Tracy

Tracy library is a useful helper for everyday PHP programmers. It helps you to:

* quickly detect and correct errors with an expandable call stack tree
* log errors (and optionally receive emails when an error occurs in production mode)
* dump variables
* measure execution time of scripts/queries
* see memory consumption between breakpoints

### Module features

Includes config settings for a variety of Tracy options.

A custom ProcessWire panel in the debug bar provides all the information from the PW admin
debug tools, as well a tree version of the current Page object.

Additionally, content can be dumped to the page via TD::dump() or to the debug bar via TD::barDump(),
or logged via TD::log() from PW template files. eg.

```
TD::debugAll($page, 'Current Page');
   Aliases;  debugAll(), da()

TD::barDump($page, 'Current Page');
   Aliases;  barDump(), bd()

TD::dump($page);
   Aliases; dump(), d()

TD::log('Log Message');
    Alias; l()

TD::fireLog('Log Message');
    Alias; fireLog(), fl()

TD::addBreakpoint('Name');
    Alias; addBreakpoint(), bp()

TD::timer();
    Aliases; timer(), t()
```

By default, manually logged content is sent to: /site/assets/logs/tracy/info.log,
but you can specify an optional second parameter to one of the following:
'debug', 'info', 'warning', 'error', 'exception', 'critical' files.

eg. `TD::log('Log Message', 'debug');` which will put the message in the debug.log file.


#### FireLog

To make fireLog work, you need to add some browser extensions:

Chrome:
* https://github.com/MattSkala/chrome-firelogger

Firefox:
* http://www.getfirebug.com/
* http://firelogger.binaryage.com/


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