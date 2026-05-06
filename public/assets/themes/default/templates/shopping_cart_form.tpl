<div class="card p-3 mb-3">

    {if {$client_data} == ''}
    <div class="alert alert-info my-3">
        {$lang_request_form_intro}
    </div>
    {/if}

<form action="{$shopping_cart_uri}" method="POST">
    <div class="row">
        <div class="col">
    <div class="mb-3">
        <label class="form-label" for="requestname">{$lang_label_name}</label>
        <input class="form-control" id="requestname" type="text" name="buyer_name" value="{$buyer_name}" {$readonly}>
    </div>
    <div class="mb-3">
        <label class="form-label" for="requestmail">{$lang_label_mail}</label>
        <input class="form-control" id="requestmail" type="email" name="buyer_mail" value="{$buyer_mail}" {$readonly}>
    </div>
            <div class="mb-3">
                <label class="form-label" for="requestcomment">{$lang_label_cart_comment}</label>
                <textarea class="form-control" id="requestcomment" name="buyer_comment">{$buyer_comment}</textarea>
            </div>
        </div>
        {if {$client_data} != ''}
            <div class="col">
                <p>{$lang_label_invoice_address}</p>
                {$client_data}
                {if $shipping_address != ''}
                    <hr>
                    <p>{$lang_label_delivery_address}</p>
                    {$shipping_address}
                {/if}
            </div>
            {/if}
    </div>
    <button type="submit" class="btn btn-success" name="send_request" value="send">{$lang_btn_send_order_request}</button>
    {$hidden_csrf_token}
</form>

</div>