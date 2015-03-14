# Overview #

Iterator keys are (typically) a special sub-class of container keys that are used for displaying lists of data. They make use of CyTE's iterator functionality and they have access to CyTE's iterator template language.

**Example:** A sample iterator key as it would appear in a template.
```

<ul>
<cyte:my_files>
	<li>{file} - {path|href} - Last modified: {last_mod | datetime(d M, Y h:m)}</li>
</cyte>
</ul>

```

## Iterator Components ##
  * **Iterator list ($this->iteration\_list):** An array of data.
  * **Iterator Template ($this->content):** The iterator template is applied to every element of the iterator list.
  * **$this->iterate():** This function applies the template to each item of the iterator list.
  * **$this->set\_iteration\_list():** This function is intended to be used for generating the iterator list and setting the key's iterator\_list property.
  * **Filters:** Filters are used to manipulate an object's data at the template level.
  * **Filter parameters:** Filter parameters modify how a filter manipulates an object's data

# Creating your own Iterators #

## Step 1 ##
Check to see if a template was passed via the container tag method or set a default template using the keys' check\_attributes function.

The iterator functionality depends on the key being passed a valid template. Without it, the iterate function will not output anything. Most iterator keys will be container keys that accept the iterator template at the page template level.

**Example:** This code causes the key to fail if no template was passed to it.
```

function check_attributes() {
	if(empty($this->content)) {
		$this->failed = TRUE;
	}
}

```

## Step 2 ##
Override the abstract key classes' set\_iteration\_list function. Use this function to define $this->iteration\_list as a non-empty array of data. The elements of the array can be objects or normal variables.

**Example:** In this example, the set\_iteration\_list function populates CyTE's error data into the iteration list.
```

function set_iteration_list() {
	global $errors;
	
	$this->iteration_list = $errors;
}

```

## Step 3 ##
Use the iterate function to apply the template to each element of the key's iterator list. This will generate the desired output.

**Example:** This code returns the output of the iterate function as the key's display output.
```

function display() {		
	if(!$this->failed) {
		return $this->iterate();
	}
}

```

# Iterator Template Language #
The iterator template language allows a developer to access the data in the iterator list at the template level: The iterator template language allows syntax of the form:
```
{var[| filter[(filter_parameters)]]}
```

## Short iterator tag ##
To access a property of an element in the iterator list and output it, use the following syntax where var is the name of the property you want to access:

**Example:** A short iterator tag that pulls data from of a property of an iterator list element
```
{var}
```

To access the entire element in the iterator list, use the following:

**Example:** A short iterator tag that accesses an iterator element
```
{this}
```

This is useful when your iterator list contains variables that are not objects.

You can also access the current iterator list element's position using **this.count**.

**Example:** A short iterator tag that outputs the current element's position in the iterator list.
```
{this.count}
```

## Filtered iterator tag ##
To apply a filter to data, simply append "| filtername" (where 'filtername' is the name of the filter you want to apply) inside the curly brackets.

**Example:** An iterator tag that uses a filter.
```
{this|href}
```

## Filtered iterator tag with a control string ##
To apply a filter to data and pass the filter a control string, simply append "| filtername(controlstring)" (where 'filtername' is the name of the filter you want to apply and 'controlstring' is the control string you want to pass to the fitler) inside the curly brackets.

**Example:** An iterator tag that uses a filter and passes it a control string.
```
{last_mod|datetime(d M, Y h:m)}
```

# Pre-defined Iterator Filters #
## href ##
The 'href' filter is used to turn a path into a link.

The filter accepts a link title as an optional parameter. If one isn't passed, it will use the variable being filtered as the path and the link title.

## datetime ##
The datetime filter is used to format unix time stamps.

The filter requires a format control string to be passed to it. If one isn't passed, the filter will output an empty string. See the documentation of PHP's [date\_format function](http://php.net/manual/en/function.date-format.php) for a list of valid control characters and control string examples.

## htmlspecialchars ##
This filter replaces html special characters with their corresponding html entities. If a control string is passed to the filter, it will be ignored.

## pluralize ##
The pluralize filter outputs a plural or singular suffix depending on the value of the integer being filtered.

The default singular suffix is an empty string. The default plural suffix is "s". - **Example:** Cow and Cows

To change the plural suffix, pass it to the filter as a parameter.

**Example:**
```
{this | pluralize(es)}
```

To change the singular suffix and the plural suffix, pass a comma separated list to the filter with the first element the singular suffix and the second element the plural suffix.

**Example:**
```
cherr{cherry_count | pluralize(y,ies)}
```

Any additional items in the list will be ignored.

**Example:**
```

<ul>
<cyte:file_stats>
	<li>This {path|href(file)} contains {cherrycount} cherr{cherrycount|pluralize(y,ies)}</li>
</cyte>
</ul>

```

# Defining custom filters #
Filters should implement the filter interface and the function execute must be defined. The class name must be prefixed with "filters_". The file should be named the same as the class and located in the filters folder (specified in config if you want to move it)._



**Example:** A new filter called prototype
```
class filters_prototype implements filters  {
	static function execute($data = '', $parameters = '')  {
		// return filtered data
	}
}
```

All filters should have two parameters. When CyTE calls the filter function, it will pass the attribute being filtered to the function as the first parameter. If it is present, the filter's control string is passed as the second parameter.

Always define a second parameter with a default value regardless of whether or not it is needed. This will prevent PHP errors occurring due to malformed iterator template code.

Your custom filter function should return a string, which will be inserted by CyTE into the template at the location of the filter call.