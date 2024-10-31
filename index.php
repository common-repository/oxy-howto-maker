<?php
/**
 * Plugin Name: OxyPlug - Howto Maker
 * Plugin URI: https://www.oxyplug.com/products/oxyplug-howto/
 * Description: Make HowTos with Structured Data
 * Version: 2.1.4
 * Author: OxyPlug
 * Author URI: https://www.oxyplug.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: oxy-howto-maker
 * Domain Path: /lang/
 * Requires at least: 4.9
 * Requires PHP: 7.4
 * Tested up to: 6.7
 *
 * Copyright 2024 OxyPlug
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Class OxyplugHowtoMaker
 */
class OxyplugHowtoMaker
{
  protected array $currencies = array();
  protected array $supply_titles = array();
  protected array $config;
  protected array $config_error;
  protected string $oxy_howto_maker_styles;
  protected string $oxy_howto_maker_custom_styles;

  public function __construct()
  {
    add_action('plugins_loaded', array($this, 'init_lang'));

    register_activation_hook(__FILE__, array($this, 'oxy_howto_maker_activate'));

    $css_dir = plugin_dir_path(__FILE__) . 'assets/css/';
    $this->oxy_howto_maker_styles = $css_dir;
    $this->oxy_howto_maker_custom_styles = $css_dir . 'custom/';
    $this->config = array(
      'step' => 9999, 'step_direction' => 9999,
      'step_tip' => 9999, 'supply' => 9999,
      'tool' => 9999, 'nofollow' => 'on',
    );

    add_action('init', array($this, 'fill_config_error'));
    add_action('admin_menu', array($this, 'add_menu'));

    add_action('init', array($this, 'save_settings'));
    add_action('add_meta_boxes', array($this, 'create_metabox'), 1);
    add_action('save_post', array($this, 'save_metaboxes'), 10, 3);
    add_action('admin_notices', array($this, 'admin_notices'));
    add_filter('the_content', array($this, 'customize_content'), 99999, 1);
    add_action('wp_enqueue_scripts', array($this, 'add_assets'));
    add_action('admin_enqueue_scripts', array($this, 'add_admin_assets'));
    add_filter('plugin_action_links', array($this, 'modify_plugin_actions'), 10, 2);
  }

  /**
   * @return string[]
   */
  private function get_currencies(): array
  {
    if (count($this->currencies) == 0) {
      $this->currencies = array(
        'AED' => 'د.إ', 'AFN' => '؋', 'ALL' => 'L', 'AMD' => 'դր.', 'ANG' => 'ƒ', 'AOA' => 'Kz',
        'ARS' => '$', 'AUD' => '$', 'AWG' => 'ƒ', 'AZN' => 'm', 'BAM' => 'КМ', 'BBD' => '$',
        'BDT' => '৳', 'BGN' => 'лв', 'BHD' => '.د.ب', 'BIF' => 'Fr', 'BMD' => '$', 'BND' => '$',
        'BOB' => 'Bs.', 'BRL' => 'R$', 'BSD' => '$', 'BTN' => 'Nu.', 'BWP' => 'P', 'BYR' => 'Br',
        'BZD' => '$', 'CAD' => '$', 'CDF' => 'Fr', 'CHF' => 'Fr', 'CLP' => '$', 'CNY' => '¥',
        'COP' => '$', 'CRC' => '₡', 'CUC' => '$', 'CUP' => '$', 'CVE' => '$', 'CZK' => 'Kč',
        'DJF' => 'Fr', 'DKK' => 'kr', 'DOP' => '$', 'DZD' => 'د.ج', 'EGP' => 'ج.م', 'ERN' => 'Nfk',
        'ETB' => 'Br', 'EUR' => '€', 'FJD' => '$', 'FKP' => '£', 'GBP' => '£', 'GEL' => 'ლ',
        'GHS' => '₵', 'GIP' => '£', 'GMD' => 'D', 'GNF' => 'Fr', 'GTQ' => 'Q', 'GYD' => '$',
        'HKD' => '$', 'HNL' => 'L', 'HRK' => 'kn', 'HTG' => 'G', 'HUF' => 'Ft', 'IDR' => 'Rp',
        'ILS' => '₪', 'INR' => '₹', 'IQD' => 'ع.د', 'IRR' => 'ريال', 'IRT' => 'تومان', 'ISK' => 'kr',
        'JMD' => '$', 'JOD' => 'د.ا', 'JPY' => '¥', 'KES' => 'Sh', 'KGS' => 'лв', 'KHR' => '៛',
        'KMF' => 'Fr', 'KPW' => '₩', 'KRW' => '₩', 'KWD' => 'د.ك', 'KYD' => '$', 'KZT' => '₸',
        'LAK' => '₭', 'LBP' => 'ل.ل', 'LKR' => 'Rs', 'LRD' => '$', 'LSL' => 'L', 'LTL' => 'Lt',
        'LYD' => 'ل.د', 'MAD' => 'د.م.', 'MDL' => 'L', 'MGA' => 'Ar', 'MKD' => 'ден', 'MMK' => 'Ks',
        'MNT' => '₮', 'MOP' => 'P', 'MRO' => 'UM', 'MUR' => '₨', 'MVR' => '.ރ', 'MWK' => 'MK',
        'MXN' => '$', 'MYR' => 'RM', 'MZN' => 'MT', 'NAD' => '$', 'NGN' => '₦', 'NIO' => 'C$',
        'NOK' => 'kr', 'NPR' => '₨', 'NZD' => '$', 'OMR' => 'ر.ع.', 'PAB' => 'B/.', 'PEN' => 'S/.',
        'PGK' => 'K', 'PHP' => '₱', 'PKR' => '₨', 'PLN' => 'zł', 'PYG' => '₲', 'QAR' => 'ر.ق',
        'RON' => 'L', 'RSD' => 'дин.', 'RUB' => 'руб.', 'RWF' => 'Fr', 'SAR' => 'ر.س', 'SBD' => '$',
        'SCR' => '₨', 'SDG' => '£', 'SEK' => 'kr', 'SGD' => '$', 'SHP' => '£', 'SLL' => 'Le',
        'SOS' => 'Sh', 'SRD' => '$', 'SSP' => '£', 'STD' => 'Db', 'SYP' => 'ل.س', 'SZL' => 'L',
        'THB' => '฿', 'TJS' => 'ЅМ', 'TMT' => 'm', 'TND' => 'د.ت', 'TOP' => 'T$', 'TRY' => '₺',
        'TTD' => '$', 'TWD' => '$', 'TZS' => 'Sh', 'UAH' => '₴', 'UGX' => 'Sh', 'USD' => '$',
        'UYU' => '$', 'UZS' => 'лв', 'VEF' => 'Bs F', 'VND' => '₫', 'VUV' => 'Vt', 'WST' => 'T',
        'XAF' => 'Fr', 'XCD' => '$', 'XOF' => 'Fr', 'XPF' => 'Fr', 'YER' => '﷼', 'ZAR' => 'R',
        'ZMW' => 'ZK',
      );
    }

    return $this->currencies;
  }

  /**
   * @return void
   */
  public function init_lang()
  {
    load_plugin_textdomain('oxy-howto-maker', false, basename(dirname(__FILE__)) . '/lang/');

    $this->supply_titles = array(
      'supply' => array(
        'title' => array(
          'singular' => __('Supply', 'oxy-howto-maker'),
          'plural' => __('Supplies', 'oxy-howto-maker'),
        ),
        'add' => __('Add Supply', 'oxy-howto-maker'),
      ),
      'material' => array(
        'title' => array(
          'singular' => __('Material', 'oxy-howto-maker'),
          'plural' => __('Materials', 'oxy-howto-maker'),
        ),
        'add' => __('Add Material', 'oxy-howto-maker'),
      ),
      'necessary_item' => array(
        'title' => array(
          'singular' => __('Necessary Item', 'oxy-howto-maker'),
          'plural' => __('Necessary Items', 'oxy-howto-maker'),
        ),
        'add' => __('Add Necessary Item', 'oxy-howto-maker'),
      ),
    );
  }

  /**
   * @return void
   */
  public function fill_config_error()
  {
    $this->config_error = array(
      'r_u_sure' => __('Are you sure?', 'oxy-howto-maker'),
      'settings_oxy_howto_maker_head' => __('Oxy HowTo Maker Settings', 'oxy-howto-maker'),
      'make_howto' => __('Make HowTo', 'oxy-howto-maker'),
      'lets_make_howtos' => __('Let\'s make some awesome howtos with Oxy Howto Maker :)', 'oxy-howto-maker'),
      'post_content_required' => __('The post content is required.', 'oxy-howto-maker'),
      'post_title_required' => __('The post title is required.', 'oxy-howto-maker'),
      'description_required' => __('The description is required.', 'oxy-howto-maker'),
      'total_time_validity' => __('The total time is invalid.', 'oxy-howto-maker'),
      'price_currency_validity' => __('The price currency is invalid.', 'oxy-howto-maker'),
      'estimated_cost_required' => __('The estimated cost is required.', 'oxy-howto-maker'),
      'estimated_cost_validity' => __('The estimated cost is invalid.', 'oxy-howto-maker'),
      'featured_image_required' => __('The featured image is required.', 'oxy-howto-maker'),
      'supply_name_validity' => __('The supply name %s is invalid.', 'oxy-howto-maker'),
      'supply_url_validity' => __('The supply link %s is invalid.', 'oxy-howto-maker'),
      'tool_name_validity' => __('The tool name %s is invalid.', 'oxy-howto-maker'),
      'tool_url_validity' => __('The tool link %s is invalid.', 'oxy-howto-maker'),
      'successfully_generated' => __('HowTo Successfully Generated.', 'oxy-howto-maker'),
      'Difficulty' => __('Difficulty', 'oxy-howto-maker'),
      'Step Count' => __('Step Count', 'oxy-howto-maker'),
      'Estimated Cost' => __('Estimated Cost', 'oxy-howto-maker'),
      'Supply' => __('Supply', 'oxy-howto-maker'),
      'Supplies' => __('Supplies', 'oxy-howto-maker'),
      'Material' => __('Material', 'oxy-howto-maker'),
      'Materials' => __('Materials', 'oxy-howto-maker'),
      'Necessary Item' => __('Necessary Item', 'oxy-howto-maker'),
      'Necessary Items' => __('Necessary Items', 'oxy-howto-maker'),
      'Tool' => __('Tool', 'oxy-howto-maker'),
      'Tools' => __('Tools', 'oxy-howto-maker'),
      'Steps' => __('Steps', 'oxy-howto-maker'),
      'Step' => __('Step', 'oxy-howto-maker'),
      'Tip' => __('Tip', 'oxy-howto-maker'),
      'Total Time' => __('Total Time', 'oxy-howto-maker'),
      'Day' => __('day', 'oxy-howto-maker'),
      'Hour' => __('hour', 'oxy-howto-maker'),
      'Minute' => __('minute', 'oxy-howto-maker'),
      'Ampersand' => __('&', 'oxy-howto-maker'),
    );
  }

  /**
   * @param $actions
   * @param $plugin_file
   * @return mixed
   */
  public function modify_plugin_actions($actions, $plugin_file)
  {
    if ($plugin_file == 'oxy-howto-maker/index.php') {
      $href = admin_url('admin.php?page=oxy-howto-maker');
      $actions['Settings'] = '<a href="' . esc_url($href) . '">' . esc_html('Settings') . '</a>';
    }

    return $actions;
  }

  /**
   * @return void
   */
  public function add_menu()
  {
    add_menu_page(
      'Oxy HowTo Maker',
      'Oxy HowTo Maker',
      'administrator',
      'oxy-howto-maker',
      array($this, 'oxy_howto_maker_settings_page'),
      'data:image/svg+xml;base64,' . base64_encode(file_get_contents(plugins_url('assets/svg/oxyplug-howto-icon.svg', __FILE__))),
      100
    );
  }

  /**
   * @return void
   */
  public function oxy_howto_maker_settings_page()
  {
    if (!empty($_GET['oxy_howto_maker_style'])) {
      $oxy_howto_maker_style = $_GET['oxy_howto_maker_style'];
      if (is_file($this->oxy_howto_maker_styles . $oxy_howto_maker_style) ||
        is_file($this->oxy_howto_maker_custom_styles . str_replace('custom_', '', $oxy_howto_maker_style))
      ) {
        update_option('_oxy_howto_maker_selected_style', $oxy_howto_maker_style);
      }
    } else {
      $oxy_howto_maker_style = $_GET['oxy_howto_maker_style'] = get_option('_oxy_howto_maker_selected_style', null);
    }
    ?>
    <div>
      <h1 class="oxy-howto-maker-head-title"><?php echo esc_html($this->config_error['settings_oxy_howto_maker_head']); ?></h1>
      <div class="oxy-howto-maker-each-section">
        <form action="admin.php" method="get">
          <input type="hidden" name="page" value="oxy-howto-maker">

          <select name="oxy_howto_maker_style" autocomplete="off">
            <option value=""><?php esc_html_e('Select Style', 'oxy-howto-maker') ?></option>
            <?php for ($s = 1; $s <= 100; $s++): ?>
              <?php
              $oxy_howto_maker_style_file = 'style-' . $s . '.css';
              $style = $this->oxy_howto_maker_styles . $oxy_howto_maker_style_file;
              ?>
              <?php if (is_file($style)): ?>
                <option
                    value="<?php echo esc_attr($oxy_howto_maker_style_file) ?>" <?php selected($oxy_howto_maker_style, $oxy_howto_maker_style_file) ?>>
                  <?php echo esc_html(sprintf(__('Style %s', 'oxy-howto-maker'), $s)) ?>
                </option>
              <?php endif; ?>
            <?php endfor; ?>

            <?php $oxy_howto_maker_custom_styles = glob($this->oxy_howto_maker_custom_styles . '*'); ?>
            <?php foreach ($oxy_howto_maker_custom_styles as $oxy_howto_maker_custom_style): ?>

              <?php $oxy_howto_maker_custom_style_file = basename($oxy_howto_maker_custom_style); ?>
              <?php $s2 = preg_replace("/\D/", '', $oxy_howto_maker_custom_style_file); ?>

              <option value="<?php echo esc_attr('custom_' . $oxy_howto_maker_custom_style_file) ?>"
                <?php selected($oxy_howto_maker_style, 'custom_' . $oxy_howto_maker_custom_style_file) ?>>
                <?php echo esc_html(sprintf(__('Custom Style %s', 'oxy-howto-maker'), $s2)) ?>
              </option>
            <?php endforeach; ?>

          </select>

          <button class="button"><?php esc_html_e('Select', 'oxy-howto-maker') ?></button>
          <br><br>
        </form>

        <form action="admin.php?page=oxy-howto-maker" method="post">
          <input type="hidden"
                 name="oxy_howto_maker_settings_nonce"
                 value="<?php echo esc_attr(wp_create_nonce('oxy_howto_maker_settings')); ?>"/>
          <input type="hidden"
                 name="oxy_howto_maker_style_file"
                 value="<?php echo esc_attr(isset($_GET['oxy_howto_maker_style']) ? sanitize_text_field($_GET['oxy_howto_maker_style']) : '') ?>"/>
          <?php
          $css_array = array();
          $style_content = null;
          if (isset($_GET['oxy_howto_maker_style'])) {
            $style = $this->oxy_howto_maker_styles . $_GET['oxy_howto_maker_style'];

            $css_array = $this->css_to_array($style);
          }
          ?>
          <?php if ($style_content): ?>
            <textarea id="oxy-howto-style-content" cols="100"
                      rows="20"><?php echo esc_textarea($style_content); ?></textarea>
            <br>
            <button class="button button-large button-primary"><?php esc_html_e('Edit', 'oxy-howto-maker') ?></button>
            <br><br>
          <?php endif; ?>

          <?php if (count($css_array)): ?>
            <?php foreach ($css_array as $css_key => $css_pair): ?>
              <?php foreach ($css_pair as $css_attr => $css_value): ?>
                <?php if ($css_attr == 'color'): ?>
                  <label>
                    <?php if ($css_key == '#oxy-howto-maker-data-made .dashicons'): ?>
                      <strong><?php esc_html_e('Icon Color', 'oxy-howto-maker'); ?></strong>
                      <input type="color"
                             name="icon_color"
                             value="<?php echo esc_attr($css_value) ?>">
                    <?php elseif ($css_key == '#oxy-howto-maker-data-made h2'): ?>
                      <strong><?php esc_html_e('Header 2 Color', 'oxy-howto-maker'); ?></strong>
                      <input type="color"
                             name="h2_color"
                             value="<?php echo esc_attr($css_value) ?>">
                    <?php elseif ($css_key == '#oxy-howto-maker-data-made a'): ?>
                      <strong><?php esc_html_e('Link Color', 'oxy-howto-maker'); ?></strong>
                      <input type="color"
                             name="a_color"
                             value="<?php echo esc_attr($css_value) ?>">
                    <?php endif; ?>
                  </label>
                <?php elseif ($css_attr == 'font-size'): ?>
                  <label class="oxy-font-size-attr">
                    <?php $size = preg_replace("/[^\d.]/", '', $css_value); ?>
                    <?php if ($css_key == '#oxy-howto-maker-data-made .oxy-howto-maker-supplies .oxy-howto-supplies-label'): ?>
                      <strong><?php esc_html_e('Supply Label Font Size', 'oxy-howto-maker'); ?></strong>
                      <input type="text"
                             name="supply_label_font_size"
                             value="<?php echo esc_attr($size) ?>">
                      <?php echo $this->get_font_sizes($css_value, 'supply_label_font_unit'); ?>
                    <?php elseif ($css_key == '#oxy-howto-maker-data-made .oxy-howto-maker-tools .oxy-howto-tools-label'): ?>
                      <strong><?php esc_html_e('Tool Label Font Size', 'oxy-howto-maker'); ?></strong>
                      <input type="text"
                             name="tool_label_font_size"
                             value="<?php echo esc_attr($size) ?>">
                      <?php echo $this->get_font_sizes($css_value, 'tool_label_font_unit'); ?>
                    <?php elseif ($css_key == '#oxy-howto-maker-data-made .oxy-howto-maker-supplies ul li'): ?>
                      <strong><?php esc_html_e('Supply List Item Font Size', 'oxy-howto-maker'); ?></strong>
                      <input type="text"
                             name="supply_li_font_size"
                             value="<?php echo esc_attr($size) ?>">
                      <?php echo $this->get_font_sizes($css_value, 'supply_li_font_unit'); ?>
                    <?php elseif ($css_key == '#oxy-howto-maker-data-made .oxy-howto-maker-tools ul li'): ?>
                      <strong><?php esc_html_e('Tool List Item Font Size', 'oxy-howto-maker'); ?></strong>
                      <input type="text"
                             name="tool_li_font_size"
                             value="<?php echo esc_attr($size) ?>">
                      <?php echo $this->get_font_sizes($css_value, 'tool_li_font_unit'); ?>
                    <?php elseif ($css_key == '#oxy-howto-maker-data-made .oxy-howto-maker-steps .oxy-howto-step-head'): ?>
                      <strong><?php esc_html_e('Step Header Font Size', 'oxy-howto-maker'); ?></strong>
                      <input type="text"
                             name="step_h2_font_size"
                             value="<?php echo esc_attr($size) ?>">
                      <?php echo $this->get_font_sizes($css_value, 'step_h2_font_unit'); ?>
                    <?php elseif ($css_key == '#oxy-howto-maker-data-made .oxy-howto-maker-steps .oxy-howto-direction-head'): ?>
                      <strong><?php esc_html_e('Direction Font Size', 'oxy-howto-maker'); ?></strong>
                      <input type="text"
                             name="direction_font_size"
                             value="<?php echo esc_attr($size) ?>">
                      <?php echo $this->get_font_sizes($css_value, 'direction_font_unit'); ?>
                    <?php elseif ($css_key == '#oxy-howto-maker-data-made .oxy-howto-maker-steps .oxy-howto-tip-head'): ?>
                      <strong><?php esc_html_e('Tip Font Size', 'oxy-howto-maker'); ?></strong>
                      <input type="text"
                             name="tip_font_size"
                             value="<?php echo esc_attr($size) ?>">
                      <?php echo $this->get_font_sizes($css_value, 'tip_font_unit'); ?>
                    <?php endif; ?>
                  </label>
                <?php endif; ?>
                <br><br>
              <?php endforeach; ?>
            <?php endforeach; ?>
            <br>
            <button class="button button-primary"
                    name="oxy_howto_maker_save_settings"
                    type="submit"><?php esc_html_e('Save', 'oxy-howto-maker') ?></button>
          <?php endif; ?>
        </form>
      </div>
    </div>
    <?php
  }

  /**
   * @param $css_value
   * @param $name
   * @return string
   */
  private function get_font_sizes($css_value, $name): string
  {
    $unit = strtolower(preg_replace("/(\d|\.)/", '', $css_value));

    return "<select name='" . $name . "' autocomplete='off'>
                    <option value='px' " . selected($unit, 'px', false) . ">px</option>
                    <option value='pt' " . selected($unit, 'pt', false) . ">pt</option>
                    <option value='rem' " . selected($unit, 'rem', false) . ">rem</option>
                    <option value='em' " . selected($unit, 'em', false) . ">em</option>
                    <option value='%' " . selected($unit, '%', false) . ">%</option>
                </select>";
  }

  /**
   * @return void
   */
  public function save_settings()
  {
    if (isset($_POST['oxy_howto_maker_save_settings']) && isset($_POST['oxy_howto_maker_settings_nonce'])) {
      if (wp_verify_nonce($_POST['oxy_howto_maker_settings_nonce'], 'oxy_howto_maker_settings')) {
        if (isset($_POST['oxy_howto_maker_style_file'])) {
          $input = str_replace('custom_', '', $_POST['oxy_howto_maker_style_file']);
          $oxy_howto_maker_style_file = $this->oxy_howto_maker_styles . $input;
          $oxy_howto_maker_custom_style_file = $this->oxy_howto_maker_custom_styles . $input;
          file_put_contents($oxy_howto_maker_custom_style_file, $this->modify_css($oxy_howto_maker_style_file));
          if (is_file($oxy_howto_maker_style_file)) {
            update_option('_oxy_howto_maker_selected_style', 'custom_' . $input);
          }
          set_transient('oxy_howto_maker_message', esc_html('Successfully updated.'), 10);
        }
      }
    }
  }

  /**
   * @param $file
   * @return array|string|string[]|null
   */
  private function modify_css($file)
  {
    $handle = fopen($file, 'r');
    $lines = array();

    $css_attrs = array(
      'icon_color' => array(
        'css_attr' => '#oxy-howto-maker-data-made .dashicons',
        'css_status' => false,
      ),
      'h2_color' => array(
        'css_attr' => '#oxy-howto-maker-data-made h2',
        'css_status' => false,
      ),
      'a_color' => array(
        'css_attr' => '#oxy-howto-maker-data-made a',
        'css_status' => false,
      ),
      'supply_label_font_size' => array(
        'css_attr' => '#oxy-howto-maker-data-made .oxy-howto-maker-supplies .oxy-howto-supplies-label',
        'css_status' => false,
      ),
      'tool_label_font_size' => array(
        'css_attr' => '#oxy-howto-maker-data-made .oxy-howto-maker-tools .oxy-howto-tools-label',
        'css_status' => false,
      ),
      'supply_li_font_size' => array(
        'css_attr' => '#oxy-howto-maker-data-made .oxy-howto-maker-supplies ul li',
        'css_status' => false,
      ),
      'tool_li_font_size' => array(
        'css_attr' => '#oxy-howto-maker-data-made .oxy-howto-maker-tools ul li',
        'css_status' => false,
      ),
      'step_h2_font_size' => array(
        'css_attr' => '#oxy-howto-maker-data-made .oxy-howto-maker-steps .oxy-howto-step-head',
        'css_status' => false,
      ),
      'direction_font_size' => array(
        'css_attr' => '#oxy-howto-maker-data-made .oxy-howto-maker-steps .oxy-howto-direction-head',
        'css_status' => false,
      ),
      'tip_font_size' => array(
        'css_attr' => '#oxy-howto-maker-data-made .oxy-howto-maker-steps .oxy-howto-tip-head',
        'css_status' => false,
      ),
    );

    $get = false;
    while (!feof($handle)) {
      $line = trim(fgets($handle));
      if ($line == '/*oxy-customize-start*/') {
        $get = true;
      } elseif ($line == '/*oxy-customize-end*/') {
        break;
      }

      if ($get === true) {
        foreach ($css_attrs as $key => &$value) {
          if (isset($_POST[$key])) {
            if (strrpos($key, 'font_size') !== false) {
              $key2 = str_replace('font_size', 'font_unit', $key);
              $input = $_POST[$key] . $_POST[$key2];
            } else {
              $input = $_POST[$key];
            }
            $returned = $this->modify_css_line($line, $value['css_attr'], $input, $value['css_status']);
            $line = $returned['line'];
            $value['css_status'] = $returned['status'];
          }
        }
        $lines[] = strpos($line, ';') ? "\t" . $line : $line;
      }
    }

    $lines = implode("\n", $lines) . "\n/*oxy-customize-end*/";
    fclose($handle);

    $style_content = file_get_contents($file);
    return preg_replace("/\/\*oxy-customize-start\*\/(.|\r?\n)*\/\*oxy-customize-end\*\//", $lines, $style_content);
  }

  /**
   * @param $line
   * @param $css_attr
   * @param $input
   * @param bool $status
   * @return array
   */
  private function modify_css_line($line, $css_attr, $input, bool $status = false): array
  {
    if (strpos($line, $css_attr) !== false) {
      $status = true;
    }
    if ($status) {
      if (strpos($line, ';') !== false) {
        $line = str_replace(array('/*', '*/'), '', $line);
        $line_exploded = explode(':', $line);
        if (count($line_exploded) == 2) {
          $line_exploded[1] = $input;
          $line = "\t" . implode(': ', $line_exploded) . ';';
          $status = false;
        }
      }
    }

    return array(
      'line' => $line,
      'status' => $status,
    );
  }

  /**
   * @param $file
   * @return array
   */
  private function css_to_array($file): array
  {
    $css_array = array();
    $file = str_replace('custom_', 'custom' . DIRECTORY_SEPARATOR, $file);
    if (is_file($file)) {
      $handle = fopen($file, 'r');
      $lines = '';
      $get = false;
      while (!feof($handle)) {
        $line = trim(fgets($handle));
        if ($line == '/*oxy-customize-start*/') {
          $get = true;
        } elseif ($line == '/*oxy-customize-end*/') {
          break;
        }

        if ($get === true) {
          $lines .= $line;
        }
      }
      $lines = str_replace('/*oxy-customize-start*/', '', $lines);

      $elements = explode('}', $lines);
      foreach ($elements as $element) {
        $a_name = explode('{', $element);
        $name = $a_name[0];
        $a_styles = explode(';', $element);
        $a_styles[0] = str_replace($name . '{', '', $a_styles[0]);
        $count = count($a_styles);
        for ($a = 0; $a < $count; $a++) {
          if ($a_styles[$a] != '') {
            if (strpos($a_styles[$a], '/*') !== false) {
              $a_styles[$a] = str_replace(array('/*', '*/'), '', $a_styles[$a]);
            }

            $a_key_value = explode(':', $a_styles[$a]);
            if (count($a_key_value) == 2) {
              $css_array[trim($name)][trim($a_key_value[0])] = trim($a_key_value[1]);
            }
          }
        }
      }

      fclose($handle);
    }

    return $css_array;
  }

  /**
   * @return void
   */
  public function create_metabox()
  {
    add_meta_box(
      'oxy_howto_maker_metabox',
      __('Howto Maker', 'oxy-howto-maker'),
      array($this, 'render_metabox'),
      'post',
      'normal', // normal: main column | side: sidebar
      'high'
    );

    add_meta_box(
      'oxy_howto_maker_side_metabox',
      __('Howto Maker', 'oxy-howto-maker'),
      array($this, 'render_side_metabox'),
      'post',
      'side',
      'high'
    );
  }

  /**
   * @param $post
   * @param $class
   * @return void
   */
  public function render_side_metabox($post, $class)
  {
    $oxy_howto_maker = get_post_meta($post->ID);
    $status = isset($oxy_howto_maker['_oxy_howto_status']) ? $oxy_howto_maker['_oxy_howto_status'][0] : 'off';
    ?>
    <div id="oxy-howto-maker-side-switcher">
      <?php $off_on = is_rtl() ? array('ON', 'OFF') : array('OFF', 'ON') ?>
      <span><?php esc_html_e($off_on[0], 'oxy-howto-maker') ?></span>
      <label class="switch">
        <input
          <?php echo esc_html($status == 'on' ? 'checked' : ''); ?>
            type="checkbox"
            autocomplete="off"
            name="oxy_howto_side_status">
        <span class="slider round"></span>
      </label>
      <span><?php esc_html_e($off_on[1], 'oxy-howto-maker') ?></span>
    </div>

    <button type="button"
            class="button button-primary button-large oxy-howto-maker-side-make-it oxy-howto-maker-button"
      <?php echo esc_html($status == 'on' ? '' : 'disabled'); ?>>
      <i class="dashicons dashicons-hammer"></i>
      <?php esc_html_e('Make It', 'oxy-howto-maker') ?>
    </button>
    <?php
  }

  /**
   * @param $post
   * @param $class
   * @return void
   */
  public function render_metabox($post, $class)
  {
    wp_nonce_field(basename(__FILE__), 'oxy_howto_maker_nonce');
    $oxy_howto_maker = get_post_meta($post->ID);
    $selected_supply_title = get_post_meta($post->ID, '_oxy_howto_selected_supply_title', true);
    if (empty($selected_supply_title)) {
      $selected_supply_title = 'supply';
    }
    if (isset($oxy_howto_maker['_oxy_howto_status'])) {
      $status = $oxy_howto_maker['_oxy_howto_status'][0];
      $difficulty = isset($oxy_howto_maker['_oxy_howto_difficulty']) ? $oxy_howto_maker['_oxy_howto_difficulty'][0] : '0';
      $estimated_cost_currency = $oxy_howto_maker['_oxy_howto_estimated_cost_currency'][0];
      $estimated_cost_value = $oxy_howto_maker['_oxy_howto_estimated_cost_value'][0];
      $description = stripcslashes($oxy_howto_maker['_oxy_howto_description'][0]);
      $description = preg_replace('!\\r?\\n!', '', $description);
      $description = json_decode($description, true);
      $supplies = isset($oxy_howto_maker['_oxy_howto_supply']) ? json_decode($oxy_howto_maker['_oxy_howto_supply'][0], true) : array();
      $tools = isset($oxy_howto_maker['_oxy_howto_tool']) ? json_decode($oxy_howto_maker['_oxy_howto_tool'][0], true) : array();
      $steps = stripcslashes($oxy_howto_maker['_oxy_howto_step'][0]);
      $steps = preg_replace('!\\r?\\n!', '', $steps);
      $steps = json_decode($steps, true);
      $day = $oxy_howto_maker['_oxy_howto_day'][0];
      $hour = $oxy_howto_maker['_oxy_howto_hour'][0];
      $minute = $oxy_howto_maker['_oxy_howto_minute'][0];
    } else {
      $estimated_cost_currency = get_option('oxy_howto_maker_currency');
      $status = 'off';
      $difficulty = '0';
      $estimated_cost_currency = $estimated_cost_currency ?? '';
      $estimated_cost_value = 0;
      $description = '';
      $supplies = array();
      $tools = array();
      $steps = array();
      $day = '';
      $hour = '';
      $minute = '';
    }

    ?>
    <div id="oxy-howto-maker-switcher">
      <?php $off_on = is_rtl() ? array('ON', 'OFF') : array('OFF', 'ON') ?>
      <span><?php esc_html_e($off_on[0], 'oxy-howto-maker') ?></span>
      <label class="switch">
        <input
          <?php echo esc_html($status == 'on' ? 'checked' : ''); ?>
            type="checkbox"
            autocomplete="off"
            name="oxy_howto_status">
        <span class="slider round"></span>
      </label>
      <span><?php esc_html_e($off_on[1], 'oxy-howto-maker') ?></span>
    </div>

    <hr>

    <div id="oxy-howto-maker-wrap" <?php echo $status == 'on' ? '' : 'style="display: none"' ?>>
      <button type="button"
              class="button button-primary button-large oxy-howto-maker-make-it oxy-howto-maker-button">
        <i class="dashicons dashicons-hammer"></i>
        <?php esc_html_e('Make It', 'oxy-howto-maker') ?>
      </button>

      <button class="button button-small fold-all oxy-howto-maker-button"
              style="display: none"
              type="button">
        <i class="dashicons dashicons-remove"></i>
        <?php esc_html_e('Fold All', 'oxy-howto-maker') ?>
      </button>
      <button class="button button-small unfold-all oxy-howto-maker-button"
              type="button">
        <i class="dashicons dashicons-insert"></i>
        <?php esc_html_e('Unfold All', 'oxy-howto-maker') ?>
      </button>

      <div class="oxy-d-inline-block">
        <label><?php esc_html_e('Goto Step #', 'oxy-howto-maker') ?></label>
        <select class="oxy-scroll-to" autocomplete="off">
          <option value=""><?php esc_html_e('Select Step', 'oxy-howto-maker') ?></option>
        </select>
      </div>

      <h3 class="oxy-howto-maker-toggler wp-ui-text-primary" data-target="#oxy-howto-maker-general-section">
        <i class="dashicons dashicons-arrow-down-alt2"></i>
        <?php esc_html_e('General', 'oxy-howto-maker') ?>
      </h3>
      <div id="oxy-howto-maker-general-section" style="display: none">
        <!-- difficulty -->
        <p class="oxy-w-100">
          <label class="oxy-howto-maker-row-title">
            <?php echo $this->config_error['Difficulty']; ?>
          </label>
          <select name="oxy_data[difficulty]" class="postbox">
            <option value="0"><?php esc_html_e('Select', 'oxy-howto-maker') ?></option>
            <option value="1" <?php selected($difficulty, '1') ?>>1</option>
            <option value="2" <?php selected($difficulty, '2') ?>>2</option>
            <option value="3" <?php selected($difficulty, '3') ?>>3</option>
          </select>
        </p>

        <!-- estimatedCost -->
        <p class="oxy-w-100">
          <label class="oxy-howto-maker-row-title">
            <?php esc_html_e('Price Currency', 'oxy-howto-maker') ?>
          </label>
          <select name="oxy_data[estimatedCost][currency]">
            <option value=""><?php esc_html_e('Select Currency', 'oxy-howto-maker'); ?></option>
            <?php foreach ($this->get_currencies() as $currency => $symbol): ?>
              <option value="<?php echo esc_attr($currency) ?>"
                      data-symbol="<?php echo esc_attr($symbol) ?>"
                <?php selected($estimated_cost_currency, $currency) ?>>
                <?php echo esc_html($currency) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </p>

        <p class="oxy-w-100">
          <label class="oxy-howto-maker-row-title">
            <?php echo $this->config_error['Estimated Cost']; ?>
          </label>
          <input class="oxy-price"
                 autocomplete="off"
                 type="text"
                 name="oxy_data[estimatedCost][value]"
                 value="<?php echo esc_attr($estimated_cost_value) ?>"/>
        </p>

        <!-- Description -->
        <p class="oxy-w-100">
          <label class="oxy-howto-maker-row-title">
            <?php esc_html_e('Description', 'oxy-howto-maker') ?>
          </label>
          <textarea class="init-tinymce"
                    id="<?php echo esc_attr('oxy_data-description') ?>"
                    name="<?php echo esc_attr('oxy_data[description]') ?>"><?php echo esc_textarea($description); ?></textarea>
        </p>
      </div>

      <h3 class="oxy-howto-maker-toggler wp-ui-text-primary" data-target="#oxy-howto-maker-supply-section">
        <i class="dashicons dashicons-arrow-down-alt2"></i>
        <?php echo $this->config_error['Supply']; ?>
      </h3>

      <div id="oxy-howto-maker-supply-section" style="display: none">
        <!-- Supply -->
        <select name="oxy_data[supply][title]" autocomplete="off">
          <?php foreach ($this->supply_titles as $key => $values): ?>
            <option value="<?php echo esc_attr($key) ?>"
                    data-title="<?php echo esc_attr($values['title']['singular']); ?>"
                    data-add="<?php echo esc_attr($values['add']); ?>"
              <?php selected($selected_supply_title, $key) ?>>
              <?php echo esc_html($values['title']['plural']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <br>
        <button type="button"
                id="oxy-howto-maker-add-supply"
                class="button button-small oxy-howto-maker-button">
          <?php esc_html_e($this->supply_titles[$selected_supply_title]['add'], 'oxy-howto-maker') ?>
        </button>
        <p class="oxy-howto-maker-add-supply oxy-w-100">
          <strong class="oxy-d-none oxy-number">
            <?php esc_html_e($this->supply_titles[$selected_supply_title]['title']['singular'], 'oxy-howto-maker'); ?> 0
          </strong>
          <input class="oxy-d-none"
                 type="text"
                 name="oxy_data[supply][0][name]"
                 value=""
                 placeholder="<?php esc_attr_e('Name', 'oxy-howto-maker') ?>"/>
          <button class="delete-supply button button-danger-outline oxy-d-none oxy-howto-maker-button"
                  type="button">
            <i class="dashicons dashicons-trash"></i>
          </button>
          <input class="oxy-d-none oxy-d-block-later ltr"
                 type="text"
                 name="oxy_data[supply][0][url]"
                 value=""
                 placeholder="<?php esc_attr_e('Link', 'oxy-howto-maker') ?>"/>
          <?php if ($this->config['nofollow'] == 'on'): ?>
            <label class="oxy-d-none">
              <input type="checkbox" name="oxy_data[supply][0][nofollow]">
              <?php esc_html_e('Nofollow', 'oxy-howto-maker'); ?>
            </label>
          <?php endif; ?>
        </p>

        <?php foreach ($supplies as $number => $supply): ?>
          <p class="oxy-howto-maker-add-supply oxy-w-100">
            <strong class="oxy-number">
              <?php esc_html_e($this->supply_titles[$selected_supply_title]['title']['singular'], 'oxy-howto-maker'); ?>
              <?php echo esc_html($number + 1); ?>
            </strong>
            <input type="text"
                   name="<?php echo esc_attr('oxy_data[supply][' . ($number + 1) . '][name]'); ?>"
                   value="<?php echo esc_attr($supply['name']) ?>"
                   placeholder="<?php esc_attr_e('Name', 'oxy-howto-maker') ?>">
            <button class="delete-supply button button-danger-outline oxy-howto-maker-button" type="button">
              <i class="dashicons dashicons-trash"></i>
            </button>
            <input class="ltr oxy-d-block"
                   type="text"
                   name="<?php echo esc_attr('oxy_data[supply][' . ($number + 1) . '][url]'); ?>"
                   value="<?php echo esc_attr($supply['url'] != '#' && $supply['url'] != '' && $supply['url'] != 'http://' ? $supply['url'] : '') ?>"
                   placeholder="<?php esc_attr_e('Link', 'oxy-howto-maker') ?>">
            <?php if ($this->config['nofollow'] == 'on'): ?>
              <label>
                <input <?php echo esc_html(isset($supply['nofollow']) && $supply['nofollow'] == 'on' ? 'checked' : '') ?>
                    type="checkbox"
                    name="<?php echo esc_attr('oxy_data[supply][' . ($number + 1) . '][nofollow]'); ?>">
                <?php esc_html_e('Nofollow', 'oxy-howto-maker'); ?>
              </label>
            <?php endif; ?>
          </p>
        <?php endforeach; ?>
      </div>

      <h3 class="oxy-howto-maker-toggler wp-ui-text-primary" data-target="#oxy-howto-maker-tool-section">
        <i class="dashicons dashicons-arrow-down-alt2"></i>
        <?php echo $this->config_error['Tool']; ?>
      </h3>
      <div id="oxy-howto-maker-tool-section" style="display: none">
        <!-- Tool -->
        <button type="button" id="oxy-howto-maker-add-tool" class="button button-small oxy-howto-maker-button">
          <?php esc_html_e('Add Tool', 'oxy-howto-maker') ?>
        </button>
        <p class="oxy-howto-maker-add-tool oxy-w-100">
          <strong class="oxy-d-none oxy-number"><?php esc_html_e('Tool', 'oxy-howto-maker') ?> 0</strong>
          <input class="oxy-d-none"
                 type="text"
                 name="oxy_data[tool][0][name]"
                 value=""
                 placeholder="<?php esc_attr_e('Name', 'oxy-howto-maker') ?>"/>
          <button class="delete-tool button button-danger-outline oxy-d-none oxy-howto-maker-button"
                  type="button">
            <i class="dashicons dashicons-trash"></i>
          </button>
          <input class="oxy-d-none oxy-d-block-later ltr"
                 type="text"
                 name="oxy_data[tool][0][url]"
                 value=""
                 placeholder="<?php esc_attr_e('Link', 'oxy-howto-maker') ?>"/>
          <?php if ($this->config['nofollow'] == 'on'): ?>
            <label class="oxy-d-none">
              <input type="checkbox" name="oxy_data[tool][0][nofollow]">
              <?php esc_html_e('Nofollow', 'oxy-howto-maker'); ?>
            </label>
          <?php endif; ?>
        </p>

        <?php foreach ($tools as $number => $tool): ?>
          <p class="oxy-howto-maker-add-tool oxy-w-100">
            <strong class="oxy-number">
              <?php esc_html_e('Tool', 'oxy-howto-maker'); ?>
              <?php echo esc_html($number + 1); ?>
            </strong>
            <input
                type="text"
                name="<?php echo esc_attr('oxy_data[tool][' . ($number + 1) . '][name]'); ?>"
                value="<?php echo esc_attr($tool['name']) ?>"
                placeholder="<?php esc_attr_e('Name', 'oxy-howto-maker') ?>">
            <button class="delete-tool button button-danger-outline oxy-howto-maker-button" type="button">
              <i class="dashicons dashicons-trash"></i>
            </button>
            <input class="ltr oxy-d-block"
                   type="text"
                   name="<?php echo esc_attr('oxy_data[tool][' . ($number + 1) . '][url]'); ?>"
                   value="<?php echo esc_attr($tool['url'] != '#' && $tool['url'] != '' && $tool['url'] != 'http://' ? $tool['url'] : '') ?>"
                   placeholder="<?php esc_attr_e('Link', 'oxy-howto-maker') ?>">
            <?php if ($this->config['nofollow'] == 'on'): ?>
              <label>
                <input <?php echo esc_html(isset($tool['nofollow']) && $tool['nofollow'] == 'on' ? 'checked' : '') ?>
                    type="checkbox"
                    name="<?php echo esc_attr('oxy_data[tool][' . ($number + 1) . '][nofollow]'); ?>">
                <?php esc_html_e('Nofollow', 'oxy-howto-maker'); ?>
              </label>
            <?php endif; ?>
          </p>
        <?php endforeach; ?>
      </div>

      <h3 class="oxy-howto-step-header oxy-howto-maker-toggler wp-ui-text-primary"
          data-target="#oxy-howto-maker-step-section">
        <i class="dashicons dashicons-arrow-down-alt2"></i>
        <?php echo $this->config_error['Steps']; ?>
      </h3>
      <div id="oxy-howto-maker-step-section" style="display: none">
        <!-- Step -->
        <?php if ($steps): ?>
          <?php $number = 0; ?>
          <?php foreach ($steps as $step): ?>
            <?php if (!empty(trim($step['name']))): ?>
              <?php
              $home = get_home_path();
              $home = substr($home, 0, strlen($home) - 1);
              $step_image_path = $home . $step['image'];
              if (is_file($step_image_path) && !is_dir($step_image_path)) {
                $step_image = isset($step['image']) ? get_home_url(null, $step['image']) : '';
                $step_image_id = $image_alt = $image_srcset = '';
                $image_width = $image_height = 0;
              } else {
                $step_image = '';
              }

              if ($step_image) {
                $step_image_id = attachment_url_to_postid($step_image);
                if ($step_image_id) {
                  $image_src_full = wp_get_attachment_image_src($step_image_id, 'full');
                  $image_width = $image_src_full[1];
                  $image_height = $image_src_full[2];

                  $image_srcset = $image_src_full[0] . ' ' . $image_width . 'w, ';

                  $image_src_large = wp_get_attachment_image_src($step_image_id, 'large');
                  if ($image_src_large) {
                    $image_srcset .= $image_src_large[0] . ' ' . $image_src_large[1] . 'w, ';
                  }

                  $image_src_medium = wp_get_attachment_image_src($step_image_id, 'medium');
                  if ($image_src_medium) {
                    $image_srcset .= $image_src_medium[0] . ' ' . $image_src_medium[1] . 'w, ';
                  }

                  $image_src_thumbnail = wp_get_attachment_image_src($step_image_id);
                  if ($image_src_thumbnail) {
                    $image_srcset .= $image_src_thumbnail[0] . ' ' . $image_src_thumbnail[1] . 'w, ';
                  }

                  $image_srcset = rtrim($image_srcset, ', ');

                  $image_alt = get_post_meta($step_image_id, '_wp_attachment_image_alt', true);
                  if (trim($image_alt) == '') {
                    $image_alt = $step['name'];
                  }

                  $image_caption = trim(wp_get_attachment_caption($step_image_id));
                }
              }
              ?>
              <div class="oxy-howto-maker-add-step">
                <h3 class="oxy-howto-step-header oxy-howto-maker-toggler"
                    data-step="<?php echo esc_attr($number + 1) ?>"
                    data-target="<?php echo esc_attr('#oxy-howto-maker-step' . ($number + 1) . '-section'); ?>">
                  <i class="dashicons dashicons-arrow-down-alt2"></i>
                  <span><?php echo $this->config_error['Step'] . ' ' . ($number + 1); ?></span>
                </h3>
                <div id="<?php echo esc_attr('oxy-howto-maker-step' . ($number + 1) . '-section'); ?>"
                     style="display: none">
                  <button
                      class="delete-step button button-danger-outline oxy-howto-maker-button <?php echo esc_html($number == 0 ? 'oxy-d-none' : '') ?>"
                      type="button">
                    <i class="dashicons dashicons-trash"></i>
                    <?php echo esc_html(sprintf(__('Delete Step %s', 'oxy-howto-maker'), $number + 1)); ?>
                  </button>

                  <p class="oxy-w-100">
                    <label class="oxy-howto-maker-row-title">
                      <?php esc_html_e('Step Name', 'oxy-howto-maker') ?>
                    </label>
                    <input class="oxy-data-step-name"
                           type="text"
                           name="<?php echo esc_attr('oxy_data[step][' . $number . '][name]') ?>"
                           value="<?php echo esc_attr($step['name']) ?>"/>
                  </p>

                  <?php $d = $t = 0; ?>
                  <?php foreach ($step['directions_and_tips'] as $dtn => $dt): ?>
                    <?php if (isset($dt['direction'])): ?>
                      <div class="oxy-howto-maker-add-stepdivtext">
                        <p class="<?php echo esc_attr('oxy-howto-maker-add-steptext' . ($d + 1)) ?> oxy-w-100">
                          <strong class="oxy-number">
                            <?php echo esc_html(__('Step Direction', 'oxy-howto-maker') . ' ' . ($d + 1)); ?>
                          </strong>
                          <textarea class="init-tinymce"
                                    id="<?php echo esc_attr('oxy_data-step-' . $number . '-directions_and_tips-' . $dtn . '-direction') ?>"
                                    name="<?php echo esc_attr('oxy_data[step][' . $number . '][directions_and_tips][' . $dtn . '][direction]') ?>"
                                    placeholder="<?php esc_attr_e('Step Direction', 'oxy-howto-maker') ?>"><?php echo esc_textarea($dt['direction']); ?></textarea>
                          <button
                              class="delete-direction button button-danger-outline oxy-howto-maker-button <?php echo esc_attr($d == '0' ? 'oxy-d-none' : '') ?>"
                              type="button">
                            <i class="dashicons dashicons-trash"></i>
                            <?php echo esc_html(sprintf(__('Delete Direction %s', 'oxy-howto-maker'), ($d + 1))); ?>
                          </button>
                        </p>
                        <button type="button"
                                class="oxy-howto-maker-add-steptext1 button button-small oxy-howto-maker-button">
                          <?php esc_html_e('Add Another Direction', 'oxy-howto-maker') ?>
                        </button>

                        <button type="button"
                                class="oxy-howto-maker-add-steptip1 button button-small oxy-howto-maker-button">
                          <?php esc_html_e('Add Tip', 'oxy-howto-maker') ?>
                        </button>
                      </div>
                      <?php $d++; ?>
                    <?php elseif (isset($dt['tip'])): ?>
                      <div class="oxy-howto-maker-add-stepdivtip">
                        <p class="<?php echo esc_attr('oxy-howto-maker-add-steptip' . ($t + 1)) ?> oxy-w-100">
                          <strong class="oxy-number">
                            <?php echo esc_html(sprintf(__('Tip %s', 'oxy-howto-maker'), ($t + 1))); ?>
                          </strong>
                          <textarea class="init-tinymce"
                                    id="<?php echo esc_attr('oxy_data-step-' . $number . '-directions_and_tips-' . $dtn . '-tip'); ?>"
                                    name="<?php echo esc_attr('oxy_data[step][' . $number . '][directions_and_tips][' . $dtn . '][tip]'); ?>"
                                    placeholder="<?php esc_attr_e('Step Tip', 'oxy-howto-maker') ?>"><?php echo esc_textarea($dt['tip']); ?></textarea>
                          <button class="delete-tip button button-danger-outline oxy-howto-maker-button"
                                  type="button">
                            <i class="dashicons dashicons-trash"></i>
                            <?php echo esc_html(sprintf(__('Delete Tip %s', 'oxy-howto-maker'), ($t + 1))); ?>
                          </button>
                        </p>
                        <button type="button"
                                class="oxy-howto-maker-add-steptext1 button button-small oxy-howto-maker-button">
                          <?php esc_html_e('Add Another Direction', 'oxy-howto-maker') ?>
                        </button>

                        <button type="button"
                                class="oxy-howto-maker-add-steptip1 button button-small oxy-howto-maker-button">
                          <?php esc_html_e('Add Tip', 'oxy-howto-maker') ?>
                        </button>
                      </div>
                      <?php $t++; ?>
                    <?php endif; ?>
                  <?php endforeach; ?>

                  <?php if ($t == 0): ?>
                    <div class="oxy-howto-maker-add-stepdivtip" style="display: none">
                      <p class="oxy-howto-maker-add-steptip1 oxy-w-100">
                        <strong class="oxy-number">
                          <?php echo esc_html(sprintf(__('Tip %s', 'oxy-howto-maker'), 1)); ?>
                        </strong>
                        <textarea class="original"
                                  id="oxy_data-step-0-directions_and_tips-0-tip"
                                  name="oxy_data[step][0][directions_and_tips][0][tip]"
                                  placeholder="<?php esc_html_e('Step Tip', 'oxy-howto-maker') ?>"></textarea>
                        <button class="delete-tip button button-danger-outline oxy-howto-maker-button"
                                type="button">
                          <i class="dashicons dashicons-trash"></i>
                          <?php echo esc_attr(sprintf(__('Delete Tip %s', 'oxy-howto-maker'), 1)); ?>
                        </button>
                      </p>
                      <button type="button"
                              class="oxy-howto-maker-add-steptext1 button button-small oxy-howto-maker-button">
                        <?php esc_html_e('Add Another Direction', 'oxy-howto-maker') ?>
                      </button>

                      <button type="button"
                              class="oxy-howto-maker-add-steptip1 button button-small oxy-howto-maker-button">
                        <?php esc_html_e('Add Tip', 'oxy-howto-maker') ?>
                      </button>
                    </div>
                  <?php endif; ?>

                  <p>
                    <label for="oxy-howto-maker-step-image" class="oxy-howto-maker-row-title">
                      <?php esc_html_e('Step Image', 'oxy-howto-maker') ?>
                    </label>
                    <img loading="lazy"
                         class="<?php echo esc_attr('oxy-howto-maker-step-image-img' . ($number + 1)); ?>"
                         src="<?php echo esc_attr($step_image); ?>">
                    <br>
                    <button type="button"
                            class="<?php echo esc_attr('oxy-howto-maker-step-image' . ($number + 1)); ?> button">
                      <?php echo esc_html(sprintf(__('Upload Step %s Image', 'oxy-howto-maker'), $number + 1)); ?>
                    </button>
                    <button class="oxy-howto-maker-delete-step-image button button-danger-outline"
                            type="button">
                      <i class="dashicons dashicons-trash"></i>
                      <?php echo esc_html(sprintf(__('Delete Step %s Image', 'oxy-howto-maker'), $number + 1)); ?>
                    </button>
                    <input class="<?php echo esc_attr('oxy-howto-maker-step-image-input' . ($number + 1)); ?>"
                           type="hidden"
                           name="<?php echo esc_attr('oxy_data[step][' . $number . '][image][url]'); ?>"
                           value="<?php echo esc_attr($step_image) ?>"
                           autocomplete="off"
                           placeholder="<?php esc_attr_e('link', 'oxy-howto-maker') ?>"
                           data-image-id="<?php echo esc_attr($step_image_id) ?>"
                           data-image-alt="<?php echo esc_attr($image_alt) ?>"
                           data-image-width="<?php echo esc_attr($image_width) ?>"
                           data-image-height="<?php echo esc_attr($image_height) ?>"
                           data-image-srcset="<?php echo esc_attr($image_srcset) ?>"
                           data-image-caption="<?php echo esc_attr($image_caption) ?>"/>
                  </p>

                  <button type="button"
                          class="button button-small oxy-howto-maker-button oxy-howto-maker-add-step-x">
                    <?php echo esc_html(__('Add Step', 'oxy-howto-maker') . ' ' . ($number + 2)); ?>
                  </button>
                </div>
              </div>
              <?php $number++; endif; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="oxy-howto-maker-add-step">
            <h3 class="oxy-howto-step-header oxy-howto-maker-toggler" data-step="1"
                data-target="#oxy-howto-maker-step1-section">
              <i class="dashicons dashicons-arrow-up-alt2"></i>
              <span><?php echo $this->config_error['Step'] . ' 1'; ?></span>
            </h3>
            <div id="oxy-howto-maker-step1-section">
              <button class="delete-step button button-danger-outline oxy-d-none oxy-howto-maker-button"
                      type="button">
                <i class="dashicons dashicons-trash"></i>
                <?php echo esc_html(sprintf(__('Delete Step %s', 'oxy-howto-maker'), 1)); ?>
              </button>

              <p class="oxy-w-100">
                <label class="oxy-howto-maker-row-title">
                  <?php esc_html_e('Step Name', 'oxy-howto-maker') ?>
                </label>
                <input class="oxy-data-step-name"
                       type="text"
                       name="oxy_data[step][0][name]"
                       value=""/>
              </p>

              <div class="oxy-howto-maker-add-stepdivtext">
                <p class="oxy-howto-maker-add-steptext1 oxy-w-100">
                  <strong class="oxy-number">
                    <?php echo esc_html(__('Step Direction', 'oxy-howto-maker') . ' 1'); ?>
                  </strong>
                  <textarea class="init-tinymce original"
                            id="oxy_data-step-0-directions_and_tips-0-direction"
                            name="oxy_data[step][0][directions_and_tips][0][direction]"
                            placeholder="<?php esc_html_e('Step Direction', 'oxy-howto-maker') ?>"></textarea>
                  <button class="delete-direction button button-danger-outline oxy-d-none oxy-howto-maker-button"
                          type="button">
                    <i class="dashicons dashicons-trash"></i>
                    <?php echo esc_html(sprintf(__('Delete Direction %s', 'oxy-howto-maker'), 1)); ?>
                  </button>
                </p>
                <button type="button"
                        class="oxy-howto-maker-add-steptext1 button button-small oxy-howto-maker-button">
                  <?php esc_html_e('Add Another Direction', 'oxy-howto-maker') ?>
                </button>

                <button type="button"
                        class="oxy-howto-maker-add-steptip1 button button-small oxy-howto-maker-button">
                  <?php esc_html_e('Add Tip', 'oxy-howto-maker') ?>
                </button>
              </div>
              <div class="oxy-howto-maker-add-stepdivtip" style="display: none">
                <p class="oxy-howto-maker-add-steptip1 oxy-w-100">
                  <strong class="oxy-number">T1</strong>
                  <textarea class="original"
                            id="oxy_data-step-0-directions_and_tips-0-tip"
                            name="oxy_data[step][0][directions_and_tips][0][tip]"
                            placeholder="<?php esc_html_e('Step Tip', 'oxy-howto-maker') ?>"></textarea>
                  <button class="delete-tip button button-danger-outline oxy-howto-maker-button"
                          type="button">
                    <i class="dashicons dashicons-trash"></i>
                    <?php esc_html_e('Delete Tip', 'oxy-howto-maker') ?>
                  </button>
                </p>
                <button type="button"
                        class="oxy-howto-maker-add-steptext1 button button-small oxy-howto-maker-button">
                  <?php esc_html_e('Add Another Direction', 'oxy-howto-maker') ?>
                </button>

                <button type="button"
                        class="oxy-howto-maker-add-steptip1 button button-small oxy-howto-maker-button">
                  <?php esc_html_e('Add Tip', 'oxy-howto-maker') ?>
                </button>
              </div>

              <p>
                <label for="oxy-howto-maker-step-image" class="oxy-howto-maker-row-title">
                  <?php esc_html_e('Step Image', 'oxy-howto-maker') ?>
                </label>
                <img class="oxy-howto-maker-step-image-img1" src="">
                <br>
                <button type="button" class="oxy-howto-maker-step-image1 button">
                  <?php echo esc_html(sprintf(__('Upload Step %s Image', 'oxy-howto-maker'), 1)); ?>
                </button>
                <button class="oxy-howto-maker-delete-step-image button button-danger-outline oxy-d-none"
                        type="button">
                  <i class="dashicons dashicons-trash"></i>
                  <?php echo esc_html(sprintf(__('Delete Step %s Image', 'oxy-howto-maker'), 1)); ?>
                </button>
                <input class="oxy-howto-maker-step-image-input1"
                       type="hidden"
                       name="oxy_data[step][0][image][url]"
                       value=""
                       autocomplete="off"
                       placeholder="<?php esc_attr_e('Url', 'oxy-howto-maker') ?>"/>
              </p>

              <button type="button"
                      class="button button-small oxy-howto-maker-button oxy-howto-maker-add-step-x">
                <?php echo esc_html(__('Add Step', 'oxy-howto-maker') . ' 2'); ?>
              </button>

            </div>
          </div>
        <?php endif; ?>
      </div>

      <h3 class="oxy-howto-step-header oxy-howto-maker-toggler wp-ui-text-primary"
          data-target="#oxy-howto-maker-time-section">
        <i class="dashicons dashicons-arrow-down-alt2"></i>
        <?php echo $this->config_error['Total Time']; ?>
      </h3>
      <div id="oxy-howto-maker-time-section" style="display: none">
        <!-- Total Time -->
        <p id="oxy-howto-maker-total-time">
          <label>
            <strong class="oxy-d-block"><?php esc_html_e('Day', 'oxy-howto-maker'); ?></strong>
            <input type="number"
                   name="oxy_data[day]"
                   value="<?php echo esc_attr($day) ?>"
                   autocomplete="off"
                   min="0"/>
          </label>
          <label>
            <strong class="oxy-d-block"><?php esc_html_e('Hour', 'oxy-howto-maker'); ?></strong>
            <input type="number"
                   name="oxy_data[hour]"
                   value="<?php echo esc_attr($hour) ?>"
                   autocomplete="off"
                   min="0"/>
          </label>
          <label>
            <strong class="oxy-d-block"><?php esc_html_e('Minute', 'oxy-howto-maker'); ?></strong>
            <input type="number"
                   name="oxy_data[minute]"
                   value="<?php echo esc_attr($minute) ?>"
                   autocomplete="off"
                   min="0"/>
          </label>
        </p>
      </div>

      <button class="button button-small fold-all oxy-howto-maker-button"
              style="display: none"
              type="button">
        <i class="dashicons dashicons-remove"></i>
        <?php esc_html_e('Fold All', 'oxy-howto-maker') ?>
      </button>
      <button class="button button-small unfold-all oxy-howto-maker-button"
              type="button">
        <i class="dashicons dashicons-insert"></i>
        <?php esc_html_e('Unfold All', 'oxy-howto-maker') ?>
      </button>

      <div class="oxy-d-inline-block">
        <label><?php esc_html_e('Goto Step #', 'oxy-howto-maker') ?></label>
        <select class="oxy-scroll-to" autocomplete="off">
          <option value=""><?php esc_html_e('Select Step', 'oxy-howto-maker') ?></option>
        </select>
      </div>

      <button type="button"
              class="button button-primary button-large oxy-howto-maker-make-it oxy-howto-maker-button">
        <i class="dashicons dashicons-hammer"></i>
        <?php esc_html_e('Make It', 'oxy-howto-maker') ?>
      </button>
    </div>

    <?php
  }

  /**
   * @return void
   */
  public function admin_notices()
  {
    if ($errors = get_transient('oxy_howto_maker_errors')) { ?>
      <div class="error">
      <?php foreach ($errors->get_error_messages() as $error): ?>
        <p><?php echo esc_html($error) ?></p>
      <?php endforeach; ?>
      </div><?php

      delete_transient('oxy_howto_maker_errors');
    }

    if ($message = get_transient('oxy_howto_maker_message')): ?>
      <div id="message" class="updated notice notice-success is-dismissible">
        <p><?php echo esc_html($message); ?></p>
      </div>
    <?php endif;
  }

  /**
   * @param $string
   * @return string
   */
  private function sanitize_for_schema($string): string
  {
    $string = str_replace('<', ' <', $string);
    $string = strip_tags($string);
    $string = str_replace(array('&nbsp;', '&lt;', '&gt;', '&sol;', '&quot;', '&apos;', '&amp;', '&copy;', '&reg;', '&deg;', '&laquo;', '&raquo;'), ' ', $string);
    $string = str_replace('\r\n', '', $string);
    //$string = preg_replace("/[^ ا-یa-zA-Z\d]/i", '', $string);
    $string = preg_replace('/\s\s+/', ' ', $string);
    return trim($string);
  }

  /**
   * @param $post_id
   * @param $post
   * @param $update
   * @return void
   */
  public function save_metaboxes($post_id, $post, $update)
  {
    if (
      (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
      !current_user_can('edit_post', $post->ID) ||
      defined('REST_REQUEST') && REST_REQUEST ||
      (is_int(wp_is_post_autosave($post_id)) || is_int(wp_is_post_revision($post_id))) ||
      !$update ||
      $post->post_type == 'revision'
    ) {
      return;
    }

    if (isset($_POST['hidden_post_status']) && $_POST['hidden_post_status'] == 'publish') {
      if (!in_array($_POST['visibility'], array('private', 'password'))) {
        remove_action('save_post', array($this, 'save_metaboxes'));
        wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
        add_action('save_post', array($this, 'save_metaboxes'), 10, 3);
      }
    }

    if (isset($_POST['oxy_howto_maker_nonce']) && isset($_POST['oxy_howto_status'])) {
      $oxy_data = array();
      $errors = new WP_Error('oxy_howto_maker_errors', __('Oops..something went wrong:', 'oxy-howto-maker'));
      if (wp_verify_nonce($_POST['oxy_howto_maker_nonce'], basename(__FILE__))) {

        if (isset($_POST['oxy_data']) && is_array($_POST['oxy_data'])) {
          $oxy_data = $_POST['oxy_data'];

          $post_content = $post->post_content;
          $post_content = trim(strip_tags(do_shortcode($post_content)));

          $schema_description = $description = '';
          if (isset($oxy_data['description'])) {
            $description = $schema_description = trim($oxy_data['description']);
            $description = preg_replace('!\\r?\\n!', '\r\n', $description);
            $description = addcslashes($description, '/"');

            $re = '~(<br />)(\1{2,10})~';
            $schema_description = preg_replace($re, '$2', $schema_description);
            $schema_description = $this->sanitize_for_schema($schema_description);
          }

          $post_title = trim(strip_tags(do_shortcode($post->post_title)));
          $schema_post_title = $this->sanitize_for_schema($post_title);

          // Content
          if (empty($post_content)) {
            $errors->add('oxy_howto_maker_errors', $this->config_error['post_content_required']);
          }

          // Title
          if (empty($post_title)) {
            $errors->add('oxy_howto_maker_errors', $this->config_error['post_title_required']);
          }

          // Description
          if (empty($description)) {
            $errors->add('oxy_howto_maker_errors', $this->config_error['description_required']);
          }

          // Total Time
          $days = (int)($oxy_data['day']);
          $hours = (int)($oxy_data['hour']);
          $minutes = (int)($oxy_data['minute']);
          $total_time = '';

          if ($days == '0' && $hours == '0' && $minutes == '0') {
            $errors->add('oxy_howto_maker_errors', $this->config_error['total_time_validity']);
          } else {
            $total_time = 'P';
            if ($days > 0) {
              $total_time = $total_time . $days . 'D';
            }
            if ($hours > 0) {
              $total_time = $total_time . 'T' . $hours . 'H';
              if ($minutes > 0) {
                $total_time = $total_time . $minutes . 'M';
              }
            } else if ($minutes > 0) {
              $total_time = $total_time . 'T' . $minutes . 'M';
            }
            if ($total_time === 'P') {
              $errors->add('oxy_howto_maker_errors', $this->config_error['total_time_validity']);
            }
          }

          // Price Currency
          if (
            !empty($oxy_data['estimatedCost']['value']) &&
            (empty($oxy_data['estimatedCost']['currency']) ||
              !isset($this->get_currencies()[$oxy_data['estimatedCost']['currency']]))
          ) {
            $errors->add('oxy_howto_maker_errors', $this->config_error['price_currency_validity']);
          }

          // Estimated Cost
          $cost = $schema_cost = 0;
          if (empty($oxy_data['estimatedCost']['value']) && $oxy_data['estimatedCost']['value'] != 0) {
            $errors->add('oxy_howto_maker_errors', $this->config_error['estimated_cost_required']);
          } else {
            $cost = str_replace(',', '', $oxy_data['estimatedCost']['value']);
            $cost = trim($cost);
            if (!preg_match('/^\d+(\.\d+)?$/', $cost)) {
              $errors->add('oxy_howto_maker_errors', $this->config_error['estimated_cost_validity']);
            }
          }

          if (isset($oxy_data['estimatedCost']['currency'])) {
            if ($oxy_data['estimatedCost']['currency'] == 'IRT') {
              $schema_cost = $cost * 10;
              $currency = 'IRR';
            } else {
              $schema_cost = $cost;
              $currency = $oxy_data['estimatedCost']['currency'];
            }
          } else {
            $currency = '';
          }

          // Featured Image
          $featuredImage = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
          // TODO: Check if the post has featured image or not.
          /*if($featuredImage === false) {
              $errors->add('oxy_howto_maker_errors', $this->config_error['featured_image_required']);
          }*/

          $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => $schema_post_title,
            'description' => $schema_description,
            'totalTime' => $total_time,
            'estimatedCost' => array(
              '@type' => 'MonetaryAmount',
              'currency' => $currency,
              'value' => $schema_cost,
            ),
          );

          // Featured Image
          if ($featuredImage) {
            $schema['image'] = array(
              '@type' => 'ImageObject',
              'url' => $featuredImage[0],
              'width' => $featuredImage[1],
              'height' => $featuredImage[2],
            );
          }

          // Supply
          $supplies = array();
          $schema['supply'] = array();
          if (isset($oxy_data['supply']) && is_array($oxy_data['supply'])) {
            foreach ($oxy_data['supply'] as $supply) {
              if (isset($supply['name'])) {
                $supplyName = trim($supply['name']);
                if ($supplyName != '') {
                  $schema['supply'][] = array(
                    '@type' => 'HowToSupply',
                    'name' => $this->sanitize_for_schema($supply['name']),
                  );

                  $supply['url'] = preg_replace('/^https?:\/\//', '', $supply['url']);
                  $supply['url'] = 'http://' . $supply['url'];

                  $supplies[] = array(
                    'name' => $supply['name'],
                    'url' => empty($supply['url']) ? '#' : $supply['url'],
                    'nofollow' => $this->config['nofollow'] == 'on' && isset($supply['nofollow']) ? 'on' : 'off',
                  );
                }
              }
            }
          }

          if (count($supplies) > $this->config['supply']) {
            $errors->add('oxy_howto_maker_errors', $this->config_error['supply']);
          }

          // Tool
          $tools = array();
          $schema['tool'] = array();
          if (isset($oxy_data['tool']) && is_array($oxy_data['tool'])) {
            foreach ($oxy_data['tool'] as $tool) {
              if (isset($tool['name'])) {
                $toolName = trim($tool['name']);
                if ($toolName != '') {
                  $schema['tool'][] = array(
                    '@type' => 'HowToTool',
                    'name' => $this->sanitize_for_schema($tool['name']),
                  );

                  $tool['url'] = preg_replace('/^https?:\/\//', '', $tool['url']);
                  $tool['url'] = 'http://' . $tool['url'];

                  $tools[] = array(
                    'name' => $tool['name'],
                    'url' => empty($tool['url']) ? '#' : $tool['url'],
                    'nofollow' => $this->config['nofollow'] == 'on' && isset($tool['nofollow']) ? 'on' : 'off',
                  );
                }
              }
            }
          }

          if (count($tools) > $this->config['tool']) {
            $errors->add('oxy_howto_maker_errors', $this->config_error['tool']);
          }

          // Step
          $steps = array();

          if (isset($oxy_data['step']) && is_array($oxy_data['step'])) {
            if (count($oxy_data['step']) > $this->config['step']) {
              $errors->add('oxy_howto_maker_errors', $this->config_error['step']);
            } else {
              $post_permalink = get_permalink($post);

              foreach ($oxy_data['step'] as $number => $step) {
                $directions_count = 0;
                $tips_count = 0;

                // Name
                if (empty($step['name'])) {
                  $errors->add('oxy_howto_maker_errors', sprintf(__('The name of step %s is invalid.', 'oxy-howto-maker'), $number + 1));
                  break;
                }

                // Image
                if (empty($step['image']['url'])) {
                  $errors->add('oxy_howto_maker_errors', sprintf(__('The image of step %s is invalid.', 'oxy-howto-maker'), $number + 1));
                  break;
                }
                $attachment_id = attachment_url_to_postid($step['image']['url']);
                if ($attachment_id == '0') {
                  $errors->add('oxy_howto_maker_errors', sprintf(__('The image of step %s is invalid.', 'oxy-howto-maker'), $number + 1));
                  break;
                }
                $step_image = wp_get_attachment_image_src($attachment_id, 'full');
                if ($step_image === false) {
                  $errors->add('oxy_howto_maker_errors', sprintf(__('The image of step %s is invalid.', 'oxy-howto-maker'), $number + 1));
                  break;
                }

                // Step Result
                $stepResult = array(
                  '@type' => 'HowToStep',
                  'url' => $post_permalink . '#step' . ($number + 1),
                  'name' => $this->sanitize_for_schema($step['name']),
                );

                // Step Image
                $stepResult['image'] = array(
                  '@type' => 'ImageObject',
                  'url' => $step_image[0],
                  'width' => $step_image[1],
                  'height' => $step_image[2],
                );

                // Step Direction & Tip
                $texts = array();
                foreach ($step['directions_and_tips'] as $text) {
                  foreach ($text as $key => $d_or_t) {
                    $d_or_t = trim($d_or_t);
                    if (strlen($d_or_t) > 0) {
                      $d_or_t = preg_replace('!\\r?\\n!', '\r\n', $d_or_t);
                      $d_or_t = addcslashes($d_or_t, '/"');
                      $texts[] = array($key => $d_or_t);
                    }
                  }
                }

                $textsCount = count($texts);
                if ($textsCount > 0) {
                  if (empty($texts[0]['direction'])) {
                    $errors->add('oxy_howto_maker_errors', sprintf(__('The direction of step %s is invalid.', 'oxy-howto-maker'), $number + 1));
                    break;
                  }
                  foreach ($texts as $text2) {
                    if (isset($text2['direction'])) {
                      $stepResult['itemListElement'][] = array(
                        '@type' => 'HowToDirection',
                        'text' => $this->sanitize_for_schema($text2['direction']),
                      );

                      $directions_count++;
                    } elseif (isset($text2['tip'])) {
                      $stepResult['itemListElement'][] = array(
                        '@type' => 'HowToTip',
                        'text' => $this->sanitize_for_schema($text2['tip']),
                      );

                      $tips_count++;
                    }
                  }
                } else {
                  $errors->add('oxy_howto_maker_errors', sprintf(__('The direction of step %s is invalid.', 'oxy-howto-maker'), $number + 1));
                  break;
                }

                if ($directions_count > $this->config['step_direction']) {
                  $errors->add('oxy_howto_maker_errors', $this->config_error['step_direction']);
                  break;
                }
                if ($tips_count > $this->config['step_tip']) {
                  $errors->add('oxy_howto_maker_errors', $this->config_error['step_tip']);
                  break;
                }

                $schema['step'][] = $stepResult;

                $steps[] = array(
                  'name' => $step['name'],
                  'image' => str_replace(get_home_url(), '', $step['image']['url']),
                  'directions_and_tips' => $texts,
                );
              }
            }
          } else {
            $errors->add('oxy_howto_maker_errors', __('The step is invalid.', 'oxy-howto-maker'));
          }

          // Difficulty
          if (isset($oxy_data['difficulty'])) {
            $difficulty = (int)($oxy_data['difficulty']);
            if ($difficulty < 0 || $difficulty > 3) {
              $errors->add('oxy_howto_maker_errors', __('The difficulty is invalid.', 'oxy-howto-maker'));
            }
          }
        } else {
          $errors->add('oxy_howto_maker_errors', __('The oxy data for howto is invalid.', 'oxy-howto-maker'));
        }
      } else {
        $errors->add('oxy_howto_maker_errors', __('The nonce not verified.', 'oxy-howto-maker'));
      }

      if (count($errors->get_error_messages()) > 1) {
        global $wpdb;
        $wpdb->update($wpdb->posts, array('post_status' => 'pending'), array('ID' => $post->ID));
        set_transient('oxy_howto_maker_errors', $errors, 10);
      } else {
        $oxy_data_estimated_cost_currency = $oxy_data['estimatedCost']['currency'] ?? '';
        update_option('oxy_howto_maker_currency', $oxy_data_estimated_cost_currency, false);

        update_post_meta($post->ID, '_oxy_howto_status', 'on');
        update_post_meta($post->ID, '_oxy_howto_difficulty', $oxy_data['difficulty'] ?? '');
        update_post_meta($post->ID, '_oxy_howto_estimated_cost_currency', $oxy_data_estimated_cost_currency);
        update_post_meta($post->ID, '_oxy_howto_estimated_cost_value', $cost ?? 0);
        update_post_meta($post->ID, '_oxy_howto_selected_supply_title', $oxy_data['supply']['title'] ?? 'supply');
        update_post_meta($post->ID, '_oxy_howto_description', isset($description) ? json_encode($description, JSON_UNESCAPED_UNICODE) : '');
        update_post_meta($post->ID, '_oxy_howto_supply', isset($supplies) ? json_encode($supplies, JSON_UNESCAPED_UNICODE) : '');
        update_post_meta($post->ID, '_oxy_howto_tool', isset($tools) ? json_encode($tools, JSON_UNESCAPED_UNICODE) : '');
        update_post_meta($post->ID, '_oxy_howto_step', isset($steps) ? json_encode($steps, JSON_UNESCAPED_UNICODE) : '');
        update_post_meta($post->ID, '_oxy_howto_day', isset($oxy_data['day']) ? (int)($oxy_data['day']) : 0);
        update_post_meta($post->ID, '_oxy_howto_hour', isset($oxy_data['hour']) ? (int)($oxy_data['hour']) : 0);
        update_post_meta($post->ID, '_oxy_howto_minute', isset($oxy_data['minute']) ? (int)($oxy_data['minute']) : 0);
        update_post_meta($post->ID, '_oxy_howto_schema', isset($schema) ? json_encode($schema, JSON_UNESCAPED_UNICODE) : '');
      }
    }
  }

  /**
   * @param $content
   * @return mixed|string
   */
  public function customize_content($content)
  {
    $oxy_howto_maker = get_post_meta(get_the_ID());
    if (isset($oxy_howto_maker['_oxy_howto_status'][0])) {
      if ($oxy_howto_maker['_oxy_howto_status'][0] == 'on') {
        $schema = '<script type="application/ld+json">' . $oxy_howto_maker['_oxy_howto_schema'][0] . '</script>';
        $content .= $schema;
      }
    }

    return $content;
  }

  /**
   * @return void
   */
  public function oxy_howto_maker_activate()
  {
    update_option('_oxy_howto_maker_selected_style', 'style-1.css');
  }

  /**
   * @return array[]
   */
  public function get_trans(): array
  {
    $trans = array(
      'meta_image_frame_options' => array(
        'title' => esc_html__('Upload image of this step', 'oxy-howto-maker'),
        'button' => array('text' => esc_html__('Select', 'oxy-howto-maker')),
        'library' => array('type' => 'image'),
      ),

      'config' => array(
        'step' => esc_html($this->config['step']),
        'step_direction' => esc_html($this->config['step_direction']),
        'step_tip' => esc_html($this->config['step_tip']),
        'supply' => esc_html($this->config['supply']),
        'tool' => esc_html($this->config['tool']),
        'nofollow' => esc_html($this->config['nofollow']),
      ),

      'user_trans' => array(
        'r_u_sure' => esc_html($this->config_error['r_u_sure']),
        'title_required' => esc_html($this->config_error['post_title_required']),
        'description_required' => esc_html($this->config_error['description_required']),
        'content_required' => esc_html($this->config_error['post_content_required']),
        'featured_image_required' => esc_html($this->config_error['featured_image_required']),
        'estimated_cost_validity' => esc_html($this->config_error['estimated_cost_validity']),
        'price_currency_validity' => esc_html($this->config_error['price_currency_validity']),
        'supply_url_validity' => esc_html($this->config_error['supply_url_validity']),
        'supply_name_validity' => esc_html($this->config_error['supply_name_validity']),
        'tool_url_validity' => esc_html($this->config_error['tool_url_validity']),
        'tool_name_validity' => esc_html($this->config_error['tool_name_validity']),
        'step_name_validity' => esc_html__('The name of step %s is invalid.', 'oxy-howto-maker'),
        'step_direction_validity' => esc_html__('The direction of step %s is invalid.', 'oxy-howto-maker'),
        'step_tip_validity' => esc_html__('The tip %s of step %s is invalid.', 'oxy-howto-maker'),
        'step_image_validity' => esc_html__('The image of step %s is invalid.', 'oxy-howto-maker'),
        'total_time_validity' => esc_html($this->config_error['total_time_validity']),
        'successfully_generated' => esc_html($this->config_error['successfully_generated']),
        'difficulty' => esc_html($this->config_error['Difficulty']),
        'step_count' => esc_html($this->config_error['Step Count']),
        'estimated_cost' => esc_html($this->config_error['Estimated Cost']),
        'supply' => esc_html($this->config_error['Supply']),
        'supplies' => esc_html($this->config_error['Supplies']),
        'material' => esc_html($this->config_error['Material']),
        'materials' => esc_html($this->config_error['Materials']),
        'necessary_item' => esc_html($this->config_error['Necessary Item']),
        'necessary_items' => esc_html($this->config_error['Necessary Items']),
        'tool' => esc_html($this->config_error['Tool']),
        'tools' => esc_html($this->config_error['Tools']),
        'steps' => esc_html($this->config_error['Steps']),
        'step' => esc_html($this->config_error['Step']),
        'delete_step' => esc_html__('Delete Step %s', 'oxy-howto-maker'),
        'delete_step_image' => esc_html__('Delete Step %s Image', 'oxy-howto-maker'),
        'direction' => esc_html__('Step Direction', 'oxy-howto-maker'),
        'delete_direction' => esc_html__('Delete Direction %s', 'oxy-howto-maker'),
        'tip' => esc_html__('Step Tip', 'oxy-howto-maker'),
        'delete_tip' => esc_html__('Delete Tip %s', 'oxy-howto-maker'),
        'total_time' => esc_html($this->config_error['Total Time']),
        'day' => esc_html($this->config_error['Day']),
        'hour' => esc_html($this->config_error['Hour']),
        'minute' => esc_html($this->config_error['Minute']),
        'ampersand' => esc_html($this->config_error['Ampersand']),
      ),
    );

    switch_to_locale(get_locale());
    $trans['site_trans'] = array(
      'difficulty' => esc_html__('Difficulty', 'oxy-howto-maker'),
      'step_count' => esc_html__('Step Count', 'oxy-howto-maker'),
      'estimated_cost' => esc_html__('Estimated Cost', 'oxy-howto-maker'),
      'ampersand' => esc_html__('&', 'oxy-howto-maker'),
      'total_time' => esc_html__('Total Time', 'oxy-howto-maker'),
      'step' => esc_html__('Step', 'oxy-howto-maker'),
      'tip' => esc_html__('Tip', 'oxy-howto-maker'),
      'successfully_generated' => esc_html__('HowTo Successfully Generated.', 'oxy-howto-maker'),
      'title_required' => esc_html__('The post title is required.', 'oxy-howto-maker'),
      'featured_image_required' => esc_html__('The featured image is required.', 'oxy-howto-maker'),
      'r_u_sure' => esc_html__('Are you sure?', 'oxy-howto-maker'),
      'description_required' => esc_html__('The description is required.', 'oxy-howto-maker'),
      'estimated_cost_validity' => esc_html__('The estimated cost is invalid.', 'oxy-howto-maker'),
      'price_currency_validity' => esc_html__('The price currency is invalid.', 'oxy-howto-maker'),
      'supply_name_validity' => esc_html__('The supply name %s is invalid.', 'oxy-howto-maker'),
      'supply_url_validity' => esc_html__('The supply link %s is invalid.', 'oxy-howto-maker'),
      'tool_name_validity' => esc_html__('The tool name %s is invalid.', 'oxy-howto-maker'),
      'tool_url_validity' => esc_html__('The tool link %s is invalid.', 'oxy-howto-maker'),
      'step_name_validity' => esc_html__('The name of step %s is invalid.', 'oxy-howto-maker'),
      'step_direction_validity' => esc_html__('The direction of step %s is invalid.', 'oxy-howto-maker'),
      'step_tip_validity' => esc_html__('The tip %s of step %s is invalid.', 'oxy-howto-maker'),
      'step_image_validity' => esc_html__('The image of step %s is invalid.', 'oxy-howto-maker'),
      'total_time_validity' => esc_html__('The total time is invalid.', 'oxy-howto-maker'),
      'delete_step' => esc_html__('Delete Step %s', 'oxy-howto-maker'),
      'delete_step_image' => esc_html__('Delete Step %s Image', 'oxy-howto-maker'),
      'direction' => esc_html__('Step Direction', 'oxy-howto-maker'),
      'delete_direction' => esc_html__('Delete Direction %s', 'oxy-howto-maker'),
      'delete_tip' => esc_html__('Delete Tip %s', 'oxy-howto-maker'),
      'day' => esc_html__('day', 'oxy-howto-maker'),
      'hour' => esc_html__('hour', 'oxy-howto-maker'),
      'minute' => esc_html__('minute', 'oxy-howto-maker'),
      'supplies' => esc_html__('Supplies', 'oxy-howto-maker'),
      'supply' => esc_html__('Supply', 'oxy-howto-maker'),
      'materials' => esc_html__('Materials', 'oxy-howto-maker'),
      'material' => esc_html__('Material', 'oxy-howto-maker'),
      'necessary_items' => esc_html__('Necessary Items', 'oxy-howto-maker'),
      'necessary_item' => esc_html__('Necessary Item', 'oxy-howto-maker'),
      'tools' => esc_html__('Tools', 'oxy-howto-maker'),
      'tool' => esc_html__('Tool', 'oxy-howto-maker'),
    );
    restore_previous_locale();

    return $trans;
  }

  /**
   * @return void
   */
  public function add_admin_assets()
  {
    wp_register_style('oxy-howto-maker-admin-style', plugins_url('assets/css/admin-style.css', __FILE__));
    wp_enqueue_style('oxy-howto-maker-admin-style');

    wp_register_script('oxy-howto-maker-admin-script', plugins_url('assets/js/admin-script.js', __FILE__));
    wp_enqueue_script('oxy-howto-maker-admin-script');

    wp_localize_script(
      'oxy-howto-maker-admin-script',
      'oxy_howto_trans',
      $this->get_trans()
    );
  }

  /**
   * @return void
   */
  public function add_assets()
  {
    $oxy_howto_maker_style = get_option('_oxy_howto_maker_selected_style', '');
    if (!empty($oxy_howto_maker_style)) {
      $style = 'assets/css/' . str_replace('custom_', 'custom/', $oxy_howto_maker_style);
      $style_file = plugin_dir_path(__FILE__) . $style;
      if (is_file($style_file) && !is_dir($style_file)) {
        $style_file = plugins_url($style, __FILE__);
        wp_register_style('oxy-howto-maker-style', $style_file);
        wp_enqueue_style('oxy-howto-maker-style');
      }
    }
  }
}

new OxyplugHowtoMaker();
