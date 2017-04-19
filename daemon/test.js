#!/usr/bin/env node

const spawn = require('child_process').spawn;
const fs    = require('fs');
var program = require('commander');
var helper  = require('./helper');
var rest    = require('./rabbitmq-rest/api');
var child;

rest.connect('localhost', 15672, 'guest', 'guest');

rest.get('/queues/portal', {}, function (response, body) {
    console.log('HEADERS: ' + JSON.stringify(response.headers));
    console.log('BODY: ' + JSON.stringify(body));
});