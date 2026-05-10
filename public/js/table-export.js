(function () {
    function currentTableKey() {
        const config = window.admin360TableExport || {};
        const exportUrl = config.url || '';
        const exportPath = exportUrl.split('?')[0].replace(/\/exports\/tables\/__TABLE__$/, '');
        const appBase = exportPath ? new URL(exportPath, window.location.origin).pathname.replace(/^\/+|\/+$/g, '') : '';
        let path = window.location.pathname.replace(/^\/+|\/+$/g, '');

        if (appBase && path.indexOf(appBase + '/') === 0) {
            path = path.substring(appBase.length + 1);
        }

        return (config.paths || {})[path] || null;
    }

    function addExportButtons() {
        if (!window.jQuery || !jQuery.fn.DataTable || !jQuery.fn.dataTable) {
            return;
        }

        const key = currentTableKey();
        const exportBaseUrl = window.admin360TableExport && window.admin360TableExport.url;

        if (!key || !exportBaseUrl) {
            return;
        }

        jQuery(jQuery.fn.dataTable.tables()).each(function () {
            const table = jQuery(this).DataTable();

            if (table.settings()[0]._admin360ExportButtonAdded) {
                return;
            }

            table.settings()[0]._admin360ExportButtonAdded = true;

            const action = function () {
                const params = table.ajax && table.ajax.params ? table.ajax.params() : {};
                delete params.draw;
                delete params.start;
                delete params.length;

                const url = exportBaseUrl.replace('__TABLE__', encodeURIComponent(key));
                const query = jQuery.param(params);
                window.location.href = query ? url + '?' + query : url;
            };

            if (table.button && jQuery.fn.dataTable.Buttons) {
                table.button().add(0, {
                    text: '<i class="fa fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-sm btn-alt-success',
                    action: action
                });
                return;
            }

            const button = jQuery('<button type="button" class="btn btn-sm btn-alt-success ms-2"><i class="fa fa-file-excel me-1"></i> Excel</button>');
            button.on('click', action);
            jQuery(this).closest('.dataTables_wrapper').find('.dataTables_filter').prepend(button);
        });
    }

    jQuery(function () {
        setTimeout(addExportButtons, 200);
    });
})();
