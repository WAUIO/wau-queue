'use strict';

var path             = require('path');
var createHttpClient = require('./client').createHttpClient;

var API = {
    NAME: 'wau-queue-js/v0.0.1'

    /**
     * Base url of the API
     */
    , API_URL: ''

    /**
     * The public key provided from API
     */
    , AUTH_USER: ''

    /**
     * Secret key provided from API
     */
    , AUTH_PASSWORD: ''

    /**
     * Library path
     */
    , PATH: __dirname

    , SECURE: false

    /**
     * Connect to API Resources Server
     *
     * @param host
     * @param port
     * @param user
     * @param password
     * @returns {API}
     */
    , connect: function (host, port, user, password) {
        this.API_HOST      = host;
        this.API_PORT      = port;
        this.AUTH_USER     = user || '';
        this.AUTH_PASSWORD = password || '';

        return this;
    }

    , secure: function(){
        this.SECURE = true;

        return this;
    }

    , __setBaseUrl: function(){
        if(this.API_URL === '') {
            var url = 'http';

            if(this.SECURE){
                url += 's';
            }

            url += '://';
            url += this.API_HOST;
            if(this.API_PORT !== ''){
                url += ':' + this.API_PORT;
            }
            url += '/api';

            this.API_URL = url;
        }

        return this;
    }

    , push: function (name, value) {
        this[name] = value;

        return this;
    }

    , pull: function (name, defaultValue) {
        return this[name] || defaultValue;
    }

    , url: function (path) {
        var url = this.__setBaseUrl().API_URL;
        if (path.match(/^\//)) {
            url += path;
        } else {
            url += "/" + path;
        }

        return url;
    }
};

Object.defineProperty(API, 'get', {
    configurable: true,
    enumerable  : true,
    get         : createHttpClient('GET')
});

Object.defineProperty(API, 'post', {
    configurable: true,
    enumerable  : true,
    get         : createHttpClient('POST')
});

Object.defineProperty(API, 'put', {
    configurable: true,
    enumerable  : true,
    get         : createHttpClient('PUT')
});

Object.defineProperty(API, 'delete', {
    configurable: true,
    enumerable  : true,
    get         : createHttpClient('DELETE')
});

module.exports = API;