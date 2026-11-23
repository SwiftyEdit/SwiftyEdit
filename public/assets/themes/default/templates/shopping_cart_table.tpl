{*
 shopping cart, two columns:
 left  - items, addresses (or the request/guest forms) and the EU warranty notice
 right - totals, payment method, comment, terms and the order button (sticky on large screens)
*}

{assign var="show_totals" value=($checkout_error_msg == '')}

<div class="row g-4 mb-4">

    <div class="{if $show_totals}col-lg-8{else}col-12{/if}">
        <div class="card mb-4">
            <div class="card-header">{$cnt_items} {$lang_label_cnt_sc_items}</div>
            <div class="card-body p-0">
                <table class="table table-sc mb-0">
                    {* on small screens the number and price columns are hidden,
                       the line price is shown below the product instead *}
                    <tr>
                        <td class="d-none d-sm-table-cell">#</td>
                        <td>{$lang_label_product_info}</td>
                        <td class="text-end d-none d-sm-table-cell">{$lang_label_price}</td>
                        <td class="d-none d-sm-table-cell"></td>
                    </tr>

                    {foreach $cart_items as $item}
                        <tr{if $item.parent_id > 0} class="sc-item-addon"{/if}>
                            <td class="d-none d-sm-table-cell">{if $item.parent_id == 0}{$item.nbr}{else}<i class="bi bi-arrow-return-right"></i>{/if}</td>
                            <td>
                                <small class="text-muted">{$item.product_number}</small> <a href="{$item.slug}"
                                                                                            title="{$item.slug}">{$item.title}</a>
                                <div class="sc-items-options">{$item.options}</div>
                                {if $item.options_comment != ""}
                                    <div class="sc-items-options-comment">{$item.options_comment_label}:<br>{$item.options_comment}
                                    </div>
                                {/if}

                                {if is_array($item.guarantee_label)}
                                    {* EU label of the manufacturer's durability guarantee, opens in full *}
                                    <div class="sc-item-guarantee-label my-2">
                                        <a href="{$item.guarantee_label.src}" class="lightbox" title="{$lang_label_guarantee_label}">
                                            <img src="{$item.guarantee_label.src}" alt="{if $item.guarantee_label.alt != ''}{$item.guarantee_label.alt}{else}{$lang_label_guarantee_label}{/if}" class="border rounded" style="max-height: 80px;" loading="lazy">
                                        </a>
                                    </div>
                                {/if}

                                {if is_array($item.requires_missing) && $item.requires_missing|count > 0}
                                    <!-- products this item needs to work, not in the cart yet (hint only) -->
                                    <div class="sc-item-requires alert alert-warning small p-2 my-2">
                                        <p class="mb-1"><i class="bi bi-exclamation-triangle"></i> {$lang_msg_cart_requires_missing}</p>
                                        {foreach $item.requires_missing as $req}
                                            <div class="d-flex align-items-center gap-2 py-1">
                                                <a href="{$req.product_href}" class="flex-grow-1">{$req.title}</a>
                                                <span class="text-nowrap">{$req.price_tag_label_from} {$req.product_currency} {$req.price_tag}</span>
                                                {if $req.can_add && $req.as_addon && $item.parent_id == 0}
                                                    {* booked as an add-on of this line *}
                                                    <form action="{$shopping_cart_uri}" method="POST">
                                                        <button class="btn btn-sm btn-success" name="add_required_addon" value="{$req.id}"><i class="bi bi-cart-plus"></i> {$lang_btn_add_to_cart}</button>
                                                        <input type="hidden" name="cart_parent_id" value="{$item.cart_id}">
                                                        {$hidden_csrf_token}
                                                    </form>
                                                {elseif $req.can_add && !$req.as_addon}
                                                    <form action="{$shopping_cart_uri}" method="POST">
                                                        <button class="btn btn-sm btn-success" name="add_to_cart" value="{$req.id}"><i class="bi bi-cart-plus"></i> {$lang_btn_add_to_cart}</button>
                                                        <input type="hidden" name="product_href" value="{$req.product_href}">
                                                        {$hidden_csrf_token}
                                                    </form>
                                                {elseif $req.as_addon}
                                                    {* add-on with options - chosen on the page of the product that needs it *}
                                                    <a href="{$item.slug}" class="btn btn-sm btn-outline-secondary">{$lang_btn_open_product}</a>
                                                {else}
                                                    <a href="{$req.product_href}" class="btn btn-sm btn-outline-secondary">{$lang_btn_open_product}</a>
                                                {/if}
                                            </div>
                                        {/foreach}
                                    </div>
                                {/if}

                                <div class="row">
                                    <div class="col-12 col-sm-6">
                                        <p class="h6">{$lang_price_single}</p>
                                        {if $price_mode == 1}
                                            <p class="mb-1">
                                                <small class="text-muted">{$lang_label_gross}
                                                    :</small> {$currency} {$item.price_gross_single_format}
                                            </p>
                                        {/if}
                                        {if $price_mode == 2}
                                            <p><small class="text-muted">{$lang_label_net}
                                                    :</small> {$currency} {$item.price_net_single_format}</p>
                                            <p class="mb-1"><small class="text-muted">{$lang_label_gross}
                                                    :</small> {$currency} {$item.price_gross_single_format}</p>
                                        {/if}
                                        {if $price_mode == 3}
                                            <p><small class="text-muted">{$lang_label_net}
                                                    :</small> {$currency} {$item.price_net_single_format}</p>
                                        {/if}
                                    </div>
                                    <div class="col-6 col-sm-2">
                                        {$lang_label_tax}
                                        <p>{$item.tax} %</p>
                                    </div>
                                    <div class="col-6 col-sm-4 col-xl-3">
                                        {$lang_label_product_amount}
                                        {if $item.parent_id == 0}
                                        <form action="{$shopping_cart_uri}" method="POST">
                                            <input type="number" name="cart_product_amount" value="{$item.amount}" class="form-control"
                                                   onchange="this.form.submit()">
                                            <input type="hidden" name="cart_item_key" value="{$item.cart_id}">
                                            {$hidden_csrf_token}
                                        </form>
                                        {else}
                                            <p>{$item.amount}</p>
                                        {/if}
                                    </div>
                                </div>

                                <div class="d-sm-none d-flex align-items-center justify-content-between mt-2">
                                    <span class="fw-bold">
                                        {$lang_label_price}:
                                        {if $price_mode == 3}{$currency} {$item.price_net_format}{else}{$currency} {$item.price_gross_format}{/if}
                                    </span>
                                    <form action="{$shopping_cart_uri}" method="POST">
                                        <button type="submit" class="btn btn-link link-danger" name="remove_from_cart"
                                                value="{$item.cart_id}"><i class="bi bi-trash"></i></button>
                                        {$hidden_csrf_token}
                                    </form>
                                </div>

                            </td>

                            <td class="text-end text-nowrap d-none d-sm-table-cell">
                                {if $price_mode == 1}
                                    <p>{$currency} {$item.price_gross_format}</p>
                                {/if}
                                {if $price_mode == 2}
                                    <p><small class="text-muted">{$lang_label_net}</small> {$currency} {$item.price_net_format}</p>
                                    <p><small class="text-muted">{$lang_label_gross}</small> {$currency} {$item.price_gross_format}</p>
                                {/if}
                                {if $price_mode == 3}
                                    <p>{$currency} {$item.price_net_format}</p>
                                {/if}
                            </td>

                            <td class="text-center d-none d-sm-table-cell">
                                <form action="{$shopping_cart_uri}" method="POST">
                                    <button type="submit" class="btn btn-link link-danger" name="remove_from_cart"
                                            value="{$item.cart_id}"><i class="bi bi-trash"></i></button>
                                    {$hidden_csrf_token}
                                </form>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            </div>
        </div>

        {* addresses one below the other, the EU warranty notice next to them -
           without addresses (request mode) the notice follows further below *}
        {if $show_submit_order_form == 1}
            <div class="row g-4 mb-4">
                <div class="{if is_array($warranty_notice)}col-md-6{else}col-12{/if}">
                    <div class="card mb-4">
                        <div class="card-header">{$lang_label_invoice_address}</div>
                        <div class="card-body">{$client_data}</div>
                    </div>
                    <div class="card">
                        <div class="card-header">{$lang_label_delivery_address}</div>
                        <div class="card-body">{$shipping_address}</div>
                    </div>
                </div>
                {if is_array($warranty_notice)}
                <div class="col-md-6">
                    {include file='shopping_cart_warranty_notice.tpl'}
                </div>
                {/if}
            </div>

            {if $checkout_error_msg != ''}
                <div class="alert alert-info">
                    {$checkout_error_msg}
                </div>

                {if $show_guest_mail_form == 1}
                    <div class="alert alert-info">
                        {$lang_label_guest_checkout_hint}
                        {if $guest_register_link != ''} {$guest_register_link}{/if}
                    </div>

                    <div id="address-mail-response"></div>
                    <div id="" hx-get="/xhr/se/profile/?address-mail" hx-trigger="load">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                {/if}

                {if $show_guest_address_form == 1}
                    <div id="address-ba-response"></div>
                    <div id="" hx-get="/xhr/se/profile/?address-ba" hx-trigger="load, changed_address_ba">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div id="address-sa-response"></div>
                    <div id="" hx-get="/xhr/se/profile/?address-sa" hx-trigger="load, changed_address_sa">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                {/if}
            {/if}
        {/if}

        {if $show_request_form == 1}
            {include file='shopping_cart_form.tpl'}
        {/if}

        {if $max_order_value_msg != ''}
            <div class="alert alert-info">
                {$max_order_value_msg}
            </div>
        {/if}

        {if $cart_error_delivery_zone != ''}
            <div class="alert alert-info">
                {$cart_error_delivery_zone}
            </div>
        {/if}

        {if $send_request_msg != ''}
            <div class="alert alert-{$request_msg_class}">
                {$send_request_msg}
            </div>
        {/if}

        {* EU notice on the legal guarantee, if not already shown next to the addresses *}
        {if $show_submit_order_form != 1 && is_array($warranty_notice)}
            <div class="mb-4">
                {include file='shopping_cart_warranty_notice.tpl'}
            </div>
        {/if}

    </div>

    {* right column: totals, payment method, comment, terms and the order button *}
    {if $show_totals}
    <div class="col-lg-4">
        <div class="card sc-summary sticky-lg-top" style="top: 1rem;">
            <div class="card-body">

                <p class="h4 mb-3">{$lang_price_total}</p>

                <table class="table table-sm table-borderless small mb-0">
                    <tr>
                        <td class="ps-0">{$lang_price_subtotal} <span class="text-muted">{$lang_label_net}</span></td>
                        <td class="text-end text-nowrap pe-0">{$currency} {$cart_price_subtotal_net}</td>
                    </tr>
                    <tr>
                        <td class="ps-0">{$lang_label_plus_tax}</td>
                        <td class="text-end text-nowrap pe-0">{$currency} {$cart_included_taxes}</td>
                    </tr>
                    <tr class="fw-bold">
                        <td class="ps-0">{$lang_price_subtotal} <span class="text-muted fw-normal">{$lang_label_gross}</span></td>
                        <td class="text-end text-nowrap pe-0">{$currency} {$cart_price_subtotal}</td>
                    </tr>
                    <tr>
                        <td class="ps-0">{$lang_label_shipping} <span class="text-muted">{$lang_label_net}</span></td>
                        <td class="text-end text-nowrap pe-0">{$currency} {$cart_shipping_costs}</td>
                    </tr>
                    <tr>
                        <td class="ps-0">{$lang_label_plus_tax}</td>
                        <td class="text-end text-nowrap pe-0">{$currency} {$cart_shipping_costs_taxes}</td>
                    </tr>
                    <tr class="fw-bold">
                        <td class="ps-0">{$lang_label_shipping}</td>
                        <td class="text-end text-nowrap pe-0">{$currency} {$cart_shipping_costs_total}</td>
                    </tr>
                </table>

                <hr>

                <p class="fw-bold mb-2">{$lang_label_payment_method}</p>
                {* radios styled as toggle buttons (Bootstrap .btn-check), a change submits the form *}
                <form action="{$shopping_cart_uri}" method="POST" id="set_payment" class="d-grid gap-2">
                    {foreach $payment_methods as $pm}
                        <input type="radio" class="btn-check" name="set_payment" value="{$pm.key}"
                               id="id_{$pm.key}" autocomplete="off" {$checked_{$pm.key}} onchange="this.form.submit()">
                        <label class="btn btn-outline-primary d-flex justify-content-between text-start" for="id_{$pm.key}">
                            <span>{$pm.title}</span>
                            <span class="text-nowrap">{$currency} {$pm.cost}</span>
                        </label>
                    {/foreach}
                    {$hidden_csrf_token}
                </form>
                {if $show_submit_order_form == 1 && $payment_message != ''}
                    <div class="small text-muted mt-2">{$payment_message}</div>
                {/if}

                <div class="d-flex justify-content-between small mt-3">
                    <span>{$lang_label_payment_method}</span>
                    <span class="text-nowrap">{$currency} {$cart_payment_costs}</span>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-baseline mb-3">
                    <span>{$lang_price_total}</span>
                    <span class="fs-3 fw-bold text-nowrap">{$currency} {$cart_price_total}</span>
                </div>

                {if $show_submit_order_form == 1}
                    <form action="{$shopping_cart_uri}" method="POST">
                        <div class="mb-3">
                            <label class="form-label" for="cartComment">{$lang_label_cart_comment}</label>
                            <textarea class="form-control" id="cartComment" name="cart_comment"></textarea>
                        </div>

                        <div class="form-check mb-3">
                            {* required - the order is refused without it anyway (checkout-send-order.php) *}
                            <input class="form-check-input" type="checkbox" name="check_cart_terms" value="check"
                                   id="cartTerms" required>
                            {* the snippet comes as a paragraph, flex keeps the marker next to it *}
                            <label class="form-check-label d-flex gap-1" for="cartTerms">
                                <span>{$cart_agree_term}</span> <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100" name="order"
                                value="send"><i class="bi bi-lock"></i> {$lang_btn_send_order}</button>
                        {$hidden_csrf_token}
                    </form>
                {/if}

            </div>
        </div>
    </div>
    {/if}

</div>
