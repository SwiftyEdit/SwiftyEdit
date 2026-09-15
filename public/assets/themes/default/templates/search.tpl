<form action="{$search_uri}" method="GET" class="mb-3 position-relative">
    <input type="text" name="s" id="search" value="{$search_string}" class="form-control" placeholder="Suchen ..." autofocus="" autocomplete="off"
        hx-get="/xhr/se/search/?target=page-search-suggestions" hx-trigger="keyup changed delay:300ms, search" hx-target="#page-search-suggestions" hx-swap="outerHTML">
    <div id="page-search-suggestions" class="dropdown-menu search-suggestions"></div>
</form>
<hr class="shadow">