<template>
  <b-card>
    <b-row>
      <!-- User Info: Left col -->
      <b-col
        cols="12"
        xl="12"
        class="d-flex justify-content-between flex-column"
      >
        <div class="d-flex justify-content-between mb-1">
          <div>
          </div>
          <div 
            class="d-flex flex-sm-row-reverse"
          >
            <!-- Botão para editar os dados do contato -->
            <feather-icon icon="EditIcon" 
              size="17"
              class="cursor-pointer d-sm-block d-none float-left mr-1"
              v-b-tooltip.hover.v-secondary
              :title="$t('channel.channelViewInfoCard.editChannel')"
              @click="openModal('modal-edit-company-'+company.id)"
            />
          </div>
        </div>
        <!-- User Avatar & Action Buttons -->
        <div class="d-flex justify-content-start h-100">
          <span
            @click="$refs.importFile.$el.click()"
            style="cursor: pointer"
          >
          <b-avatar
            src=""
            :text="avatarText(company.com_name)"
            variant="light-primary"
            size="104px"
            rounded
          >
            <span class="d-flex align-items-center">
              <feather-icon
                icon="BriefcaseIcon"
                size="60"
              />
          </span>
          </b-avatar>
          </span>
          <b-form-file
            ref="importFile"
            name="importFile"
            id="importFile"
            accept=".jpeg, .jpg, .png"
            :hidden="true"
            plain
          />
          <div class="d-flex ml-1 mt-1">
            <div class="mb-1">
              <h4 class="mb-0">
                {{ company.com_name }}
              </h4>
              <div>
                <cite class="mb-0">
                  {{ company.cha_description }}
                </cite>
              </div>
              <div style="margin-bottom: 3px" v-if="company.com_white_label">
                <b-badge 
                  variant="dark"
                >
                  <span class="card-text">White Label</span>
                </b-badge>
              </div>
            </div>
          </div>
        </div>

        <!-- User Stats -->
        <!--
        <div class="d-flex align-items-center mt-2">
          <div class="d-flex align-items-center mr-2">
            <b-avatar
              variant="light-primary"
              rounded
            >
              <feather-icon
                icon="DollarSignIcon"
                size="18"
              />
            </b-avatar>
            <div class="ml-1">
              <h5 class="mb-0">
                23.3k
              </h5>
              <small>Monthly Sales</small>
            </div>
          </div>

          <div class="d-flex align-items-center">
            <b-avatar
              variant="light-success"
              rounded
            >
              <feather-icon
                icon="TrendingUpIcon"
                size="18"
              />
            </b-avatar>
            <div class="ml-1">
              <h5 class="mb-0">
                $99.87k
              </h5>
              <small>Annual Profit</small>
            </div>
          </div>
        </div>
        -->
      </b-col>

      <!-- Right Col: Table -->
      <b-col
        cols="12"
        xl="12"
        class="mt-2"
      >
        <table class="mt-2 mt-xl-0 w-100">
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="UserIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('settings.companyViewInfoCard.responsibleName') }}</span>
            </th>
            <td class="pb-50">
              <span v-if="company.com_responsible_name"> {{ company.com_responsible_name }} </span>
              <span v-else> - </span>
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="PhoneIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('channel.channelViewInfoCard.phone') }}</span>
            </th>
            <td>
              {{ company.com_phone | VMask('(##) #####-####') }}
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="MailIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('channel.channelViewInfoCard.email') }}</span>
            </th>
            <td class="pb-50">
              <span v-if="company.com_email"> {{ company.com_email }} </span>
              <span v-else> - </span>
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="PhoneIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('settings.companyViewInfoCard.financePhoneNumber') }}</span>
            </th>
            <td>
              {{ company.com_finance_phone | VMask('(##) #####-####') }}
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="MailIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('settings.companyViewInfoCard.financeEmail') }}</span>
            </th>
            <td class="pb-50">
              <span v-if="company.com_finance_email"> {{ company.com_finance_email }} </span>
              <span v-else> - </span>
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="FileIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{company.com_cpf? 'CPF' : 'CNPJ'}}</span>
            </th>
            <td class="pb-50">
              <span v-if="company.com_cpf"> {{ company.com_cpf | VMask('###.###.###-##') }}</span>
              <span v-else-if="company.com_cnpj"> {{ company.com_cnpj | VMask('##.###.###/####-##') }} </span>
              <span v-else> - </span>
            </td>
          </tr>
          <tr>
            <th class="pb-50 d-flex">
              <feather-icon
                icon="CalendarIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('channel.channelViewInfoCard.created') }}</span>
            </th>
            <td class="pb-50">
              {{ formatDateTime(company.created_at) }}
            </td>
          </tr>
          <tr>
            <th 
              class="pb-50 mr-3 d-flex"
            >
              <feather-icon
                icon="MapPinIcon"
                class="mr-75"
              />
              <span class="font-weight-bold" style="vertical-align: middle !important;">{{ $t('channel.channelViewInfoCard.address') }}</span>
            </th>
            <td class="pb-50">
              <span v-if="company.com_address && company.com_postal_code"> {{ company.com_address }}, {{ company.com_address_number? 'Nº'+company.com_address_number: 'S/N' }}, {{ company.com_province }}, {{ company.com_city }}, {{ company.com_state }}, {{company.com_country}}, CEP: {{ company.com_postal_code }}</span>
              <span v-else> - </span>
            </td>
          </tr>
        </table>
      </b-col>
    </b-row>
    <!-- Form para cadastro de edição de um canal -->
    <b-modal
      :id="'modal-edit-company-'+company.id"
      title="Edit Company"
      hide-footer
      size="lg"
    >
      <company-modal-edit-company-handler
        :company="company"
        :clear-contact-data="clearContactData"
        @set-company="setCompany"
        @hide-modal="hideModal"
      />
    </b-modal>
  </b-card>
</template>

<script>
import {
  BCard, BButton, BAvatar, BRow, BCol, BBadge, BFormRating, VBTooltip, VBModal, BFormFile,
} from 'bootstrap-vue'
import {
  ref
} from '@vue/composition-api'
import { avatarText } from '@core/utils/filter'
import { VueMaskFilter } from 'v-mask'
import store from '@/store'
import { formatDate, formatDateTime } from '@core/utils/filter'
import CompanyModalEditCompanyHandler from './company-modal-edit-company-handler/CompanyModalEditCompanyHandler.vue'
import Swal from 'sweetalert2'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)

export default {
  components: {
    BCard, 
    BButton, 
    BRow, 
    BCol, 
    BAvatar, 
    BBadge,
    BFormRating,
    VBModal,
    BFormFile,

    //Formata a data
    formatDate,
    formatDateTime,

    CompanyModalEditCompanyHandler,
  },
  props: {
    company: {
      type: Object,
      required: true,
    },
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    openModal(modalName) {
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
    startSession(channelData) {
      this.$emit('start-session', channelData)
    },
  },
  setup(props, { emit }) {

    //Toast Notification
    const toast = useToast()

    const blankChannel = {
      cha_name: '',
      cha_company_email: '',
    }
    const channelData = ref(JSON.parse(JSON.stringify(blankChannel)))
    //Limpa os dados do popup
    const clearContactData = () => {
      channelData.value = JSON.parse(JSON.stringify(blankContact))
    }

    //Atualiza os dados do contato
    const setCompany = companyData => {
      emit('set-company', companyData)
    }

    return {
      avatarText,

      formatDate,
      formatDateTime,
      setCompany,
      clearContactData,

      channelData,
    }
  },
}
</script>

<style>

</style>
