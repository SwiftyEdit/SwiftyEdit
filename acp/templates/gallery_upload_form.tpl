<div class="modal fade" id="uploadGalModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload into Gallery ID #{post_id}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>
      <div class="modal-body">
				<form method="post" action="/admin/upload/" id="dropper" class="dropper-form">
					<div class="fallback">
						<input name="file" type="file" multiple />
					</div>
					<input type="hidden" name="gal" value="{post_id}">
					<input type="hidden" name="post_year" value="{post_year}">
					<input type="hidden" name="w" value="{max_img_width}">
					<input type="hidden" name="w_tmb" value="{max_tmb_width}">
					<input type="hidden" name="h" value="{max_img_height}">
					<input type="hidden" name="h_tmb" value="{max_tmb_height}">
					<input type="hidden" name="csrf_token" value="{token}">
					<input type="hidden" name="post_id" value="{post_id}">
				</form>
      </div>
    </div>
  </div>
</div>