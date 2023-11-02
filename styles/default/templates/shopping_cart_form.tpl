<div class="card p-3 mb-3">

    {if $send_request_msg != ''}
    <div class="alert alert-{$request_msg_class}">
        {$send_request_msg}
    </div>
    {/if}

    <div class="alert alert-info my-3">
        {$lang_request_form_intro}
    </div>

<form action="{$shopping_cart_uri}" method="POST">
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
    <button type="submit" class="btn btn-success" name="send_request" value="send">{$lang_btn_send_order_request}</button>
    {$hidden_csrf_token}
</form>

</div>