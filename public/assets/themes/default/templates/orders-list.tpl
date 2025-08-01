{if $show_order_pagination == true}
<ul class="pagination">
    <li class="page-item">
        <button class="page-link" hx-get="/xhr/se/orders/?prev_page={$prev_page_nbr}" hx-target="#orderlist"><i class="bi bi-arrow-left-short"></i></button>
    </li>
    <li class="page-item">
        <button class="page-link" hx-get="/xhr/se/orders/?next_page={$next_page_nbr}" hx-target="#orderlist"><i class="bi bi-arrow-right-short"></i></button>
    </li>
</ul>
{/if}

<table class="table table-striped">
    <tr>
        <td>#</td>
        <td>{$lang_label_date}</td>
        <td>{$lang_order_status}</td>
        <td>{$lang_label_price}</td>
        <td> </td>
    </tr>
    {foreach $orders as $order}

    <tr>
        <td>{$order.nbr}</td>
        <td>{$order.date}</td>
        <td>
            {if $order.status_payment == '1'}
                {$lang_status_payment_open}<br>
            {else}
                <span class="text-success">{$lang_status_payment_paid}</span><br>
            {/if}
        </td>
        <td>{$order.price} {$order.currency}</td>
        <td><button class="btn btn-link"
                    hx-get="/xhr/se/orders/?id={$order.id}"
                    hx-target="#order-modal"
                    hx-trigger="click"
                    data-bs-toggle="modal"
                    data-bs-target="#order-modal"><i class="bi bi-caret-down"></i></button></td>
    </tr>

{/foreach}
</table>

<div id="order-modal"
     class="modal modal-blur fade"
     style="display: none"
     aria-hidden="false"
     tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content"></div>
    </div>
</div>