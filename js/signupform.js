class SignupFormHandler {
    constructor() {
        this._$formGroups = $('.form-group');
        this._$formGroupsRequired = this._$formGroups.filter(function () {
            return $(this).hasClass('form-group-required');
        });
        this._$btnSubmit = $('#btn-submit');
        this.init();
    }

    init() {
        this.checkConfirmable();
        this._$formGroupsRequired.find('input').on('input', $.proxy(this.checkConfirmable, this));
    }

    checkConfirmable() {
        var _confirmable = true;
        this._$btnSubmit.addClass('disabled');
        $.each(this._$formGroupsRequired.find('input'), function () {
            if (this.value === '') {
                _confirmable = false
                return false
            }
        });
        if (_confirmable){
            this._$btnSubmit.removeClass('disabled');
        }
        console.log('tracking!');
    }
}
const signup_form_handler = new SignupFormHandler();