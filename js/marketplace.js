class MarketItemHandler {

    constructor(_memberObjectOfUser, _arrDateObjects, _arrIdPutRequestsByIdShift, _arrIdCallRequestsByDate, _arrDateObjectsPut, _arrDateObjectsCall, _arrShiftsByPart, _constants) {
        this._constants = _constants;
        this._memberObjectOfUser = _memberObjectOfUser;
        this._arrDateObjects = _arrDateObjects;
        this._arrIdPutRequestsByIdShift = _arrIdPutRequestsByIdShift;
        this._arrIdCallRequestsByDate = _arrIdCallRequestsByDate;
        this._arrDateObjectsPut = _arrDateObjectsPut;
        this._arrDateObjectsCall = _arrDateObjectsCall;
        this._arrShiftsByPart = _arrShiftsByPart;
        console.log(this._arrShiftsByPart);
        this.init();
    }

    init() {
        this._objTbodies = {};  // this._objTbodies[_date][_shift] = $('.modal-body')
        this._$modalBodyDisabledLang = $('<tr><td colspan=5>一部の必要言語が足りなくなるため、購入できません。<i class="text-primary far fa-sad-tear fa-lg"></i></td><tr>');
        this._$modalBodyDisabledOverlap = $('<tr><td colspan=5>いま持っているシフトとかぶるため、購入できません。<i class="text-primary far fa-surprise fa-lg"></i></td><tr>');


        this.addEvents();
        this.setArrModalBodies();
    }

    addEvents() {
        $('.div-timeline-section .btn-group .btn').click($.proxy(this.buildModal, this));
        $('#modal').on('hidden.bs.modal', function (event) {
            $('#tbody-modal').empty();
            $('#form .btn[type="submit"]').attr('disabled', false);
            $('#input-id-request').removeAttr('value');
            $('#input-mode').removeAttr('value');
        });
    }

    setArrModalBodies() {
        // Put
        for (var _date_shift in this._arrDateObjectsPut) {
            var _dateObject = this._arrDateObjects[_date_shift];
            var _dateObjectPut = this._arrDateObjectsPut[_date_shift];
            this._objTbodies[_date_shift] = {};

            for (var _shift in _dateObjectPut.arrayShiftObjectsByShift) {
                // Part overlap check
                for (var _currentPart in this._arrShiftsByPart) {
                    if (this._arrShiftsByPart[_currentPart].includes(_shift)) {
                        break;
                    }
                }

                var _arrShiftObjectsPut = _dateObjectPut.arrayShiftObjectsByShift[_shift];
                for (var idx in _arrShiftObjectsPut) {
                    var _shiftObjectRequested = _arrShiftObjectsPut[idx];
                    // If this is user's, skip this ShiftObject.
                    if (_shiftObjectRequested.id_user === this._memberObjectOfUser.id_user) {
                        continue;
                    }

                    var _found = false;
                    var _cloned_arrBalancesByPart = JSON.parse(JSON.stringify(_dateObject.arrBalancesByPart)); // This will be used for comparing before and after for every lang.

                    // Language check
                    var arrBalances = _cloned_arrBalancesByPart[_shiftObjectRequested.shiftPart];
                    var _acceptable = true;
                    for (var lang in arrBalances) {
                        arrBalances[lang] -= Number(_shiftObjectRequested.memberObject[lang]);
                        arrBalances[lang] += Number(this._memberObjectOfUser[lang]);
                        console.log('lang:', lang, 'before:', _dateObject.arrBalancesByPart[_shiftObjectRequested.shiftPart][lang], 'after:', arrBalances[lang])
                        if (arrBalances[lang] < 0 && (arrBalances[lang] < _dateObject.arrBalancesByPart[_shiftObjectRequested.shiftPart][lang])) {
                            // Cannot take this shift.
                            var _acceptable = false;
                            // Skip rest of langs 
                            break;
                        }
                    }
                    console.log('_acceptable:', _acceptable);
                    if (_acceptable) {
                        var _$tr = $('<tr></tr>');
                        var _date = new Date(_date_shift);
                        // console.log(_date);
                        var _month = `${_date.getFullYear()} ${this._constants.months[_date.getMonth()]}`;
                        var _day = `${_date.getDate()} (${this._constants.weekdays[_date.getDay()]})`;
                        var _arrTds = [_shiftObjectRequested.memberObject.nickname, _month, _day, _shift, 'YOU'];
                        // console.log(_arrTds);
                        for (var i in _arrTds) {
                            // $(`<td>${_arrTds[i]}</td>`).appendTo(_$tr);
                            _$tr.append($(`<td>${_arrTds[i]}</td>`));
                        }
                        console.log(_$tr);
                        this._objTbodies[_date_shift][_shift] = { 'ShiftObject': _shiftObjectRequested, '_$tr': _$tr };
                        // Found shiftobject for this date and shift.
                        _found = true;
                        // Search for next shift.
                        break;
                    }
                }
                if (!_found) {
                    console.log('_$modalBodyDisabledLang:', this._$modalBodyDisabledLang);
                    // No shiftObject found for this date+shift.
                    this._objTbodies[_date_shift][_shift] = { 'ShiftObject': null, '_$tr': this._$modalBodyDisabledLang };
                }
            }
        }
    }

    buildModal(event) {
        //$.proxy
        console.log('event!');
        // console.log(this);
        var _handler = this;
        var _date_shift = $(event.target).closest('.div-timeline-section').attr('id');
        var _shift = $(event.target).text();
        console.log(_date_shift, _shift);
        console.log(this._objTbodies[_date_shift][_shift]._$tr);
        $('#tbody-modal').append(this._objTbodies[_date_shift][_shift]._$tr);

        if (this._objTbodies[_date_shift][_shift].ShiftObject === null) {
            $('#form .btn[type="submit"]').attr('disabled', true);
        } else {
            console.log('exists!');
            $('#input-id-request').attr('value', this._arrIdPutRequestsByIdShift[this._objTbodies[_date_shift][_shift].ShiftObject.id_shift].id_request);
            $('#input-mode').attr('value', $(event.target).closest('.btn-group').attr('mode'));
        }
    }

    checkConfirmable(_shiftObject) {

    }
}

class TimelineSection {
    _$;
    divRow = $('<div class="row align-items-center how-it-works d-flex"></div>');
    divColDate = $('<div class="col-2 text-center bottom d-inline-flex justify-content-center align-items-center"></div>');
    divCircle = $('<div class="circle"></div>');
    divColShifts = $('<div class="col-6"></div>');

    constructor(_dateObject, _position) {
        this._position = _position;
        this._dateObject = _dateObject;
        this._timelinePath = new TimelinePath(_position);
        this.appendTimelineSection(_dateObject);
    }

    appendTimelineSection(_dateObject) {
        var _date = new Date(_dateObject.date);
        this._$ = $('<div class="row align-items-center how-it-works d-flex"></div>');
        // Col Date
        this.divColDate.append(this.divCircle.append($('<p></p>').html(`<p>${_date.getMonth()} ${_date.getDate()}<br>${_date.getDay()}<p>`))).appendTo(this._$);
        this.divRow.append(this.divColDate);
        // Col Shifts
        // Shift Put
        var _$btnGroup = $('<div class="btn-group"></div>');
        for (var _shift in _dateObject.arrayShiftObjectsByShift) {
            $(`<button class="btn" type="button">${_shift}</button>`).appendTo(_$btnGroup);
        }
        this.divColShifts.append($('<div class="row"></div>').append($('<div class="col-12 col-put">').append(_$btnGroup)));
        // Shift Call: Forget call at this point :)
        // Build up _$
        this.divRow.append(this.ColShifts);
    }
}

class TimelinePath {
    _$;
    constructor(_position) {
        this.setTimeline(_position);
    }

    setTimeline(_position) {
        switch (_position) {
            case 'left':
                var _counter = 'right';
            case 'right':
                var _counter = 'left';
        }
        this._$ = $('<div class="row timeline"></div>');
        $(`<div class="col-2"><div class="corner top-${_counter}"></div></div>`).appendTo(this._$);
        $('<div class="col-8"><hr /></div>').appendTo(this._$);
        $(`<div class="col-2"><div class="corner left-${_position}"></div></div>`).appendTo(this._$);
    }
}

