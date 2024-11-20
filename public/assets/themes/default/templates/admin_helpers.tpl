{if $smarty.session.user_class == "administrator"}
    <div class="accordion accordion-flush" id="accordionAdminHelpers">
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

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Shortcodes
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                 data-bs-parent="#accordionAdminHelpers">
                <div class="accordion-body">
                    {foreach $admin_helpers_shortcodes as $helper}
                        {$helper}
                    {/foreach}
                </div>
            </div>
        </div>

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
                    <ul>
                        {foreach $admin_helpers_plugins as $helper}
                            <li>{$helper}</li>
                        {/foreach}
                    </ul>
                </div>
            </div>
        </div>
    </div>
{/if}