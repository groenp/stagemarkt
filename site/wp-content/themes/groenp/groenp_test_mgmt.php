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
// Groen Productions - Create Meta Boxes for Dashboard of MySQL DBs 
// ****************************************************************
function groenp_dashboard_meta_boxes_add()  
{  
    wp_add_dashboard_widget( 'welcome-mb', 'Welcome to Groen Productions | Sites Management Tool', 'groenp_welcome_meta_box_cb');
    if ( current_user_can('list_users') ) wp_add_dashboard_widget( 'sel-domain-mb', 'Choose Domain to edit', 'groenp_sel_domain_meta_box_cb');
    // if ( !current_user_can('list_users') || !empty($_POST['est_sbscrbr_login']) )
    //     wp_add_dashboard_widget( 'cus-est-details-mb', 'X', 'groenp_est_details_meta_box_cb');
}
add_action( 'wp_dashboard_setup', 'groenp_dashboard_meta_boxes_add' );  


// ****************************************************************
// Callback for CHOOSE DOMAIN Meta Box (only for Administrator)
// ****************************************************************
function groenp_sel_domain_meta_box_cb()
{  

    // Meta box introduction
    echo "<p>Admin chooses domain.</p>
    <div class='custom-control custom-switch'>
        <input type='checkbox' class='custom-control-input' id='customSwitch1' checked>
        <label class='custom-control-label' for='customSwitch1'>Toggle this switch element</label>
    </div>";

} // End: groenp_sel_domain_meta_box_cb()

?>