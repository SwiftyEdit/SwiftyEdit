---
title: Snippets
description: Manage and use Snippets
btn: Snippets
group: backend
priority: 200
---
# Manage and use Snippets

<kbd>Backend</kbd> <kbd>Contents</kbd> <kbd>Snippets</kbd>

The possibilities of how snippets can be used are limitless.
Whenever you need certain text phrases more than once, they can be saved as a snippet and then
used via shortcode.

The overview lists all snippets. You can sort the list, filter it by language and, of course,
search it. If you work with a large number of snippets, you can keep order with the labels.

__Advice:__ The snippets are multilingual. When you use a multilingual page,
the snippet with the matching language is always loaded into your content.
If the snippet is not available in the desired language, the snippet in the default language
is automatically loaded.

## Embedding snippets

A snippet is embedded into content (e.g. a page, a post or another snippet) via its __name__
as a shortcode. There are three notations:

| Shortcode                     | Effect                                                                                                       |
|-------------------------------|--------------------------------------------------------------------------------------------------------------|
| `[snippet=name]`              | Inserts only the __content__ of the snippet.                                                                 |
| `[snippet]name[/snippet]`     | Identical to `[snippet=name]` – inserts only the content.                                                    |
| `[snippet=name]tpl[/snippet]` | Renders the snippet through its __own template__ (title, images, link, classes, etc.), if the theme supports it. |

Replace `name` with the name you assigned to the snippet.

__Note:__ Inside `<pre>` and `<code>` blocks, shortcodes are __not__ replaced. This lets you
show the syntax in tutorials without the snippet being embedded.

## Input fields

| Field           | Description                                                                                                                                                                                              |
|-----------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Name            | Your snippet is embedded by this name. You can use the same name more than once. The most recently created snippet is always embedded. The language is of course taken into account.                     |
| Title           | In most templates the title becomes a headline (H1–H6).                                                                                                                                                  |
| Content         | The actual content of the snippet is stored here.                                                                                                                                                       |
| Keywords        | If you use a plugin that filters snippets by keywords, you can enter them here. You will find more information in the plugin's documentation.                                                            |
| Classes         | Depending on the template/theme you use, you can assign CSS classes to your snippet. These are then passed to the template.                                                                              |
| Label           | This field lets you assign a label such as "New" or "Exclusive" to your snippet. This field is not to be confused with the labels that can be assigned to all records in the backend.                     |
| Images          | If you want to assign images to your snippet, you can select the desired images from the uploads here.                                                                                                   |
| URL             | If your snippet is used as a link, you can enter the URL and the link name here.                                                                                                                         |
| Own template    | If the installed themes support the snippet functions, these are shown in the selection here.                                                                                                            |


## Predefined snippets

| Snippet                | Description                                                                                                                  |
|------------------------|----------------------------------------------------------------------------------------------------------------------------|
| `account_confirm`      | This message is shown as soon as a user has confirmed their account.                                                       |
| `no_access`            | If a user opens a page for which they have no permission, this message is displayed.                                       |
| `account_confirm_mail` | Sent as an e-mail so that a user can activate their account. This snippet contains a few variables.                         |
| `account_reset_psw`    | Sent as an e-mail when a user resets their password. Replaces `forgotten_psw_mail_info` from the language file.             |
| `cart_order_sent`      | Shown as a message as soon as an order has been submitted.                                                                  |
| `cart_request_sent`    | Shown as a message as soon as the shopping cart has been sent as a request.                                                |
| `mail_salutation_order_request` | Used as the salutation/intro in the e-mail when the shopping cart is sent as a request. Supports the variables `{order_date}` and `{order_time}`. |
| `cart_max_order_value` | Shown when the maximum order value is exceeded and therefore only a request can be sent.                                    |
| `cart_error_delivery_zone` | Shown when the buyer's country is not included in the delivery areas and therefore only a request can be sent.          |
| `no_search_results`    | Shown when the search returns no results.                                                                                   |
| `agreement_text`       | This text must be accepted by the user before they can create an account.                                                  |
| `cart_agree_term`      | This text must be accepted before an order can be submitted.                                                               |
| `mail_psw_updated`     | Sent as an e-mail when a user has – successfully – reset their password.                                                    |

* `{USERNAME}` the user name
* `{SITENAME}` the name of the site (set in the settings)
* `{ACTIVATIONLINK}` is replaced automatically by SwiftyEdit.


### Reserved names

Some modules can access snippets directly and embed them.
In most cases you are shown a selection with the matching snippets right away.

#### Products, Posts, Events

* For the delivery time you can select all snippets whose name starts with `shop_delivery_time`.
* All snippets whose name contains `post_price` can be added to the prices.
* All snippets whose name contains `post_text` can be added to the texts.
* Plugins for payment methods use snippets with names like `cart_pm_%`.
* Plugins for shipping use snippets with names like `cart_dm_%`.
* In the shopping cart the snippet `cart_agree_term` is used for accepting the terms and conditions.
