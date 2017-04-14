#!/usr/bin/env node

const spawn = require('child_process').spawn;
const fs    = require('fs');
var program = require('commander');
var helper  = require('./helper');
var rest  = require('./rest/api');
var child;

rest.connect('localhost', 15672, 'guest', 'guest');

rest.get('/queues', {}, function(response, body){
    console.log('HEADERS: ' + JSON.stringify(response.headers));
    //console.log(body);
});