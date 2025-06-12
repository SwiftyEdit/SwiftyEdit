<form action="{$search_uri}" method="POST" class="mb-3">
    <input type="text" name="s" id="search" value="{$search_string}" class="form-control" placeholder="Suchen ..." autofocus="" autocomplete="off">
    {$hidden_csrf_token}
    <input type="hidden" name="languagePack" value="{$languagePack}">
</form>
<hr class="shadow">