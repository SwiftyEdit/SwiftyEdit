{nocache}
<p>{$msg_searchresults}</p>

<div id="searchlist">
{foreach item=link from=$arr_results}
	<div class="card mb-3 border-0">
		<div class="row">
			<div class="col-md-2">
				{if $link.thumb != ''}
					<img src="{$link.thumb}" class="img-fluid rounded">
				{/if}
		</div>
		<div class="col-md-10">
	
			<a href="{$link.set_link}" class="stretched-link" title="{$link.title}">{$link.title}</a><br>
			<p>{$link.description}<br><small class="text-success">{$link.set_link}</small></p>
	</div>
	</div>
	</div>
{/foreach}
</div>
{/nocache}