import {v4 as uuidv4} from 'uuid';


// window.addEventListener("DOMContentLoaded", () => {

// const showFormButtons = document.querySelectorAll(`[id^="drclubs-show-register-form-"]`);
// showFormButtons.forEach((el) => {
//     el.addEventListener('click', (e) => {
//         createRegisterForm(e);
//     })
// });

// add register button on checkout section

let registerButtonInCheckout = document.getElementById('drclubs-create-register-form');


if (registerButtonInCheckout) {

    registerButtonInCheckout.addEventListener('click', (e) => {
        e.preventDefault();
        createRegisterForm(e.target);

    });
    //end add register button on checkout section
}

window.createRegisterForm = (el) => {
    const button = el;


    if (!button) return;
    const dataset = button.dataset;
    if (!dataset) return;


    document.querySelectorAll(`[id^="drclubs-register-form-"]`).forEach((e) => e.remove());

    // const wrapper = document.createElement('div');
    // wrapper.id='';
    // wrapper.style.cssText = 'position : absolute;background:#11111111';
    // button.append(wrapper);

    button.insertAdjacentHTML('beforebegin', registerFormCodeMaker(dataset));


};

export const registerFormCodeMaker = function (dataset, styles = {}, dstyles = {
    'width': '50%',
    'min-width': '25rem',
    'position': 'absolute'
}) {
    const url = dataset.url;
    const id = dataset.user_id;
    const action = dataset.action;
    const nonce = dataset.nonce;
    const referrer = dataset.referrer;


    return `
    
  <div id="drclubs-register-form-${id}" class="dc-register-form" style="position: ${styles['position'] ? styles['position'] : dstyles['position']};min-width: ${styles['min-width'] ? styles['min-width'] : dstyles['min-width']};width: ${styles['width'] ? styles['width'] : dstyles['width']}; top: 0;left: 0;right: 0;margin-left: auto;margin-right: auto">
    <div   class="dc-register-form-container">
        <a href="#" id="dc-register-form-close" onclick="document.querySelector('#drclubs-register-form-${id}').remove() " class="close  ">&times;</a>

        <h2>ثبت نام در باشگاه مشتریان</h2>
        <div class="divider"></div>
      
        <label for="Fname">نام</label>
        <input autocomplete="Fname" type="text" id="Fname" name="Fname" style="text-align: right">
       
         <label for="Lname"> نام خانوادگی</label>
        <input autocomplete="Lname" type="text" id="Lname" name="Lname"  style="text-align: right">
      
       <label for="NationalCode"> کد ملی</label>
        <input  class="widefat  " autocomplete="NationalCode" type="number" id="NationalCode" name="NationalCode">
         
          <label for="PhoneNumber"> شماره تماس</label>
        <input  class="widefat" autocomplete="PhoneNumber" type="number" id="PhoneNumber" name="PhoneNumber">
            
          <label class="d-none" for="CardNumber"> شماره کارت</label>
        <input  class="widefat d-none" autocomplete="CardNumber" type="number" id="CardNumber" name="CardNumber">
       <!---->
       <label for="" style="margin-top: 1.5rem"> 
       تاریخ تولد
       <span>(مثال: 1375/05/11)</span>
       </label>
       
       <div style="display: flex; align-items: baseline "> 
        <input class="widefat" autocomplete="d" type="number" id="d" name="d" maxlength="2" placeholder="روز">
        <span class="mx-1"> / </span>
        <input class="widefat" autocomplete="m" type="number" id="m" name="m" maxlength="2" placeholder="ماه">
        <span class="mx-1"> / </span>
        <input class="widefat" autocomplete="y" type="number" id="y" name="y" maxlength="4" placeholder="سال">
        </div>
       
       
        <div class="radio-group  ">

        <input id="Gender-0" type="radio" name="Gender" value="${0}" checked/> 
        <label for="Gender-0">        مرد  </label>
        <input id="Gender-1" type="radio" name="Gender" value="${1}" />
         <label for="Gender-1">        زن </label> 
 
      </div>
      
      
        <!--<label for="password">گذرواژه</label>-->
        <!--<input autocomplete="current-password" type="password" id="password" name="password">-->
       
       <div class="loader-container    "><div class="loader"><div></div><div></div><div></div><div></div></div></div> 
       
        <p class="status" data-message="status"></p>
        <input type="button" onclick="sendRegisterData(document.querySelector('#drclubs-register-form-${id}') )" value="ثبت نام" name="submit" class="submit_button">
      
        <input type="button"   onclick="document.querySelector('#drclubs-register-form-${id}').remove() " value="لغو" name="close" >

         <input type="hidden" id="drclubs-register-user-${id}-nonce" name="drclubs-register-user-${id}-nonce" value="${nonce}">
      <input type="hidden" name="_wp_http_referer" value="${referrer}">
        <input type="hidden" name="action" value="${action}">
      <input type="hidden" name="user_id" value="${id}">
      <input type="hidden" name="url" value="${url}">
        
    </div>
</div>
    
    `
};

window.sendRegisterData = (form) => {

    const resetMessages = () => {
        status.innerHTML = "";
        status.classList.remove("success", "error");

        form.querySelector('[type="button"]').value = "ثبت کاربر";
        form.querySelector('[type="button"]').disabled = false;
    };
    const toggleErrorFields = (errors, form, state = 'on') => {

        if (errors && errors.response && errors.response.data)
            errors = errors.response.data;
        // if (errors)
        //     console.log(errors);

        if (state === 'on')
            if (errors && errors.message && typeof  errors.message !== 'string') {
                for (let id in errors.message) {
                    if (id !== 'BornDate') {

                        let el = form.querySelector('#' + id);
                        if (el)
                            el.classList.add('error');

                    } else
                        form.querySelectorAll('#d,#m,#y').forEach(el => {
                            el.classList.add('error');

                        });
                    status.innerHTML = translate(typeof errors.message[id] !== 'string' ? errors.message[id][0] : errors.message[id]);

                }
            } else {
                status.innerHTML = errors.message;


                let tmp = translate(errors.message, 'fa', true);


                if (tmp) {

                    let el = form.querySelector('#' + tmp);
                    if (el)
                        el.classList.add('error');
                }

                if (errors && errors.message && typeof  errors.message === 'string' && (errors.message === 'شماره تماس تکراری است' || errors.message === 'این کاربر از قبل در باشگاه مشتریان عضو شده است.'))
                    window.location.reload();
            }
        else {
            form.querySelectorAll('input').forEach(el => {
                el.classList.remove('error');

            });
            status.innerHTML = '';
        }
    };

    const loader = form.querySelector('.loader-container');
    const status = form.querySelector('.status');

    const url = form.querySelector('input[name="url"]').value;
    const id = form.querySelector('input[name="user_id"]').value;
    const action = form.querySelector('input[name="action"]').value;
    const nonce = form.querySelector('input[name="drclubs-register-user-' + id + '-nonce"]').value;
    const referrer = form.querySelector('input[name="_wp_http_referer"]').value;

    resetMessages();
    toggleErrorFields(null, form, 'off');
    loader.classList.add('show');
    let params = new FormData();


    params.append('Id', uuidv4());
    params.append('user_id', id);
    params.append('Fname', form.querySelector('#Fname').value);
    params.append('Lname', form.querySelector('#Lname').value);
    if (form.querySelector('#NationalCode').value)
        params.append('NationalCode', form.querySelector('#NationalCode').value);
    params.append('Gender', form.querySelector('input[name="Gender"]:checked').value);
    if (form.querySelector('#y').value && form.querySelector('#m').value && form.querySelector('#d').value)
        params.append('BornDate', `${form.querySelector('#y').value.zeroPad(4)}/${form.querySelector('#m').value.zeroPad()}/${form.querySelector('#d').value.zeroPad()}`);
    params.append('PhoneNumber', form.querySelector('#PhoneNumber').value);
    if (form.querySelector('#CardNumber').value)
        params.append('CardNumber', form.querySelector('#CardNumber').value);
    params.append('CardIsCredit', false);
    params.append('Enabled', true);

    params.append('nonce', nonce);
    params.append('action', action);
    params.append('referrer', referrer);
    params.append('nonce_name', `drclubs-register-form-${id}-nonce`);


    // let params = new URLSearchParams(new FormData(form));

    form.querySelector('[type="button"]').value = "...در حال ارسال";

    form.querySelector('[type="button"]').disabled = true;
    axios.post(url, params, {
        // headers: {

        //
        // },
    }).then((response) => {
        console.log(response.data);

        if (response == 0 || !response.data || response.data.status === 'error' || !response.status) {
            resetMessages();
            status.classList.add('error');
            loader.classList.remove('show');
            toggleErrorFields(response.data, form, 'on');
            return;
        }

        status.innerHTML = 'با موفقیت ثبت شد!';

        status.classList.add('success');
        // form.reset();
        loader.classList.remove('show');
        resetMessages();
        window.location.reload();
    }).catch((error) => {
        // console.log('ERROR CATCHED:' + "\n" + error);

        resetMessages();
        status.classList.add('error');
        status.innerHTML = error.message;
        loader.classList.remove('show');
        toggleErrorFields(error, form, 'on');
    });


};
const translate = (str, from = 'en', getFieldId = false) => {

    const en = ['BornDate', 'PhoneNumber', 'NationalCode', 'CardNumber', 'Id'];
    const fa = ['تاریخ تولد', 'شماره تماس', 'کد ملی', 'شماره کارت', 'شناسه مشتری'];

    if (getFieldId) {
        for (let idx in fa)
            if (str && str.indexOf(fa[idx]) > -1)
                return en[idx];
        return '';
    }

    if (from === 'en')
        for (let idx in en)
            str = str.replace(new RegExp("\\b(" + en[idx] + ")\\b", 'g'), fa[idx]);

    return str;
};


// });




