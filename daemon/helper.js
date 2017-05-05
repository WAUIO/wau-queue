var sanitizeBash = function (text) {
    var rg = /[\u001b\u009b][[()#;?]*(?:[0-9]{1,4}(?:;[0-9]{0,4})*)?[0-9A-ORZcf-nqry=><]/g;

    return text.replace(rg, '');
};

var round = function (number, decimal) {
    if (!decimal) decimal = 0;

    return Math.round(number * Math.pow(10, decimal)) / Math.pow(10, decimal);
};

/**
 * taken from https://github.com/Tom-Alexander/regression-js/blob/master/src/regression.js
 * `
 * @type {Object}
 */
var Stats = Object.create({
    determinationCoefficient: function (observations, predictions) {
        var sum  = observations.reduce(function (accum, observation) {
            return accum + observation[1];
        }, 0);
        var mean = sum / observations.length;

        // Sum of squares of differences from the mean in the dependent variable
        var ssyy = observations.reduce(function (accum, observation) {
            var diff = observation[1] - mean;
            return accum + diff * diff;
        }, 0);

        // Sum of squares of resudulals
        var sse = observations.reduce(function (accum, observation, ix) {
            var prediction = predictions[ix];
            var resid      = observation[1] - prediction[1];
            return accum + resid * resid;
        }, 0);

        // If ssyy is zero, r^2 is meaningless, so NaN is an appropriate answer.
        return 1 - (sse / ssyy);
    }

    , linReg: function (data, _order, options) {
        options = Object.assign(true, {
            precision:2
        }, options);

        var sum = [0, 0, 0, 0, 0];
        var results;
        var gradient;
        var intercept;
        var len = data.length;

        for (var n = 0; n < len; n++) {
            if (data[n][1] !== null) {
                sum[0] += data[n][0];
                sum[1] += data[n][1];
                sum[2] += data[n][0] * data[n][0];
                sum[3] += data[n][0] * data[n][1];
                sum[4] += data[n][1] * data[n][1];
            }
        }

        gradient  = (len * sum[3] - sum[0] * sum[1]) / (len * sum[2] - sum[0] * sum[0]);
        intercept = (sum[1] / len) - (gradient * sum[0]) / len;

        results = data.map(function (xyPair) {
            var x = xyPair[0];
            return [x, gradient * x + intercept];
        });

        return {
            r2      : this.determinationCoefficient(data, results),
            equation: [gradient, intercept],
            points  : results,
            string  : 'y = ' + round(gradient, options.precision) + 'x + ' + round(intercept, options.precision)
        };
    }
});


exports = module.exports = {
    /**
     * @param text
     */
    sanitizeBash: sanitizeBash

    /**
     *
     * @param number
     * @param decimal
     * @returns {number}
     */
    , round: round

    , stats: Stats
};