{if $no_results_at_all}
<div class="alert alert-info">{$msg_no_search_results}</div>
{else}
<div class="row">
	{if $pages_total > 0}
	<div class="col-md-6">
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<span>{$lang_tagged_section_pages} ({$pages_total})</span>
				{if $show_pages_pagination}
					<form action="{$search_uri}" method="GET" class="d-flex gap-1">
						<input type="hidden" name="s" value="{$search_string}">
						<input type="hidden" name="products_page" value="{$products_page}">
						<input type="hidden" name="posts_page" value="{$posts_page}">
						<input type="hidden" name="events_page" value="{$events_page}">
						<button class="btn btn-sm btn-outline-secondary" type="submit" name="pages_page" value="{$pages_prev_page}" {if $pages_page == $pages_prev_page}disabled{/if}><i class="bi bi-arrow-left"></i></button>
						<button class="btn btn-sm btn-outline-secondary" type="submit" name="pages_page" value="{$pages_next_page}" {if $pages_page == $pages_next_page}disabled{/if}><i class="bi bi-arrow-right"></i></button>
					</form>
				{/if}
			</div>
			<ul class="list-group list-group-flush">
				{foreach $pages as $page}
					<li class="list-group-item d-flex align-items-center gap-2">
						{if $page.thumbnail_src != ""}
							<img src="{$page.thumbnail_src}" alt="{$page.thumbnail_src}" class="flex-shrink-0" style="width:60px;height:60px;object-fit:cover;">
						{/if}
						<div class="flex-grow-1">
							<div class="fw-semibold">{$page.title}</div>
							{if $page.description != ""}<div class="text-muted small">{$page.description}</div>{/if}
						</div>
						<a href="{$page.href}" class="stretched-link"></a>
					</li>
				{/foreach}
			</ul>
		</div>
	</div>
	{/if}
	<div class="col-md-6">

		{if $products_total > 0}
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<span>{$lang_tagged_section_products} ({$products_total})</span>
				{if $show_products_pagination}
					<form action="{$search_uri}" method="GET" class="d-flex gap-1">
						<input type="hidden" name="s" value="{$search_string}">
						<input type="hidden" name="pages_page" value="{$pages_page}">
						<input type="hidden" name="posts_page" value="{$posts_page}">
						<input type="hidden" name="events_page" value="{$events_page}">
						<button class="btn btn-sm btn-outline-secondary" type="submit" name="products_page" value="{$products_prev_page}" {if $products_page == $products_prev_page}disabled{/if}><i class="bi bi-arrow-left"></i></button>
						<button class="btn btn-sm btn-outline-secondary" type="submit" name="products_page" value="{$products_next_page}" {if $products_page == $products_next_page}disabled{/if}><i class="bi bi-arrow-right"></i></button>
					</form>
				{/if}
			</div>
			<ul class="list-group list-group-flush">
				{foreach $products as $product}
					<li class="list-group-item d-flex align-items-center gap-2">
						{if $product.thumbnail_src != ""}
							<img src="{$product.thumbnail_src}" alt="{$product.thumbnail_src}" class="flex-shrink-0" style="width:60px;height:60px;object-fit:cover;">
						{/if}
						<div class="flex-grow-1">
							<div class="fw-semibold">
								{$product.meta_title}
								{if $product.number}<span class="border rounded px-1 text-secondary small ms-1">{$product.number}</span>{/if}
							</div>
							{if $product.description != ""}<div class="text-muted small">{$product.description}</div>{/if}
						</div>
						<div class="text-end text-nowrap ps-2">
							{if $product.price_tag_label_from != ''}<small class="text-muted d-block">{$product.price_tag_label_from}</small>{/if}
							{$product.price_tag} {$product.product_currency}
						</div>
						<a href="{$product.href}" class="stretched-link"></a>
					</li>
				{/foreach}
			</ul>
		</div>
		{/if}

		{if $posts_total > 0}
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<span>{$lang_tagged_section_posts} ({$posts_total})</span>
				{if $show_posts_pagination}
					<form action="{$search_uri}" method="GET" class="d-flex gap-1">
						<input type="hidden" name="s" value="{$search_string}">
						<input type="hidden" name="pages_page" value="{$pages_page}">
						<input type="hidden" name="products_page" value="{$products_page}">
						<input type="hidden" name="events_page" value="{$events_page}">
						<button class="btn btn-sm btn-outline-secondary" type="submit" name="posts_page" value="{$posts_prev_page}" {if $posts_page == $posts_prev_page}disabled{/if}><i class="bi bi-arrow-left"></i></button>
						<button class="btn btn-sm btn-outline-secondary" type="submit" name="posts_page" value="{$posts_next_page}" {if $posts_page == $posts_next_page}disabled{/if}><i class="bi bi-arrow-right"></i></button>
					</form>
				{/if}
			</div>
			<ul class="list-group list-group-flush">
				{foreach $posts as $post}
					<li class="list-group-item d-flex align-items-center gap-2">
						{if $post.thumbnail_src != ""}
							<img src="{$post.thumbnail_src}" alt="{$post.thumbnail_src}" class="flex-shrink-0" style="width:60px;height:60px;object-fit:cover;">
						{/if}
						<div class="flex-grow-1">
							<div class="fw-semibold">{$post.title}</div>
							{if $post.description != ""}<div class="text-muted small">{$post.description}</div>{/if}
						</div>
						<a href="{$post.href}" class="stretched-link"></a>
					</li>
				{/foreach}
			</ul>
		</div>
		{/if}

		{if $events_total > 0}
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<span>{$lang_tagged_section_events} ({$events_total})</span>
				{if $show_events_pagination}
					<form action="{$search_uri}" method="GET" class="d-flex gap-1">
						<input type="hidden" name="s" value="{$search_string}">
						<input type="hidden" name="pages_page" value="{$pages_page}">
						<input type="hidden" name="products_page" value="{$products_page}">
						<input type="hidden" name="posts_page" value="{$posts_page}">
						<button class="btn btn-sm btn-outline-secondary" type="submit" name="events_page" value="{$events_prev_page}" {if $events_page == $events_prev_page}disabled{/if}><i class="bi bi-arrow-left"></i></button>
						<button class="btn btn-sm btn-outline-secondary" type="submit" name="events_page" value="{$events_next_page}" {if $events_page == $events_next_page}disabled{/if}><i class="bi bi-arrow-right"></i></button>
					</form>
				{/if}
			</div>
			<ul class="list-group list-group-flush">
				{foreach $events as $event}
					<li class="list-group-item d-flex align-items-center gap-2">
						{if $event.thumbnail_src != ""}
							<img src="{$event.thumbnail_src}" alt="{$event.thumbnail_src}" class="flex-shrink-0" style="width:60px;height:60px;object-fit:cover;">
						{/if}
						<div class="flex-grow-1">
							<div class="fw-semibold">{$event.title}</div>
							{if $event.description != ""}<div class="text-muted small">{$event.description}</div>{/if}
						</div>
						<a href="{$event.href}" class="stretched-link"></a>
					</li>
				{/foreach}
			</ul>
		</div>
		{/if}

	</div>
</div>
{/if}
