<form action='{formaction}' class='form-horizontal' id='editpage' method='post' name="editpage">
	<div class="row">
		<div class="col-md-9">
			<div class="card">
				<div class="card-header">
			<ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">
				<li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#intro">{nav_btn_intro}</a></li>
				<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#content">{nav_btn_content}</a></li>
				<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#info">{nav_btn_info}</a></li>
				<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#prices">{nav_btn_prices}</a></li>
				<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#meta">{nav_btn_metas}</a></li>
				<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#prefs">{nav_btn_settings}</a></li>
			</ul>
				</div>
				<div class="card-body">
			<div class="tab-content">
				<div class="tab-pane fade show active" id="intro">
					<div class="row">
						<div class="col-md-6">
							<label>{label_title}</label>
							<input class="form-control" name="title" type="text" value="{title}">
							{input_teaser}
						</div>
						<div class="col-md-6">
							{widget_images}
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="content">
					{input_text}
				</div>
				<div class="tab-pane fade" id="info">
					<fieldset>
						<legend>{label_event_dates}</legend>
					
						<div class="row">
							<div class="col">
						<div class="input-group mb-2">
							<div class="input-group-prepend"><span class="input-group-text">Beginn</span></div>
							<input class='dp form-control' name="event_start" type="datetime-local" value="{event_start}">
						</div>
							</div>
							<div class="col">
						<div class="input-group">
							<div class="input-group-prepend"><span class="input-group-text">Ende</span></div>
							<input class='dp form-control' name="event_end" type="datetime-local" value="{event_end}">
						</div>
						</div>
						</div>
					</fieldset>
					<fieldset>
						<legend>{label_location}</legend>
					
						<div class="row">
							<div class="col-md-9">
								<div class="form-group">
									<label>{label_street}</label>
									<input class="form-control" name="event_street" type="text" value="{event_street}">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>{label_nr}</label>
									<input class="form-control" name="event_street_nbr" type="text" value="{event_street_nbr}">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label>{label_zip}</label>
									<input class="form-control" name="event_zip" type="text" value="{event_zip}">
								</div>
							</div>
							<div class="col-md-9">
								<div class="form-group">
									<label>{label_town}</label>
									<input class="form-control" name="event_city" type="text" value="{event_city}">
								</div>
							</div>
						</div>

					</fieldset>
					
					<fieldset>
						<legend>{label_guestlist}</legend>
						
						<div class="form-group">
							{select_guestlist}
						</div>
						<hr>
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="radio" name="event_guestlist_public_nbr" value="1" id="event_guestlist_public_nbr_yes" {checked_gl_public_nbr_1}>
							<label class="form-check-label" for="event_guestlist_public_nbr_yes">{label_guestlist_show_number}</label>
						</div>
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="radio" name="event_guestlist_public_nbr" value="2" id="event_guestlist_public_nbr_no" {checked_gl_public_nbr_2}>
							<label class="form-check-label" for="event_guestlist_public_nbr_no">{label_guestlist_hide_number}</label>
						</div>
						<hr>
						<div class="form-group">
							<label>{label_guestlist_limit}</label>
							<input class="form-control" name="event_guestlist_limit" type="text" value="{event_guestlist_limit}">
						</div>
						
					</fieldset>
					
				</div>
				<div class="tab-pane fade" id="prices">
						
						<label>{label_price_note}</label>
					{input_price_note}
						
				</div>
				
				<div class="tab-pane fade" id="meta">
					<div class="form-group">
						<label>{label_title}</label>
						<input class='form-control' name="meta_title" type="text" value="{meta_title}">
					</div>
					<div class="form-group">
						<label>{label_description}</label>
						<textarea class='form-control' rows="4" name="meta_description">{meta_description}</textarea>
					</div>
					<div class="form-group">
						<label>{label_keywords}</label>
						<input type="text" class='form-control tags' name="tags" value="{tags}">
					</div>
				</div>
				
				<div class="tab-pane fade" id="prefs">
					<div class="form-group">
						<label>{label_author}</label>
						<input class='form-control' name="author" type="text" value="{author}">
					</div>
					<div class="form-group">
						<label>{label_slug}</label>
						<input class='form-control' name="slug" type="text" value="{slug}">
					</div>

					<fieldset>
						<legend>RSS</legend>
						<div class="form-group">
							<label>{label_rss}</label>
							{select_rss}
						</div>
						<div class="form-group">
							<label>{label_rss_url}</label>
							<input class='form-control' name="rss_url" type="text" value="{rss_url}">
						</div>
					</fieldset>

				</div><!-- #prefs -->
			</div>
		</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card p-3">
				<div class="mb-1 pb-1 border-bottom">
					{select_language}
				</div>
				<div class="mb-2 pb-1 border-bottom">
					<label>{label_categories}</label>
					<div class="scroll-container" style="max-height: 150px;">
						{checkbox_categories}
					</div>
				</div>
				<div class="mb-2 pb-1 border-bottom">
					<label>{label_releasedate}</label>
					<input class='dp form-control' name="post_releasedate" type="datetime-local" value="{post_releasedate}">
				</div>
				<div class="mb-2 pb-1 border-bottom">
					<label>{label_priority}</label>
					<input type="number" name="priority" value="{priority}" class="form-control">
					{checkbox_fixed}
				</div>
				<div class="mb-2 pb-1 border-bottom">
					{select_status}
				</div>
				<div class="mb-2 pb-1 border-bottom">
					{select_comments}
				</div>
				<div class="mb-2 pb-1 border-bottom">
					{select_votings}
				</div>
				<div class="mb-2 pb-1 border-bottom">
					<label>{labels}</label>
					<div>{post_labels}</div>
				</div>
				<input name="type" type="hidden" value="e">
				<input name="mode" type="hidden" value="{mode}">
				<input name="id" type="hidden" value="{id}">
				<input type="hidden" name="csrf_token" value="{token}">
				<input type="hidden" name="date" value="{date}">
				{submit_button}
			</div>
		</div>
	</div>
</form>