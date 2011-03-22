<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/***
* Form library for CodeIgniter
*
*    author: Woody Gilk
* copyright: (c) 2006
*   license: http://creativecommons.org/licenses/by-sa/2.5/
*      file: libraries/Form.php
*/

/***
* Minor Alterations
*
*    author: Bianca Sayan
* 	description: changed the build function so that it can handle a new definition type "multiple"
*/

class Form_xsd {

    public function Form_xsd () {
    /***
     * @constructor
     */
        $obj =& get_instance();    
        $obj->load->library(array('xml','recaptcha'));
	    $obj->lang->load('recaptcha');
        $this->ci =& $obj;  
    } /*** END ***/
    
    private $ci;

    /*** Public variables ***/
    public $error     = '';
    public $set_error = '';
    public $output    = '';

    /*** Internal variables ***/
    private $action;
    private $rules;
    private $upload_rules;
    private $data;
    private $set_data;
    private $elements        = array();
    private $elements_upload = array();
    private $uploaded_files  = array();
    private $has_upload = false;

    public function load ($name) {
    /***
     * @public
     * Load a form definition for parsing
     */
        if (! $this->ci->xml->load ("assets/schemas/ECOSPOLD2/$name", "xsd")) {
            $this->error = "Failed to load form: $name";
            return false;
        }

        // Reset sensative vars to default value
        $this->action = '';
        $this->rules  = '';
        $this->data   = '';
        $this->set_data   = '';
        $this->set_error  = '';
        $this->has_upload = false;
        $this->elements   = array();

        $data = $this->ci->xml->parse ();
		var_dump($data);
        if (! is_array($data)) {
            $this->error = "No form data found in /data/forms/$name.xml";
            return false;  
        }
        else {
            $data = $data['form'][0];

            $this->data     = $data;
            $this->action   = $data['__attrs']['action'];
            $this->get_fields ($data);

            $this->elements        = array_unique($this->elements);
            $this->elements_upload = array_unique($this->elements_upload);
        }   

        return $data;
    } /*** END load ***/  

	public function change_action($URI) {		
		$this->action .=  "/" . $URI;
	}

    private function get_fields ($array) {
    /***
     * @private
     * Extract the rules and field names from the data
     */  
        if (! is_array($array)) {
            return;
        }

        foreach ($array as $key => $val) {
               if ($key == 'fieldset' && is_array ($val)) {
                    foreach ($val as $_val) {
                    $this->get_fields ($_val);
                }
            }
            elseif (! is_numeric ($key) && $key != '__attrs' && $key != 'fieldset') {
                foreach ($val as $_key => $_val) {
                    if (isset ($_val['__attrs']) && $_attrs = $_val['__attrs']) {
                        if (isset ($_attrs['rules'])) {
                            $this->rules[$key] = $_attrs['rules'];
                           }
                          if (isset ($_attrs['allow'])) {
                            $this->upload_rules[$key] = $_attrs['allow'];
                        }
                          }

                    if (isset ($_val['type']) && $_val['type'][0] == 'file') {
                        $this->has_upload        = true;
                        $this->elements_upload[] = $key;
                    }
                    else {
                                $this->elements[] = $key;
                     }                            
                } // end foreach
            } //end elseif   
        }
    } /*** END get_fields ***/   

    public function set_action ($location) {
    /***
     * @public
     * Set the form action
     */  

        $this->action = $location;
        return true;
    } /** END action ***/  

    public function set ($element, $key, $value = false) {
    /***
     * @public
     * Set an attribute of an element, or a new elemenet
     */  
        if (is_array ($key)) {
            foreach($key as $_key => $_value) {
                $this->set($element, $_key, $_value);
            }

            return true;
        }  

        $this->set_data[$element][$key] = $value;
        return true;
    } /*** END set ***/

    public function post_data ($validate = true) {
    /***
    * @public
    * Return all the post data from the loaded form
    */
		/*
        if ($validate == true && ! $this->validate ()) {
            return false;
        }
		*/
        $post_data  = array ();		

        if (@ count($this->elements) < 1) {
            return false;
        }  

        foreach ($this->elements as $elem) {
            $post_data[$elem] = stripslashes ($this->ci->input->post ($elem));
        }

        return array_merge($post_data, $this->uploaded_files);
    } /*** END post_data ***/

    private function do_uploads() {
        /***
         * @private
         * Helper function for validation
         */
        $error = false;
        $data  = array();
        if (count ($this->elements_upload) > 0) {
            $config['upload_path']   = isset ($this->upload_path) ? $this->upload_path : './upload/';
            $config['remove_spaces'] = true;
            $config['xss_clean']     = true;
            $config['max_size']      = '2048';

            $this->ci->load->library ('upload');

            foreach ($this->elements_upload as $elem) {
                // Reset the configuration
                if (is_array ($this->upload_rules) && isset($this->upload_rules[$elem])) {
                    $config['allowed_types'] = $this->upload_rules[$elem];
                }

                if (isset ($this->rules[$elem]) && $rules = $this->rules[$elem]) {
                    $required = strpos ($rules, 'required') !== false ? true : false;
                }
                else {
                    $required = false;
                }
                    

                $this->ci->upload->initialize ($config);

                if ($this->ci->upload->do_upload ($elem)) {
                    $return = $this->ci->upload->data();
                    $this->uploaded_files[$elem] = $return;
                }
                elseif ($required == true) {
                    $error = true;
                
                    $errors = $this->ci->upload->display_errors();
                    $this->set_error .= $errors;
                }
            }
        }

        return ($error == true ? false : true);
    } /* END do_uploads */

    public function validate ($name = false) {
    /***
     * @public
     * Validates a form based on the rules found in the definition
     */    
        if ($name != false && ! $this->load ($name)) {
            return false;
        }
        elseif (! is_array ($this->rules)) {
            if (count ($this->elements_upload) > 0) {
                return $this->do_uploads();
            }

            return false;
        }	
        $this->ci->load->library ('validation');
        $this->ci->validation->set_rules ($this->rules);

        if ($this->ci->validation->run() && $this->do_uploads())  {
	
             return true;
        }
        else {
            foreach ($this->post_data (false) as $key => $val) {
                // Set default values
                $this->set($key, 'value', $val);
            }   
            
            foreach ($this->ci->validation->_error_array as $error) {
                $this->set_error .= "\t<p>$error</p>\n";
            }  
        }

        return false;
    } /*** END validate ***/  

    private function build_group ($group, $depth = 0, $multiple = "") {  
    /***
     * @private
     * Build a fieldset
     */
        static $first_run;
        static $tabindex;
        
        $tabindex = isset ($tabindex) ? $tabindex : 1;

        // Set the valid attributes and type values
        $valid_attr = array(
            'type', 'maxlength', 'value', 'options',
            'selected', 'checked', 'rows', 'cols', 'size',
            'onclick', 'onmouseover', 'onmouseout', 'onchange'
        );
        $valid_type = array(
            'text', 'textarea', 'password', 'file',
            'dropdown', 'radio', 'checkbox',
            'submit', 'button', 'hidden', 'open', 'recaptcha'
        );
        $tabs = "";
			//repeater("\t", $depth);
		$fieldset_name = str_replace(" ", "_", strtolower($group['__attrs']['name']));
		$html ="";
        $html  .= sprintf ("$tabs".'<h1 class="level%s">%s</h1>', $depth, $group['__attrs']['name']);

        if (isset ($group['__attrs']['text']) && $text = $group['__attrs']['text']) {
            $html .= "$tabs\t<div class=\"level".$depth."\"><p>$text</p></div>\n";
        }

        $html  .= sprintf ('<div id="%s" class="level%s">'."\n", "div_".$fieldset_name, $depth);


        if ($first_run !== false) {
            $errors = '<div class="error message">'."\n\t". $this->set_error ."\n\t</div>";
            $html .= "$tabs". preg_replace("|\n\t+|", "\n$tabs\t", $errors) ."\n";
            $first_run = false;
        }


        $html .= "$tabs\t".'<ul class="layout">'."\n";

		if (isset($group['__attrs']['multiple']) == true) {
			$multiple = $multiple."[0]";						
		}
        foreach ($group as $name => $val) {	
            if ($name == '__attrs') {
                continue;
            }
            elseif ($name == 'fieldset') {
	
                foreach ($val as $_group) {
                   	$html .= "$tabs\t<li>\n". $this->build_group ($_group, $depth+1, $multiple) ."$tabs\t</li>\n";
                }
            }
            else {
                foreach ($val as $index => $def) {
                    foreach ($def as $key => $val) {
                        if ($key == '__attrs') {
                            unset ($def[$key]);
                            continue;
                        }

                        $def[$key] = $val[0];
                    }

                    // Skips defs that have no type attribute
                    if (! isset($def['type']) || ! in_array ($def['type'], $valid_type)) {
                        continue;
                    }

                    // Externally set data is present, merge with stored efinition
                    if (isset ($this->set_data[$name]) && is_array ($this->set_data[$name])) {
                        if ($def['type'] == 'checkbox' && isset ($this->set_data[$name]['value'])) {
                            $this->set_data[$name]['checked'] = (bool)$this->set_data[$name]['value'];
                            unset ($this->set_data[$name]['value']);    
                        }
                        elseif ($def['type'] == 'submit' && isset ($this->set_data[$name]['value'])) {
                            unset ($this->set_data[$name]['value']);    
                        }

                        $def = array_merge($def, $this->set_data[$name]);
                    }

                    // We always want a default value
                    $def['value'] = isset($def['value'])
                        ? $def['value'] : '';

                    // Choose a label
                    $label = isset($def['label'])
                         ? ucwords($def['label'])
                        : ucwords($name);

/*
					// Old code
                    // Create the id and name attributes
                    $idname = $def['type'] != 'hidden'
                        ? sprintf('tabindex="%s" name="%s"', $tabindex++, $name)
                        : sprintf('name="%s"', $name);
*/

					if ($def['type'] == 'hidden') {
						 $idname = sprintf('name="%s"', $name."_".$multiple);
					} elseif (isset($def['multiple']) == true) {
						$field_multiple = $multiple . "[0]";
						$idname = sprintf('name="%s"', $name."_".$field_multiple);
					} else {
                        $idname = sprintf('tabindex="%s" name="%s"', $tabindex++, $name."_".$multiple);
					}
					
					// assign classes to the input based on the rules. this is so jquery can be used for the rules instead
					if(isset($this->rules[$name])) {
						$idname .= "class=\"" . str_replace("|", " ", $this->rules[$name]) . "\"";
					}

                    // Add "*" on required items
                    $label = isset($this->rules[$name]) && in_array('required', explode('|', $this->rules[$name]))
                        ? "$label <em>*</em>"
                        : $label;

                    $row  = "";
                    $row .= $def['type'] != 'hidden'
                        ? "$tabs\t<li>\n"
                        : '';

                    $row .= $def['type'] != 'submit' && $def['type'] != 'hidden'
                        ? "$tabs\t\t<label>$label</label>\n" : '';

                    // Handle non-input elements
                    switch ($def['type']) {
					case 'recaptcha':
						$recaptcha_config = $this->ci->recaptcha->get_html();
						$input = '<script type="text/javascript">' 
						  . 'var RecaptchaOptions = { '
						  . 'theme:"' . $recaptcha_config['theme'] . '",'
						  . 'lang:"' . $recaptcha_config['lang'] . '"'
						  . '};'
						. '</script>'
						. '<script type="text/javascript" src="' . $recaptcha_config['server'] . '/challenge?k=<?= ' . $recaptcha_config['key'] . $recaptcha_config['errorpart'] . '"></script>'
						. '<noscript>'
								. '<iframe src="' . $recaptcha_config['server'] . '/noscript?lang=' . $recaptcha_config['lang'] . '&k=<?= ' . $recaptcha_config['key'] . $recaptcha_config['errorpart'] . '" height="300" width="500" frameborder="0"></iframe><br/>\n'
								. '<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>'
								. '<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>'
						. '</noscript>';
						break;
                    case 'textarea':
                        $input  = "$tabs\t\t<textarea $idname %s>". $def['value'] ."</textarea>\n";
                        unset ($def['type'], $def['value']);
                        break;  
                    case 'dropdown':
                        $def['value'] != false && $def['selected'] = $def['value'];
                        unset ($def['value']);

                        if (isset ($def['options'])) {
                            $options = '<option></option>';
                            foreach ($def['options'] as $_key => $_val) {
                                $_val = is_array ($_val) ? $_val[0] : $_val;

                                $sel = isset ($def['selected']) && $def['selected'] == $_key
                                    ? ' selected="selected"' : '';

                                $options .= "$tabs\t\t\t<option value=\"$_key\"$sel>$_val$tabs</option>\n";
                            }
                            unset ($def['type'], $def['options'], $def['selected']);

                            $input = "$tabs\t\t<select $idname %s >\n$options$tabs\t\t</select>\n";
                        }
                        else {
                            continue(2);
                        }
                        break;
                    case 'hidden':
                        $input = "$tabs\t<input $idname %s />\n";
                        break;
					case 'submit':
                    	$input = "$tabs\t<input type=\"image\" style=\"margin-left: 300px\" src=\"/assets/images/submit.gif\" />\n";
                    	break;					
                    default:
                        $input = "$tabs\t\t<input $idname %s />\n";
                    }     
                    // Parse attributes
                    $attributes = '';
                    foreach ($def as $attr => $val) {
                        if (in_array ($attr, $valid_attr)) {
                            if ($attr == 'checked' && $val != false) {
                                $val = 'checked';
                            }
                            elseif ($attr == 'checked') {
                                continue;
                            }

                            $attributes .= " $attr=\"$val\" ";
                        }
                    }
            
                    $row .= sprintf($input, $attributes);
					
					
					if (isset($def['note']) == true) {
						$row .= "<div class=\"note\">".$def['note']."</div>";
					}					
                     $row .= isset($def['type']) && $def['type'] == 'hidden'
                        ? ''
                        : "$tabs\t</li>\n";

					if (isset($def['multiple']) == true) {
						$multiple_string = str_replace("]", "",str_replace("[", "", str_replace("][", "-", $field_multiple)));
						$row =  "<div id=\"div_".$name."\">".$row."</div>$tabs\t\t<div id=\"div_multiple_".$name."_".$multiple_string."\" class=\"level".$depth."\"></div><img src=\"http://".$_SERVER['SERVER_NAME']."/assets/images/button".$depth.".gif\"  value=\"Another &gt;&gt;\" onClick=\"addField('".$name."', '".$multiple_string."')\" /><input type=\"hidden\" id=\"".$name."_counter_".$multiple_string."\" name=\"".$name."_counter_".$multiple_string."\" value=\"0\">\n";
					}
                    $html .= "$row";
                }
            }             
        }  

        $html .= "$tabs\t".'</ul>'."\n";
		if (isset($group['__attrs']['multiple']) == true) {
			$multiple_string = str_replace("]", "",str_replace("[", "", str_replace("][", "-", $multiple)));
			$html .= "$tabs".'</div>'."<div id=\"div_multiple_".$fieldset_name."_".$multiple_string."\" class=\"level".$depth."\"></div><img src=\"http://".$_SERVER['SERVER_NAME']."/assets/images/tab".$depth.".gif\" value=\"Another &gt;&gt;\" class=\"more\" onClick=\"addField('".$fieldset_name."', '".$multiple_string."')\" /><input type=\"hidden\" id=\"".$fieldset_name."_counter_".$multiple_string."\"  name=\"".$fieldset_name."_counter_".$multiple_string."\" value=\"0\">\n";
		} else {
        	$html .= "$tabs".'</div>'."\n";			
		}


        return $html;
    } /* END build_group */

    public function build ($name = false) {
    /***
     * @public
     * Convert a form definition into an XHTML form
     */
        if ($name != false && ! $this->load ($name)) {
            return false;
        }
        elseif (! is_array ($this->data)) {
            return false;
        }  
    
        $this->ci->load->helper('string');

        $form_type = $this->has_upload == true
            ? ' enctype="multipart/form-data"'
            : '';

        $out =& $this->output;
        $out  = '';
        $out .= sprintf("".'<form action="%s" id="%s" method="post"%s>'."\n",
            site_url($this->action),
            strtolower(preg_replace('|\W|', '_', $this->data['__attrs']['name'])),
            $form_type
        );

        foreach ($this->data['fieldset'] as $group) {
            $out .= $this->build_group($group);
        }

        $out .= "</form>\n";

        $this->output = $out;
        return $out;   
    } /*** END build ***/

}

?>