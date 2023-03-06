window.axios = require('axios');
window.bootstrap = require('bootstrap');

import './connect_api';
import './register_form';
import './charts';
import './actions_api';
import {makeTabs} from './tabs';

import {createApp} from 'vue';
import CustomerLogs from './components/CustomerLogs.vue';
import CustomerUi from './components/CustomerUi.vue';
import LotteryUi from './components/LotteryUi.vue';


export let loadVue = () => {
    window.drclubsApp = createApp({

        mode: 'production',
        config: {devtools: true,},
        components: {
            // CustomerLogs,
            CustomerUi,

        },

    });
    drclubsApp.mount('#drclubs-wrapper');

};


window.addEventListener("DOMContentLoaded", function () {

    makeTabs();
    // loadVue();

    // PR.prettyPrint(); //prettify export section

});

//media uploader in widget MediaWidget.php

// jQuery(document).ready(function ($) {
//     $(document).on('click', '.js-image-upload', function (e) {
//         e.preventDefault();
//         let $button = $(this);
//         let file_frame = wp.media.frames.file_frame = wp.media({
//             title: 'انتخاب و آپلود تصویر',
//             library: {
//                 type: 'image'
//             },
//             button: {
//                 text: 'انتخاب'
//             },
//             multiple: false,
//         });
//
//         file_frame.on('select', function () {
//             let attachment = file_frame.state().get('selection').first().toJSON();
//             $button.siblings('.image-upload').val(attachment.url);
//         });
//
//         file_frame.open();
//
//     });
// });

String.prototype.zeroPad = function (length) {
    length = length || 2; // defaults to 2 if no parameter is passed
    return (new Array(length).join('0') + this).slice(length * -1);
};
window.f2e = function (str) {
    if (!str) return str;
    str = str.toString();
    let eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    let per = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    for (let i in per) {
//                    str = str.replaceAll(eng[i], per[i]);
        let re = new RegExp(per[i], "g");
        str = str.replace(re, eng[i]);
    }
    return str;


};
window.e2f = function (str) {
    let eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    let per = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    if (!str) return str;
    if (Array.isArray(str)) {
        for (let idx in str) {
            for (let i in per) {
//                    str = str.replaceAll(eng[i], per[i]);
                let re = new RegExp(eng[i], "g");
                str[idx] = str[idx].replace(re, per[i]);
            }
        }
        return str;
    }
    str = str.toString();

    for (let i in per) {
//                    str = str.replaceAll(eng[i], per[i]);
        let re = new RegExp(eng[i], "g");
        str = str.replace(re, per[i]);
    }
    return str;


};