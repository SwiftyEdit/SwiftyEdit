<?php


/**
 * sidebar for pages, snippets
 */

echo '<div class="sidebar-logo">';
echo '<a href="/admin/dashboard/" class="" title="Dashboard"></a>';
echo '</a>';
echo '</div>';

echo '<ul class="nav">';

echo '<li class="sidebar-nav-item">';
echo '<a class="sidebar-nav-link d-block color-contents '.($se_section == "pages" ? 'sidebar-nav-active' :'').'" href="/admin/pages/">';
echo $icon['files'];
echo '<span>'.$lang['nav_btn_pages'].'</span></a></li>';

echo '<li class="sidebar-nav-item">';
echo '<a class="sidebar-nav-link d-block color-contents '.($se_section == "snippets" ? 'sidebar-nav-active' :'').'" href="/admin/snippets/">';
echo $icon['card_heading'];
echo '<span>'.$lang['snippets'].'</span></a></li>';

echo '<li class="sidebar-nav-item">';
echo '<a class="sidebar-nav-link d-block color-blog '.($se_section == "blog" ? 'sidebar-nav-active' :'').'" href="/admin/blog/">';
echo $icon['file_earmark_post'];
echo '<span>'.$lang['nav_btn_blog'].'</span></a></li>';

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarShop" aria-expanded="false" aria-controls="sidebarShop" class="sidebar-nav-link d-block color-shop toggler '.($se_section == "shop" ? 'sidebar-nav-active' :'').'">';
echo $icon['store'];
echo '<span>'.$lang['nav_btn_shop'].'</span>';
echo '</a>';
echo '<div class="collapse '.($se_section == "shop" ? 'show' :'').'" id="sidebarShop">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($subinc == "products-list" ? 'sidebar-nav-active' :'').'" href="/admin/shop/">'.$icon['dot'].' '.$lang['nav_btn_products'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "prices" ? 'sidebar-nav-active' :'').'" href="/admin/shop/prices/">'.$icon['dot'].' '.$lang['nav_btn_price_groups'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "features" ? 'sidebar-nav-active' :'').'" href="/admin/shop/features/">'.$icon['dot'].' '.$lang['nav_btn_features'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "options" ? 'sidebar-nav-active' :'').'" href="/admin/shop/options/">'.$icon['dot'].' '.$lang['btn_options'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "filters" ? 'sidebar-nav-active' :'').'" href="/admin/shop/filters/">'.$icon['dot'].' '.$lang['filter'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "orders" ? 'sidebar-nav-active' :'').'" href="/admin/shop/orders/">'.$icon['dot'].' '.$lang['nav_btn_orders'].'</a></li>';
echo '<li><a class="sidebar-nav" href="/admin/settings/shop/">'.$icon['gear'].' '.$lang['nav_btn_settings'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarEvents" aria-expanded="false" aria-controls="sidebarEvents" class="sidebar-nav-link d-block color-events toggler">';
echo $icon['calendar_event'];
echo '<span>'.$lang['nav_btn_events'].'</span>';
echo '</a>';
echo '<div class="collapse '.($se_section == "events" ? 'show' :'').'" id="sidebarEvents">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($subinc == "events-list" ? 'sidebar-nav-active' :'').'" href="/admin/events/">'.$icon['dot'].' '.$lang['overview'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "bookings" ? 'sidebar-nav-active' :'').'" href="/admin/events/bookings/">'.$icon['dot'].' '.$lang['nav_btn_bookings'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '<li class="sidebar-nav-item">';
echo '<a class="sidebar-nav-link d-block color-addons '.($se_section == "addons" ? 'sidebar-nav-active' :'').'" href="/admin/addons/">';
echo $icon['plugin'];
echo '<span>'.$lang['nav_btn_addons'].'</span></a></li>';


echo '<li class="sidebar-nav-item">';
echo '<a class="sidebar-nav-link '.($se_section == "uploads" ? 'sidebar-nav-active' :'').' color-uploads" href="/admin/uploads/">';
echo $icon['folder'];
echo '<span>'.$lang['nav_btn_uploads'].'</span></a></li>';


echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarReactions" aria-expanded="false" aria-controls="sidebarReactions" class="sidebar-nav-link d-block color-reactions toggler">';
echo $icon['inbox'];
echo '<span>'.$lang['nav_btn_inbox'].'</span>';
echo '</a>';
echo '<div class="collapse '.($se_section == "inbox" ? 'show' :'').'" id="sidebarReactions">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($subinc == "inbox-mail" ? 'sidebar-nav-active' :'').'" href="/admin/inbox/">'.$icon['dot'].' '.$lang['nav_btn_mails'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "inbox-comments" ? 'sidebar-nav-active' :'').'" href="/admin/inbox/comments/">'.$icon['dot'].' '.$lang['nav_btn_comments'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "inbox-reactions" ? 'sidebar-nav-active' :'').'" href="/admin/inbox/reactions/">'.$icon['dot'].' '.$lang['nav_btn_reactions'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarUser" aria-expanded="false" aria-controls="sidebarPosts" class="sidebar-nav-link d-block color-user toggler">';
echo $icon['people'];
echo '<span>'.$lang['nav_btn_user'].'</span>';
echo '</a>';
echo '<div class="collapse '.($se_section == "users" ? 'show' :'').'" id="sidebarUser">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($subinc == "users-list" ? 'sidebar-nav-active' :'').'" href="/admin/users/">'.$icon['dot'].' '.$lang['overview'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "users-groups" ? 'sidebar-nav-active' :'').'" href="/admin/users/groups/">'.$icon['dot'].' '.$lang['nav_btn_user_groups'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '<li class="sidebar-nav-item">';
echo '<a class="sidebar-nav-link '.($se_section == "categories" ? 'sidebar-nav-active' :'').'" href="/admin/categories/">';
echo $icon['bookmarks_fill'];
echo '<span>'.$lang['categories'].'</span></a></li>';

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarPrefs" aria-expanded="false" aria-controls="sidebarPrefs" class="sidebar-nav-link d-block toggler">';
echo $icon['gear'];
echo '<span>'.$lang['nav_btn_settings'].'</span>';
echo '</a>';
echo '<div class="collapse '.($se_section == "settings" ? 'show' :'').'" id="sidebarPrefs">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($subinc == "general" ? 'sidebar-nav-active' :'').'" href="/admin/settings/">'.$icon['dot'].' '.$lang['nav_btn_general'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "posts" ? 'sidebar-nav-active' :'').'" href="/admin/settings/posts/">'.$icon['dot'].' '.$lang['nav_btn_posts'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "shop" ? 'sidebar-nav-active' :'').'" href="/admin/settings/shop/">'.$icon['dot'].' '.$lang['nav_btn_shop'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "events" ? 'sidebar-nav-active' :'').'" href="/admin/settings/events/">'.$icon['dot'].' '.$lang['nav_btn_events'].'</a></li>';
echo '<li><a class="sidebar-nav '.($subinc == "labels" ? 'sidebar-nav-active' :'').'" href="/admin/settings/labels/">'.$icon['dot'].' '.$lang['labels'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '<li class="sidebar-nav-item">';
echo '<a class="sidebar-nav-link '.($se_section == "update" ? 'sidebar-nav-active' :'').'" href="/admin/update/">';
echo $icon['arrow_clockwise'];
echo '<span>'.$lang['update'].'</span></a></li>';

echo '</ul>';


echo '<div class="sidebar-footer">';
echo '<ul class="nav">';
echo '<li class="sidebar-nav-item"><a href="/">'.$icon['home'].' '.$lang['nav_btn_homepage'].'</a></li>';
echo '<li class="sidebar-nav-item"><a href="/?goto=logout">'.$icon['logout'].' '.$lang['nav_btn_logout'].'</a></li>';
echo '</ul>';
echo '</div>';