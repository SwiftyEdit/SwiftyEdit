<div class="row">

        <div class="mb-1">
            <label>{label_title}</label>
            <input class='form-control' name="title" type="text"
                   value="{title}">
        </div>


    <div class="col-md-4">
        <div class="mb-1">
            <label>{label_product_amount}</label>
            <input class='form-control' name="amount" type="number"
                   value="{amount}">
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-1">
            <label>{label_product_unit}</label>
            <input class='form-control' name="unit" type="text"
                   value="{unit}">
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-1">
            <label>{label_product_tax}</label>
            {select_tax}
        </div>
    </div>

    <div class="col-md-6">
        <div class="">
            <label>{label_product_price} {label_product_net}</label>
            <input class='form-control prod_price_net' id="price" name="price_net" type="text"
                   value="{price_net}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="">
            <label>{label_product_price} {label_product_gross}</label>
            <input class='form-control prod_price_gross' id="price_total" name="price_gross"
                   type="text" value="">
        </div>
    </div>
</div>

<!-- show volume dicounts if we edit a existing product -->
{show_price_volume_discount}

<div>
<button type="submit" name="send" class="btn btn-success">{btn_send}</button>
    <input type="hidden" name="id" value="{id}">
</div>