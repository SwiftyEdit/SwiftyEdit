{nocache}
    <div class="card">
        <div class="card-header">{$status_msg} <b>{$smarty.session.user_nick}</b></div>

        <div class="list-group list-group-flush">

            {if link_acp != ''}
                {if $smarty.session.user_class == 'administrator'}
                    <a href="{$link_acp}" class="list-group-item link-admin">{$lang_button_acp}</a>
                {/if}
            {/if}

            <a href="{$link_profile}" class="list-group-item link-profile">{$lang_button_profile}</a>

            {if orders_uri != ''}
                <a href="{$orders_uri}" class="list-group-item link-orders">{$lang_button_orders}</a>
            {/if}

            <a href="{$link_logout}" class="list-group-item link-logout">{$lang_button_logout}</a>

        </div>
    </div>
{/nocache}