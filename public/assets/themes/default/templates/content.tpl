{$msg_content nocache}
{$products_content}
{$page_content}

{if $show_page_comments != ''}
	<hr class="shadow">

	{$comments_intro}

	{if isset($page_id) && !isset($post_id) }
		<div id="comments_form" hx-get="/api/se/comments/?form=comments&page_id={$page_id}" hx-swap="innerHTML" hx-trigger="load, update_comment_posted from:body">
			Loading comments form ...
		</div>

		<div id="page_comments" hx-get="/api/se/comments/?page_id={$page_id}" hx-swap="innerHTML" hx-trigger="load, update_comments from:body">
			Loading comments ...
		</div>
	{elseif isset($post_id) }
		<div id="comments_form" hx-get="/api/se/comments/?form=comments&post_id={$post_id}" hx-swap="innerHTML" hx-trigger="load, update_comment_posted from:body">
			Loading comments form ...
		</div>

		<div id="page_comments" hx-get="/api/se/comments/?post_id={$post_id}" hx-swap="innerHTML" hx-trigger="load, update_comments from:body">
			Loading comments ...
		</div>
	{/if}



{/if}