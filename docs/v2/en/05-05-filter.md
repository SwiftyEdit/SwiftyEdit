---
title: Shop - Filter
description: Shop Filter
btn: Filter
group: backend
priority: 400
---

# Create and manage product filters

<kbd>Backend</kbd> ▶ <kbd>Shop</kbd> ▶ <kbd>Filter</kbd>

## Groups

To use filters, you first have to create a group.
Any number of values can then be assigned to this group.

### Input fields

| Field       | Type         | Description                                                                                                                              |
|-------------|--------------|--------------------------------------------------------------------------------------------------------------------------------------------|
| Group name  | `Text`       | Gives the group its name.                                                                                                                 |
| Description | `Textarea`   | Appears in the frontend as a tooltip.                                                                                                     |
| Priority    | `Text`       | Controls the sorting when there are several groups.                                                                                       |
| Type        | `Select`     | Radio, Checkbox or Range - decides whether the user can activate only one value (Radio), several values (Checkbox) or a value range (Range) of this group. |
| Language    | `Select`     | In case you run a multilingual website and group names are identical.                                                                     |
| Categories  | `Checkboxes` | The filter is only shown in the frontend if it matches the category.                                                                      |

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
