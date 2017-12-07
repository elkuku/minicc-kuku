module.exports = function (taxRate) {
    this.taxRate = 1 + taxRate / 100;

    this.sinIva = function(conIva) {
        var sinIva = conIva / this.taxRate;

        return sinIva.toFixed(2);
    };

    this.conIva = function(sinIva) {
        var conIva = sinIva * this.taxRate;

        return conIva.toFixed(2);
    };
};
