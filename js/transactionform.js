class FormHandler {
    _shiftParts = [['A', 'B', 'H'], ['C', 'D']];
    _shiftsPart1 = ["C", "D"];
    _weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    _months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    _numItem = 1;
    _formID = 1;

    constructor(_arrayShiftsByIdUser, _transaction_form_handler) {
        this._transaction_form_handler = _transaction_form_handler;
        this._arrayShiftsByIdUser = this.prepArrayShiftsByIdUser(_arrayShiftsByIdUser)
        this._$form = $('form');
        this._$defaultFormItem = $('.form-item').clone();
        this._$formItems = $('.form-item');
        this._$btnAddItem = $('#btn-add-item');
        this._$btnConfirm = $('#btn-confirm');
        this._$modalConfirm = $('#modal-confirm');
        this._$iNotFound = $('#i-not-found');
        this._$iTargetOverlap = $('#i-target-overlap');
        this._$iShiftOverlap = $('#i-shift-overlap');
        this._$iLangNotEnough = $('#i-lang-not-enough');
        this._$inputIds = $('#input-ids');
        this.init();
    }

    prepArrayShiftsByIdUser(arrayShiftsByIdUser) {
        for (var _idUser in arrayShiftsByIdUser) {
            for (var i in arrayShiftsByIdUser[_idUser]) {
                // arrayShiftsByIdUser[_idUser][i]['date_shift'] = new Date(arrayShiftsByIdUser[_idUser][i]['date_shift']);
                var date = new Date(arrayShiftsByIdUser[_idUser][i]['date_shift']);
                arrayShiftsByIdUser[_idUser][i]["Ym"] = String(date.getFullYear()) + ' ' + this._months[date.getMonth()]; // '2020 Jan'
                arrayShiftsByIdUser[_idUser][i]["d"] = String(date.getDate()); // '20'
                // arrayShiftsByIdUser[_idUser][i] = groupBy(arrayShiftsByIdUser[_idUser][i], "d");
            }
            arrayShiftsByIdUser[_idUser] = this.groupBy(arrayShiftsByIdUser[_idUser], "Ym");
            for (var Ym in arrayShiftsByIdUser[_idUser]) {
                arrayShiftsByIdUser[_idUser][Ym] = this.groupBy(arrayShiftsByIdUser[_idUser][Ym], "d");
                for (var d in arrayShiftsByIdUser[_idUser][Ym]) {
                    for (var key in arrayShiftsByIdUser[_idUser][Ym][d]) {
                        var shift = arrayShiftsByIdUser[_idUser][Ym][d][key]["shift"];
                        arrayShiftsByIdUser[_idUser][Ym][d][shift] = arrayShiftsByIdUser[_idUser][Ym][d][key]
                        delete arrayShiftsByIdUser[_idUser][Ym][d][key];
                    }
                }
            }
        }
        return arrayShiftsByIdUser
    }

    init() {
        this._$formItems.find('.btn-delete').attr('disabled', true);
        this.addFormID(this._$formItems);
        this.addEvents(this._$formItems);
        // Actions for clicking add icon
        this._$btnAddItem.click($.proxy(this.addFormItem, this));
        // Actions for clicking confirm icon: Load details to modal body
        this._$btnConfirm.click($.proxy(this.loadTransactions, this)).click($.proxy(this.setFormIds, this));
        // Actions for closing modal: Empty table body
        this._$modalConfirm.on('hidden.bs.modal', function (event) {
            $(event.target).find('.modal-body table tbody').empty();
        });
    }

    updateOptions(idx, $selects) {
        var $select = $($selects[idx]);
        $select.find('option:not(:first-child').remove();
        if (idx == 0 || idx > 4) {
            return
        }
        var selectIdFrom = $selects[0];
        if (idx == 1) {
            // Currently at Month
            for (var Ym in this._arrayShiftsByIdUser[selectIdFrom.value]) {
                var $option = $('<option></option>').attr('value', Ym);
                $option.text(Ym.split(' ')[1]);
                $option.appendTo($select);
            }
            return
        }
        var selectMonth = $selects[1];
        if (idx == 2) {
            // Currently at Day
            var YM = selectMonth.value;
            for (var d in this._arrayShiftsByIdUser[selectIdFrom.value][YM]) {
                var $option = $('<option></option>').attr('value', d);
                var weekday = this._weekdays[new Date(YM + ` ${d}`).getDay()];
                $option.text(d + ` (${weekday})`);
                $option.appendTo($select);
            }
            // Set input-year

            return
        }
        var selectDay = $selects[2];
        if (idx == 3) {
            // Currently at Shift
            for (var shift in this._arrayShiftsByIdUser[selectIdFrom.value][selectMonth.value][selectDay.value]) {
                var $option = $('<option></option>').attr('value', shift);
                $option.text(shift);
                $option.appendTo($select);
            }
            return
        }
        var selectShift = $selects[3];
        if (idx == 4) {
            var shiftSelected = selectShift.value;
            for (var idUser in this._arrayShiftsByIdUser) {
                // From == To: this condition is not required but can save calculation.
                if (idUser === selectIdFrom.value) {
                    continue
                }
                var $option = $select.closest('.form-item').find(`.select-id-from option[value=${idUser}]`).clone();
                if (selectMonth.value in this._arrayShiftsByIdUser[idUser]) {
                    if (selectDay.value in this._arrayShiftsByIdUser[idUser][selectMonth.value]) {
                        for (shift in this._arrayShiftsByIdUser[idUser][selectMonth.value][selectDay.value]) {
                            var shiftSelectedInShiftsPart = this._shiftsPart1.includes(shiftSelected);
                            if (!(this._shiftsPart1.includes(shift) ^ shiftSelectedInShiftsPart)) {
                                $option.addClass('text-warning option-warning');
                                break;
                            }
                        }
                    }
                }
                $option.appendTo($select);
            }
            return
        } else {
            console.error('Invalid index of select');
        }
    }

    changeSelects(event) {
        var $selects = $(event.target).closest('.form-item').find('select');
        var currentIdx = $selects.index(event.target);
        this.enableSelect(currentIdx + 1, $selects);
    }

    disableNoneOption(event) {
        $(event.target).find('option:first-child').attr('disabled', true);
    }

    checkConfirmable(event) {
        var handler = this;
        var _clonedArrayDateObjects = JSON.parse(JSON.stringify(this._transaction_form_handler.arrayDateObjects));
        var _objectDatesByIdxFormItem = {};
        var _ignore = false;
        var _confirmable = true;

        this._$btnConfirm.addClass('disabled');
        this._$iNotFound.addClass('invisible');
        this._$iTargetOverlap.addClass('invisible');
        this._$iShiftOverlap.addClass('invisible');
        this._$iLangNotEnough.addClass('invisible');
        this._$formItems.find('.div-form-icons i').addClass('d-none');
        this._$formItems.each(function (idxFormItem) {
            // New variables
            var error = false;

            var $formItem = $(this);
            var $selectsInFormItem = $formItem.find('select');
            $.each($selectsInFormItem, function (idxSelect) {
                // If any select is default or disabled
                if (this.value == 0 || this.disabled) {
                    error = true;
                    return false
                }
                // If select.select-id-to has option-warning class
                if (idxSelect == 4 && $(this).children(`option[value="${this.value}"]`).hasClass('option-warning')) {
                    var found = false;
                    var targetOverlap = false;
                    var shiftOverlap = false;
                    console.log(handler._$formItems);
                    // Check if there is counterpart request
                    // For each .form-item:
                    $.each(handler._$formItems, function (index) {
                        // If same form-item: Skip
                        if (index === idxFormItem) {
                            // Continue
                            return true
                        }
                        var $selects = $(this).find('select');
                        if ($selects[0].value === $selectsInFormItem[4].value
                            && $selects[1].value === $selectsInFormItem[1].value
                            && $selects[2].value === $selectsInFormItem[2].value
                            && !(handler._shiftsPart1.includes($selects[3].value) ^ handler._shiftsPart1.includes($selectsInFormItem[3].value))) {
                            found = true;
                            console.log('Found!', index);
                            return false
                        }
                    });
                    // Check if targets are overlapped for shifts in the same shift part.
                    $.each(handler._$formItems, function (index) {
                        if (index === idxFormItem) {
                            return true
                        }
                        var $selects = $(this).find('select');
                        if ($selects[1].value === $selectsInFormItem[1].value
                            && $selects[2].value === $selectsInFormItem[2].value
                            && !(handler._shiftsPart1.includes($selects[3].value) ^ handler._shiftsPart1.includes($selectsInFormItem[3]))
                            && $selects[4].value === $selectsInFormItem[4].value) {
                            targetOverlap = true;
                            return false
                        }
                    });
                    // Check if a shift is transferred to multiple targets
                    $.each(handler._$formItems, function (index) {
                        if (index === idxFormItem) {
                            return true
                        }
                        var $selects = $(this).find('select');
                        if ($selects[0].value === $selectsInFormItem[0].value
                            && $selects[1].value === $selectsInFormItem[1].value
                            && $selects[2].value === $selectsInFormItem[2].value
                            && $selects[3].value === $selectsInFormItem[3].value) {
                            shiftOverlap = true;
                            return false
                        }
                    });
                    if (!found) {
                        $formItem.find('i.i-not-found').removeClass('d-none');
                        handler._$iNotFound.attr('title', `${$(this).children(`option[value="${this.value}"]`).html()}がもともと持っているシフトとかぶります`).removeClass('invisible');
                    }
                    if (targetOverlap) {
                        $formItem.find('i.i-target-overlap').removeClass('d-none');
                        handler._$iTargetOverlap.attr('title', `${$(this).children(`option[value="${this.value}"]`).html()}に２つ以上のかぶるシフトを与えています`).removeClass('invisible');
                    }
                    if (shiftOverlap) {
                        $formItem.find('i.i-shift-overlap').removeClass('d-none');
                        handler._$iShiftOverlap.attr('title', `${$($selectsInFormItem[0]).children(`option[value="${this.value}"]`).html()}の${$selectsInFormItem[1].value} ${$selectsInFormItem[2].value} ${$selectsInFormItem[3].value}を複数人に与えています`).removeClass('invisible');
                    }
                    if (!found || targetOverlap || shiftOverlap) {
                        error = true;
                        return false
                    }
                }
            });

            // Calc num of languages for this form item.
            if (error) {
                _ignore = true;
                return false;
            }
            // Find current shift part
            for (var shiftPart in handler._shiftParts) {
                if (handler._shiftParts[shiftPart].includes($selectsInFormItem[3].value)) {
                    var currentShiftPart = shiftPart;
                    break;
                }
            }
            var currentDate = new Date(`${$selectsInFormItem[1].value} ${$selectsInFormItem[2].value}`);
            var stringDate = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(currentDate.getDate()).padStart(2, '0')}`;
            // Append form item idx and date to object.
            _objectDatesByIdxFormItem[idxFormItem] = stringDate;
            var currentArrayNumLangs = _clonedArrayDateObjects[stringDate].arrayNumLangsByPart[currentShiftPart];
            // console.log("Before:", currentArrayNumLangs);
            for (var lang in currentArrayNumLangs) {
                // Substract
                console.log("Member's", lang, handler._transaction_form_handler.arrayMemberObjectsByIdUser[$selectsInFormItem[0].value][lang]);
                currentArrayNumLangs[lang] -= Number(handler._transaction_form_handler.arrayMemberObjectsByIdUser[$selectsInFormItem[0].value][lang]);
                // Add
                currentArrayNumLangs[lang] += Number(handler._transaction_form_handler.arrayMemberObjectsByIdUser[$selectsInFormItem[4].value][lang]);
            }
            // console.log("After:", currentArrayNumLangs);
        });

        if (_ignore) {
            _confirmable = false;
        } else {
            // Finally, check if nums of languages are sufficient
            var enoughNumLangs = handler.checkNumLangs(_clonedArrayDateObjects, _objectDatesByIdxFormItem);
            if (enoughNumLangs !== true) {
                // [idxFormItem, 2020-01-23, cn, 1, 2]
                $(this._$formItems[enoughNumLangs[0]]).find('i.i-lang-not-enough').removeClass('d-none');
                this._$iLangNotEnough.attr('title', `${enoughNumLangs[1]}の${enoughNumLangs[2]}が${enoughNumLangs[3]}人になります。最低${enoughNumLangs[4]}人が必要です。`).removeClass('invisible');
                this._$formItems
                _confirmable = false;

            }
        }

        if (_confirmable) {
            this._$btnConfirm.removeClass('disabled');
        } else {
            this._$btnConfirm.addClass('disabled');
        }
    }

    checkNumLangs(_clonedArrayDateObjects, _objectDatesByIdxFormItem) {
        console.log(_objectDatesByIdxFormItem);
        for (var idxFormItem in _objectDatesByIdxFormItem) {
            var dateObject = _clonedArrayDateObjects[_objectDatesByIdxFormItem[idxFormItem]];
            console.log(dateObject);
            for (var part in dateObject.arrayNumLangsByPart) {
                var arrayLangs = dateObject.arrayLangsByPart[part];
                var arrayNumLangs = dateObject.arrayNumLangsByPart[part];
                console.log(arrayNumLangs);
                for (var lang in arrayNumLangs) {
                    if (arrayLangs[lang] !== null) {
                        if (arrayNumLangs[lang] < arrayLangs[lang]) {
                            // Insufficient number for this language
                            if (arrayNumLangs[lang] < Number(this._transaction_form_handler.arrayDateObjects[_objectDatesByIdxFormItem[idxFormItem]].arrayLangsByPart[part][lang])) {
                                // Is that due to change of number? Or it just used to be not enough? If the number is decreased by transaction, ban it.
                                return [idxFormItem, _objectDatesByIdxFormItem[idxFormItem], lang, arrayNumLangs[lang], this._transaction_form_handler.arrayDateObjects[_objectDatesByIdxFormItem[idxFormItem]].arrayLangsByPart[part][lang]]
                                // [0, 2020-01-23, cn, 1, 2]
                            }
                        }
                    }
                }
            }
        }
        return true
    }

    enableSelect(idx, $selects) {
        if (idx > $selects.length - 1) {
            return
        }
        var $select = $($selects[idx]);
        // Enable current select
        $select.removeAttr('disabled');
        // Enable and select default option
        $select.find('option:first-child').removeAttr('disabled');
        $selects[idx].value = 0;
        if (idx < $selects.length - 1) {
            // If more than 2 selects are ahead, disable beyond next of next selects and remove all options but default
            // console.log('Now at idx:', idx);
            $selects.slice(idx + 1).attr('disabled', true).map(function (index) {
                // console.log('Closing idx:', index+idx+1);
                this.value = 0;
                $(this).find('option:not(:first-child').remove();
            });
        }
        // Update current options
        this.updateOptions(idx, $selects);
        var optionsEnabled = $select.find('option').filter(function () {
            return $(this).attr('disabled') === undefined
        });
        if (optionsEnabled.length > 1) {
            // if multiple options available
        } else if (optionsEnabled.length === 1) {
            // if one option available        
            if (idx === $selects.length - 1) {
                // If current select is the last one:
            } else {
                // If current select is not the last one:
                this.enableSelect(idx + 1, $selects);
            }
        }
    }

    addFormID($formItem) {
        var handler = this;
        $formItem.prepend($('<input type="hidden">').attr('name', String(handler._formID)))
        $formItem.attr('id', handler._formID);
        $formItem.find('label').each(function () {
            var newFor = $(this).attr('for') + '_' + String(handler._formID);
            $(this).attr('for', newFor);
        });
        $formItem.find('select').each(function () {
            var newName = $(this).attr('name') + '_' + String(handler._formID);
            $(this).attr('name', newName);
        });
        $formItem.find('input.input-year').attr('name', 'year_' + handler._formID);
    }

    addEvents($formItem) {
        $formItem.find('select').on('change', $.proxy(this.changeSelects, this)).on('change', $.proxy(this.disableNoneOption, this));
        $formItem.find('select:not(.select-id-to)').on('change', $.proxy(function () {
            this._$btnConfirm.addClass('disabled');
        }, this));
        $formItem.find('select.select-id-to').on('change', $.proxy(this.checkConfirmable, this));
        $formItem.find('.btn-delete').click($.proxy(this.deleteItem, this));
    }

    addFormItem(event) {
        if (this._numItem === 1) {
            this._$formItems.find('.btn-delete').removeAttr('disabled').click($.proxy(this.deleteItem, this));
        }
        this._numItem++;
        this._formID++;
        var $newFormItem = this._$defaultFormItem.clone();
        this.addFormID($newFormItem);
        this.addEvents($newFormItem);
        this._$form.append($newFormItem);
        this._$formItems = $.merge(this._$formItems, $newFormItem);
        this._$btnConfirm.addClass('disabled');
    }

    loadTransactions(event) {
        var $modalTableBody = this._$modalConfirm.find('tbody');
        $('form .form-item').each(function () {
            // Append tr>td*5
            $modalTableBody.append('<tr><td></td><td></td><td></td><td></td><td></td></tr>');
            $(this).find('select').each(function (index) {
                // For each select
                var valueSelect = this.value;
                $($modalTableBody.find('tr:last-child td')[index]).append($(this).find('option').filter(function () {
                    return $(this).attr('value') === valueSelect
                }).html());
            })
        })
    }

    setFormIds(event) {
        var valueString = new String();
        this._$formItems.each(function () {
            if (valueString.length) {
                valueString += ',';
            }
            valueString += $(this).attr('id');
        });
        this._$inputIds.attr('value', valueString);
    }

    deleteItem(event) {
        if (this._numItem > 1) {
            $(event.target).closest('.form-item').remove();
            this._$formItems = $('.form-item');
            this._numItem--;
            if (this._numItem === 1) {
                this._$formItems.find('.btn-delete').attr('disabled', true).off();
            }
            this.checkConfirmable(event);
        } else {
            return;
        }
    }

    groupBy(xs, key) {
        return xs.reduce(function (rv, x) {
            (rv[x[key]] = rv[x[key]] || []).push(x);
            return rv;
        }, {});
    };
}