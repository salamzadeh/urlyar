<?php
/*
 * class.ss_shortener - URL Shortener Plugin Component
 * @link: http://salamzadeh.net/plugins/urlyar
 * @author Sasan Salamzadeh
 * @version 1.0
 */


/*
    Class Description
		This is the main shortener driver for the URL Shortener plugin
        It dictates the supported services and functions of the URL shortener WordPress Plugin
        For usage, please refer to http://salamzadeh.net/plugins/urlyar/
        
*/


/*
 *****************************************
 *
 *	Dependencies
 *
 *****************************************
 */

/*

if ( !class_exists('urlyar_urlyar') ) :
	include( dirname(__FILE__) . '/ss_shortener/urlyar.urlyar.php' );
endif;

*/


/*
 *****************************************
 *
 *	Main Class Declaration
 *
 *****************************************
 */

if (!class_exists('ss_shortener')) :
    class ss_shortener
    {


        /*
         *****************************************
         *
         *	Class Variables
         *
         *****************************************
         */


        private $service_keys = array();
        private $service_details = array();

        private $supported,                    //combined services
            $status,                    //class status (beta, stable, dev)
            $service = '',                //service name
            $key = '',                    //keys
            $user = '',                    //user / identity
            $generic = '',                //generic field to pass
            $shortener = '';            //shortener class

        private $shortener_modules = array();    //shortener modules


        /*
         *****************************************
         *
         *	Constructors
         *
         *****************************************
         */

        //php 5.3.3
        public function __construct($status = 'dev')
        {
            $this->ss_shortener($status);
        }

        //backward compatibility
        public function ss_shortener($status = 'dev')
        {
            $this->status = $status;
            $this->init_modules();
            $this->supported = array_combine($this->service_keys, $this->service_details);
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
		 * Load and Init modules
         *****************************************
         */
        private function init_modules()
        {
            $dirpath = dirname(__FILE__) . '/ss_shortener';
            $this->load_modules($dirpath);

            foreach ($this->shortener_modules as $module) {
                include($module);
                $mod = $this->is_module($module, true);
                $this->add_support($mod['classname']);
            }
        }


        /*
         *****************************************
		 * Check if PHP file is a module.
		 * Option to return $module information.
         *****************************************
         */
        public function is_module($module, $info = false)
        {
            $headers = array(
                'name' => 'URLYar Name',
                'description' => 'URLYar Description',
                'version' => 'version',
                'classname' => 'classname',
            );

            $mod = get_file_data($module, $headers);

            if (empty($mod['name']))
                return false;

            return ($info) ? $mod : true;

        }


        /*
         *****************************************
		 * Scan a list of component classes
		 * Put list into array
		 * component classes
         *****************************************
         */
        private function load_modules($filepath)
        {
            $filepath = untrailingslashit($filepath);

            if (!$dir = opendir($filepath)) {
                return $files;
            }

            while (false !== ($file = readdir($dir))) {
                if ('.' == substr($file, 0, 1) || '.php' != substr($file, -4)) {
                    continue;
                }

                $full_file = $filepath . '/' . $file;

                if (!is_file($full_file) || !$this->is_module($full_file)) {
                    continue;
                }

                array_push($this->shortener_modules, $full_file);
            }
            closedir($dir);
        }


        /*
         *****************************************
		 * Load a list of supported services from
		 * component classes
         *****************************************
         */
        private function add_support($classname)
        {
            $list = new $classname();

            foreach ($list->api_list() as $keys => $values) {

                //name, type, sticky.
                //name, class, type
                $detail = array($values[0], $classname, $values[1]);

                if (empty($values[2])) {
                    array_push($this->service_keys, $keys);
                    array_push($this->service_details, $detail);
                } else {
                    array_unshift($this->service_keys, $keys);
                    array_unshift($this->service_details, $detail);
                }
            }

        }


        /*
         *****************************************
		 * Component method... for use in another function
		 * This loads the correct class or file into the common shortener variable.
		 * Methods in different classes are the same.
         *****************************************
         */
        private function load_shortener()
        {

            $data = 'OK';
            $this->shortener = '';

            if (!empty($this->service) && in_array($this->service, $this->service_keys)) {

                $opt = $this->supported[$this->service];

                switch ($opt[2]) {
                    case '5':
                        if (empty($this->key)) {
                            $data = 'Error in Generating Shortlink (Invalid or No Key)';
                        }
                        break;
                    case '6':
                        if (empty($this->key) || empty($this->user)) {
                            $data = 'Error in Generating Shortlink (Invalid or No Username/Key)';
                        }
                        break;
                    default:
                        break;
                }

                if ($data == 'OK') {

                    //assign shortener
                    $this->shortener = new $opt[1]($this->service, $this->status);

                    //set configurations for object
                    $this->shortener->config($this->key, $this->user, $this->generic);
                }

            } else { //end if in_array
                $data = 'Service not specified or not supported';
            }

            return $data;
        }//end load adapter


        /*
         *****************************************
		 * Public initialization call
         *****************************************
         */
        public function init_shortener()
        {
            return $this->load_shortener();
        }


        /*
         *****************************************
		 * Public link generation call
         *****************************************
         */
        public function generate($url)
        {

            $data = NULL;

            if (!empty($this->shortener)) {

                if (!empty($url)) {
                    //check for empty request
                    $data = $this->shortener->generate($url);

                } else {
                    $data = ''; //'No URL specified';
                }

            } else {
                $data = ''; //'Shortener not initialized';
            }

            return $data;
        }


        /*
         *****************************************
         *
         *	Configurations
         *
         *****************************************
         */


        public function config($service = '', $key = '', $user = '', $generic = '')
        {
            $this->service = $service;
            $this->key = $key;
            $this->user = $user;
            $this->generic = $generic;
        }

        public function set_user($value)
        {
            $this->user = $value;
        }

        public function set_key($value)
        {
            $this->key = $value;
        }

        public function set_service($value)
        {
            $this->service = $value;
        }

        public function set_generic($value)
        {
            $this->generic = $value;
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
		 * Public listing of services, users, and keys.
         *****************************************
         */
        public function service_list($format = 'keys')
        {

            $data = array();

            switch ($format) {

                case 'authuser':
                    foreach ($this->supported as $keys => $values) {
                        if ($values[2] == 1 || $values[2] == 3 || $values[2] == 4 || $values[2] == 6) {
                            array_push($data, $keys);
                        }
                    }
                    break;
                case 'requser':
                    foreach ($this->supported as $keys => $values) {
                        if ($values[2] == 4 || $values[2] == 6) {
                            array_push($data, $keys);
                        }
                    }
                    break;
                case 'authkey':
                    foreach ($this->supported as $keys => $values) {
                        if ($values[2] == 2 || $values[2] == 3 || $values[2] == 5 || $values[2] == 6) {
                            array_push($data, $keys);
                        }
                    }
                    break;
                case 'reqkey':
                    foreach ($this->supported as $keys => $values) {
                        if ($values[2] == 5 || $values[2] == 6) {
                            array_push($data, $keys);
                        }
                    }
                    break;
                case 'detailed':
                    $data = $this->supported;
                    break;
                case 'keys':
                default:
                    $data = $this->service_keys;
                    break;
            }
            return $data;
        }


        /*
         *****************************************
		 * Public check status of shortener
         *****************************************
         */
        public function load_status()
        {
            return (empty($this->shortener)) ? false : true;
        }


        /*
         *****************************************
		 * Public list modules
         *****************************************
         */
        public function get_modules()
        {
            $list = array();

            foreach ($this->shortener_modules as $module) {
                $mod = $this->is_module($module, true);
                $mod['path'] = $module;
                array_push($list, $mod);
            }

            return $list;
        }


//end class  
    }
endif;

?>
