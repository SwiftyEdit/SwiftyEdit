<div class="tagged-archive">

    {if $tag_slug == ''}

        <h1>{$lang_tagged_index_title}</h1>

        {if $tag_index}
            <ul class="list-inline tagged-archive-index">
                {foreach $tag_index as $item}
                    {* tag-weight-1 (least used) .. tag-weight-5 (most used) - hook for
                       theme CSS to size/emphasize tags by popularity, e.g.:
                       .tag-weight-1 { font-size: .8rem; } .tag-weight-5 { font-size: 1.6rem; } *}
                    <li class="list-inline-item tag-weight-{$item.tag_weight}">
                        <a href="{$item.tag_href}" class="btn btn-sm btn-link">#{$item.tag_title}</a>
                        <span class="text-muted small">({$item.tag_usage})</span>
                    </li>
                {/foreach}
            </ul>
        {else}
            <p class="text-muted">{$lang_msg_no_entries_found}</p>
        {/if}

    {else}

        <h1>#{$tag_name}</h1>

        {if $tagged_pages == false && $tagged_posts == false && $tagged_products == false && $tagged_events == false}
            <p class="text-muted">{$lang_msg_no_entries_found}</p>
        {/if}

        {if $tagged_pages}
            <h2>{$lang_tagged_section_pages}</h2>
            <ul class="list-unstyled tagged-archive-pages">
                {foreach $tagged_pages as $item}
                    <li>
                        <a href="{$item.page_href}">{$item.page_title}</a>
                    </li>
                {/foreach}
            </ul>
        {/if}

        {if $tagged_posts}
            <h2>{$lang_tagged_section_posts}</h2>
            <ul class="list-unstyled tagged-archive-posts">
                {foreach $tagged_posts as $item}
                    <li>
                        <span class="text-muted small">{$item.tagged_date}</span>
                        <a href="{$item.post_href}">{$item.post_title}</a>
                    </li>
                {/foreach}
            </ul>
        {/if}

        {if $tagged_products}
            <h2>{$lang_tagged_section_products}</h2>
            <ul class="list-unstyled tagged-archive-products">
                {foreach $tagged_products as $item}
                    <li>
                        <a href="{$item.product_href}">{$item.title}</a>
                    </li>
                {/foreach}
            </ul>
        {/if}

        {if $tagged_events}
            <h2>{$lang_tagged_section_events}</h2>
            <ul class="list-unstyled tagged-archive-events">
                {foreach $tagged_events as $item}
                    <li>
                        <span class="text-muted small">{$item.tagged_date}</span>
                        <a href="{$item.event_href}">{$item.title}</a>
                    </li>
                {/foreach}
            </ul>
        {/if}

    {/if}

</div>
