/******************************************************************************/
/*  groenp-sites-cms-admin.js                                   Pieter Groen  */
/*  Version 0.2 - June 17, 2020                                               */
/*                                                                            */
/*  JS for Groen Productions - Sites Mgmt Tool for ADMINISTRATORS ONLY        */
/*   ( functions, bindings and vars also meant for subscribers are in:        */
/*     groenp-sites-cms(.min).js )                                            */
/*   - GP: Subscribers page                                                   */
/*                                                                            */
/******************************************************************************/

// We're only using jQuery here
$ = jQuery;

/* All functionality is loaded on document.ready */
$(document).ready(function () {

    // **************************************************************************
    // All administrator access pages
    //
    // Only calls in here that couldn't be resolved with WordPress hooks in PHP
    // collect here all jQuery that needs to load with the page
    //
    // always check after each update to WordPress 
    // **************************************************************************

    // contains ref url cookie like 'index.php?page=pagename.php' in WordPress
    var ref = $('input[name ="_wp_http_referer"]').val();

    // switch on page like is done in the admin php page with: $struct_screen 
    // var struct_screen = 'groenp_subscribers';

    // if this is page 'struct_screen'
    if (typeof ref !== 'undefined' && ref.indexOf('groenp_subscribers') != -1) {

        // initialize meta box handling
        postboxes.add_postbox_toggles(pagenow);

        // set screen layout to 1 column
        $('input[name=screen_columns][value="1"]').click();
    }

    // **************************************************************************
    // Specific Meta Boxes only:
    // **************************************************************************


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

});  // end of document.ready()

// **************************************************************************
// Helper functions for specific Meta Boxes:
// **************************************************************************

// **************************************************************************
// Subscribers  MB
// **************************************************************************

// sort and build the #sbscrbr_table table body (heading and filter are already there)
// and apply and fill the filters
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

// build the #sppair_tbl table body (heading and filter are already there)
// and apply and fill the filters
function build_fltrd_spp_table(func) {

    // set checkbox fltr fields 
    $('#sppair_tbl td.fltr input.chk').each(function () {
        $(this).val($(this).val().toUpperCase());
    });

    // clean out table except for filter
    $('#sppair_tbl').find("tr:gt(1)").remove();

    // define filters in sppair_tbl
    if ($('#fltr_spp_fk_prjct_id').val() != "") var fltr1 = $('#fltr_spp_fk_prjct_id').val();
    if ($('#fltr_spp_prjct_name').val() != "") var fltr2 = $('#fltr_spp_prjct_name').val();
    if ($('#fltr_spp_is_test_active').val().toLowerCase() === "y") {
        var fltr3 = 1;
    } else if ($('#fltr_spp_is_test_active').val().toLowerCase() === "n") {
        var fltr3 = 0;
    } else if ($('#fltr_spp_is_test_active').val() === "*") {
        var fltr3 = "*";
    }
    if ($('#fltr_spp_sbscrbr_login').val() != "") var fltr4 = $('#fltr_spp_sbscrbr_login').val();
    if ($('#fltr_spp_is_usr_blocked').val().toLowerCase() === "y") {
        var fltr5 = 1;
    } else if ($('#fltr_spp_is_usr_blocked').val().toLowerCase() === "n") {
        var fltr5 = 0;
    } else if ($('#fltr_spp_is_usr_blocked').val() === "*") {
        var fltr5 = "*";
    }
    if ($('#fltr_spp_sbscrbr_name').val() != "") var fltr6 = $('#fltr_spp_sbscrbr_name').val();
    if ($('#fltr_spp_user_email').val() != "") var fltr7 = $('#fltr_spp_user_email').val();
    if ($('#fltr_spp_sbscrbr_notes').val() != "") var fltr8 = $('#fltr_spp_sbscrbr_notes').val();

    // build table, first row is already there
    var ndx = 0;
    for (var i = 0; i < SbscrbrPrjctPrngs.length; i++) {

        // find corresponding index in Sbscrbrs array by mapping pk_sbscrbr_id in Sbscrbrs[] to fk_sbscrbr_id in SbscrbrPrjctPrngs[]
        // this isi used to append the Sbscrbr data (full name, email, notes) to SbscrbrPrjctPrngs
        ndx = Sbscrbrs.map(function (o) { return o.pk_sbscrbr_id; }).indexOf(SbscrbrPrjctPrngs[i].fk_sbscrbr_id);

        // check whether in filter
        if ((!fltr1 || (SbscrbrPrjctPrngs[i].fk_prjct_id && ((fltr1 == '*' && SbscrbrPrjctPrngs[i].fk_prjct_id) || (fltr1 && SbscrbrPrjctPrngs[i].fk_prjct_id.indexOf(fltr1) >= 0)))) &&
            (!fltr2 || (SbscrbrPrjctPrngs[i].prjct_name && ((fltr2 == '*' && SbscrbrPrjctPrngs[i].prjct_name) || (fltr2 && SbscrbrPrjctPrngs[i].prjct_name.toLowerCase().indexOf(fltr2.toLowerCase()) >= 0)))) &&
            (fltr3 == undefined || (SbscrbrPrjctPrngs[i].is_test_active && ((fltr3 == '*' && SbscrbrPrjctPrngs[i].is_test_active) || (fltr3 != undefined && SbscrbrPrjctPrngs[i].is_test_active == fltr3)))) &&
            (!fltr4 || (SbscrbrPrjctPrngs[i].sbscrbr_login && ((fltr4 == '*' && SbscrbrPrjctPrngs[i].sbscrbr_login) || (fltr4 && SbscrbrPrjctPrngs[i].sbscrbr_login.toLowerCase().indexOf(fltr4.toLowerCase()) >= 0)))) &&
            (fltr5 == undefined || (SbscrbrPrjctPrngs[i].is_usr_blocked && ((fltr5 == '*' && SbscrbrPrjctPrngs[i].is_usr_blocked) || (fltr5 != undefined && SbscrbrPrjctPrngs[i].is_usr_blocked == fltr5)))) &&

            (!fltr6 || ((Sbscrbrs[ndx].first || Sbscrbrs[ndx].last) && ((fltr6 == '*' && (Sbscrbrs[ndx].first || Sbscrbrs[ndx].last)) || (fltr6 && Sbscrbrs[ndx].first.toLowerCase().indexOf(fltr6.toLowerCase()) >= 0) || (fltr6 && Sbscrbrs[ndx].last.toLowerCase().indexOf(fltr6.toLowerCase()) >= 0)))) &&
            (!fltr7 || (Sbscrbrs[ndx].user_email && ((fltr7 == '*' && Sbscrbrs[ndx].user_email) || (fltr7 && Sbscrbrs[ndx].user_email.toLowerCase().indexOf(fltr7.toLowerCase()) >= 0)))) &&
            (!fltr8 || (Sbscrbrs[ndx].sbscrbr_notes && ((fltr8 == '*' && Sbscrbrs[ndx].sbscrbr_notes) || (fltr8 && Sbscrbrs[ndx].sbscrbr_notes.toLowerCase().indexOf(fltr8.toLowerCase()) >= 0))))
        ) {

            // create table row
            $('#sppair_tbl').find('tbody')
                .append($('<tr>')
                    .append($('<td>').text(SbscrbrPrjctPrngs[i].fk_prjct_id).addClass('numb'))
                    .append($('<td>').text(SbscrbrPrjctPrngs[i].prjct_name))
                );
            if (SbscrbrPrjctPrngs[i].is_test_active == "1") { $('#sppair_tbl tr:last').append($('<td>').text("Y").addClass('chck')); } else { $('#sppair_tbl tr:last').append($('<td>')); }
            $('#sppair_tbl tr:last').append($('<td>').html(escapeSpecialChars(SbscrbrPrjctPrngs[i].sbscrbr_login)));
            if (SbscrbrPrjctPrngs[i].is_usr_blocked == "1") { $('#sppair_tbl tr:last').append($('<td>').text("Y").addClass('chck')); } else { $('#sppair_tbl tr:last').append($('<td>')); }

            if (Sbscrbrs[ndx] == undefined) {
                $('#sppair_tbl tr:last').append($('<td>').text("SUBSCRIBER NOT FOUND")).append($('<td>')).append($('<td>')); // ERROR
            } else {
                $('#sppair_tbl tr:last').append($('<td>').html(Sbscrbrs[ndx].first + " " + Sbscrbrs[ndx].last)); // Full name
                $('#sppair_tbl tr:last').append($('<td>').html(escapeSpecialChars(Sbscrbrs[ndx].user_email)));
                $('#sppair_tbl tr:last').append($('<td>').html(escapeSpecialChars(Sbscrbrs[ndx].sbscrbr_notes)));
            }

            //  create buttons for this row
            $('#sppair_tbl tr:last').append($('<td>').append("<input type='submit' name='" + SbscrbrPrjctPrngs[i].pk_sppair_id + "' value='Edit'/>"));
            $('#sppair_tbl tr:last td:last').append("&nbsp;<input type='submit' name='" + SbscrbrPrjctPrngs[i].pk_sppair_id + "' value='Delete'/>");

        } // filter check
    }
    // clean up 'undefined's in full name field
    $('#sppair_tbl td').html().replace(/(undefined)*/g, '');

    // all created buttons need to be styled and working
    $('#sppair_tbl input[type="submit"][value="Edit"]').addClass('button-primary');
    $('#sppair_tbl input[type="submit"][value="Delete"]').addClass('button-secondary').click(function () {
        confirm_deletion('sure_' + func);
    });
} // end of: build_fltrd_spp_table()


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
// General helper functions
// **************************************************************************
// See groenp-sites-cms.js
