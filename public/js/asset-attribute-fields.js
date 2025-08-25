// Dynamically load asset attributes for selected category in asset create form
$(document).ready(function() {
    var allAttributes = window.allAttributes || [];
    var attributeFieldsDiv = $('#attribute-fields');

    function renderAttributes(categoryId) {
        attributeFieldsDiv.empty();
        var filtered = allAttributes.filter(function(attr) {
            return attr.category_id == categoryId;
        });
        filtered.forEach(function(attribute) {
            var field = '';
            var name = 'attributes[' + attribute.id + ']';
            var id = 'attribute_' + attribute.id;
            field += '<div class="mb-3">';
            field += '<label class="form-label" for="' + id + '">' + attribute.attribute_name + '</label>';
            if(attribute.attribute_type === 'string') {
                field += '<input type="text" class="form-control" id="' + id + '" name="' + name + '">';
            } else if(attribute.attribute_type === 'number') {
                field += '<input type="number" class="form-control" id="' + id + '" name="' + name + '">';
            } else if(attribute.attribute_type === 'date') {
                field += '<input type="date" class="form-control" id="' + id + '" name="' + name + '">';
            } else if(attribute.attribute_type === 'boolean') {
                field += '<div>';
                field += '<div class="form-check form-check-inline">';
                field += '<input class="form-check-input" type="radio" name="' + name + '" id="' + id + '_yes" value="1">';
                field += '<label class="form-check-label" for="' + id + '_yes">Yes</label>';
                field += '</div>';
                field += '<div class="form-check form-check-inline">';
                field += '<input class="form-check-input" type="radio" name="' + name + '" id="' + id + '_no" value="0">';
                field += '<label class="form-check-label" for="' + id + '_no">No</label>';
                field += '</div>';
                field += '</div>';
            } else if(attribute.attribute_type === 'select') {
                field += '<select class="form-control select2" id="' + id + '" name="' + name + '">';
                field += '<option value="">Select</option>';
                (attribute.options || []).forEach(function(option) {
                    field += '<option value="' + option + '">' + option + '</option>';
                });
                field += '</select>';
            }
            field += '</div>';
            attributeFieldsDiv.append(field);
        });
        // Re-initialize select2 for new fields
        attributeFieldsDiv.find('.select2').select2({ width: '100%', theme: 'bootstrap-5' });
    }

    $('#category_id').on('change', function() {
        var categoryId = $(this).val();
        renderAttributes(categoryId);
    });

    // Initial render if category is pre-selected
    var initialCategory = $('#category_id').val();
    if(initialCategory) {
        renderAttributes(initialCategory);
    }
});
