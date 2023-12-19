<?php
/*
 * WordPress Plugin Development Helpers - Options
 * @link: http://salamzadeh.net/plugins/urlyar
 * @author Sasan Salamzadeh
 * @version 1.0
 */

/*
    Class Description
        This is a helper class for WordPress options database.
        Minimizes polling to the WP Database
        Meant to be used in WordPress plugin Development.
*/

if (!class_exists('ss_options')) :
    class ss_options
    {

        /*
         *****************************************
         *
         *	Class Variables
         *
         *****************************************
         */


        private $default_details = array(), //version and status
            $plugin_details = array('version' => NULL),

            $option_name = '', //database option name

            $options = array(), //working options / saved options
            $default_options = array(), //author defaults

            $master_list = array();


        /*
         *****************************************
         *
         *	Constructors
         *
         *****************************************
         */

        //php 5.3.3
        public function __construct($name, $version, $status = 'stable')
        {
            $this->ss_options($name, $version, $status);
        }

        //backward compatibility
        public function ss_options($name, $version, $status = 'stable')
        {
            $this->default_details['version'] = $version;
            $this->default_details['status'] = $status;
            $this->option_name = $name;
        }



        /*
         *****************************************
         *
         *	Initialization
         *
         *****************************************
         */

        /*
         *****************************************
         * Create Master List
		 * (Before Saving)
         *****************************************
         */

        private function init_master()
        {
            $this->master_list = $this->default_details;
            $this->master_list['options'] = $this->options;
        }

        /*
         *****************************************
         *
         *	Helpers
         *
         *****************************************
         */


        /*
         *****************************************
         * Refresh List from Database
         *****************************************
         */
        private function refresh_vars()
        {
            $saved = get_option($this->option_name);

            if ($saved) {
                $this->options = $saved['options'];
                $this->plugin_details['version'] = $saved['version'];
                $this->plugin_details['status'] = $saved['status'];
            }

        }


        /*
         *****************************************
         * Saving List to Database
         *****************************************
         */

        private function save_options()
        {
            //create save array
            $this->init_master();

            //Update WordPress DB
            update_option($this->option_name, $this->master_list);

            //update global variable
            $this->refresh_vars();
        }

        /*
         *****************************************
         *
         *	Set and Retrival
         *
         *****************************************
         */


        /*
         *****************************************
         * Refresh and Return Saved options
         *****************************************
         */

        public function saved_options()
        {
            $this->refresh_vars();
            return $this->options;
        }


        /*
         *****************************************
         * Public refresh 
		 * Use this instead of refresh_vars.
		 * This is done for possible future ext.
         *****************************************
         */

        public function refresh()
        {
            $this->refresh_vars();
        }


        /*
         *****************************************
         * Save to DB
         *****************************************
         */

        public function save()
        {
            $this->save_options();
        }


        /*
         *****************************************
         * Return a key value
		 * 
		 * Option to refresh from db
		 * Only supports first level keys
         *****************************************
         */

        public function get($key, $refresh = true)
        {
            if ($refresh) {
                $this->refresh_vars();
            }

            $value = $this->options[$key]??"";
            return ($value) ? $value : '';
        }


        /*
         *****************************************
         * Set a key value
		 * 
		 * Option to save to / refresh from db
		 * Only supports first level keys
         *****************************************
         */

        public function set($key, $value, $save = false, $refresh = false)
        {

            if ($refresh) {
                //update global variable
                $this->refresh_vars();
            }

            $this->options[$key] = $value;

            if ($save) {
                $this->save_options();
            }

            return true;
        }

        /*
         *****************************************
         *
         *	Installation and Migration
         *
         *****************************************
         */


        /*
         *****************************************
         * Merging Two Array of Options
		 * Check for changes and 
		 * determine of save needed 
		 *
		 * 2nd Array defaulted to "default_options
         *****************************************
         */

        private function options_merge($compare, $base = NULL, $override = false)
        {
            //returns true when both sides are different and a merge is done
            //returns false otherwise..
            //allows overrides

            if (empty($base)) {
                $base = $this->default_options;
            }

            //Override defaults with saved
            if (!empty($compare)) {
                foreach ($compare as $key => $option) {
                    $base[$key] = $option;
                }
            }

            //Check for changes
            if ($compare != $base || $override == true) {
                $this->options = $base;
                return true;
            }

            return false;
        }


        /*
         *****************************************
         * Setting Default Array of Options
		 * 
		 * If item is an array, import array
		 * else just set an item value
         *****************************************
         */

        public function set_default($item, $value = '')
        {

            //reset default
            $this->default_options = array();

            //check if first item array
            if (is_array($item)) {
                foreach ($item as $key => $value) {
                    $this->default_options[$key] = $value;
                }

                //setting of individual items
            } else {
                $this->default_options[$item] = $value;
            }

        }


        /*
         *****************************************
         * Install Options
         *****************************************
         */

        public function install_options()
        {

            //ensure variables are updated
            $this->refresh_vars();

            if ($this->options_merge($this->options)) {
                $this->save_options();
            }

        }


        /*
         *****************************************
         * Option Migration
         *****************************************
         */

        public function migrate_options($remove_key = NULL)
        {

            //ensure variables are updated
            $this->refresh_vars();

            if (empty($this->plugin_details['version']) ||
                ($this->plugin_details['version'] < $this->default_details['version'])
            ) {
                $this->options = get_option($this->option_name);

                if ($remove_key) {
                    unset($this->options[$remove_key]);
                }

                if ($this->options_merge($this->options)) {
                    $this->save_options();
                }

            }

        }










        /*
         *****************************************
         *
         *	Misc
         *
         *****************************************
         */


        /*
         *****************************************
         * Get Version
		 * Either Saved DB or Plugin Default
         *****************************************
         */

        public function get_version($default = false)
        {
            if ($default) {
                return $this->default_details['version'];
            } else {
                return $this->plugin_details['version'];
            }
        }


        /*
         *****************************************
         * Reset Options to plugin defaults
		 *
		 * Option not to save first
		 * Allows for modification after reset
         *****************************************
         */

        public function reset_default($save = true)
        {
            $this->options = $this->default_options;

            if ($save) {
                $this->save_options();

                //update global variable
                $this->refresh_vars();
            }

        }


        /*
         *****************************************
         * Value comparators
		 * plugin detail or option
         *****************************************
         */

        public function option_comparator($name, $value, $default = false)
        {
            if ($default) {
                return ($this->default_options[$name] == $value) ? true : false;
            } else {
                return ($this->options[$name] == $value) ? true : false;
            }
        }


        public function detail_comparator($name, $value, $default = false)
        {
            if ($default) {
                return ($this->default_details[$name] == $value) ? true : false;
            } else {
                return ($this->plugin_details[$name] == $value) ? true : false;
            }
        }


        /*
         *****************************************
         * Bulk saving all options 
		 *
		 * Not Recommended
		 * (Not Complete yet)
         *****************************************
         */

        public function bulk_save($item)
        {
            if ($this->options_merge($item)) {
                $this->save_options();
                return true;

            } else {
                return false;
            }
        }


//end class
    }
endif;

?>
