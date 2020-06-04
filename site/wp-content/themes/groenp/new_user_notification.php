<?php 
    // Get neccessary content from wp_user
    $user_login = stripslashes($user->user_login);

    // Print correct version
    if ( $html_version )
    { ?>
        <!-- BEGIN BODY // -->
        <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateBody" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #FFFFFF;border-collapse: collapse !important;">
            <tr>
                <td valign="top" class="bodyContent" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;color: #000000;font-family: Verdana, Geneva, sans-serif;font-size: 14px;line-height: 125%;padding-top: 10px;padding-right: 20px;padding-bottom: 0;padding-left: 20px;text-align: left;">
                    <p style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;">
                    <?php if( !empty($user->first_name) && !empty($user->last_name) ) echo "Dear ". $user->first_name . " " . $user->last_name . ",<br /><br />" ?>
                        Welcome to your site management tool.
                        With this tool you can change the dynamic content of your site.</p>
                    <p style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;">Here are the registration details:</p>
                    <span style="display: inline-block;width: 12em;">Username:</span><strong><?php echo $user_login ?></strong><br>
                    <span style="display: inline-block;width: 12em;">Password:</span><strong><?php echo $plaintext_pwd ?></strong><br><br>
                    Please keep your password secret and keep it safe!<br><br>
                    You can login into <a href="<?php echo site_url('/wp-login.php') ?>" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #1388BF;font-weight: normal;text-decoration: underline;">your site management tool</a> with your username and password. 
                    For the best security you should change your password after logging in.<br><br>
                    We hope that you enjoy using the Sites Management tool. If you have any questions or suggestions please do not hesitate to contact us at: 
                    <a href="mailto://admin@groenproductions.com" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #1388BF;font-weight: normal;text-decoration: underline;">admin@groenproductions.com</a>
                    <br><br>
                    Greetings,<br>
                    The staff at Groen Productions<br>
                    https://admin.groenproductions.com<br>
                    <br>
                </td>
            </tr>
        </table>
        <!-- // END BODY -->
<?php
    } else {
    if( !empty($user->first_name) && !empty($user->last_name) ) echo "Dear ". $user->first_name . " " . $user->last_name . ",\n\n";

    echo "Welcome to your site management tool. 
With this tool you can change the dynamic content of your site.

Here are the registration details:
Username:  ". $user_login . " 
Password:  ". $plaintext_pwd  . "

Please keep your password secret and keep it safe!

You can login into your site management tool with your username and password: " . site_url('/wp-login.php') ."
For the best security you should change your password after logging in.

We hope that you enjoy using the Sites Management Tool. If you have any questions or suggestions please do not hesitate to contact us at admin@groenproductions.com.

Greetings,
The staff at Groen Productions
https://admin.groenproductions.com
Groen Productions' Privacy Statement: ". site_url('/privacy_and_terms_of_use.php') . "

";
    }
?>

