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
        :items="template"
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
            @click="removeDialerTemplate(row.item.id)"
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

      <!--
      <template #cell(template)="data">
        <b-badge
          v-b-tooltip.hover.v-secondary
          :title="data.value.tem_description"
          :variant="resolveStatusVariant(data.value.status.tem_name)" 
        >
          {{ data.value.status.tem_name }}
        </b-badge>
      </template>
      -->
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
        :disabled="template.length > 0"
      >
        <feather-icon
          icon="PlusIcon"
          class="mr-50"
        />
        <span class="align-middle">{{ $t('campaign.campaignModalListMessageHandler.add') }}</span>
      </b-button>
    </div>
      
    <!-- Lista de templates -->
    <b-modal
      id="modal-choose-template"
      :title="$t('campaign.campaignModalListMessageHandler.chooseTemplate')"
      hide-footer
      ref="modal-choose-template"
      size="xl"
    >
      <dialer-modal-choose-template-handler
        :campaign-id="1"
        @add-dialer-template="addDialerTemplate"
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
import useDialerModalListMessageHandler from './useDialerModalListMessageHandler'
import DialerModalChooseTemplateHandler from '../dialer-modal-choose-template-handler/DialerModalChooseTemplateHandler.vue'
import useDialerTemplatesList from './useDialerTemplatesList'
import TemplateDisplayMessage from '../../../../../../../management/templates/TemplateDisplayMessage.vue'
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

    DialerModalChooseTemplateHandler,

    TemplateDisplayMessage,
  },
  directives: {
    'b-tooltip': VBTooltip,
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    template: {
      type: Array,
      required: true,
    },
    /*messageItems: {
      type: Array,
      required: true,
    },*/
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
   const removeDialerTemplate = ( id ) => {
      emit('remove-dialer-template', id)
    }

    //Pega a mensagem rápida selecionada
    const addDialerTemplate = (templateData) => {
      //templateMessageData.value = templateData.template[0]
      emit('add-dialer-template', templateData.template)
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
    } = useDialerModalListMessageHandler(toRefs(props), emit)

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

    } = useDialerTemplatesList()

    //fetchCampaignTemplates(props.campaign)

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

      addDialerTemplate,
      addCampaignQuickMessage,

      resolveStatusVariant,

      removeDialerTemplate,
      
    }
  },
}
</script>