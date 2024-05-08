<template>
  <b-card>
    <b-card-title class="mb-1">
      {{ $t('dashboard.servicesMonth.servicesMonth') }}
    </b-card-title>
    <b-card-sub-title class="mb-2">
      {{ $t('dashboard.servicesMonth.amountServicesMonth') }}
    </b-card-sub-title>

    <!-- chart -->
    <b-card-body>
      <chartjs-component-bar-chart
        :height="400"
        :data="barChart.data"
        :options="barChart.options"
        v-if="labels && countServices"
      />
    </b-card-body>
  </b-card>
</template>

<script>
import {
  BCard, BCardHeader, BCardBody, BCardTitle, BCardSubTitle
} from 'bootstrap-vue'
import flatPickr from 'vue-flatpickr-component'
import ChartjsComponentBarChart from './charts-components/ChartjsComponentBarChart.vue'
import { $themeColors } from '@themeConfig'

export default {
  components: {
    BCard,
    BCardHeader,
    BCardBody,
    BCardTitle,
    BCardSubTitle,
    flatPickr,
    ChartjsComponentBarChart,

  },
  props: {
    labels: {
      //type: Array,
      required: true,
    },
    countServices: {
      //type: Array,
      required: true,
    },
    maxCountService: {
      //type: Number,
      required: true,
    },
  },
  data() {
    return {
      barChart: {
        data: {
          //labels: ['7/12', '8/12', '9/12', '10/12', '11/12', '12/12', '13/12', '14/12', '15/12', '16/12', '17/12'],
          labels: [],
          datasets: [
            {
              //data: [275, 90, 190, 205, 125, 85, 55, 87, 127, 150, 230, 280, 190],
              data: [],
              backgroundColor: '#28dac6',
              borderColor: 'transparent',
            },
          ],
        },
        options: {
          elements: {
            rectangle: {
              borderWidth: 2,
              borderSkipped: 'bottom',
            },
          },
          responsive: true,
          maintainAspectRatio: false,
          responsiveAnimationDuration: 500,
          legend: {
            display: false,
          },
          tooltips: {
            // Updated default tooltip UI
            shadowOffsetX: 1,
            shadowOffsetY: 1,
            shadowBlur: 8,
            shadowColor: 'rgba(0, 0, 0, 0.25)',
            backgroundColor: $themeColors.light,
            titleFontColor: $themeColors.dark,
            bodyFontColor: $themeColors.dark,
          },
          scales: {
            xAxes: [
              {
                display: true,
                gridLines: {
                  display: true,
                  color: 'rgba(200, 200, 200, 0.2)',
                  zeroLineColor: 'rgba(200, 200, 200, 0.2)',
                },
                scaleLabel: {
                  display: false,
                },
                ticks: {
                  fontColor: '#6e6b7b',
                },
              },
            ],
            yAxes: [
              {
                display: true,
                gridLines: {
                  color: 'rgba(200, 200, 200, 0.2)',
                  zeroLineColor: 'rgba(200, 200, 200, 0.2)',
                },
                ticks: {
                  stepSize: 100,
                  min: 0,
                  max: 400,
                  fontColor: '#6e6b7b',
                },
              },
            ],
          },
        },
      },
      //rangePicker: ['2019-05-01', '2019-05-10'],
    }
  },
  watch: {
    labels(data) {
      this.barChart.data.labels = Object.values(data)
      
    },
    countServices(data) {
      this.barChart.data.datasets[0].data = Object.values(data)
    },
    maxCountService(data) {
      this.barChart.options.scales.yAxes[0].ticks.max = data
    },
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-flatpicker.scss';
</style>
