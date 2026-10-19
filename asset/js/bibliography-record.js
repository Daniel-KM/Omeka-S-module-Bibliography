'use strict';

/**
 * Fill the resource form from a value "collected record" of Value Suggest.
 *
 * The suggester stores the whole record as json in the uri of the value. When
 * it is selected, the empty properties and the empty resource class are filled,
 * the properties already filled are listed, and the value keeps the uri of the
 * record. The same process is done on save for the api.
 */
(function () {

    const isRecordValue = function (valueElement) {
        return String($(valueElement).attr('data-data-type') || '').endsWith(':record');
    };

    const parseRecord = function (string) {
        if (typeof string !== 'string' || !string.trim().startsWith('{')) {
            return null;
        }
        try {
            const record = JSON.parse(string);
            return record && typeof record === 'object' ? record : null;
        } catch (e) {
            return null;
        }
    };

    // Previous versions stored the alternative ids of Crossref as doi, but they
    // may be ids of the publisher, for example "2014-57555-025".
    const isValidDoiValue = function (value) {
        const doi = value ? String(value['@id'] || value['@value'] || '') : '';
        return /^(?:https?:\/\/(?:dx\.)?doi\.org\/)?10\.\d+\//i.test(doi);
    };

    const recordUri = function (record) {
        for (const term of ['bibo:doi', 'bibo:uri']) {
            for (const value of record[term] || []) {
                if (value && typeof value['@id'] === 'string' && value['@id']
                    && (term !== 'bibo:doi' || isValidDoiValue(value))
                ) {
                    return value['@id'];
                }
            }
        }
        return null;
    };

    const isEmptyValue = function (valueElement) {
        const value = $(valueElement);
        if (value.hasClass('delete')) {
            return true;
        }
        if (value.find('.selected-resource .o-title a').length) {
            return false;
        }
        return !value.find(':input[data-value-key]').filter(function () {
            return this.name !== 'o:lang' && $(this).data('value-key') !== '@language'
                && String($(this).val() || '').trim() !== '';
        }).length;
    };

    const isFilledField = function (field) {
        return field.find('.values > .value').filter(function () {
            return !isEmptyValue(this);
        }).length > 0;
    };

    const setValue = function (valueElement, valueObject) {
        const value = $(valueElement);
        ['@value', '@id', 'o:label', '@language'].forEach(function (key) {
            if (typeof valueObject[key] === 'undefined' || valueObject[key] === null) {
                return;
            }
            value.find(':input[data-value-key="' + key + '"]').val(valueObject[key]);
        });
        if (valueObject['@language']) {
            value.find('.language-wrapper').addClass('active');
        }
    };

    const addValue = function (field, valueObject) {
        const type = valueObject.type || 'literal';
        // The type of the button of a template field is set with jQuery data,
        // not as attribute. Prefer the visible selector.
        const buttons = field.find('.add-value').filter(function () {
            return $(this).data('type') === type;
        });
        const visibleButtons = buttons.filter(function () {
            return $(this).closest('.add-values').is(':visible');
        });
        const button = (visibleButtons.length ? visibleButtons : buttons).first();
        if (!button.length) {
            return false;
        }
        // Reuse an empty value of the same type, for example from a template.
        let value = field.find('.values > .value').filter(function () {
            return $(this).attr('data-data-type') === type && isEmptyValue(this) && !$(this).hasClass('delete');
        }).first();
        if (!value.length) {
            button.trigger('click');
            value = field.find('.values > .value').last();
        }
        setValue(value, valueObject);
        return true;
    };

    const getField = function (term) {
        let field = $('#properties .resource-property[data-property-term="' + term + '"]').first();
        if (!field.length) {
            $('#property-selector li.selector-child[data-property-term="' + term + '"]').trigger('click');
            field = $('#properties .resource-property[data-property-term="' + term + '"]').first();
        }
        return field;
    };

    const showMessage = function (valueElement, filled, skipped) {
        const value = $(valueElement);
        value.find('.bibliography-record-message').remove();
        const messages = [];
        if (filled.length) {
            messages.push(Omeka.jsTranslate('Properties filled from the bibliographic record:') + ' ' + filled.join(', '));
        }
        if (skipped.length) {
            messages.push(Omeka.jsTranslate('Properties already filled, not modified:') + ' ' + skipped.join(', '));
        }
        if (!messages.length) {
            return;
        }
        const message = $('<div class="bibliography-record-message field-description"></div>');
        messages.forEach(function (text) {
            message.append($('<p></p>').text(text));
        });
        value.append(message);
    };

    const expandRecord = function (valueElement) {
        const value = $(valueElement);
        const idInput = value.find(':input[data-value-key="@id"]');
        const record = parseRecord(idInput.val());
        if (!record) {
            return;
        }

        const ownTerm = value.closest('.resource-property').data('property-term');
        const filled = [];
        const skipped = [];
        Object.keys(record).forEach(function (term) {
            let values = record[term];
            if (term.startsWith('o:') || !Array.isArray(values)) {
                return;
            }
            if (term === 'bibo:doi') {
                values = values.filter(isValidDoiValue);
            }
            if (!values.length) {
                return;
            }
            const field = term === ownTerm ? null : getField(term);
            if (!field || !field.length || isFilledField(field)) {
                skipped.push(term);
                return;
            }
            let isAdded = false;
            values.forEach(function (valueObject) {
                isAdded = addValue(field, valueObject) || isAdded;
            });
            (isAdded ? filled : skipped).push(term);
        });

        const classId = record['o:resource_class'] && record['o:resource_class']['o:id'];
        const classSelect = $('#resource-class-select');
        if (classId && classSelect.length && !classSelect.val()) {
            classSelect.val(String(classId)).trigger('chosen:updated');
        }

        // Keep the uri of the record. Update the input first, so the update of
        // the link below does not process the record again.
        const uri = recordUri(record);
        if (uri) {
            idInput.val(uri);
            value.find('.valuesuggest-id').empty()
                .append($('<a target="_blank" rel="noopener"></a>').attr('href', uri).text(uri));
        }

        showMessage(value, filled, skipped);
    };

    $(document).ready(function () {
        const properties = document.getElementById('properties');
        if (!properties || typeof MutationObserver === 'undefined') {
            return;
        }
        // Value Suggest updates the link of the uri when a suggestion is
        // selected, in all its versions.
        new MutationObserver(function (mutations) {
            const values = new Set();
            mutations.forEach(function (mutation) {
                const id = $(mutation.target).closest('.valuesuggest-id');
                if (id.length) {
                    const value = id.closest('.value').get(0);
                    if (value && isRecordValue(value)) {
                        values.add(value);
                    }
                }
            });
            values.forEach(expandRecord);
        }).observe(properties, {childList: true, subtree: true, characterData: true});
    });

})();
