
<h2>{$lang_label_orders}</h2>

{if $upload_message != ""}
	<div class="alert alert-{$upload_message_class}">
		{$upload_message}
	</div>

{/if}

<table class="table table-sm">
	<tr>
		<td>#</td>
		<td>{$lang_label_date}</td>
		<td>{$lang_order_status}</td>
		<td>{$lang_label_price}</td>
	</tr>
	
	{foreach $orders as $order}
		<tr>
			<td><a data-bs-toggle="collapse" href="#show{$order.nbr}">{$order.nbr}</a></td>
			<td>{$order.date}</td>
			<td>
				{if $order.status_payment == '1'}
					{$lang_status_payment_open}<br>
					{else}
					<span class="text-success">{$lang_status_payment_paid}</span><br>
				{/if}
				{if $order.status_shipping == '1'}
					{$lang_status_shipping_open}
				{else}
					<span class="text-success">{$lang_status_shipping_done}</span>
				{/if}
			</td>
			<td>{$order.price} {$order.currency}</td>
		</tr>
		<tr>
			<td colspan="4">
				<div class="collapse" id="show{$order.nbr}">
					<div class="card bg-light p-3">
						<table class="table table-sm">
							{* loop through ordered items *}
							{foreach $order.products as $product}
								<tr>
									<td>
										<span class="badge text-bg-secondary">{$product.pos}</span>
									</td>
									<td>
										<strong>{$product.title}</strong> <small>{$product.product_nbr}</small><br>
										{$product.options}<br>{$product.options_comment_label}: <span>{$product.options_comment}</span>
										{* user can upload a file for this item *}
										{if $product.need_upload != ''}
										<form action="{$order_page_uri}" method="post" enctype="multipart/form-data">
											<div class="row g-3">
												<div class="col-auto">
												<input class="form-control" type="file" name="upload_file" id="uploadFile">
												</div>
												<div class="col-auto">
													<button type="submit" name="startUpload" class="btn btn-primary">Upload</button>
												</div>
											</div>
											<input type="hidden" name="order" value="{$order.nbr}">
											<input type="hidden" name="pos" value="{$product.pos}">
										</form>
										{/if}
										{* we provide no preview, but user can download the file  *}
										{if $product.user_upload_status == 'uploaded'}
											<form action="{$order_page_uri}" method="POST" class="d-inline">
												<button type="submit" class="btn btn-sm btn-primary" name="download_user_file">Download</button>
												<input type="hidden" name="order" value="{$order.nbr}">
												<input type="hidden" name="pos" value="{$product.pos}">
											</form>
										{/if}
									</td>
									<td>{$product.amount}</td>
									<td>{$product.price_gross} {$order.currency}</td>
									<td class="text-end">
										
										{if $product.dl_file != '' AND $order.status_payment == '2'}
											<form action="{$order_page_uri}" method="POST" class="d-inline">
												<button class="btn btn-primary" type="submit" name="dl_p_file" value="{$product.post_id}"><i class="bi bi-download"></i> DOWNLOAD</button>
												<input type="hidden" name="order_id" value="{$order.nbr}">
											</form>
										{/if}
										{if $product.dl_file_ext != '' AND $order.status_payment == '2'}
											<form action="{$order_page_uri}" method="POST" class="d-inline">
												<button class="btn btn-primary" type="submit" name="dl_p_file_ext" value="{$product.post_id}"><i class="bi bi-cloud-download"></i> DOWNLOAD</button>
												<input type="hidden" name="order_id" value="{$order.nbr}">
											</form>
										{/if}
										
									</td>
								</tr>
							{/foreach}
						</table>
					</div>
				</div>
			</td>
		</tr>
	{/foreach}
</table>