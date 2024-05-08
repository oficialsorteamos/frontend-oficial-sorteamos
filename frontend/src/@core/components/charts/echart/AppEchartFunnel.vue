<template>
  <e-charts
    ref="line"
    autoresize
    :options="option"
    theme="theme-color"
    auto-resize
  />
</template>

<script>
import ECharts from 'vue-echarts'
import 'echarts/lib/component/tooltip'
import 'echarts/lib/component/legend'
import 'echarts/lib/chart/funnel'
import theme from './theme.json'

ECharts.registerTheme('theme-color', theme)

export default {
  components: {
    ECharts,
  },
  props: {
    legends: {
      type: Array,
      default: null,
    },
    series: {
      type: Array,
      default: null,
    },
    maxAgeGroup: {
      type: Number,
      default: 0,
    },
  },
  data() {
    return {
        option: {
        title: {
          text: 'Contacts'
        },
        tooltip: {
          trigger: 'item',
          formatter: '{a} <br/>{b} : {c}'
        },
        toolbox: {
          feature: {
            dataView: { readOnly: false },
            restore: {},
            saveAsImage: {}
          }
        },
        legend: {
          //data: ['Show', 'Click', 'Visit', 'Inquiry', 'Order']
          data: this.legends
        },
        series: [
          {
            name: 'Funnel',
            type: 'funnel',
            left: '10%',
            top: 60,
            bottom: 60,
            width: '80%',
            min: 0,
            max: this.maxAgeGroup,
            minSize: '0%',
            maxSize: '100%',
            sort: 'descending',
            gap: 2,
            label: {
              show: true,
              position: 'inside'
            },
            labelLine: {
              length: 10,
              lineStyle: {
                width: 1,
                type: 'solid'
              }
            },
            itemStyle: {
              borderColor: '#fff',
              borderWidth: 1
            },
            emphasis: {
              label: {
                fontSize: 20
              }
            },
            data: this.series
             /*[
              { value: 60, name: 'Visit' },
              { value: 40, name: 'Inquiry'},
              { value: 20, name: 'Order' },
              { value: 80, name: 'Click' },
              { value: 100, name: 'Show' },
            ]*/
          }
        ]
      }
    }
  }
}
</script>
