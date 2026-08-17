{$msg_content nocache}
{$products_content}
{$page_content}

{* content_tags is assigned on every page (its own tags, from template-setup.php),
   but posts/products/events display pages already render their own item-level
   badge inside their own template - skip here to avoid a duplicate *}
{if $content_tags && !isset($product_id) && !isset($post_id) && !isset($event_id)}
	<p class="m-0 content-tags">
		{foreach $content_tags as $tag}
			<a href="{$tag.tag_href}" class="btn btn-sm btn-link" title="{$tag.tag_title}">#{$tag.tag_title}</a>
		{/foreach}
	</p>
{/if}

{if $show_page_comments != ''}
	<hr class="shadow">

	<h2>{$comments_title}</h2>

	{* $page_id is assigned on every page (it's the hosting page), so post_id/product_id
	   are checked first - they only exist for the more specific post/product itself *}
	{if isset($product_id) }
		<div id="comments_form" hx-get="/xhr/se/comments/?form=comments&product_id={$product_id}" hx-swap="innerHTML" hx-trigger="load, update_comment_posted from:body">
			Loading comments form ...
		</div>

		<div id="page_comments" hx-get="/xhr/se/comments/?product_id={$product_id}" hx-swap="innerHTML" hx-trigger="load, update_comments from:body">
			Loading comments ...
		</div>
	{elseif isset($post_id) }
		<div id="comments_form" hx-get="/xhr/se/comments/?form=comments&post_id={$post_id}" hx-swap="innerHTML" hx-trigger="load, update_comment_posted from:body">
			Loading comments form ...
		</div>

		<div id="page_comments" hx-get="/xhr/se/comments/?post_id={$post_id}" hx-swap="innerHTML" hx-trigger="load, update_comments from:body">
			Loading comments ...
		</div>
	{elseif isset($page_id) }
		<div id="comments_form" hx-get="/xhr/se/comments/?form=comments&page_id={$page_id}" hx-swap="innerHTML" hx-trigger="load, update_comment_posted from:body">
			Loading comments form ...
		</div>

		<div id="page_comments" hx-get="/xhr/se/comments/?page_id={$page_id}" hx-swap="innerHTML" hx-trigger="load, update_comments from:body">
			Loading comments ...
		</div>
	{/if}



{/if}