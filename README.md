# Kummerspeck Wordpress Config Library

This library provides an easier interface to the options api using OOP design.

## Features

* Optionally load values from a config file which is useful for setting default values on options that don't exist yet.
* Namespace your options.
* Access multi-dimensional arrays of options using the dot-notation in your index.

## Examples

	// This is the contents of a config file named "example.php" in "/path/to/configs".

	return array(
		'name'	=> 'Plugin Example',
		'description' => 'An example plugin description.',
		'first_level' => array(
			'second_level' => array(
				'test1' => "embedded value",
				),
			),
		);

	// Contents of a functions.php or plugin file.

	include_once 'vendor/autoload.php';

	$options = new \Kummerspeck\Config('/path/to/configs', 'example');

	// Would echo "Plugin Example"
	echo $options['name'];

	// Would echo "embedded value"
	echo $options['first_level.second_level.test1'];

	// Example of setting a value;
	$options['name'] = 'A different name';

	// Would echo the just set value above.
	echo $options['name'];

	// Just because we set the index "name" to a new value doesn't mean
	// it's saved in the options api. To do that, just call the save method
	// on the config object.
	$options->save();