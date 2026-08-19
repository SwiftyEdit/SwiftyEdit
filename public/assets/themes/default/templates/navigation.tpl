{nocache}
<nav class="navbar navbar-expand-lg navbar-se sticky-top">
	<div class="container">
  	<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
    	<span class="navbar-toggler-icon"></span>
		</button>
			
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav mr-auto">
      	<li class="nav-item"><a class="nav-link {$homelink_status}" href="{$link_home}{$homepage_permalink}" title="{$homepage_title}">{$homepage_linkname}</a></li>	
				{foreach item=nav from=$arr_menue}
				{if $nav.children}
				<li class="nav-item dropdown {$nav.link_status} {$nav.page_classes}">
					<a class="nav-link nav-id-{$nav.page_id} {$nav.page_hash} {$nav.link_status}" href="{$nav.link}" target="{$nav.page_target}" title="{$nav.page_title}">{$nav.page_linkname}</a><button class="nav-link dropdown-toggle dropdown-toggle-split {$nav.link_status}" type="button" id="mainNavDropdown{$nav.page_id}" data-bs-toggle="dropdown" aria-expanded="false">
						<span class="visually-hidden">{$nav.page_linkname} - Untermenü öffnen</span>
					</button>
					<ul class="dropdown-menu" aria-labelledby="mainNavDropdown{$nav.page_id}">
						{foreach item=child from=$nav.children}
						<li><a class="dropdown-item nav-id-{$child.page_id} {$child.page_hash} {$child.link_status}" href="{$child.link}" target="{$child.page_target}" title="{$child.page_title}">{$child.page_linkname}</a></li>
						{/foreach}
					</ul>
				</li>
				{else}
				<li class="nav-item {$nav.page_classes}">
					<a class="nav-link nav-id-{$nav.page_id} {$nav.page_hash} {$nav.link_status}" href="{$nav.link}" target="{$nav.page_target}" title="{$nav.page_title}">{$nav.page_linkname}</a>
				</li>
				{/if}
				{/foreach}
			</ul>
		</div>

		<form class="form-inline" action="{$search_uri}" method="POST">
    		<input class="form-control mr-sm-2 searchbox" name="s" type="search" aria-label="Search" value="{$search_string}">
			{$hidden_csrf_token}
  		</form>

	</div>
</nav>
{/nocache}