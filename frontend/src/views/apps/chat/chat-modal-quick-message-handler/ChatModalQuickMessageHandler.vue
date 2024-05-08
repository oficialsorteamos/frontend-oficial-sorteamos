<template>
  <div>
    <b-tabs>
      <b-tab>
        <template #title>
          <feather-icon icon="MessageSquareIcon" />
          <span>{{ $t('chat.chatModalQuickMessageHandler.quickMessages') }}</span>
        </template>
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
        <!-- select 2 demo -->
        <b-form
          enctype="multipart/form-data"
          @submit.prevent="onSubmit"
        >
          <b-table
            ref="refQuickMessageListTable"
            :items="quickMessageItems"
            :fields="fields"
            responsive
            selectable
            :select-mode="selectMode"
            @row-selected="onRowSelected"
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
              <b-dropdown-item
                class="dropdown-item-action"
                @click="handleQuickMessageClick(row.item)"
              >
                <feather-icon
                  icon="Edit2Icon"
                  
                />
                <span>{{ $t('chat.chatModalQuickMessageHandler.edit') }}</span>
              </b-dropdown-item>
              <b-dropdown-item
                class="dropdown-item-action"
                @click="removeQuickMessage(row.item.id)"
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
            <!--
            <b-card>
              <b-row class="mb-1">
                <b-col
                  md="12"
                  class="mb-1"
                  v-html="row.item.content"
                >
                  {{ row.item.content }}
                </b-col>
              </b-row>
            </b-card>
            -->
          </template>

          <template #cell(avatar)="data">
            <b-avatar :src="data.value" />
          </template>

          <template #cell(status)="data">
            <b-badge :variant="status[1][data.value]">
              {{ status[0][data.value] }}
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
          <span
            v-if="sendButtonDisabled && channelOfficial == 1"
            style="font-size: 0.857rem; color: #ea5455;"
          >
            {{ $t('chat.chatModalQuickMessageHandler.itIsNotPossibleSendQuickMessage') }}
          </span>
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mr-2"
            type="submit"
            :disabled="sendButtonDisabled"
          >
            <feather-icon
              icon="SendIcon"
              class="mr-50"
            />
            <span class="align-middle">{{ $t('chat.chatModalQuickMessageHandler.send') }}</span>
            
          </b-button>
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            v-b-modal.modal-multi-2
            variant="dark"
            @click="openModal('modal-new-quick-message')"
          >
            <feather-icon
              icon="PlusIcon"
              class="mr-50"
            />
            <span class="align-middle">{{ $t('chat.chatModalQuickMessageHandler.new') }}</span>
          </b-button>
        </div>
        </b-form>
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
                md="3"
                class="mb-1"
              >
                <!-- Language -->
                <b-form-group
                  :label="$t('chat.chatModalQuickMessageHandler.category')"
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
                  :label="$t('chat.chatModalQuickMessageHandler.status')"
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
            :title="$t('chat.chatModalQuickMessageHandler.refresh')"
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
                @click="handleTemplateClick(row.item)"
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
          <!-- O botão de enviar só é exibido caso o canal seja oficial -->
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mr-2"
            type="submit"
            :disabled="sendTemplateButtonDisabled"
            v-show="channelOfficial"
          >
            <feather-icon
              icon="SendIcon"
              class="mr-50"
            />
            <span class="align-middle">{{ $t('chat.chatModalQuickMessageHandler.send') }}</span>
            
          </b-button>
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            v-b-modal.modal-multi-2
            variant="dark"
            @click="openModal('modal-new-template-message'); clearTemplateData()"
          >
            <feather-icon
              icon="PlusIcon"
              class="mr-50"
            />
            <span class="align-middle">{{ $t('chat.chatModalQuickMessageHandler.newTemplateMessage') }}</span>
          </b-button>
        </div>
        </b-form>
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
import useChatModalQuickMessageHandler from './useChatModalQuickMessageHandler'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import TemplateDisplayMessage from '../../management/templates/TemplateDisplayMessage.vue'
import QuickMessageDisplay from '../../management/quick-messages/QuickMessageDisplay.vue'

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

    //Template Display Message
    TemplateDisplayMessage,
    QuickMessageDisplay,
  },
  directives: {
    'b-tooltip': VBTooltip,
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    quickMessage: {
      type: Object,
      required: true,
    },
    templateMessage: {
      type: Object,
      required: true,
    },
    quickMessageItems: {
      type: Array,
      required: true,
    },
    templateItems: {
      type: Array,
      required: true,
    },
    fetchTemplates: {
      type: Function,
      required: true,
    },
    totalTemplates: {
      type: Number,
      required: true,
    },
    fetchQuickMessages: {
      type: Function,
      required: true,
    },
    totalQuickMessages: {
      type: Number,
      required: true,
    },
    channelOfficial: {
      type: Number,
      required: true,
    },
    clearTemplateData: {
      type: Function,
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
      items: [],
      sendButtonDisabled: true,
      sendTemplateButtonDisabled: true,
      fieldsTemplates: [
        {key: 'show', label: "Exibir"}, 
        {key:'tem_name', label: "Nome"}, 
        { 
          key: 'body', 
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
        { key: 'category_name', label: 'Categoria' },
        { key: 'language_name', label: 'Idioma' },
        { key: 'status', label: 'Status' },
        { key: 'actions', label: 'Ações' }
      ],
      categories: [],
      statusTemplate: [],
    }
  },
  methods: {
    //Método para quando um mensagem rápida é selecionada
    onRowSelected(message) {
      this.quickMessageLocal.content = message
      //Se o canal for oficial e mensagem não tiver parâmetros ou se for um canal não oficial
      if(message.length) {
        if(((message[0].parameters.length == 0 || (message[0].parameters.length > 0 && message[0].parameters[0].type_button_id != 2)) && this.channelOfficial == 1) || this.channelOfficial == 0) {
          //HABILITA botão de envio de mensagem rápida
          this.sendButtonDisabled = false
        }
        else {
          //DESABILITA botão de envio de mensagem rápida
          this.sendButtonDisabled = true
        }
        
      } else {
        //DESABILITA botão de envio de mensagem rápida
        this.sendButtonDisabled = true
      }
      
    },
    onRowTemplateSelected(template) {
      this.templateLocal.template = template

      //Se houver alguma mensagem selecionada
      if(template.length) {
        //console.log('template selecionado')
        //console.log(template)
        //Só HABILITA o botão de enviar template caso o mesmo esteja APROVADO ou SINALIZADO
        if(template[0].status_id == 2 || template[0].status_id == 5) {
          //HABILITA botão de envio de mensagem rápida
          this.sendTemplateButtonDisabled = false
        }
        else {
          //DESABILITA botão de envio de mensagem rápida
          this.sendTemplateButtonDisabled = true
        }
      } else {
        //DESABILITA botão de envio de mensagem rápida
        this.sendTemplateButtonDisabled = true
      }
      
    },
    //Método para quando um mensagem rápida é selecionada
    openModal(modalName) {
      //Abre o modal para cadastro de mensagem rápida
      this.$emit('open-modal', modalName)  
    },
    removeQuickMessage(quickMessageId) {
      //Abre o modal para cadastro de mensagem rápida
      this.$emit('remove-quick-message', quickMessageId)  
    },
    removeTemplate(id, templateName) {
      //Abre o modal para cadastro de mensagem rápida
      this.$emit('remove-template', {id: id, templateName: templateName} )  
    },
    handleQuickMessageClick(quickMessage) {
      //Abre o modal para atualização de mensagem rápida
      this.$emit('edit-quick-message', quickMessage)
      this.$emit('open-modal', 'modal-new-quick-message')  
    },
    handleTemplateClick(templateData) {
      //Abre o modal para atualização de mensagem rápida
      this.$emit('edit-template', templateData)
      this.$emit('open-modal', 'modal-new-template-message')  
    },
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
  setup(props,{ emit }) {

    const perPageTemplate = ref(5)
    //const totalTemplates = ref(0)
    const currentPageTemplate = ref(1)
    const perPageOptionsTemplate = [10, 25, 50, 100]
    const refTemplateListTable = ref(null)

    const categoryFilter = ref('')
    const statusTemplateFilter = ref('')
    const loadingRefresh = ref(false)
    const updateTemplates = ref(false)

    const toast = useToast()

    //Exibe as informações de paginação
    const dataMetaTemplate = computed(() => {
      const localItemsCount = refTemplateListTable.value ? refTemplateListTable.value.localItems.length : 0
      return {
        from: perPageTemplate.value * (currentPageTemplate.value - 1) + (localItemsCount ? 1 : 0),
        to: perPageTemplate.value * (currentPageTemplate.value - 1) + localItemsCount,
        of: props.totalTemplates,
      }
    })

    //Caso algum filtro seja alterado
    watch([categoryFilter, perPageTemplate, currentPageTemplate, statusTemplateFilter], () => {
        //Aplica o filtro
        props.fetchTemplates(categoryFilter.value, statusTemplateFilter.value, perPageTemplate.value, currentPageTemplate.value)
    })

    

    props.fetchTemplates(categoryFilter.value, statusTemplateFilter.value, perPageTemplate.value, currentPageTemplate.value)

    //Atualiza os status dos templates com base no facebook
    const updateStatusTemplateFacebook = ()  => {
      loadingRefresh.value = true
      updateTemplates.value = true
      
      //Traz os templates
      props.fetchTemplates(categoryFilter.value, statusTemplateFilter.value, perPageTemplate.value, currentPageTemplate.value, updateTemplates.value)

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



    //######################## PAGINAÇÃO DAS MENSAGENS RÁPIDAS #################################
    const perPageQuickMessage = ref(5)
    //const totalTemplates = ref(0)
    const currentPageQuickMessage = ref(1)
    const perPageOptionsQuickMessage = [10, 25, 50, 100]
    const refQuickMessageListTable = ref(null)

    //Exibe as informações de paginação
    const dataMetaQuickMessage = computed(() => {
      const localItemsCount = refQuickMessageListTable.value ? refQuickMessageListTable.value.localItems.length : 0
      return {
        from: perPageQuickMessage.value * (currentPageQuickMessage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPageQuickMessage.value * (currentPageQuickMessage.value - 1) + localItemsCount,
        of: props.totalQuickMessages,
      }
    })

    //Caso algum filtro seja alterado
    watch([perPageQuickMessage, currentPageQuickMessage], () => {
        //Aplica o filtro
        props.fetchQuickMessages(perPageQuickMessage.value, currentPageQuickMessage.value)
    })

    props.fetchQuickMessages(perPageQuickMessage.value, currentPageQuickMessage.value)

    const {
      quickMessageLocal,
      onSubmit,

      templateLocal,
      onSubmitTemplate,

      resolveStatusVariant,
    } = useChatModalQuickMessageHandler(toRefs(props), emit)

    return {
      // Add New Event
      quickMessageLocal,
      onSubmit,

      templateLocal,
      onSubmitTemplate,

      resolveStatusVariant,
      categoryFilter,
      statusTemplateFilter,
      updateStatusTemplateFacebook,
      loadingRefresh,
      perPageTemplate,
      //totalTemplates,
      currentPageTemplate,
      perPageOptionsTemplate,
      dataMetaTemplate,
      refTemplateListTable,
      perPageQuickMessage,
      currentPageQuickMessage,
      dataMetaQuickMessage,
      perPageOptionsQuickMessage,
      refQuickMessageListTable,
      
    }
  },
}
</script>