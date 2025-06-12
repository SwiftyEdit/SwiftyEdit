<div class="row">
	<div class="col-md-6">
		<h6>Pages {$pages_total}</h6>

		{foreach $pages as $page}
			<div class="card border-top-0 border-start-0 border-end-0 mb-3">
				<div class="row">
					{if $page.thumbnail_src != ""}
						<div class="col-md-2">
							<img src="{$page.thumbnail_src}" alt="{$page.thumbnail_src}" class="img-fluid">
						</div>
					{/if}
					<div class="col">
						<h6>{$page.title}</h6>
						<p>{$page.description}</p>
						<a href="{$page.href}" class="stretched-link"> </a>
					</div>
				</div>
			</div>
		{/foreach}
	</div>
	<div class="col-md-6">

		<h6>Products {$products_total}</h6>

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
						<h6>{$product.title}</h6>
						<p>{$product.description}</p>
						<a href="{$product.href}" class="stretched-link"> </a>
					</div>
				</div>
			</div>
		{/foreach}
	</div>
</div>