var paginator = {
    goToPage: function (e, page) {
        var form = e.closest('form');

        form.find('input[name="paginatorOptions[page]"]').val(page);

        form.submit();
    },
    setOrdering: function (e, order, orderDir) {
        var form = e.closest("form");

        form.find('input[name="paginatorOptions[order]"]').val(order);
        form.find('input[name="paginatorOptions[orderDir]"]').val(orderDir);

        form.submit();
    },
    resetAndSubmit: function (e) {
        var form = e.closest("form");

        form.find('input[name="paginatorOptions[page]"]').val('');
        form.find('input[name="paginatorOptions[order]"]').val('');
        form.find('input[name="paginatorOptions[orderDir]"]').val('');

        form.submit();
    }
};
