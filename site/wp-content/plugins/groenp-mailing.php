<?php
/*
Plugin Name:        Groen Productions | Site Management Tool -  Mailing functionality
Description:        Any administration panel functionality that cannot be included in the theme: Multi-part MIME mailing functionality. Standard WP registration mail is rewritten for GroenProductions.com.  
                    Based on the CuscoNow! mailing plugin.
Version:            0.3
Author:             Groen Productions
Author URI:         http://www.linkedin.com/in/pietergroen/
*/
//echo 'Current PHP version: ' . phpversion();

// ****************************************************************
// Registration email modification
// ****************************************************************
if (!function_exists('wp_new_user_notification')) {
    function wp_new_user_notification($user_id, $plaintext_pass) {
        groenp_plain_mail($user_id, "new_user_notification", "Welcome to Groen Productions", "Registration details", NULL, $plaintext_pass);
    }
} else {
    if( WP_DEBUG === true )
    error_log( 'wp_new_user_notification already exists...' );
}

// ****************************************************************
// Send Multipart MIME email 
// ****************************************************************
function groenp_multipart_mail($wp_user_id, $mail_content, $email_subject, $email_heading, $email_subheading = NULL, $plaintext_pwd = NULL, $email_body = NULL) 
{

    // Extract wp_user information
    $user = new WP_User($wp_user_id);


    // Set mail type to multipart/mixed and include boundary
    global $boundary;
    $boundary = uniqid('groenp');
    add_filter( 'wp_mail_content_type', function($content_type){
        global $boundary;
        return 'multipart/mixed; boundary="' . $boundary .'"';
//        return 'text/html';
    });

    // Write out the mail body parts and collect them with ob
    ob_start(); 
    echo "--" . $boundary . "\nContent-type: text/html; charset=utf-8\n";

    //Print html section
    $html_version = TRUE;
    include( get_theme_root() . "/groenp/mail/html-mail-header.php" );
    include( get_theme_root() . "/groenp/mail/" . $mail_content . ".php" );
    include( get_theme_root() . "/groenp/mail/html-mail-footer.php" );
    echo "--" . $boundary . "\r\nContent-type: text/plain; charset=utf-8\r\n\r\n"; 

    //Print  plain section
    $html_version = FALSE;
    include( get_theme_root() . "/groenp/mail/" . $mail_content . ".php" );
    echo "--" . $boundary . "--";

    $message = ob_get_contents();
    ob_end_clean();

    // Send mail
    _log("message groenp_multipart_mail: "); _log($message);                                                 // DEBUG //
    // $result = wp_mail(stripslashes($user->user_email), $email_subject, $message);
    $result = false;
    if ( $result ) {
        _lua("SMTP", "Multipart mail (". $email_subject .") succesfully sent to " . $user->user_email . ".");
    } else {
        _lua("SMTP", "ERROR sending Multipart mail (". $email_subject .") to " . $user->user_email . ".");
        _log("ERROR sending Multipart mail to " . $user->user_email . ".");
    }

    // Set mail type back to plain
    add_filter( 'wp_mail_content_type', function($content_type){
        return 'text/plain';
    });
} // end: groenp_multipart_mail()


// ****************************************************************
// Send HTML email 
// ****************************************************************
function groenp_html_mail($wp_user_id, $mail_content, $email_subject, $email_heading, $email_subheading = NULL, $plaintext_pwd = NULL, $email_body = NULL) {

    // Extract wp_user information
    $user = new WP_User($wp_user_id);

    // Set mail type to text/html
    add_filter( 'wp_mail_content_type', function($content_type){
        return 'text/html';
    });

    // Write out the mail body parts and collect them with ob
    ob_start(); 

    //Print html section
    $html_version = TRUE;
    include( get_theme_root() . "/groenp/mail/html-mail-header.php" );
    include( get_theme_root() . "/groenp/mail/" . $mail_content . ".php" );
    include( get_theme_root() . "/groenp/mail/html-mail-footer.php" );

    $message = ob_get_contents();
    ob_end_clean();

    // Send mail
    _log("message groenp_html_mail: "); _log($message);                                                 // DEBUG //
    // $result = wp_mail(stripslashes($user->user_email), $email_subject, $message);
    if ( $result ) {
        _lua("SMTP", "HTML mail (". $email_subject .") succesfully sent to " . $user->user_email . ".");
    } else {
        _lua("SMTP", "ERROR sending HTML mail (". $email_subject .") to " . $user->user_email . ".");
        _log("ERROR sending HTML mail to " . $user->user_email . ".");
    }

    // Set mail type back to plain
    add_filter( 'wp_mail_content_type', function($content_type){
        return 'text/plain';
    });

} // end: groenp_html_mail()


// ****************************************************************
// Send plain email 
// ****************************************************************
function groenp_plain_mail($wp_user_id, $mail_content, $email_subject, $email_heading, $email_subheading = NULL, $plaintext_pwd = NULL, $email_body = NULL) {

    // Extract wp_user information
    $user = new WP_User($wp_user_id);

    // Write out the mail body parts and collect them with ob
    ob_start(); 

    //Print  plain section
    $html_version = FALSE;
    include( get_theme_root() . "/groenp/mail/" . $mail_content . ".php" );

    $message = ob_get_contents();
    ob_end_clean();

    // Send mail
    _log("message groenp_plain_mail: "); _log($message);                                                 // DEBUG //
    // $result = wp_mail(stripslashes($user->user_email), $email_subject, $message);
    if ( $result ) {
        _lua("SMTP", "Plain mail (". $email_subject .") succesfully sent to " . $user->user_email . ".");
    } else {
        _lua("SMTP", "ERROR sending plain mail (". $email_subject .") to " . $user->user_email . ".");
        _log("ERROR sending plain mail to " . $user->user_email . ".");
    }

} // end: groenp_plain_mail()

?>