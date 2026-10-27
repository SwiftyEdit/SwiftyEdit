{nocache}
    <div class="card">
        <div class="card-header">{$status_msg} <b>{$smarty.session.user_nick}</b></div>

        <div class="list-group list-group-flush">

            <a href="{$link_profile}" class="list-group-item link-profile">{$lang_button_profile}</a>

            {if orders_uri != ''}
                <a href="{$orders_uri}" class="list-group-item link-orders">{$lang_button_orders}</a>
            {/if}

            {if $wishlist_uri != ''}
                <a href="{$wishlist_uri}" class="list-group-item link-wishlist">{$lang_button_wishlist}</a>
            {/if}

            {* logout only per POST with the CSRF token (see app/routing.php) *}
            <form method="post" action="{$link_logout}" class="m-0">
                {$hidden_csrf_token}
                <button type="submit" class="list-group-item list-group-item-action link-logout">{$lang_button_logout}</button>
            </form>

        </div>
    </div>
{/nocache}