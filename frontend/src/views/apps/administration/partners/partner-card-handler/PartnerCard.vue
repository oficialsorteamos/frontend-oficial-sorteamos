<template>
  <!--
  <b-card
    :img-src="require('@/assets/images/banner/banner-12.jpg')"
    img-alt="Profile Cover Photo"
    img-top
    class="card-profile"
  >
  -->
  <div>
  <div 
    class="d-flex justify-content-between flex-column pt-1 pr-1 pb-1"
    :style="skin == 'light'? 'background-color: white' : ''"
  >
    
    <div 
      class="d-flex flex-sm-row-reverse"
    >
      <feather-icon icon="SettingsIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none"
        v-b-tooltip.hover.v-secondary
        :title="$t('administrationCompany.companyCard.settings')"
        @click="openModal('modal-settings-'+partner.id)"
      />
      
      <feather-icon icon="EditIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('administrationCompany.companyCard.editCompany')"
        @click="$emit('open-modal', 'modal-edit-partner-'+partner.id)"
      />
      <!--
      <feather-icon icon="FileTextIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('administrationCompany.companyCard.contracts')"
        @click="openModal('modal-list-contract-'+partner.id)"
      />

      <feather-icon icon="RefreshCwIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('administrationCompany.companyCard.updateCompanyInformation')"
        @click="updateCompanyDetails(partner.id)"
      />
      -->
      <!--
      <feather-icon icon="SettingsIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('campaign.cardAdvanceProfile.editSettings')"
        @click="openModal('modal-settings-'+campaign.id)"
      />
      
      <feather-icon icon="MessageSquareIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('campaign.cardAdvanceProfile.addMessages')"
        @click="openModal('modal-messages-'+campaign.id); fetchCampaignMessages(campaign.id)"
      />
      -->
    </div>
  </div>
  <b-card
    img-alt="Profile Cover Photo"
    img-top
    :class="skin == 'light'? 'text-center rounded': 'text-center rounded border-primary'"
  >
    
    <div class="profile-image-wrapper mb-1">
      <div class="profile-image p-0">
        <b-avatar
          size="114"
          variant="light-primary"
        >
          <span class="d-flex align-items-center">
            <feather-icon
              size="40"
              icon="BriefcaseIcon"
            />
          </span>
        </b-avatar>
      </div>
    </div>
    
    <b-badge
      class="profile-badge"
      variant="light-primary"
      style="font-size: 18px"
    >
      {{ partner.par_corporate_name }}
    </b-badge>
    <h6 class="text-muted mt-1">
      <ul class="list-unstyled">
        <li>
          <small style="font-size: 13px">{{ partner.par_responsible_phone | VMask(' +## (##) #####-####') }} - {{ partner.par_responsible_email }}</small>
        </li>
        <li>
          <small style="font-size: 13px"><a :href="partner.par_url" target="_blank" rel="noopener noreferrer">{{ partner.par_url  }}</a></small>
        </li>
      </ul>
    </h6>
    
    <div
      class="mt-2"
    >
      <b-badge
        class="profile-badge"
        variant="success"
        v-if="partner.par_status == 'A'"
      >
        {{ $t('administrationCompany.companyCard.active') }}
      </b-badge>
      <b-badge
        class="profile-badge"
        variant="danger"
        v-else-if="partner.par_status == 'I'"
      >
        {{ $t('administrationCompany.companyCard.inactive') }}
      </b-badge>
      <!-- Se tiver alguma fatura vencida -->
      <!--
      <b-badge
        class="profile-badge"
        variant="danger"
        v-if="company.details.com_total_overdue_invoices > 0"
      >
        {{ company.details.com_total_overdue_invoices }} {{ $t('administrationCompany.companyCard.invoicesDue') }}
      </b-badge>
      -->
    </div>
    <div
      style="margin-top: 5px"
    >
      <b-badge
        class="profile-badge"
        :variant="partner.type_partner_id == 1 ? 'primary' : 'dark'" 
      >
        {{ partner.type_partner_id == 1 ? $t('partner.partnerCard.dealer')  : $t('partner.partnerCard.whiteLabel') }}
      </b-badge>
    </div>
    
    <hr class="mb-2">

    <!-- follower projects rank -->
    <div class="d-flex justify-content-between align-items-center mb-1">
      <div
        style="width: 33%"
      >
        <h6 class="text-muted font-weight-bolder">
          <feather-icon
            icon="BriefcaseIcon"
            size="16"
            class="mr-10"
            style="margin-bottom: 3px"
          />
          {{ $t('partner.partnerCard.companies') }}
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-primary">
            {{ partner.totalCompaniesPartner }}
          </b-badge>
        </h3>
      </div>
      <div
        style="width: 33%"
      >
        <h6 class="text-muted font-weight-bolder">
          <feather-icon
            icon="ChannelsIcon"
            size="16"
            class="mr-10"
            style="margin-bottom: 3px"
          />
          {{ $t('partner.partnerCard.users') }}
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-success">
            {{ partner.totalUsersCompaniesPartner }}
          </b-badge>
        </h3>
      </div>
      <div
        style="width: 33%"
      >
        <h6 class="text-muted font-weight-bolder">
          <feather-icon
            icon="SmartphoneIcon"
            size="16"
            class="mr-10"
            style="margin-bottom: 3px"
          />
          {{ $t('partner.partnerCard.channels') }}
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-dark">
            {{ partner.totalChannelsCompaniesPartner }}
          </b-badge>
        </h3>
      </div>
    </div>

    <!-- follower projects rank -->
    <!--
    <div class="d-flex justify-content-between mb-1">
      <div
        style="width: 33%"
      >
        <h6 class="text-muted font-weight-bolder">
          <feather-icon
            icon="PhoneOutgoingIcon"
            size="16"
            class="mr-10"
            style="margin-bottom: 3px"
          />
          {{ $t('administrationCompany.companyCard.messagesSent') }}
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-dark">
            {{ company.details.com_total_messages_sent }}
          </b-badge>
        </h3>
      </div>
      
      <div
        style="width: 33%"
      >
        <h6 class="text-muted font-weight-bolder">
          <feather-icon
            icon="PhoneIncomingIcon"
            size="16"
            class="mr-10"
            style="margin-bottom: 3px"
          />
          {{ $t('administrationCompany.companyCard.messagesReceived') }}
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-secondary">
            {{ company.details.com_total_messages_received }}
          </b-badge>
        </h3>
      </div>
      <div
        style="width: 33%"
      >
        <h6 class="text-muted font-weight-bolder" style="margin-bottom: 23px">
          <feather-icon
            icon="PhoneIcon"
            size="16"
            class="mr-10"
            style="margin-bottom: 3px"
          />
          {{ $t('administrationCompany.companyCard.services') }}
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-secondary">
            {{ company.details.com_total_services }}
          </b-badge>
        </h3>
      </div>
    </div>
    -->

    <!--
    <div class="justify-content-between align-items-center">
      <div>
        <h6 class="text-muted font-weight-bolder">
          <feather-icon
            icon="TrendingUpIcon"
            size="16"
            class="mr-10"
            style="margin-bottom: 3px"
          />
          Waiting Sending
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-warning">
            {{ campaign.totalContactPendingSending }}
          </b-badge>
        </h3>
      </div>
    </div>
    -->

    <!-- Form para cadastro de uma nova mensagem para uma campanha -->
    <!--
    <b-modal
      :id="'modal-new-message-'+campaign.id"
      :title="$t('campaign.cardAdvanceProfile.newMessages')"
      hide-footer
      ref="modal-new-message"
      size="lg"
    >
      <campaign-modal-new-message-handler
        :new-message="newMessageData"
        :clear-new-message-data="clearNewMessageData"
        :campaign-id="campaign.id"
        @add-message="addMessage"
        @update-message="updateMessage"
        @hide-modal="hideModal"
      />
    </b-modal>
    -->

    <b-modal
      :id="'modal-settings-'+partner.id"
      :title="$t('voip.voipCardHandler.addSetting')"
      hide-footer
      size="lg"
    >
      <!-- select 2 demo -->
      <partner-modal-setting-handler
        :partner="partner"
        @hide-modal="hideModal"
      />
    </b-modal>
  </b-card>
  </div>
</template>

<script>
import { BCard, BAvatar, BBadge, BDropdown, BDropdownItem, VBTooltip, BTooltip, } from 'bootstrap-vue'
import {
  ref, onUnmounted
} from '@vue/composition-api'
import store from '@/store'
import axios from '@axios'
import { formatDateOnlyNumber } from '@core/utils/filter'
import PartnerModalSettingHandler from './partner-modal-settings-handler/PartnerModalSettingHandler.vue'
import VueApexCharts from 'vue-apexcharts'
import { $themeColors } from '@themeConfig'
import useAppConfig from '@core/app-config/useAppConfig'
import Swal from 'sweetalert2'
import Vue from 'vue'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BCard,
    BBadge,
    BAvatar,
    BDropdown,
    BDropdownItem,
    BTooltip,

    VueApexCharts,
    PartnerModalSettingHandler,
  },
  props: {
    partner: {
      type: Object,
      required: true,
    },
    fetchPartners: {
      type: Function,
      required: true,
    },
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    return {
      currentDate: new Date().toJSON().slice(0, 10),
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
  setup(props) {
    //Toast Notification
    const toast = useToast()
    const { skin } = useAppConfig()

    const updateCompanyDetails = companyId => {
      store.dispatch('app-company/updateCompanyDetails', {
          companyId: companyId
        })
        .then(() => {
          //Carrega as empresas
          props.fetchCompanies()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Detalhes da empresa atualizados com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }
    

    const updateStatusCampaign = (typeCampaignId, statusId) => {

        if(typeCampaignId != 4) {
          store.dispatch('app-campaign/updateStatusCampaign', {
          campaignId: props.partner.id, 
          statusId: statusId
          })
          .then(response => {
            props.campaign.status_id = response.data.statusId
            //Se a campanha foi pausada
            if(response.data.statusId == 1) {
              toast({
                component: ToastificationContent,
                props: {
                  title: 'Campanha pausada com sucesso!',
                  icon: 'CheckIcon',
                  variant: 'success',
                },
              })
            }
            else if(response.data.statusId == 2) {
              toast({
                component: ToastificationContent,
                props: {
                  title: 'Campanha iniciada com sucesso!',
                  icon: 'CheckIcon',
                  variant: 'success',
                },
              })
            }
            
          })
        }
        else {
          Swal.fire({
              title: 'Você tem certeza?',
              html: "<b>Campanhas de Ligação via WhatsApp</b> não podem ser pausadas e nem finalizadas manualmente após serem iniciadas",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Sim',
              customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ml-1',
              },
              buttonsStyling: false,
            }).then(result => {
              //Caso o usuário queira fechar o atendimento
              if (result.value) {
                store.dispatch('app-campaign/updateStatusCampaign', {
                campaignId: props.partner.id, 
                statusId: statusId
                })
                .then(response => {
                  props.campaign.status_id = response.data.statusId
                  //Se a campanha foi pausada
                  if(response.data.statusId == 1) {
                    toast({
                      component: ToastificationContent,
                      props: {
                        title: 'Campanha pausada com sucesso!',
                        icon: 'CheckIcon',
                        variant: 'success',
                      },
                    })
                  }
                  else if(response.data.statusId == 2) {
                    toast({
                      component: ToastificationContent,
                      props: {
                        title: 'Campanha iniciada com sucesso!',
                        icon: 'CheckIcon',
                        variant: 'success',
                      },
                    })
                  }
                  
                })
              }
          })
        }
    }
    
    //######### LISTA DE MENSAGENS DE CAMPANHA ##########

    const campaignMessageItems = ref([])

    const fetchCampaignMessages = (campaignId)  => {
      console.log('fetchCampaignMessages')
      //Traz as mensagens cadastradas para uma determinada campanha 
      axios
        .get('/api/campaign/fetch-messages/'+campaignId)
        .then(response => {
          campaignMessageItems.value = response.data.campaignMessages
          //Atualiza a lista de pendências da campanha, se houver
          props.campaign.pendencies = response.data.campaign.pendencies
          console.log('pendencias')
          console.log(response.data.campaign)
        });
    }

    //Remove uma mensagem da campanha
    const removeCampaignMessage = messageId => {
      store.dispatch('app-campaign/removeMessage', { id: messageId })
        .then(response => {
          console.log(response)
          fetchCampaignMessages(response.data.campaignId)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Mensagem removida com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const blankQuickMessage = {
      content: '',
    }
    const compaignMessageData = ref(JSON.parse(JSON.stringify(blankQuickMessage)))


    
    
    //########################### NOVA MENSAGEM DE CAMPANHA #################################
    const blankNewMessage = {
      id: null,
      content: '',
    }
    const newMessageData = ref(JSON.parse(JSON.stringify(blankNewMessage)))
    //Limpa os dados do popup
    const clearNewMessageData = () => {
      newMessageData.value = JSON.parse(JSON.stringify(blankNewMessage))
    }

    //Abre o modal já preenchida para atualização da mensagem
    const handleMessageClick = (messageData) => {
      newMessageData.value = messageData
    }


    const updateMessage = messageData => {
      store.dispatch('app-campaign/updateMessage', messageData)
        .then(response => {
          // eslint-disable-next-line no-use-before-define
          fetchCampaignMessages(response.data.campaignId)
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

    /*######################### Mailing ############################*/
    const file = ref(null)

    const handleFileUpload = (fileData) => {
      file.value = fileData
    }

    const downloadMailingModel = campaignData => {
      
      //Chama a loading screen
      Vue.prototype.$isLoading(true)
      store.dispatch('app-campaign/downloadMailingModel', campaignData)
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
            title: 'Download do modelo do mailing realizado com sucesso!',
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


    //########################## CAMPAIGN ##############################
    const updateCampaign = campaignData => {
      store.dispatch('app-campaign/updateCampaign', campaignData)
        .then(response => {
          props.campaign.type_campaign = response.data.type_campaign
          props.campaign.cam_name = response.data.cam_name
          props.campaign.cam_description = response.data.cam_description
          toast({
            component: ToastificationContent,
            props: {
              title: 'Campanha atualizada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //Remove uma mensagem da campanha
    const removeCampaign = campaignId => {
      
      Swal.fire({
        title: 'Remover Campanha',
        text: "Você realmente quer remover essa campanha?",
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
          store.dispatch('app-campaign/removeCampaign', { id: campaignId })
          .then(() => {
            //Carrega as campanhas cadastradas
            props.fetchCampaigns()
            toast({
              component: ToastificationContent,
              props: {
                title: 'Campanha removida com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          })
        }
      })
    }


    return {
      campaignMessageItems,
      compaignMessageData,
      updateCompanyDetails,
      formatDateOnlyNumber,

      newMessageData,

      removeCampaignMessage,
      fetchCampaignMessages,

      handleMessageClick,
      clearNewMessageData,
      updateMessage,

      downloadMailingModel,
      handleFileUpload,

      updateStatusCampaign,
      updateCampaign,
      removeCampaign,

      skin,
    }
  }
}
</script>
