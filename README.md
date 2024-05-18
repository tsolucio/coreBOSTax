# coreBOS Tax Module

Tax module that saves different tax percentages for advanced tax management in coreBOS.

This module, along with the [coreBOS Tax Category module](https://github.com/tsolucio/coreBOSTaxType), permits us to establish configurations like different taxes per client, depending on their billing localization, their legal status of tax retention, or any other combination of conditions we need in our business.

The [Tax Type module](https://github.com/tsolucio/coreBOSTaxType) will permit us to tag products, clients and vendors with a tax classification, while this Tax module will permit us to define a tax value for each tax type using escalation rules depending on the configured values during the creation of the inventory record.

It is important to note that once these modules are installed you should not use the Settings Tax Configuration anymore. Create tax records only in this module, it will take care of adjusting the necessary internal details.

You can get a little more information in [this blog post](https://blog.corebos.org/blog/advancedtax).

## Updates

### 2024-04-25

If you have installed the modules before 2024-04-25 you have to update to fix an error calculating taxes when individual tax mode is selected.

Overwrite and add these files:

- modules/coreBOSTax/coreBOSTaxHandler.php
- modules/coreBOSTax/coreBOSTax.php
- modules/coreBOSTax/changesets/ycoreBOSTax.xml
- modules/coreBOSTax/changesets/addTaxCalculationgetInventoryDetailsSQL.php

Copy `modules/coreBOSTax/changesets/ycoreBOSTax.xml` to `modules/cbupdater/cbupdates/` and load and apply change sets using the application updater.
