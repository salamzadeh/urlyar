<?php
/*
 * URLYarLib Name:		urlyar.interface - JZ Shortener Component (URLYar) Interface
 * URLYarLib Description: This is the interface declarion for component classes of the main ss_shortener
 * classname: 			urlyar_interface
 * version: 			1.1.0
 * link: 				http://urlyar.ir
 * author: 				Sasan Salamzadeh <me@salamzadeh.net>
 */


if (!interface_exists('urlyar_interface')) :
    interface urlyar_interface
    {


        /*
         *****************************************
         *	Methods
         *****************************************
         */


        function generate($url);                    //main generator

        function config($key, $user, $generic);        //config call to parent

        function api_list();                        //calls parent api_list function with parameter

        function set_service($service);            //loads $api_config and service into parent class


        /*
         *****************************************
         *	Variables
         *****************************************
         */

        /*
            private $ua_string
                Stores the replacement UA String.
            ----------------------------------------------------------------


            private $api_config = array()
            ----------------------------------------------------------------
            - name
                Display name

            - endpoint
                URL to send request to

            - format
                JSON, XML, TXT, or any special formats

            - ua (TRUE/FALSE)
                Override UA strings

            - override
                Override WP_HTTP. i.e. Use normal methods of request

            - method (POST / GET )

            - sticky
                Append to front of generated API listing

            - type
                NULL = disabled / suspended

                0 = no auth/key required

                1 = authuser
                2 = authkey
                3 = authuser & key
                4 = requser
                5 = reqkey
                6 = requser & key

                101 - 200 = Generic / Special

                sub = child of a complex type
            ----------------------------------------------------------------

        */


//end interface
    }
endif;
?>
