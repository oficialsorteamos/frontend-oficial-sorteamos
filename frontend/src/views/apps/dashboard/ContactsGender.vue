<template>
  <b-card>
    <b-card-title class="mb-1">
      {{ $t('dashboard.contactsGender.gender') }}
    </b-card-title>
    <b-card-sub-title class="mb-2">
      {{ $t('dashboard.contactsGender.contactsGender') }}
    </b-card-sub-title>

    <vue-apex-charts
      type="donut"
      height="350"
      :options="chartOptions"
      :series="series"
      v-if="genderData && genderData.gendersCount"
    />
  </b-card>
</template>

<script>
import {
  BCard, BCardTitle, BCardSubTitle,
} from 'bootstrap-vue'
import VueApexCharts from 'vue-apexcharts'

export default {
  components: {
    VueApexCharts,
    BCard,
    BCardTitle,
    BCardSubTitle,
  },
  props: {
    genderData: {
      //type: Array,
      default: () => [],
    },
  },
  data: function() {
    return {
      series: [],
      chartOptions: {
          legend: {
          show: true,
          position: 'bottom',
          fontSize: '14px',
          fontFamily: 'Montserrat',
        },
        colors: [
          '#826af9',
          '#f8d3ff',
          '#d2b0ff',
        ],
        dataLabels: {
          enabled: true,
          formatter(val) {
            // eslint-disable-next-line radix
            return `${parseInt(val)}%`
          },
        },
        labels: [],
        plotOptions: {
          pie: {
            donut: {
              labels: {
                show: true,
                name: {
                  fontSize: '2rem',
                  fontFamily: 'Montserrat',
                },
                value: {
                  fontSize: '1rem',
                  fontFamily: 'Montserrat',
                  formatter(val) {
                    // eslint-disable-next-line radix
                    return `${parseInt(val)}`
                  },
                },
                total: {
                  show: false,
                  fontSize: '1.5rem',
                  label: 'Operational',
                  formatter() {
                    return '31%'
                  },
                },
              },
            },
          },
        },
        responsive: [
          {
            breakpoint: 992,
            options: {
              chart: {
                height: 380,
              },
              legend: {
                position: 'bottom',
              },
            },
          },
          {
            breakpoint: 576,
            options: {
              chart: {
                height: 320,
              },
              plotOptions: {
                pie: {
                  donut: {
                    labels: {
                      show: true,
                      name: {
                        fontSize: '1.5rem',
                      },
                      value: {
                        fontSize: '1rem',
                      },
                      total: {
                        fontSize: '1.5rem',
                      },
                    },
                  },
                },
              },
              legend: {
                show: true,
              },
            },
          },
        ],
      },
    }
  },
  watch: {
    genderData(data) {
      this.series = data.gendersCount
      this.chartOptions  = {...this.chartOptions, ...{
          labels: data.gendersName.labels
      }}
    },
  },
  
}
</script>
