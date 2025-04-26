---
title: se_paypal-pay - Payment Addon
description: Settings for the addon se_paypal-pay
btn: Settings
group: addons
priority: 500
---

# se_paypal-pay settings

In order for this plugin to be displayed in the shopping basket, it must be activated in the settings.


### Additional Costs
This amount is added to the total amount in the shopping cart as soon as this payment method is selected.

### Snippet for Shopping Cart
All snippets that you can use for payment methods are displayed here. These are all snippets named with `cart_pm_*`.
The content of the snippet is displayed in the shopping cart as an explanation of this payment method.
The title of the snippet appears in the selection of payment methods.

### Mode

* __Sandbox:__ Is used to test the plugin, your access data and the PayPal connection. As long as you are in sandbox mode, no costs can be incurred and no real money is moved.
* __Live Account:__ Your real access data is used here.

### Client ID and client secret
You can find these keys in your PayPal account.

### Cancel URL
The customer is redirected to this page if he cancels the PayPal payment process.

### Return URL
The customer will be redirected to this page as soon as the PayPal payment has been completed.
Create this page via Page Management and include `se_paypal-pay` in the Plugins tab.
This means that your customers' payments are taken into account directly in the order and the order is marked as paid.