<template>
  <b-card>
        <span
          v-for="notification in channel.notifications"
          :key="notification.id"
        >
          <b-alert
            v-height-fade
            show
            dismissible
            fade
            :variant="notification.type_notification.cha_color"
            @dismissed="hideNotification(notification.id)"
          >
            <div class="alert-body">
              <span>{{notification.cha_content}}</span>
            </div>
          </b-alert>
        </span>
    <b-row>
      <!-- User Info: Left col -->
      <b-col
        cols="12"
        xl="12"
        class="d-flex justify-content-between flex-column"
      >
        <div class="d-flex justify-content-between mb-1">
          <div
            class="justify-content-start"
          >
            <b-badge
              :variant="channel.cha_status == 'A'? 'success' : 'danger'"
            >
              {{ channel.cha_status == 'A'? $t('channel.channelViewInfoCard.enabled') : $t('channel.channelViewInfoCard.disabled') }}
            </b-badge>
            <!-- Se for um canal oficial e a cobrança por canal oficial estiver habilitada -->
            <b-badge
              :variant="channel.cha_subscription? 'success' : 'warning'"
              v-if="channel.cha_api_official == 1 && officialChannelParameter.par_value == 1"
            >
              {{ channel.cha_subscription? $t('channel.channelViewInfoCard.subscribed') : $t('channel.channelViewInfoCard.unsubscribed') }}
            </b-badge>
            <!-- Se for um canal oficial e a cobrança por canal oficial estiver habilitada -->
            <b-badge
              :variant="channel.cha_subscription? 'success' : 'warning'"
              v-if="channel.cha_api_official == 0 && unofficialChannelParameter.par_value == 1"
            >
              {{ channel.cha_subscription? $t('channel.channelViewInfoCard.subscribed') : $t('channel.channelViewInfoCard.unsubscribed') }}
            </b-badge>
            
          </div>
          <div 
            class="d-flex flex-sm-row-reverse"
          >
            <!-- Botão para deletar canal (Só exibe se o canal estiver desabilitado) -->
            <feather-icon icon="TrashIcon" 
              size="17"
              class="cursor-pointer d-sm-block d-none"
              v-if="channel.cha_status == 'I' && ((channel.cha_subscription == 0 || channel.cha_subscription == null) || (channel.cha_api_official == 0 && unofficialChannelParameter.par_value == 0) || (channel.cha_api_official == 1 && officialChannelParameter.par_value == 0))"
              v-b-tooltip.hover.v-secondary
              :title="$t('channel.channelViewInfoCard.deleteChannel')"
              @click="confirmSRemoveChannel(channel)"
            />
            <!-- Botão para conectar ou desconectar o canal (Se for um canal NÃO OFICIAL) -->
            <!-- Se a cobrança estiver desabilitada ou se a cobrança estiver a habilitada e a assinatura em dia -->
            <feather-icon icon="PowerIcon" 
              size="17"
              class="cursor-pointer d-sm-block d-none mr-1"
              v-b-tooltip.hover.v-secondary
              :title="channel.cha_status == 'A'? $t('channel.channelViewInfoCard.disableChannel') : $t('channel.channelViewInfoCard.enableChannel')"
              @click="channel.cha_status == 'A' ? confirmStatusChannel(channel) : (startSession(channel), openModal('modal-connect-channel-'+channel.id, channelIdToConnect = channel.id))"
              v-if="channel.cha_api_official == 0 && ( unofficialChannelParameter.par_value == 0 || (unofficialChannelParameter.par_value == 1 && channel.cha_subscription == 1) )"
            />
            <!-- Se a assinatura estiver vencida -->
            <feather-icon icon="PowerIcon" 
              size="17"
              class="cursor-pointer d-sm-block d-none mr-1"
              v-b-tooltip.hover.v-secondary
              :title="$t('channel.channelViewInfoCard.subscribeToChannel')"
              v-else-if="channel.cha_api_official == 0 && unofficialChannelParameter.par_value == 1 && (channel.cha_subscription == 0 || channel.cha_subscription == null)"
              stroke="#FF9F43"
            />
            <!-- Se for um CANAL OFICIAL -->
            <feather-icon icon="PowerIcon" 
              size="17"
              class="cursor-pointer d-sm-block d-none mr-1"
              v-b-tooltip.hover.v-secondary
              :title="channel.cha_status == 'A'? $t('channel.channelViewInfoCard.disableChannel') : $t('channel.channelViewInfoCard.enableChannel')"
              @click="channel.cha_status == 'A' ? $emit('update-status-channel', channel.id, null,'I') : $emit('update-status-channel', channel.id, null,'A')"
              v-if="channel.cha_api_official == 1 && ( officialChannelParameter.par_value == 0 || (officialChannelParameter.par_value == 1 && channel.cha_subscription == 1) )"
            />
            <!-- Se a assinatura estiver vencida -->
            <feather-icon icon="PowerIcon" 
              size="17"
              class="cursor-pointer d-sm-block d-none mr-1"
              v-b-tooltip.hover.v-secondary
              :title="$t('channel.channelViewInfoCard.subscribeToChannel')"
              v-else-if="channel.cha_api_official == 1 && officialChannelParameter.par_value == 1 && (channel.cha_subscription == 0 || channel.cha_subscription == null)"
              stroke="#FF9F43"
            />
            <!-- Botão para editar os dados do contato -->
            <feather-icon icon="EditIcon" 
              size="17"
              class="cursor-pointer d-sm-block d-none float-left mr-1"
              v-b-tooltip.hover.v-secondary
              :title="$t('channel.channelViewInfoCard.editChannel')"
              @click="openModal('modal-edit-channel-'+channel.id)"
            />
            <!-- Botão para editar os dados do contato. Só exibe se a cobrança por canal estiver habilitada -->
            <feather-icon icon="DollarSignIcon" 
              size="17"
              class="cursor-pointer d-sm-block d-none float-left mr-1"
              v-b-tooltip.hover.v-secondary
              :title="$t('channel.channelViewInfoCard.payment')"
              @click="openModal('modal-add-payment-'+channel.id)"
              v-if="(channel.cha_api_official == 1 && officialChannelParameter.par_value == 1) || (channel.cha_api_official == 0 && unofficialChannelParameter.par_value == 1)"
            />
          </div>
        </div>
        <!-- User Avatar & Action Buttons -->
        <div class="d-flex justify-content-start h-100">
          <span
            @click="$refs.importFile.$el.click()"
            style="cursor: pointer"
          >
          <b-avatar
            :src="'../../../'+channel.con_avatar"
            :text="avatarText(channel.fullName)"
            :variant="`light-${resolveUserRoleVariant(channel.role)}`"
            size="104px"
            rounded
          >
            <span class="d-flex align-items-center">
              <feather-icon
                icon="BriefcaseIcon"
                size="60"
              />
          </span>
          </b-avatar>
          </span>
          <b-form-file
            ref="importFile"
            name="importFile"
            id="importFile"
            accept=".jpeg, .jpg, .png"
            :hidden="true"
            plain
            v-on:change="uploadPhoto"
          />
          <div class="d-flex ml-1 mt-1">
            <div class="mb-1">
              <h4 class="mb-0">
                {{ channel.cha_name }}
              </h4>
              
              <div>
                <cite class="mb-0">
                  {{ channel.cha_description }}
                </cite>
              </div>
              <div style="margin-bottom: 3px">
                <b-badge 
                  :variant="channel.cha_api_official == 0? 'light-info' : 'light-primary'" 
                >
                  <span class="card-text">{{ channel.cha_api_official == 0? $t('channel.channelViewInfoCard.unofficialApi') : $t('channel.channelViewInfoCard.officialApi') }}</span>
                </b-badge>
              </div>
              <!-- Se for um CANAL OFICIAL, mostra a API em que ele está associado -->
              <div
                v-if="channel.cha_api_official == 1"
              >
                <b-badge 
                  variant="light-secondary" 
                >
                  <span class="card-text">{{ channel.api.api_name}}</span>
                </b-badge>
              </div>
              <!-- Se o canal já foi assinado antes e a cobrança está ativada -->
              <div
                v-if="channel.cha_subscription != null && ((channel.cha_api_official == 1 && officialChannelParameter.par_value == 1) || (channel.cha_api_official == 0 && unofficialChannelParameter.par_value == 1))"
              >
                <b-badge 
                  :variant="channel.cha_due >= currentDate ? 'dark' : 'danger'" 
                >
                  <span class="card-text">{{ $t('channel.channelViewInfoCard.due') }} {{ formatDateOnlyNumber(channel.cha_due) }}</span>
                </b-badge>
              </div>
            </div>
          </div>
        </div>
      </b-col>

      <!-- Right Col: Table -->
      <b-col
        cols="12"
        xl="12"
        class="mt-2"
      >
        <table class="mt-2 mt-xl-0 w-100">
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="BriefcaseIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('channel.channelViewInfoCard.company') }}</span>
            </th>
            <td>
              <span v-if="channel.cha_company_name"> {{ channel.cha_company_name }}</span>
              <span v-else> - </span>
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="PhoneIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('channel.channelViewInfoCard.phone') }}</span>
            </th>
            <td>
              <span
                v-if="channel.cha_phone_number.substr(0, 4) == '0800'"
              >
                {{ channel.cha_phone_ddi+channel.cha_phone_number | VMask(' +## #### ### ####') }}
              </span>
              <span
                v-else
              >
                {{ channel.cha_phone_ddi+channel.cha_phone_number | VMask(' +## (##) #####-####') }}
              </span>
              
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="MailIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('channel.channelViewInfoCard.email') }}</span>
            </th>
            <td class="pb-50">
              <span v-if="channel.cha_company_email"> {{ channel.cha_company_email }} </span>
              <span v-else> - </span>
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="GlobeIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('channel.channelViewInfoCard.site') }}</span>
            </th>
            <td class="pb-50">
              <a 
                :href="channel.cha_company_site"
                target="_blank"
              >
                <span v-if="channel.cha_company_site"> {{ channel.cha_company_site }}</span>
                <span v-else> - </span>
              </a>
            </td>
          </tr>
          <tr>
            <th class="pb-50 d-flex">
              <feather-icon
                icon="CalendarIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('channel.channelViewInfoCard.created') }}</span>
            </th>
            <td class="pb-50">
              {{ formatDateTime(channel.created_at) }}
            </td>
          </tr>
          <tr>
            <th 
              class="pb-50 mr-3 d-flex"
            >
              <feather-icon
                icon="MapPinIcon"
                class="mr-75"
              />
              <span class="font-weight-bold" style="vertical-align: middle !important;">{{ $t('channel.channelViewInfoCard.address') }}</span>
            </th>
            <td class="pb-50">
              <span v-if="channel.cha_company_address"> {{ channel.cha_company_address }}</span>
              <span v-else> - </span>
            </td>
          </tr>
        </table>
      </b-col>
    </b-row>
    <!-- Form para cadastro de edição de um canal -->
    <b-modal
      :id="'modal-edit-channel-'+channel.id"
      :title="$t('channel.channelViewInfoCard.editChannel')"
      hide-footer
      size="lg"
    >
      <channel-modal-edit-channel-handler
        :channel="channel"
        :clear-contact-data="clearContactData"
        :official-channel-parameter="officialChannelParameter"
        :unofficial-channel-parameter="unofficialChannelParameter"
        @update-channel="updateChannel"
        @hide-modal="hideModal"
      />
    </b-modal>
    <!-- Modal para adição de crédito para utilização da campanha -->
    <b-modal
      :id="'modal-add-payment-'+channel.id"
      :title="$t('channel.channelViewInfoCard.payment')"
      hide-footer
      size="lg"
    >
      <channel-modal-add-payment-handler
        :channel="channel"
        :cards-data="cardsData"
        :clear-contact-data="clearContactData"
        :add-card="addCard"
        @add-payment="addPayment"
        @update-subscription="updateSubscriptionRenewal"
        @hide-modal="hideModal"
        @open-modal="openModal"
        @set-channel="setChannel"
      />
    </b-modal>
    <!-- Modal que apresenta o Qrcode para pagamento via PIX -->
    <b-modal
      :id="'modal-pix-qrcode-'+channel.id"
      :title="$t('invoice.generatePixQrcode')"
      hide-footer
      size="lg"
    >
      <invoice-modal-pix-qrcode-handler
        :qrcode="pixQrcode"
      />
    </b-modal>
  </b-card>
</template>

<script>
import {
  BCard, BButton, BAvatar, BRow, BCol, BBadge, BFormRating, VBTooltip, VBModal, BFormFile, BAlert,
} from 'bootstrap-vue'
import {
  ref
} from '@vue/composition-api'
import axios from '@axios'
import { avatarText } from '@core/utils/filter'
import useUsersList from './useUsersList'
import { VueMaskFilter } from 'v-mask'
import store from '@/store'
import { formatDate, formatDateTime, formatDateOnlyNumber } from '@core/utils/filter'
import { heightFade } from '@core/directives/animations'
import ChannelModalEditChannelHandler from './channel-modal-edit-channel-handler/ChannelModalEditChannelHandler.vue'
import ChannelModalAddPaymentHandler from './channel-modal-add-payment-handler/ChannelModalAddPaymentHandler.vue'
import InvoiceModalPixQrcodeHandler from '../../financial/invoices/invoice-modal-pix-qrcode-handler/InvoiceModalPixQrcodeHandler.vue'
import Swal from 'sweetalert2'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)

export default {
  components: {
    BCard, 
    BButton, 
    BRow, 
    BCol, 
    BAvatar, 
    BBadge,
    BFormRating,
    VBModal,
    BFormFile,
    BAlert,

    //Formata a data
    formatDate,
    formatDateTime,

    ChannelModalEditChannelHandler,
    ChannelModalAddPaymentHandler,
    InvoiceModalPixQrcodeHandler,
  },
  props: {
    channel: {
      type: Object,
      required: true,
    },
    officialChannelParameter: {
      type: Object,
      required: true,
    },
    unofficialChannelParameter: {
      type: Object,
      required: true,
    },
  },
  directives: {
    'b-tooltip': VBTooltip,
    heightFade,
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
    startSession(channelData) {
      this.$emit('start-session', channelData)
    },
  },
  setup(props, { emit }) {
    const { resolveUserRoleVariant } = useUsersList()

    //Toast Notification
    const toast = useToast()

    const blankChannel = {
      cha_name: '',
      cha_company_email: '',
    }
    const channelData = ref(JSON.parse(JSON.stringify(blankChannel)))
    //Limpa os dados do popup
    const clearContactData = () => {
      channelData.value = JSON.parse(JSON.stringify(blankContact))
    }

    //Atualiza os dados do contato
    const updateChannel = channelData => {
      store.dispatch('app-channel/updateChannel', { channelData: channelData })
        .then(() => {  
          emit('fetch-channels')
          toast({
            component: ToastificationContent,
            props: {
              title: 'Canal atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const photo = ref('')
    
    //Faz o upload da foto de avatar do contato
    const uploadPhoto = (event) => {
      photo.value = event.target.files[0]
      
      const formData = new FormData()
      formData.append('name', 'ivahy.jpg')
      formData.append('file', photo.value)
      formData.append('chatId', props.channel.chat.id)
      formData.append('contactId', props.channel.id)
      
      const config = {
          headers: {
            'content-type': 'multipart/form-data'
          }
      }
      store.dispatch('app-contact/uploadPhoto', formData, config)
        .then(() => {
          emit('get-contact')

          toast({
            component: ToastificationContent,
            props: {
              title: 'Avatar atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
      })
    }

    // confirm texrt
    const confirmStatusChannel = channelData => {
      Swal.fire({
        title: 'Status do Canal',
        text: "Você realmente quer alterar o status do canal?",
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
          //Chama a função que atualiza o status do canal
          emit('update-status-channel', channelData.id, null,'I')
          emit('close-session', channelData)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Status do canal atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        }
      })
    }

    // confirm texrt
    const confirmSRemoveChannel = channelData => {
      Swal.fire({
        title: 'Remover Canal',
        text: "Você realmente quer remover esse canal?",
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
          //Chama a função que atualiza o status do canal
          emit('update-status-channel', channelData.id, null,'D')
          //Se for a api da Wppconnect
          if(channelData.api_communication_api == 2) {
            emit('close-session', channelData)
          }
          
          toast({
            component: ToastificationContent,
            props: {
              title: 'Canal removido com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        }
      })
    }

    //Guarda o id do canal que usuário está tentando conectar
    const channelIdToConnect = ref(null)

    //Pega os dados do usuário no localStorage
    /*
    const userdata = JSON.parse(localStorage.getItem('userData'))
    Echo.private('user.'+userdata.id)
    .listen('.StatusConnection', (statusConnectionData) => {
      if(statusConnectionData.status == 'qrReadError') {
        toast({
          component: ToastificationContent,
          props: {
            title: 'Falha na leitura do qrCode',
            icon: 'AlertTriangleIcon',
            text: 'Não foi possível fazer a leitura do qrCode. Fecha o popup e tente novamente',
            variant: 'danger',
          },
        })
      }
    })*/

    const cardsData = ref(0)

    //Adiciona uma campanha
    const addCard = cardData => {
      store.dispatch('app-channel/addCard', { cardData: cardData })
        .then(() => {
          cardsData.value = cardsData.value + 1
          toast({
            component: ToastificationContent,
            props: {
              title: 'Cartão adicionado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const pixQrcode = ref('')

    //Assina o canal
    const addPayment = paymentData => {
      //Se não for pagamento via PIX
      if(paymentData.payment_method.id != 3) {
        Vue.prototype.$isLoading(true)
      }
      store.dispatch('app-channel/addPayment', { paymentData: paymentData })
        .then(response => {  
          //Se houver alguma mensagem de erro
          if(response.data.errorMessage) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Erro ao Adicionar Crédito',
                text: response.data.errorMessage,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            },
            {
              timeout: 8000,
            })  
          }
          else {
            pixQrcode.value = response.data.qrcode
            emit('fetch-channels')
            //Se for pagamento via Cartão (Não for por PIX)
            if(!response.data.qrcode) {
              toast({
                component: ToastificationContent,
                props: {
                  title: 'Crédito adicionado com sucesso!',
                  icon: 'CheckIcon',
                  variant: 'success',
                },
              })
            }
          }
        })
        .finally(() => {
          //Esconde a loading screen
          Vue.prototype.$isLoading(false) 
        })
    }

    //Adição/Atualização de assinatura de canal
    const updateSubscriptionRenewal = subscriptionData => {
      Vue.prototype.$isLoading(true)
      store.dispatch('app-channel/updateSubscriptionRenewal', { subscriptionData: subscriptionData })
        .then(response => {  
          //Se houver alguma mensagem de erro
          if(response.data.errorMessage) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Erro ao Adicionar Assinatura',
                text: response.data.errorMessage,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            },
            {
              timeout: 8000,
            })  
          }
          else {
            //emit('fetch-channels')
            setChannelSubscription(response.data.channel)
            toast({
              component: ToastificationContent,
              props: {
                title: response.data.successMessage,
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }
        })
        .finally(() => {
          //Esconde a loading screen
          Vue.prototype.$isLoading(false) 
        })
    }

    //Atualiza os dados do canal
    const setChannel = channelData => {
      emit('set-channel', channelData)
    }

    //Atualiza os dados do cartão associado a assinatura
    const setChannelSubscription = channelData => {
      emit('set-channel-subscription', channelData)
    }

    //Atualiza os dados do contato
    const hideNotification = notificationId => {
      //console.log('chamou hideNotification')
      store.dispatch('app-channel/hideNotification', { notificationId: notificationId })
      .then(() => {
        
      })
    }


    return {
      avatarText,
      resolveUserRoleVariant,
      channelIdToConnect,

      formatDate,
      formatDateTime,
      formatDateOnlyNumber,

      ChannelModalEditChannelHandler,
      updateChannel,
      clearContactData,
      confirmStatusChannel,
      confirmSRemoveChannel,
      hideNotification,

      uploadPhoto,
      channelData,

      cardsData,
      addCard,
      addPayment,
      setChannel,
      setChannelSubscription,
      updateSubscriptionRenewal,

      pixQrcode,
    }
  },
}
</script>

<style>

</style>
