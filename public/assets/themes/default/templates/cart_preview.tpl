{if $cnt_items > 0}
    <ul class="list-unstyled mini-cart-items mb-3">
        {foreach $cart_items as $item}
            <li class="d-flex justify-content-between align-items-start gap-2 py-2 border-bottom{if $item.parent_id > 0} ps-3{/if}">
                <div>
                    {if $item.parent_id > 0}<i class="bi bi-arrow-return-right"></i>{/if}
                    <span class="fw-semibold">{$item.amount}&times;</span> {$item.title}
                    {if $item.options != ''}
                        <div class="small text-muted">{$item.options}</div>
                    {/if}
                    <div class="small">
                        {if $price_mode == 3}
                            {$currency} {$item.price_net_format}
                        {else}
                            {$currency} {$item.price_gross_format}
                        {/if}
                    </div>
                </div>
                <form hx-post="/xhr/se/cart/" hx-target="#mini-cart-body" hx-swap="innerHTML">
                    <input type="hidden" name="remove_from_cart" value="{$item.cart_id}">
                    {$hidden_csrf_token}
                    <button type="submit" class="btn btn-link btn-sm link-danger p-0" title="{$lang_button_remove_item}" aria-label="{$lang_button_remove_item}">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </li>
        {/foreach}
    </ul>

    <div class="d-flex justify-content-between fw-semibold mb-3">
        <span>{$lang_price_subtotal} <small class="text-muted">({if $price_mode == 3}{$lang_label_net}{else}{$lang_label_gross}{/if})</small></span>
        <span>{$currency} {if $price_mode == 3}{$subtotal_net_format}{else}{$subtotal_gross_format}{/if}</span>
    </div>

    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="offcanvas">{$lang_close}</button>
        <a href="{$shopping_cart_uri}" class="btn btn-success w-50">{$lang_button_view_cart}</a>
    </div>
{else}
    <p class="text-muted mb-0">{$lang_label_cart_empty}</p>
{/if}
