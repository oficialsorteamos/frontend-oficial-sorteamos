<template>
  <div>
    <b-tabs>
      <b-tab>
        <template #title>
          <feather-icon icon="MessageSquareIcon" />
          <span>{{ $t('campaign.campaignModalListMessageHandler.messages') }}</span>
        </template>
        <!-- select 2 demo  -->
        <b-form
          enctype="multipart/form-data"
          @submit.prevent="onSubmit"
        >
          <b-table
            :items="messageItems"
            :fields="campaign.campaign_type_id != 4? fields : quickMessagesFields"
            show-empty
            :empty-text="$t('campaign.campaignModalListMessageHandler.noMessagesFound')"
            responsive
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
                @click="handleQuickMessageClick(row.item, campaignId);"
              >
                <feather-icon
                  icon="Edit2Icon"
                  
                />
                <span>{{ $t('campaign.campaignModalListMessageHandler.edit') }}</span>
              </b-dropdown-item>
              -->
              <!-- Se a campanha é de DISPARO VIA WHATSAPP e já tenha sido INICIADA, desabilita a função de exclusão de áudio -->
              <b-dropdown-item
                class="dropdown-item-action"
                @click="removeMessage(row.item.id)"
                :disabled="campaign.campaign_type_id == 4 && campaign.status_id != 4"
                v-b-tooltip.hover.v-secondary
                :title="campaign.campaign_type_id == 4 && campaign.status_id != 4? $t('campaign.campaignModalListMessageHandler.titleDeleteQuickMessage') : ''"
              >
                <feather-icon
                  icon="TrashIcon"
                  class="mr-50"
                />
                <span>{{ $t('campaign.campaignModalListMessageHandler.delete') }}</span>
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
                :quick-message-data="row.item.quick_message"
                :base-url-storage="baseUrlStorage"
              />
            </b-card>
          </template>

          <template #cell(content)="data">
            <span
              v-if="data.item.quick_message.type_format_message_id == null || data.item.type_format_message_id == 1"
            >
              {{ data.value }}
            </span>
            <span
              v-else-if="data.item.quick_message.type_format_message_id == 2"
            >
              <feather-icon icon="MusicIcon" /> {{ $t('chat.chatContact.audio') }}
            </span>
            <span
              v-else-if="data.item.quick_message.type_format_message_id == 3"
              
            >
              <feather-icon icon="FileIcon" /> {{ $t('chat.chatContact.file') }}
            </span>
            <span
              v-else-if="data.item.quick_message.type_format_message_id == 4"
            >
              <feather-icon icon="VideoIcon" /> {{ $t('chat.chatContact.movie') }}
            </span>
          </template>
          
          <template #cell(quick_messages)="data">
            <span
              v-if="data.item.quick_message.parameters[0].qui_positives_responses"
            >
              <p>
                <b>Respostas Positivas:</b> {{ data.item.quick_message.parameters[0].qui_positives_responses }}
              </p>
              <p>
                <b>Respostas Positivas:</b> {{ data.item.quick_message.parameters[0].qui_negatives_responses }}
              </p>
            </span>
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
        <!-- Form Actions -->
        <div class="d-flex mt-2 modal-footer">
          <!-- A campanha seja de LIGAÇÃO VIA WHATSAPP e já tenha áudio adicionado -->
          <span
            v-if="campaign.campaign_type_id == 4 && messageItems.length > 0"
            style="font-size: 0.857rem; color: #ea5455;"
          >
            {{ $t('campaign.campaignModalListMessageHandler.itIsNotPossibleAddNewAudio') }}
          </span>
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            v-b-modal.modal-choose-quick-message
            variant="dark"
            :disabled="campaign.campaign_type_id == 4 && messageItems.length > 0"
          >
            <feather-icon
              icon="PlusIcon"
              class="mr-50"
            />
            <span class="align-middle">{{ $t('campaign.campaignModalListMessageHandler.add') }}</span>
          </b-button>
        </div>
        </b-form>
      </b-tab>

      <!-- Template Messages -->
      <!-- Se for um campanha de WhatsApp -->
      <b-tab
        v-if="campaign.campaign_type_id == 1"
      >
        <template #title>
          <feather-icon icon="FacebookIcon" />
          <span>{{ $t('campaign.campaignModalListMessageHandler.templates') }}</span>
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
                v-model="perPageCampaignTemplate"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="perPageOptionsCampaignTemplate"
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
          <b-table
            ref="refCampaignTemplateListTable"
            :items="campaignTemplateItems"
            :fields="tableColumnsCampaignTemplates"
            responsive
            show-empty
            :empty-text="$t('campaign.campaignModalListMessageHandler.noTemplatesFound')"
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
                @click="removeCampaignTemplate(row.item.id)"
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
                :template-data="row.item.template"
              />
            </b-card>
          </template>

          <template #cell(category)="data">
            {{data.value}}
          </template>

          <template #cell(language)="data">
            {{data.value}}
          </template>

          <template #cell(template)="data">
            <b-badge
              v-b-tooltip.hover.v-secondary
              :title="data.value.tem_description"
              :variant="resolveStatusVariant(data.value.status.tem_name)" 
            >
              {{ data.value.status.tem_name }}
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
              <span class="text-muted">{{ $t('campaign.showing') }} {{ dataMetaCampaignTemplate.from }} {{ $t('campaign.to') }} {{ dataMetaCampaignTemplate.to }} {{ $t('campaign.of') }} {{ dataMetaCampaignTemplate.of }} {{ $t('campaign.entries') }}</span>
            </b-col>
            <b-col
              cols="12"
              sm="6"
              class="d-flex align-items-center justify-content-center justify-content-sm-end"
            >

              <b-pagination
                v-model="currentPageCampaignTemplate"
                :total-rows="totalCampaignTemplates"
                :per-page="perPageCampaignTemplate"
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
            v-b-modal.modal-choose-template
            variant="dark"
          >
            <feather-icon
              icon="PlusIcon"
              class="mr-50"
            />
            <span class="align-middle">{{ $t('campaign.campaignModalListMessageHandler.add') }}</span>
          </b-button>
        </div>
      </b-tab>
    </b-tabs> 
    <!-- Lista de templates -->
    <b-modal
      id="modal-choose-template"
      :title="$t('campaign.campaignModalListMessageHandler.chooseTemplate')"
      hide-footer
      ref="modal-choose-template"
      size="xl"
    >
      <campaign-modal-choose-template-handler
        :campaign-id="campaignId"
        @add-campaign-template="addCampaignTemplate"
        @hide-modal="hideModal"
        @open-modal="openModal"
      />
    </b-modal>
    <!-- Lista de mensagens rápidas -->
    <b-modal
      id="modal-choose-quick-message"
      :title="$t('campaign.campaignModalListMessageHandler.chooseMessage')"
      hide-footer
      ref="modal-choose-quick-message"
      size="xl"
    >
      <campaign-modal-choose-quick-message-handler
        :campaign="campaign"
        :base-url-storage="baseUrlStorage"
        @add-campaign-quick-message="addCampaignQuickMessage"
        @add-campaign-template="addCampaignTemplate"
        @hide-modal="hideModal"
        @open-modal="openModal"
      />
    </b-modal>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BTable, BFormCheckbox, BCard, BRow, BCol, BAvatar, BBadge,
  BDropdown, BDropdownItem, BTabs, BTab, BPagination, VBTooltip,
} from 'bootstrap-vue'
import store from '@/store'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useCampaignModalListMessageHandler from './useCampaignModalListMessageHandler'
import CampaignModalChooseTemplateHandler from '../campaign-modal-choose-template-handler/CampaignModalChooseTemplateHandler.vue'
import CampaignModalChooseQuickMessageHandler from '../campaign-modal-choose-quick-message-handler/CampaignModalChooseQuickMessageHandler.vue'
import useCampaignTemplatesList from './useCampaignTemplatesList'
import TemplateDisplayMessage from '../../../management/templates/TemplateDisplayMessage.vue'
import QuickMessageDisplay from '../../../management/quick-messages/QuickMessageDisplay.vue'
import Vue from 'vue'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

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
    BPagination,
    
    // Form Validation
    ValidationProvider,
    ValidationObserver,

    CampaignModalChooseTemplateHandler,
    CampaignModalChooseQuickMessageHandler,

    TemplateDisplayMessage,
    QuickMessageDisplay,
  },
  directives: {
    'b-tooltip': VBTooltip,
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    message: {
      type: Object,
      required: true,
    },
    messageItems: {
      type: Array,
      required: true,
    },
    campaignId: {
      type: Number,
      required: true,
    },
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
        {key: 'qui_title', label: "Nome"},  
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
      quickMessagesFields: [
        {key: 'show', label: "Exibir"},  
        {key: 'qui_title', label: "Nome"},    
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
        {key: 'quick_messages', label: "Respostas Aceitas"},
        { key: 'actions', label: 'Ações' }
      ],
      items: [],
      /*quickMessageLocal: {
        content: '',
      },*/
      sendButtonDisabled: true
    }
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    //Método para quando um mensagem rápida é selecionada
    openModal(campaignId) {
      //Abre o modal para cadastro de mensagem rápida
      this.$emit('open-modal', 'modal-new-message-'+campaignId)  
    },
    removeMessage(messageId) {
      //Abre o modal para cadastro de mensagem rápida
      this.$emit('remove-message', messageId)  
    },
    handleQuickMessageClick(message, campaignId) {
      //Abre o modal para atualização de mensagem rápida
      this.$emit('edit-message', message)
      this.$emit('open-modal', 'modal-new-message-'+campaignId)  
    },
  },
  created() { 
  },
  setup(props,{ emit }) {

    const toast = useToast()
   //Remove um template de uma campanha
   const removeCampaignTemplate = ( id ) => {
      //Chama o componente de loading screen
      Vue.prototype.$isLoading(true)
      store.dispatch('app-campaign/removeCampaignTemplate', 
      { 
        id: id,
      })
        .then(() => {
          fetchCampaignTemplates(props.campaign)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Template removido com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
        .finally(() => {
          //Esconde a loading screen
          Vue.prototype.$isLoading(false) 
        })
    }

    //Pega a mensagem rápida selecionada
    const addCampaignTemplate = (templateData) => {
      templateMessageData.value = templateData.template[0]
      store.dispatch('app-campaign/addCampaignTemplate', 
      { 
        templateData: templateMessageData.value,
        campaignId: props.campaignId
      })
        .then(() => {
          fetchCampaignTemplates(props.campaign)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Template adicionado a campanha com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //#################### MENSAGENS RÁPIDAS DE CAMPANHA

    const addCampaignQuickMessage = quickMessageData => {
      var quickMessage = quickMessageData.quickMessage[0]
      store.dispatch('app-campaign/addMessage', { 
        quickMessageData: quickMessage,
        campaignId: props.campaignId
      })
        .then(() => {
          // eslint-disable-next-line no-use-before-define
          emit('fetch-campaign-messages', props.campaignId)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Mensagem adicionada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Não foi possível salvar a mensagem',
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          })
        })
    }
    

    const {
      quickMessageLocal,
      //resetTransferLocal,
      // UI
      onSubmit,
    } = useCampaignModalListMessageHandler(toRefs(props), emit)

    const {
      campaignTemplateItems,
      perPageCampaignTemplate,
      currentPageCampaignTemplate,
      perPageOptionsTemplate,
      refCampaignTemplateListTable,
      templateMessageData,
      //categoryFilter,
      //statusTemplateFilter,
      loadingCampaignTemplatesRefresh,
      dataMetaCampaignTemplate,
      tableColumnsCampaignTemplates,
      totalCampaignTemplates,
      fetchCampaignTemplates,
      perPageOptionsCampaignTemplate,

      resolveStatusVariant,

    } = useCampaignTemplatesList()

    fetchCampaignTemplates(props.campaign)

    return {
      // Add New Event
      quickMessageLocal,
      onSubmit,

      campaignTemplateItems,
      perPageCampaignTemplate,
      currentPageCampaignTemplate,
      perPageOptionsTemplate,
      refCampaignTemplateListTable,
      templateMessageData,
      //categoryFilter,
      //statusTemplateFilter,
      loadingCampaignTemplatesRefresh,
      dataMetaCampaignTemplate,
      tableColumnsCampaignTemplates,
      totalCampaignTemplates,
      fetchCampaignTemplates,
      perPageOptionsCampaignTemplate,

      addCampaignTemplate,
      addCampaignQuickMessage,

      resolveStatusVariant,

      removeCampaignTemplate,
      
    }
  },
}
</script>