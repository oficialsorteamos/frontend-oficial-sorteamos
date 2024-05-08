<template>
  <b-card
    no-body
    class="card-employee-task"
  >
    <b-card-header>
      <b-card-title>
        {{ title }}
        <b-badge
          pill
          variant="light-dark"
          v-if="servicesData"
        >
          {{ totalServices}}
        </b-badge>
      </b-card-title>
      <!--
      <feather-icon
        icon="MoreVerticalIcon"
        size="18"
        class="cursor-pointer"
      />
      -->
    </b-card-header>

    <!-- body -->
    <b-card-body>
      <div
        v-for="service in servicesData"
        :key="service.contact_id"
        :class="'p-1 mb-1 rounded border-'+color"
      >
          <div class="d-flex justify-content-between w-100 mb-1">
            <h6 class="section-label">
              {{service.cha_name}}
              <font-awesome-icon 
                :icon="['fab', 'whatsapp']" 
                size="1x" 
                style="color: #34af23 !important;"
                v-if="service.type_channel_id == 1"
              />
              <b-badge
                pill
                variant="light-danger"
                v-if="service.cam_name"
                :title="service.cam_name"
                v-b-popover.hover.top="service.cam_description"
              >
                <span
                  v-if="service.campaign_type_id == 1"
                >
                  C - whatsapp
                </span>
                <span
                  v-else-if="service.campaign_type_id == 2"
                >
                  C - SMS
                </span>
                <span
                  v-else-if="service.campaign_type_id == 4"
                >
                  C - Ligação Whatsapp
                </span>
              </b-badge>
              <b-badge
                pill
                variant="light-danger"
                v-if="service.dia_name"
                :title="service.dia_name"
                v-b-popover.hover.top="service.cam_description"
              >
                {{ service.dia_name }}
              </b-badge>
            </h6>
            <b-badge
              pill
              variant="primary"
              v-b-tooltip.hover.v-secondary
              :title="$t('services.cardServicesInProgress.protocolNumber')"
            >
              {{ service.ser_protocol_number }}
            </b-badge>
          </div>
        <div
          class="employee-task d-flex justify-content-between align-items-center"
        >
          <!-- Dados do contato -->
          <b-media no-body>
            <b-media-aside class="mr-75">
              <b-avatar
                rounded
                size="42"
                :src="service.con_avatar? baseUrlStorage+service.con_avatar : null"
              />
            </b-media-aside>
            <b-media-body class="my-auto">
              <span
                v-if="service.con_name"
              >
                <h6 
                  class="mb-0"
                  v-if="service.con_name.length <= 15"
                >
                  {{ service.con_name }}
                </h6>
                <h6 
                  class="mb-0"
                  v-else
                >
                  {{ service.con_name.substring(0,15)+".." }}
                </h6>
              </span>
              <span
                v-else
              >
                <h6 
                  class="mb-0"
                >
                  Contato
                </h6>
              </span>
              
              <small>{{ service.con_phone | VMask(' +## (##) #####-####') }}</small>
            </b-media-body>
          </b-media>
          
          <small class="text-muted mr-75">
            <feather-icon
              icon="RepeatIcon"
              size="20"
            />
          </small>

          <div class="d-flex align-items-center">
            <b-media no-body>
              <b-media-aside class="mr-75">
                <b-avatar
                  :src="'../../../'+service.avatar"
                  :text="avatarText(service.fullName)"
                  variant="light-primary"
                  size="42px"
                  rounded
                >
                  <!-- Caso o atendimento tenha  -->
                  <span 
                    class="d-flex align-items-center"
                    v-if="service.dep_name && service.name"
                  >
                    <feather-icon
                      icon="UserIcon"
                      size="20"
                    />
                  </span>
                  <span 
                    class="d-flex align-items-center"
                    v-else-if="!service.dep_name && !service.name"
                  >
                    <font-awesome-icon 
                      :icon="['fas', 'robot']" 
                      size="2x"
                    />
                  </span>
                  <span 
                    class="d-flex align-items-center"
                    v-else
                  >
                    <font-awesome-icon 
                      :icon="['fas', 'question']" 
                      size="2x"
                    />
                  </span>
                </b-avatar>
              </b-media-aside>
              <b-media-body class="my-auto">
                <!-- Se o chat estiver ativo -->
                <span
                  v-if="service.dep_name && service.name"
                >
                  <h6 
                    class="mb-0"
                    v-if="service.name.length <= 15"
                  >
                    {{ service.name }}
                  </h6>
                  <h6 
                    class="mb-0"
                    v-else
                  >
                    {{ service.name.substring(0,15)+".." }}
                  </h6>
                  <small>{{ service.dep_name }}</small>
                </span>
                <!-- Se for autoatendimento ( ainda não em departamento nem operador atendimento) -->
                <span
                  v-else-if="!service.dep_name && !service.name"
                >
                  <h6 class="mb-0">
                    {{ $t('services.cardServicesInProgress.bot') }}
                  </h6>
                  <small>-</small>
                </span>
                <!-- Se o atendimento está pendente -->
                <span
                  v-else
                >
                  <h6 class="mb-0">
                    <span v-if="service.name">
                      {{ service.name }}
                    </span>
                    <span v-else>
                      -
                    </span>
                  </h6>
                  <small>
                    <span v-if="service.dep_name">
                      {{ service.dep_name }}
                    </span>
                    <span v-else>
                      -
                    </span>
                  </small>
                </span>
              </b-media-body>
            </b-media>
          </div>
        </div>
        <div class="d-flex" style="justify-content: center">
          <b-button
            v-ripple.400="'rgba(113, 102, 240, 0.15)'"
            variant="outline-primary"
            size="sm"
            class="mr-1"
            @click="openModal('modal-chat-'+service.chat_id)"
          >
            {{ $t('services.cardServicesInProgress.openChat') }}
          </b-button>
          <b-button
            v-ripple.400="'rgba(186, 191, 199, 0.15)'"
            variant="outline-secondary"
            size="sm"
            @click="openModal('modal-transfer-'+service.chat_id)"
          >
            {{ $t('services.cardServicesInProgress.transferService') }}
          </b-button>
          <b-button
            v-ripple.400="'rgba(113, 102, 240, 0.15)'"
            variant="outline-danger"
            size="sm"
            class="ml-1"
            @click="confirmCloseService(service.chat_id)"
          >
            {{ $t('services.cardServicesInProgress.close') }}
          </b-button>
        </div>
        <!-- Modal com o form para transferir uma conversa -->
        <b-modal
          :id="'modal-transfer-'+service.chat_id"
          :title="$t('services.cardServicesInProgress.transferConversation')"
          hide-footer
        >
          <!-- select 2 demo -->
          <chat-modal-transfer-handler
            :transfer="transferData"
            :chat-id="service.chat_id"
            :service-id="service.id"
            :clear-transfer-data="clearTransferData"
            :situationService="situationService"
            @add-transfer="transferService"
            @hide-modal="hideModal"
          />
        </b-modal>
        <!-- Modal com o chat do atendimento -->
        <b-modal
          :id="'modal-chat-'+service.chat_id"
          title="Chat"
          hide-footer
          size="lg"
        >
          <card-chat-service 
            :contact-id="service.contactId"
            :base-url-storage="baseUrlStorage"
          />
        </b-modal>
      </div>
      <div
        class="no-results text-center"
        v-if="servicesData.length == 0"
      >
        <h5>{{ $t('services.cardServicesInProgress.noServicesFound') }}</h5>
      </div>
      <div class="text-center">
        <!-- Botão para chats em autoatendimento -->
        <b-button
          variant="outline-primary"
          class="btn-icon rounded-circle"
          @click="$emit('load-self-services', {offset: offsetSelfService, skip: true} ); sumOffset(1);"
          :hidden="hiddenButtonService"
          v-b-tooltip.hover.v-secondary
          :title="$t('contacts.contactViewUserTimelineCard.showMore')"
          v-if="situationService == 1"
        >
          <feather-icon 
            icon="PlusIcon" 
            size="20"
          />
        </b-button>
        <!-- Botão para chats pendentes -->
        <b-button
          variant="outline-primary"
          class="btn-icon rounded-circle"
          @click="$emit('load-pending-services', {offset: offsetPendingService, skip: true} ); sumOffset(2);"
          :hidden="hiddenButtonService"
          v-b-tooltip.hover.v-secondary
          :title="$t('contacts.contactViewUserTimelineCard.showMore')"
          v-if="situationService == 2"
        >
          <feather-icon 
            icon="PlusIcon" 
            size="20"
          />
        </b-button>
        <!-- Botão para chats ativos -->
        <b-button
          variant="outline-primary"
          class="btn-icon rounded-circle"
          @click="$emit('load-active-services', {offset: offsetActiveService, skip: true} ); sumOffset(3);"
          :hidden="hiddenButtonService"
          v-b-tooltip.hover.v-secondary
          :title="$t('contacts.contactViewUserTimelineCard.showMore')"
          v-if="situationService == 3"
        >
          <feather-icon 
            icon="PlusIcon" 
            size="20"
          />
        </b-button>
      </div>
      <div class="text-right">
        <h6 style="font-size: 10px">{{ $t('services.cardServicesInProgress.showing') }} {{servicesData.length}} {{ $t('services.cardServicesInProgress.of') }} {{totalServices}}</h6>
      </div>
    </b-card-body>

  </b-card>
</template>

<script>
import {
  BCard, BCardTitle, BCardHeader, BCardBody, BMedia, BMediaAside, BAvatar, BMediaBody, BBadge, VBTooltip, BButton, VBPopover,
} from 'bootstrap-vue'
import {
  ref
} from '@vue/composition-api'
import { VueMaskFilter } from 'v-mask'
import { avatarText } from '@core/utils/filter'
import Vue from 'vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faWhatsapp } from '@fortawesome/free-brands-svg-icons'
import { faRobot, faQuestion } from '@fortawesome/free-solid-svg-icons'
import ChatModalTransferHandler from '../chat/chat-modal-transfer-handler/ChatModalTransferHandler.vue'
import CardChatService from './CardChatService.vue'
import Badge from '../../components/badge/Badge.vue'
import Ripple from 'vue-ripple-directive'
import Swal from 'sweetalert2'
Vue.filter('VMask', VueMaskFilter)
library.add( faWhatsapp, faRobot, faQuestion)

export default {
  components: {
    BCard,
    BCardTitle,
    BCardHeader,
    BCardBody,
    BMedia,
    BMediaAside,
    BMediaBody,
    BAvatar,
    BBadge,
    BButton,

    VueMaskFilter,
    Badge,

    ChatModalTransferHandler,
    CardChatService,
  },
  directives: {
    'b-tooltip': VBTooltip,
    Ripple,
    'b-popover': VBPopover,
  },
  props: {
    title: {
      type: String,
      required: true,
    },
    color: {
      type: String,
      required: true,
    },
    servicesData: {
      type: Array,
      required: true,
    },
    situationService: {
      type: Number,
      required: true,
    },
    hiddenButtonService: {
      type: Boolean,
      required: true,
    },
    totalServices: {
      type: Number,
      required: true,
    },
    baseUrlStorage: {
      type: String,
      required: true,
    },
  },
  methods: {
    hideModal(modalName) {
      console.log('modal name')
      console.log(modalName)
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    openModal(modalName) {
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
    transferService(transfData) {
      //Fecha o Modal
      this.$emit('transfer-service', transfData)
    },
    sumOffset(typeService) {
      //Se for um autoatendimento
      if(typeService == 1) {
        this.offsetSelfService += 1
      }
      //Se for um atendimento pendente
      else if(typeService == 2) {
        this.offsetPendingService += 1
      }
      //Se for um chat ativo
      else if(typeService == 3) {
        this.offsetActiveService += 1
      }
    },
  },
  data() {
    return {
      chartData: [],
      offsetSelfService: 1,
      offsetPendingService: 1,
      offsetActiveService: 1,
    }
  },
  setup(props, {emit}) {

    //###### TRANSFERÊNCIA DE ATENDIMENTO ######
    const blankTransfer = {
      department: '',
      user: '',
      chatId: '',
      situationService: '',
    }
    const transferData = ref(JSON.parse(JSON.stringify(blankTransfer)))
    //Limpa os dados do popup
    const clearTransferData = () => {
      transferData.value = JSON.parse(JSON.stringify(blankTransfer))
    }

    // confirm close service
    const confirmCloseService = chatId => {
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
              //Chama a função que finaliza o atendimento
              emit('close-service', {chatId: chatId, sendEvaluation: true})
            } 
            else {
              emit('close-service', {chatId: chatId, sendEvaluation: false})
            }
          })
        }
      })
    }

    return {
      avatarText,
      transferData,
      clearTransferData,
      confirmCloseService,
    }
  }
}
/* eslint-disable global-require */
</script>
