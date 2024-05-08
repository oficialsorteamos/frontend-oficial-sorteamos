<template>
  <div>
    <div class="m-2">
      <!-- Table Top -->
      <b-row>

        <!-- Per Page -->
        <b-col
          cols="12"
          md="6"
          class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
        >
          <label>{{ $t('campaign.show') }}</label>
          <v-select
            v-model="perPageQuickMessage"
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :options="perPageOptionsQuickMessage"
            :clearable="false"
            class="per-page-selector d-inline-block mx-50"
          />
          <label>{{ $t('campaign.entries') }}</label>
        </b-col>

        <!-- Search -->
        <b-col
          cols="12"
          md="6"
        >
        </b-col>
      </b-row>
    </div>

    <b-form
      enctype="multipart/form-data"
      @submit.prevent="onSubmitQuickMessage"
    >
      <b-table
        ref="refQuickMessageListTable"
        :items="quickMessageItems"
        :fields="campaign.campaign_type_id != 4?  tableColumns : tableColumnsCallWhatsapp"
        responsive
        selectable
        :select-mode="selectMode"
        @row-selected="onRowQuickMessageSelected"
        show-empty
        empty-text="No quick messages found"
      >
      <!-- Ações -->
      <template #cell(actions)="row">
        <b-dropdown
          variant="link"
          toggle-class="text-decoration-none"
          no-caret
          dropleft 
        >
          <template v-slot:button-content>
            <feather-icon
              icon="MoreVerticalIcon"
              size="16"
              class="text-body align-middle mr-25"
            />
          </template>
          <!--
          <b-dropdown-item
            class="dropdown-item-action"
            v-b-modal.modal-new-template-message
            @click="handleTemplateMessageClick(row.item)"
          >
            <feather-icon
              icon="Edit2Icon"
              
            />
            <span>{{ $t('chat.chatModalQuickMessageHandler.edit') }}</span>
          </b-dropdown-item>
           -->
          <!-- NÃO deixa remover o template caso o mesmo esteja associado a uma campanha ou a um chatbot -->
          <b-dropdown-item
            class="dropdown-item-action"
            @click="removeQuickMessage(row.item.id, row.item.tem_name)"
            :disabled="row.item.campaigns.length > 0 || row.item.chatbots.length > 0"
            v-b-tooltip.hover.v-secondary
            :title="row.item.campaigns.length > 0 || row.item.chatbots.length > 0? $t('template.titleDeleteTemplate') : ''"
          >
            <feather-icon
              icon="TrashIcon"
              class="mr-50"
            />
            <span>{{ $t('chat.chatModalQuickMessageHandler.delete') }}</span>
          </b-dropdown-item>
        </b-dropdown>
        <!--
        <b-button size="sm" @click="info(row.item, row.index, $event.target)" class="mr-1">
          Info modal
        </b-button>
        -->
      </template>

      <template #cell(show)="row">
        <b-form-checkbox
          v-model="row.detailsShowing"
          plain
          class="vs-checkbox-con"
          @change="row.toggleDetails"
        >
          <span class="vs-checkbox">
          </span>
        </b-form-checkbox>
      </template>
      
      <template #cell(content)="data">
        <span
          v-if="data.item.type_format_message_id == null || data.item.type_format_message_id == 1"
        >
          {{ data.value }}
        </span>
        <span
          v-else-if="data.item.type_format_message_id == 2"
        >
          <feather-icon icon="MusicIcon" /> {{ $t('chat.chatContact.audio') }}
        </span>
        <span
          v-else-if="data.item.type_format_message_id == 3"
        >
          <feather-icon icon="FileIcon" /> {{ $t('chat.chatContact.file') }}
        </span>
        <span
          v-else-if="data.item.type_format_message_id == 4"
        >
          <feather-icon icon="VideoIcon" /> {{ $t('chat.chatContact.movie') }}
        </span>
      </template>

      <template #row-details="row">
        <b-card>
          <quick-message-display
            :quick-message-data="row.item"
            :base-url-storage="baseUrlStorage"
          />
        </b-card>
      </template>

      <template #cell(parameters)="data">
        <span
          v-if="data.item.parameters"
        >
          <p>
            <b>Respostas Positivas:</b> {{ data.item.parameters[0].qui_positives_responses }}
          </p>
          <p>
            <b>Respostas Positivas:</b> {{ data.item.parameters[0].qui_negatives_responses }}
          </p>
        </span>
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
          <span class="text-muted">{{ $t('campaign.showing') }} {{ dataMetaQuickMessage.from }} {{ $t('campaign.to') }} {{ dataMetaQuickMessage.to }} {{ $t('campaign.of') }} {{ dataMetaQuickMessage.of }} {{ $t('campaign.entries') }}</span>
        </b-col>
        <b-col
          cols="12"
          sm="6"
          class="d-flex align-items-center justify-content-center justify-content-sm-end"
        >

          <b-pagination
            v-model="currentPageQuickMessage"
            :total-rows="totalQuickMessages"
            :per-page="perPageQuickMessage"
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
    <!-- Form Actions -->
    <div class="d-flex mt-2 modal-footer">
      <b-button
        v-ripple.400="'rgba(255, 255, 255, 0.15)'"
        variant="primary"
        class="mr-2"
        type="submit"
        :disabled="sendQuickMessageButtonDisabled"
      >
        <feather-icon
          icon="SendIcon"
          class="mr-50"
        />
        <span class="align-middle">{{ $t('campaign.campaignModalChooseTemplateHandler.select') }}</span>
        
      </b-button>
      <b-button
        v-ripple.400="'rgba(255, 255, 255, 0.15)'"
        v-b-modal.modal-new-quick-message
        variant="dark"
        @click="clearNewQuickMessageData()"
      >
        <feather-icon
          icon="PlusIcon"
          class="mr-50"
        />
        <span class="align-middle">{{ $t('campaign.campaignModalChooseQuickMessageHandler.newMessage') }}</span>
      </b-button>
    </div>
    </b-form>
      <!-- Form para cadastro de uma nova mensagem rápida -->
    <b-modal
      id="modal-new-quick-message"
      :title="$t('chat.newQuickMessage')"
      hide-footer
      ref="modal-new-quick-message"
      size="lg"
    >
      <chat-modal-new-quick-message-handler
        :new-quick-message="newQuickMessageData"
        :clear-new-quick-message-data="clearNewQuickMessageData"
        :type-quick-message-id="campaign.campaign_type_id == 1? 3 : campaign.campaign_type_id == 2? 4 : 5"
        @add-quick-message="addQuickMessage"
        @update-quick-message="updateQuickMessage"
        @upload-file="handleFileUpload"
        @hide-modal="hideModal"
      />
    </b-modal>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BTable, BFormCheckbox, BCard, BCardHeader, BCardBody, BRow, BCol, BAvatar, BBadge,
  BDropdown, BDropdownItem, BTabs, BTab, VBTooltip, BSpinner, BPagination,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import store from '@/store'
import axios from '@axios'
import { toRefs, watch, ref, computed } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useCampaignModalChooseQuickMessageHandler from './useCampaignModalChooseQuickMessageHandler'
import ChatModalNewQuickMessageHandler from '../../../chat/chat-modal-new-quick-message-handler/ChatModalNewQuickMessageHandler.vue'
import useQuickMessagesList from './useQuickMessagesList'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import QuickMessageDisplay from '../../../management/quick-messages/QuickMessageDisplay.vue'
import Vue from 'vue'

export default {
  components: {
    BButton,
    BForm,
    BModal,
    VBModal,
    BFormInput,
    BFormGroup,
    vSelect,
    BTable,
    BFormCheckbox,
    BCard,
    BRow,
    BCol,
    BBadge,
    BAvatar,
    BDropdown,
    BDropdownItem,
    BTabs,
    BTab,
    BCardHeader,
    BCardBody,
    BSpinner,
    BPagination,
    
    // Form Validation
    ValidationProvider,
    ValidationObserver,

    ChatModalNewQuickMessageHandler,

    QuickMessageDisplay,
  },
  directives: {
    'b-tooltip': VBTooltip,
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    campaign: {
      type: Object,
      required: true,
    },
    baseUrlStorage: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      selectMode: 'single',
      selected: [],
      fields: [
        {key: 'show', label: "Exibir"}, 
        {key:'title', label: "Título"}, 
        { 
          key: 'content', 
          label: 'Mensagem', 
          formatter: (value) =>  {
            //Limita a quantidade de caracteres da mensagem apresentados
            if(value.length > 50) {
              return value.substring(0,50)+".."
            } else {
              return value
            }
            
          }
        },
        { key: 'actions', label: 'Ações' }
      ],
      sendButtonDisabled: true,
      sendQuickMessageButtonDisabled: true,
    }
  },
  methods: {
    onRowQuickMessageSelected(quickMessage) {
      this.quickMessageLocal.quickMessage = quickMessage

      //Se houver alguma mensagem selecionada
      if(quickMessage.length) {
        console.log('Mensagem rápida selecionada')
        console.log(quickMessage)
        
        //HABILITA botão de envio de mensagem rápida
        this.sendQuickMessageButtonDisabled = false
      }
      else {
        this.sendQuickMessageButtonDisabled = true
      }
    },
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    //Método para quando um mensagem rápida é selecionada
    openModal(modalName) {
      //Abre o modal para cadastro de mensagem rápida
      this.$emit('open-modal', modalName)  
    },
  },
  setup(props,{ emit }) {

    const toast = useToast()

    const newQuickMessage = {
      id: null,
      title: '',
      content: '',
      buttonLabel: [],
      callActions: [],
      typeQuickMessageId: '',
      listLabel: '',
      buttonDescription: [],
    }
    const newQuickMessageData = ref(JSON.parse(JSON.stringify(newQuickMessage)))

    //Limpa os dados do popup
    const clearNewQuickMessageData = () => {
      newQuickMessageData.value = JSON.parse(JSON.stringify(newQuickMessage))
    }

    //Abre o modal já preenchida para atualização da mensagem rápida
    const handleQuickMessageClick = (templateData) => {
      console.log('templateData')
      console.log(templateData)
      newQuickMessageData.value = templateData
    }

    const fileTemplateMedia = ref(null)
    //Traz os dados do arquivo upado
    const handleFileUpload = (fileData) => {
      fileTemplateMedia.value = fileData
    }

    const addQuickMessage = messageData => {
      const formData = new FormData()
      formData.append('name', 'ivahy.jpg')
      formData.append('file', fileTemplateMedia.value)

      formData.append('messageData', JSON.stringify(messageData))

      const config = {
          headers: {
            'content-type': 'multipart/form-data'
          }
      }

      //Limpa a variável que armazena a mídia
      fileTemplateMedia.value = null
      //Chama o componente de loading screen
      Vue.prototype.$isLoading(true)
      store.dispatch('app-campaign/addQuickMessage', formData, config)
        .then(response => {
          fetchQuickMessages(props.campaign)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Mensagem rápida criada com sucesso',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Não foi possível criar a mensagem rápida',
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          })
        })
        .finally(() => {
          //Esconde a loading screen
          Vue.prototype.$isLoading(false) 
        })
    }

    const updateQuickMessage = messageData => {
      store.dispatch('app-campaign/updateQuickMessage', { quickMessage: messageData })
        .then(() => {
          // eslint-disable-next-line no-use-before-define
          //fetchQuickMessage()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Mensagem rápida atualizada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //Remove uma mensagem rápida
    const removeQuickMessage = quickMessageId => {
      store.dispatch('app-campaign/removeQuickMessage', { id: quickMessageId })
        .then(() => {
          fetchQuickMessages(props.campaign)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Mensagem rápida removida',
              text: 'Mensagem rápida removida com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const {
      quickMessageItems,
      perPageQuickMessage,
      currentPageQuickMessage,
      perPageOptionsQuickMessage,
      refQuickMessageListTable,
      dataMetaQuickMessage,
      tableColumns,
      tableColumnsCallWhatsapp,
      totalQuickMessages,
      fetchQuickMessages,

    } = useQuickMessagesList()

    fetchQuickMessages(props.campaign)


    const {
      quickMessageLocal,
      onSubmitQuickMessage,

    } = useCampaignModalChooseQuickMessageHandler(newQuickMessageData, emit)

    return {
      quickMessageItems,
      perPageQuickMessage,
      currentPageQuickMessage,
      perPageOptionsQuickMessage,
      refQuickMessageListTable,
      dataMetaQuickMessage,
      totalQuickMessages,
      tableColumns,
      tableColumnsCallWhatsapp,
      
      fetchQuickMessages,

      newQuickMessageData,
      clearNewQuickMessageData,
      handleFileUpload,

      quickMessageLocal,
      onSubmitQuickMessage,

      handleQuickMessageClick,
      addQuickMessage,
      updateQuickMessage,
      removeQuickMessage,
      
    }
  },
}
</script>