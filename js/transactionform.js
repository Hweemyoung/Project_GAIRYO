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

function groupBy(xs, key) {
    return xs.reduce(function (rv, x) {
        (rv[x[key]] = rv[x[key]] || []).push(x);
        return rv;
    }, {});
};

function prepArrayShiftsByIdUser(arrayShiftsByIdUser) {
    // Convert values of 'date_shift' from String to Date Object
    for (idUser in arrayShiftsByIdUser) {
        for (i in arrayShiftsByIdUser[idUser]) {
            // arrayShiftsByIdUser[idUser][i]['date_shift'] = new Date(arrayShiftsByIdUser[idUser][i]['date_shift']);
            var date = new Date(arrayShiftsByIdUser[idUser][i]['date_shift']);
            arrayShiftsByIdUser[idUser][i]["Ym"] = String(date.getFullYear()) + ' ' + months[date.getMonth()]; // '2020 Jan'
            arrayShiftsByIdUser[idUser][i]["d"] = String(date.getDate()); // '20'
            // arrayShiftsByIdUser[idUser][i] = groupBy(arrayShiftsByIdUser[idUser][i], "d");
        }
        arrayShiftsByIdUser[idUser] = groupBy(arrayShiftsByIdUser[idUser], "Ym");
        for (Ym in arrayShiftsByIdUser[idUser]) {
            arrayShiftsByIdUser[idUser][Ym] = groupBy(arrayShiftsByIdUser[idUser][Ym], "d");
            for (d in arrayShiftsByIdUser[idUser][Ym]) {
                for (key in arrayShiftsByIdUser[idUser][Ym][d]) {
                    var shift = arrayShiftsByIdUser[idUser][Ym][d][key]["shift"];
                    arrayShiftsByIdUser[idUser][Ym][d][shift] = arrayShiftsByIdUser[idUser][Ym][d][key]
                    delete arrayShiftsByIdUser[idUser][Ym][d][key];
                }
            }
        }
    }
    return arrayShiftsByIdUser
}
function test(idx) {
    switch (idx) {
        case 0:
            console.log('case 0');
            break;
            console.log('now between 0 and 1');
        case 1:
            console.log('case 1');
            break;
    }
}

function updateOptions(idx, $selects) {
    const shiftsPart1 = ["C", "D"];
    $select = $($selects[idx]);
    // console.log(idx);
    // console.log($selects);
    // console.log($select);

    // Clear all options but default.
    $select.find('option:not(:first-child').remove();
    if (idx == 0 || idx > 4) {
        return
    }
    var selectIdFrom = $selects[0];
    if (idx == 1) {
        // Currently at Month
        for (Ym in window.arrayShiftsByIdUser[selectIdFrom.value]) {
            var $option = $('<option></option>').attr('value', new Date(Ym).getMonth() + 1);
            $option.text(Ym);
            $option.appendTo($select);
        }
        return
    }
    var selectMonth = $selects[1];
    if (idx == 2) {
        // Currently at Day
        var YM = $(selectMonth).children(`option[value=${selectMonth.value}]`).text()
        for (d in window.arrayShiftsByIdUser[selectIdFrom.value][YM]) {
            var $option = $('<option></option>').attr('value', d);
            var weekday = weekdays[new Date(YM + ` ${d}`).getDay()];
            $option.text(d + ` (${weekday})`);
            $option.appendTo($select);
        }
        return
    }
    var selectDay = $selects[2];
    if (idx == 3) {
        // Currently at Shift
        for (shift in window.arrayShiftsByIdUser[selectIdFrom.value][$(selectMonth).children(`option[value=${selectMonth.value}]`).text()][selectDay.value]) {
            var $option = $('<option></option>').attr('value', shift);
            $option.text(shift);
            $option.appendTo($select);
        }
        return
    }
    var selectShift = $selects[3];
    if (idx == 4) {
        shiftSelected = selectShift.value;
        for (idUser in window.arrayShiftsByIdUser) {
            // From == To
            if (idUser === selectIdFrom.value){
                continue
            }
            var days = window.arrayShiftsByIdUser[idUser][$(selectMonth).children(`option[value=${selectMonth.value}]`).text()];
            if (days) {
                var shifts = days[selectDay.value];
                if (shifts) {
                    var able = 1;
                    for (shift in shifts) {
                        if (!(shiftsPart1.includes(shift) ^ shiftsPart1.includes(shiftSelected))) {
                            able = 0;
                            break
                        }
                    }
                    if (!able){
                        continue
                    }
                }
            }
            $select.closest('.form-item').find(`.select-id-from option[value=${idUser}]`).clone().appendTo($select);
        }
    }
}

function changeSelects(event) {
    var $selects = $(event.target).closest('.form-item').find('select');
    currentIdx = $selects.index(event.target);
    enableSelect(currentIdx + 1, $selects);
}

function disableNoneOption(event) {
    $(event.target).find('option:first-child').attr('disabled', true);
}

function checkConfirmable() {
    var enable = 1;
    $selects = $('select');
    for (i = 0; i < $selects.length; i++) {
        if ($selects[i].value == 0 || $(this).attr('disabled') == 'disabled') {
            enable = 0;
            break;
        }
    }
    if (enable === 0) {
        $('#btn-confirm').addClass('disabled');
    } else {
        $('#btn-confirm').removeClass('disabled');
    }
}

function enableSelect(idx, $selects) {
    if (idx > $selects.length - 1) {
        return
    }
    var $select = $($selects[idx]);
    // Enable current select
    $select.removeAttr('disabled');
    // Enable and select default option
    $select.find('option:first-child').removeAttr('disabled');
    $selects[idx].value = 0;
    if (idx < $selects.length - 1) {
        // If more than 2 selects are ahead, disable beyond next of next selects and remove all options but default
        // console.log('Now at idx:', idx);
        $selects.slice(idx + 1).attr('disabled', true).map(function (index) {
            // console.log('Closing idx:', index+idx+1);
            this.value = 0;
            $(this).find('option:not(:first-child').remove();
        });
    }
    // Update current options
    updateOptions(idx, $selects);
    var optionsEnabled = $select.find('option').filter(function () {
        return $(this).attr('disabled') === undefined
    });
    if (optionsEnabled.length > 1) {
        // if multiple options available
    } else if (optionsEnabled.length === 1) {
        // if one option available        
        if (idx === $selects.length - 1) {
            // If current select is the last one:
        } else {
            // If current select is not the last one:
            enableSelect(idx + 1, $selects);
        }
    }
}

function addFormID($formItem) {
    $formItem.prepend($('<input type="hidden">').attr('name', String(window.formID)))
    $formItem.attr('id', window.formID);
    $formItem.find('label').each(function () {
        var newFor = $(this).attr('for') + '_' + String(window.formID);
        $(this).attr('for', newFor);
    });
    $formItem.find('select').each(function () {
        var newName = $(this).attr('name') + '_' + String(window.formID);
        $(this).attr('name', newName);
    });
}

function addEvents($formItem) {
    $formItem.find('select').on('change', changeSelects).on('change', disableNoneOption);
    $formItem.find('select.select-id-to').on('change', checkConfirmable);
    $formItem.find('.btn-delete').click(deleteItem);
}

function addFormItem(event) {
    if (window.numItem === 1) {
        $('.btn-delete').removeAttr('disabled').click(deleteItem);
    }
    window.numItem++;
    window.formID++;
    var newFormItem = defaultForm.clone()
    addFormID(newFormItem);
    addEvents(newFormItem);
    $('form').append(newFormItem);
    $('#btn-confirm').addClass('disabled');
}

function loadTransactions(event) {
    var modalTableBody = $('#modal-confirm .modal-dialog .modal-content .modal-body table tbody');
    $('form .form-item').each(function () {
        // Append tr>td*5
        modalTableBody.append('<tr><td></td><td></td><td></td><td></td><td></td></tr>');
        $(this).find('select').each(function (index) {
            // For each select
            var valueSelect = this.value;
            $(modalTableBody.find('tr:last-child td')[index]).append($(this).find('option').filter(function () {
                return $(this).attr('value') === valueSelect
            }).html());
        })
    })
}

function setFormIds(event){
    var valueString = new String();
    $('.form-item').each(function(){
        if (valueString.length) {
            valueString += ',';
        }
        valueString += $(this).attr('id');
    });
    $('#input-ids').attr('value', valueString);
}

function deleteItem(event) {
    if (window.numItem > 1) {
        console.log($(event.target).closest('.form-item'));
        $(event.target).closest('.form-item').remove();
        window.numItem--;
        if (window.numItem === 1) {
            $('.btn-delete').attr('disabled', true).off();
        }
        checkConfirmable();
    } else {
        return;
    }
}

// Preprocess arrayShiftsByIdUser
window.arrayShiftsByIdUser = prepArrayShiftsByIdUser(window.arrayShiftsByIdUser);
// Store default form
const defaultForm = $('.form-item').clone();
var numItem = 1;
var formID = 1;
$('.form-item .btn-delete').attr('disabled', true);
addFormID($('.form-item'));
// Actions for selecting an option: Enable/disable selects
$('.form-item select').on('change', changeSelects).on('change', disableNoneOption);
// Actions for selecting an option: Enable/disable confirm button
$('select.select-id-to').on('change', checkConfirmable);
// Actions for clicking delete icon
// $('.btn-delete').click(deleteItem);
// Actions for clicking add icon
$('#btn-add-item').click(addFormItem);
// Actions for clicking confirm icon: Load details to modal body
$('#btn-confirm').click(loadTransactions).click(setFormIds);
// Actions for closing modal: Empty table body
$('#modal-confirm').on('hidden.bs.modal', function (event) {
    $(event.target).find('.modal-body table tbody').empty();
});