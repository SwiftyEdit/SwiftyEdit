<div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{$lang_label_order_nbr}: {$order_nbr}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-md-4">
                    <h6>{$lang_label_billing_address}</h6>
                    {$order_billing_address}
                </div>
                <div class="col-md-4">
                    <h6>{$lang_label_delivery_address}</h6>
                    {$order_shipping_address}
                </div>
                <div class="col-md-4">
                    <p>{$order_time}</p>
                    <p>{$order_price_total} {$order_currency}</p>
                    <hr>
                    {if $order_status_payment == '1'}
                        {$lang_status_payment_open}<br>
                    {else}
                        <span class="text-success">{$lang_status_payment_paid}</span><br>
                    {/if}
                    {if $payment_plugin_str != ""}
                        {$payment_plugin_str}
                    {/if}
                    {if $order_status_shipping == '1'}
                        {$lang_status_shipping_open}
                    {else}
                        <span class="text-success">{$lang_status_shipping_done}</span>
                    {/if}
                </div>
            </div>

            <hr>

            <table class="table table-striped table-sm">
                {* loop through ordered items *}
                {foreach $products as $product}
                    <tr>
                        <td>
                            <span class="badge text-bg-secondary">{$product.pos}</span>
                        </td>
                        <td>{$product.amount} x</td>
                        <td>
                            <strong>{$product.title}</strong> <small>{$product.product_nbr}</small><br>
                            {$product.options}<br>{$product.options_comment_label} <span>{$product.options_comment}</span>
                            {* user can upload a file for this item *}
                            {if $product.need_upload != ''}
                                <form action="{$order_page_uri}" method="post" enctype="multipart/form-data">
                                    <div class="row g-3">
                                        <div class="col-auto">
                                            <input class="form-control" type="file" name="upload_file" id="uploadFile">
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" name="startUpload" class="btn btn-primary">Upload</button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="order" value="{$order.nbr}">
                                    <input type="hidden" name="pos" value="{$product.pos}">
                                    {$hidden_csrf_token}
                                </form>
                            {/if}
                            {* we provide no preview, but user can download the file  *}
                            {if $product.user_upload_status == 'uploaded'}
                                <form action="{$order_page_uri}" method="POST" class="d-inline">
                                    <button type="submit" class="btn btn-sm btn-primary" name="download_user_file">Download</button>
                                    <input type="hidden" name="order" value="{$order_nbr}">
                                    <input type="hidden" name="pos" value="{$product.pos}">
                                    {$hidden_csrf_token}
                                </form>
                            {/if}
                        </td>
                        <td class="text-end">
                            {if $product.file_attachment_as != '' AND $order_status_payment == '2'}
                                <form action="{$order_page_uri}" method="POST" class="d-inline">
                                    <button class="btn btn-primary" type="submit" name="dl_p_file" value="{$product.post_id}"><i class="bi bi-download"></i> DOWNLOAD</button>
                                    <input type="hidden" name="order_id" value="{$order_nbr}">
                                    {$hidden_csrf_token}
                                </form>
                            {/if}
                            {if $product.dl_file_ext != '' AND $order_status_payment == '2'}
                                <form hx-post="{$order_page_uri}" hx-target="#download_response" method="POST" class="d-inline">
                                    <button class="btn btn-primary" type="submit" name="dl_p_file_ext" value="{$product.post_id}"><i class="bi bi-cloud-download"></i> DOWNLOAD</button>
                                    <input type="hidden" name="order_id" value="{$order_nbr}">
                                    {$hidden_csrf_token}
                                </form>
                            {/if}

                        </td>
                    </tr>
                {/foreach}
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{$lang_close}</button>
        </div>
    </div>
</div>