<?php
/*
  Plugin Name: Twitter Profiler - User Profile with Twitter
  Plugin URI: http://21coder.com/
  Description: The best & smartest User Profile with Twitter Username.
  Version: 0.1
  Author: Tapan Kumer Das
  Author URI: http://21coder.com/
  Text Domain: twipro
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

  define( 'TWIPRO_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
  define( 'TWIPRO_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
  //echo plugin_basename(__FILE__);
/**
 * Load plugin textdomain
 *
 * @package Twitter Profiler
 * @since 0.1
 */
function load_twipro_plugin_textdomain() {
	load_plugin_textdomain( 'twipro', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' ); 
}
add_action( 'plugins_loaded', 'load_twipro_plugin_textdomain' );

require_once TWIPRO_PLUGIN_PATH . '/includes/TwitterAPIExchange.php';

if ( !defined( 'ABSPATH' ) ) exit;  // if direct access

if ( !class_exists( "TwitterProfiler" ) ) {

	class TwitterProfiler {

		//-----------------------------------------
        // Options
        //-----------------------------------------
		var $options = 'TwiPro';
        //-----------------------------------------
        // Options page
        //-----------------------------------------
		var $optionsPageTitle = '';
		var $optionsMenuTitle = '';

		public function __construct() {
			$this->optionsPageTitle = __( 'Twitter Profiler Settings', 'twipro' );
			$this->optionsMenuTitle = __( 'Twitter Profiler', 'twipro' );

			add_action( 'admin_menu', array($this, 'twipro_admin_menu') );
			//add_action( 'wp_enqueue_scripts', array($this, 'twipro_scripts') );
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'twipro_action_links' ) );
			//if ( is_admin() ) {
				//add_action( 'admin_footer', array($this, 'add_footer_script') );
			//}
			//add_action( 'admin_enqueue_scripts', array( $this, 'twipro_admin_script' ) );
		}

		public function twipro_admin_script() {
			wp_enqueue_media();
			wp_register_style( 'twipro-admin-css', plugins_url( '/css/admin.css', __FILE__ ), false, '1.0.0' );
			wp_enqueue_style( 'twipro-admin-css' );
			wp_enqueue_script( 'twipro-admin-js', plugins_url( '/js/admin.js', __FILE__ ), array('jquery'), '1.0.0', true );
		}

		public function twipro_action_links( $links ){
			$mylinks = array(
				'<a href="' . admin_url( 'options-general.php?page=twitter-profiler.php' ) . '">Settings</a>',
				);
			return array_merge( $links, $mylinks );
		}

		public function twipro_admin_menu() {
			//echo 100;
			add_menu_page( $this->optionsPageTitle, $this->optionsMenuTitle, 'manage_options', basename( __FILE__ ), array($this, 'optionsPage') );
		}

		public function optionsPage() {
			if ( isset( $_POST['src_nonce_box_nonce'] ) && wp_verify_nonce( $_POST['src_nonce_box_nonce'], 'src_nonce_box' ) ) {
				if ( isset( $_POST['update_options'] ) ) {
					if ( get_magic_quotes_gpc() ) {
						$_POST = array_map( 'stripslashes_deep', $_POST );
					}

					$options = $_POST['options'];
					if ( update_option( $this->options, $options ) ) {
						do_action( 'src_option_saved' );
					}
					wp_redirect( admin_url( 'admin.php?page=twipro&msg=' . __( 'Options+saved.', 'twipro' ) ) );
				}
			}

			$options = get_option( $this->options, true );

			if ( isset( $_REQUEST['msg'] ) && !empty( $_REQUEST['msg'] ) ) {
				?>
				<div class="updated">
					<p><strong><?php echo str_replace( '+', ' ', $_REQUEST['msg'] ); ?></strong></p>
				</div>
				<?php
			}

            // Display options form
			?>
			<div class="wrap">
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="post-body-content">
							<form method="post" action="<?php echo admin_url( 'admin.php?page=twipro&noheader=true' ); ?>" class="relatify_form">
								<h2><?php echo $this->optionsPageTitle; ?></h2>
								<div class="postbox">
									<h3 class="hndle"><span><?php _e( 'General Settings', 'relatify' ) ?></span></h3>
									<div class="inside">
										<table class="form-table">
											<tr valign="top">
												<th style="width: 40%" scope="row"><?php _e( 'Do you want to auto inject the related content?' ) ?></th>
												<td>
													<label style="margin-right: 10px;">
														<input <?php echo isset( $options['auto_inject'] ) && $options['auto_inject'] == 'yes' ? 'checked' : '' ?> type="radio" name="options[auto_inject]" value="yes"><?php _e( 'Yes', 'relatify' ) ?>
													</label>
													<label style="margin-right: 10px;">
														<input <?php echo isset( $options['auto_inject'] ) && $options['auto_inject'] == 'no' ? 'checked' : (!isset( $options['auto_inject'] ) ? 'checked' : '' ) ?> type="radio" name="options[auto_inject]" value="no"><?php _e( 'No', 'relatify' ) ?>
													</label>
												</td>
											</tr>
											<tr valign="top" class="autoExtra">
												<th scope="row"><?php _e( 'Title', 'relatify' ) ?></th>
												<td>
													<input type="text" name="options[title]" value="<?php echo isset( $options['title'] ) ? $options['title'] : '' ?>" style="width:50%;"/>
												</td>
											</tr>
											<tr valign="top" class="autoExtra">
												<th scope="row"><?php _e( 'Number of posts', 'relatify' ) ?></th>
												<td>
													<input type="text" name="options[number]" value="<?php echo isset( $options['number'] ) ? $options['number'] : '' ?>" style="width:50%;"/>
												</td>
											</tr>
											<tr valign="top" class="autoExtra">
												<th scope="row"><?php _e( 'Where to show', 'relatify' ) ?></th>
												<td>
													<label style="margin-right: 10px;">
														<input <?php echo isset( $options['content_pos'] ) && $options['content_pos'] == 'top' ? 'checked' : '' ?> type="radio" name="options[content_pos]" value="top"><?php _e( 'Top of the content', 'relatify' ) ?>
													</label>
													<label style="margin-right: 10px;">
														<input <?php echo isset( $options['content_pos'] ) && $options['content_pos'] == 'bottom' ? 'checked' : (!isset( $options['content_pos'] ) ? 'checked' : '' ) ?> type="radio" name="options[content_pos]" value="bottom"><?php _e( 'Bottom of the content', 'relatify' ) ?>
													</label>
												</td>
											</tr>
											<tr valign="top" class="autoExtra">
												<th scope="row"><?php _e( 'Do you want to show image in related post list?', 'relatify' ); ?></th>
												<td>
													<label style="margin-right: 10px;">
														<input <?php echo isset( $options['show_image'] ) && $options['show_image'] == 'yes' ? 'checked' : '' ?> type="radio" name="options[show_image]" onclick="show_image_type_row()"  value="yes" /><?php _e( 'Yes', 'relatify' ) ?>
													</label>
													<label style="margin-right: 10px;">
														<input <?php echo isset( $options['show_image'] ) && $options['show_image'] == 'no' ? 'checked' : (!isset( $options['show_image'] ) ? 'checked' : '' ) ?> type="radio" name="options[show_image]" onclick="hide_image_type_row()" value="no" /><?php _e( 'No', 'relatify' ) ?>
													</label>
												</td>
											</tr>
											<tr id="image_size" valign="top" class="autoExtra" <?php echo isset( $options['show_image'] ) && $options['show_image'] == 'yes' ? '' : 'style="display: none;"' ?>>
												<th scope="row">
													Image Height & Width?
												</th>
												<th scope="row">
													<div style="margin-left: 10px;">
														<label style="margin-right: 10px;">Height: </label> <input type="text" name="options[image_height]" value="<?php echo isset( $options['image_height'] ) && $options['image_height'] != '' ? $options['image_height'] : '' ?>" size="10" />
														<label style="margin-right: 10px;">Width: </label> <input type="text" name="options[image_width]" value="<?php echo isset( $options['image_width'] ) && $options['image_width'] != '' ? $options['image_width'] : '' ?>"  size="10" />
													</div>
												</th>
											</tr>
											<tr id="use_image" valign="top" class="autoExtra" <?php echo isset( $options['show_image'] ) && $options['show_image'] == 'yes' ? '' : 'style="display: none;"' ?>>
												<th scope="row">
													What type of image do you want to show?
												</th>
												<th scope="row">
													<div style="margin-left: 10px;">
														<label id="featured_img" style="margin-right: 10px;">Featured image:</label> <input <?php echo isset( $options['image_type'] ) && $options['image_type'] == 'featured' ? 'checked' : (!isset( $options['image_type'] ) ? 'checked' : '' ) ?> type="radio" name="options[image_type]" onclick="hide_custom_field_row()" value="featured"/><br><label style="font-weight: 300;">If there is no featured images,<br> first image will be used, otherwise a default image.)</label>
														<br><br>
														<label id="custom_img" style="margin-right: 10px;">Custom image field:</label> <input <?php echo isset( $options['image_type'] ) && $options['image_type'] == 'custom' ? 'checked' : '' ?> type="radio" name="options[image_type]" onclick="show_custom_field_row()" value="custom"/><br><label style="font-weight: 300;">If there is no image in custom field,<br> first image will be used, otherwise a default image.</label>
													</div>
												</th>
											</tr>
										</table>
										<?php wp_nonce_field( 'src_nonce_box', 'src_nonce_box_nonce' ); ?>
										<p class="submit">
											<input type="submit" class="button-primary button-semi-long" name="update_options" value="<?php _e( 'Update Settings', 'relatify' ) ?>"/>
										</p>
									</div>
								</div>
							</form>
							<div class="postbox">
								<h3 class="hndle"><span><?php _e( 'Upload New Theme', 'relatify' ) ?></span></h3>
								<div class="inside">
									<form action="<?php echo admin_url( 'admin.php?page=relatify.php&noheader=true' ) ?>" method="post" enctype="multipart/form-data" class="relatify_form">
										<table class="form-table">
											<tr>
												<th valign="top"><?php _e( 'Upload zip file of new theme', 'relatify' ) ?></th>
												<td valign="top">
													<input type="file" name="src_pro_theme">
												</td>
											</tr>
										</table>
										<?php wp_nonce_field( 'src_upload_nonce_box', 'src_upload_nonce_box_nonce' ); ?>
										<p>
											<input name="upload_theme" type="submit" class="button button-primary button-semi-long" value="<?php _e( 'Upload', 'relatify' ) ?>">
										</p>
									</form>
								</div>
							</div>

							<div class="postbox">
								<h3 class="hndle"><span><?php _e( 'Download Premium Themes', 'relatify' ) ?></span></h3>
								<div class="inside">
									<img src="http://relatify.co/images/premium_demo_one.png" style="width: 48%; padding: 1%;"/><img src="http://relatify.co/images/premium_demo_two.png" style="width: 48%; padding: 1%;" />
								</div>
							</div>

						</div>
						<div class="postbox-container" id="postbox-container-1">
							<div class="relatify_sidebar">

								<div class="relatify_panel postbox ">
									<h3 class="hndle"><span><?php _e( 'Support' ) ?></span></h3>
									<div class="inside">
										<?php _e( 'Please email to <a href="mailto:info@relatify.co">info@relatify.co</a> for any type of query, support and feedback.' ) ?>
									</div>
								</div>

								<div class="relatify_panel postbox ">
									<h3 class="hndle"><span><?php _e( 'Connect with us', 'relatify' ) ?></span></h3>
									<div class="inside">
										<ul class="rel_social">
											<li><a class="fb" href="https://www.facebook.com/pages/Relatify/355786654626599" target="_blank">facebook</a></li>
											<li><a class="twt" href="https://twitter.com/RelatifyWP" target="_blank">twitter</a></li>
											<li><a class="gplus" href="https://plus.google.com/b/114328735736896910748/114328735736896910748" target="_blank">google-plus</a></li>
											<li><a class="mail" href="mailto:info@relatify.co" target="_blank">mail</a></li>
										</ul>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}


	}
}