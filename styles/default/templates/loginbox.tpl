{nocache}
    <div class="card">
        <div class="card-header">{$legend_login}</div>
        <div class="card-body">
            <form action="{$smarty.server.SCRIPT_NAME}?p={$p}" method="POST">

                <div class="form-group">
                    <label for="username">{$label_username}</label>
                    <input class="login_name form-control input-sm" type="text" id="username" name="login_name"
                           value="">
                </div>
                <div class="form-group">
                    <label for="psw">{$label_psw}</label>
                    <input class="login_psw form-control input-sm" type="password" name="login_psw" value="">
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="remember_me"> {$label_remember_me}
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <input class="btn btn-outline-secondary" type="submit" name="login" id="psw"
                           value="{$button_login}">
                </div>

                {if $failed_login != ''}
                    <div class="alert alert-danger my-1">
                        {$failed_login}
                    </div>
                {/if}

                <p>{$show_forgotten_psw_link}</p>
            </form>

			{if $show_register_link != ''}
            	<p>{$msg_register}<br>{$show_register_link}</p>
			{/if}
        </div>
    </div>
{/nocache}