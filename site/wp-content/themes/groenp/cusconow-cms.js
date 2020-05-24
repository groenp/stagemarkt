/******************************************************************************/
/*                                                              Pieter Groen  */
/*  Version 1.0 - August 03, 2014                                             */
/*                                                                            */
/*  JS for CuscoNow website CMS in WordPress                                  */
/*                                                                            */
/******************************************************************************/

// We're only using jQuery here
$ = jQuery;

function confirm_deletion(inp) {
   $('#'+inp).val( confirm("Select 'OK' to confirm deletion.") ? "yes" : "no" );
}

function confirm_attachment(inp) {
   $('#'+inp).val( confirm("Select 'OK' to confirm attaching wp_user.") ? "yes" : "no" );
}

function confirm_cancellation(inp) {
   $('#'+inp).val( confirm("The present active subscription will be ended (cancelled) immediately.\n\nSelect 'OK' to confirm.") ? "yes" : "no" );
}

function confirm_reinstatement(inp) {
   $('#'+inp).val( confirm("The cancelled subscription will be continued immediately. No other subscription can be active now, otherwise the subscriber would be charged double.\nUse this function only in case this subscription was cancelled in error. Any reimbursement for not being searchable during cancellation has to be calculated by hand.\n\nSelect 'OK' to confirm.") ? "yes" : "no" );
}

function confirm_cancellation_schedule(inp) {
   $('#'+inp).val( confirm("The scheduling of the cancellation for this subscription will be changed.\n\nSelect 'OK' to confirm.") ? "yes" : "no" );
}

function confirm_subtype_edit(inp) {
   $('#'+inp).val( confirm("There are subscriptions that refer to this type. Any changes will prompt a recalculation of all mutations belonging to those subscriptions.\n\nSelect 'OK' to confirm.") ? "yes" : "no" );
}

function confirm_mailing(inp) {
   $('#'+inp).val( confirm("Select 'OK' to confirm the mailing of this statement.") ? "yes" : "no" );
}
function msg_sub_deletion_not_implemented() {
    alert("ONLY DO THIS WHEN SUBSCRIPTION WAS COMPLETELY WRONG AND REPAIR AFTER (LIKE USE A FREE SLOT, ADJUST CHARGES, PAYMENTS, ETC.)\n\nInstructions for database:\n" +
        "1) delete all muts of this subscription (make sure any valid ones (payment) get added later)\n"+
        "2) delete subscription (it is wrong and should not have been created in the first place)\n"+
        "3) change establishment: is_subscribed = false, gets_usr_mails = NULL, is_sponsor = NULL.");
}

function clear_filter(tbl) {
    $('#'+tbl+' td.fltr>input[type="text"], #'+tbl+' td.fltr>select').val("");
    $('#'+tbl+' td.head>input[type="text"], #'+tbl+' td.head>select').val("");
}


// **************************************************************************
// Mutations MB
// **************************************************************************

function filter_muts_table() {

    // define filters in muts_table
    if ($('#fltr_pk_mut_id').val() != "" ) var fltr1 = $('#fltr_pk_mut_id').val();
    if ($('#fltr_mut_date').val() != "" ) var fltr2 = $('#fltr_mut_date').val();
    if (typeof fltr2 !== 'undefined') {
        fltr2 = fltr2.replace(/\*+/g, "*");                                                                                     // remove any doubles or more
        if (fltr2.charAt(0) == "*") fltr2 = fltr2.substr(1);                                                                    // take out any starting '*'
        if (fltr2.charAt(fltr2.length-1) == "*") fltr2 = fltr2.substr(0,fltr2.length-1);                                        // take out any ending '*'
        if (fltr2.indexOf("*") != -1) {                                                                                         // if still '*' split up in max. 3 substrings that ALL need to be found
            var fltr = fltr2.split('*');
            fltr2 = fltr[0];
            if (fltr.length >= 2) var fltr2a = fltr[1];
            if (fltr.length >= 3) var fltr2b = fltr[2];
        }
    }
    if ($('#fltr_poi_shrt_name').length && $('#fltr_poi_shrt_name').val() != "" ) var fltr3 = $('#fltr_poi_shrt_name').val();   // optional element
    if ($('#fltr_deb_mut_amount').val() != "" ) var fltr4 = $('#fltr_deb_mut_amount').val();                                    // monetary amount might have comparison operator
    if (typeof fltr4 !== 'undefined') {
        if (fltr4.charAt(0) == "<") {                                                                                           // "<###"
            fltr4 = fltr4.substr(1);                                                                                            // strip off first char
            var fltr4op = -1;                                                                                                   // comparison operator is -1x >= -1y; equal to x <= y
        } else if (fltr4.charAt(0) == ">") {                                                                                    // ">###"
            fltr4 = fltr4.substr(1);                                                                                            // strip off first char
            var fltr4op = 1;                                                                                                    // comparison operator is 1x >= 1y; equal to x >= y
        }
    }
    if ($('#fltr_crd_mut_amount').val() != "" ) var fltr5 = $('#fltr_crd_mut_amount').val();
    if (typeof fltr5 !== 'undefined') {
        if (fltr5.charAt(0) == "<") {                                                                                           // "<###"
            fltr5 = fltr5.substr(1);                                                                                            // strip off first char
            var fltr5op = -1;                                                                                                   // comparison operator is -1x >= -1y; equal to x <= y
        } else if (fltr5.charAt(0) == ">") {                                                                                    // ">###"
            fltr5 = fltr5.substr(1);                                                                                            // strip off first char
            var fltr5op = 1;                                                                                                    // comparison operator is 1x >= 1y; equal to x >= y
        }
    }
    if ($('#fltr_comment_es').val() != "" ) var fltr6 = $('#fltr_comment_es').val();


    // filter table rows, but leave .fltr and .bal alone
    //$('#muts_table > tbody > tr:not(.fltr, .bal)').hide();
    $('#muts_table > tbody > tr:not(.fltr, .bal)').each(function () {
        if ( (!fltr1  || ($(this).find('td:nth-child(1)').text() && ((fltr1 == '*' && $(this).find('td:nth-child(1)').text()!="") || (fltr1 && $(this).find('td:nth-child(1)').text().indexOf(fltr1) >= 0))) ) &&
             (!fltr2  || ($(this).find('td:nth-child(2)').text() && (fltr2  && $(this).find('td:nth-child(2)').text().toLowerCase().indexOf(fltr2.toLowerCase()) >= 0))) &&
             (!fltr2a || ($(this).find('td:nth-child(2)').text() && (fltr2a && $(this).find('td:nth-child(2)').text().toLowerCase().indexOf(fltr2a.toLowerCase()) >= 0))) &&
             (!fltr2b || ($(this).find('td:nth-child(2)').text() && (fltr2b && $(this).find('td:nth-child(2)').text().toLowerCase().indexOf(fltr2b.toLowerCase()) >= 0))) &&
             (!fltr3  || ($(this).find('td:nth-child(3)').text() && ((fltr3 == '*' && $(this).find('td:nth-child(3)').text()!="") || (fltr3 && $(this).find('td:nth-child(3)').text().toLowerCase().indexOf(fltr3.toLowerCase()) >= 0))) ) &&
              // because the 3rd cell is optional (poi_shrt_name), the rest needs to be counted from the back
             (!fltr4  || ($(this).find('td:nth-last-child(4)').text() && (fltr4  && $(this).find('td:nth-last-child(4)').text().toLowerCase().indexOf(fltr4.toLowerCase()) >= 0) || (fltr4op && ( (fltr4op*parseInt($(this).find('td:nth-last-child(4)').text())) >= (fltr4op*parseInt(fltr4)) ))) ) &&
             (!fltr5  || ($(this).find('td:nth-last-child(3)').text() && (fltr5  && $(this).find('td:nth-last-child(3)').text().toLowerCase().indexOf(fltr5.toLowerCase()) >= 0) || (fltr5op && ( (fltr5op*parseInt($(this).find('td:nth-last-child(3)').text())) >= (fltr5op*parseInt(fltr5)) ))) ) &&
             (!fltr6  || ($(this).find('td:nth-last-child(2)').text() && ((fltr6 == '*' && $(this).find('td:nth-last-child(2)').text()!="") || (fltr6 && $(this).find('td:nth-last-child(2)').text().toLowerCase().indexOf(fltr6.toLowerCase()) >= 0))) ) 
           ) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });

} // end: filter_muts_table();


function toggle_monthly() {
    if( $('#monthly_btn').prop("value") == 'Hide' ) {
        $("#monthly_btn").prop('value', 'Show');
        $('tr.mnthly').hide();
    } else {
        $("#monthly_btn").prop('value', 'Hide');
        $('tr.mnthly').show();
    }
}


// **************************************************************************
// Subscriptions  MB
// **************************************************************************

// function show_sub_details() is placed in cusconow-sbscrbr.js because it is used in the Subscriber interface

function build_fltrd_sub_table(func) {

    // set end date field 'n' to 'N'
    if ($('#fltr_sub_end_date').val() == 'n') $('#fltr_sub_end_date').val('N');

    // clean out table except for filter
    $('#sub_table').find("tr:gt(1)").remove();
    $('#sub_table td').removeClass('dvdr');


    // define filters in sub_table
    if ($('#fltr_sbscrbr_login').val() != "" ) var fltr1 = $('#fltr_sbscrbr_login').val();
    if ($('#fltr_poi_shrt_name').val() != "" ) var fltr2 = $('#fltr_poi_shrt_name').val();
    if ($('#fltr_fk_poi_type_id').val() != "" ) var fltr3 = $('#fltr_fk_poi_type_id').val();
    if ($('#fltr_subtype_name').val() != "" ) var fltr4 = $('#fltr_subtype_name').val();
    if ($('#fltr_num_of_est').val() != "" ) var fltr5 = $('#fltr_num_of_est').val();
    if ($('#fltr_sub_amount').val() != "" ) var fltr6 = $('#fltr_sub_amount').val();
    if ($('#fltr_sub_strt_date').val() != "" ) var fltr7 = $('#fltr_sub_strt_date').val();
    if ($('#fltr_sub_end_date').val() != "" ) var fltr8 = $('#fltr_sub_end_date').val();
    if ($('#fltr_sub_final_bal').val() != "" ) var fltr9 = $('#fltr_sub_final_bal').val();

    // initialize 
    var group = false;
    var prev = -1;
    var bundle = [];

    // build table, first row is already there

    for ( var i=0; i<Subs.length; i++ ) {

        // check whether in filter
        if ( ( !fltr1 || (Subs[i].sbscrbr_login && ((fltr1=='*' && Subs[i].sbscrbr_login) || (fltr1 && Subs[i].sbscrbr_login.indexOf(fltr1) >= 0))) ) && 
             ( !fltr2 || (Subs[i].poi_shrt_name && ((fltr2=='*' && Subs[i].poi_shrt_name) || (fltr2 && Subs[i].poi_shrt_name.toLowerCase().indexOf(fltr2.toLowerCase()) >= 0))) ) &&
             ( !fltr3 || (Subs[i].fk_poi_type_id && (fltr3 && Subs[i].fk_poi_type_id.toLowerCase().indexOf(fltr3.toLowerCase()) >= 0)) ) &&
             ( !fltr4 || (Subs[i].subtype_name  &&  (fltr4 &&   Subs[i].subtype_name.toLowerCase().indexOf(fltr4.toLowerCase()) >= 0)) ) &&
             ( !fltr5 || (Subs[i].num_of_est    && ((fltr5=='*' && Subs[i].num_of_est)    || (fltr5 && Subs[i].num_of_est.indexOf(fltr5) >= 0)))    ) &&
             ( !fltr6 || (Subs[i].sub_amount    && ((fltr6=='*' && Subs[i].sub_amount)    || (fltr6 && Subs[i].sub_amount.indexOf(fltr6) >= 0)))    ) &&
             ( !fltr7 || (Subs[i].sub_strt_date && ((fltr7=='*' && Subs[i].sub_strt_date) || (fltr7 && loc(Subs[i].sub_strt_date).toLowerCase().indexOf(fltr7.toLowerCase()) >= 0))) ) &&
             ( !fltr8 || (Subs[i].sub_end_date  && ((fltr8=='*' && Subs[i].sub_end_date)  || (fltr8 && fltr8!=='N' && loc(Subs[i].sub_end_date).toLowerCase().indexOf(fltr8.toLowerCase()) >= 0))) || (fltr8==='N' && !Subs[i].sub_end_date) ) &&
             ( !fltr9 || (Subs[i].sub_final_bal && ((fltr9=='*' && Subs[i].sub_final_bal) || (fltr9 && Subs[i].sub_final_bal.indexOf(fltr9) >= 0))) )
           ) {

            // check whether last row of group and then build summary row
            if ( prev!=-1 && Subs[i].sbscrbr_login != Subs[prev].sbscrbr_login ) { // new sbscrbr_login so need dvdr at minimum
                if ( group ) {
                    $('#sub_table').find('tbody')
                        .append($('<tr>')
                            .append($('<td>'))
                            .append($('<td>'))
                            .append($('<td>'))
                            .append($('<td>'))
                            .append($('<td>').addClass('numb'))
                            .append($('<td>').addClass('numb'))
                            .append($('<td>'))
                            .append($('<td>'))
                            .append($('<td>').addClass('numb'))
                            .append($('<td>').append("<input type='submit' name='"+Subs[prev].pk_sbscrbr_id+"' value='Show all' onclick=\"hide_sub_details();\" />"))
                    );
                    group = false;
                }
                // it is end of (group of) sbscrbr_login: divider
                $('#sub_table tr:last td').addClass('dvdr');
            }
            // create row
            $('#sub_table').find('tbody')
                .append($('<tr>')
                    .append($('<td>').text(Subs[i].sbscrbr_login))
                    .append($('<td>').html(escapeSpecialChars(Subs[i].poi_shrt_name)))
                    .append($('<td>').text(Subs[i].fk_poi_type_id))
                    .append($('<td>').html(escapeSpecialChars(Subs[i].subtype_name)))
                    .append($('<td>').html(Subs[i].num_of_est).addClass('numb'))
                    .append($('<td>').html(amount(Subs[i].sub_amount)).addClass('numb'))
                    .append($('<td>').text(loc(Subs[i].sub_strt_date)))
                    .append($('<td>').text(loc(Subs[i].sub_end_date)))
                    .append($('<td>').html(amount(Subs[i].sub_final_bal)).addClass('numb'))
            );
            // check whether part of a bundle and then correct sub fee amount
            if (Subs[i].ref_sub_id) $('#sub_table tr:last td:eq(5)').html("free");

            // check whether cancelled sub and then change font color
            if (Subs[i].sub_end_date) $('#sub_table tr:last td+td').css('color','#a0a0a0');

            // check whether scheduled for cancellation, then calculate end date and update cell
            if (Subs[i].must_end_eop=="1") $('#sub_table tr:last td:eq(7)').html("<strong>end of period (" + calc_eop(Subs[i].sub_strt_date, Subs[i].sub_cycle, false) + ")</strong>");

            //  create buttons for this row
            $('#sub_table tr:last').append($('<td>').append("<input type='button' name='"+i+"' value='Details'/>"));
            if (Subs[i].pk_sub_id) $('#sub_table tr:last td:last').append("&nbsp;<input type='submit' name='"+Subs[i].pk_sub_id+"' value='Delete'/>");

            // check whether this row makes it a group (and then erase sbscrbr_login) 
            if (prev!=-1 && (Subs[i].sbscrbr_login == Subs[prev].sbscrbr_login)) {
                group = true;
                $('#sub_table tr:last td:first').text(''); // erase Subscriber ID
            }

            // copy over sbscrbr_login id for next row's check
            prev = i;
        } // end: filter check
    }
    // maybe final rows were group as well
    if ( group ) {
        $('#sub_table').find('tbody')
            .append($('<tr>')
                .append($('<td>'))
                .append($('<td>'))
                .append($('<td>'))
                .append($('<td>'))
                .append($('<td>').addClass('numb'))
                .append($('<td>').addClass('numb'))
                .append($('<td>'))
                .append($('<td>'))
                .append($('<td>').addClass('numb'))
                .append($('<td>').append("<input type='submit' name='"+Subs[prev].pk_sbscrbr_id+"' value='Show all'/>"))
        );
    }
    // in any case final row needs divider indication
    $('#sub_table tr:last td').addClass('dvdr');

    // all created buttons need to be styled and working
    $('#sub_table input[type="button"][value="Details"]').addClass('button-primary').click(function() {
        show_sub_details(parseInt($(this).attr('name'), 10));
    });
    $('#sub_table input[type="submit"][value="Delete"]').addClass('button-secondary').click(function () {
        confirm_deletion('sure_'+func);
    });
    $('#sub_table input[type="submit"][value="Show all"]').addClass('button-secondary');

} // end: build_fltrd_sub_table();

function hide_sub_details() {
    $('#subsnr').val('');
    $('#sub_details span.display-only:not(#wrn_sub_mail, #gets_no_mail)').text('X');
    $('#sub_details span.display-only:not(#wrn_sub_mail, #gets_no_mail)').html('X');
    $('#sub_details span.display-only:not(#wrn_sub_mail, #gets_no_mail)').removeClass('verf-err verf-ok');
    $('#sub_details').hide();

    // scroll to list
    $('html, body').animate({
        scrollTop: $('#cus-subscriptions-mb').offset().top - 40 // 40 px = top nav bar (32px) + margin
    }, 700);

} // end: hide_sub_details();


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
    if ($('#fltr_gets_sub_mails').val().toLowerCase() === "y" ) { 
        var fltr6 = 1; 
    } else if ($('#fltr_gets_sub_mails').val().toLowerCase() === "n" ) { 
        var fltr6 = 0; 
    } else if ($('#fltr_gets_sub_mails').val() === "*" ) { 
        var fltr6 = "*"; 
    }
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
             ( fltr6 == undefined || (Sbscrbrs[i].gets_sub_mails && ((fltr6=='*' && Sbscrbrs[i].gets_sub_mails) || (fltr6 != undefined && Sbscrbrs[i].gets_sub_mails == fltr6))) ) &&
             ( fltr7 == undefined || (Sbscrbrs[i].gets_html_mail && ((fltr7=='*' && Sbscrbrs[i].gets_html_mail) || (fltr7 != undefined && Sbscrbrs[i].gets_html_mail == fltr7))) ) &&
             ( fltr8 == undefined || (Sbscrbrs[i].is_usr_blocked && ((fltr8=='*' && Sbscrbrs[i].is_usr_blocked) || (fltr8 != undefined && Sbscrbrs[i].is_usr_blocked == fltr8))) ) &&
             ( !fltr9 || (Sbscrbrs[i].sbscrbr_notes && ((fltr9=='*' && Sbscrbrs[i].sbscrbr_notes) || (fltr9 && Sbscrbrs[i].sbscrbr_notes.toLowerCase().indexOf(fltr9.toLowerCase()) >= 0))) )
           ) {

            // compile full sbscrbr name
            var sbscrbr_name = (Sbscrbrs[i].last == undefined || Sbscrbrs[i].last == "" ) ? "" : Sbscrbrs[i].last + ", ";
            sbscrbr_name += (Sbscrbrs[i].first == undefined) ? "" : Sbscrbrs[i].first;

            // create table row
            $('#sbscrbr_table').find('tbody')
                .append($('<tr>')
                    .append($('<td>').text(Sbscrbrs[i].pk_sbscrbr_id).addClass('numb'))
                    .append($('<td>').text(Sbscrbrs[i].sbscrbr_login))
                    .append($('<td>').html(escapeSpecialChars(sbscrbr_name)))
                    .append($('<td>').html(escapeSpecialChars(Sbscrbrs[i].user_email)))
                    .append($('<td>').text(loc(Sbscrbrs[i].user_registered)))
            );
            if (Sbscrbrs[i].gets_sub_mails == "1") { $('#sbscrbr_table tr:last').append($('<td>').text("Y").addClass('chck'));} else { $('#sbscrbr_table tr:last').append($('<td>')); } 
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

    // set background for chckbox fields with 'Y' 
    $('#sbscrbr_table td.chck:contains(\"Y\")').css('background-color','#f9f7ed');

    // all created buttons need to be styled and working
    $('#sbscrbr_table input[type="submit"][value="Edit"]').addClass('button-primary');
    $('#sbscrbr_table input[type="submit"][value="Delete"]').addClass('button-secondary').click(function () {
        confirm_deletion('sure_'+func);
    });
} // end: build_fltrd_sbscrbrs_table();


// **************************************************************************
// Establishments and their Owners  MB
// **************************************************************************

function build_fltrd_poisests_table() {

    // set checkbox fltr fields 
    $('#sub_table td.fltr input.chk').each(function() {
        $(this).val( $(this).val().toUpperCase() );
    });

    // set sort order
    var prop1 = "poi_shrt_name";
    var prop2 = "pk_poi_id";
    var prop3 = "";

    if( $('#fltr_pk_poi_id').val() != "" ) {
        prop1 = "pk_poi_id";
        prop2 = "";
        prop3 = "";
    }
    if ( $('#fltr_pk_est_id').val() != "" || $('#fltr_est_fk_sbscrbr_id').val() != "" || $('#fltr_est_sbscrbr_login').val() != "" || $('#fltr_est_is_usr_blocked').val() != "" ) {
        prop1 = "sbscrbr_login";
        prop2 = "poi_shrt_name";
        prop3 = "pk_poi_id";
        if ($('#fltr_pk_est_id').val() != "") prop1 = "pk_est_id";
        if ($('#fltr_est_fk_sbscrbr_id').val() != "") prop1 = "fk_sbscrbr_id";
    }

    // sort array
    POIsEsts.sort( srt_by(prop1, prop2, prop3) );

    // clean out table except for filter
    $('#est_table').find("tr:gt(1)").remove();

    // define filters in est_table
    if ($('#fltr_pk_poi_id').val() != "" ) var fltr1 = $('#fltr_pk_poi_id').val();
    if ($('#fltr_poi_shrt_name').val() != "" ) var fltr2 = $('#fltr_poi_shrt_name').val();
    if ($('#fltr_fk_poi_type_id').val() != "" ) var fltr3 = $('#fltr_fk_poi_type_id').val();
    if ($('#fltr_addr_street').val() != "" ) var fltr4 = $('#fltr_addr_street').val();
    if ($('#fltr_addr_area').val() != "" ) var fltr5 = $('#fltr_addr_area').val();
    if ($('#fltr_pk_est_id').val() != "" ) var fltr6 = $('#fltr_pk_est_id').val();
    if ($('#fltr_est_fk_sbscrbr_id').val() != "" ) var fltr7 = $('#fltr_est_fk_sbscrbr_id').val();
    if ($('#fltr_est_sbscrbr_login').val() != "" ) var fltr8 = $('#fltr_est_sbscrbr_login').val();
    if ($('#fltr_est_is_usr_blocked').val().toLowerCase() === "y" ) { 
        var fltr9 = 1; 
    } else if ($('#fltr_est_is_usr_blocked').val().toLowerCase() === "n" ) { 
        var fltr9 = 0; 
    } else if ($('#fltr_est_is_usr_blocked').val() === "*" ) { 
        var fltr9 = "*"; 
    }

    // build table, first row is already there
    for ( var i=0; i<POIsEsts.length; i++ ) {

        // check whether in filter
        if ( ( !fltr1 || (POIsEsts[i].pk_poi_id &&     ((fltr1=='*' && POIsEsts[i].pk_poi_id)     || (fltr1 && POIsEsts[i].pk_poi_id.indexOf(fltr1) >= 0))) ) && 
             ( !fltr2 || (POIsEsts[i].poi_shrt_name && ((fltr2=='*' && POIsEsts[i].poi_shrt_name) || (fltr2 && POIsEsts[i].poi_shrt_name.toLowerCase().indexOf(fltr2.toLowerCase()) >= 0))) ) &&
             ( !fltr3 || (POIsEsts[i].fk_poi_type_id && (fltr3 && POIsEsts[i].fk_poi_type_id.toLowerCase().indexOf(fltr3.toLowerCase()) >= 0)) ) &&
             ( !fltr4 || (POIsEsts[i].addr_street &&   ((fltr4=='*' && POIsEsts[i].addr_street)   || (fltr4 && POIsEsts[i].addr_street.toLowerCase().indexOf(fltr4.toLowerCase()) >= 0))) ) &&
             ( !fltr5 || (POIsEsts[i].addr_area &&     ((fltr5=='*' && POIsEsts[i].addr_area)     || (fltr5 && POIsEsts[i].addr_area.toLowerCase().indexOf(fltr5.toLowerCase()) >= 0))) ) &&
             ( !fltr6 || (POIsEsts[i].pk_est_id &&     ((fltr6=='*' && POIsEsts[i].pk_est_id)     || (fltr6 && POIsEsts[i].pk_est_id.indexOf(fltr6) >= 0))) ) && 
             ( !fltr7 || (POIsEsts[i].fk_sbscrbr_id && ((fltr7=='*' && POIsEsts[i].fk_sbscrbr_id) || (fltr7 && POIsEsts[i].fk_sbscrbr_id.indexOf(fltr7) >= 0))) ) && 
             ( !fltr8 || (POIsEsts[i].sbscrbr_login && ((fltr8=='*' && POIsEsts[i].sbscrbr_login) || (fltr8 && POIsEsts[i].sbscrbr_login.toLowerCase().indexOf(fltr8.toLowerCase()) >= 0))) ) &&
             ( fltr9 == undefined || (POIsEsts[i].is_usr_blocked && ((fltr9=='*' && POIsEsts[i].is_usr_blocked) || (fltr9 != undefined && POIsEsts[i].is_usr_blocked == fltr9))) ) 
           ) {
                 
            $('#est_table').find('tbody')
                .append($('<tr>')
                    .append($('<td>').text(POIsEsts[i].pk_poi_id).addClass('numb'))
                    .append($('<td>').html(escapeSpecialChars(POIsEsts[i].poi_shrt_name)))
                    .append($('<td>').text(POIsEsts[i].fk_poi_type_id))
                    .append($('<td>').html(escapeSpecialChars(POIsEsts[i].addr_street)))
                    .append($('<td>').html(escapeSpecialChars(POIsEsts[i].addr_area)))
                    .append($('<td>').text(POIsEsts[i].pk_est_id).addClass('numb'))
                    .append($('<td>').text(POIsEsts[i].fk_sbscrbr_id).addClass('numb'))
                    .append($('<td>').text(POIsEsts[i].sbscrbr_login))
            );
            if (POIsEsts[i].is_usr_blocked == "1") {
                $('#est_table tr:last').append($('<td>').text("Y").addClass('chck'));
            //} else if (POIsEsts[i].is_usr_blocked == "0") {
            //    $('#est_table tr:last').append($('<td>').text("N").attr('align', 'center'));
            } else {
                $('#est_table tr:last').append($('<td>'));
            } // is_usr_blocked

            //  create buttons for this row
            $('#est_table tr:last').append($('<td>').append("<input type='button' name='"+i+"' value='Details'/>"));
            if (POIsEsts[i].pk_est_id) $('#est_table tr:last td:last').append("&nbsp;<input type='submit' name='"+POIsEsts[i].pk_est_id+"' value='Delete'/>");

        } // filter check

    }
    // all created buttons need to be styled and working
    $('#est_table input[type="button"][value="Details"]').addClass('button-primary').click(function() {
        show_est_details(parseInt($(this).attr('name'), 10));
    });
    $('#est_table input[type="submit"][value="Delete"]').addClass('button-secondary').click(function () {
        confirm_deletion('sure_Ests');
    });
} // end: build_fltrd_poisests_table();

function show_est_details(id) {
    if (POIsEsts[id].pk_est_id) var est_edit = true;

    if (POIsEsts[id].pk_poi_id) {
        $('#pk_poi_id').text(POIsEsts[id].pk_poi_id);
        $('#poi_shrt_name').html(escapeSpecialChars(POIsEsts[id].poi_shrt_name));
        $('#fk_poi_type_id').text(POIsEsts[id].fk_poi_type_id);
        $('#addr_street').html(escapeSpecialChars(POIsEsts[id].addr_street));
        $('#addr_area').html(escapeSpecialChars(POIsEsts[id].addr_area));
    }

    if (est_edit) {
        $('#est_details .has_est').show();
        $('#pk_est_id').text(POIsEsts[id].pk_est_id);
        $('#editkey').val(POIsEsts[id].pk_est_id);      // when edit the editkey contains pk_EST_id!
        $('#fk_sbscrbr_id').val(POIsEsts[id].fk_sbscrbr_id);
        $('#est_sbscrbr_login').val(POIsEsts[id].sbscrbr_login);
        
        $('#est_details h4').text("Establishment details");
        $('#is_allwd_feat').prop('checked', parseInt(POIsEsts[id].is_allwd_feat) );
        $('#sbscrbr_display').show();
        $('#sbscrbr_filter').hide();
        $('#est_details p.edit_btns').show();
        $('#est_details p.add_btns').hide();
    } else {
        $('#pk_est_id').text("");
        $('#editkey').val(POIsEsts[id].pk_poi_id);      // when create the editkey contains pk_POI_id!
        $('#fk_sbscrbr_id').val("");
        $('#est_sbscrbr_login').val("");

        $('#est_details .has_est').hide();
        $('#est_details h4').text("POI details");
        $('#sbscrbr_display').hide();
        $('#test_est_sbscrbr_login').val("");
        $('#sbscrbr_filter').show();
        $('#est_details p.edit_btns').hide();
        $('#est_details p.add_btns').show();
    }

    $('#fltr_sbscrbr_results').hide();
    $('#sbscrbr_details').hide();
    $('#test_est_sbscrbr_login').removeClass('verf-err verf-ok');

    $('#est_details').show()

    // scroll to form
    $('html, body').animate({
        scrollTop: $("#est_details").offset().top - 40 // 40 px = top nav bar (32px) + margin
    }, 700);
} // end: show_est_details(id);

function show_sbscrbr_filter() {
    $('#test_est_sbscrbr_login').val($('#est_sbscrbr_login').val());
    $('#sbscrbr_display input[type="button"]').hide();
    $('#sbscrbr_filter').show();
}

function select_sbscrbr() {
    $('#fk_sbscrbr_id').val(Sbscrbrs[$('#sbscrbr_logins option:selected').val()].pk_sbscrbr_id);
    $('#est_sbscrbr_login').val($('#sbscrbr_logins option:selected').text());
    $('#sbscrbr_display').show();
    $('#sbscrbr_display input[type="button"]').show();
    $('#sbscrbr_filter').hide();
    $('#fltr_sbscrbr_results').hide();
    $('input[type="submit"]').removeAttr('disabled');
}

function filter_sbscrbrs() {
    var snippet = $('#test_est_sbscrbr_login').val();
    var res = 0;
    
    // reset
    $('#test_est_sbscrbr_login').removeClass("verf-ok verf-err");
    $('#sbscrbr_logins option').remove();
    $('#fltr_sbscrbr_results').hide();
    $('#sbscrbr_details').hide();
    $('#sbscrbr_display').show();
    $('#sbscrbr_display input[type="button"]').hide();

    $('#fltr_sbscrbr_results .multi_res').show();
    // reset all errors
    $('#err1_fltr_sbscrbr_login').hide();
    $('#err2_fltr_sbscrbr_login').hide();

    if ( snippet.length < 3 ) {
        $('#err1_fltr_sbscrbr_login').show();
        $('#test_est_sbscrbr_login').addClass("verf-err");
        return false;
    }

    // filter list and create options in select
    for (var i = 0; i < Sbscrbrs.length; i++) {
        if (Sbscrbrs[i].sbscrbr_login.indexOf(snippet) != -1) {
            $('#sbscrbr_logins').append("<option value=" +i+ ">" +Sbscrbrs[i].sbscrbr_login+ "</option>");
            res++;
        }
    }

    // validate results
    if ( res >= 2 ) {
        $('#fltr_sbscrbr_results input[type="button"]').attr('disabled','disabled');
        $('#fltr_sbscrbr_results').show();
    } else if ( res == 1 ) {
        $('#test_est_sbscrbr_login').val( $('#sbscrbr_logins option:selected').text() );
        $('#test_est_sbscrbr_login').addClass("verf-ok");
        $('#fltr_sbscrbr_results').show();
        $('#sbscrbr_logins').val($('#sbscrbr_logins option:eq(0)').val());
        $('#fltr_sbscrbr_results .multi_res').hide();
        show_sbscrbr_det();
    } else if ( res == 0 ) {
        $('#err2_fltr_sbscrbr_login').show();
        $('#test_est_sbscrbr_login').addClass("verf-err");
        $('#fltr_sbscrbr_results').hide();
    }
}

function show_sbscrbr_det() {
    var id = 0;
    if ( $('#sbscrbr_logins')[0].selectedIndex != -1) {
        id = $('#sbscrbr_logins option:selected').val();
        $('#test_est_sbscrbr_login').val( Sbscrbrs[id].sbscrbr_login );
        $('#est_user_registered').text( loc(Sbscrbrs[id].user_registered) );
        $('#est_sbscrbr_name').html( escapeSpecialChars(Sbscrbrs[id].first) + "&nbsp;" + escapeSpecialChars(Sbscrbrs[id].last) );
        $('#est_user_email').text( Sbscrbrs[id].user_email );
        $('#est_sbscrbr_notes').html(escapeSpecialChars(Sbscrbrs[id].sbscrbr_notes));

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

function calc_eop(startDate, cycle, long_date) {
    var start = new Date(startDate.split(" ")[0]);
    var now = new Date();
    var dayOfMonth = start.getDate();

    start.setDate(dayOfMonth + 2);                  // sync up with start of next day (we're in western hemisphere, so local time is - GMT)
    start.setHours(3, 0, 0, 0);                     // the day starts at 03:00h in the morning
    if (start > now) return (long_date)? "end of period, but nothing charged yet!" : "not charged yet!";

    // we have baseline, now use cycle to find eops
    var month = start.getMonth();
    var year = start.getFullYear();
    do {
        month += parseInt(cycle); 
        if (month>11) {
            year += 1;
            month -= 12;
        }
        start.setMonth(month);
        start.setFullYear(year);
    } while (start < now); // check afterwards, so at least one period and start always in future

    return (long_date)? start.toString().split("GMT")[0] : start.toDateString();
}
