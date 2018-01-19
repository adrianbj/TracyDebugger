# Configuration

## Main Setup

#### Enable Tracy Debugger

* Checked by default
* If disabled, a few tiny dummy classes will be loaded instead to ensure debug calls, eg `bd()` etc don't cause errors.

#### Output Mode

* DETECT
* DEVELOPMENT
* PRODUCTION

#### Allow Logging in Production Mode

* When Tracy is in PRODUCTION mode, log notices, warnings, and errors to file.

***

## Access Permissions

#### Force Superusers into Development Mode

#### Force Guest Users into Development Mode on Localhost

#### Restrict Non-superusers

***

## Miscellaneous

#### Strict Mode

#### Strict Mode AJAX Only

#### Force Scream

#### Show Locations

#### Log Severity

#### Maximum Nesting Depth

#### Maxiumum String Length

#### Email for Production Errors

#### Clear Email Sent Flag

#### Reference Page Being Edited

***

## Debug Bar and Panels

#### Show Debug Bar

#### No Debug Bar in ...

#### No Debug Bar in Selected Templates

#### Hide Debug Bar

***

## Editor Protocol Handler
All links from Tracy error messages and the custom ProcessWire Info Panel can be set up to open the file in your favorite code editor directly to the line of the error. By default the module is configured to work with SublimeText (my editor of choice), but it can be configured to work with pretty much any editor out there. Instructions for these are currently littered throughout the module's support thread, so do a search for your editor in there and if you can't find it, please feel free to add instructions yourself. So far, we have it confirmed as working in Sublime, Eclipse, PHPStorm, and UltraEdit.

In addition to configuring this module, you will most likely also need to register an appropriate protocol handler on your computer. If you happen to be on a Mac and use SublimeText, [this one](https://github.com/saetia/sublime-url-protocol-mac) works perfectly, but a quick Google should find alternatives that suit your platform and code editor.

The Tracy website also has [additional useful information](https://pla.nette.org/en/how-open-files-in-ide-from-debugger) about this.

In addition to the protocol handler setting, there is also a "Local Root Path" option which converts remote links to your local dev files.

