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
    var newFormItem = defaultForm.clone();
    $('form').append(newFormItem).append('<hr>');
    newFormItem.find('select').on('change', changeSelects).on('change', disableNoneOption);
}

const defaultForm = $('.form-item').clone();

$('.form-item select').on('change', changeSelects).on('change', disableNoneOption);
$('#btn-add-item').click(addFormItem);