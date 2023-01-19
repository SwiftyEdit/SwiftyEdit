{nocache}
<div class="{$msg_status}">
	{$register_message}
</div>


<h2>{$lang_headline_editprofile} ({$user_nick})</h2>

<form class="form-horizontal" id="form_address" action="{$form_url}" method="POST">
	<div class="card mb-3">
		<div class="card-header">{$lang_legend_adress_fields}</div>
		<div class="card-body">
			<div class="row mb-3">
				<div class="col-6">
					<label for="firstname">{$lang_label_firstname}</label>
					<input type="text" class="form-control" id="firstname" value="{$get_firstname}" name="s_firstname">
				</div>

				<div class="col-6">
					<label for="lastname">{$lang_label_lastname}</label>
					<input type="text" class="form-control" id="lastname" value="{$get_lastname}" name="s_lastname">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-9">
					<label for="street">{$lang_label_street}</label>
					<input type="text" class="form-control" id="street" value="{$get_street}" name="s_street">
				</div>

				<div class="col-3">
					<label for="streetnbr">{$lang_label_nr}</label>
					<input type="text" class="form-control" id="streetnbr" value="{$get_nr}" name="s_nr">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-3">
					<label for="zip">{$lang_label_zip}</label>
					<input type="text" class="form-control" id="zip" value="{$get_zip}" name="s_zip">
				</div>

				<div class="col-9">
					<label for="city">{$lang_label_town}</label>
					<input type="text" class="form-control" id="city" value="{$get_city}" name="s_city">
				</div>
			</div>

			<div class="form-group">
				<label for="about">{$lang_label_about_you}</label>
				<textarea class="form-control" id="about" rows="4" name="about_you">{$send_about}</textarea>
			</div>
		</div>
		<div class="card-footer">
			<input class="btn btn-primary" type="submit" name="update_address_data" value="{$lang_button_update}">
		</div>
	</div>
</form>

<form class="form-horizontal" id="form_ba_address" action="{$form_url}" method="POST">

	<div class="card mb-3">
		<div class="card-header">{$lang_label_billing_address}</div>
		<div class="card-body">
			<div class="row mb-3">
			<div class="col-6">
				<label for="ba_company">{$lang_label_company}</label>
				<input type="text" class="form-control" id="ba_company" value="{$ba_company}" name="ba_company">
			</div>
			</div>
				<div class="row mb-3">
			<div class="col-6">
				<label for="ba_firstname">{$lang_label_firstname}</label>
				<input type="text" class="form-control" id="ba_firstname" value="{$ba_firstname}" name="ba_firstname">
			</div>
			<div class="col-6">
				<label for="ba_lastname">{$lang_label_lastname}</label>
				<input type="text" class="form-control" id="ba_lastname" value="{$ba_lastname}" name="ba_lastname">
			</div>
			</div>

			<div class="row mb-3">
				<div class="col-9">
					<label for="ba_street">{$lang_label_street}</label>
					<input type="text" class="form-control" id="ba_street" value="{$ba_street}" name="ba_street">
				</div>

				<div class="col-3">
					<label for="ba_street_nbr">{$lang_label_nr}</label>
					<input type="text" class="form-control" id="ba_street_nbr" value="{$ba_street_nbr}" name="ba_street_nbr">
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-3">
					<label for="ba_zip">{$lang_label_zip}</label>
					<input type="text" class="form-control" id="ba_zip" value="{$ba_zip}" name="ba_zip">
				</div>

				<div class="col-9">
					<label for="ba_city">{$lang_label_town}</label>
					<input type="text" class="form-control" id="ba_city" value="{$ba_city}" name="ba_city">
				</div>
			</div>

		</div>
		<div class="card-footer">
			<input class="btn btn-primary" type="submit" name="update_ba_data" value="{$lang_button_update}">
		</div>
	</div>


</form>

<form class="form-horizontal" id="form_psw_data" action="{$form_url}" method="POST">
	<div class="card mb-3">
	<div class="card-header">{$lang_legend_access_data} ({$lang_label_psw})</div>
	<div class="card-body">
		<div class="row mb-3">
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
	</div>
	<div class="card-footer">
		<input class="btn btn-primary" type="submit" name="update_psw_data" value="{$lang_button_update}">
	</div>
	</div>
</form>

<form class="form-horizontal" id="form_email_data" action="{$form_url}" method="POST">

<div class="card">
	<div class="card-header">{$lang_legend_access_data} ({$lang_label_mail} / {$get_mail_address})</div>
	<div class="card-body">

		<div class="row">

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
	

	</div>
	<div class="card-footer">
		<input class="btn btn-primary" type="submit" name="update_email_data" value="{$lang_button_update}">
	</div>
</div>
</form>


<hr>



<!-- Avatar -->
<form id="profileform" action="{$form_url}" method="post" enctype="multipart/form-data">


	<div class="card mb-3">
		<div class="card-header">{$lang_legend_avatar}</div>
		<div class="card-body">
	<div class="row">
		<div class="col-9">
			<div class="mb-3">
				<label for="formFile" class="form-label">{$lang_msg_avatar}</label>
				<input class="form-control"name="avatar" type="file" size="50">
			</div>
			<hr>
			<div class="mb-3">
				<input class="btn btn-primary" type="submit" name="upload_avatar" value="{$lang_button_save}">
				{if isset($avatar_url)}
					<input class="btn btn-danger" type="submit" name="delete_avatar" value="{$link_avatar_delete_text}">
				{/if}
			</div>
			
		</div>
		<div class="col-3 text-center">
			<img src="{$avatar_url}" class="img-fluid rounded-circle" alt="" title="">
		</div>
	</div>

</div>
</div>

</form>


<hr>

<!-- Delete Account -->

<div class="alert alert-danger mt-3">

<form id="form_delete_profile" action="{$form_url}" method="POST">
<fieldset>
<legend>{$lang_legend_delete_account}</legend>
<p>{$lang_msg_delete_account}</p>

<input class="btn btn-danger btn-small" type="submit" onclick="return confirm('{$msg_confirm_delete_account}')" name="delete_my_account" value="{$lang_button_delete}">
</fieldset>
</form>

</div>
{/nocache}