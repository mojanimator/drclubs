require('./TweenMax.min');

// require('./Winwheel.min');
// Winwheel = require('winwheeljs');
import Winwheel from 'winwheeljs'  ;

export let makeLottery;

makeLottery = () => {

    let canvas = document.getElementById('drclubs-lottery');
    // let button = document.getElementById('lottery-button');

    if (!canvas) return;
    const dataset = canvas.dataset;
    const segments = JSON.parse(dataset.segments);
    const numSegments = dataset.numSegments;
    const unit = dataset.unit;
    console.log(segments);
    console.log(numSegments);

    let theWheel = new Winwheel({
        'canvasId': canvas.id,
        'numSegments': numSegments,
        'segments': segments,
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
                'duration': 3,
                'spins': 5,
                'callbackFinished': alertPrize

            },
        'pins':                // Turn pins on.
            {
                'number': numSegments,
                'fillStyle': 'silver',
                'outerRadius': 4,
            }
    });

    // createPointer(canvas.getContext('2d'));

    let wheelSpinning = false;
    let score = null;

    function alertPrize(winningSegment) {
        // let winningSegment = theWheel.getIndicatedSegment();
        if (winningSegment.text > 0)
            alert("تبریک!\n " + winningSegment.text + unit + ' دریافت کردید');
        else
            alert('متاسفانه جایزه ای دریافت نکردید');
        resetWheel();
        wheelSpinning = false;
    }

    function startSpin() {


        if (wheelSpinning === false) {
            wheelSpinning = true;
            getWinnerSegmentFromServer(dataset.url, {
                nonce: dataset.nonce,
                action: dataset.action,
                nonce_action: dataset.nonce_action
            }).then((res) => {
                if (res && res.segment) {
                    theWheel.animation.stopAngle = theWheel.getRandomForSegment(res.segment);
                    score = res.score;
                    theWheel.startAnimation();
                    wheelSpinning = true;
                } else {
                    wheelSpinning = false;
                    alert(res);

                }
            });
        }
    }

    function getWinnerSegmentFromServer(url, params) {
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

    }

    function resetWheel() {
        theWheel.stopAnimation(false);  // Stop the animation, false as param so does not call callback function.
        theWheel.rotationAngle = 0;     // Re-set the wheel angle to 0 degrees.
        theWheel.draw();                // Call draw to render changes to the wheel.


        wheelSpinning = false;          // Reset to false to power buttons and spin can be clicked again.
    }

    function createPointer(tx) {

// Create pointer.
        if (tx) {
            tx.strokeStyle = '#000000';     // Set line colour.
            tx.fillStyle = 'aqua';        // Set fill colour.
            tx.lineWidth = 2;
            tx.beginPath();                 // Begin path.
            tx.moveTo(175, 20);             // Move to initial position.
            tx.lineTo(235, 20);             // Draw lines to make the shape.
            tx.lineTo(205, 80);
            tx.lineTo(176, 20);
            tx.stroke();                    // Complete the path by stroking (draw lines).
            tx.fill();
        }
    }

    canvas.addEventListener('click', () => {
        if (wheelSpinning === false) {

            startSpin();
        }
    });

};
