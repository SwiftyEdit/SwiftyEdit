<header id="pageHeader" class="mb-3">

    <div class="container pt-3 mb-3">

        {if $cnt_shopping_cart_items != ''}
            <div class="shopping-cart-container">
                <a href="{$shopping_cart_uri}" title="{$lang_label_shopping_cart}"><i
                            class="bi bi-cart-fill"></i> {$cnt_shopping_cart_items}</a>
            </div>
        {/if}

        {if is_array($legal_pages) }
            <div class="legal-pages-container">
                <ul>
                    {foreach item=pages from=$legal_pages}
                        <li><a class="" href="{$prefs_cms_base}{$pages.page_permalink}"
                               title="{$pages.page_title}">{$pages.page_linkname}</a></li>
                    {/foreach}
                </ul>
            </div>
        {/if}

        {if $social_media_block != ''}
            {include file='socialmedia.tpl'}
        {/if}

        <div class="row">

            {if {$page_logo} != ''}
                <div class="col-lg-2 col-sm-3 text-center text-sm-start">
                    <a href="/" title="{$prefs_pagetitle}"><img src="{$page_logo}" alt="Logo" title="{$prefs_pagetitle}"
                                                                class="img-fluid"></a>
                </div>
            {/if}

            <div class="col text-center text-sm-start">

                <p class="h1 mb-0">{$prefs_pagetitle}</p>
                <p class="h2">{$prefs_pagesubtitle}</p>
            </div>
        </div>
    </div>

    {include file='navigation.tpl'}

</header>