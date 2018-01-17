# Tips

## Example Debug Calls
### debugAll()
```php
da($var);
```
debugAll is a shortcut for outputting via all the dump/log methods via the one call.

### barDump()
```php
bd($page->body, 'body');
bd(array('a' => array(1,2,3), 'b' => array(4,5,6)), 'test array');
```
Note the second optional parameter used to name the outputs in the Dumps panel. Also see how the array is presented in an expandable tree - very handy when you have a very detailed/complex array or object.

You can also adjust the depth of array/objects, and the lengths of strings like this:

```php
bd($myArr, 'My Array', array('maxDepth' => 7, 'maxLength' => 0));
```

This can be very handy when you have a deep array or very long string that you need to see more of without changing the defaults of maxDepth:3 and MaxLength:150 which can cause problems with PW objects if you go too high. Setting to '0' means no limit so don't do this on maxDepth when dumping a PW object - it won't be pretty!

### dump()
```php
d($page->phone);
```
With dump, the output appears within the context of the web page relative to where you made the dump call. Personally I prefer barDump in most situations.

Tip: Don't forget PW's built-in getIterator() and getArray() methods when dumping objects - it can clean things up considerably by removing all the PW specific information. eg:
```php
d($page->phone->getArray());
```

### timer()
```php
t();

// insert resource intensive code here
sleep(2);
bd(t());
```
You can also add an optional name parameter to each timer() call and then dump several at once in a single page load. Note the total execution time of the entire page in the debug bar - so we can assume the rest of the page took about 274 ms.


### fireLog()
```php
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
```php
l('Message to debug.log file', 'debug');
l($page);
```
log() is useful if you want to store the results over multiple page requests.

***
