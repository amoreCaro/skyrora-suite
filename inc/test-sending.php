<?php
// Додати метабокс
function sk_add_sending_metabox() {
    add_meta_box(
        'skyrora-testsend',      // ID метабоксу
        'Test Sending',            // Заголовок метабоксу
        'sk_send_message',   // Функція для відображення вмісту
        'mailing',              // Тип запису
        'side',                 // Розташування (в сайдбарі)
        'default'               // Пріоритет
    );
}
add_action('add_meta_boxes', 'sk_add_sending_metabox');


function sk_send_message($post){

  if(get_post_type() !== "mailing"){
    return;
  }

  // $users = get_users(['fields' => ['ID', 'user_email']]);
  if ($post) {  
    ?>
<div id="sk_email_metabox">

  <div id="sk_custom_subject_wrap" style="margin-top:20px; margin-bottom:10px">
    <label for="sk_custom_subject" style="margin-bottom:5px; display:block"> <?php echo esc_html('Subject')?></label>
    <input type="text" id="sk_custom_subject" placeholder="Enter subject" style="width:100%"
      value="<?php echo esc_attr('Subject Test Sender')?>">
  </div>
  <div id="sk_custom_email_wrap">
    <input type="email" id="sk_custom_email" placeholder="you@example.com" style="width:100%">
  </div>

  <p style="margin-top:20px">
    <button type="button" class="button button-primary"
      id="sk_send_test_email"><?php echo esc_html('Send Test Email')?></button>
  </p>

  <div id="sk_email_response" style="margin-top: 10px;"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // const radios = document.querySelectorAll('input[name="sk_email_method"]');
  // const userWrap = document.getElementById('sk_user_select_wrap');
  const customWrap = document.getElementById('sk_custom_email_wrap');
  const sendBtn = document.getElementById('sk_send_test_email');
  const responseBox = document.getElementById('sk_email_response');

  // radios.forEach(r => {
  //     r.addEventListener('change', () => {
  //         if (r.value === 'user') {
  //             userWrap.style.display = '';
  //             customWrap.style.display = 'none';
  //         } else {
  //             userWrap.style.display = 'none';
  //             customWrap.style.display = '';
  //         }
  //     });
  // });

  sendBtn.addEventListener('click', () => {
    let email = '';
    let subject = '';
    email = document.getElementById('sk_custom_email').value;
    subject = document.getElementById('sk_custom_subject').value;
    if (subject == '') {
      responseBox.innerHTML = '<div style="color:red;">❌ Please enter a valid subject.</div>';
      return;
    }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      responseBox.innerHTML = '<div style="color:red;">❌ Please enter a valid email address.</div>';
      return;
    }

    responseBox.innerHTML = '⏳ Sending...';

    fetch(ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'sk_send_test_email',
          nonce: '<?php echo wp_create_nonce('sk_send_test_email_nonce'); ?>',
          email: email,
          post_id: '<?php echo esc_attr($post->ID); ?>',
          subject: subject,
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          responseBox.innerHTML = '<div style="color:green;">Email sent to ' + email + '</div>';
        } else {
          responseBox.innerHTML = '<div style="color:red;">' + data.data + '</div>';
        }
      });
  });
});
</script>
<?php
  }
}
add_action('wp_ajax_sk_send_test_email', 'sk_send_test_email_ajax');
function sk_send_test_email_ajax() {
    check_ajax_referer('sk_send_test_email_nonce', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    $subject = sanitize_text_field($_POST['subject'] ?? '');
    $post_id = intval($_POST['post_id'] ?? 0);

    if (!is_email($email)) {
        wp_send_json_error('Invalid email address.');
    }
    if (empty($subject)) {
        wp_send_json_error('Invalid subject.');
    }
    if (!$post_id || get_post_type($post_id) !== 'mailing') {
        wp_send_json_error('Invalid post ID.');
    }

    $post = get_post($post_id);
    if (!$post) {
        wp_send_json_error('Post not found.');
    }

    // Важливо: фільтруємо контент напряму
    $raw_content = $post->post_content;
    $message = '<html lang="en"><head>
                  <meta charset="UTF-8">
                  <meta name="viewport" content="width=device-width, initial-scale=1.0">
                  <style>
                      *{
                          margin: 0;
                          padding: 0;
                          box-sizing: border-box;
                      }
                  </style>
              </head>
              <body>
                <div id="wrapper" style="max-width: 100%; margin: 0px auto; background-color: rgb(24, 27, 36); padding: 0px 20px;">
                  <div class="container" style="max-width: 640px; width: 100%; background-color: #fff;  margin: 0 auto; min-height: 50vh; box-sizing: border-box; padding: 0px;">';
    // $message .= apply_filters('the_content', $raw_content);
    $message .= $raw_content;
    $message .= '</div></div></body></html>';

    if (empty(trim($message))) {
        wp_send_json_error('Post content is empty after filtering.');
    }

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    $sent = wp_mail($email, $subject, $message, $headers);

    if ($sent) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to send email. Please check your mail settings.');
    }
}

?>