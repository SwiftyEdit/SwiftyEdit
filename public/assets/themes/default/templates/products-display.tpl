<div class="post-product">

    {if isset($smarty.session.last_shop_url)}
        <a href="{$smarty.session.last_shop_url}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left-short"></i> {$lang_button_back}
        </a>
    {/if}

    <div class="row">

        {if $product_img_src != ''}
        <div class="col-md-4">
            <img src="{$product_img_src}" alt="{$product_img_alt}" title="{$product_img_title}" class="img-fluid"><br>
            <small>{$product_img_caption}</small>

            {if is_array($product_show_images)}
            <section data-featherlight-gallery data-featherlight-filter="a">
            <div class="row mt-3 g-1">

                {foreach $product_show_images as $img => $value}
                <div class="col-3">
                    <a href="{$value.media_file}" title="{$value.media_title}" class="lightbox">
                        <img src="{$value.media_file}" alt="{$value.media_alt}" class="img-fluid">
                    </a>
                </div>
                {/foreach}

            </div>
            </section>
            {/if}
        </div>
        {/if}

        <div class="col">

            <h1>{$product_title}</h1>
            {$product_teaser}
            <!-- pricetag -->
            {if $product_pricetag_mode != "2"}
                <div class="price-tag d-inline-block">
                    <div class="clearfix">
                        <div class="price-tag-label">{$product_price_label}</div>
                    </div>
                    <div class="price-tag-inner">
                        {$product_currency} <span id="price-display">{$product_price_tag}</span> <span class="product-amount">{$product_amount}</span> <span class="product-unit">{$product_unit}</span>
                    </div>
                    <div class="price-tag-note">{$product_tax_label}</div>
                </div>

                <div class="delivery-time">
                    {$label_delivery_time}: <span><strong>{$product_delivery_time_title}</strong> {$product_delivery_time_text}</span>
                </div>
                {if $product_cart_mode != "2"}
                <div class="mt-3">
                    <form action="{$form_action}" method="POST" class="text-start d-inline">

                        {if is_array($select_options)}
                            <!-- product options -->
                            {foreach $select_options as $option}
                                <label class="form-label">{$option.title}</label>
                                <select class="form-select w-auto" name="product_options[]">
                                    {foreach $option.values as $value}
                                        <option value="{$option.title}: {$value}">{$value}</option>
                                    {/foreach}
                                </select>
                            {/foreach}

                        {/if}

                        {if $product_options_comment_label != ""}
                            <label class="form-label">{$product_options_comment_label}</label>
                            <textarea class="form-control" name="customer_options_comment"></textarea>
                        {/if}
                    {if $file_upload_message != ''}
                        <div class="alert alert-info my-3">
                            {$file_upload_message}
                        </div>
                    {/if}
                        <div class="mt-2 pt-2 d-flex border-top">
                            <div class="input-group w-25">
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustQuantity(-1)">−</button>
                            <input type="number"
                                   id="quantity"
                                   name="amount"
                                   value="{$product_amount}" {$product_order_quantity_min} {$product_order_quantity_max}
                                   class="form-control form-control-lg"
                                   hx-get="/xhr/se/products/?calc=price&product_id={$product_id}"
                                   hx-target="#price-display"
                                   hx-trigger="keyup changed delay:500ms, input"
                            >
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustQuantity(1)">+</button>
                            </div>
                            <button class="btn btn-outline-success btn-lg" name="add_to_cart" value="{$product_id}">{$btn_add_to_cart}</button>
                        </div>
                        <input type="hidden" name="product_href" value="{$product_href}">
                        {$hidden_csrf_token}

                    </form>
                </div>
                {/if}
            {/if}
            <!-- pricetag end -->

        </div>
    </div>

    <div class="post-text mt-3">

        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                {if $product_text_label != ""}
                    <a class="nav-link active" aria-selected="true" data-bs-toggle="tab" href="#main">{$product_text_label}</a>
                {/if}
                {if is_array($product_features)}
                    <a class="nav-link" aria-selected="false" data-bs-toggle="tab" href="#tab-features">{$label_product_features}</a>
                {/if}
                {if is_array($show_volume_discounts)}
                    <a class="nav-link" aria-selected="false" data-bs-toggle="tab" href="#tab-volume-discounts">{$label_prices_discount}</a>
                {/if}
                {if $text_additional1_label != ""}
                    <a class="nav-link" aria-selected="false" data-bs-toggle="tab" href="#tab_additional1">{$text_additional1_label}</a>
                {/if}
                {if $text_additional2_label != ""}
                    <a class="nav-link" aria-selected="false" data-bs-toggle="tab" href="#tab_additional2">{$text_additional2_label}</a>
                {/if}
                {if $text_additional3_label != ""}
                    <a class="nav-link" aria-selected="false" data-bs-toggle="tab" href="#tab_additional3">{$text_additional3_label}</a>
                {/if}
                {if $text_additional4_label != ""}
                    <a class="nav-link" aria-selected="false" data-bs-toggle="tab" href="#tab_additional4">{$text_additional4_label}</a>
                {/if}
                {if $text_additional5_label != ""}
                    <a class="nav-link" aria-selected="false" data-bs-toggle="tab" href="#tab_additional5">{$text_additional5_label}</a>
                {/if}
                {if $text_scope_of_delivery != ""}
                    <a class="nav-link" aria-selected="false" data-bs-toggle="tab" href="#sod">{$lang_label_scope_of_delivery}</a>
                {/if}
            </div>
        </nav>
        <div class="tab-content my-3" id="myTabContent">
            <div class="tab-pane fade show active" id="main" role="tabpanel">
                {$product_text}
            </div>
            {if is_array($product_features)}
            <div class="tab-pane fade" id="tab-features" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-body">
                        <table class="table table-sm">
                            {foreach $product_features as $feature => $value}
                                <tr>
                                    <td>{$value.snippet_title}</td>
                                    <td>{$value.snippet_content}</td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                </div>
            </div>
            {/if}
            {if is_array($show_volume_discounts)}
            <div class="tab-pane fade" id="tab-volume-discounts" role="tabpanel">
                <table class="table table-sm">
                    <tr>
                        <td>Menge</td>
                        <td>Netto</td>
                        <td>Brutto</td>
                    </tr>
                    {foreach $show_volume_discounts as $discount => $value}
                        <tr>
                            <td># {$value.amount}</td>
                            <td>{$value.price_net}</td>
                            <td>{$value.price_gross}</td>
                        </tr>
                    {/foreach}
                </table>
            </div>
            {/if}
            {if $text_additional1_label != ""}
            <div class="tab-pane fade" id="tab_additional1" role="tabpanel">
                {$text_additional1}
            </div>
            {/if}
            {if $text_additional2_label != ""}
                <div class="tab-pane fade" id="tab_additional2" role="tabpanel">
                    {$text_additional2}
                </div>
            {/if}
            {if $text_additional3_label != ""}
                <div class="tab-pane fade" id="tab_additional3" role="tabpanel">
                    {$text_additional3}
                </div>
            {/if}
            {if $text_additional4_label != ""}
                <div class="tab-pane fade" id="tab_additional4" role="tabpanel">
                    {$text_additional4}
                </div>
            {/if}
            {if $text_additional5_label != ""}
                <div class="tab-pane fade" id="tab_additional5" role="tabpanel">
                    {$text_additional5}
                </div>
            {/if}
            {if $text_scope_of_delivery != ""}
                <div class="tab-pane fade" id="sod" role="tabpanel">
                    {$text_scope_of_delivery}
                </div>
            {/if}
        </div>

    </div>


    {if is_array($show_variants)}
        <div class="card mb-3 variants-picker">
            <div class="card-header d-flex justify-content-between">
                <div>{$lang_label_product_variants}</div>
                {if $product_lowest_price_gross}
                <div>{$lang_price_tag_label_from} {$product_currency} {$product_lowest_price_gross}</div>
                {/if}
            </div>
            <div class="card-body">
                <div class="row row-cols-4 mb-3">
                    {foreach $show_variants as $product => $value}
                        <div class="col mb-2">
                            <div class="card h-100 {$value.class}">
                                {if $value.image != ""}
                                <img src="{$value.image}" class="card-img-top" alt="{$value.title}"
                                     title="{$value.title}">
                                {/if}
                                <div class="card-body fs-6 lh-sm">
                                    <h6 class="card-title mb-0">{$value.title}</h6>
                                    <small>{$value.teaser}</small>
                                    {if $value.class != 'active'}
                                        <a href="{$value.product_href}" class="stretched-link" title="{$value.title}"> </a>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}

    {if is_array($show_accessories)}
        <div class="card mb-3">
            <div class="card-header">{$lang_label_products_accessories}</div>
            <div class="card-body">
                <div class="row row-cols-4 mb-3">
                    {foreach $show_accessories as $product => $value}
                        <div class="col">
                            <div class="card h-100 mb-2">
                                {if $value.image != ""}
                                <img src="{$value.image}" class="card-img-top" alt="{$value.title}"
                                     title="{$value.title}">
                                {/if}
                                <div class="card-body fs-6 lh-sm">
                                    <h6 class="card-title mb-0">{$value.title}</h6>
                                    <small>{$value.teaser}</small>
                                    {if $value.class != 'active'}
                                        <a href="{$value.product_href}" class="stretched-link" title="{$value.title}"> </a>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}

    {if is_array($show_related)}
        <div class="card mb-3">
            <div class="card-header">{$lang_label_related_products}</div>
            <div class="card-body">
                <div class="row row-cols-4 mb-3">
                    {foreach $show_related as $product => $value}
                        <div class="col">
                            <div class="card h-100 mb-2">
                                {if $value.image != ""}
                                <img src="{$value.image}" class="card-img-top" alt="{$value.title}"
                                     title="{$value.title}">
                                {/if}
                                <div class="card-body fs-6 lh-sm">
                                    <h6 class="card-title mb-0">{$value.title}</h6>
                                    <small>{$value.teaser}</small>
                                    {if $value.class != 'active'}
                                        <a href="{$value.product_href}" class="stretched-link" title="{$value.title}"> </a>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}

    {if $product_snippet_text != ""}
        <div class="card mb-3">
            <div class="card-header">{$product_snippet_title}</div>
            <div class="card-body">
                {$product_snippet_text}
            </div>
        </div>
    {/if}

    {if $attachment_filename != ""}

        {if $alert_download != ""}
            {$alert_download}
        {/if}

        <form action="{$form_action}" method="POST">
        <div class="card mb-3">
            <div class="card-header">{$download_title}</div>
            <div class="card-body">{$download_text}</div>
            <div class="card-footer">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startDownloadModal"><i class="bi bi-download"></i> Download</button>
                <input type="hidden" name="get_attachment" value="{$product_id}">
            </div>
        </div>
            {$hidden_csrf_token}
        </form>
    {/if}

    {if $product_snippet_price != ""}
    <div class="card post-snippet-price mb-3">
        <div class="card-header">{$label_prices_snippet}</div>
        <div class="card-body">
            {$product_snippet_price}
        </div>
    </div>
    {/if}

    {if $show_voting == true}
        <div class="mb-3">
            <button class="btn btn-sm btn-outline-secondary" name="upvote" onclick="vote(this.value)"
                    value="up-post-{$product_id}" {$votes_status_up}>
                <i class="bi bi-hand-thumbs-up-fill"></i> <span id="vote-up-nbr-{$product_id}">{$votes_up}</span>
            </button>
            <button class="btn btn-sm btn-outline-secondary" name="dnvote" onclick="vote(this.value)"
                    value="dn-post-{$product_id}" {$votes_status_dn}>
                <i class="bi bi-hand-thumbs-down-fill"></i> <span id="vote-dn-nbr-{$product_id}">{$votes_dn}</span>
            </button>
        </div>
    {/if}

</div>

{if $se_snippet_downloading_modal != ""}
<div class="modal fade" id="startDownloadModal" tabindex="-1" aria-labelledby="startDownloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Download</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                    {$se_snippet_downloading_modal}

            </div>
        </div>
    </div>
</div>
{/if}

{foreach from=$product_plugin_actions item=action}
    {if $action.type == 'button'}
        <button type="submit"
                name="{$action.name|escape}"
                value="{$action.value|escape}"
                class="{$action.class|escape}">
            {$action.label|escape}
        </button>
    {/if}
{/foreach}