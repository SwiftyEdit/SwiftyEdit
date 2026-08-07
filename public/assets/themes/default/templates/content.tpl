{$msg_content nocache}
{$products_content}
{$page_content}

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