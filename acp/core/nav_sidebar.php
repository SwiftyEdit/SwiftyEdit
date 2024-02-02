<?php


/**
 * sidebar for pages, snippets
 */

echo '<a href="?tn=dashboard" class="d-block px-4 py-3" title="Dashboard">';
echo '<img src="images/swiftyedit_bright.svg" width="100%" class="mx-auto">';
echo '</a>';


echo '<ul class="nav">';




echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarContents" aria-expanded="false" aria-controls="sidebarContents" class="sidebar-nav-link d-block color-contents">';
echo $icon['diagram_3'];
echo '<span>'.$lang['tn_contents'].'</span>';
echo '</a>';

echo '<div class="collapse '.($tn == "pages" ? 'show' :'').'" id="sidebarContents">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "pages-list" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=pages&sub=pages-list">'.$icon['files'].' '.$lang['page_list'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "snippets" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=pages&sub=snippets">'.$icon['card_heading'].' '.$lang['nav_snippets'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "shortcodes" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=pages&sub=shortcodes">'.$icon['code'].' Shortcodes</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "pages-index" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=pages&sub=pages-index">'.$icon['database'].' '.$lang['page_index'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "rss" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=pages&sub=rss">'.$icon['rss'].' RSS/XML</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';


echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarPosts" aria-expanded="false" aria-controls="sidebarPosts" class="sidebar-nav-link d-block color-blog">';
echo $icon['file_earmark_post'];
echo '<span>'.$lang['tn_posts'].'</span>';
echo '</a>';
echo '<div class="collapse '.($tn == "posts" ? 'show' :'').'" id="sidebarPosts">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "blog-list" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=posts&sub=blog-list">'.$icon['files'].' '.$lang['nav_overview'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "blog-edit" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=posts&sub=blog-edit">'.$icon['plus'].' '.$lang['nav_new'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';


echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarShop" aria-expanded="false" aria-controls="sidebarShop" class="sidebar-nav-link d-block color-shop">';
echo $icon['store'];
echo '<span>'.$lang['tn_shop'].'</span>';
echo '</a>';
echo '<div class="collapse '.($tn == "shop" ? 'show' :'').'" id="sidebarShop">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "shop-list" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=shop&sub=shop-list">'.$icon['files'].' '.$lang['nav_products'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "shop-prices" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=shop&sub=shop-prices">'.$icon['cash_stack'].' '.$lang['nav_price_groups'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "shop-features" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=shop&sub=shop-features">'.$icon['star_outline'].' '.$lang['nav_features'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "shop-filter" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=shop&sub=shop-filter">'.$icon['filter'].' '.$lang['nav_filter'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "shop-orders" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=shop&sub=shop-orders">'.$icon['cart'].' '.$lang['nav_orders'].'</a></li>';
echo '<li><a class="sidebar-nav" href="?tn=system&sub=shop">'.$icon['gear'].' '.$lang['nav_preferences'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarEvents" aria-expanded="false" aria-controls="sidebarEvents" class="sidebar-nav-link d-block color-events">';
echo $icon['calendar_event'];
echo '<span>'.$lang['tn_events'].'</span>';
echo '</a>';
echo '<div class="collapse '.($tn == "events" ? 'show' :'').'" id="sidebarEvents">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "events-list" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=events&sub=events-list">'.$icon['files'].' '.$lang['nav_overview'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "bookings" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=events&sub=bookings">'.$icon['calendar_check'].' '.$lang['nav_bookings'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';



/**
 * addons
 */

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarAddons" aria-expanded="false" aria-controls="sidebarAddons" class="sidebar-nav-link d-block color-addons">';
echo $icon['plugin'];
echo '<span>'.$lang['nav_addons'].'</span>';
echo '</a>';

echo '<div class="collapse '.($tn == "addons" ? 'show' :'').'" id="sidebarAddons">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "list" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=addons&sub=list">'.$icon['files'].$lang['nav_overview'].'</a></li>';

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
echo '<a class="sidebar-nav-link '.($tn == "filebrowser" ? 'sidebar-nav-active' :'').' color-uploads" href="acp.php?tn=filebrowser&sub=browse">';
echo $icon['folder'];
echo '<span>'.$lang['tn_filebrowser'].'</span></a></li>';


echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarReactions" aria-expanded="false" aria-controls="sidebarReactions" class="sidebar-nav-link d-block color-reactions">';
echo $icon['inbox'];
echo '<span>'.$lang['nav_inbox'].'</span>';
echo '</a>';
echo '<div class="collapse '.($tn == "inbox" ? 'show' :'').'" id="sidebarReactions">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "mailbox" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=inbox&sub=mailbox">'.$icon['envelope'].' '.$lang['nav_mailbox'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "comments" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=inbox&sub=comments">'.$icon['chat_square_dots'].' '.$lang['reactions_comments'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "votings" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=inbox&sub=votings">'.$icon['thumbs_up'].' '.$lang['reactions_votings'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarUser" aria-expanded="false" aria-controls="sidebarPosts" class="sidebar-nav-link d-block color-user">';
echo $icon['people'];
echo '<span>'.$lang['nav_user'].'</span>';
echo '</a>';
echo '<div class="collapse '.($tn == "user" ? 'show' :'').'" id="sidebarUser">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "user-list" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=user&sub=user-list">'.$icon['user'].' '.$lang['nav_overview'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "user-groups" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=user&sub=user-groups">'.$icon['user_friends'].' '.$lang['nav_usergroups'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '<li class="sidebar-nav-item">';
echo '<a class="sidebar-nav-link '.($tn == "categories" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=categories">';
echo $icon['bookmarks_fill'];
echo '<span>'.$lang['categories'].'</span></a></li>';

echo '<li class="sidebar-nav-item">';
echo '<a data-bs-toggle="collapse" href="#sidebarPrefs" aria-expanded="false" aria-controls="sidebarPrefs" class="sidebar-nav-link d-block">';
echo $icon['gear'];
echo '<span>'.$lang['nav_preferences'].'</span>';
echo '</a>';
echo '<div class="collapse '.($tn == "system" ? 'show' :'').'" id="sidebarPrefs">';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "general" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=system&sub=general">'.$icon['arrow_right_short'].' '.$lang['nav_general'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "posts" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=system&sub=posts">'.$icon['file_earmark_post'].' '.$lang['tn_posts'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "shop" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=system&sub=shop">'.$icon['store'].' '.$lang['tn_shop'].'</a></li>';
echo '<li><a class="sidebar-nav '.($sub == "events" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=system&sub=events">'.$icon['calendar_event'].' '.$lang['tn_events'].'</a></li>';
echo '</ul>';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "labels" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=system&sub=labels">'.$icon['tags_fill'].' '.$lang['labels'].'</a></li>';
echo '</ul>';
echo '<ul>';
echo '<li class="mt-2"><a class="sidebar-nav '.($sub == "customize" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=system&sub=customize">'.$icon['database'].' '.$lang['customize_database'].'</a></li>';
echo '</ul>';
echo '<ul>';
echo '<li><a class="sidebar-nav '.($sub == "update" ? 'sidebar-nav-active' :'').'" href="acp.php?tn=system&sub=update">'.$icon['arrow_repeat'].' '.$lang['system_update'].'</a></li>';
echo '</ul>';
echo '</div>';
echo '</li>';

echo '</ul>';


echo '<div class="sidebar-footer">';
echo '<ul class="nav">';
echo '<li class="sidebar-nav-item"><a href="../">'.$icon['home'].' '.$lang['back_to_page'].'</a></li>';
echo '<li class="sidebar-nav-item"><a href="../index.php?goto=logout">'.$icon['logout'].' '.$lang['logout'].'</a></li>';
echo '</ul>';
echo '</div>';