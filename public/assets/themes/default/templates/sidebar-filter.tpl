{* templates/products-list.tpl - Filter Sidebar *}

{if is_array($product_filter)}
<aside class="shop-sidebar">

    {foreach $product_filter as $filter_group}
        <div class="card mb-1">
            <div class="card-header fw-bold">
            {$filter_group.title}

            {if $filter_group.description}
                <span data-bs-toggle="tooltip" data-bs-title="{$filter_group.description}" data-bs-html="true">
                    <i class="bi-info-circle"></i>
                </span>
            {/if}
            </div>


            {* Radio Buttons (input_type = 1) *}
            {if $filter_group.input_type == 1}
                <div class="card-body">

                    <a href="{$filter_group.clear_url}"
                       class="filter-option text-decoration-none {if !$filter_group.has_active}active{/if}">
                        <i class="bi bi-circle{if !$filter_group.has_active}-fill{/if} me-2"></i>
                        {$lang_btn_all}
                    </a>

                    {foreach $filter_group.items as $item}
                        {if $item.slug != ''}
                            <a href="{$item.filter_url}"
                               class="filter-option d-block py-1 text-decoration-none {if $item.checked}active fw-bold{/if}">
                                <i class="bi bi-circle{if $item.checked}-fill{/if} me-2"></i>
                                {$item.title}
                                <span class="text-muted small ms-1">
                                    (<span hx-get="/xhr/se/counter/?filter={$item.id}&categories={$cat_hashes}" hx-trigger="load">0</span>)
                                </span>
                            </a>
                        {/if}
                    {/foreach}
                </div>
            {/if}

            {* Checkboxes (input_type = 2) *}
            {if $filter_group.input_type == 2}
                <div class="card-body">
                    {foreach $filter_group.items as $item}
                        {if $item.slug != ''}
                            <a href="{$item.filter_url}"
                               class="filter-option d-block py-1 text-decoration-none {if $item.checked}active fw-bold{/if}">
                                <i class="bi bi-{if $item.checked}check-{/if}square me-2"></i>
                                {$item.title}
                                <span class="text-muted small ms-1">
                                    (<span hx-get="/xhr/se/counter/?filter={$item.id}&categories={$cat_hashes}" hx-trigger="load">0</span>)
                                </span>
                            </a>
                        {/if}
                    {/foreach}
                </div>
            {/if}

            {* Range Slider (input_type = 3) *}
            {if $filter_group.input_type == 3}
                <div class="filter-range">
                    <div id="range-{$filter_group.slug}"
                         class="range-slider mb-3"
                         data-filter-slug="{$filter_group.slug}"
                         data-min="{$filter_group.range_min}"
                         data-max="{$filter_group.range_max}"
                         data-current-min="{$filter_group.current_min}"
                         data-current-max="{$filter_group.current_max}">
                    </div>

                    <div class="range-display text-center">
                <span id="range-{$filter_group.slug}-display" class="badge bg-secondary">
                    {$filter_group.current_min} - {$filter_group.current_max}
                </span>
                    </div>
                </div>
            {/if}
        </div>
    {/foreach}
</aside>
{/if}