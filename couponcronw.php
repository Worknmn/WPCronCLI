<?php
error_reporting(E_ALL);

header('Content-Type: application/javascript; charset=UTF-8');

// указываем, что нам нужен минимум от WP
define('SHORTINIT', true);

define('WP_USE_THEMES', false);

define('DISABLE_WP_CRON', true);
//define('DOING_CRON', true);
define( 'WPINC', 'wp-includes' );


//var_dump($_SERVER);
//die();

if (is_cli()) {
  $_SERVER['HTTP_HOST'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '7lamp.ru';
  $_SERVER['SERVER_NAME'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '7lamp.ru';
  ini_set( 'default_charset', 'UTF-8' );
  if (version_compare( phpversion(), '5.6.0', '>=' ) ) { 
    ini_set("input_encoding", "UTF-8");
    ini_set("internal_encoding", "UTF-8");
    ini_set("output_encoding", "UTF-8");    
  } else {
    ini_set('mbstring.internal_encoding','UTF-8');  
    iconv_set_encoding("input_encoding", "UTF-8");
    iconv_set_encoding("internal_encoding", "UTF-8");
    iconv_set_encoding("output_encoding", "UTF-8");
  }
  setlocale(LC_ALL, 'ru_RU.UTF-8');
  //ini_set("default_charset", "UTF-8");
  mb_internal_encoding("UTF-8");
  ini_set('mbstring.func_overload',7);
  
  $use_mysqli = FALSE;
  
	if ( function_exists( 'mysqli_connect' ) ) {
		if ( defined( 'WP_USE_EXT_MYSQL' ) ) {
			$use_mysqli = ! WP_USE_EXT_MYSQL;
		} elseif ( version_compare( phpversion(), '5.5', '>=' ) || ! function_exists( 'mysql_connect' ) ) {
			$use_mysqli = true;
		}
	}
  
  require_once(__DIR__ . '/couponcronconf.php');

  $dbh = do_connect( $dbhost, $dbuser, $dbpassword, $use_mysqli );

	if ( $dbh ) {
    if ( ! has_cap($dbh, $use_mysqli, 'utf8mb4')) {
      define('DB_CHARSET', 'utf8');
      echo "Setup DB_CHARSET utf8\n";
      var_dump(DB_CHARSET);
    }
	}
  
} 

//define( 'TABLE_PREFIX', 'wp_8_' );

//echo_memory_usage();

// подгружаем среду WordPress
// WP делает некоторые проверки и подгружает только самое необходимое для подключения к БД
//die('1');
require_once( __DIR__ . '/../../../wp-load.php' );

//phpinfo();
global $wpdb;
global $blog_id;

echo "Checked utf8mb4\n";
var_dump($wpdb->has_cap('utf8mb4'));


echo "ABSPATH\n";
var_dump(ABSPATH);

//echo "WP_SETUP_CONFIG\n";
//var_dump(defined("WP_SETUP_CONFIG"));

error_reporting(E_ALL);

//$_options = $wpdb->get_results("SELECT `option_name`, `option_value` FROM $wpdb->options WHERE `option_name` IN ('siteurl', '".$wpdb->prefix."user_roles')", 'OBJECT_K');
//if (!$_options) exit;

//die('2');

//wp_initial_constants();
//die('3');

require_once( ABSPATH . WPINC . '/l10n.php' );
require( ABSPATH . WPINC . '/formatting.php' );
require( ABSPATH . WPINC . '/capabilities.php' );
require( ABSPATH . WPINC . '/class-wp-roles.php' );
require( ABSPATH . WPINC . '/class-wp-role.php' );
require( ABSPATH . WPINC . '/class-wp-user.php' );
require( ABSPATH . WPINC . '/query.php' );
require( ABSPATH . WPINC . '/theme.php' );
require( ABSPATH . WPINC . '/user.php' );
//require( ABSPATH . WPINC . '/class-wp-user-query.php' );
require( ABSPATH . WPINC . '/session.php' );
require( ABSPATH . WPINC . '/meta.php' );
require( ABSPATH . WPINC . '/class-wp-meta-query.php' );
require( ABSPATH . WPINC . '/general-template.php' );
require( ABSPATH . WPINC . '/link-template.php' );
require( ABSPATH . WPINC . '/author-template.php' );
require( ABSPATH . WPINC . '/post.php' );
require( ABSPATH . WPINC . '/class-wp-post.php' );
require( ABSPATH . WPINC . '/post-template.php' );
require( ABSPATH . WPINC . '/revision.php' );
require( ABSPATH . WPINC . '/post-formats.php' );
require( ABSPATH . WPINC . '/comment.php' );
require( ABSPATH . WPINC . '/class-wp-comment.php' );
require( ABSPATH . WPINC . '/class-wp-comment-query.php' );
require( ABSPATH . WPINC . '/class-wp-rewrite.php' );
require( ABSPATH . WPINC . '/kses.php' );
require( ABSPATH . WPINC . '/cron.php' );
require( ABSPATH . WPINC . '/taxonomy.php' );
require( ABSPATH . WPINC . '/class-wp-term.php' );
require( ABSPATH . WPINC . '/class-wp-tax-query.php' );
//require( ABSPATH . WPINC . '/option.php' );
require( ABSPATH . WPINC . '/nav-menu.php' );
require( ABSPATH . WPINC . '/pluggable.php' );
//die('4');

// константы, чтобы не ругались 
wp_plugin_directory_constants();
wp_cookie_constants();

//создаём таксаномии
create_initial_taxonomies();
//create_initial_post_types();

if (is_cli()) {
  $GLOBALS['wp_rewrite'] = new WP_Rewrite();
} 

// Отключаем сам REST API
add_filter('rest_enabled', '__return_false');

// Отключаем фильтры REST API
remove_action( 'xmlrpc_rsd_apis',            'rest_output_rsd' );
remove_action( 'wp_head',                    'rest_output_link_wp_head', 10, 0 );
remove_action( 'template_redirect',          'rest_output_link_header', 11, 0 );
remove_action( 'auth_cookie_malformed',      'rest_cookie_collect_status' );
remove_action( 'auth_cookie_expired',        'rest_cookie_collect_status' );
remove_action( 'auth_cookie_bad_username',   'rest_cookie_collect_status' );
remove_action( 'auth_cookie_bad_hash',       'rest_cookie_collect_status' );
remove_action( 'auth_cookie_valid',          'rest_cookie_collect_status' );
remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );

// Отключаем события REST API
remove_action( 'init',          'rest_api_init' );
remove_action( 'rest_api_init', 'rest_api_default_filters', 10, 1 );
remove_action( 'parse_request', 'rest_api_loaded' );

// Отключаем Embeds связанные с REST API
remove_action( 'rest_api_init',          'wp_oembed_register_route'              );
remove_filter( 'rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4 );

remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
// если собираетесь выводить вставки из других сайтов на своем, то закомментируйте след. строку.
remove_action( 'wp_head',                'wp_oembed_add_host_js'                 );

remove_action('init', 'kses_init');
remove_action( 'set_current_user', 'kses_init' );

remove_filter('title_save_pre', 'wp_filter_kses');

$optionsname = 'coupon_options';

$options = get_option($optionsname);

error_reporting(E_ALL);

// если не получилось штатно unserialize
if (is_cli() AND (is_bool($options) AND ! $options)) {
  echo "Try to fix options string(problem serialize/unserialize)\nMay be charset coding DB problem\n";
  $row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $optionsname ) );
  $val = $row->option_value;
  if (is_serialized($val)) {
    $options = @unserialize($val);
    if ( ! $options) {
      //$fixed_data = preg_replace_callback ( '!s:(\d+):"(.*?)";!', function($match) {      
      //  return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
      //    },$bad_data );
      $val = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $val);
      $options = @unserialize($val);
      if (is_bool($options) AND ! $options) {
        echo "Error in option = coupon_options\n";
        die();
      }
    }
  } else {
    $options = $val;
  }
  
} 


echo "DB_CHARSET\n";
var_dump(DB_CHARSET);

//echo "wpdb\n";
//var_dump($wpdb);

//echo "options\n";
//var_dump($options);

$timewait = (int) $options['timewait'];
$autopostcountforstep = (int) $options['autopostcountforstep'];
$minfreecountcoupon = (int) $options['minfreecountcoupon'];
$minmes = new stdClass(); 
$minmes->emailminfreecountcouponwarning = $options['emailminfreecountcouponwarning'];
$minmes->textemailminfreecountcouponwarning = $options['textemailminfreecountcouponwarning'];
$timewaitautocomment = (int) $options['timewaitautocomment'];

$now = new DateTime("now");

// цикл автокомментария

$args = array(
    
    'post_type'  => 'coupon',
    'meta_query' => array(
        'relation' => 'AND',
        array(
            'key'   => 'coupon_status',
            'value' => 'public',
        ),
        array(
            'key'   => 'coupon_process',
            'value' => 'no',
        ),
        array(
            'key'   => 'coupon_datetimecopy', //есть
            'compare' => 'EXISTS',
        ),
        array(
            'key'   => 'coupon_daytimecomment', // нет
            'compare' => 'NOT EXISTS',
        ),
        
    ),
    /**/
    'nopaging' => TRUE,
    'orderby' => 'coupon_lifeend',
    'order' => 'ASC',
);
$postlist = get_posts($args);
wp_reset_postdata();

// массив для следующего шага
$autoposts = array();

$wrk_by_autopost = FALSE;

foreach ($postlist as $post) {
  $dtc = get_post_meta($post->ID,'coupon_datetimecopy', TRUE);
  //var_dump($timewaitautocomment);
  //var_dump(getinterval($now, $dtc));
  if ($dtc) {
    if ($timewaitautocomment < getinterval($now, $dtc)) {
      $idpost = (int) get_post_meta($post->ID,'idpost_coupon_publ', TRUE);
      if ($idpost>0) {
        $commentdata = array(
        	'comment_post_ID'      => $idpost,
        	'comment_author'       => $options['autorautocomment'],
        	'comment_author_email' => '',
        	'comment_author_url'   => '',
        	'comment_content'      => $options['textautocomment'],
        	'comment_type'         => '',
          'comment_approved'      => 1,
        	//'comment_parent'       => 315,
        	'user_ID'              => 0,
          'comment_meta'          =>  array('autorobot' => 1),
        );
        //remove_all_filters('duplicate_comment_id');
        //remove_all_actions('comment_duplicate_trigger');
        //remove_all_filters('pre_comment_approved');
        //remove_all_actions('wp_insert_comment'); // не ахти
        //remove_all_filters('preprocess_comment');
        //add_filter('duplicate_comment_id', 'undupcomfilter');
        //$idcomment = wp_new_comment(wp_slash($commentdata)); //проверка на дубли идёт
        //remove_filter('duplicate_comment_id', 'undupcomfilter');
        $idcomment = wp_insert_comment(wp_slash($commentdata));
        echo "idcomment\n";
        if ($idcomment) {
          update_post_meta( $post->ID, 'coupon_status', 'used');//date('Y-m-d H:i:s', strtotime($_POST['coupon_lifeend_name'])) );
          update_post_meta( $post->ID, 'coupon_datetimeuse', date('Y-m-d H:i:s'));//date('Y-m-d H:i:s', strtotime($_POST['coupon_lifeend_name'])) );
          update_post_meta( $post->ID, 'coupon_daytimecomment', date('Y-m-d H:i:s'));//date('Y-m-d H:i:s', strtotime($_POST['coupon_lifeend_name'])) );
          //update_post_meta( $post->ID, 'coupon_process', 'no');//date('Y-m-d H:i:s', strtotime($_POST['coupon_lifeend_name'])) );
          update_post_meta( $post->ID, 'coupon_process', 'yes');
          $autoposts[] = $post->ID;
          $wrk_by_autopost = TRUE;          
        } else {
          echo "Error insert autocomment!\n";
        }
      }
      //var_dump($dtc);
    }
  }
}

// цикл автокомментария конец

// цикл автозаписи

$args = array(
    
    'post_type'  => 'coupon',
    'meta_query' => array(
        'relation' => 'AND',
        array(
            'key'   => 'coupon_status',
            'value' => 'used',
        ),
        array(
            'key'   => 'coupon_process',
            'value' => 'no',
        ),
        array(
            'key'   => 'coupon_datetimecopy', //есть
            'compare' => 'EXISTS',
        ),
        array(
            'key'   => 'coupon_daytimecomment', // нет
            'compare' => 'EXISTS',
        ),
        
    ),
    /**/
    'nopaging' => TRUE,
    //'orderby' => 'coupon_lifeend',
    //'order' => 'ASC',
);
$couponlist = get_posts($args);// не проработанные
wp_reset_postdata();
//var_dump($couponlist);

$wrk_by_timewait = FALSE;

foreach ($couponlist as $coupon) {
  $dtc = get_post_meta($coupon->ID,'coupon_datetimeuse', TRUE);
  if ($timewait < getinterval($now, $dtc)) {
    // ставим маркер, что надо добавлять и промечаем купоны как проработанные
    $wrk_by_timewait = TRUE;
    //die('22');
    update_post_meta( $coupon->ID, 'coupon_process', 'yes');
  }  
}

echo "wrk_by_autopost\n";
var_dump($wrk_by_autopost);
echo "wrk_by_timewait\n";
var_dump($wrk_by_timewait);
//die('33');
// решаем надо ли нам автозаписи делать
if ($wrk_by_autopost OR $wrk_by_timewait) {

  $args = array(
      
      'post_type'  => 'coupon',
      'meta_query' => array(
          'relation' => 'AND',
          array(
              'key'   => 'coupon_status',
              'value' => 'new',
          ),
          array(
              'key'   => 'coupon_lifeend',
              'value' => $now->format('Y-m-d'),
              'type' => 'DATE',
              'compare' => '>',
          ),
          
      ),
      /**/
      'nopaging' => TRUE,
      'orderby' => 'coupon_lifeend',
      'order' => 'ASC',
  );
  $freecouponlist = get_posts($args);
  wp_reset_postdata();
  
  echo "freecouponlist count\n";
  var_dump(count($freecouponlist));
  //die('44');
  // количество для добавления с учётом свободных
  $countforadd = ($autopostcountforstep>=count($freecouponlist)) ? count($freecouponlist) : $autopostcountforstep;
  
  echo "countforadd\n";
  var_dump($countforadd);
  //die("55");
  // добавляем новые посты
  for ($i=0; $i <= ($countforadd-1) ; $i++ ) {
    //var_dump($i);
    // отключаем фильтры и хуки
    remove_all_filters('wp_insert_post_empty_content'); //filter
    remove_all_filters('wp_insert_post_data'); //filter
    remove_all_actions('save_post_post'); //hook
    remove_all_actions('save_post'); //hook
    remove_all_filters('wp_insert_post'); //filter
    $coupon = $freecouponlist[$i]->post_title;
    $pcid = $freecouponlist[$i]->ID; 
    $content = preg_replace('/{codecoupon}/i', '{couponpid='.$pcid.',couponcode='.$coupon.'}', $options['autopostbody']);
    $content = preg_replace('/{codelifeend}/i', date('d.m.Y', strtotime(get_post_meta($pcid,'coupon_lifeend', TRUE))), $content);
    //var_dump($content);
    // Создаем массив
    $post_data = array(
    	'post_title'    => $options['autopostheader'],
    	'post_content'  => $content,
    	'post_status'   => 'publish',
      'post_parent'   => 0,
      'menu_order'    => 0,
      'to_ping'       => '',
      'pinged'        => '',
    	'post_author'   => $options['autorautopost'],
    	'post_category' => array($options['autopostcategory']),
      'comment_status' => 'open',
      'post_type' => 'post',
     
    );
    $post_id = wp_insert_post( wp_slash($post_data) );
    //wp_set_post_categories( $post_id, array(2) );
    //wp_set_object_terms($post_id, 2, 'category', true);
    if ($post_id) {
      update_post_meta($pcid, 'idpost_coupon_publ', $post_id);
      update_post_meta($pcid, 'coupon_status', 'public');
    }
    echo "add auto post\n"; 
    var_dump($post_id);  	
  }
  // вызвать сообщение о достижении минимума
  if ($minfreecountcoupon>=(count($freecouponlist)-$countforadd)) {
    // шлём предупреждение
    //var_dump(count($freecouponlist));
    //var_dump('e-mail send min free coupon');
    send_email($minmes);  
  }
}

echo_memory_usage();

// цикл автозаписи конец

//===========================================================

function to_seconds($interval) 
{ 
  return ($interval->y * 365 * 24 * 60 * 60) + 
         ($interval->m * 30 * 24 * 60 * 60) + 
         ($interval->d * 24 * 60 * 60) + 
         ($interval->h * 60 * 60) + 
         ($interval->i * 60) + 
         $interval->s; 
}

function send_email($mes) {
	$to = $mes->emailminfreecountcouponwarning;
	$site_name = get_bloginfo('name');
	$site_url = get_bloginfo('url');
	$site_email = get_bloginfo('admin_email');
	//$subject = __('Your Comment is Approved!', 'comment-approved-notifier-extended');
  $subject = 'Количество свободных купонов мало';
	$charset = get_option('blog_charset');
	$servername = strtolower( $_SERVER['SERVER_NAME'] );
  if ( substr( $servername, 0, 4 ) == 'www.' ) {
    $servername = substr( $servername, 4 );
	}
	$from_email = $site_email;
	$message = $mes->textemailminfreecountcouponwarning;
	$headers = "From: $site_name <$from_email>\n";
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: text/html; charset=\"{$charset}\"\n";
  //global $wp_filter, $merged_filters; тут хранятся фильтры, чтобы сохранить их до удаления
  remove_all_filters( 'wp_mail_from' ); 
  remove_all_filters( 'wp_mail_from_name' );
  /*
  var_dump($to);
  var_dump($subject);
  var_dump($message);
  var_dump($headers);
  /**/
	$res = wp_mail( $to, $subject, $message, $headers);
  echo "WP_MAIL result\n";
  var_dump($res);
}

function echo_memory_usage() { 
    $mem_usage = memory_get_usage(true); 
    
    if ($mem_usage < 1024) 
        echo $mem_usage." bytes"; 
    elseif ($mem_usage < 1048576) 
        echo round($mem_usage/1024,2)." kilobytes"; 
    else 
        echo round($mem_usage/1048576,2)." megabytes"; 
        
    echo "\n"; 
}

function getinterval($now, $dtc) {
  
  $dtco = DateTime::createFromFormat('Y-m-d H:i:s', $dtc);
  
  $interval = $now->diff($dtco);
  
  return to_seconds($interval);
}

function undupcomfilter () {
  return FALSE;
}

function is_cli()
{
    if( defined('STDIN') )
    {
        return true;
    }
    
    if ( php_sapi_name() === 'cli' )
    {
        return true;
    }
    
    if ( array_key_exists('SHELL', $_ENV) ) {
        return true;
    }
     
    if( empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) 
    {
        return true;
    }
    
    if ( array_key_exists('REQUEST_METHOD', $_SERVER) )
    {
        return true;
    } 
     
    return false;
}

function has_cap( $dbh, $use_mysqli, $db_cap ) {
	$version = db_version($dbh, $use_mysqli);

	switch ( strtolower( $db_cap ) ) {
		case 'collation' :    // @since 2.5.0
		case 'group_concat' : // @since 2.7.0
		case 'subqueries' :   // @since 2.7.0
			return version_compare( $version, '4.1', '>=' );
		case 'set_charset' :
			return version_compare( $version, '5.0.7', '>=' );
		case 'utf8mb4' :      // @since 4.1.0
			if ( version_compare( $version, '5.5.3', '<' ) ) {
				return false;
			}
			if ( $use_mysqli ) {
				$client_version = mysqli_get_client_info();
			} else {
				$client_version = mysql_get_client_info();
			}

			/*
			 * libmysql has supported utf8mb4 since 5.5.3, same as the MySQL server.
			 * mysqlnd has supported utf8mb4 since 5.0.9.
			 */
			if ( false !== strpos( $client_version, 'mysqlnd' ) ) {
				$client_version = preg_replace( '/^\D+([\d.]+).*/', '$1', $client_version );
				return version_compare( $client_version, '5.0.9', '>=' );
			} else {
				return version_compare( $client_version, '5.5.3', '>=' );
			}
	}

	return false;
}

function db_version($dbh, $use_mysqli) {
	if ( $use_mysqli ) {
		$server_info = mysqli_get_server_info( $dbh );
	} else {
		$server_info = mysql_get_server_info( $dbh );
	}
	return preg_replace( '/[^0-9.].*/', '', $server_info );
}

function do_connect($dbhost, $dbuser, $dbpassword, $use_mysqli) {
	$new_link = defined( 'MYSQL_NEW_LINK' ) ? MYSQL_NEW_LINK : true;
	$client_flags = defined( 'MYSQL_CLIENT_FLAGS' ) ? MYSQL_CLIENT_FLAGS : 0;

	if ( $use_mysqli ) {
		$dbh = mysqli_init();

		// mysqli_real_connect doesn't support the host param including a port or socket
		// like mysql_connect does. This duplicates how mysql_connect detects a port and/or socket file.
		$port = null;
		$socket = null;
		$host = $dbhost;
		$port_or_socket = strstr( $host, ':' );
		if ( ! empty( $port_or_socket ) ) {
			$host = substr( $host, 0, strpos( $host, ':' ) );
			$port_or_socket = substr( $port_or_socket, 1 );
			if ( 0 !== strpos( $port_or_socket, '/' ) ) {
				$port = intval( $port_or_socket );
				$maybe_socket = strstr( $port_or_socket, ':' );
				if ( ! empty( $maybe_socket ) ) {
					$socket = substr( $maybe_socket, 1 );
				}
			} else {
				$socket = $port_or_socket;
			}
		}

		mysqli_real_connect( $dbh, $host, $dbuser, $dbpassword, null, $port, $socket, $client_flags );

		if ( $dbh->connect_errno ) {
			$dbh = null;
			/* It's possible ext/mysqli is misconfigured. Fall back to ext/mysql if:
	 		 *  - We haven't previously connected, and
	 		 *  - WP_USE_EXT_MYSQL isn't set to false, and
	 		 *  - ext/mysql is loaded.
	 		 */
			$attempt_fallback = true;

			if ( defined( 'WP_USE_EXT_MYSQL' ) && ! WP_USE_EXT_MYSQL ) {
				$attempt_fallback = false;
			} elseif ( ! function_exists( 'mysql_connect' ) ) {
				$attempt_fallback = false;
			}

			if ( $attempt_fallback ) {
				$use_mysqli = false;
				return do_connect( $dbhost, $dbuser, $dbpassword, $use_mysqli );
			}					
		}
	} else {
			$dbh = mysql_connect( $dbhost, $dbuser, $dbpassword, $new_link, $client_flags );
	}
  return $dbh;
}

