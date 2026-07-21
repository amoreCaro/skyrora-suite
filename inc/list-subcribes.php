<?php


// function skyrora_list_subscriber_metabox() {
//     add_meta_box(
//         'skyrora-subscribers',     
//         'List subscribers',         
//         'skyrora_list_subscribers', 
//         'mailing',                 
//         'normal',              
//         'high'                  
//     );
// }
// add_action('add_meta_boxes', 'skyrora_list_subscriber_metabox');

// function skyrora_list_subscribers() {
//     global $wpdb;

//     $mailpoet_table = $wpdb->prefix . 'mailpoet_subscribers';

//     $sql = "SELECT * FROM $mailpoet_table";
//     $results = $wpdb->get_results($sql);

//     if ($results === false) {
//         die("Помилка запиту: " . $wpdb->last_error);
//     }
    
//     if (!empty($results)) {
//         foreach ($results as $subscriber) {
//             $email = $subscriber->email;

//             // Перевіряємо, чи вже існує пост із такою електронною адресою
//             $existing_post = get_posts([
//                 'post_type'  => 'subscription',
//                 'meta_key'   => 'subscriber_email',
//                 'meta_value' => $email,
//                 'numberposts' => 1,
//             ]);

//             if (!$existing_post) {
//                 // Створюємо новий пост для підписника
//                 $post_id = wp_insert_post([
//                     'post_title'  => $email,  // Заголовок поста — email підписника
//                     'post_type'   => 'subscription',
//                     'post_status' => 'publish',
//                 ]);

//                 if (!is_wp_error($post_id)) {
//                     // Додаємо email як метаполе
//                     update_post_meta($post_id, 'subscriber_email', $email);
//                 }
//             }
//         }

//         echo '<div class="notice notice-success"><p>Усі підписники збережені у пост-тип "subscription".</p></div>';
//     } else {
//         echo '<div class="notice notice-warning"><p>Підписників не знайдено.</p></div>';
//     }

// }