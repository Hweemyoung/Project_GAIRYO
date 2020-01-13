class AlertHandler {
    constructor(_alertArray, ms=5000) {
        this._alertArray = _alertArray;
        this._ms = ms;
        this.process();
    }

    prependAlert() {
        if (typeof(this._alertArray) != undefined) {
            this._$alert = $('<div></div>').attr('id', 'div-alert').addClass(`alert ${this._alertArray.alertMode} alert-dismissible fade show fixed-bottom`).append($('<strong></strong>').html(this._alertArray.alertStrong)).append($('<p></p>').html(this._alertArray.alertMsg));
            this._$alert.prependTo('main');
        }
    }

    closeAlert() {
        setTimeout(() => {
            this._$alert.alert('close');
        }, this._ms);
    }

    process() {
        this.prependAlert();
        this.closeAlert();
    }
}