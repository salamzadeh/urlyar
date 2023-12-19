<?php
/*
Plugin Name: URLYar
Plugin URI: https://salamzadeh.net/plugins/urlyar
Description: This plugin provides integration of URL Shorteners (e.g. urlyar.ir, trew.ir, fsda.ir, saqw.ir and what you want).
Author: Sasan Salamzadeh
Author URI: http://www.salamzadeh.net
Text Domain: url-shortener
Version: 1.1.0
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('URLYAR_URL_SHORTENER_VERSION', '1.1.0');
define('URLYAR_URL_SHORTENER_STATUS', 'stable');


/*
 *****************************************
 *
 *	Dependencies
 *
 *****************************************
 */

if (!class_exists('ss_options')) :
    include(dirname(__FILE__) . '/include/class.ss_options.php');
endif;

if (!class_exists('ss_shortener')) :
    include(dirname(__FILE__) . '/include/class.ss_shortener.php');
endif;


/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function urlyar_load_textdomain() {
    load_plugin_textdomain( 'url-shortener', null, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'urlyar_load_textdomain' );


/*
 *****************************************
 *
 *	Main Class Declaration
 *
 *****************************************
 */

if (!class_exists('URLYAR_URL_Shortener')) :
    class URLYAR_URL_Shortener
    {


        /*
         *****************************************
         *
         *	Class Variables
         *
         *****************************************
         */


        private $plugin_version,
            $plugin_status,
            $plugin_option,            //(global) plugin options
            $plugin_url,
            $plugin_page,            //options page
            $shortener,                //(global) shared shortener assignment
            $shortener_modules;        //available modules for shortener


        /*
         *****************************************
         *
         *	Constructors
         *
         *****************************************
         */


        //php 5.3.3
        function __construct($version = '0.0', $status = 'dev')
        {
            $this->URLYAR_URL_Shortener($version, $status);
        }

        //backward compatibility
        function URLYAR_URL_Shortener($version = '0.0', $status = 'dev')
        {
            $this->plugin_version = $version;
            $this->plugin_status = $status;

            $this->plugin_option = new ss_options('urlyar_urlfx', $this->plugin_version, $this->plugin_status);
            $this->shortener = new ss_shortener($this->plugin_status);
            $this->shortener_modules = $this->shortener->get_modules();
        }




        /*
         *****************************************
         *
         *	Plugin Activation Calls
         *
         *****************************************
         */

        //hooks and loaders
        public function activate_shortener()
        {
            $this->plugin_url = defined('WP_PLUGIN_URL') ? WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)) : trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));

            if ($this->plugin_option->get('urlserviceenable') == 'yes') {
                //FX registration mirrors WP stats plugin
                if (!function_exists('wp_get_shortlink')) {
                    //Register these only for WP < 3.0.
                    add_action('wp_head', array(&$this, 'urlyar_shortlink_wp_head'));
                    add_action('wp', array(&$this, 'urlyar_shortlink_header'));


                    //edit post-page button
                    if ($this->check_wp_version(2.9)) {
                        add_filter('get_sample_permalink_html', array(&$this, 'urlyar_get_shortlink_html'), 10, 2);
                    }


                    //compatibility - remove wp.me
                    remove_action('wp_head', 'wpme_shortlink_wp_head');
                    remove_action('wp', 'wpme_shortlink_header');
                    remove_filter('get_sample_permalink_html', 'wpme_get_shortlink_html', 10, 2);


                } else {
                    //Register a shortlink handler for WP >= 3.0.
                    add_filter('get_shortlink', array(&$this, 'pub_gateway'), 10, 4);
                }


                //Future to Present
                add_action('future_to_publish', array(&$this, 'transition_get_shortlink'), 10, 1);
            }

            //Options
            add_action('admin_menu', array(&$this, 'plugin_menu'));


            //Tables
            add_filter('post_row_actions', array(&$this, 'table_hover_link'), 10, 2);
            add_filter('page_row_actions', array(&$this, 'table_hover_link'), 10, 2);
            add_action('admin_head', array(&$this, 'urlyar_urlshortener_adminhead'));
            add_action('load-edit.php', array(&$this, 'urlyar_urlshortener_edit_head'));
            add_action('load-edit-pages.php', array(&$this, 'urlyar_urlshortener_edit_head'));


            //AJAX Calls
            add_action('wp_ajax_urlshortener_act', array(&$this, 'urlyar_urlshortener_ajaxcallback'));


            //Nice IDs
            if ($this->plugin_option->get('niceid') == 'yes') {
                add_filter('template_redirect', array(&$this, 'template_redirect'), 10, 2);
            }


            //Shortcode
            if ($this->plugin_option->get('url_shortcode') != 'disable') {
                add_shortcode('urlyar_shortlink', array(&$this, 'shortcode_support'));
            }

            //Append Text
            add_filter('the_content', array(&$this, 'urlyar_filter_append_post'));

            //Append QR Code
            add_filter('the_content', array(&$this, 'urlyar_filter_append_post_qr'));


        }


        private function check_wp_version($version, $operator = ">=")
        {
            global $wp_version;
            return version_compare($wp_version, $version, $operator);
        }


        /*
         *****************************************
         * Check if shortener loaded. 
         * Attempt to load it otherwise
         * Return false when all fails.
         *****************************************
         */

        private function shortener_loader()
        {

            if (!$this->shortener->load_status()) {

                $service = $this->plugin_option->get('urlservice');

                $this->shortener->set_user($this->plugin_option->get('apiuser_' . $service));
                $this->shortener->set_key($this->plugin_option->get('apikey_' . $service));
                $this->shortener->set_generic($this->plugin_option->get('generic_' . $service));
                $this->shortener->set_service($service);

                $result = $this->shortener->init_shortener();

                return ($result == 'OK') ? true : false;
            }

            return true;
        }


        /*
         *****************************************
         * Plugin Installer
         * Includes setting of default values
         * Migration Utility for v3.0 Series
         *****************************************
         */

        public function install()
        {
            $defaults = array(
                'urlserviceenable' => 'no',
                'urlservice' => '',
                'useslug' => 'no',
                'niceid' => 'no',
                'niceid_prefix' => '/',
                'appendurl' => array(
                    'home ' => 'no',
                    'single' => 'no',
                    'page' => 'no',
                    'product' => 'no',
                    'text' => 'Short URL:',
                ),
                'appendqr' => array(
                    'home' => 'no',
                    'single' => 'no',
                    'page' => 'no',
                    'product' => 'no',
                    'text' => '',
                    'provider' => 'urlyar'
                ),
                'url_shortcode' => 'disable',
            );

            $this->plugin_option->set_default($defaults);

            $this->plugin_option->migrate_options('about_plugin'); //check migration requirement
            $this->plugin_option->install_options();
        }







        /*
         *****************************************
         *
         *	Shortlink Generation Main Functions
         *
         *****************************************
         */


        /*
         *****************************************
         * Automatic shortener
		 *
         * Activated during Post Publishing or Post Viewing
         *****************************************
         */

        private function pub_get_shortlink($id = 0, $context = 'post', $allow_slugs = true, $transition = false)
        {
            include(dirname(__FILE__) . '/include/pub_get_shortlink.php');
            return $shortlink;
        }


        /*
         *****************************************
         * On demand shortener
		 *
         * For calls directly from the public.
         * Shortener allocation must be different from global shortener
		 * Fallback on default setting if service undefined
         *****************************************
         */

        public function od_get_shortlink($url = '', $service = '', $key = '', $user = '')
        {
            $shortlink = NULL;

            if (!empty($url)) {
                $od_shortener = new ss_shortener($this->plugin_status);

                if (empty($service)) {

                    $service = $this->plugin_option->get('urlservice');
                    $od_shortener->set_user($this->plugin_option->get('apiuser_' . $service));
                    $od_shortener->set_key($this->plugin_option->get('apikey_' . $service));
                    $od_shortener->set_generic($this->plugin_option->get('generic_' . $service));
                    $od_shortener->set_service($service);

                } else {

                    $od_shortener->config($service, $key, $user);

                }

                $status = $od_shortener->init_shortener();

                $shortlink = ($status == 'OK') ? $od_shortener->generate($url) : NULL;
            }

            return $shortlink;
        }


        /*
         *****************************************
         * Status hook for future posts
         * Hook to ensure generation only during publishing
         *****************************************
         */
        public function transition_get_shortlink($post)
        {
            $shortlink = $this->pub_get_shortlink($post->ID, 'post', true, true);
        }






        /*
         *****************************************
         *
         *	Publishing CALLBACKS
         *
         *****************************************
         */


        //WP >= 3.0

        public function pub_gateway($shortlink, $id, $context, $allow_slugs)
        {
            $shortlink = $this->pub_get_shortlink($id, $context, $allow_slugs);
            return $shortlink;
        }

        //WP < 3.0 
        public function urlyar_shortlink_wp_head()
        {
            global $wp_query;
            $shortlink = $this->pub_get_shortlink(0, 'query');
            if ($shortlink) {
                echo '<link rel="shortlink" href="' . $shortlink . '" />';
            }
        }

        public function urlyar_shortlink_header()
        {
            global $wp_query;
            if (headers_sent())
                return;
            $shortlink = $this->pub_get_shortlink(0, 'query');
            header('Link: <' . $shortlink . '>; rel=shortlink');
        }

        public function urlyar_get_shortlink_html($html, $post_id)
        {
            $shortlink = $this->pub_get_shortlink($post_id);
            if ($shortlink) {
                $html .= '<input id="shortlink" type="hidden" value="' . $shortlink . '" /><a href="' . $shortlink . '" class="button" onclick="prompt(&#39;URL:&#39;, jQuery(\'#shortlink\').val()); return false;">' . __('Get Shortlink') . '</a>';
            }
            return $html;
        }








        /*
         *****************************************
         *
         *	WordPress Admin Pages
         *
         *****************************************
         */

        /*
         *****************************************
         * Plugin Options Page
         *****************************************
         */
        public function plugin_menu()
        {
            if (!is_admin())
                return;

            $this->plugin_page = add_options_page(__('URLYar', 'url-shortener'), __('URLYar', 'url-shortener'), 'manage_options', 'shorturl', array(&$this, 'options_page'));

            add_action('load-' . $this->plugin_page, array(&$this, 'options_style_scripts'));

            include(dirname(__FILE__) . '/include/options_page_help.php');
            add_contextual_help($this->plugin_page, $help_text);

        }

        public function options_page()
        {
            if (!is_admin())
                return;

            $action_url = $_SERVER['REQUEST_URI'];
            include(dirname(__FILE__) . '/include/options_page.php');
        }


        public function options_style_scripts()
        {
            wp_enqueue_style('url_shortener_options_css', $this->plugin_url . '/assets/css/options_page.css');
        }


        /*
         *****************************************
         * Table Pages
         *****************************************
         */
        public function table_hover_link($actions, $post)
        {
            $shortlink = $this->pub_get_shortlink($post->ID);
            if ($shortlink) {
                $actions['shortlink'] = '<a href="' . $shortlink . '" onclick="prompt(&#39;URL:&#39;, jQuery(this).attr(\'href\')); return false;">' . __('Get Shortlink') . '</a>';
            }
            return $actions;
        }

        public function urlyar_urlshortener_adminhead()
        {
            ?>
            <script type="text/javascript">
                var aaurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                var nonce = '<?php echo wp_create_nonce('urlshortener_ajax');?>';
            </script>
            <?php
        }

        public function urlyar_urlshortener_edit_head()
        {
            wp_enqueue_script('urlyar_surl_ajax', $this->plugin_url . '/assets/js/jquery.ajaxq.js', array('jquery'), 1.0);
            wp_enqueue_script('urlyar_surl_edit', $this->plugin_url . '/assets/js/tablecol.js', array('jquery'), 1.0);
        }









        /*
         *****************************************
         *
         *	AJAX CALLBACKS
         *
         *****************************************
         */


        /*
         *****************************************
         * Bulk Delete in Table Pages
         *****************************************
         */
        public function urlyar_urlshortener_ajaxcallback()
        {
            check_ajax_referer('urlshortener_ajax');
            $post_id = $_POST['pid'];
            delete_post_meta($post_id, 'shorturl');
        }




        /*
         *****************************************
         *
         *	Misc Functions
         *
         *****************************************
         */


        /*
         *****************************************
         * QR Code Support
		 * Used in: 
		 * - Shortcode
		 * - Template Display
		 * - Nice ID redirect
         *****************************************

         */
        public function get_qrcode($url, $size = '57', $qr_error = 'M', $url_only = false)
        {
            $append = $this->plugin_option->get('appendqr');
            if ($append["provider"] == "google")
                $link = 'https://chart.googleapis.com/chart?cht=qr&chl=' . $url . '&chs=' . $size . 'x' . $size . '&chld=' . $qr_error;
            else
                $link = $url . '/qr?chs=' . $size . 'x' . $size . '&chld=' . $qr_error;
            return ($url_only) ? $link : '<img class="urlyar-qr-img" width="' . $size . '" height="' . $size . '" src="' . $link . '" alt="" />';
        }


        /*
         *****************************************
         * Nice ID URL Redirect
         *****************************************
         */
        public function template_redirect($requested_url = null, $do_redirect = true)
        {

            global $wp;
            global $wp_query;

            if (is_404()) {
                $post_id = '';
                $request = $wp->request;
                $qr = false;

                //check for QR Code endings
                if (substr($request, -3) == '.qr') {
                    $qr = true;
                    $request = substr($request, 0, -3);
                }

                //matching for post id
                if (preg_match('/(\\d+)/', $request, $matches)) {
                    $post_id = $matches[0];
                }

                //determine if still 404
                if (!empty($post_id) && is_numeric($post_id)) {
                    $full_url = get_permalink($post_id);

                    if ($full_url) {
                        if ($qr) {
                            header('Location: ' . $this->get_qrcode($full_url, 150, 'M', true));
                            exit();
                        } else {
                            status_header(200);
                            wp_redirect($full_url, 301);
                            exit();
                        }
                    }
                }

            } //ends is_404 block

        }


        /*
         *****************************************
         * For use in template to 
		 * display shorturl
         *****************************************
         */

        public function urlyar_get_shortlink_display($post_id)
        {
            $shortlink = $this->pub_get_shortlink($post_id);
            return $shortlink;
        }


        /*
         *****************************************
         * Shortcode Support
         *****************************************
         */

        public function shortcode_support($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'name' => '',
                'url' => '',
                'service' => $this->plugin_option->get('urlservice'),
                'key' => '',
                'user' => '',
                'qr' => false,
                'size' => '',
                'qr_error' => '',
                'full' => false,
            ), $atts));

            global $post;

            $post_id = $post->ID;
            $full_url = get_permalink($post_id);

            //Assign URL as content if content not empty
            if (!empty($content)) {
                $url = $content;
            }


            //check if url still empty
            //Get shortlink if url true
            //Get Post URL and shortlink if false
            $shortlink = ($url) ? $this->od_get_shortlink($url, $service, $key, $user) :
                $this->pub_get_shortlink($post_id);

            //output type QR code?
            if (empty($qr)) {

                $output = '<a href="' . $shortlink . '">';
                $output .= ($name) ? $name : $shortlink;
                $output .= '</a>';

            } else {

                //determine if full URL used in QR code
                $output = ($full) ? '<a href="' . $full_url . '">' . $this->get_qrcode($full_url, $size, $qr_error) . '</a>' :
                    '<a href="' . $shortlink . '">' . $this->get_qrcode($shortlink, $size, $qr_error) . '</a>';
            }

            return $output;

        }


        /*
         *****************************************
         * Append Shortlink to content
         *****************************************
         */

        public function urlyar_filter_append_post($content)
        {
            global $post;
            $append = $this->plugin_option->get('appendurl');

            if ((is_home() && $append['home'] == 'yes') ||
                (is_single() && $append['single'] == 'yes') ||
                (is_single() && $append['product'] == 'yes') ||
                (is_page() && $append['page'] == 'yes')
            ) {

                $shortlink = $this->pub_get_shortlink($post->ID);
                $content .= '<div class="urlyar-shortlink"><strong>' . $append['text'] . '</strong> <a class="urlyar-link" href="' . $shortlink . '">' . $shortlink . '</a></div>';
            }
            return $content;
        }


        /*
         *****************************************
         * Append QR Code to content
         *****************************************
         */
        public function urlyar_filter_append_post_qr($content)
        {
            global $post;
            $append = $this->plugin_option->get('appendqr');

            if ((is_home() && $append['home'] == 'yes') ||
                (is_single() && $append['single'] == 'yes') ||
                (is_single() && $append['product'] == 'yes') ||
                (is_page() && $append['page'] == 'yes')
            ) {

                $shortlink = $this->pub_get_shortlink($post->ID);
                $content .= '<div class="urlyar-qr">';
                $content .= (!empty($append['text'])) ? '<strong>' . $append['text'] . '</strong>' : '';
                $content .= $this->get_qrcode($shortlink, 150) . '</div>';
            }
            return $content;
        }


//end class
    }
endif;


/*
 *****************************************
 *
 *	WordPress Call to
 *	Initialize and Activate Object
 *
 *****************************************
 */
if (class_exists('URLYAR_URL_Shortener')) :


    /*
     *****************************************
     * Plugin Activation and assignments
     *****************************************
     */
    global $URLYAR_URL_Shortener;
    $URLYAR_URL_Shortener = new URLYAR_URL_Shortener(URLYAR_URL_SHORTENER_VERSION, URLYAR_URL_SHORTENER_STATUS);
    $URLYAR_URL_Shortener->activate_shortener();

    if (isset($URLYAR_URL_Shortener)) {
        register_activation_hook(__FILE__, array(&$URLYAR_URL_Shortener, 'install'));
    }


    /*
     *****************************************
     * Backward compatibility (template) functions
     *****************************************
     */

    //Show URL
    function urlyar_show_shorturl($post, $output = true)
    {
        global $URLYAR_URL_Shortener;

        $post_id = $post->ID;
        $shorturl = $URLYAR_URL_Shortener->urlyar_get_shortlink_display($post_id);

        if ($output) {
            echo $shorturl;
        } else {
            return $shorturl;
        }
    }

    //On-demand URL Shortener
    function urlyar_shorturl($url, $service, $output = true, $key = '', $user = '')
    {
        global $URLYAR_URL_Shortener;

        $shorturl = $URLYAR_URL_Shortener->od_get_shortlink($url, $service, $key, $user);

        if ($output) {
            echo $shorturl;
        } else {
            return $shorturl;
        }
    }


endif;
?>
