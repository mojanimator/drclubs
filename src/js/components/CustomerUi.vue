<template>
  <div ref="drclubs-front-ui" id="drclubs-front-ui" class="drclubs ">

    <div id="drclubs-customer-button" @click="viewPanel=!viewPanel; " :style="margins + positions">باشگاه
      مشتریان
    </div>


    <div v-if="viewPanel" class="drclubs-panel-wrapper mx-auto p-2    " style="  ">
      <div class="drclubs-panel">
        <div class="p-3  px-3 drclubs-panel-header "
             style="">
          <div>
            <img class="mx-2" :src=" assetUrl + 'img/logo.png' " alt="" width="32" height="32">
            <span style="font-weight: bold"> پنل باشگاه مشتریان </span>
            <small>( قدرت گرفته از<span>
          <a class="text-decoration-none " style="color:lightgrey;" target="_blank"
             href="https://drclubs.ir"> دکتر کلابز </a>
        </span> )
            </small>

          </div>
          <span @click="viewPanel=false "
                class="dashicons dashicons-no-alt scale-on-hover"
                style="cursor:pointer;margin-right: auto;color:white;">
      </span>

        </div>

        <div class="p-2 drclubs-panel-body">
          <ul class="nav   " style="font-family: Tanha,serif">
            <li @click="tab=1" :class="tab==1 ? 'active' : ''"> اطلاعات کاربری</li>
            <li @click="tab=2" :class="tab==2 ? 'active' : ''"> امتیاز گیری</li>
            <li @click="tab=3" :class="tab==3 ? 'active' : ''"> گزارشات سیستم</li>

          </ul>

          <div class="  " style=" position:relative">

            <div ref="drclubs-loading" :class="loading?'show':''" class="loader-container  text-center  "
                 style="  bacground-color:white;position:absolute">
              <div class="loader">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
              </div>
            </div>

            <div v-show="tab==1" id="tab-1" :class=" 'drclubs-tab-content   p-2 '+  (tab==1 ? 'active' : '')  ">

              <div v-if="user_id==0" style="text-align: center;margin: 1rem;">
            <span>ابتدا <strong>
            <a style="color:red" :href="login_url"> وارد </a>
            </strong>شوید یا <strong>
              <a style="color:red;" :href="register_url"> ثبت نام </a>
            </strong>کنید</span>
              </div>
              <div v-else-if="user==0" style="text-align: center;margin: 1rem;">
            <span>ابتدا در باشگاه مشتریان <strong>
              <span @click="toggleRegisterForm " id="drclubs-show-register-form" style="color:red;cursor:pointer;"
                    class="scale-on-hover">ثبت نام</span>
            </strong>   کنید</span>
              </div>

              <div v-else="" style=" display: flex;  flex-wrap: wrap;align-items: center ; justify-content: center ">
                <img :src="assetUrl + (level ? `img/level${level}.png` : 'img/user.png')" :alt="level_name"
                     style=" max-height:150px;display: inline-block;  "/>
                <!--<div style="border-left: 1px solid red;display: inline-block;height: 80% "></div>-->
                <div style=" vertical-align: middle; ;color:black;margin: 1rem;display: inline-block;  ">
                  <div>
                    <span style="color:red" class="dashicons dashicons-tag"></span>
                    <span> {{ user.Fname ? user.Fname : '' }} </span>
                    <span> {{ user.Lname ? user.Lname : '' }} </span>
                    <span> {{ user.Lname && user.Fname ? '' : 'نام: نامشخص' }} </span>
                  </div>
                  <div>
                    <span style="color:red" class="dashicons dashicons-calendar-alt"></span>
                    <span>{{ user.BornDate ? user.BornDate : 'تاریخ تولد: نامشخص' }}</span>

                  </div>
                  <div>
                    <span style="color:red" class="dashicons dashicons-id"></span>
                    <span>{{ user.NationalCode ? user.NationalCode : 'کد ملی: نامشخص' }}</span>

                  </div>
                  <div :style="'display:'+ (user.CardNumber ? 'block' : 'none') ">
                    <span style="color:red" class="dashicons dashicons-feedback    "></span>
                    <span> {{ user.CardNumber ? user.CardNumber : 'شماره کارت: نامشخص' }}</span>

                  </div>
                </div>
              </div>

              <div class="row g-2" style="   ">
                <div class="col-sm-6">
                  <div style=" border: 1px solid;border-radius: .5rem;  text-align: center;">
                    <div>کیف پول</div>
                    <div v-if="account.balance != null">{{ account.balance + ' ' + currency }}</div>
                    <span v-else class="dashicons dashicons-info"></span>

                  </div>

                </div>
                <div class="col-sm-6">
                  <div class=" " style="  border: 1px solid;border-radius: .5rem;  text-align: center;">
                    <div>تعداد خرید</div>
                    <div v-if="account.purchaseCount != null">{{ account.purchaseCount }}</div>
                    <span v-else class="dashicons dashicons-info"></span>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class=" ">
                    <div class=" " style="border: 1px solid;border-radius: .5rem;  text-align: center;">
                      <div>مجموع خرید</div>
                      <div v-if="account.purchaseVolume != null">{{ account.purchaseVolume + ' ' + currency }}</div>
                      <span v-else class="dashicons dashicons-info"></span>

                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class=" " style="border: 1px solid;border-radius: .5rem;  text-align: center;">
                    <div>امتیاز</div>
                    <div v-if="account.score != null">{{ Math.floor(account.score).toString() }}</div>
                    <span v-else class="dashicons dashicons-info"></span>

                  </div>
                </div>


              </div>


              <div v-if="transactions.length>0">
                <div class="text-center m-2 font-weight-bold text-dark">تراکنش های اخیر</div>
                <div class="table-responsive text-center">
                  <table class="table table-striped">
                    <thead>
                    <tr>
                      <th :style="!('amount' in transactions[0]) ? 'display:none;' : ''">{{ 'خرید (ریال)' }}
                      </th>
                      <th :style="!('score' in transactions[0]) ? 'display:none;' : ''">{{ 'امتیاز' }}
                      </th>
                      <th :style="!('balance' in transactions[0]) ? 'display:none;' : ''">{{ 'از کیف پول (ریال)' }}
                      </th>
                      <th :style="!('dateTime' in transactions[0]) ? 'display:none;' : ''">{{ 'زمان' }}
                      </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="t  in transactions">
                      <td :style="isNaN(t['amount']) ? 'display:none;' : ' '">{{
                          t['amount']
                        }}
                      </td>

                      <td :style="isNaN(t['score']) ? 'display:none;' : ' '">{{
                          t['score']
                        }}
                      </td>

                      <td :style="isNaN(t['balance']) ? 'display:none;' : ' '">{{
                          t['balance']
                        }}
                      </td>

                      <td :style="!(t['dateTime']) ? 'display:none;' : ' '">{{
                          t['dateTime']
                        }}
                      </td>

                    </tr>

                    </tbody>
                  </table>
                </div>

              </div>
            </div>
            <div v-show="tab==2" id="tab-2" :class="'drclubs-tab-content  p-2 '+ ( tab==2 ? 'active' : '')  ">

              <div style="text-align: center">

                <div v-if="commentScoreIsAvailable()" style="font-family: Tanha,serif;">
                  <span> ثبت نظر برای محصولات</span>:
                  <span> {{ scores.comment }}<span> امتیاز </span>
                <span v-if="limits && limits.comment">(هر {{ limits.comment }} ساعت)</span>
              </span>
                </div>

                <div v-if="lotteryIsAvailable()" style="font-family: Tanha,serif;">
                  <span> چرخاندن گردونه شانس</span>

                  (<span v-if="limits && limits.lottery">هر  {{ limits.lottery }} ساعت</span> )
                </div>
                <span
                    v-if="false && !commentScoreIsAvailable() && !lotteryIsAvailable()">در حال حاضر روش امتیاز گیری تعیین نشده است</span>

                <div v-if="user_id==0 || user_id==null" style="text-align: center;margin: 1rem;">
            <span>ابتدا <strong>
            <a style="color:red" :href="login_url"> وارد </a>
            </strong>شوید یا <strong>
              <a style="color:red;" :href="register_url"> ثبت نام </a>
            </strong>کنید</span>
                </div>
                <div v-else-if="user==0" style="text-align: center;margin: 1rem;">
            <span>ابتدا در باشگاه مشتریان <strong>
              <span @click="toggleRegisterForm " id="drclubs-show-register-form" style="color:red;cursor:pointer;"
                    class="scale-on-hover">ثبت نام</span>
            </strong>   کنید</span>
                </div>

                <div class=" " align="center" valign="center"
                     style="position: relative; height:100%; width:100%;object-fit: fill ;margin:1rem">

                  <lottery-ui v-if="lotteryIsAvailable()"

                              :width="250"
                              :height="250"
                              :num-segments="scores.lottery.length"
                              :unit="lottery_unit"
                              :url="dataUrl"
                              :nonce="lottery_nonce"
                              action="make_lottery"
                              nonce_action="lottery_nonce"
                              :segments="lotteryData">
                    مرورگر شما قادر به نمایش این مورد نیست
                  </lottery-ui>
                  <div class="m-1" style="color:red;"><strong>برای چرخاندن گردونه، روی آن کلیک کنید</strong></div>
                </div>

              </div>

            </div>

            <div v-show="tab==3" id="tab-3" :class="'drclubs-tab-content  p-2 '+ ( tab==3 ? 'active' : '')     ">

              <div v-if="user_id==0" style="text-align: center;margin: 1rem;">
            <span>ابتدا <strong>
            <a style="color:red" :href="login_url"> وارد </a>
            </strong>شوید یا <strong>
              <a style="color:red;" :href="register_url"> ثبت نام </a>
            </strong>کنید</span>
              </div>
              <div v-else-if="user==0" style="text-align: center;margin: 1rem;">
            <span>ابتدا در باشگاه مشتریان <strong>
              <span @click="toggleRegisterForm" id="drclubs-show-register-form" style="color:red;cursor:pointer;"
                    class="scale-on-hover">ثبت نام</span>
            </strong>   کنید</span>
              </div>
              <div v-else>
                <customer-logs
                    v-if="drclubs_log_action"
                    :log-types="log_types"
                    :log-link="dataUrl"
                    :parent-params="{
                action:  drclubs_log_action,
                nonce: log_nonce,
                nonce_action: log_nonce_action,
                user_id: user_id
                }">

                </customer-logs>


              </div>

            </div>

          </div>

        </div>
      </div>


    </div>
  </div>

</template>

<script>

import customerLogs from "./CustomerLogs.vue";
import {makeLottery} from '../lottery';
import LotteryUi from "./LotteryUi.vue";
import {registerFormCodeMaker} from "../register_form";

export default {
  props: ['dataUrl', 'assetUrl'],
  components: {
    customerLogs, LotteryUi,
  },
  data() {
    return {
      tab: 1,
      viewPanel: false,
      registerForm: null,
      data: null,
      margins: '',
      positions: '',
      account: {},
      transactions: [],
      assets_path: '',
      currency: null,
      drclubs_log_action: null,
      drclubs_register_action: null,
      drclubs_register_nonce: null,
      level: null,
      level_name: null,
      limits: null,
      log_nonce: null,
      log_nonce_action: null,
      log_types: null,
      login_url: null,
      lottery_nonce: null,
      lottery_unit: null,
      register_url: null,
      scores: null,
      status: null,
      user: {},
      user_id: null,
      lotteryData: [],
    }
  },
  beforeCreate: () => {

  },
  created() {
    this.getData();
  },
  mounted() {

  },
  methods: {
    prepareUi() {
      this.margins = '';
      for (let m in this.data.margins)
        this.margins += this.data.margins[m].style.replace('\$', this.data.margins[m].value) + ";"

      this.positions = this.data.positions[this.data.positions['selected']].style.replace('\$', ' ');

      this.lotteryData = [];
      for (let i in this.scores.lottery) {
        this.lotteryData.push({
          'textFillStyle': (i % 2 != 0 ? '#ff0000' : '#fff'),
          'fillStyle': (i % 2 == 0 ? '#ff0000' : '#fff'),
          'text': this.scores.lottery[i]
        });
      }
      if (this.user != 0 && this.user_id != 0) this.tab = 1; else this.tab = 2;

      this.registerForm = registerFormCodeMaker({
        user_id: this.user_id,
        url: this.dataUrl,
        action: this.drclubs_register_action,
        nonce: this.drclubs_register_nonce,
      }, {position: 'absolute'});
    },
    toggleRegisterForm(e) {

      document.querySelector('.drclubs-panel-body').insertAdjacentHTML('afterend', this.registerForm);

    },
    getData() {
//                console.log({...this.params, ...JSON.parse(this.parentParams)});
      this.loading = true;

      axios.get(this.dataUrl, {
        params: {'action': 'drclubs_prepare'}
      })

          .then((response) => {
                // console.log(response.data);
                this.loading = false;

                if (response.status === 200) {
                  this.data = response.data;

                  this.account = this.data.account;
                  this.transactions = this.data.transactions;
                  this.currency = this.data.currency;
                  this.drclubs_log_action = this.data.drclubs_log_action;
                  this.drclubs_register_action = this.data.drclubs_register_action;
                  this.drclubs_register_nonce = this.data.drclubs_register_nonce;
                  this.level = this.data.level;
                  this.level_name = this.data.level_name;
                  this.limits = this.data.limits;
                  this.log_nonce = this.data.log_nonce;
                  this.log_nonce_action = this.data.log_nonce_action;
                  this.log_types = this.data.log_types;
                  this.login_url = this.data.login_url;
                  this.lottery_nonce = this.data.lottery_nonce;
                  this.lottery_unit = this.data.lottery_unit;
                  this.positions = this.data.positions;
                  this.register_url = this.data.register_url;
                  this.scores = this.data.scores;
                  this.status = this.data.status;
                  this.user = this.data.user;
                  this.user_id = this.data.user_id;

                  this.prepareUi();
                }
                //                            window.location.reload();


              }
          ).catch((error) => {
        this.loading = false;

        let errors = '';
        if (error.response && error.response.status === 422)
          for (let idx in error.response.data.errors)
            errors += error.response.data.errors[idx] + '<br>';
        else if (error.response && error.response.status === 403)
          errors = error.response.data.message;
        else {
          errors = error;
        }
        console.log(errors);

      });
    },
    commentScoreIsAvailable() {

      return this.scores && this.scores.comment != 0;
    }
    ,
    lotteryIsAvailable() {
      return this.scores && (this.scores.lottery instanceof Array) && this.scores.lottery.filter((e) => e != 0 && !isNaN(e)).length > 0;
    },
    log(str) {
      console.log(str);
    },

  },
}
</script>
<style lang="scss" scoped>
$primary-color: 'red';
@import "../../scss/loader";
#drclubs-front-ui {
  #drclubs-customer-button {
    cursor: pointer;
    font-family: Tanha, serif;
    position: fixed;
    border-radius: 1rem;
    padding: .8rem;
    z-index: 99999999;
    color: white;
    background-color: red;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;

    //v-bind(margin);
  }

  .drclubs-panel-wrapper {

    font-family: Tanha, serif;
    transition: all ease .3s;
    position: fixed;
    width: 100%;
    height: 100%;
    overflow-y: scroll;
    left: 0;
    right: 0;
    bottom: 0;
    top: 0;
    background-color: rgba(0, 0, 0, .8);
    z-index: 2000000000;
    text-align: center;

    .drclubs-panel {
      position: relative;
      z-index: 1000000000;
      border-radius: 1rem;
      //display: flex;
      align-items: center;
      background-color: red;
      //color: white;
      text-align: right;

      .drclubs-panel-header {
        display: flex;
        color: white;
      }

      .drclubs-panel-body {
        //display: flex;
        //color: white;
        font-weight: bold;

        .drclubs-tab-content {
          background-color: white;
          color: #4b0b0b;
          border-radius: 1rem 0rem 1rem 1rem;
        }
      }

      ul {


        li {
          border-radius: 1rem 1rem 0rem 0rem;
          padding: .8rem;

          cursor: pointer;

          background-color: rgba(173, 48, 48, 0.8);
          color: white;
          transition: all ease .3s;

          &.active {
            transition: all ease .3s;
            background-color: white;
            color: red;
          }
        }
      }
    }
  }
}
</style>