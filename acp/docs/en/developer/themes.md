---
title: Themes
description: The template system
btn: Themes
group: developer
priority: 200
---

# The template system
We use Smarty template system.
If you want to create your own template, you should be familiar with the smarty documentation.

## Organization and file structure of a theme

We need the following files and directories

- /dist/ for your CSS and JS files
- /php/index.php for general theme functions (frontend only)
- /php/options.php (optional) manage theme options (frontend and backend)
- /php/xhr.php (optional) XHR routing file (frontend)
- /templates/ home of your smarty template files
- /templates-mail/ home of your mail templates

## Specifics

### tinyMCE
A template is not only responsible for the appearance of your website,
but can also control the look and functions of the tinyMCE editor.
This way you can achieve that the WYSIWYG view is as close as possible 
to the original layout (i.e. the frontend).

__Two files are largely relevant for this:__

* themes/{theme}/dist/editor.css
* themes/{theme}/dist/tinyMCE_config.js

The CSS file is used for the appearance of the content in the editor.
This is the easiest way to customize the editor to your layout.
The JavaScript file is used for configuration.
You can find more information in the tinyMCE documentation.

Further information on configuration can be found in the 
[tinyMCE documentation](https://www.tiny.cloud/docs/tinymce/latest/)