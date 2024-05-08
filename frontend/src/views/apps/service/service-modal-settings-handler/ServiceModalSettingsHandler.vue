<template>
  <b-tabs
    vertical
    content-class="col-12 col-md-9 mt-1 mt-md-0"
    pills
    nav-wrapper-class="col-md-3 col-12"
    nav-class="nav-left"
  >

    <!-- general tab -->
    <b-tab active>

      <!-- title -->
      <template #title>
        <feather-icon
          icon="RadioIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('services.serviceModalSettingsHandler.transfer') }}</span>
      </template>

      <service-setting-distribution
        v-if="options.general"
        :general-data="options.general"
      />
    </b-tab>
    <!--/ general tab -->
  </b-tabs>
</template>

<script>
import { BTabs, BTab } from 'bootstrap-vue'
import { ref} from '@vue/composition-api'
import store from '@/store'
import ServiceSettingDistribution from './service-setting-distribution/ServiceSettingDistribution.vue'

export default {
  components: {
    BTabs,
    BTab,
    ServiceSettingDistribution,
  },
  props: {
    
  },
  data() {
    return {
      options: {},
    }
  },
  beforeCreate() {
    this.$http.get('/account-setting/data').then(res => { this.options = res.data })
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
  },
}
</script>
