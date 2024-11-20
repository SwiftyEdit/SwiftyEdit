<?php


/**
 * sidebar for pages, snippets
 */

echo '<div class="sidebar-logo mb-1 mt-2">';
echo '<a href="/admin/dashboard/" class="" title="Dashboard"></a>';
echo '</a>';
echo '</div>';

echo '<ul class="nav">';

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarContents" aria-expanded="false" aria-controls="sidebarContents" class="sidebar-nav-link d-block color-contents">';
echo $icon['diagram_3'];
echo '<span>'.$lang['nav_btn_contents'].'</span>';
echo '</a>';

echo '<div class="collapse '.($maininc == "inc.pages" ? 'show' :'').'" id="sidebarContents">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "pages-list" ? 'sidebar-nav-active' :'').'" href="/admin/pages/">'.$icon['files'].' '.$lang['nav_btn_pages'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "snippets" ? 'sidebar-nav-active' :'').'" href="/admin/snippets/">'.$icon['card_heading'].' '.$lang['snippets'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "shortcodes" ? 'sidebar-nav-active' :'').'" href="/admin/shortcodes/">'.$icon['code'].' Shortcodes</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';


echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarPosts" aria-expanded="false" aria-controls="sidebarPosts" class="sidebar-nav-link d-block color-blog">';
echo $icon['file_earmark_post'];
echo '<span>'.$lang['nav_btn_blog'].'</span>';
echo '</a>';
echo '<div class="collapse '.($maininc == "inc.blog" ? 'show' :'').'" id="sidebarPosts">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "blog-list" ? 'sidebar-nav-active' :'').'" href="/admin/blog/">'.$icon['files'].' '.$lang['overview'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "blog-edit" ? 'sidebar-nav-active' :'').'" href="/admin/posts/new/">'.$icon['plus'].' '.$lang['btn_new'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';


echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarShop" aria-expanded="false" aria-controls="sidebarShop" class="sidebar-nav-link d-block color-shop">';
echo $icon['store'];
echo '<span>'.$lang['nav_btn_shop'].'</span>';
echo '</a>';
echo '<div class="collapse '.($maininc == "inc.shop" ? 'show' :'').'" id="sidebarShop">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "shop-list" ? 'sidebar-nav-active' :'').'" href="/admin/shop/">'.$icon['files'].' '.$lang['nav_btn_products'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "shop-prices" ? 'sidebar-nav-active' :'').'" href="/admin/shop/prices/">'.$icon['cash_stack'].' '.$lang['nav_btn_price_groups'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "shop-features" ? 'sidebar-nav-active' :'').'" href="/admin/shop/features/">'.$icon['star_outline'].' '.$lang['nav_btn_features'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "shop-filter" ? 'sidebar-nav-active' :'').'" href="/admin/shop/filters/">'.$icon['filter'].' '.$lang['filter'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "shop-orders" ? 'sidebar-nav-active' :'').'" href="/admin/shop/orders/">'.$icon['cart'].' '.$lang['nav_btn_orders'].'</a></li>';
echo '<li><a class="sidebar-nav" href="/admin/settings/shop/">'.$icon['gear'].' '.$lang['nav_btn_settings'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarEvents" aria-expanded="false" aria-controls="sidebarEvents" class="sidebar-nav-link d-block color-events">';
echo $icon['calendar_event'];
echo '<span>'.$lang['nav_btn_events'].'</span>';
echo '</a>';
echo '<div class="collapse '.($maininc == "inc.events" ? 'show' :'').'" id="sidebarEvents">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "events-list" ? 'sidebar-nav-active' :'').'" href="/admin/events/">'.$icon['files'].' '.$lang['overview'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "bookings" ? 'sidebar-nav-active' :'').'" href="/admin/events/bookings/">'.$icon['calendar_check'].' '.$lang['nav_btn_bookings'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';



/**
 * addons
 */

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarAddons" aria-expanded="false" aria-controls="sidebarAddons" class="sidebar-nav-link d-block color-addons">';
echo $icon['plugin'];
echo '<span>'.$lang['nav_btn_addons'].'</span>';
echo '</a>';

echo '<div class="collapse '.($tn == "addons" ? 'show' :'').'" id="sidebarAddons">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "list" ? 'sidebar-nav-active' :'').'" href="/admin/addons/">'.$icon['files'].$lang['overview'].'</a></li>';

/* loop trough modules */
$cnt_mods = count($all_mods);
for($i=0;$i<$cnt_mods;$i++) {
    unset($mod);
    $modFolder = $all_mods[$i]['folder'];
    echo '<li>';
    echo '<a class="sidebar-nav '.($sub == "$modFolder" ? 'sidebar-nav-active' :'').'" href="?tn=addons&sub='.$modFolder.'&a=start">'.$icon['plus'].basename($modFolder,'.mod').'</a>';

    if($sub == "$modFolder") {
        echo '<ul>';
        include SE_CONTENT.'/modules/'.$modFolder.'/info.inc.php';
        $cnt_modnav = count($modnav);

        for($x=0;$x<$cnt_modnav;$x++) {
            $showlink = $modnav[$x]['link'];
            $incpage = $modnav[$x]['file'];

            if($a == $incpage) {
                $sub_link_class = "sidebar-sub-active";
            } else {
                $sub_link_class = "sidebar-sub";
            }

            echo '<li><a class="'.$sub_link_class.'" href="acp.php?tn=addons&sub='.$modFolder.'&a='.$incpage.'">'.$showlink.'</a></li>';

        }
        echo '</ul>';
    }

    echo '</li>';
}

echo '</ul>';
echo '</div>';
echo '</li>';

echo '<li class="sidebar-nav-item">';
echo '<a class="sidebar-nav-link '.($tn == "filebrowser" ? 'sidebar-nav-active' :'').' color-uploads" href="/admin/uploads/">';
echo $icon['folder'];
echo '<span>'.$lang['nav_btn_uploads'].'</span></a></li>';


echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarReactions" aria-expanded="false" aria-controls="sidebarReactions" class="sidebar-nav-link d-block color-reactions">';
echo $icon['inbox'];
echo '<span>'.$lang['nav_btn_inbox'].'</span>';
echo '</a>';
echo '<div class="collapse '.($tn == "inbox" ? 'show' :'').'" id="sidebarReactions">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "mailbox" ? 'sidebar-nav-active' :'').'" href="/admin/inbox/">'.$icon['envelope'].' '.$lang['nav_btn_mails'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "comments" ? 'sidebar-nav-active' :'').'" href="/admin/inbox/comments/">'.$icon['chat_square_dots'].' '.$lang['nav_btn_comments'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "votings" ? 'sidebar-nav-active' :'').'" href="/admin/inbox/reactions/">'.$icon['thumbs_up'].' '.$lang['nav_btn_reactions'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarUser" aria-expanded="false" aria-controls="sidebarPosts" class="sidebar-nav-link d-block color-user">';
echo $icon['people'];
echo '<span>'.$lang['nav_btn_user'].'</span>';
echo '</a>';
echo '<div class="collapse '.($tn == "user" ? 'show' :'').'" id="sidebarUser">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "user-list" ? 'sidebar-nav-active' :'').'" href="/admin/users/">'.$icon['user'].' '.$lang['overview'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "user-groups" ? 'sidebar-nav-active' :'').'" href="/admin/users/groups/">'.$icon['user_friends'].' '.$lang['nav_btn_user_groups'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '<li class="sidebar-nav-item">';
echo '<a class="sidebar-nav-link '.($maininc == "inc.categories" ? 'sidebar-nav-active' :'').'" href="/admin/categories/">';
echo $icon['bookmarks_fill'];
echo '<span>'.$lang['categories'].'</span></a></li>';

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarPrefs" aria-expanded="false" aria-controls="sidebarPrefs" class="sidebar-nav-link d-block">';
echo $icon['gear'];
echo '<span>'.$lang['nav_btn_settings'].'</span>';
echo '</a>';
echo '<div class="collapse '.($maininc == "inc.settings" ? 'show' :'').'" id="sidebarPrefs">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "general" ? 'sidebar-nav-active' :'').'" href="/admin/settings/">'.$icon['arrow_right_short'].' '.$lang['nav_btn_general'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "posts" ? 'sidebar-nav-active' :'').'" href="/admin/settings/posts/">'.$icon['file_earmark_post'].' '.$lang['nav_btn_posts'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "shop" ? 'sidebar-nav-active' :'').'" href="/admin/settings/shop/">'.$icon['store'].' '.$lang['nav_btn_shop'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "events" ? 'sidebar-nav-active' :'').'" href="/admin/settings/events/">'.$icon['calendar_event'].' '.$lang['nav_btn_events'].'</a></li>';
echo '</ul>';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "labels" ? 'sidebar-nav-active' :'').'" href="/admin/settings/labels/">'.$icon['tags_fill'].' '.$lang['labels'].'</a></li>';
echo '</ul>';
echo '<ul>';
echo '<li class="mt-2"><a class="sidebar-nav '.($sub == "customize" ? 'sidebar-nav-active' :'').'" href="/admin/settings/database/">'.$icon['database'].' '.$lang['nav_btn_customize_db'].'</a></li>';
echo '</ul>';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "update" ? 'sidebar-nav-active' :'').'" href="/admin/settings/update/">'.$icon['arrow_repeat'].' '.$lang['btn_update'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '</ul>';


echo '<div class="sidebar-footer">';
echo '<ul class="nav">';
echo '<li class="sidebar-nav-item"><a href="/">'.$icon['home'].' '.$lang['nav_btn_homepage'].'</a></li>';
echo '<li class="sidebar-nav-item"><a href="/?goto=logout">'.$icon['logout'].' '.$lang['nav_btn_logout'].'</a></li>';
echo '</ul>';
echo '</div>';