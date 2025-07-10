{include file='sidebar-categories.tpl'}
{include file='sidebar-filter.tpl'}
{include file='sidebar-toc.tpl'}

{$se_snippet_sidebar_text}

<div id="user-box"
     hx-get="/xhr/se/statusbox/"
     hx-trigger="load, update_user_status"
     hx-swap="innerHTML">
    <div class="d-flex align-items-center htmx-indicator">
        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
        <span class="sr-only">{$lang_loading}</span>
    </div>

</div>


{include file='admin_helpers.tpl'}