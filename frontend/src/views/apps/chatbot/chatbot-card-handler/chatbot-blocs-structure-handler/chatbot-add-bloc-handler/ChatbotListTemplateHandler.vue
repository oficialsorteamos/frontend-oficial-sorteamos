<template>
  <div>
    <b-tabs>
        <b-tab>
          <template #title>
            <feather-icon icon="MessageSquareIcon" />
            <span>{{ $t('chat.chatModalQuickMessageHandler.quickMessages') }}</span>
          </template>
          <chatbot-list-quick-message-handler
            :quick-message-id="1"
            :chatbot="chatbot"
            :base-url-storage="baseUrlStorage"
            @set-quick-message-selected="onRowQuickMessageSelected"
          />
        </b-tab>
        <!-- Template Messages -->
        <b-tab>
          <template #title>
            <feather-icon icon="FacebookIcon" />
            <span>{{ $t('chat.chatModalQuickMessageHandler.templates') }}</span>
          </template>
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
                  <!-- Language --> 
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
                  <!-- Language -->
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
          <div 
            class="float-right mb-1"
          >
            <b-button
              variant="primary"
              class="btn-icon rounded-circle"
              @click="updateStatusTemplateFacebook"
              v-b-tooltip.hover.v-secondary
              :title="$t('campaign.campaignModalChooseTemplateHandler.refresh')"
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
          </div>
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
                md="6"
              >
              </b-col>
            </b-row>
          </div>

          <b-form
            enctype="multipart/form-data"
            @submit.prevent="onSubmitTemplate"
          >
            <b-table
              ref="refTemplateListTable"
              :items="templateItems"
              :fields="fieldsTemplates"
              responsive
              selectable
              :select-mode="selectMode"
              @row-selected="onRowTemplateSelected"
              show-empty
              :empty-text="$t('chatbot.chatbotListTemplateHandler.noTemplatesFound')"
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

            <template #cell(status)="data">
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
          </div>
          <!-- Form Actions -->
          <div class="d-flex mt-2 modal-footer">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              v-b-modal.modal-new-template-message
              variant="dark"
              @click="clearNewTemplateMessageData()"
            >
              <feather-icon
                icon="PlusIcon"
                class="mr-50"
              />
              <span class="align-middle">{{ $t('campaign.campaignModalChooseTemplateHandler.newTemplateMessage') }}</span>
            </b-button>
          </div>
          </b-form>
          <!-- Form para cadastro de um novo template -->
            <b-modal
              id="modal-new-template-message"
              :title="$t('campaign.campaignModalChooseTemplateHandler.newTemplate')"
              hide-footer
              ref="modal-new-template-message"
              size="xl"
            >
              <chat-modal-new-template-message-handler
                :template-message="newTemplateMessageData"
                :clear-new-quick-message-data="clearNewTemplateMessageData"
                :check-template-name-exist="checkTemplateNameExist"
                :error-template-name="errorTemplateName"
                :channel="chatbot.channels[0]"
                @add-template-message="addTemplateMessage"
                @hide-modal="hideModal"
                @upload-file="handleFileUpload"
              />
            </b-modal> 
        </b-tab>
    </b-tabs>
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
import useChatbotModalChooseTemplateHandler from './useChatbotModalChooseTemplateHandler'
import ChatModalNewTemplateMessageHandler from '../../../../chat/chat-modal-new-template-message-handler/ChatModalNewTemplateMessageHandler.vue'
import useTemplatesList from './useTemplatesList'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import TemplateDisplayMessage from '../../../../management/templates/TemplateDisplayMessage.vue'
import ChatbotListQuickMessageHandler from './ChatbotListQuickMessageHandler'
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

    ChatModalNewTemplateMessageHandler,

    TemplateDisplayMessage,
    ChatbotListQuickMessageHandler,
  },
  directives: {
    'b-tooltip': VBTooltip,
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    templateChosen: {
      type: Object,
      required: true,
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
      fieldsTemplates: [
        {key: 'show', label: "Exibir"}, 
        {
          key:'tem_name', 
          label: "Nome",
          formatter: (value) =>  {
            //Limita a quantidade de caracteres da mensagem apresentados
            if(value.length > 20) {
              return value.substring(0, 20)+".."
            } else {
              return value
            }
            
          }
        
        }, 
        { 
          key: 'body', 
          label: 'Mensagem', 
          formatter: (value) =>  {
            //Limita a quantidade de caracteres da mensagem apresentados
            if(value.length > 50) {
              return value.substring(0, 50)+".."
            } else {
              return value
            }
            
          }
        },
        { key: 'category_name', label: 'Categoria' },
        { key: 'status', label: 'Status' },
        { key: 'actions', label: 'Ações' }
      ],
      categories: [],
      statusTemplate: [],
    }
  },
  methods: {
    //Captura o template selecionado
    onRowTemplateSelected(template) {
      this.templateLocal.template = template
      this.$emit('set-template-selected', template)
    },
    onRowQuickMessageSelected(quickMessage) {
      this.templateLocal.quickMessage = quickMessage
      this.$emit('set-quick-message-selected', quickMessage)
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
      console.log('templateChosen')
      console.log(this.templateChosen)
      //Filtra o template na tabela
      var template = this.templateItems.find(c => c.id === this.templateChosen.template_id)
      //Pega o index do template na tabela
      var templateIndexTable = this.templateItems.indexOf(template)
      //Seleciona o template na tabela
      this.$refs.refTemplateListTable.selectRow(templateIndexTable)
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
  updated() {
    this.selectThirdRow()
  },
  setup(props,{ emit }) {

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
    const handleTemplateMessageClick = (templateData) => {
      console.log('templateData')
      console.log(templateData)
      newTemplateMessageData.value = templateData
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
      store.dispatch('app-chatbot/addTemplateMessage', formData, config)
        .then(response => {
          //Caso não tenha ocorrido nenhum erro ao criar o template
          if(response.data.error == false) {
            fetchTemplates(props.chatbot.channels)
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
            fetchTemplates(props.chatbot.channels)
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
      store.dispatch('app-chatbot/removeTemplate', 
      { 
        id: id,
        templateName: templateName,
        //channel_id: activeChat.value.chatContactData.service.channel_id
      })
        .then(response => {
          //Se não houve erro ao enviar a mensagem
          if(response.data.error == false) {
            fetchTemplates(props.chatbot.channels)
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
      fetchTemplates(props.chatbot.channels)

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

    fetchTemplates(props.chatbot.channels)


    const {
      templateLocal,
      onSubmitTemplate,

    } = useChatbotModalChooseTemplateHandler(templateMessageData, emit)

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
      checkTemplateNameExist,
      errorTemplateName,
      handleTemplateMessageClick,
      handleFileUpload,

      templateLocal,
      onSubmitTemplate,

      resolveStatusVariant,
      
    }
  },
}
</script>