<template>
  <b-card>
    <b-card-title class="mb-1">
      {{ $t('dashboard.contactsDensity.contactsDensity') }} 
    </b-card-title>
    <b-card-sub-title class="mb-2">
      {{ $t('dashboard.contactsDensity.amountContactsStates') }}
    </b-card-sub-title>
    <vue-chart
      chart-type="GeoChart"
      region="ID"
      :columns="columns"
      :rows="rows"
      :options="options"
      v-if="contactsState"
    ></vue-chart>
  </b-card>
</template>

<script>
import { BCard, BCardTitle, BCardSubTitle } from 'bootstrap-vue'
import { toRefs } from '@vue/composition-api'
import VueCharts from "vue-charts"
import Vue from "vue"
Vue.use(VueCharts)



export default {
  components: {
    BCard,
    BCardTitle,
    BCardSubTitle
  },
  props: {
    contactsState: {
      //type: Array,
      required: true,
    },
  },
  data: function () {
    return {
      columns: [
        {
          type: "string",
          label: "Country",
        },
        {
          type: "number",
          label: "Contacts",
        },
      ],
      rows: [],
      options: {
        region: "BR",
        resolution: "provinces",
        title: "Statistiques",
        width: '100%',
        height: 450,
        colorAxis: {
          colors: [
            "#fff",
            //"#900",
            //"#ABC",
            //"#918",
            "#549",
            //"#019",
            //"#FFF",
            //"#384",
          ],
        },
        backgroundColor: "#81d4fa",
        datalessRegionColor: "#fff",
        defaultColor: "#fff",
      },
    }
  },
  watch: {
    contactsState(data) {
      this.rows = Object.entries(data)
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
