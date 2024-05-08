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
        <span class="font-weight-bold">{{ $t('administrationCompany.companyModalSettingHandler.status') }}</span>
      </template>

      <company-settings-status
        v-if="options.general"
        :general-data="options.general"
        :company="company"
        @update-company-status="updateCompanyStatus"
      />
    </b-tab>
    <!-- Plan -->
    <b-tab
      v-if="!isWhiteLabel"
    >
      <template #title>
        <feather-icon
          icon="ClipboardIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('administrationCompany.companyModalSettingHandler.plan') }}</span>
      </template>

      <company-settings-plan
        v-if="options.general"
        :general-data="options.general"
        :company="company"
        @update-company-plan="updateCompanyPlan"
      />
    </b-tab>
    <!-- Fee -->
    <!--
    <b-tab
      v-if="!isWhiteLabel"
    >
      <template #title>
        <feather-icon
          icon="DollarSignIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('administrationCompany.companyModalSettingHandler.fees') }}</span>
      </template>

      <company-settings-fee
        v-if="options.general"
        :general-data="options.general"
        :company="company"
        @update-company-fees="updateCompanyFees"
      />
    </b-tab>
    -->
    <!-- Charge -->
    <!-- Se NÃO for o ambiente de um White Label -->
    <b-tab
      v-if="!isWhiteLabel"
    >
      <!-- title -->
      <template #title>
        <feather-icon
          icon="CheckSquareIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('administrationCompany.companyModalSettingHandler.charges') }}</span>
      </template>

      <company-settings-charge
        v-if="options.general"
        :general-data="options.general"
        :company="company"
        @update-company-charges="updateCompanyCharges"
      />
    </b-tab>
    
    <!--/ general tab -->
  </b-tabs>
  
</template>

<script>
import { BTabs, BTab } from 'bootstrap-vue'
import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import CompanySettingsStatus from './company-setting-status/CompanySettingsStatus.vue'
import CompanySettingsPlan from './company-setting-plan/CompanySettingsPlan.vue'
import CompanySettingsFee from './company-setting-fee/CompanySettingsFee.vue'
import CompanySettingsCharge from './company-setting-charge/CompanySettingsCharge.vue'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BTabs,
    BTab,
    CompanySettingsStatus,
    CompanySettingsPlan,
    CompanySettingsFee,
    CompanySettingsCharge,
  },
  props: {
    company: {
      type: Object,
      required: true,
    },
    isWhiteLabel: {
      type: Number,
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

    const updateCompanyPlan = companyData => {
      store.dispatch('app-company/updateCompanyPlan', companyData)
        .then(response => {
          props.company.plan = response.data.companyPlan
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

    const updateCompanyFees = companyData => {
      store.dispatch('app-company/updateCompanyFees', companyData)
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
      updateCompanyPlan,
      updateCompanyFees,
      updateCompanyCharges,
    }
  }
}
</script>
