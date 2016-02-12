Tracy Debugger
================

Processwire module for running the Tracy debugger from Nette (https://tracy.nette.org/).

### About Tracy

Tracy library is a useful helper for everyday PHP programmers. It helps you to:

* quickly detect and correct errors with an expandable call stack tree
* log errors (and optionally receive emails when an error occurs)
* dump variables
* measure execution time of scripts/queries
* see memory consumption

### Module features

Includes config settings for a variety of Tracy options.

A custom ProcessWire panel in the debug bar provides all the information from the PW admin
debug tools, as well a tree version of the current Page object.

Additionally, content can be dumped to the page via dump() or to the debug bar via barDump() from
PW template files. eg.

```
barDump($page, 'Current Page');
barDump($page->body, 'Body Field');
dump($page);
```

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