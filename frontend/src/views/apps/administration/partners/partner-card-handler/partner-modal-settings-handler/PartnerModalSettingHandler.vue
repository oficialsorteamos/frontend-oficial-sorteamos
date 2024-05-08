<template>
  <b-tabs
    vertical
    content-class="col-12 col-md-9 mt-1 mt-md-0"
    pills
    nav-wrapper-class="col-md-3 col-12"
    nav-class="nav-left"
  >

    <!-- general tab -->
    <!--
    <b-tab active>
      <template #title>
        <feather-icon
          icon="RadioIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('administrationCompany.companyModalSettingHandler.status') }}</span>
      </template>

      <partner-settings-status
        v-if="options.general"
        :general-data="options.general"
        :company="company"
        @update-company-status="updateCompanyStatus"
      />
    </b-tab>
    -->
    <!-- Commission -->
    <!-- Se for um parceiro REVENDEDOR -->
    <b-tab
      v-if="partner.type_partner_id == 1"
    >
      <template #title>
        <feather-icon
          icon="AwardIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('partner.partnerModalSettingHandler.commission') }}</span>
      </template>

      <partner-settings-commission
        v-if="options.general"
        :general-data="options.general"
        :partner="partner"
        @update-commission="updateCommission"
      />
    </b-tab>

    <!-- Fee -->
    <!-- Se for um parceiro WHITE LABEL -->
    <b-tab
      v-if="partner.type_partner_id == 2"
    >
      <template #title>
        <feather-icon
          icon="DollarSignIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('administrationCompany.companyModalSettingHandler.fees') }}</span>
      </template>

      <partner-settings-fee
        v-if="options.general"
        :general-data="options.general"
        :company="partner"
        @update-partner-fees="updatePartnerFees"
      />
    </b-tab>
    
    
    <!--/ general tab -->
  </b-tabs>
  
</template>

<script>
import { BTabs, BTab } from 'bootstrap-vue'
import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import PartnerSettingsStatus from './partner-setting-status/PartnerSettingsStatus.vue'
import PartnerSettingsCommission from './partner-setting-commission/PartnerSettingsCommission.vue'
import PartnerSettingsFee from './partner-setting-fee/PartnerSettingsFee.vue'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BTabs,
    BTab,
    PartnerSettingsStatus,
    PartnerSettingsCommission,
    PartnerSettingsFee,
  },
  props: {
    partner: {
      type: Object,
      required: true,
    },
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
  setup(props) {

    //Toast Notification
    const toast = useToast()

    //########################## SETTINGS ##############################
    const updateCompanyStatus = companyData => {
      store.dispatch('app-company/updateCompanyStatus', {
        companyId: companyData.id,
        statusId: companyData.status.id
      })
        .then(response => {
          props.company.status_id = response.data
          toast({
            component: ToastificationContent,
            props: {
              title: 'Configurações atualizadas com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const updateCommission = partnerData => {
      store.dispatch('app-partner/updateCommission', partnerData)
        .then(response => {
          props.partner.commission = response.data.commission
          toast({
            component: ToastificationContent,
            props: {
              title: 'Configurações atualizadas com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const updatePartnerFees = partnerData => {
      store.dispatch('app-partner/updatePartnerFees', partnerData)
        .then(response => {
          //props.company.plan = response.data.companyPlan
          toast({
            component: ToastificationContent,
            props: {
              title: 'Configurações atualizadas com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const updateCompanyCharges = companyData => {
      store.dispatch('app-company/updateCompanyCharges', companyData)
        .then(response => {
          //props.company.plan = response.data.companyPlan
          toast({
            component: ToastificationContent,
            props: {
              title: 'Configurações atualizadas com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }


    return {
      updateCompanyStatus,
      updateCommission,
      updatePartnerFees,
      updateCompanyCharges,
    }
  }
}
</script>
