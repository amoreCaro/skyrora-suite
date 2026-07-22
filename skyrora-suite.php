<?php

/*
 * @wordpress-plugin
 * Plugin Name:       Skyrora Suite — Mailing & Builder
 * Description:       Об'єднаний плагін: розсилка (mailing) + конструктор блоків (Spacebox Builder).
 * Version:           1.0.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       skyrora-suite
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SK_VERSION', '1.0.0' );
define( 'SK_TEXT_DOMAIN', 'skyrora-mailing' );
define( 'SK_PLUGIN', __FILE__ );
define( 'SK_PLUGIN_BASENAME', plugin_basename( SK_PLUGIN ) );
define( 'SK_PLUGIN_NAME', trim( dirname( SK_PLUGIN_BASENAME ), '/' ) );
define( 'SK_PLUGIN_DIR', untrailingslashit( dirname( SK_PLUGIN ) ) );
define( 'SK_PLUGIN_DIR_URL', plugin_dir_url(__FILE__) );

include_once SK_PLUGIN_DIR .'/inc/init.php';
include_once SK_PLUGIN_DIR .'/inc/settings.php';
include_once SK_PLUGIN_DIR .'/inc/subscribers.php';
include_once SK_PLUGIN_DIR .'/inc/lists.php';
include_once SK_PLUGIN_DIR .'/inc/page-html.php';
include_once SK_PLUGIN_DIR .'/inc/send.php';
include_once SK_PLUGIN_DIR .'/inc/test-sending.php';
include_once SK_PLUGIN_DIR .'/inc/spacebox-builder.php';

if ( is_admin() ) {
	include_once SK_PLUGIN_DIR . '/inc/enqueue.php';
	include_once SK_PLUGIN_DIR . '/inc/admin-header.php';
}


add_filter( 'the_content', function ( $content ) {
    if ( is_singular( 'mailing' ) ) { ?>
        <div id="wrapper" style="max-width: 100%; margin: 0px auto; background-color: rgb(24, 27, 36); padding: 0px 20px;">
            <div class="container" style="max-width: 640px; width: 100%; background-color: #fff;  margin: 0 auto; min-height: 50vh; box-sizing: border-box; padding: 0px;">
                <?php echo $content; ?>
            </div>
        </div>
    <?php }
} );