<p></p>
<table role="presentation" border="0" cellpadding="0" cellspacing="0">
     <tr>
       <td class="wrapper">
         <table role="presentation" border="0" cellpadding="0" cellspacing="0">
             <tr>
                 <td><p>{lang_label_order_nbr}</p></td><td><p>{order_nbr}</p></td>
             </tr>
             <tr>
                 <td><p>{lang_label_payment}</p></td><td><p>{payment_status}</p></td>
             </tr>
             <tr>
                 <td><p>{lang_price_total}</p></td><td><p>{price_total} {currency}</p></td>
             </tr>
             <tr>
                 <td><p>{lang_label_shipping}</p></td><td><p>{shipping_status}</p></td>
             </tr>
             <tr>
                 <td><p>{lang_label_invoice_address}</p></td>
                 <td><p>{invoice_address}</p></td>
             </tr>
             <tr>
                 <td><p>{lang_label_delivery_address}</p></td>
                 <td><p>{shipping_address}</p></td>
             </tr>
             <tr>
                 <td><p>{lang_label_comment}</p></td>
                 <td><p>{order_user_comment}</p></td>
             </tr>
         </table>
       </td>
     </tr>
 </table>
<hr>
{order_products}
<hr>
<table role="presentation" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td class="wrapper">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td>{lang_price_subtotal}</td>
                    <td align="right">{price_subtotal} {currency}</td>
                </tr>
                <tr>
                    <td>{lang_label_shipping}</td>
                    <td align="right">{price_shipping} {currency}</td>
                </tr>
                <tr>
                    <td>{lang_label_tax}</td>
                    <td align="right">{included_tax} {currency}</td>
                </tr>
                <tr>
                    <td>{lang_price_total}</td>
                    <td align="right">{price_total} {currency}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>