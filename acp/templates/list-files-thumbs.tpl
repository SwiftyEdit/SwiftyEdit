<div class="col-md-3 col-xl-2 mb-2">
	<div class="card h-100">
		<div class="card-header p-1 small text-center">{short_filename}</div>
		{preview_img}
		<div class="card-body p-1">
			<p class="m-0 text-center"><small>{show_filetime} / {filesize}</small></p>
			 {labels}
		</div>
		<div class="card-footer p-1 d-flex justify-content">
			<form action="/admin/uploads/edit/" method="POST" class="d-inline">
			{edit_button}
				<input type="hidden" name="csrf_token" value="{csrf_token}">
			</form>
			{delete_button}
		</div>
	</div>
</div>