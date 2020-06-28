<?php
/********************************************************************************************/
/*                                                                            Pieter Groen  */
/*  Version 0.1 - June 27, 2020                                                             */
/*                                                                                          */
/*  PHP for Groen Productions website CMS in WordPress                                      */
/*                                                                                          */
/* SECTION: Debug functions                                                                 */
/* - Debugging into debug.log                                               (~line   55)    */
/* - Logging into user_activity_log_{year}_{month}.txt                      (~line   75)    */
/*                                                                                          */
/* SECTION: User Management and Session Security                                            */ 
/* - Custom Admin Dashboard functions:                                      (~line  100)    */
/* - based on twentytwenty theme                                                            */
/* - add login cookie message                                               (~line  110)    */
/* - make login links language sensitive                                    (~line  150)    */
/* - create Manager role based on author and allow User Admin               (~line  180)    */
/* - simplify Profile pages for non Administrators                          (~line  200)    */
/* - collapse side menu for Subscriber role                                 (~line  265)    */
/* - remove unneccesary widgets                                             (~line  300)    */
/* - stop heartbeat (ajax calls) DISABLED                                   (~line  360)    */
/*                                                                                          */
/* SECTION: User mails and msgs are adjusted to user's locale (es, nl, en):                 */
/* - Redirect blocked users                                                 (~line  375)    */
/* - Email change notification                                              (~line  440)    */
/* - Password change notification                                           (~line  570)    */
/* - New password request                                                   (~line  625)    */
/*                                                                                          */
/* SECTION: Tracing user activity and changes to databases                  (~line  740)    */
/* - Logging user activities in Standard Wordpress interface                (~line  740)    */
/* SECTION: insertion of asset files into head                              (~line  860)    */
/* - pomo setup for i18n                                                    (~line  920)    */
/*                                                                                          */
/* Plugins needed:                                                                          */
/* - Groen Productions Mailing plugin to change registration mails                          */
/* - WP-Mail-SMTP plugin to use groenproductions.com for registration mail                  */
/*                                                                                          */
/* SECTION: Creation of Meta Boxes and linking of files:                                    */
/* - Require PHP files based on user privileges                             (~line  940)    */
/* - Welcome Meta Box                                                       (~line  950)    */
/* - Settings Meta Box                                                      (~line 1000)    */
/*                                                                                          */
/* Functions used in other Groen Productions PHP files for site management:                 */
/* - Determine User's privileges for PHP file                               (~line 1080)    */
/* - Get project data based on PHP file                                     (~line 1125)    */
/* - Get project data based on page slug                                    (~line 1150)    */
/* - Create menu entry and page (generic)                                   (~line 1190)    */
/*                                                                                          */
/* SECTION: General helper functions                                                        */
/* - Connect to Groen Productions database                                  (~line 1270)    */
/* - Retrieve likely locale when user's logged out                          (~line 1300)    */
/* - Upload pictures                                                        (~line 1330)    */
/* - Input/output functions for database, forms, web page                   (~line 1420)    */
/*   . Sanitization of input data                                             (~line 1450)  */
/*   . Sanitization of input for prepared statements                          (~line 1515)  */
/*   . Display (escaping and formatting) of database data                     (~line 1620)  */
/* - Search (json) arrays for key <=> value pair                            (~line 1700)    */
/*                                                                                          */
/********************************************************************************************/
// namespace groenp;

// *****************************************************************************************    
// DEBUGGING, use: _log(). Arrays need to go into separate call
// *****************************************************************************************    
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

// *****************************************************************************************    
// Groen Productions - Logging in User Activity Log
// *****************************************************************************************    
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

// ***************************************************************************************** //
// Groen Production                                                                          //
// SECTION: User Management and Session Security                                             //
//                                                                                           //
// ***************************************************************************************** //

// *****************************************************************************************    
// Attach admin login header logo and removal of return-to-blog link by CSS
// *****************************************************************************************    
// see: function groenp_include_in_head()


// *****************************************************************************************    
// Custom admin login message
// *****************************************************************************************    
add_filter('login_message', 'groenp_cookie_warning_login');
function groenp_cookie_warning_login() {

    // retrieve locale from url query, or from html page
    $locale = groenp_anon_locale();
    $lng = strtolower( substr($locale, 0, 2) );

    switch($lng):

        case 'nl':
            $message = "<p class='message lowlight'>
            Deze site gebruikt cookies om uw gebruikerssessie te waarborgen en uw voorkeuren vast te leggen.<br><br>
            Door in te loggen accepteert u het gebruik van cookies. 
            Bekijk de <a href='privacy_and_terms_of_use.php?wp_lang=". $locale."'>Privacy verklaring en de gebruiksvoorwaarden</a> voor alle details.</p>";
            break;

        case 'es':
            $message = "<p class='message lowlight'>
            Este sitio utiliza cookies para salvaguardar su sesión y realizar un seguimiento de sus preferencias.<br><br>
            Al iniciar sesión, acepta el uso de estas cookies. 
            Consulte la <a href='privacy_and_terms_of_use.php?wp_lang=". $locale."'>Declaración de privacidad y los Términos de uso</a> para obtener todos los detalles.</p>";
            break;

        default:
            $message = "<p class='message lowlight'>
            This site uses cookies in order to safeguard your session and to keep track of your preferences.<br><br>
            By logging in you accept the use of these cookies. 
            Please review the <a href='privacy_and_terms_of_use.php?wp_lang=". $locale."'>Privacy Statement and Terms of Use</a> for all details.</p>";

    endswitch;
    return $message;
}


// *****************************************************************************************    
// Change admin login logo link
// *****************************************************************************************    
add_filter('login_headerurl', 'groenp_change_wp_login_url');
function groenp_change_wp_login_url()
{
    return trailingslashit(admin_url());
}

// *****************************************************************************************    
// Make Forgot password link and login link language sensitive
// *****************************************************************************************    
add_filter('lostpassword_url', 'groenp_lostpassword_url', 10, 2);
function groenp_lostpassword_url()
{
    return site_url('wp-login.php?action=lostpassword&wp_lang='. groenp_anon_locale() );
}

add_filter('login_url', 'groenp_login_url', 10, 2);
function groenp_login_url()
{
    return site_url('wp-login.php?&wp_lang='. groenp_anon_locale() );
}


// *****************************************************************************************    
// Reset the login page to be main Dashboard page (not profile page)
// *****************************************************************************************    
add_filter('login_redirect', 'groenp_login_to_dashboard', 10, 2);
function groenp_login_to_dashboard( $redirect_to, $request ) {
    return admin_url('index.php');
}

// *****************************************************************************************    
// ONLY ON CHANGE OF THEME:
// Simplify capabilities of Admin and others, so not to clutter the U/I
// Allow Manager role (somebody working for Groen Productions) to do user admin 
// Remove Editor and Contributor roles
// *****************************************************************************************    
// this is saved in the DB so only do this once...
// add_action( 'after_switch_theme', 'groenp_create_manager_role', 10 ,  2);

function groenp_create_manager_role($oldname, $oldtheme=false) {
    global $wp_roles;

    if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();

    // Manager role will be based on Author 
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


// *****************************************************************************************    
// Clean up Profile pages for non Admin (subscriber and author roles)
//
// (jQuery adjustments in footer: check with each update to WP)
// *****************************************************************************************    
add_action('admin_init', 'groenp_user_profile_fields_disable');
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

            // remove 'About Yourself' section
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

// *****************************************************************************************    
// Collapse side menu for Subscriber role
// *****************************************************************************************    
add_action('admin_init', 'groenp_collapse_side_menu_for_subscriber');
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

// *****************************************************************************************    
// Admin footer modification
// *****************************************************************************************    
add_filter('admin_footer_text', 'groenp_change_footer_admin');
function groenp_change_footer_admin ()
{
    // TRANSLATORS: %s: Groen Productions 
    echo "<span id='footer-thankyou'>". sprintf( __('Developed in WordPress by %s', 'groenp'), 'Groen Productions') ."</span>";
}

// *****************************************************************************************    
// Set upload folder to be uploads/ (just in case somebody changes it in the admin)
// *****************************************************************************************    
define( 'UPLOADS', 'wp-content/uploads' );


// *****************************************************************************************    
// Remove meta boxes from wordpress dashboard for all users
// *****************************************************************************************    
add_action('wp_dashboard_setup', 'groenp_remove_dashboard_widgets' );
function groenp_remove_dashboard_widgets()
{
    // remove_meta_box('dashboard_site_health', 'dashboard', 'normal');		// site health status
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal');			// right now/at a glance
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');			// activity
 
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');			// quick press/quick draft
    remove_meta_box('dashboard_primary', 'dashboard', 'side');				// wordpress news and events
}
// *****************************************************************************************    
// Remove  WordPress Welcome Panel
remove_action('welcome_panel', 'wp_welcome_panel');

// Remove Screen Options tab
// add_filter('screen_options_show_screen', '__return_false');

// *****************************************************************************************    
// Remove Help tab, Comments count, '+' New widget, Gravatar, and change greeting in admin bar
// *****************************************************************************************    
add_action('admin_bar_menu', 'groenp_clean_up_toolbar_items', 999);
function groenp_clean_up_toolbar_items($wp_admin_bar) {

    $screen = get_current_screen();
    $screen->remove_help_tabs();

    // Add screen option: limited the number of columns on the Dasbaord main page for all users
    // user can choose between 1 or 2 columns (default 2 for dashboard, 1 for others) 
    if ( $screen->id == 'dashboard' ){
        add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
    } else {
        add_screen_option('layout_columns', array('max' => 2, 'default' => 1) );
    }
    // _log('screen ID: ' . $screen->id);
    
    // _log("remove toolbar items: "); _log($wp_adminbar); // DEBUG //
	$wp_admin_bar->remove_node('wp-logo');
  	$wp_admin_bar->remove_node('comments');
    $wp_admin_bar->remove_node('new-content');
    $wp_admin_bar->remove_node( 'user-info' );

    $user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    // TRANSLATORS: Welcome + username
    $newtitle = "<span id='title-greeting' class='greeting'>". __('Welcome', 'groenp') ." </span> '<span class='display-name'>" . $current_user->display_name . "'</span>";

    // update the node with the changes
    $wp_admin_bar->add_node( array( 
        'id'        => 'my-account',
        'parent'    => 'top-secondary',
        'title'     => $newtitle,
        'href'      => false,
        'meta'      => array(
            'class'     => '',
        ),
    ) );
}


// *****************************************************************************************    
// Stop admin-ajax.php making unneccesary calls every 30secs
// *****************************************************************************************    
// add_action( 'init', 'stop_heartbeat', 1 );
// function stop_heartbeat() {
//         wp_deregister_script('heartbeat');
// }

// ***************************************************************************************** //
// Groen Production                                                                          //
// SECTION: User mails and msgs                                                              //
//                                                                                           //
// ***************************************************************************************** //

// *****************************************************************************************    
// Redirect blocked user to login page - close session
// *****************************************************************************************    
add_action( 'admin_init', 'groenp_redirect_blocked_users' );
function groenp_redirect_blocked_users() {

    // Open groenp_sites_cms database
    $con = groenp_open_database();

    // Get user status
    $wp_userID = get_current_user_id();
    $result = mysqli_query($con, 'SELECT is_usr_blocked FROM gp_subscribers WHERE fr_ID="' . $wp_userID . '";');
    if ( $result ) $row = mysqli_fetch_array($result);

    // Close database
    mysqli_close($con);

    
	if ( $result && $row[0] ) 
    {
        // Retrieve user's locale before user is forced out
        $locale = get_user_locale( $wp_userID );

        _lua("WPuser", "Subscriber (wpID:" . $wp_userID.") is trying to login, but has been BLOCKED!");
        session_unset(); 
        //session_destroy();
        wp_redirect( site_url('wp-login.php?custom_logout=yes&wp_lang='.$locale) );
    }
}
// *****************************************************************************************
// Define custom message for force-out situation
// *****************************************************************************************
if(!empty($_GET['custom_logout']) && strtolower($_GET['custom_logout']) == 'yes'){

    add_filter('login_message', 'groenp_force_out_message');
    function groenp_force_out_message() {

        if ( !empty($_GET['wp_lang']) ) {

            //switch on the first 2 chars
            $lng = strtolower( substr($_GET['wp_lang'], 0, 2) );

            switch($lng): 

                case 'nl':
                    $message = "<p class='message'>Uw gebruikersnaam is geblokkeerd.<br />
                    Neem contact op met de <a href='mailto:admin@groenproductions.com'>administratie van Groen Productions</a> voor meer informatie.</p>";
                    break;

                case 'es':
                    $message = "<p class='message'>Su nombre de usuario ha sido bloqueada.<br />
                    Por favor, póngase en contacto con la <a href='mailto:admin@groenproductions.com'>administración de Groen Productions</a> para obtener más información.</p>";
                    break;

                default:
                    $message = "<p class='message'>Your username has been blocked.<br />
                    Please contact <a href='mailto:admin@groenproductions.com'>administration at Groen Productions</a> for more information.</p>";

            endswitch;

        } else {
            // create default message (en_US)
            $message = "<p class='message'>Your username has been blocked.<br />
            Please contact <a href='mailto:admin@groenproductions.com'>administration at Groen Productions</a> for more information.</p>";
        }

        return $message;
    }
}

// *****************************************************************************************    
// New email with link to user for confirmation - message  filter hook
// (Change of msg body only)
// *****************************************************************************************    
add_filter( 'new_user_email_content',  'groenp_new_email_mail_message', 10, 2);
function groenp_new_email_mail_message( $message ) 
{
    //   $message: message body to build wp_mail().
    //
    //      The following strings have a special meaning and will get replaced dynamically:
    //      ###USERNAME###  The current user's username.
    //      ###ADMIN_URL### The link to click on to confirm the email change.
    //      ###EMAIL###     The new email.
    //      ###SITENAME###  The name of the site.
    //      ###SITEURL###   The URL to the site.
    
    // Retrieve user's locale 
    $user = wp_get_current_user();
    $locale = ($user)? get_user_locale( $user->ID ) : get_user_locale();
    $lng = strtolower( substr($locale, 0, 2) );

    switch($lng):

        case 'nl':
            $message = "U heeft recentelijk een aanvraag gedaan om het emailadres te wijzigen voor uw account (###USERNAME###)\r\n\r\n" .
                "Als dit juist is, dan kunt u nu deze link selecteren om de wijziging te bevestigen:  ###ADMIN_URL###\r\n\r\n" .
                "Als u deze wijziging niet heeft aangevraagd, kunt u dit bericht gewoon negeren. Er gebeurt dan niets.\n" .
                "Deze e-mail is verzonden aan ###EMAIL###\r\n\r\n" .
                "Met vriendelijke groeten,\nDe medewerkers van Groen Productions\n\n\n" .
                "Privacy verklaring: " . site_url('privacy_and_terms_of_use.php?wp_lang='. $locale ."\n\n");
            break;
            
        case 'es':
            $message = "Recientemente solicitaste cambiar la dirección de correo en su cuenta (###USERNAME###).\r\n\r\n" .
                "Si es correcto, visita el siguiente enlace para cambiarlo: ###ADMIN_URL###\r\n\r\n" .
                "Si no quiere realizar el cambio, puede ignorar este correo.\n" .
                "Este correo ha sido enviado a ###EMAIL###\r\n\r\n" .
                "Saludos,\nEl equipo de Groen Productions\n\n\n" .
                "Declaración de privacidad: " . site_url('privacy_and_terms_of_use.php?wp_lang='. $locale ."\n\n");
            break;
            
        default:
            $message = "You recently requested to have the email address changed on your account (###USERNAME###).\r\n\r\n" .
                "If this is correct, please select the following link to confirm the change: ###ADMIN_URL###\r\n\r\n" .
                "If you did not request this change, you can safely ignore this email. Nothing will happen.\n" .
                "This email has been sent to ###EMAIL###\r\n\r\n" .
                "Greetings,\nThe staff at Groen Productions\n\n\n" .
                "Privacy statement: " . site_url('privacy_and_terms_of_use.php?wp_lang='. $locale ."\n\n");

    endswitch;
    // adjust the message in the password change mail array
    _log("message for new email: "); _log($message);                                                 // DEBUG //

  return $message;
} // end of: groenp_new_email_mail_message()

// *****************************************************************************************    
// Changed email address notification to user - message  filter hook
// (Change of msg title and body)
// *****************************************************************************************    
add_filter( 'email_change_email',  'groenp_changed_email_mail_message', 10, 3);
function groenp_changed_email_mail_message( $email_change_email,  $user,  $userdata ) 
{
    //   $email_change_email: Used to build wp_mail().
    //  (all are strings:)
	//   $to      The intended recipients.
    //   $subject The subject of the email.  => __( '[%s] Email Changed' )
    //   $headers Headers.
	//   $message The content of the email.
    //      The following strings have a special meaning and will get replaced dynamically:
    //      - ###USERNAME###    The current user's username.
    //      - ###ADMIN_EMAIL### The admin email in case this was unexpected.
    //      - ###NEW_EMAIL###   The new email address.
    //      - ###EMAIL###       The old email address.
    //      - ###SITENAME###    The name of the site.
    //      - ###SITEURL###     The URL to the site.
    
    // Retrieve user's locale 
    $locale = get_user_locale( $user['ID'] );
    $lng = strtolower( substr($locale, 0, 2) );

    switch($lng):

        case 'nl':
            $message = "Dit bericht is een bevestiging dat het emailadres voor ###SITENAME### gewijzigd is voor de volgende gebruiker.\n\n" .
                "Nieuw emailadres: ###NEW_EMAIL###\r\n".
                "Gebruikersnaam: ###USERNAME###\r\n\r\n" .
                "Als u zelf niet het emailadres veranderd heeft, neem dan contact op met ons via: ###ADMIN_EMAIL###\n" .
                "Deze e-mail is verzonden aan ###EMAIL###\r\n\r\n" .
                "Met vriendelijke groeten,\nDe medewerkers van Groen Productions\n\n\n" .
                "Privacy verklaring: " . site_url('privacy_and_terms_of_use.php?wp_lang='. $locale ."\n\n");
            $subject = "Uw emailadres is gewijzigd voor %s";
            break;
            
        case 'es':
            $message = "Este aviso confirma que el correo electrónico por ###SITENAME### ha sido cambiado por el siguiente usuario.\n\n" .
                "Neuvo correo electrónico: ###NEW_EMAIL###\r\n" .
                "Nombre de Usuario: ###USERNAME###\r\n\r\n" .
                "Si usted no ha cambiado el correo electrónico, contacte con nosotros en: ###ADMIN_EMAIL###\n" .
                "Este correo ha sido enviado a ###EMAIL###\r\n\r\n" .
                "Saludos,\nEl equipo de Groen Productions\n\n\n" .
                "Declaración de privacidad: " . site_url('privacy_and_terms_of_use.php?wp_lang='. $locale ."\n\n");
            $subject = "Su correo electrónico ha sido cambiado por %s";
            break;
            
        default:
            $message = "This notice confirms that the email address for ###SITENAME### has been changed for the following user.\n\n" .
                "New email address: ###NEW_EMAIL###\r\n". 
                "Username: ###USERNAME###\r\n\r\n" .
                "If you did not change the email address, please contact us at: ###ADMIN_EMAIL###\n" .
                "This email has been sent to ###EMAIL###\r\n\r\n" .
                "Greetings,\nThe staff at Groen Productions\n\n\n" .
                "Privacy statement: " . site_url('privacy_and_terms_of_use.php?wp_lang='. $locale ."\n\n");
            $subject = "Your email address has been changed for %s";
        
    endswitch;
    // adjust the message in the password change mail array
    $email_change_email[ 'message' ] = $message;
    $email_change_email[ 'subject' ] = $subject;
    _log("message for changed email: ". $subject); _log($message);                                                 // DEBUG //

  return $email_change_email;
} // end of: groenp_changed_email_mail_message()


// *****************************************************************************************    
// Changed password notification to user - message  filter hook
// (Change of msg title and body)
// *****************************************************************************************    
add_filter( 'password_change_email',  'groenp_changed_pwd_mail_message', 10, 3);
function groenp_changed_pwd_mail_message(  $pass_change_email,  $user,  $userdata ) 
{
    // Originally: $pass_change_email['subject'] = "[%s] Password changed" 
    // (%s = $blogname and $blogname = "Groen Productions | Site Management Tool")

    $user_login = $userdata['user_login'];
    $user_email = $userdata['user_email'];

    // Retrieve user's locale 
    $locale = get_user_locale( $user['ID'] );
    $lng = strtolower( substr($locale, 0, 2) );

    switch($lng):

        case 'nl':
            $message = "Dit bericht is een bevestiging dat het wachtwoord gewijzigd is voor de volgende gebruiker.\n\n" .
                "Gebruikersnaam: " . $user_login . "\r\n\r\n" .
                "Als u zelf niet het wachtwoord veranderd heeft, neem dan contact op met ons via: admin@groenproductions.com\n" .
                "Deze e-mail is verzonden aan ". $user_email ."\r\n\r\n" .
                "Met vriendelijke groeten,\nDe medewerkers van Groen Productions\n\n\n" .
                "Privacy verklaring: " . site_url('privacy_and_terms_of_use.php?wp_lang='. $locale ."\n\n");
            $subject = "Uw wachtwoord is gewijzigd voor %s";
            break;
            
        case 'es':
            $message = "Este aviso confirma que la contraseña ha sido cambiado por el siguiente usuario.\n\n" .
                "Nombre de Usuario: " . $user_login . "\r\n\r\n" .
                "Si usted no ha cambiado la contraseña, contacte con nosotros en: admin@groenproductions.com\n" .
                "Este correo ha sido enviado a ". $user_email ."\r\n\r\n" .
                "Saludos,\nEl equipo de Groen Productions\n\n\n" .
                "Declaración de privacidad: " . site_url('privacy_and_terms_of_use.php?wp_lang='. $locale ."\n\n");
            $subject = "Su contraseña ha sido cambiado por %s";
            break;
            
        default:
            $message = "This notice confirms that the password has been changed for the following user.\n\n" .
                "Username: " . $user_login . "\r\n\r\n" .  
                "If you did not change your password, please contact us at: admin@groenproductions.com\n" .
                "This email has been sent to ". $user_email ."\r\n\r\n" .
                "Greetings,\nThe staff at Groen Productions\n\n\n" .
                "Privacy statement: " . site_url('privacy_and_terms_of_use.php?wp_lang='. $locale ."\n\n");
            $subject = "Your password has been changed for %s";
        
    endswitch;
    // adjust the message in the password change mail array
    $pass_change_email[ 'message' ] = $message;
    $pass_change_email[ 'subject' ] = $subject;
    _log("message for changed password: ". $subject); _log($message);                                                 // DEBUG //
  return $pass_change_email;
}

// *****************************************************************************************
// User requested new password - message filter hook
// (change msg body only)
// *****************************************************************************************
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
    
    // Retrieve user's locale 
    $locale = get_user_locale( $user_data->ID );
    $lng = strtolower( substr($locale, 0, 2) );

    // Set up message for password retrieval in nl, es, en
    switch($lng):

        case 'nl':
            $message = "Er is een reset voor het wachtwoord van uw account aangevraagd bij Groen Productions | Site Management Tool.\n\n" .
                "Gebruikersnaam: " . $user_login . "\r\n\r\n" .
                "Als u deze reset niet heeft aangevraagd, kunt u dit bericht gewoon negeren. Er gebeurt dan niets.\n\n" .
                "Selecteer deze link om uw wachtwoord te resetten:\n" .
                site_url("wp-login.php?action=rp&wp_lang=$locale&key=$key&login=" . rawurlencode($user_login), 'login') ."\r\n\r\n" .
                "Wij hopen dat het gebruik van de Site Management Tool bevalt. Als u nog vragen of suggesties heeft, aarzel dan niet om contact op te nemen via: admin@groenproductions.com\n\n" .
                "Met vriendelijke groeten,\nDe medewerkers van Groen Productions\n\n\n" .
                "Privacy verklaring: " . site_url('privacy_and_terms_of_use.php?wp_lang='. $locale ."\n\n");
            break;

        case 'es':
            $message = "Alguien ha solicitado un restablecimiento de la contraseña para su cuenta en el Site Management Tool | Groen Productions.\n\n" .
                "Nombre de Usuario: " . $user_login . "\r\n\r\n" .
                "Si no desea cambiar la contraseña, tranquilamente puede ignorar este mensaje y nada va a pasar.\n\n" .
                "Para cambiar su contraseña, ingrese al siguiente enlace:\n" .
                site_url("wp-login.php?action=rp&wp_lang=$locale&key=$key&login=" . rawurlencode($user_login), 'login') ."\r\n\r\n" .
                "Esperamos que disfrute usando la herramienta de Site Management. Si tiene algún pregunta o sugerencias, por favor no dude en contactar con nosotros a: admin@groenproductions.com\n\n" .
                "Saludos,\nEl equipo de Groen Productions\n\n\n" .
                "Declaración de privacidad: " . site_url('privacy_and_terms_of_use.php?wp_lang='. $locale ."\n\n");
            break;

        default:
            $message = "A password reset has been requested for your account on the Groen Productions | Site Management Tool.\n\n" .
                "Username: " . $user_login . "\r\n\r\n" .
                "If you did not request this, just ignore this email and nothing will happen.\n\n" .
                "To reset your password, select the following link:\n" .
                site_url("wp-login.php?action=rp&wp_lang=$locale&key=$key&login=" . rawurlencode($user_login), 'login') ."\r\n\r\n" .
                "We hope that you enjoy using the Site Management tool. If you have any questions or suggestions please do not hesitate to contact us at: admin@groenproductions.com\n\n" .
                "Greetings,\nThe staff at Groen Productions\n\n\n" .
                "Privacy statement: " . site_url('privacy_and_terms_of_use.php?wp_lang='. $locale ."\n\n");

    endswitch;
    _log("message for retrieve password: "); _log($message);                                                 // DEBUG //
    return $message;
} // end of: groenp_retrieve_password_message()

// *****************************************************************************************
// User requested new password - message filter hook
// (change msg title only)
// *****************************************************************************************
add_filter( 'retrieve_password_title',  'groenp_retrieve_password_title', 10, 2);
function groenp_retrieve_password_title(  $title,  $user_login ) 
{
    // $title       = Default email title.
    // $title       = sprintf( __( '[%s] Password Reset' ), $site_name );
    // $user_login  = The username for the user.

    // Get official site name for email header
    $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

    // Get user locale
    $user = get_user_by('login', $user_login);
    $locale = get_user_locale( $user->ID );
    $lng = strtolower( substr($locale, 0, 2) );

    // Set up title for password retrieval in nl, es, en
    switch($lng):

        case 'nl':
            $title = "Wachtwoord reset voor ". $user_login ." op ". $site_name;
            break;

        case 'es':
            $title = "Restablecimiento de contraseña para ". $user_login ." por ". $site_name;
            break;

        default:
            $title = "Password reset for ". $user_login ." on ". $site_name;
    endswitch;

    return $title;

} // end of: groenp_retrieve_password_title()


// **************************************************************************************** //   
// Groen Production                                                                         //
// SECTION: Log database actions in monthly log files                                       //
//                                                                                          //
// **************************************************************************************** //    
//  For standard WordPress interface: 
//  'Add New User', 'Edit', 'Delete', 'Forgot password', 'Start session', 'Close session' 
// *****************************************************************************************
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

add_action('edit_user_profile_update', 'groenp_log_update_user', 10, 1);                    // hooked at reload of user details page (of other users)
add_action('personal_options_update', 'groenp_log_update_user', 10, 1);                     // hooked at reload of own user details page
function groenp_log_update_user( $user_id ) 
{
    global $wpdb;
    $del_user = get_userdata( $user_id );
    if ( !empty($_POST['pass1']) &&  !empty($_POST['pass2']) )                              // this works because the pwd fields are emptied, when an error occurs
    {                                                                                       // if the pwd fields are returned with the $_POST it must be a successful submit
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


add_action( 'delete_user', 'groenp_log_delete_user');                                       // hooked just before deletion
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

add_action( 'deleted_user', 'groenp_log_deleted_user');                                     // hooked AFTER deletion (so can't refer to user data)
function groenp_log_deleted_user( $user_id ) 
{
    _lua("WPuser", "User delete successful.");
}

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
    if ( $user->roles[0] == 'subscriber')
    {
        _lua("WPuser", "Subscriber (wpID:". $user->ID .", ". $user_login .") logged in.");
    } else {
        _lua("WPuser", "Admin user (wpID:". $user->ID .", ". $user_login .") logged in.");
    }
}

// add_action('wp_logout', 'groenp_log_logout');                                               // called after auth cookie cleared
add_action('clear_auth_cookie', 'groenp_log_logout');                                       // called before auth cookie cleared
function groenp_log_logout() 
{
    // global $wpdb;
    $user = wp_get_current_user();
    if ( isset($user) && ($user->ID != '0') )
    {
        if ( $user->roles[0] == 'subscriber')
        {
            _lua("WPuser", "Subscriber (wpID:". $user->ID .", ". $user->user_login .") logged out.");
        } else {
            _lua("WPuser", "Admin user (wpID:". $user->ID .", ". $user->user_login .") logged out.");
        }
    } else {
        _lua("WPuser", "User logged out.");
    }
}


// **************************************************************************** //
// Groen Production                                                             //
// SECTION: Insert Assets files into Management pages                           //
//                                                                              //
// **************************************************************************** //

// *****************************************************************************************    
// Groen Productions  - groenp_script_enqueuer()
//                    - Includes jQuery (ajax) javascript at the right spot, and 
//                      non-subscriber javascript only for GP admin
// *****************************************************************************************    

function groenp_script_enqueuer() 
{
    // default SSL port number OR http: port number; use minimized version, otherwise not
    $min_url = ($_SERVER['SERVER_PORT'] == '443' || $_SERVER['SERVER_PORT'] == '80') ? '.min' : '';
    
    wp_register_script( 'groenp-sites-cms', trailingslashit( get_stylesheet_directory_uri() ) .'assets/groenp-sites-cms' . $min_url . '.js', array('jquery') );
    // wp_localize_script( 'groenp-sites-cms', 'groenpAsync', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
    if ( current_user_can('list_users') ) {
        wp_register_script( 'groenp-sites-cms-admin',  trailingslashit( get_stylesheet_directory_uri() ) . 'assets/groenp-sites-cms-admin' . $min_url . '.js', array('jquery') );
    }

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'groenp-sites-cms' );
    if ( current_user_can('list_users') ) wp_enqueue_script( 'groenp-sites-cms-admin' );
} // End of: groenp_script_enqueuer()
add_action( 'init', 'groenp_script_enqueuer' );


// *****************************************************************************************    
// Groen Productions  - groenp_include_in_head()
//                    - Includes in head: 
//                      groenp-sites-cms(.min).css
//
// *****************************************************************************************    
function groenp_include_in_head() 
{ 
    // default SSL port number OR http: port number; use minimized version, otherwise not
    $min_url = ($_SERVER['SERVER_PORT'] == '443' || $_SERVER['SERVER_PORT'] == '80') ? '.min' : '';

    // include style sheet (use dashicons instead of font awesome)
    echo "<link type='text/css' href='" . trailingslashit( get_stylesheet_directory_uri() ) . "assets/groenp-sites-cms" . $min_url . ".css' rel='stylesheet' media='all' />";
} 
add_action('admin_head','groenp_include_in_head');
add_action('login_head','groenp_include_in_head');


// Prints script in footer of Dashboard pages - NOT NEEDED ANYMORE
// add_action( 'admin_footer', 'groenp_print_script_in_footer' ); // is placed only in other .php admin files
function groenp_print_script_in_footer() {
    echo "<script type='text/javascript'>
        jQuery(document).ready(function(){ 

            // initialize meta box handling
            postboxes.add_postbox_toggles(pagenow); 
        });
        </script>";
}
// *****************************************************************************************    
// Groen Productions  - groenp_pomo_setup()
//                    - Load the groenp mo files from /wp-content/languages/themes
// *****************************************************************************************    
add_action( 'after_setup_theme', 'groenp_pomo_setup' );
function groenp_pomo_setup(){

    load_child_theme_textdomain( 'groenp', get_template_directory() . '/languages' );
}



// **************************************************************************** //
// Groen Production                                                             //
// SECTION: Require PHP files depending on user privileges                      //
//          - Define common Meta Boxes: WELCOME, SETTINGS                       //
//          - Helper functions                                                  //
//                                                                              //
// **************************************************************************** //


// *****************************************************************************************    
// Groen Productions - Require other project's PHP files
//                     - Depending on role or Prjct/Sbscrbr assignment
// *****************************************************************************************    
/* all meta boxes for the Subscribers page have been defined in: 'groenp_subscribers.php' */
if ( current_user_can('list_users') )                           require_once( 'groenp_subscribers.php' );

// Check user privileges and determine which sections can be loaded (ADMIN has always access)
if ( groenp_load_on_privileges( 'groenp_test_mgmt.php' ) > 0 )  require_once( 'groenp_test_mgmt.php' );
if ( groenp_load_on_privileges( 'groenp_test2_mgmt.php' ) > 0 )  require_once( 'groenp_test2_mgmt.php' );


// *****************************************************************************************    
// Groen Productions - Create WELCOME Meta Box for Dashboard  
// *****************************************************************************************    
add_action( 'wp_dashboard_setup', 'groenp_dashboard_meta_boxes_add' );  
function groenp_dashboard_meta_boxes_add()  
{  
    // TRANSLATORS: %s: Groen Productions | Site Management Tool (also translated)
    wp_add_dashboard_widget( 'welcome-mb', sprintf( __("Welcome to the %s", 'groenp'), __("Groen Productions | Site Management Tool", 'groenp')) , 'groenp_welcome_meta_box_cb');
}

// *****************************************************************************************    
// Groen Productions - Callback for WELCOME Meta Box 
//                      - i19n translatable
// *****************************************************************************************    
function groenp_welcome_meta_box_cb()
{  
    // Retrieve user's locale 
    $user = wp_get_current_user();
    $locale = ($user)? get_user_locale( $user->ID ) : get_user_locale();
    // _log("user: ". $user->ID .", locale: " . $locale);                                                          // DEBUG //

    // Meta box introduction
    echo "<p>". __("Welcome to the Site Management Tool. This tool allows you to create dynamic content for your site. Inside the box(es) for your own website you can find instructions on how to do this.", 'groenp') ."</p>

    <p>". // TRANSLATORS: text + link to: Privacy Statement and Terms of Use (already translated)
    __("Please be aware that while you use this tool, you must adhere to our Terms of Use. You can find them here, together with the Privacy Statement and the explanation of our use of cookies: ",'groenp') ."
    <a href= '". site_url('privacy_and_terms_of_use.php?wp_lang=' . $locale) ."'>". __("Privacy Statement and Terms of Use", 'groenp') ."</a>.</p>".

    // text + Profile page (separately translated)
    "<p>". __("If this is your first time using this tool, please change your password as soon as is convenient to you. You can change it on your",'groenp') ." <i class='wpicon'>&#xf110;</i>&nbsp;<a href= '". admin_url('profile.php') ."'>". 
    // TRANSLATORS: %s: Profile (part of core po-file)
    sprintf( __("%s page", 'groenp'), /* TRANSLATORS: DO NOT TRANSLATE; part of core po-file*/ __("Profile", 'core')). "</a>. ".
    // TRANSLATORS: Copy over code as is; %s: [icon] 'Dashboard' (part of core po-file)
    sprintf( __("You can return to this page by selecting <i class='wpicon'>&#xf226;</i>&nbsp;&lsquo;%s&rsquo; in the side menu.", 'groenp'), 
    // TRANSLATORS: DO NOT TRANSLATE; part of core po-file
    __("Dashboard", 'core'))."</p>".

    // TRANSLATORS: text + email link to admin@groenproductions.com
    "<p>". __("I hope that you enjoy using the tool. If you have any questions and/or suggestions, please do not hesitate to contact me at: ",'groenp') . 
    "<a href='mailto://admin@groenproductions.com'>admin@groenproductions.com</a></p>

    <p>". __("Have a nice day!<br />
    Pieter at Groen Productions",'groenp') . "</p>"; 
    include('assets/GroenProductions.min.svg');
    // TRANSLATORS: in imperative voice
    echo "<p id='welcome-mb-boxctrl' class='htb'><a href='#'>". __("remove this box",'groenp') ."</a></p>";
    
} // End of: groenp_welcome_meta_box_cb()


// *****************************************************************************************    
// Groen Productions - Callback for general SETTINGS Meta Box  
//                      - Projects table driven 
//                      - $php_file = name of php file (basename())
// *****************************************************************************************    
function groenp_settings_meta_box_cb()
{  
    // retrieve project information
    global $plugin_page;
    if ( isset($plugin_page) ) {

        // should be only 1 settings box per page; make box unique per page = project
        $project = groenp_get_project_from_slug( $plugin_page );

    } else {
        // it's on dashboard, so most likely Subscriber; it may only have one project assigned
        global $project; 
    }

    // retrieve user's locale 
    $user = wp_get_current_user();
    $locale = ($user)? get_user_locale( $user->ID ) : get_user_locale();

    // make this form unique
    $func = 'Rfrsh';
    
    // default SSL port number; use https version, otherwise not
    $protocol = ($_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';

    // create form url for this meta box
	$form_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '#' . $func;

    // store the switch setting on load of form 
    // switch can be set ("on" = TEST), can be off and not showing in $_POST ( "" = LIVE), or simply not set (other form loaded etc.)
    // for this last alternative, $test_set is created that always stores the setting on load. it shows in every form
    // 1) check if any form is submitted. if not, just load as not set, so LIVE
    // 2) check if switch is set (by pushbutton: 'RefreshLT') and then check switch ('live-test') ignores everything else
    // 3) check test_set field (switch is not used, simply copy over from previous submit)
    $test_set = isset($_POST['test_set'])? ( isset($_POST['RefreshLT'])? ( isset($_POST['live-test'])? $_POST['live-test'] : "" ) : $_POST['test_set'] ) : "";

    // Meta box introduction
    // TRANSLATORS: Please review the + Privacy Statement and Terms of Use 
    echo "<a class='anchr' name=" . $func . "></a>
    <form action='" . $form_url . "' method='post' enctype='multipart/form-data'>
        <p>". __("Please review the",'groenp') . " <a href= '". site_url('privacy_and_terms_of_use.php?wp_lang=' . $locale) ."'>".  __("Privacy Statement and Terms of Use", 'groenp') ."</a>.</p>";

        // switch and buttons only shown if there is a test version
        // create hidden field that contains the state of the switch as the page loads
        // the custom switch does not necessarily show the correct state at load, so needs to be set through jquery 
        if ( $project['is_test_active'] ) echo "
        <input type='hidden' name='test_set' id='test_set' value='". $test_set ."' />
        <p class='hor-form test-switch'>
            <span class='prompt'>Choose here which version of your CMS you want to work on:<br />
            <span class='context testver'>Only those that have access to the test version will see it. It is a great way to try things out, before everybody sees it.</span>
            <span class='context livever'>The live version is directly connected to the live site.</span></span>
            <label class='test-switch'>
                <input id='live-test' name='live-test' type='checkbox' " . dis($test_set,"chk_ctrl") . ">
                <span class='test-slider'></span>
            </label>
            <span class='context'>(these versions are not connected in any way, the data is not copied over)</span> 
        </p>
        <p class='hor-form'>
            <label for='refresh'>Retrieve data for selected version (Live/Test):</label><span>(reload page in order to retrieve the data for all the lists on this page)</span><button type='submit' class='button-primary' name='RefreshLT'>Reload entire page  <i class='wpicon'>&#xf463;</i></button>
        </p>
        <p class='hor-form livever'"; if ($test_set == "on") echo "style=' display: none;'"; echo ">
            <label for='open_site'>Verify the results on LIVE:</label><span>(website will open in a separate window or tab)</span><a type='button' class='button-primary launch' name='open_site' target='_blank' href='https://". $project['base_url'] ."'>Open LIVE website</a>
        </p>
        <p class='hor-form testver'"; if ($test_set == "") echo "style=' display: none;'"; echo ">
            <label for='open_site'>Verify the results on TEST:</label><span>(website will open in a separate window or tab)</span><a type='button' class='button-primary launch' name='open_site' target='_blank' href='https://". $project['test_url'] ."'>Open TEST website</a>
        </p>";

    // in any case echo this
    echo "</form>
    <p class='button-row btt'><a href='#'>back to top</a>
    </p>";

} // End: groenp_settings_meta_box_cb()


// *****************************************************************************************    
//  Groen Productions - Determine whether php file should be loaded based on
//                      the sbscrbr/project pairing in groenp_subscribers.php
//                      - $php_file = name of php file with project and MBs
//                      -> returns the number of hits of sbscrbr/project (PHP) pairing: 0 or 1
//
// *****************************************************************************************    
function groenp_load_on_privileges( $php_file )
{
    if ( current_user_can('list_users') ) 
    {
        // Part of Mgmt team, so always load MB but in separate page
        $load = 1;

    } else {
        // User is Subscriber and logged in 
        $current_user = wp_get_current_user();
        $sbscrbr_login = $current_user->user_login;

        // open database
        $con = groenp_open_database();

        // query: prjct_php-> pk = fk_prjct_id | fk_sbscrbr_id = pk <- sbscrbr_login
        $stmt = mysqli_prepare($con, 'SELECT spp.pk_sppair_id' .
            ' FROM gp_sbscrbr_prjct_pairings spp'.
            ' LEFT JOIN gp_projects prj ON (spp.fk_prjct_id = prj.pk_prjct_id)' .
            ' LEFT JOIN gp_subscribers sub ON (spp.fk_sbscrbr_id = sub.pk_sbscrbr_id)' . 
            ' WHERE prj.prjct_php = ? AND sub.sbscrbr_login = ?');
        mysqli_stmt_bind_param($stmt, 'ss', $php_file, $sbscrbr_login);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        // record the number of hits, this can be zero, one or more
        $load = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt); 

        // close database
        mysqli_close($con);   
    }
    return $load;
}  // end of: groenp_load_on_privileges( $php_file )


// *****************************************************************************************    
// Function to get Projects data from groenp_sites_cms database
// - Based on $php_file (basename() of __FILE__ )
// - Queries Projects table in groenp_sites_cms
// *****************************************************************************************    
function groenp_get_project_from_file( $php_file )
{
    // open database
    $con = groenp_open_database();

    // query projects and store in array
    // $result = mysqli_query($con, 'SELECT prjct_name, prjct_php, base_url, is_test_active, test_url FROM gp_projects WHERE prjct_php = "'. $php_file .'";');
    $result = mysqli_query($con, 'SELECT * FROM gp_projects WHERE prjct_php = "'. $php_file .'";');

    // there can only be one result row. In any case; only get the first one
    $row = mysqli_fetch_assoc($result);

    // $row will be null if there was no result, load error instead
    if (!isset($row)) $row = array('error'=>"Could not get project data for php file: ". $php_file .".");

    // free result and close database
    mysqli_free_result($result);
    mysqli_close($con);

    return $row;
} // end of: groenp_get_project_from_file()


// *****************************************************************************************    
// Function to get Projects data from groenp_sites_cms database
// - Based on global $plugin_page
// - Queries Projects table in groenp_sites_cms
// *****************************************************************************************    
function groenp_get_project_from_slug( $plugin_page )
{
    // open database
    $con = groenp_open_database();

    // query projects and store in array
    // $result = mysqli_query($con, 'SELECT prjct_name, prjct_php, base_url, is_test_active, test_url FROM gp_projects WHERE prjct_php = "'. $php_file .'";');
    $result = mysqli_query($con, 'SELECT * FROM gp_projects WHERE page_slug = "'. $plugin_page .'";');

    // there can only be one result row. In any case; only get the first one
    $row = mysqli_fetch_assoc($result);

    // $row will be null if there was no result, load error instead
    if (!isset($row)) $row = array('error'=>"Could not get project data for slug: ". $plugin_page .".");

    // free result and close database
    mysqli_free_result($result);
    mysqli_close($con);

    return $row;
} // end of: groenp_get_project_from_slug()



// *****************************************************************************************    
// Callback for sub menu page framework creation based on add_submenu_page() call
//
//        - third step in the meta box creation (do, specifically in own page)
//          (meta boxes: register -> add (creation in mem) -> do (placement))
//        - allows the metaboxes to be spread over 1 or 2 columns.
//        - Uses: 
//          global $title (page title)
//          global $plugin_page (page slug)
// *****************************************************************************************    
function groenp_create_page_cb() 
{
    // Retrieve project information
    global $title;
    global $plugin_page;

    echo "<div class='wrap'>
        <h2>". $title ."</h2>";
 
        // wp_nonce_field used to save closed meta boxes and their order. 
        // This is not working, because a form will interfere with the other forms in the Groen Productions Meta Boxes.
        // They are placed here, so the manipulation of these post boxes work during the page display.

		echo "<form method='post'>";
        wp_nonce_field($plugin_page);
        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
        wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		echo "</form>";

        echo "<div id='poststuff'>
 
            <div id='post-body' class='metabox-holder columns-2'>

                <!-- #post-body-content -->
                <div id='postbox-container-1' class='postbox-container'>
                    <!-- second column stays empty -->
                    <div class='meta-box-sortables ui-sortable empty-container' id='side-sortables'></div>
                </div>
 
                <div id='postbox-container-2' class='postbox-container'>

                    <!-- all metaboxes go into the first column -->";

        do_meta_boxes($plugin_page, 'normal', null); 

                echo "</div>

            </div> <!-- #post-body --> 
 
        </div> <!-- #poststuff -->

    </div>  <!-- wrap -->";
}

// *****************************************************************************************    
// Groen Productions  - groenp_register_meta_boxes()
//                      registers metaboxes based on unique global $plugin_page
//                    - first step in generic inclusion of a project's metaboxes
//                      meta boxes: register -> add (creation in mem) -> do (placement)
// *****************************************************************************************   
function groenp_register_meta_boxes() 
{
    // make sure to load the correct meta boxes for the page
    global $plugin_page;

    // trigger the add_meta_boxes hooks to allow meta boxes to be added 
    do_action('add_meta_boxes_' . $plugin_page, null);
    do_action('add_meta_boxes'  , $plugin_page, null);
 
    // enqueue WordPress' script for handling the meta boxes 
    wp_enqueue_script('postbox');
 
    // Add screen option: set to 1 in jQuery (groenp-sites-admin.js) 
}



// **************************************************************************** //
// Groen Production                                                             //
// SECTION: GENERAL HELPER FUNCTIONS                                            //
//                                                                              //
// **************************************************************************** //

// *****************************************************************************************    
//  Groen Productions - Open GROENP_SITES_CMS database 
//
//  There are 2 groenp users, one for each level of access:
//    - Same users defined on all servers
//    - Database is named groenp_sites_cms local and on Groen Productions server
//    - There is no separate database on test server
//
// *****************************************************************************************    
function groenp_open_database() 
{
    // connect with correct user and select correct db
    $db = 'groenp_sites_cms';

    if (current_user_can('list_users'))
    {
        // CRUD connection to groenp_sites_cms
        $connect = mysqli_connect('localhost','groenpf99v6Vd53','m^W$pQ&5W!j5', $db); // CRUD privileges
        // _log('groenp database connection opened with CRUD priv');                          // DEBUG //

    } else {
        // read only connection to groenp_sites_cms
        $connect = mysqli_connect('localhost','groenpRdrgYAUCm','#yCAh(j&kv>Q', $db); // R/O privileges
        // _log('groenp database connection opened with R/O priv');                          // DEBUG //
    }

    if (!$connect)
    {
        die('Could not connect to MySQL server: ' . mysqli_connect_error());
    }
    
    // change character set to utf8 
    if (mysqli_set_charset($connect, 'utf8')) {
        return $connect;
    } else {
        _log("Error loading character set utf8: " . mysqli_error($connect));
    }
}


// *****************************************************************************************    
//  Groen Productions - retrieve locale from url or <html>
//                      ONLY use when user is not logged in
// *****************************************************************************************    
function groenp_anon_locale()
{
    // _log('wp_lang: '. $_GET['wp_lang']);                                                                                                // DEBUG //
    // _log('get_language_attributes: '. str_replace('"', '', substr(get_language_attributes(), 6)));                                      // DEBUG //
    // _log('groenp_anon_locale: '. substr($locale, 0, 2));                                                                                // DEBUG //

    // Get the url cookie (url query), and if there is none, then get it from <html> element
    return isset( $_GET['wp_lang'] )? $_GET['wp_lang'] : str_replace('"', '', substr(get_language_attributes(), 6));

    // Return only main language to avoid confusion between 'es_PE' and 'es-PE' etc.
    // return substr($locale, 0, 2);
}

// *****************************************************************************************    
// Groen Productions - Upload Picture to Stroomt directory (optionally set max filesize and exact dimensions)
//
// $photo_file	= local path as it has been defined in the <input type='file'>
// $maxsize_kb	= maximum file size in KB (optional)
// $width		= image width in pixels (optional)
// &height		= image height in pixels (optional)
//
// *****************************************************************************************    

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


// *****************************************************************************************    
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
// *****************************************************************************************    
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

// *****************************************************************************************    
// Groen Productions - Sanitizes user input FOR PREPARED STATEMENTS (no qoutes),
//                     returns NULL when empty input.
//
// $input		    = user input
// $m			    = mode (string):
//   'wp'			    = string for wp_user, san handled by wp
//   'chk'			    = checkbox ('1' or '0')
//   'a'			    = only alpha-numeric plus '_' (everything else stripped)
//   'b'			    = boolean (true or false)
//   'i'			    = integer
//   'f'			    = float
//   'm'			    = monetary amount
//   'tr'			    = bookkeeping transaction, needs amount + crd/deb/bal indicator
//   'd'			    = numerical date (no time): dd-mm-yyyy
//   'dh'			    = date from hidden field: yyyy-mm-dd (MySQL format)
//   't'			    = time in hh:mm format
//   'tel'			    = telephone number as 9-digit integer (INT(9))
//   's'			    = string (default, escape slashes stripped)
//
// *****************************************************************************************    
function prep(&$input, $m = 's', $acc = '')
{
    if( $m=='wp' ) return $input; // don't do anything, wp will take care of san
    
    // php with checkboxes needs special handling
    if( $m=='chk' ) if ( empty($input) ) { return '0'; } else { return '1'; };
    
    // when empty make sure the word NULL is returned for query string building
    if( empty($input) && $input!== '0') return NULL;

    switch(strtolower($m)) // the real sanitization starts here
    {
        case 'a':
        return preg_replace( '/[^a-zA-Z0-9_]/', '', $input);

        case 'b':
        return ($input)? TRUE : FALSE;

        case 'i':
        return intval($input);

        case 'f':
        return floatval($input);

        case 'm':
        case 'tr':
        $amt = preg_replace( '/[^0-9\.,]/', '', $input );                           // strip off anything that's not a digit or potential delimiter
        $posp = strrpos( $amt, '.');                                                // last occurrence of .
        $posc = strrpos( $amt, ',');                                                // last occurrence of ,
        if($posp === false && $posc === false ) return floatval($amt);              // if no delimeters found, then return number

        $dec = ($posc > $posp)? substr($amt,$posc + 1) : substr($amt,$posp + 1);    // get decimal section (. or , used as decimal?)
        $amt = preg_replace( '/[\.,]/', '', $amt );                                 // strip off any delimeters
        $amt = preg_replace('/'.$dec.'$/', '', $amt);                               // strip off $dec part
        $amt = round( floatval($amt . '.' . $dec), 2);                              // create float and round it to 2 decimals
        if (strtolower($m) == 'm') return $amt;

        // case "tr" only:
        if      ( $acc == 'deb' ) { return (-1 * $amt); }
        elseif  ( $acc == 'crd' ) { return ($amt); }
        elseif  ( $acc == 'bal' ) { return (0); }
        else { 
            _log("prep(): error in transaction conversion with amount: " . $input . ", and accounting type: " . $acc . "."); 
            return 0;
        }

        case 'd':
        $date = date_create_from_format('d-m-Y',$input, new DateTimeZone('America/Lima'));
        if (!$date) return "input_error";
        date_time_set($date, 8,00,00); // yyyy-mm-dd 08:00:00
        return date_format($date, 'Y-m-d H:i:s');

        case 'dh':
        $date = date_create_from_format('Y-m-d',$input, new DateTimeZone('America/Lima'));
        if (!$date) return "input_error";
        return date_format($date, 'Y-m-d');

        case't':
        if ( strpos($input, ':') === FALSE ) return "input_error";
        $time = explode(':',$input);
        $time[0] = (strlen( (string)intval($time[0])) == 0 )? "00" : (string)intval($time[0]);
        $time[0] = str_pad(substr($time[0],0,2), 2, '0', STR_PAD_LEFT);
        $time[1] = (strlen( (string)intval($time[1])) == 0 )? "00" : (string)intval($time[1]);
        $time[1] = str_pad(substr($time[1],0,2), 2, '0', STR_PAD_LEFT);
        return gmdate('H:i', mktime($time[0], $time[1]));

        case 'tel':
        return intval( preg_replace( '/[^0-9]/', '', $input ) );

        case 's':
//        return htmlspecialchars(stripslashes($input), ENT_QUOTES, 'UTF-8');

        default:
        return stripslashes($input);
    }
} // end of: prep()


// *****************************************************************************************    
// Groen Productions - Displays sanitized data (from database or straight from $_POST) 
//                    to be used in filter fields 
//                    or from database (for specific display format)
//
// $output		    = output from database or straight from $_POST
// $f			    = format (string):
//   's'			    =  string (spec. chars escaped, straight from $_POST)
//   'a'			    =  display of alpha-numeric (no escaping, default)
//   'chk'			    =  checkbox ('Y' or '')
//   'chk_ctrl'		    =  checkbox control 
//   'rad_ctrl'		    =  radio control, also needs rad to be 0 or 1 to be set
//   'b'			    =  boolean (true or false)
//   'i'			    =  integer
//   'f'			    =  float/double
//   'fr'			    =  rounded float/double (+/-####.##) (used in compare of mut comment text)
//   'm'			    =  monetary amount (#,###.##)
//   'ms'			    =  signed monetary amount (+/-#,###.##)
//   'd'			    =  date (dd-mm-yyyy), used for input controls, etc.
//   'dl'			    =  date in language (dd-MMM-yyyy)
//
// *****************************************************************************************    
function dis(&$output, $f = 'a', $rad = NULL)
{
    // boolean always returns true or false 
    if ($f === 'b') return ($output) ? 'true' : 'false';

        //_log("output: " . $output);
    if ( $output === NULL) return ''; // when output is null return empty;

    if ($output === '*') return '*'; // usually a wildcard is allowed as well
 
    switch(strtolower($f)) // assume all output has been sanitized but not HTML safe
    {
        case 's':
        // this escapes the output so it doesn't screw the html
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8'); 

        case 's%':
        // to be used for filter controls (it receives straight from $_POST so slashes need to be stripped)
        // percentage signs for LIKE stmt (and single quotes) need to be placed around it
        return htmlspecialchars(stripslashes($output), ENT_QUOTES, 'UTF-8'); 

        case 'chk':
        // fill cell with 'Y' or nothing
        return !empty($output) ? 'Y' : '';  

        case 'chk_ctrl':
        // set control on or not (empty)
        return !empty($output) ? "checked='checked' " : "";

        case 'rad_ctrl':
        // set control on ('1') or not ('0'), depending on $rad
        if ( !isset($rad))  return '';
        return ($output == $rad) ? "checked='checked' " : "";

        case 'm':
        return number_format(abs($output),2);

        case 'ms':
        return number_format($output,2);

        case 'fr':
        return round($output, 2);

        case 'd':
        if ( $output==='NOW' ) {
            $date = new DateTime(null, new DateTimeZone('America/Lima'));
        } else {
            $date = date_create_from_format('Y-m-d H:i:s',$output, new DateTimeZone('America/Lima'));
        }
        return ($date)? date_format($date, 'd-m-Y') : 'date error';

        case 'dl':
        //_log("loc a the moment: " . setlocale(LC_TIME, 'spanish'));
        if ( $output==='NOW' ) {
            $date = new DateTime(null, new DateTimeZone('America/Lima'));
        } else {
            $date = date_create_from_format('Y-m-d H:i:s',$output, new DateTimeZone('America/Lima'));
        }
        return ($date)? strftime('%d-%b-%Y', date_timestamp_get($date)) : 'date error';

//        case 'a':
//        case 'i':
//        case 'f':
        default:
        return $output;
    }
} // end of: dis()


// *****************************************************************************************    
// Groen Productions - Search (json style) array for specific key <=> value pair 
//                     first pair encountered will be returned, so pair has to be unique 
//
// $json		    = the array created by json_encode()
// $key			    = the unique key (string) that is searched on 
// $value		    = the value that should belong to that key
//
// returns array containing the found pair
//
// *****************************************************************************************    
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