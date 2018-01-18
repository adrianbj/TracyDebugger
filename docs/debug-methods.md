# Debug Methods

The debug methods have three options.

These show an example of each for the barDump method:
* TD::method() `TD::barDump()`
* method() `barDump()`
* shortcut() `bd()`

In most cases using the shortcut is the easiest option, although if you have a conflict with another method you may want to disable the shortcuts via the config settings and use the TD namespaced version.

## addBreakpoint

Method for use with the [Performance Panel](debug-bar.md#performance)

```php
bp($name, $enforceParent);

/**
* @param string $name
* @param bool $enforceParent (default: false)
*/
```

***

## barDump

Most commonly used debug method. It dumps the variable to a dedicated panel on the debug bar.

```php
bd($var, $title, $options);

/**
* @param mixed $var string|array|object to be dumped
* @param string $title string to identify this dump
* @param array $options | maxDepth, maxLength
*/
```

```php
bd($page->body, 'body');
bd(array('a' => array(1,2,3), 'b' => array(4,5,6)), 'test array');
```
Note the second optional parameter used to name the outputs in the Dumps panel. Also see how the array is presented in an expandable tree - very handy when you have a very detailed/complex array or object.

You can also adjust the depth of array/objects, and the lengths of strings like this:

```php
bd($myArr, 'My Array', array('maxDepth' => 7, 'maxLength' => 0));
```
OR
```php
bd($myArr, 'My Array', [7,0]);
```

This can be very handy when you have a deep array or very long string that you need to see more of without changing the defaults of maxDepth:3 and MaxLength:150 which can cause problems with PW objects if you go too high. Setting to '0' means no limit so don't do this on maxDepth when dumping a PW object - it won't be pretty!

***

## barDumpBig

Shortcut to `bd($var, $title, array('maxDepth' => 6, 'maxLength' => 999))`

```php
bdb($var, $title);
```

***

## barDumpLive

Uses Tracy's "Live" dumping method whereby each level of an array or object is added to the DOM in realtime as you click to open the level. The downside to this method is that is displays incorrect information when used inside a hook, so use with caution.

```php
bdl($var, $title);
```

## debugAll

Shortcut for outputting via all the dump/log methods via the one call

```php
da($var, $title, $options);
```


***

## dump

Unlike the barDump methods, this dumps the variable within the DOM of the page where it is called

```php
d($var, $title, $options);
```

***

## fireLog

Dumps to the developer console in Chrome or Firefox

```php
fl($var);
```

This is very useful when using PHP to generate a file for output where the other dump methods won't work. It can also be useful dumping variables during AJAX requests.

To make this work you must first install these browser extensions:

**Chrome:**

[https://github.com/MattSkala/chrome-firelogger](https://github.com/MattSkala/chrome-firelogger)

**Firefox:**

[http://www.getfirebug.com/](http://www.getfirebug.com/)

[http://firelogger.binaryage.com/](http://firelogger.binaryage.com/)

***

## log

Useful if you want to store the results over multiple page requests.

```php
l($var, $priority);

/**
* @param string $var string to be logged
* @param string $priority | "debug", "info", "warning", "error", "exception", "critical" (default: info)
*/
```
The priority setting reflects the name of the file that will be stored in /site/assets/logs/tracy/


***

## templateVars

Extracts just the template variables, removing all ProcessWire variables.

```php
tv(get_defined_vars());
```

***

## timer

Determines time it takes to execute a block of code

```php
t($name)
```

```php
t();
// insert resource intensive code here
sleep(2);
bd(t());
```
You can also add an optional name parameter to each timer() call and then dump several at once in a single page load.

***