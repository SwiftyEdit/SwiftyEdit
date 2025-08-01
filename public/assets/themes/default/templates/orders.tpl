<h2>{$lang_label_orders}</h2>

{if $upload_message != ""}
	<div class="alert alert-{$upload_message_class}">
		{$upload_message}
	</div>

{/if}

<div id="orderlist" hx-get="/xhr/se/orders/" hx-trigger="load, update_orders_list from:body" hx-swap="innerHTML" class="">
	<div class="d-flex align-items-center htmx-indicator">
		<div class="spinner-border spinner-border-sm me-2" role="status"></div>
		<span class="sr-only">{$lang_loading}</span>
	</div>
</div>