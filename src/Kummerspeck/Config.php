<?php namespace Kummerspeck;
/**
 * Kummerspeck Wordpress Utilities
 *
 * @package Kummerspeck/Wordpress
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

/**
 * Config class handles all interactions between loading and saving options
 * to the [wordpress options api](https://codex.wordpress.org/Options_API).
 * This class can also load option values from a config file which
 * is useful when setting default values in an options form.'
 *
 * @package Kummerspeck/Wordpress
 * @subpackage Utility/Config
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Config extends \Multi\Arr {

    /**
     * Set the name space for all options
     * loaded by this object. A typical usage would
     * to set the namespace to a plugin name.
     *
     * @access protected
     * @var string
     */
    protected $_namespace;

    /**
     * Path to where config files exist.
     *
     * @access protected
     * @var string
     */
    protected $_path;

    /**
     * Tracks which top-level config/option values have been
     * loaded.
     *
     * @access protected
     * @var array
     */
    protected $_loaded = array();

    /**
     * Initial the object with the path to config files and
     * optionally arguments.
     *
     * @access public
     * @param string $path Path to where config files are.
     * @param string $namespace Option namespace.
     * @param string $delimiter Delimiter character for array access.
     * @return void
     */
    public function __construct($path, $namespace = null, $delimiter = null)
    {
        parent::__construct(null, $delimiter);

        $this->setFilePath($path);

        if ($namespace !== null)
        {
            $this->setNamespace($namespace)
                ->loadNamespace();
        }
    }

    /**
     * Force a save to the options API when the
     * object is destroyed or dereferenced.
     *
     * @access public
     * @return void
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * getPathParts - Splits a string by the delimiter character.
     *
     * @access public
     * @param  string $path Array index path
     * @return array        Array of strings
     */
    public function getPathParts($path)
    {
        return preg_split(
            '/\s?' . preg_quote($this->getDelimiter()) . '\s?/',
            $path
        );
    }

    /**
     * Load a key/path from a config file and then from
     * the option key.
     *
     * @access public
     * @param  string $key Array index path to load.
     * @return mixed       Config/Option value.
     */
    public function load($key)
    {
        // Don't reload the value if it's been loaded before.
        if ($this->loaded($key))
        {
            return $this;
        }

        // Get the path parts.
        $parts = $this->getPathParts($key);

        // If there's a config file path set then
        // let's try and load values from it.
        if ($filePath = $this->getFilePath())
        {
            $pathParts = $parts;
            $path = '';

            // Loop through the parts until a file is found
            while ($part = array_shift($pathParts))
            {
                if (is_file($filePath . $path . $part))
                {
                    // Replace directory characters with the
                    // delimiter character for proper setting
                    // of the data array in this object.
                    $pathKey = str_replace(
                        array('/', '\\'),
                        $this->getDelimiter(),
                        $path
                    );

                    // Append the last part
                    $pathKey .= $part;

                    // Finally, set the value to the complete
                    // array index path.
                    $this[$pathKey] = $this->_loadFile(
                        $filePath . $path . $part,
                        'php'
                    );
                }
                elseif (is_dir($filePath . $path . $part))
                {
                    // If this is a directory then append
                    // the dirname to the path variable
                    // which will be used in the next
                    // iteration to file a config file
                    $path .= $part . DIRECTORY_SEPARATOR;
                }
            }
        }

        // Let's lookup an option value that might be saved
        // to the top-level path parts.
        $optionValue = get_option($this->getNamespace() . $parts[0]);

        if ($optionValue)
        {
            // Overwrite any config file values with the value
            // loaded from the options api.
            $this[$parts[0]] = unserialize($optionValue);
        }

        // Track the loaded values.
        $this->_loaded[] = $this->getNamespace() . $parts[0];

        return $this;
    }

    /**
     * Check to see if a array index path has been
     * loaded already by this object.
     *
     * @access public
     * @param  string $key Array index path
     * @return bool        True if the $key has been loaded else false.
     */
    public function loaded($key)
    {
        $parts = $this->getPathParts($key);

        return (in_array($this->getNamespace() . $parts[0], $this->_loaded));
    }

    /**
     * Load config file or folder of the set namespace if any exists.
     *
     * @access public
     * @return $this
     */
    public function loadNamespace()
    {
        $namespace = rtrim($this->getNamespace(), $this->getDelimiter());
        $filePath  = $this->getFilePath();

        if (is_file($filePath . $namespace . '.php'))
        {
            $this->_data = $this->_loadFile($filePath . $namespace, 'php');
        }
        elseif (is_dir($filePath . $namespace))
        {
            //
        }
    }

    /**
     * Load a config file's content.
     *
     * @access private
     * @param  string $file      Complete path to the file name.
     * @param  string $extension File extension.
     * @return array  The file should return an array of associative data.
     */
    private function _loadFile($file, $extension)
    {
        return include $file . '.' . $extension;
    }

    /**
     * Saves the entire data array of this object into
     * the Wordpress Options API.
     *
     * @access public
     * @return $this
     */
    public function save()
    {
        // Iterate through the data array and save each index
        // as a field in the options api.
        foreach ($this->_data as $key => $value)
        {
            update_option(
                $this->getNamespace() . $key,
                serialize($value)
            );
        }

        return $this;
    }

    /**
     * Set the namespace for the options loaded by this
     * object.
     *
     * @access public
     * @param string $namespace Object namespace.
     * @return $this
     */
    public function setNamespace($namespace)
    {
        // Clean up the string, make sure there's no trailing
        // delimiter character since we're going to re-append it.
        $this->_namespace = rtrim(
            $namespace,
            $this->getDelimiter() . ' '
        );

        // Append delimiter
        $this->_namespace .= $this->getDelimiter();

        return $this;
    }

    /**
     * Get the namespace.
     *
     * @access public
     * @return string Object namespace
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Set file path where config files are located.
     *
     * @access public
     * @param string $path
     * @return $this
     * @throws InvalidArgumentException If invalid path is given.
     */
    public function setFilePath($path)
    {
        // Validate the path by resolving it.
        $resolvedPath = realpath($path);

        // Throw exception if it's not a valid
        // path on the system.
        if ( ! $path)
        {
            throw new \InvalidArgumentException(
                sprintf(
                    'Config path "%s" does not exist',
                    $path
                )
            );
        }

        // Append a directory separator and set the property.
        $this->_path = $resolvedPath . DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * Get the file path.
     *
     * @access public
     * @return string File path.
     */
    public function getFilePath()
    {
        return $this->_path;
    }

}