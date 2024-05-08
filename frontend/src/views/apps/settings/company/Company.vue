<template>
  <component :is="company === undefined ? 'div' : 'b-card'"
    style="background-color: #f6f6f6;"
  >
    <!-- First Row -->
    <b-row
        class="mb-1">
      <b-col
        cols="12"
        xl="5"
        lg="8"
        md="7"
      >
        <company-view-info-card 
          :company="company"
          class="h-100"
          @set-company="setCompany"
          v-if="company"
        />
      </b-col>
      <b-col
        cols="12"
        xl="7"
        lg="8"
        md="7"
        class="scroll-col"
      >
        <company-details-tab-card 
          :plan="plan"
          :whiteLabel="whiteLabel"
          :parameters-charge="parametersCharge"
          :parameters-general="parametersGeneral"
          :hidden-button-service="hiddenButtonService"
          @set-plan="setPlan"
          @set-white-label="setWhiteLabel"
          @set-general-data="setGeneralData"
          class="h-100"
          v-if="plan && parametersCharge"
        /> 
      </b-col>
    </b-row>
  </component>
</template>

<script>
import {
  BTab, BTabs, BCard, BAlert, BLink, BRow, BCol, BBadge, BButton, VBTooltip,
} from 'bootstrap-vue'
import { ref, toRefs } from '@vue/composition-api'
import CompanyViewInfoCard from './CompanyViewInfoCard.vue'
import CompanyDetailsTabCard from './CompanyDetailsTabCard.vue'
import useCompany from './useCompany'

export default {
  components: {
    BTab,
    BTabs,
    BCard,
    BAlert,
    BLink,
    BRow,
    BCol,
    BBadge,
    BButton,

    CompanyViewInfoCard,
    CompanyDetailsTabCard,
  },

  directives: {
    'b-tooltip': VBTooltip,
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
  },
  setup(props, {emit}) {


    const {
      fetchCompany,
      company,
      plan,
      whiteLabel,
      parametersCharge,
      parametersGeneral,
      servicesData,
      hiddenButtonService,
    } = useCompany(toRefs(props), emit)

    
    fetchCompany()
    

    const channel = ref(null)

    const blankChannel = {
      cha_name: '',
      cha_description: '',
      cha_company_name: '',
      cha_company_email: '',
      cha_company_site: '',
      cha_company_address: '',
      cha_phone_number: '',
      cha_app_name_api: '',
      cha_api_official: false,
      api: '',
      whatsapp_business_account_id: '',
      cha_app_id_api: '',
      cha_channel_id_api: '',
      cha_session_token: '',
    }
    const channelData = ref(JSON.parse(JSON.stringify(blankChannel)))
    //Limpa os dados do popup
    const clearContactData = () => {
      channelData.value = JSON.parse(JSON.stringify(blankContact))
    }

    //Atualiza os dados da empresa
    const setCompany = companyData => {
      company.value = companyData
    }

    //Atualiza os dados do plano
    const setPlan = planData => {
      plan.value = planData
    }

    //Atualiza os dados do plano
    const setWhiteLabel = whiteLabelData => {
      whiteLabel.value = whiteLabelData
    }

    const setGeneralData = generalData => {
      parametersGeneral.value = generalData
    }

    return {
      company,
      plan,
      whiteLabel,
      parametersCharge,
      parametersGeneral,
      channelData,
      servicesData,
      hiddenButtonService,
      fetchCompany,
      setCompany,
      setPlan,
      setWhiteLabel,
      setGeneralData,
      clearContactData,
    }
  },
}
</script>

<style>
  .scroll-col {
    max-height: 500px; 
    overflow-y: scroll;
  }
</style>
