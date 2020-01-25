class MarketItemHandler {
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