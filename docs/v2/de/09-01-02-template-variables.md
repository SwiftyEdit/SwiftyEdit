---
title: Themes - Template-Variablen
description: Referenztabellen der wichtigsten Smarty-Variablen pro Template
btn: Template-Variablen
group: developer
priority: 200
---

# Template-Variablen (Referenz)

Diese Seite listet die wichtigsten Variablen für die Templates, die am häufigsten angepasst
werden - Blog, Shop, Events, Listen (Wishlist) und Bestellungen, plus die layoutweiten Variablen,
die auf jeder Seite zur Verfügung stehen. Sie ist **kuratiert, nicht vollständig**: Für
Zwischenvariablen, die hier fehlen, oder um zu prüfen, ob sich seit dieser Seite etwas geändert
hat, bleibt `{debug}` bzw. der Blick in den zuständigen Handler die maßgebliche Quelle - siehe
[Template-Variablen](09-01-01-templates.md#template-variablen) in der vorherigen Seite für beide
Wege.

Wo nicht anders angegeben, ist der Typ ein String; leer/`""`, wenn im ACP nichts eingetragen
wurde. Boolesche Variablen werden hier als "true/false" angegeben, auch wenn PHP intern teils
`true` / `false`, teils `1` / `2` (ACP-Select-Werte) verwendet - das ist bei jeder Variable vermerkt,
wo es nicht aus dem Namen hervorgeht.

## Layout & global {#layout-global}

Zugewiesen von `app/template-setup.php` und `app/smarty.php`, auf **jeder** Frontend-Seite
verfügbar (auch innerhalb Blog/Shop/Events-Templates).

| Variable | Bedeutung |
|---|---|
| `$page_content` | Der fertig gerenderte Inhalt der aktuellen Seite (Editor-Content, bereits durch Shortcodes/`[include]`/Editor-Plugin geparst). Bei Blog-Listen wird dieser Wert vom Handler mit dem gerenderten `posts-list.tpl` überschrieben. |
| `$products_content` | **Nur** auf Shop-Listenseiten gesetzt: das gerenderte `products-list.tpl`. Hier bleibt `$page_content` unverändert der eigene Editor-Text der Seite - so lässt sich über der Produktliste ein eigener Einleitungstext pflegen. `content.tpl` gibt beide nacheinander aus. |
| `$msg_content` | Einmalige Statusmeldung (z. B. nach Logout), meist leer. `nocache`. |
| `$content_tags` | Tags des aktuellen Inhalts (Seite/Post/Produkt/Event) als Array `tag_href` / `tag_title`. Wird zusätzlich von jedem Post/Produkt/Event-Handler für den jeweiligen Eintrag neu gesetzt. |
| `$page_title`, `$page_meta_description`, `$page_meta_keywords`, `$page_meta_author`, `$page_meta_robots`, `$page_canonical_url` | Meta-Angaben der aktuellen Seite, bereits `html_entity_decode()`t. |
| `$page_logo`, `$page_thumbnail`, `$page_thumbnails` (Array), `$favicon_base`, `$page_hash` | Nur gesetzt, wenn im ACP hinterlegt. |
| `$se_template`, `$se_template_layout`, `$se_template_stylesheet` | Aktives Theme, gewähltes Seitenlayout, gewählte Stylesheet-Variante (Farbschema-Picker). |
| `$body_template` | Alias für `$se_template_layout`, wie er von `index.tpl` zum Einbinden des Layout-Templates verwendet wird. |
| `$hidden_csrf_token` | Fertiges `<input type="hidden" name="csrf_token" ...>`-HTML - direkt in jedes `<form method="POST">` einbetten. |
| `$arr_menue`, `$homepage_linkname`, `$homepage_title`, `$homepage_permalink`, `$link_home`, `$homelink_status` | Hauptnavigation (siehe `navigation.tpl`). Jeder Eintrag in `$arr_menue` kann `children` (Array, gleiche Struktur) für Dropdown-Untermenüs enthalten. |
| `$arr_submenue`, `$legend_toc` | Inhaltsverzeichnis-Block für Seiten mit Unterseiten (siehe `sidebar-toc.tpl`) - **nur gesetzt, wenn die aktuelle Seite Unterseiten hat.** |
| `$arr_bcmenue` | Breadcrumb-Pfad (siehe `footer.tpl`), auf der 404-Seite nicht gesetzt. |
| `$legal_pages` | Als "rechtlich" markierte Seiten (Impressum, Datenschutz, ...) fürs Footer-Menü. |
| `$search_uri` | Permalink der Suchseite. |
| `$show_shopping_cart`, `$shopping_cart_uri` | Nur gesetzt, wenn der Warenkorb in den Shop-Einstellungen aktiviert ist. |
| `$show_page_comments`, `$comments_title` | Nur gesetzt, wenn Kommentare für diese Seite aktiv sind. |
| `$page_categories_mode` | Steuert, ob/wie Kategorien für diese Seite angezeigt werden (ACP-Seiteneinstellung). |
| `$se_snippet_<name>` | Jedes globale Snippet (ACP → Snippets) steht unter diesem Präfix zur Verfügung, Inhalt bereits geparst - z. B. `$se_snippet_footer_text`. |
| `$prefs_<key>` | Jede Einstellung aus `$se_settings[...]` (ACP → Einstellungen) steht unter diesem Präfix zur Verfügung - z. B. `$prefs_dateformat`, `$prefs_cms_base`, `$prefs_pagetitle`. |
| `$lang_<key>` | Jeder Übersetzungsstring aus `$lang[...]` steht unter diesem Präfix zur Verfügung - z. B. `$lang_button_acp`. |
| `$se_pageload_time`, `$se_base_href`, `$se_page_url`, `$se_include_path`, `$se_start_time`, `$se_end_time` | Debug-/Basis-URL-Informationen. |
| `$admin_helpers_snippets`, `$admin_helpers_plugins`, `$admin_helpers_products`, `$admin_helpers_images`, `$admin_helpers_files` | Nur für eingeloggte Administratoren: IDs für die "Diese Seite/dieses Element bearbeiten"-Schnellzugriffe (`admin_helpers.tpl`). |

## Blog (`posts-list.tpl` / `posts-display.tpl`)

Zugewiesen von `app/handlers/posts-list.php` bzw. `app/handlers/posts-display.php`.

**`posts-list.tpl`**

| Variable | Bedeutung |
|---|---|
| `$posts` | Array der anzuzeigenden Beiträge. Jeder Eintrag trägt bereits frontend-fertige Schlüssel: `post_title`, `post_teaser` / `post_text` (bereits dekodiert), `post_tmb_src` (Thumbnail oder leer), `post_href` (**`false`**, wenn im ACP "Detailseite ausblenden" gesetzt ist!), `post_categories` (Array `cat_href` / `cat_title`), `content_tags`, `show_voting`, `votes_status_up` / `votes_status_dn`, `votes_up` / `votes_dn`, `draft_message` / `post_css_classes` (nur Entwürfe, für Admins), `post_thumbnails` (nur Galerie-Beiträge), `video_id` (nur Video-Beiträge), `btn_open_post`. |
| `$categories` | Kategorie-Navigation für diese Listenseite (gleiche Struktur wie im Shop, siehe unten). |
| `$category_template_data` | Dekodierte `page_template_values` der aktiven Kategorie - nur gesetzt, wenn diese Kategorie eigene Theme-Werte für das aktuelle Template hinterlegt hat. |
| `$post_cnt`, `$post_start_nbr`, `$post_end_nbr` | Gesamtzahl Treffer sowie "zeige X-Y von Z". |
| `$show_posts_list`, `$show_pagination`, `$pagination` (Array `href` / `nbr` / `active_class`), `$pag_prev_href`, `$pag_next_href` | Listen-/Pagination-Status. |
| `$form_action` | POST-Ziel für Formulare auf dieser Seite (z. B. Voting). |

**`posts-display.tpl`**

| Variable | Bedeutung |
|---|---|
| `$post_id`, `$post_type` | `post_type`: `m` Nachricht, `i` Bild, `g` Galerie, `v` Video, `l` Link, `d` Download. |
| `$post_title`, `$post_teaser`, `$post_text`, `$post_author`, `$post_releasedate_str` | Grunddaten. |
| `$post_tmb_src` | Erstes Bild. |
| `$gallery_thumbs` | Nur `post_type` `g`: Array `tmb_src` / `img_src`. |
| `$video_id` | Nur `post_type` `v`: YouTube-Video-ID. |
| `$post_external_link`, `$post_external_redirect`, `$post_link_text` | Nur `post_type` `l`. |
| `$post_file_attachment`, `$post_file_attachment_external`, `$post_file_version`, `$post_file_license` | Nur `post_type` `d`. |
| `$content_tags`, `$show_voting`, `$votes_status_up` / `$votes_status_dn`, `$votes_up` / `$votes_dn`, `$show_comments` | Wie oben. |
| `$form_action`, `$btn_download` | |

## Shop (`products-list.tpl` / `products-display.tpl`)

Zugewiesen von `app/handlers/products-list.php` bzw. `app/handlers/products-display.php`.

**`products-list.tpl`**

| Variable | Bedeutung |
|---|---|
| `$products` | Array der anzuzeigenden Produkte. Jeder Eintrag trägt zusätzlich zu den Rohdaten: `product_title`, `product_teaser` / `product_text` (dekodiert), `product_img_src`, `product_href`, `price_tag` (bereits nach `posts_price_mode` formatiert: brutto / netto+brutto / nur netto), `price_tag_label_from` (nicht leer ⇒ "ab"-Preis anzeigen, weil eine Variante günstiger ist), `product_categories`, `content_tags`, `show_voting`, `votes_status_up` / `votes_status_dn`, `votes_up` / `votes_dn`, `variants_alert` (nur wenn >1 Variante existiert), `show_shopping_cart` (pro Artikel - `false` bei ausverkauft/eigenen Optionen/deaktiviertem Warenkorb für dieses Produkt), `show_wishlist_button`, `draft_message` / `product_css_classes`. |
| `$product_filter` | Sidebar-Filterdefinition: Array von Gruppen mit `items` (je `title`, `checked`, `filter_url`), `has_active`, `clear_url`; bei Bereichs-Filtern (`input_type == 3`) zusätzlich `range_min` / `range_max` / `current_min` / `current_max`. Siehe [Filter](05-05-filter.md). |
| `$active_filter_tags`, `$has_active_filters` | Aktuell aktive Filter-Chips mit ihrer "entfernen"-URL. |
| `$sort_urls` (Array `default` / `name` / `ts` / `pasc` / `pdesc`), `$class_sort_name` / `$class_sort_topseller` / `$class_sort_price_asc` / `$class_sort_price_desc` | Sortier-Links bzw. die `active`-CSS-Klasse für die aktuelle Sortierung. |
| `$categories`, `$cat_hashes` | Kategorie-Navigation. |
| `$product_cnt` / `$nbr_products`, `$show_products_list`, `$show_pagination`, `$pagination`, `$pag_prev_href`, `$pag_next_href` | Listen-/Pagination-Status. |
| `$show_shopping_cart` | Site-weiter Warenkorb-Schalter (ACP-Einstellung), nicht zu verwechseln mit dem Feld gleichen Namens innerhalb jedes `$products`-Eintrags. |
| `$wishlist_login_uri`, `$filter_base_url`, `$form_action`, `$page_slug` | |
| `$btn_add_to_cart`, `$btn_read_more` | |

**`products-display.tpl`**

Das umfangreichste Template im Shop-Bereich - hier die vollständige Liste:

| Variable | Bedeutung |
|---|---|
| `$product_id`, `$product_title`, `$product_number`, `$product_teaser`, `$product_text`, `$product_href` | Grunddaten. |
| `$product_type` | `"p"` Hauptprodukt, `"v"` Variante eines Hauptprodukts. |
| `$product_price_tag` | Fertig formatierter Preis nach `posts_price_mode` (brutto / netto+brutto / nur netto - B2B-Modus). Nicht selbst neu berechnen. |
| `$product_tax_label`, `$product_currency`, `$product_price_label`, `$product_unit`, `$product_amount` | |
| `$product_price_gross`, `$product_price_net`, `$product_price_tax` | Einzelwerte, falls eigenes Preis-Layout gebraucht wird statt `$product_price_tag`. |
| `$product_pricetag_mode` | ACP-Wert `"1"` = Preis anzeigen, `"2"` = kompletten Preis-/Warenkorb-Block ausblenden. Im Template: `{if $product_pricetag_mode != "2"}`. |
| `$product_cart_mode` | ACP-Wert `"1"` = Warenkorb-Formular anzeigen, `"2"` = ausblenden. Wird zusätzlich site-weit über die Shop-Einstellung "Warenkorb" erzwungen. |
| `$is_addon_only`, `$product_addon_only_note` (bool / Text) | `true`, wenn das Produkt nur als Zusatzoption zu einem anderen Produkt buchbar ist (siehe `$select_addons` unten) - hat dann keinen eigenen "In den Warenkorb"-Button. |
| `$show_volume_discounts` (Array `amount` / `price_net` / `price_gross`), `$label_prices_discount` | Nur gesetzt, wenn Mengenrabatte in der Preisgruppe hinterlegt sind. |
| `$product_lowest_price_net`, `$product_lowest_price_gross` | Nur gesetzt, wenn Varianten existieren und deren günstigster Preis unter dem des Hauptprodukts liegt ("ab"-Preis). |
| `$select_options` (Array `title` / `values`) | Klassische Dropdown-Produktoptionen (z. B. Größe/Farbe ohne eigenen Aufpreis). |
| `$select_addons` (Array `id` / `href` / `title` / `delivery_time` / `teaser` / `image_src` / `price` / `sku` / `unit` / `amount`), `$product_addons_label` | Buchbare Zusatzoptionen mit eigenem Preis (Checkboxen) - jeder Eintrag ist selbst ein Produkt mit `$is_addon_only == true`. |
| `$product_options_comment_label` | Label für das "Anmerkung"-Textfeld, nur gesetzt wenn im ACP hinterlegt. |
| `$file_upload_message` | Nur gesetzt, wenn das Produkt einen Datei-Upload durch den eingeloggten Kunden erfordert. |
| `$product_order_quantity_min`, `$product_order_quantity_max` | **Bereits fertige HTML-Attribute** (z. B. `min="1"`), nicht nur die Zahl - direkt ins `<input>` einsetzen. |
| `$show_wishlist_button`, `$wishlist_logged_in`, `$wishlist_already_saved`, `$wishlist_login_uri` | Siehe den `{capture name="wishlist_btn"}`-Block im Default-Theme. |
| `$product_img_src` / `_alt` / `_title` / `_caption` | Erstes Produktbild. |
| `$product_show_images` (Array `media_file` / `media_title` / `media_alt`) | Alle Produktbilder, für Galerie/Lightbox. |
| `$product_text_label`, `$label_product_features`, `$product_features` (Array `snippet_title` / `snippet_content`) | Beschreibungs- und Merkmale-Tab. |
| `$text_additional1` … `$text_additional5`, `$text_additional1_label` … `$text_additional5_label` | Bis zu 5 frei benannte zusätzliche Text-Tabs; Label leer ⇒ Tab ausblenden. |
| `$text_scope_of_delivery` | Lieferumfang-Tab. |
| `$show_variants`, `$show_related`, `$show_accessories` | Je ein Array mit gleicher Struktur: `title` / `teaser` / `image` / `product_href` / `class` (`class == 'active'` markiert das gerade angezeigte Produkt innerhalb der Varianten). |
| `$product_snippet_text` / `_title`, `$product_snippet_price`, `$label_prices_snippet` | Optionale Snippet-Blöcke (frei verknüpfbarer Text-/Preis-Baustein aus dem ACP). |
| `$attachment_filename`, `$download_title` / `_text` / `_credit` / `_version` / `_license`, `$se_snippet_downloading_modal` | Nur gesetzt, wenn ein Anhang hinterlegt ist. |
| `$show_voting`, `$votes_status_up` / `$votes_status_dn`, `$votes_up` / `$votes_dn` | Wie bei Posts/Events. |
| `$content_tags`, `$product_delivery_time_title` / `_text`, `$label_delivery_time`, `$form_action`, `$btn_add_to_cart` | |
| `$data_source` | Debug-Info: `"cache"` oder `"database"` - woher `$product_data` kam. |
| `$product_plugin_actions` | Array von Plugin-Aktionen (`type` `button` / `link` mit `name` / `value` / `class` / `label` bzw. `href` / `class` / `label`) - siehe [Hooks](09-01-00-themes.md#hooks), Filter `product.display.actions`. |

## Events (`events-list.tpl` / `events-display.tpl`)

Zugewiesen von `app/handlers/events-list.php` bzw. `app/handlers/events-display.php`. Struktur
ist weitgehend analog zu Blog/Shop.

**`events-list.tpl`**

`$events` (Array analog `$posts`, mit `event_title`, `event_teaser` / `event_text`, `event_img_src`,
`event_href`, `event_releasedate`, `event_start_day` / `_month` / `_month_text` / `_year`,
`event_end_day` / `_month` / `_year`, `event_categories`, `content_tags`, `show_voting`, `votes_*`,
`draft_message`), `$events_cnt`, `$show_events_list`, `$show_pagination`, `$pagination`,
`$pag_prev_href` / `$pag_next_href`, `$categories`, `$form_action`, `$btn_read_more`.

**`events-display.tpl`**

| Variable | Bedeutung |
|---|---|
| `$event_id`, `$event_title`, `$event_teaser`, `$event_text`, `$event_img_src` | Grunddaten. |
| `$event_start_day` / `_month` / `_month_text` / `_year`, `$event_end_day` / `_month` / `_year` | |
| `$event_price_note` | Freitext-Preishinweis. |
| `$show_guestlist` | Gästeliste aktiv (ACP: `event_guestlist == 2` registrierte Nutzer, `== 3` alle). |
| `$disabled` | Fertiges `disabled`-HTML-Attribut oder leer - für den "Zusagen"-Button. |
| `$label_nbr_total_available` / `$nbr_available_total`, `$label_nbr_commitments` / `$nbr_commitments` | Nur gesetzt, wenn ein Platzlimit bzw. eine öffentliche Zusagen-Anzahl konfiguriert ist. |
| `$sign_guestlist`, `$description_guestlist` | Beschriftungen für die Gästeliste. |
| `$product_snippet_text` | Nur gesetzt, wenn dem Event ein Snippet zugeordnet ist. |
| `$content_tags`, `$show_voting`, `$votes_*`, `$form_action`, `$btn_add_to_cart` | Wie oben. |

## Listen (Wishlist)

Zugewiesen von `app/handlers/wishlist.php`. Siehe [Listen](05-00-shop.md#listen) für die
kundenseitige Funktion. `$wishlist_page_uri` wird immer zugewiesen (für Querverweise aus anderen
Templates, z. B. dem "Zur Liste hinzufügen"-Button in `products-display.tpl`).

| Template | Variablen |
|---|---|
| `wishlist_overview.tpl` (eigene Listen des eingeloggten Kunden) | `$wishlists` (Array), `$wishlist_is_owner` (immer `true`). |
| `wishlist_public.tpl` (öffentliche, schreibgeschützte Ansicht einer geteilten Liste) | `$wishlist`, `$wishlist_items`, `$wishlist_is_owner` (immer `false`), `$form_action`. |

## Bestellungen (Orders)

| Template | Zugewiesen von | Variablen |
|---|---|---|
| `orders.tpl` | `app/handlers/orders.php` | `$order_page_uri`; `$upload_message` / `$upload_message_class` nur nach einem Upload-Versuch. |
| `orders-list.tpl` (HTMX-Partial) | `app/xhr/orders.php` | `$orders` (Array `id` / `nbr` / `date` / `status` / `status_payment` / `withdrawal_requested` / `price`), `$show_order_pagination`, `$next_page_nbr` / `$prev_page_nbr`. |
| `order-item.tpl` (Modal) | `app/xhr/orders.php` | `$products` (Array `pos` / `title` / `options` / `options_comment` / `options_comment_label` / `product_nbr` / `amount` / `price_gross` / `post_id` / `need_upload` / `user_upload` / `user_upload_status` / `file_attachment_as` / `dl_file_ext`), `$order_time` / `_nbr` / `_currency` / `_price_total`, `$payment_plugin_str`, `$order_billing_address` / `$order_shipping_address`, `$order_status` / `_payment` / `_shipping`, `$order_page_uri`, `$order_withdrawal_uri` / `_eligible` / `_requested`. |
| `order-withdrawal.tpl` | `app/handlers/order_withdrawal.php` | `$prefill_order_nbr` / `$prefill_mail`, `$heading_order_withdrawal`, `$label_order_nbr` / `$label_mail` / `$label_order_withdrawal_reason`, `$button_order_withdrawal`, `$text_order_withdrawal_intro`. |
