# Payment Addon for SwiftyEdit

Help and instructions for use and settings are available at:

`/admin/addons/plugin/se_paypal-pay/docs/`

## Notes

* This addon **has aftersale** functions
* Do not remove this Plugin. It will be reinstalled with every update anyway.
* Good to know: Plugins that are not activated do not affect the Core. So, if you don't need it, do not activate it.

## How it works

In order for this plugin to be displayed in the shopping cart, it must be activated under
Settings / Shop / Payment & Shipping

`se_paypal-pay/aftersale.php`
will be included after the checkout is done. A button for the PayPal payment process is displayed.

`se_paypal-pay/global/index.php`
* Create a page to which PayPal redirects when the payment has been made.
* Activate this Plugin.
* The orders are automatically marked as paid.

### Resources

* https://github.com/paypal/Checkout-PHP-SDK
* https://developer.paypal.com/