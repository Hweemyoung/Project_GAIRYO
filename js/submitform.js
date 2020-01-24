function loadRecords(arrayRecords) {
    for (name in arrayRecords) {
        if (arrayRecords[name] === '0') {
            continue;
        } else {
            // console.log(name);
            checkLabels($(`#form-application label[for=${name}]`))
        }
    }
}

var listShifts = ['A', 'B', 'H', 'C', 'D', 'O']
var weekdays = new Array(7);
weekdays[0] = "Sun";
weekdays[1] = "Mon";
weekdays[2] = "Tue";
weekdays[3] = "Wed";
weekdays[4] = "Thu";
weekdays[5] = "Fri";
weekdays[6] = "Sat";
var months = new Array(12);
months[0] = "Jan";
months[1] = "Feb";
months[2] = "Mar";
months[3] = "Apr";
months[4] = "May";
months[5] = "Jun";
months[6] = "Jul";
months[7] = "Aug";
months[8] = "Sep";
months[9] = "Oct";
months[10] = "Nov";
months[11] = "Dec";

const monthShift = _constants.months[submitMonth - 1] + ' ' + String(submitYear); // String('Dec 2020')
var d = new Date('15 ' + monthShift);

// Clone .form-check-inline
const checkboxFrame = $('#div-form form>.row table tbody tr td .form-group .form-check-inline').clone();
// Remove .form-group
$('#div-form form>.row table tbody tr td .form-group').remove();
// Clone raw row w/o .form-group
const rowFrame = $('#div-form form .row table tbody tr').clone();
// Remove original row
$('#div-form form>.row table tbody tr').remove();
// Create right column
$("#div-form form>.row .col-sm-6").clone().appendTo($('#div-form form>.row'));
var tBodyLeft = $('#div-form form>.row .col-sm-6:first-child table tbody');
var tBodyRight = $('#div-form form>.row .col-sm-6:last-child table tbody');
// Fill out headings
tBodyRight.closest('table').siblings('h3').html(monthShift);
var lastMonth = new Date(d);
lastMonth.setMonth(lastMonth.getMonth() - 1);
tBodyLeft.closest('table').siblings('h3').html(_constants.months[lastMonth.getMonth()] + ' ' + lastMonth.getFullYear());

// Create rows within tbody
while (true) {
    var newRow = rowFrame.clone();
    var tds = newRow.find('td');
    // First td: fill with date
    var date = d.getDate()
    tds[0].innerHTML = date;
    // Second td: fill with day
    tds[1].innerHTML = _constants.weekdays[d.getDay()];
    if (d.getDay() === 0) {
        tds.slice(0, 2).addClass('text-danger');
    } else if (d.getDay() === 6) {
        tds.slice(0, 2).addClass('text-primary');
    }
    // Third td: complete checkboxes
    // 1. Append new .form-group
    $('<div></div>').addClass('form-group').appendTo($(tds[2]));
    // 2. Append clone of formCheckInlines to .form-group
    for (var i = 0; i < listShifts.length; i++) {
        var shift = listShifts[i];
        var id = String(date) + shift;
        var newFormCheckInline = checkboxFrame.clone();
        newFormCheckInline.find('label').attr('for', id).append(shift).find('input').attr('name', id).attr('value', id).attr('id', id);
        newFormCheckInline.appendTo($(tds[2]).find('.form-group'));
    }
    // Append row to tbody
    if (date < 16) {
        newRow.prependTo(tBodyRight);
    } else {
        newRow.prependTo(tBodyLeft);
    }
    // Go to previous day
    if (d.getDate() === 16) {
        break;
    } else {
        d.setDate(d.getDate() - 1);
    }
}

function uncheckLabels($labels) {
    $labels.removeClass('text-info').removeClass('text-selected').find('input').prop('checked', false);
}

function checkLabels($labels) {
    $labels.addClass('text-info').addClass('text-selected').find('input').prop('checked', true);
}

$('label').click(function () {
    if ($(this).find('input').is(':checked')) {
        checkLabels($(this));
    } else {
        uncheckLabels($(this));
    }
});

$('label').filter(function () {
    return $(this).text().trim() === 'O'
}).click(function () {
    uncheckLabels($(this).parent().siblings('.form-check-inline').find('label'));
});

$('label').filter(function () {
    return $(this).text().trim() !== 'O'
}).click(function () {
    var labelsNotO = $(this).parent('.form-check-inline').parent('.form-group').find('label').filter(function () {
        return $(this).text().trim() !== 'O'
    });
    // If all checkbox other than 'O' is checked:
    if (labelsNotO.find('input').not(':checked').length == 0) {
        // Uncheck them
        uncheckLabels(labelsNotO);
        // Check label for O
        checkLabels(
            $(this).parent().siblings('.form-check-inline').find('label').filter(function () {
                return $(this).text().trim() === 'O'
            })
        );
    } else {
        uncheckLabels(
            $(this).parent().siblings('.form-check-inline').find('label').filter(function () {
                return $(this).text().trim() === 'O'
            })
        );
    }
});

// BUTTONS
// btn-clear
$('#btn-clear').click(function (event) {
    uncheckLabels($('#div-form form .form-group label'));
})

// btn-comfirm
// Complete modal body
$('#btn-confirm').click(function (event) {
    var $trs = $('#div-form table tbody tr').clone().filter(function () {
        return $(this).find('td:last-child input').is(':checked')
    }).map(function () {
        // For each tr
        $(this).find('td:first-child').each(function () {
            // For each td:first-child
            if (parseInt($(this).text()) < 16) {
                $(this).prepend(document.createTextNode(' '));
                $(this).prepend(_constants.months[submitMonth - 1]);
            } else {
                $(this).prepend(document.createTextNode(' '));
                $(this).prepend(_constants.months[lastMonth.getMonth()] + ' ');
            }
        })
        $(this).find('td:last-child').each(function () {
            // For each td:last-child
            var $td = $(this);
            var texts = $(this).clone().find('input').filter(function () {
                return $(this).is(':checked')
            }).map(function () {
                // $(text, ..., text)
                return this.nextSibling.nextSibling
            });
            $td.empty();
            texts.each(function () {
                $('<kbd></kbd>').append(this).appendTo($td);
                // $td.append(this)
                $td.append(document.createTextNode(' '));
            })
            // for (var i = 0; i < texts.length; i++) {
            //     $('<mark></mark>').append(text);
            //     $(this).append(texts[i]);
            //     if(i==texts.length - 1){
            //         break;
            //     } else {
            //         $(this).append('/');
            //     }
            // }
        });
        return this
    });
    if ($trs.length !== 0) {
        $trs.appendTo('#modal-confirm .modal-body tbody');
    } else {
        $('<tr><td colspan=3>None</td></tr>').appendTo('#modal-confirm .modal-body tbody');
    }
    $('#modal-confirm').modal('show');
});
// Clear modal body
$('#modal-confirm').on('hidden.bs.modal', function (event) {
    $('#modal-confirm .modal-body tbody').empty();
})

// Load records
loadRecords(arrayRecords);


// $('#div-form table tbody tr form-group form-check-inline').filter(function () {
//     // Return true if any checkbox in .form-check-inline is checked
//     return $(this).find('input').is(':checked')
// }).clone().map(function () {
//     $(this).closest('td').html($(this).find('input').filter(function () {
//         // $('input', ...,'input')
//         return $(this).is(':checked')
//     }).map(function () {
//         // $(text, ..., text)
//         return this.nextSibling.nextSibling
//     }))
// })
// $('#modal-confirm').modal('show');
