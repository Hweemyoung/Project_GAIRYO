class MarketItemHandler {
    constructor(_objectDateObjects) {
        this._objectDateObjects = _objectDateObjects;
        this._objectTimelineSections = {};
    }

    genTimelineSections() {
        for (var _date in this._objectDateObjects) {
            this._objectTimelineSections[_date] = new TimelineSection(this._objectDateObjects[_date]);
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
    }

    setTimelineSection(_dateObject) {
        this._$ = $('<div class="row align-items-center how-it-works d-flex"></div>');
        // Col Date
        this.divColDate.clone().append(this.divCircle.append($('<p></p>').text(_dateObject.date))).appendTo(this._$);
        // Col Shifts
        // Shift Put
        var _$btnGroup = $('<div class="btn-group"></div>');
        for (var _shift in _dateObject.arrayShiftObjectsByShift) {
            $(`<button class="btn" type="button">${_shift}</button>`).appendTo(_$btnGroup);
        }
        this.divColShifts.append($('<div class="row"></div>').append($('<div class="col-12 col-put">').append(_$btnGroup)));
        // Shift Call
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