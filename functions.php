<?php

// Define the version as a constant so we can easily replace it throughout the theme
define('LESS_VERSION', 2.0);

/*-----------------------------------------------------------------------------------*/
/* Add Rss to Head
/*-----------------------------------------------------------------------------------*/
add_theme_support('automatic-feed-links');


/*-----------------------------------------------------------------------------------*/
/* register main menu
/*-----------------------------------------------------------------------------------*/
register_nav_menus(
    array(
        'primary' => __('Primary Menu', 'less'),
    )
);

/*-----------------------------------------------------------------------------------*/
/* Enque Styles and Scripts
/*-----------------------------------------------------------------------------------*/

function less_scripts()
{

    // theme styles
    wp_enqueue_style('less-style', get_template_directory_uri() . '/style.css', '10000', 'all');

    // theme js
    wp_enqueue_script('main.js', get_template_directory_uri() . '/dist/main.min.js', array('jquery'), LESS_VERSION, true);
}

add_action('wp_enqueue_scripts', 'less_scripts');

function send_notif_to_browser($post_ID)
{
    $link = get_post_permalink($post_ID);
    $post = get_post($post_ID);
    $title = $post->post_title;
    $content = mb_substr(strip_tags($post->post_content), 0, 130) . " ...";

    $TOKEN = PUSHE_API_KEY;
    $data = array(
        "applications" => ["sejil"],
        "notification" => array(
            "title" => $title,
            "content" => $content,
            "visibility" => true,
            "show_app" => true,
            "url" => $link,
            "icon" => "https://sejil.me/favicon.png"
        )
    );
    $data_string = json_encode($data);

    $ch = curl_init("http://panel.pushe.co/api/v1/notifications/");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Accept: application/json",
        "Authorization: Token " . $TOKEN,
    ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

function publish_post_fun($post_ID)
{
    if (get_post_status($post_ID) == 'publish') {
        send_notif_to_browser($post_ID);
    }
}

add_action('save_post', 'publish_post_fun', 11, 1);

function no_notification()
{
    remove_action('save_post', 'publish_post_fun', 11, 1);
}

add_action('publish_to_publish', 'no_notification');
