<div id="article_list_header">

    <div class="d-flex justify-content-between align-items-center mb-1">
        <div>
            <div class="btn-group" role="group" title="{$lang_sort}">
                <a href="{$sort_urls.default}"
                   class="btn btn-sm btn-outline-secondary {if !$class_sort_name && !$class_sort_topseller && !$class_sort_price_asc && !$class_sort_price_desc}active{/if}">
                    {$lang_label_sort_relevance}
                </a>
                <a href="{$sort_urls.name}"
                   class="btn btn-sm btn-outline-secondary {$class_sort_name}">
                    A-Z
                </a>
                <a href="{$sort_urls.pasc}"
                   class="btn btn-sm btn-outline-secondary {$class_sort_price_asc}">
                    {$lang_label_price} ↑
                </a>
                <a href="{$sort_urls.pdesc}"
                   class="btn btn-sm btn-outline-secondary {$class_sort_price_desc}">
                    {$lang_label_price} ↓
                </a>
                <a href="{$sort_urls.ts}"
                   class="btn btn-sm btn-outline-secondary {$class_sort_topseller}">
                    {$lang_label_sort_topseller}
                </a>
            </div>
        </div>
        <div>
            {$nbr_products} {$lang_label_products}
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-1">
        {if $has_active_filters}
            <div class="active-filters-bar">
                <div class="filter-tags">
                    {foreach $active_filter_tags as $tag}
                        <a href="{$tag.remove_url}" class="btn btn-sm btn-outline-secondary">
                            <span class="filter-remove"><i class="bi bi-x-circle"></i></span>
                            <span class="filter-label small">{$tag.filter_title}:</span>
                            <span class="filter-value">{$tag.display}</span>
                        </a>
                    {/foreach}

                    {* "Alle zurücksetzen" Link *}
                    <a href="{$page_slug}" class="clear-all-filters btn btn-sm btn-outline-secondary">
                        {$lang_reset}
                    </a>
                </div>
            </div>
        {/if}
        {if $show_pagination}
            <div class="flex-fill">
            <nav aria-label="Pagination" class="mt-4">
                <ul class="pagination pagination-sm justify-content-end">
                    <li class="page-item {if $disable_prev_link}disabled{/if}">
                        <a class="page-link" href="{$filter_base_url}{$pag_prev_href}">«</a>
                    </li>

                    {foreach $pagination as $page}
                        <li class="page-item {$page.active_class}">
                            <a class="page-link" href="{$filter_base_url}{$page.href}">{$page.nbr}</a>
                        </li>
                    {/foreach}

                    <li class="page-item {if $disable_next_link}disabled{/if}">
                        <a class="page-link" href="{$filter_base_url}{$pag_next_href}">»</a>
                    </li>
                </ul>
            </nav>
            </div>
        {/if}
    </div>
</div>

{if $show_products_list == true}

{foreach $products as $product => $value}


    <div class="product-list-entry {$value.product_css_classes}">
        {$value.draft_message}
        <div class="row">
            {if $value.product_img_src != ''}
            <div class="col-md-4">
                <div class="teaser-image">
                    <img src="{$value.product_img_src}" class="img-fluid">
                </div>
            </div>
            {/if}
            <div class="col">

                <!-- pricetag -->
                {if $value.product_pricetag_mode !== 2}
                <div class="price-tag price-tag-fix">
                    <div class="clearfix">
                        <div class="price-tag-label">{$post_product_price_label}</div>
                    </div>
                    <div class="price-tag-inner">
                        <div class="price">{$value.price_tag_label_from} {$value.price_tag}</div>
                        {$value.product_currency} <span class="product-amount">{$value.product_amount}</span> <span class="product-unit">{$value.product_unit}</span>
                    </div>
                </div>
                {/if}
                <!-- pricetag end -->

                <span class="post-author">{$value.product_author}</span> <span class="post-releasedate">{$pvalue.product_releasedate}</span>
                <a class="post-headline-link" href="{$value.product_href}"><h3>{$value.product_title}</h3></a>
                {$value.product_teaser}
            </div>
        </div>
        <div class="row mt-1 mb-3">
            <div class="col-md-4">
                {if $value.show_voting == true}
                <button class="btn btn-sm btn-outline-secondary" name="upvote" onclick="vote(this.value)" value="up-post-{$value.product_id}" {$value.votes_status_up}>
                    <i class="bi bi-hand-thumbs-up-fill"></i> <span id="vote-up-nbr-{$value.product_id}">{$value.votes_up}</span>
                </button>
                <button class="btn btn-sm btn-outline-secondary" name="dnvote" onclick="vote(this.value)" value="dn-post-{$value.product_id}" {$value.votes_status_dn}>
                    <i class="bi bi-hand-thumbs-down-fill"></i> <span id="vote-dn-nbr-{$value.product_id}">{$value.votes_dn}</span>
                </button>
                {/if}
            </div>
            <div class="col-md-8">

                <div class="row">
                    <div class="col-md-8">
                        {if $value.variants_alert != ''}
                            {$value.variants_alert}
                        {/if}
                    </div>
                    <div class="col-md-4">
                        <a class="btn btn-link w-100 {$link_classes}" href="{$value.product_href}">{$btn_read_more}</a>
                        {if $value.show_shopping_cart == true}
                            <form action="{$form_action}" method="POST" class="pt-1">
                                <button class="btn btn-outline-success w-100" name="add_to_cart" value="{$value.product_id}">{$btn_add_to_cart}</button>
                                <input type="hidden" name="product_href" value="{$value.product_href}">
                                {$hidden_csrf_token}
                            </form>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
        <div class="m-0 post-categories text-end">
            {foreach $value.product_categories as $category}
                <a href="{$category.cat_href}" class="btn btn-sm btn-link" title="{$category.cat_title}">{$category.cat_title}</a>
            {/foreach}
        </div>
    </div>

 {/foreach}

{else}
    <div class="alert alert-info">
        {$lang_msg_no_products_found}
    </div>
{/if}

<div class="product-list-footer">
    {if $show_pagination}
        <nav aria-label="Pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item {if $disable_prev_link}disabled{/if}">
                    <a class="page-link" href="{$filter_base_url}{$pag_prev_href}">«</a>
                </li>

                {foreach $pagination as $page}
                    <li class="page-item {$page.active_class}">
                        <a class="page-link" href="{$filter_base_url}{$page.href}">{$page.nbr}</a>
                    </li>
                {/foreach}

                <li class="page-item {if $disable_next_link}disabled{/if}">
                    <a class="page-link" href="{$filter_base_url}{$pag_next_href}">»</a>
                </li>
            </ul>
        </nav>
    {/if}
</div>