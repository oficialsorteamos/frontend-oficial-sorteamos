<template>
  <b-card> >
    <!-- form -->
    <b-form
      enctype="multipart/form-data"
      @submit.prevent="handleSubmit(onSubmit)"
    >
      <b-table
          ref="refUserListTable"
          class="position-relative mt-2"
          :items="fowardingSettingsData"
          responsive
          :fields="tableColumns"
          primary-key="ser_protocol_number"
          :sort-by.sync="sortBy"
          show-empty
          :empty-text="$t('dialer.listDialerSettingForwarding.noFowardingSettingsFound')"
          :sort-desc.sync="isSortDirDesc"
        >
          <template #cell(channel)="data">
            <span
              class="font-weight-bold d-block text-nowrap"
              v-b-tooltip.hover.v-secondary
              :title="data.item.channel.cha_name"
            >
              {{ data.item.channel.cha_name.length > 20? data.item.channel.cha_name.substring(0,20)+'...' : data.item.channel.cha_name }}
            </span>
          </template>

          <template #cell(dia_message)="data">
            <span
              v-if="data.item.dia_message"
            >
              <div 
                class="text-nowrap"
                :id="'dia_message'+data.index" 
              >
                <span class="align-text-top" style="white-space: pre-wrap" v-if="data.item.dia_message.length > 50" v-html="data.item.dia_message.substring(0,50)+'...'">
                </span>
                <span class="align-text-top" style="white-space: pre-wrap" v-else v-html="data.item.dia_message.substring(0,50)">
                </span>
              </div>
              <b-tooltip :target="'dia_message'+data.index" v-if="data.item.dia_message"><span v-html="data.item.dia_message"> </span> </b-tooltip>
            </span>
            <span
              v-else
            >
              <div 
                class="text-nowrap"
                :id="'dia_message'+data.index" 
                v-if="data.item.template"
              >
                <span class="align-text-top" style="white-space: pre-wrap" v-if="data.item.template.body.length > 50" v-html="data.item.template.body.substring(0,50)+'...'">
                </span>
                <span class="align-text-top" style="white-space: pre-wrap" v-else v-html="data.item.template.body.substring(0,50)">
                </span>
              </div>
              <b-tooltip :target="'dia_message'+data.index" v-if="data.item.template"><span v-html="data.item.template.body"> </span> </b-tooltip>
            </span>
            
        </template>

          <template #cell(chatbot)="data">
            <span
              class="font-weight-bold d-block text-nowrap"
              v-if="data.item.chatbot"
              v-b-tooltip.hover.v-secondary
              :title="data.item.chatbot.cha_name"
            >
              {{ data.item.chatbot.cha_name.length > 20? data.item.chatbot.cha_name.substring(0,20)+'...' : data.item.chatbot.cha_name }}
            </span>
          </template>

          <template #cell(department)="data">
            <span
              class="font-weight-bold d-block text-nowrap"
              v-b-tooltip.hover.v-secondary
              :title="data.item.department.dep_name"
            >
              {{ data.item.department.dep_name.length > 20? data.item.department.dep_name.substring(0,20)+'...' : data.item.department.dep_name }}
            </span>
          </template>

          <template #cell(fair_distribution)="data">
            <span
              class="font-weight-bold d-block text-nowrap"
              v-b-tooltip.hover.v-secondary
              :title="data.item.fair_distribution.fai_name"
              v-if="data.item.fair_distribution"
            >
              {{ data.item.fair_distribution.fai_name.length > 20? data.item.fair_distribution.fai_name.substring(0,20)+'...' : data.item.fair_distribution.fai_name }}
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
                @click="openModal('modal-settings-fowarding-'+data.item.id)"
              >
                <feather-icon icon="EditIcon" />
                <span class="align-middle ml-50">{{ $t('dialer.listDialerSettingForwarding.edit') }}</span>
              </b-dropdown-item>
              <b-dropdown-item
                @click="removeFowardingSetting(data.item.id)"
              >
                <feather-icon icon="TrashIcon" />
                <span class="align-middle ml-50">{{ $t('dialer.listDialerSettingForwarding.delete') }}</span>
              </b-dropdown-item>
            </b-dropdown>
            <!-- Contém os canais associados oa chatbot -->
            <b-modal
              :id="'modal-settings-fowarding-'+data.item.id"
              :title="$t('dialer.listDialerSettingForwarding.addSetting')"
              hide-footer
              size="lg"
            >
              <!-- select 2 demo -->
              <dialer-modal-setting-fowarding-handler
                :dialer="dialer"
                :fowarding-setting="data.item"
                :clear-chatbot-data="clearChatbotData"
                :base-url-storage="baseUrlStorage"
                @hide-modal="hideModal"
                @update-fowarding-setting="updateFowardingSetting"
              />
            </b-modal>
          </template>
        </b-table>
      <!-- Form Actions -->
      <div class="d-flex mt-2 modal-footer">
        <b-button
          v-ripple.400="'rgba(255, 255, 255, 0.15)'"
          v-b-modal.modal-settings-fowarding
          variant="dark"
        >
          <feather-icon
            icon="PlusIcon"
            class="mr-50"
          />
          <span class="align-middle">{{ $t('dialer.listDialerSettingForwarding.addSetting') }}</span>
        </b-button>
      </div>
    </b-form>

    <b-modal
      id="modal-settings-fowarding"
      :title="$t('dialer.listDialerSettingForwarding.addSetting')"
      hide-footer
      size="lg"
    >
      <!-- select 2 demo -->
      <dialer-modal-setting-fowarding-handler
        :dialer="dialer"
        :fowarding-setting="fowardingSettingData"
        :base-url-storage="baseUrlStorage"
        :clear-chatbot-data="clearChatbotData"
        @hide-modal="hideModal"
        @add-fowarding-setting="addFowardingSetting"
      />
    </b-modal>
  </b-card>
</template>

<script>
import {
  BButton, BForm, BFormGroup, BFormInput, BRow, BCol, BCard, BInputGroup, BInputGroupAppend, BFormInvalidFeedback, BTable, VBTooltip,
  BBadge, BListGroup, BTooltip, BDropdown, BDropdownItem,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { ref } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import DialerModalSettingFowardingHandler from './dialer-modal-setting-fowarding-handler/DialerModalSettingFowardingHandler'
import useDialerSettingForwarding from './useDialerSettingForwarding'
import { togglePasswordVisibility } from '@core/mixins/ui/forms'
import axios from '@axios'
import vSelect from 'vue-select'
import Swal from 'sweetalert2'

export default {
  components: {
    BButton,
    BForm,
    BFormGroup,
    BFormInput,
    BTable,
    BRow,
    BCol,
    BCard,
    BInputGroup,
    BInputGroupAppend,
    BFormInvalidFeedback,
    BBadge,
    BListGroup,
    BTooltip,
    BDropdown, 
    BDropdownItem,
    vSelect,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    DialerModalSettingFowardingHandler,
  },
  directives: {
    'b-tooltip': VBTooltip,
    Ripple,
  },
  props: {
    dialer: {
      type: Object,
      required: true,
    },
    fetchChannelsChatbots: {
      type: Function,
      required: false,
    },
  },
  mixins: [togglePasswordVisibility],
  data() {
    return {
      optionSelected: '',
      departments: [],
      channels: [],
      fair_distribution: [],
      channels_chatbot: [],
      chatbotsCampaign: [],
    }
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
  },
  watch: {
    optionSelected(optionSelected) {
      this.settingsLocal.channel = optionSelected
      //Caso exista algum departamento selecionado
      if(optionSelected) {
        //Traz os chatbots que possuem um determinado canal associado para serem inseridos em uma campanha
        axios
          //.get('/api/system/user/get-user/')
          .get(`/api/campaign/fetch-chatbots-campaign/${optionSelected.id}`)
          .then(response => {
            console.log(response.data)
            this.chatbotsCampaign = response.data.chatbotsCampaign
          })
      }
    },
  },
  setup(props, {emit}) {

    const {
      fetchFowardingSettings,
      addFowardingSetting,
      updateFowardingSetting,
      removeFowardingSetting,
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      fowardingSettingsData,
      baseUrlStorage,

    } = useDialerSettingForwarding()

    fetchFowardingSettings(props.dialer.id)

    const blankFowardingSetting = {
      dialer_id: '',
      channel: '',
      chatbot: '',
      department: '',
      template: [],
      dia_message: '',
    }
    const fowardingSettingData = ref(JSON.parse(JSON.stringify(blankFowardingSetting)))
    //Limpa os dados do popup
    const clearChatbotData = () => {
      fowardingSettingData.value = JSON.parse(JSON.stringify(blankFowardingSetting))
    }


    const confirmSRemoveChannel = channelChatbotId => {
      Swal.fire({
        title: 'Remoção',
        text: "Tem certeza que deseja remover o robô associado a esse canal?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Cancelar',
        customClass: {
          confirmButton: 'btn btn-primary',
          cancelButton: 'btn btn-outline-danger ml-1',
        },
        buttonsStyling: false,
      }).then(result => {
        if (result.value) {
          //Chama a função que atualiza o status do canal
          emit('remove-channel-chabot-campaign', channelChatbotId)
          //Recarrega os canais que possuem chatbots associados
          //fetchFowardingSettings(props.campaign.id)
        }
      })
    }

    return {
      //fetchFowardingSettings,
      addFowardingSetting,
      updateFowardingSetting,
      removeFowardingSetting,
      fowardingSettingData,
      clearChatbotData,
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      fowardingSettingsData,
      baseUrlStorage,
      confirmSRemoveChannel,
    }
  }
}
</script>