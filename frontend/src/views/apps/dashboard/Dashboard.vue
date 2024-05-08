<template>
  <section id="dashboard-ecommerce">
    <b-row class="match-height">
      <b-col
        xl="8"
        md="6"
      >
        <general-statistics 
          :statistics-data="statisticsData" 
        />
      </b-col>
      <b-col
        xl="4"
        md="6"
      >
        <services-status 
          :data="servicesStatusData"
        />
        <!--
        <ecommerce-medal :data="data.congratulations" />
        -->
      </b-col>
    </b-row>

    <b-row class="match-height">
      <b-col lg="4">
        <contacts-gender 
          :genderData="contactsGenderData"
        />
      </b-col>
      <!-- Revenue Report Card -->
      <b-col lg="4">
        <contacts-age-groups 
          :labels="contactsAgeGroupData.labels"
          :dataAgeGroup="contactsAgeGroupData.dataAgeGroup"
        />
        <!--
        <ecommerce-revenue-report :data="data.revenue" />
        -->
      </b-col>
      <!-- Company Table Card --> 
      <b-col 
        lg="4"
        style="max-height: 510px; overflow-y: scroll;"
      >
        <operator-ranking
          :table-data="bestOperatorsData" 
        />
      </b-col>
      <!--/ Revenue Report Card -->
    </b-row>

    <b-row class="match-height">
      <!--/ Company Table Card -->
      <b-col md="7">
        <services-month 
          :labels="servicesLastMonthsData.labels"
          :countServices="servicesLastMonthsData.countServices"
          :maxCountService="servicesLastMonthsData.maxCountService"
        />
      </b-col>
      <!-- Developer Meetup Card -->
      <b-col
        lg="5"
        md="10"
      >
        <contacts-density
          :contactsState="contactsStateData"
        />
        <!--
        <ecommerce-meetup :data="data.meetup" />
        -->
      </b-col>
    </b-row>
  </section>
</template>

<script>
import {
  ref, onUnmounted,
} from '@vue/composition-api'
import store from '@/store'
import { BRow, BCol } from 'bootstrap-vue'
import dashboardStoreModule from './dashboardStoreModule'
import { getUserData } from '@/auth/utils'
import GeneralStatistics from './GeneralStatistics.vue'
import ServicesStatus from './ServicesStatus.vue'
import OperatorRanking from './OperatorRanking.vue'
import ContactsGender from './ContactsGender.vue'
import ContactsAgeGroups from './ContactsAgeGroups.vue'
import ServicesMonth from './ServicesMonth.vue'
import ContactsDensity from './ContactsDensity.vue'

export default {
  components: {
    BRow,
    BCol,

    GeneralStatistics,
    OperatorRanking,
    ContactsGender,
    ContactsAgeGroups,
    ServicesMonth,
    ContactsDensity,
    ServicesStatus,
  },
  data() {
    return {
      data: {},
    }
  },
  created() {
    // data
    this.$http.get('/ecommerce/data')
      .then(response => {
        this.data = response.data

        // ? Your API will return name of logged in user or you might just directly get name of logged in user
        // ? This is just for demo purpose 
        const userData = getUserData()
        this.data.congratulations.name = userData.name.split(' ')[0] || userData.username
      })
  },
  setup() {
    const DASHBOARD_APP_STORE_MODULE_NAME = 'app-dashboard'

    // Register module
    if (!store.hasModule(DASHBOARD_APP_STORE_MODULE_NAME)) store.registerModule(DASHBOARD_APP_STORE_MODULE_NAME, dashboardStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(DASHBOARD_APP_STORE_MODULE_NAME)) store.unregisterModule(DASHBOARD_APP_STORE_MODULE_NAME)
    })
    const statisticsData = ref([])
    const contactsGenderData = ref([])
    const contactsAgeGroupData = ref([])
    const bestOperatorsData = ref([])
    const servicesLastMonthsData = ref([])
    const contactsStateData = ref([])
    const servicesStatusData = ref([])

    //Traz os dados estatísticos
    const fetchStatistics = () => {
      store.dispatch('app-dashboard/fetchStatistics')
        .then(response => {
          statisticsData.value = response.data
        })
    }
    fetchStatistics()

    //Traz a quantidade de atendimentos por status
    const fetchServicesByStatus = () => {
      store.dispatch('app-dashboard/fetchServicesByStatus')
        .then(response => {
          servicesStatusData.value = response.data
          //console.log(response.data)
        })
    }
    fetchServicesByStatus()

    //Traz a quantidade de contatos por gêneros
    const fetchContactsByGender = () => {
      store.dispatch('app-dashboard/fetchContactsByGender')
        .then(response => {
          contactsGenderData.value = response.data
          //console.log(response.data)
        })
    }
    fetchContactsByGender()

    //Traz a quantidade de contatos por faixa etária
    const fetchContactsByAgeGroup = () => {
      store.dispatch('app-dashboard/fetchContactsByAgeGroup')
        .then(response => {
          contactsAgeGroupData.value = response.data
          //console.log(response.data)
        })
    }
    fetchContactsByAgeGroup()

    //Traz os melhores operadores
    const fetchBestOperators = () => {
      store.dispatch('app-dashboard/fetchBestOperators')
        .then(response => {
          bestOperatorsData.value = response.data
          //console.log(response.data)
        })
    }
    fetchBestOperators()

    //Traz a quantidade de atendimentos nos últimos meses
    const fetchServicesByLastMonths = () => {
      store.dispatch('app-dashboard/fetchServicesByLastMonths')
        .then(response => {
          servicesLastMonthsData.value = response.data
          //console.log(response.data)
        })
    }
    fetchServicesByLastMonths()

    //Traz a de contatos por Estado
    const fetchContactsPerState = () => {
      store.dispatch('app-dashboard/fetchContactsPerState')
        .then(response => {
          contactsStateData.value = response.data
          //console.log(response.data)
        })
    }
    fetchContactsPerState()

    return {
      fetchStatistics,
      fetchContactsByGender,
      fetchContactsByAgeGroup,
      fetchBestOperators,
      fetchServicesByLastMonths,
      fetchContactsPerState,
      fetchServicesByStatus,

      statisticsData,
      contactsGenderData,
      contactsAgeGroupData,
      bestOperatorsData,
      servicesLastMonthsData,
      contactsStateData,
      servicesStatusData
    }
  }
}
</script>

<style lang="scss">
@import '@core/scss/vue/pages/dashboard-ecommerce.scss';
@import '@core/scss/vue/libs/chart-apex.scss';
</style>
