/***********************************************************************/
/***                          GLOBAL VARS                            ***/
/***********************************************************************/
var Type = ""; // type of AJAX request: 'test'
var Info = ""; // any data that's retrieved from server


/***********************************************************************/
/***                      ON LOAD OF JS FILE (happens first)         ***/
/***********************************************************************/

// console.log("js load");                                                                         // DEBUG //


/***********************************************************************/
/***                  DOCUMENT READY LOADING                         ***/
/***********************************************************************/

$(document).ready(function () {

    console.log("on document ready");                                                             // DEBUG //

    // groen productions: yes it's me!
    $('.groenp a').hover(function () {
        $('#pg-GroenP').removeClass('conceal');
        void $('.groenp a').offset();
        $('#pg-GroenP').addClass('reveal');
    }, function () {
        $('#pg-GroenP').removeClass('reveal');
        void $('.groenp a').offset();
        $('#pg-GroenP').addClass('conceal');
    });

    // groen productions: there's no hover on phones, use end of page
    $(window).scroll(function () {
        // console.log("scrollTop + window: " + parseInt($(window).scrollTop() + $(window).height()));                          // DEBUG //
        // console.log("document h: " + $(document).height());                                                                  // DEBUG //

        if (parseInt($(window).scrollTop() + $(window).height() + 260) >= parseInt($(document).height())) {

            // console.log("reached bottom: " + parseInt($(window).scrollTop() + $(window).height() + 260 - $(document).height())); // DEBUG //

            $('#pg-GroenP').removeClass('conceal');
            void $('#pg-GroenP').offset();
            $('#pg-GroenP').addClass('reveal');
        } else {
            $('#pg-GroenP').removeClass('reveal');
            void $('#pg-GroenP').offset();
            $('#pg-GroenP').addClass('conceal');
        }
    });

    /*********************** cookie section ***********************/

    // ckie: destroy the privacy alert if already dismissed once, otherwise show it again
    if (readCkie('tou')) {
        // DEBUG
        var readDate = new Date(readCkie('tou'));   // Cookie Read date; only date, no hrs, mins, secs, etc.
        var privDate = new Date(PrivDate);          // Latest Post date; only date, no hrs, mins, secs, etc.
        var now = new Date();
        now.setHours(0, 0, 0, 0);                   // Now: set Now to no hrs, mins, secs, etc.

        // privDate.setDate(now.getDate() + 0); // set it to x days ago/future                                              // DEBUG //
        // readDate.setDate(now.getDate() - 0); // set it to x days ago/future                                              // DEBUG //
        // console.log("tou cookie: " + readCkie('tou'));                                                                   // DEBUG //
        // console.log("privDate: " + privDate);                                                                            // DEBUG //
        // console.log("readDate: " + readDate);                                                                            // DEBUG //
        // console.log("now: " + now);                                                                                      // DEBUG //

        if (privDate.getTime() - now.getTime() > 0) {                       // if Post date is in the future: do nothing
            // console.log("Post date is in the future");                                          // DEBUG
            $('#priv-tou').remove();                                        // remove the tou alert, so it does not block the menu

        } else if (privDate.getTime() - now.getTime() == 0) {                // if Post date is today:
            // console.log("Post date is today");                                                                           // DEBUG //
            if (privDate.getTime() - readDate.getTime() == 0) {                 // if Post date is equal to Read date: do nothing 
                // console.log("...and Read date is today");                                                                // DEBUG //
                $('#priv-tou').remove();                                            // remove the tou alert, so it does not block the menu

            } else {                                                            // if Post date is later than Read date: post update
                // console.log("...and Read date is in the past");                                                          // DEBUG //
                $('.upd-priv').show();                                              // show update message
                $('.new-priv').hide();                                              // hide new message
                $('#priv-tou').addClass('show');                                    // show the tou alert                                      
            }
        } else {                                                            // if Post date is in the past:
            // console.log("Post date is in the past");                                                                     // DEBUG //
            if (privDate.getTime() - readDate.getTime() >= 0) {                 // if Post date is equal or later than Read date: post update 
                // console.log("...and Read date is the same or earlier");                                                  // DEBUG //
                $('.upd-priv').show();                                              // show update message
                $('.new-priv').hide();                                              // hide new message
                $('#priv-tou').addClass('show');                                    // show the tou alert  

            } else {                                                            // if Post date is older than Read date: do nothing
                // console.log("...and Read date is later");                                                                // DEBUG //
                $('#priv-tou').remove();                                            // remove the tou alert, so it does not block the menu
            }
        }

    } else {                                                                // if there is no tou cookie: post new message
        // console.log("There is no tou cookie");                                                                           // DEBUG //
        $('.upd-priv').hide();                                                  // hide update message
        $('.new-priv').show();                                                  // show new message
        $('#priv-tou').addClass('show');                                        // show the tou alert                                      
    };

    // ckie: ok-button handler for cookie modal
    $('#ok-cookie').click(function () {
        writeCkie('ckie', true, 366);                                       // write cookie, don't care if successful or not
        $('#pop-cookie').modal('hide');                                     // hide modal cookie dialog in any case
        $("#cookieSwitch").prop('checked', true);                           // set control (if exists)
        $("#cookieSwitch").next().text("Cookies accepted");                 // adjust label control (if exists)
    });

    // ckie: x-button and link handler for tou alert
    $('#priv-tou button, #priv-tou a').click(function () {
        var touRead = new Date();
        touRead.setHours(0, 0, 0, 0);
        if (readCkie('ckie')) writeCkie('tou', touRead, 366);               // only do if cookie set; write tou cookie
        $('#priv-tou').remove();                                            // remove the alert, so it does not block the menu
    });

    // ckie: set cookie switch on load
    if (!readCkie('ckie')) {                                                // if cookies are (!)not accepted
        $("#cookieSwitch").next().text("Cookies NOT accepted");
        $("#cookieSwitch").prop('checked', false);
    } else {
        $("#cookieSwitch").next().text("Cookies accepted");
        $("#cookieSwitch").prop('checked', true);
    };

    // ckie: cookie switch handler
    $('#cookieSwitch').on('change.bootstrapSwitch', function (evt) {
    // console.log("cookie being checked: " + e.target.checked);                                                            // DEBUG //
        if (!evt.target.checked) {

            // console.log("delete cookies now");                                                                           // DEBUG //
            clearCkie('ckie');                                              // delete cookie, don't care if successful or not
            clearCkie('tou');                                               // delete cookie, don't care if successful or not
            clearCkie('pnlClose');                                          // delete cookie, don't care if successful or not
            clearCkie('pnlDel');                                            // delete cookie, don't care if successful or not
            $("#cookieSwitch").next().text("Cookies now NOT accepted");     // adjust label control
        } else {

            // console.log("write cookie ckie now");                                                                        // DEBUG //
            writeCkie('ckie', true, 366);                                   // write cookie, don't care if successful or not
            $("#cookieSwitch").next().text("Cookies now accepted");         // adjust label control
        }
    });

    // ckie: set modal cookie dialog (is this too much nagging? Can also be wrapped inside document.ready)
    if (!readCkie('ckie')) {                                                // only show if cookie not set
        $('#pop-cookie').modal('show');                                     // show modal cookie dialog
        $('.upd-priv').hide();
        $('.new-priv').show();
        $('#priv-tou').addClass('show');                                    // show tou alert
    };

});  // end of document.ready()


/***********************************************************************/
/***              ON WINDOW LOAD (ALL SIZING IS DONE)                ***/
/***********************************************************************/


$(window).on('load', function () {

    // console.log("on window load");                                                                                        // DEBUG //

});


/***********************************************************************/
/***                      HELPER  FUNCTIONS                          ***/
/***********************************************************************/



/***********************************************************************/
/***                      COOKIE  FUNCTIONS                          ***/
/***********************************************************************/

function storage() { // Check if local (and session) storage is supported, returns true or false

    // because of iOS an actual attempt has to be made, this function may run before iOSprivMode()
    try {
        sessionStorage.setItem("test", "1");
        sessionStorage.removeItem("test");
    } catch (e) {
        return false;
    }
    //return false; // DEBUG // force alternative path... 
    return 'localStorage' in window && window['localStorage'] !== null;
} // /storage()



// function iOSprivMode() { // Check if sessionStorage is supported AND working; iOS check
//     try {
//         sessionStorage.setItem("test", "1");
//         sessionStorage.removeItem("test");
//     } catch (e) {
//         if (e.code === DOMException.QUOTA_EXCEEDED_ERR && sessionStorage.length === 0) {
//             console.log("sessionStorage not active: iOS in Private mode");
//             return true;
//         } else {
//             console.log("sessionStorage not present");
//             return false;
//         }
//     }
//     console.log("sessionStorage works");
//     // return true; // DEBUG // force alternative path... 
//     return false;
// } // /iOSprivMode()



function readCkie(name) { // Returns a cookie by name ('name=') as a string, 'name' has to be string
    if (storage() == true) {                                                                            // check if persistent storage is supported
        return localStorage.getItem(name);                                                              // return cookie
    } else {
        var a_ckie = document.cookie.split("; ");                                                       // split domain cookies
        for (var i = 0; i < a_ckie.length; i++) {                                                       // for each cookie
            while (a_ckie[i].charAt(0) == " ") a_ckie[i] = a_ckie[i].substring(1, a_ckie[i].length);    // strip any spaces before cookie name
            if (a_ckie[i].indexOf(name + "=") == 0) {                                                   // if right cookie found
                return unescape(a_ckie[i].substring(name.length + 1, a_ckie[i].length));                // return the section after '='
            }
        }
    }
    return false;
} // /readCkie()


function writeCkie(name, data, days) { // Write cookie with name (as a string) and data, use of days is optional (only works with old cookie system)
    if (storage() == true) {
        localStorage.setItem(name, data);                                                               // persistence (local) storage is always unlimited
    } else {
        var date_today = new Date();                                                                    // today's date, for expiry calculation
        var date_expire = (days) ? new Date(date_today.valueOf() + (days * 86400000)) : null;            // expiry date (= today + exp days in msec)
        var s_ckie = name + "=" + escape(data);                                                         // cookie will be named 'name' DON'T ESCAPE THE '='sign!

        if (days) s_ckie += "; expires=" + date_expire.toGMTString();                                   // and add expiry date in proper format (only when defined)
        s_ckie += "; path=/"                                                                            // set path to use cookie from pages in different dir's
        document.cookie = s_ckie;                                                                       // set the cookie
    }
} // /writeCkie()


function clearCkie(name) { // Wipes cookie, could be used in conjunction with cookie existence check with writeCkie() 
   if (storage() == true) {
       localStorage.removeItem(name);
   } else {
       writeCkie(name, "", -1);
   }
} // /clearCkie()

// function rdGlbl(name) {         // readGlobal var, name is string; usage: var = (storage())? rdGlbl("name") : name;
//     var data = sessionStorage.getItem(name);
//     if (data == null) data = "";
//     return data;
// } // /rdGlbl()


// function wrtGlbl(name, data) {  // writeGlobal var, name and data are strings; usage: name = wrtGlbl("name", data); (may need a JSON.stringify)
//     if (storage()) {
//         sessionStorage.setItem(name, data);
//     }
//     return data;
// } // /wrtGlbl()


// function clrGlbl(name) {        // clearGlobal var, name is string; usage: name = clrGlbl("name");
//     if (storage()) sessionStorage.removeItem(name);
//     return "";
// } // /clrGlbl()



/***********************************************************************/
/***                     DATABASE FUNCTIONS                          ***/
/***********************************************************************/

function goFetch(url) { // Perform an AJAX search (while navigating to a results type page), calls queryCMS with type (of search), and form vars from checkboxes (srchVals) and multi select lists are passed on separately (srchSels).
                        // When url is defined the results page is loaded after the response has been received. The search button should have href="#" in that case.
                        // When no url has been defined, the search button needs an href defined, like with maps.
                        // Maps take longer to load (through AJAX), and can simultaneously wait for response, with lists the page is loaded too soon, and the user will be kicked back to the search form with "no results".

    // $.mobile.loading('show');

    // Set type of search
    var type = "test";
    // var type = (storage()) ? rdGlbl("Type") : Type;
    // if (type == "") {
    //     type = "test";
    // }


    // only do search when known what to search for 
    if (type == "test") {

        // do search
        jQfull.ajax({
            type: "post",
            url: "./assets/queryCMS.php",
            cache: false,
            dataType: "json",
            data: {
                // queryCMS.php switches on 'type' to perform the requested DB search: test, search (options), 
                type: type
            },
            success: function (response) {
                console.log("successfully received response: " + JSON.stringify(response.data));  // DEBUG //

                // Store the info in sessionStorage
                var info = response.data.test;
                // if (info != "no test entries found") {
                //     wrtGlbl("Info", JSON.stringify(info));
                // } else {
                //     wrtGlbl("Info", info);
                // }
                Info = info;
                //console.log(info);                                                                // DEBUG //

                // Navigate or load depending on url presence
                if (url != null) {
                    window.location.href = url;
                    // on that pageload it needs to retrieve any data from the Global var (or sessionStorage)
                } else {
                    // load data
                }
            }, // /success ajax

            error: function (response) {
                console.log("oopsie: " + JSON.stringify(response));  // DEBUG //
            }
        });
    }
    return true;
} // /goFetch()
