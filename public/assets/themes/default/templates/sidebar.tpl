{include file='sidebar-categories.tpl'}
{include file='sidebar-filter.tpl'}
{include file='sidebar-toc.tpl'}

{$se_snippet_sidebar_text}

<div id="user-box"
     hx-get="/xhr/se/statusbox/"
     hx-trigger="load, update_user_status"
     hx-swap="innerHTML">
    Lade...
</div>


{include file='admin_helpers.tpl'}