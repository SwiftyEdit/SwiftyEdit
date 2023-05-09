{if is_array($product_filter) }
    <div class="list-group">
        {foreach $product_filter as $groups}
            <div class="card mb-1">
                <div class="card-header">{$groups.title}</div>
                <div class="list-group">
                    {foreach $groups.items as $item}
                        <a class="list-group-item list-group-item-action {$item.class}"
                           href="?set_filter={$item.id}">{$item.title}</a>
                    {/foreach}
                </div>
            </div>
        {/foreach}
    </div>
{/if}