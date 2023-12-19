<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$options = $this->plugin_option;


$options->refresh();


/*
*****************************************
*
* Option page saving
*
*****************************************
*/

if (isset($_POST['submitted'])) {
    check_admin_referer('urlyar-urlshortener');

    //Shortener
    $options->set('urlserviceenable',sanitize_text_field( $_POST['urlserviceenable']));
    $options->set('useslug', sanitize_text_field($_POST['useslug']));
    $options->set('appendurl', array('home' =>sanitize_text_field( $_POST['appendurl_home']),
            'single' => sanitize_text_field($_POST['appendurl_single']),
            'page' => sanitize_text_field($_POST['appendurl_page']),
            'product' => sanitize_text_field($_POST['appendurl_product']),
            'text' => strip_tags(sanitize_text_field($_POST['appendurl_text'])),
        )
    );

    $options->set('appendqr', array('home' => sanitize_text_field($_POST['appendqr_home']),
            'single' => sanitize_text_field($_POST['appendqr_single']),
            'page' => sanitize_text_field($_POST['appendqr_page']),
            'product' => sanitize_text_field($_POST['appendqr_product']),
            'text' => strip_tags(sanitize_text_field($_POST['appendqr_text'])),
            'provider' => sanitize_text_field($_POST['appendqr_provider']),
        )
    );

    //Nice ID
    $options->set('niceid', sanitize_text_field($_POST['niceid']));
    $options->set('niceid_prefix', esc_url_raw($_POST['niceid_prefix']));

    //Shortcode
    $options->set('url_shortcode', sanitize_text_field($_POST['url_shortcode']));

    //Services
    $service_list = $this->shortener->service_list('detailed');

    foreach ($service_list as $keys => $values) {

        switch ($values[2]) {
            case 1:
            case 4:
                $options->set('apiuser_' . $keys, sanitize_text_field($_POST['apiuser_' . $keys]));
                break;
            case 2:
            case 5:
                $options->set('apikey_' . $keys, sanitize_text_field($_POST['apikey_' . $keys]));
                break;
            case 3:
            case 6:
            case 101:
                $options->set('apiuser_' . $keys, sanitize_text_field($_POST['apiuser_' . $keys]));
                $options->set('apikey_' . $keys, sanitize_text_field($_POST['apikey_' . $keys]));
                break;

            default:
                break;
        }
    }

    $options->set('generic_yourls', array('endpoint' => sanitize_text_field($_POST['generic_yourls_endpoint'])
        )
    );


    $options->set('generic_interdose', array('service' => sanitize_text_field($_POST['generic_interdose_service'])
        )
    );


    //last command includes save.
    $options->set('urlservice', sanitize_text_field($_POST['urlservice']));
    $options->save();


    echo '<div class="updated fade"><p>Plugin settings saved.</p></div>';
}


/*
*****************************************
*
* Setup option page display
*
*****************************************
*/


$options->refresh();

$urlserviceenable = $options->get('urlserviceenable');
$urlservice = $options->get('urlservice');
$useslug = $options->get('useslug');
$appendurl = $options->get('appendurl');
$appendqr = $options->get('appendqr');
$niceid = $options->get('niceid');
$niceid_prefix = $options->get('niceid_prefix');
$url_shortcode = $options->get('url_shortcode');

//$sfx = new FTShared();

$supported = $this->shortener->service_list('detailed');
$authkey = $this->shortener->service_list('authkey');
$authuser = $this->shortener->service_list('authuser');
$reqkey = $this->shortener->service_list('reqkey');
$requser = $this->shortener->service_list('requser');


/*
*****************************************
*
* Start of the form
*
*****************************************
*/
?>
<div class="wrap">
    <h2><?php _e('URLYar URL Shortener', 'url-shortener');
        echo ' ' . $this->plugin_version ?></h2>

    <div class="j-show" id="tab-select">
        <?php _e('Options', 'url-shortener')?>:
        <ul>
            <li><a rel="opt-gen" href="#opt-general"><?php _e('General', 'url-shortener')?></a></li>
            <li><a rel="opt-add" href="#opt-additional"><?php _e('Additional Features', 'url-shortener')?></a></li>
            <!--<li><a rel="opt-mod" href="#opt-modules">Modules</a></li>-->
        </ul>
    </div>


    <form method="post" action="<?php echo $action_url ?>" id="shorturl_options">
        <?php wp_nonce_field('urlyar-urlshortener'); ?>
        <input type="hidden" name="submitted" value="1"/>


        <?php /*
		*****************************************
		*
		* General Options
		*
		*****************************************
		*/ ?>
        <fieldset id="opt-general" title="General Options for Plugin" class="fs-opt opt-gen j-hide">
            <h3 class="divider"><?php _e('Main Settings', 'url-shortener'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label
                                for="urlserviceenable"><?php _e('URL Shortener Integration', 'url-shortener'); ?></label>
                    </th>
                    <td>
                        <input name="urlserviceenable" id="urlserviceenable" type="checkbox"
                               value="yes" <?php checked('yes', $urlserviceenable) ?> />
                        <span class="description"><?php _e('Enable Short URL generation using your <a href="#shorturl_selector">configured service<a/>.', 'url-shortener'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="useslug"><?php _e('Use Permalinks for Short URLs', 'url-shortener'); ?></label>
                    </th>
                    <td>
                        <input name="useslug" id="useslug" type="checkbox"
                               value="yes" <?php checked('yes', $useslug) ?> />
                        <span class="description"><?php sprintf(_e('Use your <a href="%s/wp-admin/options-permalink.php">permalinks</a> to generate the Short URL.'), get_option('siteurl')); ?>
                            <br/><?php _e('(Default: "http://yoursite/?p=123" or "http://yoursite/?page_id=123")', 'url-shortener'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php _e('Append Short URL to: ', 'url-shortener'); ?></label></th>
                    <td>
                        <input name="appendurl_home" id="appendurl_home" type="checkbox"
                               value="yes" <?php checked('yes', $appendurl['home']) ?> />
                        <label for="appendurl_home"><?php _e('Posts (Homepage)', 'url-shortener'); ?></label>
                        <br/>
                        <input name="appendurl_single" id="appendurl_single" type="checkbox"
                               value="yes" <?php checked('yes', $appendurl['single']) ?> />
                        <label for="appendurl_single"><?php _e('Posts (Individual)', 'url-shortener'); ?></label>
                        <br/>
                        <input name="appendurl_page" id="appendurl_page" type="checkbox"
                               value="yes" <?php checked('yes', $appendurl['page']) ?> />
                        <label for="appendurl_page"><?php _e('Pages', 'url-shortener'); ?></label>
                        <br/>
                        <input name="appendurl_product" id="appendurl_product" type="checkbox"
                               value="yes" <?php checked('yes', $appendurl['product']) ?> />
                        <label for="appendurl_product"><?php _e('Products', 'url-shortener'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th><label for="appendurl_text"><?php _e('Text before Short URL link ', 'url-shortener'); ?></label>
                    </th>
                    <td>
                        <input name="appendurl_text" type="text" id="appendurl_text"
                               value="<?php echo $appendurl['text']; ?>" class="regular-text code"/>
                    </td>
                </tr>
            </table>
        </fieldset>


        <?php /*
		*****************************************
		*
		* Additional options
		*
		*****************************************
		*/ ?>
        <fieldset id="opt-additional" title="Additional Features" class="fs-opt opt-add j-hide">
            <h3 class="divider"><?php _e('Nice ID', 'url-shortener'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="niceid"><?php _e('Nice ID URLs', 'url-shortener'); ?></label><br/>
                        <span class="description"><?php _e('(Formally named template_redirection)', 'url-shortener'); ?></span>
                    </th>
                    <td>
                        <input name="niceid" id="niceid" type="checkbox" value="yes" <?php checked('yes', $niceid) ?> />
                        <span class="description"><?php _e('Allows usage of "http://yoursite/123" instead of "http://yoursite/?p=123"', 'url-shortener'); ?></span>
                    </td>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="niceid"><?php _e('Nice ID URL Prefix', 'url-shortener'); ?></label></th>
                    <td>
                        <input name="niceid_prefix" type="text" id="niceidprefix" value="<?php echo $niceid_prefix; ?>"
                               class="regular-text code"/>
                        <span class="description"><?php _e('default: "/"  (http://yoursite/123)</span>
                        <p>Examples:
                            <br />"<span class="red">prefix/</span>" = http://yoursite/<span class="red">prefix/</span>123
                            <br />"<span class="red">prefix-</span>" = http://yoursite/<span class="red">prefix-</span>123
                        </p>', 'url-shortener'); ?>
                    </td>
                    </td>
                </tr>
            </table>
            <h3 class="divider"><?php _e('Shortcode', 'url-shortener'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label
                                for="url_shortcode"><?php _e('Disable Shortcode [urlyar_shortlink]', 'url-shortener'); ?></label>
                    </th>
                    <td>
                        <input name="url_shortcode" id="url_shortcode" type="checkbox"
                               value="disable" <?php checked('disable', $url_shortcode) ?> />
                        <span class="description"><?php _e('Disables the usage of URL Shortener shortcode [urlyar_shortlink]', 'url-shortener'); ?></span>
                    </td>
                    </td>
                </tr>
            </table>
            <h3 class="divider"><?php _e('QR Code', 'url-shortener'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label><?php _e('Append QR Code to: ', 'url-shortener'); ?></label></th>
                    <td>
                        <input name="appendqr_home" id="appendqr_home" type="checkbox"
                               value="yes" <?php checked('yes', $appendqr['home']) ?> />
                        <label for="appendqr_home"><?php _e('Posts (Homepage)', 'url-shortener'); ?></label>
                        <br/>
                        <input name="appendqr_single" id="appendqr_single" type="checkbox"
                               value="yes" <?php checked('yes', $appendqr['single']) ?> />
                        <label for="appendqr_single"><?php _e('Posts (Individual)', 'url-shortener'); ?></label>
                        <br/>
                        <input name="appendqr_page" id="appendqr_page" type="checkbox"
                               value="yes" <?php checked('yes', $appendqr['page']) ?> />
                        <label for="appendqr_page"><?php _e('Pages', 'url-shortener'); ?></label>
                        <br/>
                        <input name="appendqr_product" id="appendqr_product" type="checkbox"
                               value="yes" <?php checked('yes', $appendqr['page']) ?> />
                        <label for="appendqr_product"><?php _e('Products', 'url-shortener'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th><label for="appendqr_provider"><?php _e('QR Code Provider ', 'url-shortener'); ?></label></th>
                    <td>
                        <div><input name="appendqr_provider" type="radio" id="appendqr_provider"
                                    value="urlyar" <?php echo ($appendqr['provider'] == "urlyar") ? "checked" : ""; ?>
                                    class="regular-radio code"/>
                            <label for="appendqr_urlyar"><?php _e('URLYar', 'url-shortener'); ?></label>
                        </div>
                        <div><input name="appendqr_provider" type="radio" id="appendqr_provider"
                                    value="google" <?php echo ($appendqr['provider'] == "google") ? "checked" : ""; ?>
                                    class="regular-radio code"/>
                            <label for="appendqr_google"><?php _e('Google', 'url-shortener'); ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><label for="appendqr_text"><?php _e('Text before QR Code ', 'url-shortener'); ?></label></th>
                    <td>
                        <input name="appendqr_text" type="text" id="appendqr_text"
                               value="<?php echo $appendqr['text']; ?>" class="regular-text code"/>
                    </td>
                </tr>
            </table>
        </fieldset>

        <?php /*
		*****************************************
		*
		* Modules
		*
		*****************************************
        <fieldset id="opt-modules" title="List of Modules" class="fs-opt opt-mod j-hide">
			<h3 class="divider"><?php _e('Shortener Modules', 'url-shortener'); ?></h3> 
			<table id="component_list" class="widefat post fixed" cellspacing="0">
            	<thead>
                    <tr>
                        <th scope="col" class="manage-column"><?php _e('Name', 'url-shortener'); ?></th>
						<th scope="col" class="manage-column"><?php _e('Description', 'url-shortener'); ?></th>
						<th scope="col" class="manage-column colsmall"><?php _e('ID', 'url-shortener'); ?></th>
                    </tr>
                </thead>
            	<tfoot>
                    <tr>
                        <th scope="col" class="manage-column"><?php _e('Name', 'url-shortener'); ?></th>
						<th scope="col" class="manage-column"><?php _e('Description', 'url-shortener'); ?></th>
						<th scope="col" class="manage-column colsmall"><?php _e('ID', 'url-shortener'); ?></th>
                    </tr>
                </tfoot>
				<tbody>
				<?php foreach ($this->shortener_modules as $modules){ ?>
					<tr>
						<td class="name">
							<strong class="checkit"><?php echo $modules['name']; ?></strong>
							<span>Version: <?php echo $modules['version']; ?></span>
						</td>
						<td>

							<?php echo $modules['description']; ?>
						</td>
						<td><?php echo $modules['classname']; ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
        </fieldset>
		*/ ?>

        <?php /*
		*****************************************
		*
		* Shortlink Service Table
		*
		*****************************************
		*/ ?>
        <fieldset title="URL Shortening Services" id="shorturl_selector" class="opt-gen j-hide">
            <h3 class="divider"><?php _e('URL Service Configuration', 'url-shortener'); ?></h3>
            <p><?php _e('Select and configure your desired Short URL service.', 'url-shortener'); ?></p>
            <p><?php _e('<span class="red">*</span> are required configurations for that service.', 'url-shortener'); ?></p>
            <div class="reqfielderror"></div>
            <table id="shorturl_table" class="widefat post fixed" cellspacing="0">
                <thead>
                <tr>
                    <th scope="col" id="col1" class="manage-column"><?php _e('Select', 'url-shortener'); ?></th>
                    <th scope="col" id="col2" class="manage-column"><?php _e('Services', 'url-shortener'); ?></th>
                    <th scope="col" class="manage-column"><span
                                class="col3c"><?php _e('Configuration', 'url-shortener'); ?></span></th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <th scope="col" class="manage-column"><?php _e('Select', 'url-shortener'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Services', 'url-shortener'); ?></th>
                    <th scope="col" class="manage-column"><span
                                class="col3c"><?php _e('Configuration', 'url-shortener'); ?></span></th>
                </tr>
                </tfoot>
                <tbody>
                <?php

                /*
                 *****************************************
                 *
                 * Individual configuration fields
                 *
                 *****************************************
                 */

                foreach ($supported as $key => $value) {

                    if ($urlservice == $key) {
                        $sh = 'show';
                        $rh = 'class="detail"';
                    } else {
                        $sh = 'hide';
						$rh='';
                    }
                    $apirow = '<tr id="row_' . $key . '"' . $rh . '>';
                    $apirow .= '<th class="ssr" scope="row"><input name="urlservice" id="' . $key . '" type="radio" value="' . $key . '"' . checked($key, $urlservice, false) . '/></th>';
                    $apirow .= '<td class="ssl"><label for="' . $key . '">' . $value[0] . '</label></td><td>';

                    $apirow .= '<div id="userkey_' . $key . '" class="APIConfig ' . $sh . '">';
                    $apireq = '';


                    /*
                     *****************************************
                     * User Authentication
                     *****************************************
                     */
                    if (in_array($key, $authuser)) {
                        $apireq .= '<label for="apiuser_' . $key . '">';
                        in_array($key, $requser) ? $apireq .= '<span>*</span>' : $apireq .= '';
                        $apireq .= __('User/ID', 'url-shortener') . ': </label><input type="text" name="apiuser_' . $key . '" id="apiuser_' . $key . '" value="' . $options->get('apiuser_' . $key) . '" />';
                    }


                    /*
                     *****************************************
                     * Key Authentication
                     *****************************************
                     */
                    if (in_array($key, $authkey)) {
                        $apireq .= '<label for="apikey_' . $key . '">';
                        in_array($key, $reqkey) ? $apireq .= ' <span>*</span>' : $apireq .= '';
                        $apireq .= __('Key/API', 'url-shortener') . ': </label><input type="text" name="apikey_' . $key . '" id="apikey_' . $key . '" value="' . $options->get('apikey_' . $key) . '" />';

                    }

                    /*
                     *****************************************
                     * Misc Authentication Fields..
                     * Case by Case Basis
                     *****************************************
                     */

                    switch ($key) {
                        case 'yourls':
                            $apireq .= '<label for="apiuser_' . $key . '">';
                            $apireq .= __('User/ID', 'url-shortener') . ': </label><input type="text" name="apiuser_' . $key . '" id="apiuser_' . $key . '" value="' . $options->get('apiuser_' . $key) . '" />';

                            $apireq .= '<label for="apikey_' . $key . '">';
                            $apireq .= __('Password', 'url-shortener') . ': </label><input type="password" name="apikey_' . $key . '" id="apikey_' . $key . '" value="' . $options->get('apikey_' . $key) . '" />';

                            $apireq .= '<div class="contentblock"><label for="generic_' . $key . '_endpoint">';
                            $generic = $options->get('generic_' . $key);
                            $apireq .= __('URL to the YOURLS API', 'url-shortener') . ': </label><input style="display: block; width: 80%;" type="text" name="generic_' . $key . '_endpoint" id="generic_' . $key . '_endpoint" value="' . $generic['endpoint'] . '" />';
                            $apireq .= 'Example: http://site.com/yourls-api.php';
                            $apireq .= '<br /><strong>Note:</strong> Please check out <a href="http://salamzadeh.net/plugins/urlyar/docs/url-shortener-wordpress-plugin/known-issues">Known issues</a> before activiting yourls support</strong>';
                            $apireq .= '</div>';

                            break;
                        case 'interdose':
                            $apireq .= '<div class="contentblock"><label for="generic_' . $key . '_service">';
                            $generic = $options->get('generic_' . $key);
                            $apireq .= '<span>*</span>' . __('Service/Domain', 'url-shortener') . ': </label><input style="display: block; width: 80%;" type="text" name="generic_' . $key . '_service" id="generic_' . $key . '_service" value="' . $generic['service'] . '" />';
                            $apireq .= 'Example: piep.net, xlnk.cc';
                            $apireq .= '</div>';

                            break;

                        default:
                            break;
                    }


                    /*if (in_array($key, $this->generic)){
                        $apireq .= '<br /><label for="generic_'.$key.'">';

                        //Cases
                        switch ($key){
                            case 'interdose':
                                $apireq .= __('Service', 'url-shortener') . ': </label><input type="text" name="generic_'.$key.'" id="generic_'.$key.'" value="'.$options->get('generic_'.$key].'" />';
                                $data = $sfx->openurl('http://api.interdose.com/api/shorturl/v1/services.json');
                                $data = $sfx->processjson($data);
                                $count = count($data);
                                if ($count){
                                    $apireq .= '<br /><span class="slabel">Public Services: </span>';
                                    for ($i = 0; $i < $count; $i++){
                                        $apireq .= '<a class="val_'.$key.'" href="#generic_'.$key.'">'.$data[$i]->service.'</a>';
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                    } */

                    if ($apireq == '') {
                        $apireq = '<span class="nc">' . __('No Configuration Needed', 'url-shortener') . '</span>';
                    }

                    $apirow .= $apireq;
                    $apirow .= '</div></td></tr>';
                    $rh = '';
                    echo $apirow;
                }
                ?>
                </tbody>
            </table>

            <div class="clear"></div>
        </fieldset>


        <div class="reqfielderror"></div>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {

                //display modification
                $('.hide, .col3c, .reqfielderror, .j-hide').hide();
                $('#tab-select li:first a').addClass('tab-now');
                $('.j-show').show();

                var tabnow = '.' + $('.tab-now').attr('rel');
                $(tabnow).show();

                $('#tab-select a').click(function () {
                    $('.j-hide').hide();
                    $('.tab-now').removeClass('tab-now');
                    $(this).addClass('tab-now');

                    var tabnow = '.' + $('.tab-now').attr('rel');
                    $(tabnow).fadeIn();
                    return false;
                })

                //Service Selection Table
                $('.ssr input[type="radio"]').change(function () {
                    $('.showdetail .APIConfig, .detail .APIConfig').hide();
                    $('.showdetail').removeClass('showdetail');
                    var pc = '';
                    pc = $(this).parent().parent();
                    if (($(this).is(':checked'))) {// && !(pc.hasClass('rh'))){
                        pc.addClass('showdetail');
                        $('.showdetail .APIConfig').show();
                    }
                });

                $('.val_interdose').click(function () {
                    linkval = $(this).html()
                    $('#generic_interdose').val(linkval);
                })

                //Submission Functions
                var requser = ['snipurl', 'snurl', 'snipr', 'snim', 'cllk'];
                var reqkey = ['snipurl', 'snurl', 'snipr', 'snim', 'cllk', 'awesm', 'pingfm'];
                $('#shorturl_options').submit(function () {
                    $('.reqfielderror').html('');
                    var errorcount = false;
                    var checkopt = $('input:radio[name=urlservice]:checked').val();
                    if ($.inArray(checkopt, requser) == -1) {
                    } else {
                        var suser = jQuery.trim($('#apiuser_' + checkopt).val());
                        if (suser == '') {
                            $('.reqfielderror').append('<?php _e('<strong>Service Configuration: </strong>Please fill the required User/ID', 'url-shortener'); ?><br />');
                            errorcount = true;
                        }
                    }
                    if ($.inArray(checkopt, reqkey) == -1) {
                    } else {
                        var spass = jQuery.trim($('#apikey_' + checkopt).val());
                        if (spass == '') {
                            $('.reqfielderror').append('<?php _e('<strong>Service Configuration: </strong>Please fill in the required API/Key', 'url-shortener'); ?><br />');
                            errorcount = true;
                        }
                    }
                    if (errorcount) {
                        $('.reqfielderror').fadeIn(400);
                        return false;
                    } else {
                        $('.reqfielderror').hide();
                        //return false;
                    }
                });//end submission

            });//end js    
        </script>

        <p class="submit"><input type="submit" id="submit-button" class="button-primary"
                                 value="<?php _e('Save Changes') ?>"/></p>
    </form>
</div>
