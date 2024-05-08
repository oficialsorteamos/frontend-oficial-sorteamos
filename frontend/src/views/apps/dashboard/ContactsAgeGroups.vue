<template>
  <b-card>
    <b-card-title class="mb-1">
      {{ $t('dashboard.contactsAgeGroups.ageGroups') }}
    </b-card-title>
    <b-card-sub-title class="mb-2">
      {{ $t('dashboard.contactsAgeGroups.contactsAgeGroups') }}
    </b-card-sub-title>

    
      <chartjs-component-horizontal-bar-chart
        :height="400"
        :data="horizontalBarChart.data"
        :options="horizontalBarChart.options"
        v-if="labels && dataAgeGroup"
      />
  </b-card>
</template>

<script>
import {
  BCard, BCardBody, BCardHeader, BCardTitle, BCardSubTitle,
} from 'bootstrap-vue'
import flatPickr from 'vue-flatpickr-component'
import ChartjsComponentHorizontalBarChart from './charts-components/ChartjsComponentHorizontalBarChart.vue'
import { $themeColors } from '@themeConfig'

export default {
  components: {
    BCard,
    BCardBody,
    BCardHeader,
    BCardTitle,
    BCardSubTitle,
    flatPickr,
    ChartjsComponentHorizontalBarChart,
  },
  props: {
    labels: {
      //type: Array,
      required: true,
    },
    dataAgeGroup: {
      //type: Array,
      required: true,
    },
  },
  data() {
    return {
      horizontalBarChart: {
        options: {
          elements: {
            rectangle: {
              borderWidth: 2,
              borderSkipped: 'top',
            },
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
          responsive: true,
          maintainAspectRatio: false,
          responsiveAnimationDuration: 500,
          legend: {
            display: false,
          },
          scales: {
            xAxes: [
              {
                display: true,
                gridLines: {
                  zeroLineColor: 'rgba(200, 200, 200, 0.2)',
                  borderColor: 'transparent',
                  color: 'rgba(200, 200, 200, 0.2)',
                  drawTicks: false,
                },
                scaleLabel: {
                  display: true,
                },
                ticks: {
                  min: 0,
                  fontColor: '#6e6b7b',
                },
              },
            ],
            yAxes: [
              {
                display: true,
                gridLines: {
                  display: false,
                },
                scaleLabel: {
                  display: true,
                },
                ticks: {
                  fontColor: '#6e6b7b',
                },
              },
            ],
          },
        },
        data: {
          //labels: ['MON', 'TUE', 'WED ', 'THU', 'FRI', 'SAT', 'SUN'],
          labels: [],
          datasets: [
            {
              //data: [710, 350, 470, 580, 230, 460, 120],
              data: [],
              backgroundColor: $themeColors.info,
              borderColor: 'transparent',
              barThickness: 15,
            },
          ],
        },
      },
      //rangePicker: ['2019-05-01', '2019-05-10'],
    }
  },
  watch: {
    labels(data) {
      this.horizontalBarChart.data.labels = data
    },
    dataAgeGroup(data) {
      this.horizontalBarChart.data.datasets[0].data = data
    },
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-flatpicker.scss';
</style>
