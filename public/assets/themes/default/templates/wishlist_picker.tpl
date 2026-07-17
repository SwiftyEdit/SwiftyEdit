{if $picker_message != ''}
    <div class="alert alert-success">{$picker_message}</div>
{/if}

{if is_array($picker_lists) && $picker_lists|@count > 0}
    <form hx-post="/xhr/se/wishlist/" hx-target="#wishlist-picker-modal-body" hx-swap="innerHTML">
        {$hidden_csrf_token}
        <input type="hidden" name="product_id" value="{$picker_product_id}">
        <input type="hidden" name="product_href" value="{$picker_product_href}">

        <div class="list-group mb-3">
            {foreach $picker_lists as $list}
                <label class="list-group-item d-flex align-items-center gap-2">
                    <input type="radio" class="form-check-input flex-shrink-0" name="wishlist_id" value="{$list.id}" {if $list@first}checked{/if}>
                    <span class="flex-grow-1">{$list.name} <small class="text-muted">({$list.item_count} {$lang_label_wishlist_item_count})</small></span>
                </label>
            {/foreach}
        </div>

        <button type="submit" class="btn btn-outline-danger w-100 mb-3" name="add_wishlist_item" value="{$picker_product_id}">
            {$lang_btn_add_to_wishlist}
        </button>
    </form>
{/if}

<form class="input-group" hx-post="/xhr/se/wishlist/" hx-target="#wishlist-picker-modal-body" hx-swap="innerHTML">
    {$hidden_csrf_token}
    <input type="hidden" name="product_id" value="{$picker_product_id}">
    <input type="hidden" name="product_href" value="{$picker_product_href}">
    <input type="text" class="form-control" name="wishlist_name" placeholder="{$lang_btn_new_wishlist_placeholder}" required>
    <button type="submit" class="btn btn-primary" name="create_wishlist" value="1">
        {$lang_btn_create_wishlist}
    </button>
</form>
