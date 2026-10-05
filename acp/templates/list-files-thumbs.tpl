<div class="col-md-3 col-xl-2 mb-2">
	<div class="card h-100">
		<div class="card-header p-1 small text-center">{short_filename}</div>
		<div class="position-relative">
			{preview_img}
			<div class="position-absolute bottom-0 start-0 m-1 px-1 rounded bg-dark bg-opacity-75 d-flex gap-1 lh-1">{lang_thumb}</div>
		</div>
		<div class="card-body p-1">
			<p class="m-0"><small>{show_filetime}<br>{filesize}</small></p>
			 {labels}
		</div>
		<div class="card-footer p-1 d-flex justify-content">
			<form action="/admin/uploads/edit/" method="POST" id="{form_id}" class="d-inline-flex">
			{edit_button}
				<input type="hidden" name="file" value="{media_file}">
				<input type="hidden" name="csrf_token" value="{csrf_token}">
			</form>
			<div class="ms-auto">
			{delete_button}
			</div>
		</div>
	</div>
</div>