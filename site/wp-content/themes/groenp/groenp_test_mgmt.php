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