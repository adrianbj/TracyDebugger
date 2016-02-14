Tracy Debugger
================

Processwire module for running the Tracy debugger from Nette (https://tracy.nette.org/).

### About Tracy

Tracy library is a useful helper for everyday PHP programmers. It helps you to:

* quickly detect and correct errors with an expandable call stack tree
* log errors (and optionally receive emails when an error occurs in production mode)
* dump variables
* measure execution time of scripts/queries
* see memory consumption

### Module features

Includes config settings for a variety of Tracy options.

A custom ProcessWire panel in the debug bar provides all the information from the PW admin
debug tools, as well a tree version of the current Page object.

Additionally, content can be dumped to the page via TD::dump() or to the debug bar via TD::barDump(),
or logged via TD::log() from PW template files. eg.

```
TD::barDump($page, 'Current Page');
   Aliases;  barDump(), bd()

TD::dump($page);
   Aliases; dump(), d()

TD::log('Log Message');
    Alias; l()

TD::timer();
    Aliases; timer(), t()
```

By default, manually logged content is sent to: /site/assets/logs/tracy/info.log,
but you can specify an optional second parameter to one of the following:
'debug', 'info', 'warning', 'error', 'exception', 'critical' files.

eg. `TD::log('Log Message', 'debug');` which will put the message in the debug.info file.

##### Alternate Syntax

You can also call the methods directly via Tracy\Debugger, eg.
```
Tracy\Debugger::barDump('Dump Content');
```
###### Advantages
* The location from where the dump() method was called will be correct, rather than referencing a line in this module.
* You can access any built-in Tracy methods, even if they haven't been implemented in this module yet.


###### Disadvantages
* Longer to type `Tracy\Debugger::barDump('Dump Content')` compared with `bd('Dump Content')`


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