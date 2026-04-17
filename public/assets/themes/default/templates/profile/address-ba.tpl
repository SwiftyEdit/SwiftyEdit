<form>
    <div class="card mb-3">
        <div class="card-header toggle">
            <a data-bs-toggle="collapse" href="#collapseAddressBa" role="button" aria-expanded="false" aria-controls="collapseAddressBa">
            {$lang_label_billing_address}
                <span class="ms-auto"><i class="bi bi-chevron-down"></i></span>
            </a>
        </div>
        <div class="card-body collapse" id="collapseAddressBa">
            <div class="row mb-3">
                <div class="col-6">
                    <label for="ba_company">{$lang_label_company}</label>
                    <input type="text" class="form-control" id="ba_company" value="{$ba_company}" name="ba_company">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="ba_firstname">{$lang_label_firstname}</label>
                    <input type="text" class="form-control" id="ba_firstname" value="{$ba_firstname}" name="ba_firstname">
                </div>
                <div class="col-6">
                    <label for="ba_lastname">{$lang_label_lastname}</label>
                    <input type="text" class="form-control" id="ba_lastname" value="{$ba_lastname}" name="ba_lastname">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-9">
                    <label for="ba_street">{$lang_label_street}</label>
                    <input type="text" class="form-control" id="ba_street" value="{$ba_street}" name="ba_street">
                </div>

                <div class="col-3">
                    <label for="ba_street_nbr">{$lang_label_nr}</label>
                    <input type="text" class="form-control" id="ba_street_nbr" value="{$ba_street_nbr}" name="ba_street_nbr">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-3">
                    <label for="ba_zip">{$lang_label_zip}</label>
                    <input type="text" class="form-control" id="ba_zip" value="{$ba_zip}" name="ba_zip">
                </div>
                <div class="col-9">
                    <label for="ba_city">{$lang_label_town}</label>
                    <input type="text" class="form-control" id="ba_city" value="{$ba_city}" name="ba_city">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">

                    <select name="ba_country" class="form-control" id="billingCountry">
                        <option value="">{$lang_label_please_select}</option>
                        {if $delivery_countries_options}
                            <optgroup label="Delivery countries">
                                {foreach $delivery_countries_options as $code => $name}
                                    <option value="{$code}" {if $code === $selected_billing_country}selected{/if}>{$name}</option>
                                {/foreach}
                            </optgroup>
                            <optgroup label="Other countries">
                                {foreach $other_countries_options as $code => $name}
                                    <option value="{$code}" {if $code === $selected_billing_country}selected{/if}>{$name}</option>
                                {/foreach}
                            </optgroup>
                        {else}
                            {foreach $other_countries_options as $code => $name}
                                <option value="{$code}" {if $code === $selected_billing_country}selected{/if}>{$name}</option>
                            {/foreach}
                        {/if}
                    </select>
                </div>
            </div>

            <button class="btn btn-primary" type="button" name="update_address_ba"
                    hx-post="/xhr/se/profile/"
                    hx-trigger="click"
                    hx-target="#address-ba-response"
                    hx-swap="innerHTML">
                {$lang_button_update}
            </button>
            {$hidden_csrf_token}

        </div>
    </div>
</form>