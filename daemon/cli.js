#!/usr/bin/env node

const _       = require('lodash-node');
const program = require('commander');
const spawn   = require('child_process').spawn;
const fs      = require('fs');
const helper  = require('./helper');
const rest    = require('./rabbitmq-rest/api');
var child;

rest.connect('localhost', 15672, 'guest', 'guest');

program
    .version('0.0.1')
;

const runLog = fs.openSync('./storages/logs/run.log', 'w');
const errLog = fs.openSync('./storages/logs/errors.log', 'w');

program
    .command('run <worker>')
    .description('Run a worker script, powered by php-cli')
    .option('-P, --prefetch <n>', 'Prefetch count', parseInt)
    .option('-U, --level-up <n>', 'Load up level', parseInt)
    .option('-D, --level-down <n>', 'Load down level', parseInt)
    .action(function (worker, options) {
        console.log('run "%s" using with options [Prefecth=%s, Level Up=%s, Level Down=%s]', worker, options.prefetch,
            options.levelUp, options.levelDown);

        var workers = [];

        function addWorker(workers){
            workers.push(
                spawn("/usr/bin/php", [worker], {
                    stdio: ['ignore', runLog, errLog]
                    //stdio: ['ignore', process.stdout, errLog]
                })
            );
        }

        addWorker(workers);

        if (options.prefetch) {
            setInterval(function () {

                console.log(">>>>>>>>> ", workers.length);

                rest.get('/queues', {}, function (response, body) {
                    if (body) {

                        var status = _.map(body, function (queue) {
                            return {
                                messages : queue.messages_ready,
                                consumers: queue.consumers,
                                rates    : queue.messages_details.rate
                            };
                        }).reduce(function (result, value, key) {
                            result.messages += parseInt(value.messages);
                            result.consumers += parseInt(value.consumers);
                            result.rates = (parseFloat(value.rates) + parseFloat(result.rates)) / (key ? 2 : 1);

                            return result;
                        }, {
                            messages : 0,
                            consumers: 0,
                            rates    : 0
                        });
                        console.log("<<<<<<<<< STATUS >>>>>>>>> ", status);


                        if (workers.length < options.prefetch) {
                            addWorker(workers);
                        }

                    } else {
                        //error
                        console.error('Error')
                    }
                });

            }, 5000);

        }


    }).on('--help', function () {
    console.log('  Examples:');
    console.log();
    console.log('    $ deploy exec sequential');
    console.log('    $ deploy exec async');
    console.log();
})
;

program.parse(process.argv);