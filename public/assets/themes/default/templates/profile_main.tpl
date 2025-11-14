{nocache}
<div class="{$msg_status}">
	{$register_message}
</div>


<h2>{$lang_headline_editprofile} ({$user_nick})</h2>

<div id="password-response"></div>
<div id="" hx-get="/xhr/se/profile/?password" hx-trigger="load, changed_password">
    Loading ...
</div>

 <div id="mail-response"></div>
 <div id="" hx-get="/xhr/se/profile/?mail" hx-trigger="load, changed_mail_temp">
     <div class="spinner-border" role="status">
         <span class="visually-hidden">Loading...</span>
     </div>
</div>

<div id="address-response"></div>
<div id="" hx-get="/xhr/se/profile/?address" hx-trigger="load, changed_address">
    <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<div id="address-ba-response"></div>
<div id="" hx-get="/xhr/se/profile/?address-ba" hx-trigger="load, changed_address_ba">
    <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<div id="address-sa-response"></div>
<div id="" hx-get="/xhr/se/profile/?address-sa" hx-trigger="load, changed_address_sa">
    <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<hr>


<div class="row">
    <div class="col-md-9">
        <div id="avatar-response"></div>
        <div id="" hx-get="/xhr/se/profile/?avatar" hx-trigger="load">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div id="" hx-get="/xhr/se/profile/?show_avatar" hx-trigger="load, changed_avatar from:body">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <button name="delete_avatar"
                hx-post="/xhr/se/profile/"
                hx-include="[name='csrf_token']"
                hx-swap="none">
            {$lang_delete}
        </button>
    </div>

</div>



<hr>

<!-- Delete Account -->

<div class="alert alert-danger mt-3">

<form id="form_delete_profile" action="{$form_url}" method="POST">
<fieldset>
<legend>{$lang_legend_delete_account}</legend>
<p>{$lang_msg_delete_account}</p>

<input class="btn btn-danger btn-small" type="submit" onclick="return confirm('{$lang_msg_confirm_delete_account}')" name="delete_my_account" value="{$lang_button_delete}">
</fieldset>
	{$hidden_csrf_token}
</form>

</div>
{/nocache}