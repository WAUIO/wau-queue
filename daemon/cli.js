#!/usr/bin/env node

const program       = require('commander');
const spawn         = require('child_process').spawn;
const fs            = require('fs');
const helper        = require('./helper');
const rest          = require('./rabbitmq-rest/api');
const processLoader = require('./process-loader');
var child;

var config = fs.readFileSync('./.config.json', {'encoding':'utf8'});

rest.connect(config.host, config.port.api, config.user, config.password).secure();
const vhost = config.vhost;

program
    .version('0.0.1')
;

const runLog = fs.openSync('./storages/logs/run.log', 'w');
const errLog = fs.openSync('./storages/logs/errors.log', 'w');

program
    .command('run <worker>')
    .description('Run a worker script, powered by php-cli')
    .option('-l, --interval <n>', 'Loop interval (in sec)', parseInt)
    .option('-P, --prefetch <n>', 'Prefetch count', parseInt)
    .option('-e, --eat <n>', 'EAT in seconds', parseInt)
    .action(function (worker, options) {

        // default value
        if (!options.interval)
            options.interval = 1;

        if (!options.eat)
            options.eat = 30;

        /*
        if (!options.prefetch)
            options.prefetch = 10;
        */

        console.log('run "%s" using with options [Prefecth=%s, EAT=%s secs]', worker, options.prefetch, options.eat);

        function addWorker(workers) {
            workers.push(
                spawn("/usr/bin/php", [worker], {
                    //stdio: ['ignore', runLog, errLog]
                    stdio: ['ignore', process.stdout, errLog]
                })
            );
        }

        function subWorker(workers) {
            if(workers.length > 1) {
                //[*, x, *, *, ....]
                var temp = workers.splice(1);
                // kill the process
                temp[0].kill();

                // remove the worker from list
                temp.shift();
                temp.forEach(function(w){
                    workers.push(w);
                });
                console.log("loadind down...", workers.length);
            }
        }

        addWorker(processLoader.workers);

        setInterval(function () {

            try {
                rest.get('/queues/' + vhost, {}, function (response, body) {
                    if (body) {
                        processLoader.balance(body, options, addWorker, subWorker);
                    } else {
                        //error
                        console.error('** ERROR **')
                    }
                });

                console.log(">> Length", processLoader.workers.length);
            } catch (e) {
                console.error(e);
            }

        }, parseInt(options.interval) * 1000);

    }).on('--help', function () {
    console.log('  Examples:');
    console.log();
    console.log('    $ cli run /path/to/script.php');
    console.log('    $ cli run /path/to/script.php -P 10 -U 20 -D 30');
    console.log();
})
;

program.parse(process.argv);