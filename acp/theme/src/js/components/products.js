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


var inputs_price_net = $('.prod_price_net');
var inputs_price_gross = $('.prod_price_gross');

$('.prod_price_net').on('keyup', function() {
    var price_net = $(this).closest('.row').find(inputs_price_net).val();
    var current_gross = swap_net_to_gross(price_net);
    var price_gross_input = $(this).closest('.row').find(inputs_price_gross)
    $(price_gross_input).val(current_gross);
});

$('.prod_price_gross').on('keyup', function() {
    var price_gross = $(this).closest('.row').find(inputs_price_gross).val();
    var current_net = swap_gross_to_net(price_gross);
    var price_net_input = $(this).closest('.row').find(inputs_price_net)
    $(price_net_input).val(current_net);
});


$('.prod_price_net').each(function(i, obj) {
    var price_net = $(this).closest('.row').find(inputs_price_net).val();
    if(price_net) {
        var current_gross = swap_net_to_gross(price_net);
        var price_gross_input = $(this).closest('.row').find(inputs_price_gross)
        $(price_gross_input).val(current_gross);
    }
});

});