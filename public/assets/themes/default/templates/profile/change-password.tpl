
<form>
    <div class="card mb-3">
        <div class="card-header toggle">
            <a data-bs-toggle="collapse" href="#collapsePassword" role="button" aria-expanded="false" aria-controls="collapsePassword">
            {$lang_legend_access_data} ({$lang_label_psw})
                <span class="ms-auto"><i class="bi bi-chevron-down"></i></span>
            </a>
        </div>
        <div class="card-body collapse" id="collapsePassword">
            <div class="row mb-1">
                <div class="col-6">
                    <label for="psw">{$lang_label_psw}</label>
                    <input type="password" class="form-control" id="psw" value="" name="s_psw">
                </div>

                <div class="col-6">
                    <label for="psw_repeat">{$lang_label_psw_repeat}</label>
                    <input type="password" class="form-control" id="psw_repeat" value="" name="s_psw_repeat">
                    <p class="help-block">{$msg_edit_psw}</p>
                </div>
            </div>

        <button class="btn btn-primary" type="button" name="change_password"
                hx-post="/xhr/se/profile/"
                hx-trigger="click"
                hx-target="#password-response"
                hx-swap="innerHTML">
            {$lang_button_update}
        </button>
        {$hidden_csrf_token}
        </div>
    </div>
</form>