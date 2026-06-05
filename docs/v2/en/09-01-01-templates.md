---
title: Themes - Templates
description: The template system
btn: Templates
group: developer
priority: 200
---

# The templates (.tpl files)

Some templates are included directly by SwiftyEdit.
These templates should therefore be located in the `public/assets/themes/{theme}/templates/` folder.
If a file is missing from your theme or you deliberately leave it out,
SwiftyEdit loads the file from the "default" theme.

## The most important template files

* __index.tpl__<br>
  Is the first template that is loaded.
  From here you can link all your other template files.
* __404.tpl__<br>
  Is shown for the HTTP status code 404 (page not found).
* __registerform.tpl__<br>
  Contains the form to register as a user.
* __profile_main.tpl__<br>
  Here users can update their profile.
* __status_message.tpl__<br>
  System messages are output here.
* __statusbox.tpl__<br>
  Here the links to the ACP (for administrators) or to the profile are displayed.
* __loginbox.tpl__<br>
  Shows the login form (can be disabled in the ACP).
* __searchresults.tpl__<br>
  Displays the search results.
* __password.tpl__<br>
  The form to reset your password.
