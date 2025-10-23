{*
 If additional mandatory fields are specified in the backend settings,
 these fields must also be included in the form, otherwise registration will not be possible.
 It is important that the form fields have the same name attribute value as the mandatory field name.

 example
 if you select the field user_firstname as additional required field:
 <input type="text" class="form-control" value="{$user_firstname}" name="user_firstname" id="inputUserFirstName" required>
 *}

{nocache}
<h2>{$lang_legend_register}</h2>
<div class="{$msg_status}">
{$register_message}
</div>

<p>{$lang_msg_register_intro}</p>

<form id="pd_form" action="{$form_url}" method="POST">

<fieldset>
	<legend>{$lang_legend_required_fields}</legend>

	<div class="form-group">
		<label for="inputUsername">{$lang_label_username}</label>
		<input type="text" class="form-control" value="{$send_username}" name="username" id="inputUsername" required>
	</div>
		
	<div class="form-group">
		<label for="inputMail1">{$lang_label_mail}</label>
		<input type="text" class="form-control" value="{$send_mail}" name="mail" id="inputMail1" required>
	</div>
		
	<div class="form-group">
			<label for="inputMail2">{$lang_label_mailrepeat}</label>
			<input type="text" class="form-control" value="{$send_mailrepeat}" name="mailrepeat" id="inputMail2" required>
	</div>	

	<div class="form-group">
		<label for="inputPass1">{$lang_label_psw}</label>
		<input type="password" class="form-control" name="psw" id="inputPass1" required>
	</div>	

	<div class="form-group">
		<label for="inputPass2">{$lang_label_psw_repeat}</label>
		<input type="password" class="form-control" name="psw_repeat" id="inputPass2" required>
	</div>

</fieldset>

<fieldset>

	<legend>{$lang_legend_optional_fields}</legend>

	<div class="form-group">
		<label for="inputFirstname">{$lang_label_firstname}</label>
		<input type="text" class="form-control" value="{$send_firstname}" name="firstname" id="inputFirstname">
	</div>	
		
	<div class="form-group">		
		<label for="inputLastname">{$lang_label_lastname}</label>
		<input type="text" class="form-control" value="{$send_name}" name="name" id="inputLastname">
	</div>	
				
	<div class="form-group">
		<label for="inputStreet">{$lang_label_street}</label>
		<input type="text" class="form-control" value="{$send_street}" name="street" id="inputStreet">
	</div>
				
	<div class="form-group">
		<label for="inputStreetNbr">{$lang_label_nr}</label>
		<input type="text" class="form-control" value="{$send_nr}" name="nr" id="inputStreetNbr">
	</div>
		
	<div class="form-group">
		<label for="inputZip">{$lang_label_zip}</label>
		<input type="text" class="form-control" value="{$send_zip}" name="zip" id="inputZip">
	</div>	
			
	<div class="form-group">
		<label for="inputCity">{$lang_label_town}</label>
		<input type="text" class="form-control" value="{$send_city}" name="city" id="inputCity">
	</div>	
				
	<div class="form-group">
		<label for="inputAboutYou">{$lang_label_about_you}</label>
		<textarea name="about_you" class="form-control" id="inputAboutYou">{$send_about}</textarea>
	</div>
		
	
</fieldset>

	
		
<div class="scrollBox">
	{$agreement_text}
</div>
			
<div class="alert alert-info">
	<div class="form-group form-check">
			<input type="checkbox" name="accept_terms" class="form-check-input" id="checkAcceptTerms">
			<label class="form-check-label" for="checkAcceptTerms">{$lang_msg_register_outro}</label>
	</div>
</div>
		
		
<input class="btn btn-success mt-3 mb-3" type="submit" name="send_registerform" value="{$lang_button_send_register}">
	{$hidden_csrf_token}

</form>
{/nocache}