<form action='{formaction}' class='form-horizontal' id='editpage' method='post' name="editpage">
	<div class="row">
		<div class="col-md-9">
			<div class="card">
				<div class="card-header">
			<ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">
				<li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#content">{nav_btn_content}</a></li>
				<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#meta">{nav_btn_seo}</a></li>
				<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#prefs">{nav_btn_settings}</a></li>
					<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#addons" title="{nav_btn_addons}">{icon_addons}</a></li>
			</ul>
			</div>
			<div class="card-body">
			<div class="tab-content">
				<div class="tab-pane fade show active" id="content">

					<div class="row">
						<div class="col-md-8">
					<div class="mb-3">
						<label for="inputUrl" class="form-label">{label_url}</label>
						<input id="inputUrl" class="form-control" name="post_link" type="text" value="{post_link}">
					</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label for="inputLinkText" class="form-label">{label_text}</label>
								<input id="inputLinkText" class="form-control" name="post_link_text" type="text" value="{post_link_text}">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<label for="inputTitle" class="form-label">{label_title}</label>
								<input id="inputTitle" class="form-control" name="post_title" type="text" value="{post_title}">
							</div>
							<div class="mb-3">
								<label for="inputTeaser" class="form-label">{label_description}</label>
								<textarea id="inputTeaser" class='mceEditor_small' name='post_teaser'>{post_teaser}</textarea>
							</div>
						</div>
						<div class="col-md-6">
							<label class="form-label">{label_image}</label>
							<input class="filter-images form-control" name="filter-images" placeholder="Filter ..." type="text">
							<div class="images-list scroll-container">
								{widget_images}
							</div>
						</div>
					</div>
				</div>
				
				<div class="tab-pane fade" id="meta">
					<div class="mb-3">
						<label for="inputMetaTitle" class="form-label">{label_title}</label>
						<input id="inputMetaTitle" class='form-control' name="post_meta_title" type="text" value="{post_meta_title}">
					</div>
					<div class="mb-3">
						<label for="inputMetaDescription" class="form-label">{label_description}</label>
						<textarea id="inputMetaDescription" class='form-control count-chars' rows="4" name="post_meta_description">{post_meta_description}</textarea>
					</div>
					<div class="mb-3">
						<label class="form-label">{label_keywords}</label>
						<input type="text" class='form-control tags' name="post_tags" value="{post_tags}">
					</div>

					<div class="row">
						<div class="col-md-4">
							<div class="mb-2">
								<label>{label_pages_permalink}</label>
								<input type="text" id="disabledTextInput" class="form-control" value="{se_base_url}" disabled>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-2">
								<label>{label_main_category_page}</label>
								{select_main_cat_page}
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-2">
								<label class="form-label">{label_slug}</label>
								<input class='form-control' name="post_slug" type="text" value="{post_slug}">
							</div>
						</div>
					</div>

					<div class="mb-3">
						<label for="inputCanonicalUrl" class="form-label">Canonical URL {canonical_url_tooltip}</label>
						<input id="inputCanonicalUrl" class='form-control' name="post_canonical_url" type="text" value="{post_canonical_url}">
					</div>
				</div>

				<div class="tab-pane fade" id="prefs">
					<div class="mb-3">
						<label for="inputAuthor" class="form-label">{label_author}</label>
						<input id="inputAuthor" class='form-control' name="post_author" type="text" value="{post_author}">
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

				<div class="tab-pane fade" id="addons">
					{addon_module_forms}
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