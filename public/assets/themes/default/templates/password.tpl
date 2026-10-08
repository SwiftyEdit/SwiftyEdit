<div id="reset-alert"></div>

<div>
    {$alert_reset}
</div>

<h3>{$forgotten_psw}</h3>

{if $reset_token != ''}

<p class="lead">{$forgotten_psw_new_intro}</p>

<form class="form" action="{$form_url}" method="POST">
    <div class="card">
        <div class="card-header">{$legend_ask_for_psw}</div>
        <div class="card-body">
            <div class="mb-3">
                <label for="newPsw">{$label_psw}</label>
                <input type="password" class="form-control" name="new_psw" id="newPsw" autocomplete="new-password" required>
            </div>
            <div class="mb-3">
                <label for="newPswRepeat">{$label_psw_repeat}</label>
                <input type="password" class="form-control" name="new_psw_repeat" id="newPswRepeat" autocomplete="new-password" required>
            </div>
            <input type="hidden" name="reset_token" value="{$reset_token}">
            <input class="btn btn-success" type="submit" name="set_new_psw" value="{$button_save}">
            {$hidden_csrf_token}
        </div>
    </div>
</form>

{elseif !$reset_done}

<p class="lead">{$forgotten_psw_intro}</p>

<form class="form" hx-post="/xhr/se/password-reset/" hx-target="#reset-alert" method="POST">
    <div class="card">
        <div class="card-header">{$legend_ask_for_psw}</div>
        <div class="card-body">
            <div class="mb-3">
                <label for="emailReset">{$label_mail}</label>
                <input type="text" class="form-control" name="mail" id="emailReset">
            </div>
            <input class="btn btn-success" type="submit" name="ask_for_psw" value="{$button_send}">
            {$hidden_csrf_token}
        </div>
    </div>
</form>

{/if}
