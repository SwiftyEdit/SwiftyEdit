---
title: Templates
description: List of the Template files
btn: Templates
group: developer
priority: 200
---

# Templates files (.tpl)

Some templates are included directly from SwiftyEdit.
So these templates should be in the folder `styles/yourtheme/templates/`.
If a file is missing in your theme or you intentionally omit it,
SwiftyEdit loads the file from the "default" theme.

## The most important template files

* __index.tpl__<br>
Is the first template that is loaded. From here you can link all your other template files.
* __404.tpl__<br>
Will be displayed at HTTP status code 404 (page not found).
* __registerform.tpl__<br>
Contains the form to register as a user.
* __profile_main.tpl__<br>
Here users can update their profile.
* __status_message.tpl__<br>
Here system messages are displayed
* __statusbox.tpl__<br>
Here the links to the ACP (for administrators) or to the profile are displayed.
* __loginbox.tpl__<br>
Shows the login form (can be disabled in ACP).
* __searchresults.tpl__<br>
Displays the search results.
* __password.tpl__<br>
The form to reset your password.