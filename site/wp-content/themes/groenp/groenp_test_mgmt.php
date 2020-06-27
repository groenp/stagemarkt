<?php
/******************************************************************************/
/*                                                              Pieter Groen  */
/*  Version 0.1 - May 24, 2014                                                */
/*                                                                            */
/*  PHP for Groen Productions Sites Mgmt CMS in WordPress:                    */
/*   - test Meta Box to test filter per user                                  */
/*                                                                            */
/******************************************************************************/

/* Global var */
$slug = 'test';


function groenp_get_project( $php_file )
{
    // store results in row of project data
    $row = array();

    // open database
    $con = groenp_open_database();


    // query projects and store in array
    $result = mysqli_query($con, 'SELECT prjct_name, base_url, is_test_active, test_url FROM gp_projects WHERE prjct_php = "'. $php_file .'";');

    // There can only be one result row. In any case; only get the first one
    if ( $result ) { 
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
    } else {
        $row['error'] = "Could not get project data from ". $php_file .".";
    }
    // close database
    mysqli_close($con);

    _log('right after creation:');                  // DEBUG //
        _log( $prjct );                           // DEBUG //
    }
}
/*** WRAP IN FUNCTION  ***/
/*** /WRAP IN FUNCTION  ***/



add_action( 'wp_dashboard_setup', 'groenp_test_mgmt_meta_boxes_add' );  
function groenp_test_mgmt_meta_boxes_add()  
{  
    // TRANSLATORS: DO NOT TRANSLATE; part of core po-file
    wp_add_dashboard_widget( 'gp-settings-mb', '<i class="fas fa-cog"></i> '.  __("Settings", 'core'), 'groenp_settings_meta_box_cb');
}


// ****************************************************************
// Groen Productions - add project boxes to Dashboard or new page 
// ****************************************************************
function groenp_add_project_meta_boxes( $slug )
{

} //end of: groenp_add_project_meta_boxes()


// ****************************************************************
// Callback for TEST Meta Box 
// ****************************************************************
function groenp_settings_meta_box_cb()
{  
    // Retrieve user's locale 
    $user = wp_get_current_user();
    $locale = ($user)? get_user_locale( $user->ID ) : get_user_locale();

    // Meta box introduction
    // TRANSLATORS: Please review the + Privacy Statement and Terms of Use 
    echo "<p>". __("Please review the",'groenp') . " <a href= '". site_url('privacy_and_terms_of_use.php?wp_lang=' . $locale) ."'>".  __("Privacy Statement and Terms of Use", 'groenp') ."</a>.</p>
    <p class='hor-form test-switch'>
        <span class='prompt'>Choose here which version of your CMS you want to work on:<br />
        <span class='context testver'>Only those that have access to the test version will see it. It is a great way to try things out, before everybody sees it.</span>
        <span class='context livever'>The live version is directly connected to the live site.</span></span>
        <label class='test-switch'>
            <input id='live-test' name='live-test' type='checkbox'>
            <span class='test-slider'></span>
        </label>
        <span class='context'>These versions are not connected in any way, the data is not copied over.</span> 
    </p>
    <p class='hor-form'>
        <label for='open_site'>To verify the results:</label><span>(website will open in a separate window or tab)</span><a type='button' class='button-primary launch' name='open_site' target='_blank' href='https://test.groenproductions.com/'>Show website</a>
    </p>
    <p class='button-row btt'><a href='#'>back to top</a>
    </p>";

} // End: groenp_settings_meta_box_cb()


// ****************************************************************
// Callback for TEST Meta Box 
// ****************************************************************
function groenp_sel_domain_meta_box_cb()
{  

    // Meta box introduction
    echo "<p>Test.</p>
    <div class='custom-control custom-switch'>
        <input type='checkbox' class='custom-control-input' id='customSwitch1' checked>
        <label class='custom-control-label' for='customSwitch1'>Toggle this switch element</label>
    </div>";

} // End: groenp_sel_domain_meta_box_cb()


?>