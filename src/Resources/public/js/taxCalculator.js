function taxCalculator (taxRate) {
    this.taxRate = taxRate;
    this.color = "red";
}

taxCalculator.prototype.getInfo = function() {
    return this.color + ' ' + this.taxRate + ' apple';
};

taxCalculator.prototype.sinIva = function(conIva) {
    var sinIva = conIva / this.taxRate;

    return sinIva.toFixed(2);
};

taxCalculator.prototype.conIva = function(sinIva) {
    var conIva = sinIva * this.taxRate;

    return conIva.toFixed(2);
};
