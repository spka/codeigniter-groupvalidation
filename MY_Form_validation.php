<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation {

    protected $CI;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->CI =& get_instance();
    }


/*  Alternative run function in order to run "validation groups"
    make sure config/form_validation.php has the right syntax
    ----------------------------------------------------------- */

    public function run_group($group = '')
    {
        // Do we even have any data to process?
        $validation_array = empty($this->validation_data) ? $_POST : $this->validation_data;
        if (count($validation_array) === 0)
        {
            return FALSE;
        }

        // Does the _field_data array containing the validation rules exist?
        // If not, we look to see if they were assigned via a config file
        if (count($this->_field_data) === 0)
        {
            // No validation rules?
            if (count($this->_config_rules) === 0)
            {
                return FALSE;
            }

            // New group array key support. From these values create a new temporary group.
            // Then use this new group as validation group. When empty, validate the whole config file.
            foreach($this->_config_rules as $config_item)
            {
                if(isset($config_item['groups']))
                {
                    $groups = explode('|', $config_item['groups']);
                    foreach($groups as $value)
                    {
                        $amount = 0; $field = NULL;
                        // remove [*] from group string if possible and get the amount of fields
                        if(preg_match('/\[(.*?)\]/', $value))
                        {
                            $amount = substr($value, -2, -1) - 1; // -1 the amount, because counting starts at 0
                            $value = substr($value, 0, -3); // value of a field without brackets
                            $field = $config_item['field']; // save the value of the field for later use
                        }
                        if($value == $group)
                        {
                            for($i = 0; $i <= $amount; $i++)
                            {
                                // only add array numbers to fields if the group had numbers
                                $config_item['field'] = ($field ? $field.'['.$i.']' : $config_item['field']);
                                $temp_config_array[] = $config_item;
                            }
                        }
                    }
                }
            }

            $this->set_rules(isset($temp_config_array) ? $temp_config_array : $this->_config_rules);

            // Were we able to set the rules correctly?
            if (count($this->_field_data) === 0)
            {
                log_message('debug', 'Unable to find validation rules');
                return FALSE;
            }
        }

        // Load the language file containing error messages
        $this->CI->lang->load('form_validation');

        // Cycle through the rules for each field and match the corresponding $validation_data item
        foreach ($this->_field_data as $field => $row)
        {
            // Fetch the data from the validation_data array item and cache it in the _field_data array.
            // Depending on whether the field name is an array or a string will determine where we get it from.
            if ($row['is_array'] === TRUE)
            {
                $this->_field_data[$field]['postdata'] = $this->_reduce_array($validation_array, $row['keys']);
            }
            elseif (isset($validation_array[$field]) && $validation_array[$field] !== '')
            {
                $this->_field_data[$field]['postdata'] = $validation_array[$field];
            }
        }

        // Execute validation rules
        // Note: A second foreach (for now) is required in order to avoid false-positives
        // for rules like 'matches', which correlate to other validation fields.
        foreach ($this->_field_data as $field => $row)
        {
            // Don't try to validate if we have no rules set
            if (empty($row['rules']))
            {
                continue;
            }

            $this->_execute($row, $row['rules'], $this->_field_data[$field]['postdata']);
        }

        // Did we end up with any errors?
        $total_errors = count($this->_error_array);
        if ($total_errors > 0)
        {
            $this->_safe_form_data = TRUE;
        }

        // Now we need to re-set the POST data with the new, processed data
        $this->_reset_post_array();

        return ($total_errors === 0);
    }

    
/*  required fix for array validation
    http://ellislab.com/forums/viewthread/159243/
    ----------------------------------------------------------- */

    public function _execute($row, $rules, $postdata = NULL, $cycles = 0)
    {
        if ((! is_array($postdata) && strlen($postdata) == 0) || (is_array($postdata) && empty($postdata))) $postdata = NULL;
        return parent::_execute($row, $rules, $postdata, $cycles);
    }


/*  returns error messages as array
    ----------------------------------------------------------- */

    public function error_array()
    {
        if (count($this->_error_array) === 0)
        {
            return FALSE;
        }
        else
        {
            return $this->_error_array;
        }
    }


/*  extra validation options
    ----------------------------------------------------------- */

    public function numeric_dash($str)
    {
        return ( !preg_match( '/^[\-+]?[0-9-]*\.?[0-9]+$/', $str)) ? FALSE : TRUE;
    }

    // http://stackoverflow.com/questions/5436824/matching-accented-characters-with-javascript-regexes
    // http://en.wikibooks.org/wiki/Unicode/Character_reference/0000-0FFF

    public function alpha_space($str)
    {
        return ( !preg_match("/^([a-z\x{00C0}-\x{017E} ])+$/iu", $str)) ? FALSE : TRUE;
    }

    public function alpha_space_dot($str)
    {
        return ( !preg_match("/^([a-z\x{00C0}-\x{017E} \.])+$/iu", $str)) ? FALSE : TRUE;
    }

    public function alpha_space_dash($str)
    {
        return ( !preg_match("/^([a-z\x{00C0}-\x{017E} \-])+$/iu", $str)) ? FALSE : TRUE;
    }

    // callback for alpha + numeric + dash + single quote + dot + space

    public function alpha_numeric_extra($str)
    {
        return ( !preg_match("/^([\w\.\-'\(\)\s\/]+)$/i", $str)) ? FALSE : TRUE;
    }

    public function banknumber($str)
    {
        return ( !preg_match("/^[1-9][0-9]{6,9}$/", $str)) ? FALSE : TRUE;
    }

    public function iban($str)
    {
        return ( !preg_match("/^NL[0-9]{2}[A-Z]{4}[0-9]{10}$/", $str)) ? FALSE : TRUE;
    }

    public function convert_date($datum)
    {
        return date('Y-m-d',strtotime($datum));
    }

}
/* End of file Template.php */
/* Location: ./application/libraries/MY_Form_validation.php */
