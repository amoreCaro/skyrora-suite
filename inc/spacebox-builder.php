<?php

// Prevent a fatal "Cannot redeclare function" error when the standalone
// spacebox-builder plugin is also active (it declares the same functions).
// Everything is wrapped in this guard so the function declarations become
// conditional (bound at runtime) instead of being early-bound at compile time.
if ( ! function_exists( 'theme_allowed_blocks' ) ) {

add_filter( 'allowed_block_types_all', 'theme_allowed_blocks', 10, 2 );

function theme_allowed_blocks( $allowed_blocks, $block_editor_context ) {
    return array(
        'app/spacer',
        'core/columns',
        'core/paragraph',
        'app/header',
        'app/heading',
        'app/paragraph',
        'app/image',
        'app/banner',
        'app/gallery',
        'app/button',
        'app/divider',
        'app/footer',
        'app/padding-section',
    );
}

function register_custom_blocks() {

    $blocks = array(
        '/components/banner',
        '/components/button',
        '/components/divider',
        '/components/gallery',
        '/components/heading',
        '/components/image',
        '/components/padding-section',
        '/components/paragraph',
        '/components/spacer',

        '/layouts/footer',
        '/layouts/header',
    );

    foreach ( $blocks as $block ) {
        register_block_type(
            plugin_dir_path( __FILE__ ) . '../build' . $block
        );
    }
}

add_action( 'init', 'register_custom_blocks' );

function allow_svg_uploads($mimes) {
    // Add SVG file type to allowed mime tсдpes
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_uploads');

function moneyline_enqueue_block_editor_assets() {

    wp_enqueue_style(
        'theme-global',
        plugin_dir_url( __FILE__ ) . '../src/base/css/global/admin.css',
        array(),
        '1.0.0',
        'all'
    );

    $data = get_current_screen();

    if ($data->post_type === 'page') {

        $mode = get_option('my_theme_dark_mode', 'light');

        if ($mode === 'dark') {

            wp_enqueue_style(
                'theme-dark',
                plugin_dir_url( __FILE__ ) . '..src/base/css/global/dark.css',
                array(),
                '1.0.0',
                'all'
            );


        } else {

            wp_enqueue_style(
                'admin-light',
                plugin_dir_url( __FILE__ ) . '..src/base/css/global/light.css',
                array(),
                filemtime( plugin_dir_path( __FILE__ ) . '..src/base/css/global/light.css' )
            );

        }

    }

    wp_enqueue_script(
        'preview',
        plugins_url( '..src/base/js/preview.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ),
        null,
        false
    );
}
add_action( 'enqueue_block_editor_assets', 'moneyline_enqueue_block_editor_assets' );

add_action( 'enqueue_block_assets', function() {
    wp_enqueue_style(
        'theme-blocks',
        '/wp-content/themes/spacebox-react/assets/css/style.bundle.css',
        array(),
        '1.0.0',
        'all'
    );
});

function theme_add_settings_page() {
    add_menu_page(
        'Theme Settings',
        'Theme Settings',
        'manage_options',
        'theme-settings',
        'theme_render_settings_page'
    );
}
add_action('admin_menu', 'theme_add_settings_page');


function theme_render_settings_page() {
    ?>
  <div class="wrap">
      <h1><?php esc_html_e('Theme Settings', 'my-theme'); ?></h1>
      <form method="post" action="options.php">
          <?php
              settings_fields('theme_options_group');
              do_settings_sections('theme-settings');
              submit_button();
              ?>
      </form>
  </div>
<?php
}


function theme_settings_init() {
    register_setting('theme_options_group', 'my_theme_dark_mode');

    add_settings_section(
        'theme_settings_section',
        __('Dark Mode Settings', 'my-theme'),
        null,
        'theme-settings'
    );

    add_settings_field(
        'my_theme_dark_mode',
        'Choose Theme Mode',
        'theme_dark_mode_render',
        'theme-settings',
        'theme_settings_section'
    );
}
add_action('admin_init', 'theme_settings_init');


function theme_dark_mode_render() {
    $option = get_option('my_theme_dark_mode', 'light');
    ?>
  <label>
      <input type="radio" name="my_theme_dark_mode" value="light" <?php checked($option, 'light'); ?>>
      <?php _e('Light Mode'); ?>
  </label><br>
  <label>
      <input type="radio" name="my_theme_dark_mode" value="dark" <?php checked($option, 'dark'); ?>>
      <?php _e('Dark Mode') ?>
  </label>
<?php
}


function theme_apply_mode_class() {
    $screen = get_current_screen();

    if ($screen->post_type === 'page') {
        $mode = get_option('my_theme_dark_mode', 'light');

        if ($mode === 'dark') {
            echo '<script>document.body.classList.add("dark-mode");</script>';
        } else {
            echo '<script>document.body.classList.remove("dark-mode");</script>';
        }
    }
}
add_action('admin_footer', 'theme_apply_mode_class');


function app_news_render_callback( $block_attributes, $content ) {
    ob_start();
    ?>
  <section class="section news">
      <div class="container">
          <h2>
              News
          </h2>
          <div class="row news__row">

              <?php
                  $default_image_url = '/wp-content/uploads/2023/01/woocommerce-placeholder.png';

                  $post_args = array(
                      'post_type' => 'post',
                      'posts_per_page' => 9,
                      'order' => 'ASC',
                      'orderby' => 'date', 
                      'post_status' => 'publish',
                      'fields' => 'ids',
                      'suppress_filters' => false
                  );

                  $posts = get_posts($post_args); 


                  $project_args = array(
                      'post_type' => 'project',
                      'posts_per_page' => 9,
                      'order' => 'ASC',
                      'orderby' => 'date', 
                      'post_status' => 'publish',
                      'fields' => 'ids',
                      'suppress_filters' => false
                  );

                  $projects = get_posts($project_args); 

                  if( $posts ): 
                      foreach( $posts as $post ): 
                          $post_id = $post;
                          $post_image_id = get_post_thumbnail_id( $post_id );
                          $post_image = wp_get_attachment_image($post_image_id, 'medium');
                          ?>

              <a href="<?php echo get_the_permalink($post_id); ?>" class="news-item">
                  <div class="news-item__picture">
                      <div class="h-object-fit">
                          <?php 
                                          
                                          if( !empty($post_image)){
                                                  echo $post_image;
                                              } else {
                                                  echo '<img src="' . esc_url($default_image_url) . '" alt="Default Image">';
                                              }
                                          ?>
                      </div>
                  </div>
                  <div class="news-item__info">
                      <div class="news-item__info-top">
                          <?php 

                                              $tags = get_the_tags($post_id);

                                              if( !empty($tags) ){
                                                  foreach( $tags as $tag ){
                                                      echo '<span>#' . $tag->name . '</span>';
                                                  }
                                              }

                                          ?>
                      </div>
                      <div class="news-item__info-main">
                          <h4>
                              <?php echo get_the_title($post_id); ?>
                          </h4>
                          <time datetime="<?php echo get_the_date('Y-m-d' , $post_id); ?>">
                              <?php echo get_the_date('d.m.Y' , $post_id); ?>
                          </time>
                          <div class="news-item__info-content">
                              <?php echo get_the_excerpt($post_id); ?>
                          </div>
                      </div>
                  </div>
              </a>

              <?php endforeach; wp_reset_postdata(); ?>
              <?php endif;

                  if( $projects ): 
                      foreach( $projects as $project ): 
                          $project_id = $project;
                          $project_image_id = get_post_thumbnail_id( $project_id );
                          $project_image = wp_get_attachment_image($project_image_id, 'medium');
                          ?>

              <a href="<?php echo get_the_permalink($project_id); ?>" class="news-item">
                  <div class="news-item__picture">
                      <div class="h-object-fit">
                          <?php 
                                          
                                          if( !empty($project_image)){
                                                  echo $project_image;
                                              } else {
                                                  echo '<img src="' . esc_url($default_image_url) . '" alt="Default Image">';
                                              }
                                          ?>
                      </div>
                  </div>
                  <div class="news-item__info">
                      <div class="news-item__info-top">
                          <?php 

                                              $tags = get_the_tags($project_id);

                                              if( !empty($tags) ){
                                                  foreach( $tags as $tag ){
                                                      echo '<span>#' . $tag->name . '</span>';
                                                  }
                                              }

                                          ?>
                      </div>
                      <div class="news-item__info-main">
                          <h4>
                              <?php echo get_the_title($project_id); ?>
                          </h4>
                          <time datetime="<?php echo get_the_date('Y-m-d' , $project_id); ?>">
                              <?php echo get_the_date('d.m.Y' , $project_id); ?>
                          </time>
                          <div class="news-item__info-content">
                              <?php echo get_the_excerpt($project_id); ?>
                          </div>
                      </div>
                  </div>
              </a>

              <?php endforeach; wp_reset_postdata(); ?>
              <?php endif; ?>
          </div>
      </div>
  </section>
  <?php

    return ob_get_clean();
}

function render_dynamic_posts_grid() {
    ob_start();
    $response = wp_remote_get( esc_url( rest_url( 'wp/v2/posts' ) ) );

    if ( is_wp_error( $response ) ) {
        return '<p>Error loading posts.</p>';
    }

    $posts = json_decode( wp_remote_retrieve_body( $response ) );

    if ( ! empty( $posts ) ) { ?>
  <section class="section news">
      <div class="container">
          <h2>News</h2>
          <div class="row news__row">
              <?php 
                      foreach ( $posts as $post ) {
                          // Отримуємо ID поста
                          $id = $post->id;

                          $media_id = isset($post->featured_media) ? $post->featured_media : null;

                          if ($media_id) {
                              // Отримуємо зображення без додаткової функції
                              $media_url = wp_remote_get( esc_url( rest_url( 'wp/v2/media/' . $media_id ) ) );
              
                              // Перевіряємо, чи є помилка
                              if ( is_wp_error( $media_url ) ) {
                                  $image_url = '';
                              } else {
                                  $media_body = json_decode( wp_remote_retrieve_body( $media_url ) );
                                  // Зображення отримуємо за полем source_url
                                  $image_url = isset( $media_body->source_url ) ? $media_body->source_url : '';
                              }
                          }
                          // Зображення за замовчуванням
                          $default_image_url = '/wp-content/uploads/2023/01/woocommerce-placeholder.png';

                          // Виводимо HTML для кожного посту
                          ?>
              <a href="<?php echo esc_url( $post->link ); ?>" class="news-item">
                  <div class="news-item__picture">
                      <div class="h-object-fit">
                          <?php 
                                      // Перевіряємо, чи є зображення, якщо є - виводимо його
                                      if( !empty($image_url)){
                                          echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr( $post->title->rendered ) . '">';
                                      } else {
                                          // Якщо зображення немає - використовуємо зображення за замовчуванням
                                          echo '<img src="' . esc_url($default_image_url) . '" alt="Default Image">';
                                      }
                                      ?>
                      </div>
                  </div>
                  <div class="news-item__info">
                      <div class="news-item__info-main">
                          <h4><?php echo esc_html( $post->title->rendered ); ?></h4>
                          <time datetime="<?php echo esc_attr( $post->date ); ?>">
                              <?php 
                                          // Виводимо дату у форматі 'день.місяць.рік'
                                          echo esc_html( date( 'd.m.Y', strtotime( $post->date ) ) ); 
                                          ?>
                          </time>
                      </div>
                  </div>
              </a>
              <?php
                      }
                      ?>
          </div>
      </div>
  </section>
  <?php
    }

    return ob_get_clean();
}



function get_block_json_data($data) {
    $block_name = $data['block_name'];
    $block_path = __DIR__ . '/../src/components/' . $block_name . '/block.json';

    if (file_exists($block_path)) {
        $json_data = file_get_contents($block_path);
        return json_decode($json_data);
    }
    return new WP_Error('block_not_found', 'Block JSON file not found', array('status' => 404));
}

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/block-json/', array(
        'methods' => 'GET',
        'callback' => 'get_block_json_data',
        'args' => array(
            'block_name' => array(
                'required' => true,
                'validate_callback' => function($param, $request, $key) {
                    return is_string($param);
                }
            ),
        ),
    ));
});
// http://local-react:8888/wp-json/custom/v1/block-json/?block_name=news



function update_block_json_with_posts() {
    // Отримуємо всі пости
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1, // Всі пости
    );
    $query = new WP_Query( $args );
    $posts_data = [];

    // Формуємо масив даних
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $posts_data[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'link' => get_permalink(),
            ];
        }
        wp_reset_postdata();
    }

    // Отримуємо шлях до файлу block.json
    $block_json_path = plugin_dir_path( __FILE__ ) . '../build/components/news/block.json';

    // Перевіряємо, чи існує файл
    if ( file_exists( $block_json_path ) ) {
        // Читаємо існуючий файл block.json
        $block_json = json_decode( file_get_contents( $block_json_path ), true );

        // Оновлюємо поле 'posts'
        if ( isset( $block_json['attributes'] ) ) {
            $block_json['attributes']['news'] = $posts_data;

            // Записуємо оновлені дані в block.json
            file_put_contents( $block_json_path, json_encode( $block_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
        }
    }
}

// Викликаємо функцію для оновлення block.json при збереженні поста
function trigger_update_block_json_on_post_save( $post_id ) {
    // Перевірка, чи це авто збереження або не публікація
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
    if ( 'post' !== get_post_type( $post_id ) ) return $post_id;

    // Оновлюємо block.json
    update_block_json_with_posts();

    return $post_id;
}
add_action( 'save_post', 'trigger_update_block_json_on_post_save' );



// http://local-react:8888/wp-json/custom/v1/block-attributes/text-center?post_id=400
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/block-attributes/(?P<blockName>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'get_block_attributes',
        'args' => [
            'blockName' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return is_string($param);
                }
            ],
            'post_id' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ]
        ],
    ]);
});

function get_block_attributes($data) {
    $block_name = sanitize_text_field('app/'.$data['blockName']);
    $post_id = (int) $data['post_id'];

    // Path to the block's block.json file
    $blockPath = plugin_dir_path( __FILE__ ) . '../build/components/'.$data['blockName'].'/block.json';

    // Check if block.json file exists
    if (!file_exists($blockPath)) {
        return new WP_Error('no_block', 'Block not found', array('status' => 404));
    }

    // Read and decode block.json
    $blockData = json_decode(file_get_contents($blockPath), true);

    // Check if block has attributes
    if (isset($blockData['attributes'])) {
        $attributes = $blockData['attributes'];
        $result = get_block_value($post_id, $block_name);
        
        // If block attributes are found, return them
        if ($result !== null) {
            return $result;
        } else {
            return new WP_Error('no_attributes', 'No attributes found for this block', array('status' => 404));
        }
    } else {
        return new WP_Error('no_attributes', 'Block does not contain attributes', array('status' => 400));
    }
}

function get_block_value($post_id, $block_name) {
    // Parse all blocks in the post content
    $blocks = parse_blocks(get_post_field('post_content', $post_id));

    // Loop through blocks and find the one that matches the given block name
    if (!empty($blocks)) {
        foreach ($blocks as $block) {
            if ($block['blockName'] === $block_name) {
                // Return block attributes
                return isset($block['attrs']) ? $block['attrs'] : null;
            }
        }
    }

    // If no block found, return null
    return null;
}

} // end guard: ! function_exists( 'theme_allowed_blocks' )