<div class="row gx-4">
	<div class="col-8">
		<div class="card h-100">
			<div class="card-header">
				<ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" href="#" data-bs-target="#pages_list" data-bs-toggle="tab">
							{tab_pages}
							<span id="countPages" hx-get="/admin/counter/read/?count=pages" hx-trigger="load" class="badge bg-secondary"></span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#" data-bs-target="#snippets_list" data-bs-toggle="tab">
							{tab_snippets}
							<span id="countPages" hx-get="/admin/counter/read/?count=snippets" hx-trigger="load" class="badge bg-secondary">0</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#" data-bs-target="#post_list" data-bs-toggle="tab">
							{tab_blog}
							<span id="countPosts" hx-get="/admin/counter/read/?count=posts" hx-trigger="load" class="badge bg-secondary">0</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#" data-bs-target="#products_list" data-bs-toggle="tab">
							{tab_products}
							<span id="countProducts" hx-get="/admin/counter/read/?count=products" hx-trigger="load" class="badge bg-secondary">0</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#" data-bs-target="#events_list" data-bs-toggle="tab">
							{tab_events}
							<span id="countEvents" hx-get="/admin/counter/read/?count=events" hx-trigger="load" class="badge bg-secondary">0</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#" data-bs-target="#comment_list" data-bs-toggle="tab">
							{tab_comments}
							<span id="countComments" hx-get="/admin/counter/read/?count=comments" hx-trigger="load" class="badge bg-secondary">0</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#" data-bs-target="#user_list" data-bs-toggle="tab">
							{tab_user}
							<span id="countUser" hx-get="/admin/counter/read/?count=users" hx-trigger="load" class="badge bg-secondary">0</span>
						</a>
					</li>
				</ul>
			</div>
			<div class="card-body">
				<div class="tab-content h-100">
					<div class="tab-pane h-100 fade show active" id="pages_list">
						<div class="d-flex flex-column h-100">
							<div id="getPages" class="p-1"
								 hx-post="{reader_uri}?action=list_pages"
								 hx-trigger="load"
								 hx-include="[name='csrf_token']">
							</div>
							<div class="row mt-auto g-1">
								<div class="col">{btn_page_overview}</div>
								<div class="col">{btn_new_page}</div>
								<div class="col-2">{btn_update_index}</div>
								<div class="col-2">{btn_delete_cache}</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade h-100" id="snippets_list">
						<div class="d-flex flex-column h-100">
							<div id="getSnippets" class="p-1"
								 hx-post="{reader_uri}?action=list_snippets"
								 hx-trigger="load"
								 hx-include="[name='csrf_token']">
							</div>
							<div class="row mt-auto g-1">
								<div class="col-2">{btn_snippets_overview}</div>
								<div class="col-2">{btn_snippets_new}</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade h-100" id="post_list">
						<div class="d-flex flex-column h-100">
							<div id="getPosts" class="p-1"
								 hx-post="{reader_uri}?action=list_posts"
								 hx-trigger="load"
								 hx-include="[name='csrf_token']">
							</div>
							<div class="row mt-auto g-1">
								<div class="col-2">{btn_blog_overview}</div>
								<div class="col-2">{btn_blog_new}</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade h-100" id="products_list">
						<div class="d-flex flex-column h-100">
							<div id="getProducts" class="p-1"
								 hx-post="{reader_uri}?action=list_products"
								 hx-trigger="load"
								 hx-include="[name='csrf_token']">
							</div>
							<div class="row mt-auto g-1">
								<div class="col-2">{btn_products_overview}</div>
								<div class="col-2">{btn_products_new}</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade h-100" id="events_list">
						<div class="d-flex flex-column h-100">
							<div id="getEvents" class="p-1"
								 hx-post="{reader_uri}?action=list_events"
								 hx-trigger="load"
								 hx-include="[name='csrf_token']">
							</div>
							<div class="row mt-auto g-1">
								<div class="col-2">{btn_events_overview}</div>
								<div class="col-2">{btn_events_new}</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade h-100" id="comment_list">
						<div class="d-flex flex-column h-100">
							<div id="getComments" class="p-1"
								 hx-post="{reader_uri}?action=list_comments"
								 hx-trigger="load"
								 hx-include="[name='csrf_token']">
							</div>
							<div class="row mt-auto g-1">
								<div class="col-2">{btn_comments_overview}</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade h-100" id="user_list">
						<div class="d-flex flex-column h-100">
							<div id="getUser" class="p-1"
								 hx-post="{reader_uri}?action=list_user"
								 hx-trigger="load"
								 hx-include="[name='csrf_token']">
							</div>
							<div class="row mt-auto g-1">
								<div class="col-2">{btn_user_overview}</div>
								<div class="col-2">{btn_user_new}</div>
								<div class="col-2">{btn_usergroups_overview}</div>
							</div>
						</div>
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
							<div id="getLogfile" class="p-1"
								 hx-post="{reader_uri}?action=list_logfile"
								 hx-trigger="load"
								 hx-include="[name='csrf_token']">
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="alerts">
						<div class="scroll-container">
							<div id="getAlerts" class="p-1"
								 hx-post="{reader_uri}?action=list_alerts"
								 hx-trigger="load"
								 hx-include="[name='csrf_token']">
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="info_table">
						<div class="scroll-container">
							<div id="getInfos" class="p-1"
								 hx-post="{reader_uri}?action=list_infos"
								 hx-trigger="load"
								 hx-include="[name='csrf_token']">
							</div>
						</div>
					</div>

			</div>
			</div>
		</div>
	</div>
	</div>
