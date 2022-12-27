


	<div class="row gx-4">
	<div class="col-8">
		<div class="card h-100">
			<div class="card-header">
				<ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">
					<li class="nav-item"><a class="nav-link active" href="#" data-bs-target="#pages_list" data-bs-toggle="tab">{tab_pages}</a></li>
					<li class="nav-item"><a class="nav-link" href="#" data-bs-target="#post_list" data-bs-toggle="tab">{tab_blog}</a></li>
					<li class="nav-item"><a class="nav-link" href="#" data-bs-target="#products_list" data-bs-toggle="tab">{tab_products}</a></li>
					<li class="nav-item"><a class="nav-link" href="#" data-bs-target="#events_list" data-bs-toggle="tab">{tab_events}</a></li>
					<li class="nav-item"><a class="nav-link" href="#" data-bs-target="#comment_list" data-bs-toggle="tab">{tab_comments}</a></li>
					<li class="nav-item"><a class="nav-link" href="#" data-bs-target="#user_list" data-bs-toggle="tab">{tab_user}</a></li>
				</ul>
			</div>
			<div class="card-body">
				<div class="tab-content h-100">
					<div class="tab-pane h-100 fade show active" id="pages_list">
						<div class="d-flex flex-column h-100">
							{pages_list}
							<div class="row mt-auto g-1">
								<div class="col">{btn_page_overview}</div>
								<div class="col">{btn_new_page}</div>
								<div class="col-2">{btn_update_index}</div>
								<div class="col-2">{btn_delete_cache}</div>
							</div>
						</div>



					</div>
					<div class="tab-pane fade" id="post_list">
						{posts_list}
					</div>
					<div class="tab-pane fade" id="products_list">
						{products_list}
					</div>
					<div class="tab-pane fade" id="events_list">
						{events_list}
					</div>
					<div class="tab-pane fade" id="comment_list">
						{comments_list}
					</div>
					<div class="tab-pane fade" id="user_list">
						{user_list}
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-4">
		<div class="card h-100">
			<div class="card-header">
				<ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">
					<li class="nav-item"><a class="nav-link active" href="#" data-bs-target="#logfile" data-bs-toggle="tab"><i class="bi bi-card-list"></i></a></li>
					<li class="nav-item"><a class="nav-link" href="#" data-bs-target="#alerts" data-bs-toggle="tab"><i class="bi bi-exclamation-triangle-fill"></i></a></li>
					<li class="nav-item"><a class="nav-link" href="#" data-bs-target="#info_table" data-bs-toggle="tab"><i class="bi bi-info-circle-fill"></i></a></li>
				</ul>
			</div>
			<div class="card-body p-1">
				<div class="tab-content">
					<div class="tab-pane fade show active" id="logfile">
						<div class="scroll-container">
							{dashboard_logfile}
						</div>
					</div>
					<div class="tab-pane fade" id="alerts">
						<div class="scroll-container">
							{dashboard_alerts}
						</div>
					</div>
					<div class="tab-pane fade" id="info_table">
						<div class="scroll-container">
				<table class="table table-sm">
					<tr>
						<td>{label_pages}</td>
						<td>{cnt_all_pages}</td>
					</tr>
					<tr>
						<td>{label_user}</td>
						<td>{cnt_all_user}</td>
					</tr>
					<tr>
						<td>{label_posts}</td>
						<td>{cnt_all_posts}</td>
					</tr>
					<tr>
						<td>{label_products}</td>
						<td>{cnt_all_products}</td>
					</tr>
					<tr>
						<td>{label_events}</td>
						<td>{cnt_all_events}</td>
					</tr>
					<tr>
						<td>{label_comments}</td>
						<td>{cnt_all_comments}</td>
					</tr>
				</table>

				<table class="table table-sm">
					<tr>
						<td>Server:</td>
						<td>{val_server}</td>
					</tr>
					<tr>
						<td>PHP:</td>
						<td>{val_phpversion}</td>
					</tr>
					<tr>
						<td>Database:</td>
						<td>{val_database}</td>
					</tr>
					<tr>
						<td>CMS Domain:</td>
						<td>{val_cms_domain}</td>
					</tr>
					<tr>
						<td>SSL Domain:</td>
						<td>{val_cms_ssl_domain}</td>
					</tr>
					<tr>
						<td>Base:</td>
						<td>{val_base_uri}</td>
					</tr>
					<tr>
						<td>E-Mail:</td>
						<td>{val_cms_mail}</td>
					</tr>
					<tr>
						<td>E-Mail Name:</td>
						<td>{val_cms_email_name}</td>
					</tr>
				</table>
						</div>
					</div>

			</div>
			</div>
		</div>
	</div>
	</div>
