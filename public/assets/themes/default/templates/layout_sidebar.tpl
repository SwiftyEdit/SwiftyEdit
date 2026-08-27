{include file='header.tpl'}

<div class="container">
    <div class="row">
        {* single product view (products-display.tpl) has no use for the sidebar
           (category nav, shop filters, page TOC) - skip it and give content the
           full width instead of a column it can't fill *}
        <div class="{if isset($product_id)}col-12{else}col-lg-9{/if}">
            {include file='content.tpl'}
        </div>
        {if !isset($product_id)}
        <div class="col">

            {include file='sidebar.tpl'}

        </div>
        {/if}
    </div>
</div>

{include file='footer.tpl'}
