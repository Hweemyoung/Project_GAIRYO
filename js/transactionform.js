function updateOptions($select) {
    return
}

function changeSelects(event) {
    var $selects = $(event.target).closest('.form-item').find('select');
    currentIdx = $selects.index(event.target);
    enableSelect(currentIdx + 1, $selects);
}

function disableNoneOption(event){
    $(event.target).find('option:first-child').attr('disabled', 'disabled');
}

function checkConfirmable(){
    var enable = 1;
    $('select[name="id_to"]').map(function(){
        if ($(this).attr('value') === 0 || $(this).attr('disabled') == 'disabled'){
            enable = enable * 0;
        } else {
            enable = enable * 1;
        }
    });
    if (enable === 0){
        $('#btn-confirm').addClass('disabled');
    } else {
        $('#btn-confirm').removeClass('disabled');
    }
}

function enableSelect(idx, $selects) {
    if(idx > $selects.length - 1){
        return
    }
    var $select = $($selects[idx]);
    // Enable current select
    $select.removeAttr('disabled');
    // Enable and select default option
    $select.find('option:first-child').removeAttr('disabled');
    $selects[idx].value = 0;
    if (idx < $selects.length - 1){
        // if more than 2 selects are ahead, disable beyond next of next selects
        // console.log('Now at idx:', idx);
        $selects.slice(idx + 1).attr('disabled', 'disabled').map(function(index){
            // console.log('Closing idx:', index+idx+1);
            this.value = 0;
        });
    }
    // Update current options
    updateOptions($select);
    var optionsEnabled = $select.find('option').filter(function(){
        return $(this).attr('disabled') === undefined
    });
    if (optionsEnabled.length > 1) {
        // if multiple options available
    } else if (optionsEnabled.length === 1) {
        // if one option available        
        if (idx === $selects.length - 1) {
            // If current select is the last one:
        } else  {
            // If current select is not the last one:
            enableSelect(idx + 1, $selects);
        }
    }
}

function addFormItem(event){
    numItem++;
    var newFormItem = defaultForm.clone().attr('id', 'form-item'+String(numItem));
    $('form').append(newFormItem);
    newFormItem.find('select').on('change', changeSelects).on('change', disableNoneOption);
    newFormItem.find('select[name="id_to"]').on('change', checkConfirmable);
    newFormItem.find('.btn-delete').click(deleteItem);
    $('#btn-confirm').addClass('disabled');
}

function loadTransactions(event){
    var modalTableBody = $('#modal-confirm .modal-dialog .modal-content .modal-body table tbody');
    $('form .form-item').each(function(){
        // Append tr>td*5
        modalTableBody.append('<tr><td></td><td></td><td></td><td></td><td></td></tr>');
        $(this).find('select').each(function(index){
            // For each select
            var valueSelect = this.value;
            $(modalTableBody.find('tr:last-child td')[index]).append($(this).find('option').filter(function(){
                return $(this).attr('value') === valueSelect
            }).html());
        })
    })
}

function deleteItem(event){
    if ($('.form-item').length > 1){
        console.log($(event.target).closest('.form-item'));
        $(event.target).closest('.form-item').remove();
    }
}

const defaultForm = $('.form-item').clone();
var numItem = 1;

// Actions for selecting an option: Enable/disable selects
$('.form-item select').on('change', changeSelects).on('change', disableNoneOption);
// Actions for selecting an option: Enable/disable confirm button
$('select[name="id_to"]').on('change', checkConfirmable);
// Actions for clicking delete icon
$('.btn-delete').click(deleteItem);
// Actions for clicking add icon
$('#btn-add-item').click(addFormItem);
// Actions for clicking confirm icon: Load details to modal body
$('#btn-confirm').click(loadTransactions);
// Actions for closing modal: Empty table body
$('#modal-confirm').on('hidden.bs.modal', function(event){
    $(event.target).find('.modal-body table tbody').empty();
});