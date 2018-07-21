const $ = require('jquery')

window.$ = $

module.exports = function () {
    this.goToPage = function (e, page) {
        var form = e.closest('form');

        form.find('input[name="paginatorOptions[page]"]').val(page);

        form.submit();
    };

    this.setOrdering = function (e, order, orderDir) {
        var form = e.closest("form");

        form.find('input[name="paginatorOptions[order]"]').val(order);
        form.find('input[name="paginatorOptions[orderDir]"]').val(orderDir);

        form.submit();
    };

    this.resetAndSubmit = function (e) {
        var form = e.closest("form");

        form.find('input[name="paginatorOptions[page]"]').val('');
        form.find('input[name="paginatorOptions[order]"]').val('');
        form.find('input[name="paginatorOptions[orderDir]"]').val('');

        form.submit();
    }
};
