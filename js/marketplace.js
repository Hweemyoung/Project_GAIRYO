class MarketItemHandler {
    _$modal = $('#modal');

    constructor(_memberObjectOfUser, _arrDateObjects, _arrIdRequestsByIdShift, _arrDateObjectsRequested, _constants) {
        this._constants = _constants;
        this._memberObjectOfUser = _memberObjectOfUser;
        this._arrDateObjects = _arrDateObjects;
        this._arrIdRequestsByIdShift = _arrIdRequestsByIdShift;
        this._arrDateObjectsRequested = _arrDateObjectsRequested;
        this._objModalBodies = {};  // this._objModalBodies[_date][_shift] = $('.modal-body')
        this.setArrModalBodiesAcceptable();
        this.disableBtns();
    }

    disableBtns(){
        for(_date in this._objModalBodies){
            for(_shift in _objModalBodies[_date]){

            }
        }
    }

    setArrModalBodiesAcceptable(){
        // Language check
        for(var _date_shift in this._arrDateObjects){
            var _dateObject = this._arrDateObjects[_date_shift];
            var _dateObjectRequested = this._arrDateObjectsRequested[_date_shift];
            this._objModalBodies[_date_shift] = {};

            for(var _shift in _dateObjectRequested.arrayShiftObjectsByShift){
                var _arrShiftObjectsRequested = this._dateObjectRequested.arrayShiftObjectsByShift[_shift];

                for(var idx in _arrShiftObjectsRequested){
                    var _cloned_arrBalancesByPart = JSON.parse(JSON.stringify(_dateObject.arrBalancesByPart)); // This will be used for comparing before and after for every lang.
                    var _shiftObjectRequested = _arrShiftObjectsRequested[idx];
                    var arrBalances = _cloned_arrBalancesByPart[_shiftObjectRequested.shiftPart];
                    var _acceptable = true;
                    for(var lang in arrBalances){
                        arrBalances[lang] -= Number(_shiftObject.memberObject[lang]);
                        arrBalances[lang] += Number(this._memberObjectOfUser[lang]);
                        if (arrBalances[lang] < 0 && (arrBalances[lang] < _dateObject.arrBalancesByPart)){
                            // Cannot take this shift.
                            var _acceptable = false;
                            // Skip rest of langs 
                            break;
                        }
                    }
                    if (_acceptable){
                        var _$tr = $('<tr></tr>');
                        var _date = new Date(_date_shift);
                        var _month = `${_date.getFullYear()} ${this._constants.months[_date.getMonth()]}`;
                        var _day = `${_date.getDate()} (${this._constants.weekdays[_date.getDay()]})`;
                        var _arrTds = [_shiftObjectRequested.memberObject.nickname, _month, _day, _shift, 'YOU'];
                        for(var i in _arrTds){
                            $(`<td>${_arrTds[i]}</td>`).appendTo(_$tr);
                        }
                        _$tr.appendTo($('#tbody-modal tr'));
                        this._objModalBodies[_date_shift][_shift] = _$tr;
                        // Found shiftobject for this date and shift.
                        // Search for next shift.
                        break;
                    }
                }
            }
        }
    }

    buildModal(event) {
        // Under $.Proxy
        // event.target: .btn
        var _handler = this;
        var _$modalBody = this._$modal.find('.modal-body');
        var _date = $(event.target).closest('.div-timeline-section').attr('id');
        var _shift = $(event.target).text;
        var _dateObject = this._arrDateObjects[_date];
        var _cloned_arrBalancesByPart = JSON.parse(JSON.stringify(_dateObject.arrBalancesByPart));
        // Check confirmable : Language
        // Assume transaction has been executed.


    }

    checkConfirmable(_shiftObject){
        
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

