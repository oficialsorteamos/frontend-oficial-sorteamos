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
      <!-- Botão para deletar canal (Só exibe se o canal estiver desabilitado) -->
      <feather-icon icon="TrashIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none"
        v-b-tooltip.hover.v-secondary
        :title="$t('chatbot.chatbotCardHandler.removeChatbot')"
        @click="removeChatbot(chatbot.id)"
      />
      <!-- Se a campanha estiver PAUSADA ou NÃO INICIADA, coloca em andamento, caso contrário, coloca como pausada -->
      <feather-icon 
        :icon="chatbot.cha_status == 'I'? 'PlayIcon' : 'PauseIcon'" 
        size="17"
        class="cursor-pointer d-sm-block d-none mr-1"
        v-b-tooltip.hover.v-secondary
        :title="chatbot.cha_status == 'A'? $t('chatbot.chatbotCardHandler.pauseChatbot') : $t('chatbot.chatbotCardHandler.startChatbot')"
        @click="chatbot.cha_status == 'A'? updateStatusChatbot('I') : updateStatusChatbot('A')"
        v-if="!chatbot.pendencies"
        stroke="#28C76F"
      />
      <feather-icon 
        icon="PlayIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none mr-1"
        :title="chatbot.pendencies"
        v-else-if="chatbot.pendencies"
        stroke="#FF9F43"
        :id="'pendencies-message'+chatbot.id"
      >
      </feather-icon>
      <b-tooltip v-if="chatbot.pendencies" :target="'pendencies-message'+chatbot.id"><span v-html="chatbot.pendencies"> </span> </b-tooltip>
      <!-- Botão para editar os dados do contato -->
      <feather-icon icon="EditIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('chatbot.chatbotCardHandler.editChatbot')"
        @click="openModal('modal-edit-chatbot-'+chatbot.id)"
      />
      <!-- Botão para editar os dados do contato -->
      <feather-icon icon="SettingsIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('chatbot.chatbotCardHandler.editSettings')"
        @click="openModal('modal-settings-'+chatbot.id)"
      />
      <router-link
        :to="{ name: 'apps-chatbot-view-structure', params: { id: chatbot.id} }"
        style="text-decoration: none; color: inherit;"
      >
        <!-- Botão para editar os dados do contato -->
      <feather-icon icon="EyeIcon" 
        size="17"
        class="cursor-pointer d-sm-block d-none float-left mr-1"
        v-b-tooltip.hover.v-secondary
        :title="$t('chatbot.chatbotCardHandler.viewStructure')" 
      />
      </router-link>
    </div>
  </div>
  <b-card
    img-alt="Profile Cover Photo"
    img-top
    :class="skin == 'light'? 'text-center' : 'text-center border-primary'"
  >
    
    <div class="profile-image-wrapper">
      <div class="profile-image p-0">
        <b-avatar
          size="114"
          variant="light-primary"
        >
          <span class="d-flex align-items-center">
            <font-awesome-icon 
              :icon="['fas', 'robot']" 
              size="4x"
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
      {{chatbot.cha_name}}
    </b-badge>
    <h6 class="text-muted">
      <ul class="list-unstyled">
        <li>
          <small v-if="chatbot.cha_description != ''">{{chatbot.cha_description}}</small>
          <small v-else>-</small>
        </li>
      </ul>
    </h6>
    <div
      class="mb-1"
    >
      <b-badge
        class="profile-badge"
        variant="primary"
        v-if="chatbot.type_chatbot_id == 1"
      >
        {{ $t('chatbot.chatbotCardHandler.service') }}
      </b-badge>
      <b-badge
        class="profile-badge"
        variant="dark"
        v-else-if="chatbot.type_chatbot_id == 2"
      >
        {{ $t('chatbot.chatbotCardHandler.campaign') }}
      </b-badge>
    </div>
    
    <b-badge
      class="profile-badge"
      variant="warning"
      v-if="chatbot.cha_status == 'I'"
    >
      {{ $t('chatbot.chatbotCardHandler.paused') }}
    </b-badge>
    <b-badge
      class="profile-badge"
      variant="success"
      v-else-if="chatbot.cha_status == 'A'"
    >
      {{ $t('chatbot.chatbotCardHandler.inProgress') }}
    </b-badge>
    
    <hr class="mb-2">

      <b-row>
        <!-- Quantidade de blocos -->
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
        <!-- Quantidade de ações -->
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

    <!-- Edita as informações do chatbot -->
    <b-modal
      :id="'modal-edit-chatbot-'+chatbot.id"
      :title="$t('chatbot.chatbotCardHandler.chatbotEdit')"
      hide-footer
      size="lg"
    >
      <!-- select 2 demo -->
      <chatbot-modal-edit-chatbot-handler
        :chatbot="chatbot"
        @update-chatbot="updateChatbot"
        @hide-modal="hideModal"
      />
    </b-modal>

    <!-- Contém os canais associados oa chatbot -->
    <b-modal
      :id="'modal-settings-'+chatbot.id"
      :title="$t('chatbot.chatbotCardHandler.addSetting')"
      hide-footer
      size="lg"
    >
      <!-- select 2 demo -->
      <chatbot-modal-settings-handler
        :chatbot="chatbot"
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
import axios from '@axios'
import { $themeColors } from '@themeConfig'
import useAppConfig from '@core/app-config/useAppConfig'
import ChatbotModalEditChatbotHandler from '../chatbot-modal-new-chatbot-handler/ChatbotModalNewChatbotHandler.vue'
import ChatbotModalSettingsHandler from './chatbot-modal-settings-handler/ChatbotModalSettingsHandler.vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faRobot } from '@fortawesome/free-solid-svg-icons'
import Swal from 'sweetalert2'
import Vue from 'vue'
library.add( faRobot )

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
    
    ChatbotModalEditChatbotHandler,
    ChatbotModalSettingsHandler,
  },
  props: {
    chatbot: {
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

    const updateStatusChatbot = statusId => {
      store.dispatch('app-chatbot/updateStatusChatbot', {
        chatbotId: props.chatbot.id, 
        statusId: statusId
        })
        .then(response => {
          props.chatbot.cha_status = response.data.statusId
          //Se a campanha foi pausada
          if(response.data.statusId == 'I') {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Chatbot Pausado',
                text: 'Chatbot pausado com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }
          else if(response.data.statusId == 'A') {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Chatbot Iniciado',
                text: 'Chatbot iniciado com sucesso!',
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
      updateStatusChatbot,
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
