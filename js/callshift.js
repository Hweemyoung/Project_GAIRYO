class ShiftCallHandler {
    constructor(_constants) {
        this._constants = _constants;
        this.addEvents();
    }

    addEvents() {
        $('#section-call-list .btn-group a.btn').click($.proxy(this.buildModal, this));
        $('#modal').on('hidden.bs.modal', function (event) {
            $('#tbody-modal').empty();
            $('#form .btn[type="submit"]').attr('disabled', false);
            $('#input-shift').removeAttr('value');
            $('#input-date-shift').removeAttr('value');
        });
    }

    buildModal(event) {
        // Get shift
        _shift = $(event.target).text();
        // Get date_shift
        _date_components = $(event.target).closest('li').children('span').text().split(' '); // 2020 Jan 3 -> ['2020', 'Jan', '3']
        _dateObj = new Date(`${_date_components[1]} ${(_date_components[2].padStart(2, '0'))} ${_date_components[0]}`);
        _date = String(_dateObj.getDate()).padStart(2, '0'); // '3'->'03'
        _month = String(_dateObj.getMonth() + 1).padStart(2, '0'); // 0->1->'1'->'01'
        _year = String(_dateObj.getFullYear); // '2020'
        _date_shift = _year + '-' + _month + '-' + _date; // '2020-01-03'
        $('#input-shift').attr('value', _shift);
        $('#input-date-shift').attr('value', _date_shift);
        $('#tbody-modal').append(`<tr><th>${_date_components[0] + ' ' + _date_components[1]}</th><th class="${this._constants.getTextColorByDay(_dateObj.getDay())}">${_date_components[2]} (${this._constants.weekdays[_dateObj.getDay()]})</th><th>${_shift}</th></tr>`);
    }
}

$shift_call_handler = new ShiftCallHandler();