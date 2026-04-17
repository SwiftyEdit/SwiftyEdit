<form>
    <div class="card mb-3">
        <div class="card-header toggle">
            <a data-bs-toggle="collapse" href="#collapseAddressSa" role="button" aria-expanded="false" aria-controls="collapseAddressSa">
            {$lang_label_delivery_address}
                <span class="ms-auto"><i class="bi bi-chevron-down"></i></span>
            </a>
        </div>
        <div class="card-body collapse" id="collapseAddressSa">
            <div class="row mb-3">
                <div class="col-6">
                    <label for="sa_company">{$lang_label_company}</label>
                    <input type="text" class="form-control" id="sa_company" value="{$sa_company}" name="sa_company">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="sa_firstname">{$lang_label_firstname}</label>
                    <input type="text" class="form-control" id="sa_firstname" value="{$sa_firstname}" name="sa_firstname">
                </div>
                <div class="col-6">
                    <label for="sa_lastname">{$lang_label_lastname}</label>
                    <input type="text" class="form-control" id="sa_lastname" value="{$sa_lastname}" name="sa_lastname">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-9">
                    <label for="sa_street">{$lang_label_street}</label>
                    <input type="text" class="form-control" id="sa_street" value="{$sa_street}" name="sa_street">
                </div>

                <div class="col-3">
                    <label for="sa_street_nbr">{$lang_label_nr}</label>
                    <input type="text" class="form-control" id="sa_street_nbr" value="{$sa_street_nbr}" name="sa_street_nbr">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-3">
                    <label for="sa_zip">{$lang_label_zip}</label>
                    <input type="text" class="form-control" id="sa_zip" value="{$sa_zip}" name="sa_zip">
                </div>
                <div class="col-9">
                    <label for="sa_city">{$lang_label_town}</label>
                    <input type="text" class="form-control" id="sa_city" value="{$sa_city}" name="sa_city">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <select name="sa_country" class="form-control" id="billingCountry">
                        <option value="">{$lang_label_please_select}</option>
                        {if $delivery_countries_options}
                            <optgroup label="Delivery countries">
                                {foreach $delivery_countries_options as $code => $name}
                                    <option value="{$code}" {if $code === $selected_delivery_country}selected{/if}>{$name}</option>
                                {/foreach}
                            </optgroup>
                            <optgroup label="Other countries">
                                {foreach $other_countries_options as $code => $name}
                                    <option value="{$code}" {if $code === $selected_delivery_country}selected{/if}>{$name}</option>
                                {/foreach}
                            </optgroup>
                        {else}
                            {foreach $other_countries_options as $code => $name}
                                <option value="{$code}" {if $code === $selected_delivery_country}selected{/if}>{$name}</option>
                            {/foreach}
                        {/if}
                    </select>
                </div>
            </div>
            <button class="btn btn-primary" type="button" name="update_address_sa"
                    hx-post="/xhr/se/profile/"
                    hx-trigger="click"
                    hx-target="#address-sa-response"
                    hx-swap="innerHTML">
                {$lang_button_update}
            </button>
            {$hidden_csrf_token}
        </div>
    </div>
</form>