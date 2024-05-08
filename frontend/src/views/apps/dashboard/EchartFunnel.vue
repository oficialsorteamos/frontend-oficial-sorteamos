<template>
  <b-card title="Age Groups">
    <!-- echart -->
    <app-echart-funnel 
      :legends="optionsData.legend.data" 
      :series="optionsData.series.data"
      :maxAgeGroup="optionsData.maxAgeGroup"
      v-if="options && options.legend"
    />

  </b-card>
</template>

<script>
import { BCard } from 'bootstrap-vue'
import { toRefs } from '@vue/composition-api'
import AppEchartFunnel from '@core/components/charts/echart/AppEchartFunnel.vue'

export default {
  components: {
    BCard,
    AppEchartFunnel,
  },
  props: {
    options: {
      //type: Array,
      required: true,
    },
  },
  data() {
    return {
      optionsData: []
    }
  },
  watch: {
    options(data) {
      console.log('arrayAge')
      console.log(data)
      this.optionsData = data
    },
  },
}
</script>

<style lang="scss">
.card-body{
  position: relative;
  .pie-text{
    width: 105px;
    position:absolute;
    margin: auto;
    left: 0;
    right: 0;
    top: 44%;
    bottom: 0;
  }
  }
</style>
