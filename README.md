# SwagTax

## What does this plugin do?

This plugin adds a new wizard to the Shopware backend, which allows easily changing the tax rate to another.

The user can

* create a mapping from old tax rate to new tax rate
* calculate optionally all prices of chosen customer groups
* run it now or on a scheduled date

## Installation

* Upload the plugin with plugin manager
* Install the plugin and activate it
* Clear all caches

## Extension

When you want to react to in your plugin to the tax change. You can subscribe to the event `Swag_Tax_Updated_TaxRate`. This event will be fired on any single tax change. The given options looks like this:

```php
[
  'config' => [ // The user configuration in the module
    'id' => '1',
    'active' => '1',
    'recalculate_prices' => true, // Should the prices be recalculated to the gross price?
    'tax_mapping' => [ // key => oldTaxId, value => new taxRate
      1 => 15,
    ],
    'customer_group_mapping' => [ // List of customer groups where the price should be recalcuated
      'EK',
    ],
    'scheduled_date' => '2020-06-06 13:59:07',
  ],
  'newTaxId' => 7, // New created tax id
  'newTaxRate' => 15, // New tax rate
]
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
