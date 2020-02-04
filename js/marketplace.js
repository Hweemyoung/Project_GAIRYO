class MarketItemHandler {

    constructor(_memberObjectOfUser, _arrDateObjects, _arrPutRequestsByIdShift, _arrCallRequestsByDate, _arrDateObjectsPut, _arrDateObjectsCall, _arrShiftsByPart, _http_host, _constants) {
        this._constants = _constants;
        this._http_host = _http_host;
        this._memberObjectOfUser = _memberObjectOfUser;
        this._arrDateObjects = _arrDateObjects;
        this._arrPutRequestsByIdShift = _arrPutRequestsByIdShift;
        this._arrCallRequestsByDate = _arrCallRequestsByDate;
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
        // this._$modalBodyDisabledIsUsers = $('<tr><td colspan=5>これはあなたの出品したシフトです。<i class="text-primary far fa-surprise fa-lg"></i></td><tr>');


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

    isOverlap(_shift, _dateObject) {
        for (var _currentPart in this._arrShiftsByPart) {
            if (this._arrShiftsByPart[_currentPart].includes(_shift)) {
                break;
            }
        }
        var _arrShiftObjects = _dateObject.arrayShiftObjectsByShift[_shift];
        for (var idx in _arrShiftObjects) {
            if (_arrShiftObjects[idx].id_user === this._memberObjectOfUser.id_user || _arrShiftObjects[idx].shiftPart === _currentPart) {
                // Overlap found.
                return true;
            }
        }
        return false;
    }

    // isUsers(_shiftObjectRequested) {
    // return (_shiftObjectRequested.id_user === this._memberObjectOfUser.id_user);
    // }

    notEnoughLangs(_shiftObjectRequested, arrBalancesByPart) {
        var _cloned_arrBalancesByPart = JSON.parse(JSON.stringify(arrBalancesByPart)); // This will be used for comparing before and after for every lang.
        var arrBalances = _cloned_arrBalancesByPart[_shiftObjectRequested.shiftPart];
        for (var lang in arrBalances) {
            arrBalances[lang] -= Number(_shiftObjectRequested.memberObject[lang]);
            arrBalances[lang] += Number(this._memberObjectOfUser[lang]);
            console.log('lang:', lang, 'before:', arrBalancesByPart[_shiftObjectRequested.shiftPart][lang], 'after:', arrBalances[lang])
            if (arrBalances[lang] < 0 && (arrBalances[lang] < arrBalancesByPart[_shiftObjectRequested.shiftPart][lang])) {
                // Cannot take this shift.
                // Skip rest of langs 
                return true;
            }
        }
        return false;
    }

    setArrModalBodiesMode(_mode, _arrDateObjectsMode){
        this._objTbodies[_mode] = {};
        for (var _date_shift in _arrDateObjectsMode) {
            console.log('Now date_shift:', _date_shift);
            var _dateObject = this._arrDateObjects[_date_shift];
            var _dateObjectMode = _arrDateObjectsMode[_date_shift];
            this._objTbodies[_mode][_date_shift] = {};
            console.log('date_shift set:', _date_shift, this._objTbodies[_mode][_date_shift]);
            for (var _shift in _dateObjectMode.arrayShiftObjectsByShift) {
                console.log('Shift:', _shift);
                // Part overlap check
                console.log('Overlap?', this.isOverlap(_shift, _dateObject));
                if (this.isOverlap(_shift, _dateObject)) {
                    // Go to next shift
                    console.log('Overlapped!')
                    this._objTbodies[_mode][_date_shift][_shift] = { 'ShiftObject': null, '_$tr': this._$modalBodyDisabledOverlap };
                    continue;
                }

                var _arrShiftObjectsMode = _dateObjectMode.arrayShiftObjectsByShift[_shift];
                for (var idx in _arrShiftObjectsMode) {
                    console.log('New shiftobject');
                    var _shiftObjectMode = _arrShiftObjectsMode[idx];
                    // If this is user's, skip this ShiftObject. <- This has already been filtered by overlap stage.
                    // var _isUsers = false;
                    // console.log('Is User\'s?', this.isUsers(_shiftObjectMode));
                    // if (this.isUsers(_shiftObjectMode)) {
                    // _isUsers = true;
                    // Go to next shiftobject
                    // continue;
                    // }

                    // Language check
                    var _notEnoughLangs = false;
                    console.log('Not enough langs?', this.notEnoughLangs(_shiftObjectMode, _dateObject.arrBalancesByPart));
                    if (this.notEnoughLangs(_shiftObjectMode, _dateObject.arrBalancesByPart)) {
                        _notEnoughLangs = true;
                        // Go to next shiftobject
                        continue;
                    }
                }
                if (_notEnoughLangs) {
                    console.log('Not enough langs!');
                    this._objTbodies[_mode][_date_shift][_shift] = { 'ShiftObject': null, '_$tr': this._$modalBodyDisabledLang };
                    continue;
                }

                // ShiftObject found!
                var _$tr = $('<tr></tr>');
                var _date = new Date(_date_shift);
                // console.log(_date);
                var _month = `${_date.getFullYear()} ${this._constants.months[_date.getMonth()]}`;
                var _day = `${_date.getDate()} (${this._constants.weekdays[_date.getDay()]})`;
                if (_mode === 'put'){
                    var _arrTds = [_shiftObjectMode.memberObject.nickname, _month, _day, _shift, 'YOU'];
                } else if (_mode === 'call'){
                    var _arrTds = [_shiftObjectMode.memberObject.nickname, _month, _day, _shift, 'YOU'];
                }
                // console.log(_arrTds);
                for (var i in _arrTds) {
                    // $(`<td>${_arrTds[i]}</td>`).appendTo(_$tr);
                    _$tr.append($(`<td>${_arrTds[i]}</td>`));
                }
                console.log(_$tr);
                this._objTbodies[_mode][_date_shift][_shift] = { 'ShiftObject': _shiftObjectMode, '_$tr': _$tr };
                // Search for next shift.
                break;
            }
        }
    }

    setArrModalBodies() {
        this.setArrModalBodiesMode('put', this._arrDateObjectsPut);
        this.setArrModalBodiesMode('call', this._arrDateObjectsCall);
    }

    buildModal(event) {
        //$.proxy
        console.log('event!');
        // console.log(this);
        var _handler = this;
        var _mode = $(event.target).closest('.btn-group').attr('mode');
        var _date_shift = $(event.target).closest('.div-timeline-section').attr('id');
        var _shift = $(event.target).text();
        console.log(_date_shift, _shift);
        console.log(this._objTbodies[_mode][_date_shift][_shift]._$tr);
        $('#tbody-modal').append(this._objTbodies[_mode][_date_shift][_shift]._$tr);

        if (this._objTbodies[_mode][_date_shift][_shift].ShiftObject === null) {
            $('#form .btn[type="submit"]').attr('disabled', true);
        } else {
            console.log('exists!');
            $('#input-mode').attr('value', _mode);
            $('#form .btn[type="submit"]').attr('action', `${this._http_host}/process/upload_market_item.php`);
            if (_mode === 'put'){
                $('#input-id-request').attr('value', this._arrPutRequestsByIdShift[this._objTbodies[_date_shift][_shift].ShiftObject.id_shift].id_request);
            } else if (_mode === 'call'){
                for(var key in this._arrCallRequestsByDate[_date_shift][_shift]){
                    if (this._arrCallRequestsByDate[_date_shift][_shift][key].id_shift === this._objTbodies[_mode][_date_shift][_shift].ShiftObject.id_shift){

                    }
                }
                $('#input-id-request').attr('value', this._arrCallRequestsByDate[_date_shift][_shift][this._objTbodies[_date_shift][_shift].ShiftObject.id_shift].id_request);
            }

        }
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

