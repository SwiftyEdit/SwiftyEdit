{if $cart_alert_error != ''}
    <div class="alert alert-danger">
        {$cart_alert_error}
    </div>
{/if}

{if $cart_alert_success != ''}
    <div class="alert alert-success">
        {$cart_alert_success}
    </div>
{/if}

{if $cart_alert_payment != ''}
    <div class="alert alert-info">
        {$cart_alert_payment}
    </div>
{/if}

{if $send_request_msg != ''}
    <div class="alert alert-info">
        {$send_request_msg}
    </div>
{/if}

<p>{$cnt_items} {$lang_label_cnt_sc_items}</p>

{if $cnt_items > 0}
    {include file='shopping_cart_table.tpl'}
{/if}