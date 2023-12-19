<?php
/*
 * URLYar Name: URLYar Support
 * URLYar Description: This component adds support for URLYar's URL Shortening Service
 * classname: urlyar_urlyar
 * version: 1.1.0
 * link: https://salamzadeh.net
 * author: Sasan Salamzadeh
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 *****************************************
 *
 *	Dependencies
 *
 *****************************************
 */
if (!class_exists('urlyar_shared')) :
    include(dirname(__FILE__) . '/lib/urlyar.shared.php');
endif;


/*
 *****************************************
 *
 *	Main Class Declaration
 *
 *****************************************
 */

if (!class_exists('urlyar_urlyar')) :
    class urlyar_urlyar extends urlyar_shared
    {


        /*
         *****************************************
         *
         *	Class Variables
         *
         *****************************************
         */

        private $api_config = array(
            'urlyar' => array(
                'name' => 'URLYar',
                'endpoint' => 'http://urlyar.ir/api/',
                'format' => 'json',
                'ua' => FALSE,
                'override' => TRUE,
                'method' => 'GET',
                'type' => 2,
                'sticky' => TRUE,
            ),

        );


        /*
         *****************************************
         *
         *	Constructors
         *
         *****************************************
         */


        //php 5.3.3
        public function __construct($service = NULL, $status = 'dev')
        {
            $this->urlyar_urlyar($service, $status);
        }

        //backward compatibility
        public function urlyar_urlyar($service = NULL, $status = 'dev')
        {
            $this->api_name = 'urlyar';
            $this->loaded_api = $this->api_config['urlyar'];
            $this->status = $status;
        }




        /*
         *****************************************
         *
         *	Methods
         *
         *****************************************
         */


        /*
         *****************************************
         * 	Config Requirements
         *****************************************
         */
        public function set_service($service,$api_config = '')
        {
            parent::set_service('urlyar', $this->api_config['urlyar']);
        }

        public function config($key = '', $user = '',$loaded_api='', $generic = '')
        {
            $loaded_api = $this->api_config['urlyar'];
            parent::config($key, $user, $loaded_api, $generic);
        }

        public function api_list($list='')
        {
            return parent::api_list($this->api_config);
        }


        /*
         *****************************************
         * 	Main Generator 
         *****************************************
         */
        public function generate($url)
        {
            $this->url = $url;

            if ($this->url) {

                $request_url = $this->loaded_api['endpoint'] ."url/add";
				$body = json_encode(["url" => $this->url]);
                if ($this->key) {
					$post_opt['httpheader'] = array('Content-Type: application/json',
					'Authorization: Bearer '.$this->key);
                    
                } else {
					echo __("Please use a valid API Key");
                    return "";
                }

                if ($this->loaded_api['ua']) {
                    $post_opt['useragent'] = $ua_string;
                }
                $result = parent::request_gateway($request_url, TRUE, "POST", $post_opt, $body);
                //die($result);
                if ($result) {
                    $result = parent::json_process($result);
					if($result->error == 1){
						echo $result->message;
						$result = '';
					}
                    else
						$result = $result->shorturl;
                }

                //remove url request
                $this->url = '';

                return $result;
            }
        }


//end class
    }
endif;

?>
