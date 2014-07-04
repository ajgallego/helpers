<?php 

namespace Ajgallego\Helpers\Datatypes;

/**
* Array helper class
*/
class HelpArray
{
    /**
    * Find the position of the first occurrence of an array of substrings (needles) in a string (haystack).
    * This function is equal to strpos but allows to use an array of needles.
    * @param string $haystack The string to search in.
    * @param array $needles Array of needles to search
    * @param integer $offset If specified, search will start this number of chars from the beginning of the string. 
    * @return True if the needle if found or false in other case.
    */
    public static function strposa($haystack, array $needles, $offset=0)
    {
        foreach($needles as $needle)
        {
            if(strpos($haystack, $needle, $offset) !== false)
                return true; // stop on first true result
        }
        return false;
    }

    /**
    * Search a key value recursively in an array.
    * @param string $needle Value to search recursively
    * @return True if the needle if found or false in other case.
    */
    public static function recursiveFind($needle, array $haystack)
    {
        /* Other implementation:

        foreach ( $haystack as $key => $value ) 
        {
            if ( $needle === $key ) 
                return $value;
           
            if ( is_array( $value ) ) 
            {
                if( ( $rvalue = self::recursiveFind( $needle, $value ) ) !== false )
                    return $rvalue;
            }
        }
        return false;
        */

        $iterator  = new RecursiveArrayIterator($haystack);
        $recursive = new RecursiveIteratorIterator($iterator,
                             RecursiveIteratorIterator::SELF_FIRST);

        foreach ($recursive as $key => $value) 
        {           
            if ($key === $needle) {
                return $value;
            }
        }

        return false;
    }

    /**
    * Transform an array to an object
    * @param array $array The input array
    * @return The new object
    */
    public static function arrayToObject( array $array )
    {
        $object = new stdClass();

        foreach ($array as $key => $value)
        {
            $object->$key = $value;
        }

        return $object;
    }

    /**
    * Transform an object to an array
    * @param $obj The input object
    * @return The new array
    */
    public static function objectToArray( $obj )
    {
        $arrObj = is_object($obj) ? (array)($obj) : $obj;
        $arr = array();

        foreach ($arrObj as $key => $val) 
        {
            if( is_array($val) || is_object($val) )
                $val = self::objectToArray($val);

            $arr[$key] = $val;
        }
        return $arr;
    } 

    /**
    * Creates an array from a mysql object, setting the keys of the array using
    * the key property of the object, the value is obtained from the value_name
    * @param $object
    * @param $key_name
    * @param $value_name
    * @return
    */
    public static function mysqlObjectToArray( $object, $key_name, $value_name )
    {
        $options = array();

        foreach( $object as $item )
        {
            $options[ $item->$key_name ] = $item->$value_name;
        }

        return $options;
    }

    /**
    * Compares two arrays and returns the differencies.
    * @param array $array1 First input array to compare
    * @param array $array2 Second input array to compare
    * @return Returns an array with the differencies or false if are identical.
    */
    public static function arrayCompare( array $array1, array $array2 ) 
    { 
        $diff = false; 
        
        // Left-to-right 
        foreach ($array1 as $key => $value) 
        { 
            if (!array_key_exists($key,$array2)) { 
                $diff[0][$key] = $value; 
            } elseif (is_array($value)) { 
                 if (!is_array($array2[$key])) { 
                        $diff[0][$key] = $value; 
                        $diff[1][$key] = $array2[$key]; 
                 } else { 
                        $new = self::array_compare($value, $array2[$key]); 
                        if ($new !== false) { 
                             if (isset($new[0])) $diff[0][$key] = $new[0]; 
                             if (isset($new[1])) $diff[1][$key] = $new[1]; 
                        }; 
                 }; 
            } elseif ($array2[$key] !== $value) { 
                 $diff[0][$key] = $value; 
                 $diff[1][$key] = $array2[$key]; 
            }; 
        }; 
        
        // Right-to-left 
        foreach ($array2 as $key => $value) { 
            if (!array_key_exists($key,$array1)) { 
                 $diff[1][$key] = $value; 
            }; 
            // No direct comparsion because matching keys were compared in the 
            // left-to-right loop earlier, recursively. 
        }; 
        return $diff; 
    }

    /**
    * 
    */
    public static function printNice( $element )
    {
        return self::prv_printNice( $element, 100, array() );
    }

    /**
    * 
    */
    private static function prv_printNice( $elem, $max_level=100, $print_nice_stack=array() )
    {
        $str = '';

        if(is_array($elem) || is_object($elem))
        {
            if( in_array( $elem, $print_nice_stack, true) )
            {
                return '<span style="color:red">RECURSION</span>';
            }

            $print_nice_stack[] = $elem;

            if( $max_level < 1 )
            {
                return '<span style="color:red">Max recursion level</span>';
            }
            
            $max_level--;

            $str .= '<table border=1 cellspacing=0 cellpadding=3 width=100%>';
            
            if( is_array($elem) )
            {
                //$str .= '<tr><td colspan=2 style="background-color:#333333;color:white"><strong>ARRAY</strong></td></tr>';
            }
            else
            {
                $str .= '<tr><td colspan=2 style="background-color:#333333;"><strong>';
                //$str .= '<font color=white>OBJECT Type: '.get_class($elem).'</font></strong></td></tr>';
            }
            
            $color=0;

            foreach( $elem as $k => $v )
            {
                if($max_level%2)
                {
                    $rgb = ($color++%2)?'#8888BB':'#BBBBFF';
                }
                else
                {
                    $rgb = ($color++%2)?'#888888':'#BBBBBB';
                }

                $str .= '<tr><td valign="top" style="width:40px;background-color:'.$rgb.';">'
                        .   '<strong>'.$k.'</strong>'
                        .'</td><td>'
                        .   self::printNice( $v, $max_level, $print_nice_stack )
                        .'</td></tr>';
            } 

            $str .= '</table>';
        }
        else
        {
            $elem = trim( $elem );

            if($elem === null)
            { 
                $str .= '<span style="color:green">null</span>'; 
            }
            elseif($elem === 0)
            { 
                $str .= '0';
            }
            elseif($elem === true)
            { 
                $str .= '<span style="color:green">true</span>'; 
            }
            elseif($elem === false)
            { 
                $str .= '<span style="color:green">false</span>';
            }
            elseif($elem === "")
            {
                $str .= '<span style="color:green">empty string</span>'; 
            }
            else
            {
                //$str = str_replace("\n",'<strong><span style="color:red">*</span></strong><br/>'."\n",$elem);
                $aux = str_replace( "\n", "<br/>\n", $elem);
                $aux = str_replace( ',', ', ', $aux );

                $str .= $aux;
            }
        }

        return $str;
    }

/*
    //--------------------------------------------------------------
    public static function printNice( $elem, $max_level=100, $print_nice_stack=array() )
    {
        if(is_array($elem) || is_object($elem))
        {
            if(in_array($elem,$print_nice_stack,true))
            {
                echo '<span style="color:red">RECURSION</span>';
                return;
            }
            
            $print_nice_stack[]=$elem;
            if($max_level<1)
            {
                echo '<span style="color:red">nivel maximo alcanzado</span>';
                return;
            }
            
            $max_level--;
            echo '<table border=1 cellspacing=0 cellpadding=3 width=100%>';
            
            if(is_array($elem))
            {
                //echo '<tr><td colspan=2 style="background-color:#333333;color:white"><strong>ARRAY</strong></td></tr>';
            }
            else
            {
                echo '<tr><td colspan=2 style="background-color:#333333;"><strong>';
                //echo '<font color=white>OBJECT Type: '.get_class($elem).'</font></strong></td></tr>';
            }
            
            $color=0;
            foreach($elem as $k => $v)
            {
                
                if($max_level%2)
                {
                    $rgb=($color++%2)?'#8888BB':'#BBBBFF';
                }
                else
                {
                    $rgb=($color++%2)?'#888888':'#BBBBBB';
                }
                
                echo '<tr><td valign="top" style="width:40px;background-color:'.$rgb.';">';

                echo '<strong>'.$k.'</strong></td><td>'; 

                self::print_nice($v,$max_level,$print_nice_stack);
                echo '</td></tr>';
            } 
            
            echo '</table>';
            return;
        }

        $elem = trim( $elem );

        if($elem === null)
        { 
            echo '<span style="color:green">null</span>'; 
        }
        elseif($elem === 0)
        { 
            echo '0';
        }
        elseif($elem === true)
        { 
            echo '<span style="color:green">true</span>'; 
        }
        elseif($elem === false)
        { 
            echo '<span style="color:green">false</span>';
        }elseif($elem === "")
        {
            echo '<span style="color:green">empty string</span>'; 
        }
        else
        { 
            //$str = str_replace("\n",'<strong><span style="color:red">*</span></strong><br/>'."\n",$elem);
            $str = str_replace( "\n", "<br/>\n", $elem);
            $str = str_replace( ',', ', ', $str );
            echo $str;
        }
    }
    */
}
