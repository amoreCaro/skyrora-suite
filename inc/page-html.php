<?php
function sk_page_to_html(){

    if( get_post_type() == "mailing") {

        $page_id = get_the_id();
        $page = get_post($page_id);
        $page_title = $page->post_title;
        $page_slug = $page->post_name;
        // $page_content = apply_filters('the_content', $page->post_content);
        $page_content = get_the_content($page_id);
        $page_content = str_replace('<p>', '<p style="margin-bottom: 1rem; color: #0e0f17; font-size: 16px; line-height: 1.6;">', $page_content);
        $page_content = str_replace('<h1>', '<h1 style="margin-bottom: 1rem; color: #0e0f17; font-size: 16px; line-height: 1.6;">', $page_content);
        $page_content = str_replace('<h2>', '<h2 style="margin-bottom: 1rem; color: #0e0f17; font-size: 16px; line-height: 1.6;">', $page_content);
        $page_content = str_replace('<h3>', '<h3 style="margin-bottom: 1rem; color: #0e0f17; font-size: 16px; line-height: 1.6;">', $page_content);
        $page_content = str_replace('<h4>', '<h4 style="margin-bottom: 1rem; color: #0e0f17; font-size: 16px; line-height: 1.6;">', $page_content);
        $page_content = str_replace('<ol>', '<ol style="margin-left: 20px;">', $page_content);
        $page_content = str_replace('<ul>', '<ul style="margin-left: 20px;">', $page_content);
        $page_content = str_replace('<a>', '<a style="color: #164bdc; text-decoration: none;">', $page_content);
        $page_content = preg_replace('/<img([^>]*)>/', '<img$1 style="width: 100%; height: auto; object-fit: contain;">', $page_content);
        $page_content = str_replace('<b>', '<b style="font-weight: bold;">', $page_content);
        $page_content = str_replace('<strong>', '<strong style="font-weight: bold;">', $page_content);
        $page_content = str_replace('<figure>', '<figure style="width: 100%; padding: 0; margin: 0;">', $page_content);
        $page_content = str_replace('<li>', '<li style="color: #0e0f17; line-height: 1.6;">', $page_content);
        
       
        // Створюємо HTML файл
        $html_content = '
            <!DOCTYPE html>
            <html lang="en" style="
                padding: 0;
                margin: 0;
                box-sizing: border-box;
            ">
            <head>
                <meta charset="UTF-8">
                <title>' . esc_html($page_title) . '</title>
            </head>
            <body style="
                background-color: #181b24;
                font-family: merriweather sans, helvetica neue, helvetica, arial, sans-serif;
                color: #0e0f17;
                font-size: 16px;
                padding: 50px 0;
                line-height: 1.6;">
                    <div class="wrapper" style="
                        max-width: 1600px;
                        background-color: #181b24;
                        margin: 0px auto;"
                    >
                    <div class="logo" style="
                        display: block;
                        width: 100%;
                        text-align: center;
                        margin: 20px auto;">
                        <img src="https://skyrora.com/wp-content/uploads/2024/07/Logotype.png" alt="logo">
                    </div>
                    <div class="section section-newsletter" style="
                        background: white;
                        max-width: 660px;
                        margin: 0 auto;
                        ">
                        <div class="container" style="padding: 2rem;">
                            '.$page_content.'
                        </div>
                   </div>
                    <footer class="footer" style="
                        background-color: #181b24;
                        color: white;
                        padding: 20px 0;">
                        <div class="footer__nav" style="
                            display: block;
                            width: 100%;
                            text-align: center;
                            margin: 20px auto;">
                            <a target="_blank" href="https://skyrora.com/terms" style="text-decoration:none; color: white; margin-right: 40px;">TERMS OF USE</a>
                            <a target="_blank" href="https://skyrora.com/privacy" style="text-decoration:none; color: white; margin-right: 40px;">PRIVACY POLICY</a>
                            <a target="_blank" href="https://skyrora.com/unsubscribe" style="text-decoration:none; color: white; margin-right: 40px;">UNSUBSCRIBE</a>
                        </div>
                        <div class="footer__copyright" style="
                            color: white; 
                            display: block;
                            width: 100%;
                            text-align: center;
                            margin: 40px auto;">
                            © 2024, SKYRORA LIMITED
                        </div>
                        <div class="footer__media" style="
                            display: block;
                            width: 100%;
                            text-align: center;
                            margin: 20px 0 40px auto;
                            ">
                                <a target="_blank" href="https://www.facebook.com/Skyrora" style="color: white; margin-right: 20px; text-decoration: none;">
                                    <img width="40px" height="40px" src="https://skyrora.com/wp-content/uploads/2024/07/Facebook.png" alt="facebook" style="object-fit: contain;">
                                </a>

                                <a target="_blank" href="https://www.youtube.com/channel/UCbvhnK_lUF1vH8jCTy0y2-w" style="color: white; margin-right: 20px; text-decoration: none;">
                                    <img width="40px" height="40px" src="https://skyrora.com/wp-content/uploads/2024/07/Youtube.png" alt="youtube" style="object-fit: contain;">
                                </a>

                                <a target="_blank" href="https://www.instagram.com/skyrora/?igshid=1bzznae6x8b1f" alt="instagram" style="color: white; margin-right: 20px; text-decoration: none;">
                                    <img width="40px" height="40px" src="https://skyrora.com/wp-content/uploads/2024/07/Social.png" style="object-fit: contain;">
                                </a>

                                <a target="_blank" href="https://twitter.com/Skyrora_Ltd" alt="twitter" style="color: white; margin-right:20px; text-decoration: none;">
                                    <img width="40px" height="40px" src="https://skyrora.com/wp-content/uploads/2024/07/Twitter.png" style="object-fit: contain;">
                                </a>

                                <a target="_blank" href="https://www.linkedin.com/company/25012809/" alt="linkedin" style="color: white; margin-right:20px; text-decoration: none;">
                                    <img width="40px" height="40px" src="https://skyrora.com/wp-content/uploads/2024/07/Linkedin.png" style="object-fit: contain;">
                                </a>
                        </div>
                    </footer>
                </div>
            </body>
        </html>
        ';

        // Шлях до папки, де буде збережений HTML файл
        $save_path = SK_PLUGIN_DIR . '/pages/';
        $html_file = $save_path . $page_slug.'.html';

        // Зберігаємо HTML файл
        file_put_contents($html_file, $html_content);

    }
}

add_action('admin_footer', 'sk_page_to_html');


// // Додати метабокс
// function sk_add_custom_metabox() {
//     add_meta_box(
//         'skyrora-postbox',      // ID метабоксу
//         'HTML Page',            // Заголовок метабоксу
//         'sk_postbox_content',   // Функція для відображення вмісту
//         'mailing',              // Тип запису
//         'side',                 // Розташування (в сайдбарі)
//         'default'               // Пріоритет
//     );
// }
// add_action('add_meta_boxes', 'sk_add_custom_metabox');


// function sk_postbox_content(){

//     $page_id = get_the_ID();
//     $page = get_post($page_id);
//     $page_slug = $page->post_name;

//     $save_path = SK_PLUGIN_DIR_URL . 'pages/';
//     $html_file = $save_path . $page_slug.'.html';

//     echo 'Створено HTML файл: <a target="_blank" href="' . $html_file . '">' . basename($html_file) . '</a>';

// }