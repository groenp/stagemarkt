/******************************************************************************/
/*                                                              Pieter Groen  */
/*  Version 1.0 - October 01, 2014                                            */
/*                                                                            */
/*  JS for for Subscriber interface of CuscoNow website                       */
/*  includes JS for asynchronous communications with CuscoNow website         */
/*                                                                            */
/******************************************************************************/

// We're only using jQuery here
$ = jQuery;
//alert("cusconow-sbscrbr.js loaded");                              // DEBUG //


// ****************************************************************
// CuscoNow - Load Home page behaviour on document.ready
//
// (jQuery adjustments: check with each update to WP)
// ****************************************************************

var InitialValues = [];         // initial values for form after loading of page
var Changed = false;            // to store whether form content has changed

$(document).ready(function ($) {

    // Initialize es/en msgs (on document.ready because Lng needs to be copied in first from php page)
    if ($('#cus-est-details-mb').length) {
        var Async_scs = (Lng == "es") ? "La información de este Establicimiento a sido cargada." : "Establishment data has been loaded.";
        var Async_err = (Lng == "es") ? "No hay información de este Establicimiento para cargar." : "There is no establishment data to be loaded.";
        // var Change_prmpt defined in php
        var All_text = (Lng == "es") ? " - Todos los establecimientos" : " - All establishments";
    }
    // Remove screen options tab
    // $('#screen-options-link-wrap').hide();

    // Remove expand/collapse function and arrows, but only on the est-details box
    $('#cus-est-details-mb div.handlediv').hide();
    $('#cus-est-details-mb').removeClass('closed');
    $('#cus-est-details-mb').click(function () {
        jQuery(this).removeClass('closed');
    });
    $('#cus-est-details-mb div.multipage').hide();      // hide all multipage divs


    // ************************************************************
    // Create Menu onclicks and roll-over behaviour
    //
    // ************************************************************
    $('#cus-task-menu a').click(function () {

        // Remove any general feedback msgs
        $('p.gen').remove();


        // Check whether any changes in form and user wants to save
        if (Changed && !confirm(Change_prmpt)) return false;
        Changed = false;

        // Set U/I mouse behaviour
        $('#cus-task-menu a').removeClass("menu-hl");   // reset all to unselected
        $('#cus-task-menu a').hover(function () {       // reset all hovers to normal
            $(this).addClass("menu-hl");
        }, function () {
            $(this).removeClass("menu-hl");
        });
        $(this).hover(function () {                     // make this hover to be always selected
            $(this).addClass("menu-hl");
        }, function () {
            $(this).addClass("menu-hl");
        });

        $(this).addClass("menu-hl");                    // set this background to selected immediately

        $('#cus-est-details-mb div.multipage').hide();  // hide all multipage divs
        $($(this).attr('href')).show();                 // display multipage div that is named in the href of anchor

        // Collect choice and set bookmark
        var key = $(this).attr('data-id');
        $('input[name="bkmrk"]').val(this.className.split(" ")[0] + "%" + key);


        // ********************************************************
        // Billing links specific (except for bill all)
        // ********************************************************
        if ($(this).hasClass('bill')) {

            // Set title
            $('#bill_title').text(" - " + $(this).text());

            // Load and show subscription details
            $('#sub_details').show();
            show_sub_details(key);
        }

        // ********************************************************
        // Bill-all link specific
        // ********************************************************
        if ($(this).hasClass('bill_all')) {

            // Set title
            $('#bill_title').text(All_text);

            // Hide subscription details
            $('#sub_details').hide();
        }

        // ********************************************************
        // All billing links specific
        // ********************************************************
        if ($(this).hasClass('bill') || $(this).hasClass('bill_all')) {

            // Create visual feedback
            $('#loader').show();
            $('#muts').hide();

            // Recalculate the height for the billing page
            align_heights('cus-my-billing');
            $('#loader').css('height', $('#cus-my-billing').height() - 36);

            // Capture data to be sent with the request
            if ($(this).attr("data-id")) {
                id = $(this).attr("data-id");
            } else {
                id = "all";
            }
            var user = $(this).attr("data-user");
            var nonce = $(this).attr("data-nonce");

            // Set up ajax
            $.ajax({
                type: "post",
                dataType: "json",
                url: cusconowAsync.ajaxurl,
                data: { action: "cusconow_get_muts", id: id, user: user, nonce: nonce },

                success: function (response) {
                    $('#loader').hide();
                    $('#muts').show();
                    if (response.type == "success") {

                        // Load the results
                        $('#muts div').html(response.html);
                    } else {
                        // Give feedback on failure
                        var msg = (Lng == "es") ? "No se pudo recuperar los datos." : "Failed to retrieve data.";
                        $('#muts div').html("<p class='err-msg'>" + msg + "</p>");
                    }
                    // Recalculate the height for the billing page
                    align_heights('cus-my-billing');
                }
            });
        }

        // ********************************************************
        // Feature links specific
        // ********************************************************
        if ($(this).hasClass('feat')) {

            // Set title
            $('#feat_title').text($(this).parent().parent().prev().text() + " - ");

            // Create visual feedback
            //$('#cus-my-feats form p.err-msg, #cus-my-feats form p.fbk-msg').remove();
            $('.edit-feat').hide();
            $('.rdo-feat').show();
            $('#loader').show();

            // Show correct sections depending on the selected establishment's poi-type
            if (Hospitality.indexOf(Subs[$(this).attr("data-id")].fk_poi_type_id) == -1) {   // hospitality (defined in cusconow_site_management.php)
                $('#cus-my-feats .hospitality').hide();
            } else {
                $('#cus-my-feats .hospitality').show();
            }
            if (Bar_feat.indexOf(Subs[$(this).attr("data-id")].fk_poi_type_id) == -1) {      // bar_feat (defined in cusconow_site_management.php)
                $('#cus-my-feats .bar-feat').hide();
            } else {
                $('#cus-my-feats .bar-feat').show();
            }
            if (Rest_feat.indexOf(Subs[$(this).attr("data-id")].fk_poi_type_id) == -1) {     // rest_feat (defined in cusconow_site_management.php)
                $('#cus-my-feats .rest-feat').hide();
            } else {
                $('#cus-my-feats .rest-feat').show();
            }

            // Recalculate the height for the feats page
            align_heights('cus-my-feats');
            $('#loader').css('height', $('#cus-my-feats').height() - 36);

            // Capture data to be sent with the request
            if ($(this).attr("data-id")) {
                id = $(this).attr("data-id");
            } else {
                id = "all";
            }
            user = $(this).attr("data-user");
            nonce = $(this).attr("data-nonce");

            // Set up ajax
            $.ajax({
                type: "post",
                dataType: "json",
                url: cusconowAsync.ajaxurl,
                data: { action: "cusconow_get_feats", id: id, user: user, nonce: nonce },

                success: function (response) {
                    $('#loader').hide();
                    if (response.type == "success") {

                        // Show/hide edit-feat (real check on submission)
                        if (response.data.is_allwd_feat == 1 || typeof Sbscrbrs !== 'undefined') {
                            $('.edit-feat').show();
                            $('.rdo-feat').hide();
                            $('#cus-my-feats input, #cus-my-feats select').prop('disabled', false);
                            $('#cus-my-feats select').show();
                        } else {
                            $('.edit-feat').hide();
                            $('.rdo-feat').show();
                        }

                        // Load the results
                        load_data(response.data);
                        load_phone_nrs(response.phone_nrs);
                        load_flex_list(response.ccs, "cc_", "fr_cc_id");
                        load_flex_list(response.atmos, "atmos_", "fr_atmosph_id");
                        load_flex_list(response.cuisines, "cuisine_", "fr_cuisine_id");

                        // If read-only hide the selects that have no value
                        if (response.data.is_allwd_feat == 1 || typeof Sbscrbrs !== 'undefined') {
                        } else {
                            $('#cus-my-feats input, #cus-my-feats select').prop('disabled', true);
                            $('#cus-my-feats select').filter(function () {
                                return !this.value;
                            }).hide();
                        }

                        // Store initial form values for #cus-my-feats
                        InitialValues = [];
                        $.each($('#cus-my-feats form').serializeArray(), function () {
                            InitialValues[this.name] = this.value;
                        });
                        $('#cus-my-feats form').prepend("<p class='gen fbk-msg'>" + Async_scs + "</p>");
                    } else {
                        $('#cus-my-feats form').prepend("<p class='gen err-msg'>" + Async_err + "</p>");
                    }
                    // Recalculate the height for the feats page
                    align_heights('cus-my-feats');
                }
            });


        }

        // ********************************************************
        // Hours links specific
        // ********************************************************
        if ($(this).hasClass('hour')) {

            // Set title
            $('#hour_title').text($(this).parent().parent().prev().text() + " - ");

            // Create visual feedback
            //$('#cus-my-hours form p.err-msg, #cus-my-hours form p.fbk-msg').remove();
            $('#loader').show();

            // Recalculate the height for the hours page
            align_heights('cus-my-hours');
            $('#loader').css('height', $('#cus-my-hours').height() - 36);

            // Capture data to be sent with the request
            if ($(this).attr("data-id")) {
                id = $(this).attr("data-id");
            } else {
                id = "all";
            }
            user = $(this).attr("data-user");
            nonce = $(this).attr("data-nonce");

            // Set up ajax
            $.ajax({
                type: "post",
                dataType: "json",
                url: cusconowAsync.ajaxurl,
                data: { action: "cusconow_get_hours", id: id, user: user, nonce: nonce },

                success: function (response) {
                    $('#loader').hide();
                    if (response.type == "success") {

                        // load the results
                        load_data(response.data);


                        // Store initial form values for #cus-my-hours
                        InitialValues = [];
                        $.each($('#cus-my-hours form').serializeArray(), function () {
                            InitialValues[this.name] = this.value;
                        });
                        $('#cus-my-hours form').prepend("<p class='gen fbk-msg'>" + Async_scs + "</p>");
                    } else {
                        $('#cus-my-hours form').prepend("<p class='gen err-msg'>" + Async_err + "</p>");
                    }
                    // Recalculate the height for the hours page
                    align_heights('cus-my-hours');
                }
            });

        }

        // ********************************************************
        // Menu links specific
        // ********************************************************
        if ($(this).hasClass('menu')) {

            // Set title
            $('#menu_title').text($(this).parent().parent().prev().text() + " - ");

            // Create visual feedback
            //$('#cus-my-menus  p.err-msg, #cus-my-menus p.fbk-msg').remove();
            $('#loader').show();

            // Recalculate the height for the menus page
            align_heights('cus-my-menus');
            $('#loader').css('height', $('#cus-my-menus').height() - 36);

            // Capture data to be sent with the request
            if ($(this).attr("data-id")) {
                id = $(this).attr("data-id");
            } else {
                id = "all";
            }
            user = $(this).attr("data-user");
            nonce = $(this).attr("data-nonce");

            // Set up ajax
            $.ajax({
                type: "post",
                dataType: "json",
                url: cusconowAsync.ajaxurl,
                data: { action: "cusconow_get_menus", id: id, user: user, nonce: nonce },

                success: function (response) {
                    $('#loader').hide();

                    // Clean tabs
                    clean_tabs();

                    if (response.type == "success") {

                        // Load the results
                        load_tabs(response.data);

                        // Store initial form values for #cus-my-menus
                        InitialValues = [];
                        $.each($('#cus-my-menus form').serializeArray(), function () {
                            InitialValues[this.name] = this.value;
                        });
                        $('#cus-my-menus ul.tabs').prepend("<p class='gen fbk-msg'>" + Async_scs + "</p>");
                    } else {
                        $('#cus-my-menus ul.tabs').prepend("<p class='gen err-msg'>" + Async_err + "</p>");
                    }

                    // Recalculate the height for the menus page
                    align_heights('cus-my-menus');
                }
            });

        }
        // ********************************************************

        return false;

    }); // End of: Menu behaviour

    // Do a click on the highlighted item
    $('a.menu-hl').triggerHandler("click");


    // ************************************************************
    //  SPECIAL CONTROLS: DATE, TIME, TELNR changes and blurs
    //
    // ************************************************************

    // Hour control changes 
    $('span.time input.numb[id$="_hr"]').focus(function () {
        if ($(this).val() == "hh") $(this).val("").removeClass('prompt');                                   // if control gets focus and has prmpt inside, take it out and remove styling
    });
    $('span.time input.numb[id$="_hr"]').blur(function () {                                                 // if control loses focus, capture the min part of the hidden control with value, but only if it has actual input
        var snip = (($(this).next().next().next().val().split(':')[1] != undefined) && ($(this).next().next().next().val().split(':')[1] != "mm")) ? $(this).next().next().next().val().split(':')[1] : "";
        var inp = ($(this).val().length == 1) ? "0" + $(this).val() : $(this).val();                        // sanitize input, by padding with 0 if neccesary
        $(this).val(inp);
        if (inp == "" || inp == "hh") $(this).val("hh").addClass('prompt');                                 // if field is empty or person typed "hh", then style it with inside prompt
        if (inp == "" && snip == "") {                                                                      // if both hh and mm are empty
            $(this).next().next().next().val("");                                                           // keep hidden value field empty
        } else {                                                                                            // else
            $(this).next().next().next().val(inp + ":" + snip);                                             // save hour and min combo with ":" in hidden field, ready for database commit
        }
        chk_changed($(this).next().next().next());                                                          // Check if value of hidden control has changed
    });

    // Minute control changes 
    $('span.time input.numb[id$="_min"]').focus(function () {
        if ($(this).val() == "mm") $(this).val("").removeClass('prompt');
    });
    $('span.time input.numb[id$="_min"]').blur(function () {                                                // if control loses focus
        var snip = ($(this).next().val().split(':')[0] != "hh") ? $(this).next().val().split(':')[0] : "";  // capture the hour part of of the hidden control with value, but only if it has actual input
        var inp = ($(this).val().length == 1) ? "0" + $(this).val() : $(this).val();
        $(this).val(inp);
        if (inp == "" || inp == "mm") $(this).val("mm").addClass('prompt');
        if (inp == "" && snip == "") {
            $(this).next().val("");
        } else {
            $(this).next().val(snip + ":" + inp);
        }
        chk_changed($(this).next());                                                                        // Check if value of hidden control has changed
    });

    // Set enablement of all time controls
    $('span.time input[type="radio"]').change(function () {                                                 // all radio controls in time controls
        if ($(this).prop('checked') == true && $(this).prop('id').indexOf('_closed') > 0) {                 // if selected radio is turned on (always) and is closed
            $(this).parent().find('input[type="text"], input[type="checkbox"]').prop('disabled', true);     // disable all controls
            $(this).parent().find('input[type="text"]').addClass('greyed');
            $(this).parent().find('input[type="checkbox"]').css('opacity', '0');
        } else {
            $(this).parent().find('input[type="text"], input[type="checkbox"]').prop('disabled', false);    // enable all controls
            $(this).parent().find('input[type="text"]').removeClass('greyed');
            $(this).parent().find('input[type="checkbox"]').css('opacity', '1');
        }
        Changed = true;                                                                                     // also record a change no matter what...
    });

    // Day date control changes 
    $('span.date input[id$="_dd"]').focus(function () {
        if ($(this).val() == "dd") $(this).val("").removeClass('prompt');
    });
    $('span.date input[id$="_dd"]').blur(function () {
        var snip = $(this).next().next().next().next().next().val().split(' ')[0];                          // strip off any time part if present
        snip = (snip.split('-')[1] != undefined) ? snip.split('-')[0] + "-" + snip.split('-')[1] : "-";     // capture the year and month part of of the hidden control with value, but only if it has actual input
        var inp = ($(this).val().length == 1) ? "0" + $(this).val() : $(this).val();
        $(this).val(inp);
        if (inp == "" || inp == "dd") $(this).val("dd").addClass('prompt');
        if (inp == "" && snip == "-") {
            $(this).next().next().next().next().next().val("");
        } else {
            $(this).next().next().next().next().next().val(snip + "-" + inp);
        }
        chk_changed($(this).next().next().next().next().next());                                            // Check if value of hidden control has changed
    });

    // Month control changes 
    $('span.date input[id$="_mmm"]').focus(function () {
        if ($(this).val() == "mmm") $(this).val("").removeClass('prompt');                                  // when clicking into the field it needs to remove the prompt and styling 
    });
    $('span.date input[id$="_mmm"]').blur(function () {
        var lng = $(this).parent().find('input[id$="_lng"]').val();                                         // language to display the correct version of the month, input is translated to number from any language
        var snip = $(this).next().next().next().val().split(' ')[0];                                        // strip off potential time part if database input is datetime
        var inp = $(this).val().toUpperCase();                                                              // dates are always uppercase in cusconow
        var mth = ['', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SET', 'OCT', 'NOV', 'DIC'];

        // idx is the index in the month array
        var idx = mth.indexOf(inp);                                                                         // find input in array and store index
        if (lng == "es" && idx > 0 && idx < 13) idx += 12;                                                  // input given in English but should be Spanish: up the index to corresponding number
        if (lng == 'en' && idx > 12) idx -= 12;                                                             // input given in Spanish but should be English: down the index to corresponding number
        $(this).val(mth[idx]);                                                                              // reflect corrected input (to uppercase and in correct language)

        // inp is the numeric month value that will be stored in the database
        inp = (idx == -1) ? "" : "" + idx % 12;                                                             // keep input empty when string not found, but use modulo to find the month number in array
        if (idx == 0) inp = "";                                                                             // the input is empty, make sure it doesn't translate to '00' 
        if (inp === "0") inp = "12";                                                                        // DEC is 12th month and modulo 12 yields 0
        inp = (inp.length == 1) ? "0" + inp : inp;                                                          // pad the number with zeros, so always two digits
        var snip1 = snip.split('-')[0];                                                                     // snip1 is year part of date
        var snip3 = (snip.split('-')[2] != undefined) ? snip.split('-')[2] : "";                            // snip3 is day part of date
        if (inp == "" || inp == "mmm") $(this).val("mmm").addClass('prompt');                               // if input is empty, style it with inside prompt
        if (inp == "" && snip1 == "" && snip3 == "") {                                                      // if all parts of date are empty, store it as empty (maybe is optional)
            $(this).next().next().next().val("");
        } else {
            $(this).next().next().next().val(snip1 + "-" + inp + "-" + snip3);
        }
        chk_changed($(this).next().next().next());                                                          // Check if value of hidden control has changed
    });

    // Year control changes 
    $('span.date input[id$="_yyyy"]').focus(function () {
        if ($(this).val() == "aaaa" || $(this).val() == "yyyy") $(this).val("").removeClass('prompt');      // independent of language, remove inside prompt and styling
    });
    $('span.date input[id$="_yyyy"]').blur(function () {
        var lng = $(this).parent().find('input[id$="_lng"]').val();
        var snip = $(this).next().val().split(' ')[0];
        snip = (snip.split('-')[1] != undefined) ? snip.split('-')[1] + "-" + snip.split('-')[2] : "-";     // capture the month and day part of of the hidden control with value, but only if it has actual input
        var inp = ($(this).val().length == 2) ? "20" + $(this).val() : $(this).val();
        $(this).val(inp);
        if ((inp == "" || inp == "yyyy") && lng == 'en') $(this).val("yyyy").addClass('prompt');
        if ((inp == "" || inp == "aaaa") && lng == "es") $(this).val("aaaa").addClass('prompt');
        if (inp == "" && snip == "-") {
            $(this).next().val("");
        } else {
            $(this).next().val(inp + "-" + snip);
        }
        chk_changed($(this).next());                                                                        // Check if value of hidden control has changed

    });

    // Telephone control changes
    $('span.telnr input[type="text"]').focus(function () {
        if ($(this).val() == "### ### ###" || $(this).val() == "84-## ## ##" || $(this).val() == "") $(this).val("").removeClass('prompt'); // remove inside prompt and styling
    });
    $('span.telnr input[type="text"]').blur(function () {
        if ($(this).val() == "### ### ###" || $(this).val() == "84-## ## ##" || $(this).val() == "") {
            if ($(this).parent().prev().find('option:selected').data('mobile') == '1') {
                $(this).val("### ### ###").addClass('prompt');
            } else {
                $(this).val("84-## ## ##").addClass('prompt');
            }
        } else { // just to be sure, remove any prompt class
            $(this).removeClass('prompt');
        }
    });
    $('span.telnr input[name="main_phone"]').blur(function () {
        if ($(this).val() != "### ### ###" && $(this).val() != "84-## ## ##" && $(this).val() != "" && $(this).parent().prev().val() != "") {
            $('#add_telnr').prop('disabled', false);
        } else {
            $('#add_telnr').prop('disabled', true);
        }
    });

    $('span.telnr select').change(function () {
        if ($(this).val() != "") {
            $(this).next().find('input').prop('disabled', false).removeClass('greyed');
            if ($(this).next().find('input').val() == "### ### ###" || $(this).next().find('input').val() == "84-## ## ##" || $(this).next().find('input').val() == "") {
                if ($(this).find('option:selected').data('mobile') == '1') {
                    $(this).next().find('input').val("### ### ###");
                } else if ($(this).find('option:selected').data('mobile') == '0') {
                    $(this).next().find('input').val("84-## ## ##");
                } else {
                    $(this).next().find('input').val("");
                }
            } else {
                // if this is the main telnr and something in telnr's text, then add_telnr needs to be enabled
                if ($(this).prop('name') == 'fr_phone_type_id') $('#add_telnr').prop('disabled', false);
            }
            // there is something in select, so remove prompt class
            $(this).removeClass('prompt');
        } else {
            $(this).next().find('input').prop('disabled', true).addClass('greyed');
            // there is something in select, so remove prompt class
            $(this).addClass('prompt');
            // if this is the main tel and nothing in type select, then add_telnr needs to be disabled
            if ($(this).prop('name') == 'fr_phone_type_id') $('#add_telnr').prop('disabled', true);
        }
    });
    $('span.telnr select').blur(function () {
        chk_changed($(this));                                                                               // Check if value of any textarea has changed
    });

    // Price Range control changes
    $('span.prc_rnge input').focus(function () {
        if ($(this).val() == "###,##") $(this).val("").removeClass('prompt');                               // if control gets focus and has prmpt inside, take it out and remove styling
    });
    $('span.prc_rnge input').blur(function () {                                                             // if control loses focus, sanitize for "." or "," decimal notation
        var inp = $(this).val().toString().replace(/[^0-9\.,]/g, "");                                       // strip out any non-digits (except for delimiters
        var pos = (inp.lastIndexOf(",") > inp.lastIndexOf(".")) ? inp.lastIndexOf(",") : inp.lastIndexOf("."); // get decimal position
        var dec = "";
        if (pos > -1) {
            dec = inp.substring(pos + 1).substring(0, 2);                                                   // allow only two digits max
            inp = inp.substring(0, pos);                                                                    // get eveything before the delimiter
        }
        inp = inp.replace(/[\.,]/g, "");                                                                    // strip all (pot.) delimiters
        if (dec == "") dec = "00";                                                                          // fill out decimals to 2 chars
        if (dec.length == 1) dec += "0";
        if (inp == "") inp = "0";                                                                           // if no digits, set a leading zero
        inp = inp + "," + dec;                                                                              // compose peruvian amount
        $(this).val(inp).removeClass('prompt');
        if (inp == "0,00" || inp == "###,##") $(this).val("###,##").addClass('prompt');                     // if field is empty/zero or person typed "###,##", then style it with inside prompt
        chk_changed($(this));                                                                               // Check if value of hidden control has changed
    });
    // End: DATE, TIME, TELNR, PRC_RNGE control changes and blurs

    // Thumbnail control change
    $('#thumbnail_url').change(function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                //$('span.logo-box').attr('background-image', e.target.result);
                $('span.logo-box span.usr-logo img').attr('src', e.target.result);
                $('input[name="thumbnail_remove"]').val("");
            };
            reader.readAsDataURL(this.files[0]);
            $('span.usr-logo').show();
            $('div.std-logo').hide();
        } else {
            //$('span.logo-box').attr('background-image', url("../../../assets/rest-big.png"));
            //$('span.logo-box span.usr-logo img').attr('src', "../../../assets/rest-big.png");
            $('span.usr-logo').hide();
            $('div.std-logo').show();
        }
        Changed = true;
    });

    // Menu name change (on blur)
    $('#cus-my-menus input[name*="_title_"]').blur(function () {
        var lng = $(this).attr('name').slice(-2);
        if (Lng == lng) {
            if ($(this).val() != "") {
                $('li.active-tab a').text($(this).val());
            } else {
                var othr = $(this).parent().parent().find('input').not('[name$=' + lng + ']');              // select the other input field in the same tr
                if (othr.val() != "") $('li.active-tab a').text(othr.val());                                // if not empty use that as the menu title
            }
        }
    });

    // Check if any other controls have changed...
    $('textarea').blur(function () {                                                                        // Check if value of any textarea has changed
        chk_changed($(this));
    });

    $('input[type="checkbox"]').change(function () {                                                        // On change for all checkboxes on all pages
        Changed = true;
    });

    $('#cus-my-feats input[type="text"], #cus-my-menus input[type="text"]').blur(function () {              // all text boxes on #cus-my-feats and #cus-my-menus page (also telnr)
        chk_changed($(this));                                                                               // Check if value of any text has changed
    });

    // Tabs clicks
    $('ul.tabs li a').click(function () {

        // Check whether any changes in form and user wants to save
        if (Changed && !confirm(Change_prmpt)) return false;
        Changed = false;

        // Remove focus from all tabs and place focus on this tab
        $(this).parent().parent().find('li').removeClass('active-tab');
        $(this).parent().addClass('active-tab');

        // Hide all tabpages and show this tabpage
        var page = $(this).parent().attr('name');
        $('div.tabpage').hide();
        $('div.tabpage[id="' + page + '"]').show();

        // Recalculate the height for the hours page
        align_heights('cus-my-menus');
    });


    // ************************************************************
    // Form validation - feats
    // ************************************************************
    $('#cus-my-feats input[name^="save"]').click(function (event) {
        var stop = false;

        // Cancel any previous errors
        $('#cus-my-feats form p.err-msg, #cus-my-feats form p.fbk-msg').remove();
        $('#cus-my-feats span.telnr').removeClass('verf-err');
        $('#cus-my-feats span.telnr').find('span.err-msg span').hide();

        // For main telnr: if it has no type selected and there are other spans created except for main and template
        var num_of_telnrs = $('#cus-my-feats span.telnr select').filter(function () { return this.value; }).length;

        if ($('#fr_phone_type_id').val() == "" && num_of_telnrs > 0) {
            var item = $('#cus-my-feats span.telnr').first();
            item.find('span.err-msg span[name="msg3"]').show();
            item.addClass('verf-err');
            stop = true;
        }

        // For each span.telnr in this form do
        $('#cus-my-feats span.telnr').each(function () {
            var item = $(this);

            // check only if it is not disabled or hidden
            if (item.find('select').val() != "") {

                // Check telnr input
                var is_mobile = item.find('option:selected').data('mobile');
                var telnr = item.find('input[type="text"]').val().replace(/[\s\-]/g, '');
                var is_numb = /^\d+$/.test(telnr);

                if ((telnr.length != 8 || !is_numb || telnr[0] == "0") && is_mobile == "0") {
                    item.find('span.err-msg span[name="msg1"]').show();
                    item.addClass('verf-err');
                    stop = true;
                } else if ((telnr.length != 9 || !is_numb || telnr[0] == "0") && is_mobile == "1") {
                    item.find('span.err-msg span[name="msg2"]').show();
                    item.addClass('verf-err');
                    stop = true;
                }
            }
        });
        // Recalculate the height for the feats page
        align_heights('cus-my-feats');

        // Make sure the form is not further processed...
        if (stop) {
            // Scroll to first error (3 levels up)
            $('html, body').animate({
                scrollTop: $(this).parent().parent().parent().find('.verf-err:eq(0)').offset().top - 40 // 40 px = top nav bar (32px) + margin
            }, 700);
            return false;
        }

        // Show loader and recalculate the height for the loader
        $('#loader').show();
        $('#loader').css('height', $('#cus-my-feats').height() - 36);

    }); // End of: form validations for #cus-my-feats


    // ************************************************************
    // Form validation - hours
    // ************************************************************
    $('#cus-my-hours input[name^="save"]').click(function (event) {
        var stop = false;
        var this_form = $(this).parent().parent();

        // Cancel any previous overall messages
        $('#cus-my-hours form p.err-msg, #cus-my-hours form p.fbk-msg').remove();

        // For each span.time in this form do
        if ( validate_time_controls(this_form) ) stop = true;

        // For each span.date in this form do
        $(this_form).find('span.date').each(function () {
            var item = $(this);

            // Cancel any previous errors
            item.removeClass('verf-err');
            item.find('span.err-msg span').hide();

            // Check day input
            var day = item.find('input[id$="_dd"]').val();
            if (day != "dd" && (!$.isNumeric(day) || day < 1 || day > 31)) {
                item.find('span.err-msg span[name="msg1"]').show();
                item.addClass('verf-err');
                stop = true;
            }
            // Check all year inputs (month are automatically corrected)
            var year = item.find('input[id$="_yyyy"]').val();
            item.find('input[id$="_yyyy"]').each(function () {
                if (($(this).val() != "yyyy" && $(this).val() != "aaaa") && (!$.isNumeric($(this).val()) || $(this).val() < 2000 || $(this).val() > 2099)) {
                    item.find('span.err-msg span[name="msg1"]').show();
                    item.addClass('verf-err');
                    stop = true;
                }
            });
            // Date checking, only when date value exists
            var value = item.find('input[type="hidden"][name]').val().split(' ')[0];
            if (value != "") {

                // Check if not only part of dd-mmm-yyyy is empty
                if (value[0] == "-" || value.indexOf("--") != -1 || value[value.length - 1] == "-") {
                    item.find('span.err-msg span[name="msg1"]').show();
                    item.addClass('verf-err');
                    stop = true;
                }
                // Check if date exists
                var bits = value.split('-');
                var testDate = new Date(bits[0], bits[1] - 1, bits[2]);
                if (testDate.getFullYear() != bits[0] || (testDate.getMonth() + 1) != bits[1] || testDate.getDate() != Number(bits[2])) {
                    item.find('span.err-msg span[name="msg2"]').show();
                    item.addClass('verf-err');
                    stop = true;
                }

                // Check if date in future (if future set)
                //var future = new Date();
                var future = new Date( (new Date()).setHours(new Date().getHours() + parseInt(TZsrvr)) );
                //future.setDate(future.getDate() + 1);
                var valDate = new Date(value);
                if (item.find('input[id$="_future"]').val() == 1 && valDate < future) {
                    item.find('span.err-msg span[name="msg3"]').show();
                    item.addClass('verf-err');
                    stop = true;
                }

                // If reference date is given, check if date within range
                var ref = item.find('input[id$="_dateid"]').val();
                var period = parseInt(item.find('input[id$="_num_days"]').val());

                if (ref != "" && !isNaN(period))
                    var refValue = $('#' + ref).val();                                                      // retrieve reference value, but only if reference and period are given

                if (refValue && refValue != "") {
                    var refDate = new Date(refValue);                                                       // create date with the value in the reference
                    var refToDate = new Date(refValue);
                    refToDate.setDate(refDate.getDate() + period);                                          // create a to date and add/subtract the period 

                    // if period > 0 and valDate is outside period (ref-refTo) OR period < 0 and valDate is outside period (refTo-ref)
                    if ((period >= 0 && (refDate > valDate || refToDate < valDate)) || (period < 0 && (refToDate >= valDate || refDate <= valDate))) {
                        item.find('span.err-msg span[name="msg4"]').show();
                        item.addClass('verf-err');
                        stop = true;
                    }
                }
            }
        });

        // Recalculate the height for the hours page
        align_heights('cus-my-hours');

        // Make sure the form is not further processed...
        if (stop) {
            // Scroll to first error (3 levels up)
            $('html, body').animate({
                scrollTop: $(this_form).find('.verf-err:eq(0)').offset().top - 40                       // 40 px = top nav bar (32px) + margin
            }, 700);
            return false;
        }

        // Show loader and recalculate the height for the loader
        $('#loader').show();
        $('#loader').css('height', $('#cus-my-hours').height() - 36);

    }); // End of: form validations for #cus-my-hours


    // ************************************************************
    // Form validation - menus
    // ************************************************************
    $('#cus-my-menus input[name^="save"]').click(function (event) {
        var stop = false;
        var this_form = $(this).parent().parent().parent();

        // Cancel any previous errors on #cus-my-menus page
        $('#cus-my-menus  p.err-msg, #cus-my-menus p.fbk-msg').remove();
        $(this_form).find('.global-err').removeClass('verf-err').hide();                                // cancel any previous global errors
        $(this_form).find('table.editform .verf-err').removeClass('verf-err');                          // remove row and cell highlights inside table
        $(this_form).find('table.editform span.err-msg span').hide();                                   // hide any error messages

        // For each span.time in this form do
        if ( validate_time_controls(this_form) ) stop = true;

        // For each span.prc_rnge in this form do
        $(this_form).find('span.prc_rnge').each(function () {
            var item = $(this);

            // Cancel any previous errors
            item.removeClass('verf-err');
            item.find('span.err-msg span').hide();

            // Check all amount inputs
            item.find('input').each(function () {
                var amt = parseFloat($(this).val().replace(",", "."));
                if (!isNaN(amt) && (amt < 0 || amt >= 1000)) {
                    item.find('span.err-msg span[name="msg1"]').show();
                    item.addClass('verf-err');
                    stop = true;
                }
            });

            // Check is from (lt) to
            var fr = parseFloat(item.find('input:eq(0)').val().replace(",", "."));
            var to = parseFloat(item.find('input:eq(1)').val().replace(",", "."));
            if (!isNaN(fr) && !isNaN(to) && (fr >= to)) {
                item.find('span.err-msg span[name="msg2"]').show();
                item.addClass('verf-err');
                stop = true;
            }

        });

        // Per menu_title check on ERR1, ERR2/ERR5, and ERR3
        var titles = "";                                                                                // to store all titles in

        $(this_form).find('input[name*="title"]').not('.tmpl').each(function () {                       // for all menu titles (except template)

            var sect_names = [];                                                                        // create sect_names array
            var lang = $(this).attr('name').split("_").pop();                                           // capture language code from title field
            titles += $(this).val();                                                                    // add title into one titles string


            // ERR1: Check for all section names for this language, except for template sections
            if ($(this).val() != "") {  // but only if the language section of the menu has a name

                // Collect all section names for that lang
                $(this).parent().parent().parent().find('tr.sect input:visible[name$="' + lang + '"]').each(function () {
                    sect_names[sect_names.length] = $(this).val();                                                      // add value to sect_names array
                });
                sect_names = sect_names.sort();                                                                         // sort array alphabetically
                for (var i = 0; i < sect_names.length - 1; i++) {
                    if (sect_names[i + 1] == sect_names[i] && "" != sect_names[i]) {                                        // if two subsequent entries are same and not empty
                        $(this).parent().parent().parent().find('tr.sect').not('.tmpl').addClass('verf-err');               // show error targets
                        $(this).parent().parent().parent().parent().parent().find('.err1').addClass('verf-err').show();     // show section name error
                        stop = true;
                        break;
                    } // end: if duplicate
                } // end: for each element in array


                // ERR2/ERR5 - Check if no entries for menu language that doesn't exist
            } else { // the language section of the menu has no name

                // Find all inputs/textareas for this lang that are not part of template 
                $(this).parent().parent().parent().find('tr:not(.tmpl .title) input:visible[name$="' + lang + '"], tr:not(.tmpl) textarea[name$="' + lang + '"]').each(function () {
                    if ($(this).val() != "") {                                                                          // if user typed value
                        $(this).parent().addClass('verf-err');
                        $(this).parent().find('span[name="err5"]').show();                                                  // ERR5 shown on td
                        stop = true;

                        // show global error as well
                        $(this).parent().parent().parent().parent().parent().find('.err2').addClass('verf-err').show();     // show entries in lang section with no title error
                    }
                });

            } // end: if lang section of menu has name

        });

        // ERR3: Check content of all menu titles for at least one entry
        if (titles == "") {                                                                             // if that title string is still empty
            $(this).parent().parent().find('tr.title').not('.tmpl').addClass('verf-err');               // show error target
            $(this).parent().parent().find('.err3').addClass('verf-err').show();                        // show title error
            $(this).parent().parent().find('.err2').removeClass('verf-err').hide();                     // error 3 also shows error 2 already
            stop = true;
        }

        //// ERR4: Check for each first menu entry if it's not empty when title has been filled out
        //$(this_form).find('textarea[name*="sect0_item0"]').not('.tmpl').each(function () {              // for all first items in first section (except template)
        //    var lang = $(this).attr('name').split("_").pop();
        //    if ($(this).val() == "" && $(this_form).find('tr.title input[name$="' + lang + '"]').val() != "") {
        //        $(this).parent().addClass('verf-err');
        //        $(this).parent().find('span[name="err4"]').show();
        //        stop = true;
        //    }
        //});

        // Recalculate the height for the hours page
        align_heights('cus-my-menus');

        // Make sure the form is not further processed...
        if (stop) {
            // Scroll to first error (4 levels up)
            $('html, body').animate({
                scrollTop: $(this_form).parent().find('.verf-err:eq(0)').offset().top - 40              // 40 px = top nav bar (32px) + margin
            }, 700);
            return false;
        }

        // Show loader and recalculate the height for the loader
        $('#loader').show();
        $('#loader').css('height', $('#cus-my-menus').height() - 36);

    }); // End of: form validations for #cus-my-menus

});


// **************************************************************************
// Functions
// **************************************************************************

function validate_time_controls(form) {
    var stop = false;
    var brktime = ($(form).find('#break_frm').val() != "" && ($(form).find('#break_tll').val() != "")) ? true : false;

    // Do for each time control found in this form
    $(form).find('span.time').each(function () {
        var item = $(this);

        // check only if it is not closed
        if (item.find('input[id$="_is_closed"]').prop('checked') != true) {

            // Cancel any previous errors
            item.removeClass('verf-err');
            item.find('span.err-msg span').hide();

            // Check all hour inputs
            item.find('input.numb[id$="_hr"]').each(function () {
                if ($(this).val() != "hh" && (!$.isNumeric($(this).val()) || $(this).val() < 0 || $(this).val() > 23)) {
                    item.find('span.err-msg span[name="msg1"]').show();
                    item.addClass('verf-err');
                    stop = true;
                }
            });
            // Check all minute inputs
            item.find('input.numb[id$="_min"]').each(function () {
                if ($(this).val() != "mm" && (!$.isNumeric($(this).val()) || $(this).val() < 0 || $(this).val() > 59)) {
                    item.find('span.err-msg span[name="msg1"]').show();
                    item.addClass('verf-err');
                    stop = true;
                }
            });
            // Check if not only part of hh:mm is empty
            item.find('input[id$="_frm"], input[id$="_tll"]').each(function () {
                if ($(this).val()[0] == ":" || $(this).val()[$(this).val().length - 1] == ":") {
                    item.find('span.err-msg span[name="msg1"]').show();
                    item.addClass('verf-err');
                    stop = true;
                }
            });
            // Check is from < till
            if ((parseInt(item.find('input[id$="_frm"]').val().replace(':', '')) >= parseInt(item.find('input[id$="_tll"]').val().replace(':', ''))) && parseInt(item.find('input[id$="_tll"]').val().replace(':', '')) > 500) {
                item.find('span.err-msg span[name="msg2"]').show();
                item.addClass('verf-err');
                stop = true;
            }
            // Check if break-time is indicated and not defined
            if (!brktime && item.find('input[id$="_break"]').prop('checked') == true) {
                item.find('span.err-msg span[name="msg3"]').show();
                item.addClass('verf-err');
                stop = true;
            }
        }
    });
    return stop;
}

function remove_thumb_upload() {
    //$('span.logo-box img').attr('src', "../../../assets/" + logo);                // set the image to default logo
    $('#thumbnail_url').replaceWith($('#thumbnail_url').val('').clone(true));       // replace the input[type=file] with a new one
    $('input[name="thumbnail_remove"]').val("1");                                   // make sure a database clean for this field is triggered
    $('span.usr-logo').hide();
    $('span.std-logo').show();
    Changed = true;
}

function add_tel_nr() { // add telnr span below all others
    var ndx = parseInt($('span.tmpl.telnr select').prop('name').substr($('span.tmpl.telnr select').prop('name').length - 1)) + 1; // capture ndx and +1
    $('#add_telnr').before($('span.tmpl.telnr').clone(true));                       // clone tmpl telnr control before the add button
    $('span.tmpl.telnr').first().removeClass('tmpl').show();                        // make first of clones visible and remove tmpl class
    $('span.tmpl.telnr select').prop('name', 'fr_phone_type_id_' + ndx);            // change the name props of the only left clone to next number
    $('span.tmpl.telnr input[type="text"]').prop('name', 'phone_nr_' + ndx);
    $('span.tmpl.telnr label:eq(0)').prop('for', 'fr_phone_type_id_' + ndx);

    // Recalculate the height for the feats page
    align_heights('cus-my-feats');
}

function remove_tel_nr(elm) { // remove parent (telnr span)
    var telnr = elm.parentNode;                                                     // parent of the button - all JavaScript
    telnr.parentNode.removeChild(telnr);                                            // remove parent of the button - all JavaScript

    // Recalculate the height for the feats page
    align_heights('cus-my-feats');
}

function add_menu(elm, lbl) {
    var new_tab = elm.parentNode; // the tab on which the button resides

    // Check whether any changes in form and user wants to save
    if (Changed && !confirm(Change_prmpt)) {
        return false;
    }
    Changed = false;

    // Clone the previous tab (last one, that's not new-tab) and set focus on it.
    var ndx = parseInt($('div.tabpage').last().attr('id').slice(8));
    $(new_tab).before($('ul.tabs li').not('.new-tab').last().clone(true));
    $(new_tab).parent().find('li').removeClass('active-tab');
    $(new_tab).prev().attr('name', "tabpage_"+ndx).find('a').text(lbl);
    $(new_tab).prev().addClass('active-tab');

    // Clone the template tabpage and show it
    $('div.tabpage.tmpl').after($('div.tabpage.tmpl').clone(true));
    $('div.tabpage').hide();
    $('div.tabpage.tmpl:eq(0)').removeClass('tmpl').show();

    // Adjust all the template controls with new index (+1 for next add)
    ndx++;
    renumber_tmpl_page(ndx);

    // Hide add button if there are seven menus
    var count = $('div.tabpage').length;                     // number of tabs before deletion, including template
    if (count == 8) $('#add_menu_button').hide();

}

function renumber_tmpl_page(ndx) {

    $('div.tabpage.tmpl').attr('id', "tabpage_" + ndx);                                                                                             // id of tabpage itself
    $('div.tabpage.tmpl input[name="tabmrk"]').val(ndx);                                                                                            // value of tabmrk for loaded tab
    $('div.tabpage.tmpl input[type="checkbox"]').attr('name', "menu" + ndx + $('div.tabpage.tmpl input[type="checkbox"]').attr('name').slice(5));   // name of checkbox 

    // Set id of: all input.numb controls (they have label), all hidden time controls (on change)
    $('div.tabpage.tmpl input.numb, div.tabpage.tmpl span.time input[type="hidden"]').each(function () {
        var ndx = $('#cus-my-menus div.tabpage').last().attr('id').slice(8);
        var name_lngth = $(this).attr('id').split("_")[0].length;
        $(this).attr('id', "menu" + ndx + $(this).attr('id').slice(name_lngth));
    });
    // Set for of: all labels, except for checkbox
    $('div.tabpage.tmpl label').not('.chk').each(function () {
        var ndx = $('#cus-my-menus div.tabpage').last().attr('id').slice(8);
        var name_lngth = $(this).attr('for').split("_")[0].length;
        $(this).attr('for', "menu" + ndx + $(this).attr('for').slice(name_lngth));
    });
    // Set name of: all textareas, all hidden inputs (time control, pk's), all text inputs in table
    $('div.tabpage.tmpl textarea, div.tabpage.tmpl input[type="hidden"][name^="menu"], div.tabpage.tmpl table input[type="text"]').each(function () {
        var ndx = $('#cus-my-menus div.tabpage').last().attr('id').slice(8);
        var name_lngth = $(this).attr('name').split("_")[0].length;
        $(this).attr('name', "menu" + ndx + $(this).attr('name').slice(name_lngth));
    });

    // Reset the values of all hidden tabmrk fields (change in tabs)
    var num_tabs = $('#cus-my-menus ul.tabs li[name^="tabpage"]').length;           // number of tabs
    for (i = 0; i < num_tabs; i++) {
        $('#cus-my-menus input[name="tabmrk"]:eq('+i+')').val(i);
    }

}

function add_menu_section(elm) {
    var tabpage = elm.parentNode.parentNode;                                        // the tabpage in which the button resides
    $(tabpage).find('tr').last().after($(tabpage).find('tr.tmpl').clone(true));     // clone all rows (with handlers) and insert before this row (textareas will be empty)
    $(tabpage).find('tr.tmpl:lt(5)').removeClass('tmpl');                           // remove tmpl class from first 5 trs

    // Change all textarea names
    $(tabpage).find('tr.tmpl textarea').each(function () {
        var name = $(this).attr('name').split("_");
        $(this).attr('name', name[0] + "_sect" + (parseInt(name[1].substring(4)) + 1) + "_" + name[2] +  "_" + name[3]);
    });

    // Change all input names and ids
    $(tabpage).find('tr.tmpl input[type="text"]').each(function () {
        var name = $(this).attr('name').split("_");
        $(this).attr('name', name[0] + "_sect" + (parseInt(name[1].substring(4)) + 1) + "_" + name[2] +  "_" + name[3]);
        if ( ('#' + name).length ) $(this).attr('id', name[0] + "_sect" + (parseInt(name[1].substring(4)) + 1)  + "_" + name[2]+  "_" + name[3]);
    });

    // Change all hidden text names
    $(tabpage).find('tr.tmpl input[type="hidden"]').each(function () {
        var name = $(this).attr('name').split("_");
        $(this).attr('name', name[0] + "_sect" + (parseInt(name[1].substring(4)) + 1) + "_" + name[2] +  "_" + name[3] + "_" + name[4]);
    });

    // Change all label fors
    $(tabpage).find('tr.tmpl label').each(function () {
        var name = $(this).attr('for').split("_");
        $(this).attr('for', name[0] + "_sect" + (parseInt(name[1].substring(4)) + 1) + "_" + name[2] +  "_" + name[3]);
    });

    // Recalculate the height for the menus page
    align_heights('cus-my-menus');
}

function add_menu_entry(elm) {
    var row = elm.parentNode.parentNode;                                            // the row in which the button resides
    $(row).before($(row).prev().clone(true));                                       // clone previous row (with handlers) and insert before this row (textareas will be empty)

    // Change all textarea names
    $(row).prev().find('textarea').each(function () {
        var name = $(this).attr('name').split("_");
        $(this).attr('name', name[0] + "_" + name[1] + "_item" + (parseInt(name[2].substring(4)) + 1) + "_" + name[3]);
    });

    // Change all hidden field names
    $(row).prev().find('input[type="hidden"]').each(function () {
        var name = $(this).attr('name').split("_");
        $(this).attr('name', name[0] + "_" + name[1] + "_pk_item" + (parseInt(name[3].substring(4)) + 1) + "_" + name[4]);
        $(this).val("");
    });

    // Recalculate the height for the menus page
    align_heights('cus-my-menus');
}

function confirm_menu_deletion(elm, msg, func) {
    var tabpage = elm.parentNode.parentNode.parentNode.parentNode;                  // the tabpage on which the button resides
    msg = msg.replace("%1", "\""+ $('#cus-my-menus ul.tabs li[name="' + $(tabpage).attr('id') + '"] a').text() + "\"");
    var answer = confirm(msg) ? "yes" : "no";
    $(tabpage).find('input[name="sure_'+func+'"]').val( answer );   // place response of confirm popup into RUsure hidden field
}

function chk_changed(elm) { // check whether elm.val() has changed by comparing it with InitialValues
    var now = elm.val();
    var old = InitialValues[elm.attr('name')];
    if (old != now) Changed = true;
}

function load_flex_list(data, pre, keyid) { // Load data for flexible length lists (ccs, atmos, cuisines)

    // Reset all checkboxes
    $('#cus-my-feats input[id^="'+pre+'"]').prop('checked', false);
    
    // Go through data array and set corresponding checkbox for each keyid found
    if (data !== undefined) {
        for (var i = 0; i < data.length; i++) {
            $('#'+pre+data[i][keyid]).prop('checked', true);
        }
    }
}

function load_phone_nrs(data) {

    // Delete all telnr fields except first one and template
    $('span.telnr').first().addClass('tmpl');
    $('span.telnr').not('.tmpl').remove();
    $('span.telnr').first().removeClass('tmpl');

    // For each in json data build a telnr field and populate it
    if (data != undefined) {
        for (var i = 0; i < data.length; i++) {
            add_tel_nr();
            var el = $('span.telnr').not('.tmpl').last();
            el.find('select').val(data[i].fr_phone_type_id).removeClass('prompt');
            el.find('input[type="text"]').val(data[i].phone_nr).removeClass('prompt');
        }
    }

    // Main telnr is loaded directly with general data, but need to be cleared (time and date are loaded below, other telnrs in separate call)
    if ($('#main_phone').val() != "") {
        $('#main_phone').removeClass('prompt');
    } else {
        $('#main_phone').addClass('prompt');
    }

    // For ALL phone numbers; apply correct mask
    $('span.telnr').find('input[type="text"]').each(function () {
        var value = $(this).val();
        if (value.length == 9) {
            value = value.substr(0, 3) + " " + value.substr(3, 3) + " " + value.substr(6, 3);                               // ### ### ###
        } else if (value.length == 8) {
            value = value.substr(0, 2) + "-" + value.substr(2, 2) + " " + value.substr(4, 2) + " " + value.substr(6, 2);    // 84-## ## ##
        }
        $(this).val(value);
    }); 
}

function clean_tabs() { // Delete all possible previously inserted pages
    var num_pages = $('#cus-my-menus div.tabpage').length - 1;                      // number of pages before deletion, minus template

    $('#add_menu_button').show();                                                   // show add button, since there will be room for one more menu

    for (i = 1; i < num_pages; i++) {
        $('#cus-my-menus ul.tabs li:eq(1)').remove();                               // delete all tabs from the second tab on, except the add tab (1st tab is needed or cloning)
        $('#cus-my-menus div.tabpage:eq(1)').remove();                              // delete all tabpages from the second tabpage on, except the template tabpage
    }
    renumber_tmpl_page("1");                                                        // renumber the controls in the template tab
    $('#cus-my-menus ul.tabs li.new-tab input').triggerHandler("click");            // create a clean copy of an empty page: template tab

    $('#cus-my-menus div.tabpage:eq(0)').remove();                                  // now that there are 2 tabs (and new tab has been cloned) the first dirty page (and tab) can be deleted
    $('#cus-my-menus ul.tabs li:eq(0)').remove();

}

function load_tabs(data) { // Create tabs according to data.menu

    // Collect all unique menus
    var menus = [];
    for (i = 0; i < data.menu.length; i++) {
        //console.log("data.menu[i]: " + data.menu[i].menu_id + ": " + data.menu[i].menu_title);                            // DEBUG //
        if ($.inArray(data.menu[i].menu_id, menus) == -1) {
            menus.push(data.menu[i].menu_id);                                                                               // if menu_id not found, add to menus array 
        }
    }
    console.log("menus: " + menus);                                                                                       // DEBUG //

    // Collect all unique languages by name in data.menu
    var langs = [];
    for (i = 0; i < data.menu.length; i++) {
        if ($.inArray(data.menu[i].fr_lang_code, langs) == -1) {
            langs.push(data.menu[i].fr_lang_code);                                                                          // if fr_lang_code not found, add to langs array 
        }
    }
    //console.log("langs: " + langs);                                                                                       // DEBUG //

    // Clean titles and tabs, and use add_menu for all found menus minus first
    $('#cus-my-menus div.tabpage:eq(0) input[name*="_menu_title_"]').val("");                                               // empty both menu titles for first tabpage
    $('#cus-my-menus ul.tabs li:eq(0) a').text("x");                                                                        // correct label will be inserted later, "x" used for detection of empty

    var btn = document.getElementById("add_menu_button");
    for (var i = 1; i < menus.length; i++) {
        add_menu(btn, "x");                                                                                                 // correct labels will be inserted later, "x" used for detection of empty
        $('#cus-my-menus div.tabpage:eq(' + i + ') input[name*="_menu_title_"]').val("");                               // empty both menu titles
    }

    // Find out if the form is reloaded and re-select tab (+ tabpage)
    var sel = $('#sel_tab').val();
    if (sel == "") sel = 0;
    $('#cus-my-menus ul.tabs li:eq('+sel+') a').triggerHandler("click");

    // Load menu information per page
    for (var i = 0; i < menus.length; i++) {

        // The tabpage id number and the menu number fields have the same number
        var menu_ndx = "menu" + $('#cus-my-menus div.tabpage:eq('+i+')').attr('id').split("_")[1];
        var first_blk = true;                                                                                               // use 'es' (first block) to load all language independent information

        // ...and per language block
        for (var j = 0; j < langs.length; j++) {

            // Filter data.menu for this menu block
            var menu_blk = $.grep(data.menu, function(v) {
                return v.menu_id == menus[i] && v.fr_lang_code == langs[j];
            });

            // Create enough sections if there are more than 1
            if (menu_blk.length > 1) {
                var num_sections = $('#cus-my-menus div.tabpage:eq('+i+') tr.sect').length - 1;                             // count present number of sections minus template

                for (k = num_sections; k < menu_blk.length; k++) {
                    //console.log("extra section needed: "+k+" for menu #: " + menu_ndx + ", lang: " + langs[j] + ", name: " + menu_blk[0]['menu_title']); // DEBUG //
                    $('#cus-my-menus div.tabpage:eq('+i+') table+p.alone input').triggerHandler("click");                   // for each additional section needed, click on button
                }
            }

            // Only continue loading the block if there's data
            if (menu_blk.length) {

                // Set tab label if the language is preferred or preferred lang did not have title (=> either es is pref and doesn't load OR en is pref and overrides all es input)
                if ( langs[j] == Lng || $('#cus-my-menus ul.tabs li:eq(' + i + ') a').text() =="x" ) $('#cus-my-menus ul.tabs li:eq(' + i + ') a').text(menu_blk[0]['menu_title']);

                //console.log("menu #: " + menu_ndx + ", menu_id: " + menus[i] + ", lang: " + langs[j] + ", name: " + menu_blk[0]['menu_title']); // DEBUG //

                // Lang independent stuff
                if (first_blk) {
                    $('input[name="' + menu_ndx + '_menu_id"]').val(menu_blk[0]['menu_id']);
                    $('input[name="' + menu_ndx + '_menu_frm"]').val(menu_blk[0]['menu_frm']);
                    $('input[name="' + menu_ndx + '_menu_tll"]').val(menu_blk[0]['menu_tll']);
                    var check = (menu_blk[0]['is_active'] == "1") ? true : false;
                    $('input[name="' + menu_ndx + '_is_active"]').prop('checked', check);
                    if (menu_blk[0]['menu_fr_sol'] != null) $('input[name="' + menu_ndx + '_menu_fr_sol"]').val(menu_blk[0]['menu_fr_sol'].replace(".", ",")).trigger("blur");
                    if (menu_blk[0]['menu_to_sol'] != null) $('input[name="' + menu_ndx + '_menu_to_sol"]').val(menu_blk[0]['menu_to_sol'].replace(".", ",")).trigger("blur");
                }

                // Menu-wide stuff
                $('input[name="' + menu_ndx + '_pk_menu_id_' + langs[j] + '"]').val(menu_blk[0]['pk_menu_id']);
                $('input[name="' + menu_ndx + '_menu_title_' + langs[j] + '"]').val(menu_blk[0]['menu_title']);

                // Section stuff
                for (var k = 0; k < menu_blk.length; k++) {

                    $('input[name="' + menu_ndx + '_sect' + k + '_name_' + langs[j] + '"]').val(menu_blk[k]['section_name']);
                    $('input[name="' + menu_ndx + '_sect' + k + '_pk_name_' + langs[j] + '"]').val(menu_blk[k]['pk_section_id']);


                    // Lang independent stuff
                    if (first_blk) {
                        if (menu_blk[0]['sect_fr_sol'] != null) $('input[name="' + menu_ndx + '_sect' + k + '_fr_sol"]').val(menu_blk[k]['sect_fr_sol'].replace(".", ",")).trigger("blur");
                        if (menu_blk[0]['sect_to_sol'] != null) $('input[name="' + menu_ndx + '_sect' + k + '_to_sol"]').val(menu_blk[k]['sect_to_sol'].replace(".", ",")).trigger("blur");
                    }

                    // Filter data.items for this section
                    var items = $.grep(data.items, function (v) {
                        return v.fk_section_id == menu_blk[k]['pk_section_id'];
                    });

                    // Create enough items if there are more than 2
                    if (items.length > 1) {
                        // Count present number of items: all rows in the section minus title row + price range row 
                        var num_items = $('#cus-my-menus div.tabpage:eq(' + i + ') tr.actn-row:eq(' + k + ')').get(0).rowIndex - $('#cus-my-menus div.tabpage:eq(' + i + ') tr.sect:eq(' + k + ')').get(0).rowIndex - 2;

                        for (var l = num_items; l < items.length; l++) {
                            //console.log("extra item needed: " + l + " for section: " + menu_blk[k]['section_name'] + ", menu #: " + menu_ndx + ", lang: " + langs[j]); // DEBUG //
                            $('#cus-my-menus div.tabpage:eq(' + i + ') tr.actn-row:eq(' + k + ') input').triggerHandler("click");                   // for each additional section needed, click on button
                        }
                    }

                    // Only continue loading the items if there's data
                    if (items.length) {

                        // Items stuff
                        for (var l = 0; l < items.length; l++) {
                            $('textarea[name="' + menu_ndx + '_sect' + k + '_item' + l + "_" + langs[j] + '"]').val(items[l]['item_name']);
                            $('input[name="' + menu_ndx + '_sect' + k + '_pk_item' + l + "_" + langs[j] + '"]').val(items[l]['pk_id']);
                        } // End of: items stuff

                    } // End of: if items.length

                } // End of: for all sections

                first_blk = false; // after first header and menu sections no need to load prices and times

            } // End of: menu_blk.length

        } // End of: for all langs

    } // End of: for all menu tabpages

    // Load any time control sets *** COPIED FROM load_data ***
    $('span.time input.numb[id$="_hr"]').each(function () {                                                                 // all hour fields
        $(this).val($(this).next().next().next().val().split(':')[0]).removeClass('prompt');                                // real data is in hidden field hh:mm
        if ($(this).val() == "") $(this).val("hh").addClass('prompt');                                                      // if empty, style it with inside prompt
    });
    $('span.time input.numb[id$="_min"]').each(function () {                                                                // all minute fields (same)
        $(this).val($(this).next().val().split(':')[1]).removeClass('prompt');
        if ($(this).val() == "") $(this).val("mm").addClass('prompt');
    });

    Changed = false;
}

function load_data(data) {

    // reset all errors and selections
    $('span').removeClass('verf-err');
    $('select').val("");

    // Load all inputs
    $.each(data, function (key, value) {                                                                                    // For each data point in the JSON
        var el = $('#' + key);                                                                                              // Try to get the element by potential ID
        if (value == null) value = "";
        //console.log(key + ": " + el.prop('type') + " : " + value);                                                        // DEBUG //

        if (el.prop('type') == "hidden" || el.prop('type') == "text" || el.prop('type') == "textarea" || el.prop('type') == "select-one") { // if it's (hidden) text, textarea, select field  
            el.val(value);                                                                                                  // then value can be set
            el.trigger("change");                                                                                           // trigger onchange event for potential input checks (later test occurs for edit/rdo)
            $('span.display-only[name="' + key + '"]').text(value);                                                         // there may be read-only versions of this field as well
        }

        if (el.prop('type') == "checkbox") {                                                                                // if it's a checkbox
            var check = (value == "1") ? true : false;                                                                      // translate MySQL data to jQuery setting
            el.prop('checked', check);                                                                                      // set the checkbox
        }
        if (el.is("span") == true) {                                                                                        // if it's a span with ID
            el.text(value);                                                                                                 // set the text of the span
            $('input[name="' + key + '"]').val(value);                                                                      // copy it into any input or span withthe same name (edit vs. read-only)
            $('span[name="' + key + '"]').text(value);
        }
        if (el.prop('type') == undefined && $('input[name="' + key + '"]').prop('type') == "radio") {                       // if it's not an ID, it might be a radio
            var choice = (value == "1") ? "on" : "";                                                                        // radios in date controls are "on" or "" (empty)
            $('input[name="' + key + '"][value="' + choice + '"]').prop('checked', true);                                   // by default radios are off, so set the ones that are on
        }
    });

    // Load any time control sets *** COPIED TO LOAD_TABS *** 
    $('span.time input.numb[id$="_hr"]').each(function () {                                                                 // all hour fields
        $(this).val($(this).next().next().next().val().split(':')[0]).removeClass('prompt');                                // real data is in hidden field hh:mm
        if ($(this).val() == "") $(this).val("hh").addClass('prompt');                                                      // if empty, style it with inside prompt
    });
    $('span.time input.numb[id$="_min"]').each(function () {                                                                // all minute fields (same)
        $(this).val($(this).next().val().split(':')[1]).removeClass('prompt');
        if ($(this).val() == "") $(this).val("mm").addClass('prompt');
    });
    // Set enablement of time controls according to initial settings
    $('span.time input[id$="_closed"]').each(function () {
        if ($(this).prop('checked') == true) {                                                                              // by default it shows for 'open'
            $(this).parent().find('input[type="text"], input[type="checkbox"]').prop('disabled', true);                     // disable inputs and checkboxes
            $(this).parent().find('input[type="text"]').addClass('greyed');                                                 // grey out font, so it is not readable
            $(this).parent().find('input[type="checkbox"]').css('opacity', '0');                                            // only way to make check mark not show up
        } else {
            $(this).parent().find('input[type="text"], input[type="checkbox"]').prop('disabled', false);                    // enable all controls
            $(this).parent().find('input[type="text"]').removeClass('greyed');
            $(this).parent().find('input[type="checkbox"]').css('opacity', '1');
        }
    });

    // Load any date control sets
    $('span.date input[id$="_dd"]').each(function () {                                                                      // all day fields
        $(this).val($(this).next().next().next().next().next().val().split(' ')[0].split('-')[2]).removeClass('prompt');    // copy over the XX part in ????-??-XX ??:??:??
        if ($(this).val() == "") $(this).val("dd").addClass('prompt');
    });
    $('span.date input[id$="_mmm"]').each(function () {                                                                     // all month fields
        var lng = $(this).parent().find('input[id$="_lng"]').val();                                                         // read out lang in (mandatory) hidden field 
        if (lng == 'en') var mth = ['', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        if (lng == "es") var mth = ['', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SET', 'OCT', 'NOV', 'DIC'];
        var id = parseInt($(this).next().next().next().val().split('-')[1]);                                                // copy over the XX part in ????-XX-?? ??:??:??
        if (isNaN(id)) id = 0;
        $(this).val(mth[id]).removeClass('prompt');                                                                         // use array to translate number to 3-letter value
        if ($(this).val() == "") $(this).val("mmm").addClass('prompt');
    });
    $('span.date input[id$="_yyyy"]').each(function () {                                                                    // all year fields
        $(this).next().val($(this).next().val().split(' ')[0]);                                                             // sanitize the hidden date input value (in case it is datetime)
        var lng = $(this).parent().find('input[id$="_lng"]').val();
        $(this).val($(this).next().val().split('-')[0]).removeClass('prompt');                                              // copy over the XXXX part in XXXX-??-?? ??:??:??
        if ($(this).val() == "" && lng == 'en') $(this).val("yyyy").addClass('prompt');                                     // if empty, style it with EN inside prompt
        if ($(this).val() == "" && lng == "es") $(this).val("aaaa").addClass('prompt');                                     // if empty, style it with ES inside prompt
    });

    // Load address
    var txt1 = (Lng == "es") ? "primer piso" : "first floor", txt2 = (Lng == "es") ? "segundo piso" : "second floor", txt3 = (Lng == "es") ? "tercer piso y/o superior" : "third floor or higher";
    var txt4 = (Lng == "es") ? "ascensor" : "elevator", txt5 = (Lng == "es") ? "escaleras mecánicas" : "escalator", txt6 = (Lng == "es") ? "disponible" : "available";

    var addr = "";
    if (data.addr_street != null) addr = data.addr_street;
    if (data.addr_area != null) addr += (addr != "")? (",<br />" + data.addr_area) : data.addr_area;
    if (addr != "") addr += ".<br />";
    //var addr = data.addr_street + ",<br />" + data.addr_area + ".<br />";
    var flr_line = "";
    var mech_line = "";
    if (data['1st_floor'] == "1") flr_line = "("+ txt1;
    if (data['2nd_floor'] == "1") flr_line += (flr_line == "")? "(" + txt2 : ", " + txt2;
    if (data['3rd_and_floor'] == "1") flr_line += (flr_line == "")? "(" + txt3 : ", " + txt3;
    if (data.elevator == "1") mech_line += " - " + txt4;
    if (data.escalator == "1") mech_line += (mech_line == "")? " - " + txt5 : ", " + txt5;
    if (mech_line != "") mech_line += " " + txt6;
    if (flr_line != "" || mech_line != "") addr += flr_line + mech_line + ")";
    $('#cus-my-feats #address').html(addr);

    // Set standard logo, and load thumbnail, if any
    $('span.logo-box span.std-logo img').removeClass().addClass('clip poitype'+data.poi_type_icon);
    if (data.thumbnail_url != null && data.thumbnail_url != "") {
        $('span.logo-box span.usr-logo img').attr('src',data.thumbnail_url);
        //$('input[name="thumbnail_remove"]').val("");
        $('span.usr-logo').show();
        $('span.std-logo').hide();
    } else {
        $('span.usr-logo').hide();
        $('span.std-logo').show();
    }

}

function edit_feat(show) { // DEBUG //
    if (show === true) {
        $('#cus-my-feats .edit-feat').show();
        $('#cus-my-feats .rdo-feat').hide();
    } else {
        $('#cus-my-feats .edit-feat').hide();
        $('#cus-my-feats .rdo-feat').show();
    }

    // Recalculate the height for the feats page
    align_heights('cus-my-feats');
}


// **************************************************************************
// Subscriptions  MB
// **************************************************************************

function show_sub_details(id) {

    // reset 
    $('#sub_details span.display-only:not(#wrn_sub_mail, #gets_no_mail)').removeClass('verf-err verf-ok');
    $('#sub_details').show();

    // store the four keys
    $('#subsnr').val(id);                               // To be used for reloading sub details on POST
    $('#subsnr_in_muts').val(id);                       // To be used for reloading sub details on POST in MUTATIONS form
    if (Subs[id].pk_sub_id) {
        var sub_edit = true;
        $('#pk_sub_id').val(Subs[id].pk_sub_id);        // To be used for adding, editing or deleting
    }
    $('#pk_est_id').val(Subs[id].pk_est_id);            // To be used for adding, editing or deleting
    $('#pk_sbscrbr_id').val(Subs[id].pk_sbscrbr_id);    // To be used for adding, editing or deleting

    // Display subscriber details
    $('#sbscrbr_login').text(Subs[id].sbscrbr_login);
    if (Subs[id].is_usr_blocked == "1") { $('#is_usr_blocked').text('Yes').addClass('verf-err'); } else { $('#is_usr_blocked').text('No'); }

    if (Subs[id].fr_ID == null) {                       // never can receive mail
        $('#gets_no_mail').show(); 
        $('#gets_sub_mails').hide();
        $('#wrn_sub_mail').hide(); 
    } else {                                            // might receive sub mail 
        //$('#gets_sub_mails').prop('checked', Subs[id].gets_sub_mails==="1");
        $('#gets_no_mail').hide(); 

        if (Subs[id].gets_sub_mails === "1") { 
            $('#wrn_sub_mail').hide(); 
            $('#gets_sub_mails').show(); 
        } else { 
            $('#wrn_sub_mail').show(); 
            $('#gets_sub_mails').hide(); 
        } 
    }
    $('#poi_shrt_name').html(escapeSpecialChars(Subs[id].poi_shrt_name));
    $('#fk_poi_type_id').text(Subs[id].fk_poi_type_id); // to be placed behind poi_shrt_name 
    $('#sbscrbr_notes').html(escapeSpecialChars(Subs[id].sbscrbr_notes));

    if( sub_edit ) {                                    // edit subscription
        $('#sub_details .has_sub').show();
        $('#sub_details .no_sub').hide();
        $('#subtype_name').html(escapeSpecialChars(Subs[id].subtype_name));

        $('#sub_strt_date').html(loc(Subs[id].sub_strt_date));
        if (Subs[id].sub_end_date == null)  {           // it is an active subscription
            txt = (Lng == "es") ? "Activo" : "Active";
            $('#sub_details .act_sub').show();
            $('#sub_details .old_sub').hide();
            $('#sub_end_date').text(txt).addClass('verf-ok'); 
            if (Subs[id].must_end_eop == "1") {
                $('#sub_end_date').text(calc_eop(Subs[id].sub_strt_date, Subs[id].sub_cycle, true)); // place text with eop in field
                $('#eop_button').prop('value', "Revert scheduled cancellation");
            } else {
                $('#eop_button').prop('value', "End subscription at end of cycle");
            }

        } else {                                        // it is an old subscription
            $('#sub_details .old_sub').show();
            $('#sub_details .act_sub').hide();
            $('#sub_end_date').html(loc(Subs[id].sub_end_date));

            // include and format end balance
            if (Subs[id].sub_final_bal != null) { 
                if (Subs[id].sub_final_bal >= 0 ) {
                    $('#sub_final_bal').text('S/. ' + amount(Subs[id].sub_final_bal)).addClass('verf-ok'); 
                } else {
                    $('#sub_final_bal').text('S/. ' + amount(Subs[id].sub_final_bal) + ' DUE').addClass('verf-err'); 
                }
            } else { 
                $('#sub_final_bal').html('&nbsp;'); 
            }
            
            // load bundle information, if any, into the reinstate button
            var button_bundle_text = $('#sub_details input[type="submit"][value="Reinstate subscription"]').prop('name');
            if (Subs[id].sub_bundle_id != null) {
                var my_bundles = [];
                my_bundles = Subs.filter(function (el) { // these are the active subs that belong to this bundle
                    return el.sub_bundle_id == Subs[id].sub_bundle_id && el.sub_end_date == null;
                });
                // if there are active subs AND less than number of slots available 
                if (my_bundles.length > 0 && my_bundles.length < Subs[id].num_of_est) {             // then there are open slots 
                    main_sub = my_bundles.filter(function (el) {  return el.ref_sub_id == null; }); // find the main sub
                    button_bundle_text += "bundle" + main_sub[0].pk_sub_id;                          // place its pk_sub_id into the button text
                    $('#sub_details input[type="submit"][value="Reinstate subscription"]').prop('name', button_bundle_text);   // place button text in name attribute
                }
            }
        } 
        if (Subs[id].is_usr_blocked == "1") $('#sub_details .not_blocked').hide();  // if user is blocked then it should not be possible to create or update a subscription

        // find subtype
        var st_id = Subs[id].fk_subtype_id;
        for (var i = 0; i < SubTypes.length;i++ ) {
            if ( SubTypes[i].pk_subtype_id == st_id ) {
                st_id = i;
                break;
            }
        }
        // Display subscription details
        var txt1 = (Lng == "es") ? " por " : " per ", 
        txt2 = (Lng == "es") ? " mes(es)" : " month(s)", 
        txt3 = (Lng == "es") ? " establicimiento(s)" : " establishment(s)", 
        txt4 = (Lng == "es") ? "% sobre el(los) primer " : "% over the first ", 
        txt5 = (Lng == "es") ? " período(s)" : " period(s)";

        var txt6 = (Lng == "es") ? " después de la expiración del período." : " after expiry of period.",
        txt7 = (Lng == "es") ? ". Período de gracia: " : ". Grace period: ",
        txt8 = (Lng == "es") ? " mes(es) después del inicio del período." : " month(s) after the start of the period.",
        txt9 = (Lng == "es") ? "<br />La multa se calcula cada " : "<br />Penalty calculated every ",
        txt10 = (Lng == "es") ? "mes(es)." : " month(s).",
        txt11 = (Lng == "es") ? "% sobre el importe debido" : "% over amount due",
        txt12 = (Lng == "es") ? "Si" : "Yes",
        txt13 = (Lng == "es") ? "No" : "No";

        $('#sub_amount').text('S/. ' + amount(Subs[id].sub_amount) + txt1 + SubTypes[st_id].sub_cycle + txt2);
        $('#num_of_est').text(SubTypes[st_id].num_of_est + txt3);
        if (SubTypes[st_id].discount_perc && Subs[id].is_1st_subtype != "0") {
            $('#discount').text( amount(SubTypes[st_id].discount_perc) + txt4 + SubTypes[st_id].disc_paycycles + txt5); 
        } else {
            $('.discount').hide();
        }
        if (SubTypes[st_id].penalty_perc) {
            var delay = (SubTypes[st_id].penalty_start == SubTypes[st_id].sub_cycle) ? txt6 : txt7 + (SubTypes[st_id].penalty_start) + txt8;
            var pen_rpt = (SubTypes[st_id].penalty_repeat == SubTypes[st_id].sub_cycle) ? "" : txt9 + (SubTypes[st_id].penalty_repeat) + txt10;

            $('#penalty').html( amount(SubTypes[st_id].penalty_perc) + txt11 + delay + pen_rpt);
            $('#min_pen_bal').text( 'S/. ' + SubTypes[st_id].min_pen_bal ); 
        } else {
            $('.penalty').hide(); 
        } 
        if (SubTypes[st_id].sponsorship == "1") { $('#sponsorship').text(txt12).addClass('verf-ok'); } else { $('#sponsorship').text(txt13); }

    } else {        // no subscription

        var txt14 = (Lng == "es") ? "Ninguna" : "None";

        $('#sub_details .has_sub').hide();
        $('#sub_details .no_sub').show();
        if (Subs[id].is_usr_blocked == "1") $('#sub_details .not_blocked').hide();  // if user is blocked then it should not be possible to create or update a subscription
        $('#subtype_name').text(txt14).addClass('verf-err');
    }


    // ***********************************************
    // update subtype droplist in form with bundles

    // remove any previous bundles (if any) in droplist
    $('#pk_subtype_id option.list-dvdr').remove();
    $('#pk_subtype_id option[value*="b"]').remove();
    // remove any bundled establishments that are listed in the form
    $('#sub_details .bundle').remove();
    $('#sub_details .grp-top').removeClass('grp-top');

    // these are the bundles for that subscriber
    var my_bundles = [];
    my_bundles = Subs.filter(function (el) {
        return el.pk_sbscrbr_id == $('#pk_sbscrbr_id').val() && el.ref_sub_id == null && el.sub_bundle_id != null && el.sub_end_date == null;
    });
    // check for each found bundle if it has more subscriptions
    for (var i = my_bundles.length - 1; i >= 0; i--) {
        chk_bundle = Subs.filter(function (el) {
            return el.pk_sbscrbr_id == $('#pk_sbscrbr_id').val() && el.sub_bundle_id == my_bundles[i].sub_bundle_id && el.sub_end_date == null;
        });
        // if amount of subs is more or equal to num_of_est; take it out
        if (chk_bundle.length >= my_bundles[i].num_of_est) { 
            my_bundles.splice(i, 1); 
        // if subtype is same as present (and it is bundle type), and if present sub is main sub; take it out
        } else if( my_bundles[i].fk_subtype_id && my_bundles[i].fk_subtype_id==Subs[id].fk_subtype_id && Subs[id].ref_sub_id==null ) { 
            my_bundles.splice(i, 1);
        }
    }

    if (my_bundles.length != 0) { // there are extra bundles to be added
        $('#pk_subtype_id option:first').after("<option value='' class='list-dvdr' disabled='disabled'>Standard subscriptions:</option>");
        for ( var i=0; i<my_bundles.length; i++ )
            $('#pk_subtype_id option:first').after("<option value='"+my_bundles[i].fk_subtype_id+"b"+my_bundles[i].pk_sub_id+"'>"+my_bundles[i].subtype_name+"</option>");
        $('#pk_subtype_id option:first').after("<option value='' class='list-dvdr' disabled='disabled'>Free slot(s):</option>");
    }

    // check for all subscriptions belonging to this bundle:
    // this subscription has to be active; if so, list all other active subscriptions
    if (Subs[id].sub_end_date == null) {
        var this_bundle = []; // free memory
        this_bundle = Subs.filter(function (el) {
            return el.sub_bundle_id != null && el.sub_end_date == null && el.sub_bundle_id == Subs[id].sub_bundle_id && el.pk_sub_id != Subs[id].pk_sub_id;
        });
        for ( var i=0; i<this_bundle.length; i++ ) {
            if ( this_bundle.length > 0 && i == 0) { // create deliniation borders (1 and up, since own sub is already stripped off)
                $('#poi_shrt_name').after("<label class='bundle grp-btm'></label><span class='bundle '>"+this_bundle[i].fk_poi_type_id+"</span><span class='bundle display-only grp-btm'>"+this_bundle[i].poi_shrt_name+"</span>");
                $('#poi_shrt_name, #sub_details label[for="poi_shrt_name"]').addClass('grp-top');
            } else {
                $('#poi_shrt_name').after("<label class='bundle'></label><span class='bundle'>"+this_bundle[i].fk_poi_type_id+"</span><span class='bundle display-only'>"+this_bundle[i].poi_shrt_name+"</span>");
            }
        }
    }
    // ***********************************************

    // scroll to form
    $('html, body').animate({
        scrollTop: $("#sub_details").offset().top - 40 // 40 px = top nav bar (32px) + margin
    }, 700);

} // end: show_sub_details(id);


// **************************************************************************
// Helper functions
// **************************************************************************

function align_heights(page_id) {

    // Reset all heights
    $('#' + page_id).css('min-height', 500);
    $('#cus-task-menu').css('min-height', 500);

    // multipage may have a (absolute) button row; capture this height in this case, add a 10px margin at the bottom of the page content
    var btm = ( $('#' + page_id + ' p.button-row').length )? 
        $('#' + page_id + ' p.button-row').height() + parseInt( $('#' + page_id + ' p.button-row').css('padding-top')) + parseInt( $('#' + page_id + ' p.button-row').css('padding-bottom')) + 10 : 10; 

    // Adjust heights to newly calculated by manipulating min-heights, so height calculation stays correct always
    if ( ($('#' + page_id).height() + btm) < $('#cus-task-menu').height() ) {
        $('#' + page_id).css('min-height', $('#cus-task-menu').height());
    } else if ( ($('#' + page_id).height() + btm) > $('#cus-task-menu').height() ) {
        $('#' + page_id).css('min-height', $('#' + page_id).height() + btm);
        $('#cus-task-menu').css('min-height', $('#' + page_id).height());
    }
}

function loc(dat_time) {
    if (dat_time != undefined) {
        //var loc_dt = new Date((dat_time).replace(" ", "T") + "-05:00").toString().split("GMT")[0]; // Cusco server 
        var loc_dt = new Date((dat_time).replace(" ", "T") + TZsrvr).toString().split("GMT")[0]; // any server 
        //var loc_dt = new Date((dat_time).replace(" ", "T") + "+00:00").toString().split("GMT")[0]; // any server, seems to be a wp update? 
        return loc_dt;
    } else {
        return ""; // fail silently, so no "undefined" shown
    }
}

function goto(target) {
    // scroll to (form) target with margin
    $('html, body').animate({
        scrollTop: $('a[name="add_' + target + '"]').offset().top - 40 // 40 px = top nav bar (32px) + margin
    }, 700);
}

function amount(number) {
    if ( number =="" || number == null) return "";
    return parseFloat(number).toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

if(typeof escapeSpecialChars == 'undefined') {
    escapeSpecialChars = function (text) {
        if (text == null) return "";
        return text.replace(/[\'\"<>\&]/g, function (c) {
            return '&' +
            (escapeSpecialChars.entityTable[c.charCodeAt(0)] || '#' + c.charCodeAt(0)) + ';';
        });
    };

    // SQL escape: amp, lt, gt, quot and apos
    escapeSpecialChars.entityTable = {
        34 : 'quot', //'#034',
        38 : 'amp', 
        39 : '#039', //'apos', 
        60 : 'lt', 
        62 : 'gt'
    };
}

if(typeof escapeHtmlEntities == 'undefined') {
    escapeHtmlEntities = function (text) {
        return text.replace(/[\u00A0-\u2666\'\"<>\&]/g, function(c) {
            return '&' + 
            (escapeHtmlEntities.entityTable[c.charCodeAt(0)] || '#'+c.charCodeAt(0)) + ';';
        });
    };

    // all HTML4 entities as defined here: https://www.w3.org/TR/html4/sgml/entities.html
    // added: amp, lt, gt, quot and apos
    escapeHtmlEntities.entityTable = {
        34 : '#034', //'quot',
        38 : 'amp', 
        39 : '#039', //'apos', 
        60 : 'lt', 
        62 : 'gt', 
        160 : 'nbsp', 
        161 : 'iexcl', 
        162 : 'cent', 
        163 : 'pound', 
        164 : 'curren', 
        165 : 'yen', 
        166 : 'brvbar', 
        167 : 'sect', 
        168 : 'uml', 
        169 : 'copy', 
        170 : 'ordf', 
        171 : 'laquo', 
        172 : 'not', 
        173 : 'shy', 
        174 : 'reg', 
        175 : 'macr', 
        176 : 'deg', 
        177 : 'plusmn', 
        178 : 'sup2', 
        179 : 'sup3', 
        180 : 'acute', 
        181 : 'micro', 
        182 : 'para', 
        183 : 'middot', 
        184 : 'cedil', 
        185 : 'sup1', 
        186 : 'ordm', 
        187 : 'raquo', 
        188 : 'frac14', 
        189 : 'frac12', 
        190 : 'frac34', 
        191 : 'iquest', 
        192 : 'Agrave', 
        193 : 'Aacute', 
        194 : 'Acirc', 
        195 : 'Atilde', 
        196 : 'Auml', 
        197 : 'Aring', 
        198 : 'AElig', 
        199 : 'Ccedil', 
        200 : 'Egrave', 
        201 : 'Eacute', 
        202 : 'Ecirc', 
        203 : 'Euml', 
        204 : 'Igrave', 
        205 : 'Iacute', 
        206 : 'Icirc', 
        207 : 'Iuml', 
        208 : 'ETH', 
        209 : 'Ntilde', 
        210 : 'Ograve', 
        211 : 'Oacute', 
        212 : 'Ocirc', 
        213 : 'Otilde', 
        214 : 'Ouml', 
        215 : 'times', 
        216 : 'Oslash', 
        217 : 'Ugrave', 
        218 : 'Uacute', 
        219 : 'Ucirc', 
        220 : 'Uuml', 
        221 : 'Yacute', 
        222 : 'THORN', 
        223 : 'szlig', 
        224 : 'agrave', 
        225 : 'aacute', 
        226 : 'acirc', 
        227 : 'atilde', 
        228 : 'auml', 
        229 : 'aring', 
        230 : 'aelig', 
        231 : 'ccedil', 
        232 : 'egrave', 
        233 : 'eacute', 
        234 : 'ecirc', 
        235 : 'euml', 
        236 : 'igrave', 
        237 : 'iacute', 
        238 : 'icirc', 
        239 : 'iuml', 
        240 : 'eth', 
        241 : 'ntilde', 
        242 : 'ograve', 
        243 : 'oacute', 
        244 : 'ocirc', 
        245 : 'otilde', 
        246 : 'ouml', 
        247 : 'divide', 
        248 : 'oslash', 
        249 : 'ugrave', 
        250 : 'uacute', 
        251 : 'ucirc', 
        252 : 'uuml', 
        253 : 'yacute', 
        254 : 'thorn', 
        255 : 'yuml', 
        402 : 'fnof', 
        913 : 'Alpha', 
        914 : 'Beta', 
        915 : 'Gamma', 
        916 : 'Delta', 
        917 : 'Epsilon', 
        918 : 'Zeta', 
        919 : 'Eta', 
        920 : 'Theta', 
        921 : 'Iota', 
        922 : 'Kappa', 
        923 : 'Lambda', 
        924 : 'Mu', 
        925 : 'Nu', 
        926 : 'Xi', 
        927 : 'Omicron', 
        928 : 'Pi', 
        929 : 'Rho', 
        931 : 'Sigma', 
        932 : 'Tau', 
        933 : 'Upsilon', 
        934 : 'Phi', 
        935 : 'Chi', 
        936 : 'Psi', 
        937 : 'Omega', 
        945 : 'alpha', 
        946 : 'beta', 
        947 : 'gamma', 
        948 : 'delta', 
        949 : 'epsilon', 
        950 : 'zeta', 
        951 : 'eta', 
        952 : 'theta', 
        953 : 'iota', 
        954 : 'kappa', 
        955 : 'lambda', 
        956 : 'mu', 
        957 : 'nu', 
        958 : 'xi', 
        959 : 'omicron', 
        960 : 'pi', 
        961 : 'rho', 
        962 : 'sigmaf', 
        963 : 'sigma', 
        964 : 'tau', 
        965 : 'upsilon', 
        966 : 'phi', 
        967 : 'chi', 
        968 : 'psi', 
        969 : 'omega', 
        977 : 'thetasym', 
        978 : 'upsih', 
        982 : 'piv', 
        8226 : 'bull', 
        8230 : 'hellip', 
        8242 : 'prime', 
        8243 : 'Prime', 
        8254 : 'oline', 
        8260 : 'frasl', 
        8472 : 'weierp', 
        8465 : 'image', 
        8476 : 'real', 
        8482 : 'trade', 
        8501 : 'alefsym', 
        8592 : 'larr', 
        8593 : 'uarr', 
        8594 : 'rarr', 
        8595 : 'darr', 
        8596 : 'harr', 
        8629 : 'crarr', 
        8656 : 'lArr', 
        8657 : 'uArr', 
        8658 : 'rArr', 
        8659 : 'dArr', 
        8660 : 'hArr', 
        8704 : 'forall', 
        8706 : 'part', 
        8707 : 'exist', 
        8709 : 'empty', 
        8711 : 'nabla', 
        8712 : 'isin', 
        8713 : 'notin', 
        8715 : 'ni', 
        8719 : 'prod', 
        8721 : 'sum', 
        8722 : 'minus', 
        8727 : 'lowast', 
        8730 : 'radic', 
        8733 : 'prop', 
        8734 : 'infin', 
        8736 : 'ang', 
        8743 : 'and', 
        8744 : 'or', 
        8745 : 'cap', 
        8746 : 'cup', 
        8747 : 'int', 
        8756 : 'there4', 
        8764 : 'sim', 
        8773 : 'cong', 
        8776 : 'asymp', 
        8800 : 'ne', 
        8801 : 'equiv', 
        8804 : 'le', 
        8805 : 'ge', 
        8834 : 'sub', 
        8835 : 'sup', 
        8836 : 'nsub', 
        8838 : 'sube', 
        8839 : 'supe', 
        8853 : 'oplus', 
        8855 : 'otimes', 
        8869 : 'perp', 
        8901 : 'sdot', 
        8968 : 'lceil', 
        8969 : 'rceil', 
        8970 : 'lfloor', 
        8971 : 'rfloor', 
        9001 : 'lang', 
        9002 : 'rang', 
        9674 : 'loz', 
        9824 : 'spades', 
        9827 : 'clubs', 
        9829 : 'hearts', 
        9830 : 'diams', 
        338 : 'OElig', 
        339 : 'oelig', 
        352 : 'Scaron', 
        353 : 'scaron', 
        376 : 'Yuml', 
        710 : 'circ', 
        732 : 'tilde', 
        8194 : 'ensp', 
        8195 : 'emsp', 
        8201 : 'thinsp', 
        8204 : 'zwnj', 
        8205 : 'zwj', 
        8206 : 'lrm', 
        8207 : 'rlm', 
        8211 : 'ndash', 
        8212 : 'mdash', 
        8216 : 'lsquo', 
        8217 : 'rsquo', 
        8218 : 'sbquo', 
        8220 : 'ldquo', 
        8221 : 'rdquo', 
        8222 : 'bdquo', 
        8224 : 'dagger', 
        8225 : 'Dagger', 
        8240 : 'permil', 
        8249 : 'lsaquo', 
        8250 : 'rsaquo', 
        8364 : 'euro'
    };
}
