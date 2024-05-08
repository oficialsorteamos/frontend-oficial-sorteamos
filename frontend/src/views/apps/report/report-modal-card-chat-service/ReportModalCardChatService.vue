<template>
  <b-card
    class="chat-widget"
    no-body
  >
    <b-card-header>
      <div class="d-flex align-items-center">
        <b-avatar
          size="34"
          :src="'../../'+activeChat.contact.con_avatar"
          class="mr-50 badge-minimal"
          badge
          :badge-variant="activeChat.contact.status == 'online' ? 'success' : 'warning'"
          :variant="activeChat.contact.con_avatar != null ? 'transparent' : 'light-'+activeChat.contact.avatarColor"
          :text="activeChat.contact.initialsName != null ? activeChat.contact.initialsName : 'CL'"
          v-if="activeChat && activeChat.contact"
        />
        <h5 class="mb-0"
          v-if="activeChat.contact"
        >
          {{ activeChat.contact.con_name }}
        </h5>
      </div>
      <feather-icon
        icon="MoreVerticalIcon"
        size="18"
      />
    </b-card-header>

    <section class="chat-app-window">
      <!-- User Chat Area -->
      <vue-perfect-scrollbar
        ref="refChatLogPS"
        :settings="perfectScrollbarSettings"
        class="user-chats scroll-area"
      >
        <chat-log
          :chat-data="activeChat"
          :hidden-button-more-message="hiddenButtonMoreMessage"
          :loading-messages="loadingMessages"
          :waiting-resend-message="waitingResendMessage"
          :profile-user="profileUserDataMinimal"
          :base-url-storage="baseUrlStorage"
          @load-messages="fetchMessagesChat"
          @resend-message="resendMessage"
          :profile-user-avatar="require('@/assets/images/avatars/10.png')"
          v-if="activeChat && activeChat.contact"
        />
      </vue-perfect-scrollbar>

      <!-- Message Input -->
      <!--
      <b-form
        class="chat-app-form"
        @submit.prevent="sendMessage"
      >
        <b-input-group class=" mr-1">
          <b-input-group>
            <b-form-textarea
              @keydown="submitFormText"
              v-model="chatInputMessage"
              style="height: 38px"
              id="input-send-message"
              :placeholder="$t('services.cardChatService.inputMessagePlaceholder')"
              no-resize
            />
            <b-input-group-prepend 
              is-text 
              class="ml-1"
            >
              <b-form-checkbox
                v-model="privateMessage"
                name="checkbox-input"
                v-b-tooltip.hover.v-secondary
                title="Private Message"
              />
            </b-input-group-prepend>
          </b-input-group>
        </b-input-group>
        <b-button
          variant="primary"
          type="submit"
          ref="submitMessageButton"
        >
          {{ $t('services.cardChatService.send') }}
        </b-button>
      </b-form>
      -->
    </section>
  </b-card>
</template>

<script>
import {
  BCard, BCardHeader, BAvatar, BForm, BFormInput, BInputGroup, BButton, BFormCheckbox, BInputGroupPrepend, VBTooltip, BFormTextarea,
} from 'bootstrap-vue'
import store from '@/store'
import {
  ref, nextTick,
} from '@vue/composition-api'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import ChatLog from '@/views/apps/chat/ChatLog.vue'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BCard,
    BCardHeader,
    BAvatar,
    BForm,
    BFormInput,
    BInputGroup,
    BButton,
    BFormCheckbox,
    BInputGroupPrepend,
    BFormTextarea,

    // SFC
    ChatLog,

    // 3rd party
    VuePerfectScrollbar,
  },
  props: {
    contactId: {
      type: Number,
      required: true,
    },
    serviceId: {
      type: Number,
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
      perfectScrollbarSettings: {
        maxScrollbarLength: 150,
        wheelPropagation: false,
      },
      chatData: {
        chat: {
          id: 2, userId: 1, unseenMsgs: 0, chat: [{ message: "How can we help? We're here for you!", time: 'Mon Dec 10 2018 07:45:00 GMT+0000 (GMT)', senderId: 11 }, { message: 'Hey John, I am looking for the best admin template. Could you please help me to find it out?', time: 'Mon Dec 10 2018 07:45:23 GMT+0000 (GMT)', senderId: 1 }, { message: 'It should be Bootstrap 4 compatible.', time: 'Mon Dec 10 2018 07:45:55 GMT+0000 (GMT)', senderId: 1 }, { message: 'Absolutely!', time: 'Mon Dec 10 2018 07:46:00 GMT+0000 (GMT)', senderId: 11 }, { message: 'Modern admin is the responsive bootstrap 4 admin template.!', time: 'Mon Dec 10 2018 07:46:05 GMT+0000 (GMT)', senderId: 11 }, { message: 'Looks clean and fresh UI.', time: 'Mon Dec 10 2018 07:46:23 GMT+0000 (GMT)', senderId: 1 }, { message: "It's perfect for my next project.", time: 'Mon Dec 10 2018 07:46:33 GMT+0000 (GMT)', senderId: 1 }, { message: 'How can I purchase it?', time: 'Mon Dec 10 2018 07:46:43 GMT+0000 (GMT)', senderId: 1 }, { message: 'Thanks, from ThemeForest.', time: 'Mon Dec 10 2018 07:46:53 GMT+0000 (GMT)', senderId: 11 }, { message: 'I will purchase it for sure. 👍', time: '2020-12-08T13:52:38.013Z', senderId: 1 }],
        },
        contact: {
          id: 1,
          fullName: 'Felecia Rower',
          // eslint-disable-next-line global-require
          avatar: require('@/assets/images/avatars/1.png'),
          status: 'away',
        },
      },
    }
  },
  mounted() {
    //this.psToBottom()
  },
  methods: {
    submitFormText(e) {
      if (e.keyCode === 13 && !e.shiftKey) {
        e.preventDefault();
        console.log('entrou')
        this.$refs.submitMessageButton.click();
      }
    },
  },
  setup(props) {
    //Toast Notification
    const toast = useToast()
    //Pega os dados do usuário no localStorage
    const userdata = JSON.parse(localStorage.getItem('userData'))

    const activeChat = ref({})
    const chatInputMessage = ref('')
    const file = ref('')
    const hiddenButtonMoreMessage = ref(false)

    const profileUserDataMinimal = ref({})

    // Scroll to Bottom ChatLog
    const refChatLogPS = ref(null)
    const scrollToBottomInChatLog = () => {
      const scrollEl = refChatLogPS.value.$el || refChatLogPS.value
      scrollEl.scrollTop = scrollEl.scrollHeight
    }
    
    const openChatOfContact = () => {
      //Pega o id do contato
      var contactId = props.contactId
      var serviceId = props.serviceId
      // Reset send message input value
      chatInputMessage.value = ''
      file.value = null

      // Busca as mensagens entre o operador e o cliente
      //store.dispatch('app-service/getChat', { userId })
      store.dispatch('app-service/getChat', {
        contactId: contactId,
        serviceId: serviceId,
        isManager: true //Se o usuário que está abrindo as conversas de um chat é um GESTOR
      })
        .then(response => {
          profileUserDataMinimal.value = response.data.profileUser
          activeChat.value = response.data
          hiddenButtonMoreMessage.value = false
          //Caso o chat tenha menos de 15 mensagens trocadas
          if(response.data.chat.chat.length < 15) {
            //Esconde o botão de exibir "Mais Mensagens" (Já que não teria mais mensagens a serem carregadas pelo botão "Mais Mensagens")
            hiddenButtonMoreMessage.value = true
          }

          // Rola a tela do chat para baixo
          nextTick(() => { scrollToBottomInChatLog() })
        })
    }

    openChatOfContact()

    const loadingMessages = ref(false)

    //Traz as mensagens do usuário de acordo com a rolagem do chat para cima
    const fetchMessagesChat = offset => {
      //Exibi o spinner
      loadingMessages.value = true
      store.dispatch('app-service/fetchMessagesChat', { chatId: activeChat.value.chat.id, serviceId: props.serviceId, offset: offset } )
        .then(response => {
          //Esconde o spinner
          loadingMessages.value = false
          //Se existem atendimentos para ser exibidos
          if(response.data.length > 0) {
              if(offset > 0) {
                //Insere cada novo atendimento carregado no array de serviços
                response.data.map(function(message, key) {
                  activeChat.value.chat.chat.unshift(message)
                });
              }
          }
          else {
            //Esconde o botão que carrega mais atendimentos
            hiddenButtonMoreMessage.value = true
          }
        })
    }

    const privateMessage = ref(false)
    const quickMessageData = ref('teste')

    const sendMessage = () => {
      // Caso exista algum texto no input ou tenha algum arquivo importado
      if (!chatInputMessage.value && !file.value && !quickMessageData.value) return

      const formData = new FormData()
      formData.append('name', 'ivahy.jpg')
      formData.append('file', file.value)
      formData.append('contactId', activeChat.value.contact.id)
      formData.append('typeUserId', 4) //Gestor
      formData.append('senderId', userdata.id)
      formData.append('privateMessage', privateMessage.value) //Se for uma mensagem privada (do gestor para operador, apenas) ou não
      //Se for uma mensagem rápida
      if(quickMessageData.value.content) {
        formData.append('message', quickMessageData.value.content)
      } else {
        formData.append('message', chatInputMessage.value)
      }
      
      const config = {
          headers: {
            'content-type': 'multipart/form-data'
          }
      }

      // Limpa o input de mensagem
      chatInputMessage.value = ''

      store.dispatch('app-service/sendMessage', formData, config)
        .then(response => {
          const { newMessageData, chat } = response.data

          // ? If it's not undefined => New chat is created (Contact is not in list of chats)
          // Se o contato ainda não existia no chat
          if (chat !== undefined) {
            activeChat.value = { chat, contact: activeChat.value.contact }
            //Cria o contato no chat (abre um novo chat)
            chatsContacts.value.push({
              ...activeChat.value.contact,
              chat: {
                id: chat.id,
                lastMessage: newMessageData,
                cha_unseen_messages: 0,
              },
            })
            // Caso o contato já exista
          } else {
            //Define a mensagem como sendo enviada por um GESTOR
            newMessageData.type_user_id = 4
            // Add message to log
            // Adiciona a mensagem no chat
            activeChat.value.chat.chat.push(newMessageData)
            // Scroll to bottom
            nextTick(() => { scrollToBottomInChatLog() })
          }

          /*
          // Limpa o input de mensagem
          chatInputMessage.value = ''
          */
         
          quickMessageData.value.content = null

          //file.value = null
          document.getElementById("importFile").value = null;

          // Set Last Message for active contact
          const contact = chatsContacts.value.find(c => c.id === activeChat.value.contact.id)
          contact.chat.lastMessage = newMessageData

          // Scroll to bottom
          nextTick(() => { scrollToBottomInChatLog() })
        })
    }

    const waitingResendMessage = ref(false)

    //Remove uma mensagem rápida
    const resendMessage = messageData => {
      waitingResendMessage.value = true
      store.dispatch('app-service/resendMessage', { messageData: messageData })
        .then(response => {
          //Se a mensagem foi reenviada com sucesso
          if(response.data.resendMessageData.sendSuccess == true) {
            console.log('chats do contato');
            //Marca a mensagem como enviada dinamicamente
            activeChat.value.chat.chat.find(c => c.id === response.data.resendMessageData.messageId).sendSuccess = true
            activeChat.value.chat.chat.find(c => c.id === response.data.resendMessageData.messageId).status_message_chat_id = null
            console.log(activeChat.value.chat.chat);

            toast({
              component: ToastificationContent,
              props: {
                title: 'Mensagem enviada com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }
          else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Falha ao reenviar a mensagem. Verifique sua conexão está conectado à internet e se o canal utilizado está conectado ao Whatsapp.',
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            })
          }
          console.log('resend data')
          console.log(response.data)
        })
        .finally(() => {
          //Esconde o spinner
          waitingResendMessage.value = false 
        })
        
    }

    //Escuta as mensagens enviadas pelos contatos
    Echo.private('user.'+userdata.id)
    .listen('.SendMessage', (newMessageData) => {
      //console.log('SendMessage')
      //console.log(newMessageData)
      //Se o chat estiver ativo
      if(activeChat.value.contact) {
        //Caso a mensagem tenha sido enviada pelo contato cujo chat está ativo ou tenha sido enviada pelo OPERADOR
        if(activeChat.value.contact.id == newMessageData.message.senderId || 
          (activeChat.value.contact.id == newMessageData.message.contactId && newMessageData.message.type_user_id == 1)) {
          //Adiciona ma mensagem no chat (na tela) 
          activeChat.value.chat.chat.push(newMessageData.message)
          // Scroll to bottom
          nextTick(() => { scrollToBottomInChatLog() })
        }
      } 
    })
    //Atualiza a situação de uma determinada mensagem
    .listen('.UpdateStatusMessage', (statusMessageData) => {
      //console.log('statusMessageData')
      //console.log(statusMessageData)
      //Se a mensagem foi enfileirada, enviada ou entregue, marca o status como sucesso
      /*
      if(statusMessageData.statusId == 1 || statusMessageData.statusId == 2 || statusMessageData.statusId == 3) {
        activeChat.value.chat.chat.find(c => c.id === statusMessageData.messageId).sendSuccess = true
      }
      //Se houve algum erro ao enviar a mensagem 
      else if(statusMessageData.statusId == 4) {
        activeChat.value.chat.chat.find(c => c.id === statusMessageData.messageId).sendSuccess = false
      }
      */
      //Se o chat estiver ativo
      if(activeChat.value.contact) {
        //Atualiza o status da mensagem
        if(activeChat.value.chat.chat.find(c => c.id === statusMessageData.messageId)) {
          activeChat.value.chat.chat.find(c => c.id === statusMessageData.messageId).status_message_chat_id = statusMessageData.statusId
        }
      }
    })

    return {
      activeChat,
      hiddenButtonMoreMessage,
      loadingMessages,
      refChatLogPS,
      chatInputMessage,
      privateMessage,
      profileUserDataMinimal,
      waitingResendMessage,

      fetchMessagesChat,
      sendMessage,
      resendMessage,
    }
  }
}
</script>

<style lang="scss">
@import "@core/scss/base/pages/app-chat-list.scss";

//Esconde o scroll do input de texto
#input-send-message::-webkit-scrollbar {
  display: none;
}

//Esconde o scroll do input de texto
#input-send-message {
    -ms-overflow-style: none; /* for Internet Explorer, Edge */
    scrollbar-width: none; /* for Firefox */
    overflow-y: scroll; 
}
</style>
