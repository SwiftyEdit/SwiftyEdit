<div id="reset-alert"></div>

<h3>{$forgotten_psw}</h3>

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