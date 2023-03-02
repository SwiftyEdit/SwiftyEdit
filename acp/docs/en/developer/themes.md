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

## Specifics
A template is not only responsible for the appearance of your website,
but can also control the look and functions of the tinyMCE editor.
This way you can achieve that the WYSIWYG view is as close as possible 
to the original layout (i.e. the frontend).

__Two files are largely relevant for this:__

* styles/deinTemplate/css/editor.css
* styles/deinTemplate/js/tinyMCE_config.js

The CSS file is used for the appearance of the content in the editor.
This is the easiest way to customize the editor to your layout.
The JavaScript file is used for configuration.
You can find more information in the tinyMCE documentation.