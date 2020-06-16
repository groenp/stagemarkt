<?php 
    $locale = ($user)? get_user_locale( $user->ID ) : get_user_locale();

    // This file contains all language versions of the text, for readablity reasons. Use the main language code as a switch
    $lng = strtolower( substr($locale, 0, 2) );
?>
                                <!-- // END BODY -->
                            </td>
                        </tr>
                        <tr>
                            <td align="center" valign="top" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
                                <!-- BEGIN FOOTER // -->
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateFooter"
                                    style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #F9F9F9;border-top-width: 1px;border-top-color: #CCCCCC;border-top-style: dashed;border-collapse: collapse !important;">
                                    <tr>
                                        <td valign="top" class="footerContent" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;color: #777777;font-family: Helvetica;font-size: 10px;line-height: 125%;padding-top: 0;padding-right: 20px;padding-bottom: 0;padding-left: 20px;text-align: left;">

<?php if ($lng == "es"): ?>
                                            <br>Este correo fue enviado por que usted esta registrado en  
                                            <a href="<?php echo trailingslashit(admin_url()) ?>" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;white-space: nowrap;">Site Management Tool</a>.<br>
                                            Groen Productions tiene una <a href="<?php echo site_url('/privacy_and_terms_of_use.php?wp_lang='. $locale) ?>" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;white-space: nowrap;">Declaración de privacidad</a>.<br>
                                            <br>
                                            <em>Groen Productions &copy; <?php echo date("Y"); ?></em> | <em>designing systems to fit the people</em><br>
                                            <br>

<?php elseif ($lng == "nl"): ?>
                                            <br>Deze mail is naar u verzonden omdat u geregistreerd bent voor de  
                                            <a href="<?php echo trailingslashit(admin_url()) ?>" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;white-space: nowrap;">Site Management Tool</a>.<br>
                                            Groen Productions heeft een <a href="<?php echo site_url('/privacy_and_terms_of_use.php?wp_lang='. $locale) ?>" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;white-space: nowrap;">Privacy verklaring</a>.<br>
                                            <br>
                                            <em>Groen Productions &copy; <?php echo date("Y"); ?></em> | <em>ontwerpen voor het gebruik</em><br>
                                            <br>

<?php else: ?>
                                            <br>This mail has been sent to you because you have registered for the 
                                            <a href="<?php echo trailingslashit(admin_url()) ?>" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;white-space: nowrap;">Site Management Tool</a>.<br>
                                            Groen Productions has a <a href="<?php echo site_url('/privacy_and_terms_of_use.php?wp_lang='. $locale) ?>" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;white-space: nowrap;">Privacy Statement</a>.<br>
                                            <br>
                                            <em>Groen Productions &copy; <?php echo date("Y"); ?></em> | <em>designing systems to fit the people</em><br>
                                            <br>
<?php endif; ?>
                                        </td>
                                        <td valign="top" width="33%" class="footerContent footerRightContent" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;color: #777777;font-family: Helvetica;font-size: 10px;line-height: 125%;padding-top: 0;padding-right: 20px;padding-bottom: 0;padding-left: 0;text-align: left;">
<?php if ($lng == "es"): ?>
                                            <br>Nuestra información de contacto:<br>
<?php elseif ($lng == "nl"): ?>
                                            <br>Onze contactgegevens:<br>
<?php else: ?>
                                            <br>Our contact information:<br>
<?php endif; ?>
                                            <a href="mailto://admin@groenproductions.com" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;white-space: nowrap;">admin@groenproductions.com</a><br>
                                            Amsterdam<br>
                                            Holland<br>
                                        </td>
                                    </tr>
                                </table>
                                <!-- // END FOOTER -->
                            </td>
                        </tr>
                    </table>
                    <!-- // END TEMPLATE -->
                </td>
            </tr>
        </table>
    </center>
</body>

</html>
