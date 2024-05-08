<template>
  <b-card
    class="earnings-card"
  >
    <b-row>
      <b-col cols="6">
        <b-card-title class="mb-1">
          {{ $t('dashboard.servicesStatus.servicesStatus') }}
        </b-card-title>
        <b-card-text class="text-muted font-small-2">
          <span class="font-weight-bolder">{{ data.percentageClosed }}%</span><span> {{ $t('dashboard.servicesStatus.ofClosedServices') }}.</span>
        </b-card-text>
      </b-col>
      <b-col cols="6">
        <!-- chart -->
        <vue-apex-charts
          height="120"
          :options="earningsChart.chartOptions"
          :series="data.series"
          v-if="data && data.series"
        />
      </b-col>
    </b-row>
  </b-card>
</template>

<script>
import {
  BCard, BRow, BCol, BCardTitle, BCardText,
} from 'bootstrap-vue'
import VueApexCharts from 'vue-apexcharts'
import { $themeColors } from '@themeConfig'

const $earningsStrokeColor2 = '#28c76f66'
const $earningsStrokeColor3 = '#28c76f33'
export default {
  components: {
    BCard,
    BRow,
    BCol,
    BCardTitle,
    BCardText,
    VueApexCharts,
  },
  props: {
    data: {
      //type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      serieValues: [],
      earningsChart: {
        chartOptions: {
          chart: {
            type: 'donut',
            toolbar: {
              show: false,
            },
          },
          dataLabels: {
            enabled: false,
          },
          legend: { show: false },
          comparedResult: [2, -3, 8],
          //labels: ['App', 'Service', 'Product'],
          labels: [],
          stroke: { width: 0 },
          colors: [$earningsStrokeColor2, $earningsStrokeColor3, $themeColors.success],
          grid: {
            padding: {
              right: -20,
              bottom: -8,
              left: -20,
            },
          },
          plotOptions: {
            pie: {
              startAngle: 0,
              donut: {
                labels: {
                  show: true,
                  name: {
                    offsetY: 15,
                  },
                  value: {
                    offsetY: -15,
                    formatter(val) {
                      // eslint-disable-next-line radix
                      return `${parseInt(val)}`
                    },
                  },
                  total: {
                    show: false,
                    offsetY: 15,
                    label: 'App',
                    formatter() {
                      return '53%'
                    },
                  },
                },
              },
            },
          },
          responsive: [
            {
              breakpoint: 1325,
              options: {
                chart: {
                  height: 100,
                },
              },
            },
            {
              breakpoint: 1200,
              options: {
                chart: {
                  height: 120,
                },
              },
            },
            {
              breakpoint: 1045,
              options: {
                chart: {
                  height: 100,
                },
              },
            },
            {
              breakpoint: 992,
              options: {
                chart: {
                  height: 120,
                },
              },
            },
          ],
        },
      },
    }
  },
  watch: {
    data(data) {
      console.log('labels atendimentos')
      console.log(data)
      this.earningsChart.chartOptions  = {...this.earningsChart.chartOptions, ...{
          labels: data.labels
      }}
      this.serieValues = data.series
    },
  },
}
</script>
