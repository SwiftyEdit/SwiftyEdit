---
title: Shop - Filter
description: Shop Filter
btn: Filter
group: backend
priority: 400
---

# Create and manage product filters

## Groups

To use filters, you first have to create a group.
Any number of values can then be assigned to this group.

### Input fields

* __Group name__ gives the group its name
* __Description__ appears in the frontend as a tooltip
* __Priority__ controls the sorting when there are several groups
* __Type__ Radio, Checkbox or Range, decides whether the user can activate only one value (Radio), several values (Checkbox) or a value range (Range) of this group.
* __Language__ in case you run a multilingual website and group names are identical
* __Categories__ the filter is only shown in the frontend if it matches the category.

## Values

The __values__ are the actual filters. These can later be selected in the frontend.
Here too, the __Priority__ field controls the sorting.

#### Example

| Group | Values          |
|-------|-----------------|
| Color | red, blue, yellow |

### Assign a filter to a product

So that the correct products are displayed in the frontend,
the filters have to be activated in the backend for the respective product.

To do this, open the product in the backend and click the Filter tab. All filters are shown here and
can be activated.
