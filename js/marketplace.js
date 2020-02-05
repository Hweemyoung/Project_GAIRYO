class MarketItemHandler {

    constructor(_id_user, _arrMemberObjectsByUser, _arrDateObjects, _arrPutRequestsByIdShift, _arrCallRequestsByDate, _arrDateObjectsPut, _arrDateObjectsCall, _arrShiftsByPart, _http_host, _constants) {
        this._constants = _constants;
        this._http_host = _http_host;
        this._arrMemberObjectsByUser = _arrMemberObjectsByUser;
        this._id_user = _id_user;
        this._memberObjectOfUser = _arrMemberObjectsByUser[_id_user];
        this._arrDateObjects = _arrDateObjects;
        this._arrPutRequestsByIdShift = _arrPutRequestsByIdShift;
        this._arrCallRequestsByDate = _arrCallRequestsByDate;
        this._arrDateObjectsPut = _arrDateObjectsPut; // Includes user's all shiftobjects for put date_shifts
        this._arrDateObjectsCall = _arrDateObjectsCall; // Includes user's shiftobjects for call date_shifts
        this._arrShiftsByPart = _arrShiftsByPart;
        console.log(this._arrShiftsByPart);
        this.init();
    }

    init() {
        this._objTbodies = {};  // this._objTbodies[_date][_shift] = $('.modal-body')
        // Put
        this._$modalBodyDisabledOverlap = $('<tr><td colspan=5>いま持っているシフトとかぶるため、購入できません。<i class="text-primary far fa-surprise fa-lg"></i></td><tr>');
        // Call
        this._$modalBodyShiftNotExist = $('<tr><td colspan=5>これに対応するシフトを持っていません。<i class="text-primary far fa-surprise fa-lg"></i></td><tr>');
        // Common
        this._$modalBodyDisabledLang = $('<tr><td colspan=5>一部の必要言語が足りなくなるため、購入できません。<i class="text-primary far fa-sad-tear fa-lg"></i></td><tr>');

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

    isOverlap(_shift, _dateObject) { // _dateObject includes put shifts by all users.
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

    notEnoughLangs(_shiftObjectRequested, arrBalancesByPart, _memberObjectOfIdTo) {
        var _cloned_arrBalancesByPart = JSON.parse(JSON.stringify(arrBalancesByPart)); // This will be used for comparing before and after for every lang.
        var arrBalances = _cloned_arrBalancesByPart[_shiftObjectRequested.shiftPart];
        for (var lang in arrBalances) {
            console.log('Giver:', _shiftObjectRequested.id_user);
            console.log('Giver can speak', lang, _shiftObjectRequested.memberObject[lang]);
            arrBalances[lang] -= Number(_shiftObjectRequested.memberObject[lang]);
            console.log('Taker user can speak', lang, _memberObjectOfIdTo[lang]);
            arrBalances[lang] += Number(_memberObjectOfIdTo[lang]);
            console.log('lang:', lang, 'before:', arrBalancesByPart[_shiftObjectRequested.shiftPart][lang], 'after:', arrBalances[lang])
            if (arrBalances[lang] < 0 && (arrBalances[lang] < arrBalancesByPart[_shiftObjectRequested.shiftPart][lang])) {
                // Cannot take this shift.
                // Skip rest of langs 
                return true;
            }
        }
        return false;
    }

    setArrModalBodiesPut() {
        var _mode = 'put';
        this._objTbodies[_mode] = {};
        for (var _date_shift in this._arrDateObjectsPut) {
            console.log('Now date_shift:', _date_shift);
            var _dateObject = this._arrDateObjects[_date_shift];
            var _dateObjectPut = this._arrDateObjectsPut[_date_shift];
            this._objTbodies[_mode][_date_shift] = {};
            console.log('date_shift set:', _date_shift, this._objTbodies[_mode][_date_shift]);
            for (var _shift in _dateObjectPut.arrayShiftObjectsByShift) {
                console.log('Shift:', _shift);
                // Part overlap check
                console.log('Overlap?', this.isOverlap(_shift, _dateObject));
                if (this.isOverlap(_shift, _dateObject)) {
                    // Go to next shift
                    console.log('Overlapped!')
                    this._objTbodies[_mode][_date_shift][_shift] = { 'id_shift': null, 'id_request': null, 'ShiftObject': null, '_$tr': this._$modalBodyDisabledOverlap };
                    continue;
                }

                var _arrShiftObjectsPut = _dateObjectPut.arrayShiftObjectsByShift[_shift];
                for (var idx in _arrShiftObjectsPut) {
                    console.log('New shiftobject');
                    var _shiftObjectPut = _arrShiftObjectsPut[idx];
                    // If this is user's, skip this ShiftObject. <- This has already been filtered by overlap stage.
                    // var _isUsers = false;
                    // console.log('Is User\'s?', this.isUsers(_shiftObjectPut));
                    // if (this.isUsers(_shiftObjectPut)) {
                    // _isUsers = true;
                    // Go to next shiftobject
                    // continue;
                    // }

                    // Language check
                    var _notEnoughLangs = false;
                    // console.log('Not enough langs?', this.notEnoughLangs(_shiftObjectPut, _dateObject.arrBalancesByPart));
                    if (this.notEnoughLangs(_shiftObjectPut, _dateObject.arrBalancesByPart, this._memberObjectOfUser)) {
                        _notEnoughLangs = true;
                        // Go to next shiftobject
                        continue;
                    }
                    // If found
                    break;
                }
                if (_notEnoughLangs) {
                    console.log('Not enough langs!');
                    this._objTbodies[_mode][_date_shift][_shift] = { 'id_shift': null, 'id_request': null, 'ShiftObject': null, '_$tr': this._$modalBodyDisabledLang };
                    continue;
                }

                // ShiftObject found!
                var _$tr = $('<tr></tr>');
                var _date = new Date(_date_shift);
                // console.log(_date);
                var _month = `${_date.getFullYear()} ${this._constants.months[_date.getMonth()]}`;
                var _day = `${_date.getDate()} (${this._constants.weekdays[_date.getDay()]})`;
                var _arrTds = [_shiftObjectPut.memberObject.nickname, _month, _day, _shift, 'YOU'];
                // console.log(_arrTds);
                for (var i in _arrTds) {
                    // $(`<td>${_arrTds[i]}</td>`).appendTo(_$tr);
                    _$tr.append($(`<td>${_arrTds[i]}</td>`));
                }
                console.log(_$tr);
                console.log(_shiftObjectPut.id_shift);
                this._objTbodies[_mode][_date_shift][_shift] = { 'id_shift': _shiftObjectPut.id_shift, 'id_request': this._arrPutRequestsByIdShift[_shiftObjectPut.id_shift].id_request, 'ShiftObject': _shiftObjectPut, '_$tr': _$tr };
                // Search for next shift.
                break;
            }
            console.log(_mode, _date_shift, this._objTbodies[_mode][_date_shift]);
        }
    }

    setArrModalBodiesCall() {
        var _mode = 'call';
        this._objTbodies[_mode] = {};
        for (var _date_shift in this._arrCallRequestsByDate) {
            console.log('Now date_shift:', _date_shift);
            var _dateObject = this._arrDateObjects[_date_shift];
            var _arrCallRequestsByShift = this._arrCallRequestsByDate[_date_shift];
            this._objTbodies[_mode][_date_shift] = {};
            if (this._arrDateObjectsCall[_date_shift] === undefined) { // If user doesn't have shifts for this date
                console.log("user doesn't have shifts for this date");
                for (var _shift in _arrCallRequestsByShift) {
                    this._objTbodies[_mode][_date_shift][_shift] = { 'id_shift': null, 'id_request': null, 'ShiftObject': null, '_$tr': this._$modalBodyShiftNotExist };
                }
                // Search for next date_shift
                continue;
            }
            var _shiftObjectsByShift = this._arrDateObjectsCall[_date_shift].arrayShiftObjectsByShift;
            for (var _shift in _arrCallRequestsByShift) {
                if (_shiftObjectsByShift[_shift] === undefined) { // If user doesn't have this shift
                    this._objTbodies[_mode][_date_shift][_shift] = { 'id_shift': null, 'id_request': null, 'ShiftObject': null, '_$tr': this._$modalBodyShiftNotExist };
                    continue;
                }
                var _arrCallRequests = _arrCallRequestsByShift[_shift];
                for (var _idx_request in _arrCallRequests) {
                    var _callRequest = _arrCallRequests[_idx_request];

                    // Lang check
                    var _arrShiftObjectsCall = _shiftObjectsByShift[_shift]; // Only one shiftobject exists for shift of the user.
                    for (var idx in _arrShiftObjectsCall) {
                        console.log('New shiftobject');
                        var _shiftObjectCall = _arrShiftObjectsCall[idx];
                        var _notEnoughLangs = false;
                        // console.log('Not enough langs?', this.notEnoughLangs(_shiftObjectMode, _dateObject.arrBalancesByPart));
                        if (this.notEnoughLangs(_shiftObjectCall, _dateObject.arrBalancesByPart, this._arrMemberObjectsByUser[_callRequest.id_to])) {
                            _notEnoughLangs = true;
                            // Go to next shiftobject
                            continue;
                        }
                        // If found
                        break;
                    }
                    if (!_notEnoughLangs) {
                        // ShiftObject found!
                        var _$tr = $('<tr></tr>');
                        var _date = new Date(_date_shift);
                        // console.log(_date);
                        var _month = `${_date.getFullYear()} ${this._constants.months[_date.getMonth()]}`;
                        var _day = `${_date.getDate()} (${this._constants.weekdays[_date.getDay()]})`;
                        var _arrTds = ['YOU', _month, _day, _shift, this._arrMemberObjectsByUser[_callRequest.id_to].nickname];
                        // console.log(_arrTds);
                        for (var i in _arrTds) {
                            // $(`<td>${_arrTds[i]}</td>`).appendTo(_$tr);
                            _$tr.append($(`<td>${_arrTds[i]}</td>`));
                        }
                        console.log(_$tr);
                        this._objTbodies[_mode][_date_shift][_shift] = { 'id_shift': _shiftObjectCall.id_shift, 'id_request': _callRequest.id_request, 'ShiftObject': _shiftObjectCall, '_$tr': _$tr };
                        // Search for next shift.
                        break;
                    } else {
                        console.log('Not enough langs for this request. Try next one.');
                    }
                }
                if (this._objTbodies[_mode][_date_shift][_shift] === undefined) {
                    console.log('User doesn\'t have valid shift for any requests for this shift on this date');
                    this._objTbodies[_mode][_date_shift][_shift] = { 'id_shift': null, 'id_request': null, 'ShiftObject': null, '_$tr': this._$modalBodyDisabledLang };
                }
            }
        }
    }

    setArrModalBodies() {
        this.setArrModalBodiesPut();
        this.setArrModalBodiesCall();

    }

    buildModal(event) {
        //$.proxy
        console.log('event!');
        // console.log(this);
        var _handler = this;
        var _mode = $(event.target).closest('.btn-group').attr('mode');
        var _date_shift = $(event.target).closest('.div-timeline-section').attr('id');
        var _shift = $(event.target).text();
        var _target = this._objTbodies[_mode][_date_shift][_shift];
        $('#tbody-modal').append(this._objTbodies[_mode][_date_shift][_shift]._$tr);
        if (this._objTbodies[_mode][_date_shift][_shift].ShiftObject === null) {
            $('#form .btn[type="submit"]').attr('disabled', true);
        } else {
            console.log('exists!');
            $('#form .btn[type="submit"]').attr('action', `${this._http_host}/process/upload_market_item.php`);
            $('#input-mode').attr('value', _mode);
            $('#input-id-request').attr('value', _target.id_request);
            $('#input-id-shift').attr('value', _target.id_shift);
            

        }
    }
}