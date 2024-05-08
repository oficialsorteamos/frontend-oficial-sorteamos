<template>
  <div>
    <b-button
      variant="primary"
      class="btn-icon rounded-circle mb-1"
      @click="openModal('modal-new-template-message'); clearNewTemplateMessageData()"
      v-b-tooltip.hover.v-secondary
      :title="$t('template.addTemplate')"
    >
      <feather-icon 
        icon="PlusIcon" 
        size="20"
      />
    </b-button>
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
            md="3"
            class="mb-1"
          >
            <!-- Language -->
            <b-form-group
              :label="$t('template.category')"
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
            md="3"
            class="mb-1"
          >
            <!-- Language -->
            <b-form-group
              :label="$t('template.status')"
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
    <!-- Table Container Card -->
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
              v-model="perPageTemplate"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptionsTemplate"
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
            <b-button
            variant="primary"
            class="btn-icon rounded-circle"
            @click="updateStatusTemplateFacebook"
            v-b-tooltip.hover.v-secondary
            :title="$t('template.refresh')"
            :disabled="loadingRefresh"
          >
            <feather-icon 
              icon="RefreshCwIcon" 
              size="20"
              :hidden="loadingRefresh"
            />
            <b-spinner 
              :label="$t('chat.chatLog.loading')+'...'" 
              v-show="loadingRefresh"
              style="width: 1.5rem; height: 1.5rem;"
            />
          </b-button>
          </b-col>
        </b-row>
      </div>
    
      <b-table
        ref="refTemplateListTable"
        :items="templateItems"
        :fields="tableColumns"
        responsive
        show-empty
        empty-text="No templates found"
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
          <b-dropdown-item
            class="dropdown-item-action"
            v-b-modal.modal-new-template-message
            @click="handleTemplateMessageClick(row.item)"
            v-if="row.item.status.id == 7"
          >
            <feather-icon
              icon="Edit2Icon"
              
            />
            <span>{{ $t('chat.chatModalQuickMessageHandler.edit') }}</span>
          </b-dropdown-item>
          <!-- NÃO deixa remover o template caso o mesmo esteja associado a uma campanha ou a um chatbot -->
          <b-dropdown-item
            class="dropdown-item-action"
            @click="removeTemplate(row.item.id, row.item.tem_name)"
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

      <template #row-details="row">
        <b-card>
          <template-display-message
            :template-data="row.item"
          />
        </b-card>
      </template>

      <template #cell(category_name)="data">
        {{data.value}}
      </template>

      <template #cell(language_name)="data">
        {{data.value}}
      </template>

      <template #cell(channel)="data">
        <div>
          {{ data.value.cha_name}}
        </div>
        <div>
          <span
            v-if="data.value.cha_phone_number.substr(0, 4) == '0800'"
          >
            ({{data.value.cha_phone_ddi+data.value.cha_phone_number | VMask('+## #### ### ####') }})
          </span>
          <span
            v-else
          >
            ({{data.value.cha_phone_ddi+data.value.cha_phone_number | VMask('+## (##) #####-####') }})
          </span>
          
        </div>
      </template>

      <template  #cell(status)="data">
        <b-badge
          v-b-tooltip.hover.v-secondary
          :title="data.value.tem_description"
          :variant="resolveStatusVariant(data.value.tem_name)" 
        >
          {{ data.value.tem_name }}
        </b-badge>
        <b-badge
          style="margin-left: 3px; margin-top: 4px;"
          v-b-tooltip.hover.v-secondary
          :title="$t('template.templateAsssociatedCampaign')"
          variant="light-danger"
          v-if="data.item.campaigns.length > 0"
        >
          C
        </b-badge>
        <b-badge
          style="margin-left: 3px; margin-top: 4px;"
          v-b-tooltip.hover.v-secondary
          :title="$t('template.templateAsssociatedChatbot')"
          variant="light-primary"
          v-if="data.item.chatbots.length > 0"
        >
          B
        </b-badge>
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
            v-model="currentPageTemplate"
            :total-rows="totalTemplates"
            :per-page="perPageTemplate"
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
      <!-- Form para cadastro de um novo template -->
      <b-modal
        id="modal-new-template-message"
        :title="$t('template.newTemplate')"
        hide-footer
        ref="modal-new-template-message"
        size="xl"
      >
        <chat-modal-new-template-message-handler
          :template-message="newTemplateMessageData"
          :clear-new-quick-message-data="clearNewTemplateMessageData"
          :check-template-name-exist="checkTemplateNameExist"
          :error-template-name="errorTemplateName"
          @add-template-message="addTemplateMessage"
          @hide-modal="hideModal"
          @upload-file="handleFileUpload"
        />
      </b-modal>
    </div>
  </b-card>
    
  </div>
</template>

<script>
import {
  BCard, BRow, BCol, BFormInput, BButton, BTable, BMedia, BAvatar, BLink, BFormGroup, BSpinner,
  BBadge, BDropdown, BDropdownItem, BPagination, BCardBody, BCardHeader, VBTooltip, VBModal, BFormCheckbox,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import store from '@/store'
import axios from '@axios'
import Ripple from 'vue-ripple-directive'
import { ref, onUnmounted } from '@vue/composition-api'
import { VueMaskFilter } from 'v-mask'
import useTemplatesList from './useTemplatesList'
import templateStoreModule from './templateStoreModule'
import ChatModalNewTemplateMessageHandler from '../../chat/chat-modal-new-template-message-handler/ChatModalNewTemplateMessageHandler.vue'
import TemplateDisplayMessage from './TemplateDisplayMessage.vue'
import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'


export default {
  components: {
    BCard,
    BRow,
    BCol,
    BFormInput,
    BButton,
    BTable,
    BMedia,
    BAvatar,
    BLink,
    BBadge,
    BDropdown,
    BDropdownItem,
    BPagination,
    BCardBody,
    BCardHeader,
    VBModal,
    BFormGroup,
    BSpinner,
    BFormCheckbox,

    vSelect,
    ChatModalNewTemplateMessageHandler,
    TemplateDisplayMessage,
  },
  directives: {
    'b-tooltip': VBTooltip,
    'b-modal': VBModal,
    Ripple,
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
    handleTemplateClick(templateData) {
      //Abre o modal para atualização de mensagem rápida
      this.$emit('edit-template', templateData)
      this.$emit('open-modal', 'modal-new-template-message')  
    },
  },
   data() {
    return {
      categories: [],
      statusTemplate: [],
    }
  },
  created() { 
    //Traz todos as categorias de um template
    axios
      .get('/api/chat/fetch-template-categories')
      .then(response => {
        console.log(response.data)
        this.categories = response.data.categories
      });
    
    axios
      .get('/api/chat/fetch-template-status')
      .then(response => {
        console.log(response.data)
        this.statusTemplate = response.data.statusTemplate
      });
  },
  setup() {
    const TEMPLATE_APP_STORE_MODULE_NAME = 'app-template'

    // Register module
    if (!store.hasModule(TEMPLATE_APP_STORE_MODULE_NAME)) store.registerModule(TEMPLATE_APP_STORE_MODULE_NAME, templateStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(TEMPLATE_APP_STORE_MODULE_NAME)) store.unregisterModule(TEMPLATE_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()

    const newTemplateMessage = {
      tem_name: '',
      category: '',
      language: '',
      parameters: [],
      callActions: [],
      typeButton: '',
      templateButtons: [],
      buttonLabel: [],
      footer: null,
      typeHeader: '',
      header: null,
      mediaHeader: null,
      variablesTags: [],
      body: '',
      channel: '',
    }
    const newTemplateMessageData = ref(JSON.parse(JSON.stringify(newTemplateMessage)))
    
    const templateMessage = {
      template: [],
    }

    //Limpa os dados do popup
    const clearNewTemplateMessageData = () => {
      newTemplateMessageData.value = JSON.parse(JSON.stringify(newTemplateMessage))
    }

    const templateMessageData = ref(JSON.parse(JSON.stringify(templateMessage)))
    

    //Abre o modal já preenchida para atualização da mensagem rápida
    const handleTemplateMessageClick = (templateData) => {
      console.log('templateData')
      console.log(templateData)
      newTemplateMessageData.value = templateData
    }

    const errorTemplateName = ref(false)

    //Verifica se o nome digitado para o template já está sendo usado por outro template ativo
    const checkTemplateNameExist = templateName => {
      //Se foi digitado entre 12 e 14 caracteres e não seja o próprio número atual do canal
      if(templateName.length >= 3) {        
        store.dispatch('app-template/checkTemplateNameExist', { templateName: templateName })
        .then(response => {
          console.log(response.data.error)
          //Habilita o erro ou não
          errorTemplateName.value = response.data.error
        })
        .catch(error => {
        })
      }
    }

    const fileTemplateMedia = ref(null)
    //Traz os dados do arquivo upado
    const handleFileUpload = (fileData) => {
      fileTemplateMedia.value = fileData
    }

    const addTemplateMessage = messageData => {
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
      store.dispatch('app-template/addTemplateMessage', formData, config)
        .then(response => {
          //Caso não tenha ocorrido nenhum erro ao criar o template
          if(response.data.error == false) {
            fetchTemplates('', '', 10, 1)
            toast({
              component: ToastificationContent,
              props: {
                title: response.data.message,
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }
          else {
            fetchTemplates('', '', 10, 1)
            toast({
              component: ToastificationContent,
              props: {
                title: 'Não foi possível adicionar o modelo',
                text: response.data.message,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            },
            {
              timeout: 8000,
            })
          }
          
          
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Não foi possível criar o template',
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

    const removeTemplate = ( id, templateName ) => {
      //Chama o componente de loading screen
      Vue.prototype.$isLoading(true)
      store.dispatch('app-template/removeTemplate', 
      { 
        id: id,
        templateName: templateName,
        //channel_id: activeChat.value.chatContactData.service.channel_id
      })
        .then(response => {
          //Se não houve erro ao enviar a mensagem
          if(response.data.error == false) {
            fetchTemplates('', '', 10, 1)
            toast({
              component: ToastificationContent,
              props: {
                title: 'Template removido com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }
          else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Não foi possível remover o template',
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            })
          }
        })
        .finally(() => {
          //Esconde a loading screen
          Vue.prototype.$isLoading(false) 
        })
    }

    //Atualiza os status dos templates com base no facebook
    const updateStatusTemplateFacebook = ()  => {
      loadingRefresh.value = true
      updateTemplates.value = true
      
      //Traz os templates
      fetchTemplates(categoryFilter.value, statusTemplateFilter.value, perPageTemplate.value, currentPageTemplate.value, updateTemplates.value)

      toast({
        component: ToastificationContent,
        props: {
          title: 'Templates atualizados com sucesso!',
          icon: 'CheckIcon',
          variant: 'success',
        },
      })
    
      //Esconde o spinner
      loadingRefresh.value = false 
    }
    


    const {
      templateItems,
      perPageTemplate,
      currentPageTemplate,
      perPageOptionsTemplate,
      refTemplateListTable,
      categoryFilter,
      statusTemplateFilter,
      loadingRefresh,
      updateTemplates,
      dataMetaTemplate,
      tableColumns,
      totalTemplates,
      fetchTemplates,

      resolveStatusVariant,

    } = useTemplatesList()

    fetchTemplates()
    
    return {
      templateItems,
      perPageTemplate,
      currentPageTemplate,
      perPageOptionsTemplate,
      refTemplateListTable,
      categoryFilter,
      statusTemplateFilter,
      loadingRefresh,
      updateTemplates,
      dataMetaTemplate,
      totalTemplates,
      tableColumns,
      
      fetchTemplates,
      updateStatusTemplateFacebook,
      removeTemplate,
      addTemplateMessage,

      newTemplateMessageData,
      templateMessageData,
      clearNewTemplateMessageData,
      handleTemplateMessageClick,
      checkTemplateNameExist,
      errorTemplateName,
      handleFileUpload,

      resolveStatusVariant,
      
    }
  },
}
</script>

<style lang="scss" scoped>
.per-page-selector {
  width: 90px;
}
</style>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';

#btn-emoji-default {
  height: auto !important;
  width: 25px !important;
  margin: 0 !important;
}
#btn-emoji-default > div > img.emoji {
  width: 17px !important;
  height: 17px !important;
}
</style>
