<?php
/********************************************************************************************/
/*                                                                            Pieter Groen  */
/*  Version 0.1 - August 14, 2020                                                           */
/*                                                                                          */
/*  PHP for Bloem-Consultants website in Groen Productions Sites Mgmt CMS:                  */
/*   - Focus item types (admin only)                                                        */
/*   - Focus items                                                                          */
/*                                                                                          */
/********************************************************************************************/
// namespace groenp_bloem;

// *****************************************************************************************    
// Function to open database depending on whether switch is set to Live or Test
// $test comes straight out of switch setting in form ("" or "on")
// *****************************************************************************************    
function bloem_open_database($test = false)
{
    // connect with CRUD user (always for CMS!) and select correct db depdning on $test and whether on host
    if ($test && ($_SERVER['SERVER_PORT'] == "80" || $_SERVER['SERVER_PORT'] == "443") )
    {
        $connect = mysqli_connect('localhost','bloemcnsltRuFC7N','C>Gtj7T@xA(Bew$n', 'bloem_test_cms');         // TEST db
    } else {
        $connect = mysqli_connect('localhost','bloemcnsltRuFC7N','C>Gtj7T@xA(Bew$n', 'bloemconsultants_cms');   // LIVE db
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
// Function for sub menu creation based on $project
// menu and empty page get created based on 'list_users' access
// (for Administrators only)
// *****************************************************************************************    
function bloem_register_submenu_page_mngr() 
{
    // Retrieve project information
    $project = groenp_get_project_from_file( basename(__FILE__) );

    // global $plugin_page; // there is no submenu page yet once called the first time

    // create menu item
	add_submenu_page( 'index.php', $project['prjct_name'], $project['prjct_name'], 'list_users', $project['page_slug'], 'groenp_create_page_cb' ); 
}

// *****************************************************************************************       
// Groen Productions  - MAIN: add project boxes to Dashboard or new page (if Project exists)
//                      (meta boxes: register -> add (creation in mem) -> do (placement))
// *****************************************************************************************    
{ // MAIN

    // Globals > AVOID USE - they change every time a thread runs
    $project = groenp_get_project_from_file( basename(__FILE__) );

    // This PHP file has been loaded - does it have a corresponding Project entry?
    // only load on this page if the project information is loaded
    if ( !isset( $project['error'] ) ) 
    {
        // retrieve slug
        $page_slug = $project['page_slug'];

        // first branch on privileges so that Admin and Manager get their own page, willem bloem will not
        if ( current_user_can('list_users') )
        {
            // I: create submenu page with specific call for this page: 
            add_action('admin_menu', 'bloem_register_submenu_page_mngr');

            // II: First step in the meta box creation:
            // register metaboxes based on unique $page_slug
            add_action('load-dashboard_page_' . $page_slug, 'groenp_register_meta_boxes');

            // current user is Administrator or Manager: add metaboxes in own page
            
            // III: Second step in the meta box creation:
            // specific call back: bloem_meta_boxes_add
            add_action('add_meta_boxes_'. $page_slug, 'bloem_meta_boxes_add');
        }
        else 
        {
            // II: First step in the meta box creation:
            // register metaboxes based on unique $page_slug
            add_action('load-dashboard_page_' . $page_slug, 'groenp_register_meta_boxes');
            
            // current user is Subscriber: add metaboxes to dashboard

            // III: Second step in the meta box creation:
            // specific call back: bloem_meta_boxes_add_dash
            add_action( 'wp_dashboard_setup', 'bloem_meta_boxes_add_dash' );  

        }

    }
    
} // end of: MAIN

function bloem_meta_boxes_add()
{
    // Retrieve project information
    global $plugin_page;
    $project = groenp_get_project_from_slug( $plugin_page );

    // Add metaboxes to this php_file's submenu page
    // TRANSLATORS: DO NOT TRANSLATE; part of core po-file
    add_meta_box( 'gp-'. $project['page_slug'] .'-settings-mb', '<span class="intro"><i class="wpicon">&#xf111;</i> '.  __("Settings") .' - '. $project['prjct_name'] .'</span>', 'groenp_settings_meta_box_cb', $project['page_slug'], 'normal' );
    
    // In this case branch on user privileges, but only for me; managers should not edit classnames etc.
    if ( current_user_can('switch_themes') )
    {
        add_meta_box( 'bloem_focus_types_mb', $project['prjct_name'] ."= Focus Types", 'bloem_focus_types_meta_box_cb', $project['page_slug'], 'normal' );
    }

    add_meta_box( 'bloem_focus_items_mb', $project['prjct_name'] ." = Focus Items", 'bloem_focus_items_meta_box_cb', $project['page_slug'], 'normal' );

} //end of: bloem_meta_boxes_add()


function bloem_meta_boxes_add_dash()  
{  
    // Retrieve project information
    $project = groenp_get_project_from_file( basename(__FILE__) );
    // global $plugin_page; // doesn't work since it is on dashboard and not on its own page

    // Add metaboxes to the dashboard
    // TRANSLATORS: DO NOT TRANSLATE; part of core po-file
    wp_add_dashboard_widget( 'gp-'. $project['page_slug'] .'-settings-mb', '<span class="intro"><i class="wpicon">&#xf111;</i> '.  __("Settings") .' - '. $project['prjct_name'] .'</span>', 'groenp_settings_meta_box_cb');
    wp_add_dashboard_widget( 'bloem_focus_items_mb', $project['prjct_name'] ." = Focus Items", 'bloem_focus_items_meta_box_cb' );
} //end of: bloem_meta_boxes_add_dash()


// ****************************************************************
// Callback for Focus Items Meta Box 
// ****************************************************************
function bloem_focus_items_meta_box_cb()
{
    // Meta box introduction
    echo "<h4>This is project specific: ". basename(__FILE__) ."</h4>
    <div class=''>
     <p>This is a project specific box, but no project meta data, other than what's used in the add meta box call.</p>
    </div>";

} // End: bloem_focus_items_meta_box_cb()


// ****************************************************************
// Callback for Focus Types Meta Box
// ****************************************************************
function bloem_focus_types_meta_box_cb()  
{     
    // retrieve Live/Test selection for database connection  
    // (first check if any form submitted, then switch operated, otherwise read from test_set field)
    $test_set = isset($_POST['test_set'])? ( isset($_POST['RefreshLT'])? ( isset($_POST['live-test'])? $_POST['live-test'] : "" ) : $_POST['test_set'] ) : "";
    
    // open database
    $con = bloem_open_database($test_set);

    // ************************************************************
    // 1. Process any form data
    // ************************************************************

    // Make this form unique
    $func = 'FcsTps';
    
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
		// Check if all mandatory fields have been entered, file uploads only can be mandatory during 'add' submit
		if ( empty($_POST['fcs_type_name']) || empty($_POST['fcs_classname']) || (  isset($_POST['add_'.$func]) && empty($_FILES['fcs_img_url']['name'])  ) ) 
		{
            echo "<p class='err-msg'>All input fields marked with a '*' must be completed. <br />
            † Focus type icon must be selected during creation only.</p>";
        }
        else 
        {
                // define and sanitize vars
                $fcs_type_name = prep($_POST['fcs_type_name'], 's');
                $fcs_classname = prep($_POST['fcs_classname'], 's');
                $fcs_aria_lbl = prep($_POST['fcs_aria_lbl'], 's');

                // Upload svg image if defined 
                if ( $_FILES['fcs_img_url']['name'] ) 
                {
                    // get project info for directory slug
                    $project = groenp_get_project_from_file( basename(__FILE__) );
                    // $uploads_dir = $test_set ? $project['test_upl_dir'] : $project['upload_dir'];

                    // upload svg and retrieve url (filename, upload_dir)
                    $fcs_img_url = groenp_upload_svg($_FILES['fcs_img_url'], $project['page_slug'], 10);
                }
                else { $fcs_img_url = false; }

            // ************************************************************
		    if ( isset($_POST[('add_'. $func)]) ) // insert form data into tables
			// ************************************************************
			{
                // create a prepared statement 
                $query_string = 'INSERT INTO bloem_focus_types ' .
                    '(fcs_type_name, fcs_classname, fcs_aria_lbl, fcs_img_url ) ' . 
                    'VALUES (?, ?, ?, ?)';
                // _log("Add query for ". $func .": ". $query_string);                // DEBUG //
                $stmt = mysqli_prepare($con, $query_string);

                if ($stmt ===  FALSE) { _log("Invalid insertion query for " . $func . ": " . mysqli_error($con)); }
                else {
                    // bind stmt = i: integer, d: double, s: string
                    $bind = mysqli_stmt_bind_param($stmt, 'ssss', $fcs_type_name, $fcs_classname, $fcs_aria_lbl, $fcs_img_url);
                    if ($bind ===  FALSE) { _log("Bind parameters failed for add query in " . $func); }
                    else {
                        $exec = mysqli_stmt_execute($stmt);
                        if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not add item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>";  }
                        else { _lua($func, "Bloem Focus Type (ID: ". mysqli_insert_id($con) .", ". $fcs_type_name .") created."); }
                        mysqli_stmt_close($stmt); 
                    } // end of: binding successful
                } // end of: stmt prepared successful
            } // End of: add
            
			// ************************************************************
			elseif ( isset($_POST[('edit_'. $func)]) && isset($_POST['sure_'. $func]) && $_POST['sure_'. $func]!=='no')  // update tables row with editkey 
			// ************************************************************
			{
                // sanitize editkey
                $pk_fcs_type_id= prep($_POST['editkey'], 'i');

                // create a prepared statement 
                if ($fcs_img_url == false) {
                    $query_string = 'UPDATE LOW_PRIORITY bloem_focus_types SET ' .
                        'fcs_type_name = ?, fcs_classname = ?, fcs_aria_lbl = ? ' . 
                        'WHERE pk_fcs_type_id = ?';
                } else {
                    $query_string = 'UPDATE LOW_PRIORITY bloem_focus_types SET ' .
                        'fcs_type_name = ?, fcs_classname = ?, fcs_aria_lbl = ?, fcs_img_url = ? ' . 
                        'WHERE pk_fcs_type_id = ?';
                }
                $stmt = mysqli_prepare($con, $query_string);

                if ($stmt ===  FALSE) { _log("Invalid update query for " . $func . ": " . mysqli_error($con)); }
                else {
                    // bind stmt = i: integer, d: double, s: string, b: blob and will be sent in packets
                    if ($fcs_img_url == false) {
                        $bind = mysqli_stmt_bind_param($stmt, 'sssi', $fcs_type_name, $fcs_classname, $fcs_aria_lbl, $pk_fcs_type_id);
                    } else {
                        $bind = mysqli_stmt_bind_param($stmt, 'ssssi', $fcs_type_name, $fcs_classname, $fcs_aria_lbl, $fcs_img_url, $pk_fcs_type_id);
                    }

                    if ($bind ===  FALSE) { _log("Bind parameters failed for add query in " . $func); }
                    else {
                        $exec = mysqli_stmt_execute($stmt);
                        if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not update item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                        else { _lua($func, "Bloem Focus Type (ID: ". $pk_fcs_type_id .", ". $fcs_type_name .") updated."); }
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
            $query_string = 'DELETE LOW_PRIORITY FROM bloem_focus_types WHERE pk_fcs_type_id = ?';
            $stmt = mysqli_prepare($con, $query_string);
            if ($stmt ===  FALSE) { _log("Invalid delete query for " . $func . ": " . mysqli_error($con)); }
            else {
                $bind = mysqli_stmt_bind_param($stmt, 'i', $delkey);
                $exec = mysqli_stmt_execute($stmt);
                if ($exec ===  FALSE) { echo "<p class='err-msg'>Focus Type could not be deleted: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                else { _lua($func, "Bloem Focus Type (ID: ". $delkey .") deleted."); }
                mysqli_stmt_close($stmt); 
            } // end of: stmt prepared successful
	    } // End of: deletion
    } // End of: checking for form submits

    // ************************************************************
    // 2. Create jQuery object holding all <Test>s
    // ************************************************************

    // For now not used in this Meta Box
    // Create query for all <Test>s
    $query_string = 'SELECT fcs_type_name, fcs_classname, fcs_aria_lbl, fcs_img_url FROM bloem_focus_types ORDER BY fcs_type_name;';

    // store in different temp array
    $focus_types = array();

    $result = mysqli_query($con, $query_string);
    if ( $result ) { 
        while($r = mysqli_fetch_assoc($result)) {
            $focus_types[] = $r;
        }
        mysqli_free_result($result);
    }

    // Create javascript Object: <Test>s
    echo "<script type='text/javascript'>

        var FcsTypes = "; echo json_encode($focus_types); echo ";

    </script>";

    // ************************************************************
    //  3. Build updated table with Focus Types
    // ************************************************************

    // Start of form;  hidden input field to store RUsure? response and display of LIVEvsTEST
    // keep loaded Live/Test selection in read-only field (first check if any form submitted, then switch operated, otherwise read from test_set field)
    echo "<form action='" . $form_url . "' method='post' enctype='multipart/form-data'>
    <p>
        <input type='hidden' name='sure_" . $func . "' id='sure_" . $func . "' value='maybe' />
        <input type='text' class='test-display' name='test_set' id='test_" . $func . "' value='" . dis($test_set,'a') . "' readonly='readonly' />
    </p>"; 
    
    // Meta box introduction
    echo "<p class='btt'><a href='#'>back to top</a></p>
    <p>Focus types are defined as a class inside bloem(.min).css<br />
    This table serves to link the classes to the actual focus items. Next to the class, an aria-label is used as a text depiction of the type.
    In order to handle the styles inside the focus item metabox, the focus types also get a name and icon for selection. These have no influence on the functioning of the actual website. The Focus type icon is defined in the style sheet.</p>
    <p>Focus types cannot be deleted when they are assigned to one or more focus items. Entry fields marked with a '*' are mandatory.<br />
    † Focus type icon only needs to be uploaded during Focus type creation (Add).</p>";
    
	// Start table build
    echo "<table class='manage' style='width: 100%; table-layout: fixed; overflow: hidden; white-space: nowrap;'><thead style='text-align: left'>
            <tr style='text-align: left'>
                <th class='pic'>icon</th>
                <th>Focus Type name</th>
                <th>Class name</th>
                <th>aria label (type descr)</th>
                <th>Action</th>
            </tr></thead><tbody>";


    // Query table, and leave out 'edit' row depending on action selection, and filter 
    unset($result); unset($row); // re-initialize
    if (isset($editkey))
    {
        // prepare statement excluding item to be edited
        $stmt = mysqli_prepare($con, 'SELECT pk_fcs_type_id, fcs_type_name, fcs_classname, fcs_aria_lbl, fcs_img_url' .
                             ' FROM bloem_focus_types WHERE pk_fcs_type_id != ? ORDER BY fcs_type_name');
        mysqli_stmt_bind_param($stmt, 'i', $editkey);
    } else {
        // prepare statement in similar way as edit, but no parameters
        $stmt = mysqli_prepare($con, 'SELECT pk_fcs_type_id, fcs_type_name, fcs_classname, fcs_aria_lbl, fcs_img_url' .
                             ' FROM bloem_focus_types ORDER BY fcs_type_name');
    } // End of: not set editkey

    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $row['pk_fcs_type_id'], $row['fcs_type_name'], $row['fcs_classname'], $row['fcs_aria_lbl'], $row['fcs_img_url']);

    // Retrieve row by row the project data in the DB
    while ( mysqli_stmt_fetch($stmt) )
    {
        // Build row
        echo "<tr>
                <td class='pic'><img height='16' src='". $row['fcs_img_url'] ."' /></td>
                <td>" . dis($row['fcs_type_name'],'s') . "</td>
                <td>" . dis($row['fcs_classname'],'s') . "</td>
                <td>" . dis($row['fcs_aria_lbl'],'s') . "</td>";

                // Add final cell with button section and link to javascript pop-up
                echo "<td><input type='submit' class='button-primary' name='" . $row['pk_fcs_type_id'] . "' value='Edit'> 
                            <input type='submit' class='button-secondary' name='" . $row['pk_fcs_type_id'] . "' onclick='confirm_deletion(\"sure_" . $func . "\");' value='Delete'></td>
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
        $stmt = mysqli_prepare($con, 'SELECT fcs_type_name, fcs_classname, fcs_aria_lbl FROM bloem_focus_types WHERE pk_fcs_type_id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $editkey);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $editrow['fcs_type_name'], $editrow['fcs_classname'], $editrow['fcs_aria_lbl']);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt); 
    }
    
    // Start rendering the form
    echo "<h4>Add or edit Focus type</h4>
    <p class='hor-form'>
        <a class='anchr' name=add_" . $func . "></a>";
    
        // If edit form keep edit key in hidden field and insert anchor: we need to scroll till here
        if(isset($editkey)) echo "<input type='hidden' name='editkey' value=". $editkey . " > 
        <a class='anchr' name=" . $func . "></a>"; 
        
        // If Edit populate each field with the present values
        echo "
        <label for='fcs_type_name'>Focus type name *</label><span>(max. 20 chars, only used in the FI Meta Box)</span><input type='text' name='fcs_type_name' id='fcs_type_name' maxlength='20' value='" . dis($editrow['fcs_type_name'],"s") . "' />
        <label for='fcs_img_url'>Focus type icon (svg)"; if(!isset($editkey)) echo " *"; echo "</label><span>(only used in the Focus Items Meta Box)</span><input type='file' name='fcs_img_url' id='fcs_img_url' />
        <label for='fcs_classname'>Class name *</label><span>(max. 20 chars, defined in bloem.css)</span><input type='text' name='fcs_classname' id='fcs_classname' maxlength='20' value='" . dis($editrow['fcs_classname'],"s") . "' />
        <label for='fcs_aria_lbl'>Aria-label</label><span>(max. 20 chars, text for aria-label for span indicating type)</span><input type='text' name='fcs_aria_lbl' id='fcs_aria_lbl' maxlength='20' value='" . dis($editrow['fcs_aria_lbl'],"s") . "' />
    </p>
    <p class='button-row'>";

    if ( isset($editkey) )
    {
        // Edit form, so create Edit and Cancel buttons  
        echo "<input type='submit'  class='button-primary' name='edit_" . $func . "' value='Edit Focus type'> <input type='submit' class='button-secondary' name='cancel' value='Cancel'>";
    } else {
        // Normal (Add) form, so only Add button needed
        echo "<input type='submit'  class='button-primary' name='add_" . $func . "' value='Add Focus type'>";
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

}  // End: bloem_focus_types_meta_box_cb() 

?>