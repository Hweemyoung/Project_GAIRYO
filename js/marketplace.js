class MarketItemHandler {

    constructor(_memberObjectOfUser, _arrDateObjects, _arrIdRequestsByIdShift, _arrDateObjectsRequested, _constants) {
        this._constants = _constants;
        this._memberObjectOfUser = _memberObjectOfUser;
        this._arrDateObjects = _arrDateObjects;
        this._arrIdRequestsByIdShift = _arrIdRequestsByIdShift;
        this._arrDateObjectsRequested = _arrDateObjectsRequested;
        this.init();
    }

    init() {
        this._objTbodies = {};  // this._objTbodies[_date][_shift] = $('.modal-body')
        this._$modalBodyDisabled = $('<tr><td colspan=5>一部の必要言語が足りなくなるため、応募できません。</td><tr>');
        this.addEvents();
        this.setArrModalBodies();
    }

    addEvents() {
        $('.div-timeline-section .btn-group .btn').click($.proxy(this.buildModal, this));
        $('#modal').on('hidden.bs.modal', function (event) {
            $('#tbody-modal').empty();
            $('#form .btn[type="submit"]').removeClass('disabled');
            $('#form input').removeAttr('value');
        });
    }

    setArrModalBodies() {
        // Language check
        for (var _date_shift in this._arrDateObjectsRequested) {
            var _dateObject = this._arrDateObjects[_date_shift];
            var _dateObjectRequested = this._arrDateObjectsRequested[_date_shift];
            this._objTbodies[_date_shift] = {};

            for (var _shift in _dateObjectRequested.arrayShiftObjectsByShift) {
                var _arrShiftObjectsRequested = _dateObjectRequested.arrayShiftObjectsByShift[_shift];

                for (var idx in _arrShiftObjectsRequested) {
                    var _cloned_arrBalancesByPart = JSON.parse(JSON.stringify(_dateObject.arrBalancesByPart)); // This will be used for comparing before and after for every lang.
                    var _shiftObjectRequested = _arrShiftObjectsRequested[idx];
                    var arrBalances = _cloned_arrBalancesByPart[_shiftObjectRequested.shiftPart];
                    var _acceptable = true;
                    for (var lang in arrBalances) {
                        arrBalances[lang] -= Number(_shiftObjectRequested.memberObject[lang]);
                        arrBalances[lang] += Number(this._memberObjectOfUser[lang]);
                        if (arrBalances[lang] < 0 && (arrBalances[lang] < _dateObject.arrBalancesByPart)) {
                            // Cannot take this shift.
                            var _acceptable = false;
                            // Skip rest of langs 
                            break;
                        }
                    }
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
                        // Search for next shift.
                        break;
                    }
                }

                // No shiftObject found for this date+shift.
                this._objTbodies[_date_shift][_shift] = { 'ShiftObject': null, '_$tr': this._$modalBodyDisabled };
            }
        }
    }

    buildModal(event) {
        //$.proxy
        var _handler = this;
        var _date_shift = $(event.target).closest('.div-timeline-section').attr('id');
        var _shift = $(event.target).text();
        console.log(_date_shift, _shift);
        this._objTbodies[_date_shift][_shift]._$tr.appendTo('#tbody-modal');
        if (this._objTbodies[_date_shift][_shift].ShiftObject) {
            $('#form input').attr('value', this._arrIdRequestsByIdShift[this._objTbodies[_date_shift][_shift].ShiftObject.id_shift]);
        } else {
            $('#form .btn[type="submit"]').addClass('disabled');
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

