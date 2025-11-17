<div class="row">
	<div class="col-md-6">
		<h6>{$lang_label_pages} {$pages_total}</h6>

		{foreach $pages as $page}
			<div class="card border-top-0 border-start-0 border-end-0 mb-3">
				<div class="row">
					{if $page.thumbnail_src != ""}
						<div class="col-md-2">
							<img src="{$page.thumbnail_src}" alt="{$page.thumbnail_src}" class="img-fluid">
						</div>
					{/if}
					<div class="col">
						<h5 class="mb-1">{$page.title}</h5>
						<p>{$page.description}</p>
						<a href="{$page.href}" class="stretched-link"> </a>
					</div>
				</div>
			</div>
		{/foreach}
	</div>
	<div class="col-md-6">

		<h6>{$lang_label_products} {$products_total}</h6>

		{if $show_prod_pagination}
			<form action="{$search_uri}" method="POST" class="text-end mb-3">
				<button class="btn btn-sm btn-primary" name="prev_page" value="{$prev_page_nbr}"><i class="bi bi-arrow-left"></i></button>
				<button class="btn btn-sm btn-primary" name="next_page" value="{$next_page_nbr}"><i class="bi bi-arrow-right"></i></button>
				{$hidden_csrf_token}
				<input type="hidden" name="s" value="{$search_string}">
			</form>
		{/if}

		{foreach $products as $product}
			<div class="card border-top-0 border-start-0 border-end-0 mb-3">
				<div class="row">
					{if $product.thumbnail_src != ""}
						<div class="col-md-2">
							<img src="{$product.thumbnail_src}" alt="{$product.thumbnail_src}" class="img-fluid">
						</div>
					{/if}
					<div class="col">
						<h5 class="mb-1">{$product.title}</h5>
                        <div class="d-flex flex-row">
                            <div class="flex-grow-1">
                                <p>
                                    {if $product.number}
                                        <span class="border rounded p-1 text-secondary small">{$product.number}</span>
                                    {/if}
                                    {$product.description}</p>
                            </div>
                            <div>
                                <div hx-get="/xhr/se/products/?calc=true&product_id={$product.id}" hx-trigger="load" class="text-end">
                                    <div class="spinner-border spinner-border-sm float-start me-2" role="status"></div>
                                    <small>{$lang_label_price} ...</small>
                                </div>
                            </div>
                        </div>

						<a href="{$product.href}" class="stretched-link"> </a>
					</div>
				</div>
			</div>
		{/foreach}
	</div>
</div>