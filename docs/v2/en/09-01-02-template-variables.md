---
title: Themes - Template Variables
description: Reference tables of the most important Smarty variables per template
btn: Template Variables
group: developer
priority: 200
---

# Template Variables (Reference)

This page lists the most important variables for the templates that get customized most often -
blog, shop, events, lists (wishlist), and orders - plus the layout-wide variables available on
every page. It is **curated, not exhaustive**: for intermediate variables missing here, or to
check whether something has changed since this page was written, `{debug}` or looking at the
responsible handler remains the authoritative source - see
[Template Variables](09-01-01-templates.md#template-variables) on the previous page for both
approaches.

Unless noted otherwise, the type is a string; empty/`""` if nothing was entered in the ACP.
Boolean variables are listed here as "true/false" even though PHP internally sometimes uses
`true` / `false` and sometimes `1` / `2` (ACP select values) - that's noted per variable wherever it
doesn't follow from the name.

## Layout & global {#layout-global}

Assigned by `app/template-setup.php` and `app/smarty.php`, available on **every** frontend page
(including inside blog/shop/events templates).

| Variable | Meaning |
|---|---|
| `$page_content` | The fully rendered content of the current page (editor content, already parsed through shortcodes/`[include]`/the editor plugin). On blog list pages, the handler overwrites this value with the rendered `posts-list.tpl`. |
| `$products_content` | Set **only** on shop list pages: the rendered `products-list.tpl`. Here `$page_content` stays the page's own editor text unchanged - so an intro text above the product grid can be maintained separately. `content.tpl` outputs both, one after the other. |
| `$msg_content` | A one-off status message (e.g. after logout), usually empty. `nocache`. |
| `$content_tags` | Tags of the current content (page/post/product/event) as an array of `tag_href` / `tag_title`. Also re-assigned by every post/product/event handler for that specific entry. |
| `$page_title`, `$page_meta_description`, `$page_meta_keywords`, `$page_meta_author`, `$page_meta_robots`, `$page_canonical_url` | Meta info for the current page, already `html_entity_decode()`d. |
| `$page_logo`, `$page_thumbnail`, `$page_thumbnails` (array), `$favicon_base`, `$page_hash` | Only set when configured in the ACP. |
| `$se_template`, `$se_template_layout`, `$se_template_stylesheet` | Active theme, chosen page layout, chosen stylesheet variant (color-skin picker). |
| `$body_template` | Alias for `$se_template_layout`, as used by `index.tpl` to include the layout template. |
| `$hidden_csrf_token` | Ready-made `<input type="hidden" name="csrf_token" ...>` HTML - embed directly in every `<form method="POST">`. |
| `$arr_menue`, `$homepage_linkname`, `$homepage_title`, `$homepage_permalink`, `$link_home`, `$homelink_status` | Main navigation (see `navigation.tpl`). Each entry in `$arr_menue` can carry `children` (array, same shape) for dropdown submenus. |
| `$arr_submenue`, `$legend_toc` | Table-of-contents block for pages with subpages (see `sidebar-toc.tpl`) - **only set when the current page has subpages.** |
| `$arr_bcmenue` | Breadcrumb trail (see `footer.tpl`), not set on the 404 page. |
| `$legal_pages` | Pages flagged as "legal" (imprint, privacy policy, ...) for the footer menu. |
| `$search_uri` | Permalink of the search page. |
| `$show_shopping_cart`, `$shopping_cart_uri` | Only set when the shopping cart is enabled in the shop settings. |
| `$show_page_comments`, `$comments_title` | Only set when comments are enabled for this page. |
| `$page_categories_mode` | Controls whether/how categories are shown for this page (ACP page setting). |
| `$se_snippet_<name>` | Every global snippet (ACP → Snippets) is available under this prefix, content already parsed - e.g. `$se_snippet_footer_text`. |
| `$prefs_<key>` | Every setting from `$se_settings[...]` (ACP → Settings) is available under this prefix - e.g. `$prefs_dateformat`, `$prefs_cms_base`, `$prefs_pagetitle`. |
| `$lang_<key>` | Every translation string from `$lang[...]` is available under this prefix - e.g. `$lang_button_acp`. |
| `$se_pageload_time`, `$se_base_href`, `$se_page_url`, `$se_include_path`, `$se_start_time`, `$se_end_time` | Debug/base-URL information. |
| `$admin_helpers_snippets`, `$admin_helpers_plugins`, `$admin_helpers_products`, `$admin_helpers_images`, `$admin_helpers_files` | Logged-in administrators only: IDs for the "edit this page/item" quick-access shortcuts (`admin_helpers.tpl`). |

## Blog (`posts-list.tpl` / `posts-display.tpl`)

Assigned by `app/handlers/posts-list.php` and `app/handlers/posts-display.php` respectively.

**`posts-list.tpl`**

| Variable | Meaning |
|---|---|
| `$posts` | Array of posts to display. Each entry already carries frontend-ready keys: `post_title`, `post_teaser` / `post_text` (already decoded), `post_tmb_src` (thumbnail or empty), `post_href` (**`false`** when "hide detail page" is set in the ACP!), `post_categories` (array of `cat_href` / `cat_title`), `content_tags`, `show_voting`, `votes_status_up` / `votes_status_dn`, `votes_up` / `votes_dn`, `draft_message` / `post_css_classes` (drafts only, for admins), `post_thumbnails` (gallery posts only), `video_id` (video posts only), `btn_open_post`. |
| `$categories` | Category navigation for this listing page (same shape as the shop's, see below). |
| `$category_template_data` | Decoded `page_template_values` of the active category - only set when that category has its own theme values defined for the current template. |
| `$post_cnt`, `$post_start_nbr`, `$post_end_nbr` | Total match count plus "showing X-Y of Z". |
| `$show_posts_list`, `$show_pagination`, `$pagination` (array of `href` / `nbr` / `active_class`), `$pag_prev_href`, `$pag_next_href` | List/pagination state. |
| `$form_action` | POST target for forms on this page (e.g. voting). |

**`posts-display.tpl`**

| Variable | Meaning |
|---|---|
| `$post_id`, `$post_type` | `post_type`: `m` message, `i` image, `g` gallery, `v` video, `l` link, `d` download. |
| `$post_title`, `$post_teaser`, `$post_text`, `$post_author`, `$post_releasedate_str` | Basic data. |
| `$post_tmb_src` | First image. |
| `$gallery_thumbs` | `post_type` `g` only: array of `tmb_src` / `img_src`. |
| `$video_id` | `post_type` `v` only: YouTube video ID. |
| `$post_external_link`, `$post_external_redirect`, `$post_link_text` | `post_type` `l` only. |
| `$post_file_attachment`, `$post_file_attachment_external`, `$post_file_version`, `$post_file_license` | `post_type` `d` only. |
| `$content_tags`, `$show_voting`, `$votes_status_up` / `$votes_status_dn`, `$votes_up` / `$votes_dn`, `$show_comments` | As above. |
| `$form_action`, `$btn_download` | |

## Shop (`products-list.tpl` / `products-display.tpl`)

Assigned by `app/handlers/products-list.php` and `app/handlers/products-display.php`
respectively.

**`products-list.tpl`**

| Variable | Meaning |
|---|---|
| `$products` | Array of products to display. Besides the raw data, each entry carries: `product_title`, `product_teaser` / `product_text` (decoded), `product_img_src`, `product_href`, `price_tag` (already formatted per `posts_price_mode`: gross / net+gross / net only), `price_tag_label_from` (non-empty ⇒ show an "from" price, because a variant is cheaper), `product_categories`, `content_tags`, `show_voting`, `votes_status_up` / `votes_status_dn`, `votes_up` / `votes_dn`, `variants_alert` (only if >1 variant exists), `show_shopping_cart` (per item - `false` when sold out/has its own options/cart disabled for this product), `show_wishlist_button`, `draft_message` / `product_css_classes`. |
| `$product_filter` | Sidebar filter definition: array of groups with `items` (each `title`, `checked`, `filter_url`), `has_active`, `clear_url`; range filters (`input_type == 3`) additionally carry `range_min` / `range_max` / `current_min` / `current_max`. See [Filter](05-05-filter.md). |
| `$active_filter_tags`, `$has_active_filters` | Currently active filter chips with their "remove" URL. |
| `$sort_urls` (array `default` / `name` / `ts` / `pasc` / `pdesc`), `$class_sort_name` / `$class_sort_topseller` / `$class_sort_price_asc` / `$class_sort_price_desc` | Sort links, and the `active` CSS class for the current sort. |
| `$categories`, `$cat_hashes` | Category navigation. |
| `$product_cnt` / `$nbr_products`, `$show_products_list`, `$show_pagination`, `$pagination`, `$pag_prev_href`, `$pag_next_href` | List/pagination state. |
| `$show_shopping_cart` | Site-wide shopping cart toggle (ACP setting) - not to be confused with the field of the same name inside each `$products` entry. |
| `$wishlist_login_uri`, `$filter_base_url`, `$form_action`, `$page_slug` | |
| `$btn_add_to_cart`, `$btn_read_more` | |

**`products-display.tpl`**

The most extensive template in the shop area - the full list:

| Variable | Meaning |
|---|---|
| `$product_id`, `$product_title`, `$product_number`, `$product_teaser`, `$product_text`, `$product_href` | Basic data. |
| `$product_type` | `"p"` main product, `"v"` variant of a main product. |
| `$product_price_tag` | Fully formatted price per `posts_price_mode` (gross / net+gross / net only - B2B mode). Don't recompute this yourself. |
| `$product_tax_label`, `$product_currency`, `$product_price_label`, `$product_unit`, `$product_amount` | |
| `$product_price_gross`, `$product_price_net`, `$product_price_tax` | Individual values, in case a custom price layout is needed instead of `$product_price_tag`. |
| `$product_pricetag_mode` | ACP value `"1"` = show price, `"2"` = hide the whole price/cart block. In the template: `{if $product_pricetag_mode != "2"}`. |
| `$product_cart_mode` | ACP value `"1"` = show the add-to-cart form, `"2"` = hide it. Also forced site-wide via the shop's "shopping cart" setting. |
| `$is_addon_only`, `$product_addon_only_note` (bool / text) | `true` when this product can only be booked as an add-on to another product (see `$select_addons` below) - it then has no add-to-cart button of its own. |
| `$show_volume_discounts` (array `amount` / `price_net` / `price_gross`), `$label_prices_discount` | Only set when volume discounts are configured in the price group. |
| `$product_lowest_price_net`, `$product_lowest_price_gross` | Only set when variants exist and their lowest price undercuts the main product's ("from" price). |
| `$select_options` (array `title` / `values`) | Classic dropdown product options (e.g. size/color without their own surcharge). |
| `$select_addons` (array `id` / `href` / `title` / `delivery_time` / `teaser` / `image_src` / `price` / `sku` / `unit` / `amount`), `$product_addons_label` | Bookable add-ons with their own price (checkboxes) - each entry is itself a product with `$is_addon_only == true`. |
| `$product_options_comment_label` | Label for the "comment" text field, only set when configured in the ACP. |
| `$file_upload_message` | Only set when the product requires a file upload from the logged-in customer. |
| `$product_order_quantity_min`, `$product_order_quantity_max` | **Already-finished HTML attributes** (e.g. `min="1"`), not just the number - insert directly into the `<input>`. |
| `$show_wishlist_button`, `$wishlist_logged_in`, `$wishlist_already_saved`, `$wishlist_login_uri` | See the `{capture name="wishlist_btn"}` block in the default theme. |
| `$product_img_src` / `_alt` / `_title` / `_caption` | First product image. |
| `$product_show_images` (array `media_file` / `media_title` / `media_alt`) | All product images, for the gallery/lightbox. |
| `$product_text_label`, `$label_product_features`, `$product_features` (array `snippet_title` / `snippet_content`) | Description and features tab. |
| `$text_additional1` … `$text_additional5`, `$text_additional1_label` … `$text_additional5_label` | Up to 5 freely named additional text tabs; empty label ⇒ hide that tab. |
| `$text_scope_of_delivery` | Scope-of-delivery tab. |
| `$show_variants`, `$show_related`, `$show_accessories` | Each an array with the same shape: `title` / `teaser` / `image` / `product_href` / `class` (`class == 'active'` marks the currently displayed product among the variants). |
| `$product_snippet_text` / `_title`, `$product_snippet_price`, `$label_prices_snippet` | Optional snippet blocks (freely linkable text/price building block from the ACP). |
| `$attachment_filename`, `$download_title` / `_text` / `_credit` / `_version` / `_license`, `$se_snippet_downloading_modal` | Only set when an attachment is configured. |
| `$show_voting`, `$votes_status_up` / `$votes_status_dn`, `$votes_up` / `$votes_dn` | As with posts/events. |
| `$content_tags`, `$product_delivery_time_title` / `_text`, `$label_delivery_time`, `$form_action`, `$btn_add_to_cart` | |
| `$data_source` | Debug info: `"cache"` or `"database"` - where `$product_data` came from. |
| `$product_plugin_actions` | Array of plugin actions (`type` `button` / `link` with `name` / `value` / `class` / `label`, or `href` / `class` / `label`) - see [Hooks](09-01-00-themes.md#hooks), filter `product.display.actions`. |

## Events (`events-list.tpl` / `events-display.tpl`)

Assigned by `app/handlers/events-list.php` and `app/handlers/events-display.php` respectively.
The shape is largely analogous to blog/shop.

**`events-list.tpl`**

`$events` (array analogous to `$posts`, with `event_title`, `event_teaser` / `event_text`,
`event_img_src`, `event_href`, `event_releasedate`, `event_start_day` / `_month` / `_month_text` / `_year`,
`event_end_day` / `_month` / `_year`, `event_categories`, `content_tags`, `show_voting`, `votes_*`,
`draft_message`), `$events_cnt`, `$show_events_list`, `$show_pagination`, `$pagination`,
`$pag_prev_href` / `$pag_next_href`, `$categories`, `$form_action`, `$btn_read_more`.

**`events-display.tpl`**

| Variable | Meaning |
|---|---|
| `$event_id`, `$event_title`, `$event_teaser`, `$event_text`, `$event_img_src` | Basic data. |
| `$event_start_day` / `_month` / `_month_text` / `_year`, `$event_end_day` / `_month` / `_year` | |
| `$event_price_note` | Free-text price note. |
| `$show_guestlist` | Guest list enabled (ACP: `event_guestlist == 2` registered users, `== 3` everyone). |
| `$disabled` | A finished `disabled` HTML attribute, or empty - for the "RSVP" button. |
| `$label_nbr_total_available` / `$nbr_available_total`, `$label_nbr_commitments` / `$nbr_commitments` | Only set when a seat limit or a public commitment count is configured. |
| `$sign_guestlist`, `$description_guestlist` | Labels for the guest list. |
| `$product_snippet_text` | Only set when a snippet is linked to the event. |
| `$content_tags`, `$show_voting`, `$votes_*`, `$form_action`, `$btn_add_to_cart` | As above. |

## Lists (Wishlist)

Assigned by `app/handlers/wishlist.php`. See [Lists](05-00-shop.md#listen) for the customer-facing
feature. `$wishlist_page_uri` is always assigned (for cross-links from other templates, e.g. the
"add to wishlist" button in `products-display.tpl`).

| Template | Variables |
|---|---|
| `wishlist_overview.tpl` (the logged-in customer's own lists) | `$wishlists` (array), `$wishlist_is_owner` (always `true`). |
| `wishlist_public.tpl` (public, read-only view of a shared list) | `$wishlist`, `$wishlist_items`, `$wishlist_is_owner` (always `false`), `$form_action`. |

## Orders

| Template | Assigned by | Variables |
|---|---|---|
| `orders.tpl` | `app/handlers/orders.php` | `$order_page_uri`; `$upload_message` / `$upload_message_class` only after an upload attempt. |
| `orders-list.tpl` (HTMX partial) | `app/xhr/orders.php` | `$orders` (array `id` / `nbr` / `date` / `status` / `status_payment` / `withdrawal_requested` / `price`), `$show_order_pagination`, `$next_page_nbr` / `$prev_page_nbr`. |
| `order-item.tpl` (modal) | `app/xhr/orders.php` | `$products` (array `pos` / `title` / `options` / `options_comment` / `options_comment_label` / `product_nbr` / `amount` / `price_gross` / `post_id` / `need_upload` / `user_upload` / `user_upload_status` / `file_attachment_as` / `dl_file_ext`), `$order_time` / `_nbr` / `_currency` / `_price_total`, `$payment_plugin_str`, `$order_billing_address` / `$order_shipping_address`, `$order_status` / `_payment` / `_shipping`, `$order_page_uri`, `$order_withdrawal_uri` / `_eligible` / `_requested`. |
| `order-withdrawal.tpl` | `app/handlers/order_withdrawal.php` | `$prefill_order_nbr` / `$prefill_mail`, `$heading_order_withdrawal`, `$label_order_nbr` / `$label_mail` / `$label_order_withdrawal_reason`, `$button_order_withdrawal`, `$text_order_withdrawal_intro`. |
