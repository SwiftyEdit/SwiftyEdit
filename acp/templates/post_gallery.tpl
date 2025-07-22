<form action='{formaction}' class='form-horizontal' id='editpage' method='post' name="editpage">
	<div class="row">
		<div class="col-md-9">
			<div class="card">
				<div class="card-header">
					<ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">
						<li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#content">{nav_btn_content}</a></li>
						<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#meta">{nav_btn_metas}</a></li>
						<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#preferences">{nav_btn_settings}</a></li>
					</ul>
				</div>
				<div class="card-body">
					<div class="tab-content">
						<div class="tab-pane show fade active" id="content">
					<div class="row">
						<div class="col-md-8">
							<label>{label_title}</label> <input class="form-control" name="post_title" type="text" value="{post_title}"><br>
							<label>{label_description}</label> 
							<textarea class='mceEditor_small' name='post_teaser'>{post_teaser}</textarea>						
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>{label_upload}</label>
								<button type="button" class="w-100 btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadGalModal" {disabled_upload_btn}>{btn_upload}</button>
								<p class="form-text text-muted">{msg_info_gallery_upload}</p>
							</div>
						</div>
					</div>
					<!-- if we have uploaded images, show a thumbnail list -->
					{thumbnail_list_form}
				</div>
				
				<div class="tab-pane fade" id="meta">
					<div class="form-group">
						<label>{label_title}</label>
						<input class='form-control' name="post_meta_title" type="text" value="{post_meta_title}">
					</div>
					<div class="form-group">
						<label>{label_description}</label>
						<textarea class='form-control' rows="4" name="post_meta_description">{post_meta_description}</textarea>
					</div>
					<div class="form-group">
						<label>{label_keywords}</label>
						<input type="text" class='form-control tags' name="post_tags" value="{post_tags}">
					</div>		
				</div>
				
				<div class="tab-pane fade" id="preferences">
					<div class="form-group">
						<label>{label_author}</label>
						<input class='form-control' name="post_author" type="text" value="{post_author}">
					</div>
					<div class="form-group">
						<label>{label_source}</label>
						<input class='form-control' name="post_source" type="text" value="{post_source}">
					</div>
					<div class="form-group">
						<label>{label_slug}</label>
						<input class='form-control' name="post_slug" type="text" value="{post_slug}">
					</div>

					<h5 class="heading-line">RSS</h5>
					<div class="mb-3">
						{select_rss}
					</div>
					<div class="mb-3">
						<label for="inputRssUrl" class="form-label">{label_rss_url}</label>
						<input id="inputRssUrl" class='form-control' name="post_rss_url" type="text" value="{post_rss_url}">
					</div>
				</div>
			</div>
		</div>
		</div>
		

		
		</div>
		<div class="col-md-3">
			{sidebar}
		</div>
	</div>
</form>

<div class="container p-3">
<div class="card p-3">
<div id="listGalThumbs" hx-get="/admin-xhr/blog/read/?gallery_thumbs={post_id}" hx-trigger="load, update_gallery_thumbs from:body">Loading thumbnails ...</div>
</div>
</div>

<!-- if we have a gallery id, show the upload form -->
{modal_upload_form}



