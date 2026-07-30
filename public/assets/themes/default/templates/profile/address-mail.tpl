<form>
    <div class="card mb-3">
        <div class="card-header">
            {$lang_legend_guest_mail}
        </div>
        <div class="card-body">
            <p>{$lang_label_guest_mail_text}</p>

            <div class="row mb-3">
                <div class="col-6">
                    <label for="ba_mail">{$lang_label_mail}</label>
                    <input type="email" class="form-control" id="ba_mail" value="{$ba_mail}" name="ba_mail" required>
                </div>
                <div class="col-6">
                    <label for="ba_mail_repeat">{$lang_label_mailrepeat}</label>
                    <input type="email" class="form-control" id="ba_mail_repeat" value="" name="ba_mail_repeat" required>
                </div>
            </div>

            <button class="btn btn-primary" type="button" name="update_address_mail"
                    hx-post="/xhr/se/profile/"
                    hx-trigger="click"
                    hx-target="#address-mail-response"
                    hx-swap="innerHTML">
                {$lang_button_update}
            </button>
            {$hidden_csrf_token}

        </div>
    </div>
</form>
