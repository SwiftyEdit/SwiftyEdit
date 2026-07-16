<form class="input-group mb-3"
      hx-post="/xhr/se/wishlist/"
      hx-target="#wishlist-list-column"
      hx-swap="innerHTML">
    {$hidden_csrf_token}
    <input type="text" class="form-control" name="wishlist_name" placeholder="{$lang_btn_new_wishlist_placeholder}" required>
    <button type="submit" class="btn btn-primary" name="create_wishlist" value="1">
        {$lang_btn_create_wishlist}
    </button>
</form>

{if is_array($wishlists) && $wishlists|@count > 0}
    <div class="list-group">
        {foreach $wishlists as $list}
            <button type="button" class="list-group-item list-group-item-action"
                    hx-get="/xhr/se/wishlist/?form=detail&id={$list.id}"
                    hx-target="#wishlist-detail-panel"
                    hx-swap="innerHTML">
                {$list.name}
                <span class="badge bg-secondary float-end">{$list.item_count} {$lang_label_wishlist_item_count}</span>
                {if $list.is_public == 1}
                    <span class="badge bg-success float-end me-1">{$lang_label_wishlist_visibility}</span>
                {/if}
            </button>
        {/foreach}
    </div>
{else}
    <p class="text-muted">{$lang_label_no_wishlists}</p>
{/if}
