<template>
  <b-card>

    <b-card
      no-body
      class="mb-0"
    >
      <div class="m-2">
        <!-- Table Top -->
        <b-row>

          <!-- Per Page -->
          <b-col
            cols="12"
            md="11"
            class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
          >
            <label>{{ $t('campaign.show') }}</label>
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>{{ $t('campaign.entries') }}</label>
          </b-col>

          <!-- Search -->
          <b-col
            cols="12"
            md="1"
            class="text-right"
          >
            
          </b-col>
        </b-row>
      </div>
    
      <b-table
        ref="refCreditListTable"
        :items="creditItems"
        :fields="tableColumns"
        responsive
        show-empty
        :empty-text="$t('credit.noCreditsFound')"
      >

      <template  #cell(fai_name)="data">
        <div class="text-nowrap">
          <span
            v-if="data.item.fai_main == 1"
          >
            <feather-icon 
              icon="StarIcon" 
              size="16"
              v-b-tooltip.hover.v-secondary
              title="Configuração Principal"
            />
          </span>
          <span class="align-text-top">{{ data.item.fai_name }}
          </span>
        </div>
      </template>

      <template #cell(fai_description)="data">
        <div class="text-nowrap">
          <span class="align-text-top">{{ data.item.fai_description }}</span>
        </div>
      </template>

      <template  #cell(channels)="data">
        <span
          v-for="channel in data.item.channels"
          :key="channel.id"
        >
          <b-badge
            variant="dark"
            style="margin-right: 5px; margin-top: 5px"
          >
            {{ channel.cha_name }}
          </b-badge>
        </span>
      </template>

      <template  #cell(users)="data">
        <span
          v-for="user in data.item.users"
          :key="user.id"
        >
          <b-badge
            variant="primary"
            style="margin-right: 5px; margin-top: 5px" 
          >
            {{ user.name }}
          </b-badge>
        </span>
      </template>

      <template #cell(actions)="data">
        <b-dropdown
          variant="link"
          no-caret
          :right="$store.state.appConfig.isRTL"
        >

          <template #button-content>
            <feather-icon
              icon="MoreVerticalIcon"
              size="16"
              class="align-middle text-body"
            />
          </template>

          <b-dropdown-item 
            @click="openModal('modal-edit-fair-distribution-'+data.item.id)"
          >
            <feather-icon icon="EditIcon" />
            <span class="align-middle ml-50">{{ $t('department.edit') }}</span>
          </b-dropdown-item>
          
          <b-dropdown-item
            @click="data.item.restrictionDelete? '' : removeFairDistribution(data.item.id)"
            :id="'pendencies-message'+data.item.id"
          >
            <feather-icon icon="TrashIcon" />
            <span class="align-middle ml-50">Remover</span>
            <b-tooltip v-if="data.item.restrictionDelete" :target="'pendencies-message'+data.item.id"><span v-html="data.item.restrictionDelete"> </span> </b-tooltip>
          </b-dropdown-item>
        </b-dropdown>
        <!-- Edita uma configuração de distribuição igualitária -->
        <b-modal
          :id="'modal-edit-fair-distribution-'+data.item.id"
          :title="$t('services.serviceSettingDistribution.updateFairDistribution')"
          hide-footer
          size="lg"
        >
          <!-- select 2 demo -->
          <service-modal-fair-distribution-handler
            :fair-distribution="data.item"
            @hide-modal="hideModal"
            @update-fair-distribution="updateFairDistribution"
          />
        </b-modal>
      </template>
    </b-table>
    <!-- Pagination -->
    <div class="mx-2 mb-2">
      <b-row>
        <b-col
          cols="12"
          sm="6"
          class="d-flex align-items-center justify-content-center justify-content-sm-start"
        >
          <span class="text-muted">{{ $t('campaign.showing') }} {{ dataMeta.from }} {{ $t('campaign.to') }} {{ dataMeta.to }} {{ $t('campaign.of') }} {{ dataMeta.of }} {{ $t('campaign.entries') }}</span>
        </b-col>
        <b-col
          cols="12"
          sm="6"
          class="d-flex align-items-center justify-content-center justify-content-sm-end"
        >

          <b-pagination
            v-model="currentPage"
            :total-rows="totalCredits"
            :per-page="perPage"
            first-number
            last-number
            class="mb-0 mt-1 mt-sm-0"
            prev-class="prev-item"
            next-class="next-item"
          >
            <template #prev-text>
              <feather-icon
                icon="ChevronLeftIcon"
                size="18"
              />
            </template>
            <template #next-text>
              <feather-icon
                icon="ChevronRightIcon"
                size="18"
              />
            </template>
          </b-pagination>
        </b-col>
      </b-row>
    </div>
  </b-card>

    <b-row>

      <b-col cols="12">
        <b-button
          v-ripple.400="'rgba(255, 255, 255, 0.15)'"
          variant="dark"
          class="mt-2 mr-1 float-right"
          v-b-modal.modal-add-fair-distribution
        >
          {{ $t('services.serviceSettingDistribution.add') }}
        </b-button>
      </b-col>
    </b-row>
      

    <!-- Adiciona uma configuração de distribuição igualitária -->
    <b-modal
      id="modal-add-fair-distribution"
      :title="$t('services.serviceSettingDistribution.addFairDistribution')"
      hide-footer
      size="lg"
    >
      <!-- select 2 demo -->
      <service-modal-fair-distribution-handler
        :fair-distribution="{}"
        @hide-modal="hideModal"
        @add-fair-distribution="addFairDistribution"
      />
    </b-modal>
  </b-card>
</template>

<script>
import {
  BFormFile, BButton, BForm, BFormGroup, BFormInput, BRow, BCol, BAlert, BCard, BCardText, BMedia, BMediaAside, BMediaBody, 
  BLink, BImg, BBadge, BFormRating, BFormInvalidFeedback, VBTooltip, BTable, BPagination, BDropdownItem, BDropdown, BTooltip,
} from 'bootstrap-vue'
import store from '@/store'
import Ripple from 'vue-ripple-directive'
import { useInputImageRenderer } from '@core/comp-functions/forms/form-utils'
import { ref, toRefs } from '@vue/composition-api'
import axios from '@axios'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import ServiceModalFairDistributionHandler from './service-modal-fair-distribution-handler/ServiceModalFairDistributionHandler.vue'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import useServiceSettingDistributionList from './useServiceSettingDistributionList'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import vSelect from 'vue-select'

export default {
  components: {
    BButton,
    BForm,
    BImg,
    BFormFile,
    BFormGroup,
    BFormInput,
    BRow,
    BCol,
    BAlert,
    BCard,
    BCardText,
    BMedia,
    BMediaAside,
    BMediaBody,
    BLink,
    BBadge,
    BFormRating,
    BFormInvalidFeedback,
    vSelect,
    BTable,
    BPagination,
    BDropdownItem,
    BDropdown,
    BTooltip,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    ServiceModalFairDistributionHandler,
  },
  directives: {
    Ripple,
    'b-tooltip': VBTooltip,
  },
  props: {
    generalData: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return { 
      channelsData: [],
      usersData: [],
    }
  },
  created() { 
    //Traz os canais 
    axios
      .get('/api/management/channel/fetch-channels-by-status/A')
      .then(response => {
        console.log(response.data)
        this.channelsData = response.data.channels
      });

    //Traz os operadores
    axios
      .get('/api/management/user/get-users-by-role/2')
      .then(response => {
        console.log(response.data)
        this.usersData = response.data
      });
  },
  methods: {
    resetForm() {
      this.optionsLocal = JSON.parse(JSON.stringify(this.generalData))
    },
    //Seta número completo
    setPhoneNumber: function(data) {
      this.phoneNumber = data.formattedNumber
    },
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    openModal(modalName) {
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
  },
  setup(props, { emit }) {
    const refInputEl = ref(null)
    const previewEl = ref(null)

    const { inputImageRenderer } = useInputImageRenderer(refInputEl, previewEl)

    const toast = useToast()

    const {
      creditItems,
      perPage,
      currentPage,
      perPageOptions,
      refCreditListTable,
      loadingRefresh,
      dataMeta,
      tableColumns,
      totalCredits,
      fetchFairDistribution,
      addCard,
      addCredit,
      cardsData,

      resolveStatusVariant,
      checkFlagCard,

    } = useServiceSettingDistributionList()

    fetchFairDistribution()

    //Adiciona uma configuração de transferência igualitária
    const addFairDistribution = distributionData => {
      store.dispatch('app-service/addFairDistribution', distributionData)
        .then(() => {  
          fetchFairDistribution()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Configuração para distribuição igualitária adicionada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const updateFairDistribution = distributionData => {
      //console.log('dados os canais')
      //console.log(channelData.channels)
      store.dispatch('app-service/updateFairDistribution', distributionData)
        .then(() => {
          fetchFairDistribution()

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

    //Remove uma mensagem da campanha
    const removeFairDistribution = fairDistributionId => {
      store.dispatch('app-service/removeFairDistribution', { id: fairDistributionId })
        .then(() => {
          fetchFairDistribution()

          toast({
            component: ToastificationContent,
            props: {
              title: 'Configuração de distribuição igualitária removida com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    return {
      refInputEl,
      previewEl,
      inputImageRenderer,

      addFairDistribution,
      updateFairDistribution,
      removeFairDistribution,

      creditItems,
      perPage,
      currentPage,
      perPageOptions,
      refCreditListTable,
      loadingRefresh,
      dataMeta,
      tableColumns,
      totalCredits,
      fetchFairDistribution,
      addCard,
      addCredit,
      cardsData,

      resolveStatusVariant,
      checkFlagCard,
    }
  },
}

import 'vue-phone-number-input/dist/vue-phone-number-input.css';
</script>
