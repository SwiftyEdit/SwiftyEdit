{if $smarty.session.user_class == "administrator"}

    <a href="/admin/" class="btn btn-secondary w-100 mb-1">{$lang_button_acp}</a>

    <form action="/admin/pages/edit/" method="POST">
        <div class="py-1">
            <button name="page_id" value="{$page_id}"
                    class="btn btn-secondary w-100">{$lang_button_acp_edit_page}</button>
            {$hidden_csrf_token}
        </div>
    </form>

    {if $product_id != ""}
        <form action="/admin/shop/edit/" method="POST">
            <div class="py-1">
            <button name="product_id" value="{$product_id}"
                    class="btn btn-secondary w-100">{$lang_button_acp_edit_product}</button>
            {$hidden_csrf_token}
            </div>
        </form>

        <p>Source: {$data_source}</p>
    {/if}

    <div class="card mt-2">
        <div class="card-header">Helpers</div>
        <div class="card-body">
            <div class="accordion accordion-flush" id="accordionAdminHelpers">

                {if $admin_helpers_products}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingProd">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseProd" aria-expanded="false" aria-controls="collapseProd">
                                Products
                            </button>
                        </h2>
                        <div id="collapseProd" class="accordion-collapse collapse" aria-labelledby="headingProd"
                             data-bs-parent="#accordionAdminHelpers">
                            <div class="accordion-body">
                                {foreach $admin_helpers_products as $helper}
                                    {$helper}
                                {/foreach}
                            </div>
                        </div>
                    </div>
                {/if}

                {if $admin_helpers_snippets}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                Snippets
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                             data-bs-parent="#accordionAdminHelpers">
                            <div class="accordion-body">
                                {foreach $admin_helpers_snippets as $helper}
                                    {$helper}
                                {/foreach}
                            </div>
                        </div>
                    </div>
                {/if}

                {if $admin_helpers_images}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Images
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                             data-bs-parent="#accordionAdminHelpers">
                            <div class="accordion-body">
                                <ul>
                                    {foreach $admin_helpers_images as $helper}
                                        <li>{$helper}</li>
                                    {/foreach}
                                </ul>
                            </div>
                        </div>
                    </div>
                {/if}

                {if $admin_helpers_files}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Files
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                             data-bs-parent="#accordionAdminHelpers">
                            <div class="accordion-body">
                                <ul>
                                    {foreach $admin_helpers_files as $helper}
                                        <li>{$helper}</li>
                                    {/foreach}
                                </ul>
                            </div>
                        </div>
                    </div>
                {/if}

                {if $admin_helpers_plugins}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Plugins
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
                             data-bs-parent="#accordionAdminHelpers">
                            <div class="accordion-body">
                                {foreach $admin_helpers_plugins as $helper}
                                    {$helper}
                                {/foreach}
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>


{/if}