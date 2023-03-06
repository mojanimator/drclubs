<template>
  <div class="     position-relative  ">
    <div v-show="loading" class="  position-fixed start-0 top-50  end-0   text-center"
         style="  z-index: 10">
      <div v-for="i in  [1,1,1,1,1]"
           class="spinner-grow mx-1   spinner-grow-sm text-danger "
           role="status">
        <span class="visually-hidden"> </span>
      </div>

    </div>
    <div class="    ">

      <div class="   px-0 p-sm-1  ">
        <!--chart section-->
        <div class="  ">
          <div class="row  align-items-center px-1">

            <div class="col-sm-6 text-center d-flex p-2">
              <div v-for="idx,type in types">
                <div class="form-check form-check-inline  ">
                  <input checked
                         @change="updateTypes(type)" class="form-check-input"
                         type="checkbox" :id="'check-'+type"
                         :value="type"/>
                  <label class="form-check-label noselect mx-1" :for="'check-'+type">{{ idx }}</label>
                </div>
              </div>


            </div>
            <div class="btn-group       bg-transparent   my-1 col-sm-6" role="group" dir="rtl">

              <input @change="params.timestamp='d';getData()" v-model="params.timestamp" value="d"
                     type="radio"
                     class="btn-check   "
                     name="timestamp-d"
                     id="timestamp-d"
                     autocomplete="off"/>
              <label class="btn btn-outline-primary px-sm-3 rounded-0" for="timestamp-d">روزانه</label>
              <input @change="params.timestamp='m';getData()" v-model="params.timestamp" value="m"
                     type="radio"
                     class="btn-check"
                     name="timestamp-m"
                     id="timestamp-m"
                     autocomplete="off"/>
              <label class="btn btn-outline-primary px-sm-3 rounded-0" for="timestamp-m">ماهانه</label>

              <input @change="params.timestamp='y';getData()" v-model="params.timestamp" value="y"
                     type="radio"
                     class="btn-check"
                     name="timestamp-y"
                     id="timestamp-y"
                     autocomplete="off"/>
              <label class="btn btn-outline-primary px-sm-3 rounded-0" for="timestamp-y">سالانه</label>


            </div>

            <div class="row     mx-auto">
              <date-picker class="rounded-2 p-1 col-sm-6 fromdate" inputClass="" :editable="true"

                           inputFormat="YYYY/MM/DD" placeholder="از تاریخ" color="#00acc1"
                           v-model="params.dateFrom" @change="getData()"></date-picker>
              <date-picker class="rounded-2 p-1 col-sm-6 todate" inputClass="" :editable="true"

                           inputFormat="YYYY/MM/DD" placeholder="تا تاریخ" color="#dd77dd"
                           v-model="params.dateTo" @change="getData()"></date-picker>


            </div>
          </div>
          <div>
            <canvas class="w-100 px-0 p-sm-4" id="myChart"></canvas>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import Chart from 'chart.js/auto';
import VuePersianDatetimePicker from 'vue3-persian-datetime-picker';
import {shallowRef} from 'vue';

let colors = ['rgba(255, 99, 132, 1)',
  'rgba(54, 162, 235, 1)',
  'rgba(255, 206, 86, 1)',
  'rgba(75, 192, 192, 1)',
  'rgba(153, 102, 255, 1)',
  'rgba(255, 159, 64, 1)'];
export default {
  props: ['logLink', 'parentParams', 'logTypes'],
  components: {
    datePicker: VuePersianDatetimePicker
  },
  data() {
    return {
      data: [],
      params: {
        timestamp: 'd',
        order_dir: 'DESC',
        order_by: 'created_at',
        type: null,
        types: [],

      },
      link: null,
      loading: false,
      types: [],
      chart: null,
      table: [],
      toggleSort: true

    }
  },
  mounted() {
    this.types = this.logTypes;

    for (let k in this.types)
      this.params.types.push(k);

    document.querySelector('.fromdate label').append('از تاریخ');
    document.querySelector('.todate label').append('تا تاریخ');

    document.querySelectorAll('.vpd-input-group').forEach((el) => {
      el.classList.add('d-inline');
    });
    document.querySelectorAll('.vpd-input-group input').forEach((el) => {
      el.classList.add('w-100');
    });
    this.params.dateFrom = this.date('yesterday');
    this.params.dateTo = this.date();
    this.initChart();
    this.getData();
  },
  methods: {

    updateTypes(type) {

      let index = this.params.types.indexOf(type);
      if (index !== -1) {
        this.params.types.splice(index, 1);
      } else {
        this.params.types.push(type);
      }
      if (this.params.types.length !== 1)
        this.params.type = null;
//                else
//                    this.params.type = this.params.types[0];
      this.getData();
    },
    sort(by) {
      this.toggleSort = !this.toggleSort;
      this.table.sort(function (a, b) {
        return ('' + a[by]).localeCompare('' + b[by]);
//                    return a[by] - b[by];
      });
      if (this.toggleSort)
        this.table.reverse();
    },

    initChart() {
      const ctx = document.getElementById('myChart').getContext('2d');
      this.chart = shallowRef(new Chart(ctx, {
        type: 'line',
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: ''
            },
          },
          interaction: {
            intersect: false,
          },
          scales: {
            x: {
              display: true,
              title: {
                display: true
              }
            },
            y: {
              beginAtZero: true,
              display: true,
              title: {
                display: true,
                text: 'مقدار'
              },
//                                suggestedMin: -10,
//                                suggestedMax: 200
            }
          }
        },
        data: {},

      }));
    },
    updateChart() {

      this.chart.data.datasets = [];

      let ix = -1;

      for (let i in this.data.datas) {

        ix++;
        let counts = [];

        for (let idx in this.data.dates) {
          if (!(this.data.dates[idx] in this.data.datas[i])) {
            this.data.datas[i][this.data.dates[idx]] = [];
          }

          counts.push(this.data.datas[i][this.data.dates[idx]].reduce((partialSum, a) => partialSum + (!isNaN(a.value) ? parseInt(a.value) : 0), 0));
        }

//                    var r = Math.floor(Math.random() * 255);
//                    var g = Math.floor(Math.random() * 255);
//                    var b = Math.floor(Math.random() * 255);

        this.chart.data.datasets.push({
          label: this.types[i],
//                        cubicInterpolationMode: 'monotone',
          borderWidth: 1,
          data: counts,
//                        borderColor: "rgb(" + r + "," + g + "," + b + ")",
          borderColor: colors[ix],
          backgroundColor: colors[ix],
          tension: 0.4,
        });
      }
      this.chart.data.labels = e2f(this.data.dates);

//                this.chart.options.scales.y.title.text = this.params.type === 'مالی'
//                    ? 'پرداخت (تومان)' : 'تعداد';
      this.chart.update();
    },
    getData() {
//                console.log({...this.params, ...JSON.parse(this.parentParams)});
      this.loading = true;
      // console.log(this.parentParams);
      axios.get(this.logLink, {
        params: {...this.params, ...this.parentParams}
      })

          .then((response) => {
//                            console.log(response.data);
                this.loading = false;

                if (response.status === 200) {
                  this.data = response.data;
                  this.updateChart();
                  this.table = [];

                  this.table = [];
                  for (let i in this.data.datas) {
                    for (let j in this.data.datas[i])
                      this.table.push(this.data.datas[i][j]);

                  }
                  this.table = this.table.flat();

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
    date(day) {
      const t = new Date().getTime();
      let today;
      if (day && day === 'yesterday')
        today = new Date(t - 24 * 60 * 60 * 1000);
      else
        today = new Date(t + 24 * 60 * 60 * 1000);
      let options = {
        hour12: false,

        year: 'numeric',
        month: '2-digit',
        day: '2-digit',

        calendar: 'persian',
      };
//                var dd = String(today.getDate()).padStart(2, '0');
//                var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
//                var yyyy = today.getFullYear();
//                return yyyy + '/' + mm + '/' + dd;

      return f2e(today.toLocaleDateString('fa-IR', options));
    }
    ,
    log(str) {
      console.log(str);
    },

  },
}
</script>
