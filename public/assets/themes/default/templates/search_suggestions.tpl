<div id="{$container_id}" class="dropdown-menu search-suggestions{if $variant == 'modal'} search-suggestions-modal{/if}{if $is_open} show{/if}">
{if $search_undersized_msg}
<div class="dropdown-item-text text-muted small">{$search_undersized_msg}</div>
{elseif $is_open}
    {if $pages_total == 0 && $products_total == 0 && $posts_total == 0 && $events_total == 0}
    <div class="dropdown-item-text text-muted small">{$msg_no_search_results}</div>
    {else}
        {if $pages_total > 0}
        <h6 class="dropdown-header">{$lang_tagged_section_pages} ({$pages_total})</h6>
            {foreach $pages as $page}
            <a class="dropdown-item" href="{$page.href}">{$page.title}</a>
            {/foreach}
        {/if}
        {if $products_total > 0}
            {if $pages_total > 0}<div class="dropdown-divider"></div>{/if}
            <h6 class="dropdown-header">{$lang_tagged_section_products} ({$products_total})</h6>
            {foreach $products as $product}
            <a class="dropdown-item d-flex justify-content-between align-items-center gap-2" href="{$product.href}">
                <span class="search-result-title">{$product.title}</span>
                <span class="text-muted small text-nowrap">{if $product.price_tag_label_from != ''}{$product.price_tag_label_from} {/if}{$product.price_tag} {$product.product_currency}</span>
            </a>
            {/foreach}
        {/if}
        {if $posts_total > 0}
            {if $pages_total > 0 || $products_total > 0}<div class="dropdown-divider"></div>{/if}
            <h6 class="dropdown-header">{$lang_tagged_section_posts} ({$posts_total})</h6>
            {foreach $posts as $post}
            <a class="dropdown-item" href="{$post.href}">{$post.title}</a>
            {/foreach}
        {/if}
        {if $events_total > 0}
            {if $pages_total > 0 || $products_total > 0 || $posts_total > 0}<div class="dropdown-divider"></div>{/if}
            <h6 class="dropdown-header">{$lang_tagged_section_events} ({$events_total})</h6>
            {foreach $events as $event}
            <a class="dropdown-item" href="{$event.href}">{$event.title}</a>
            {/foreach}
        {/if}
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-center small" href="{$search_uri}?s={$search_string|escape:'url'}">{$lang_btn_show_all_results}</a>
    {/if}
{/if}
</div>
