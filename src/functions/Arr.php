<?php namespace Kummerspeck\Arr;

function get_key($key, array $array, $default = null)
{
    return (isset($array[$key])) ? $array[$key] : $default;
}

function get_path($path, array $array = null, $default = null, $delimiter = '.')
{
    if (array_key_exists($path, $array))
    {
        // No need to do extra processing
        return $array[$path];
    }

    // Eliminate any spaces between delimiters.
    $path = preg_replace('/\s?' . preg_quote($delimiter) . '\s?/', $delimiter, $path);

    // Split the keys by delimiter
    $keys = explode($delimiter, $path);

    do
    {
        $key = array_shift($keys);

        if (ctype_digit($key))
        {
            // Make the key an integer
            $key = (int) $key;
        }

        if (isset($array[$key]))
        {
            if ($keys)
            {
                if (is_array($array[$key]))
                {
                    // Dig down into the next part of the path
                    $array = $array[$key];
                }
                else
                {
                    // Unable to dig deeper
                    break;
                }
            }
            else
            {
                // Found the path requested
                return $array[$key];
            }
        }
        else
        {
            // Unable to dig deeper
            break;
        }
    }
    while ($keys);

    // Unable to find the value requested
    return $default;
}

function set( & $array, $path, $value, $delimiter = '.')
{
    // Split the keys by delimiter
    $keys = explode($delimiter, $path);

    // Set current $array to inner-most array path
    while (count($keys) > 1)
    {
        $key = array_shift($keys);

        if (ctype_digit($key))
        {
            // Make the key an integer
            $key = (int) $key;
        }

        if ( ! isset($array[$key]))
        {
            $array[$key] = array();
        }

        $array =& $array[$key];
    }

    // Set key on inner-most array
    $array[array_shift($keys)] = $value;
}

function unset_path( & $array, $path, $delimiter = '.')
{
    // Split the keys by delimiter
    $keys = explode($delimiter, $path);

    // Set current $array to inner-most array path
    while (count($keys) > 1)
    {
        $key = array_shift($keys);

        if (ctype_digit($key))
        {
            // Make the key an integer
            $key = (int) $key;
        }

        if (isset($array[$key]))
        {
            $array =& $array[$key];
        }
    }

    // Set key on inner-most array
    unset($array[array_shift($keys)]);
}