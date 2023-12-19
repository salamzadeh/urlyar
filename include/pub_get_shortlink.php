<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wp_query;

$shortlink = '';
$options = $this->plugin_option;


/*
    Ensure shortener is loaded.. 
    i.e all options are set correctly
    Abort otherwise
*/

if (!$this->shortener_loader()) {
    return '';
}


/*
    Page type switches
*/
if ($context == 'query') {
    if (is_singular()) {
        $id = $wp_query->get_queried_object_id();
        $context = 'post';
    } elseif (is_front_page()) {
        $context = 'blog';
    } else {
        return '';
    }
}


$home_url = get_option('home');


/*
    If Homepage
*/
if ($context == 'blog') {
    if (empty($id))
        $url = $home_url;

    if ($options->get('urlservice') != 'yourls') {
        $shortlink = $this->shortener->generate($url);
    }
    return $shortlink;
}

/*
    If post
*/
$post = get_post($id);
if (empty($post)) {
    return '';
}


/*
    Set up post details
*/

$post_id = $post->ID;
$post_type = $post->post_type;
$post_status = $post->post_status;
$url = '';
$saved_url = get_post_meta($post_id, 'shorturl', true);


/*
 ************************
 *
 * Main Shortening Work
 *
 ************************
 */

//check prior generation and publish status
if (empty($saved_url) && ($post_status == 'publish' || $transition == true)) {


    //Add 'Generating' word stub to prevent generation loops (esp. Yourls)
    update_post_meta($post_id, 'shorturl', 'Generating...');

    //Use permalinks
    if ($options->get('useslug') == 'yes') {
        $url = get_permalink($post_id);

        if ($url) {
            $shortlink = $this->shortener->generate($url);
        }

        //Use IDs
    } else {


        switch ($post_type) {
            case 'post' :
                $url = $home_url . "/index.php?p=" . $post_id;
                break;
            case 'product' :
                $url = $home_url . "/index.php?p=" . $post_id;
                break;
            case 'page' :
                $url = $home_url . "/index.php?page_id=" . $post_id;
                break;
            default :
                break;
        }
        if ($url) {
            $shortlink = $this->shortener->generate($url);
        }
    }

    /*
     ***********************
     * Allow other plugins to use generated shortlink (1st generation) 
     ************************
     */

    if (!empty($shortlink)) {
        do_action('urlyar_use_shortlink', $post_id, $shortlink);
    }


//assign saved URL if already generated    
} elseif (!empty($saved_url)) {
    $shortlink = $saved_url;
}

//Update Custom Field
if (empty($saved_url) && !empty($shortlink)) {
    update_post_meta($post_id, 'shorturl', $shortlink);

} elseif (empty($saved_url)) {
    //remove 'Generating' word stub in case generation failed
    delete_post_meta($post_id, 'shorturl', 'Generating...');
}


//Return Nice ID if shortlink is still empty
if ($options->get('niceid') == 'yes' && empty($shortlink)) {
    $shortlink = $home_url . $options->get('niceid_prefix') . $post_id;
}


/*
 ***********************
 * Allow other plugins to filter output
 ************************
 */

if (!empty($shortlink)) {
    apply_filters('urlyar_filter_shortlink', $post_id, $shortlink);
}


/*
 ***********************
 * Finally!
 ************************
 */
return $shortlink;


?>
