class FormAutoSelector {
    constructor(_id_from, _month, _day, _shift) {
        this.id_from = _id_from;
        this.month = _month.replace("_", " ");
        this.day = _day;
        this.shift = _shift;
        this.process();
    }

    process() {
        $('#1').find(`select[name="id_from_1"] > option[value="${this.id_from}"]`).prop('selected', 'selected');
        $('#1').find(`select[name="id_from_1"]`).trigger('change');
        $('#1').find(`select[name="month_1"] > option[value="${this.month}"]`).prop('selected', 'selected');
        $('#1').find(`select[name="month_1"]`).trigger('change');
        $('#1').find(`select[name="day_1"] > option[value="${this.day}"]`).prop('selected', 'selected');
        $('#1').find(`select[name="day_1"]`).trigger('change');
        $('#1').find(`select[name="shift_1"] > option[value="${this.shift}"]`).prop('selected', 'selected');
        $('#1').find(`select[name="shift_1"]`).trigger('change');
    }
}