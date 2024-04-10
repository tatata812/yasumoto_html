<?php
/* =================================
  タイトル

<title>タグを使わずにfunctionsで表示させていました。
HTMLの段階では<title>タグが入っていますが、WordPress化の段階で削除します。
<title>タグ、下層がややこしくなったりするのでfunctionsに書くと楽です。。


================================= */
 function theme_slug_setup() {
    add_theme_support( 'title-tag' );
 }
 add_action( 'after_setup_theme', 'theme_slug_setup' );
 //titleタグのセパレータ
 function title_separator( $sep ){
     $sep = '|';
     return $sep;
 }
 add_filter( 'document_title_separator', 'title_separator' );


/* =================================
  jqueryを任意のものに

WordPressが自動で読み込むjsのバージョンを操るやつです。
なんかおまじない的に書いてました。。
================================= */
function add_my_scripts() {
  if ( !is_admin() ) {
  wp_deregister_script( 'jquery');
  wp_enqueue_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js', array(), '2.2.4', false);
  wp_enqueue_script( 'jquery-migrate', '//cdnjs.cloudflare.com/ajax/libs/jquery-migrate/1.2.1/jquery-migrate.min.js', array(), '1.2.1', false);
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
function new_excerpt_more($post) {
	return '<a href="'. get_permalink($post->ID) . '">' . '...' . '</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');


/* =================================
  抜粋文字数変更
================================= */
function custom_excerpt_length( $length ) {
     return 200;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );


/* =================================
  本文抜粋、冒頭の160文字を表示
================================= */
function my_get_lead ($content, $length = 160) {
  $stripped = strip_tags($content);
  $lead= mb_substr($stripped, 0, $length, 'utf-8');
  if (mb_strlen($stripped, 'utf-8') > $length) {
    $lead.= '...';
  }
  return $lead;
}


/* =================================
  ACFに入力したiframeが投稿者権限で触ると消えちゃう

勝手に消えちゃうんですよね。。
安穏会館では必要ないとは思いますが、他でたまにあるので、セットに入れちゃってます。
================================= */
add_filter('content_save_pre','test_save_pre');
function test_save_pre($content){
	global $allowedposttags;

	// iframeとiframeで使える属性を指定する
	$allowedposttags['iframe'] = array('class' => array () , 'src'=>array() , 'width'=>array(),
	'height'=>array() , 'frameborder' => array() , 'scrolling'=>array(),'marginheight'=>array(),
	'marginwidth'=>array());

	return $content;
}


/* =================================
  勝手に絵文字になるのを防ぐ
================================= */
function disable_emoji() {
     remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
     remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
     remove_action( 'wp_print_styles', 'print_emoji_styles' );
     remove_action( 'admin_print_styles', 'print_emoji_styles' );
     remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
     remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
     remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'disable_emoji' );


/* =================================
  固定ページの自動整形無効

固定ページをお客さんに触ってもらうような場合、pタグがおかしくなったりするのを防ぎます。
================================= */
add_filter('the_content', 'wpautop_filter', 9);
function wpautop_filter($content) {
  global $post;
  $remove_filter = false;
 
  $arr_types = array('page'); //自動整形を無効にする投稿タイプを記述 ＝固定ページ
  $post_type = get_post_type( $post->ID );
  if (in_array($post_type, $arr_types)){
                $remove_filter = true;
        }
 
  if ( $remove_filter ) {
    remove_filter('the_content', 'wpautop');
    remove_filter('the_excerpt', 'wpautop');
  }
 
  return $content;
}


/* =================================
  短縮URLを取得ボタンを編集画面に表示

倫理で使っているあの短縮URLボタンです。何かと便利なので...
================================= */
add_filter( 'get_shortlink', function( $shortlink ) {return $shortlink;} );


/* =================================
  ページのスラッグを取得

スラッグで記述を分けたりする際に使います。
ページごとにclassを変えたりする場合とか...
================================= */
function get_page_slug($page_id) {
    $page = get_page($page_id);
    return $page->post_name;
}

/* =================================
  ホームURL

管理画面のエディタ内で使うショートコードです。
================================= */
add_shortcode( 'home', 'shortcode_home' );
function shortcode_home( $atts, $content = '' ) {
	return home_url().$content;
}

/* =================================
  テーマURL
================================= */
add_shortcode( 'tp', 'shortcode_tp' );
function shortcode_tp( $atts, $content = '' ) {
	return get_stylesheet_directory_uri().$content;
}


/* =================================
  ログイン画面カスタマイズ

ログイン画面にそのサイトのロゴを表示します！
サイトによって、ロゴの画像パスと横幅300pxの場合の縦幅を変更します。
================================= */
function login_logo_image() {
    echo '<style type="text/css">#login h1 a { background: url('.get_bloginfo('template_url') . '/assets/img/common/logo.jpg) no-repeat;width:300px;height:172px;background-size:100%; }</style>';
}
add_action('login_head', 'login_logo_image');
// ロゴのリンク先を指定
function my_login_logo_url() {
 return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'my_login_logo_url' );
// ロゴのtitleテキストを指定
function my_login_logo_tit() {
 return get_option( 'blogname' );
}
add_filter( 'login_headertitle', 'my_login_logo_tit' );

function admin_favicon() {
  echo '<link rel="shortcut icon" href="'.get_bloginfo('template_url').'/assets/img/favicon.ico">';
}
add_action('admin_head', 'admin_favicon');


/* 前後リンクを生成（長いタイトルを丸める）
============================================= 

https://www.hiraya-ube.com/blog/%e7%b9%b0%e3%82%8a%e4%b8%8a%e3%81%92%e8%bf%94%e6%b8%88[…]1%bb%e3%81%86%e3%81%8c%e3%81%84%e3%81%84%e3%81%ae%ef%bc%9f/
このページの下部にある、ページの前後リンクですが
タイトルを表示して、長い時は「…」になるようにしています。
テンプレート側は、平家工房うべを参考にしてみてください！




*/

function Custom_previous_post_link($maxlen = -1, $format='&laquo; %link', $link='%title', $in_same_cat = false, $excluded_categories = '') {
Custom_adjacent_post_link($maxlen, $format, $link, $in_same_cat, $excluded_categories, true, $maxlen);
}
function Custom_next_post_link($maxlen = -1, $format='%link &raquo;', $link='%title', $in_same_cat = false, $excluded_categories = '') {
Custom_adjacent_post_link($maxlen, $format, $link, $in_same_cat, $excluded_categories, false);
}

function Custom_adjacent_post_link($maxlen = -1, $format='&laquo; %link', $link='%title', $in_same_cat = false, $excluded_categories = '', $previous = true) {

if ( $previous && is_attachment() )
$post = & get_post($GLOBALS['post']->post_parent);
else
$post = get_adjacent_post($in_same_cat, $excluded_categories, $previous);

if ( !$post )
return;

$tCnt = mb_strlen( $post->post_title, get_bloginfo('charset') );
if(($maxlen > 0)&&($tCnt > $maxlen)) {
$title = mb_substr( $post->post_title, 0, $maxlen, get_bloginfo('charset') ) . '…';
} else {
$title = $post->post_title;
}

if ( empty($post->post_title) )
$title = $previous ? __('Previous Post') : __('Next Post');

$title = apply_filters('the_title', $title, $post->ID);
$date = mysql2date(get_option('date_format'), $post->post_date);
$rel = $previous ? 'prev' : 'next';

$string = '<a href="'.get_permalink($post).'" rel="'.$rel.'">';
$link = str_replace('%title', $title, $link);
$link = str_replace('%date', $date, $link);
$link = $string . $link . '</a>';

$format = str_replace('%link', $link, $format);
echo $format;
}

