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
/*** FROM HERE ON IN THIS FILE ***/
// *****************************************************************************************    


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
        // retrieve slug: groenp_test2
        $page_slug = $project['page_slug'];

        // I: create submenu page with specific call for this page: groenp_register_submenu_page_test2
        add_action('admin_menu', 'groenp_register_submenu_page_test2');

        // II: First step in the meta box creation:
        // register metaboxes based on unique $page_slug
        add_action('load-dashboard_page_' . $page_slug, 'groenp_register_meta_boxes');

        // III: Second step in the meta box creation:
        // In this case branch on user privileges
        if ( current_user_can('list_users') )
        {
            // current user is Administrator: add metaboxes in own page
            // specific call back based on unique $page_slug: groenp_test2_meta_boxes_add
            add_action('add_meta_boxes_' . $page_slug, 'groenp_test2_meta_boxes_add');
        } 
        else 
        {
            // current user is Subscriber: add metaboxes to dashboard
            // specific call back: groenp_test2_meta_boxes_add_dash
            add_action( 'wp_dashboard_setup', 'groenp_test2_meta_boxes_add_dash' );  
        }

    }
    
} // end of: MAIN

//naming convention: $page_slug . '_meta_boxes_add'
function groenp_test2_meta_boxes_add()
{
    // Retrieve project information
    $project = groenp_get_project_from_file( basename(__FILE__) );
    global $plugin_page;

    // Add metaboxes to this php_file's submenu page
    // TRANSLATORS: DO NOT TRANSLATE; part of core po-file
    add_meta_box( 'gp-'. $project['page_slug'] .'-settings-mb', '<span class="intro"><i class="wpicon">&#xf111;</i> '.  __("Settings") .' - '. $project['prjct_name'] .'</span>',  'groenp_settings_meta_box_cb', $project['page_slug'], 'normal' );
    add_meta_box( $project['page_slug'] .'-mb', $project['prjct_name'], 'groenp_test2_meta_box_cb', $project['page_slug'], 'normal' );

} //end of: groenp_test2_meta_boxes_add()

//naming convention: $page_slug . '_meta_boxes_add_dash'
function groenp_test2_meta_boxes_add_dash()  
{  
    // Retrieve project information
    $project = groenp_get_project_from_file( basename(__FILE__) );
    // global $plugin_page; // doesn't work since it is on dashboard and not on its own page

    // Add metaboxes to the dashboard
    // TRANSLATORS: DO NOT TRANSLATE; part of core po-file
    wp_add_dashboard_widget( 'gp-'. $project['page_slug'] .'-settings-mb', '<span class="intro"><i class="wpicon">&#xf111;</i> '.  __("Settings") .' - '. $project['prjct_name'] .'</span>',  'groenp_settings_meta_box_cb');
    wp_add_dashboard_widget( $project['page_slug'] .'-mb', $project['prjct_name'], 'groenp_test2_meta_box_cb');
} //end of: groenp_test2_meta_boxes_add_dash()



// *****************************************************************************************    
// Function for sub menu creation based on $project
// menu and empty page get created based on 'list_users' access
// (for Managers & Administrators only)
// *****************************************************************************************    
function groenp_register_submenu_page_test2() 
{
    // Retrieve project information
    $project = groenp_get_project_from_file( basename(__FILE__) );
    // global $plugin_page; // there is no submenu page yet once called the first time

    // create menu 
	if ( !isset( $project['error'] ) ) add_submenu_page( 'index.php', $project['prjct_name'], 'GP: '. $project['prjct_name'], 'list_users', $project['page_slug'], 'groenp_create_page_cb' ); 
}


// ****************************************************************
// Callback for TEST2 Meta Box 
// ****************************************************************
function groenp_test2_meta_box_cb()
{
    // Meta box introduction
    echo "<h4>This is project specific: groenp_test2_mgmt.php</h4>
    <div class=''>
     <p>This is a project specific box, but no project meta data, other than what's used in the add meta box call.</p>
    </div>";

} // End: groenp_sel_domain_meta_box_cb()


?>