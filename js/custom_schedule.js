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

if(screen_sm){
    li_valid.map(function(){
        $(this).children().html($(this).children().html().slice(0, 2));
    })
}

function resizeColumns() {
    // Distance between timestamps
    var num_horizontal_lines = timeline_valid.length;
    var dist_horizontal = $('.div-schedule').height() / num_horizontal_lines;
    $('.timeline li').css('height', dist_horizontal).attr('type', 'none');


    // set top of #div-columns
    $('#div-columns').css('top', '.5rem');

    var timeline_width = $('.timeline').width();
    var columns_left = $('#div-columns').position().left;

    // Calc width and height of #div-columns
    $('#div-columns').css('width', timeline_width - columns_left).css('height', $('.timeline').height());

    // Calc width of every column and buttons
    var width = $('#div-columns').width() / $('.column').length;
    $('.column').css('width', width);
    $('.column a').css('width', width);

    var time_head = timeline_default[0];
    var time_head = Number(time_head.slice(0, 2)) + Number(time_head.slice(-2)) / 60
    $('#div-columns .column a').map(function () {
        // Calc top
        // Get hours
        var hours_start = Number(this.getAttribute('time-start').slice(0, 2));
        // Get minutes
        var minutes_start = Number(this.getAttribute('time-start').slice(-2));
        // Scale
        var scale_start = hours_start + minutes_start / 60;
        // Calc num of blocks
        var blocks = scale_start - time_head;
        // Calc top of button
        var top = blocks * dist_horizontal;
        // console.log(scale_start, time_head, dist_horizontal);
        $(this).css('top', top);

        // Calc button height
        var hours_end = Number(this.getAttribute('time-end').slice(0, 2));
        var minutes_end = Number(this.getAttribute('time-end').slice(-2));
        var scale_end = hours_end + minutes_end / 60;
        var blocks = scale_end - scale_start;
        var height = blocks * dist_horizontal;
        $(this).css('height', height);


        if (window.innerWidth < 576 && !screen_sm) {
            li_valid.children('span').map(function (index) {
                this.innerHTML = this.innerHTML.slice(0, 2);
            });
            screen_sm = true;
        } else if (window.innerWidth >= 576 && screen_sm) {
            li_valid.children('span').map(function (index) {
                this.innerHTML = timeline_valid[index];
            });
            screen_sm = false;
        }

        if (window.innerWidth < 768 && !screen_md) {
            $('.div-schedule').css('height', 500);
            screen_md = true;
        } else if (window.innerWidth >= 768 && screen_md) {
            if ($('#shift-member-table').height() > 500) {
                $('.div-schedule').css('height', $('#shift-member-table').height());
            }
            screen_md = false;
        }
    })
}

resizeColumns();
$(window).resize(resizeColumns);