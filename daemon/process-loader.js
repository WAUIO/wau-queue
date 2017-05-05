const helper = require('./helper');
const trend  = require('trend');

const ACTION_STABLE = 'loadStable';
const ACTION_UP     = 'loadUp';
const ACTION_DOWN   = 'loadDown';
const ACTION_LOWEST = 'loadLowest';

var workers = [];
var logStatus = [];
var logAction = [];

Array.prototype.sum = function () {
    if (this.length === 0) return 0;
    return this.reduce(function (a, v) {
        return a + v;
    })
};
Array.prototype.avg = function () {
    return this.sum() / this.length;
};

function ProcessLoader(data) {
    this.data = data;
    this.status = {
        messages : 0,
        consumers: 0,
        rates    : []
    };
    this.load = {};
    this.analyze();
}

ProcessLoader.prototype = {

    analyze: function () {
        console.log('Analyzing...');

        this.status = this.data.map(function (queue) {
            return {
                messages : queue.messages_ready,
                consumers: queue.consumers,
                rates    : queue.messages_details.rate
            };
        }).reduce(function (result, value, key) {
            result.messages += parseInt(value.messages);
            result.consumers += parseInt(value.consumers);
            //result.rates = (parseFloat(value.rates) + parseFloat(result.rates)) / (key ? 2 : 1);
            result.rates.push(parseFloat(value.rates));

            return result;
        }, this.status);

        var date = new Date();
        logStatus.push({
            time:date.getTime(),
            data:this.status
        });

        if(logStatus.length > 10) {
            logStatus.shift();
        }
    }

    , decide: function (options) {
        if(this.status.messages === 0) {
            if(workers.length > 1) {
                return [1, ACTION_LOWEST];
            } else {
                return [1, ACTION_STABLE];
            }
        }

        var lin = helper.stats.linReg(logStatus.map(function(item, i){
            return [i, item.data.messages];
        }));

        console.log("REG >>", lin);
        /*
        if (lin.r2 <= 0.45) {
            // don't up nor down
            return ACTION_STABLE;
        }
        */

        if(lin.equation[0] > 0 || isNaN(lin.equation[0])) {

            if(options.prefetch && options.prefetch === workers.length){
                return [lin.r2, ACTION_STABLE];
            }

            // the trend is increasing or we are on begining
            return [lin.r2, ACTION_UP];
        } else {
            // calculate a forecast when messages will hit 0
            var eat = Math.abs(lin.equation[1] / lin.equation[0]);
            console.log("EAT>> ", helper.round(eat), options.eat);

            if(eat <= options.eat) {
                return [lin.r2, ACTION_DOWN];
            } else {
                if(options.prefetch && options.prefetch === workers.length){
                    return [lin.r2, ACTION_STABLE];
                }

                return [lin.r2, ACTION_UP];
            }
        }
    }

    , setLoader: function (name, callback) {
        this.load[name] = callback;

        return this;
    }

    , balance: function (options) {
        var action = this.decide(options);

        if(action[0] < 0.45) {
            action[1] = ACTION_STABLE;
        } else {
            logAction.push(action[1]);
        }

        if(logAction.length > 3) {
            logAction.shift();
        }

        // don't up or down load charge if request is not repetitive at least for 3 times
        if(logAction.length === 3) {
            var shouldChange = true;
            var i = 1;
            do {
                shouldChange &= logAction[i] === logAction[i - 1];
                i++;
            }while (i < logAction.length);

            if(!shouldChange) {
                action[1] = ACTION_STABLE;
            }
        }

        console.log('>> STATUS', this.status, {
            Action  : action[1],
            Rate    : helper.round(this.status.rates.avg(), 2),
            Workers : workers.length,
            //_Workers : workers.map(function(w){return w.pid;}),
            Interval: options.interval,
            Prefetch: options.prefetch
        });
        this.trigger(action[1]);
    }

    , trigger: function (name) {
        try {
            var callback = this.load[name];
            if (typeof callback === 'function') {
                callback.call(this, workers);
            }
        } catch (e) {
        }
    }

};

module.exports.balance = function (data, options, upCallback, downCallback) {
    var loader = new ProcessLoader(data);
    loader
        .setLoader(ACTION_UP, upCallback)
        .setLoader(ACTION_DOWN, downCallback)
        .setLoader(ACTION_LOWEST, function(workers){
            var i = 0;
            while (workers.length > 1 && i < 5) {
                downCallback.call(loader, workers);
                i++;
            }
        })
        .setLoader(ACTION_STABLE, function(){})
    ;
    
    loader.balance(options);
    
    return loader;
};

module.exports.workers = workers;