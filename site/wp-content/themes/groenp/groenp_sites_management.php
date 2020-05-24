<?php
/******************************************************************************/
/*                                                              Pieter Groen  */
/*  Version 0.1 - May 24, 2014                                                */
/*                                                                            */
/*  PHP for Groen Productions Sites Mgmt CMS in WordPress:                    */
/*   - show dashboard after login                                             */
/*   - welcome callback                                                       */
/*                                                                            */
/******************************************************************************/


// ****************************************************************
// Groen Productions - reset the login page to be main Dashboard page 
// ****************************************************************
function groenp_login_to_dashboard( $redirect_to, $request ) {
    return admin_url('index.php');
}
add_filter('login_redirect', 'groenp_login_to_dashboard', 10, 2);


// ****************************************************************
// Groen Productions - Create Meta Boxes for Dashboard of MySQL DBs 
// ****************************************************************
function groenp_dashboard_meta_boxes_add()  
{  
    wp_add_dashboard_widget( 'welcome-mb', 'Welcome to Groen Productions | Sites Management', 'groenp_welcome_meta_box_cb');
    if ( current_user_can('list_users') ) wp_add_dashboard_widget( 'sel-domain-mb', 'Choose Domain to edit', 'groenp_sel_domain_meta_box_cb');
    // if ( !current_user_can('list_users') || !empty($_POST['est_sbscrbr_login']) )
    //     wp_add_dashboard_widget( 'cus-est-details-mb', 'X', 'groenp_est_details_meta_box_cb');
}
add_action( 'wp_dashboard_setup', 'groenp_dashboard_meta_boxes_add' );  


// ****************************************************************
// Callback for WELCOME Meta Box
// ****************************************************************
function groenp_welcome_meta_box_cb()
{  
    // Meta box introduction
    echo "<p>Welcome to your site management tool. Groen Production's Sites Management tool allows you to change the dynamic content of your site.</p>
          <p>I hope that you enjoy using the Sites Management tool. If you have any questions and/or suggestions, please do not hesitate to contact me at: <a href=\"mailto: admin@groenproductions.com\">admin@groenproductions.com</a></p>
          <p>Cheers,<br />
          Pieter at Groen Productions</p>";
} // End: groenp_welcome_meta_box_cb()

// ****************************************************************
// Callback for CHOOSE DOMAIN Meta Box (only for Administrator)
// ****************************************************************
function groenp_sel_domain_meta_box_cb()
{  

    // Meta box introduction
    echo "<p>Admin chooses domain.</p>";
    

} // End: groenp_sel_domain_meta_box_cb()


// ****************************************************************
// Callback for SEL SUBSCRIBER Meta Box
// ****************************************************************
function cusconow_sel_subscriber_meta_box_cb()
{  
    // open database
    $con = cusconow_open_database();

    // ************************************************************
    // 1. Process any form data
    // ************************************************************

    // Make this form unique
	$func = 'SelSbs';

    // Set anchor
    echo "<a name=" . $func . "></a>";  

    // Create form url for this meta box
	$form_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "#" . $func;

    // Pick up POST no matter what
    $sbscrbr_login  = prep($_POST['est_sbscrbr_login'], "a");
    //_log("sbscrbr_login: " . $sbscrbr_login);


    // DEBUG section
	// All form fields (name and id) have the same name as the column name of their counterpart in the database
	// lang specific fields have DB name and "_<lng>" appended to it
    //_log("===> START CHECKING MANAGEMENT FORM for: " . $func ); // DEBUG //
	//_log("form url: " . $form_url );                            // DEBUG // 
 	//_log("POST: "); _log($_POST); // form fields //             // DEBUG // 
 	//_log($_FILES);                // pics //                    // DEBUG // 

    // ************************************************************
	if ( isset($_POST[('show_'. $func)]) )  // THIS form has been submitted
    // ************************************************************
    {
		// Check if all mandatory fields have been entered
        $error = FALSE; // initialize

        if ( empty($_POST['est_sbscrbr_login']) )
        {
            //_log("*** input error ***");
            echo "<p class='err-msg'>No Subscriber has been selected. Make sure to use the Select button to confirm selection.</p>";
            $error = TRUE;
        }

    } // end of: checking for form submits
	
    // ************************************************************
    // 1. Create jQuery object holding all Subscribers
    // ************************************************************

    // Select correct database depending on server
    $db = ( strpos($_SERVER['SERVER_NAME'], "test.") === false )? "cusconow_cms" : "cn_test_cms";

    // Create query to get all subscribers that DON'T have admin panel access AND those that DO have admin panel access
    $query_string = "SELECT cn.pk_sbscrbr_id, cn.sbscrbr_login, cn.is_usr_blocked, cn.gets_sub_mails, cn.gets_html_mail,";
    $query_string .= " wp_fst.meta_value first, wp_lst.meta_value last, wp.user_registered, wp.user_email, cn.sbscrbr_notes"; 
    $query_string .= " FROM ". $db .".subscribers cn LEFT JOIN ".DB_NAME.".wp_users wp ON ( wp.ID = cn.fr_ID )";
    $query_string .= " LEFT JOIN ".DB_NAME.".wp_usermeta wp_fst ON ( wp_fst.user_id = cn.fr_ID AND wp_fst.meta_key = 'first_name')";
    $query_string .= " LEFT JOIN ".DB_NAME.".wp_usermeta wp_lst ON ( wp_lst.user_id = cn.fr_ID AND wp_lst.meta_key = 'last_name') ORDER BY cn.sbscrbr_login;";

    // Store in temp array
    $rows = array();
    $result = mysqli_query($con, $query_string);
    while($r = mysqli_fetch_assoc($result)) {
        $rows[] = $r;
    }
    mysqli_free_result($result);
    //print json_encode($rows);                                                                                                             // DEBUG //

    // Create javascript Object: Sbscrbrs only for CN users
    echo "<script type='text/javascript'>
        var Sbscrbrs = "; echo json_encode($rows); echo ";
    </script>";


    // ************************************************************
    //  2. Build search form for SUBSCRIBERS
    // ************************************************************

    // Meta box introduction
    //echo "<p>Use the Change button to search for a subscriber for whom the Subscriber's panel will be loaded.</p>";
    
    // Start of form
	echo "<form action='" . $form_url . "' method='post' enctype='multipart/form-data'><p>";

        // create form fields
        echo "<div id='est_details'>
        <div id='sbscrbr_display' style='display: none;'>
            <p class='hor-form'>
                <input type='hidden' id='fk_sbscrbr_id' name='fk_sbscrbr_id' value='' >
                <label for='est_sbscrbr_login'>Subscriber ID *</label><input type='button' class='button-primary' value='Change' onclick='show_sbscrbr_filter();' style='' /><input type='text' id='est_sbscrbr_login' name='est_sbscrbr_login' value='". $sbscrbr_login ."' readonly='readonly' />
            </p>
        </div>
        <div id='sbscrbr_filter'>
            <p class='hor-form'>
                <span id='err1_fltr_sbscrbr_login' class='err-msg' style='display:none;'>Please type at least 3 sequential characters (letters, digits, '_'s) of the ID</span>
                <span id='err2_fltr_sbscrbr_login' class='err-msg' style='display:none;'>There are no Subscriber IDs with this text pattern</span>
                <label for='test_est_sbscrbr_login'>Type Subscriber ID, then select Check (3 chars min.)</label>
                <input type='button' class='button-secondary' name='check_subscbr' value='Check' onclick='filter_sbscrbrs();' />
                <input type='text' id='test_est_sbscrbr_login' name='test_est_sbscrbr_login' value='". $sbscrbr_login ."' maxlength='60' pattern='[a-zA-Z0-9_]{3}[a-zA-Z0-9_]+' />
            </p>
        </div>
        <div id='fltr_sbscrbr_results' style='display: none;'>
            <p class='hor-form'>
                <label for='sbscrbr_logins'><span class='multi_res'>Select one subscriber ID from the list, then </span>Choose Select</label>
                <input type='button' class='button-primary' name='select_subscbr' value='Select' onclick='select_sbscrbr();' />
                <select class='multi_res' name='sbscrbr_logins[]' id='sbscrbr_logins' multiple='true' size='5' onchange='show_sbscrbr_det();'>
                </select>
            </p>
        </div>
        <div id='sbscrbr_details' style='display: none;'>
            <p class='hor-form'>
                <label class='wp_registered' for='est_user_registered'>CMS access registered</label><span class='wp_registered display-only' id='est_user_registered' name='est_user_registered' ></span>
                <label class='wp_registered' for='est_sbscrbr_name'>Name</label><span class='wp_registered display-only' id='est_sbscrbr_name' name='est_sbscrbr_name' ></span>
                <label class='wp_registered' for='est_user_email'>Email</label><span class='wp_registered display-only' id='est_user_email' name='est_user_email' ></span>
                <!-- <label for='est_sbscrbr_notes'>Notes</label><span></span><textarea id='est_sbscrbr_notes' name='est_sbscrbr_notes' readonly='readonly' rows='4' cols='50'></textarea> -->
                <label for='est_sbscrbr_notes'>Notes</label><span class='display-only' id='est_sbscrbr_notes' name='est_sbscrbr_notes' style='min-height:80px;'></span>
            </p>
        </div>
        ";

    // form submit buttons 
    echo "<p class='button-row'>
        <input type='submit' class='button-primary' name='show_" . $func . "' value='Show subscriber&#039;s panel' disabled='disabled'>
    </p>
    </div> <!-- est_details -->
    </form>";

    // 4. Clean up
    mysqli_close($con);
	unset($result);
	unset($rows);   // Sbsrbrs in js

    //_log( "Momentary Memory Usage: ". number_format(memory_get_usage()) ." bytes (Peak: ". number_format(memory_get_peak_usage()) ." bytes)." );

} // End: cusconow_sel_subscriber_meta_box_cb()




// ****************************************************************
// CuscoNow - echo_time_set()
//
//          - Echoes a set of time controls, representing a period.
//          - JQuery is applied on load with input validation.
//          - Optionally has radios to switch on/off period (Open/Closed).
//          - Has checkbox for break definition (needs specific 
//            break time set with id: break defined as well).
//
// Mandatory parameters:
// $id          = HTML id (and name) for the hidden controls with 
//                time values, appended with _frm and _tll.
// $inp_switch  = Boolean indicating radios can enable/disable control set.
// $lng         = 2-letter coded language for prompts, tool tips, and msgs.
//
// Optional parameters:
// $inp_open    = Boolean indicating initial setting for switch.
// $inp_frm     = initial from time (hh:mm).
// $inp_tll     = initial until time (hh:mm).
// $inp_brk     = Boolean indicating break is applied. 
// ****************************************************************
function echo_time_set($id, $inp_switch, $lng, $inp_open = NULL, $inp_frm = NULL, $inp_tll = NULL, $inp_brk = NULL)
{
    $ttl1  = ($lng == 'es')? "número entre 0 y 23 o las horas y minutos en blanco" : "number between 0 and 23 or hours and minutes blank";
    $ttl2  = ($lng == 'es')? "número entre 00 y 59 o las horas y minutos en blanco" : "number between 00 and 59 or hours and minutes blank";
    $txt1  = ($lng == 'es')? "Cerrado" : "Closed";
    $txt2  = ($lng == 'es')? "Abierto:" : "Open:";
    $txt3  = ($lng == 'es')? "Desde" : "From";
    $txt4  = ($lng == 'es')? "Hasta" : "Until";
    $txt5  = ($lng == 'es')? "Descanso diario" : "Daily break";
    $msg1  = ($lng == 'es')? "Rellene hh: mm usando el reloj de 24 horas. Si los tiempos no son fijos déjelos en blanco. " : "Fill out hh:mm using the 24 hour clock. If times are not fixed leave them blank. ";
    $msg2  = ($lng == 'es')? "Los tiempos \"Desde\" tienen que ser antes de  los tiempos \"Hasta\", con la excepción de la madrugada (antes de 05:00hh). " : "'From' times have to be before 'Until' times, with the exception of the early morning (before 05:00h). ";
    $msg3  = ($lng == 'es')? "\"". $txt5 ."\" ha sido seleccionado, pero no hay tiempos se han llenado para \"Descansa durante el dia\"" : "'". $txt5 ."' has been selected, but no times have been entered for the daily break at the bottom of this section.";
    $html = "<span class='time'>";
        if ($inp_switch) $html .= "<input type='radio' id='". $id ."_is_closed' name='". $id ."_open' value='' ". dis($inp_open, "rad_ctrl", "0") ."/><label for='". $id ."_is_closed'>". $txt1 ."</label><input type='radio' id='". $id ."_is_open' name='". $id ."_open' value='on' ". dis($inp_open, "rad_ctrl", "1") ."/><label for='". $id ."_is_open'>". $txt2 ."</label>";
        $html .= "<label for='". $id ."_frm_hr'>". $txt3 ."</label><input  type='text' class='numb"; if(!$inp_frm) $html .= " prompt' value='hh"; $html .= "' id='". $id ."_frm_hr' maxlength='2' value='' pattern='([0-1][0-9]|[2][0-3]|hh|[0-9])' title='". $ttl1 ."' /><label for='". $id ."_frm_min'>:</label><input type='text' class='numb"; if(!$inp_frm) $html .= " prompt' value='mm"; $html .= "' id='". $id ."_frm_min' maxlength='2' value='' pattern='([0-5][0-9]|mm)' title='". $ttl2 ."' />
        <input type='hidden' name='". $id ."_frm' id='". $id ."_frm' value='"; if($inp_frm) $html .= $inp_frm; $html .= "' pattern='\d\d:\d\d' />
        <label for='". $id ."_tll_hr'>". $txt4 ."</label><input type='text' class='numb"; if(!$inp_tll) $html .= " prompt' value='hh"; $html .= "' id='". $id ."_tll_hr' maxlength='2' value='' pattern='([0-1][0-9]|[2][0-3]|hh|[0-9])' title='". $ttl1 ."' /><label for='". $id ."_tll_min'>:</label><input type='text' class='numb"; if(!$inp_tll) $html .= " prompt' value='mm"; $html .= "' id='". $id ."_tll_min' maxlength='2' value='' pattern='([0-5][0-9]|mm)' title='". $ttl2 ."' />
        <input type='hidden' name='". $id ."_tll' id='". $id ."_tll' value='"; if($inp_tll) $html .= $inp_tll; $html .= "' pattern='\d\d:\d\d' />";
        if ( isset($inp_brk)) $html .= "<input type='checkbox' name='". $id ."_break' id='". $id ."_break' ". dis($inp_brk, "chk_ctrl") ."/><label for='". $id ."_break'>". $txt5 ."</label>";
        $html .= "<span class='err-msg'><span name='msg1' style='display: none;'>". $msg1 ."</span><span name='msg2' style='display: none;'>". $msg2 ."</span><span name='msg3' style='display: none;'>". $msg3 ."</span></span>
    </span>";
    return $html;
} // End of: echo_time_set($id, $inp_switch, $lng, $inp_open = NULL, $inp_frm = NULL, $inp_tll = NULL, $inp_brk = NULL)


// ****************************************************************
// CuscoNow - echo_date()
//
//          - Echoes a set of date controls, representing one date.
//          - JQuery is applied on load with input validation.
//          - Input is in dd-MMM-yyyy, with months in ES or EN.
//
// Mandatory parameters:
// $id          = HTML id (and name) for the hidden control. 
// $date        = Initial date value in MySQL format: yyyy-mm-dd.
// $lng         = 2-letter coded language for prompts, tool tips, and msgs.
//
// Optional parameters:
// $future      = Boolean indicating date has to be today or future.
// $comp_dateid = id of referenced date control set.
// $check_num_days = number of days (pos or neg) this date has to 
//                  be within the period of referenced date.
// ****************************************************************
function echo_date($id, $date, $lng, $future = FALSE, $comp_dateid = NULL, $check_num_days = NULL)
{
    $ttl1  = ($lng == 'es')? "número entre 1 y 31 o en blanco" : "number between 1 and 31 or blank";
    $ttl2  = ($lng == 'es')? "3 código de letras para los meses del año" : "3 letter code for month of the year";
    $ttl3  = ($lng == 'es')? "año en su totalidad y en este siglo (20##)" : "year in full and in this century (20##)";
    $ppt1  = ($lng == 'es')? "aaaa" : "yyyy";
    $msg1  = ($lng == 'es')? "Llene la fecha de acuerdo con el formato día-mes-año. Meses 3 abreviaturas de letras. " : "Fill out the date according to the day-month-year format. Months are 3 letter abbreviations (in Spanish or English). ";
    $msg2  = ($lng == 'es')? "Esta no es una fecha existente. " : "This is not an existing date. ";
    $msg3  = ($lng == 'es')? "La fecha no puede ser en el pasado. " : "Date cannot be in the past. ";
    $msg4  = ($lng == 'es')? "La fecha debe estar dentro de un período de ". $check_num_days ." días a partir de la fecha de referencia." : "Date must be within a period of ". $check_num_days ." days from referenced date.";
    $html = "<span class='date'>" .
        "<input  type='text'"; if(!$date) $html .= " class='prompt' value='dd'"; $html .= " id='". $id ."_dd' maxlength='2' pattern='([0][1-9]|[1-2][0-9]|[3][0-1]|dd|[1-9])' title='". $ttl1 ."' />" .
        "<label for='". $id ."_mmm'>-</label><input type='text' class='dmy"; if(!$date) $html .= " prompt' value='mmm"; $html .= "' id='". $id ."_mmm' maxlength='3' pattern='([a-zA-Z]{3}|m{3})' title='". $ttl2 ."' />" .
        "<label for='". $id ."_yyyy'>-</label><input type='text' class='dmy"; if(!$date) $html .= " prompt' value='". $ppt1; $html .= "' id='". $id ."_yyyy' maxlength='4'  pattern='(20\d{2}|y{4}|a{4})' title='". $ttl3 ."' />" .
        "<input type='hidden' name='". $id ."' id='". $id ."' value='"; if($date) $html .= $date; $html .= "' />" .
        "<input type='hidden' id='". $id ."_lng' value='". $lng ."' /><input type='hidden' id='". $id ."_future' value='". $future ."' /><input type='hidden' id='". $id ."_dateid' value='"; if($comp_dateid) $html .= $comp_dateid; $html .= "' /><input type='hidden' id='". $id ."_num_days' value='"; if($check_num_days) $html .= $check_num_days; $html .= "' />" .
        "<span class='err-msg'><span name='msg2' style='display: none;'>". $msg2 ."</span><span name='msg1' style='display: none;'>". $msg1 ."</span><span name='msg3' style='display: none;'>". $msg3 ."</span><span name='msg4' style='display: none;'>". $msg4 ."</span></span>
    </span>";
    return $html;
} // End of: echo_date($id, $date, $lng, $future = FALSE, $comp_dateid = NULL, $check_num_days = NULL)


// ****************************************************************
// CuscoNow - echo_price_range()
//
//          - Echoes a set of amount controls, representing a range.
//          - JQuery is applied on load with input validation.
//
// Mandatory parameters:
// $id          = HTML id (and name) for the controls with amounts,
//                appended with _fr_sol and _to_sol.
// $lng         = 2-letter coded language for prompts, tool tips, and msgs.
// ****************************************************************
function echo_price_range($id, $lng)
{
    $ttl  = ($lng == 'es')? "monto en soles hasta S/. 1,000" : "amount in Soles up to S/. 1,000";
    $txt1  = ($lng == 'es')? "Desde" : "From";
    $txt2  = ($lng == 'es')? "Hasta" : "To";
    $msg1  = ($lng == 'es')? "Rellene monto en soles y céntimos (###,##). Monto tiene que ser menos de mil soles. " : "Fill out amount in soles and cents (###,##). Amount has to be below 1000 Soles. ";
    $msg2  = ($lng == 'es')? "\"Desde\" cantidad tiene que ser inferior a \"Hasta\" cantidad. " : "'From' amount has to be lower than 'To' amount. ";
    $html = "<span class='prc_rnge'>" .
        "<label for='". $id ."_fr_sol'>". $txt1 ."</label>S/.<input type='text' class='numb prompt' id='". $id ."_fr_sol' name='". $id ."_fr_sol' maxlength='6' value='###,##' title='". $ttl ."' />" .
        "<label for='". $id ."_to_sol'>". $txt2 ."</label>S/.<input type='text' class='numb prompt' id='". $id ."_to_sol' name='". $id ."_to_sol' maxlength='6' value='###,##' title='". $ttl ."' />" .
        //"<label>". $txt1 ."</label>S/.<input type='text' class='numb prompt' name='". $id ."_price_fr' maxlength='6' value='###,##' pattern='([0-9,.]*|###,##)' title='". $ttl ."' />" .
        //"<label>". $txt2 ."</label>S/.<input type='text' class='numb prompt' name='". $id ."_price_to' maxlength='6' value='###,##' pattern='([0-9,.]*|###,##)' title='". $ttl ."' />" .
        "<span class='err-msg'><span name='msg1' style='display: none;'>". $msg1 ."</span><span name='msg2' style='display: none;'>". $msg2 ."</span></span>" .
    "</span>";
    return $html;
} // End of: echo_price_range()


// ****************************************************************
// CuscoNow - cusconow_get_owners_ests_and_subs()
//
//          - Retrieves set of establishments and subscriptions that:
//            . are owned by user, or
//            . ownned by 'user' in case user is CN management. 
//              (user comes from 'user' data-field in ajax link)
//
// Mandatory parameters:
// $con         = database connection
// ****************************************************************
function cusconow_get_owners_ests_and_subs($con)
{
    // Get sbscrbr_login
    if ( current_user_can('list_users') ) 
    {
        // Part of Mgmt team, so sbscrbr_login is passed in url
        $sbscrbr_login  = $_REQUEST['user'];
        if ( empty($sbscrbr_login) ) {
            echo "<p class='err-msg'>Could not retrieve subscriber information.</p>";
            exit();
        }
    } else {
        // Subscriber is logged in, avoid tampering 
        $current_user = wp_get_current_user();
        $sbscrbr_login = $current_user->user_login;
    }
    //_log("sbscrbr_login in cusconow_get_muts(): " .$sbscrbr_login);                                                                       // DEBUG //

    // Initialize
	$ests_subs = array();

    $query_string = "SELECT establishments.fk_sbscrbr_id, pk_est_id, sub_strt_date, pk_sub_id, fk_poi_type_id " .
    //$query_string = "SELECT establishments.fk_sbscrbr_id, pk_est_id, poi_shrt_name, is_subscribed, pk_sub_id " .
    //$query_string = "SELECT fk_sbscrbr_id, pk_est_id, pk_sub_id " .
                    "FROM establishments " . 
                    "LEFT JOIN pois ON (establishments.fk_poi_id = pois.pk_poi_id ) " .
                    "LEFT JOIN subscriptions ON (establishments.pk_est_id = subscriptions.fk_est_id) " .
                    "LEFT JOIN subscribers ON (establishments.fk_sbscrbr_id = subscribers.pk_sbscrbr_id) " .
                    //"LEFT JOIN subscription_types ON (subscriptions.fk_subtype_id = subscription_types.pk_subtype_id) " .  
                    "WHERE (is_subscribed = 0 OR sub_end_date IS NULL) AND sbscrbr_login = '". $sbscrbr_login ."'" .
                    "ORDER BY -sub_bundle_id DESC, ref_sub_id, poi_shrt_name, sub_end_date DESC, pk_est_id;";
    $result = mysqli_query($con, $query_string);
    while ( $row = mysqli_fetch_assoc($result) ) $ests_subs[] = $row;
    //$ests_subs = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);

    // Depending on request sent information about single est/sub or all
    $id = $_REQUEST['id'];
    //_log("ests_subs[id]: "); _log($ests_subs[$id]);                                                                                       // DEBUG //
    return ( $id == "all")? $ests_subs : $ests_subs[$id];

} // End of:  cusconow_get_owners_ests_and_subs($con)



// ****************************************************************
// CuscoNow - cusconow_get_muts()
//
//          - Uses WordPress ajax architecture; prevents user from
//            . successfully pasting ajax url in browser address bar,
//            . requesting data without being logged in.
//          - Picks up parameters through ajax call.
//
// ****************************************************************
add_action("wp_ajax_cusconow_get_muts", "cusconow_get_muts");
add_action("wp_ajax_nopriv_cusconow_get_muts", "cusconow_must_login");

function cusconow_get_muts()
{
    // Stop any tampering
    if ( isset($_REQUEST['nonce']) && !wp_verify_nonce( $_REQUEST['nonce'], "cusconow_get_muts_nonce") ) {
        _lua($func, "Nonce for cusconow_get_muts() failed.");
        exit();
    }  
     
    // Open database
    $con = cusconow_open_database();

    // Initialize
	$ests_subs = array();
    $ests_subs = cusconow_get_owners_ests_and_subs($con);
    //_log("est_subs: ". count($ests_subs)); _log($ests_subs);                                                                                      // DEBUG //
    $id = $_REQUEST['id'];
    $func = 'Async';
    // Get language from locale 
    $lng = explode("_", get_locale());
    $lng = $lng[0];

    if ( count($ests_subs) == 0 ) 
    {
        $response['type'] = "error";
        _log("Could not get subscription or owner in async call.");
    } else {
        $response['type'] = "success";

        // Create html response: Build mutations table for CuscoNow Mgmt depending on scope
        if ( $id === 'all' )
        {
            $response['html'] = cusconow_build_muts_table($func, $con, $lng, TRUE, NULL, NULL, NULL, $ests_subs[0]['fk_sbscrbr_id'], NULL);
        } else {
            $response['html'] = cusconow_build_muts_table($func, $con, $lng, NULL, $ests_subs['pk_sub_id'], $ests_subs['pk_est_id'], $ests_subs['sub_strt_date'], NULL, NULL);
        }
    }

    // If this is an async request; echo response, otherwise reload the page (with response)
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $response = json_encode($response);
        echo $response;
    } else {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
    }

    // Clean up
    mysqli_close($con);
    unset($response); 

   die();

} // End of: cusconow_get_muts()


// ****************************************************************
// CuscoNow - cusconow_get_feats()
//
//          - Uses WordPress ajax architecture; prevents user from
//            . successfully pasting ajax url in browser address bar,
//            . requesting data without being logged in.
//          - Picks up parameters through ajax call.
//
// ****************************************************************
add_action("wp_ajax_cusconow_get_feats", "cusconow_get_feats");
add_action("wp_ajax_nopriv_cusconow_get_feats", "cusconow_must_login");

function cusconow_get_feats()
{
    // Initialize
	$ests_subs = array();
    $func = 'Async';

    // Stop any tampering
    if ( isset($_REQUEST['nonce']) && !wp_verify_nonce( $_REQUEST['nonce'], "cusconow_get_feats_nonce") ) {
        //_log("Nonce for cusconow_get_feats() failed.");                                                                                           // DEBUG //
        _lua($func, "Nonce for cusconow_get_feats() failed.");
        exit();
    }  
     
    // Open database
    $con = cusconow_open_database();

    // Get all defined languages
    $langs = array();
    $result = mysqli_query($con, "SELECT pk_lang_code FROM languages;");
    while($row = mysqli_fetch_array($result)) $langs[] = $row['pk_lang_code'];
    mysqli_free_result($result);

    // Get the right establishment
    $ests_subs = cusconow_get_owners_ests_and_subs($con);
    //_log("est_subs: ". count($ests_subs)); _log($ests_subs);                                                                                      // DEBUG //

    if ( count($ests_subs) == 0 ) // if there are empty results
    {
        $response['type'] = "error";
        _log("Could not get subscription or owner in async call.");
    }
    else 
    {
        // Create data response: Query establishments and their address data, and lang dep names. Plus all data on establishment features.
        $query_string = "SELECT fk_sbscrbr_id, pk_est_id, gets_usr_mails, is_allwd_feat, thumbnail_url, main_phone, fr_phone_type_id, " . 
                        "poi_shrt_name, fk_poi_type_id, addr_street, addr_area, 1st_floor, 2nd_floor, 3rd_and_floor, elevator, escalator, poi_type_icon, " .
                        "wifi_name, wifi_pwd, has_inet_trmls, has_cableTV, has_smkng, has_fire_plce, has_balcony, is_at_square, has_patio, has_kid_crnr, " .
                        "has_baby_chnge, has_library, has_guard_prk, has_bar_games, has_esprsso, serves_alcohol, has_imp_beer, has_drft_beer, has_cocktails, has_pisco";

        if ($ests_subs['fk_poi_type_id'] == "restaurant" || $ests_subs['fk_poi_type_id'] == "bar-rest" || $ests_subs['fk_poi_type_id'] == "cafe") 
            $query_string .=", has_full_menu, has_desserts, has_bar, has_take_out, has_delivery, has_buffet, has_all_u_eat, has_fine_dining";

        $table_string = " FROM establishments " . 
                        "LEFT JOIN establishment_features ON (establishments.pk_est_id = establishment_features.fk_est_id ) ";

        if ($ests_subs['fk_poi_type_id'] == "restaurant" || $ests_subs['fk_poi_type_id'] == "bar-rest"|| $ests_subs['fk_poi_type_id'] == "cafe") 
            $table_string .="LEFT JOIN restaurants ON (establishments.pk_est_id = restaurants.fk_est_id ) ";

        $table_string .="LEFT JOIN pois ON (establishments.fk_poi_id = pois.pk_poi_id ) " .
                        "LEFT JOIN poi_types ON (pois.fk_poi_type_id = poi_types.pk_poi_type_id ) ";
        $where_string = "WHERE pk_est_id='" . $ests_subs['pk_est_id']. "'";

        foreach($langs as $lang) 
        {
            $query_string .= ", poi_descriptions_".$lang.".poi_full_name poi_full_name_".$lang. 
                             ", establishment_feats_".$lang.".overview_txt overview_txt_".$lang.  
                             ", establishment_feats_".$lang.".othr_srvcs othr_srvcs_".$lang.  
                             ", establishment_feats_".$lang.".othr_cuis othr_cuis_".$lang;
            $table_string .= "LEFT JOIN poi_descriptions_lang poi_descriptions_".$lang." ON ( poi_descriptions_".$lang.".fk_poi_id = pois.pk_poi_id AND poi_descriptions_".$lang.".fr_lang_code = '".$lang."') " .
                             "LEFT JOIN establishment_feats_lang establishment_feats_".$lang." ON ( establishment_feats_".$lang.".fk_est_id = establishments.pk_est_id AND establishment_feats_".$lang.".fr_lang_code = '".$lang."') ";
        }
        //_log("full query string for feats: ". $query_string . $table_string . $where_string .";");                                                // DEBUG //
        $result = mysqli_query($con, $query_string . $table_string . $where_string .";");

        if ($result && mysqli_num_rows($result) > 0)  // Establishment found
        {
            $response['data'] = mysqli_fetch_assoc($result);
            //_log("response['data']: "); _log($response['data']);                                                                                  // DEBUG //
            mysqli_free_result($result);
            $response['type'] = "success";
        } else {
            $response['type'] = "error";
            _log("Could not get features for establishment (ID:". $ests_subs['pk_est_id'] .") in async call.");                                     // DEBUG //
        }

        // Create telnr response: Query other_phone_nrs (other telephone numbers table).
        $result = mysqli_query($con, "SELECT fr_phone_type_id, phone_nr FROM other_phone_nrs WHERE fk_est_id='". $ests_subs['pk_est_id'] ."';");
        if ($result && mysqli_num_rows($result) > 0)  // at least 1 othr_phone_nr  found
        {
            unset($row);
            while ( $row = mysqli_fetch_assoc($result) ) $response['phone_nrs'][] = $row;
            //$response['phone_nrs'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_free_result($result);
        } else {
            _log("Could not get any other phone numbers for establishment (ID:". $ests_subs['pk_est_id'] .") in async call.");                      // DEBUG //
        }

        // Create charge card response: Query cards_accepted.
        $result = mysqli_query($con, "SELECT fr_cc_id FROM cards_accepted WHERE fk_est_id='". $ests_subs['pk_est_id'] ."';");
        if ($result && mysqli_num_rows($result) > 0)  // at least 1 cc found
        {
            unset($row);
            while ( $row = mysqli_fetch_assoc($result) ) $response['ccs'][] = $row;
            //$response['ccs'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_free_result($result);
        } else {
            _log("Could not get any charge cards for establishment (ID:". $ests_subs['pk_est_id'] .") in async call.");                             // DEBUG //
        }

        // Create atmosphere response: Query atmospheres.
        $result = mysqli_query($con, "SELECT fr_atmosph_id FROM atmospheres WHERE fk_est_id='". $ests_subs['pk_est_id'] ."';");
        if ($result && mysqli_num_rows($result) > 0)  // at least 1 atmosphere found
        {
            unset($row);
            while ( $row = mysqli_fetch_assoc($result) ) $response['atmos'][] = $row;
            //$response['atmos'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_free_result($result);
        } else {
            _log("Could not get any atmospheres for establishment (ID:". $ests_subs['pk_est_id'] .") in async call.");                              // DEBUG //
        }

        // Create cuisine response: Query cuisines.
        $result = mysqli_query($con, "SELECT fr_cuisine_id FROM cuisines WHERE fk_est_id='". $ests_subs['pk_est_id'] ."';");
        if ($result && mysqli_num_rows($result) > 0)  // at least 1 atmosphere found
        {
            unset($row);
            while ( $row = mysqli_fetch_assoc($result) ) $response['cuisines'][] = $row;
            //$response['cuisines'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_free_result($result);
        } else {
            _log("Could not get any cuisines for establishment (ID:". $ests_subs['pk_est_id'] .") in async call.");                                 // DEBUG //
        }

    }

    // If this is an async request; echo response, otherwise reload the page (with response)
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $response = json_encode($response);
        echo $response;
    } else {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
    }

    // Clean up
    mysqli_close($con);
    unset($response); 

   die();

} // End of: cusconow_get_feats()


// ****************************************************************
// CuscoNow - cusconow_get_hours()
//
//          - Uses WordPress ajax architecture; prevents user from
//            . successfully pasting ajax url in browser address bar,
//            . requesting data without being logged in.
//          - Picks up parameters through ajax call.
//
// ****************************************************************
add_action("wp_ajax_cusconow_get_hours", "cusconow_get_hours");
add_action("wp_ajax_nopriv_cusconow_get_hours", "cusconow_must_login");

function cusconow_get_hours()
{
    // Stop any tampering
    if ( isset($_REQUEST['nonce']) && !wp_verify_nonce( $_REQUEST['nonce'], "cusconow_get_hours_nonce") ) {
        _lua($func, "Nonce for cusconow_get_hours() failed.");
        exit();
    }  
     
    // Open database
    $con = cusconow_open_database();

    // Get all defined languages
    $langs = array();
    $result = mysqli_query($con, "SELECT pk_lang_code FROM languages;");
    while($row = mysqli_fetch_array($result)) $langs[] = $row['pk_lang_code'];
    mysqli_free_result($result);

    // Initialize
	$ests_subs = array();
    $ests_subs = cusconow_get_owners_ests_and_subs($con);
    //_log("est_subs: ". count($ests_subs)); _log($ests_subs);                                                                                      // DEBUG //
    $func = 'Async';

    if ( count($ests_subs) == 0 ) // if there are empty results
    {
        $response['type'] = "error";
        _log("Could not get subscription or owner in async call.");
    }
    else 
    {
        // Create data response: Query opening_hours for this establishment, and join closure_text
        $query_string = "SELECT mon_open, mon_frm, mon_tll, mon_break, tue_open, tue_frm, tue_tll, tue_break, wed_open, wed_frm, wed_tll, wed_break, thu_open, thu_frm, thu_tll, thu_break, fri_open, fri_frm, fri_tll, fri_break, sat_open, sat_frm, sat_tll, sat_break, sun_open, sun_frm, sun_tll, sun_break, break_frm, break_tll, closure_strt, closure_end";
        $table_string = " FROM opening_hours ";

        foreach($langs as $lang) 
        {
            $query_string .= ", establishment_feats_".$lang.".closure_text closure_text_".$lang;
            $table_string .= "LEFT JOIN establishment_feats_lang establishment_feats_".$lang." ON ( establishment_feats_".$lang.".fk_est_id = opening_hours.fk_est_id AND establishment_feats_".$lang.".fr_lang_code = '".$lang."') ";
        }
        
        $result = mysqli_query($con, $query_string . $table_string ."WHERE opening_hours.fk_est_id='" . $ests_subs['pk_est_id']. "';");
        //$result = mysqli_query($con, "SELECT * FROM opening_hours WHERE fk_est_id='" . $ests_subs['pk_est_id']. "';");
        //_log("hours query string: ". $query_string . $table_string ."WHERE opening_hours.fk_est_id='" . $ests_subs['pk_est_id']. "';");           // DEBUG //
        
        if ($result && mysqli_num_rows($result) > 0)  // Establishment found
        {
            $response['data'] = mysqli_fetch_assoc($result);
            //_log("response['data']: "); _log($response['data']);                                                                                  // DEBUG //
            mysqli_free_result($result);
            $response['type'] = "success";
        } else {
            $response['type'] = "error";
            _log("Could not get hours for establishment (ID:". $ests_subs['pk_est_id'] .") in async call.");                                        // DEBUG //
        }
    }

    // If this is an async request; echo response, otherwise reload the page (with response)
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $response = json_encode($response);
        echo $response;
    } else {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
    }

    // Clean up
    mysqli_close($con);
    unset($response); 

   die();

} // End of: cusconow_get_hours()



// ****************************************************************
// CuscoNow - cusconow_get_menus()
//
//          - Uses WordPress ajax architecture; prevents user from
//            . successfully pasting ajax url in browser address bar,
//            . requesting data without being logged in.
//          - Picks up parameters through ajax call.
//
// ****************************************************************
add_action("wp_ajax_cusconow_get_menus", "cusconow_get_menus");
add_action("wp_ajax_nopriv_cusconow_get_menus", "cusconow_must_login");

function cusconow_get_menus()
{
    // Open database
    $con = cusconow_open_database();

    // Get all defined languages
    $langs = array();
    $result = mysqli_query($con, "SELECT pk_lang_code FROM languages;");
    while($row = mysqli_fetch_array($result)) $langs[] = $row['pk_lang_code'];
    mysqli_free_result($result);

    // Initialize
    $func = 'Async';
    if ( isset($_REQUEST['nonce']) && !wp_verify_nonce( $_REQUEST['nonce'], "cusconow_get_menus_nonce") ) { // Stop any tampering

        _lua($func, "Nonce for cusconow_get_menus() failed.");
        exit();
    }
	$ests_subs = array();
    $ests_subs = cusconow_get_owners_ests_and_subs($con);
    //_log("est_subs: ". count($ests_subs)); _log($ests_subs);                                                                                      // DEBUG //

    if ( count($ests_subs) == 0 ) // if there are empty results
    {
        $response['type'] = "error";
        _log("Could not get subscription or owner in async call.");
    }
    else 
    {
        // Create data response: Query menus and sections for this establishment
        $result = mysqli_query($con, "SELECT pk_menu_id, menu_id, fr_lang_code, menu_title, menu_fr_sol, menu_to_sol, menu_frm, menu_tll, is_active, pk_section_id, section_name, sect_fr_sol, sect_to_sol FROM menus_lang LEFT JOIN menu_sections_lang ON (pk_menu_id = fk_menu_id ) WHERE fk_est_id='". $ests_subs['pk_est_id'] ."' ORDER BY menu_id, pk_menu_id, pk_section_id;");

        if ($result && mysqli_num_rows($result) > 0)  // Menu(s) found
        {
            while($row = mysqli_fetch_assoc($result)) $response['data']['menu'][] = $row;
            //_log("response['data']['menu']: "); _log($response['data']['menu']);                                                                  // DEBUG //
            mysqli_free_result($result);
            $response['type'] = "success";

            // There's at least one menu, create an array with all the menu items
            $result = mysqli_query($con, "SELECT pk_id, fk_section_id, item_name FROM menus_lang LEFT JOIN menu_sections_lang ON (pk_menu_id = fk_menu_id ) LEFT JOIN menu_items_lang ON (pk_section_id = fk_section_id ) WHERE fk_est_id='". $ests_subs['pk_est_id'] ."' ORDER BY fk_section_id, pk_id;");
            while($row = mysqli_fetch_assoc($result)) $response['data']['items'][] = $row;
            //_log("response['data']['items']: "); _log($response['data']['items']);                                                                // DEBUG //
            mysqli_free_result($result);

        } else {
            $response['type'] = "error";
            _log("Could not get menus for establishment (ID:". $ests_subs['pk_est_id'] .") in async call.");                                        // DEBUG //
        }
    }

    // If this is an async request; echo response, otherwise reload the page (with response)
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $response = json_encode($response);
        echo $response;
    } else {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
    }

    // Clean up
    mysqli_close($con);
    unset($response); 

   die();

} // End of: cusconow_get_menus()


// ****************************************************************
// Groen Productions - groenp_must_login()
//                   - Echoes general string in case user tries to load page 
//                     without being logged in.
//
// ****************************************************************
function groenp_must_login() 
{
   echo "You must be logged in."; // user must be fiddling with the url, don't give away information...
   die();
} // End of: groenp_must_login() 


?>