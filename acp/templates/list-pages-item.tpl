<div style="padding-left: {item-indent}">

	<div class="page-list-controls page-list-item {item-class}">
		<div class="label-page-status" title="{status-label}"></div>
	
		<div class="row">
			<div class="col-12 col-xxl-8 flex-grow-1">
				
				<div class="row">
					<div class="col-sm-2 d-none d-lg-block">
						<div class="w-100 h-100 rounded" style="background-image: url({item-tmb-src}); background-size: contain; background-repeat: no-repeat;" ></div>
					</div>
					<div class="col-sm-10">
						<p class="mb-1"><a class="btn btn-default btn-sm" href="{frontend-link}" title="{frontend-link}"><i class="bi bi-box-arrow-in-up-right"></i> {item-linkname}</a></p>
						<h5 class="mb-0">{item-title}</h5>
						<p>{item-description}</p>
						<p class="small border-top py-1">
							<i class="bi bi-link-45deg"></i> {item-permalink} <span class="text-primary">{item-redirect}</span><br>
						</p>
					</div>

				</div>
			</div>
			<div class="col-12 col-xxl-4" style="min-width:120px;">

				<div class="controls-container d-flex justify-content">
					{edit-btn}
					{duplicate-btn}
					{info-btn}
				</div>

				<div class="text-muted small">
					<p class="my-1"><i class="bi bi-sort-down"></i> {item-pagesort} | {item-lang}</p>
					<p class="my-1"><i class="bi-palette"></i> {item-template}</p>
					<p class="my-1"><i class="bi bi-tag"></i> {page_labels}</p>
					<p>{item-mod}</p>
					<p class="my-1"><i class="bi bi-clock"></i> {item-lastedit}</p>
				</div>
				

			</div>
		</div>
	</div>

</div>