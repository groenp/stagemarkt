<?php
/******************************************************************************/
/*                                                              Pieter Groen  */
/*  Version 0.1 - May 23, 2020 (conversion from cusconow)                     */
/*                                                                            */
/*  PHP for Groen Productions Sites Mgmt CMS in WordPress:                    */
/*   - subscriber management                (~line  125)                      */
/*   - domain management                    (~line  560)                      */
/*                                                                            */
/******************************************************************************/


/******************************************************************************/
/*  Groen Productions - Add Subscribers page to the WordPress Dashboard       */
/******************************************************************************/

// ****************************************************************
// Define this screen globally: 'groenp_subscribers'
// ****************************************************************
$struct_screen = 'groenp_subscribers';

// ****************************************************************
// Add callbacks for this screen only
// ****************************************************************
add_action('load-dashboard_page_' . $struct_screen, 'groenp_insert_subscribers_meta_boxes');
add_action('admin_footer-dashboard_page_' . $struct_screen,'groenp_print_script_in_footer');

function groenp_insert_subscribers_meta_boxes() {
 
    //global $struct_screen;
    $struct_screen = 'groenp_subscribers';

    /* Trigger the add_meta_boxes hooks to allow meta boxes to be added */
    do_action('add_meta_boxes_' . $struct_screen, null);
    do_action('add_meta_boxes'  , $struct_screen, null);
 
    /* Enqueue WordPress' script for handling the meta boxes */
    wp_enqueue_script('postbox');
 
    /* Add screen option: user can choose between 1 or 2 columns (default 1) */
    add_screen_option('layout_columns', array('max' => 2, 'default' => 1) );
}
 
/* Create page content */
function groenp_register_subscribers_submenu_page() {
	add_submenu_page( 'index.php', 'Subscribers', 'GP: Subscribers', 'create_users', 'groenp_subscribers', 'groenp_subscribers_page_cb' ); 
}
add_action('admin_menu', 'groenp_register_subscribers_submenu_page');

function groenp_subscribers_page_cb() {
	
    //global $struct_screen;
    $struct_screen = 'groenp_subscribers';

    echo "<div class='wrap'>
        <h2>Sites Management - Subscribers and their Domains</h2>";
 
        /* Used to save closed meta boxes and their order. 
		   This is not working, because a form will interfere with the other forms in the Groen Productions Meta Boxes. */

		echo "<form method='post'>";
        wp_nonce_field($struct_screen);
        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
        wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		echo "</form>";

 
        echo "<div id='poststuff'>
 
            <div id='post-body' class='metabox-holder columns-1'>
                <!-- #post-body-content -->
                <div class='postbox' id='post-body-content'>

                    <div class='inside'>
                        <h3>This page supports the following tasks:</h3>
                        <ul>
                            <li><a href='#Sbscbr'>Manage Subscribers</a></li>
                            <li><a href='#add_Sbscbr'>Add or edit Subscriber</a></li>
                            <li><a href='#Domain'>Manage Domains</a></li>
                            <li><a href='#add_Domain'>Add or edit Domain</a></li>
                        </ul>
                    </div>

                </div>

                <div id='postbox-container-1' class='postbox-container'>

                    <!-- all metaboxes go into the first column -->";
                    do_meta_boxes($struct_screen, 'normal', null);
                echo "</div>

                <div id='postbox-container-2' class='postbox-container'>
                    <!-- second column stays empty -->
                    <div class='meta-box-sortables ui-sortable empty-container' id='side-sortables'></div>
                </div>
 
            </div> <!-- #post-body --> 
 
        </div> <!-- #poststuff -->";
 
//		echo "</form>";
 
    echo "</div>  <!-- wrap -->";

}


/******************************************************************************/
/* Groen Productions - Create Meta Boxes for Subscribers page of MySQL DBs             */
/*                                                                            */
/******************************************************************************/

function groenp_subscribers_meta_boxes_add() 
{
    //global $struct_screen;
    $struct_screen = 'groenp_subscribers';
    // add_meta_box( 'cus-subscribers-mb', 'CuscoNow - Manage subscribers',    'groenp_subscribers_meta_box_cb', $struct_screen, 'normal' );
    // add_meta_box( 'cus-establishments-mb', 'CuscoNow - Manage establishments and their owners (subscribers)', 'groenp_establishments_meta_box_cb', $struct_screen, 'normal' );
}
add_action('add_meta_boxes_' . $struct_screen, 'groenp_subscribers_meta_boxes_add');


// ****************************************************************
// Callback for SUBSCRIBERS Meta Box
// ****************************************************************
function groenp_subscribers_meta_box_cb()  
{  
    // open database
    $gpcon = groenp_open_database();

    // ************************************************************
    // 1. Process any form data
    // ************************************************************

    // Make this form unique
	$func = 'Sbscbr';
    
    // If no Edit button pressed inside the table of this meta box
    if ( !array_search('Edit', $_POST) ) echo "<a name=" . $func . "></a>";  // Set anchor

    // Create form url for this meta box
	$form_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "#" . $func;

    // DEBUG section
	// All form fields (name and id) have the same name as the column name of their counterpart in the database
	// lang specific fields have DB name and "_<lng>" appended to it
    //_log("===> START CHECKING MANAGEMENT FORM for: " . $func ); // DEBUG //
	//_log("form url will be index.php with: " . $form_url );     // DEBUG // 
    //_log("For url: "); _log($screen);                           // DEBUG // 
 	//_log("POST: "); _log($_POST); // form fields //             // DEBUG // 
 	//_log($_FILES);                // pics //                    // DEBUG // 

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
            $user_login     = prep($_POST['user_login'], "a");
            $sbscrbr_notes  = prep($_POST['sbscrbr_notes'], "s");
            $is_usr_blocked = prep($_POST['is_usr_blocked'], "chk");
            $gets_sub_mails = prep($_POST['gets_sub_mails'], "chk");
            $gets_html_mail = prep($_POST['gets_html_mail'], "chk");
            $pk_sbscrbr_id  = prep($_POST['edit_id'],"i");

			// ************************************************************
		    if ( isset($_POST[('add_'. $func)]) ) // insert form data into tables
			// ************************************************************
			{
                // Subscriber ID (user_login) has to be unique
                $stmt = mysqli_prepare($con, "SELECT sbscrbr_login FROM subscribers WHERE sbscrbr_login=?");
                mysqli_stmt_bind_param($stmt, "s", $user_login);
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
                                'last_name' => $_POST['last_name'])
                        );
                        // Mail the user with the new ID and pwd
                        //cusconow_plain_mail(   $user_id, "new_user_notification", "Bienvenido a CuscoNow!", "Detalles de registro", NULL, $password);
                        //cusconow_html_mail(   $user_id, "new_user_notification", "Bienvenido a CuscoNow!", "Detalles de registro", NULL, $password);
                        cusconow_multipart_mail($user_id, "new_user_notification", "Bienvenido a CuscoNow!", "Detalles de registro", NULL, $password);
                        //wp_mail( $_POST['user_email'], 'Bienvenido a CuscoNow!', 'Your loginID: '. $_POST['user_login'] . ', Your Password: ' . $password );

                        if ( is_wp_error( $user_id ) ) { // There was an error updating the wp_user table...
                            echo "<p class='err-msg'>Created a new wp_user, but could not update that user with all form data. Try to edit the subscriber from the list.</p>";
                        };
                        // We're doing swell, now create subscriber and link to wp_user
                        $query_string = "INSERT INTO subscribers " .
                            "(fr_ID, sbscrbr_login, is_usr_blocked, gets_sub_mails, gets_html_mail, sbscrbr_notes) " . 
                            "VALUES (?, ?, 0, 1, 1, ?)";
                        $stmt = mysqli_prepare($con, $query_string);

                        if ($stmt ===  FALSE) { _log("Invalid insertion query for " . $func . ": " . mysqli_error($con)); }
                        else {
                            // bind stmt = i: integer, d: double, s: string
                            $bind = mysqli_stmt_bind_param($stmt, "iss", $user_id, $user_login, $sbscrbr_notes);
                            if ($bind ===  FALSE) { _log("Bind parameters failed for add query (".$lng.") in " . $func); }
                            else {
                                // execute query 
                                $exec = mysqli_stmt_execute($stmt);
                                if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not add item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                                else { _lua($func, "Subscriber (ID:". mysqli_insert_id($con) .", ". $user_login . ", email: ". $_POST['user_email'] .") with panel access created."); }
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
                        $query_string = "INSERT INTO subscribers " .
                            "(fr_ID, sbscrbr_login, is_usr_blocked, gets_sub_mails, gets_html_mail, sbscrbr_notes) " . 
                            "VALUES (NULL, ?, 0, NULL, NULL, ?)";
                        $stmt = mysqli_prepare($con, $query_string);

                        if ($stmt ===  FALSE) { _log("Invalid insertion query for " . $func . ": " . mysqli_error($con)); }
                        else {
                            // bind stmt = i: integer, d: double, s: string
                            $bind = mysqli_stmt_bind_param($stmt, "ss", $user_login, $sbscrbr_notes);
                            if ($bind ===  FALSE) { _log("Bind parameters failed for add query (".$lng.") in " . $func); }
                            else {
                                // execute query 
                                $exec = mysqli_stmt_execute($stmt);
                                if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not add item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                                else { _lua($func, "Subscriber (ID:". mysqli_insert_id($con) .", ". $user_login .") - no panel access - created."); }
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
                $query_string = "UPDATE LOW_PRIORITY subscribers SET " .
                    "is_usr_blocked = ?, gets_sub_mails = ?, gets_html_mail = ?, sbscrbr_notes = ? " . 
                    "WHERE pk_sbscrbr_id = ?";
                //_log("Edit query for ". $func .": ". $query_string);              // DEBUG //
                $stmt = mysqli_prepare($con, $query_string);

                if ($stmt ===  FALSE) { _log("Invalid update query for " . $func . ": " . mysqli_error($con)); } 
                else { 
                    // bind stmt = i: integer, d: double, s: string
                    $bind = mysqli_stmt_bind_param($stmt, "iiisi", $is_usr_blocked, $gets_sub_mails, $gets_html_mail, $sbscrbr_notes, $pk_sbscrbr_id);
                    if ($bind ===  FALSE) { _log("Bind parameters failed for add query in " . $func); }
                    else {
                        // execute query 
                        $exec = mysqli_stmt_execute($stmt);
                        if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not update item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                        else { _lua($func, "Subscriber (ID:". $pk_sbscrbr_id .", ". $user_login .") updated."); }
                        mysqli_stmt_close($stmt); 
                    } // end of: binding successful
                } // end of: stmt prepared successful

                // Update wp_user if present
                $stmt = mysqli_prepare($con, "SELECT fr_ID FROM subscribers WHERE pk_sbscrbr_id=?");
                mysqli_stmt_bind_param($stmt, "s", $pk_sbscrbr_id);
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
                        'last_name' => $_POST['last_name'])
                    );
                    _lua($func, "Subscriber (ID:". $pk_sbscrbr_id .", ". $user_login .") wp_user part edit successful? (no answer means yes)"); 
                }
                if ( is_wp_error( $row['fr_ID'] ) ) { // There was an error updating the wp_user table...
                    echo "<p class='err-msg'>Could not update the WordPress section of the subscriber with all form data. Try to edit the subscriber in Users.</p>";
                    _lua($func, "Subscriber (ID:". $pk_sbscrbr_id .", ". $user_login .") wp_user part edit failed!"); 
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
            $stmt = mysqli_prepare($con, "SELECT fr_ID, sbscrbr_login FROM subscribers WHERE pk_sbscrbr_id=?");
            mysqli_stmt_bind_param($stmt, "i", $sbscrbr_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $row['fr_ID'], $row['sbscrbr_login']);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt); 
            //_log("UserID in CN: " . $delkey);               // DEBUG //
            //_log("UserID in wp: " . $row['fr_ID']);         // DEBUG //

            if ( !empty($row['fr_ID']) ) $wp_user = get_userdata($row['fr_ID']);

        } else { 
            // No result through fr_ID (must be action to attach then), let's go through user_login and edit_id
            if ( !empty($_POST['edit_id']) ) $sbscrbr_id = prep($_POST['edit_id'],"i");
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
                $query_string = "UPDATE LOW_PRIORITY subscribers SET fr_ID = NULL, gets_sub_mails = NULL, gets_html_mail = NULL WHERE pk_sbscrbr_id = ?";
				//_log("Remove wp_user query string for " . $func . ": " . $query_string);			// DEBUG //
                $stmt = mysqli_prepare($con, $query_string);
                $bind = mysqli_stmt_bind_param($stmt, "i", $sbscrbr_id);
                $exec = mysqli_stmt_execute($stmt);
                if ($exec ===  FALSE) { echo "<p class='err-msg'>Invalid remove wp_user query for " . $func . ": " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                else { _lua($func, "Subscriber (ID:". $sbscrbr_id .", ". $row['sbscrbr_login'] .") wp_user section removed."); }
                mysqli_stmt_close($stmt); 

                if ( $wp_user->roles[0] == "subscriber")
                {
                    wp_delete_user($wp_user->ID);
                   _log("wp_user (ID:" . $wp_user->ID . ") deleted.");						// DEBUG //
                   _lua($func, "Subscriber's wp_user (ID:". $wp_user->ID .", ". $wp_user->user_login .") deleted.");
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
                $query_string = "UPDATE LOW_PRIORITY subscribers SET fr_ID = ?, gets_sub_mails = 1, gets_html_mail = 1 WHERE pk_sbscrbr_id = ?";
				//_log("Attach wp_user query string for " . $func . ": " . $query_string);			// DEBUG //
                $stmt = mysqli_prepare($con, $query_string);
                mysqli_stmt_bind_param($stmt, "ii", $wp_user->ID, $sbscrbr_id);
                $exec = mysqli_stmt_execute($stmt);
                if ($exec ===  FALSE) { echo "<p class='err-msg'>Invalid attach wp_user query for " . $func . ": " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                else { _lua($func, "Subscriber (ID:". $sbscrbr_id .", ". $wp_user->user_login .") wp_user section added."); }
                mysqli_stmt_close($stmt); 
            } else {
                echo "<p class='err-msg'>Could not find the subscriber (whose wp_user with corresponding login ID needs to be attached) through its ID: " . $_POST['edit_id'] . "</p>";
            }
        }  // end attachment of wp_user part

		// [3] Maybe there is a delete id and user answered 'Yes' on RUsure?
        elseif ( isset($_POST[$delkey]) && isset($_POST['sure_'. $func]) && $_POST['sure_'. $func]=='yes')
        {
            // Delete all rows in table with id= delkey
            $query_string = "DELETE LOW_PRIORITY FROM subscribers WHERE pk_sbscrbr_id = ?";
            $stmt = mysqli_prepare($con, $query_string);
            mysqli_stmt_bind_param($stmt, "i", $delkey);
            $exec = mysqli_stmt_execute($stmt);
            if ($exec === FALSE) { echo "<p class='err-msg'>Subscriber could not be deleted: Subscriber may own an establishment, see below. " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
            elseif ( $wp_user ) 
            {
                // delete wp_user only if user role is "subscriber" when wp_user exists
                if ( $wp_user->roles[0] == "subscriber" ) 
                {
                    wp_delete_user($wp_user->ID );
                    _lua($func, "Subscriber (ID:". $delkey .", ". $wp_user->user_login .") deleted, and corresponding wp_user deleted.");
                } else {
                    echo "<p class='err-msg'>The corresponding wp_user part of this subscriber has a role higher than subscriber and cannot be deleted.</p>";
                }
            } // wp_user exists for deletion
            else { _lua($func, "Subscriber (ID:". $delkey .", ". $row['sbscrbr_login'] .") deleted (but no wp_user deleted)."); }
            mysqli_stmt_close($stmt); 
	    } // end deletion
    } // end checking for form submits


    // ************************************************************
    // 2. Create jQuery object holding all Subscribers
    // ************************************************************

    // Select correct database depending on server
    // $db = ( strpos($_SERVER['SERVER_NAME'], "test.") === false )? "cusconow_cms" : "cn_test_cms";
    $db = "groenp_sites_cms";

    // Create query to get all subscribers that DON'T have admin panel access AND those that DO have admin panel access

    $query_string = "SELECT gp.pk_sbscrbr_id, gp.sbscrbr_login, gp.is_usr_blocked, gp.gets_sub_mails, gp.gets_html_mail,";
    $query_string .= " wp_fst.meta_value first, wp_lst.meta_value last, wp.user_registered, wp.user_email, gp.sbscrbr_notes"; 
    $query_string .= " FROM ". $db .".subscribers gp LEFT JOIN ".DB_NAME.".wp_users wp ON ( wp.ID = gp.fr_ID )";
    $query_string .= " LEFT JOIN ".DB_NAME.".wp_usermeta wp_fst ON ( wp_fst.user_id = gp.fr_ID AND wp_fst.meta_key = 'first_name')";
    $query_string .= " LEFT JOIN ".DB_NAME.".wp_usermeta wp_lst ON ( wp_lst.user_id = gp.fr_ID AND wp_lst.meta_key = 'last_name') ORDER BY gp.sbscrbr_login;";

    // store in different temp array
    $rows = array();
    //echo "query string: " . $query_string;
    $result = mysqli_query($con, $query_string);

    while($r = mysqli_fetch_assoc($result)) {
        $rows[] = $r;
    }
    mysqli_free_result($result);

    // Create javascript Object: Sbscrbrs
    echo "<script type='text/javascript'>

        var Sbscrbrs = "; echo json_encode($rows); echo ";

        //  after page built, create these callbacks
        jQuery(document).ready(function($){

            $('#gets_sub_mails').change(function () {
                if(this.checked) {
                    $('#wrn_sub_mail').text('(mail will always be sent to registered address only)').removeClass('verf-err');
                } else {
                    $('#wrn_sub_mail').text('Notify subscriber on subscription changes!').addClass('verf-err');
                }
            });

        });
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

    <p>Subscribers cannot be deleted once they are an owner of an Establishment and/or have signed up for a subscription. 
    When a client doesn't want to pay for our services anymore, you can block this person in this section. You must also cancel all subscriptions that this person has.
    You can block a subscriber (see the Edit panel in this section), in order to prevent this person from logging in or any of their establishments from showing up in the mobile interface.
    Establishments cannot be deleted once one or more subscriptions have been created for it, even if the subscription has been cancelled (ended).</p>


    <p>Entry fields marked with a '*' are mandatory. Entry fields marked with '†' are only saved when the Email address has been provided.</p>

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
                <th class='chck'>Sub. mails?</th>
                <th class='chck'>HTML format?</th>
                <th class='chck'>Blocked?</th>
                <th>Notes</th>
                <th>Action</th>
            </tr></thead><tbody>";

    // Build filter 
    echo "<tr>
        <td class='head'><input class='numb' type='text' value='" . dis($_POST['fltr_pk_sbscrbr_id'],"i") . "' name='fltr_pk_sbscrbr_id' id='fltr_pk_sbscrbr_id' pattern='\d*|\*' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_sbscrbr_login'],"a") . "' name='fltr_sbscrbr_login' id='fltr_sbscrbr_login' pattern='[a-zA-Z0-9_]+|\*' maxlength='60' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_sbscrbr_name'],"s%") . "' name='fltr_sbscrbr_name' id='fltr_sbscrbr_name' maxlength='200' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_user_email'],"a") . "' name='fltr_user_email' id='fltr_user_email' maxlength='100' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_user_registered'],"a") . "' name='fltr_user_registered' id='fltr_user_registered' maxlength='30' /></td>
        <td class='head'><input class='chk' type='text' value='" . dis($_POST['fltr_gets_sub_mails'],"chk") . "' name='fltr_gets_sub_mails' id='fltr_gets_sub_mails' pattern='[YyNn\*]' maxlength='1' /></td>
        <td class='head'><input class='chk' type='text' value='" . dis($_POST['fltr_gets_html_mail'],"chk") . "' name='fltr_gets_html_mail' id='fltr_gets_html_mail' pattern='[YyNn\*]' maxlength='1' /></td>
        <td class='head'><input class='chk' type='text' value='" . dis($_POST['fltr_is_usr_blocked'],"chk") . "' name='fltr_is_usr_blocked' id='fltr_is_usr_blocked' pattern='[YyNn\*]' maxlength='1' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_sbscrbr_notes'],"s%") . "' name='fltr_sbscrbr_notes' id='fltr_sbscrbr_notes' maxlength='200' /></td>
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
        $stmt = mysqli_prepare($con, "SELECT fr_ID, sbscrbr_login, pk_sbscrbr_id, is_usr_blocked, gets_sub_mails, gets_html_mail, sbscrbr_notes FROM subscribers WHERE pk_sbscrbr_id=?");
        mysqli_stmt_bind_param($stmt, "i", $editkey);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $editrow['fr_ID'], $editrow['sbscrbr_login'], $editrow['pk_sbscrbr_id'], $editrow['is_usr_blocked'], $editrow['gets_sub_mails'], $editrow['gets_html_mail'], $editrow['sbscrbr_notes']);

        // Retrieve the subscriber data in the DB
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt); 

        // Get wp_user if subscriber connected thru fr_ID
        $wp_user = FALSE;
        if ( isset($editrow['fr_ID']) ) $wp_user = get_userdata($editrow['fr_ID']);

        //_log("edit row: "); _log($editrow);     // DEBUG // 
        //_log("wp_user: "); _log($wp_user);      // DEBUG // 
    }
    
    // If it is an Edit then we need to scroll till here.
    if ( isset($editkey) ) echo "<a name=" . $func . "></a>";  // Set anchor

    // Start rendering the form
    echo "<a name=add_" . $func . "></a>
    <h4>Add or edit subscriber</h4>
    <p class='hor-form'>";
        // If Edit form keep edit key in hidden field
        if(isset($editkey)) echo "<input type='hidden' name='edit_id' value=". $editkey . " > "; 
        
        // If Edit populate each field with the present values
        if (isset($editkey)) echo "<label for='pk_sbscrbr_id'>ID</label><span id='pk_sbscrbr_id' name='pk_sbscrbr_id' class='display-only' >". dis($editrow['pk_sbscrbr_id'],"i") ."</span>";
        echo "<label for='user_login'>Subscriber ID (user_login) *</label><span>(use letters, digits, '_'s, has to be unique)</span><input type='text' name='user_login' id='user_login' maxlength='60' "; if(isset($editkey)) echo "value='" . dis($editrow['sbscrbr_login'],"a") . "' readonly='readonly' "; echo "/>
        <label for='user_email'>Email address</label><span>(leave empty for no panel access)</span><input type='email' name='user_email' id='user_email' maxlength='100' "; if(isset($editkey) && $wp_user) echo "value='" . $wp_user->user_email . "'"; if(isset($editkey)) echo " readonly='readonly' "; echo "/>";
        
        // If Edit place field with registration date and time if wp_user has been created as well (have to javascript:loc, so some inline script)
        if(isset($editkey) && $wp_user)
        {
            echo "<label for='user_registered'>CMS access registered</label><span id='user_registered' name='user_registered' class='display-only' ></span>
            <script type='text/javascript'>
                $('#user_registered').text(loc(\"" . $wp_user->user_registered . "\"));
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
                    $editrow['pk_sbscrbr_id'] = prep($editrow['pk_sbscrbr_id'],"i");
                    $stmt = mysqli_prepare($con, "UPDATE LOW_PRIORITY subscribers SET fr_ID = NULL, gets_sub_mails = 0 WHERE pk_sbscrbr_id = ?");
                    mysqli_stmt_bind_param($stmt, "i",$editrow['pk_sbscrbr_id']);
                    $exec = mysqli_stmt_execute($stmt);
                    if ($exec ===  FALSE) { echo "<span class='inline err-msg'>Invalid remove wp_user reference query for " . $func . ": " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</span>"; } 
                    else { echo "<span class='inline err-msg'>Old reference to wp_user removed. Re-edit this subscriber (Cancel and Edit again) to see possible corresponding wp_user.</span>"; } 
                    mysqli_stmt_close($stmt); 

                } else { // corresponding wp_user found; provide U/I to Delete wp_user part
                    echo "<label for=del_wpuser_" . $func . "'>Corresponding wp_user: " . $editrow['fr_ID'] . "</label><span>(ALL OTHER INPUT WILL BE IGNORED)</span><input type='submit' class='button-primary' name='del_wpuser_" . $func . "' onclick='confirm_deletion(\"sure_" . $func . "\");' value='Delete wp_user part' style='width: 198px;'><br /><br />";
                }
            }
            if ( empty($editrow['fr_ID']) ) // other reference 
            {
                $user_id = FALSE;
                $user_id = username_exists( $editrow['sbscrbr_login'] );
                if ( $user_id ) echo "<label for=add_wpuser_" . $func . "'>Corresponding wp_user: " . $user_id . "</label><span>(ALL OTHER INPUT WILL BE IGNORED)</span><input type='submit' class='button-primary' name='add_wpuser_" . $func . "' onclick='confirm_attachment(\"sure_" . $func . "\");' value='Attach wp_user part' style='width: 198px;'>";
            }
            if($wp_user) 
            {
                echo "<label for='gets_sub_mails'>Receive subscription mail? *</label>"; if (prep($editrow['gets_sub_mails'],"chk")) { echo "<span id='wrn_sub_mail'>(mail will always be sent to registered address only)"; } else {  echo "<span id='wrn_sub_mail' class='verf-err'>Notify subscriber on subscription changes!"; } echo"</span><input type='checkbox' name='gets_sub_mails' id='gets_sub_mails' ". dis($editrow['gets_sub_mails'],"chk_ctrl") ."/><br /><br />
                      <label for='gets_html_mail'>Receive in HTML format? *</label><span>(if user cannot read the mail, uncheck and mail will be sent in plain text)</span><input type='checkbox' name='gets_html_mail' id='gets_html_mail' ". dis($editrow['gets_html_mail'],"chk_ctrl") ."/><br />";
            }
            echo "<label for='is_usr_blocked'>Block subscriber temporarily?</label><input type='checkbox' name='is_usr_blocked' id='is_usr_blocked' ". dis($editrow['is_usr_blocked'],"chk_ctrl") ."/><br />";
        } 
        if ( !isset($editkey) ) 
        {
            echo "<label for='first_name'>First name †</label><span>(† only saved when Email provided)</span><input type='text' name='first_name' id='first_name' maxlength='200' />
            <label for='last_name'>Last name †</label><input type='text' name='last_name' id='last_name' maxlength='200' />";
        }
        elseif ( $wp_user )
        {
            $first = $wp_user->first_name;
            $last = $wp_user->last_name;
            echo "<label for='first_name'>First name</label><input type='text' name='first_name' id='first_name' maxlength='200' value='" . dis($first,"s") . "' />
            <label for='last_name'>Last name</label><input type='text' name='last_name' id='last_name' maxlength='200' value='" . dis($last,"s") . "' />";
        }
        echo "<label for='sbscrbr_notes'>Notes</label><span>(max. 200 chars, only visible to administrator and managers, enter first and last name when no email)</span><textarea name='sbscrbr_notes' rows='4' cols='50'>". dis($editrow['sbscrbr_notes'],"s") ."</textarea>
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
    </form>";

    // 5. Clean up
    mysqli_close($con);
	unset($result);
	unset($row);
	unset($editrow);
    unset($wp_user);
	unset($rows);

}  // End: groenp_subscribers_meta_box_cb() 


// ****************************************************************
// Callback for ESTABLISHMENTS Meta Box
// ****************************************************************
function cusconow_establishments_meta_box_cb()
{  
    // open database
    $con = cusconow_open_database();

    // ************************************************************
    // 1. Process any form data
    // ************************************************************

    // Make this form unique
	$func = 'Ests';

    // Set anchor
    echo "<a name=" . $func . "></a>";  

    // Create form url for this meta box
	$form_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "#" . $func;

    // DEBUG section
	// All form fields (name and id) have the same name as the column name of their counterpart in the database
	// lang specific fields have DB name and "_<lng>" appended to it
    //_log("===> START CHECKING MANAGEMENT FORM for: " . $func ); // DEBUG //
	//_log("form url: " . $form_url );                            // DEBUG // 
 	//_log("POST: "); _log($_POST); // form fields //             // DEBUG // 
 	//_log($_FILES);                // pics //                    // DEBUG // 

    // ************************************************************
	if ( isset($_POST[('add_'. $func)]) || isset($_POST[('edit_'. $func)]) )  // THIS form has been submitted
    // ************************************************************
    {
		// Check if all mandatory fields have been entered
        $error = FALSE; // initialize

        if ( empty($_POST['fk_sbscrbr_id']) )
        {
            //_log("*** input error ***");
            echo "<p class='err-msg'>No establishment has been created or updated, because the subscriber ID of the owner is missing (not selected).</p>";
            $error = TRUE;
        }

        if ( !$error ) {
                // define and sanitize vars
                $fk_sbscrbr_id  = prep($_POST['fk_sbscrbr_id'], "i");
                $fk_poi_id      = prep($_POST['editkey'], "i");
                $is_allwd_feat  = prep($_POST['is_allwd_feat'], "chk");
                $pk_est_id      = prep($_POST['editkey'], "i");

		    // ************************************************************
		    if ( isset($_POST[('add_'. $func)]) ) // insert form data into tables
			// ************************************************************
			{
                // create a prepared statement: Add establishment section
                $query_string = "INSERT INTO establishments (fk_sbscrbr_id, fk_poi_id, is_subscribed, gets_usr_mails, is_sponsor, is_allwd_feat, thumbnail_url, main_phone, fr_phone_type_id) VALUES (?, ?, 0, NULL, NULL, ?, NULL, NULL, NULL)";
                //_log("Add query for ". $func .": ". $query_string);                // DEBUG //
                $stmt = mysqli_prepare($con, $query_string);

                if ($stmt ===  FALSE) { _log("Invalid insertion query for " . $func . ": " . mysqli_error($con)); }
                else {
                    // bind stmt = i: integer, d: double, s: string
                    $bind = mysqli_stmt_bind_param($stmt, "iii", $fk_sbscrbr_id, $fk_poi_id, $is_allwd_feat);
                    if ($bind ===  FALSE) { _log("Bind parameters failed for add query in " . $func); }
                    else {
                        // execute query 
                        $exec = mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt); 
                        if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not add item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
				        else { 
                            echo "<p class='fbk-msg'>A new Establishment has been created with Est ID: ".  mysqli_insert_id($con) . "</p>"; 
                            _lua($func, "Establishment (Est ID:". mysqli_insert_id($con) .") created."); 
                        }
                    } // end of: binding successful
                } // end of: stmt prepared successful
			} // end of: add
			// ************************************************************
			elseif ( isset($_POST[('edit_'. $func)]) )  // update tables row with editkey 
			// ************************************************************
			{
                // Create a prepared statement: Update establishments table
                $stmt = mysqli_prepare($con, "UPDATE LOW_PRIORITY establishments SET fk_sbscrbr_id = ?, is_allwd_feat = ? WHERE pk_est_id = ?");
                if ($stmt ===  FALSE) { _log("Invalid update query for " . $func . ": " . mysqli_error($con)); }
                else {
                    // bind stmt = i: integer, d: double, s: string, b: blob and will be sent in packets
                    $bind = mysqli_stmt_bind_param($stmt, "iii", $fk_sbscrbr_id, $is_allwd_feat, $pk_est_id);
                    if ($bind ===  FALSE) { _log("Bind parameters failed for add query in " . $func); }
                    else {
                        // execute query 
                        $exec = mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt); 
                        if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not update item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
				        else { 
                            echo "<p class='fbk-msg'>Establishment with ID ". $pk_est_id . " has been succesfully updated.</p>"; 
                            _lua($func, "Establishment (Est ID:". $pk_est_id .") updated."); 
                        }
                    } // end of: binding successful
                } // end of: stmt prepared successful
			} // end of: edit
		} // end of: not missing mandatory fields
    } // end of: add or edit (form submitted)

    // ************************************************************
    elseif (isset($_POST[('sure_'. $func)]))  // check for 'sure_'+function to see whether pushbutton used in THIS table or form
    // ************************************************************
    {
        $delkey = intval(array_search('Delete', $_POST));   // Maybe it's a Delete; check for id on 'Delete' button (case sensitive) and store it
        if (!empty($delkey))  _log("Delete selected with sanitized delkey: ". $delkey);					// DEBUG //

		// If there is a delete id and user answered 'Yes' on RUsure?
        if ( isset($delkey) && isset($_POST[$delkey]) && isset($_POST['sure_'. $func]) && $_POST['sure_'. $func]=='yes')
        {
			// Delete all rows in establishments table with id = delkey
            $query_string = "DELETE LOW_PRIORITY FROM establishments WHERE pk_est_id = ?";
            $stmt = mysqli_prepare($con, $query_string);
            if ($stmt ===  FALSE) { _log("Invalid delete query for " . $func . ": " . mysqli_error($con)); }
            else { 
                $bind = mysqli_stmt_bind_param($stmt, "i", $delkey);
                if ($bind ===  FALSE) { _log("Bind parameters failed for delete query in " . $func); }
                $exec = mysqli_stmt_execute($stmt);
                if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not delete item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
				else { 
                    echo "<p class='fbk-msg'>Establishment with ID ". $delkey . " has been succesfully deleted.</p>"; 
                    _lua($func, "Establishment (Est ID:". $delkey .") deleted."); 
                }
                mysqli_stmt_close($stmt); 
            } // end of: stmt prepared successful
        } // end deletion
    } // end checking for form submits
	
                
    // ************************************************************
    // 2. Create jQuery object holding all POIs and Establishments
    // ************************************************************

    // create query to get all pois that DON'T have establishments
    $query_string = "SELECT pk_poi_id, fk_poi_type_id, poi_shrt_name, addr_street, addr_area, 1st_floor, 2nd_floor, 3rd_and_floor, elevator, escalator"; 
    $query_string .= " FROM pois LEFT JOIN establishments ON (pois.pk_poi_id = establishments.fk_poi_id )";
    $query_string .= " LEFT JOIN poi_types ON (fk_poi_type_id = poi_types.pk_poi_type_id )";
    $query_string .= " WHERE establishments.fk_poi_id IS NULL AND is_nvr_est IS NOT TRUE";

    // store in temp array
    unset($rows);  // re-initialize
    $rows = array();
    $result = mysqli_query($con, $query_string . ";");
    while($r = mysqli_fetch_assoc($result)) {
        $rows[] = $r;
    }
    mysqli_free_result($result);

    // create query to get all pois that DO have establishments
    $query_string = "SELECT pk_poi_id, fk_poi_type_id, poi_shrt_name, addr_street, addr_area, 1st_floor, 2nd_floor, 3rd_and_floor, elevator, escalator"; 
    $query_string .= ", establishments.pk_est_id, establishments.fk_sbscrbr_id, establishments.is_allwd_feat, subscribers.fr_ID, subscribers.sbscrbr_login, subscribers.is_usr_blocked";
    $query_string .= " FROM pois LEFT JOIN establishments ON (pois.pk_poi_id = establishments.fk_poi_id)";
    $query_string .= " LEFT JOIN subscribers ON (establishments.fk_sbscrbr_id = subscribers.pk_sbscrbr_id)";
    $query_string .= " WHERE establishments.fk_poi_id IS NOT NULL;";

    // continue storing in temp array
    $result = mysqli_query($con, $query_string);
    while($r = mysqli_fetch_assoc($result)) {
        $rows[] = $r;
    }
    mysqli_free_result($result);

    // create query to get all establishments that DON'T have pois (only subscribers)
    $query_string = "SELECT establishments.pk_est_id, establishments.fk_sbscrbr_id, establishments.is_allwd_feat, subscribers.fr_ID, subscribers.sbscrbr_login, subscribers.is_usr_blocked";
    $query_string .= " FROM establishments LEFT JOIN subscribers ON (establishments.fk_sbscrbr_id = subscribers.pk_sbscrbr_id)";
    $query_string .= " WHERE establishments.fk_poi_id IS NULL;";

    // continue storing in temp array
    $result = mysqli_query($con, $query_string);
    while($r = mysqli_fetch_assoc($result)) {
        $rows[] = $r;
    }
    mysqli_free_result($result);

    // Create javascript Object: POIsEsts
    //print json_encode($rows);
    echo "<script type='text/javascript'>

        var POIsEsts = ". json_encode($rows) .";
    
        //  after page built, create these callbacks
        jQuery(document).ready(function($){
            $('#test_est_sbscrbr_login').focus(function () {
                $(this).removeClass('verf-err verf-ok');
            });
            $('#sbscrbr_logins').blur(function () {
                if ( $('#sbscrbr_logins')[0].selectedIndex == -1) {
                    $('#fltr_sbscrbr_results input[type=\"button\"]').attr('disabled','disabled');
                }
            });
        });

    </script>";


    // ************************************************************
    //  3. Build updated table with ESTABLISHMENTS
    // ************************************************************

    // Meta box introduction
    echo "<p>This interface allows you to connect an owner (Subscriber ID) with a POI location, creating an Establishment object in the process.</p>

    <p>Once an Establishment (with an owner and a POI) has been created, the owner can create all relevant establishment information through their admin panel. 
    An establishment can also have a subscription, which will make it show up in the search. 
    Establishments cannot be deleted once one or more subscriptions have been created for it, even if the subscription has been cancelled (ended).
    In order for an Establishment not to show up in the mobile search, you can end the subscription.</p>

    <p>When an Establishment Owner is going to move, you can 'park' this Establishment by disconnecting it from a POI in the DB. 
    In table 'establishments' you place 'NULL' in the 'fk_poi_id' field for this Establishment. Parked/orphaned establishments will show up in this list. 
    You can then connect them to another POI by placing that POI's ID in the 'fk_poi_id' field.</p>
    
    <p>Select Filter to obtain a list of POIs and Establishments according to the filter criteria. Most fields also accepts a '*' for any entry. 
    Any boolean field (with ? in label) accepts Y/N or '*' for both Y & N . Select Clear to remove any filtering, then select Filter to obtain full list.</p>";
    
    // Start of form
	echo "<form action='" . $form_url . "' method='post' enctype='multipart/form-data'><p>
            <input type='hidden' name='sure_" . $func . "' id='sure_" . $func . "' value='maybe' /></p>"; // hidden input field to store RUsure? response
			
	// Start table build
    echo "<table id='est_table' class='manage' style='width: 100%; table-layout: fixed; overflow: hidden; white-space: nowrap;'><thead style='text-align: left'>
            <tr style='text-align: left'>
                <th class='numb'>POI ID</th>
                <th>POI name</th>
                <th>POI type</th>
                <th>Street</th>
                <th>Area</th>
                <th class='numb'>Est ID</th>
                <th class='numb'>ID</th>
                <th>Subscriber ID</th>
                <th class='chck'>Blocked?</th>
                <th style='width: 125px;'>Action</th>
            </tr></thead><tbody>";

    // Build filter, subscriber section already used in Subscribers table; prepend with 'est_'
    echo "<tr>
        <td class='head'><input class='numb' type='text' value='" . dis($_POST['fltr_pk_poi_id'],"i") . "' name='fltr_pk_poi_id' id='fltr_pk_poi_id' pattern='\d*|\*' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_poi_shrt_name'],"s") . "' name='fltr_poi_shrt_name' id='fltr_poi_shrt_name' maxlength='60' /></td>
        <td class='head'><select class='prompt' name='fltr_fk_poi_type_id' id='fltr_fk_poi_type_id' onchange='this.style.color= \"#000000\";' >
			<option value=''>Select one:</option>";
			$result_types = mysqli_query($con, "SELECT pk_poi_type_id FROM poi_types WHERE is_nvr_est IS NOT TRUE ORDER BY pk_poi_type_id;");
			while($row_types = mysqli_fetch_array($result_types)) { echo "<option value='" . $row_types['pk_poi_type_id'] . "'"; if(isset($_POST['fltr_fk_poi_type_id']) && ($_POST['fltr_fk_poi_type_id']==$row_types['pk_poi_type_id'])) echo " selected "; echo ">" . $row_types['pk_poi_type_id'] . "</option>"; }
            mysqli_free_result($result_types);
        echo "</select></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_addr_street'],"s") . "' name='fltr_addr_street' id='fltr_addr_street' maxlength='200' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_addr_area'],"s") . "' name='fltr_addr_area' id='fltr_addr_area' maxlength='200' /></td>
        <td class='head'><input class='numb' type='text' value='" . dis($_POST['fltr_pk_est_id'],"i") . "' name='fltr_pk_est_id' id='fltr_pk_est_id' pattern='\d*|\*' /></td>
        <td class='head'><input class='numb' type='text' value='" . dis($_POST['fltr_est_fk_sbscrbr_id'],"i") . "' name='fltr_est_fk_sbscrbr_id' id='fltr_est_fk_sbscrbr_id' pattern='\d*|\*' /></td>
        <td class='head'><input type='text' value='" . dis($_POST['fltr_est_sbscrbr_login'],"s") . "' name='fltr_est_sbscrbr_login' id='fltr_est_sbscrbr_login' maxlength='50' /></td>
        <td class='head'><input class='chk' type='text' value='" . dis($_POST['fltr_est_is_usr_blocked'],"chk") . "' name='fltr_est_is_usr_blocked' id='fltr_est_is_usr_blocked' pattern='[YyNn\*]' /></td>
        ";

        echo "<td class='head'>
            <input type='button' class='button-primary' name='fltr_" . $func . "' value='Filter' onclick='build_fltrd_poisests_table();'>
            <input type='button' class='button-secondary' name='clr_" . $func . "' value='Clear' onclick='clear_filter(\"est_table\");'>
        </td>
    </tr>";

	// Establishments table built by jQuery: build_fltrd_poisests_table();

    // finalize table
    echo "</tbody></table>";


    // ************************************************************
    // 4. Build add/edit form
    // ************************************************************

    // Start rendering the form
    echo "<div id='est_details' style='display:none;'>
    <h4>POI or Establishment details</h4>";

        // If edit of establishment paste editkey in hidden field through jQuery
        echo "<input type='hidden' id='editkey' name='editkey' value='' >"; 

        // create form fields
        echo "<p class='hor-form'>
            <label for='pk_poi_id'>POI ID</label><span id='pk_poi_id' name='pk_poi_id' class='display-only' ></span>
            <label class='has_est' for='pk_est_id'>Est. ID </label><span class='has_est'>(for DB manipulation)</span><span class='display-only has_est' id='pk_est_id' name='pk_est_id' ></span>
            <label for='poi_shrt_name'>Establishment name</label><span id='poi_shrt_name' name='poi_shrt_name' class='display-only' ></span>
            <label for='fk_poi_type_id'>Establishment type</label><span id='fk_poi_type_id' name='fk_poi_type_id' class='display-only' ></span>
            <label for='addr_street'>Street</label><span id='addr_street' name='addr_street' class='display-only' ></span>
            <label for='addr_area'>Area</label><span id='addr_area' name='addr_area' class='display-only' ></span>
        </p>
        <div id='sbscrbr_display'>
            <p class='hor-form'>
                <input type='hidden' id='fk_sbscrbr_id' name='fk_sbscrbr_id' value='' >
                <label for='est_sbscrbr_login'>Owner *</label><input type='button' class='button-primary' value='Change' onclick='show_sbscrbr_filter();' style='' /><input type='text' id='est_sbscrbr_login' name='est_sbscrbr_login' readonly='readonly' />
            </p>
        </div>
        <div id='sbscrbr_filter' style='display: none;'>
            <p class='hor-form'>
                <span id='err1_fltr_sbscrbr_login' class='err-msg' style='display:none;'>Please type at least 3 sequential characters (letters, digits, '_'s) of the ID</span>
                <span id='err2_fltr_sbscrbr_login' class='err-msg' style='display:none;'>There are no Subscriber IDs with this text pattern</span>
                <label for='test_est_sbscrbr_login'>Type new Subscriber ID, then select Check (3 chars min.)</label>
                <input type='button' class='button-secondary' name='check_subscbr' value='Check' onclick='filter_sbscrbrs();' />
                <input type='text' id='test_est_sbscrbr_login' name='test_est_sbscrbr_login' maxlength='60' pattern='[a-zA-Z0-9_]{3}[a-zA-Z0-9_]+' />
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
                <!-- <label for='est_sbscrbr_login'>Subscriber ID</label><span class='display-only' id='est_sbscrbr_login' name='est_sbscrbr_login' ></span> -->
                <label class='wp_registered' for='est_user_registered'>CMS access registered</label><span class='wp_registered display-only' id='est_user_registered' name='est_user_registered' ></span>
                <label class='wp_registered' for='est_sbscrbr_name'>Name</label><span class='wp_registered display-only' id='est_sbscrbr_name' name='est_sbscrbr_name' ></span>
                <label class='wp_registered' for='est_user_email'>Email</label><span class='wp_registered display-only' id='est_user_email' name='est_user_email' ></span>
                <!-- <label for='est_sbscrbr_notes'>Notes</label><span></span><textarea id='est_sbscrbr_notes' name='est_sbscrbr_notes' readonly='readonly' rows='4' cols='50'></textarea> -->
                <label for='est_sbscrbr_notes'>Notes</label><span class='display-only' id='est_sbscrbr_notes' name='est_sbscrbr_notes' style='min-height:80px;'></span>
            </p>
        </div>
        <div>
            <p class='hor-form'>
                <label for='is_allwd_feat'>Owner may edit general features?</label><span>(owner can always edit rest of data)</span><input type='checkbox' name='is_allwd_feat' id='is_allwd_feat' checked='checked' /><br />
            </p>
        </div>
        
        ";

    // form submit buttons 
    echo "<p class='button-row edit_btns'>
        <input type='submit'  class='button-primary' name='edit_" . $func . "' value='Edit establishment'> <input type='submit' class='button-secondary' name='cancel' value='Cancel'>
    </p>
    <p class='button-row add_btns'>
        <input type='submit'  class='button-primary' name='add_" . $func . "' value='Create establishment'>
    </p>
    </div> <!-- est_details -->
    </form>";

    // 4. Clean up
    mysqli_close($con);
	unset($row);
	unset($result_types);
	unset($editrow);
	unset($rows);

    //_log( "Momentary Memory Usage: ". number_format(memory_get_usage()) ." bytes (Peak: ". number_format(memory_get_peak_usage()) ." bytes)." );

} // End: cusconow_establishments_meta_box_cb()
?>