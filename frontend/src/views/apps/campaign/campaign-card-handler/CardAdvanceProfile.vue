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
      <feather-icon icon="TrashIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none"
        v-b-tooltip.hover.v-secondary
        :title="$t('campaign.cardAdvanceProfile.notRemoveCampaign')"
        stroke="#FF9F43"
        v-if="campaign.campaign_type_id == 4 && (campaign.status_id != 3 && campaign.status_id != 9 && campaign.status_id != 4)"
      />
      <feather-icon icon="TrashIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none"
        v-b-tooltip.hover.v-secondary
        :title="$t('campaign.cardAdvanceProfile.removeCampaign')"
        @click="removeCampaign(campaign.id)"
        v-else
      />
      <!-- Se a campanha estiver PAUSADA ou NÃO INICIADA ou PAUSADA POR FALTA DE SALDO, coloca em andamento, caso contrário, coloca como pausada -->
      <!-- Só mostra o botão de PAUSAR quando NÃO é uma campanha de LIGAÇÃO VIA WHATSAPP -->
      <feather-icon 
        :icon="campaign.status_id == 1 || campaign.status_id == 4 || campaign.status_id == 6? 'PlayIcon' : 'PauseIcon'" 
        size="17"
        class="cursor-pointer d-sm-block d-none mr-1"
        v-b-tooltip.hover.v-secondary
        :title="campaign.status_id == 2? $t('campaign.cardAdvanceProfile.pauseCampaign') : $t('campaign.cardAdvanceProfile.playCampaign')"
        @click="campaign.status_id == 1 || campaign.status_id == 4 || campaign.status_id == 6? updateStatusCampaign(campaign.campaign_type_id, 2) : updateStatusCampaign(campaign.campaign_type_id, 1)"
        v-if="((campaign.campaign_type_id != 4 && campaign.status_id != 3) || (campaign.campaign_type_id == 4 && campaign.status_id == 4)) && !campaign.pendencies"
        stroke="#28C76F"
      />
      <feather-icon 
        icon="PlayIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none mr-1"
        :title="campaign.pendencies"
        v-else-if="campaign.status_id != 3 && campaign.pendencies"
        stroke="#FF9F43"
        :id="'pendencies-message'+campaign.id"
      >
      </feather-icon>
      <b-tooltip v-if="campaign.pendencies" :target="'pendencies-message'+campaign.id"><span v-html="campaign.pendencies"> </span> </b-tooltip>
      <!-- Botão para editar os dados do contato -->
      <feather-icon icon="EditIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('campaign.cardAdvanceProfile.editCampaign')"
        @click="openModal('modal-edit-campaign-'+campaign.id)"
      />
      <!-- Botão para editar os dados do contato -->
      <feather-icon icon="SettingsIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('campaign.cardAdvanceProfile.editSettings')"
        @click="openModal('modal-settings-'+campaign.id)"
      />
      <!-- Botão para editar os dados do contato -->
      <feather-icon icon="MessageSquareIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('campaign.cardAdvanceProfile.addMessages')"
        @click="openModal('modal-messages-'+campaign.id); fetchCampaignMessages(campaign.id)"
      />
      <!-- Botão para editar os dados do contato -->
      <feather-icon icon="UserPlusIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('campaign.cardAdvanceProfile.addContacts')"
        @click="openModal('modal-mailing-'+campaign.id);"
        v-if=" campaign.campaign_type_id != 4 || (campaign.campaign_type_id == 4 && campaign.status_id == 4)"
      />
      <!-- Botão para editar os dados do contato -->
      <feather-icon icon="UserPlusIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('campaign.cardAdvanceProfile.notAddMailing')"
        v-else
        stroke="#FF9F43"
      />
      <router-link
        :to="{ name: 'apps-campaign-view-mailing', params: { id: campaign.id } }"
        style="text-decoration: none; color: inherit;"
      >
        <!-- Botão para editar os dados do contato -->
      <feather-icon icon="EyeIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('campaign.cardAdvanceProfile.viewMailing')" 
      />
      </router-link>
    </div>
  </div>
  <b-card
    img-alt="Profile Cover Photo"
    img-top
    :class="skin == 'light'? 'text-center rounded': 'text-center rounded border-primary'"
  >
    <!--
    <div class="profile-image-wrapper">
      <div class="profile-image p-0">
        
        <b-avatar
          size="114"
          variant="light"
          :src="require('@/assets/images/portrait/small/avatar-s-9.jpg')"
        />
        
        <b-avatar
          size="114"
          :src="'../../../'+contact.con_avatar"
          :badge="contact.avatar"
          class="badge-minimal"
          :variant="contact.avatar != null ? 'transparent' : 'light-'+contact.avatarColor"
          :text="contact.initialsName != null ? contact.initialsName : 'CL'"
        />
      </div>
    </div>
    -->
    <b-badge
      class="profile-badge"
      variant="light-primary"
      style="font-size: 18px"
    >
      {{campaign.cam_name}}
    </b-badge>
    <h6 class="text-muted">
      <ul class="list-unstyled">
        <li>
          <small v-if="campaign.cam_description != ''">{{campaign.cam_description}}</small>
          <small v-else>-</small>
        </li>
      </ul>
    </h6>
    <div
      class="mb-1"
    >
      <b-badge
        class="profile-badge"
        variant="dark"
        v-if="campaign.type_campaign"
      >
        {{ campaign.type_campaign.cam_description }}
      </b-badge>
    </div>
    

    <b-badge
      class="profile-badge"
      variant="warning"
      v-if="campaign.status_id == 1"
    >
      {{ $t('campaign.cardAdvanceProfile.pause') }}
    </b-badge>
    <b-badge
      class="profile-badge"
      variant="success"
      v-else-if="campaign.status_id == 2"
    >
      {{ $t('campaign.cardAdvanceProfile.inProgress') }}
    </b-badge>
    <b-badge
      class="profile-badge"
      variant="primary"
      v-else-if="campaign.status_id == 3"
    >
      {{ $t('campaign.cardAdvanceProfile.finished') }}
    </b-badge>
    <b-badge
      class="profile-badge"
      variant="danger"
      v-if="campaign.status_id == 4"
    >
      {{ $t('campaign.cardAdvanceProfile.notStarted') }}
    </b-badge>
    <b-badge
      class="profile-badge"
      variant="warning"
      v-if="campaign.status_id == 6"
    >
      {{ $t('campaign.cardAdvanceProfile.pauseNoBalance') }}
    </b-badge>
    <b-badge
      class="profile-badge"
      variant="warning"
      v-if="campaign.status_id == 7"
    >
      {{ $t('campaign.cardAdvanceProfile.validating') }}
    </b-badge>
    <b-badge
      class="profile-badge"
      variant="warning"
      v-if="campaign.status_id == 8"
    >
      {{ $t('campaign.cardAdvanceProfile.preparingSend') }}
    </b-badge>
    <b-badge
      class="profile-badge"
      variant="danger"
      v-if="campaign.status_id == 9"
    >
      {{ $t('campaign.cardAdvanceProfile.invalid') }}
    </b-badge>
    
    
    <hr class="mb-2">
    <!-- apex chart -->
    <vue-apex-charts
      type="radialBar"
      height="325"
      :options="productOrdersRadialBar.chartOptions"
      :series="productOrdersRadialBar.series"
    />

    <!-- follower projects rank -->
    <div class="d-flex justify-content-between align-items-center mb-1">
      <div
        style="width: 33%"
      >
        <h6 class="text-muted font-weight-bolder">
          <feather-icon
            icon="TrendingUpIcon"
            size="16"
            class="mr-10"
            style="margin-bottom: 3px"
          />
          {{ $t('campaign.cardAdvanceProfile.processedContacts') }}
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-primary">
            {{ campaign.totalContactsProcessed }}
          </b-badge>
        </h3>
      </div>
      
      <div
        style="width: 33%"
      >
        <h6 class="text-muted font-weight-bolder">
          <feather-icon
            icon="SendIcon"
            size="16"
            class="mr-10"
            style="margin-bottom: 3px"
          />
          {{ $t('campaign.cardAdvanceProfile.sentSuccessfully') }}
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-success">
            {{ campaign.totalContactSentMessage }}
          </b-badge>
        </h3>
      </div>
      <div
        style="width: 33%"
      >
        <h6 class="text-muted font-weight-bolder">
          <feather-icon
            icon="XCircleIcon"
            size="16"
            class="mr-10"
            style="margin-bottom: 3px"
          />
          {{ $t('campaign.cardAdvanceProfile.errorSending') }}
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-danger">
            {{ campaign.totalContactSentFailure }}
          </b-badge>
        </h3>
      </div>
    </div>

    <!-- follower projects rank -->
    <div class="d-flex justify-content-between mb-1">
      <div
        style="width: 33%"
      >
        <h6 class="text-muted font-weight-bolder">
          <feather-icon
            icon="SlashIcon"
            size="16"
            class="mr-10"
            style="margin-bottom: 3px"
          />
          {{ $t('campaign.cardAdvanceProfile.blacklist') }}
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-dark">
            {{ campaign.totalContactBlacklist }}
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
          {{ $t('campaign.cardAdvanceProfile.inAttendance') }}
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-secondary">
            {{ campaign.totalContactInService }}
          </b-badge>
        </h3>
      </div>
      <div
        style="width: 33%"
        class="text-center"
      >
        <h6 class="text-muted font-weight-bolder">
          <feather-icon
            icon="RotateCcwIcon"
            size="16"
            class="mr-10"
            style="margin-bottom: 3px"
          />
          {{ $t('campaign.cardAdvanceProfile.returned') }}
        </h6>
        <h3 class="mb-0">
          <b-badge variant="light-info">
            {{ campaign.totalContactReturnedMessage }}
          </b-badge>
        </h3>
      </div>
    </div>
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
    <b-modal
      :id="'modal-messages-'+campaign.id"
      :title="$t('campaign.cardAdvanceProfile.messages')"
      hide-footer
      ref="modal-messages"
      size="xl"
    >
      <campaign-modal-list-message-handler
        :message="compaignMessageData"
        :message-items="campaignMessageItems"
        :campaign-id="campaign.id"
        :campaign="campaign"
        :base-url-storage="baseUrlStorage"
        @fetch-campaign-messages="fetchCampaignMessages"
        @hide-modal="hideModal"
        @remove-message="removeCampaignMessage"
        @open-modal="openModal"
        @edit-message="handleMessageClick"
      />
    </b-modal>

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

    <!-- Adiciona uma lista de contatos que participarão da campanha -->
    <b-modal
      :id="'modal-mailing-'+campaign.id"
      :title="$t('campaign.cardAdvanceProfile.addMailing')"
      hide-footer
    >
      <!-- select 2 demo -->
      <campaign-modal-new-mailing-handler
        :mailing="{}"
        :campaign-id="campaign.id"
        @add-mailing="addMailing"
        @hide-modal="hideModal"
        @upload-file="handleFileUpload"
        @download-mailing-model="downloadMailingModel"
      />
    </b-modal>

    <!-- Contém a configuração do canal -->
    <b-modal
      :id="'modal-settings-'+campaign.id"
      :title="$t('campaign.cardAdvanceProfile.addSettings')"
      hide-footer
      size="lg"
    >
      <!-- select 2 demo -->
      <campaign-modal-settings-handler
        :campaign="campaign"
        :campaign-id="campaign.id"
        @hide-modal="hideModal"
      />
    </b-modal>

    <!-- Edita as informações do canal -->
    <b-modal
      :id="'modal-edit-campaign-'+campaign.id"
      :title="$t('campaign.cardAdvanceProfile.addCampaign')"
      hide-footer
      size="lg"
    >
      <!-- select 2 demo -->
      <campaign-modal-edit-campaign-handler
        :campaign="campaign"
        @update-campaign="updateCampaign"
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
import VueApexCharts from 'vue-apexcharts'
import { $themeColors } from '@themeConfig'
import useAppConfig from '@core/app-config/useAppConfig'
import CampaignModalListMessageHandler from './campaign-modal-list-message-handler/CampaignModalListMessageHandler.vue'
import CampaignModalNewMessageHandler from './campaign-modal-new-message-handler/CampaignModalNewMessageHandler.vue'
import CampaignModalNewMailingHandler from './campaign-modal-new-mailing-handler/CampaignModalNewMailingHandler.vue'
import CampaignModalSettingsHandler from './campaign-modal-settings-handler/CampaignModalSettingsHandler.vue'
import CampaignModalEditCampaignHandler from '../campaign-modal-new-campaign-handler/CampaignModalNewCampaignHandler.vue'
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

    CampaignModalListMessageHandler,
    CampaignModalNewMessageHandler,
    CampaignModalNewMailingHandler,
    CampaignModalSettingsHandler,
    CampaignModalEditCampaignHandler,
  },
  props: {
    campaign: {
      type: Object,
      required: true,
    },
    fetchCampaigns: {
      type: Function,
      required: true,
    },
    baseUrlStorage: {
      type: String,
      required: true,
    },
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    return {
      //chartInfo: {},
    }
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    openModal(modalName) {
      console.log(modalName)
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
  },
  setup(props) {
    //Toast Notification
    const toast = useToast()
    const { skin } = useAppConfig()

    //################### CAMPAIGN ########################

    // confirm texrt
    const confirmText = chatId => {
      Swal.fire({
        title: 'Você tem certeza?',
        text: "Você realmente quer finalizar esse atendimento?",
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
          console.log('dados serviços')
          console.log(activeChat.value)
          //Se é um atendimento de campanha ou de comunicação ativa
          if(activeChat.value.chatContactData.service.campaign_id || activeChat.value.chatContactData.service.active_communication) {
            Swal.fire({
              title: 'Avaliação do Atendimento',
              text: "Você quer que o contato avalie o atendimento?",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Sim',
              cancelButtonText: 'Não',
              customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ml-1',
              },
              buttonsStyling: false,
            }).then(result => {
              //Caso o usuário queira que o contato avalie o atendimento 
              if (result.value) {
                //Chama a função que finaliza o atendimento e solicita uma nota para o atendimento
                closeService(chatId, true)
              } 
              else {
                closeService(chatId, false)
              }
            })
          } //Caso não seja uma mensagem de campanha, fecha o atendimento e solicita uma nota para o atendimento
          else {
            closeService(chatId, true)
          }
        }
      })
    }

    const updateStatusCampaign = (typeCampaignId, statusId) => {

        if(typeCampaignId != 4) {
          store.dispatch('app-campaign/updateStatusCampaign', {
          campaignId: props.campaign.id, 
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
                campaignId: props.campaign.id, 
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

    const addMailing = mailingData => {
      
      const formData = new FormData()
      formData.append('name', 'ivahy.jpg')
      formData.append('file', file.value)
      formData.append('campaignId', mailingData.campaignId)
      //Para cada tag selecionada
      formData.append('tags[]', JSON.stringify(mailingData.tag))
      
      
      const config = {
          headers: {
            'content-type': 'multipart/form-data'
          }
      }
      //Chama a loading screen
      Vue.prototype.$isLoading(true)
      store.dispatch('app-campaign/addMailing', formData, config)
        .then(response => {
          //Se houver alguma mensagem de erro
          if(response.data.errorMessage) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Erro na Importação',
                text: response.data.errorMessage,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            },
            {
              timeout: false,
            })
          }
          else {
            props.campaign.pendencies = response.data.campaign.pendencies
            props.campaign.totalContactMailing = response.data.campaign.totalContactMailing
            console.log( props.campaign)
            props.campaign.status_id = response.data.campaign.status_id
            updateChart()
            
            toast({
              component: ToastificationContent,
              props: {
                title: 'Mailing adicionado com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })  
          }   
        })
        .catch(response => {
          console.log('response')
          console.log(response)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Não foi possível salvar o Mailing',
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

    const totalcontact = ref(0)
    const productOrdersRadialBar = ref({
      //series: [70, 52, 26, 50],
      series: [],
      seriesTotals: [],
      chartOptions: {
        labels: ['Processado', 'Enviadas', 'Erro', 'Atendimento'],
        plotOptions: {
          radialBar: {
            size: 150,
            hollow: {
              size: '20%',
            },
            track: {
              strokeWidth: '100%',
              margin: 11,
            },
            dataLabels: {
              value: {
                fontSize: '1rem',
                colors: '#5e5873',
                fontWeight: '500',
                offsetY: 5,
              },
              total: {
                show: true,
                label: totalcontact,
                fontSize: '1.286rem',
                colors: '#5e5873',
                fontWeight: '500',
                formatter() {
                  return ''
                }
              },
            },
          },
        },
        colors: [$themeColors.primary, $themeColors.success, $themeColors.danger, $themeColors.secondary],
        stroke: {
          lineCap: 'round',
        },
        chart: {
          height: 355,
          dropShadow: {
            enabled: true,
            blur: 3,
            left: 1,
            top: 1,
            opacity: 0.1,
          },
        },
      },
    })
    
    const updateChart = () => {
      console.log('total de contatos')
      console.log(props.campaign.totalContactMailing)
      totalcontact.value = props.campaign.totalContactMailing

      //Se algum contato foi processado (resolver problema da divisão por 0)
      if(props.campaign.totalContactsProcessed > 0) {
        //Seta a quantidade de operações de acordo com as suas classificações
        productOrdersRadialBar.value.series = [((props.campaign.totalContactsProcessed/props.campaign.totalContactMailing)*100).toFixed(2), 
                                          ((props.campaign.totalContactSentMessage/props.campaign.totalContactsProcessed)*100).toFixed(2),  
                                          ((props.campaign.totalContactSentFailure/props.campaign.totalContactsProcessed)*100).toFixed(2),
                                          ((props.campaign.totalContactInService/props.campaign.totalContactsProcessed)*100).toFixed(2)]
      }
      else {
        //Seta a quantidade de operações de acordo com as suas classificações
        productOrdersRadialBar.value.series = [((props.campaign.totalContactsProcessed/props.campaign.totalContactMailing)*100).toFixed(2), 
                                          (props.campaign.totalContactSentMessage).toFixed(2),  
                                          (props.campaign.totalContactSentFailure).toFixed(2),
                                          (props.campaign.totalContactInService).toFixed(2)]
      }
    }

    updateChart()


    return {
      campaignMessageItems,
      compaignMessageData,

      newMessageData,

      removeCampaignMessage,
      fetchCampaignMessages,

      handleMessageClick,
      clearNewMessageData,
      updateMessage,

      addMailing,
      downloadMailingModel,
      handleFileUpload,

      updateStatusCampaign,
      updateCampaign,
      removeCampaign,

      productOrdersRadialBar,
      skin,
    }
  }
}
</script>
