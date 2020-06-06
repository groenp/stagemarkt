<?php
/******************************************************************************/
/*                                                              Pieter Groen  */
/*  Version 0.1 - June 5, 2020                                                */
/*                                                                            */
/*  PHP for Groen Productions website CMS in WordPress                        */
/*                                                                            */
/*  Debug functions:                                                          */
/* - Debugging into debug.log                                 (~line   40)    */
/* - Logging into user_activity_log_{year}_{month}.txt        (~line   60)    */
/*                                                                            */
/*  Custom Admin Dashboard functions:                                         */
/* - based on twentytwenty theme                                              */
/* - change login screen                                      (~line   85)    */
/* - create Manager role based on author and allow User Admin (~line  110)    */
/* - simplify Profile pages for non Administrators            (~line  160)    */
/* - collapse side menu for Subscriber role                   (~line  200)    */
/* - remove unneccesary widgets                               (~line  230)    */
/* - stop heartbeat (ajax calls)                              (~line  260)    */
/* - Rewrite email messages for change and reset pwd          (~line  270)    */
/* - Logging user activities in Standard Wordpress interface  (~line  365)    */
/* - insert Groen Productions asset files into admin pages    (~line  480)    */
/* - create meta boxes for Groen Productions CMS              (~line  555)    */
/*                                                                            */
/*  Plugins needed:                                                           */
/* - Groen Productions Mailing plugin to change registration mails            */
/* - WP-Mail-SMTP plugin to use groenproductions.com for registration mail    */
/*                                                                            */
/* Functions used in other Groen Productions PHP files for site management:   */
/* - Connect to Groen Productions database                    (~line  540)    */
/* - Upload pictures                                          (~line  570)    */
/* - Input/output functions for database, forms, web page     (~line  650)    */
/*   . Sanitization of input data                               (~line  650)  */
/*   . Sanitization of input for prepared statements            (~line  730)  */
/*   . Display (escaping and formatting) of database data       (~line  820)  */
/* - Search (json) arrays for key <=> value pair              (~line  910)    */
/*                                                                            */
/******************************************************************************/

// ****************************************************************
// DEBUGGING, use: _log(). Arrays need to go into separate call
// ****************************************************************
if(!function_exists('_log')){
	function _log( $message ) 
    {
		// Only print to log when WP_DEBUG is on. This is set in wp-config.php, together with print destination. 
		// It is on only for local DB user on PG's environment.
		if( WP_DEBUG === true ){
			if( is_array( $message ) || is_object( $message ) )
			{
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		} // end: if WP_DEBUG is on
	}
}

// ****************************************************************
// Groen Productions - Logging in User Activity Log
// ****************************************************************
if(!function_exists('_lua')){
    function _lua( $mod = "", $message = "") 
    {
        // create a new file every month, create local timestamp for server
        $filename = ABSPATH .'/logs/user_activity_log_'.date("Y_m", strtotime(get_option('gmt_offset') . " hours")).'.txt';
        unset($current_user);
        $current_user = wp_get_current_user();
        if ( $current_user && !empty($current_user->user_login) ) 
        {
            $log = "[" . date("D d-M-y H:i:s", strtotime(get_option('gmt_offset') . " hours")) . "][" . str_pad(substr($mod, 0, 6), 6) . "] " . $current_user->user_login . ": " . $message . PHP_EOL;
        } else {
            $log = "[" . date("D d-M-y H:i:s", strtotime(get_option('gmt_offset') . " hours")) . "][" . str_pad(substr($mod, 0, 6), 6) . "] " . $message . PHP_EOL;
        }
        file_put_contents($filename, $log, FILE_APPEND);
    }
} 


/******************************************************************************/
/* Common Dashboard management functions                                      */
/*                                                                            */
/******************************************************************************/

// ****************************************************************
// Attach admin login header logo and removal of return-to-blog link by CSS
// ****************************************************************
// see: function groenp_include_in_head()

// ****************************************************************
// Custom admin login message
// ****************************************************************
function groenp_cookie_warning_login() {
	return '<p class="message lowlight">This site uses cookies in order to safeguard your session and to keep track of your preferences.<br><br>By logging in you accept the use of these cookies. Please review the <a href="privacy_and_terms_of_use.php">Privacy Statement and Terms of Use</a> for more detail.</p>';
}
add_filter('login_message', 'groenp_cookie_warning_login');


// ****************************************************************
// Change admin login logo link
// ****************************************************************
function groenp_change_wp_login_url()
{
    return trailingslashit(admin_url());
}
add_filter('login_headerurl', 'groenp_change_wp_login_url');


// ****************************************************************
// ONLY ON CHANGE OF THEME:
// Simplify capabilities of Admin and others, so not to clutter the U/I
// Allow Manager role (somebody working for Groen Productions) to do user admin 
// Remove Editor and Contributor roles
// ****************************************************************

// Manager role will be based on Author, 
function groenp_create_manager_role($oldname, $oldtheme=false) {
    global $wp_roles;

    if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();

    // rename the author role in U/I only
    $wp_roles->roles['author']['name'] = 'Manager';
    $wp_roles->role_names['author'] = 'Manager';     
    
    // remove superfluous roles: THIS IS REMOVED FROM THE WP-DB! There is no turning back! 
    remove_role( 'editor' );
    remove_role( 'contributor' );

    // list all currently available roles
    $roles = $wp_roles->get_names();
    _log("present roles: "); _log($roles);                             /* DEBUG */

    // get the author role
    // see https://codex.wordpress.org/Roles_and_Capabilities
    $role = get_role( 'author' );

    // This only works, because it accesses the class instance.
    $role->add_cap( 'list_users' ); 
    $role->add_cap( 'edit_users' ); 
    $role->add_cap( 'create_users' ); 

    // simplify the left-hand menu
    $role->remove_cap( 'upload_files' );    // Media menu item
    $role->remove_cap( 'edit_posts' );      // Pages and Comments menu items
    //$role->remove_cap( 'promote_users' ); // not necessary; profile box is removed from edit form instead

}
// this is saved in the DB so only do this once...
// add_action( 'after_switch_theme', 'groenp_create_manager_role', 10 ,  2);


// ****************************************************************
// Clean up Profile pages for non Admin (subscriber and author roles)
//
// (jQuery adjustments in footer: check with each update to WP)
// ****************************************************************
function groenp_user_profile_fields_disable() {
    global $pagenow;

    if ($pagenow!=='profile.php' && $pagenow!=='user-edit.php' && $pagenow!=='user-new.php') {
        return; // do not do anything for other pages
    }
    if (current_user_can('administrator')) {
        return; // do not do anything if user is Admin
    }
    add_action( 'admin_footer', 'groenp_user_profile_fields_disable_js' );
}
add_action('admin_init', 'groenp_user_profile_fields_disable');
 
function groenp_user_profile_fields_disable_js() {
?>
    <script type='text/javascript'>
        jQuery(document).ready(function ($) {
            //var fields_to_disable = ['color-picker', 'role', 'nickname', 'display_name', 'url', 'aim', 'yim', 'jabber', 'description'];
            var fields_to_disable = ['nickname', 'display_name', 'url'];
            for (i = 0; i < fields_to_disable.length; i++) {
                if ($('#' + fields_to_disable[i]).length) {
                    $('#' + fields_to_disable[i]).parent().parent().hide();
                }
            }

            // remove "About Yourself" section
            $('#your-profile .user-description-wrap').parent().parent().prev().hide();  // <h2>About Yourself</h2>
            $('#your-profile .user-description-wrap').parent().parent().hide();         // entire table

            // check boxes to be removed
            $('#rich_editing').parent().parent().parent().hide();                       // checkbox
            $('#syntax_highlighting').parent().parent().parent().hide();                // checkbox
            $('#comment_shortcuts').parent().parent().parent().hide();                  // checkbox
            $('#admin_bar_front').parent().parent().parent().hide();                    // checkbox
        });
    </script>
<?php
}

// ****************************************************************
// Collapse side menu for Subscriber role
// ****************************************************************
function groenp_collapse_side_menu_for_subscriber() {
    if ( !current_user_can('list_users') ) { // this is the least a manager and administrator can do
        add_action( 'admin_footer', 'groenp_add_collapse_js' );
    }
}

function groenp_add_collapse_js() {
?>
    <script type='text/javascript'>
        if ( !jQuery(document.body).hasClass('folded') ) jQuery(document.body).addClass('folded');
    </script>
<?php
}
add_action('admin_init', 'groenp_collapse_side_menu_for_subscriber');

// ****************************************************************
// Admin footer modification
// ****************************************************************
function groenp_change_footer_admin ()
{
    echo "<span id='footer-thankyou'>Developed in WordPress by Groen Productions</span>";
}
add_filter('admin_footer_text', 'groenp_change_footer_admin');

// ****************************************************************
// Set upload folder to be uploads/ (just in case somebody changes it in the admin)
// ****************************************************************
define( 'UPLOADS', 'wp-content/uploads' );

// ****************************************************************
// Remove meta boxes from wordpress dashboard for all users
// ****************************************************************
function groenp_remove_dashboard_widgets()
{
    // remove_meta_box('dashboard_site_health', 'dashboard', 'normal');		// site health status
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal');			// right now/at a glance
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');			// activity
 
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');			// quick press/quick draft
    remove_meta_box('dashboard_primary', 'dashboard', 'side');				// wordpress news and events
}
add_action('wp_dashboard_setup', 'groenp_remove_dashboard_widgets' );

// Remove  WordPress Welcome Panel
remove_action('welcome_panel', 'wp_welcome_panel');

// Remove Screen Options tab
// add_filter('screen_options_show_screen', '__return_false');

// Remove Help tab, Comments count and '+' New widget in admin bar
function groenp_remove_toolbar_items($wp_adminbar) {

    global $current_screen;
    $current_screen->remove_help_tabs();
    
    // _log("remove toolbar items: "); _log($wp_adminbar); // DEBUG //
	$wp_adminbar->remove_node('wp-logo');
  	$wp_adminbar->remove_node('comments');
    $wp_adminbar->remove_node('new-content');
}
add_action('admin_bar_menu', 'groenp_remove_toolbar_items', 999);

// ****************************************************************
// Stop admin-ajax.php making unneccesary calls every 30secs
// ****************************************************************
// add_action( 'init', 'stop_heartbeat', 1 );
function stop_heartbeat() {
        wp_deregister_script('heartbeat');
}

// ****************************************************************
// Redirect blocked user to login page - close session
// ****************************************************************
// add_action( 'admin_init', 'groenp_redirect_blocked_users' );
function groenp_redirect_blocked_users() {

    // Open groenp_sites_cms database
    $con = groenp_open_database();

    // Get user status
    $wp_userID = get_current_user_id();
    $result = mysqli_query($con, "SELECT is_usr_blocked FROM subscribers WHERE fr_ID='" . $wp_userID . "';");
    if ( $result ) $row = mysqli_fetch_array($result);

	if ( $result && $row[0] ) 
    {
        _lua("WPuser", "Subscriber (wpID:" . $wp_userID.") is trying to login, but has been BLOCKED!");
        session_unset(); 
        //session_destroy();
        wp_redirect( '/wp-login.php?custom-logout=yes' );
    }
    // Close database
    mysqli_close($con);
}

// Define custom message for the force-out situation
if(!empty($_GET['custom-logout']) && strtolower($_GET['custom-logout']) == "yes"){
    function groenp_force_out_message() {
        $message = '<p class="message">Su nombre de usuario ha sido bloqueada. <br />Por favor, póngase en contacto con la <a href="mailto:admin@groenproductions.com">administración de Groen Productions</a> para obtener más información.</p>';
        return $message;
    }
    add_filter('login_message', 'groenp_force_out_message');
}


// ****************************************************************
// Changed password notification to user - message  filter hook
// ****************************************************************
add_filter( 'password_change_email',  'groenp_changed_pwd_mail_message', 10, 3);
function groenp_changed_pwd_mail_message(  $pass_change_mail,  $user,  $userdata ) {

    $user_login = $user['user_login'];
    $user_email = $user['user_email'];

    $message = "This notice confirms that the password has been changed on Groen Productions – Sites Management Tool for the following user.\n\n";
    $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";  
    $message .= "If you did not change your password, please contact us at: admin@groenproductions.com\n";
    $message .= "This email has been sent to " . $user_email ."\r\n\r\n";
    $message .= "Greetings,\nThe staff at Groen Productions\n\n";

  $pass_change_mail[ 'message' ] = $message;
  return $pass_change_mail;
}

// ****************************************************************
// User requested new password - message  filter hook
// ****************************************************************
add_filter( 'retrieve_password_message', 'groenp_retrieve_password_message', 10, 2 );
function groenp_retrieve_password_message( $message, $key ){
    $user_data = '';

    // If no value is posted, return false
    if( ! isset( $_POST['user_login'] )  ){
            return '';
    }

    // Fetch user information from user_login (if user_login has email)
    if ( strpos( $_POST['user_login'], '@' ) ) {
        $user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );

    } else {
        // user_login has userID
        $login = trim($_POST['user_login']);
        $user_data = get_user_by('login', $login);
    }
    if( ! $user_data  ){
        return '';
    }

    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;

    // Set up message for retrieve password
    $message = "A password reset has been requested for your account on the Groen Productions | Sites Management Tool.\n\n";
    $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";  
    $message .= "If you did not request this, just ignore this email and nothing will happen.\n\n"; 
    $message .= "To reset your password, select the following link:\n";
    $message .= site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "\r\n\r\n";
    $message .= "We hope that you enjoy using the Sites Management Tool. If you have any questions or suggestions please do not hesitate to contact us at: admin@groenproductions.com\n\n";
    $message .= "Greetings,\nThe staff at Groen Productions\n\n";
    return $message;
}

// ****************************************************************
// Logs the actions in the Std WordPress interface ('Add New User', 'Edit', 'Delete', 'Olvidó su contraseña', 'Iniciar Sesión', 'Cerrar sesión' )
// ****************************************************************
add_action( 'user_register', 'groenp_log_add_user', 10, 1); // hooked right after creation
function groenp_log_add_user( $user_id ) 
{
    if ( isset($_POST['user_login']) )
    {
        if ( isset($_POST['email']) ) {
            _lua("WPuser", "User (wpID:". $userid .", ". $_POST['user_login'] . ", email: ". $_POST['email'] . ") created through the standard WordPress interface.");
        } elseif ( isset($_POST['user_email']) ) {
            _lua("WPuser", "User (". $_POST['user_login'] . ", email: ". $_POST['user_email'] . ") created through the Subscribers interface.");
        } else {
            _lua("WPuser", "User (". $_POST['user_login'] . ") created.");
        }
    } else {
        _lua("WPuser", "User (wpID:". $userid .") created through the standard WordPress interface.");
    }
}

add_action('edit_user_profile_update', 'groenp_log_update_user', 10, 1); // hooked at reload of user details page (of other users)
add_action('personal_options_update', 'groenp_log_update_user', 10, 1); // hooked at reload of own user details page
function groenp_log_update_user( $user_id ) 
{
    global $wpdb;
    $del_user = get_userdata( $user_id );
    if ( !empty($_POST['pass1']) &&  !empty($_POST['pass2']) ) // This works because the pwd fields are emptied, when an error occurs. 
    {                                                          // If the pwd fields are returned with the $_POST it must be a successful submit.
        if ( $del_user )
        {
            _lua("WPuser", "User (wpID:". $del_user->ID .", ". $del_user->user_login  . ") password changed.");
        } else {
            _lua("WPuser", "User password changed.");
        }
    }
//    else // this is not neccesary since the result is uncertain. The hook profile_update is used below.
//    {
//    }
}

add_action( 'profile_update', 'groenp_log_updated_user', 10, 1);
function groenp_log_updated_user( $user_id ) 
{
    global $wpdb;
    $user = get_userdata( $user_id );
    if ( $user )
    {
        _lua("WPuser", "User (wpID:". $user->ID .", ". $user->user_login  . ") updated through the standard WordPress interface.");
    } else {
        _lua("WPuser", "User updated through the standard WordPress interface.");
    }
}


add_action( 'delete_user', 'groenp_log_delete_user');  // hooked just before deletion
function groenp_log_delete_user( $user_id ) 
{
    global $wpdb;
    $del_user = get_userdata( $user_id );
    if ( $del_user )
    {
        _lua("WPuser", "User (wpID:". $del_user->ID .", ". $del_user->user_login  . ") to be deleted through the standard WordPress interface.");
    } else {
        _lua("WPuser", "User to be deleted through the standard WordPress interface.");
    }
}

add_action( 'deleted_user', 'groenp_log_deleted_user');  // hooked AFTER deletion (so can't refer to user data)
function groenp_log_deleted_user( $user_id ) 
{
    _lua("WPuser", "User delete successful.");
}

// check_passwords

add_action( 'retrieve_password_key', 'groenp_log_user_forgot', 10, 1);
function groenp_log_user_forgot( $user_login ) 
{
    if ( isset($user_login) )
    {
        _lua("WPuser", "User (". $user_login .") requested a new password.");
    } else {
        _lua("WPuser", "A user requested a new password.");
    }
}

add_action( 'wp_login' , 'groenp_log_login', 10, 2);
function groenp_log_login($user_login, $user) 
{
    if ( $user->roles[0] == "subscriber")
    {
        _lua("WPuser", "Subscriber (wpID:". $user->ID .", ". $user_login .") logged in.");
    } else {
        _lua("WPuser", "GP User (wpID:". $user->ID .", ". $user_login .") logged in.");
    }
}

add_action('wp_logout', 'groenp_log_logout'); // called after auth cookie cleared
function groenp_log_logout() 
{
    global $wpdb;
    $user = wp_get_current_user();
    if ( $user )
    {
        _lua("WPuser", "User (wpID:". $user->ID . ", ". $user->user_login  . ") logged out.");
    } else {
        _lua("WPuser", "User logged out.");
    }
}


// ****************************************************************
// Insert Assets files into Dashboard pages
// ****************************************************************

// ****************************************************************
// Groen Productions  - cusconow_script_enqueuer()
//                    - Includes jQuery (ajax) javascript at the right spot, and 
//                      non-subscriber javascript only for GP admin
// ****************************************************************

function groenp_script_enqueuer() 
{
    // default SSL port number OR http: port number; use minimized version, otherwise not
    $min_url = ($_SERVER['SERVER_PORT'] == "443" || $_SERVER['SERVER_PORT'] == "80") ? ".min" : "";
    
    wp_register_script( 'groenp-sbscrbr', trailingslashit( get_stylesheet_directory_uri() ) .'groenp-sbscrbr' . $min_url . '.js', array('jquery') );
    if ( current_user_can('list_users') ) wp_register_script( 'groenp-sites-cms',  trailingslashit( get_stylesheet_directory_uri() ) . 'groenp-sites-cms' . $min_url . '.js', array('jquery') );
    wp_localize_script( 'groenp-sbscrbr', 'groenpAsync', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'groenp-sbscrbr' );
    if ( current_user_can('list_users') ) wp_enqueue_script( 'groenp-sites-cms' );
} // End of: groenp_script_enqueuer()
add_action( 'init', 'groenp_script_enqueuer' );


// ****************************************************************
// Groen Productions  - groenp_include_in_head()
//                    - Includes groenp-sites-cms in head, and 
//                      non-subscriber javascript only for GP admin
// ****************************************************************
function groenp_include_in_head() 
{ 
    // global vars driven by server
    // echo "<script type='text/javascript'>
    //     var TZsrvr = '". sprintf('%+03d', get_option('gmt_offset')) ."' + ':00'; // get server time-zone (from WordPress)

    //     jquery disables forward anchor links in page
    //     jQuery(document).ready(function($){
    //         var fwd_link = window.location.hash.substr(1);
    //         if ( fwd_link != '' && window.location.href.indexOf('index.php?page') != -1 ) {
    //             // scroll to anchor
    //             $('html, body').animate({
    //                 scrollTop: $('a[name=\"'+fwd_link+'\"]').offset().top - 90 // 90 px = h3 bar (link is just below it) + top nav bar (32px) + margin
    //             }, 700);
    //         }
    //     });

    // </script>";

    // default SSL port number OR http: port number; use minimized version, otherwise not
    $min_url = ($_SERVER['SERVER_PORT'] == "443" || $_SERVER['SERVER_PORT'] == "80") ? ".min" : "";

    // include style sheets
    echo "<link type='text/css' href='" . trailingslashit( get_stylesheet_directory_uri() ) . "groenp-sites-cms" . $min_url . ".css' rel='stylesheet' media='all' />";
} 
add_action('admin_head','groenp_include_in_head');
add_action('login_head','groenp_include_in_head');


// Prints script in footer of Dashboard pages
function groenp_print_script_in_footer() {
    echo "<script type='text/javascript'>
        jQuery(document).ready(function(){ 

            // initialize meta box handling
            postboxes.add_postbox_toggles(pagenow); 

            // set bkgnd for boolean display with 'Y'
            // $('div.postbox table.manage td.chck:contains(\"Y\")').css('background-color','#f9f7ed');
        });
        </script>";
}
// add_action( 'admin_footer', 'groenp_print_script_in_footer' ); // is placed only in other .php admin files

/******************************************************************************/
/* Groen Productions site management functions for all Dashboard pages        */
/*                                                                            */
/******************************************************************************/

// Check user privileges and determine which sections can be loaded
// _log('load other page files');

/* all general and admin meta boxes for the Dashboard page have been defined in: 'groenp_sites_management.php' */
require_once( 'groenp_sites_management.php' );

/* all meta boxes for the Subscribers page have been defined in: 'groenp_subscribers.php' */
require_once( 'groenp_subscribers.php' );


// ****************************************************************
// Groen Productions - Open GROENP_SITES_CMS database 
//
// There are 2 groenp users, one for each level of access:
// - Same users defined on all servers
// - Database is named groenp_sites_cms local and on Groen Productions server
// - There is no separate database on test server
// ****************************************************************

function groenp_open_database() 
{
    // connect with correct user and select correct db
    $db = "groenp_sites_cms";

    if (current_user_can('list_users'))
    {
        // CRUD connection to groenp_sites_cms
        $connect = mysqli_connect('localhost','groenpf99v6Vd53','m^W$pQ&5W!j5', $db); // CRUD privileges
    } else {
        // read only connection to groenp_sites_cms
        $connect = mysqli_connect('localhost','groenpRdrgYAUCm','#yCAh(j&kv>Q', $db); // R/O privileges
    }

    if (!$connect)
    {
        die('Could not connect to MySQL server: ' . mysqli_connect_error());
    }
    
    // change character set to utf8 
    if (mysqli_set_charset($connect, "utf8")) {
        return $connect;
    } else {
        _log("Error loading character set utf8: " . mysqli_error($connect));
    }
}


// ****************************************************************
// Groen Productions - Upload Picture to Stroomt directory (optionally set max filesize and exact dimensions)
//
// $photo_file	= local path as it has been defined in the <input type='file'>
// $maxsize_kb	= maximum file size in KB (optional)
// $width		= image width in pixels (optional)
// &height		= image height in pixels (optional)
//
// ****************************************************************

function groenp_upload_pic($photo_file, $maxsize_kb = NULL, $width = NULL, $height = NULL)
{
	 // Initialize
	 $allowedExts = array('jpg', 'jpeg', 'gif', 'png', 'JPG', 'JPEG', 'GIF', 'PNG');
	 $extension = end(explode('.', $photo_file['name']));
	 if ( is_null($maxsize_kb) ) {$size = 500 * 1024;} else {$size = $maxsize_kb * 1024;}

     $photo_info = getimagesize($photo_file['tmp_name']);
	 //_log('photo dims: '. $photo_info[0].'px x '.$photo_info[1].'px (wxh)');
     $photo_width = $photo_info[0];
     $photo_height = $photo_info[1];
    
     // Check if file adheres to restrictions
	 if (  (   ($photo_file['type'] == 'image/gif')
	        || ($photo_file['type'] == 'image/jpeg')
	        || ($photo_file['type'] == 'image/png')
	        || ($photo_file['type'] == 'image/pjpeg') ) 
	     && in_array($extension, $allowedExts)
	     && ( $photo_file['size'] < $size )
         && ( is_null($width) || $width == $photo_width )
         && ( is_null($height) || $height == $photo_height ) )
	 {  
	     if ( $photo_file['error'] > 0 )
	     {
			 // Something went wrong in the actual transfer to the temp directory, most likely no path specified
	         echo "<p class='err-msg'>Upload error: " . $photo_file['error'] . "</p>";
	     } 
		 else 
		 {
			// Load in file.php for std. upload function
			if ( ! function_exists( 'wp_handle_upload' ) ) require_once( admin_url('/includes/file.php') );
			$upload_overrides = array( 'test_form' => false );	// No post form has been used, so don't test for it.

			// Test for anything suspicious that hasn't been caught already
			$movefile = wp_handle_upload( $photo_file, $upload_overrides );
            _log("$movefile: " . $movefile['file']);                             // DEBUG //
            _log($movefile);                                                    // DEBUG //

			if (isset( $movefile['url'])) 
			{
                // Server url is set, so upload testing was successful 
				_log('Upload successful: ' . $movefile['url']);	/* DEBUG */

                // TEST: move the file to another dir                           // DEBUG //
                // __DIR__: root\CuscoNow\wp-content\themes\CuscoNow
                // Trgt dir: admin.groenproductions/site

                // _log("present directory: " . __DIR__);                          // DEBUG //
                // $uploads_dir = '/home/notgrumpy/public_html/test/bloem-consultants/uploads/';
                // _log("uploads_dir: " . $uploads_dir);                           // DEBUG //

                // // extract new filename after upload
                // $url_parts = explode( '/', $movefile['file'] );
                // $filename = $url_parts[sizeof($url_parts)-1];
                // _log("filename: ". $photo_file['name'] . ", became: ". $filename ); // DEBUG //
                
                // rename($movefile['file'], $uploads_dir . $filename );


				@ chmod( $movefile['file'], 0000644 );			// Force rw-r--r-- access on uploaded files
				return($movefile['url']);

			} else { 
				// Some error occurred inside wp_handle_upload; just perculate it up
				echo "<p class='err-msg'>File \"" . $photo_file['name'] . "\": " . $movefile['error'] . "</p>";
//				_log('Upload failed: ' . $movefile['error']);	/* DEBUG */
				return(FALSE);
			}
		} // end: if error in actual transfer
	} 
	else 
	{ 
		// File did not adhere to restrictions inside this function
		if ($photo_file['name'])
		{
			// Specify file that has been attempted (more than one pic can be tried inside form)
			echo "<p class='err-msg'>File \"" . $photo_file['name'] . "\" has not been uploaded.";
			if ( !$height && ( $width != $photo_width && $width ) ) { echo " Image must be " . $width . "px wide."; }
			elseif ( !$width && ( $height != $photo_height && $height ) ) { echo " Image must be " . $height . "px high."; }
			elseif ( ( $width != $photo_width && $width ) || ( $height != $photo_height && $height ) ) { echo " Image dimensions must be " . $width . "px by " . $height . "px."; }
			echo  " Only image files (.jpg, .gif, .png) smaller than " . round($size/1024) . "KB are allowed.</p>";
		} 
		else 
		{
			// File was not read correctly. Just state general requirements.
	        echo "<p class='err-msg'>No image file has been selected for upload. Only image files (.jpg, .gif, .png) smaller than " . round($size/1024) . "KB are allowed.</p>";
		}
		return(FALSE);
	} // end: if adheres to these function restrictions or not
}

// ************************************************************************** //
//  Groen Productions - input/output functions to handle:                     //
//   - san(): sanitization of user input                                      //
//   - prep(): preparation / partial sanitization for prepared statements     //
//   - dis(): proper display formats in HTML docs                             //
//                                                                            //
// ************************************************************************** //


// ****************************************************************
// Groen Productions - Sanitizes user input; magic quotes are on, so 
//                     places all input inside quotes, except for "chk" and "b",
//                     returns string with "NULL" when empty input.
//
// $input		    = user input
// $m			    = mode (string):
//   "s"			    = string (default) 
//   "s%"               = string for LIKE statements, read from control 
//                          (after displayed with dis("s"), so special chars need to be decoded first)
//   "a"			    = alpha-numeric plus "_"
//   "chk"			    = checkbox ("1" or "0")
//   "b"			    = boolean (true or false)
//   "i"			    = integer
//   "f"			    = float
//   "m"			    = monetary amount
//   "wp"			    = string for wp_user, san handled by wp
//
// ****************************************************************
function san(&$input, $m = "s")
{
    if( $m=="wp" ) return $input; // don't do anything, wp will take care of san
    
    // php with checkboxes needs special handling
    if( $m=="chk" ) if ( empty($input) ) { return "0"; } else { return "1"; };
    
    // when empty make sure the word NULL is returned for query string building
    if( empty($input) && $input!== "0") return "NULL";

    // the real sanitization starts here, at least place inside apos (except boolean)
    switch(strtolower($m)) 
    {
        case "a":
        return "'" . preg_replace( '/[^a-zA-Z0-9_]/', '', $input) ."'";

        case "blb":
        return "'" . htmlentities($input, ENT_QUOTES, "UTF-8") . "'";

        case "blb%":
        return "'%". addslashes(html_entity_decode($input, ENT_QUOTES)) ."%'";

        case "b":
        return ($input)? TRUE : FALSE;

        case "i":
        return "'" . intval($input) . "'";

        case "f":
        return "'" . floatval($input) . "'";

        case "m":
        return "'" . round( floatval( preg_replace( '/[^0-9\.]/', '', $input ) ), 2) . "'";

        case "s%":
//        _log("the like search string: ". addslashes(html_entity_decode($input, ENT_QUOTES)) );
//        _log("the like search string: ". htmlspecialchars_decode($input, ENT_QUOTES) );
//        _log("the like search string: ". $input );
        return "'%". addslashes(htmlspecialchars_decode($input, ENT_QUOTES)) ."%'";

        case "s":
        default:
        return "'" . $input . "'";
    }
} // end of: san()

// ****************************************************************
// Groen Productions - Sanitizes user input FOR PREPARED STATEMENTS (no qoutes),
//                     returns NULL when empty input.
//
// $input		    = user input
// $m			    = mode (string):
//   "wp"			    = string for wp_user, san handled by wp
//   "chk"			    = checkbox ("1" or "0")
//   "a"			    = only alpha-numeric plus "_" (everything else stripped)
//   "b"			    = boolean (true or false)
//   "i"			    = integer
//   "f"			    = float
//   "m"			    = monetary amount
//   "tr"			    = bookkeeping transaction, needs amount + crd/deb/bal indicator
//   "d"			    = numerical date (no time): dd-mm-yyyy
//   "dh"			    = date from hidden field: yyyy-mm-dd (MySQL format)
//   "t"			    = time in hh:mm format
//   "tel"			    = telephone number as 9-digit integer (INT(9))
//   "s"			    = string (default, escape slashes stripped)
//
// ****************************************************************
function prep(&$input, $m = "s", $acc = "")
{
    if( $m=="wp" ) return $input; // don't do anything, wp will take care of san
    
    // php with checkboxes needs special handling
    if( $m=="chk" ) if ( empty($input) ) { return "0"; } else { return "1"; };
    
    // when empty make sure the word NULL is returned for query string building
    if( empty($input) && $input!== "0") return NULL;

    switch(strtolower($m)) // the real sanitization starts here
    {
        case "a":
        return preg_replace( '/[^a-zA-Z0-9_]/', '', $input);

        case "b":
        return ($input)? TRUE : FALSE;

        case "i":
        return intval($input);

        case "f":
        return floatval($input);

        case "m":
        case "tr":
        $amt = preg_replace( '/[^0-9\.,]/', '', $input );                           // strip off anything that's not a digit or potential delimiter
        $posp = strrpos( $amt, ".");                                                // last occurrence of .
        //_log("posp: " . $posp);                                                     // DEBUG //
        $posc = strrpos( $amt, ",");                                                // last occurrence of ,
        //_log("posc: " . $posc);                                                     // DEBUG //
        if($posp === false && $posc === false ) return floatval($amt);              // if no delimeters found, then return number
        $dec = ($posc > $posp)? substr($amt,$posc + 1) : substr($amt,$posp + 1);    // get decimal section (. or , used as decimal?)
        //_log("dec: " . $dec);                                                       // DEBUG //
        //_log("amt: " . $amt);                                                       // DEBUG //
        $amt = preg_replace( '/[\.,]/', '', $amt );                                 // strip off any delimeters
        //_log("amt: " . $amt);                                                       // DEBUG //
        $amt = preg_replace('/'.$dec.'$/', '', $amt);                               // strip off $dec part
        //_log("amt: " . $amt);                                                       // DEBUG //
        $amt = round( floatval($amt.".".$dec), 2);                                  // create float and round it to 2 decimals
        //_log("amt: " . $amt);                                                       // DEBUG //
        if (strtolower($m) == "m") return $amt;

        // case "tr" only:
        if      ( $acc == "deb" ) { return (-1 * $amt); }
        elseif  ( $acc == "crd" ) { return ($amt); }
        elseif  ( $acc == "bal" ) { return (0); }
        else { 
            _log("prep(): error in transaction conversion with amount: " . $input . ", and accounting type: " . $acc . "."); 
            return 0;
        }

        case "d":
        $date = date_create_from_format('d-m-Y',$input, new DateTimeZone('America/Lima'));
        if (!$date) return "input_error";
        date_time_set($date, 8,00,00); // yyyy-mm-dd 08:00:00
        return date_format($date, 'Y-m-d H:i:s');

        case "dh":
        $date = date_create_from_format('Y-m-d',$input, new DateTimeZone('America/Lima'));
        if (!$date) return "input_error";
        return date_format($date, 'Y-m-d');

        case"t":
        if ( strpos($input, ":") === FALSE ) return "input_error";
        $time = explode(":",$input);
        $time[0] = (strlen( (string)intval($time[0])) == 0 )? "00" : (string)intval($time[0]);
        $time[0] = str_pad(substr($time[0],0,2), 2, '0', STR_PAD_LEFT);
        $time[1] = (strlen( (string)intval($time[1])) == 0 )? "00" : (string)intval($time[1]);
        $time[1] = str_pad(substr($time[1],0,2), 2, '0', STR_PAD_LEFT);
        return gmdate("H:i", mktime($time[0], $time[1]));

        case "tel":
        return intval( preg_replace( '/[^0-9]/', '', $input ) );

        case "s":
//        return htmlspecialchars(stripslashes($input), ENT_QUOTES, "UTF-8");

        default:
        return stripslashes($input);
    }
} // end of: prep()


// ****************************************************************
// Groen Productions - Displays sanitized data (from database or straight from $_POST) 
//                    to be used in filter fields 
//                    or from database (for specific display format)
//
// $output		    = output from database or straight from $_POST
// $f			    = format (string):
//   "s"			    =  string (spec. chars escaped, straight from $_POST)
//   "a"			    =  display of alpha-numeric (no escaping, default)
//   "chk"			    =  checkbox ("Y" or "")
//   "chk_ctrl"		    =  checkbox control 
//   "rad_ctrl"		    =  radio control, also needs rad to be 0 or 1 to be set
//   "b"			    =  boolean (true or false)
//   "i"			    =  integer
//   "f"			    =  float/double
//   "fr"			    =  rounded float/double (+/-####.##) (used in compare of mut comment text)
//   "m"			    =  monetary amount (#,###.##)
//   "ms"			    =  signed monetary amount (+/-#,###.##)
//   "d"			    =  date (dd-mm-yyyy), used for input controls, etc.
//   "dl"			    =  date in language (dd-MMM-yyyy)
//
// ****************************************************************
function dis(&$output, $f = "a", $rad = NULL)
{
    // boolean always returns true or false 
    if ($f === "b") return ($output) ? "true" : "false";

        //_log("output: " . $output);
    if ( $output === NULL) return ""; // when output is null return empty;

    if ($output === "*") return "*"; // usually a wildcard is allowed as well
 
    switch(strtolower($f)) // assume all output has been sanitized but not HTML safe
    {
        case "s":
        // this escapes the output so it doesn't screw the html
        return htmlspecialchars($output, ENT_QUOTES, "UTF-8"); 

        case "s%":
        // to be used for filter controls (it receives straight from $_POST so slashes need to be stripped)
        // percentage signs for LIKE stmt (and single quotes) need to be placed around it
        return htmlspecialchars(stripslashes($output), ENT_QUOTES, "UTF-8"); 

        case "chk":
        // fill cell with "Y" or nothing
        return !empty($output) ? "Y" : "";  

        case "chk_ctrl":
        // set control on or not (empty)
        return !empty($output) ? "checked='checked' " : "";

        case "rad_ctrl":
        // set control on ("1") or not ("0"), depending on $rad
        if ( !isset($rad))  return "";
        return ($output == $rad) ? "checked='checked' " : "";

        case "m":
        return number_format(abs($output),2);

        case "ms":
        return number_format($output,2);

        case "fr":
        return round($output, 2);

        case "d":
        if ( $output==="NOW" ) {
            $date = new DateTime(null, new DateTimeZone('America/Lima'));
        } else {
            $date = date_create_from_format('Y-m-d H:i:s',$output, new DateTimeZone('America/Lima'));
        }
        return ($date)? date_format($date, 'd-m-Y') : "date error";

        case "dl":
        //_log("loc a the moment: " . setlocale(LC_TIME, "spanish"));
        if ( $output==="NOW" ) {
            $date = new DateTime(null, new DateTimeZone('America/Lima'));
        } else {
            $date = date_create_from_format('Y-m-d H:i:s',$output, new DateTimeZone('America/Lima'));
        }
        return ($date)? strftime('%d-%b-%Y', date_timestamp_get($date)) : "date error";

//        case "a":
//        case "i":
//        case "f":
        default:
        return $output;
    }
} // end of: dis()


// ****************************************************************
// Groen Productions - Search (json style) array for specific key <=> value pair 
//                     first pair encountered will be returned, so pair has to be unique 
//
// $json		    = the array created by json_encode()
// $key			    = the unique key (string) that is searched on 
// $value		    = the value that should belong to that key
//
// returns array containing the found pair
//
// ****************************************************************
function find_array_in_json(Array $json, $key, $value) 
{   
    foreach ($json as $subarray){  
        if (isset($subarray[$key]) && $subarray[$key] == $value)
          return $subarray;       
    } 
}

// Search (json style) multi-level array for specific key <=> value pair 
// return each array that applies
function find_objects_in_json($array, $key, $value)
{
    $results = array();

    if (is_array($array)) {
        if (isset($array[$key]) && $array[$key] == $value) {
            $results[] = $array;
        }

        foreach ($array as $subarray) {
            $results = array_merge($results, find_objects_in_json($subarray, $key, $value));
        }
    }
    return $results;
}

?>