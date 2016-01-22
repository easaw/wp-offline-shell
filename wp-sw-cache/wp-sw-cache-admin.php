<?php

load_plugin_textdomain('wpswcache', false, dirname(plugin_basename(__FILE__)) . '/lang');

class SW_Cache_Admin {
  private static $instance;

  public function __construct() {
    add_action('admin_menu', array($this, 'on_admin_menu'));
    add_action('admin_notices', array($this, 'on_admin_notices'));
  }

  public static function init() {
    if (!self::$instance) {
      self::$instance = new self();
    }
  }

  public function on_admin_notices() {
    // TODO:  Add notice that if the plugin is activated but no files are selected, nothing is happening
  }

  public function on_admin_menu() {
    add_options_page(__('WP SW Cache', 'wpswcache'), __('WP SW Cache', 'wpswcache'), 'manage_options', 'wp-sw-cache-options', array($this, 'options'));
  }

  // http://php.net/manual/en/function.scandir.php#109140
  public function scan_theme_dir($directory, $recursive = true, $listDirs = false, $listFiles = true, $exclude = '') {
    $arrayItems = array();
    $skipByExclude = false;
    $handle = opendir($directory);
    if ($handle) {
        while (false !== ($file = readdir($handle))) {
        preg_match("/(^(([\.]){1,2})$|(\.(svn|git|md))|(Thumbs\.db|\.DS_STORE))$/iu", $file, $skip);
        if($exclude){
            preg_match($exclude, $file, $skipByExclude);
        }
        if (!$skip && !$skipByExclude) {
            if (is_dir($directory. DIRECTORY_SEPARATOR . $file)) {
                if($recursive) {
                    $arrayItems = array_merge($arrayItems, $this->scan_theme_dir($directory. DIRECTORY_SEPARATOR . $file, $recursive, $listDirs, $listFiles, $exclude));
                }
                if($listDirs){
                    $file = $directory . DIRECTORY_SEPARATOR . $file;
                    $arrayItems[] = $file;
                }
            } else {
                if($listFiles){
                    $file = $directory . DIRECTORY_SEPARATOR . $file;
                    $arrayItems[] = $file;
                }
            }
        }
    }
    closedir($handle);
    }
    return $arrayItems;
  }

  function options() {

?>

<div class="wrap">

  <h1><?php _e('WordPress Service Worker Cache', 'wpswcache'); ?></h1>

  <p><?php _e('WordPress Service Worker Cache is a ultility that harnesses the power of the <a href="https://serviceworke.rs" target="_blank">ServiceWorker API</a> to cache frequently used assets for the purposes of performance and offline viewing.'); ?></p>

  <form method="post" action="">
    <h2><?php _e('Enable ServiceWorker Cache', 'wpswcache'); ?></h2>
    <table class="form-table">
    <tr>
      <th scope="row"><label for="wp_sw_cache_enabled"><?php _e('Enable Service Worker?', 'wpswcache'); ?></label></th>
      <td>
        <input type="checkbox" name="wp_sw_cache_enabled" id="wp_sw_cache_enabled" value="1" autofocus />
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="wp_sw_cache_name"><?php _e('Cache Prefix?', 'wpswcache'); ?></label></th>
      <td>
        <input type="text" name="wp_sw_cache_name" id="wp_sw_cache_name" value="" />
      </td>
    </tr>
    </table>

    <?php submit_button(__('Save Changes'), 'primary'); ?>

    <h2><?php _e('Theme Files to Cache', 'wpswcache'); ?> (<code><?php echo get_template(); ?></code>)</h2>
    <p><?php _e('Select theme assets (typically JavaScript, CSS, fonts, and image files) that are used on a majority of pages.', 'wpswcache'); ?></p>
    <div style="max-height: 300px;background:#fefefe;border:1px solid #ccc;padding:10px;overflow-y:auto;">
      <table class="form-table">
      <?php
        $template_abs_path = get_template_directory();
        $theme_files = $this->scan_theme_dir($template_abs_path);

        foreach($theme_files as $file) {
          $file_relative = str_replace(get_theme_root().'/'.get_template().'/', '', $file);
      ?>
        <tr>
          <td style="width: 30px;">
            <input type="checkbox" name="wp_sw_cache_files[]" id="wp_sw_cache_files['<?php echo $file_relative; ?>']" value="<?php echo $file_relative; ?>" />
          </td>
          <td>
            <label for="wp_sw_cache_files['<?php echo $file_relative; ?>']"><?php echo $file_relative; ?></label>
          </td>
        </tr>
      <?php
        }
      ?>
      </table>
    </div>

    <?php submit_button(__('Save Changes'), 'primary'); ?>
  </form>

</div>

<?php
  }
}
?>
