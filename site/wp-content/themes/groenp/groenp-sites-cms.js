/******************************************************************************/
/*                                                              Pieter Groen  */
/*  Version 0.1 - May 26, 2020                                                */
/*                                                                            */
/*  JS for Groen Productions - Sites Mgmt website CMS in WordPress            */
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
