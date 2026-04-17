<form>
    <div class="card mb-3">
        <div class="card-header toggle">
            <a data-bs-toggle="collapse" href="#collapseAddress" role="button" aria-expanded="false" aria-controls="collapseAddress">
                {$lang_legend_adress_fields}
                <span class="ms-auto"><i class="bi bi-chevron-down"></i></span>
            </a>
        </div>
        <div class="card-body collapse" id="collapseAddress">
            <div class="row mb-2">
                <div class="col-6">
                    <label for="firstname">{$lang_label_firstname}</label>
                    <input type="text" class="form-control" id="firstname" value="{$user_firstname}" name="user_firstname">
                </div>

                <div class="col-6">
                    <label for="lastname">{$lang_label_lastname}</label>
                    <input type="text" class="form-control" id="lastname" value="{$user_lastname}" name="user_lastname">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-9">
                    <label for="street">{$lang_label_street}</label>
                    <input type="text" class="form-control" id="street" value="{$user_street}" name="user_street">
                </div>

                <div class="col-3">
                    <label for="streetnbr">{$lang_label_nr}</label>
                    <input type="text" class="form-control" id="streetnbr" value="{$user_street_nbr}" name="user_street_nbr">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-3">
                    <label for="zip">{$lang_label_zip}</label>
                    <input type="text" class="form-control" id="zip" value="{$user_zip}" name="user_zip">
                </div>

                <div class="col-9">
                    <label for="city">{$lang_label_town}</label>
                    <input type="text" class="form-control" id="city" value="{$user_city}" name="user_city">
                </div>
            </div>

            <div class="mb-2">
                <label for="about">{$lang_label_about_you}</label>
                <textarea class="form-control" id="about" rows="4" name="user_public_profile">{$user_public_profile}</textarea>
            </div>

            <button class="btn btn-primary" type="button" name="update_address"
                    hx-post="/xhr/se/profile/"
                    hx-trigger="click"
                    hx-target="#address-response"
                    hx-swap="innerHTML">
                {$lang_button_update}
            </button>
            {$hidden_csrf_token}

        </div>
    </div>
</form>