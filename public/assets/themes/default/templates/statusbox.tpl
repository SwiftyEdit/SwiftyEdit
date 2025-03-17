{nocache}
    <div class="card">
        <div class="card-header">{$status_msg} <b>{$smarty.session.user_nick}</b></div>

        <div class="list-group list-group-flush">
            <a href="{$link_profile}" class="list-group-item">{$lang_button_profile}</a>

            {if orders_uri != ''}
                <a href="{$orders_uri}" class="list-group-item">{$lang_button_orders}</a>
            {/if}

            <a href="{$link_logout}" class="list-group-item">{$lang_button_logout}</a>

        </div>
    </div>
{/nocache}