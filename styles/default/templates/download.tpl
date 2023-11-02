<form action="{$form_Action}" method="POST">
	<h4>{$file_title}</h4>
	<p class="download-caption">{$file_caption}</p>
	<input type="hidden" name="file" value="{$file_src}">
	<input type="hidden" name="csrf_token" value="{$csrf_token}">
	<button class="btn btn-success" type="submit" name="download" value="{$file_src}">
		<i class="bi-arrow-down-circle"></i> Download
	</button>
	{$hidden_csrf_token}
</form>