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
            arrayShiftsByIdUser[_idUser] = groupBy(arrayShiftsByIdUser[_idUser], "Ym");
            for (var Ym in arrayShiftsByIdUser[_idUser]) {
                arrayShiftsByIdUser[_idUser][Ym] = groupBy(arrayShiftsByIdUser[_idUser][Ym], "d");
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
                $option.text(Ym);
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
                console.log(idUser);
                // From == To: this condition is not required but can save calculation.
                if (idUser === selectIdFrom.value) {
                    continue
                }
                var $option = $select.closest('.form-item').find(`.select-id-from option[value=${idUser}]`).clone();
                console.log(this._arrayShiftsByIdUser[idUser][selectMonth.value]);
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
        // this._clonedArrayDateObjects = JSON.parse(JSON.stringify(handler._transaction_form_handler.arrayDateObjects));
        this._clonedArrayDateObjects = Object.assign({}, handler._transaction_form_handler.arrayDateObjects);
        this._$iNotFound.addClass('invisible');
        this._$iTargetOverlap.addClass('invisible');
        this._$iShiftOverlap.addClass('invisible');
        var _confirmable = true;
        this._$formItems.find('.div-form-icons i').addClass('d-none');
        this._$formItems.each(function (idxFormItem) {
            var error = false;
            var $formItem = $(this);
            var $selectsInFormItem = $formItem.find('select');
            $.each($selectsInFormItem, function (idxSelect) {
                // If any select is default or disabled
                if (this.value == 0 || this.disabled) {
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
                _confirmable = false;
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
            // console.log(arrayNumLangsByPart);
            console.log(currentShiftPart);
            console.log(`${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(currentDate.getDate()).padStart(2, '0')}`);
            var currentArrayNumLangs = handler._clonedArrayDateObjects[`${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(currentDate.getDate()).padStart(2, '0')}`].arrayNumLangsByPart[currentShiftPart];
            for (var lang in currentArrayNumLangs) {
                // Substract
                currentArrayNumLangs[lang] -= handler._transaction_form_handler.arrayMemberObjectsByIdUser[$selectsInFormItem[0].value].lang;
                // Add
                currentArrayNumLangs[lang] += handler._transaction_form_handler.arrayMemberObjectsByIdUser[$selectsInFormItem[4].value].lang;
            }
            console.log(currentArrayNumLangs);
        });

        // Finally, check if nums of languages are sufficient
        var enoughNumLangs = handler.checkNumLangs(this._clonedArrayDateObjects);
        if (!enoughNumLangs) {
            _confirmable = false;
        }
        if (!_confirmable) {
            this._$btnConfirm.addClass('disabled');
        } else {
            this._$btnConfirm.removeClass('disabled');
        }
    }

    checkNumLangs(_clonedArrayDateObjects) {
        for (var date in _clonedArrayDateObjects) {
            var dateObject = _clonedArrayDateObjects[date]
            for (var part in dateObject.arrayNumLangsByPart) {
                var arrayNumLangs = dateObject.arrayNumLangsByPart[part];
                for (var lang in arrayNumLangs) {
                    if (arrayNumLangs[lang] !== null && _clonedArrayDateObjects[date].arrayNumLangs[lang] < arrayNumLangs[lang]) {
                        // Insufficient number for this language
                        return false
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
}

// class FormHandler {
//     _shiftParts = [['A', 'B', 'H'], ['C', 'D']];
//     _shiftsPart1 = ["C", "D"];
//     _weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
//     _months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
//     _numItem = 1;
//     _formID = 1;

//     constructor(arrayShiftsByIdUser, arrayDateObjects) {
//         this._arrayShiftsByIdUser = this.prepArrayShiftsByIdUser(arrayShiftsByIdUser);
//         this.arrayDateObjects = arrayDateObjects;
//         this._$form = $('form');
//         this._$defaultFormItem = $('.form-item').clone();
//         this._$formItems = $('.form-item');
//         this._$btnAddItem = $('#btn-add-item');
//         this._$btnConfirm = $('#btn-confirm');
//         this._$modalConfirm = $('#modal-confirm');
//         this._$iNotFound = $('#i-not-found');
//         this._$iTargetOverlap = $('#i-target-overlap');
//         this._$iShiftOverlap = $('#i-shift-overlap');
//         this._$inputIds = $('#input-ids');
//         this.init();
//     }

//     init() {
//         this._$formItems.find('.btn-delete').attr('disabled', true);
//         this.addFormID(this._$formItems);
//         this.addEvents(this._$formItems);
//         // Actions for clicking add icon
//         this._$btnAddItem.click($.proxy(this.addFormItem, this));
//         // Actions for clicking confirm icon: Load details to modal body
//         this._$btnConfirm.click($.proxy(this.loadTransactions, this)).click($.proxy(this.setFormIds, this));
//         // Actions for closing modal: Empty table body
//         this._$modalConfirm.on('hidden.bs.modal', function (event) {
//             $(event.target).find('.modal-body table tbody').empty();
//         });
//     }

//     prepArrayShiftsByIdUser(arrayShiftsByIdUser) {
//         for (var _idUser in arrayShiftsByIdUser) {
//             for (var i in arrayShiftsByIdUser[_idUser]) {
//                 // arrayShiftsByIdUser[_idUser][i]['date_shift'] = new Date(arrayShiftsByIdUser[_idUser][i]['date_shift']);
//                 var date = new Date(arrayShiftsByIdUser[_idUser][i]['date_shift']);
//                 arrayShiftsByIdUser[_idUser][i]["Ym"] = String(date.getFullYear()) + ' ' + this._months[date.getMonth()]; // '2020 Jan'
//                 arrayShiftsByIdUser[_idUser][i]["d"] = String(date.getDate()); // '20'
//                 // arrayShiftsByIdUser[_idUser][i] = groupBy(arrayShiftsByIdUser[_idUser][i], "d");
//             }
//             arrayShiftsByIdUser[_idUser] = groupBy(arrayShiftsByIdUser[_idUser], "Ym");
//             for (var Ym in arrayShiftsByIdUser[_idUser]) {
//                 arrayShiftsByIdUser[_idUser][Ym] = groupBy(arrayShiftsByIdUser[_idUser][Ym], "d");
//                 for (var d in arrayShiftsByIdUser[_idUser][Ym]) {
//                     for (var key in arrayShiftsByIdUser[_idUser][Ym][d]) {
//                         var shift = arrayShiftsByIdUser[_idUser][Ym][d][key]["shift"];
//                         arrayShiftsByIdUser[_idUser][Ym][d][shift] = arrayShiftsByIdUser[_idUser][Ym][d][key]
//                         delete arrayShiftsByIdUser[_idUser][Ym][d][key];
//                     }
//                 }
//             }
//         }
//         return arrayShiftsByIdUser
//     }

//     updateOptions(idx, $selects) {
//         var $select = $($selects[idx]);
//         $select.find('option:not(:first-child').remove();
//         if (idx == 0 || idx > 4) {
//             return
//         }
//         var selectIdFrom = $selects[0];
//         if (idx == 1) {
//             // Currently at Month
//             for (var Ym in this._arrayShiftsByIdUser[selectIdFrom.value]) {
//                 var $option = $('<option></option>').attr('value', Ym);
//                 $option.text(Ym);
//                 $option.appendTo($select);
//             }
//             return
//         }
//         var selectMonth = $selects[1];
//         if (idx == 2) {
//             // Currently at Day
//             var YM = selectMonth.value;
//             for (var d in this._arrayShiftsByIdUser[selectIdFrom.value][YM]) {
//                 var $option = $('<option></option>').attr('value', d);
//                 var weekday = this._weekdays[new Date(YM + ` ${d}`).getDay()];
//                 $option.text(d + ` (${weekday})`);
//                 $option.appendTo($select);
//             }
//             // Set input-year

//             return
//         }
//         var selectDay = $selects[2];
//         if (idx == 3) {
//             // Currently at Shift
//             for (var shift in this._arrayShiftsByIdUser[selectIdFrom.value][selectMonth.value][selectDay.value]) {
//                 var $option = $('<option></option>').attr('value', shift);
//                 $option.text(shift);
//                 $option.appendTo($select);
//             }
//             return
//         }
//         var selectShift = $selects[3];
//         if (idx == 4) {
//             var shiftSelected = selectShift.value;
//             for (var idUser in this._arrayShiftsByIdUser) {
//                 // From == To: this condition is not required but can save calculation.
//                 if (idUser === selectIdFrom.value) {
//                     continue
//                 }
//                 var $option = $select.closest('.form-item').find(`.select-id-from option[value=${idUser}]`).clone();
//                 for (shift in this._arrayShiftsByIdUser[idUser][selectMonth.value][selectDay.value]) {
//                     var shiftSelectedInShiftsPart = this._shiftsPart1.includes(shiftSelected);
//                     if (!(this._shiftsPart1.includes(shift) ^ shiftSelectedInShiftsPart)) {
//                         $option.addClass('text-warning option-warning');
//                         break;
//                     }
//                 }
//                 $option.appendTo($select);
//             }
//             return
//         } else {
//             console.error('Invalid index of select');
//         }
//     }

//     changeSelects(event) {
//         var $selects = $(event.target).closest('.form-item').find('select');
//         var currentIdx = $selects.index(event.target);
//         this.enableSelect(currentIdx + 1, $selects);
//     }

//     disableNoneOption(event) {
//         $(event.target).find('option:first-child').attr('disabled', true);
//     }

//     checkConfirmable(event) {
//         var handler = this;
//         this._$iNotFound.addClass('invisible');
//         this._$iTargetOverlap.addClass('invisible');
//         this._$iShiftOverlap.addClass('invisible');
//         var _confirmable = true;
//         this._$formItems.find('.div-form-icons i').addClass('d-none');
//         this._$formItems.each(function (idxFormItem) {
//             var error = false;
//             var $formItem = $(this);
//             var $selectsInFormItem = $formItem.find('select');
//             $.each($selectsInFormItem, function (idxSelect) {
//                 // If any select is default or disabled
//                 if (this.value == 0 || this.disabled) {
//                     return false
//                 }
//                 // If select.select-id-to has option-warning class
//                 if (idxSelect == 4 && $(this).children(`option[value="${this.value}"]`).hasClass('option-warning')) {
//                     var found = false;
//                     var targetOverlap = false;
//                     var shiftOverlap = false;
//                     console.log(handler._$formItems);
//                     // Check if there is counterpart request
//                     // For each .form-item:
//                     $.each(handler._$formItems, function (index) {
//                         // If same form-item: Skip
//                         if (index === idxFormItem) {
//                             // Continue
//                             return true
//                         }
//                         var $selects = $(this).find('select');
//                         if ($selects[0].value === $selectsInFormItem[4].value
//                             && $selects[1].value === $selectsInFormItem[1].value
//                             && $selects[2].value === $selectsInFormItem[2].value
//                             && !(handler._shiftsPart1.includes($selects[3].value) ^ handler._shiftsPart1.includes($selectsInFormItem[3].value))) {
//                             found = true;
//                             console.log('Found!', index);
//                             return false
//                         }
//                     });
//                     // Check if targets are overlapped for shifts in the same shift part.
//                     $.each(handler._$formItems, function (index) {
//                         if (index === idxFormItem) {
//                             return true
//                         }
//                         var $selects = $(this).find('select');
//                         if ($selects[1].value === $selectsInFormItem[1].value
//                             && $selects[2].value === $selectsInFormItem[2].value
//                             && !(handler._shiftsPart1.includes($selects[3].value) ^ handler._shiftsPart1.includes($selectsInFormItem[3]))
//                             && $selects[4].value === $selectsInFormItem[4].value) {
//                             targetOverlap = true;
//                             return false
//                         }
//                     });
//                     // Check if a shift is transferred to multiple targets
//                     $.each(handler._$formItems, function (index) {
//                         if (index === idxFormItem) {
//                             return true
//                         }
//                         var $selects = $(this).find('select');
//                         if ($selects[0].value === $selectsInFormItem[0].value
//                             && $selects[1].value === $selectsInFormItem[1].value
//                             && $selects[2].value === $selectsInFormItem[2].value
//                             && $selects[3].value === $selectsInFormItem[3].value) {
//                             shiftOverlap = true;
//                             return false
//                         }
//                     });
//                     if (!found) {
//                         $formItem.find('i.i-not-found').removeClass('d-none');
//                         handler._$iNotFound.attr('title', `${$(this).children(`option[value="${this.value}"]`).html()}がもともと持っているシフトとかぶります`).removeClass('invisible');
//                     }
//                     if (targetOverlap) {
//                         $formItem.find('i.i-target-overlap').removeClass('d-none');
//                         handler._$iTargetOverlap.attr('title', `${$(this).children(`option[value="${this.value}"]`).html()}に２つ以上のかぶるシフトを与えています`).removeClass('invisible');
//                     }
//                     if (shiftOverlap) {
//                         $formItem.find('i.i-shift-overlap').removeClass('d-none');
//                         handler._$iShiftOverlap.attr('title', `${$($selectsInFormItem[0]).children(`option[value="${this.value}"]`).html()}の${$selectsInFormItem[1].value} ${$selectsInFormItem[2].value} ${$selectsInFormItem[3].value}を複数人に与えています`).removeClass('invisible');
//                     }
//                     if (!found || targetOverlap || shiftOverlap) {
//                         error = true;
//                         return false
//                     }
//                 }
//             });
//             if (error) {
//                 _confirmable = false;
//                 return false;
//             }
//         });


//         if (!_confirmable) {
//             this._$btnConfirm.addClass('disabled');
//         } else {
//             this._$btnConfirm.removeClass('disabled');
//         }
//     }

//     enableSelect(idx, $selects) {
//         if (idx > $selects.length - 1) {
//             return
//         }
//         var $select = $($selects[idx]);
//         // Enable current select
//         $select.removeAttr('disabled');
//         // Enable and select default option
//         $select.find('option:first-child').removeAttr('disabled');
//         $selects[idx].value = 0;
//         if (idx < $selects.length - 1) {
//             // If more than 2 selects are ahead, disable beyond next of next selects and remove all options but default
//             // console.log('Now at idx:', idx);
//             $selects.slice(idx + 1).attr('disabled', true).map(function (index) {
//                 // console.log('Closing idx:', index+idx+1);
//                 this.value = 0;
//                 $(this).find('option:not(:first-child').remove();
//             });
//         }
//         // Update current options
//         this.updateOptions(idx, $selects);
//         var optionsEnabled = $select.find('option').filter(function () {
//             return $(this).attr('disabled') === undefined
//         });
//         if (optionsEnabled.length > 1) {
//             // if multiple options available
//         } else if (optionsEnabled.length === 1) {
//             // if one option available        
//             if (idx === $selects.length - 1) {
//                 // If current select is the last one:
//             } else {
//                 // If current select is not the last one:
//                 this.enableSelect(idx + 1, $selects);
//             }
//         }
//     }

//     addFormID($formItem) {
//         var handler = this;
//         $formItem.prepend($('<input type="hidden">').attr('name', String(handler._formID)))
//         $formItem.attr('id', handler._formID);
//         $formItem.find('label').each(function () {
//             var newFor = $(this).attr('for') + '_' + String(handler._formID);
//             $(this).attr('for', newFor);
//         });
//         $formItem.find('select').each(function () {
//             var newName = $(this).attr('name') + '_' + String(handler._formID);
//             $(this).attr('name', newName);
//         });
//         $formItem.find('input.input-year').attr('name', 'year_' + handler._formID);
//     }

//     addEvents($formItem) {
//         $formItem.find('select').on('change', $.proxy(this.changeSelects, this)).on('change', $.proxy(this.disableNoneOption, this));
//         $formItem.find('select:not(.select-id-to)').on('change', $.proxy(function () {
//             this._$btnConfirm.addClass('disabled');
//         }, this));
//         $formItem.find('select.select-id-to').on('change', $.proxy(this.checkConfirmable, this));
//         $formItem.find('.btn-delete').click($.proxy(this.deleteItem, this));
//     }

//     addFormItem(event) {
//         if (this._numItem === 1) {
//             this._$formItems.find('.btn-delete').removeAttr('disabled').click($.proxy(this.deleteItem, this));
//         }
//         this._numItem++;
//         this._formID++;
//         var $newFormItem = this._$defaultFormItem.clone();
//         this.addFormID($newFormItem);
//         this.addEvents($newFormItem);
//         this._$form.append($newFormItem);
//         this._$formItems = $.merge(this._$formItems, $newFormItem);
//         this._$btnConfirm.addClass('disabled');
//     }

//     loadTransactions(event) {
//         var $modalTableBody = this._$modalConfirm.find('tbody');
//         $('form .form-item').each(function () {
//             // Append tr>td*5
//             $modalTableBody.append('<tr><td></td><td></td><td></td><td></td><td></td></tr>');
//             $(this).find('select').each(function (index) {
//                 // For each select
//                 var valueSelect = this.value;
//                 $($modalTableBody.find('tr:last-child td')[index]).append($(this).find('option').filter(function () {
//                     return $(this).attr('value') === valueSelect
//                 }).html());
//             })
//         })
//     }

//     setFormIds(event) {
//         var valueString = new String();
//         this._$formItems.each(function () {
//             if (valueString.length) {
//                 valueString += ',';
//             }
//             valueString += $(this).attr('id');
//         });
//         this._$inputIds.attr('value', valueString);
//     }

//     deleteItem(event) {
//         if (this._numItem > 1) {
//             $(event.target).closest('.form-item').remove();
//             this._$formItems = $('.form-item');
//             this._numItem--;
//             if (this._numItem === 1) {
//                 this._$formItems.find('.btn-delete').attr('disabled', true).off();
//             }
//             this.checkConfirmable(event);
//         } else {
//             return;
//         }
//     }
// }

function groupBy(xs, key) {
    return xs.reduce(function (rv, x) {
        (rv[x[key]] = rv[x[key]] || []).push(x);
        return rv;
    }, {});
};

// const shiftsPart1 = ["C", "D"];
// var weekdays = new Array(7);
// weekdays[0] = "Sun";
// weekdays[1] = "Mon";
// weekdays[2] = "Tue";
// weekdays[3] = "Wed";
// weekdays[4] = "Thu";
// weekdays[5] = "Fri";
// weekdays[6] = "Sat";
// var months = new Array(12);
// months[0] = "Jan";
// months[1] = "Feb";
// months[2] = "Mar";
// months[3] = "Apr";
// months[4] = "May";
// months[5] = "Jun";
// months[6] = "Jul";
// months[7] = "Aug";
// months[8] = "Sep";
// months[9] = "Oct";
// months[10] = "Nov";
// months[11] = "Dec";



// function prepArrayShiftsByIdUser(arrayShiftsByIdUser) {
//     // Convert values of 'date_shift' from String to Date Object
//     for (idUser in arrayShiftsByIdUser) {
//         for (i in arrayShiftsByIdUser[idUser]) {
//             // arrayShiftsByIdUser[idUser][i]['date_shift'] = new Date(arrayShiftsByIdUser[idUser][i]['date_shift']);
//             var date = new Date(arrayShiftsByIdUser[idUser][i]['date_shift']);
//             arrayShiftsByIdUser[idUser][i]["Ym"] = String(date.getFullYear()) + ' ' + months[date.getMonth()]; // '2020 Jan'
//             arrayShiftsByIdUser[idUser][i]["d"] = String(date.getDate()); // '20'
//             // arrayShiftsByIdUser[idUser][i] = groupBy(arrayShiftsByIdUser[idUser][i], "d");
//         }
//         arrayShiftsByIdUser[idUser] = groupBy(arrayShiftsByIdUser[idUser], "Ym");
//         for (Ym in arrayShiftsByIdUser[idUser]) {
//             arrayShiftsByIdUser[idUser][Ym] = groupBy(arrayShiftsByIdUser[idUser][Ym], "d");
//             for (d in arrayShiftsByIdUser[idUser][Ym]) {
//                 for (key in arrayShiftsByIdUser[idUser][Ym][d]) {
//                     var shift = arrayShiftsByIdUser[idUser][Ym][d][key]["shift"];
//                     arrayShiftsByIdUser[idUser][Ym][d][shift] = arrayShiftsByIdUser[idUser][Ym][d][key]
//                     delete arrayShiftsByIdUser[idUser][Ym][d][key];
//                 }
//             }
//         }
//     }
//     return arrayShiftsByIdUser
// }
// function test(idx) {
//     switch (idx) {
//         case 0:
//             console.log('case 0');
//             break;
//             console.log('now between 0 and 1');
//         case 1:
//             console.log('case 1');
//             break;
//     }
// }

// function updateOptions(idx, $selects, shiftsPart1) {
//     $select = $($selects[idx]);
//     // console.log(idx);
//     // console.log($selects);
//     // console.log($select);

//     // Clear all options but default.
//     $select.find('option:not(:first-child').remove();
//     if (idx == 0 || idx > 4) {
//         return
//     }
//     var selectIdFrom = $selects[0];
//     if (idx == 1) {
//         // Currently at Month
//         for (Ym in window.arrayShiftsByIdUser[selectIdFrom.value]) {
//             var $option = $('<option></option>').attr('value', Ym);
//             $option.text(Ym);
//             $option.appendTo($select);
//         }
//         return
//     }
//     var selectMonth = $selects[1];
//     if (idx == 2) {
//         // Currently at Day
//         var YM = selectMonth.value;
//         for (d in window.arrayShiftsByIdUser[selectIdFrom.value][YM]) {
//             var $option = $('<option></option>').attr('value', d);
//             var weekday = weekdays[new Date(YM + ` ${d}`).getDay()];
//             $option.text(d + ` (${weekday})`);
//             $option.appendTo($select);
//         }
//         // Set input-year

//         return
//     }
//     var selectDay = $selects[2];
//     if (idx == 3) {
//         // Currently at Shift
//         for (shift in window.arrayShiftsByIdUser[selectIdFrom.value][selectMonth.value][selectDay.value]) {
//             var $option = $('<option></option>').attr('value', shift);
//             $option.text(shift);
//             $option.appendTo($select);
//         }
//         return
//     }
//     var selectShift = $selects[3];
//     if (idx == 4) {
//         shiftSelected = selectShift.value;
//         for (idUser in window.arrayShiftsByIdUser) {
//             // From == To: this condition is not required but can save calculation.
//             if (idUser === selectIdFrom.value) {
//                 continue
//             }
//             var $option = $select.closest('.form-item').find(`.select-id-from option[value=${idUser}]`).clone();
//             for (shift in window.arrayShiftsByIdUser[idUser][selectMonth.value][selectDay.value]) {
//                 var shiftSelectedInShiftsPart = shiftsPart1.includes(shiftSelected);
//                 if (!(shiftsPart1.includes(shift) ^ shiftSelectedInShiftsPart)) {
//                     $option.addClass('text-warning option-warning');
//                     break;
//                 }
//             }
//             $option.appendTo($select);
//         }
//         return
//     }
// }

// function changeSelects(event) {
//     var $selects = $(event.target).closest('.form-item').find('select');
//     currentIdx = $selects.index(event.target);
//     enableSelect(currentIdx + 1, $selects);
// }

// function disableNoneOption(event) {
//     console.log(this);
//     $(event.target).find('option:first-child').attr('disabled', true);
// }

// function checkConfirmable() {
//     var enable = 1;
//     var $formItems = $('.form-item');
//     $formItems.each(function (idxFormItem) {
//         var $formItem = $(this);
//         var $selectsInFormItem = $formItem.find('select');
//         $.each($selectsInFormItem, function (idxSelect) {
//             if (this.value == 0 || this.getAttribute('disabled')) {
//                 return false
//             }
//             if (idxSelect === 4 && $(this).children(`option[value="${this.value}"]`).hasClass('option-warning')) {
//                 var found = 0;
//                 $.each($formItems, function (index) {
//                     if (index === idxSelect) {
//                         // Continue
//                         return true
//                     }
//                     $selects = $(this).find('select');
//                     if ($selects[0].value === $selectsInFormItem[4].value
//                         && $selects[1].value === $selectsInFormItem[1].value
//                         && $selects[2].value === $selectsInFormItem[2].value
//                         && !(shiftsPart1.includes($selects[3].value) ^ shiftsPart1.includes($selectsInFormItem[3]))) {
//                         found = 1;
//                         // Break
//                         return false
//                     }
//                 });
//                 if (!found) {
//                     enable = 0;
//                     return false
//                 }
//             }
//         })
//     });
//     if (enable === 0) {
//         $('#btn-confirm').addClass('disabled');
//     } else {
//         $('#btn-confirm').removeClass('disabled');
//     }
// }

// function enableSelect(idx, $selects) {
//     if (idx > $selects.length - 1) {
//         return
//     }
//     var $select = $($selects[idx]);
//     // Enable current select
//     $select.removeAttr('disabled');
//     // Enable and select default option
//     $select.find('option:first-child').removeAttr('disabled');
//     $selects[idx].value = 0;
//     if (idx < $selects.length - 1) {
//         // If more than 2 selects are ahead, disable beyond next of next selects and remove all options but default
//         // console.log('Now at idx:', idx);
//         $selects.slice(idx + 1).attr('disabled', true).map(function (index) {
//             // console.log('Closing idx:', index+idx+1);
//             this.value = 0;
//             $(this).find('option:not(:first-child').remove();
//         });
//     }
//     // Update current options
//     updateOptions(idx, $selects);
//     var optionsEnabled = $select.find('option').filter(function () {
//         return $(this).attr('disabled') === undefined
//     });
//     if (optionsEnabled.length > 1) {
//         // if multiple options available
//     } else if (optionsEnabled.length === 1) {
//         // if one option available        
//         if (idx === $selects.length - 1) {
//             // If current select is the last one:
//         } else {
//             // If current select is not the last one:
//             enableSelect(idx + 1, $selects);
//         }
//     }
// }

// function addFormID($formItem) {
//     $formItem.prepend($('<input type="hidden">').attr('name', String(window.formID)))
//     $formItem.attr('id', window.formID);
//     $formItem.find('label').each(function () {
//         var newFor = $(this).attr('for') + '_' + String(window.formID);
//         $(this).attr('for', newFor);
//     });
//     $formItem.find('select').each(function () {
//         var newName = $(this).attr('name') + '_' + String(window.formID);
//         $(this).attr('name', newName);
//     });
//     $formItem.find('input.input-year').attr('name', 'year_' + window.formID);
// }

// function addEvents($formItem) {
//     $formItem.find('select').on('change', changeSelects).on('change', disableNoneOption);
//     $formItem.find('select.select-id-to').on('change', checkConfirmable);
//     $formItem.find('.btn-delete').click(deleteItem);
// }

// function addFormItem(event) {
//     if (window.numItem === 1) {
//         $('.btn-delete').removeAttr('disabled').click(deleteItem);
//     }
//     window.numItem++;
//     window.formID++;
//     var newFormItem = defaultForm.clone()
//     addFormID(newFormItem);
//     addEvents(newFormItem);
//     $('form').append(newFormItem);
//     $('#btn-confirm').addClass('disabled');
// }

// function loadTransactions(event) {
//     var modalTableBody = $('#modal-confirm .modal-dialog .modal-content .modal-body table tbody');
//     $('form .form-item').each(function () {
//         // Append tr>td*5
//         modalTableBody.append('<tr><td></td><td></td><td></td><td></td><td></td></tr>');
//         $(this).find('select').each(function (index) {
//             // For each select
//             var valueSelect = this.value;
//             $(modalTableBody.find('tr:last-child td')[index]).append($(this).find('option').filter(function () {
//                 return $(this).attr('value') === valueSelect
//             }).html());
//         })
//     })
// }

// function setFormIds(event) {
//     var valueString = new String();
//     $('.form-item').each(function () {
//         if (valueString.length) {
//             valueString += ',';
//         }
//         valueString += $(this).attr('id');
//     });
//     $('#input-ids').attr('value', valueString);
// }

// function deleteItem(event) {
//     if (window.numItem > 1) {
//         console.log($(event.target).closest('.form-item'));
//         $(event.target).closest('.form-item').remove();
//         window.numItem--;
//         if (window.numItem === 1) {
//             $('.btn-delete').attr('disabled', true).off();
//         }
//         checkConfirmable();
//     } else {
//         return;
//     }
// }



// // Preprocess arrayShiftsByIdUser
// window.arrayShiftsByIdUser = prepArrayShiftsByIdUser(window.arrayShiftsByIdUser);
// // Store default form
// const defaultForm = $('.form-item').clone();
// var numItem = 1;
// var formID = 1;
// $('.form-item .btn-delete').attr('disabled', true);
// addFormID($('.form-item'));
// // Actions for selecting an option: Enable/disable selects
// $('.form-item select').on('change', changeSelects).on('change', disableNoneOption);
// // Actions for selecting an option: Enable/disable confirm button
// $('select.select-id-to').on('change', checkConfirmable);
// // Actions for clicking delete icon
// // $('.btn-delete').click(deleteItem);
// // Actions for clicking add icon
// $('#btn-add-item').click(addFormItem);
// // Actions for clicking confirm icon: Load details to modal body
// $('#btn-confirm').click(loadTransactions).click(setFormIds);
// // Actions for closing modal: Empty table body
// $('#modal-confirm').on('hidden.bs.modal', function (event) {
//     $(event.target).find('.modal-body table tbody').empty();
// });