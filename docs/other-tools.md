# Other Tools

## Bluescreen
The Tracy Bluescreen shows fatal errors complete with a call stack

## User Bar

This is a simplified version of the Template Path panel that is available from the Debug Bar. It is also similar to the User Dev Template option, but this one allows the user to try multiple options that you provide. Remember you can always have the alternate template files load different js/css etc files as well, so you can provide a very different version of a page for your users to test.

To make it more friendly for your clients/editors, the labels in the list are formatted to look like page names, rather than filenames. The user simply selects an option and the page instantly refreshes showing the page using the alternate version. Even if you have the Page Versions option selected in the config settings, it won't appear on the User Bar unless you have alternately named template files matching this pattern: "home-tracy-alternate-one.php" etc. The key things are the name of the template, plus "-tracy-", plus whatever you want to appear in the list of options, like "alternate-one", plus .php

Users must also have the "tracy-page-versions" permission assigned to their role.