import {registerFormCodeMaker} from './register_form';

// import CustomerLogs from './components/CustomerLogs.vue';
import {loadVue} from './app';
import {makeTabs} from './tabs';
import {makeLottery} from './lottery';


document.addEventListener('DOMContentLoaded', (e) => {
        const scr = document.getElementById('drclubs-customer-ui-js');
        const data = scr.dataset;
        const vueContainer = document.createElement('div');
        vueContainer.setAttribute("id", "drclubs-wrapper");
        // vueContainer.setAttribute("onclick", `
        //   event.stopPropagation();
        //  if(!event.target.id  ||  event.target.id != 'drclubs-wrapper' ) return;
        //   document.getElementById('drclubs-wrapper').style.display="none";
        // `);
        // vueContainer.style.cssText = "display:none;transition: all ease .3s;position:fixed;width:100%;height:100%;overflow-y:scroll;left:0;right:0:bottom:0;top:0;background-color:rgba(0,0,0,.8);z-index:99999999;text-align:center;";

        //vue component
        const ui = document.createElement('customer-ui');
        ui.setAttribute("data-url", data.ajax_url);
        ui.setAttribute("asset-url", data.asset_url);
        vueContainer.appendChild(ui);
        document.body.appendChild(vueContainer);

        loadVue();

        return;
        // window.app.component('customer-logs', CustomerLogs);


        const script = document.getElementById('drclubs-customer-ui-js');

        if (!script) return;

        const dataset = script.dataset;
        const positions = JSON.parse(dataset.positions);
        const margins = JSON.parse(dataset.margins);
        const login_url = dataset.login_url;
        const register_url = dataset.register_url;
        const user_id = dataset.user_id;
        const user = dataset.user ? JSON.parse(dataset.user.replace(/\*~/g, " ")) : null;
        const account = JSON.parse(dataset.account);
        const scores = JSON.parse(dataset.scores);
        const limits = JSON.parse(dataset.limits);
        const transactions = JSON.parse(dataset.transactions.replace(/\*/g, " "));
        const log_types = JSON.parse(dataset.log_types.replace(/\*/g, " "));

        const unit = dataset.lottery_unit;
        const level = dataset.level;
        const level_name = dataset.level_name;
        const currency = dataset.currency;


        const button = document.createElement('div');
        button.innerHTML = 'باشگاه مشتریان';
        button.style.cssText += 'cursor:pointer;font-family:Tanha,serif;position:fixed;border-radius:1rem;padding:.8rem;z-index:1000000000;color:white;background-color:red;';
        button.style.cssText += ' -webkit-touch-callout: none;  ' +
            '  -webkit-user-select: none; ' +
            '  -khtml-user-select: none;  ' +
            '  -moz-user-select: none;  ' +
            '  -ms-user-select: none;  ' +
            '  user-select: none;';

        for (let i in margins)
            button.style.cssText += margins[i].style.replace('$', margins[i].value);

        button.style.cssText += positions[positions['selected']].style.replace('$', ' ');

        const wrapper = document.createElement('div');
        wrapper.setAttribute("id", "drclubs-wrapper");
        wrapper.setAttribute("onclick", `
        event.stopPropagation();
       if(!event.target.id  ||  event.target.id != 'drclubs-wrapper' ) return;
        document.getElementById('drclubs-wrapper').style.display="none";
      `);
        wrapper.style.cssText = "display:none;transition: all ease .3s;position:fixed;width:100%;height:100%;overflow-y:scroll;left:0;right:0:bottom:0;top:0;background-color:rgba(0,0,0,.8);z-index:99999999;text-align:center;";


        const panel = ` 
   
 <div class="  mx-auto" style=" max-width:50rem;margin-top:2rem; background-color: lightgrey;display: flex;flex-direction: column; font-family: Tanha,serif;border-radius: 1rem;overflow: hidden;    "> 
 
 <div class="panel-header px-3" style="display: flex;justify-items: ; align-items: center; background-color: red;color:white;text-align: right;">
 <div>
    <img class="mx-2" src="${dataset.assets_path + 'img/logo.png'}" alt="" width="32" height="32">
    <span style="font-weight: bold">پنل باشگاه مشتریان</span>
    <small>( قدرت گرفته از<span><a class="text-decoration-none " style="color:lightgrey;" target="_blank" href="https://drclubs.ir"> دکتر کلابز </a></span> ) </small>
 </div>
    <span onclick="document.getElementById( 'drclubs-wrapper' ).style.display='none'" class="dashicons dashicons-no-alt scale-on-hover" style="cursor:pointer;margin-right: auto;color:white;"> </span>
 
 </div>
 
 <div class="p-1"> 
    <ul class="nav dc-nav-tabs " style="font-family: Tanha,serif">
        <li class="${user != 0 && user_id != 0 ? 'active' : ''}"><a href="#tab-1">اطلاعات کاربری</a></li>
        <li class="${user == 0 || user_id == 0 ? 'active' : ''}"><a href="#tab-2">امتیاز گیری</a></li>
        <li class=""><a href="#tab-3">گزارشات سیستم</a></li>

    </ul>

    <div class="dc-tab-content" style="text-align: right">
        <div id="tab-1" class="dc-tab-pane p-1 ${user != 0 && user_id != 0 ? 'active' : ''}  ">
       ` + (getUserLoginRegister() ||

                `
            <div    style=" display: flex;  flex-wrap: wrap;align-items: center ; justify-content: center "> 
                <img   src="${dataset.assets_path + (level ? `img/level${level}.png` : 'img/user.png')}" alt="${level_name}"  style=" max-height:150px;display: inline-block;  "  />
                <!--<div style="border-left: 1px solid red;display: inline-block;height: 80% "></div>-->
                <div style=" vertical-align: middle; ;color:black;margin: 1rem;display: inline-block;  ">
                    <div> 
                        <span style="color:red" class="dashicons dashicons-tag"></span>
                        <span> ${user.Fname ? user.Fname : ''} </span>
                        <span> ${user.Lname ? user.Lname : ''} </span>
                        <span> ${user.Lname && user.Fname ? '' : 'نام: نامشخص'} </span>
                    </div>
                    <div>
                         <span style="color:red" class="dashicons dashicons-calendar-alt"></span>
                        <span>${user.BornDate ? user.BornDate : 'تاریخ تولد: نامشخص'}</span>
                    
                    </div>
                    <div>
                         <span style="color:red" class="dashicons dashicons-id"></span>
                        <span>${user.NationalCode ? user.NationalCode : 'کد ملی: نامشخص'}</span>
                    
                    </div>
                    <div style="display:${user.CardNumber ? 'block' : 'none'}">
                         <span style="color:red" class="dashicons dashicons-feedback    "></span>
                        <span>${user.CardNumber ? user.CardNumber : 'شماره کارت: نامشخص'}</span>
                    
                    </div>
                </div>
            </div> 
            
            <div class="row g-2"   style="   "> 
                <div class="col-sm-6" > 
                    <div style=" border: 1px solid;border-radius: .5rem;  text-align: center;" >
                        <div>کیف پول</div>
                        <div>${(account.balance != null ? (account.balance + ' ' + currency) : '') || '<span class="dashicons dashicons-info"></span>'}</div>
                    </div>          
                </div>
                <div class="col-sm-6" >           
                  <div class=" " style=" 1;border: 1px solid;border-radius: .5rem;  text-align: center;" >
                    <div>تعداد خرید</div>
                    <div>${account.purchaseCount != null ? account.purchaseCount : '' || '<span class="dashicons dashicons-info"></span>'}</div>
                  </div>
                </div>
                <div class="col-sm-6" > 
                    <div class=" " >         
                      <div class=" " style="border: 1px solid;border-radius: .5rem;  text-align: center;" >
                        <div>مجموع خرید</div>
                        <div>${(account.purchaseVolume != null ? (account.purchaseVolume + ' ' + currency) : '') || '<span class="dashicons dashicons-info"></span>'}</div>
                    </div>
                </div>
                </div>
                <div class="col-sm-6" > 
                  <div class=" " style="border: 1px solid;border-radius: .5rem;  text-align: center;" >
                        <div>امتیاز</div>
                        <div>${(account.score != null ? Math.floor(account.score).toString() : '0') || '<span class="dashicons dashicons-info"></span>'}</div>
                 </div>
               </div>
          
      
          </div>` +

                (transactions && transactions.length > 0 ?
                    (` <div class="text-center m-2 font-weight-bold text-dark">تراکنش های اخیر</div>
          <div class="table-responsive text-center">
            <table class="table table-striped">
                <thead>
                  <tr>
                    <th style="${!('amount' in transactions[0]) ? 'display:none;' : ''}" >${'خرید (ریال)'}</th>
                    <th style="${!('score' in transactions[0]) ? 'display:none;' : ''}">${'امتیاز'}</th>
                    <th style="${!('balance' in transactions[0]) ? 'display:none;' : ''}">${'از کیف پول (ریال)'}</th>
                    <th style="${!('dateTime' in transactions[0]) ? 'display:none;' : ''}">${'زمان'}</th>
                  </tr>
                </thead>
              <tbody>` +
                        createTableRows()
                        + `
            </tbody>
            </table>
            </div>`) : ``)

            )
            +
            `
        </div>

        <div id="tab-2" class="dc-tab-pane p-1  ${user == 0 || user_id == 0 ? 'active' : ''}  ">
`
            + (getUserLoginRegister() +

                `
        <div style="text-align: center">
            ${commentScoreIsAvailable() ? `<div style="font-family: Tanha,serif;"> <span> ثبت نظر برای محصولات</span>: <span> ${scores.comment} <span> امتیاز </span> ( ${limits && limits.comment ? `<span>هر ${limits.comment} ساعت</span>` : ''} ) </span> </div>` : ''}
            ${lotteryIsAvailable() ? `<div style="font-family: Tanha,serif;"> <span> چرخاندن گردونه شانس</span>  </span> ( ${limits && limits.lottery ? `<span>هر ${limits.lottery} ساعت</span>` : ''} ) </span> </div>` : ''}
            ${!commentScoreIsAvailable() && !lotteryIsAvailable() ? '<span>در حال حاضر روش امتیاز گیری تعیین نشده است</span>' : ''}
      
             ${lotteryIsAvailable() ? showLottery() : ''}
        </div>
        `

            )
            +
            `
        </div>

        <div id="tab-3" class="dc-tab-pane p-1  ">
             ` + (getUserLoginRegister() ||


                `
            <div>
              <customer-logs
                log-types='${dataset.log_types}'
               log-link="${dataset.ajax_url}" parent-params=${JSON.stringify({
                    action: dataset.drclubs_log_action,
                    nonce: dataset.log_nonce,
                    nonce_action: dataset.log_nonce_action,
                    user_id: user_id
                })}></customer-logs>
   
            
            </div> 
             `

            )
            + `
        </div>
    
    </div>
    
   </div>
    
    
    </div>
    
    `;

        function createTableRows() {
            let p = '';
            for (let i in transactions)
                p +=

                    `<tr>
                      <td style="${isNaN(transactions[i]['amount']) ? 'display:none;' : ' '}">${transactions[i]['amount']}</td>
           
                      <td style="${isNaN(transactions[i]['score']) ? 'display:none;' : ' '}">${transactions[i]['score']}</td>
          
                      <td style="${isNaN(transactions[i]['balance']) ? 'display:none;' : ' '}">${transactions[i]['balance']}</td>
                      
                      <td style="${!(transactions[i]['dateTime']) ? 'display:none;' : ' '}">${transactions[i]['dateTime']}</td>
           
               </tr>`;
            return p;
        }

        function commentScoreIsAvailable() {
            return scores && scores.comment != 0;
        }

        function lotteryIsAvailable() {
            return scores && (scores.lottery instanceof Array) && scores.lottery.filter((e) => e != 0 && !isNaN(e)).length > 0;
        }

        function getUserLoginRegister() {


            if (user_id == 0) return `
        
        <div style="text-align: center;margin: 1rem;"><span>ابتدا <strong><a style="color:red" href="${login_url}"> وارد </a></strong>شوید یا <strong><a style="color:red;" href="${register_url}"> ثبت نام </a></strong>کنید</span></div>
        `;
            if (user == 0) {

                return `
            <div style="text-align: center;margin: 1rem;"><span>ابتدا در باشگاه مشتریان <strong><span id="show-register-form" style="color:red;cursor:pointer;" class="scale-on-hover"    >ثبت نام</span></strong>   کنید</span></div>
    `;
            }
            return '';
        }

        function showLottery() {
            let arr = [];

            for (let i in scores.lottery) {
                arr.push({
                    'textFillStyle': (i % 2 != 0 ? '#ff0000' : '#fff'),
                    'fillStyle': (i % 2 == 0 ? '#ff0000' : '#fff'),
                    'text': scores.lottery[i]
                });
            }

            return `
                    <div class="the_wheel" align="center" valign="center"
         style="position: relative; height:100%; width:100%;object-fit: fill ;margin:1rem">
         
        <canvas width="250" height="250"
                style="    cursor: pointer; object-fit: fill ;  "
                id='lottery' data-num-segments="${scores.lottery.length}"
                data-unit="${unit}"
                data-url="${dataset.ajax_url}"
                data-nonce="${dataset.lottery_nonce}"
                data-action="make_lottery"
                data-nonce_action="lottery_nonce"
                data-segments='${JSON.stringify(arr)}'>
            Canvas not supported, use another browser.
        </canvas>
       <div class="m-1" style="color:red;"> <strong>برای چرخاندن گردونه، روی آن کلیک کنید</strong></div>
    </div>
            `;
        }

        document.body.appendChild(button);

        wrapper.innerHTML = panel;
        document.body.appendChild(wrapper);
        loadVue();
        makeLottery();
        makeTabs();

        // wrapper.setAttribute("onclick", "document.getElementById('drclubs-wrapper').remove()");

        const form = registerFormCodeMaker({
            user_id: user_id,
            url: dataset.ajax_url,
            action: dataset.drclubs_register_action,
            nonce: dataset.drclubs_register_nonce,
        }, {position: 'absolute'});

        const formToggle = document.getElementById('show-register-form');
        if (formToggle)
            wrapper.addEventListener('click', (e) => {

                // console.log(e.target.id === formToggle.id);
                // wrapper.innerHTML += form;
                if (e.target && e.target.id === formToggle.id)
                    // formToggle.insertAdjacentHTML("afterend", form);
                    e.target.insertAdjacentHTML('afterend', form);

            }, false);
        if (button)
            button.addEventListener('click', () => {

                if (wrapper.style.display === 'none') {

                    wrapper.style.display = 'block';
                } else wrapper.style.display = 'none';
                // wrapper.remove();

            });


    }
);