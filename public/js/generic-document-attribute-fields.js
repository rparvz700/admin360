$(document).ready(function() {
    function renderAttributeFields(attributes, values = {}) {
        let html = '';
        attributes.forEach(function(attribute) {
            html += '<div class="mb-3">';
            html += '<label class="form-label" for="attribute_' + attribute.id + '">' + attribute.attribute_name;
            if(attribute.attribute_type === 'select') html += ' <small>(Select)</small>';
            html += '</label>';
            let val = values[attribute.id] || '';
            if(attribute.attribute_type === 'string') {
                html += '<input type="text" class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']" value="' + val + '">';
            } else if(attribute.attribute_type === 'number') {
                html += '<input type="number" class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']" value="' + val + '">';
            } else if(attribute.attribute_type === 'date') {
                html += '<input type="date" class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']" value="' + val + '">';
            } else if(attribute.attribute_type === 'boolean') {
                html += '<div class="form-check form-check-inline">';
                html += '<input class="form-check-input" type="radio" name="attributes[' + attribute.id + ']" id="attribute_' + attribute.id + '_yes" value="1" ' + (val == '1' ? 'checked' : '') + '>';
                html += '<label class="form-check-label" for="attribute_' + attribute.id + '_yes">Yes</label>';
                html += '</div>';
                html += '<div class="form-check form-check-inline">';
                html += '<input class="form-check-input" type="radio" name="attributes[' + attribute.id + ']" id="attribute_' + attribute.id + '_no" value="0" ' + (val == '0' ? 'checked' : '') + '>';
                html += '<label class="form-check-label" for="attribute_' + attribute.id + '_no">No</label>';
                html += '</div>';
            } else if(attribute.attribute_type === 'select') {
                let options = Array.isArray(attribute.options) ? attribute.options : [];
                html += '<select class="form-control select2" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']">';
                html += '<option value="">Select</option>';
                options.forEach(function(option) {
                    html += '<option value="' + option + '" ' + (val == option ? 'selected' : '') + '>' + option + '</option>';
                });
                html += '</select>';
            }
            html += '</div>';
        });
        $('#attribute-fields').html(html);
        // Destroy any existing select2 before re-initializing to avoid duplicate dropdowns
        $('#attribute-fields .select2').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });
        $('.select2').select2();

    }

    let allAttributes = window.allDocumentAttributes || [];
    let oldValues = window.oldAttributeValues || {};


    function updateAttributeFields() {
        // Always clear blur if present before re-rendering
        $('.select2-blur-overlay').remove();
        $('body').removeClass('select2-blur-active');
        let categoryId = $('#category_id').val();
        let attributes = allAttributes.filter(function(attr) {
            return attr.category_id == categoryId;
        });
        renderAttributeFields(attributes, oldValues);
    }

    $('#category_id').on('change', function() {
        updateAttributeFields();
    });

    updateAttributeFields();
});
