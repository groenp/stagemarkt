<?php 
    // Get neccessary content from wp_user
    $user_login = stripslashes($user->user_login);
    $locale = ($user)? get_user_locale( $user->ID ) : get_user_locale();

    // This file contains all language versions of the text, for readablity reasons. Use the main language code as a switch
    $lng = strtolower( substr($locale, 0, 2) );

    // Print correct version
    if ( $html_version ):
?>
        <!-- BEGIN BODY // -->
        <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateBody" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #FFFFFF;border-collapse: collapse !important;">
            <tr>
                <td valign="top" class="bodyContent" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;color: #000000;font-family: Verdana, Geneva, sans-serif;font-size: 14px;line-height: 125%;padding-top: 10px;padding-right: 20px;padding-bottom: 0;padding-left: 20px;text-align: left;">
                    <p style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;">

<?php if ($lng == "es"): 
            if( !empty($user->first_name) && !empty($user->last_name) ) echo "Estimado ". $user->first_name . " " . $user->last_name . ",<br /><br />" ?>
                        Bienvenido a su herramienta de Site Management.
                        Con esta herramienta puede cambiar el contenido dinámico de su sitio.
                    <p style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;">Aquí están los detalles de su registro:</p>
                    <span style="display: inline-block;width: 12em;">Nombre de Usuario:</span><strong><?php echo $user_login ?></strong><br>
                    <span style="display: inline-block;width: 12em;">Contraseña:</span><strong><?php echo $plaintext_pwd ?></strong><br><br>
                    Por favor asegúrese de mantener su clave en secreto y mantenerla segura!<br><br>
                    Puede iniciar sesión en <a href="<?php echo site_url('/wp-login.php?wp_lang='. $locale) ?>" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #1388BF;font-weight: normal;text-decoration: underline;">la herramienta de administración</a> con este nombre de usuario y contraseña. 
                    Para la mejor seguridad, debe cambiar su contraseña después de iniciar su sesión por la primera vez.<br><br>
                    Esperamos que disfrute usando la herramienta de Site Management. Si tiene algún pregunta o sugerencias, por favor no dude en contactar con nosotros a: 
                    <a href="mailto://admin@groenproductions.com" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #1388BF;font-weight: normal;text-decoration: underline;">admin@groenproductions.com</a>
                    <br><br>
                    Atentamente,<br>
                    El equipo de Groen Productions<br>
                    <br>

<?php elseif ($lng == "nl"): 
            if( !empty($user->first_name) && !empty($user->last_name) ) echo "Dear ". $user->first_name . " " . $user->last_name . ",<br /><br />" ?>
                        Welkom bij uw site beheer tool. 
                        Met dit tool kunt u dynamische content van uw site beheren.
                    <p style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;">Hier zijn de registratiegegevens:</p>
                    <span style="display: inline-block;width: 12em;">Gebruikersnaam:</span><strong><?php echo $user_login ?></strong><br>
                    <span style="display: inline-block;width: 12em;">Wachtwoord:</span><strong><?php echo $plaintext_pwd ?></strong><br><br>
                    Bescherm uw wachtwoord en houd het op een veilige plek.<br><br>
                    U kunt op <a href="<?php echo site_url('/wp-login.php?wp_lang='. $locale) ?>" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #1388BF;font-weight: normal;text-decoration: underline;">uw site beheer tool</a> inloggen met deze gebruikersnaam en wachtwoord. 
                    Het veiligst is als u meteen uw wachtwoord wijzigt na de eerste keer inloggen.<br><br>
                    Wij hopen dat het gebruik van de Site Management Tool bevalt. Als u nog vragen of suggesties heeft, aarzel dan niet om contact op te nemen via: 
                    <a href="mailto://admin@groenproductions.com" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #1388BF;font-weight: normal;text-decoration: underline;">admin@groenproductions.com</a>
                    <br><br>
                    Met vriendelijke groeten,<br>
                    De medewerkers van Groen Productions<br>
                    <br>

<?php else:
            if( !empty($user->first_name) && !empty($user->last_name) ) echo "Dear ". $user->first_name . " " . $user->last_name . ",<br /><br />" ?>
                        Welcome to your site management tool.
                        With this tool you can change the dynamic content of your site.
                    <p style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;">Here are the registration details:</p>
                    <span style="display: inline-block;width: 12em;">Username:</span><strong><?php echo $user_login ?></strong><br>
                    <span style="display: inline-block;width: 12em;">Password:</span><strong><?php echo $plaintext_pwd ?></strong><br><br>
                    Please keep your password secret and keep it safe!<br><br>
                    You can login into <a href="<?php echo site_url('/wp-login.php?wp_lang='. $locale) ?>" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #1388BF;font-weight: normal;text-decoration: underline;">your site management tool</a> with your username and password. 
                    For the best security you should change your password after the first login.<br><br>
                    We hope that you enjoy using the Site Management tool. If you have any questions or suggestions please do not hesitate to contact us at: 
                    <a href="mailto://admin@groenproductions.com" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #1388BF;font-weight: normal;text-decoration: underline;">admin@groenproductions.com</a>
                    <br><br>
                    Greetings,<br>
                    The staff at Groen Productions<br>
                    <br>
<?php endif; ?>

                </td>
            </tr>
        </table>
        <!-- // END BODY -->
<?php else: 

    if ( $lng == 'es' ):
        if ( !empty($user->first_name) && !empty($user->last_name) ): 
            echo "Estimado ". $user->first_name . " " . $user->last_name . ",\n\n";
        endif;
        echo "Bienvenido a su herramienta de Site Management.
Con esta herramienta puede cambiar el contenido dinámico de su sitio.

Aquí están los detalles de su registro:
Nombre de Usuario: ". $user_login . " 
Contraseña:        ". $plaintext_pwd  . "

Por favor asegúrese de mantener su clave en secreto y mantenerla segura!

Puede iniciar sesión en la herramienta de administración  con este nombre de usuario y contraseña: " . site_url('/wp-login.php?wp_lang='. $locale) ."
Para la mejor seguridad, debe cambiar su contraseña después de iniciar su sesión por la primera vez.

Esperamos que disfrute usando la herramienta de Site Management. Si tiene algún pregunta o sugerencias, por favor no dude en contactar con nosotros a admin@groenproductions.com.

Atentamente,
El equipo de Groen Productions


Declaración de privacidad: ". site_url('/privacy_and_terms_of_use.php?wp_lang='. $locale) . "

";

    elseif ( $lng == 'nl' ):
        if ( !empty($user->first_name) && !empty($user->last_name) ): 
            echo "Beste ". $user->first_name . " " . $user->last_name . ",\n\n";
        endif; 
        echo "Welkom bij uw site beheer tool. 
Met dit tool kunt u dynamische content van uw site beheren.

Hier zijn de registratiegegevens:
Gebruikersnaam:  ". $user_login . " 
Wachtwoord:      ". $plaintext_pwd  . "

Bescherm uw wachtwoord en houd het op een veilige plek.

U kunt op uw site beheer tool inloggen met deze gebruikersnaam en wachtwoord: " . site_url('/wp-login.php?wp_lang='. $locale) ."
Het veiligst is als u meteen uw wachtwoord wijzigt na de eerste keer inloggen.

Wij hopen dat het gebruik van de Site Management Tool bevalt. Als u nog vragen of suggesties heeft, aarzel dan niet om contact op te nemen via admin@groenproductions.com.

Met vriendelijke groeten,
De medewerkers van Groen Productions


Privacy verklaring: ". site_url('/privacy_and_terms_of_use.php?wp_lang='. $locale) . "

";
            
    else:
        if ( !empty($user->first_name) && !empty($user->last_name) ): 
            echo "Dear ". $user->first_name . " " . $user->last_name . ",\n\n";
        endif;    
        echo "Welcome to your site management tool. 
With this tool you can change the dynamic content of your site.

Here are the registration details:
Username:  ". $user_login . " 
Password:  ". $plaintext_pwd  . "

Please keep your password secret and keep it safe!

You can login into your site management tool with this username and password: " . site_url('/wp-login.php?wp_lang='. $locale) ."
For the best security you should change your password after logging in for the first time.

We hope that you enjoy using the Site Management Tool. If you have any questions or suggestions, please do not hesitate to contact us at admin@groenproductions.com.

Greetings,
The staff at Groen Productions


Privacy Statement: ". site_url('/privacy_and_terms_of_use.php?wp_lang='. $locale) . "

";
    endif; 
endif; ?>