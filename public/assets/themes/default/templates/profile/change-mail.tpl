<form>
    <div class="card mb-3">
        <div class="card-header toggle">
            <a data-bs-toggle="collapse" href="#collapseMail" role="button" aria-expanded="false" aria-controls="collapseMail">
            {$lang_legend_access_data} ({$lang_label_mail} / {$get_user_mail})
                <span class="ms-auto"><i class="bi bi-chevron-down"></i></span>
            </a>
        </div>
        <div class="card-body collapse" id="collapseMail">

            <div class="row mb-1">

                <div class="col-6">
                    <label for="mail">{$lang_label_mail}</label>
                    <input type="text" class="form-control" id="mail" value="" name="set_mail">
                </div>

                <div class="col-6">
                    <label for="mail_repeat">{$lang_label_mailrepeat}</label>
                    <input type="text" class="form-control" id="mail_repeat" value="" name="set_mail_repeat">
                    <p class="help-block">{$msg_edit_mail}</p>
                </div>
            </div>

            <button class="btn btn-primary" type="button" name="change_mail"
                    hx-post="/xhr/se/profile/"
                    hx-trigger="click"
                    hx-target="#mail-response"
                    hx-swap="innerHTML">
                {$lang_button_update}
            </button>
            {$hidden_csrf_token}
        </div>
    </div>
</form>