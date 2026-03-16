// resources/js/bootstrap.js
import '../css/app.css';
import _ from 'lodash';

window._ = _;

try {
    window.axios = require('axios');
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
} catch (e) {
    console.error(e);
}