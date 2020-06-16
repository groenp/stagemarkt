/******************************************************************************/
/*                                                              Pieter Groen  */
/*  Version 0.1 - June 13, 2020                                               */
/*                                                                            */
/*  JS for Groen Productions - Sites Mgmt Tool for ADMINISTRATORS ONLY        */
/*   - GP: Subscribers page                                                   */
/*                                                                            */
/******************************************************************************/

// We're only using jQuery here
$ = jQuery;


$(document).ready(function () {


    // **************************************************************************
    // All administrator access pages
    //
    // Only calls in here that couldn't be resolved with WordPress hooks in PHP
    // collect here all jQuery that needs to load with the page
    //
    // always check after each update to WordPress 
    // **************************************************************************

    // contains the ref url cookie like 'index.php?page=pagename.php' in WordPress
    var ref = $('input[name ="_wp_http_referer"]').val();

    // switch on page like is done in the admin php page with: $struct_screen 
    // var struct_screen = 'groenp_subscribers';

    // if this is page 'struct_screen'
    if (typeof ref !== 'undefined' && ref.indexOf('groenp_subscribers') != -1) {

        // initialize meta box handling
        postboxes.add_postbox_toggles(pagenow); 

    }

    // **************************************************************************
    // Projects MB
    // **************************************************************************


    // **************************************************************************
    // Subscriber / Project Pairings MB
    // **************************************************************************


    // Subscriber select list: on blur; disable Select button when no item selected 
    $('#sbscrbr_logins').blur(function () {
        if ($('#sbscrbr_logins')[0].selectedIndex == -1) {
            $('#fltr_sbscrbr_results input[type=\"button\"]').attr('disabled', 'disabled');
        }
    });


    // Project name dropdown list: on change (and load by triggering a select)
    $('#spp_prjct_id').change(function () {

        // populate read-only below Project name select with test available information
        var active =  ( $("#spp_prjct_id option:selected").attr("data-active") == "1" )? "Yes": "No";
        $('#spp_is_test_active').html(active);
        $('#spp_dis_prjct_id').html($("#spp_prjct_id option:selected").attr("data-id"));

        // when first item is selected (value=''), then grey and empty read-only below
        if ( $(this).val() ) { 
            $(this).css("color", '#000')
        } else { 
            $(this).css("color", '#a0a0a0');
            $('#spp_is_test_active').html("");
            $('#spp_dis_prjct_id').html("");
        };
    });
    $('#spp_prjct_id').trigger('change');

    // when user selects the filter input field, remove all feedback
    $('#test_spp_sbscrbr_login').focus(function () {
        $(this).removeClass('verf-err verf-ok');
    });


    // Augment table  list of pairings: sppair_tbl with subscriber details

    // This is normally done by LEFT JOIN the user table
    // The only reason to use the Sbscrbrs json is to minimize contact with wp tables

    $('#sppair_tbl tbody tr').each(function () {
        var sbscrbr_id = $(this).find('td:eq(4)').text();
        if (sbscrbr_id) {

            // find index in array by mapping pk_sbscrbr_id to parsed sbscrbr_id
            var ndx = Sbscrbrs.map(function (o) { return o.pk_sbscrbr_id; }).indexOf(sbscrbr_id);

            // only if the right Sbscrbrs item has been found
            if (typeof Sbscrbrs[ndx] !== "undefined") {
                $(this).find('td:eq(4)').html(Sbscrbrs[ndx].first + " " + Sbscrbrs[ndx].last);  // Full name
                $(this).find('td:eq(5)').html(Sbscrbrs[ndx].user_email);                        // Email
                $(this).find('td:eq(6)').html(Sbscrbrs[ndx].sbscrbr_notes);                     // Notes
            }
        }
    });
    
    
});  // end of document.ready()


// **************************************************************************
// Subscribers  MB
// **************************************************************************

function build_fltrd_sbscrbrs_table(func) {

    // set checkbox fltr fields 
    $('#sbscrbr_table td.fltr input.chk').each(function() {
        $(this).val( $(this).val().toUpperCase() );
    });

    // set sort order
    var prop1 = "sbscrbr_login";
    var prop2 = "pk_sbscrbr_id";
    var prop3 = "";

    if( $('#fltr_pk_sbscrbr_id').val() != "" ) {
        prop1 = "pk_sbscrbr_id";
        prop2 = "";
        prop3 = "";
    }
    if( $('#fltr_sbscrbr_name').val() != "" ) {
        prop1 = "last";
        prop2 = "first";
        prop3 = "sbscrbr_login";
    }
    if( $('#fltr_user_email').val() != "" ) {
        prop1 = "user_email";
        prop2 = "sbscrbr_login";
        prop3 = "pk_sbscrbr_id";
    }
    if( $('#fltr_user_registered').val() != "" ) {
        prop1 = "user_registered";
        prop2 = "sbscrbr_login";
        prop3 = "pk_sbscrbr_id";
    }

    // sort array
    Sbscrbrs.sort( srt_by(prop1, prop2, prop3) );

    // clean out table except for filter
    $('#sbscrbr_table').find("tr:gt(1)").remove();

    // define filters in sbscrbr_table
    if ($('#fltr_pk_sbscrbr_id').val() != "" ) var fltr1 = $('#fltr_pk_sbscrbr_id').val();
    if ($('#fltr_sbscrbr_login').val() != "" ) var fltr2 = $('#fltr_sbscrbr_login').val();
    if ($('#fltr_sbscrbr_name').val() != "" )  var fltr3 = $('#fltr_sbscrbr_name').val();
    if ($('#fltr_user_email').val() != "" ) var fltr4 = $('#fltr_user_email').val();
    if ($('#fltr_user_registered').val() != "" ) var fltr5 = $('#fltr_user_registered').val();
    if ($('#fltr_locale').val() != "") var fltr6 = $('#fltr_locale').val();
    if ($('#fltr_gets_html_mail').val().toLowerCase() === "y" ) { 
        var fltr7 = 1; 
    } else if ($('#fltr_gets_html_mail').val().toLowerCase() === "n" ) { 
        var fltr7 = 0; 
    } else if ($('#fltr_gets_html_mail').val() === "*" ) { 
        var fltr7 = "*"; 
    }
    if ($('#fltr_is_usr_blocked').val().toLowerCase() === "y" ) { 
        var fltr8 = 1; 
    } else if ($('#fltr_is_usr_blocked').val().toLowerCase() === "n" ) { 
        var fltr8 = 0; 
    } else if ($('#fltr_is_usr_blocked').val() === "*" ) { 
        var fltr8 = "*"; 
    }
    if ($('#fltr_sbscrbr_notes').val() != "" ) var fltr9 = $('#fltr_sbscrbr_notes').val();

    // build table, first row is already there
    for ( var i=0; i<Sbscrbrs.length; i++ ) {

        // check whether in filter
        if ( ( !fltr1 || (Sbscrbrs[i].pk_sbscrbr_id && ((fltr1=='*' && Sbscrbrs[i].pk_sbscrbr_id) || (fltr1 && Sbscrbrs[i].pk_sbscrbr_id.indexOf(fltr1) >= 0))) ) && 
             ( !fltr2 || (Sbscrbrs[i].sbscrbr_login && ((fltr2=='*' && Sbscrbrs[i].sbscrbr_login) || (fltr2 && Sbscrbrs[i].sbscrbr_login.toLowerCase().indexOf(fltr2.toLowerCase()) >= 0))) ) &&
             ( !fltr3 || ((Sbscrbrs[i].first || Sbscrbrs[i].last) && ((fltr3=='*' && (Sbscrbrs[i].first || Sbscrbrs[i].last)) || (fltr3 && Sbscrbrs[i].first.toLowerCase().indexOf(fltr3.toLowerCase()) >= 0) || (fltr3 && Sbscrbrs[i].last.toLowerCase().indexOf(fltr3.toLowerCase()) >= 0))) ) &&
             ( !fltr4 || (Sbscrbrs[i].user_email &&    ((fltr4=='*' && Sbscrbrs[i].user_email)    || (fltr4 && Sbscrbrs[i].user_email.toLowerCase().indexOf(fltr4.toLowerCase()) >= 0))) ) &&
             ( !fltr5 || (Sbscrbrs[i].user_registered && ((fltr5=='*' && Sbscrbrs[i].user_registered) || (fltr5 && loc(Sbscrbrs[i].user_registered).toLowerCase().indexOf(fltr5.toLowerCase()) >= 0))) ) &&
             ( !fltr6 || (Sbscrbrs[i].locale && ((fltr6 == '*' && Sbscrbrs[i].locale) || (fltr6 && Sbscrbrs[i].locale.toLowerCase().indexOf(fltr6.toLowerCase()) >= 0)))) &&
             ( fltr7 == undefined || (Sbscrbrs[i].gets_html_mail && ((fltr7=='*' && Sbscrbrs[i].gets_html_mail) || (fltr7 != undefined && Sbscrbrs[i].gets_html_mail == fltr7))) ) &&
             ( fltr8 == undefined || (Sbscrbrs[i].is_usr_blocked && ((fltr8=='*' && Sbscrbrs[i].is_usr_blocked) || (fltr8 != undefined && Sbscrbrs[i].is_usr_blocked == fltr8))) ) &&
             ( !fltr9 || (Sbscrbrs[i].sbscrbr_notes && ((fltr9=='*' && Sbscrbrs[i].sbscrbr_notes) || (fltr9 && Sbscrbrs[i].sbscrbr_notes.toLowerCase().indexOf(fltr9.toLowerCase()) >= 0))) )
           ) {

            // compile full sbscrbr name as "last, first"
            var sbscrbr_name = (Sbscrbrs[i].last == undefined || Sbscrbrs[i].last == "" ) ? "" : Sbscrbrs[i].last + ", ";
            sbscrbr_name += (Sbscrbrs[i].first == undefined) ? "" : Sbscrbrs[i].first;

            // adjust locale for default ("")
            var locale = ( Sbscrbrs[i].locale === null )? "N/A" : Sbscrbrs[i].locale;
            if ( locale == "" ) locale = "site";
            if (locale == "N/A") locale = "";

            // create table row
            $('#sbscrbr_table').find('tbody')
                .append($('<tr>')
                    .append($('<td>').text(Sbscrbrs[i].pk_sbscrbr_id).addClass('numb'))
                    .append($('<td>').text(Sbscrbrs[i].sbscrbr_login))
                    .append($('<td>').html(escapeSpecialChars(sbscrbr_name)))
                    .append($('<td>').html(escapeSpecialChars(Sbscrbrs[i].user_email)))
                    .append($('<td>').text(loc(Sbscrbrs[i].user_registered)))
                    .append($('<td>').html(escapeSpecialChars(locale)))
            );
            if (Sbscrbrs[i].gets_html_mail == "1") { $('#sbscrbr_table tr:last').append($('<td>').text("Y").addClass('chck'));} else { $('#sbscrbr_table tr:last').append($('<td>')); } 
            if (Sbscrbrs[i].is_usr_blocked == "1") { $('#sbscrbr_table tr:last').append($('<td>').text("Y").addClass('chck'));} else { $('#sbscrbr_table tr:last').append($('<td>')); } 
            $('#sbscrbr_table tr:last').append($('<td>').html(escapeSpecialChars(Sbscrbrs[i].sbscrbr_notes)));

            //  create buttons for this row
            $('#sbscrbr_table tr:last').append($('<td>').append("<input type='submit' name='"+Sbscrbrs[i].pk_sbscrbr_id+"' value='Edit'/>"));
            $('#sbscrbr_table tr:last td:last').append("&nbsp;<input type='submit' name='"+Sbscrbrs[i].pk_sbscrbr_id+"' value='Delete'/>");

        } // filter check

    }
    // clean up 'undefined's in full name field
    $('#sbscrbr_table td').html().replace(/(undefined)*/g, '');

    // all created buttons need to be styled and working
    $('#sbscrbr_table input[type="submit"][value="Edit"]').addClass('button-primary');
    $('#sbscrbr_table input[type="submit"][value="Delete"]').addClass('button-secondary').click(function () {
        confirm_deletion('sure_'+func);
    });
} // end: build_fltrd_sbscrbrs_table();


// **************************************************************************
// Subscriber - Project Pairing  MB
// **************************************************************************

// Change button: to change selected subscriber, 
// shows the inline selection form elements
function show_sbscrbr_filter() {
    $('#test_spp_sbscrbr_login').val($('#spp_sbscrbr_login').val());
    $('#sbscrbr_display input[type="button"]').hide();
    $('#sbscrbr_filter').show();
}

// Select button: places the selected subscriber in the mandatory form field
function select_sbscrbr() {
    $('#spp_sbscrbr_id').val(Sbscrbrs[$('#sbscrbr_logins option:selected').val()].pk_sbscrbr_id);
    $('#spp_sbscrbr_login').val($('#sbscrbr_logins option:selected').text());
    $('#sbscrbr_display').show();
    $('#sbscrbr_display input[type="button"]').show();
    $('#sbscrbr_filter').hide();
    $('#fltr_sbscrbr_results').hide();
    $('input[type="submit"]').removeAttr('disabled');
}

// Check button: filters the list of subscribers according pattern,
// and places the list in the multiple select field
function filter_sbscrbrs() {
    var snippet = $('#test_spp_sbscrbr_login').val();
    var res = 0;

    // reset
    $('#test_spp_sbscrbr_login').removeClass("verf-ok verf-err");
    $('#sbscrbr_logins option').remove();
    $('#fltr_sbscrbr_results').hide();
    $('#sbscrbr_details').hide();
    $('#sbscrbr_display').show();
    $('#sbscrbr_display input[type="button"]').hide();

    $('#fltr_sbscrbr_results .multi_res').show();
    // reset all errors
    $('#err1_fltr_sbscrbr_login').hide();
    $('#err2_fltr_sbscrbr_login').hide();

    if (snippet.length < 3) {
        $('#err1_fltr_sbscrbr_login').show();
        $('#test_spp_sbscrbr_login').addClass("verf-err");
        return false;
    }

    // filter list and create options in select
    for (var i = 0; i < Sbscrbrs.length; i++) {
        if (Sbscrbrs[i].sbscrbr_login.indexOf(snippet) != -1) {
            $('#sbscrbr_logins').append("<option value=" + i + ">" + Sbscrbrs[i].sbscrbr_login + "</option>");
            res++;
        }
    }

    // validate results
    if (res >= 2) {
        $('#fltr_sbscrbr_results input[type="button"]').attr('disabled', 'disabled');
        $('#fltr_sbscrbr_results').show();
    } else if (res == 1) {
        $('#test_spp_sbscrbr_login').val($('#sbscrbr_logins option:selected').text());
        $('#test_spp_sbscrbr_login').addClass("verf-ok");
        $('#fltr_sbscrbr_results').show();
        $('#sbscrbr_logins').val($('#sbscrbr_logins option:eq(0)').val());
        $('#fltr_sbscrbr_results .multi_res').hide();
        show_sbscrbr_det();
    } else if (res == 0) {
        $('#err2_fltr_sbscrbr_login').show();
        $('#test_spp_sbscrbr_login').addClass("verf-err");
        $('#fltr_sbscrbr_results').hide();
    }
}

// On selection of subscriber in list: shows subscriber details
function show_sbscrbr_det() {
    var id = 0;
    if ($('#sbscrbr_logins')[0].selectedIndex != -1) {
        id = $('#sbscrbr_logins option:selected').val();
        $('#test_spp_sbscrbr_login').val(Sbscrbrs[id].sbscrbr_login);
        $('#spp_user_registered').text(loc(Sbscrbrs[id].user_registered));
        $('#spp_sbscrbr_name').html(escapeSpecialChars(Sbscrbrs[id].first) + "&nbsp;" + escapeSpecialChars(Sbscrbrs[id].last));
        $('#spp_user_email').text(Sbscrbrs[id].user_email);
        $('#spp_sbscrbr_notes').html(escapeSpecialChars(Sbscrbrs[id].sbscrbr_notes));

        // if 'first' key exists, then wp_user section created
        if ("first" in Sbscrbrs[id]) { $('.wp_registered').show(); } else { $('.wp_registered').hide(); };
        $('#fltr_sbscrbr_results input[type="button"]').removeAttr('disabled');
        $('#sbscrbr_details').show();
    } else {
        $('#sbscrbr_details').hide();
    }
}



// **************************************************************************
// Helper functions
// **************************************************************************

function confirm_deletion(inp) {
    $('#' + inp).val(confirm("Select 'OK' to confirm deletion.") ? "yes" : "no");
}

function confirm_attachment(inp) {
    $('#' + inp).val(confirm("Select 'OK' to confirm attaching wp_user.") ? "yes" : "no");
}

function msg_sub_deletion_not_implemented() {
    alert("ONLY DO THIS WHEN SUBSCRIPTION WAS COMPLETELY WRONG AND REPAIR AFTER (LIKE USE A FREE SLOT, ADJUST CHARGES, PAYMENTS, ETC.)\n\nInstructions for database:\n" +
        "1) delete all muts of this subscription (make sure any valid ones (payment) get added later)\n" +
        "2) delete subscription (it is wrong and should not have been created in the first place)\n" +
        "3) change establishment: is_subscribed = false, gets_usr_mails = NULL, is_sponsor = NULL.");
}

function clear_filter(tbl) {
    $('#' + tbl + ' td.fltr>input[type="text"], #' + tbl + ' td.fltr>select').val("");
    $('#' + tbl + ' td.head>input[type="text"], #' + tbl + ' td.head>select').val("");
}

function srt_by(propA, propB, propC) {
    return function(a,b){
        // if it both are a number and a>b, if a is null and b not, if both are not null and not numbers and a>b 
        // if( (a[propA] != null) && ( (b[propA] == null) || ( !isNaN(a[propA]) && (parseInt(a[propA])-parseInt(b[propA]))>0 ) || (a[propA] > b[propA]) ) ){
            //console.log( parseInt(a[propA]) + " - " + parseInt(b[propA]) +" = " + (parseInt(a[propA])-parseInt(b[propA])) ); 
        if ( (!isNaN(a[propA]) && !isNaN(b[propA]) && (parseInt(a[propA])-parseInt(b[propA]))>0 ) || (a[propA] == null &&  b[propA] != null) || (a[propA] != null && isNaN(a[propA]) &&  b[propA] != null && isNaN(b[propA]) && (a[propA].toLowerCase() > b[propA].toLowerCase())) ) {
            return 1;
        } else if ( (!isNaN(a[propA]) && !isNaN(b[propA]) && (parseInt(a[propA])-parseInt(b[propA]))<0 ) || (a[propA] != null &&  b[propA] == null) || (a[propA] != null && isNaN(a[propA]) &&  b[propA] != null && isNaN(b[propA]) && (a[propA].toLowerCase() < b[propA].toLowerCase())) ) {
            return -1;
        } else if ( (!isNaN(a[propB]) && !isNaN(b[propB]) && (parseInt(a[propB])-parseInt(b[propB]))>0 ) || (a[propB] == null &&  b[propB] != null) || (a[propB] != null && isNaN(a[propB]) &&  b[propB] != null && isNaN(b[propB]) && (a[propB].toLowerCase() > b[propB].toLowerCase())) ) {
            return 1;
        } else if ( (!isNaN(a[propB]) && !isNaN(b[propB]) && (parseInt(a[propB])-parseInt(b[propB]))<0 ) || (a[propB] != null &&  b[propB] == null) || (a[propB] != null && isNaN(a[propB]) &&  b[propB] != null && isNaN(b[propB]) && (a[propB].toLowerCase() < b[propB].toLowerCase())) ) {
            return -1;
        } else if ( (!isNaN(a[propC]) && !isNaN(b[propC]) && (parseInt(a[propC])-parseInt(b[propC]))>0 ) || (a[propC] == null &&  b[propC] != null) || (a[propC] != null && isNaN(a[propC]) &&  b[propC] != null && isNaN(b[propC]) && (a[propC].toLowerCase() > b[propC].toLowerCase())) ) {
            return 1;
        } else if ( (!isNaN(a[propC]) && !isNaN(b[propC]) && (parseInt(a[propC])-parseInt(b[propC]))<0 ) || (a[propC] != null &&  b[propC] == null) || (a[propC] != null && isNaN(a[propC]) &&  b[propC] != null && isNaN(b[propC]) && (a[propC].toLowerCase() < b[propC].toLowerCase())) ) {
            return -1;
        }
        return 0;
    }
}
