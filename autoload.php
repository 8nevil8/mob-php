<?

function __autoload($class_name) {
    $class_folder = 'classes';
    //Lookup in same directory
    $class_paths[] = dirname(__FILE__) . '/';
    $class_paths[] = $_SERVER['DOCUMENT_ROOT'] . "/app/$class_folder/";
    $class_paths[] = $_SERVER['DOCUMENT_ROOT'] . "/admin/app/$class_folder/";
    $class_paths[] = dirname($_SERVER['SCRIPT_FILENAME']) . "/$class_folder/";
    $class_paths[] = dirname(__FILE__) . "/$class_folder/";
    //$CLASS_PATHS
    if (!empty($GLOBALS["CLASS_PATHS"])) {
        if (!is_array($GLOBALS["CLASS_PATHS"])) throw new Exception('$CLASS_PATHS must be array!');
        $class_paths = array_merge($class_paths, $GLOBALS["CLASS_PATHS"]);
    }

    //A_B_C
    $slashed_class_name = str_replace("_", "/", $class_name); // A/B/C
    $short_path = substr($slashed_class_name, 0, strrpos($slashed_class_name, '/')); // A/B
    $class_paths[] = $_SERVER['DOCUMENT_ROOT'] . "/$short_path/$class_folder/";

    foreach ($class_paths as $class_path) {
        // //A/B/C.class.php
        $file_full_name = "{$class_path}{$class_name}";
        if (lookupClass($file_full_name)) {
            return;
        }

        $file_full_name = "{$class_path}{$slashed_class_name}";
        if (lookupClass($file_full_name)) {
            return;
        }

        // /A/B/C/A_B_C.class.php
        $file_full_name = "{$class_path}{$slashed_class_name}/{$class_name}";
        if (lookupClass($file_full_name)) {
            return;
        }

        // /A/B/A_B_C.class.php
        $file_full_name = "{$class_path}{$short_path}/{$class_name}";
        if (lookupClass($file_full_name)) {
            return;
        }

        // /A/B/A_B_C/A_B_C.class.php
        $file_full_name = "{$class_path}{$short_path}/{$class_name}/{$class_name}";
        if (lookupClass($file_full_name)) {
            return;
        }
    }
}

function lookupClass($file_full_name, $class_postfix = 'class.php') {
    $file_full_name = $file_full_name . '.' . $class_postfix;
//    echo $file_full_name, '<br>';
    if (file_exists($file_full_name)) {
        require_once($file_full_name);
        return true;
    }
    return false;
}
// Future-friendly json_encode
if (!function_exists('json_encode')) {

    function json_encode($data) {
        $json = new Services_JSON();
        return( $json->encode($data) );
    }
}

// Future-friendly json_decode
if (!function_exists('json_decode')) {

    function json_decode($data, $bool) {
        if ($bool) {
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        } else {
            $json = new Services_JSON();
        }
        return( $json->decode($data) );
    }
}

if (!function_exists('array_replace_recursive')) {

    function recurse($array, $array1) {
        foreach ($array1 as $key => $value) {
            // create new key in $array, if it is empty or not an array
            if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))) {
                $array[$key] = array();
            }

            // overwrite the value in the base array
            if (is_array($value)) {
                $value = recurse($array[$key], $value);
            }
            $array[$key] = $value;
        }
        return $array;
    }

    function array_replace_recursive($array, $array1) {
        // handle the arguments, merge one by one
        $args = func_get_args();
        $array = $args[0];
        if (!is_array($array)) {
            return $array;
        }
        for ($i = 1; $i < count($args); $i++) {
            if (is_array($args[$i])) {
                $array = recurse($array, $args[$i]);
            }
        }
        return $array;
    }
}
?>