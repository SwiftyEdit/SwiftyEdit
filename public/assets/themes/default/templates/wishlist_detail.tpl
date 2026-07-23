<div hx-get="/xhr/se/wishlist/?form=detail&id={$wishlist.id}" hx-trigger="update_wishlist_detail from:body" hx-swap="outerHTML">

    <div class="card mb-3">
        <div class="card-body">

            <form class="mb-2"
                  hx-post="/xhr/se/wishlist/"
                  hx-swap="none">
                {$hidden_csrf_token}
                <input type="hidden" name="wishlist_id" value="{$wishlist.id}">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="wishlist_name" value="{$wishlist.name|escape}" required>
                    <button type="submit" class="btn btn-outline-secondary" name="rename_wishlist" value="1">
                        {$lang_update}
                    </button>
                </div>
                <textarea class="form-control" name="wishlist_description" rows="2"
                          placeholder="{$lang_label_product_description}">{$wishlist.description|escape}</textarea>
            </form>

            <div class="form-check form-switch mb-2">
                <input type="checkbox" class="form-check-input" role="switch" id="wishlist-visibility-{$wishlist.id}"
                       {if $wishlist.is_public == 1}checked{/if}
                       hx-post="/xhr/se/wishlist/"
                       hx-vals='{ldelim}"toggle_wishlist_public": 1, "wishlist_id": {$wishlist.id}, "is_public": {if $wishlist.is_public == 1}0{else}1{/if}{rdelim}'
                       hx-include="[name='csrf_token']"
                       hx-swap="none">
                <label class="form-check-label" for="wishlist-visibility-{$wishlist.id}">{$lang_label_wishlist_visibility}</label>
            </div>

            {if $wishlist.is_public == 1}
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="wishlist-share-url-{$wishlist.id}" readonly value="{$wishlist_share_url}">
                    <button type="button" class="btn btn-outline-secondary" data-copy-target="wishlist-share-url-{$wishlist.id}" onclick="copyWishlistLink(this)">
                        <i class="bi bi-clipboard"></i> {$lang_btn_copy_wishlist_link}
                    </button>
                    <a href="{$wishlist_share_url}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right"></i> {$lang_btn_open_wishlist}
                    </a>
                </div>
                <p class="text-muted small">{$lang_label_wishlist_public_notice}</p>
            {/if}

            <form>
                {$hidden_csrf_token}
                <button type="button" class="btn btn-outline-danger btn-sm" name="delete_wishlist" value="{$wishlist.id}"
                        hx-post="/xhr/se/wishlist/"
                        hx-target="#wishlist-detail-panel"
                        hx-swap="innerHTML"
                        hx-confirm="{$lang_btn_delete_wishlist}?">
                    {$lang_btn_delete_wishlist}
                </button>
            </form>

        </div>
    </div>

    {if is_array($wishlist_items) && $wishlist_items|@count > 0}
        <ul class="list-group wishlist-sortable" data-wishlist-id="{$wishlist.id}">
            {foreach $wishlist_items as $item}
                <li class="list-group-item d-flex align-items-center gap-2" data-item-id="{$item.id}">
                    <span class="wishlist-drag-handle" style="cursor:grab;"><i class="bi bi-grip-vertical"></i></span>
                    {if $item.product.product_img_src != ''}
                        <a href="{$item.product_href}" class="flex-shrink-0">
                            <img src="{$item.product.product_img_src}" alt="{$item.product.title|escape}" style="width:60px;height:60px;object-fit:cover;">
                        </a>
                    {/if}
                    <span class="flex-grow-1">
                        <a href="{$item.product_href}">{$item.product.title}</a>
                        {if $item.product.product_teaser != ''}
                            <div class="text-muted small">{$item.product.product_teaser}</div>
                        {/if}
                        {if $item.product.product_pricetag_mode !== 2}
                            <div class="small">
                                {if $item.product.price_tag_label_from != ''}{$item.product.price_tag_label_from}{/if}
                                {$item.product.price_tag} {$item.product.product_currency}
                            </div>
                        {/if}
                    </span>
                    <form class="m-0">
                        {$hidden_csrf_token}
                        <button type="button" class="btn btn-outline-danger btn-sm" name="remove_wishlist_item" value="{$item.id}"
                                hx-post="/xhr/se/wishlist/"
                                hx-swap="none">
                            {$lang_btn_remove_from_wishlist}
                        </button>
                    </form>
                </li>
            {/foreach}
        </ul>
    {else}
        <p class="text-muted">{$lang_label_wishlist_empty}</p>
    {/if}

</div>
