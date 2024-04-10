<?php
/* =================================
  タイトル
================================= */
function theme_slug_setup()
{
    add_theme_support('title-tag');
}
add_action('after_setup_theme', 'theme_slug_setup');
//titleタグのセパレータ
function title_separator($sep)
{
    $sep = '|';
    return $sep;
}
add_filter('document_title_separator', 'title_separator');


/* =================================
  jqueryを任意のものに
================================= */
function add_my_scripts()
{
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js', array(), '2.2.4', false);
        wp_enqueue_script('jquery-migrate', '//cdnjs.cloudflare.com/ajax/libs/jquery-migrate/1.2.1/jquery-migrate.min.js', array(), '1.2.1', false);
    }
}
add_action('wp_enqueue_scripts', 'add_my_scripts');


/* =================================
  アイキャッチ有効化
================================= */
add_theme_support('post-thumbnails');


/* =================================
  抜粋リンク
================================= */
function new_excerpt_more($post)
{
    return '<a href="'. get_permalink($post->ID) . '">' . '...' . '</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');


/* =================================
  抜粋文字数変更
================================= */
function custom_excerpt_length($length)
{
    return 200;
}
add_filter('excerpt_length', 'custom_excerpt_length', 999);


/* =================================
  本文抜粋、冒頭の160文字を表示
================================= */
function my_get_lead($content, $length = 160)
{
    $stripped = strip_tags($content);
    $lead= mb_substr($stripped, 0, $length, 'utf-8');
    if (mb_strlen($stripped, 'utf-8') > $length) {
        $lead.= '...';
    }
    return $lead;
}


/* =================================
  ACFに入力したiframeが投稿者権限で触ると消えちゃう
================================= */
add_filter('content_save_pre', 'test_save_pre');
function test_save_pre($content)
{
    global $allowedposttags;

    // iframeとiframeで使える属性を指定する
    $allowedposttags['iframe'] = array('class' => array() , 'src'=>array() , 'width'=>array(),
    'height'=>array() , 'frameborder' => array() , 'scrolling'=>array(),'marginheight'=>array(),
    'marginwidth'=>array());

    return $content;
}


/* =================================
  勝手に絵文字になるのを防ぐ
================================= */
function disable_emoji()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
add_action('init', 'disable_emoji');


/* =================================
  固定ページの自動整形無効

固定ページをお客さんに触ってもらうような場合、pタグがおかしくなったりするのを防ぎます。
================================= */
add_filter('the_content', 'wpautop_filter', 9);
function wpautop_filter($content)
{
    global $post;
    $remove_filter = false;

    $arr_types = array('page'); //自動整形を無効にする投稿タイプを記述 ＝固定ページ
    $post_type = get_post_type($post->ID);
    if (in_array($post_type, $arr_types)) {
        $remove_filter = true;
    }

    if ($remove_filter) {
        remove_filter('the_content', 'wpautop');
        remove_filter('the_excerpt', 'wpautop');
    }

    return $content;
}


/* =================================
  短縮URLを取得ボタンを編集画面に表示
================================= */
add_filter('get_shortlink', function ($shortlink) {return $shortlink;});


/* =================================
  ページのスラッグを取得

スラッグで記述を分けたりする際に使います。
ページごとにclassを変えたりする場合とか...
================================= */
function get_page_slug($page_id)
{
    $page = get_page($page_id);
    return $page->post_name;
}

/* =================================
  ホームURL

管理画面のエディタ内で使うショートコードです。
================================= */
add_shortcode('home', 'shortcode_home');
function shortcode_home($atts, $content = '')
{
    return home_url().$content;
}

/* =================================
  テーマURL
================================= */
add_shortcode('tp', 'shortcode_tp');
function shortcode_tp($atts, $content = '')
{
    return get_stylesheet_directory_uri().$content;
}


/* =================================
  ログイン画面カスタマイズ
================================= */
function login_logo_image()
{
    echo '<style type="text/css">#login h1 a { background: url('.get_bloginfo('template_url') . '/assets/img/logo.png) no-repeat;width:300px;height:172px;background-size:100%; }</style>';
}
add_action('login_head', 'login_logo_image');
// ロゴのリンク先を指定
function my_login_logo_url()
{
    return get_bloginfo('url');
}
add_filter('login_headerurl', 'my_login_logo_url');
// ロゴのtitleテキストを指定
function my_login_logo_tit()
{
    return get_option('blogname');
}
add_filter('login_headertitle', 'my_login_logo_tit');

function admin_favicon()
{
    echo '<link rel="shortcut icon" href="'.get_bloginfo('template_url').'/assets/img/favicon.ico">';
}
add_action('admin_head', 'admin_favicon');


/* =================================
  カスタム投稿増えてもメディアの上
================================= */
function customize_menus()
{
    global $menu;
    $menu[19] = $menu[10];  //メディアの移動
    unset($menu[10]);
}
add_action('admin_menu', 'customize_menus');


/* =================================
カスタム投稿タイプ
================================= */

add_action('init', 'create_post_type'); // 初期化時に呼び出して
//ここから条件
function create_post_type()
{
    /* ----------支援プロジェクトroom ---------- */
    register_post_type('room', [ // 投稿タイプ名の定義
      'labels' => [
        'name' => '客室', // 管理画面上で表示する投稿タイプ名
        'singular_name' => 'room', // カスタム投稿の識別名
        'all_items' => 'すべてを表示',
      ],
      'public' => true, // 投稿タイプをpublicにするか
      'has_archive' => true, // アーカイブ機能ON/OFF
      'menu_position' => 5, // 管理画面上での配置場所
      'show_in_rest' => true, // 5系から出てきた新エディタ「Gutenberg」を有効にする
      'menu_icon' => 'dashicons-admin-multisite',
      'supports' => [ "title", "author" ]
    ]);

    // /* ----------技術シーズ紹介seeds ---------- */
    // register_post_type('seeds', [ // 投稿タイプ名の定義
    //   'labels' => [
    //     'name' => '技術シーズ紹介', // 管理画面上で表示する投稿タイプ名
    //     'singular_name' => 'seeds', // カスタム投稿の識別名
    //     'all_items' => 'すべてを表示',
    //   ],
    //   'public' => true, // 投稿タイプをpublicにするか
    //   'has_archive' => true, // アーカイブ機能ON/OFF
    //   'menu_position' => 5, // 管理画面上での配置場所
    //   'show_in_rest' => true, // 5系から出てきた新エディタ「Gutenberg」を有効にする
    //   'menu_icon' => 'dashicons-admin-customizer',
    //   'supports' => [ "title", "thumbnail", "editor", "author" ]
    // ]);

    // /* ----------研究者紹介researcher ---------- */
    // register_post_type('researcher', [ // 投稿タイプ名の定義
    //   'labels' => [
    //     'name' => '研究者紹介', // 管理画面上で表示する投稿タイプ名
    //     'singular_name' => 'researcher', // カスタム投稿の識別名
    //     'all_items' => 'すべてを表示',
    //   ],
    //   'public' => true, // 投稿タイプをpublicにするか
    //   'has_archive' => true, // アーカイブ機能ON/OFF
    //   'menu_position' => 5, // 管理画面上での配置場所
    //   'show_in_rest' => true, // 5系から出てきた新エディタ「Gutenberg」を有効にする
    //   'menu_icon' => 'dashicons-businessperson',
    //   'supports' => [ "title", "thumbnail", "editor", "author" ]
    // ]);

    // /* ---------活動実績report ---------- */
    // register_post_type('report', [ // 投稿タイプ名の定義
    //   'labels' => [
    //     'name' => '活動実績', // 管理画面上で表示する投稿タイプ名
    //     'singular_name' => 'report', // カスタム投稿の識別名
    //     'all_items' => 'すべてを表示',
    //   ],
    //   'public' => true, // 投稿タイプをpublicにするか
    //   'has_archive' => true, // アーカイブ機能ON/OFF
    //   'menu_position' => 5, // 管理画面上での配置場所
    //   'show_in_rest' => true, // 5系から出てきた新エディタ「Gutenberg」を有効にする
    //   'menu_icon' => 'dashicons-format-aside',
    //   'supports' => [ "title", "thumbnail", "editor", "author" ]
    // ]);

    // /* ---------会員情報members ---------- */
    // register_post_type('members', [ // 投稿タイプ名の定義
    //   'labels' => [
    //     'name' => '会員情報', // 管理画面上で表示する投稿タイプ名
    //     'singular_name' => 'members', // カスタム投稿の識別名
    //     'all_items' => 'すべてを表示',
    //   ],
    //   'public' => true, // 投稿タイプをpublicにするか
    //   'has_archive' => true, // アーカイブ機能ON/OFF
    //   'menu_position' => 7, // 管理画面上での配置場所
    //   'show_in_rest' => true, // 5系から出てきた新エディタ「Gutenberg」を有効にする
    //   'menu_icon' => 'dashicons-universal-access-alt',
    //   'supports' => [ "title", "author" ]
    // ]);
}


/* =================================
カスタム投稿タイプ　タクソノミー
================================= */

// register_taxonomy(
//     'room-cat',
//     'room',
//     array(
//     'show_in_rest' => true,
//     'hierarchical' => true,
//     'update_count_callback' => '_update_post_term_count',
//     'label' => 'カテゴリー',
//     'singular_label' => 'カテゴリー',
//     'public' => true,
//     'show_ui' => true,
//     'show_admin_column' => true
//   )
// );





