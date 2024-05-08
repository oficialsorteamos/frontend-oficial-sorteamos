<template>
  <div>
    <!--
    <b-card 
      no-body
      class="p-1"
    >
      <b-card-header class="pb-50">
        <h5>
          {{ $t('contacts.contactsList.filters') }}
        </h5>
      </b-card-header>
      <b-card-body>
        <b-row>
          <b-col
            md="5"
            class="mb-1"
          >
            
            <b-form-group
              :label="$t('campaign.campaignModalChooseTemplateHandler.category')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="categoryFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="categories"
                :getOptionLabel="categories => categories.tem_name"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            md="5"
            class="mb-1"
          >
            
            <b-form-group
              :label="$t('campaign.campaignModalChooseTemplateHandler.status')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="statusTemplateFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="statusTemplate"
                :getOptionLabel="statusTemplate => statusTemplate.tem_name"
                transition=""
              />
            </b-form-group>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>
    -->
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
        :fields="tableColumns"
        responsive
        selectable
        :select-mode="selectMode"
        @row-selected="onRowQuickMessageSelected"
        show-empty
        :empty-text="$t('chatbot.chatbotListQuickMessageHandler.noQuickMessagesFound')"
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
            v-b-modal.modal-new-quick-message
            @click="handleQuickMessageClick(row.item)"
          >
            <feather-icon
              icon="Edit2Icon"
              
            />
            <span>{{ $t('chat.chatModalQuickMessageHandler.edit') }}</span>
          </b-dropdown-item>
          -->
          <b-dropdown-item
            class="dropdown-item-action"
            @click="removeQuickMessage(row.item.id)" 
            :disabled="row.item.chatbots.length > 0"
            v-b-tooltip.hover.v-secondary
            :title="row.item.chatbots.length > 0? $t('chatbot.chatbotListQuickMessageHandler.titleDeleteQuickMessage') : ''"
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
  
      <template #row-details="row">
        <b-card>
          <quick-message-display
            :quick-message-data="row.item"
            :base-url-storage="baseUrlStorage"
          />
        </b-card>
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


    </b-table>
    <!-- Pagination -->
    <div class="mx-2 mb-2">
      <b-row>
        <b-col
          cols="12"
          sm="6"
          class="d-flex align-items-center justify-content-center justify-content-sm-start"
        >
          <span class="text-muted">{{ $t('campaign.showing') }} {{ dataMetaTemplate.from }} {{ $t('campaign.to') }} {{ dataMetaTemplate.to }} {{ $t('campaign.of') }} {{ dataMetaTemplate.of }} {{ $t('campaign.entries') }}</span>
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
    <!-- Se for um chatbot para canal oficial -->
    <span
      v-if="showNotPossibleMessage && chatbot.cha_only_official_channel == 1"
      style="font-size: 0.857rem; color: #ea5455;"
    >
      {{ $t('chat.chatModalQuickMessageHandler.itIsNotPossibleSendQuickMessage') }}
    </span>
    <!-- Form Actions -->
    <div class="d-flex mt-2 modal-footer">
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
        <span class="align-middle">{{ $t('chatbot.chatbotListQuickMessageHandler.newQuickMessage') }}</span>
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
        :type-quick-message-id="2"
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
import useChatbotModalChooseQuickMessageHandler from './useChatbotModalChooseQuickMessageHandler'
import useQuickMessagesList from './useQuickMessagesList'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import ChatModalNewQuickMessageHandler from '../../../../chat/chat-modal-new-quick-message-handler/ChatModalNewQuickMessageHandler.vue'
import QuickMessageDisplay from '../../../../management/quick-messages/QuickMessageDisplay.vue'
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
    quickMessageId: {
      type: Number,
      required: false,
    },
    chatbot: {
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
      sendButtonDisabled: true,
      sendTemplateButtonDisabled: true,
      categories: [],
      statusTemplate: [],
      showNotPossibleMessage: false,
    }
  },
  methods: {
    //Captura o template selecionado
    onRowQuickMessageSelected(quickMessage) {
      this.templateLocal.quickMessage = quickMessage

      if(quickMessage.length) {
        if(((quickMessage[0].parameters.length == 0 || (quickMessage[0].parameters.length > 0 && quickMessage[0].parameters[0].type_button_id != 2)) && this.chatbot.cha_only_official_channel == 1) || this.chatbot.cha_only_official_channel == 0) {
          //HABILITA botão de envio de mensagem rápida
          this.showNotPossibleMessage = false
          this.$emit('set-quick-message-selected', quickMessage)
          console.log('entrou aqui')
        }
        else {
          //DESABILITA botão de envio de mensagem rápida
          this.showNotPossibleMessage = true
          this.$emit('set-quick-message-selected', null)
        }
        
      } else {
        //DESABILITA botão de envio de mensagem rápida
        this.showNotPossibleMessage = true
        this.$emit('set-quick-message-selected', null)
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
    selectThirdRow() {
      console.log('this.quickMessageId')
      console.log(this.quickMessageId)
      console.log(this.chatbot)
      if(this.quickMessageId) {
        //Filtra o template na tabela
        var quickMessage = this.quickMessageItems.find(c => c.id === this.quickMessageId)
        console.log('quickMessage')
        console.log(quickMessage)
        //Pega o index do template na tabela
        var quickMessageIndexTable = this.quickMessageItems.indexOf(quickMessage)
        //Seleciona o template na tabela
        this.$refs.refQuickMessageListTable.selectRow(quickMessageIndexTable).scrollIntoView();
      }
      else {
        this.$refs.refQuickMessageListTable.clearSelected()
      }
    },
  },
  created() { 
    //Traz todos as categorias de um template
    axios
      .get('/api/chat/fetch-template-categories')
      .then(response => {
        //console.log(response.data)
        this.categories = response.data.categories
      });
    
    axios
      .get('/api/chat/fetch-template-status')
      .then(response => {
        //console.log(response.data)
        this.statusTemplate = response.data.statusTemplate
      });
  },
  watch: { 
    quickMessageId: function(newVal, oldVal) { // watch it
      //Chama a função que seleciona uma mensagem rápida, se houver
      this.selectThirdRow()
    }
  },
  updated() {
    this.selectThirdRow()
  },
  setup(props,{ emit }) {

    const toast = useToast()

    const newTemplateMessage = {
      id: null,
      title: '',
      content: '',
      buttonLabel: [],
      callActions: [],
      typeQuickMessageId: '',
      listLabel: '',
      buttonDescription: [],
    }
    const newQuickMessageData = ref(JSON.parse(JSON.stringify(newTemplateMessage)))
    
    const templateMessage = {
      template: [],
    }

    //Limpa os dados do popup
    const clearNewQuickMessageData = () => {
      newQuickMessageData.value = JSON.parse(JSON.stringify(newTemplateMessage))
    }

    const templateMessageData = ref(JSON.parse(JSON.stringify(templateMessage)))

    const errorTemplateName = ref(false)

    //Verifica se o nome digitado para o template já está sendo usado por outro template ativo
    const checkTemplateNameExist = templateName => {
      //Se foi digitado entre 12 e 14 caracteres e não seja o próprio número atual do canal
      if(templateName.length >= 3) {
        store.dispatch('app-chatbot/checkTemplateNameExist', { templateName: templateName })
        .then(response => {
          console.log(response.data.error)
          //Habilita o erro ou não
          errorTemplateName.value = response.data.error
        })
        .catch(error => {
        })
      }
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
      store.dispatch('app-chatbot/addQuickMessage', formData, config)
        .then(response => {
          fetchQuickMessages()
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
      store.dispatch('app-chatbot/updateQuickMessage', { quickMessage: messageData })
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
      store.dispatch('app-chatbot/removeQuickMessage', { id: quickMessageId })
        .then(() => {
          fetchQuickMessages()
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
      categoryFilter,
      statusTemplateFilter,
      loadingRefresh,
      dataMetaTemplate,
      tableColumns,
      totalQuickMessages,
      fetchQuickMessages,

      resolveStatusVariant,

    } = useQuickMessagesList()

    fetchQuickMessages()


    const {
      templateLocal,
      onSubmitQuickMessage,

    } = useChatbotModalChooseQuickMessageHandler(templateMessageData, emit)

    return {
      quickMessageItems,
      perPageQuickMessage,
      currentPageQuickMessage,
      perPageOptionsQuickMessage,
      refQuickMessageListTable,
      categoryFilter,
      statusTemplateFilter,
      loadingRefresh,
      dataMetaTemplate,
      totalQuickMessages,
      tableColumns,
      
      fetchQuickMessages,
      removeQuickMessage,
      addQuickMessage,
      updateQuickMessage,

      newQuickMessageData,
      templateMessageData,
      clearNewQuickMessageData,
      checkTemplateNameExist,
      errorTemplateName,
      handleQuickMessageClick,
      handleFileUpload,

      templateLocal,
      onSubmitQuickMessage,

      resolveStatusVariant,
      
    }
  },
}
</script>