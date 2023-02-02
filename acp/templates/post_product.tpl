<div class="subHeader">
    <div class="row">
        <div class="col-9">
            {form_header_message}
        </div>
        <div class="col-3">
            {form_header_mode}
        </div>
    </div>
</div>
<form action='{formaction}' class='form-horizontal' id='editpage' method='post' name="editpage">
    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab"
                                                href="#intro">{post_tab_intro}</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                href="#content">{post_tab_descriptions}</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                href="#product">{post_tab_product}</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                href="#prices_delivery">{post_tab_prices_delivery}</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                href="#options">{post_tab_options}</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                href="#variants">{post_tab_variants}</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                href="#features">{post_tab_features}</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#seo">{post_tab_SEO}</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="intro">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{label_title}</label> <input class="form-control" name="title" type="text"
                                                                        value="{title}">
                                    <label>{label_description}</label>
                                    <textarea class='mceEditor_small' name='teaser'>{teaser}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <div class="well well-sm">
                                        <label>{label_image}</label> <input class="filter-images form-control"
                                                                            name="filter-images"
                                                                            placeholder="Filter ..." type="text">
                                        <div class="images-list scroll-container">
                                            {widget_images}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="content">

                            <div class="card">
                                <div class="card-header">
                                    <ul class="nav nav-tabs card-header-tabs" id="content_Sections" role="tablist">
                                        <li class="nav-item"><a class="nav-link active me-auto" data-bs-toggle="tab"
                                                                href="#main-content">{prod_tab_main_description}</a>
                                        </li>
                                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                                href="#add-content1"
                                                                title="{prod_tab_additional_description}">1</a></li>
                                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                                href="#add-content2"
                                                                title="{prod_tab_additional_description}">2</a></li>
                                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                                href="#add-content3"
                                                                title="{prod_tab_additional_description}">3</a></li>
                                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                                href="#add-content4"
                                                                title="{prod_tab_additional_description}">4</a></li>
                                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                                href="#add-content5"
                                                                title="{prod_tab_additional_description}">5</a></li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content my-2">
                                        <div class="tab-pane fade show active" id="main-content">
                                            <div class="mb-3">
                                                <label for="text_label" class="form-label">Label</label>
                                                <input type="text" name="text_label" value="{text_label}"
                                                       class="form-control" id="text_label">
                                            </div>
                                            <div class="mb-3">
                                                <textarea class='mceEditor' name='text'>{text}</textarea>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="add-content1">
                                            <div class="mb-3">
                                                <label for="text_label1" class="form-label">Label</label>
                                                <input type="text" name="text_additional1_label"
                                                       value="{text_label_additional_1}" class="form-control"
                                                       id="text_label1">
                                            </div>
                                            <div class="mb-3">
                                                <textarea class='mceEditor'
                                                          name='text_additional1'>{text_additional_1}</textarea>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="add-content2">
                                            <div class="mb-3">
                                                <label for="text_label2" class="form-label">Label</label>
                                                <input type="text" name="text_additional2_label"
                                                       value="{text_label_additional_2}" class="form-control"
                                                       id="text_label2">
                                            </div>
                                            <div class="mb-3">
                                                <textarea class='mceEditor'
                                                          name='text_additional2'>{text_additional_2}</textarea>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="add-content3">
                                            <div class="mb-3">
                                                <label for="text_label3" class="form-label">Label</label>
                                                <input type="text" name="text_additional3_label"
                                                       value="{text_label_additional_3}" class="form-control"
                                                       id="text_label3">
                                            </div>
                                            <div class="mb-3">
                                                <textarea class='mceEditor'
                                                          name='text_additional3'>{text_additional_3}</textarea>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="add-content4">
                                            <div class="mb-3">
                                                <label for="text_label4" class="form-label">Label</label>
                                                <input type="text" name="text_additional4_label"
                                                       value="{text_label_additional_4}" class="form-control"
                                                       id="text_label4">
                                            </div>
                                            <div class="mb-3">
                                                <textarea class='mceEditor'
                                                          name='text_additional4'>{text_additional_4}</textarea>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="add-content5">
                                            <div class="mb-3">
                                                <label for="text_label5" class="form-label">Label</label>
                                                <input type="text" name="text_additional5_label"
                                                       value="{text_label_additional_5}" class="form-control"
                                                       id="text_label5">
                                            </div>
                                            <div class="mb-3">
                                                <textarea class='mceEditor'
                                                          name='text_additional5'>{text_additional_5}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label for="snippet_text" class="form-label">{label_product_snippet_text}</label>
                                {snippet_select_text}
                            </div>

                        </div>

                        <div class="tab-pane fade" id="product">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{label_product_number}</label>
                                        <input class='form-control' name="product_number" type="text"
                                               value="{product_number}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{label_product_manufacturer}</label>
                                        <input class='form-control' name="product_manufacturer" type="text"
                                               value="{product_manufacturer}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{label_product_supplier}</label>
                                        <input class='form-control' name="product_supplier" type="text"
                                               value="{product_supplier}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{label_product_currency}</label>
                                        <input class='form-control' name="product_currency" type="text"
                                               value="{product_currency}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{label_product_price_label}</label>
                                        <input class='form-control' name="product_price_label" type="text"
                                               value="{product_price_label}">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>{label_url}</label>
                                        <input class='form-control' name="link" type="text" value="{link}">
                                    </div>
                                </div>


                            </div>

                            <hr>

                            <!-- files -->

                            <fieldset class="my-4">
                                <legend>{label_product_attachments}</legend>
                                <p class="fw-bold">{msg_aftersale_files}</p>
                                <div class="form-group">
                                    <label>{label_file_as_select}</label>
                                    {select_file_as}
                                    <div class="form-text">Downloads: {cnt_attachment_as_hits}</div>
                                </div>
                                <p class="fw-bold">{msg_presale_files}</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{label_file_select}</label>
                                            {select_file}
                                            <div class="form-text">Downloads: {cnt_attachment_hits}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{label_external_file}</label>
                                            <input class='form-control' name="file_attachment_external" type="text"
                                                   value="{file_attachment_external}">
                                        </div>
                                    </div>
                                </div>


                            </fieldset>

                            <hr>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{label_product_nbr_stock}</label>
                                        <input class='form-control' id="nbr_stock" name="product_nbr_stock" type="text"
                                               value="{product_nbr_stock}">
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="product_ignore_stock"
                                               value="1" id="ignoreStock" {checkIgnoreStock}>
                                        <label class="form-check-label"
                                               for="ignoreStock">{label_product_ignore_stock}</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{label_product_cnt_sales}</label>
                                        <input class='form-control' id="cnt_sales" name="product_cnt_sales" type="text"
                                               value="{product_cnt_sales}">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label>{label_product_cart_mode}</label>
                                    {select_product_cart_mode}
                                </div>
                                <div class="col-md-6">
                                    <label>{label_product_pricetag_mode}</label>
                                    {select_product_pricetag_mode}
                                </div>
                            </div>

                        </div> <!-- #product -->

                        <div class="tab-pane fade" id="prices_delivery">

                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{label_product_amount}</label>
                                        <input class='form-control' name="product_amount" type="text"
                                               value="{product_amount}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{label_product_unit}</label>
                                        <input class='form-control' name="product_unit" type="text"
                                               value="{product_unit}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{label_shipping}</label>
                                        {select_shipping_mode}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{label_shipping_costs_cat}</label>
                                        {select_shipping_category}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{label_product_delivery_time}</label>
                                        {select_delivery_time}
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label>{label_product_price} {label_product_net}</label>
                                        <input class='form-control' id="price" name="product_price_net" type="text"
                                               value="{product_price_net}">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label>{label_product_price_addition}</label>
                                    <div class="input-group">
                                        <input class='form-control' id="price_addition" name="product_price_addition"
                                               type="text" value="{product_price_addition}">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="">
                                        <label>{label_product_tax}</label>
                                        {select_tax}
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <div class="">
                                        <label>{label_product_price} {label_product_gross} <small>({label_product_net}
                                                <span id="calculated_net"></span>)</small></label>
                                        <input class='form-control' id="price_total" name="product_price_gross"
                                               type="text" value="{product_price_gross}">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <fieldset class="mt-4">
                                <legend>{label_product_snippet_price}</legend>
                                {snippet_select_pricelist}
                            </fieldset>


                        </div> <!-- #prices_delivery -->

                        <div class="tab-pane fade" id="options">
                            <div class="mb-3">
                                <label>{label_product_customer_feedback}</label>
                                <input type="text" class="form-control" name="product_options_comment_label"
                                       value="{product_options_comment_label}">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="file_attachment_user"
                                           value="2" id="file_attachment_user" {checked_user_uploads}>
                                    <label class="form-check-label"
                                           for="file_attachment_user">{label_product_customer_upload}</label>
                                </div>
                            </div>
                            <hr>
                            {options_input}
                        </div> <!-- #options -->

                        <div class="tab-pane fade" id="variants">
                            <div class="form-group">
                                <label>{label_title}</label>
                                <input class='form-control' name="product_variant_title" type="text"
                                       value="{product_variant_title}">
                            </div>
                            <div class="form-group">
                                <label>{label_description}</label>
                                <textarea class='form-control' rows="4"
                                          name="product_variant_description">{product_variant_description}</textarea>
                            </div>
                            {variants_list}
                        </div> <!-- #variants -->

                        <div class="tab-pane fade" id="features">
                            {checkboxes_features}
                        </div> <!-- #features -->

                        <div class="tab-pane fade" id="seo">
                            <div class="form-group">
                                <label>{label_title}</label>
                                <input class='form-control' name="meta_title" type="text" value="{meta_title}">
                            </div>
                            <div class="form-group">
                                <label>{label_description}</label>
                                <textarea class='form-control' rows="4"
                                          name="meta_description">{meta_description}</textarea>
                            </div>
                            <div class="form-group">
                                <label>{label_keywords}</label>
                                <input type="text" class='form-control' name="tags" data-role="tagsinput"
                                       value="{tags}">
                            </div>
                            <div class="form-group">
                                <label>{label_slug}</label>
                                <input class='form-control' name="post_slug" type="text" value="{slug}">
                            </div>
                            <fieldset>
                                <legend>RSS</legend>
                                <div class="form-group">
                                    <label>{rss}</label>
                                    {select_rss}
                                </div>
                                <div class="form-group">
                                    <label>{label_rss_url}</label>
                                    <input class='form-control' name="rss_url" type="text" value="{rss_url}">
                                </div>
                            </fieldset>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="well well-sm">
                <fieldset>
                    <legend>{label_language}</legend>
                    <div class="">
                        {checkboxes_lang}
                    </div>
                </fieldset>
                <fieldset>
                    <legend>{label_categories}</legend>
                    <div class="scroll-container" style="max-height: 150px;">
                        {checkbox_categories}
                    </div>
                </fieldset>
                <fieldset>
                    <legend>{label_releasedate}</legend>
                    <input class='dp form-control' name="releasedate" type="text" value="{releasedate}">
                </fieldset>
                <fieldset>
                    <legend>{label_priority}</legend>
                    <input type="number" name="priority" value="{input_priority}" class="form-control">
                    {checkbox_fixed}
                </fieldset>
                <fieldset>
                    <legend>{label_status}</legend> {select_status}
                </fieldset>
                <fieldset>
                    <legend>{label_comments}</legend> {select_comments}
                </fieldset>
                <fieldset>
                    <legend>{label_votings}</legend> {select_votings}
                </fieldset>
                <fieldset>
                    <legend>{labels}</legend> {product_labels}
                </fieldset>
                <input name="type" type="hidden" value="{type}">
                <input name="parent_id" type="hidden" value="{parent_id}">
                <input name="modus" type="hidden" value="{modus}">
                <input name="edit_id" type="hidden" value="{id}">
                <input type="hidden" name="csrf_token" value="{token}">
                <input type="hidden" name="date" value="{date}">

                {submit_delete_button}

                {submit_variant_button}
                {submit_button}

            </div>
        </div>
    </div>
</form>