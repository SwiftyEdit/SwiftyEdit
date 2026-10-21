import $ from "jquery";
import accounting from "accounting";
$(function() {

function addTax(price,tax) {
    tax = parseInt(tax);
    price = price*(tax+100)/100;
    return price;
}

function removeTax(price,tax) {
    tax = parseInt(tax);
    price = price*100/(tax+100);
    return price;
}

function swap_net_to_gross(price_net) {
    var tax = $( "#tax option:selected" ).text();
    tax = parseInt(tax);
    var price_net_format = price_net.replace(/\./g, '');
    var price_net_format = price_net_format.replace(",",".");
    var current_gross = addTax(price_net_format,tax);
    var current_gross = accounting.formatNumber(current_gross,8,".",",");
    return current_gross;
}

function swap_gross_to_net(price_gross) {
    var tax = $( "#tax option:selected" ).text();
    tax = parseInt(tax);
    var price_gross_format = price_gross.replace(/\./g, '');
    var price_gross_format = price_gross_format.replace(",",".");
    var current_net = removeTax(price_gross_format,tax);
    var current_net = accounting.formatNumber(current_net,8,".",",");
    return current_net;
}


// delegated handlers, so forms loaded later via htmx (e.g. price groups) work too
$(document).on('keyup', '.prod_price_net', function() {
    var price_net = $(this).val();
    var current_gross = swap_net_to_gross(price_net);
    $(this).closest('.row').find('.prod_price_gross').val(current_gross);
});

$(document).on('keyup', '.prod_price_gross', function() {
    var price_gross = $(this).val();
    var current_net = swap_gross_to_net(price_gross);
    $(this).closest('.row').find('.prod_price_net').val(current_net);
});

// fill the gross fields from the stored net prices
function init_gross_prices(container) {
    $(container).find('.prod_price_net').each(function() {
        var price_net = $(this).val();
        if(price_net) {
            var current_gross = swap_net_to_gross(price_net);
            $(this).closest('.row').find('.prod_price_gross').val(current_gross);
        }
    });
}

init_gross_prices(document);

document.addEventListener('htmx:load', function(evt) {
    init_gross_prices(evt.target);
});

});