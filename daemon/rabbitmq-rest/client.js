'use strict';

var http       = require('http');
var fs         = require('fs');
var path       = require('path');
var urlencoded = require('form-urlencoded');

/**
 * Basic client
 *
 * @param method
 * @param uri
 * @param data
 * @constructor
 */
function HttpClient(method, uri, data) {
    this.method   = method;
    this.uri      = uri;
    this.data     = data;
    this.$context = null;
    this.oauth = {};
}

HttpClient.prototype = {
    /**
     * Init the client request
     */
    init: function(context){
        this.$context = context;

        return this;
    }

    /**
     * Parse the url and define all compoenents as host, port, protocol
     *
     * @param url
     * @returns {{}}
     */
    , parseUrl: function(url){
        var parsed = (/(https?):\/\/([^/]+)(\/.*)?/ig).exec(url);

        if(!parsed) {
            throw Error("[API Client] wrong url", url);
        }

        var result = {};

        result.protocol = parsed[1];
        result.host = parsed[2].split(':')[0];
        result.port = parsed[2].split(':')[1] || 80;
        result.path = parsed[3] || '';

        return result;
    }

    /**
     * Send the request and execute a callback of the promise
     *
     * @param callback
     * @returns {HttpClient}
     */
    , send: function (callback) {
        var url = this.parseUrl(this.uri);

        var options = {
            host: url.host,
            port: url.port,
            path: url.path,
            headers: {
                'user-agent': this.$context.NAME,
                'Authorization': 'Basic ' + new Buffer(this.$context.AUTH_USER + ':' + this.$context.AUTH_PASSWORD).toString('base64')
            },
            method: this.method
        };

        var context = this;

        var request = http.request(options, function(res) {
            res.setEncoding('utf8');
            res.on('data', function (body) {
                callback.call(context, res, JSON.parse(body));
            });
        });

        request.on('error', function (e) {
            console.error('Client API Error: ', e.message, e);
        });

        // process the request
        if(context.method !== 'GET') {
            request.write(urlencoded(context.data));
        }

        request.end();

        return this;
    }
};

/**
 * Implement method
 * @param method
 * @returns {Function}
 */
function createHttpClient(method) {
    return function () {
        return function (path, data, callback) {
            var client = new HttpClient(method, this.url(path), data);

            return client.init(this).send(callback);
        }
    }
}

//module.exports.HttpClient       = HttpClient;
module.exports.createHttpClient = createHttpClient;