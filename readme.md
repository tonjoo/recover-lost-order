# Abandon Order Email

WooCommerce plugin to automaticaly sent email for user who abandon their order (did not complete payment).


## Available Hooks
* `aoe_email_hading` *(filter)*
    Edit email heading.
* `aoe_email_subject` *(filter)*
    Edit email subject.
* `aoe_email_template_path` *(filter)*
    Edit email template path
* `aoe_token_variable` *(filter)*
    Set token variable name on recovery URL
* `aoe_email_schedule` *(filter)*
    Edit emailer cron schedule. Default to every 5 minutes.
* `aoe_email_sent` *(action)*
    Fired when email successfully sent to user. Args: `$order_id`
* `aoe_recovered_cart` *(action)*
    Fired when a cart data successfully created from an abandoned order. Args: `$abandoned_order_id`
* `aoe_recovered_order` *(action)*
    Fired when a recovered order successfully created. Args: `$recovered_order_id`, `$abandoned_order_id`