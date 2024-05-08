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
      <!-- Se a campanha estiver PAUSADA ou NÃO INICIADA, coloca em andamento, caso contrário, coloca como pausada -->
      <feather-icon 
        :icon="voip.voi_status == 'I'? 'PlayIcon' : 'PauseIcon'" 
        size="17"
        class="cursor-pointer d-sm-block d-none mr-1"
        v-b-tooltip.hover.v-secondary
        :title="voip.voi_status == 'A'? $t('voip.voipCardHandler.active') : $t('voip.voipCardHandler.inactive')"
        @click="voip.voi_status == 'A'? updateStatusVoip('I') : updateStatusVoip('A')"
        v-if="!voip.pendencies"
        stroke="#28C76F"
      />
      <feather-icon 
        icon="PlayIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none mr-1"
        :title="voip.pendencies"
        v-else-if="voip.pendencies"
        stroke="#FF9F43"
        :id="'pendencies-message'+voip.id"
      >
      </feather-icon>
      <b-tooltip v-if="voip.pendencies" :target="'pendencies-message'+voip.id"><span v-html="voip.pendencies"> </span> </b-tooltip>
      <!-- Botão para editar os dados do contato -->
      <feather-icon icon="SettingsIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('voip.voipCardHandler.editSettings')"
        @click="openModal('modal-settings-'+voip.id)"
      />
    </div>
  </div>
  <b-card
    img-alt="Profile Cover Photo"
    img-top
    :class="skin == 'light'? 'text-center': 'text-center rounded border-primary'"
  >
    
    <div class="profile-image-wrapper">
      <div class="profile-image p-0">
        <b-avatar
          size="114"
          variant="light-primary"
        >
          <span class="d-flex align-items-center">
            <feather-icon
              size="40"
              icon="PhoneIcon"
            />
          </span>
        </b-avatar>
      </div>
    </div>
    
    <b-badge
      class="profile-badge mt-1"
      variant="light-primary"
      style="font-size: 18px"
    >
      {{voip.voi_name}}
    </b-badge>
    <h6 class="text-muted">
      <ul class="list-unstyled">
        <li>
          <small v-if="voip.voi_description != ''">{{voip.voi_description}}</small>
          <small v-else>-</small>
        </li>
      </ul>
    </h6>
    
    <b-badge
      class="profile-badge"
      variant="warning"
      v-if="voip.voi_status == 'I'"
    >
      {{ $t('voip.voipCardHandler.inactive') }}
    </b-badge>
    <b-badge
      class="profile-badge"
      variant="success"
      v-else-if="voip.voi_status == 'A'"
    >
      {{ $t('voip.voipCardHandler.active') }}
    </b-badge>
    
    <!--
    <hr class="mb-2">

      <b-row>
        
        <b-col
          lg="6"
          md="6"
          class="border-right"
        >
          <div
            class="transaction-item mt-1"
          >
            <b-media no-body>
              <b-media-aside>
                <b-avatar
                  rounded
                  size="42"
                  variant="light-dark"
                >
                  <feather-icon
                    size="20"
                    icon="BoxIcon"
                  />
                </b-avatar>
              </b-media-aside>
              <b-media-body>
                <h6 class="transaction-title">
                  {{ $t('chatbot.chatbotCardHandler.blocs') }}
                </h6>
                <small>
                  {{ chatbot.totalBlocs }}
                </small>
              </b-media-body>
            </b-media>
          </div>
        </b-col>
        
        <b-col
          lg="6"
          md="6"
        >
          <div
            class="transaction-item mt-1"
          >
            <b-media no-body>
              <b-media-aside>
                <b-avatar
                  rounded
                  size="42"
                  variant="light-success"
                >
                  <feather-icon
                    size="18"
                    icon="CheckSquareIcon"
                  />
                </b-avatar>
              </b-media-aside>
              <b-media-body>
                <h6 class="transaction-title">
                  {{ $t('chatbot.chatbotCardHandler.actions') }}
                </h6>
                <small>
                  {{ chatbot.totalActions }}
                </small>
              </b-media-body>
            </b-media>
          </div>
        </b-col>      
      </b-row>
    -->
    <!-- Contém os canais associados oa chatbot -->
    <b-modal
      :id="'modal-settings-'+voip.id"
      :title="$t('voip.voipCardHandler.addSetting')"
      hide-footer
      size="lg"
    >
      <!-- select 2 demo -->
      <voip-modal-settings-handler
        :voip="voip"
        @hide-modal="hideModal"
      />
    </b-modal>
  </b-card>
  </div>
</template>

<script>
import { BCard, BAvatar, BBadge, BDropdown, BDropdownItem, VBTooltip, BTooltip, BMediaAside, BMediaBody, 
        BMedia, BRow, BCol, } from 'bootstrap-vue'
import {
  ref, onUnmounted
} from '@vue/composition-api'
import store from '@/store'
import useAppConfig from '@core/app-config/useAppConfig'
import VoipModalSettingsHandler from './voip-modal-settings-handler/VoipModalSettingsHandler.vue'
import Swal from 'sweetalert2'

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
    BMediaAside,
    BMediaBody,
    BMedia,
    BRow,
    BCol,
    
    VoipModalSettingsHandler,
  },
  props: {
    voip: {
      type: Object,
      required: true,
    },
    fetchChatbots: {
      type: Function,
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

    const updateStatusVoip = statusId => {
      store.dispatch('app-voip/updateStatusVoip', {
        voipId: props.voip.id, 
        statusId: statusId
        })
        .then(response => {
          props.voip.voi_status = response.data.statusId
          //Se a campanha foi pausada
          if(response.data.statusId == 'I') {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Integração Inativada',
                text: 'Integração inativada com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }
          else if(response.data.statusId == 'A') {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Integração Ativada',
                text: 'Integração ativada com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }
          
        })
    }
    
    
    //########################## CHATBOT ##############################
    const updateChatbot = chatbotData => {
      store.dispatch('app-chatbot/updateChatbot', chatbotData)
        .then(response => {
          props.chatbot.type_chatbot = response.data.type_chatbot
          props.chatbot.type_chatbot_id = response.data.type_chatbot.id
          props.chatbot.cha_name = response.data.cha_name
          props.chatbot.cha_description = response.data.cha_description
          props.chatbot.cha_only_official_channel = response.data.cha_only_official_channel
          toast({
            component: ToastificationContent,
            props: {
              title: 'Chatbot atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //Remove um chatbot
    const removeChatbot = chatbotId => {
      
      Swal.fire({
        title: 'Remover Chatbot',
        text: "Você realmente quer remover esse chatbot?",
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
          store.dispatch('app-chatbot/removeChatbot', { id: chatbotId })
          .then(() => {
            //Carrega as campanhas cadastradas
            props.fetchChatbots()
            toast({
              component: ToastificationContent,
              props: {
                title: 'Chatbot removido com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          })
        }
      })
    }

    
    return {
      updateStatusVoip,
      updateChatbot,
      removeChatbot,

      skin,
    }
  }
}
</script>

<style lang="scss" scoped>
.media-body {
  flex: 0 ;
}
</style>
