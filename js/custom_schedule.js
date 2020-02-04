// RESIZE
function resizeColumns() {
    $('.div-columns .column button.btn').map(function () {
        // Switch screen_sm
        if (window.innerWidth < 576 && !screen_sm) {
            $('.div-schedule .timeline ul li span').map(function () {
                // Slice timeline texts
                this.innerHTML = this.innerHTML.slice(0, 2);
            });
            screen_sm = true;
        } else if (window.innerWidth >= 576 && screen_sm) {
            $('.timeline ul').map(function () {
                $(this).children('li').filter(function () {
                    return $(this).css('display') != 'none'
                }).map(function (index) {
                    $(this).children('span').html(timeline_valid[index]);
                });
            });
            screen_sm = false;
        }
    });

    // if (window.innerWidth < 768 && !screen_md) {
    if (window.innerWidth < 768) {
        // $('.div-schedule').css('height', 500);
        screen_md = true;
    // } else if (window.innerWidth >= 768 && screen_md && $('.col-right').height() > 500) {
    } else {
        // $('.div-schedule').css('height', $('.col-right').height());
        screen_md = false;
    }
    // $('.div-schedule .timeline ul li').css('height', $('.div-schedule').height() / 12);

    resizeButtons();
}

function resizeButtons(){
    var heightLi = $('.div-schedule .timeline ul li').height()
    // $('.div-schedule .div-columns .column a.btn').map(function(){
    $('.div-schedule .div-columns .column button.btn').map(function(){
        // Calc button height and top
        timeStartScaled = parseInt($(this).attr('time-start').slice(0, 2), 10) + parseInt($(this).attr('time-start').slice(-2), 10) / 60;
        timeEndScaled = parseInt($(this).attr('time-end').slice(0, 2), 10) + parseInt($(this).attr('time-end').slice(-2), 10) / 60;
        var heightButton = heightLi * (timeEndScaled - timeStartScaled);
        var topButton = heightLi * (timeStartScaled - timelineStartScaled)
        // Set height and top of button
        $(this).css('height', heightButton).css('top', topButton);
    });
}

// INITIALIZATION
// Screen mode
if (window.innerWidth < 576) {
        var screen_sm = true;
    } else {
        var screen_sm = false;
    }

    if (window.innerWidth < 768) {
        var screen_md = true;
    } else {
        var screen_md = false;
    }

    // Hparams
    const numColumnsPerDiv = 3

    // Create timeline
    const timeline_default = ['07:00', '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00'];
    $(timeline_default).map(function () {
        $('.timeline ul').append($('<li></li>').append('<span>' + this + '</span>'));
    })

    // Remove 2n th li object
    $('.timeline li:nth-child(2n)').css('display', 'none');

    // Store valid timeline texts
    var timeline_valid = [];
    var li_valid = $('.timeline ul li').filter(function () {
        return $(this).css('display') != 'none'
    });
    li_valid.map(function () {
        timeline_valid.push($(this).children('span').html());
    });

    // Timeline start
    var timelineStartScaled = parseInt(timeline_valid[0].slice(0, 2), 10) + parseInt(timeline_valid[0].slice(-2), 10) / 60;

    // Slice timeline text when screen_sm
    if (screen_sm) {
        li_valid.map(function () {
            $(this).children().html($(this).children().html().slice(0, 2));
        })
    }

    // Manipulate modal
    $(document).ready(function () {
        $('.div-columns button.btn').click(function () {
            $('.modal-title').html('<h3>' + $(this).children('h5').html() + '</h3>');
            $('.modal-body').empty();
            $(this).children('ul').clone().appendTo($('.modal-body'));
            // $('.modal').modal();
        });
    });

    // Resize
    resizeColumns();
    $(window).resize(resizeColumns);
    

    // EVENTS
    // $(window).resize(resizeColumns);
    // $(document).ready(function () {
    //     $('#accordion .card .card-header a').click(function () {
    //         $(this).parent().siblings('.collapse').append()
    //     });
    // });


    // TEMPORARY
    // Duplicate card-body
    // $('#accordion .card .collapse:not(#day4) .card-body').map(function () {
    //     $(this).replaceWith($('#day4 .card-body').clone());
    // });
// $('#accordion .card .collapse .card-body').replaceWith($('#day4 .card-body'))