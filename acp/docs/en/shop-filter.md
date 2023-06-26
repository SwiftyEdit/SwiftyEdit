---
title: Shop Filter
description: Product-Filter for the frontend
btn: Shop Filter
group: backend
priority: 430
---

## Gruppen

To be able to use the filters, a group must be created first.
Any number of values can be assigned to this group.

### Input fields

* __group name__ gives the name to the group
* __Description__ appears in the frontend as a tooltip
* __Priority__ Provides for sorting in case of multiple groups
* __Type__ Checkbox or radio, decides if the user can activate several or only one value of this group.
* __Language__ If you run a multilingual website and group names are identical.
* __Categories__ The filter will be displayed in the frontend only if it matches the category.

## values

The __values__ are the actual filters. You can select them later in the frontend.
Here, too, the __Priority__ field controls the sorting.

#### Example

| Group | Values           |
|-------|------------------|
| Color | red, green, blue |

### Assign filters to a product

So that the correct products are displayed in the frontend,
the filters must be activated in the backend for the relevant product.

To do this, open the product in the backend and click on the __Filter tab__. 
Here all filters are displayed and can be activated.