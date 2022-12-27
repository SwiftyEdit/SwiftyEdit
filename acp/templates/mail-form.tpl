<form action="{formaction}" method="post">
<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-header">{mail_form_status}</div>
            <div class="card-body">
                <label class="form-label">{lang_subject}</label>
                <input class="form-control" type="text" name="mail_subject" value="{mail_subject}">
                <label class="form-label">{lang_text}</label>
                <textarea class="form-control" type="text" name="mail_text" rows="8">{mail_text}</textarea>
            </div>
        </div>

    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-header">{lang_recipients}</div>
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="mail_recipients" value="all">
                    <label class="form-check-label">
                        {label_all_users}
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="mail_recipients" value="marketing">
                    <label class="form-check-label">
                        {label_marketing_users}
                    </label>
                </div>
            </div>
        </div>
        <hr>
        <div class="d-flex">
            {btn_save_draft}
            {btn_send}
            {btn_close}
        </div>
        {hidden_csrf}
    </div>
</div>
</form>