<div class="post-product">


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
                    <a href="{$value.media_file}" title="{$value.media_title}"><img src="{$value.media_thumb}" alt="{$value.media_alt}" class="img-fluid"></a>
                </div>
                {/foreach}

            </div>
            </section>
            {/if}
        </div>
        {/if}

        <div class="col">

            <h1>{$product_title}</h1>

            <!-- pricetag -->
            {if $product_pricetag_mode != "2"}
                <div class="price-tag d-inline-block">
                    <div class="clearfix">
                        <div class="price-tag-label">{$product_product_price_label}</div>
                    </div>
                    <div class="price-tag-inner">
                        {$product_currency} {$product_price_gross} <span class="product-amount">{$product_amount}</span> <span class="product-unit">{$product_unit}</span>
                    </div>
                </div>
                <div class="price-tag-note">
                    {$product_price_tag_label_gross} {$product_price_tag_label_delivery}
                </div>
                <div class="delivery-time">
                    {$label_delivery_time}: <span>{$product_delivery_time_title}</span>
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
                        <div class="mt-2">
                            <hr>
                            <button class="btn btn-outline-success btn-lg" name="add_to_cart" value="{$product_id}">{$btn_add_to_cart}</button>
                        </div>
                        <input type="hidden" name="csrf_token" value="{$csrf_token}">

                    </form>
                    </div>
                {/if}

            {/if}
            <!-- pricetag end -->

        </div>
    </div>

    <div class="post-text mt-3">
        {$product_text}
    </div>

    {if is_array($product_features)}
        <div class="card">
            <div class="card-header">{$label_product_features}</div>
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
    {/if}

    {if is_array($show_variants)}
        <div class="card mb-3">
            <div class="card-header">{$lang_label_product_variants}</div>
            <div class="card-body">
                <div class="row row-cols-4 mb-3">
                    {foreach $show_variants as $product => $value}
                        <div class="col">
                            <div class="card h-100">
                                <img src="{$value.image}" class="card-img-top" alt="{$value.title}"
                                     title="{$value.title}">

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
                <input type="hidden" name="csrf_token" value="{$csrf_token}">
            </div>
        </div>
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