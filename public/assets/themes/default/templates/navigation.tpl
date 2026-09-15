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

		<button type="button" class="btn searchbox d-inline-flex align-items-center gap-2 me-2" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="{$lang_label_search}">
			<i class="bi bi-search"></i> {$lang_label_search}
		</button>

	</div>
</nav>

{* Instant-search modal, opened from the searchbox button above - a plain
   text field wouldn't have room for long result titles without the page
   getting a horizontal scrollbar (the field sits at the navbar's right
   edge), so search happens in a modal instead of a dropdown. Anchored near
   the top (plain modal-dialog, not modal-dialog-centered - that's
   Bootstrap's default position) since the result list grows as you type -
   centering it would waste the space above once it's tall. *}
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body p-4">
				<form action="{$search_uri}" method="GET" class="d-flex align-items-center gap-2 mb-3">
					<button type="submit" class="btn p-0 border-0 text-muted" aria-label="{$lang_label_search}">
						<i class="bi bi-search"></i>
					</button>
					<label class="visually-hidden" for="searchModalInput" id="searchModalLabel">{$lang_label_search}</label>
					<input type="text" class="form-control form-control-lg border-0 shadow-none" id="searchModalInput" name="s" value="{$search_string}" placeholder="{$lang_label_search} ..." autocomplete="off"
						hx-get="/xhr/se/search/?target=modal-search-suggestions&variant=modal" hx-trigger="keyup changed delay:300ms, search" hx-target="#modal-search-suggestions" hx-swap="outerHTML">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</form>
				<div id="modal-search-suggestions" class="dropdown-menu search-suggestions search-suggestions-modal"></div>
			</div>
		</div>
	</div>
</div>
{/nocache}