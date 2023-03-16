<form action="{formaction}" method="post">
<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-header">{mail_form_status}</div>
            <div class="card-body">
                <label class="form-label">{lang_subject}</label>
                <input class="form-control" type="text" name="mail_subject" value="{mail_subject}">
                <label class="form-label">{lang_text}</label>
                <textarea class="form-control" type="text" name="mail_content" rows="8">{mail_content}</textarea>
            </div>
        </div>

    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-header">{lang_recipients}</div>
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="mail_recipients" value="all" id="checkAll" {checked_all}>
                    <label class="form-check-label" for="checkAll">
                        {label_all_users}
                    </label>
                </div>
                <div class="mt-2">
                    {list_usergroups}
                </div>
            </div>

        </div>

        <hr>
        <div class="card p-3">
        <div class="row">
            <div class="col-md-8">
                {btn_save_draft}
            </div>
            <div class="col-md-4">
                {btn_close}
            </div>
        </div>
        <div class="my-3 text-center">
            {btn_send}
        </div>
        </div>

        {hidden_csrf}
        <input type="hidden" name="mail_id" value="{mail_id}">
    </div>
</div>
</form>