<template>
  <div>
    <!-- select 2 demo  -->
    <b-form
      enctype="multipart/form-data"
      @submit.prevent="onSubmit"
    >
      <b-table
        :items="contractItems"
        :fields="fields"
        show-empty
        :empty-text="$t('campaign.campaignModalListMessageHandler.noMessagesFound')"
        responsive
      >

        <template #cell(com_dt_start)="data">
          <div class="text-nowrap">
            <span class="align-text-top text-capitalize"> {{ formatDateOnlyNumber(data.item.com_dt_start) }} </span>
          </div>
        </template>

        <template #cell(com_dt_end)="data">
          <div class="text-nowrap">
            <span class="align-text-top text-capitalize"> {{ formatDateOnlyNumber(data.item.com_dt_end) }} </span>
          </div>
        </template>

        <!-- Ações -->
        <template #cell(actions)="row">
          <span
            v-if="row.item.com_link"
          >
            <a :href="row.item.com_link" target="_blank" rel="noopener noreferrer">
              <feather-icon
                icon="DownloadIcon"
                size="16"
                class="cursor-pointer"
                v-b-tooltip.hover.v-secondary
                :title="$t('invoice.downloadInvoice')"
                @click="downloadContract(row.item)"
              />
            </a>
          </span>
          
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
              @click="openModal('modal-edit-contract-'+row.item.id);"
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
              @click="removeContract(row.item.id)"
              v-b-tooltip.hover.v-secondary
            >
              <feather-icon
                icon="TrashIcon"
                class="mr-50"
              />
              <span>{{ $t('campaign.campaignModalListMessageHandler.delete') }}</span>
            </b-dropdown-item>
          </b-dropdown>
        </template>
    </b-table>
    <!-- Form Actions -->
    <div class="d-flex mt-2 modal-footer">
      <b-button
        v-ripple.400="'rgba(255, 255, 255, 0.15)'"
        v-b-modal.modal-new-contract
        variant="dark"
      >
        <feather-icon
          icon="PlusIcon"
          class="mr-50"
        />
        <span class="align-middle">{{ $t('campaign.campaignModalListMessageHandler.add') }}</span>
      </b-button>
    </div>
    </b-form> 
    <b-modal
      id="modal-new-contract"
      :title="$t('administrationCompany.companyModalListContractHandler.addContract')"
      hide-footer
      ref="modal-new-contract"
      size="lg"
    >
      <company-modal-new-contract-handler
        :contract="{}"
        :company="company"
        @add-contract="addContract"
        @upload-file="handleFileUpload"
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
import { toRefs, ref } from '@vue/composition-api'
import { formatDateOnlyNumber } from '@core/utils/filter'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useCampaignModalListMessageHandler from './useCampaignModalListMessageHandler'
import useCampaignTemplatesList from './useCampaignTemplatesList'
import CompanyModalNewContractHandler from './company-modal-new-contract-handler/CompanyModalNewContractHandler.vue'
import Swal from 'sweetalert2'
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

    CompanyModalNewContractHandler,

  },
  directives: {
    'b-tooltip': VBTooltip,
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    contractItems: {
      type: Array,
      required: true,
    },
    company: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      selectMode: 'single',
      selected: [],
      fields: [
        { key: 'com_dt_start', label: "Data Início" },  
        { key: 'com_dt_end', label: "Data Fim" },
        { key: 'actions', label: 'Ações', class: 'text-right' }
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
    openModal(modalName) {
      //Abre o modal para cadastro de mensagem rápida
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')  
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

    const file = ref(null)

    const handleFileUpload = (fileData) => {
      file.value = fileData
    }

    //Traz os templates cadastrados de acordo com o filtro aplicado
  const fetchContracts = ()  => {
    store.dispatch('app-company/fetchContracts', { 
      companyId: props.company.id,
      })
      .then(response => {
        //props.contractItems = response.data.contracts
        props.company.contracts = response.data.contracts
      })
  }

    const addContract = contractData => {
      console.log('contractData')
      console.log(contractData)

      const formData = new FormData()
      formData.append('name', 'file.jpg')
      formData.append('file', file.value)
      formData.append('contractData', JSON.stringify(contractData))
      
      const config = {
          headers: {
            'content-type': 'multipart/form-data'
          }
      }
      
      store.dispatch('app-company/addContract', formData, config)
        .then(response => {
          //Se houver alguma mensagem de erro
          if(response.data.errorMessage) {
            toast({
              component: ToastificationContent,
              props: {
                title: response.data.errorMessage,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            },
            {
              timeout: false,
            })
          }
          else {
            fetchContracts()
            toast({
              component: ToastificationContent,
              props: {
                title: 'Contrato adicionado com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }   
        })
    }

    const updateContract = contractData => {
      store.dispatch('app-company/updateContract', contractData)
      .then(() => {  
        fetchContracts()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Contrato atualizado com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
    }

    const removeContract = contractId => {
      Swal.fire({
        title: 'Remover Contrato',
        text: "Você tem certeza que deseja remover esse contrato?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        customClass: {
          confirmButton: 'btn btn-primary',
          cancelButton: 'btn btn-outline-danger ml-1',
        },
        buttonsStyling: false,
      }).then(result => {
            //Caso o usuário queira que o contato avalie o atendimento 
            if (result.value) {
              store.dispatch('app-company/removeContract', { id: contractId })
                .then(() => {
                  fetchContracts()
                  toast({
                    component: ToastificationContent,
                    props: {
                      title: 'Contrato removido com sucesso!',
                      icon: 'CheckIcon',
                      variant: 'success',
                    },
                  })
                })
            } 
          })
    }

    const downloadContract = campaignData => {
      
      //Chama a loading screen
      Vue.prototype.$isLoading(true)
      store.dispatch('app-company/downloadContract', campaignData)
      .then(response => {
        console.log(response.data)
        const anchor = document.createElement("a")
        anchor.setAttribute("download", response.data.filename)
        anchor.setAttribute("href", response.data.linkData)
        document.body.appendChild(anchor)
        anchor.click();
        document.body.removeChild(anchor)
        //Atualiza a lista de contatos do mailing
        //refetchData()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Download do contrato realizado com sucesso!',
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

      fetchContracts,
      addContract,
      updateContract,
      removeContract,
      downloadContract,
      handleFileUpload,

      resolveStatusVariant,

      formatDateOnlyNumber,
    }
  },
}
</script>