<?php
/*
 * URLYarLib Name:		urlyar.shared - Shared JZ Shortener Components
 * URLYarLib Description: This class contains the request gateway for shortening
 * classname: 			urlyar_interface
 * version: 			1.1.0
 * link: 				https://salamzadeh.net
 * author: 				Sasan Salamzadeh <me@salamzadeh.net>
 */

/*
	Full Description:
		This class contains the request gateway for shortening.. 
		Where possible, it makes use of the WP_HTTP class, but will fall back on basic methods 
		when the WP_HTTP class is not available.

		It's meant to be extended / inherited, but can be used as is.
*/




if ( !class_exists('urlyar_shared') ) :
	class urlyar_shared {
        




/*
 *****************************************
 *
 *	Class Variables
 *
 *****************************************
 */


		protected 	$status 	= 'stable', //stable, beta, dev, trial?
					$user 		= '', 		//Username if required
				  	$key 		= '', 		//API key if required
				  	$url 		= '',		//URL to shorten
					$loaded_api	= '',		//API details and other definitions
					$api_name	= '',		//Name of service	
					$generic 	= '';		//generic fields (Optional)
        



/*
 *****************************************
 *
 *	Constructors
 *
 *****************************************
 */

        //php 5.3.3
        public function __construct() {
            $this->urlyar_shared($status);
        }
       
        //backward compatibility
        public function urlyar_shared($status){
			$this->status = $status;         
        }
      



  


/*
 *****************************************
 *
 *	Configuration
 *
 *****************************************
 */

		public function config($key, $user, $loaded_api, $generic = ''){
			$this->user = $user;
			$this->key = $key;
			$this->loaded_api = $loaded_api;
			$this->generic = $generic;
		}








/*
 *****************************************
 *
 *	Data Processing
 *
 *****************************************
 */



        /*
         *****************************************
         * JSON Parser
		 * Use PHP built in fx where available
         *****************************************
         */
        public function json_process($item){
            if ( class_exists('Services_JSON') ){
                $json = new Services_JSON();
                $result = $json->decode($item);
            }else{
                $result = json_decode($item, false);
            }
            return $result;
        }






        /*
         *****************************************
         * XML Parser
         *****************************************
         */

		public function xml_process($item){
			if ( class_exists('SimpleXMLElement') ){
				$result = new SimpleXMLElement($item);	
			}
			return $result;
		}





/*
 *****************************************
 *
 *	Data Request
 *
 *****************************************
 */


	    /*
         *****************************************
         * "Normal" Gateway
		 *
		 * By default, uses CURL for POST, GET
		 * Fallback on file_get_contents for GET
         *****************************************
         */

        protected function request_ALT($request_url, $post_opt = array(), $method = 'GET', $fields = '') {
			
			$data = NULL;
			
			if ( function_exists('curl_init') ) {
				if ( !empty($post_opt['useragent']) ){ ini_set($useragent); }

				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, $request_url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, false);	

				if ( !empty($post_opt['useragent']) ){ curl_setopt($ch, CURLOPT_USERAGENT, $post_opt['useragent']); }

				if ($method == 'POST'){
					
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);	
				}      


				if ( !empty($post_opt['httpheader']) ){        		
					curl_setopt($ch, CURLOPT_HTTPHEADER, $post_opt['httpheader']);
				}

				$data = curl_exec($ch);
				curl_close($ch);

				if ( !empty($post_opt['useragent']) ){ ini_restore('user_agent'); }


			} elseif($method = 'GET') {
				$data = file_get_contents($request_url);
			}

		 	return $data;
        }





        /*
         *****************************************
         * WordPress Specific Gateway
		 * Uses WP_HTTP class methods
         *****************************************
         */
		protected function request_WP($request_url, $post_opt, $method, $body){
			$data = NULL;

	
			if ( class_exists('WP_Http') ){
				$request = new WP_Http;
				$details = array();


				if ($method == 'GET'){
					$result = $request->request( $request_url );
				}else{	
					$details['method'] = $method;
					$details['body'] = $body;

					if ( empty($post_opt['useragent']) ){
						$details['user-agent'] = $useragent;
					}

					$result = $request->request( $request_url, $details ); 
				}


				if($result['body']){
					$data = $result['body'];
				}
				
			}

			return $data;

		}




 	    /*
         *****************************************
         * Overall Gateway
		 *
		 * Switches between WordPress or Universal 
		 * where appropriate
		 *
         *****************************************
         */

        public function request_gateway($request_url, $override = false, $method='POST', $post_opt = array(), $body = ''){
            $data = '';

			//check status
			switch ($this->status){	
				case 'trial'	:
				case 'stable'	:

				    //check for WP Http
				    if ( class_exists('WP_Http') && !$override ){

				       $data = $this->request_WP( $request_url, $post_opt, $method, $body);

				    } else {
					//use backup methods
					
						$data = $this->request_ALT($request_url, $post_opt, $method, $body);
						
					} //end if WP_HTTP
					break;


				case 'beta'	:
				case 'dev'	:
					$data = '[ '.$request_url.' ]-[ '.$body.' ]';
					break;

				default: 
					break;


			} //end switch



			return $data;
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
         * URL Encoding
         *****************************************
         */

        public function cleanurl(){
            return urlencode($this->url);
        }





        /*
         *****************************************
         * Supported API List generation
         *****************************************
         */

		public function api_list($list){
			$compiled = array();

			foreach ($list as $keys => $values){
				if ( is_int($values['type']) ){
					$compiled[$keys] = array($values['name'], $values['type'], $values['sticky']);
				}
			}
			return $compiled;
		}



        /*
         *****************************************
         * Setting service
         *****************************************
         */
		public function set_service($service, $api_config){
			$this->api_name = $service;
			$this->loaded_api = $api_config;
		}



//end class  
    }
endif;
 
?>
