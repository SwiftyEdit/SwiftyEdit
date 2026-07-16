<h2>{$wishlist.name|escape}</h2>

{if is_array($wishlist_items) && $wishlist_items|@count > 0}
    <div class="row">
        {foreach $wishlist_items as $item}
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    {if $item.product.product_img_src != ''}
                        <img src="{$item.product.product_img_src}" class="card-img-top" alt="{$item.product.title|escape}">
                    {/if}
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><a href="{$item.product_href}">{$item.product.title}</a></h5>
                        {if $item.product.product_teaser != ''}
                            <p class="text-muted small">{$item.product.product_teaser}</p>
                        {/if}
                        {if $item.product.product_pricetag_mode !== 2}
                            <p class="mb-2">
                                {if $item.product.price_tag_label_from != ''}{$item.product.price_tag_label_from}{/if}
                                <strong>{$item.product.price_tag} {$item.product.product_currency}</strong>
                            </p>
                        {/if}
                        <form action="{$form_action}" method="POST" class="mt-auto" id="wishlist-cart-form-{$item.id}"
                              hx-post="/xhr/se/wishlist/"
                              hx-target="#wishlist-cart-form-{$item.id}"
                              hx-swap="outerHTML">
                            <button type="submit" class="btn btn-outline-success w-100" name="add_to_cart" value="{$item.product_id}">{$lang_btn_add_to_cart}</button>
                            <input type="hidden" name="product_href" value="{$item.product_href}">
                            {$hidden_csrf_token}
                        </form>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
{else}
    <p class="text-muted">{$lang_label_wishlist_empty}</p>
{/if}
