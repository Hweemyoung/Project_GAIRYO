// var mysql = require('mysql');
// var con = mysql.createConnection({
//     host: 'localhost',
//     user: 'root',
//     password: '9957',
//     database: 'gairyo'
// });
// con.connect(function(err){
//     if (err) throw err;
//     console.log('Connected!');
//     var id = "0";
//     var sql = "SELECT * FROM requests WHERE `id_from=`" + id;
//     con.query(sql, function(err, result, fields){
//         if (err) throw err;
//         console.log(result);
//     })
// });


// // Get number from DB
// var number_requests = 3;
// var number_notices = 2;

// // Get href and texts of .dropdown-item from DB
// class requestPut {
//     constructor(status, nameFrom, nameTo, date, shift) {
//         this.status = status;
//         this.nameFrom = nameFrom;
//         this.nameTo = nameTo;
//         this.date = date;
//         this.shift = shift;
//     }
//     createText() {
//         switch(this.status){
//             // Case declined
//             case 0:
//                 var text = 'Put request: ' + this.date + 'from ' + this.nameFrom + 


//         }
//     }
// }

// // .dropdown-item frame
// const dropdownItemRaw = $('<a></a>').addClass('dropdown-item');


// // Create 'nav' under section#section-nav
// var $nav = $('<nav></nav>').addClass('navbar navbar-expand-sm bg-light fixed-top');

// // Complete logo part 'a'
// var $a = $('<a></a>').addClass('navbar-brand order-sm-1 d-flex').attr('href', './overview.html').appendTo($nav);
// $('<img>').addClass('d-none d-md-block mr-md-4').attr('src', './data/png/logo_travel_color_large.png').attr('alt', 'imgLogo').appendTo($a);
// $('<p></p>').addClass('d-none d-sm-block mr-md-4').text('外国人旅行センター').appendTo($a);
// $('<p></p>').addClass('d-sm-none').text('外旅').appendTo($a);

// // Complete navbar part 'ul'
// var $ul = $('<ul></ul>').addClass('px-0 ml-auto mr-2 my-0 order-sm-3').attr('id', 'navbar').appendTo($nav);
// var $liRequests = $('<li></li>').addClass('nav-item dropdown no-arrow').appendTo($ul);
// var $aRequests = $('<a></a>').addClass('nav-link dropdown-toggle text-light').attr('href', '').attr('role', 'button').attr('data-toggle', 'dropdown').appendTo($liRequests);
// // Badge_requests
// $('<span></span>').addClass('badge badge-sm badge-danger').append($('<i></i>').addClass('fas fa-exchange-alt')).appendTo($aRequests);
// // Badge_number_requests
// $('<span></span>').addClass('badge badge-sm badge-danger').text(number_requests).appendTo($liRequests);
// var $dropdownRequests = $('<div></div>').addClass('dropdown-menu dropdown-menu-right').appendTo($liRequests);
// $('<div></div>').addClass('dropdown-header').text('Requests').appendTo($dropdownRequests);



// var $liNotices = $('<li></li>').addClass('nav-item dropdown no-arrow').appendTo($ul);
// var $aNotices = $('<a></a>').addClass('nav-link dropdown-toggle text-light').attr('href', '').attr('role', 'button').attr('data-toggle', 'dropdown').appendTo($liNotices);
// // badge_notices
// $('<span></span>').addClass('badge badge-sm badge-warning').append($('<i></i>').addClass('fas fa-bell fa-fw')).appendTo($aNotices);
// // badge_number_notices
// $('<span></span>').addClass('badge badge-sm badge-danger').text(number_notices).appendTo($liNotices);


// Append 'a' to 'nav'

// GOOGLE API
// function onSignIn(googleUser) {
//     var profile = googleUser.getBasicProfile();
//     console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
//     console.log('Name: ' + profile.getName());
//     console.log('Image URL: ' + profile.getImageUrl());
//     console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
// }

function init() {
    console.log('init');
    gapi.load('auth2', function () {
        /* Ready. Make a call to gapi.auth2.init or some other API */
        var gauth = gapi.auth2.init({
            // Specify your app's client ID
            client_id: '794838339499-eq7uhgrb57bsglhrjvcm760n4blj3lrs.apps.googleusercontent.com'
          });
        gauth.then(function(){
            console.log('GoogleAuth Initialized!');
        }, function(){
            console.log('GoogleAuth Initialization FAILED!');
        });
    });
}