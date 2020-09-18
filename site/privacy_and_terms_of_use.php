<?php 
    // GLOBAL VARS 
    $priv_date = 'June 10, 2020'; 
    $tou_date = 'September 16, 2020';

    // $date = new DateTime(null, new DateTimeZone('Europe/Amsterdam'));                      // DEBUG // 
    // _log('\nThis session started on: ' . $date->format('d M Y, H:i:s e'));                 // DEBUG // 

    // switch on minify for remote servers
    if ($_SERVER['SERVER_PORT'] == '443' || $_SERVER['SERVER_PORT'] == '80') // default SSL port number OR http: port number
    {
        $min_url = '.min';
    } else {
        $min_url = '';
    }
    
    // Get locale from the url cookie (url query), and if there is none, then get it from <html> element (with double quotes stripped)
    $locale = isset( $_GET['wp_lang'] )? $_GET['wp_lang'] : str_replace('"', '', substr(get_language_attributes(), 6));

    // This file contains all language versions of the text, for readablity reasons. Use the main language code as a switch
    $lng = strtolower( substr($locale, 0, 2) );
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- font awesome inserted for icons on buttons etc. -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet" />
    <!-- <script src="https://kit.fontawesome.com/a02f8b3e52.js" crossorigin="anonymous"></script> -->

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />

    <!-- Groen Productions branding -->
    <link rel="stylesheet" href="../assets/groenp-sites<?php echo $min_url; ?>.css" />
    <link rel="shortcut icon" href="../favicon.ico" />
    <!-- Groen Productions globals -->
    <script type="text/javascript">
        var PrivDate = "<?php echo $priv_date; ?>";
    </script>

<?php if ($lng == "es"): 
    setlocale(LC_TIME, "es_PE"); ?>
    <title>Groen Productions - Declaración de Privacidad y los Términos de Uso</title>
<?php elseif ($lng == "nl"): 
    setlocale(LC_TIME, "nl_NL"); ?>
    <title>Groen Productions - Privacy Verklaring en Gebruiksvoorwaarden</title>
<?php else: 
    setlocale(LC_TIME, "en_CA"); ?>
    <title>Groen Productions - Privacy Statement and Terms of Use</title>
<?php endif; ?>
</head>

<body class="login" data-offset="50">
    <!-- <body class="modal-open"> -->

    <div class="container-flex">

        <!-- content section -->
        <div class="container-md content">

            <div class="row py-2">
                <div class="intro col-12">
                    <a class="" href="./wp-admin/">
                        <h1><img src="../assets/GroenProductionsPath.svg" alt="Groen Productions | Site Management Tool" /><br /></h1>
                    </a>

<?php if ($lng == "es"): ?>
                    <p><a type="button" class="btn float-right" href="./wp-admin/?wp_lang=<?php echo $locale; ?>">Al sitio</a></p>

                    <h2>Declaraciones Legales</h2>
                    <h3>Definiciones</h3>
                    <p>El Site Management Tool | Groen Productions se llama el “sitio” con el propósito de esta declaración. A lo largo de la declaración “nosotros”, 
                    “nos” y “nuestro” se refieren a Groen Productions, su propietario y asociados.</p>
                    <p>Groen Productions ofrece este sitio, incluyendo toda la información, herramientas y servicios disponibles en este sitio para usted, 
                    el usuario, dependiendo de su aceptación de todos los términos, condiciones, políticas y avisos aquí establecidos.</p>
                    <p>Este texto se proporciona en español para su conveniencia. El texto en inglés tiene validez legal si hay diferencias de opinión sobre el significado 
                    del texto en español frente al texto en inglés. Puede leer <a class="txt" href='privacy_and_terms_of_use.php?wp_lang=en_US'>el texto en inglés aquí</a>.
                    <p>Esta página contiene la <a class="txt" href="#priv">Declaración de Privacidad</a> y los <a class="txt" href="#term">Términos de Uso</a>.</p>

<?php elseif ($lng == "nl"): ?>
                    <p><a type="button" class="btn float-right" href="./wp-admin/?wp_lang=<?php echo $locale; ?>">Naar de site</a></p>

                    <h2>Juridische verklaringen</h2>
                    <h3>Definities</h3>
                    <p>In deze verklaringen wordt met “site” bedoeld het Groen Productions | Site Management Tool. In deze hele verklaring wordt met “wij”, 
                    “ons” en “onze” gerefereerd aan Groen Productions, haar eigenaren en medewerkers.</p>
                    <p>Groen Productions biedt deze site, inclusief alle informatie, tools en diensten die via deze site voor u beschikbaar zijn, aan u,
                    de gebruiker, onder voorbehoud van uw aanvaarding van alle hier vermelde voorwaarden, het beleid en kennisgevingen.</p>
                    <p>Deze tekst wordt u in het Nederlands aangeboden voor uw gemak. Als er verschillen van meningen zijn over de betekenis van de Nederlandse tekst 
                    versus de Engelse tekst dan heeft de Engelse tekst rechtsgeldigheid. U kunt hier <a class="txt" href='privacy_and_terms_of_use.php?wp_lang=en_US'>de Engelse tekst</a> lezen.</p>
                    <p>Op deze pagina vindt u de <a class="txt" href="#priv">Privacy verklaring</a> en de <a class="txt" href="#term">Gebruiksvoorwaarden</a>.</p>

<?php else: ?>
                    <p><a type="button" class="btn float-right" href="./wp-admin/">To site</a></p>

                    <h2>Legal Statements</h2>
                    <h3>Definitions</h3>
                    <p>The Groen Productions | Site Management Tool is called the “site” for the purpose of these statements. Throughout the statement “we”,
                    “us” and “our” refer to Groen Productions, its owner and associates.</p>
                    <p>Groen Productions offers this site, including all information, tools and services available from this site to you,
                    the user, conditioned upon your acceptance of all terms, conditions, policies and notices stated here.</p>
                    <p>This page contains the <a class="txt" href="#priv">Privacy Statement</a> and the <a class="txt" href="#term">Terms of Use</a>.</p>
<?php endif; ?>
                </div>
            </div> <!-- end of row -->

            <a class="anchr" id="priv"></a>
            <div class="row py-2">
                <div class="col-12">
                    <h2>Privacy Statement</h2>
                    <p>This Statement was last modified on <strong><?php echo $priv_date; ?></strong>.</p>
                    <h3>What information do we collect?</h3>
                    <p>The site stores your cookie preference. The site collects information from you when you fill out a contact form.</p>
                    <h3>What do we use this information for?</h3>
                    <p>The contact information may be used to contact you and/or to verify your identity.</p>
                    <h3>Internet providers</h3>
                    <p>Certain data are collected automatically from your web browser and our web server: IP
                    addresses, browser types (Chrome, Internet Explorer, etc.), browser versions and various pages that users are visiting.
                    This standard information is sent over the internet and can be read by third parties. We will not use this data to track
                    your personal information.</p>
                    <h3>How we use Cookies</h3>
                    <p>The site uses cookies to keep track of your session and . </p>
                    <!-- <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="cookieSwitch">
                        <label class="custom-control-label" for="cookieSwitch">Cookies</label>
                    </div> -->
                    <p>This cookie information may be stored in the browser's local storage and not sent over the internet.</p>
                    <h3>If you contact us</h3>
                    <p>If you contact us, we may use your email address to contact you with a reply. We may also act on any information that
                    you supply, as we see fit. Do not share any information in your electronic communication that you do not want us to use.</p>
                    <h3>Your consent</h3>
                    <p>By using this site, you consent to our privacy policy.</p>
                    <h3>Changes to our Privacy Policy</h3>
                    <p>If we decide to change our privacy policy, we will post those changes on this page.</p>
                    <h3>Contacting us</h3>
                    <p>If there are any questions regarding this privacy policy, you may contact us using the information below:</p>
                    <p>Privacy@GroenProductions.com<br/>
                    Groen Productions<br />
                    Amsterdam<br />
                    Netherlands</p>
                </div>

            </div> <!-- end of row -->
            
            <a class="anchr" id="term"></a>
            <div class="row py-2">

<?php if ($lng == "es"): ?>
                <div class="col-12">
                    <h2>Términos de Uso</h2>
                    <p>Estos términos se modificaron por última vez el <strong><?php echo strftime("%e de %B de %Y",strtotime($tou_date)); ?></strong>.</p>

                    <p>Al utilizar nuestra app usted, el usuario, participa en nuestro “Servicio” y acuerda estar obligado por los siguientes términos y condiciones 
                    (“Términos de Uso”, “Condiciones”). Por favor, lea estas Condiciones de Uso antes de acceder o utilizar nuestro servicio. 
                    Al acceder o utilizar cualquier parte del Servicio, usted acepta que quedará vinculado por estas Condiciones de Uso. 
                    Si usted no está de acuerdo con todos los términos y condiciones de este acuerdo, entonces usted no puede acceder a la sitio o utilizar 
                    cualquiera de los servicios.</p>

                    <h3>General</h3>
                    <p>Nos reservamos el derecho de rechazar prestar el servicio a cualquier persona por cualquier motivo y en cualquier momento.</p>

                    <p>Usted acepta no reproducir, duplicar, copiar, vender, revender o explotar cualquier parte del Servicio, uso del Servicio o acceso al Servicio o cualquier contacto en el sitio web a través del cual se presta el servicio, sin autorización expresa por escrito por nosotros.</p>

                    <h3>Exactitud, Integridad y Oportunidad De La Información</h3>
                    <p>No nos hacemos responsables si la información disponible en este servicio no es exacta, completa o actual. El material se ofrece en este servicio para obtener información general y no debe confiarse en ella o se utiliza como la única base para tomar decisiones sin consultar a las fuentes primarias, más precisas, más completas o más oportunas. Cualquier confianza en el material de este servicio es bajo su propio riesgo.</p>

                    <p>Este servicio puede contener cierta información histórica. La información histórica, necesariamente, no es actual y se proporciona únicamente para su referencia. Nos reservamos el derecho de modificar el contenido de este servicio en cualquier momento, pero no tenemos ninguna obligación de actualizar ninguna información sobre nuestro servicio. Usted acepta que es su responsabilidad de monitorear los cambios en nuestro servicio.</p>

                    <h3>Modificaciones del Servicio</h3>
                    <p>Nos reservamos el derecho en cualquier momento de modificar o interrumpir el Servicio (o cualquier parte o contenido de éste) sin previo aviso en cualquier momento.</p>

                    <p>Nosotros no seremos responsables ante usted o cualquier tercero por cualquier modificación, suspensión o interrupción del Servicio.</p>

                    <h3>Enlaces de Terceros</h3>
                    <p>Ciertos contenidos, productos y servicios disponibles a través de nuestro Servicio pueden incluir materiales de terceros.</p>

                    <p>Enlaces de terceros en esta app se pueden dirigir a páginas web de terceros que no están afiliadas a nosotros. No nos hacemos responsables de examinar o evaluar el contenido o exactitud y no garantiza y no tendremos ningún tipo de responsabilidad por cualquier material de terceros o sitios web, o por cualquier otro material, productos o servicios de terceros.</p>

                    <p>No nos hacemos responsables de cualquier daño o daños relacionados con la adquisición o utilización de bienes, servicios, recursos, contenidos o cualesquiera otras transacciones realizadas en relación con los sitios web de terceros. Por favor revise cuidadosamente las políticas y prácticas del de terceros y asegúrese de entender su contenido antes de participar en cualquier transacción. Las quejas, reclamos, inquietudes o preguntas con respecto a productos de terceros deben ser dirigidos a la tercera parte.</p>

                    <h3>Comentarios del Usuario, Comentarios y Otras Presentaciones</h3>
                    <p>Si, a petición nuestra, usted envía ciertas propuestas específicas (por ejemplo, contenido para un sitio web) o sin una solicitud de nuestra parte que envíe ideas creativas, sugerencias, propuestas, planes u otros materiales, ya sea en línea, por correo electrónico, por correo postal, o de otra manera (colectivamente, “Comentarios”) , usted acepta que es posible que, en cualquier momento, sin restricciones, editar, copiar, publicar, distribuir, traducir y utilizar de otro modo en cualquier medio de los comentarios que esperamos nosotros. Nosotros estamos y no estaremos ninguna obligación (1) de mantener cualquier comentario en secreto; (2) a pagar una indemnización por cualquier comentario; o (3) para responder a cualquier comentario.</p>

                    <p>Nosotros podemos, pero no tenemos ninguna obligación de, monitorear, editar o eliminar contenido que, según nuestro criterio sea, delictivo, ofensivo, amenazante, calumnioso, difamatorio, pornográfico, obsceno o de dudosa o que viole la propiedad intelectual de cualquiera de las partes o estas Términos de Uso.</p>

                    <p>El usuario acepta que sus comentarios no violará ningún derecho de terceros, incluyendo los derechos de autor, marca registrada, la privacidad, la personalidad u otro derecho personal o de propiedad. Asimismo, acepta que sus comentarios no contienen material difamatorio o ilegal, abusivo u obsceno, o contienen virus informáticos u otro software malicioso que podría de alguna manera afectar el funcionamiento del Servicio o de cualquier sitio web relacionado. Usted no puede utilizar una dirección de correo electrónico falsa, pretender ser alguien que no sea usted mismo, engañarnos o a terceros en cuanto al origen de los comentarios. Usted es el único responsable de cualquier comentario que haga y su precisión. No nos hacemos responsables y no asumimos ninguna responsabilidad por cualquier comentarios publicados por usted o cualquier tercero.</p>

                    <h3>Errores, Inexactitudes y Omisiones</h3>
                    <p>De vez en cuando puede haber información en el Servicio que contiene errores tipográficos, inexactitudes u omisiones que puedan relacionarse con las descripciones de productos, precios, promociones, ofertas, los gastos de envío del producto, el tiempo de tránsito y la disponibilidad. Nos reservamos el derecho de corregir los errores, inexactitudes u omisiones y de cambiar o actualizar la información o cancelar pedidos si alguna información en el Servicio o en cualquier sitio web relacionado es inexacta en cualquier momento sin previo aviso.</p>

                    <p>No asumimos ninguna obligación de actualizar, corregir o aclarar la información en el Servicio o en cualquier sitio web relacionado, incluyendo, sin limitación, la información de precios, excepto cuando sea requerido por la ley. No se especifica la actualización o la fecha aplicada en el Servicio o en cualquier sitio web relacionado refrescar, se debe tomar para indicar que toda la información en el Servicio o en cualquier sitio web relacionado ha sido modificada o actualizada.</p>

                    <h3>Usos Prohibidos</h3>
                    <p>Además de otras prohibiciones como se establece en las Términos del Uso, que está prohibido el uso de la app o de su contenido: (a) para cualquier propósito ilegal; (b) para solicitar a otros a realizar o participar en cualquier acto ilegal; (c) viole, reglamentos provinciales o estatales, reglas, leyes u ordenanzas internacionales, federales locales; (d) a infringir o violar nuestros derechos de propiedad intelectual o los derechos de propiedad intelectual de terceros; (e) de acosar, abusar, insultar, dañar, difamar, calumniar, desacreditar, intimidar o discriminar por razones de género, orientación sexual, religión, etnia, raza, edad, origen nacional o discapacidad; (f) presentar información falsa o engañosa; (g) para subir o transmitir virus o cualquier otro tipo de código malicioso que sea o pueda ser utilizado en cualquier forma que pueda comprometerse la funcionalidad o el funcionamiento del Servicio o de cualquier sitio web relacionado, otros sitios web o Internet; (h) para obtener o rastrear la información personal de los demás; (i) es de spam, phishing, pharm, pretexto, araña, arrastre, o raspar; (j) para cualquier propósito obsceno o inmoral; o (k) para interferir con o eludir los dispositivos de seguridad del Servicio o de cualquier sitio web relacionado, otros sitios web, o en Internet.</p>

                    <h3>Exclusión de Garantías; Limitación De Responsabilidad</h3>
                    <p>No garantizamos, aseguramos ni declaramos que el uso de nuestro servicio será ininterrumpido, puntual, seguro o libre de errores.</p>

                    <p>No garantizamos que los resultados que se puedan obtener del uso del servicio serán exactos o confiables.</p>

                    <p>Usted acepta que de vez en cuando podemos quitar el servicio por períodos de tiempo indefinidos o cancelar el servicio en cualquier momento, sin previo aviso.</p>

                    <p>Usted acepta expresamente que el uso de, o la imposibilidad de usar el Servicio es bajo su propio riesgo. El servicio es (salvo lo expresamente manifestado por nosotros) proporcionado “tal cual” y “como disponibles” para su uso, sin ninguna representación, garantía o condiciones de ningún tipo, ya sea expresa o implícita, incluyendo todas las garantías o condiciones implícitas de comercialización, calidad comercializable, aptitud para un propósito en particular, la durabilidad, el título y no infracción.</p>

                    <p>En ningún caso Groen Productions, nuestros propietarios, socios, contratistas, proveedores o prestadores de servicios será responsable por cualquier daño, pérdida, reclamación o daños directos, indirectos, incidentales, punitivos, especiales o consecuentes de cualquier tipo, incluyendo , sin limitación la pérdida de beneficios, pérdida de ingresos, pérdida de ahorros, pérdida de datos, costos de reemplazo o cualquier daño similares, ya sea basado en contrato, agravio (incluyendo negligencia), responsabilidad estricta o de otra manera, que surja del uso de cualquiera de los servicios o cualquiera de los productos adquiridos a utilizar el servicio, o por cualquier otra reclamación relacionada de alguna manera con su uso del servicio o cualquier producto, incluyendo, pero no limitado a, cualquier error u omisión en cualquier contenido, o cualquier pérdida o daño de cualquier tipo incurridos como consecuencia de la utilización del servicio o cualquier contenido (o producto) publicado, transmitido o puesto a disposición a través del servicio, incluso si se avisa de su posibilidad. Debido a que algunas jurisdicciones no permiten la exclusión o la limitación de responsabilidad por daños consecuenciales o incidentales, en dichos estados o jurisdicciones, nuestra responsabilidad se limitará en la medida máxima permitida por la ley.</p>

                    <h3>Indemnización</h3>
                    <p>Usted acepta indemnizar, defender y mantener indemne Groen Productions y nuestros propietarios, socios, contratistas, proveedores o prestadores de servicios de cualquier reclamo o demanda, incluyendo honorarios razonables de abogados, hecha por cualquier tercero debido a, o que surja de su incumplimiento de estos Términos de Uso o los documentos que incorporan por referencia, o su violación de cualquier ley o los derechos de un tercero. </p>

                    <h3>Información Del Contacto</h3>
                    <p>Las preguntas sobre las condiciones de uso deben ser enviadas a nosotros en <a class="txt" href="mailto://Privacy@GroenProductions.com">Privacy@GroenProductions.com</a>.</p>
                    <p>1017 JN Amsterdam<br />Países Bajos</p>
                    <p><a type="button" class="btn float-right" href="./wp-admin/?wp_lang=<?php echo $locale; ?>">Al sitio</a></p>
                </div>
<?php elseif ($lng == "nl"): ?>
                <div class="col-12">
                    <h2>Gebruiksvoorwaarden</h2>
                    <p>Deze voorwaarden zijn het laatst aangepast op <strong><?php echo strftime("%e %B %Y",strtotime($tou_date)); ?></strong>.</p>
                    <p>Door onze site te gebruiken, neemt u, de gebruiker, deel aan onze “Dienst” en gaat u ermee akkoord gebonden te zijn aan de volgende voorwaarden en bepalingen (“Gebruiksvoorwaarden”, “Voorwaarden”). Lees deze gebruiksvoorwaarden zorgvuldig door voordat u onze service gebruikt of inlogt. Door enig deel van de service te openen of te gebruiken, gaat u ermee akkoord gebonden te zijn aan deze gebruiksvoorwaarden. Als u niet akkoord gaat met alle voorwaarden van deze overeenkomst, mag u niet inloggen in de website of de diensten gebruiken.</p>

                    <h3>Algemeen</h3>
                    <p>We behouden ons het recht voor om op elk moment Dienst aan wie dan ook te weigeren.</p>

                    <p>U stemt ermee in om geen enkel deel van de Dienst, het gebruik van de Dienst of toegang tot de Dienst of enig toegang tot de website waarmee de Dienst wordt geleverd, te reproduceren, dupliceren, kopiëren, verkopen, doorverkopen of exploiteren zonder uitdrukkelijke schriftelijke toestemming van ons.</p>

                    <h3>Nauwkeurigheid, Volledigheid en Tijdigheid van Informatie</h3>
                    <p>Wij zijn niet verantwoordelijk als de informatie die op deze Dienst beschikbaar wordt gesteld, niet juist, volledig of actueel is. Het materiaal op deze Dienst is uitsluitend bedoeld als algemene informatie en mag niet worden gebruikt als de enige basis voor het nemen van beslissingen zonder primaire, nauwkeurigere, completere of actuelere informatiebronnen te raadplegen. Elke afhankelijkheid van het materiaal op deze Dienst is voor eigen risico.</p>

                    <p>Deze Dienst kan bepaalde historische informatie bevatten. Historische informatie is noodzakelijkerwijs niet actueel en wordt alleen ter referentie verstrekt. We behouden ons het recht voor om de inhoud van deze Dienst op elk moment te wijzigen, maar we zijn niet verplicht om informatie over onze Dienst bij te werken. U gaat ermee akkoord dat het uw verantwoordelijkheid is om wijzigingen in onze Dienst te volgen.</p>

                    <h3>Wijzigingen aan de Dienst</h3>
                    <p>We behouden ons het recht voor om de Dienst (of een deel daarvan of een deel van de inhoud) op elk moment zonder voorafgaande kennisgeving te wijzigen of stop te zetten.</p>

                    <p>Wij zijn niet aansprakelijk jegens u of een derde partij voor enige wijziging, opschorting of stopzetting van de Dienst.</p>

                    <h3>Links naar Derden</h3>
                    <p>Bepaalde inhoud, producten en services die beschikbaar zijn via onze Dienst, kunnen materiaal van derden bevatten.</p>

                    <p>Links van derden op deze site kunnen u doorverwijzen naar websites van derden die niet aan ons zijn gelieerd. Wij zijn niet verantwoordelijk voor het onderzoeken of evalueren van de inhoud of nauwkeurigheid en we geven geen garantie en zijn niet aansprakelijk of verantwoordelijk voor materialen of websites van derden of voor andere materialen, producten of diensten van derden.</p>

                    <p>Wij zijn niet aansprakelijk voor enige schade of schade die verband houdt met de aankoop of het gebruik van goederen, diensten, bronnen, inhoud of andere transacties die zijn uitgevoerd in verband met websites van derden. Lees het beleid en de praktijken van de derde partij zorgvuldig door en zorg ervoor dat u ze begrijpt voordat u een transactie aangaat. Klachten, claims, zorgen of vragen met betrekking tot producten van derden moeten worden gericht aan de derde partij.</p>

                    <h3>Commentaar, feedback en andere bijdragen</h3>
                    <p>Als u op ons verzoek bepaalde specifieke bijdragen verzendt (bijvoorbeeld inhoud voor een website) of zonder een verzoek van ons, stuurt u creatieve ideeën, suggesties, voorstellen, plannen of ander materiaal, hetzij online, per e-mail, per post, of anderszins (gezamenlijk ‘bijdragen’), gaat u ermee akkoord dat we op elk moment, zonder beperking, alle bijdragen die u naar ons stuurt, kunnen bewerken, kopiëren, publiceren, verspreiden, vertalen en anderszins gebruiken in elk medium. Wij zijn en zijn niet verplicht (1) om eventuele bijdragen vertrouwelijk te houden; (2) om een vergoeding te betalen voor eventuele bijdragen; of (3) om op eventuele bijdragen te reageren.</p>

                    <p>We kunnen, maar zijn niet verplicht, inhoud te controleren, bewerken of verwijderen waarvan we naar eigen goeddunken vaststellen dat deze onwettig, beledigend, bedreigend, lasterlijk, pornografisch, obsceen of anderszins verwerpelijk is of in strijd is met het intellectuele eigendom van een derde partij of deze Gebruiksvoorwaarden.</p>

                    <p>U stemt ermee in dat uw bijdragen geen inbreuk maken op enig recht van een derde partij, inclusief copyright, handelsmerk, privacy, persoonlijkheid of ander persoonlijk of eigendomsrecht. U gaat er verder mee akkoord dat uw bijdragen geen lasterlijk of anderszins onwettig, beledigend of obsceen materiaal zullen bevatten, of een computervirus of andere malware zullen bevatten die op enigerlei wijze de werking van de Dienst of een gerelateerde website kunnen beïnvloeden. U mag geen vals e-mailadres gebruiken, zich voordoen als iemand anders dan uzelf, of ons of derden anderszins misleiden met betrekking tot de oorsprong van eventuele opmerkingen. U bent als enige verantwoordelijk voor eventuele bijdragen die u maakt en hun nauwkeurigheid. Wij nemen geen verantwoordelijkheid en aanvaarden geen aansprakelijkheid voor eventuele bijdragen die door u of een derde partij zijn geplaatst.</p>

                    <h3>Fouten, Onnauwkeurigheden en Weglatingen</h3>
                    <p>Af en toe kan er informatie in de Dienst zijn die typografische fouten, onnauwkeurigheden of weglatingen bevat die betrekking kunnen hebben op productbeschrijvingen, prijzen, promoties, aanbiedingen, productverzendkosten, verzendtijden en beschikbaarheid. We behouden ons het recht voor zonder voorafgaande kennisgeving om eventuele fouten, onnauwkeurigheden of weglatingen te corrigeren en om informatie te wijzigen of bij te werken als enige informatie van de Dienst of op een gerelateerde website op enig moment onnauwkeurig of fout is.</p>

                    <p>We zijn niet verplicht om informatie van de Dienst of op een gerelateerde website bij te werken, te wijzigen of te verduidelijken, inclusief maar niet beperkt tot prijsinformatie, behalve zoals vereist door de wet. Een gespecificeerde update- of verversingsdatum in de Dienst of op een gerelateerde website betekent geenszins dat alle informatie in de Dienst of op een gerelateerde website is gewijzigd of bijgewerkt.</p>

                    <h3>Verboden gebruik</h3>
                    <p>Naast andere verboden zoals uiteengezet in de Gebruiksvoorwaarden, is het u verboden de site of de inhoud ervan te gebruiken: (a) voor enig onwettig doel; (b) om anderen te vragen om onwettige handelingen uit te voeren of eraan deel te nemen; (c) om internationale, federale, provinciale of nationale voorschriften, regels, wetten of lokale verordeningen te schenden; (d) om inbreuk te maken op of in strijd te zijn met onze intellectuele eigendomsrechten of de intellectuele eigendomsrechten van anderen; (e) om lastig te vallen, te misbruiken, te beledigen, te schaden, te belasteren, te kleineren, te intimideren of te discrimineren op basis van geslacht, seksuele geaardheid, religie, etniciteit, ras, leeftijd, nationale afkomst of handicap; (f) om valse of misleidende informatie te verstrekken; (g) om virussen of enig ander type kwaadaardige code te uploaden of te verzenden die zal of kan worden gebruikt op een manier die de functionaliteit of werking van de Dienst of van enige gerelateerde website, andere websites of internet beïnvloedt; (h) om de persoonlijke informatie van anderen te verzamelen of te tracken; (i) om te spammen, phishing activiteiten te doen, data te verzamelen, te voorwenden als iemand anders, te crawlen of automatisch te oogsten; (j) voor enig obsceen of immoreel doel; of (k) om de beveiligingsfuncties van de Dienst of een gerelateerde website, andere websites of internet te verstoren of te omzeilen.</p>

                    <h3>Vrijwaring van garanties; Beperking van aansprakelijkheid</h3>
                    <p>We kunnen niet garanderen, verklaren of garanderen dat uw gebruik van onze Dienst ononderbroken, tijdig, veilig of foutloos zal zijn.</p>

                    <p>We garanderen niet dat de resultaten die kunnen worden verkregen door het gebruik van de Dienst nauwkeurig of betrouwbaar zijn.</p>

                    <p>U gaat ermee akkoord dat we van tijd tot tijd de Dienst voor onbepaalde tijd kunnen verwijderen of de Dienst op elk moment kunnen annuleren, zonder kennisgeving aan u.</p>

                    <p>U gaat er uitdrukkelijk mee akkoord dat uw gebruik van of het onvermogen om de Dienst te gebruiken op eigen risico is. De Dienst wordt (behalve wanneer uitdrukkelijk door ons vermeld) geleverd ‘in huidige staat’ en ‘zoals beschikbaar’ voor uw gebruik, zonder enige verklaring, garanties of voorwaarden van welke aard dan ook, expliciet of impliciet, inclusief alle impliciete garanties of voorwaarden van verkoopbaarheid, handelskwaliteit, geschiktheid voor een bepaald doel, duurzaamheid, rechten en oorbaarheid.</p>

                    <p>Groen Productions, onze eigenaren, medewerkers, aannemers, leveranciers of dienstverleners zijn in geen geval aansprakelijk voor enig letsel, verlies, claim of enige directe, indirecte, incidentele, punitieve, speciale of gevolgschade van welke aard dan ook, inclusief, zonder beperking gederfde winst, gederfde inkomsten, gemiste besparingen, verlies van gegevens, vervangingskosten of gelijkaardige schade, hetzij gebaseerd op contract, onrechtmatige daad (inclusief nalatigheid), strikte aansprakelijkheid of anderszins, die voortvloeit uit uw gebruik van een van de Diensten of enige andere producten die zijn aangeschaft met behulp van de Dienst, of voor enige andere claim die op enigerlei wijze verband houdt met uw gebruik van de Dienst of een product, inclusief, maar niet beperkt tot, fouten of weglatingen in de inhoud, of enig verlies of enige schade opgelopen als een resultaat van het gebruik van de Dienst of enige inhoud (of product) die is gepost, verzonden of anderszins beschikbaar gesteld via de Dienst, zelfs indien op de hoogte gebracht van hun mogelijkheid. Omdat sommige rechtsgebieden de uitsluiting of beperking van aansprakelijkheid voor gevolgschade of incidentele schade niet toestaan, zal onze aansprakelijkheid in dergelijke staten of rechtsgebieden beperkt zijn tot de maximale mate die wettelijk is toegestaan.</p>

                    <h3>Vrijwaring</h3>
                    <p>U stemt ermee in om Groen Productions en onze eigenaars, medewerkers, aannemers, leveranciers of dienstverleners schadeloos te stellen, te verdedigen en te vrijwaren van enige claim of eis, inclusief redelijke advocaatkosten, ingediend door een derde partij als gevolg van of voortvloeiend uit uw schending van deze gebruiksvoorwaarden of de documenten die ze door middel van verwijzing bevatten, of uw schending van enige wet of de rechten van een derde partij.</p>

                    <h3>Contactinformatie</h3>
                    <p>Vragen over de gebruiksvoorwaarden moeten gestuurd worden naar <a class="txt" href="mailto://Privacy@GroenProductions.com">Privacy@GroenProductions.com</a>.</p>
                    <p>1017 JN Amsterdam<br />Nederland</p>
                    <p><a type="button" class="btn float-right" href="./wp-admin/?wp_lang=<?php echo $locale; ?>">Naar de site</a></p>
                </div>
<?php else: ?>
                <div class="col-12">
                    <h2>Terms of Use</h2>
                    <p>These terms were last modified on <strong><?php echo $tou_date; ?></strong>.</p>
                    <p>By using our site you, the user, engage in our “Service” and agree to be bound by the following terms and conditions (“Terms of Use”, “Terms”). Please read these Terms of Use carefully before accessing or using our Service. By accessing or using any part of the Service, you agree to be bound by these Terms of Use. If you do not agree to all the terms and conditions of this agreement, then you may not access the site or use any services.</p>

                    <h3>General</h3>
                    <p>We reserve the right to refuse service to anyone for any reason at any time.</p>

                    <p>You agree not to reproduce, duplicate, copy, sell, resell or exploit any portion of the Service, use of the Service, or access to the Service or any contact on the website through which the Service is provided, without express written permission by us.</p>

                    <h3>Accuracy, Completeness and Timeliness Of Information</h3>
                    <p>We are not responsible if information made available on this Service is not accurate, complete or current. The material on this Service is provided for general information only and should not be relied upon or used as the sole basis for making decisions without consulting primary, more accurate, more complete or more timely sources of information. Any reliance on the material on this Service is at your own risk.</p>

                    <p>This Service may contain certain historical information. Historical information, necessarily, is not current and is provided for your reference only. We reserve the right to modify the contents of this Service at any time, but we have no obligation to update any information on our Service. You agree that it is your responsibility to monitor changes to our Service.</p>

                    <h3>Modifications to the Service</h3>
                    <p>We reserve the right at any time to modify or discontinue the Service (or any part or content thereof) without notice at any time.</p>

                    <p>We shall not be liable to you or to any third-party for any modification, suspension or discontinuance of the Service.</p>

                    <h3>Third-party Links</h3>
                    <p>Certain content, products and services available via our Service may include materials from third-parties.</p>

                    <p>Third-party links on this site may direct you to third-party websites that are not affiliated with us. We are not responsible for examining or evaluating the content or accuracy and we do not warrant and will not have any liability or responsibility for any third-party materials or websites, or for any other materials, products, or services of third-parties.</p>

                    <p>We are not liable for any harm or damages related to the purchase or use of goods, services, resources, content, or any other transactions made in connection with any third-party websites. Please review carefully the third-party's policies and practices and make sure you understand them before you engage in any transaction. Complaints, claims, concerns, or questions regarding third-party products should be directed to the third-party.</p>

                    <h3>User Comments, Feedback and Other Submissions</h3>
                    <p>If, at our request, you commit certain specific submissions (for example content for a website) or without a request from us you send creative ideas, suggestions, proposals, plans, or other materials, whether online, by email, by postal mail, or otherwise (collectively, 'comments'), you agree that we may, at any time, without restriction, edit, copy, publish, distribute, translate and otherwise use in any medium any comments that you forward to us. We are and shall be under no obligation (1) to maintain any comments in confidence; (2) to pay compensation for any comments; or (3) to respond to any comments.</p>

                    <p>We may, but have no obligation to, monitor, edit or remove content that we determine in our sole discretion are unlawful, offensive, threatening, libelous, defamatory, pornographic, obscene or otherwise objectionable or violates any party’s intellectual property or these Terms of Service.</p>

                    <p>You agree that your comments will not violate any right of any third-party, including copyright, trademark, privacy, personality or other personal or proprietary right. You further agree that your comments will not contain libelous or otherwise unlawful, abusive or obscene material, or contain any computer virus or other malware that could in any way affect the operation of the Service or any related website. You may not use a false e-mail address, pretend to be someone other than yourself, or otherwise mislead us or third-parties as to the origin of any comments. You are solely responsible for any comments you make and their accuracy. We take no responsibility and assume no liability for any comments posted by you or any third-party.</p>

                    <h3>Errors, Inaccuracies and Omissions</h3>
                    <p>Occasionally there may be information in the Service that contains typographical errors, inaccuracies or omissions that may relate to product descriptions, pricing, promotions, offers, product shipping charges, transit times and availability. We reserve the right to correct any errors, inaccuracies or omissions, and to change or update information if any information in the Service or on any related website is inaccurate at any time without prior notice.</p>

                    <p>We undertake no obligation to update, amend or clarify information in the Service or on any related website, including without limitation, pricing information, except as required by law. No specified update or refresh date applied in the Service or on any related website, should be taken to indicate that all information in the Service or on any related website has been modified or updated.</p>

                    <h3>Prohibited Uses</h3>
                    <p>In addition to other prohibitions as set forth in the Terms of Use, you are prohibited from using the site or its content: (a) for any unlawful purpose; (b) to solicit others to perform or participate in any unlawful acts; (c) to violate any international, federal, provincial or state regulations, rules, laws, or local ordinances; (d) to infringe upon or violate our intellectual property rights or the intellectual property rights of others; (e) to harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate based on gender, sexual orientation, religion, ethnicity, race, age, national origin, or disability; (f) to submit false or misleading information; (g) to upload or transmit viruses or any other type of malicious code that will or may be used in any way that will affect the functionality or operation of the Service or of any related website, other websites, or the Internet; (h) to collect or track the personal information of others; (i) to spam, phish, pharm, pretext, spider, crawl, or scrape; (j) for any obscene or immoral purpose; or (k) to interfere with or circumvent the security features of the Service or any related website, other websites, or the Internet.</p>

                    <h3>Disclaimer Of Warranties; Limitation Of Liability</h3>
                    <p>We do not guarantee, represent or warrant that your use of our Service will be uninterrupted, timely, secure or error-free.</p>

                    <p>We do not warrant that the results that may be obtained from the use of the Service will be accurate or reliable.</p>

                    <p>You agree that from time to time we may remove the Service for indefinite periods of time or cancel the Service at any time, without notice to you.</p>

                    <p>You expressly agree that your use of, or inability to use, the Service is at your sole risk. The Service is (except as expressly stated by us) provided ‘as is’ and ‘as available’ for your use, without any representation, warranties or conditions of any kind, either express or implied, including all implied warranties or conditions of merchantability, merchantable quality, fitness for a particular purpose, durability, title, and non-infringement.</p>

                    <p>In no case shall Groen Productions, our owners, associates, contractors, suppliers, or service providers be liable for any injury, loss, claim, or any direct, indirect, incidental, punitive, special, or consequential damages of any kind, including, without limitation lost profits, lost revenue, lost savings, loss of data, replacement costs, or any similar damages, whether based in contract, tort (including negligence), strict liability or otherwise, arising from your use of any of the service or any products procured using the service, or for any other claim related in any way to your use of the service or any product, including, but not limited to, any errors or omissions in any content, or any loss or damage of any kind incurred as a result of the use of the service or any content (or product) posted, transmitted, or otherwise made available via the service, even if advised of their possibility. Because some jurisdictions do not allow the exclusion or the limitation of liability for consequential or incidental damages, in such states or jurisdictions, our liability shall be limited to the maximum extent permitted by law.</p>

                    <h3>Indemnification</h3>
                    <p>You agree to indemnify, defend and hold harmless Groen Productions and our owners, associates, contractors, suppliers, or service providers harmless from any claim or demand, including reasonable attorneys’ fees, made by any third-party due to or arising out of your breach of these Terms of Use or the documents they incorporate by reference, or your violation of any law or the rights of a third-party.</p>

                    <h3>Contact Information</h3>
                    <p>Questions about the Terms of Use should be sent to us at <a class="txt" href="mailto://Privacy@GroenProductions.com">Privacy@GroenProductions.com</a>.</p>
                    <p>1017 JN Amsterdam<br />Netherlands</p>
                    <p><a type="button" class="btn float-right" href="./wp-admin/?wp_lang=<?php echo $locale; ?>">To site</a></p>
                </div>
<?php endif; ?>

            </div> <!-- end of row -->

            <!-- Groen Productions.com -->
            <div class="row  groenp">
                <div class="col-12">
                    <a class="" href="https://www.linkedin.com/in/pietergroen" target="_blank">Made by
                        <?php include("../assets/GroenProductions.min.svg"); ?></a>
                </div>
            </div>
            
        </div> <!-- end of content section -->
    </div> <!-- end of page container -->

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
        integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n"
        crossorigin="anonymous"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script> -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
        crossorigin="anonymous"></script>
    <!-- My script has to wait for jQuery to load -->
    <script type="text/javascript" src="../assets/groenp-sites<?php echo $min_url; ?>.js"></script>
</body>

</html>