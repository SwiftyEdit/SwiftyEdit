<footer id="pageFooter" class="pt-3">

	{if is_array($arr_bcmenue) }
	<div class="container">
		<nav aria-label="breadcrumb" class="mt-3">
			<ol class="breadcrumb">
				{foreach item=bc from=$arr_bcmenue}
					{if $bc.link == ''}
						<li class="breadcrumb-item">{$bc.page_linkname}</li>
					{else}
						<li class="breadcrumb-item"><a href="{$bc.link}" title="{$bc.page_title}">{$bc.page_linkname}</a></li>
					{/if}
				{/foreach}
			</ol>
		</nav>
	</div>
	{/if}

	<div class="container" style="margin-top:25px;">
		{$se_snippet_footer_text}
	</div>


	<p class="text-center d-none">{$se_pageload_time} Sekunden</p>
</footer>