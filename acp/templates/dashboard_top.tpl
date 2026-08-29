<p class="dash-group-label">{label_content}</p>
<div class="row g-3 row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-5">

	<div>
	<section class="card dash-card-accent-border" style="--card-color: var(--section-color-contents)">
		<div class="card-header d-flex align-items-center gap-2">
			<span class="dash-card-icon">{icon_pages}</span>
			<a href="/admin/pages/" class="dash-card-title h6 mb-0 flex-grow-1">{tab_pages}</a>
			<span id="countPages" class="dash-card-count" hx-get="/admin-xhr/counter/read/?count=pages" hx-trigger="load">0</span>
		</div>
		<div class="card-body p-0">
			<div id="getPages" class="p-0 scroll-container scroll-container-h240"
				 hx-get="{reader_uri}?action=list_pages"
				 hx-trigger="load">
				<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>
			</div>
		</div>
		<div class="card-footer d-flex gap-2">{btn_page_overview} {btn_new_page}</div>
	</section>
	</div>

	<div>
	<section class="card dash-card-accent-border" style="--card-color: var(--section-color-contents)">
		<div class="card-header d-flex align-items-center gap-2">
			<span class="dash-card-icon">{icon_snippets}</span>
			<a href="/admin/snippets/" class="dash-card-title h6 mb-0 flex-grow-1">{tab_snippets}</a>
			<span id="countSnippets" class="dash-card-count" hx-get="/admin-xhr/counter/read/?count=snippets" hx-trigger="load">0</span>
		</div>
		<div class="card-body p-0">
			<div id="getSnippets" class="p-0 scroll-container scroll-container-h240"
				 hx-get="{reader_uri}?action=list_snippets"
				 hx-trigger="load">
				<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>
			</div>
		</div>
		<div class="card-footer d-flex gap-2">{btn_snippets_overview} {btn_snippets_new}</div>
	</section>
	</div>

	<div>
	<section class="card dash-card-accent-border" style="--card-color: var(--section-color-blog)">
		<div class="card-header d-flex align-items-center gap-2">
			<span class="dash-card-icon">{icon_blog}</span>
			<a href="/admin/blog/" class="dash-card-title h6 mb-0 flex-grow-1">{tab_blog}</a>
			<span id="countPosts" class="dash-card-count" hx-get="/admin-xhr/counter/read/?count=posts" hx-trigger="load">0</span>
		</div>
		<div class="card-body p-0">
			<div id="getPosts" class="p-0 scroll-container scroll-container-h240"
				 hx-get="{reader_uri}?action=list_posts"
				 hx-trigger="load">
				<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>
			</div>
		</div>
		<div class="card-footer d-flex gap-2">{btn_blog_overview} {btn_blog_new}</div>
	</section>
	</div>

	<div>
	<section class="card dash-card-accent-border" style="--card-color: var(--section-color-shop)">
		<div class="card-header d-flex align-items-center gap-2">
			<span class="dash-card-icon">{icon_products}</span>
			<a href="/admin/shop/" class="dash-card-title h6 mb-0 flex-grow-1">{tab_products}</a>
			<span id="countProducts" class="dash-card-count" hx-get="/admin-xhr/counter/read/?count=products" hx-trigger="load">0</span>
		</div>
		<div class="card-body p-0">
			<div id="getProducts" class="p-0 scroll-container scroll-container-h240"
				 hx-get="{reader_uri}?action=list_products"
				 hx-trigger="load">
				<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>
			</div>
		</div>
		<div class="card-footer d-flex gap-2">{btn_products_overview} {btn_products_new}</div>
	</section>
	</div>

	<div>
	<section class="card dash-card-accent-border" style="--card-color: var(--section-color-events)">
		<div class="card-header d-flex align-items-center gap-2">
			<span class="dash-card-icon">{icon_events}</span>
			<a href="/admin/events/" class="dash-card-title h6 mb-0 flex-grow-1">{tab_events}</a>
			<span id="countEvents" class="dash-card-count" hx-get="/admin-xhr/counter/read/?count=events" hx-trigger="load">0</span>
		</div>
		<div class="card-body p-0">
			<div id="getEvents" class="p-0 scroll-container scroll-container-h240"
				 hx-get="{reader_uri}?action=list_events"
				 hx-trigger="load">
				<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>
			</div>
		</div>
		<div class="card-footer d-flex gap-2">{btn_events_overview} {btn_events_new}</div>
	</section>
	</div>

</div>

<p class="dash-group-label">{label_system}</p>
<div class="row g-3 row-cols-1 row-cols-md-2 row-cols-xxl-4">

	<div>
	<section class="card" style="--card-color: var(--section-color-user)">
		<div class="card-header d-flex align-items-center gap-2">
			<span class="dash-card-icon">{icon_user}</span>
			<a href="/admin/users/" class="dash-card-title h6 mb-0 flex-grow-1">{tab_user}</a>
			<span id="countUser" class="dash-card-count" hx-get="/admin-xhr/counter/read/?count=users" hx-trigger="load">0</span>
		</div>
		<div class="card-body p-0">
			<div id="getUser" class="p-0 scroll-container scroll-container-h240"
				 hx-get="{reader_uri}?action=list_user"
				 hx-trigger="load">
				<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>
			</div>
		</div>
		<div class="card-footer d-flex gap-2">{btn_user_overview} {btn_new_user}</div>
	</section>
	</div>

	<div>
	<section class="card" style="--card-color: var(--section-color-shop)">
		<div class="card-header d-flex align-items-center gap-2">
			<span class="dash-card-icon">{icon_orders}</span>
			<h2 class="h6 mb-0 flex-grow-1">{tab_orders}</h2>
			<span id="countOrders" class="dash-card-count" hx-get="/admin-xhr/counter/read/?count=orders" hx-trigger="load">0</span>
		</div>
		<div class="card-body p-0">
			<div id="getOrdersCard" class="p-0 scroll-container scroll-container-h240"
				 hx-get="{reader_uri}?action=list_orders"
				 hx-trigger="load">
				<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>
			</div>
		</div>
		<div class="card-footer d-flex gap-2">{btn_orders_overview}</div>
	</section>
	</div>

	<div>
	<section class="card" style="--card-color: var(--section-color-system)">
		<div class="card-header d-flex align-items-center gap-2">
			<span class="dash-card-icon">{icon_activity}</span>
			<h2 class="h6 mb-0">{label_activity}</h2>
		</div>
		<div class="card-body p-0">
			<div id="getLogfile" class="p-0 scroll-container scroll-container-h240"
				 hx-get="{reader_uri}?action=list_logfile"
				 hx-trigger="load">
				<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>
			</div>
		</div>
		<div class="card-footer d-flex gap-2">{btn_reload_activity}</div>
	</section>
	</div>

	<div>
	<section class="card" style="--card-color: var(--section-color-system)">
		<div class="card-header d-flex align-items-center gap-2">
			<span class="dash-card-icon">{icon_alerts}</span>
			<h2 class="h6 mb-0">{label_alerts}</h2>
		</div>
		<div class="card-body p-0">
			<div id="getAlerts" class="p-0 scroll-container scroll-container-h240"
				 hx-get="{reader_uri}?action=list_alerts"
				 hx-trigger="load">
				<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>
			</div>
		</div>
		<div class="card-footer d-flex gap-2">{btn_reload_alerts}</div>
	</section>
	</div>


</div>

<div class="row g-3 row-cols-1 row-cols-md-2 row-cols-lg-3 mt-3">

	<div>
	<section class="card" style="--card-color: var(--section-color-addons)">
		<div class="card-header d-flex align-items-center gap-2">
			<span class="dash-card-icon">{icon_addons}</span>
			<h2 class="h6 mb-0 flex-grow-1">{tab_addons}</h2>
			<span id="countAddons" class="dash-card-count" hx-get="/admin-xhr/counter/read/?count=addons" hx-trigger="load">0</span>
		</div>
		<div class="card-body p-0">
			<div id="getAddons" class="p-0 scroll-container scroll-container-h240"
				 hx-get="{reader_uri}?action=list_addons"
				 hx-trigger="load">
				<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>
			</div>
		</div>
		<div class="card-footer d-flex gap-2">{btn_addons_overview}</div>
	</section>
	</div>

	<div>
	<section class="card" style="--card-color: var(--section-color-system)">
		<div class="card-header d-flex align-items-center gap-2">
			<span class="dash-card-icon">{icon_cache}</span>
			<h2 class="h6 mb-0">{tab_cache}</h2>
		</div>
		<div class="card-body p-0">
			<div id="getCache" class="p-0 scroll-container scroll-container-h240"
				 hx-get="{reader_uri}?action=list_cache"
				 hx-trigger="load, cache_rebuilt from:body">
				<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>
			</div>
		</div>
		<div class="card-footer d-flex align-items-center gap-2">{btn_cache_rebuild_all}</div>
	</section>
	</div>

	<div>
	<section class="card" style="--card-color: var(--section-color-system)">
		<div class="card-header d-flex align-items-center gap-2">
			<span class="dash-card-icon">{icon_settings}</span>
			<h2 class="h6 mb-0">{label_settings}</h2>
		</div>
		<div class="card-body p-0">
			<div id="getInfos" class="p-0 scroll-container scroll-container-h240"
				 hx-get="{reader_uri}?action=list_infos"
				 hx-trigger="load">
				<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>
			</div>
		</div>
		<div class="card-footer d-flex gap-2">{btn_settings_overview}</div>
	</section>
	</div>

</div>
