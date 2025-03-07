<p align="center">
<img src="https://user-images.githubusercontent.com/5982156/211496033-fc3d3fb3-123b-46cf-b100-05a7e0372922.svg" width="350" alt="SwiftyEdit Logo">
</p>

# SwiftyEdit CMS

## A few notes on version 2

* To increase security, we will use a /public/ folder for the frontend
* We use mod_rewrite for the backend
* We run almost all actions via AJAX/HTMX
* Extensions are only available in two variants: Plugins and Themes
* Existing themes should be able to continue to be used without much effort
* We remove the shortcode section. It's the same as snippets.

SwiftyEdit is an Open Source Content Management System based on PHP and MySQL or SQLite.

+ License: GNU GENERAL PUBLIC LICENSE Version 3<br>
![GitHub License](https://img.shields.io/github/license/SwiftyEdit/SwiftyEdit)
+ You are welcome to help with the translations<br>
[![Crowdin](https://badges.crowdin.net/swiftyedit/localized.svg)](https://crowdin.com/project/swiftyedit)

## Features

SwiftyEdit has a very simple structure and can be used for the smallest projects as well as larger challenges. 
The following modules are integrated in the system:

* The kind of __page management__ a CMS should have.
* __Snippets / Shortcodes__ - Don't write the same thing over and over again.
* __Blog__ - Publish your news, pictures, galleries or downloads and videos. Anything you want.
* __Events__ - Publish your events and manage reservations and guest lists.
* __Shop__ - Present your offers. Sell your things - including digital products.

### User manual and Developer Documentation

* https://swiftyedit.org/documentation/
* https://swiftyedit.org/de/dokumentation/

#### Technical requirements

+ Software: PHP 8+
+ Web Server: Apache with PDO/SQLite Module and mod_rewrite
+ Database: MySQL 5.6+ or SQLite

#### Download

Get the latest Version from https://swiftyedit.org/download/

#### Or install using Composer
```
composer create-project swiftyedit/swiftyedit
```

__Please note:__ 
As of version 2.0, we use the /public/ folder as the domain root. This means that your domain must point to this folder.

### Contribution

__You are very welcome to take part in this project.__ We are happy for every contribution.

When contributing to this repository, please first discuss the change you wish to make via issue, 
email, or any other method with the owners of this repository before making a change.

If you want to create a translation or improve an existing one, 
visit the [Crowdin Project](https://crowdin.com/project/swiftyedit)