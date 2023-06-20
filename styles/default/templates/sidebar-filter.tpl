{*
Custom filters a set in the backend > shop > filter
@param array $product_filter
You can display filters as links or in a form
example for links:
<a class="list-group-item list-group-item-action {$item.class}" href="?remove_filter={$item.id}">{$item.title}</a>
<a class="list-group-item list-group-item-action {$item.class}" href="?add_filter={$item.id}">{$item.title}</a>
*}


{if is_array($product_filter) }
    <div class="mb-2">
        <form action="{$form_action}" method="POST">
            {foreach $product_filter as $groups}
                <div class="card mb-1">

                    {if $groups.input_type == 1}
                        <div class="card-header fw-bold">
                            {$groups.title}
                            {if $groups.description != ""}
                            <span title="{$groups.description}"><i class="bi-info-circle"></i></span>
                            {/if}
                        </div>
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sf_radio[{$groups.id}][]" value="all"
                                       id="sf_id_all{$groups.id}" checked onchange="this.form.submit()">
                                <label class="form-check-label" for="sf_id_all{$groups.id}">{$lang_btn_all}</label>
                            </div>

                            {foreach $groups.items as $item}
                                <div class="form-check">
                                    <input type="hidden" name="all_radios[]" value="{$item.id}">
                                    <input class="form-check-input" type="radio" name="sf_radio[{$groups.id}][]"
                                           value="{$item.id}" id="sf_id_{$item.id}"
                                           onchange="this.form.submit()" {$item.checked}>
                                    <label class="form-check-label" for="sf_id_{$item.id}"><span title="{$item.description}">{$item.title}</span></label>
                                </div>
                            {/foreach}
                        </div>
                    {else}
                        <div class="card-header fw-bold">{$groups.title}</div>
                        <div class="card-body">
                            {foreach $groups.items as $item}
                                <div class="form-check">
                                    <input type="hidden" name="all_checks[]" value="{$item.id}">
                                    <input class="form-check-input" type="checkbox" name="sf_checkbox[]"
                                           value="{$item.id}" id="sf_id_{$item.id}"
                                           onchange="this.form.submit()" {$item.checked}>
                                    <label class="form-check-label" for="sf_id_{$item.id}"><span title="{$item.description}">{$item.title}</span></label>
                                </div>
                            {/foreach}
                        </div>
                    {/if}

                </div>
            {/foreach}
    </div>
    <input type="hidden" name="set_custom_filters" value="send">
    </form>
{/if}