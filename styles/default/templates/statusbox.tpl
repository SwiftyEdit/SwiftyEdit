{nocache}
<div class="card">
	<div class="card-header">{$status_msg} <b>{$smarty.session.user_nick}</b></div>

	<div class="list-group list-group-flush">
		<a href="{$link_profile}" class="list-group-item">{$lang_button_profile}</a>
		
		{if orders_uri != ''}
			<a href="{$orders_uri}" class="list-group-item">{$lang_button_orders}</a>
		{/if}
		
		<a href="{$link_logout}" class="list-group-item">{$lang_button_logout}</a>

	</div>
		{if $smarty.session.user_class == "administrator"}
			<div class="p-3">
		<a href="{$link_acp}" class="btn btn-secondary w-100 mb-1">{$lang_button_acp}</a>

		<form action="{$link_acp}?tn=pages&sub=edit" method="POST">
			<div class="d-grid gap-2">
				<button name="editpage" value="{$page_id}" class="btn btn-secondary w-100">{$lang_button_edit_page}</button>
				{$hidden_csrf_token}
			</div>
		</form>
			</div>
		{/if}

</div>
{/nocache}