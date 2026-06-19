# TinyTemplate
TinyTemplate is a small JavaScript templating library using the `<template>` HTML element.

The library is a single .js file, that creates a `TinyTemplate` object. This object has the following methods:
```JavaScript
TinyTemplate.activate(name [, data [, elem [, do_not_insert]]])
TinyTemplate.fill(element, data)
```

## .activate(...)
```JavaScript
TinyTemplate.activate(name [, data [, elem [, do_not_insert]]])
```
Returns the newly cloned fragment of the contents in the template, with values from `data` inserted.
See below on defining where data is inserted.
Unless specified, the fragment will automatically be inserted into the document just before the template element.
This is meant to ease use of templates for creating lists of similar elements.

When cloning the template, any `id` attributes on radiobuttons or checkboxes are replaced along with their corresponding labels.
To disable this behaviour, set `data.disable_random_radio = true` and `data.disable_random_checkbox = true` respectively, where `data` is the object passed as the `data` parameter.

Parameter | Description
---|---
`name`| A string specifying which template to use. Templates specify their name with the `data-tmpl-name` attribute.
`data`| An object with values that will be inserted in the newly created fragment.
`elem`| Restricts the search for the template to be inside the given HTMLElement. Defaults to `document`. This enables multiple templates with identical names in the same document.
`do_not_insert` | Boolean. If true, the newly created fragment will not be inserted automatically.

## .fill(...)
```JavaScript
TinyTemplate.fill(element, data)
```
See below on defining where data is inserted.

Parameter | Description
---|---
`element`| The values in `data` will be inserted into the given `HTMLElement` and all it's children and descendants.
`data`| An object with the values that will be inserted.

# Defining data insertion
TinyTemplate use a custom attribute named `data-tmpl` to specify how and where to insert values. The syntax is as follows:
```
data-tmpl="target1:value1,target2:value2,target3:value3"
```

Where `target` is the method of insertion, and `value` is the name of the property in `data` that should be inserted.

The following targets are supported:

Target  |Result
--------|---
content | The value is inserted as text, using `.textContent`
value   | The value is assigned to the `value` attribute used by `<input>` elements.
checked | The value is assigned to the `checked` attribute used by `<input type=checkbox>` elements.
radio   | The `checked` attribute used by `<input type=radio>` is set to `true` if the value is soft equal to the `value` attribute, otherwise it is set to `false`.
id      | The value is set as the id of the element.
class   | The value is set as the class of the element. If `data-tmpl-class-prefix` exists, then it is prepended to the value before setting the class.
src     | The value is assigned to the `src` attribute used by `<img>` and other elements.
href    | The value is assigned to the `href` attributed used by `<a>` and other elements. If `data-tmpl-href-prefix` exists, then it is prepended to the value before setting the `href` attribute.
data-*  | The value is assigned to the named custom attribute.
