<div id="order-withdrawal-alert"></div>

<h3>{$heading_order_withdrawal}</h3>

<div class="lead">
    {$text_order_withdrawal_intro}
</div>

<form class="form" hx-post="/xhr/se/order-withdrawal/" hx-target="#order-withdrawal-alert" hx-swap="innerHTML" method="POST">
    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <label for="orderWithdrawalNbr">{$label_order_nbr}</label>
                <input type="text" class="form-control" name="order_nbr" id="orderWithdrawalNbr" value="{$prefill_order_nbr}" required>
            </div>
            <div class="mb-3">
                <label for="orderWithdrawalMail">{$label_mail}</label>
                <input type="email" class="form-control" name="mail" id="orderWithdrawalMail" value="{$prefill_mail}" required>
            </div>
            <div class="mb-3">
                <label for="orderWithdrawalReason">{$label_order_withdrawal_reason}</label>
                <textarea class="form-control" name="reason" id="orderWithdrawalReason" rows="4"></textarea>
            </div>
            <input class="btn btn-success" type="submit" name="send_order_withdrawal" value="{$button_order_withdrawal}">
            {$hidden_csrf_token}
        </div>
    </div>
</form>
