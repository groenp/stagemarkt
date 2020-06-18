/******************************************************************************/
/*  groenp-sites-cms-admin.js                                   Pieter Groen  */
/*                                                                            */
/*  Version 0.1 - May 26, 2020   - copied from CuscoNow.com                   */
/*  Version 0.2 - June 17, 2020  - Rename to stipulate this is for ALL USERS  */
/*                                                                            */
/*  JS for Groen Productions - Sites Mgmt Tool for ALL USERS                  */
/*  includes JS for asynchronous communications with  website                 */
/*  THIS FILE MAY BE AUGMENTED WITH A PRODUCT SPECIFIC ONE FOR EACH BUILT     */
/*                                                                            */
/******************************************************************************/

// We're only using jQuery here
$ = jQuery;
//alert("groenp-sbscrbr.js loaded");                              // DEBUG //


// ****************************************************************************
// Global vars that are needed throughout the session need to be stored in Local Storage
//
// 
// ****************************************************************************

var InitialValues = [];         // initial values for form after loading of page
var Changed = false;            // to store whether form content has changed

/* All functionality is loaded on document.ready */
$(document).ready(function () {

    // ************************************************************************
    // All subscriber access pages
    //
    // Only calls in here that couldn't be resolved with WordPress hooks in PHP
    // collect here all jQuery that needs to load with the page
    //
    // always check after each update to WordPress 
    // ************************************************************************


    // groen productions: yes it's me!
    $('#pg-GroenP').hover(function () {
        $(this).removeClass('conceal');
        void $('#pg-GroenP').offset();
        $(this).addClass('reveal');
    }, function () {
        $(this).removeClass('reveal');
            void $('#pg-GroenP').offset();
        $(this).addClass('conceal');
    });

    // Remove Welcome to GP | SMT
    // only if the removal checkbox is in the screen options
    if ( $('#welcome-mb-hide').length ) {

        // show the control
        $('#welcome-mb-boxctrl').show();

        // add click event
        $('#welcome-mb-boxctrl a').click(function() {
            $('#welcome-mb-hide').click();
        });

    }
    
//     // Remove screen options tab
//     // $('#screen-options-link-wrap').hide();


});


// ****************************************************************************
// Helper functions
// ****************************************************************************

function confirm_deletion(inp) {
    $('#' + inp).val(confirm("Select 'OK' to confirm deletion.") ? "yes" : "no");
}
/* only used in subscribers.php, but grouped with all other helper functions */
function confirm_attachment(inp) {
    $('#' + inp).val(confirm("Select 'OK' to confirm attaching wp_user.") ? "yes" : "no");
}

function clear_filter(tbl) {
    $('#' + tbl + ' td.fltr>input[type="text"], #' + tbl + ' td.fltr>select').val("");
    $('#' + tbl + ' td.head>input[type="text"], #' + tbl + ' td.head>select').val("");
}

function srt_by(propA, propB, propC) {
    return function (a, b) {
        // if it both are a number and a>b, if a is null and b not, if both are not null and not numbers and a>b 
        // if( (a[propA] != null) && ( (b[propA] == null) || ( !isNaN(a[propA]) && (parseInt(a[propA])-parseInt(b[propA]))>0 ) || (a[propA] > b[propA]) ) ){
        //console.log( parseInt(a[propA]) + " - " + parseInt(b[propA]) +" = " + (parseInt(a[propA])-parseInt(b[propA])) ); 
        if ((!isNaN(a[propA]) && !isNaN(b[propA]) && (parseInt(a[propA]) - parseInt(b[propA])) > 0) || (a[propA] == null && b[propA] != null) || (a[propA] != null && isNaN(a[propA]) && b[propA] != null && isNaN(b[propA]) && (a[propA].toLowerCase() > b[propA].toLowerCase()))) {
            return 1;
        } else if ((!isNaN(a[propA]) && !isNaN(b[propA]) && (parseInt(a[propA]) - parseInt(b[propA])) < 0) || (a[propA] != null && b[propA] == null) || (a[propA] != null && isNaN(a[propA]) && b[propA] != null && isNaN(b[propA]) && (a[propA].toLowerCase() < b[propA].toLowerCase()))) {
            return -1;
        } else if ((!isNaN(a[propB]) && !isNaN(b[propB]) && (parseInt(a[propB]) - parseInt(b[propB])) > 0) || (a[propB] == null && b[propB] != null) || (a[propB] != null && isNaN(a[propB]) && b[propB] != null && isNaN(b[propB]) && (a[propB].toLowerCase() > b[propB].toLowerCase()))) {
            return 1;
        } else if ((!isNaN(a[propB]) && !isNaN(b[propB]) && (parseInt(a[propB]) - parseInt(b[propB])) < 0) || (a[propB] != null && b[propB] == null) || (a[propB] != null && isNaN(a[propB]) && b[propB] != null && isNaN(b[propB]) && (a[propB].toLowerCase() < b[propB].toLowerCase()))) {
            return -1;
        } else if ((!isNaN(a[propC]) && !isNaN(b[propC]) && (parseInt(a[propC]) - parseInt(b[propC])) > 0) || (a[propC] == null && b[propC] != null) || (a[propC] != null && isNaN(a[propC]) && b[propC] != null && isNaN(b[propC]) && (a[propC].toLowerCase() > b[propC].toLowerCase()))) {
            return 1;
        } else if ((!isNaN(a[propC]) && !isNaN(b[propC]) && (parseInt(a[propC]) - parseInt(b[propC])) < 0) || (a[propC] != null && b[propC] == null) || (a[propC] != null && isNaN(a[propC]) && b[propC] != null && isNaN(b[propC]) && (a[propC].toLowerCase() < b[propC].toLowerCase()))) {
            return -1;
        }
        return 0;
    }
}

function loc(dat_time) {
    if (dat_time != undefined) {

        // create time zone information for client, assume server data always in GMT
        // var tz = Date().split("GMT")[1].substring(0, 3);
        // if ( tz && !isNaN(parseInt(tz)) ) {

        //     // create Date object, add timezone hours to it (can be negative for N/A), then strip off all TZ data
        //     var loc_dt = new Date((dat_time));
        //     loc_dt.setHours(loc_dt.getHours() + parseInt(tz));
        //     // console.log(loc_dt);                                         // DEBUG //
        //     loc_dt = loc_dt.toString().split("GMT")[0];
        // } else {
        //     // just take the GMT time that was put in (this has the SAME RESULT!)
        //     var loc_dt = new Date((dat_time).replace(" ", "T") + "+00:00").toString().split("GMT")[0]; // any server, seems to be a wp update? 
        // }
        // just take the GMT time that was put in (this has the SAME RESULT!)
        var loc_dt = new Date((dat_time).replace(" ", "T") + "+00:00").toString().split("GMT")[0]; // any server, seems to be a wp update? 
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
