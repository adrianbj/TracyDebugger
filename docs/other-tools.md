# Other Tools

## Bluescreen
The Tracy Bluescreen shows fatal errors complete with a call stack.

This is a real life example - in fact it is the reason I learned about Tracy. @tpr posted about a PHP Notice he was getting with my BatchChildEditor module - all he initially provided was:

PHP Notice: Trying to get property of non-object ... in Page.php:780
Not very helpful is it :) We know it's in the PW core Page.php file, but we don't know what is triggering it and I couldn't replicate it. Then he sent me the HTML of the Tracy debug output, which looked like the following (note - the call stack is truncated for brevity here):

![Bluescreen panel](img/bluescreen.png)

From this I could quickly see a call from line #1095 of BatchChildEditor.module, so I expanded that and could see the exact line. From there it only took a second to realize that my check for is_array() was insufficient because it needed to check that $ptod was a valid PW page object.

> TIP: because the initial error is actually just a PHP Notice, Tracy needs to be put into Strict mode to see this full callstack. You can do this easily by clicking the "Enable Strict Mode" button on the Panel Selector.



***

## User Bar

> The User Bar allows you to give regular users (not guests), access to a bar with Admin, edit, and other custom links.

![User Bar](img/user-bar.png)

See the [configuration options](configuration.md#user-bar) for more details on the setup.

### Page Versions

The "Page versions" feature  allows an authorized user to select alternate versions of a page (different template files). This is a simplified version of the Debug Bar's Template Path panel that is available from the . It is also similar to the User Dev Template option, but this one allows the user to try multiple options that you provide. Remember you can always have the alternate template files load different js/css etc files as well, so you can provide a very different version of a page for your users to test.

![Page Versions](img/page-versions.png)

To make it more friendly for your clients/editors, the labels in the list are formatted to look like page names, rather than filenames. The user simply selects an option and the page instantly refreshes showing the page using the alternate version. Even if you have the Page Versions option selected in the config settings, it won't appear on the User Bar unless you have alternately named template files matching this pattern: "home-tracy-alternate-one.php" etc. The key things are the name of the template, plus "-tracy-", plus whatever you want to appear in the list of options, like "alternate-one", plus .php

Users must also have the "tracy-page-versions" permission assigned to their role.

***