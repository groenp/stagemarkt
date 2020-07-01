<?php
/********************************************************************************************/
/*                                                                            Pieter Groen  */
/*  Version 0.1 - May 24, 2014                                                              */
/*                                                                                          */
/*  PHP for Groen Productions Sites Mgmt CMS in WordPress:                                  */
/*   - test Meta Box to test filter per user                                                */
/*                                                                                          */
/********************************************************************************************/
// namespace groenp;

// *****************************************************************************************    
// Function to open database depending on whether switch is set to Live or Test
// $test comes straight out of switch setting in form ("" or "on")
// *****************************************************************************************    
function testDB_open_database($test = false)
{
    // connect with CRUD user (always for CMS!) and select correct db
    if ($test)
    {
        $connect = mysqli_connect('localhost','gp_testCRUDuser','testCRUDpwd', 'gp_testdb_test_cms');   // TEST db
    } else {
        $connect = mysqli_connect('localhost','gp_testCRUDuser','testCRUDpwd', 'gp_testdb_live_cms');   // LIVE db
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
// menu and empty page get created based on 'read' access
// *****************************************************************************************    
function groenp_register_submenu_page_read() 
{

    // Retrieve project information
    $project = groenp_get_project_from_file( basename(__FILE__) );

    // create menu 
	add_submenu_page( 'index.php', $project['prjct_name'], 'GP: '. $project['prjct_name'], 'read', $project['page_slug'], 'groenp_create_page_cb' ); 
}

// *****************************************************************************************    
// Function for sub menu creation based on $project
// menu and empty page get created based on 'list_users' access
// (for Managers & Administrators only)
// *****************************************************************************************    
function groenp_register_submenu_page_mngr() 
{
    // Retrieve project information
    $project = groenp_get_project_from_file( basename(__FILE__) );

    // global $plugin_page; // there is no submenu page yet once called the first time

    // create menu 
	add_submenu_page( 'index.php', $project['prjct_name'], 'GP: '. $project['prjct_name'], 'list_users', $project['page_slug'], 'groenp_create_page_cb' ); 
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

        // I: create submenu page with specific call for this page: 
        add_action('admin_menu', 'groenp_register_submenu_page_read');

        // II: First step in the meta box creation:
        // register metaboxes based on unique $page_slug
        add_action('load-dashboard_page_' . $page_slug, 'groenp_register_meta_boxes');

        // III: Second step in the meta box creation:
        // In this case branch on user privileges
        if ( current_user_can('list_users') )
        {
            // current user is Administrator: add metaboxes in own page
            // specific call back: groenp_test_meta_boxes_add
            add_action('add_meta_boxes_'. $page_slug, 'groenp_test_meta_boxes_add');
        } 
        else 
        {
            // current user is Subscriber: add metaboxes to dashboard

            // specific call back: groenp_test_meta_boxes_add_dash
            add_action( 'wp_dashboard_setup', 'groenp_test_meta_boxes_add_dash' );  

            // and also add some to new page.. just to test
            add_action('add_meta_boxes_'. $page_slug, 'groenp_test_meta_boxes_add');
        }

    }
    
} // end of: MAIN

function groenp_test_meta_boxes_add()
{
    // Retrieve project information
    global $plugin_page;
    $project = groenp_get_project_from_slug( $plugin_page );

    // Add metaboxes to this php_file's submenu page
    if ( current_user_can('list_users') )
    {
        // TRANSLATORS: DO NOT TRANSLATE; part of core po-file
        add_meta_box( 'gp-'. $project['page_slug'] .'-settings-mb', '<i class="wpicon">&#xf111;</i> '.  __("Settings") .' - '. $project['prjct_name'], 'groenp_settings_meta_box_cb', $project['page_slug'], 'normal' );
        add_meta_box( $project['page_slug'] .'-mb', $project['prjct_name'], 'groenp_test_meta_box_cb', $project['page_slug'], 'normal' ); 
        add_meta_box( 'groenp_testDB-mb', $project['prjct_name'], 'groenp_testDB_meta_box_cb', $project['page_slug'], 'normal' );
    }
    add_meta_box( $project['page_slug'] .'2-mb', 'This is the second coming of the box', 'groenp_anothertest_meta_box_cb', $project['page_slug'], 'normal' );

} //end of: groenp_test_meta_boxes_add()


function groenp_test_meta_boxes_add_dash()  
{  
    // Retrieve project information
    $project = groenp_get_project_from_file( basename(__FILE__) );
    // global $plugin_page; // doesn't work since it is on dashboard and not on its own page

    // Add metaboxes to the dashboard
    // TRANSLATORS: DO NOT TRANSLATE; part of core po-file
    wp_add_dashboard_widget( 'gp-'. $project['page_slug'] .'-settings-mb', '<i class="wpicon">&#xf111;</i> '.  __("Settings") .' - '. $project['prjct_name'], 'groenp_settings_meta_box_cb');
    wp_add_dashboard_widget( $project['page_slug'] .'-mb', $project['prjct_name'], 'groenp_test_meta_box_cb');
    wp_add_dashboard_widget( 'groenp_testDB-mb', $project['prjct_name'], 'groenp_testDB_meta_box_cb' );
} //end of: groenp_test_meta_boxes_add_dash()


// ****************************************************************
// Callback for TEST Meta Box 
// ****************************************************************
function groenp_test_meta_box_cb()
{
    // Meta box introduction
    echo "<h4>This is project specific: groenp_test_mgmt.php</h4>
    <div class=''>
     <p>This is a project specific box, but no project meta data, other than what's used in the add meta box call.</p>
    </div>";

} // End: groenp_sel_domain_meta_box_cb()


// ****************************************************************
// Callback for ANOTHERTEST Meta Box 
// ****************************************************************
function groenp_anothertest_meta_box_cb()
{
    // Meta box introduction
    echo "<h4>This is project specific: groenp_test_mgmt.php</h4>
    <div class=''>
     <p>This is the second meta box for the same project.</p>
    </div>";

} // End: groenp_sel_domain_meta_box_cb()


// ****************************************************************
// Callback for testDB Meta Box
// ****************************************************************
function groenp_testDB_meta_box_cb()  
{     
    // retrieve Live/Test selection for database connection  
    // (first check if any form submitted, then switch operated, otherwise read from test_set field)
    $test_set = isset($_POST['test_set'])? ( isset($_POST['RefreshLT'])? ( isset($_POST['live-test'])? $_POST['live-test'] : "" ) : $_POST['test_set'] ) : "";
    
    // open database
    $con = testDB_open_database($test_set);

    // ************************************************************
    // 1. Process any form data
    // ************************************************************

    // Make this form unique
    $func = 'TstDB';
    
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
		if ( empty($_POST['test_name']) ) 
		{
            //_log("*** input error ***");
    		echo "<p class='err-msg'>All input fields marked with a '*' must be completed.</p>";
        }
        else {
                // define and sanitize vars
                $test_name = prep($_POST['test_name'], 's');

            // ************************************************************
		    if ( isset($_POST[('add_'. $func)]) ) // insert form data into tables
			// ************************************************************
			{
                // create a prepared statement 
                $query_string = 'INSERT INTO gp_test ' .
                    '(test_name) ' . 
                    'VALUES (?)';
                // _log("Add query for ". $func .": ". $query_string);                // DEBUG //
                $stmt = mysqli_prepare($con, $query_string);

                if ($stmt ===  FALSE) { _log("Invalid insertion query for " . $func . ": " . mysqli_error($con)); }
                else {
                    // bind stmt = i: integer, d: double, s: string
                    $bind = mysqli_stmt_bind_param($stmt, 's', $test_name);
                    if ($bind ===  FALSE) { _log("Bind parameters failed for add query in " . $func); }
                    else {
                        $exec = mysqli_stmt_execute($stmt);
                        if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not add item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>";  }
                        else { _lua($func, "Test item (ID: ". mysqli_insert_id($con) .", ". $test_name .") created."); }
                        mysqli_stmt_close($stmt); 
                    } // end of: binding successful
                } // end of: stmt prepared successful
            } // End of: add
            
			// ************************************************************
			elseif ( isset($_POST[('edit_'. $func)]) && isset($_POST['sure_'. $func]) && $_POST['sure_'. $func]!=='no')  // update tables row with editkey 
			// ************************************************************
			{
                // sanitize editkey
                $pk_test_id= prep($_POST['editkey'], 'i');

                // create a prepared statement 
                $query_string = 'UPDATE LOW_PRIORITY gp_test SET ' .
                    'test_name = ? ' . 
                    'WHERE pk_test_id = ?';
                $stmt = mysqli_prepare($con, $query_string);

                if ($stmt ===  FALSE) { _log("Invalid update query for " . $func . ": " . mysqli_error($con)); }
                else {
                    // bind stmt = i: integer, d: double, s: string, b: blob and will be sent in packets
                    $bind = mysqli_stmt_bind_param($stmt, 'si', $test_name, $pk_test_id);
                    if ($bind ===  FALSE) { _log("Bind parameters failed for add query in " . $func); }
                    else {
                        $exec = mysqli_stmt_execute($stmt);
                        if ($exec ===  FALSE) { echo "<p class='err-msg'>Could not update item: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                        else { _lua($func, "Project (ID: ". $pk_test_id .", ". $test_name .") updated."); }
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
            $query_string = 'DELETE LOW_PRIORITY FROM gp_test WHERE pk_test_id = ?';
            $stmt = mysqli_prepare($con, $query_string);
            if ($stmt ===  FALSE) { _log("Invalid delete query for " . $func . ": " . mysqli_error($con)); }
            else {
                $bind = mysqli_stmt_bind_param($stmt, 'i', $delkey);
                $exec = mysqli_stmt_execute($stmt);
                if ($exec ===  FALSE) { echo "<p class='err-msg'><Test> could not be deleted: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>"; }
                else { _lua($func, "<Test> (ID: ". $delkey .") deleted."); }
                mysqli_stmt_close($stmt); 
            } // end of: stmt prepared successful
	    } // End of: deletion
    } // End of: checking for form submits

    // ************************************************************
    // 2. Create jQuery object holding all <Test>s
    // ************************************************************

    // For now not used in this Meta Box
    // Create query for all <Test>s
    $query_string = 'SELECT pk_test_id, test_name FROM gp_test ORDER BY test_name;';

    // store in different temp array
    $tests = array();

    $result = mysqli_query($con, $query_string);
    if ( $result ) { 
        while($r = mysqli_fetch_assoc($result)) {
            $tests[] = $r;
        }
        mysqli_free_result($result);
    }

    // Create javascript Object: <Test>s
    echo "<script type='text/javascript'>

        var Tests = "; echo json_encode($tests); echo ";

    </script>";

    // ************************************************************
    //  3. Build updated table with <Test>s
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
    <p>Entry fields marked with a '*' are mandatory. Entry fields marked with '**' have to be unique as well.</p>";
    
	// Start table build
    echo "<table class='manage' style='width: 100%; table-layout: fixed; overflow: hidden; white-space: nowrap;'><thead style='text-align: left'>
            <tr style='text-align: left'>
                <th class='numb'>ID</th>
                <th>Test name</th>
                <th>Action</th>
            </tr></thead><tbody>";


    // Query table, and leave out 'edit' row depending on action selection, and filter 
    unset($result); unset($row); // re-initialize
    if (isset($editkey))
    {
        // prepare statement excluding item to be edited
        $stmt = mysqli_prepare($con, 'SELECT pk_test_id, test_name' .
                             ' FROM gp_test WHERE pk_test_id != ? ORDER BY test_name');
        mysqli_stmt_bind_param($stmt, 'i', $editkey);
    } else {
        // prepare statement in similar way as edit, but no parameters
        $stmt = mysqli_prepare($con, 'SELECT pk_test_id, test_name' .
                             ' FROM gp_test ORDER BY test_name');
    } // End of: not set editkey

    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $row['pk_test_id'], $row['test_name']);

    // Retrieve row by row the project data in the DB
    while ( mysqli_stmt_fetch($stmt) )
    {
        // Build row
        echo "<tr>
                <td class='numb'>" . dis($row['pk_test_id'],'i') . "</td>
                <td>" . dis($row['test_name'],'s') . "</td>";

                // Add final cell with button section and link to javascript pop-up
                echo "<td><input type='submit' class='button-primary' name='" . $row['pk_test_id'] . "' value='Edit'> 
                            <input type='submit' class='button-secondary' name='" . $row['pk_test_id'] . "' onclick='confirm_deletion(\"sure_" . $func . "\");' value='Delete'></td>
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
        $stmt = mysqli_prepare($con, 'SELECT test_name FROM gp_test WHERE pk_test_id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $editkey);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $editrow['test_name']);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt); 
    }
    
    // Start rendering the form
    echo "<h4>Add or edit [test]</h4>
    <p class='hor-form'>
        <a class='anchr' name=add_" . $func . "></a>";
    
        // If edit form keep edit key in hidden field and insert anchor: we need to scroll till here
        if(isset($editkey)) echo "<input type='hidden' name='editkey' value=". $editkey . " > 
        <a class='anchr' name=" . $func . "></a>"; 
        
        // If Edit populate each field with the present values
        echo "
        <label for='test_name'>Name *</label><span>(max. 50 chars)</span><input type='text' name='test_name' id='test_name' maxlength='50' value='" . dis($editrow['test_name'],"s") . "' />
    </p>
    <p class='button-row'>";

    if ( isset($editkey) )
    {
        // Edit form, so create Edit and Cancel buttons  
        echo "<input type='submit'  class='button-primary' name='edit_" . $func . "' value='Edit <Test>'> <input type='submit' class='button-secondary' name='cancel' value='Cancel'>";
    } else {
        // Normal (Add) form, so only Add button needed
        echo "<input type='submit'  class='button-primary' name='add_" . $func . "' value='Add <Test>'>";
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

}  // End: groenp_testDB_meta_box_cb() 

?>