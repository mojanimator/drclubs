<template>
  <div class=" ">

    <canvas @click="startSpin" ref="drclubs-lottery" id="drclubs-lottery" :width="width" :height="height"
            style=" cursor: pointer; object-fit: fill ;  "
    >
      مرورگر شما قادر به نمایش این مورد نیست
    </canvas>

  </div>

</template>

<script>
import Winwheel from 'winwheeljs'  ;

export default {
  props: ['dataUrl', 'assetUrl', 'width', 'height', 'numSegments', 'unit', 'url', 'nonce', 'action', 'nonce_action', 'segments'],
  components: {},
  data() {
    return {theWheel: null, canvas: null, wheelSpinning: false, score: 0, preWheel: false}
  },
  beforeCreate: () => {

  },
  watch: {
    wheelSpinning(before, after) {
      this.$parent.loading = this.wheelSpinning;


    }
  },
  created() {

  },
  mounted() {
    this.prepareUi();

  },
  methods: {
    prepareUi() {
      this.canvas = this.$refs["drclubs-lottery"];

      if (!this.canvas) return;


      this.theWheel = new Winwheel({
        'canvasId': this.canvas.id,
        'numSegments': this.numSegments,
        'segments': this.segments,
        'textOrientation': 'horizontal', // Make text vertial so goes down from the outside of wheel.
        'textAlignment': 'center',
        'fillStyle': '#e7706f',
        'textFillStyle': '#fff',
        'innerRadius': 5,
        'textFontFamily': 'Tanha',
        'textFontWeight': 'Normal',
        // 'textStrokeStyle': 'black',
        'pointerGuide':        // Turn pointer guide on.
            {
              'display': true,
              'strokeStyle': 'grey',
              'lineWidth': 1
            },
        'lineWidth': 1,
        'strokeStyle': 'red',
        'animation':               // Definition of the animation
            {
              'type': 'spinToStop',
              'duration': 5,
              'spins': 10,
              'callbackFinished': this.alertPrize

            },
        'repeat': -1,
        'pins':                // Turn pins on.
            {
              'number': this.numSegments,
              'fillStyle': 'silver',
              'outerRadius': 4,
            }
      });

      // createPointer(canvas.getContext('2d'));

      this.wheelSpinning = false;
      this.score = null;
    },

    alertPrize(winningSegment) {

      if (!this.wheelSpinning) {

        return;
      }
      // let winningSegment = theWheel.getIndicatedSegment();
      if (winningSegment.text > 0)
        alert("تبریک!\n " + winningSegment.text + this.unit + ' دریافت کردید');
      else
        alert('متاسفانه جایزه ای دریافت نکردید');
      this.resetWheel();
      this.wheelSpinning = false;
    },
    preWheelAnimation() {
      this.preWheel = true;
      this.theWheel.startAnimation();
    },
    startSpin() {

      if (this.wheelSpinning === false) {
        this.wheelSpinning = true;
        let score = null;
        let segment = null;
        // this.preWheelAnimation();
        this.getWinnerSegmentFromServer(this.url, {
          nonce: this.nonce,
          action: this.action,
          nonce_action: this.nonce_action
        }).then((res) => {
          // console.log(res)
          if (res && res.segment) {
            this.theWheel.animation.stopAngle = this.theWheel.getRandomForSegment(res.segment);
            this.score = res.score;
            this.theWheel.startAnimation();
            this.wheelSpinning = true;

            this.getWinnerSegmentFromServer(this.url, {
              score: res.score,
              segment: res.segment,
              nonce: this.nonce,
              action: this.action,
              nonce_action: this.nonce_action
            }).then((res) => {
              // console.log(res)
              if (res && res.segment) {

              } else {
                this.resetWheel();
                alert(res);

              }
            });
          } else {
            this.wheelSpinning = false;
            console.log(res)
            alert(res);

          }
        });
      }
    },

    getWinnerSegmentFromServer(url, params) {
      let formData = new FormData;
      for (let i in params) {
        formData.append(i, params[i]);
      }

      return axios.post(url, formData, {
        // headers: {

        //
        // },
      }).then((response) => {
        // console.log(response.data);

        // alert(response.data);
        if (response && response.status === 200)
          return response.data;
        else
          return 'مشکلی پیش آمد. لطفا مجدد تلاش کنید';
      }).catch((error) => {
        console.log('ERROR CATCHED:' + "\n" + error + "\n" + error.response.data);
        // alert(error);
        return 'مشکلی پیش آمد. لطفا مجدد تلاش کنید';

      });

    },

    resetWheel() {
      this.theWheel.stopAnimation(false);  // Stop the animation, false as param so does not call callback function.
      this.theWheel.rotationAngle = 0;     // Re-set the wheel angle to 0 degrees.
      this.theWheel.draw();                // Call draw to render changes to the wheel.


      this.wheelSpinning = false;          // Reset to false to power buttons and spin can be clicked again.
    },
    log(str) {
      console.log(str);
    },

  },
}
</script>
<style lang="scss" scoped>

</style>