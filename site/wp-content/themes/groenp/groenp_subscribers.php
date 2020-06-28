<?php
/******************************************************************************/
/*                                                              Pieter Groen  */
/*  Version 0.1 - May  23, 2020 (conversion from cusconow)                    */
/*  Version 0.2 - June  5, 2020 (project mgmt)                                */
/*  Version 0.3 - June  9, 2020 (subscriber-project pairing)                  */
/*  Version 0.4 - June 25, 2020 (use of general functions for page creation)  */
/*                                                                            */
/*  PHP for Groen Productions Sites Mgmt CMS in WordPress:                    */
/*   - subscriber management                 (~line   80)                     */
/*   - project management                    (~line  660)                     */
/*   - subscriber-project pairing            (~line  990)                     */
/*                                                                            */
/******************************************************************************/
// namespace groenp;

/******************************************************************************/
/*  Groen Productions - Add Subscribers page to the WordPress Dashboard       */
/******************************************************************************/


// ****************************************************************
// Define this screen globally: 'groenp_subscribers' 
// ****************************************************************
// $page_slug = 'groenp_subscribers'; // << avoid using global vars >>

// Globals > AVOID
$project = groenp_get_project_from_file( basename(__FILE__) );
$page_slug = $project['page_slug'];

// ****************************************************************
// Add callbacks for this screen only
// ****************************************************************
add_action('load-dashboard_page_' . $page_slug, 'groenp_register_meta_boxes');
// groenp_register_meta_boxes() defined in functions.php

 
/* Create page content */
add_action('admin_menu', 'groenp_register_subscribers_submenu_page');

function groenp_register_subscribers_submenu_page() 
{
    $project = groenp_get_project_from_file( basename(__FILE__) );

    // add_submenu_page( 'index.php', 'Subscribers', 'GP: Subscribers', 'create_users', 'groenp_subscribers', 'groenp_create_page_cb' ); 
	add_submenu_page( 'index.php', $project['prjct_name'], 'GP: '. $project['prjct_name'] , 'create_users', $project['page_slug'], 'groenp_create_page_cb' ); 
}
// groenp_create_page_cb() defined in functions.php


/******************************************************************************/
/* Groen Productions - Create Meta Boxes for Subscribers page of MySQL DBs    */
/*                                                                            */
/******************************************************************************/

// pattern: add_action('add_meta_boxes_'. $page_slug, $page_slug .'_meta_boxes_add')
add_action('add_meta_boxes_groenp_subscribers', 'groenp_subscribers_meta_boxes_add');

function groenp_subscribers_meta_boxes_add() 
{
    global $plugin_page;

    add_meta_box( 'gp-sbscrbr-cnt-mb', 'This page supports the following tasks:', 'groenp_sbscrbr_content_meta_box_cb', $plugin_page, 'normal' );
    add_meta_box( 'gp-subscribers-mb', 'Manage subscribers',                      'groenp_subscribers_meta_box_cb',     $plugin_page, 'normal' );
    add_meta_box( 'gp-projects-mb',    'Manage projects',                         'groenp_projects_meta_box_cb',        $plugin_page, 'normal' );
    add_meta_box( 'gp-subpro-mb',      'Manage subscriber / project pairing',     'groenp_subpro_pairing_meta_box_cb',  $plugin_page, 'normal' );

}


// ****************************************************************
// Callback for SBSCRBR CONTENT Meta Box
// ****************************************************************
function groenp_sbscrbr_content_meta_box_cb()  
{
    echo "<ul>
        <li><a href='#Sbscbr'>Manage subscribers</a>, <a href='#add_Sbscbr'>Add or edit subscriber</a></li>
        <li><a href='#Prjct'>Manage projects</a>, <a href='#add_Prjct'>Add or edit project</a></li>
        <li><a href='#SbsPrj'>Manage subscriber-project pairing</a>, <a href='#add_SbsPrj'>Add or edit pairing</a></li>
    </ul>";

}  // End: groenp_sbscrbr_content_meta_box_cb() 

// ****************************************************************
// Callback for SUBSCRIBERS Meta Box
// ****************************************************************
function groenp_subscribers_meta_box_cb()  
{  
    // open database
    $con = groenp_open_database();

    // ************************************************************
    // 1. Process any form data
    // ************************************************************

    // Make this form unique
	$func = 'Sbscbr';
    
    // If no Edit button pressed inside the table of this meta box
    if ( !array_search('Edit', $_POST) ) echo "<a class='anchr' name=" . $func . "></a>";  // Set anchor

    // default SSL port number; use https version, otherwise not
    $protocol = ($_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';

    // Create form url for this meta box
	$form_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '#' . $func;

    // DEBUG section
	// All form fields (name and id) have the same name as the column name of their counterpart in the database
	// lang specific fields have DB name and "_<lng>" appended to it
    // _log("===> START CHECKING MANAGEMENT FORM for: " . $func ); // DEBUG //
	// _log("form url will be index.php with: " . $form_url );     // DEBUG // 
 	// _log("POST: "); _log($_POST); // form fields //             // DEBUG // 
 	// _log($_FILES);                // pics //                    // DEBUG // 

    // ************************************************************
	if ( isset($_POST[('add_'. $func)]) || isset($_POST[('edit_'. $func)]) )  // THIS form has been submitted
    // ************************************************************
    {
		// Check if all mandatory fields have been entered
		if ( empty($_POST['user_login']) )
		{
            //_log("*** input error ***");
			echo "<p class='err-msg'>The user_login field must be filled in. All input fields marked with a '*' must be completed.</p>";
		}
        else {
            // define and sanitize vars
            $user_login     = prep($_POST['user_login'], 'a');
            $sbscrbr_notes  = prep($_POST['sbscrbr_notes'], 's');
            $is_usr_blocked = prep($_POST['is_usr_blocked'], 'chk');
            $gets_html_mail = prep($_POST['gets_html_mail'], 'chk');
            $pk_sbscrbr_id  = prep($_POST['edit_id'],'i');

			// ************************************************************
		    if ( isset($_POST[('add_'. $func)]) ) // insert form data into tables
			// ************************************************************
			{
                // Subscriber ID (user_login) has to be unique
                $stmt = mysqli_prepare($con, 'SELECT sbscrbr_login FROM gp_subscribers WHERE sbscrbr_login=?');
                mysqli_stmt_bind_param($stmt, 's', $user_login);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                $result =  mysqli_stmt_num_rows($stmt);                         // check number of rows as proof of result
                mysqli_stmt_free_result($stmt);
                mysqli_stmt_close($stmt); 
                //_log("user_login already exists?: ". $result);                // DEBUG //

				// check if user_email entered, if so try to create new wp_user
                if ( !empty($_POST['user_email']) ) 
                {
                    // user_login and email have to be unique, user_login in two tables
                    $user_id = username_exists( $user_login );
                    if ( !$user_id && email_exists( $_POST['user_email'] ) === FALSE && $result == 0 )
                    {
                        // Create user with userID and auto-generated password, kses applied
                        $password  = wp_generate_password( 12, FALSE );
                        $user_id = wp_create_user( $user_login, $password, $_POST['user_email']); 
                        // Add all data to wp_user table, kses applied
                        wp_update_user( array(
                                'ID'    => $user_id,
                                'first_name' => $_POST['first_name'],
                                'last_name' => $_POST['last_name'],
                                'locale'=> $_POST['locale'])
                        );
                        // Cannot create the reset key immediately afterwards, it expires

                        // Mail the user with the new ID and pwd, this is a safer bet (need to understand hashing)
                        // groenp_plain_mail(    $user_id, 'new_user_notification', "Welcome to Groen Productions - Site Management", "Registration details", NULL, $password);
                        // groenp_html_mail(     $user_id, 'new_user_notification', "Welcome to Groen Productions - Site Management", "Registration details", NULL, $password);

                        // Mail in new user's own language as defined by admin during creation: nl, es, en (or any other)
                        $lng = substr($_POST['locale'], 0, 2);
                        if ($lng == 'nl') {
                            groenp_multipart_mail($user_id, 'new_user_notification', "Welkom bij Groen Productions - Sitebeheer", "Registratiegegevens", NULL, $password);
                        } elseif ($lng == 'es') {
                            groenp_multipart_mail($user_id, 'new_user_notification', "Bienvenido a Groen Productions - Manejo de sitio", "Detalles de registro", NULL, $password);
                        } else {
                            groenp_multipart_mail($user_id, 'new_user_notification', "Welcome to Groen Productions - Site Management", "Registration details", NULL, $password);
                        }
                        //wp_mail( $_POST['user_email'], 'Bienvenido a CuscoNow!', 'Your loginID: '. $_POST['user_login'] . ', Your Password: ' . $password );

                        if ( is_wp_error( $user_id ) ) { // There was an error updating the wp_user table...
                            echo "<p class='err-msg'>Created a new wp_user, but could not update that user with all form data. Try to edit the subscriber from the list.</p>";
                        };
                        // We're doing swell, now create subscriber and link to wp_user
                        $query_string = 'INSERT INTO gp_subscribers ' .
                            '(fr_ID, sbscrbr_login, is_usr_blocked, gets_html_mail, sbscrbr_notes) ' . 
                            'VALUES (?, ?, 0, 1, ?)';
                        $stmt = mysqli_prepare($con, $query_string);

                        if ($stmt ===  FALSE) { _log("Invalid insertion query for " . $func . ": " . mysqli_error($con)); }
                        else {
                            // bind stmt = i: integer, d: double, s: string
                            $bind = mysqli_stmt_bind_param($stmt, 'iss', $user_id, $user_login, $sbscrbr_notes);
                            if ($bind ===  FALSE) { _log("Bind parameters failed for add query (".$lng.") in " . $func); }
                            else {
                                // execute query 
                                $exec = mysqli_stmt_execute($stmt);
                                if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not add item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                                else { _lua($func, "Subscriber (ID: ". mysqli_insert_id($con) .", ". $user_login . ", email: ". $_POST['user_email'] .") with panel access created."); }
                                mysqli_stmt_close($stmt); 
                            } // end of: binding successful
                        } // end of: stmt prepared successful
                    } else { // the user_login and/or email are not unique
                        echo "<p class='err-msg'>The email address and/or userID (login) are already in use. You can create a new subscriber (without email) and then attach it to that account. 
                        Also, it is possible to add a new subscription to an existing subscriber.</p>";
                    }
                } else { // no email so just create subscriber
                    if ( $result != 0 ) // there's a hit
                    {
                        echo "<p class='err-msg'>The Subscriber ID is already in use. You can create a new subscriber (without email) and then attach it to that account. 
                        Also, it is possible to add a new subscription to an existing subscriber.</p>";
                    } else {
                        $query_string = 'INSERT INTO gp_subscribers ' .
                            '(fr_ID, sbscrbr_login, is_usr_blocked, gets_html_mail, sbscrbr_notes) ' . 
                            'VALUES (NULL, ?, 0, NULL, ?)';
                        $stmt = mysqli_prepare($con, $query_string);

                        if ($stmt ===  FALSE) { _log("Invalid insertion query for " . $func . ": " . mysqli_error($con)); }
                        else {
                            // bind stmt = i: integer, d: double, s: string
                            $bind = mysqli_stmt_bind_param($stmt, 'ss', $user_login, $sbscrbr_notes);
                            if ($bind ===  FALSE) { _log("Bind parameters failed for add query (".$lng.") in " . $func); }
                            else {
                                // execute query 
                                $exec = mysqli_stmt_execute($stmt);
                                if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not add item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                                else { _lua($func, "Subscriber (ID: ". mysqli_insert_id($con) .", ". $user_login .") - no panel access - created."); }
                                mysqli_stmt_close($stmt); 
                            } // end of: binding successful
                        } // end of: stmt prepared successful
                    } // end of: Sub ID is unique
                } // end of: email present?
			} // end of: add?
			// ************************************************************
			elseif ( isset($_POST[('edit_'. $func)]) )  // update tables row with edit_id 
			// ************************************************************
			{
                // create a prepared statement, Update subscriber table
                $query_string = 'UPDATE LOW_PRIORITY gp_subscribers SET ' .
                    'is_usr_blocked = ?, gets_html_mail = ?, sbscrbr_notes = ? ' . 
                    'WHERE pk_sbscrbr_id = ?';
                //_log("Edit query for ". $func .": ". $query_string);              // DEBUG //
                $stmt = mysqli_prepare($con, $query_string);

                if ($stmt ===  FALSE) { _log("Invalid update query for " . $func . ": " . mysqli_error($con)); } 
                else { 
                    // bind stmt = i: integer, d: double, s: string
                    $bind = mysqli_stmt_bind_param($stmt, 'iisi', $is_usr_blocked, $gets_html_mail, $sbscrbr_notes, $pk_sbscrbr_id);
                    if ($bind ===  FALSE) { _log("Bind parameters failed for add query in " . $func); }
                    else {
                        // execute query 
                        $exec = mysqli_stmt_execute($stmt);
                        if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not update item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                        else { _lua($func, "Subscriber (ID: ". $pk_sbscrbr_id .", ". $user_login .") updated."); }
                        mysqli_stmt_close($stmt); 
                    } // end of: binding successful
                } // end of: stmt prepared successful

                // Update wp_user if present
                $stmt = mysqli_prepare($con, 'SELECT fr_ID FROM gp_subscribers WHERE pk_sbscrbr_id=?');
                mysqli_stmt_bind_param($stmt, 's', $pk_sbscrbr_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $row['fr_ID']);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt); 
                //_log("row['fr_ID']: " . $row['fr_ID']);      // DEBUG //

                if ( !empty($row['fr_ID']) ) 
                {
                    wp_update_user( array(
                        'ID'    => $row['fr_ID'],
                        'first_name' => $_POST['first_name'],
                        'last_name' => $_POST['last_name'],
                        'locale'=> $_POST['locale'])
                    );
                    _lua($func, "Subscriber (ID: ". $pk_sbscrbr_id .", ". $user_login .") wp_user part edit successful? (no answer means yes)"); 
                }
                if ( is_wp_error( $row['fr_ID'] ) ) { // There was an error updating the wp_user table...
                    echo "<p class='err-msg'>Could not update the WordPress section of the subscriber with all form data. Try to edit the subscriber in Users.</p>";
                    _lua($func, "Subscriber (ID: ". $pk_sbscrbr_id .", ". $user_login .") wp_user part edit failed!"); 
                }
			}
		} // end of not missing mandatory fields
    } // end of add or edit (form submitted)

    // ************************************************************
    elseif (isset($_POST[('sure_'. $func)]))  // check for 'sure_'+function to see whether pushbutton used in THIS table or form
    // ************************************************************
    {
        $delkey = intval(array_search('Delete', $_POST));   // Maybe it's a Delete; check for id on 'Delete' button (case sensitive) and store it
        $editkey = intval(array_search('Edit', $_POST));    // Maybe it's an Edit; check for id on 'Edit' button (case sensitive) and store it
        if ( empty($editkey) ) unset($editkey);             // It's not an Edit, so unset otherwise the form will load as an edit

        if ( !empty($delkey) ) $sbscrbr_id = $delkey;
        if ( !empty($editkey) ) $sbscrbr_id = $editkey;

        // Find wp_user
        $wp_user = FALSE;
        if ( !empty($sbscrbr_id) )
        {
            // Use $sbscrbr_id to find corresponding wp_user part if any
            $stmt = mysqli_prepare($con, 'SELECT fr_ID, sbscrbr_login FROM gp_subscribers WHERE pk_sbscrbr_id=?');
            mysqli_stmt_bind_param($stmt, 'i', $sbscrbr_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $row['fr_ID'], $row['sbscrbr_login']);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt); 
            //_log("UserID in GP: " . $delkey);               // DEBUG //
            //_log("UserID in wp: " . $row['fr_ID']);         // DEBUG //

            if ( !empty($row['fr_ID']) ) $wp_user = get_userdata($row['fr_ID']);

        } else { 
            // No result through fr_ID (must be action to attach then), let's go through user_login and edit_id
            if ( !empty($_POST['edit_id']) ) $sbscrbr_id = prep($_POST['edit_id'],'i');
            $user_id = username_exists( $_POST['user_login'] );
            // _log("UserID in wp: " . $user_id);
            if ( $user_id ) $wp_user = get_userdata($user_id);
        }
        // DEBUG section
        //if (!empty($delkey))  _log("Delete selected with sanitized delkey: ". $delkey);					// DEBUG //
        //_log("Delete js response: ". $_POST['sure_'. $func]);												// DEBUG //
        //if (!empty($editkey)) _log("Edit selected with sanitized editkey: ". $editkey);					// DEBUG //
        //_log("San sbscrbr_id: " .  $sbscrbr_id . ". Corresponding wp_user with id: ". $wp_user->ID);		// DEBUG //
        //if ( $wp_user ) _log("User role: " . $wp_user->roles[0]);											// DEBUG //


        // [1] Maybe "Delete (corresponding) wp_user part" has been selected and user answered 'Yes' on RUsure?
        if ( isset($_POST['del_wpuser_'. $func]) && isset($_POST['sure_'. $func]) && $_POST['sure_'. $func]=='yes')
        {
            if ( $wp_user ) 
            {
                // Update subscriber table
                $query_string = 'UPDATE LOW_PRIORITY gp_subscribers SET fr_ID = NULL, gets_html_mail = NULL WHERE pk_sbscrbr_id = ?';
				//_log("Remove wp_user query string for " . $func . ": " . $query_string);			// DEBUG //
                $stmt = mysqli_prepare($con, $query_string);
                $bind = mysqli_stmt_bind_param($stmt, 'i', $sbscrbr_id);
                $exec = mysqli_stmt_execute($stmt);
                if ($exec ===  FALSE) { echo "<p class='err-msg'>Invalid remove wp_user query for " . $func . ": " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                else { _lua($func, "Subscriber (ID: ". $sbscrbr_id .", ". $row['sbscrbr_login'] .") wp_user section removed."); }
                mysqli_stmt_close($stmt); 

                if ( $wp_user->roles[0] == 'subscriber')
                {
                    wp_delete_user($wp_user->ID);
                   _log("wp_user (ID: " . $wp_user->ID . ") deleted.");						// DEBUG //
                   _lua($func, "Subscriber's wp_user (ID: ". $wp_user->ID .", ". $wp_user->user_login .") deleted.");
                } else {
                    echo "<p class='err-msg'>The corresponding wp_user of this subscriber has a role higher than subscriber and cannot be deleted.</p>";
                }
            } else {
                echo "<p class='err-msg'>Could not find the subscriber (whose wp_user needs to be deleted) through its ID: " . $_POST['edit_id'] . "</p>"; // never use sanitized vars in msgs!
            }
        } // end deletion of wp_user part

        // [2] Maybe "Attach (corresponding) wp_user part" has been selected and user answered 'Yes' on RUsure?
        elseif ( isset($_POST['add_wpuser_'. $func]) && isset($_POST['sure_'. $func]) && $_POST['sure_'. $func]=='yes')
        {
            if ( $wp_user ) 
            {
                // Update subscriber table
                $query_string = "UPDATE LOW_PRIORITY gp_subscribers SET fr_ID = ?, gets_html_mail = 1 WHERE pk_sbscrbr_id = ?";
				//_log("Attach wp_user query string for " . $func . ": " . $query_string);			// DEBUG //
                $stmt = mysqli_prepare($con, $query_string);
                mysqli_stmt_bind_param($stmt, 'ii', $wp_user->ID, $sbscrbr_id);
                $exec = mysqli_stmt_execute($stmt);
                if ($exec ===  FALSE) { echo "<p class='err-msg'>Invalid attach wp_user query for " . $func . ": " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                else { _lua($func, "Subscriber (ID: ". $sbscrbr_id .", ". $wp_user->user_login .") wp_user section added."); }
                mysqli_stmt_close($stmt); 
            } else {
                echo "<p class='err-msg'>Could not find the subscriber (whose wp_user with corresponding login ID needs to be attached) through its ID: " . $_POST['edit_id'] . "</p>";
            }
        }  // end attachment of wp_user part

		// [3] Maybe there is a delete id and user answered 'Yes' on RUsure?
        elseif ( isset($_POST[$delkey]) && isset($_POST['sure_'. $func]) && $_POST['sure_'. $func]=='yes')
        {
            // First check whether Subscriber is not 'active', ie. assigned to one or more projects
            $stmt = mysqli_prepare($con, 'SELECT pk_sppair_id FROM gp_sbscrbr_prjct_pairings WHERE fk_sbscrbr_id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $sbscrbr_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            // record the number of hits, this can be zero, one or more
            $hits = mysqli_stmt_num_rows($stmt);
            mysqli_stmt_close($stmt); 
            
            if ( $hits == 0 )
            {
                // Delete all rows in table with id= delkey
                $query_string = 'DELETE LOW_PRIORITY FROM gp_subscribers WHERE pk_sbscrbr_id = ?';
                $stmt = mysqli_prepare($con, $query_string);
                mysqli_stmt_bind_param($stmt, 'i', $delkey);
                $exec = mysqli_stmt_execute($stmt);
                if ($exec === FALSE) { echo "<p class='err-msg'>Subscriber could not be deleted: Subscriber may be linked to a project, see below. " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                elseif ( $wp_user ) 
                {
                    // delete wp_user only if user role is 'subscriber' when wp_user exists
                    if ( $wp_user->roles[0] == 'subscriber' ) 
                    {
                        wp_delete_user($wp_user->ID );
                        _lua($func, "Subscriber (ID: ". $delkey .", ". $wp_user->user_login .") deleted, and corresponding wp_user deleted.");
                    } else {
                        echo "<p class='err-msg'>The corresponding wp_user part of this subscriber has a role higher than subscriber and cannot be deleted.</p>";
                    }
                } // wp_user exists for deletion
                else { _lua($func, "Subscriber (ID: ". $delkey .", ". $row['sbscrbr_login'] .") deleted (but no wp_user deleted)."); }
                mysqli_stmt_close($stmt); 
            }
            else // there is at least one Sbscrbr/Prjct assignment
            {
                        echo "<p class='err-msg'>Subscriber has at least one project assigned to them. This Subscriber has not been deleted. Check <a href='#SbsPrj'>Subscriber-Project Pairing table</a> for more information.</p>";
            }
	    } // end deletion
    } // end checking for form submits


    // ************************************************************
    // 2. Create jQuery object holding all Subscribers
    // ************************************************************

    // Select correct database depending on server
    // $db = ( strpos($_SERVER['SERVER_NAME'], 'test.') === false )? 'cusconow_cms' : 'cn_test_cms';
    $db = 'groenp_sites_cms';

    // Create query to get all gp_subscribers that DON'T have admin panel access AND those that DO have admin panel access

    $query_string = 'SELECT gp.pk_sbscrbr_id, gp.sbscrbr_login, gp.is_usr_blocked, gp.gets_html_mail,';
    $query_string .= ' wp_fst.meta_value first, wp_lst.meta_value last, wp_loc.meta_value locale, wp.user_registered, wp.user_email, gp.sbscrbr_notes'; 
    $query_string .= ' FROM '. $db .'.gp_subscribers gp LEFT JOIN '.DB_NAME.'.wp_users wp ON ( wp.ID = gp.fr_ID )';
    $query_string .= ' LEFT JOIN '.DB_NAME.'.wp_usermeta wp_fst ON ( wp_fst.user_id = gp.fr_ID AND wp_fst.meta_key = \'first_name\')';
    $query_string .= ' LEFT JOIN '.DB_NAME.'.wp_usermeta wp_lst ON ( wp_lst.user_id = gp.fr_ID AND wp_lst.meta_key = \'last_name\')';
    $query_string .= ' LEFT JOIN '.DB_NAME.'.wp_usermeta wp_loc ON ( wp_loc.user_id = gp.fr_ID AND wp_loc.meta_key = \'locale\')';
    $query_string .= ' ORDER BY gp.sbscrbr_login;';

    // store in different temp array
    $sbscrbrs = array();
    //echo "query string: " . $query_string;
    $result = mysqli_query($con, $query_string);

    // BUG: handle empty result first for ALL occurrences
    if ( $result ) { 
        while($r = mysqli_fetch_assoc($result)) {
            $sbscrbrs[] = $r;
        }
        mysqli_free_result($result);
    }

    // Create javascript Object: Sbscrbrs
    echo "<script type='text/javascript'>

        var Sbscrbrs = "; echo json_encode($sbscrbrs); echo ";

    </script>";

    // ************************************************************
    //  3. Build updated table with SUBSCRIBERS
    // ************************************************************

    // Meta box introduction
    echo "<p>Subscribers are the representatives of our clients in this CMS. Subscribers are needed to create a subscription.</p>
    
    <p>If you create a subscriber with an email address all neccessary information and links will be created. The subscriber will receive an email with the secret password to their account.
    You can create a subscriber without administration panel access by leaving the email field empty.
    You can then create admin panel access separately in the Users section (on left menu). User accounts that have the same Username (user_login) as Subscriber ID can be linked. 
    This link can also be severed. 
    (When the subscriber doesn't know how to change their email address, you can sever the present email from their account, then create a new email in the Users section with the same username, 
    and then join the email account with the subscriber account again.)</p>

    <p>Entry fields marked with a '*' are mandatory. Entry fields marked with '†' are only saved when the Email address has been provided. 
    Subscribers cannot be deleted when they are assigned to one or more projects.</p>

    <p>Select Filter to obtain a list of subscribers according to the filter criteria. Most fields also accepts a '*' for any entry. 
    Any boolean field (with ? in label) accepts Y/N or '*' for both Y & N . Select Clear to remove any filtering, then select Filter to obtain full list.</p>";
    
    // Start of form
	echo "<form action='" . $form_url . "' method='post' enctype='multipart/form-data'>
            <input type='hidden' name='sure_" . $func . "' id='sure_" . $func . "' value='maybe' />"; // hidden input field to store RUsure? response
			
	// Start table build
    echo "<table id='sbscrbr_table' class='manage' style='width: 100%; table-layout: fixed; overflow: hidden; white-space: nowrap;'><thead style='text-align: left'>
            <tr style='text-align: left'>
                <th class='numb'>ID</th>
                <th>Subscriber ID</th>
                <th>Full name</th>
                <th>Email address</th>
                <th>Registered date</th>
                <th class='chck'>Locale</th>
                <th class='chck'>HTML format?</th>
                <th class='chck'>Blocked?</th>
                <th>Notes</th>
                <th>Action</th>
            </tr></thead><tbody>";

    // Build filter 
    echo "<tr>
        <td class='head'><input class='numb' type='text' value='" . dis($_POST['fltr_pk_sbscrbr_id'],'i') . "' name='fltr_pk_sbscrbr_id' id='fltr_pk_sbscrbr_id' pattern='\d*|\*' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_sbscrbr_login'],'a') . "' name='fltr_sbscrbr_login' id='fltr_sbscrbr_login' pattern='[a-zA-Z0-9_]+|\*' maxlength='60' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_sbscrbr_name'],'s%') . "' name='fltr_sbscrbr_name' id='fltr_sbscrbr_name' maxlength='200' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_user_email'],'a') . "' name='fltr_user_email' id='fltr_user_email' maxlength='100' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_user_registered'],'a') . "' name='fltr_user_registered' id='fltr_user_registered' maxlength='30' /></td>
        <td class='head'><input class='numb' type='text' value='" . dis($_POST['fltr_locale'],'s%') . "' name='fltr_locale' id='fltr_locale' pattern='[a-zA-Z0-9_]+|\*' maxlength='20' /></td>
        <td class='head'><input class='chk' type='text' value='" . dis($_POST['fltr_gets_html_mail'],'chk') . "' name='fltr_gets_html_mail' id='fltr_gets_html_mail' pattern='[YyNn\*]' maxlength='1' /></td>
        <td class='head'><input class='chk' type='text' value='" . dis($_POST['fltr_is_usr_blocked'],'chk') . "' name='fltr_is_usr_blocked' id='fltr_is_usr_blocked' pattern='[YyNn\*]' maxlength='1' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_sbscrbr_notes'],'s%') . "' name='fltr_sbscrbr_notes' id='fltr_sbscrbr_notes' maxlength='200' /></td>
        <td class='head'>
            <input type='button' class='button-primary' name='srch_". $func ."' value='Filter' onclick='build_fltrd_sbscrbrs_table(\"". $func ."\");'>
            <input type='button' class='button-secondary' name='clr_" . $func . "' value='Clear' onclick='clear_filter(\"sbscrbr_table\");'>
        </td>
    </tr>";

	// Subscribers table built by jQuery: build_fltrd_sbscrbrs_table(func);

    // finalize table
    echo "</tbody></table>";


    // ************************************************************
    // 4. Build add/edit form
    // ************************************************************

    // Retrieve edit row, if edit version of form
    if (isset($editkey))
    {
        $stmt = mysqli_prepare($con, 'SELECT fr_ID, sbscrbr_login, pk_sbscrbr_id, is_usr_blocked, gets_html_mail, sbscrbr_notes FROM gp_subscribers WHERE pk_sbscrbr_id=?');
        mysqli_stmt_bind_param($stmt, 'i', $editkey);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $editrow['fr_ID'], $editrow['sbscrbr_login'], $editrow['pk_sbscrbr_id'], $editrow['is_usr_blocked'], $editrow['gets_html_mail'], $editrow['sbscrbr_notes']);

        // Retrieve the subscriber data in the DB
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt); 

        // Get wp_user if subscriber connected thru fr_ID
        $wp_user = FALSE;
        if ( isset($editrow['fr_ID']) ) $wp_user = get_userdata($editrow['fr_ID']);

        //_log("edit row: "); _log($editrow);     // DEBUG // 
        //_log("wp_user: "); _log($wp_user);      // DEBUG // 
    }
    
    // Start rendering the form
    echo "<h4></a>Add or edit subscriber</h4>
    <p class='hor-form'>
        <a class='anchr' name=add_" . $func . "></a>";
    
        // If edit form keep edit key in hidden field and insert anchor: we need to scroll till here
        if(isset($editkey)) echo "<input type='hidden' name='edit_id' value=". $editkey . " > 
        <a class='anchr' name=" . $func . "></a>"; 

        // Get list of installed languages (except for default language)
        $langs = array_keys( wp_get_installed_translations('core')['default'] );
        $deflang = get_locale();
        // _log("default lang: ". $deflang);                                               // DEBUG //
        // _log("available langs: "); _log($langs);                                        // DEBUG //
                
        // If Edit populate each field with the present values
        if (isset($editkey)) echo "<label for='pk_sbscrbr_id'>ID</label><span id='pk_sbscrbr_id' name='pk_sbscrbr_id' class='display-only' >". dis($editrow['pk_sbscrbr_id'],'i') ."</span>";
        echo "<label for='user_login'>Subscriber ID (user_login) *</label><span>(use letters, digits, '_'s, has to be unique)</span><input type='text' name='user_login' id='user_login' maxlength='60' "; if(isset($editkey)) echo "value='" . dis($editrow['sbscrbr_login'],'a') . "' readonly='readonly' "; echo "/>
        <label for='user_email'>Email address</label><span>(leave empty for no panel access)</span><input type='email' name='user_email' id='user_email' maxlength='100' "; if(isset($editkey) && $wp_user) echo "value='" . $wp_user->user_email . "'"; if(isset($editkey)) echo " readonly='readonly' "; echo "/>";
        
        // If Edit place field with registration date and time if wp_user has been created as well (have to javascript:loc, so some inline script)
        if(isset($editkey) && $wp_user)
        {
            echo "<label for='user_registered'>CMS access registered</label><span>(in GMT)</span><span id='user_registered' name='user_registered' class='display-only' ></span>
            <script type='text/javascript'>
                $('#user_registered').text(\"" . $wp_user->user_registered . "\");
            </script>";
        }
        if ( isset($editkey) ) 
        {
            // edit: attach to/detach from wp_user, or there is an empty acct with no corresponding wp_user
            if ( !empty($editrow['fr_ID']) ) 
            {
                if ( !$wp_user ) // there is no wp_user part but reference still there (deleted outside cusconow U/I)
                {
                    // Remove fr_ID reference
                    $editrow['pk_sbscrbr_id'] = prep($editrow['pk_sbscrbr_id'],'i');
                    $stmt = mysqli_prepare($con, 'UPDATE LOW_PRIORITY gp_subscribers SET fr_ID = NULL WHERE pk_sbscrbr_id = ?');
                    mysqli_stmt_bind_param($stmt, 'i',$editrow['pk_sbscrbr_id']);
                    $exec = mysqli_stmt_execute($stmt);
                    if ($exec ===  FALSE) { echo "<span class='inline err-msg'>Invalid remove wp_user reference query for " . $func . ": " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</span>"; } 
                    else { 
                        echo "<span class='inline err-msg'>Old reference to wp_user removed. Re-edit this subscriber (Cancel and Edit again) to see possible corresponding wp_user.</span>"; 
                        _lua($func, "Subscriber (ID: ". $editrow['pk_sbscrbr_id'] .", ". $editrow['fr_ID'] .") wp_user has been deleted in WordPress U/I. Reference to wp_user removed."); 
                    } 
                    mysqli_stmt_close($stmt); 

                } else { // corresponding wp_user found; provide U/I to Delete wp_user part
                    echo "<label for=del_wpuser_" . $func . "'>Corresponding wp_user: " . $editrow['fr_ID'] . "</label><span>(ALL OTHER INPUT WILL BE IGNORED)</span><input type='submit' class='button-primary' name='del_wpuser_" . $func . "' onclick='confirm_deletion(\"sure_" . $func . "\");' value='Delete wp_user' style='width: 198px;'><br /><br />";
                }
            }
            if ( empty($editrow['fr_ID']) ) // other reference 
            {
                $user_id = FALSE;
                $user_id = username_exists( $editrow['sbscrbr_login'] );
                if ( $user_id ) echo "<label for=add_wpuser_" . $func . "'>Corresponding wp_user: " . $user_id . "</label><span>(ALL OTHER INPUT WILL BE IGNORED)</span><input type='submit' class='button-primary' name='add_wpuser_" . $func . "' onclick='confirm_attachment(\"sure_" . $func . "\");' value='Attach wp_user' style='width: 198px;'>";
            }
            if($wp_user) 
            {
                echo "<label for='gets_html_mail'>Receive in HTML format? *</label><span>(if user cannot read the mail, uncheck and mail will be sent in plain text)</span><input type='checkbox' name='gets_html_mail' id='gets_html_mail' ". dis($editrow['gets_html_mail'],'chk_ctrl') ."/><br />";
            }
            echo "<label for='is_usr_blocked'>Block subscriber temporarily?</label><input type='checkbox' name='is_usr_blocked' id='is_usr_blocked' ". dis($editrow['is_usr_blocked'],'chk_ctrl') ."/><br />";
        } 
        if ( !isset($editkey) ) 
        {
            echo "<label for='first_name'>First name †</label><span>(† only saved when Email provided)</span><input type='text' name='first_name' id='first_name' maxlength='200' />
            <label for='last_name'>Last name †</label><input type='text' name='last_name' id='last_name' maxlength='200' />
            <label for='locale'>Locale †</label><select class='prompt' name='locale' id='locale'>
                <option value='". $deflang ."' selected >Default: ". $deflang ."</option>";
                foreach($langs as $lng) {
                    echo "<option value='" . $lng . "'>" . $lng  ."</option>";
                }
            echo "</select>";  
        }
        elseif ( $wp_user )
        {
            $first = $wp_user->first_name;
            $last = $wp_user->last_name;
            $locale = get_user_locale( $wp_user->ID );
            $langs = array_keys( wp_get_installed_translations('core')['default'] );
            echo "<label for='first_name'>First name</label><input type='text' name='first_name' id='first_name' maxlength='200' value='" . dis($first,'s') . "' />
            <label for='last_name'>Last name</label><input type='text' name='last_name' id='last_name' maxlength='200' value='" . dis($last,'s') . "' />
            <label for='locale'>Locale</label><select class='prompt' name='locale' id='locale'>
                <option value=''"; if($locale == $deflang) echo " selected "; echo ">Default: ". $deflang ."</option>";
                foreach($langs as $lng) {
                    echo "<option value='" . $lng . "'"; if($locale == $lng) echo " selected "; echo ">" . $lng  ."</option>";
                }
            echo "</select>";
        }
        echo "<label for='sbscrbr_notes'>Notes</label><span>(max. 200 chars, only visible to administrator and managers, enter first and last name when no email)</span><textarea name='sbscrbr_notes' rows='4' cols='50'>". dis($editrow['sbscrbr_notes'],'s') ."</textarea>
    </p>

    <p class='button-row'>";
    if ( isset($editkey) )
    {
        // Edit form, so create Edit and Cancel buttons  
        echo "<input type='submit'  class='button-primary' name='edit_" . $func . "' value='Edit subscriber'> <input type='submit' class='button-secondary' name='cancel' value='Cancel'>";
    } else {
        // Normal (Add) form, so only Add button needed
        echo "<input type='submit'  class='button-primary' name='add_" . $func . "' value='Add subscriber'>";
    }
     echo "
    </p>
    </form>
    <p class='btt'><a href='#'>back to top</a></p>";

    // 5. Clean up
    mysqli_close($con);

	unset($result);
	unset($row);
	unset($editrow);
    unset($wp_user);
	unset($sbscrbrs);

}  // End: groenp_subscribers_meta_box_cb() 


// ****************************************************************
// Callback for PROJECTS Meta Box
// ****************************************************************
function groenp_projects_meta_box_cb()  
{  
    // open database
    $con = groenp_open_database();

    // ************************************************************
    // 1. Process any form data
    // ************************************************************

    // Make this form unique
    $func = 'Prjct';
    
    // If no Edit button pressed inside the table of this meta box
    if ( !array_search('Edit', $_POST) ) echo "<a class='anchr' name=" . $func . "></a>";  // Set anchor

    // default SSL port number; use https version, otherwise not
    $protocol = ($_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';

    // Create form url for this meta box
	$form_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '#' . $func;

    
    // ************************************************************
	if ( isset($_POST[('add_'. $func)]) || isset($_POST[('edit_'. $func)]) )  // THIS form has been submitted
    // ************************************************************
    {
		// Check if all mandatory fields have been entered
		if ( empty($_POST['prjct_name']) || empty($_POST['page_slug']) || empty($_POST['prjct_php']) || empty($_POST['base_url']) ) // empty($_POST['upload_dir']) left out: upload is optional, also test is optional
		{
            //_log("*** input error ***");
    		echo "<p class='err-msg'>All input fields marked with a '*' must be completed.</p>";
        }
        elseif ( ( isset($_POST['is_test_active']) && empty($_POST['test_url']) ) || ( !empty($_POST['upload_dir']) && isset($_POST['is_test_active']) && empty($_POST['test_upl_dir']) ) )
        {
            if ( isset($_POST['is_test_active']) && empty($_POST['test_url']) )
            {
                echo "<p class='err-msg'>Test version cannot be active without a test url.</p>";
            }
            if ( !empty($_POST['upload_dir']) && isset($_POST['is_test_active']) && empty($_POST['test_upl_dir']) )
            {
                echo "<p class='err-msg'>When upload directory is defined and test version is active, upload directory must be defined for test version as well.</p>";
            }
        }
        else {
                // define and sanitize vars
                $prjct_name       = prep($_POST['prjct_name'], 's');
                $page_slug        = prep($_POST['page_slug'], 's');
                $prjct_php        = prep($_POST['prjct_php'], 's');
                $base_url       = prep($_POST['base_url'], 's');
                $upload_dir     = prep($_POST['upload_dir'], 's');
                // $upload_dir     = ( isset($_POST['upload_dir']) )? prep($_POST['upload_dir'], 's') : 'NULL';
                $is_test_active = prep($_POST['is_test_active'], 'chk');
                $test_url       = prep($_POST['test_url'], 's');
                $test_upl_dir   = prep($_POST['test_upl_dir'], 's');

            // ************************************************************
		    if ( isset($_POST[('add_'. $func)]) ) // insert form data into tables
			// ************************************************************
			{
                // create a prepared statement 
                $query_string = 'INSERT INTO gp_projects ' .
                    '(prjct_name, page_slug, prjct_php, base_url, upload_dir, is_test_active, test_url, test_upl_dir) ' . 
                    'VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
                // _log("Add query for ". $func .": ". $query_string);                // DEBUG //
                $stmt = mysqli_prepare($con, $query_string);

                if ($stmt ===  FALSE) { _log("Invalid insertion query for " . $func . ": " . mysqli_error($con)); }
                else {
                    // bind stmt = i: integer, d: double, s: string
                    $bind = mysqli_stmt_bind_param($stmt, 'sssssiss', $prjct_name, $page_slug, $prjct_php, $base_url, $upload_dir, $is_test_active, $test_url, $test_upl_dir);
                    if ($bind ===  FALSE) { _log("Bind parameters failed for add query in " . $func); }
                    else {
                        // execute query 
                        $exec = mysqli_stmt_execute($stmt);
                        if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not add item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>";  }
                        else { _lua($func, "Project (ID: ". mysqli_insert_id($con) .", ". $prjct_name .") created."); }
                        mysqli_stmt_close($stmt); 
                    } // end of: binding successful
                } // end of: stmt prepared successful
			} // End of: add
			// ************************************************************
			elseif ( isset($_POST[('edit_'. $func)]) && isset($_POST['sure_'. $func]) && $_POST['sure_'. $func]!=='no')  // update tables row with editkey 
			// ************************************************************
			{
                // sanitize editkey
                $pk_prjct_id= prep($_POST['editkey'], 'i');

                // create a prepared statement 
                $query_string = 'UPDATE LOW_PRIORITY gp_projects SET ' .
                    'prjct_name = ?, page_slug = ?, prjct_php = ?, base_url = ?, upload_dir = ?, is_test_active = ?, test_url = ?, test_upl_dir = ? ' . 
                    'WHERE pk_prjct_id = ?';
                //_log("Edit query for ". $func .": ". $query_string);              // DEBUG //
                $stmt = mysqli_prepare($con, $query_string);

                if ($stmt ===  FALSE) { _log("Invalid update query for " . $func . ": " . mysqli_error($con)); }
                else {
                    // bind stmt = i: integer, d: double, s: string, b: blob and will be sent in packets
                    $bind = mysqli_stmt_bind_param($stmt, 'sssssissi', $prjct_name, $page_slug, $prjct_php, $base_url, $upload_dir, $is_test_active, $test_url, $test_upl_dir, $pk_prjct_id);
                    if ($bind ===  FALSE) { _log("Bind parameters failed for add query in " . $func); }
                    else {
                        // execute query 
                        $exec = mysqli_stmt_execute($stmt);
                        if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not update item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                        else { _lua($func, "Project (ID: ". $pk_prjct_id .", ". $prjct_name .") updated."); }
                        mysqli_stmt_close($stmt); 
                    } // end of: binding successful
                } // end of: stmt prepared successful
			} // End of: edit
		} // End of: not missing mandatory fields
    } // End of: add or edit (form submitted)

    // ************************************************************
    elseif (isset($_POST[('sure_'. $func)]))  // check for 'sure_'+function to see whether pushbutton used in THIS table or form
    // ************************************************************
    {
        $delkey = intval(array_search('Delete', $_POST));   // Maybe it's a Delete; check for id on 'Delete' button (case sensitive) and store it
        $editkey = intval(array_search('Edit', $_POST));    // Maybe it's an Edit; check for id on 'Edit' button (case sensitive) and store it
        if ( empty($editkey) ) unset($editkey);             // It's not an Edit, so unset otherwise the form will load as an edit

		// If there is a delete id and user answered 'Yes' on RUsure?
        if ( isset($delkey) && isset($_POST[$delkey]) && isset($_POST['sure_'. $func]) && $_POST['sure_'. $func]=='yes')
        {
            // First check whether Subscriber is not 'active', ie. assigned to one or more projects
            $stmt = mysqli_prepare($con, 'SELECT pk_sppair_id FROM gp_sbscrbr_prjct_pairings WHERE fk_prjct_id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $delkey);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            // record the number of hits, this can be zero, one or more
            $hits = mysqli_stmt_num_rows($stmt);
            mysqli_stmt_close($stmt); 
            
            if ( $hits == 0 )
            {
                // Create a prepared statement and delete row
                $query_string = 'DELETE LOW_PRIORITY FROM gp_projects WHERE pk_prjct_id = ?';
                $stmt = mysqli_prepare($con, $query_string);
                if ($stmt ===  FALSE) { _log("Invalid delete query for " . $func . ": " . mysqli_error($con)); }
                else {
                    $bind = mysqli_stmt_bind_param($stmt, 'i', $delkey);
                    $exec = mysqli_stmt_execute($stmt);
                    if ($exec ===  FALSE) { echo "<p class='err-msg'>Project could not be deleted. Project may be assigned to subscriber. See below: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                    else { _lua($func, "Project (ID: ". $delkey .") deleted."); }
                    mysqli_stmt_close($stmt); 
                } // end of: stmt prepared successful
            }
            else // there is at least one Sbscrbr/Prjct assignment
            {
                echo "<p class='err-msg'>Project has at least one Subscriber assigned to it. This Project has not been deleted. Check <a href='#SbsPrj'>Subscriber-Project Pairing table</a> for more information.</p>";
            }
	    } // End of: deletion
    } // End of: checking for form submits

    // ************************************************************
    // 2. Create jQuery object holding all Projects
    // ************************************************************

    // For now not used in this Meta Box, but use in Subscriber/Project Pairing
    // Create query for all Projects
    $query_string = 'SELECT pk_prjct_id, prjct_name, page_slug, prjct_php, base_url, upload_dir, is_test_active, test_url, test_upl_dir' .
                    ' FROM gp_projects ORDER BY prjct_name, page_slug;';

    // store in different temp array
    $projects = array();

    //echo "query string: " . $query_string;
    $result = mysqli_query($con, $query_string);

    // BUG: handle empty result first for ALL occurrences
    if ( $result ) { 
        while($r = mysqli_fetch_assoc($result)) {
            $projects[] = $r;
        }
        mysqli_free_result($result);
    }

    // Create javascript Object: Projects
    echo "<script type='text/javascript'>

        var Projects = "; echo json_encode($projects); echo ";

    </script>";

    // ************************************************************
    //  3. Build updated table with PROJECTS
    // ************************************************************

    // Meta box introduction
    echo "<p class='btt'><a href='#'>back to top</a></p>
    <p>Projects are the products (websites) that can be managed in this Sites Management tool. 
    The project name is only used as a label in this tool. The url is the domain link used by the users/customers of the subscriber. 
    It typically resides under the public_html directory on GoDaddy. Some products allow the upload of files into the cms. 
    For this purpose the directory path relative to the public_html can be stored.</p>
    
    <p>A project can have a test version. This version could serve two purposes:</p>
    <ol>
        <li>test new functionality as a stage in the development process. 
        For this purpose it will have a separate cms php file containing the meta boxes that will load in the test version of this tool.</li>
        <li>test new content in the live version of the product. 
        For this purpose the live cms php file will link to the test database for this product. 
        (This is the same database that the test version of the Sites Mgmt tool links to.)</li>
    </ol>
    <p>This interface controls subscriber access to the test version.</p>

    <p>If there is a test version of the product, it will reside in a different directory at GoDaddy which may mean a different relative upload directory. 
    This is stored in the Test upload directory.</p>

    <p>Projects cannot be deleted when they are assigned to one or more subscribers. Entry fields marked with a '*' are mandatory. Entry fields marked with '**' have to be unique as well.</p>";
    
    // Start of form
	echo "<form action='" . $form_url . "' method='post' enctype='multipart/form-data'><p>
            <input type='hidden' name='sure_" . $func . "' id='sure_" . $func . "' value='maybe' /></p>"; // hidden input field to store RUsure? response
			
	// Start table build
    echo "<table class='manage' style='width: 100%; table-layout: fixed; overflow: hidden; white-space: nowrap;'><thead style='text-align: left'>
            <tr style='text-align: left'>
                <th class='numb'>ID</th>
                <th>Project name</th>
                <th>Page slug</th>
                <th>Base url</th>
                <th>PHP filename</th>
                <th>Upload dir</th>
                <th class='chck'>Test active?</th>
                <th>Test url</th>
                <th>Action</th>
            </tr></thead><tbody>";


    // Query table, and leave out 'edit' row depending on action selection, and filter 
    unset($result); unset($row); // re-initialize
    if (isset($editkey))
    {
        // prepare statement excluding item to be edited
        $stmt = mysqli_prepare($con, 'SELECT pk_prjct_id, prjct_name, page_slug, prjct_php, base_url, upload_dir, is_test_active, test_url' .
                             ' FROM gp_projects WHERE pk_prjct_id != ? ORDER BY prjct_name, prjct_php');
        mysqli_stmt_bind_param($stmt, 'i', $editkey);
    } else {
        // prepare statement in similar way as edit, but no parameters
        $stmt = mysqli_prepare($con, 'SELECT pk_prjct_id, prjct_name, page_slug, prjct_php, base_url, upload_dir, is_test_active, test_url' .
                             ' FROM gp_projects ORDER BY prjct_name, prjct_php');
    } // End of: not set editkey

    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $row['pk_prjct_id'], $row['prjct_name'], $row['page_slug'], $row['prjct_php'], $row['base_url'], $row['upload_dir'], $row['is_test_active'], $row['test_url']);

    // Retrieve row by row the project data in the DB
    while ( mysqli_stmt_fetch($stmt) )
    {
        // Build row
        echo "<tr>
                <td class='numb'>" . dis($row['pk_prjct_id'],'i') . "</td>
                <td>" . dis($row['prjct_name'],'s') . "</td>
                <td>" . dis($row['page_slug'],'s') . "</td>
                <td>" . dis($row['base_url'],'s') . "</td>
                <td>" . dis($row['prjct_php'],'s') . "</td>
                <td>" . dis($row['upload_dir'],'s') . "</td>
                <td class='chck'>" . dis($row['is_test_active'],'chk') . "</td>
                <td>" . dis($row['test_url'],'s') . "</td>";

                // Add final cell with button section and link to javascript pop-up
                echo "<td><input type='submit' class='button-primary' name='" . $row['pk_prjct_id'] . "' value='Edit'> 
                            <input type='submit' class='button-secondary' name='" . $row['pk_prjct_id'] . "' onclick='confirm_deletion(\"sure_" . $func . "\");' value='Delete'></td>
                </tr>";
    } // End of: while result
    mysqli_stmt_close($stmt);

    // finalize table
    echo "</tbody></table>";


    // ************************************************************
    // 3. Build add/edit form
    // ************************************************************

    // Retrieve edit row, if edit version of form
    if ( isset($editkey) )
    {
        unset($stmt);
        $stmt = mysqli_prepare($con, 'SELECT prjct_name, page_slug, prjct_php, base_url, upload_dir, is_test_active, test_url, test_upl_dir' .
                             ' FROM gp_projects WHERE pk_prjct_id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $editkey);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $editrow['prjct_name'], $editrow['page_slug'], $editrow['prjct_php'], $editrow['base_url'], $editrow['upload_dir'], $editrow['is_test_active'], $editrow['test_url'], $editrow['test_upl_dir']);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt); 
    }
    
    // Start rendering the form
    echo "<h4>Add or edit project</h4>
    <p class='hor-form'>
        <a class='anchr' name=add_" . $func . "></a>";
    
        // If edit form keep edit key in hidden field and insert anchor: we need to scroll till here
        if(isset($editkey)) echo "<input type='hidden' name='editkey' value=". $editkey . " > 
        <a class='anchr' name=" . $func . "></a>"; 
        
        // If Edit populate each field with the present values
        echo "
        <label for='prjct_name'>Project name *</label><span>(max. 50 chars, only for this interface)</span><input type='text' name='prjct_name' id='prjct_name' maxlength='50' value='" . dis($editrow['prjct_name'],"s") . "' />
        <label for='page_slug'>Page slug **</label><span>(unique identifier for possible page)</span><input type='text' name='page_slug' id='page_slug' maxlength='20' value='" . dis($editrow['page_slug'],"s") . "' />
        <label for='prjct_php'>PHP file **</label><span>(filename in themes directory)</span><input type='text' name='prjct_php' id='prjct_php' maxlength='50' value='" . dis($editrow['prjct_php'],"s") . "' />
        <label for='base_url'>Base url *</label><span>(without protocol)</span><input type='text' name='base_url' id='base_url' maxlength='100' value='" . dis($editrow['base_url'],"s") . "' />
        <label for='upload_dir'>Upload directory</label><span>(relative from public_html)</span><input type='text' name='upload_dir' id='upload_dir' maxlength='100' value='" . dis($editrow['upload_dir'],"s") . "' />
        <label for='is_test_active'>Test version active?</label><input type='checkbox' name='is_test_active' id='is_test_active' " . dis($editrow['is_test_active'],"chk_ctrl") . "/><br />
        <label for='test_url'>Test url †</label><span>(† mandatory if test version is active)</span><input type='text' name='test_url' id='test_url' maxlength='100' value='" . dis($editrow['test_url'],"s") . "' />
        <label for='test_upl_dir'>Test upload directory †</label><span>(† mandatory if upload directory filled out and test version is active)</span><input type='text' name='test_upl_dir' id='test_upl_dir' maxlength='100' value='" . dis($editrow['test_upl_dir'],"s") . "' />
    </p>
    <p class='button-row'>";

    if ( isset($editkey) )
    {
        // Edit form, so create Edit and Cancel buttons  
        echo "<input type='submit'  class='button-primary' name='edit_" . $func . "' value='Edit project'> <input type='submit' class='button-secondary' name='cancel' value='Cancel'>";
    } else {
        // Normal (Add) form, so only Add button needed
        echo "<input type='submit'  class='button-primary' name='add_" . $func . "' value='Add project'>";
    }
     echo "
    </p>
    </form>
    <p class='btt'><a href='#'>back to top</a></p>";

    // 4. Clean up
    mysqli_close($con);
	unset($result);
	unset($row);
    unset($editrow);
	unset($projects);

}  // End: groenp_projects_meta_box_cb() 


// ****************************************************************
// Callback for SBSCRBR/PRJCT PAIRING Meta Box
// ****************************************************************
function groenp_subpro_pairing_meta_box_cb()  
{  
    // open database
    $con = groenp_open_database();

    // ************************************************************
    // 1. Process any form data
    // ************************************************************

    // Make this form unique
    $func = 'SbsPrj';
    
    // If no Edit button pressed inside the table of this meta box
    if ( !array_search('Edit', $_POST) ) echo "<a class='anchr' name=" . $func . "></a>";  // Set anchor

    // default SSL port number; use https version, otherwise not
    $protocol = ($_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';

    // Create form url for this meta box
	$form_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '#' . $func;

    
    // ************************************************************
	if ( isset($_POST[('add_'. $func)]) || isset($_POST[('edit_'. $func)]) )  // THIS form has been submitted
    // ************************************************************
    {
		// Check if all mandatory fields have been entered
		if ( empty($_POST['spp_prjct_id']) || empty($_POST['spp_sbscrbr_id'])  ) 
		{
            //_log("*** input error ***");
    		echo "<p class='err-msg'>All input fields marked with a '*' must be completed.</p>";
        }
        else {

            // define and sanitize vars
            $spp_prjct_id   = prep($_POST['spp_prjct_id'], 'i');
            $spp_sbscrbr_id = prep($_POST['spp_sbscrbr_id'], 'i');

            // Check if pair is unique
            $stmt = mysqli_prepare($con, 'SELECT pk_sppair_id' .
                             ' FROM gp_sbscrbr_prjct_pairings' .
                             ' WHERE fk_prjct_id=? AND fk_sbscrbr_id=?');
            if ($stmt ===  FALSE) { _log("Invalid compare uniqueness query query for " . $func . ": " . mysqli_error($con)); }
            else {
                // bind stmt = i: integer, d: double, s: string
                $bind = mysqli_stmt_bind_param($stmt, 'ii', $spp_prjct_id, $spp_sbscrbr_id);
                if ($bind ===  FALSE) { _log("Bind parameters failed for compare uniqueness query in " . $func); }
                else {
                    // execute query and record the number of hits; this can be zero, one or more
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_store_result($stmt);
                    $num_rows = mysqli_stmt_num_rows($stmt);
                    mysqli_stmt_close($stmt); 

                } // end of: binding successful
            } // end of: stmt prepared successful

            // ************************************************************
            if ( $num_rows > 0 ) // at least one more identical pair was found
            // ************************************************************
            {
                echo "<p class='err-msg'>There already is an identical pairing. This one has not been added or edited.</p>";
            }

            // ************************************************************
		    elseif ( isset($_POST[('add_'. $func)]) ) // insert form data into tables
			// ************************************************************
			{
                // create a prepared statement 
                $query_string = 'INSERT INTO gp_sbscrbr_prjct_pairings ' .
                    '(fk_prjct_id, fk_sbscrbr_id) ' . 
                    'VALUES (?, ?)';
                // _log("Add query for ". $func .": ". $query_string);                // DEBUG //
                $stmt = mysqli_prepare($con, $query_string);

                if ($stmt ===  FALSE) { _log("Invalid insertion query for " . $func . ": " . mysqli_error($con)); }
                else {
                    // bind stmt = i: integer, d: double, s: string
                    $bind = mysqli_stmt_bind_param($stmt, 'ii', $spp_prjct_id, $spp_sbscrbr_id);
                    if ($bind ===  FALSE) { _log("Bind parameters failed for add query in " . $func); }
                    else {
                        // execute query 
                        $exec = mysqli_stmt_execute($stmt);
                        if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not add item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                        else { _lua($func, "Sbscrbr/Prjct pairing (ID: ". mysqli_insert_id($con) .", Sbscrbr ID: ". $spp_sbscrbr_id .", Project ID: ". $spp_prjct_id .") created."); }
                        mysqli_stmt_close($stmt); 
                    } // end of: binding successful
                } // end of: stmt prepared successful
            } // End of: add
            
			// ************************************************************
			elseif ( isset($_POST[('edit_'. $func)]) && isset($_POST['sure_'. $func]) && $_POST['sure_'. $func]!=='no')  // update tables row with editkey 
			// ************************************************************
			{
                // sanitize editkey
                $pk_sppair_id= prep($_POST['editkey'], 'i');

                // create a prepared statement 
                $query_string = 'UPDATE LOW_PRIORITY gp_sbscrbr_prjct_pairings SET ' .
                    'fk_prjct_id = ?, fk_sbscrbr_id = ? WHERE pk_sppair_id = ?';
                //_log("Edit query for ". $func .": ". $query_string);              // DEBUG //
                $stmt = mysqli_prepare($con, $query_string);

                if ($stmt ===  FALSE) { _log("Invalid update query for " . $func . ": " . mysqli_error($con)); }
                else {
                    // bind stmt = i: integer, d: double, s: string, b: blob and will be sent in packets
                    $bind = mysqli_stmt_bind_param($stmt, 'iii', $spp_prjct_id, $spp_sbscrbr_id,  $pk_sppair_id);
                    if ($bind ===  FALSE) { _log("Bind parameters failed for add query in " . $func); }
                    else {
                        // execute query 
                        $exec = mysqli_stmt_execute($stmt);
                        if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not update item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                        else { _lua($func, "Sbscrbr/Prjct pairing (ID: ". $pk_sppair_id .", Sbscrbr ID: ". $spp_sbscrbr_id .", Project ID: ". $spp_prjct_id .") updated."); }
                        mysqli_stmt_close($stmt); 
                    } // end of: binding successful
                } // end of: stmt prepared successful
			} // End of: edit
		} // End of: not missing mandatory fields
    } // End of: add or edit (form submitted)

    // ************************************************************
    elseif (isset($_POST[('sure_'. $func)]))  // check for 'sure_'+function to see whether pushbutton used in THIS table or form
    // ************************************************************
    {
        $delkey = intval(array_search('Delete', $_POST));   // Maybe it's a Delete; check for id on 'Delete' button (case sensitive) and store it
        $editkey = intval(array_search('Edit', $_POST));    // Maybe it's an Edit; check for id on 'Edit' button (case sensitive) and store it
        if ( empty($editkey) ) unset($editkey);             // It's not an Edit, so unset otherwise the form will load as an edit

		// If there is a delete id and user answered 'Yes' on RUsure?
        if ( isset($delkey) && isset($_POST[$delkey]) && isset($_POST['sure_'. $func]) && $_POST['sure_'. $func]=='yes')
        {
            // Create a prepared statement and delete row
            $query_string = 'DELETE LOW_PRIORITY FROM gp_sbscrbr_prjct_pairings WHERE pk_sppair_id = ?';
            $stmt = mysqli_prepare($con, $query_string);
            if ($stmt ===  FALSE) { _log("Invalid delete query for " . $func . ": " . mysqli_error($con)); }
            else {
                $bind = mysqli_stmt_bind_param($stmt, 'i', $delkey);
                $exec = mysqli_stmt_execute($stmt);
                if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not delete item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                else { _lua($func, "Sbscrbr/Prjct pairing (ID: ". $delkey .") deleted."); }
                mysqli_stmt_close($stmt); 
            } // end of: stmt prepared successful
	    } // End of: deletion
    } // End of: checking for form submits
    
    // ************************************************************
    // 2. Create jQuery object holding all Subscriber/Project Pairings
    // ************************************************************

    // Sbscrbrs json object needed to fill out Add/Edit form:
    // has already been created in groenp_subscribers_meta_box_cb

    // Create query for all Projects
    $query_string = 'SELECT spp.pk_sppair_id, spp.fk_sbscrbr_id, sub.sbscrbr_login, sub.is_usr_blocked, spp.fk_prjct_id, prj.prjct_name, prj.prjct_php, prj.is_test_active' .
                    ' FROM gp_sbscrbr_prjct_pairings spp LEFT JOIN gp_projects prj ON (spp.fk_prjct_id = prj.pk_prjct_id)' .
                    ' LEFT JOIN gp_subscribers sub ON (spp.fk_sbscrbr_id = sub.pk_sbscrbr_id)' . 
                    ' ORDER BY prj.prjct_name, sub.sbscrbr_login';

    // store in different temp array
    $sub_proj_pairings = array();

    //echo "query string: " . $query_string;
    $result = mysqli_query($con, $query_string);

    // BUG: handle empty result first for ALL occurrences
    if ( $result ) { 
        while($r = mysqli_fetch_assoc($result)) {
            $sub_proj_pairings[] = $r;
        }
        mysqli_free_result($result);
    }

    // Create javascript Object: Projects
    echo "<script type='text/javascript'>

        var SbscrbrPrjctPrngs = "; echo json_encode($sub_proj_pairings); echo ";

    </script>";

    
    // ************************************************************
    //  3. Build updated table with SBSCRBR/PRJCT PAIRING
    // ************************************************************

    // Meta box introduction
    echo "<p class='btt'><a href='#'>back to top</a></p>
    <p>Subscribers are the representatives of the clients in this CMS. 
    Subscribers need to be linked to one of more projects in order to give CMS access to that project. 
    In order to create this link, the  subscriber and project need to be created first. 
    This is done through the other meta boxes on this page. 
    Only those subscribers with an email address will actually have access.</p>
    
    <p>All fields are mandatory.</p>";
    
    // Start of form
	echo "<form action='" . $form_url . "' method='post' enctype='multipart/form-data'><p>
            <input type='hidden' name='sure_" . $func . "' id='sure_" . $func . "' value='maybe' /></p>"; // hidden input field to store RUsure? response
			
	// Start table build
    echo "<table id='sppair_tbl' class='manage' style='width: 100%; table-layout: fixed; overflow: hidden; white-space: nowrap;'>
        <thead style='text-align: left'>
            <tr style='text-align: left'>
                <th class='numb'>Prjct ID</th>
                <th>Project name</th>
                <th class='chck'>Test enabled?</th>
                <th>Subscriber ID</th>
                <th class='chck'>Blocked?</th>
                <th>Full name</th>
                <th>Email</th>
                <th>Notes</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>";

    // Build filter 
    echo "<tr>
        <td class='head'><input class='numb' type='text' value='" . dis($_POST['fltr_spp_fk_prjct_id'],'i') . "' name='fltr_spp_fk_prjct_id' id='fltr_spp_fk_prjct_id' pattern='\d*|\*' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_spp_prjct_name'],'a') . "' name='fltr_spp_prjct_name' id='fltr_spp_prjct_name' maxlength='100' /></td>
        <td class='head'><input class='chk' type='text' value='" . dis($_POST['fltr_spp_is_test_active'],'chk') . "' name='fltr_spp_is_test_active' id='fltr_spp_is_test_active' pattern='[YyNn\*]' maxlength='1' /></td>

        <td class='head'><input type='text' value='" . dis($_POST['fltr_spp_sbscrbr_login'],'a') . "' name='fltr_spp_sbscrbr_login' id='fltr_spp_sbscrbr_login' pattern='[a-zA-Z0-9_]+|\*' maxlength='60' /></td>
        <td class='head'><input class='chk' type='text' value='" . dis($_POST['fltr_spp_is_usr_blocked'],'chk') . "' name='fltr_spp_is_usr_blocked' id='fltr_spp_is_usr_blocked' pattern='[YyNn\*]' maxlength='1' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_spp_sbscrbr_name'],'s%') . "' name='fltr_spp_sbscrbr_name' id='fltr_spp_sbscrbr_name' maxlength='200' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_spp_user_email'],'a') . "' name='fltr_spp_user_email' id='fltr_spp_user_email' maxlength='100' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_spp_sbscrbr_notes'],'s%') . "' name='fltr_spp_sbscrbr_notes' id='fltr_spp_sbscrbr_notes' maxlength='200' /></td>
        <td class='head'>
            <input type='button' class='button-primary' name='srch_". $func ."' value='Filter' onclick='build_fltrd_spp_table(\"". $func ."\");'>
            <input type='button' class='button-secondary' name='clr_" . $func . "' value='Clear' onclick='clear_filter(\"sppair_tbl\");'>
        </td>
    </tr>";

	// Subscribers table built by jQuery: build_fltrd_sbscrbrs_table(func);

    // finalize table
    echo "</tbody></table>";

    
    // // Query table, and leave out 'edit' row depending on action selection, and filter 
    // unset($result); unset($row); // re-initialize
    // if (isset($editkey))
    // {
    //     // prepare statement excluding item to be edited
    //     $stmt = mysqli_prepare($con, 'SELECT spp.pk_sppair_id, spp.fk_sbscrbr_id, sub.sbscrbr_login, sub.is_usr_blocked, spp.fk_prjct_id, prj.prjct_name, prj.is_test_active' .
    //                          ' FROM gp_sbscrbr_prjct_pairings spp LEFT JOIN gp_projects prj ON (spp.fk_prjct_id = prj.pk_prjct_id)' .
    //                          ' LEFT JOIN gp_subscribers sub ON (spp.fk_sbscrbr_id = sub.pk_sbscrbr_id)' . 
    //                          ' WHERE spp.pk_sppair_id != ?'.
    //                          ' ORDER BY prj.prjct_name, sub.sbscrbr_login');
    //     mysqli_stmt_bind_param($stmt, 'i', $editkey);
    // } 
    // else 
    // {
    //     // prepare statement in similar way as edit, but no parameters
    //     $stmt = mysqli_prepare($con, 'SELECT spp.pk_sppair_id, spp.fk_sbscrbr_id, sub.sbscrbr_login, sub.is_usr_blocked, spp.fk_prjct_id, prj.prjct_name, prj.is_test_active' .
    //                          ' FROM gp_sbscrbr_prjct_pairings spp LEFT JOIN gp_projects prj ON (spp.fk_prjct_id = prj.pk_prjct_id)' .
    //                          ' LEFT JOIN gp_subscribers sub ON (spp.fk_sbscrbr_id = sub.pk_sbscrbr_id)' . 
    //                          ' ORDER BY prj.prjct_name, sub.sbscrbr_login');
    // } // End of: not set editkey

    // mysqli_stmt_execute($stmt);
    // mysqli_stmt_bind_result($stmt, $row['pk_sppair_id'], $row['pk_sbscrbr_id'], $row['sbscrbr_login'], $row['is_usr_blocked'], $row['fk_prjct_id'], $row['prjct_name'], $row['is_test_active']);

    // // Retrieve row by row the project data in the DB
    // while ( mysqli_stmt_fetch($stmt) )
    // {
    //     // Build row
    //     echo "<tr>
    //             <td>" . dis($row['prjct_name'],'s') . "</td>
    //             <td class='chck'>" . dis($row['is_test_active'],'chk') . "</td>
    //             <td>" . dis($row['sbscrbr_login'],'s') . "</td>
    //             <td class='chck'>" . dis($row['is_usr_blocked'],'chk') . "</td>
    //             <td>" . dis($row['pk_sbscrbr_id'],'i') . "</td>
    //             <td></td>
    //             <td></td>";

    //             // Add final cell with button section and link to javascript pop-up
    //             echo "<td><input type='submit' class='button-primary' name='" . $row['pk_sppair_id'] . "' value='Edit'> 
    //                         <input type='submit' class='button-secondary' name='" . $row['pk_sppair_id'] . "' onclick='confirm_deletion(\"sure_" . $func . "\");' value='Delete'></td>
    //             </tr>";
    // } // End of: while result
    // mysqli_stmt_close($stmt);

    // // finalize table
    // echo "</tbody>
    // </table>";


    // ************************************************************
    // 3. Build add/edit form
    // ************************************************************

    // Retrieve edit row, if edit version of form
    if ( isset($editkey) )
    {
        unset($stmt);
        $stmt = mysqli_prepare($con, 'SELECT spp.pk_sppair_id, spp.fk_sbscrbr_id, sub.sbscrbr_login, sub.is_usr_blocked, spp.fk_prjct_id, prj.prjct_name, prj.is_test_active' .
                             ' FROM gp_sbscrbr_prjct_pairings spp LEFT JOIN gp_projects prj ON (spp.fk_prjct_id = prj.pk_prjct_id)' .
                             ' LEFT JOIN gp_subscribers sub ON (spp.fk_sbscrbr_id = sub.pk_sbscrbr_id)' . 
                             ' WHERE spp.pk_sppair_id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $editkey);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $editrow['pk_sppair_id'], $editrow['fk_sbscrbr_id'], $editrow['sbscrbr_login'], $editrow['is_usr_blocked'], $editrow['fk_prjct_id'], $editrow['prjct_name'], $editrow['is_test_active']);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt); 
    }
    
    // Start rendering the form
    echo "<h4></a>Add or edit subscriber/project pairing</h4>
    <p class='hor-form'>
        <a class='anchr' name=add_" . $func . "></a>";
    
        // If edit form keep edit key in hidden field and insert anchor: we need to scroll till here
        if(isset($editkey)) echo "<input type='hidden' name='editkey' value=". $editkey . " > 
        <a class='anchr' name=" . $func . "></a>"; 
        
        // If Edit populate each field with the present values
        echo "
        <label for='spp_dis_prjct_id'>Project ID</label><span class='display-only' id='spp_dis_prjct_id' name='spp_dis_prjct_id' ></span>
        <label for='spp_prjct_id'>Project name *</label><select class='prompt' name='spp_prjct_id' id='spp_prjct_id'>
            <option value=''"; if( !isset($editkey) ) echo " selected "; echo ">Select project:</option>";

        unset($result); unset($row); // re-initialize
    
        //  query all projects (projects can be assigned to several subscribers, so should always be available)
        $result = mysqli_query($con, 'SELECT pk_prjct_id, prjct_name, is_test_active FROM gp_projects ORDER BY prjct_name, pk_prjct_id;');
        while($row = mysqli_fetch_array($result)) 
        { 
            echo "<option value='" . $row['pk_prjct_id'] . "' data-id='". $row['pk_prjct_id'] . "' data-active='" . $row['is_test_active']  . "'"; 
            if(isset($editkey) && ($editrow['fk_prjct_id']==$row['pk_prjct_id'])) echo " selected "; 
            echo ">" . $row['prjct_name'] ."</option>";
        }
        mysqli_free_result($result);

        echo "</select>
        <label for='spp_is_test_active'>Test version active?</label><span class='display-only' id='spp_is_test_active' name='spp_is_test_active' ></span>
    </p>

        <div id='sbscrbr_display'>
            <p class='hor-form'>
                <input type='hidden' id='spp_sbscrbr_id' name='spp_sbscrbr_id' value='"; if(isset($editkey)) echo  $editrow['fk_sbscrbr_id']; echo "' >
                <label for='spp_sbscrbr_login'>Subscriber ID *</label><input type='button' class='button-primary' value='Change' onclick='show_sbscrbr_filter();' style='' />
                <input type='text' id='spp_sbscrbr_login' name='spp_sbscrbr_login' value='"; if(isset($editkey)) echo  $editrow['sbscrbr_login']; echo "' readonly='readonly' />
            </p>
        </div>
        <div id='sbscrbr_filter' style='display: none;'>
            <p class='hor-form'>
                <span id='err1_fltr_sbscrbr_login' class='err-msg' style='display:none;'>Please type at least 3 sequential characters (letters, digits, '_'s) of the ID</span>
                <span id='err2_fltr_sbscrbr_login' class='err-msg' style='display:none;'>There are no Subscriber IDs with this text pattern</span>
                <label for='test_spp_sbscrbr_login'>Type new Subscriber ID, then select Check (3 chars min.)</label>
                <input type='button' class='button-secondary' name='check_subscbr' value='Check' onclick='filter_sbscrbrs();' />
                <input type='text' id='test_spp_sbscrbr_login' name='test_spp_sbscrbr_login' maxlength='60' pattern='[a-zA-Z0-9_]{3}[a-zA-Z0-9_]+' />
            </p>
        </div>
        <div id='fltr_sbscrbr_results' style='display: none;'>
            <p class='hor-form'>
                <label for='sbscrbr_logins'><span class='multi_res'>Select one subscriber ID from the list, then </span>Choose Select to set as Owner</label>
                <input type='button' class='button-primary' name='select_subscbr' value='Select' onclick='select_sbscrbr();' />
                <select class='multi_res' name='sbscrbr_logins[]' id='sbscrbr_logins' multiple='true' size='5' onchange='show_sbscrbr_det();'>
                </select>
            </p>
        </div>
        <div id='sbscrbr_details' style='display: none;'>
            <p class='hor-form' >
                <label class='wp_registered' for='spp_user_registered'>CMS access registered</label><span class='wp_registered display-only' id='spp_user_registered' name='spp_user_registered' ></span>
                <label class='wp_registered' for='spp_sbscrbr_name'>Name</label><span class='wp_registered display-only' id='spp_sbscrbr_name' name='spp_sbscrbr_name' ></span>
                <label class='wp_registered' for='spp_user_email'>Email</label><span class='wp_registered display-only' id='spp_user_email' name='spp_user_email' ></span>
                <label for='spp_sbscrbr_notes'>Notes</label><span class='display-only' id='spp_sbscrbr_notes' name='spp_sbscrbr_notes' style='min-height:80px;'></span>
            </p>
        </div>

    <p class='button-row'>";

    if ( isset($editkey) )
    {
        // Edit form, so create Edit and Cancel buttons  
        echo "<input type='submit'  class='button-primary' name='edit_" . $func . "' value='Edit pairing'> <input type='submit' class='button-secondary' name='cancel' value='Cancel'>";
    } else {
        // Normal (Add) form, so only Add button needed
        echo "<input type='submit'  class='button-primary' name='add_" . $func . "' value='Add pairing'>";
    }
     echo "
    </p>
    </form>
    <p class='btt'><a href='#'>back to top</a></p>";

    // 4. Clean up
    mysqli_close($con);
	unset($result);
	unset($row);
	unset($editrow);
	unset($sub_proj_pairings);

}  // End: groenp_subpro_pairing_meta_box_cb() 

?>