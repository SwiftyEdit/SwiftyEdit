<form action='{formaction}' class='form-horizontal' id='editpage' method='post' name="editpage">
	<div class="row">
		<div class="col-md-9">
			<div class="card">
				<div class="card-header">
			<ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">
				<li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#intro">{nav_btn_intro}</a></li>
				<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#content">{nav_btn_content}</a></li>
				<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#file">{nav_btn_files}</a></li>
				<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#meta">{nav_btn_metas}</a></li>
				<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#prefs">{nav_btn_settings}</a></li>
			</ul>
				</div>
				<div class="card-body">
			<div class="tab-content">
				<div class="tab-pane fade show active" id="intro">
					<div class="row">
						<div class="col-md-6">
							<label>{label_title}</label> <input class="form-control" name="post_title" type="text" value="{post_title}"> <label>{label_description}</label> 
							<textarea class='mceEditor_small' name='post_teaser'>{post_teaser}</textarea>
						</div>
						<div class="col-md-6">
							<div class="well well-sm">
								<label>{label_image}</label> <input class="filter-images form-control" name="filter-images" placeholder="Filter ..." type="text">
								<div class="images-list scroll-container">
									{widget_images}
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="tab-pane fade" id="content">
					<textarea class='mceEditor' name='post_text'>{post_text}</textarea>
				</div>
				
				<div class="tab-pane fade" id="file">
					
					<div class="row">
						<div class="col-md-8">
							<div class="form-group">
								<label>{label_file_select}</label>
								{select_file}
							</div>
						</div>
						<div class="col-md-4">				
							<div class="form-group">
								<label>{label_file_version}</label>
								<input class='form-control' name="post_file_version" type="text" value="{post_file_version}">
							</div>
						</div>
					</div>

					<div class="form-group">
						<label>{label_external_file}</label>
						<input class='form-control' name="post_file_attachment_external" type="text" value="{post_file_attachment_external}">
					</div>
									
					<div class="form-group">
						<label>{label_license}</label>
						<input class='form-control' name="post_file_license" type="text" value="{post_file_license}">
					</div>
					
				</div> <!-- #file -->

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
						<input type="text" class='form-control' name="post_tags" data-role="tagsinput" value="{post_tags}">
					</div>		
				</div>
				
				<div class="tab-pane fade" id="prefs">
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
					
					<fieldset>
						<legend>RSS</legend>
						<div class="form-group">
							<label>{label_rss}</label>
							{select_rss}
						</div>
						<div class="form-group">
							<label>{label_rss_url}</label>
							<input class='form-control' name="post_rss_url" type="text" value="{post_rss_url}">
						</div>
					</fieldset>
					
				</div><!-- #prefs -->
			</div>
		</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card p-3">
				<div class="mb-3 pb-3 border-bottom">
					<label>{label_language}</label>
					{checkboxes_lang}
				</div>
				<div class="mb-3 pb-3 border-bottom">
					<label>{label_categories}</label>
					<div class="scroll-container" style="max-height: 150px;">
						{checkbox_categories}
					</div>
				</div>
				<div class="mb-3 pb-3 border-bottom">
					<label>{label_releasedate}</label>
					<input class='dp form-control' name="post_releasedate" type="text" value="{post_releasedate}">
				</div>
				<div class="mb-3 pb-3 border-bottom">
					<label>{label_priority}</label>
					<input type="number" name="post_priority" value="{post_priority}" class="form-control">
					{checkbox_fixed}
				</div>
				<div class="mb-3 pb-3 border-bottom">
					<label>{label_status}</label>
					{select_status}
				</div>
				<div class="mb-3 pb-3 border-bottom">
					<label>{label_comments}</label>
					{select_comments}
				</div>
				<div class="mb-3 pb-3 border-bottom">
					<label>{label_votings}</label>
					{select_votings}
				</div>
				<div class="mb-3 pb-3 border-bottom">
					<label>{labels}</label>
					<div>{post_labels}</div>
				</div>
				<input name="post_type" type="hidden" value="{post_type}">
				<input name="modus" type="hidden" value="{modus}">
				<input name="post_id" type="hidden" value="{post_id}">
				<input type="hidden" name="csrf_token" value="{token}">
				<input type="hidden" name="post_date" value="{post_date}">
				{submit_button}
			</div>
		</div>
	</div>
</form>