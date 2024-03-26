<?php 

/*
Plugin Name: Bookster Cross Property Search
Description: Add a Bookster Cross Property Search Widget for your Bookster Subscription to your posts and pages
Version: 1.0
Author: Bookster
Author URI: https://www.booksterhq.com/
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Bookster_CPSW
{
  /**
	 * Static property to hold our singleton instance
	 *
	 */
	static $instance = false;

  const option_name = 'bookster_cpsw_sub';
  const option_sub_name = 'bookster_cpsw_sub_id';
  const version = '1.0';

  private function __construct() {
    # on activation
    register_activation_hook( __FILE__, array($this,'activate'));
      
    # uninstall
    register_uninstall_hook(__FILE__, array($this,'uninstall'));

    add_action( 'plugins_loaded', array($this,'pluginLoaded'), 10);

    if(is_admin()) {
      add_action( 'admin_init', array( $this, 'adminInit' ), 10 );
      add_action('admin_menu', array($this, 'adminMenu'), 9, 0);
      add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
    } else {
      add_action('wp_enqueue_scripts', array($this, 'enqueue'));
    }
  }

  /**
   * Init admin menus, interfaces, templates, and hooks
   */
  public function adminInit()
  {
    register_setting('bookster_cpsw', self::option_name);

    add_settings_section(
      'bookster_cpsw_sub_section',
      '',
      array($this,'settingsSection'),
      'bookster-cpsw'
    );

    add_settings_field(
      self::option_sub_name,
      'Bookster Subscription ID',
      array($this, 'settingsField'),
      'bookster-cpsw',
      'bookster_cpsw_sub_section',
      array(
        'label_for' => self::option_sub_name,
      )
    );
  }

  /**
   * Register shortcodes
   */
  public function pluginLoaded()
  {
    add_shortcode('bookster_cpsw', array($this,'runShortcode'));
  }

  /**
   * Run plugin activation tasks
   */
  public function activate()
  {
    update_option(self::option_name, array(self::option_sub_name => ''));
  }

  /**
   * Run plugin uninstall tasks
   */
  public function uninstall()
  {
    delete_option(self::option_name);
  }

  /**
   * Callback function for the widget shortcode.
   */
  public function runShortcode($atts, $content = null, $code)
  {
    //test sub id = 21265

    if ( is_feed() ) {
      return '[bookster_cpsw]';
    }
  
    $output = '';

    if ( 'bookster_cpsw' === $code ) {
      $atts = shortcode_atts(
        array(
          'sub_id' => '',
        ),
        $atts, 'bookster_cpsw'
      );
  
      $id = trim( $atts['sub_id'] );
  
      if($id != '') {

        $checkIn = new DateTime('now');
        $checkIn->modify('+1 day');
        $checkOut = new DateTime('now');
        $checkOut->modify('+3 days');

        $output .= '<div class="bookster-cpsw-form-container">';
        $output .= '<form id="bookster-cpsw-form" action="">';
        $output .= '<h5>Check Availability</h5>';
        $output .= '<div class="bookster-cpsw-form-group">';
        $output .= '<label for="bookster-cpsw-check-in">Check-in</label>';
        $output .= '<duet-date-picker value="'.$checkIn->format('Y-m-d').'" identifier="bookster-cpsw-check-in" name="bookster-cpsw-check-in" class="js-bookster-cpsw-date js-bookster-cpsw-check-in"></duet-date-picker>';
        $output .= '</div>';
        $output .= '<div class="bookster-cpsw-form-group">';
        $output .= '<label for="bookster-cpsw-check-out">Check-out</label>';
        $output .= '<duet-date-picker value="'.$checkOut->format('Y-m-d').'" identifier="bookster-cpsw-check-out" name="bookster-cpsw-check-out" class="js-bookster-cpsw-date js-bookster-cpsw-check-out"></duet-date-picker>';      
        $output .= '</div>';
        $output .= '<div class="bookster-cpsw-form-group">';
        $output .= '<label for="bookster-cpsw-party">Party size</label>';
        $output .= '<select id="bookster-cpsw-party" name="bookster-cpsw-party" class="js-bookster-cpsw-party">';
        $output .= '<option value="--">--</option>';
        for($i=1;$i<11;$i++) {
          $selected = ($i == 2) ? ' selected ' : '';
          $output .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
        }
        $output .= '</select>';
        $output .= '</div>';
        $output .= '<input type="hidden" value="'.$id.'" name="bookster-cpsw-subId" class="js-bookster-cpsw-subId" />';
        $output .= '<button type="submit" class="bookster-cpsw-submit js-bookster-cpsw-submit">Search</button>';
        $output .= '</form></div>';
      }
    }
  
    return $output;
  }

  /**
   * Enqueue frontend sttyles and scripts
   */
  public function enqueue()
  {
    wp_enqueue_style('bookster-cpsw-duet-css', plugin_dir_url( __FILE__ ) . 'includes/css/duet.css');
    wp_enqueue_script('bookster-cpsw-duet-js', 'https://cdn.jsdelivr.net/npm/@duetds/date-picker@1.4.0/dist/duet/duet.js');
    wp_enqueue_style('bookster-cpsw-form-css', plugin_dir_url( __FILE__ ) . 'includes/css/bookster-cpsw.css', array(), self::version, 'all');
    wp_enqueue_script('bookster-cpsw-form-js', plugin_dir_url( __FILE__ ) . 'includes/js/bookster-cpsw.js', array(), self::version, true);
  }

  /**
   * Setup admin menu items
   */
  public function adminMenu()
  {
    add_menu_page('Bookster CPS', 'Bookster CPS', 'manage_options', 'bookster-cpsw', array($this, 'adminPage'), 'dashicons-calendar');
  }

  /**
   * Enqueue admin scripts and styles
   */
  public function adminEnqueueScripts($hook)
  {
    if ( false === strpos( $hook, 'bookster-cpsw' )) {
      return;
    }

    wp_enqueue_style('bookster-cpsw-admin-css', plugin_dir_url( __FILE__ ) . 'admin/css/bookster-cpsw-admin.css', array(), self::version, 'all');

    wp_enqueue_script('bookster-cpsw-admin-js', plugin_dir_url( __FILE__ ) . 'admin/js/bookster-cpsw-admin.js', array(), self::version);
  }

  /** 
   * Callback function for settings section
   */
  public function settingsSection()
  {
    //do nothing
  }

  /**
   * Callback for settings field
   */
  public function settingsField($args)
  {
    $option = get_option(self::option_name);
    ?>

    <input id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo self::option_name ?>[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo $option[$args['label_for']] ?>" />

    <?php 
  }

  /**
   * Admin page for the plugin
   */
  public function adminPage()
  {
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }

    $option = get_option(self::option_name);

    if ( isset( $_GET['settings-updated'] ) ) {
      // add settings saved message with the class of "updated"
      add_settings_error( 'bookster_cpsw_messages', 'bookster_cpsw_message', 'Subscription ID Saved', 'updated' );
    }
  
    // show error/update messages
    settings_errors( 'bookster_cpsw_messages' );
   ?>
    <div class="wrap" id="bookster-cpsw-settings">
      <h1 class="wp-heading-inline">Bookster Cross Property Search</h1>

      <hr class="wp-header-end">
      <div class="notice notice-info">
        <p><strong>Important:</strong> You need a <a href="https://www.booksterhq.com/">Bookster</a> Subscription and to use your Subscription ID for this plugin to work.</p>
        <p>You can find your Bookster Subscription ID by logging into the Bookster dashboard, and clicking on Settings. Your web browser will have an address that looks like <br>https://app.booksterhq.com/subscriptions/<strong>123456789</strong>/edit. The number between "<em>subscriptions/</em>" and "<em>/edit</em>" is your Subscription ID. Which in our example is 123456789. Please note this for example purposes only and is not an actual Subscription ID.</p>
        <?php if($option[self::option_sub_name] == ''): ?><p>Add your Subscription ID and a shortcode will appear below.</p><?php endif; ?>
        <p>Add the Bookster Cross Property search form to your posts and pages by using the shortcode. You can learn how to use WordPress shortcodes <a href="https://wordpress.com/support/wordpress-editor/blocks/shortcode-block/">here</a>.</p> 
        <p>You can copy the shortcode by clicking on it.</p>
      </div>
      <?php if($option[self::option_sub_name] != ''): 
        $shortcode = '[bookster_cpsw sub_id="'.$option[self::option_sub_name].'"]';
      ?>

      <div class="inside shortcode-container">
        <p class="description">
          <label for="bookster-cpsw-shortcode">Copy this shortcode and paste it into your post, page, or text widget content:</label>
          <span class="shortcode wp-ui-highlight">
            <input type="text" id="bookster-cpsw-shortcode" readonly="readonly" class="large-text code" value="<?php echo esc_attr($shortcode) ?>" />
          </span>
        </p>
      </div>
      <?php endif; ?>
      <form action="options.php" method="post">
        <?php
        settings_fields( 'bookster_cpsw' );
        do_settings_sections( 'bookster-cpsw' );
        submit_button( 'Save Subscription ID' );
        ?>
      </form>
    <?php
  }

  /**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return Bookster_CPSW
	 */

   public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}
}

// Instantiate our class
$Bookster_CPSW = Bookster_CPSW::getInstance();