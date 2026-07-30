<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( get_the_title() ); ?></title>
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .sk-mailing-preview-back {
            position: fixed;
            top: 120px;
            left: 0;
            z-index: 9999;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 40px;
            padding: 10px 18px 10px 14px;
            background: #3858E9;
            color: #fff;
            font: 600 14px/1.2 -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            text-decoration: none;
            transform: translateX(-88%);
            transition: transform 0.25s ease, background 0.25s ease;
        }

        .sk-mailing-preview-back:hover,
        .sk-mailing-preview-back:focus {
            background: #2B47C7;
            transform: translateX(0);
            outline: none;
        }

        .sk-mailing-preview-back__arrow {
            display: block;
            font-size: 16px;
            line-height: 1;
        }
    </style>
</head>
<body>
    <?php
    $post_id  = (int) get_queried_object_id();
    $edit_url = $post_id ? get_edit_post_link( $post_id, 'raw' ) : '';

    if ( $edit_url && current_user_can( 'edit_post', $post_id ) ) :
        ?>
        <a class="sk-mailing-preview-back" href="<?php echo esc_url( $edit_url ); ?>">
            <span class="sk-mailing-preview-back__arrow" aria-hidden="true">&larr;</span>
            <?php esc_html_e( 'Back', 'skyrora-mailing' ); ?>
        </a>
        <?php
    endif;

    if ( have_posts() ) {
        while ( have_posts() ) {
            the_post();
            the_content();
        }
    }
    ?>
</body>
</html>
