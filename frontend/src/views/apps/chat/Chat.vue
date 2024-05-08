<template>
  <!-- Need to add height inherit because Vue 2 don't support multiple root ele -->
  <div style="height: inherit">
    <div
      class="body-content-overlay"
      :class="{'show': shallShowUserProfileSidebar || shallShowActiveChatContactSidebar || mqShallShowLeftSidebar}"
      @click="mqShallShowLeftSidebar=shallShowActiveChatContactSidebar=shallShowUserProfileSidebar=false"
    />

    <!-- Main Area -->
    <section class="chat-app-window">

      <!-- Start Chat Logo -->
      <div
        v-if="!activeChat.contact"
        class="start-chat-area"
      >
        <div class="mb-1 start-chat-icon">
          <feather-icon
            icon="MessageSquareIcon"
            size="56"
          />
        </div>
        <h4
          class="sidebar-toggle start-chat-text"
          @click="startConversation"
        >
          {{ $t('chat.startConversation') }}
        </h4>
      </div>

      <!-- Chat Content -->
      <div
        v-else
        class="active-chat"
      >
        <!-- Chat Navbar -->
        <div class="chat-navbar">
          <header class="chat-header">

            <!-- Avatar & Name -->
            <div class="d-flex align-items-center">

              <!-- Toggle Icon -->
              <div class="sidebar-toggle d-block d-lg-none mr-1">
                <feather-icon
                  icon="MenuIcon"
                  class="cursor-pointer"
                  size="21"
                  @click="mqShallShowLeftSidebar = true"
                />
              </div>

              <b-avatar
                size="36"
                :src="activeChat.contact.con_avatar? urlBaseStorage+activeChat.contact.con_avatar : null"
                class="mr-1 cursor-pointer badge-minimal"
                badge
                :badge-variant="resolveAvatarBadgeVariant(activeChat.contact.status)"
                @click.native="shallShowActiveChatContactSidebar=true"
                :variant="activeChat.contact.con_avatar != null ? 'transparent' : 'light-'+activeChat.contact.avatarColor"
                :text="activeChat.contact.initialsName != null ? activeChat.contact.initialsName : 'CL'"
              />
              <div class="d-flex flex-column"> 
                <h6 class=" pl-1 mb-0 padding-bottom-2">
                  {{ activeChat.contact.con_name }}
                </h6>
                <b-row class="pl-2 mb-0">
                  <!-- Caso exista alguma tag associado ao contato -->
                  <span
                    v-if="activeChat.contact.tags.length > 0"
                  >
                    <!-- Para cada tag -->
                    <span
                      v-for="tag in activeChat.contact.tags"
                      :key="tag.id"
                    >
                      <b-badge
                        :style="'margin-right: 5px; background-color: '+tag.tag_color"
                        
                        v-b-modal.modal-tag
                      >
                        {{ tag.tag_name }}
                    </b-badge>
                    </span>
                  </span>
                  <span
                    v-else
                  >
                    <b-badge 
                      variant="light-secondary" 
                      v-b-modal.modal-tag
                    >
                      {{ $t('chat.none') }}
                  </b-badge>
                  </span>
                </b-row>
              </div>
            </div>

            <!-- Contact Actions -->
            <div class="d-flex align-items-center">
              <!-- Se o usuário não tiver ramal associado -->
              <!--
              <feather-icon
                icon="PhoneCallIcon"
                size="17"
                class="mr-1 d-none d-xl-block"
                v-b-tooltip.hover.v-secondary
                :title="$t('chat.callPhoneContact')"
                v-if="!userExtension"
                stroke="#FF9F43"
              />
              <feather-icon
                icon="PhoneCallIcon"
                size="17"
                class="cursor-pointer  mr-1 d-none d-xl-block"
                v-b-tooltip.hover.v-secondary
                :title="$t('chat.callPhoneContact')"
                @click="callPhone(activeChat.contact.con_phone, userExtension.name, userExtension.id)"
                v-else
              />
              -->
              <feather-icon
                icon="RepeatIcon"
                size="17"
                class="cursor-pointer mr-1 d-none d-xl-block"
                v-b-modal.modal-transfer
                v-b-tooltip.hover.v-secondary
                :title="$t('chat.transferConversation')"
                @click="clearTransferData(); setChatId(activeChat.chat.id);"
                v-if="activeChat.chat.statusService == 'activeService' || showSendMessageArea == true"
              />
              <feather-icon
                icon="CalendarIcon"
                size="17"
                class="cursor-pointer mr-1 d-none d-xl-block"
                v-b-modal.modal-calendar
                v-b-tooltip.hover.v-secondary
                :title="$t('chat.calendar')"
              />
              <feather-icon
                icon="XCircleIcon"
                size="17"
                class="cursor-pointer mr-1 d-none d-xl-block"
                v-b-tooltip.hover.v-secondary
                :title="$t('chat.blacklistCampaign')"
                @click="addContactBlacklistCampaign(activeChat.contact.id, activeChat.chatContactData.service.campaign_id);"
              />
              <feather-icon
                icon="SlashIcon"
                size="17"
                class="cursor-pointer mr-1 d-none d-xl-block"
                v-b-tooltip.hover.v-secondary
                :title="activeChat.contact.blocked?   $t('chat.unlockContact') : $t('chat.blockContact')"
                @click="activeChat.contact.blocked?  unlockContact(activeChat.contact.id) : blockContact(activeChat.contact.id)"
                :stroke="activeChat.contact.blocked? '#FF9F43': '#625F6E'"
                v-if="activeChat.chat.statusService == 'activeService' || showSendMessageArea == true"
              />
              <feather-icon
                icon="PowerIcon"
                size="17"
                class="cursor-pointer  mr-1 d-none d-xl-block"
                v-b-tooltip.hover.v-secondary
                :title="$t('chat.closeService')"
                @click="confirmText(activeChat.chat.id)"
                v-if="activeChat.chat.statusService == 'activeService' || showSendMessageArea == true"
              />
              
              <div class="dropdown d-block d-xl-none">
                <b-dropdown
                  variant="link"
                  no-caret
                  toggle-class="p-0"
                  right
                >
                  <template #button-content>
                    <feather-icon
                      icon="MoreVerticalIcon"
                      size="17"
                      class="align-middle text-body"
                    />
                  </template>
                  <b-dropdown-item
                    v-b-modal.modal-transfer
                    @click="clearTransferData(); setChatId(activeChat.chat.id);"
                    v-if="activeChat.chat.statusService == 'activeService' || showSendMessageArea == true"
                  >
                    {{ $t('chat.transferConversation') }}
                  </b-dropdown-item>
                  <b-dropdown-item
                    v-b-modal.modal-calendar
                  >
                    {{ $t('chat.calendar') }}
                  </b-dropdown-item>
                  <b-dropdown-item
                    @click="addContactBlacklistCampaign(activeChat.contact.id, activeChat.chatContactData.service.campaign_id);"
                  >
                    {{ $t('chat.blacklistCampaign') }}
                  </b-dropdown-item>
                  <b-dropdown-item
                    @click="activeChat.contact.blocked?  unlockContact(activeChat.contact.id) : blockContact(activeChat.contact.id)"
                    v-if="activeChat.chat.statusService == 'activeService' || showSendMessageArea == true"
                  >
                    {{ activeChat.contact.blocked?   $t('chat.unlockContact') : $t('chat.blockContact') }}
                  </b-dropdown-item>
                  <b-dropdown-item
                    @click="confirmText(activeChat.chat.id)"
                    v-if="activeChat.chat.statusService == 'activeService' || showSendMessageArea == true"
                  >
                    {{ $t('chat.closeService') }}
                  </b-dropdown-item>
                 
                </b-dropdown>
              </div>
              
            </div>
          </header>
        </div>

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
            :profile-user="profileUserDataMinimal? profileUserDataMinimal : {}"
            :base-url-storage="urlBaseStorage"
            :message-reply="messageReply"
            @load-messages="fetchMessagesChat"
            @resend-message="resendMessage"
            @set-reply-api-message-id="setMessageReply"
            @close-reply-box="closeReplyBox"
            :style="blurChat || (activeChat.chat.statusService == 'blockService' && showSendMessageArea == false)? 'filter: blur(7px); pointer-events: none;' : null"
            :class="blurChat || (activeChat.chat.statusService == 'blockService' && showSendMessageArea == false)? 'unselectable' : null"
          />
        </vue-perfect-scrollbar>
        <!-- Se o atendimento estiver Pendente -->
        <div
          class="mt-1 text-center"
          v-if="(activeChat.chat.statusService == 'pending' || activeChat.chat.statusService == 'close') && showSendMessageArea == false && activeChat.chatContactData.service"
        >
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="danger"
            @click="startService( {chatId: activeChat.chat.id, channelId: null} );"
          >
            {{ $t('chat.startService') }}
          </b-button>
        </div>
        <!-- Se for uma comunicação ativa -->
        <div
          class="mt-1 text-center"
          v-if="(activeChat.chat.statusService == 'pending' || activeChat.chat.statusService == 'close') && showSendMessageArea == false && !activeChat.chatContactData.service"
        >
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="danger"
            v-b-modal.modal-choose-channel
          >
            {{ $t('chat.startNewService') }}
          </b-button>
        </div>
        <div
          class="mt-1 text-center"
          v-else-if="activeChat.chat.statusService == 'blockService' && showSendMessageArea == false"
        >
          <h4>
            <b-badge 
              variant="light-warning"
            >
              {{ $t('chat.contactAlreadyService') }}
            </b-badge>
          </h4>
        </div>
        <div
          class="mt-1 text-center"
          v-else-if="activeChat.chat.statusService == 'autoService' && showSendMessageArea == false"
        >
          <h4>
            <b-badge 
              variant="light-warning"
            >
              {{ $t('chat.contactSelfService') }}
            </b-badge>
          </h4>
        </div>
        <div
          class="mt-1 text-center"
          v-else-if="activeChat.chat.statusService == 'blockSystem' && showSendMessageArea == false"
        >
          <h4>
            <b-badge 
              variant="light-danger"
            >
              {{ $t('chat.systemBlocked') }}
            </b-badge>
          </h4>
        </div>
        <div 
          v-else-if="activeChat.chat.statusService == 'activeService'"
        >
          <!-- Message Input -->
          <b-form
            class="chat-app-form"
            enctype="multipart/form-data"
            @submit.prevent="sendMessage"
          >
            <b-input-group class="input-group-merge form-send-message mr-1" :style="groupTextAreaStyle">
              <b-form-textarea
                @keydown="submitFormText"
                @keyup="checkValueInput"
                v-model="chatInputMessage"
                :placeholder="textAreaPlaceholder"
                ref="inputMessage"
                :style="textAreaStyle"
                id="input-send-message"
                @drop.stop.prevent="dropFile"
                @drop.stop="dragLeave"
                @dragenter="dragEnter"
                @dragleave="dragLeave"
                @paste="handleFilePaste"
                no-resize
              />
              <b-input-group-append is-text>
                <twemoji-picker
                  :emojiData="emojiDataAll"
                  :emojiGroups="emojiGroups"
                  :skinsSelection="false"
                  :searchEmojisFeat="true"
                  :recentEmojisFeat="true"
                  :randomEmojiArray="['😀']"
                  searchEmojiPlaceholder="Search here."
                  searchEmojiNotFound="Emojis not found."
                  isLoadingLabel="Loading..."
                  :triggerType="'hover'"
                  @emojiUnicodeAdded="emojiSelected"
                  twemojiPath="https://cdnjs.cloudflare.com/ajax/libs/twemoji/14.0.2/"
                >
                </twemoji-picker>
                <!--
                <b-form-checkbox switch class="mr-n2 mb-n1">
                  <span class="sr-only">Checkbox for previous text input</span>
                </b-form-checkbox>
                -->
              </b-input-group-append>
            </b-input-group>
            <!--
            <b-button
              variant="secondary"
              size="sm"
              class="mr-75"
              @click="$refs.importFile.$el.click()"
            >
              <feather-icon icon="LinkIcon" />
            </b-button>
            -->
            <b-form-file
              ref="importFile"
              name="importFile"
              id="importFile"
              accept=".jpeg, .jpg, .png, .pdf, .doc, .docx, .xls, .xlsx, .mp4, .mp3"
              :hidden="true"
              plain
              v-on:change="handleFileUpload"
            />
            <div 
              style="padding-left: 55px; position: sticky;"
            >
              <fab
                :icon-size="iconSize"                
                :bg-color="bgColor"
                :actions="fabActions"
                @cache="cache"
                @alertMe="$refs.importFile.$el.click();"
              ></fab>
            </div>
            <span style="height: 120px">
              <span style="margin-bottom: 100px; position: fixed; margin-left: 7px">
                <b-button
                  variant="danger"
                  class="btn-icon rounded-circle"
                  size="sm"
                  v-if="showCancelAudioRecordButton"
                  @click="cancelAudioRecordSend"
                >
                <feather-icon 
                    icon="TrashIcon"
                  />
                </b-button>
              </span>
              <span @mousedown="clickAudioRecord" style="margin-bottom: -5px">
                <vue-record-audio 
                  v-on:result="onResult"
                  v-if="hasTextInputMessage == false "
                  class="audio-recorder"
                  style="margin-top: 38px"
                />
              </span>
            </span>
            <b-button
              variant="primary"
              v-if="hasTextInputMessage"
              type="submit"
              class="btn-icon rounded-circle"
              size="lg"
              ref="submitMessageButton"
            >
              <feather-icon 
                icon="SendIcon"
              />
            </b-button>
          </b-form>
        </div>
      </div>
    </section>

    <!-- Modal com o form para cadastro de evento na agenda -->
    <b-modal
      id="modal-calendar"
      :title="$t('chat.addEvent')"
      hide-footer
    >
      <calendar-modal-event-handler
        :isEventHandlerSidebarActive="true"
        :event="event"
        :clear-event-data="clearEventData"
        :contactId="activeChat.contact ? activeChat.contact.id : null"
        @add-event="addEvent"
        @hide-modal="hideModal"
      />
    </b-modal>

    <!-- Modal com o form para transferir uma conversa -->
    <b-modal
      id="modal-transfer"
      :title="$t('chat.transferConversation')"
      hide-footer
    >
      <!-- select 2 demo -->
      <chat-modal-transfer-handler
        :transfer="transferData"
        :chat-id="chatId"
        :clear-transfer-data="clearTransferData"
        @add-transfer="transferService"
        @hide-modal="hideModal"
      />
    </b-modal>
    <!-- Lista de mensagens rápidas -->
    <b-modal
      id="modal-quick-message"
      :title="$t('chat.quickMessages')"
      hide-footer
      ref="modal-quick-message"
      size="xl"
    >
      <chat-modal-quick-message-handler
        :quick-message="quickMessageData"
        :template-message="templateMessageData"
        :quick-message-items="quickMessageItems"
        :template-items="templateItems"
        :total-templates="totalTemplateItems"
        :total-quick-messages="totalQuickMessagesItems"
        :fetch-templates="fetchTemplates"
        :fetch-quick-messages="fetchQuickMessages"
        :clear-template-data="clearTemplateData"
        :channel-official="activeChat.chatContactData? activeChat.chatContactData.cha_api_official : false"
        :base-url-storage="urlBaseStorage"
        @send-quick-message="setQuickMessage"
        @send-template-message="setTemplateMessage"
        @hide-modal="hideModal"
        @remove-quick-message="removeQuickMessage"
        @remove-template="removeTemplate"
        @edit-quick-message="handleQuickMessageClick"
        @edit-template="handleTemplateMessageClick"
        @open-modal="openModal"
      />
    </b-modal>

    <!-- Form para cadastro de uma nova mensagem rápida -->
    <b-modal
      id="modal-new-quick-message"
      :title="$t('chat.newQuickMessage')"
      hide-footer
      ref="modal-new-quick-message"
      size="lg"
    >
      <chat-modal-new-quick-message-handler
        :new-quick-message="newQuickMessageData"
        :clear-new-quick-message-data="clearNewQuickMessageData"
        :type-quick-message-id="1"
        @add-quick-message="addQuickMessage"
        @update-quick-message="updateQuickMessage"
        @upload-file="handleFileUploadQuickMessage"
        @hide-modal="hideModal"
      />
    </b-modal>

    <!-- Form para cadastro de um novo template -->
    <b-modal
      id="modal-new-template-message"
      :title="$t('chat.newTemplate')"
      hide-footer
      ref="modal-new-template-message"
      size="xl"
    >
      <chat-modal-new-template-message-handler
        :template-message="newTemplateMessageData"
        :clear-new-quick-message-data="clearNewQuickMessageData"
        :check-template-name-exist="checkTemplateNameExist"
        :error-template-name="errorTemplateName"
        :channelId="activeChat.chatContactData? activeChat.chatContactData.channel_id: {}"
        @add-template-message="addTemplateMessage"
        @update-quick-message="updateQuickMessage"
        @hide-modal="hideModal"
        @upload-file="handleFileUploadTemplate"
      />
    </b-modal>

    <!-- Modal para alterar o status do contato no funil -->
    <b-modal
      id="modal-tag"
      title="Tags"
      hide-footer
    >
      <!-- select 2 demo -->
      <chat-modal-tag-handler
        :tag="activeChat.contact ? activeChat.contact : null"
        :contactId="activeChat.contact ? activeChat.contact.id : null"
        @update-tag="updateTag"
        @hide-modal="hideModal"
      />
    </b-modal>

    <!-- Modal para adição de um novo contato -->
    <b-modal
      id="modal-new-contact"
      :title="$t('chat.addContact')"
      hide-footer
    >
      <!-- select 2 demo -->
      <chat-modal-new-contact-handler
        :new-contact="newContactData"
        :clear-transfer-data="clearNewContactData"
        @add-contact="addContact"
        @hide-modal="hideModal"
        @open-modal="openModal"
      />
    </b-modal>

    <!-- Modal para escolha do canal que será utilizado em uma comunicação ativa -->
    <b-modal
      id="modal-choose-channel"
      :title="$t('chat.chooseChannel')"
      hide-footer
    >
      <!-- select 2 demo -->
      <chat-modal-choose-channel-handler
        :chat="activeChat.chat? activeChat.chat : null"
        @start-service="startService"
        @hide-modal="hideModal"
      />
    </b-modal>

    <!-- Active Chat Contact Details Sidebar -->
    <chat-active-chat-content-details-sidedbar
      :shall-show-active-chat-contact-sidebar.sync="shallShowActiveChatContactSidebar"
      :contact="activeChat.contact || clearContactData"
      :chat="activeChat.chat || {}"
      :base-url-storage="urlBaseStorage"
      :chat-observations="chatObservations"
      :hidden-button-chat-observations="hiddenButtonChatObservations"
      @fetch-chat-observations="fetchChatObservations"
      @add-chat-observation="addChatObservation"
      @remove-chat-observation="removeChatObservation"
      @set-contact="setContact"
      @set-address-contact="setAddressContact"
    />

    <!-- Sidebar -->
    <portal to="content-renderer-sidebar-left">
      <chat-left-sidebar
        :chats-contacts="chatsContacts"
        :pending-contacts="pendingContacts"
        :contacts="contactsSearch"
        :active-chat-contact-id="activeChat.contact ? activeChat.contact.id : null"
        :shall-show-user-profile-sidebar.sync="shallShowUserProfileSidebar"
        :profile-user-data="profileUserData"
        :profile-user-minimal-data="profileUserDataMinimal"
        :mq-shall-show-left-sidebar.sync="mqShallShowLeftSidebar"
        :hidden-button-active-chats="hiddenButtonActiveChats"
        :hidden-button-pending-chats="hiddenButtonPendingChats"
        :search-active-chats="searchActiveChats"
        :search-pending-chats="searchPendingChats"
        :base-url-storage="urlBaseStorage"
        @show-user-profile="showUserProfileSidebar"
        @open-chat="openChatOfContact"
        @open-modal="openModal"
        @set-user-situation="setUserSituation"
        @fetch-active-chats="fetchActiveChats"
        @fetch-pending-chats="fetchPendingChats"
        @set-search-chat="setSearchChat"
        @set-search-contact="setSearchContact"
      />
    </portal>
  </div>
</template>

<script>
import store from '@/store'
import {
  ref, onUnmounted, nextTick, onMounted, watch,
} from '@vue/composition-api'
import {
  BAvatar, BDropdown, BDropdownItem, BForm, BInputGroup, BFormInput, BButton, BFormFile, BBadge, BRow, VBModal, BFormGroup, BFormInvalidFeedback, BFormTextarea,
  BInputGroupAppend, BFormCheckbox, VBTooltip,
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import Ripple from 'vue-ripple-directive'
import VueRecord from '@codekraft-studio/vue-record'
// import { formatDate } from '@core/utils/filter'
import { $themeBreakpoints } from '@themeConfig'
import { useResponsiveAppLeftSidebarVisibility } from '@core/comp-functions/ui/app'
import ChatLeftSidebar from './ChatLeftSidebar.vue'
import chatStoreModule from './chatStoreModule'
import ChatActiveChatContentDetailsSidedbar from './ChatActiveChatContentDetailsSidedbar.vue'
import ChatLog from './ChatLog.vue'
import useChat from './useChat'
import FullCalendar from '@fullcalendar/vue'
import calendarStoreModule from '../calendar/calendarStoreModule'
import CalendarModalEventHandler from '../calendar/calendar-modal-event-handler/CalendarModalEventHandler.vue'
import useCalendar from '../calendar/useCalendar'
import ChatModalTransferHandler from './chat-modal-transfer-handler/ChatModalTransferHandler.vue'
import ChatModalNewContactHandler from './chat-modal-new-contact-handler/ChatModalNewContactHandler.vue'
import ChatModalTagHandler from './chat-modal-tag-handler/ChatModalTagHandler.vue'
import ChatModalQuickMessageHandler from './chat-modal-quick-message-handler/ChatModalQuickMessageHandler.vue'
import ChatModalNewQuickMessageHandler from './chat-modal-new-quick-message-handler/ChatModalNewQuickMessageHandler.vue'
import ChatModalNewTemplateMessageHandler from './chat-modal-new-template-message-handler/ChatModalNewTemplateMessageHandler.vue'
import ChatModalChooseChannelHandler from './chat-modal-choose-channel-handler/ChatModalChooseChannelHandler.vue'
import useContactsList from './useContactsList'
import { TwemojiPicker } from '@kevinfaguiar/vue-twemoji-picker'
import EmojiAllData from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-all-groups.json'
import EmojiDataAnimalsNature from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-animals-nature.json'
import EmojiDataFoodDrink from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-food-drink.json'
import EmojiGroups from '@kevinfaguiar/vue-twemoji-picker/emoji-data/emoji-groups.json'
import fab from 'vue-fab'
import axios from '@axios'
import Swal from 'sweetalert2'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

import Vue from 'vue'

Vue.use(VueRecord)

export default {
  components: {

    // BSV
    BAvatar,
    BDropdown,
    BDropdownItem,
    BForm,
    BInputGroup,
    BFormInput,
    BButton,
    BFormFile,
    BBadge,
    BRow,
    VBModal,
    BFormGroup,
    BInputGroupAppend,
    BFormCheckbox,
    BFormTextarea,

    // 3rd Party
    VuePerfectScrollbar,

    //Gravador de Áudio
    VueRecord,

    // SFC
    ChatLeftSidebar,
    ChatActiveChatContentDetailsSidedbar,
    ChatLog,

    //Agenda
    FullCalendar,
    CalendarModalEventHandler,
    
    //Transferência de Atendimento
    ChatModalTransferHandler,

    //Emojis
    'twemoji-picker': TwemojiPicker,
    EmojiGroups,
    EmojiDataFoodDrink,
    EmojiDataAnimalsNature,

    //Float Actions
    fab,

    //Mensagens rápidas
    ChatModalQuickMessageHandler,
    ChatModalNewQuickMessageHandler,

    //Tags
    ChatModalTagHandler,

    //Adicionar novo contato
    ChatModalNewContactHandler,

    //Escolhe qual canal será utilizado em uma comunicação ativa
    ChatModalChooseChannelHandler,

    ChatModalNewTemplateMessageHandler,
  },
  directives: {
    'b-tooltip': VBTooltip,
    Ripple,
  },
  data() {
    return {
      hasTextInputMessage: false,
      chatId: '',
      bgColor: '#778899',
      iconSize: 'small',
      fabActions: [
          {
              name: 'cache',
              icon: 'message',
              tooltip: 'Mensagens rápidas e Modelos',
              color: '#46C5FF',
          },
          {
              name: 'alertMe',
              icon: 'attachment',
              tooltip: 'Upload',
              color: '#358856',
          }
      ],
      textAreaStyle: 'height: 38px',
      groupTextAreaStyle: '',
      textAreaPlaceholder: 'Escreva sua mensagem',
    };
  },
  computed: {
    emojiDataAll() {
      return EmojiAllData;
    },
    emojiGroups() {
      return EmojiGroups;
    }
  },
  methods: {
    submitFormText(e) {
      if (e.keyCode === 13 && !e.shiftKey) {
        e.preventDefault();
        console.log('entrou')
        this.$refs.submitMessageButton.click();
      }
    },
    checkValueInput() {
      //Se o campo de texto não está vazio
      if(this.$refs.inputMessage.$el.value != '') {
        //Mostra o botão de envio de mensagem
        this.hasTextInputMessage = true;
      }
      else {
        //Mostra o botão de envio de áudio
        this.hasTextInputMessage = false;
      }
      
      //Caso exista mais de 135 caracteres no campo de mensagem, aumenta a altura do mesmo 
      if(this.$refs.inputMessage.$el.value.length > 125) {
        this.textAreaStyle = 'height: 120px;'
        this.groupTextAreaStyle = 'margin-bottom: 70px;'
      }
      else {
        this.textAreaStyle = 'height: 38px;'
        this.groupTextAreaStyle = ''
      }
    },
    setChatId(id) {
      console.log(id)
      this.chatId = id
    },
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    openModal(modalName) {
      console.log(modalName)
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
    //Insere emojis
    emojiSelected: function(emoji) {
      const self = this;
      var tArea = this.$refs.inputMessage;

      // get cursor's position:
      var startPos = tArea.selectionStart,
      endPos = tArea.selectionEnd,
      cursorPos = startPos,
      tmpStr = tArea.value;

      // insert:
      self.chatInputMessage = tmpStr.substring(0, startPos) + emoji + tmpStr.substring(endPos, tmpStr.length);

      // move cursor:
      setTimeout(() => {
        cursorPos += emoji.length;
        tArea.selectionStart = tArea.selectionEnd = cursorPos;
      }, 10);
    },
    cache(){
        //Exibe o modal de mensagens rápidas
        this.$refs['modal-quick-message'].show()
    },
    alert(){
        alert('Clicked on alert icon')
    },
    dragEnter(event) {
      console.log('em cima')
      this.textAreaStyle = 'height: 55px; border-style: dashed !important;'
      this.textAreaPlaceholder = 'Solte o arquivo aqui'
    },
    dragLeave(event) {
      console.log('em baixo')
      this.textAreaStyle = 'height: 38px; border-color: #d8d6de'
      this.textAreaPlaceholder = 'Escreva sua mensagem'
    },
  },
  setup() {
    const CHAT_APP_STORE_MODULE_NAME = 'app-chat'

    // Register module
    if (!store.hasModule(CHAT_APP_STORE_MODULE_NAME)) store.registerModule(CHAT_APP_STORE_MODULE_NAME, chatStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(CHAT_APP_STORE_MODULE_NAME)) store.unregisterModule(CHAT_APP_STORE_MODULE_NAME)
    })

    const CALENDAR_APP_STORE_MODULE_NAME = 'calendar'

    // Register module
    if (!store.hasModule(CALENDAR_APP_STORE_MODULE_NAME)) store.registerModule(CALENDAR_APP_STORE_MODULE_NAME, calendarStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(CALENDAR_APP_STORE_MODULE_NAME)) store.unregisterModule(CALENDAR_APP_STORE_MODULE_NAME)
    })

    const {
      refCalendar,
      isCalendarOverlaySidebarActive,
      event,
      clearEventData,
      addEvent,
      updateEvent,
      removeEvent,
      fetchEvents,
      refetchEvents,
      calendarOptions,

      // ----- UI ----- //
      //isEventHandlerSidebarActive,
    } = useCalendar()

    const { resolveAvatarBadgeVariant } = useChat()

    // Scroll to Bottom ChatLog
    const refChatLogPS = ref(null)
    const scrollToBottomInChatLog = () => {
      const scrollEl = refChatLogPS.value.$el || refChatLogPS.value
      scrollEl.scrollTop = scrollEl.scrollHeight
    }

    // ------------------------------------------------
    // Chats & Contacts
    // ------------------------------------------------
    const chatsContacts = ref([])
    const pendingContacts = ref([])
    const contacts = ref([])
    const showSendMessageArea = ref(false)

    const offsetPendingChats = ref(0)
    const offsetActiveChats = ref(0)

    const skipActiveChats = ref(0)
    const skipPendingChats = ref(0)

    const hiddenButtonActiveChats = ref(false)
    const hiddenButtonPendingChats = ref(false)
    
    const searchActiveChats = ref('')
    const searchPendingChats = ref('')

    const searchActiveChatsAppliedFilter = ref(false)
    const searchPendingChatsAppliedFilter = ref(false)

    const urlBaseStorage = ref('')
    const userExtension = ref(null)


    //Pega os dados do usuário no localStorage
    const userdata = JSON.parse(localStorage.getItem('userData'))

    const fetchChatAndContacts = () => {
      store.dispatch('app-chat/fetchChatsAndContacts')
        .then(response => {
          userExtension.value = response.data.userExtension
          urlBaseStorage.value = response.data.baseUrlStorage
          //console.log(Vue.prototype.$userData)
          chatsContacts.value = response.data.chatsContacts
          chatsContacts.value.totalActiveChats = response.data.totalActiveChats
          
          //Se o total de contatos for igual a quantidade contatos apresentada na tela
          if(response.data.totalActiveChats == chatsContacts.value.length) {
            //Esconde o botão que carrega mais atendimentos
            hiddenButtonActiveChats.value = true  
          }
          
          pendingContacts.value = response.data.pendingContacts
          pendingContacts.value.totalPendingChats = response.data.totalPendingChats
          
          //Se o total de contatos for igual a quantidade contatos apresentada na tela
          if(response.data.totalPendingChats == pendingContacts.value.length) {
            //Esconde o botão que carrega mais atendimentos
            hiddenButtonPendingChats.value = true  
          }
          //contacts.value = response.data.contacts
          // eslint-disable-next-line no-use-before-define
          profileUserDataMinimal.value = response.data.profileUser
        })
    }

    fetchChatAndContacts()

    const setSearchChat = ({searchData: searchData, typeSearch: typeSearch}) => {
      //Se a buscar estiver ocorrendo nos chats ativos
      if(typeSearch == 1) {
        searchActiveChats.value = searchData
      }
      //Se a busca estiver ocorrendo nos chats pendentes
      else if(typeSearch == 2) {
        searchPendingChats.value = searchData
      }
    }

    watch([searchActiveChats,], () => {
      //Se o usuário fez uma busca digitando mais de 3 caracteres 
      if( ((searchActiveChats.value != '' && searchActiveChats.value != null) && searchActiveChats.value.length > 3) ) {
        //Chama a função pesquisando pelos dados
        fetchActiveChats({q: searchActiveChats.value})
        //Esconde o botão de MOSTRAR MAIS chats
        hiddenButtonActiveChats.value = true

        //Seta a variável simbolizando que um filtro foi aplicado aos chats ativos
        searchActiveChatsAppliedFilter.value = true
      }
      else if( (searchActiveChats.value == '' || searchActiveChats.value == null) ) {
        fetchActiveChats({offset: offsetActiveChats.value, skip: false, q: ''})
        searchActiveChatsAppliedFilter.value == false
      }
    })

    watch([searchPendingChats,], () => {
      if( ((searchPendingChats.value != '' && searchPendingChats.value != null) && searchPendingChats.value.length > 3) ) {
        //Chama a função pesquisando pelos dados
        fetchPendingChats({q: searchPendingChats.value})
        //Esconde o botão de MOSTRAR MAIS chats
        hiddenButtonPendingChats.value = true

        //Seta a variável simbolizando que um filtro foi aplicado aos chats ativos
        searchPendingChatsAppliedFilter.value = true
      }
      else if( (searchPendingChats.value == '' || searchPendingChats.value == null) ) {
        fetchPendingChats({offset: offsetPendingChats.value, skip: false, q: ''})
        searchPendingChatsAppliedFilter.value == false
      }
    })

    //Traz os chats ativos associados a um usuário
    const fetchActiveChats = ( {offset: offset, skip: skip} ) => {
      //Se o usuário clicou no botão para mostrar mais chats
      if(skip) {
        if(skip == true) {
          offsetActiveChats.value = offset
        }
      }
      store.dispatch('app-chat/fetchActiveChats', { 
        offset: offset, 
        skip: skip,
        q: searchActiveChats.value
        })
        .then(response => {
          //console.log('active chats')
          //console.log(response)
          //Se existem atendimentos para ser exibidos
          if(response.data.activeContacts.length > 0) {
            //Se um novo atendimento chegou no painel de atendimentos
              if(response.data.skip == null) {
                //Acrescenta em 1 a quantidade de contatos que serão pulados da próxima vez que o botão "Ver Mais" for clicado
                //skipActiveChats.value++
                //Esvazia o array de atendimentos
                chatsContacts.value = []
              }
              else {
                //skipActiveChats.value = 0
              }

            //Se forem os primeiros atendimentos carregados
            if(offset == 0) {
              chatsContacts.value = response.data.activeContacts
            }
            else {
              
              //Insere cada novo atendimento carregado no array de serviços
              response.data.activeContacts.map(function(chat, key) {
                chatsContacts.value.push(chat)
              });
            }

            //Se já foram exibidos todos os chats em autoatendimento (total de autoatendimento é igual ao total de autoatendimentos carregados)
            if(response.data.totalActiveChats == chatsContacts.value.length) {
              //Esconde o botão que carrega mais atendimentos
              hiddenButtonActiveChats.value = true  
            } //Exibe o botão de mais atendimentos
            else {
              if(searchActiveChats.value.length <= 3 )
              hiddenButtonActiveChats.value = false
            }
          }
          else {
            chatsContacts.value = []
            //Esconde o botão que carrega mais atendimentos
            hiddenButtonActiveChats.value = true
          }
          //Atribui a quantidade total de chats ativos
          chatsContacts.value.totalActiveChats = response.data.totalActiveChats
        })
    }

    //Traz os chats ativos associados a um usuário
    const fetchPendingChats = ( {offset: offset, skip: skip} ) => {
      //Se o usuário clicou no botão para mostrar mais chats
      if(skip) {
        if(skip == true) {
          offsetPendingChats.value = offset
        }
      }
      store.dispatch('app-chat/fetchPendingChats', { 
        offset: offset, 
        skip: skip,
        q: searchPendingChats.value
        })
        .then(response => {
          console.log('pending chats')
          console.log(response)
          //Se existem atendimentos para ser exibidos
          if(response.data.pendingContacts.length > 0) {
            //Se um novo atendimento chegou no painel de atendimentos
              if(response.data.skip == null) {
                //Acrescenta em 1 a quantidade de contatos que serão pulados da próxima vez que o botão "Ver Mais" for clicado
                //skipActiveChats.value++
                //Esvazia o array de atendimentos
                pendingContacts.value = []
              }
              else {
                //skipActiveChats.value = 0
              }

            //Se forem os primeiros atendimentos carregados
            if(offset == 0) {
              pendingContacts.value = response.data.pendingContacts
            }
            else {
              
              //Insere cada novo atendimento carregado no array de serviços
              response.data.pendingContacts.map(function(chat, key) {
                pendingContacts.value.push(chat)
              });
            }

            //Se já foram exibidos todos os chats
            if(response.data.totalPendingChats == pendingContacts.value.length) {
              //Esconde o botão que carrega mais atendimentos
              hiddenButtonPendingChats.value = true  
            } //Exibe o botão de mais atendimentos
            else {
              if(searchPendingChats.value.length <= 3 )
              hiddenButtonPendingChats.value = false
            }
          }
          else {
            pendingContacts.value = []
            
            //Esconde o botão que carrega mais atendimentos
            hiddenButtonPendingChats.value = true
          }
          //Atribui a quantidade total de chats ativos
          pendingContacts.value.totalPendingChats = response.data.totalPendingChats
        })
    }


    // ------------------------------------------------
    // Single Chat
    // ------------------------------------------------
    const activeChat = ref({})
    const chatInputMessage = ref('')
    const file = ref('')
    const hiddenButtonMoreMessage = ref(false)
    const blurChat = ref(false)
    const openChatOfContact = ({contactData, blurChatData}) => {
      // Reset send message input value
      chatInputMessage.value = ''
      file.value = null
      //Responsável por ofuscar o chat, caso o operador não tenha permissão para visualizá-lo
      blurChat.value = blurChatData
      // Busca as mensagens entre o usuário e o cliente
      store.dispatch('app-chat/getChat',  {
        contactId: contactData.id,
        isManager: false //Se o usuário que está abrindo as conversas de um chat NÃO é um GESTOR
      })
        .then(response => {
          activeChat.value = response.data
          console.log(activeChat.value)
          chatObservations.value = []
          hiddenButtonChatObservations.value = false
          //Traz as observações relacionadas ao chat
          fetchChatObservations({chatId: activeChat.value.chat.id, offset: 0})
          //Pega os dados associados ao chat e ao contato
          activeChat.value.chatContactData = contactData
          hiddenButtonMoreMessage.value = false
          //Caso o chat tenha menos de 15 mensagens trocadas
          if(response.data.chat.chat.length < 15) {
            //Esconde o botão de exibir "Mais Mensagens" (Já que não teria mais mensagens a serem carregadas pelo botão "Mais Mensagens")
            hiddenButtonMoreMessage.value = true
          }
          
          //Caso o chat esteja pendente ou fechado, coloca o input para envio de mensagens como false (esconde o mesmo)
          if(activeChat.value.chat.statusService == 'pending' || activeChat.value.chat.statusService == 'close') {
            showSendMessageArea.value = false
          } 
           
          // Seta as mensagens não vistas como 0, já que o chat foi clicado e visualizado
          const contact = chatsContacts.value.find(c => c.id === contactData.id)
          if (contact) contact.chat.cha_unseen_messages = 0

          //Retira a tag de chat NOVO
          if (contact.ser_new_service) contact.ser_new_service = null
          // Rola a tela do chat para baixo
          nextTick(() => { scrollToBottomInChatLog() })
        })

      // if SM device =>  Close Chat & Contacts left sidebar
      // eslint-disable-next-line no-use-before-define
      mqShallShowLeftSidebar.value = false
    }
    //Toast Notification
    const toast = useToast()

    //######## Funções para início e finalização de atendimento #########

    const startService = ({chatId, channel}) => {
      store.dispatch('app-chat/startService', { chatId: chatId, channelId: channel? channel.id : null })
        .then(response => {
          //Se a função retornou o serviço (Caso seja uma comunicação ativa)
          if(response.data.service) {
            activeChat.value.chatContactData.service = response.data.service
            activeChat.value.chatContactData.cha_api_official = response.data.channel.cha_api_official
            activeChat.value.chatContactData.service.active_communication = true

            //Libera o input de envio de mensagem
            showSendMessageArea.value = true
            activeChat.value.chat.statusService = 'activeService'
            //Remove o desfoque do texto do chat
            enlivenChat(false)
          }
          if(searchActiveChatsAppliedFilter.value == false) {
            fetchActiveChats( {offset: offsetActiveChats.value, skip: false} )
          }
          
          //Atualiza os chats e contatos na tela
            //fetchChatAndContacts()
        })
    }
    
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
    //Fecha o atendimento em questão
    const closeService = (chatId , sendEvaluation) => {
      console.log('test')
      store.dispatch('app-chat/closeService', { chatId: chatId, sendEvaluation: sendEvaluation })
        .then(() => {

          activeChat.value.chatContactData.service = null
          toast({
            component: ToastificationContent,
            props: {
              title: 'Atendimento finalizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })

          //Atualiza os chats e contatos na tela
          //fetchChatAndContacts()
          //Remove
          chatsContacts.value.splice(chatsContacts.value.findIndex(c => c.id === activeChat.value.contact.id), 1)
          //Se o usuário NÃO está aplicando filtro nos chats ativos
          if(searchActiveChatsAppliedFilter.value == false) {
            fetchActiveChats( {offset: offsetActiveChats.value, skip: false} )
          }
          
          //Esconde a área de envio de mensagens
          showSendMessageArea.value = false
        })
    }


    const showCancelAudioRecordButton = ref(false)
    const CancelAudioRecordSend = ref(false)

    //Captura o click no botão de gravar o áudio
    const clickAudioRecord = () => {
      showCancelAudioRecordButton.value = true
      cancelAudioRecordSend.value = false
    }

    //Captura a ação de cancelamento do envio do áudio
    const cancelAudioRecordSend = () => {
      cancelAudioRecordSend.value = true
      //Esconde o botão de cancelar o envio do áudio
      showCancelAudioRecordButton.value = false
    }

    //Função que atrasa a execução (semelhante ao sleep do PHP)
    const sleep = (delay) => new Promise((resolve) => setTimeout(resolve, delay))

    const onResult = async (data) => {
      await sleep(1500)
      //console.log('The blob data:', data)
      //console.log('Downloadable audio', window.URL.createObjectURL(data))
      //Se o envio do áudio não foi cancelado
      if(cancelAudioRecordSend.value == false) {
        file.value = data
        sendMessage()
      }
    }

    const handleFileUpload = (event) => {
      file.value = event.target.files[0]
      //console.log(file.value)
      sendMessage()
    }

    const messageReply = ref(null)
    
    //Seta a mensagem que será respondida
    const setMessageReply = (messageReplyData) => {
      messageReply.value = messageReplyData

      //document.getElementById("input-send-message").focus();
    }

    //Fecha o box de respostas
    const closeReplyBox = () => {
      messageReply.value = null
    }

    //######### LISTA DE MENSAGENS RÁPIDAS ##########

    const quickMessageItems = ref([])
    const totalQuickMessagesItems = ref(0)

    const fetchQuickMessages = (perPage, currentPage)  => {
      //Traz as mensagens rápidas cadastradas
      store.dispatch('app-chat/fetchQuickMessages', {
        perPageQuickMessage: perPage,
        currentPageQuickMessage: currentPage,
        typeQuickMessage: 1,
      })
        .then(response => {
          console.log('response.data.quickMessages')
          console.log(response.data.quickMessages)
          quickMessageItems.value = response.data.quickMessages
          totalQuickMessagesItems.value = response.data.totalQuickMessages
        });
    }

    //Remove uma mensagem rápida
    const removeQuickMessage = quickMessageId => {
      store.dispatch('app-chat/removeQuickMessage', { id: quickMessageId })
        .then(() => {
          fetchQuickMessages()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Mensagem rápida removida',
              text: 'Mensagem rápida removida com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //fetchQuickMessages()

    const blankQuickMessage = {
      content: '',
    }
    const quickMessageData = ref(JSON.parse(JSON.stringify(blankQuickMessage)))

    //Pega a mensagem rápida selecionada
    const setQuickMessage = (quickMessage) => {
      quickMessageData.value = quickMessage.content[0]
      sendMessage()
      console.log('quickMessageData')
      console.log(quickMessageData.value)
    }

    const sendMessage = () => {
      // Caso exista algum texto no input ou tenha algum arquivo importado
      if (!chatInputMessage.value && !file.value && !quickMessageData.value && !templateMessageData.value) return

      const formData = new FormData()
      formData.append('name', 'image.jpg')
      formData.append('file', file.value)
      formData.append('contactId', activeChat.value.contact.id)
      formData.append('replyApiMessageId', messageReply.value? messageReply.value.apiMessageId : null)
      formData.append('typeUserId', 1) //Operador
      formData.append('senderId', profileUserDataMinimal.value.id)
      formData.append('privateMessage', 'false') //Se for uma mensagem privada (nesse caso, sempre é falso, já que não tem como mandar mensagem privado do operador para o gestor) 
      //Se for uma mensagem rápida
      if(quickMessageData.value.content || quickMessageData.value.type_format_message_id == 2 || quickMessageData.value.type_format_message_id == 3 || 
         quickMessageData.value.type_format_message_id == 4 ) {
        formData.append('message', quickMessageData.value.content)
        formData.append('quickMessageData', JSON.stringify(quickMessageData.value))
      } //Se for uma mensagem template
      else if(templateMessageData.value.body) {
        formData.append('message', templateMessageData.value.body)
        formData.append('templateData', JSON.stringify(templateMessageData.value))
      }
      else {
        formData.append('message', chatInputMessage.value)
      }
      
      
      const config = {
          headers: {
            'content-type': 'multipart/form-data'
          }
      }
      
      //Se o usuário estiver carregando algum arquivo ou áudio (alguma mídia)
      if(file.value != null || (quickMessageData.value.parameters && quickMessageData.value.parameters.length > 0)) {
        //Carrega o loading screen
        Vue.prototype.$isLoading(true)
      }

      // Limpa o input de mensagem
      chatInputMessage.value = ''
      file.value = null
      messageReply.value = null
      quickMessageData.value.parameters = null
      showCancelAudioRecordButton.value = false

      store.dispatch('app-chat/sendMessage', formData, config)
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
            // Add message to log
            // Adiciona a mensagem no chat
            activeChat.value.chat.chat.push(newMessageData)
          }
          //Se o usuário NÃO está aplicando filtro nos chats ativos
          /*if(searchActiveChatsAppliedFilter.value == false) {
            //fetchActiveChats( {offset: offsetActiveChats.value, skip: false} )
          }*/

          quickMessageData.value.content = null
          quickMessageData.value.type_format_message_id = null
          templateMessageData.value.body = null
          messageReply.value = null

          //file.value = null
          document.getElementById("importFile").value = null;

          // Set Last Message for active contact
          const contact = chatsContacts.value.find(c => c.id === activeChat.value.contact.id)
          //Se estiver presente na lista de chats (pode não estar presente quando u usuário está pesquisando por outro contato)
          if(contact) {
            contact.chat.lastMessage = newMessageData
            //Remove o contato da lista de chats ativos
            chatsContacts.value.splice(chatsContacts.value.findIndex(c => c.id === activeChat.value.contact.id), 1)
            //Insere o contato no topo da lista de chats ativos
            chatsContacts.value.unshift(contact)
          }
          /*
          chatsContacts.value.sort(function(a,b){
            // Turn your strings into dates, and then subtract them
            // to get a value that is either negative, positive, or zero.
            if(b.chat.lastMessage && a.chat.lastMessage) {
              return new Date(b.chat.lastMessage.created_at) - new Date(a.chat.lastMessage.created_at)
            }
          })
          */

          //chatsContacts.value = dynamicSort('chat.lastMessage.created_at')

          // Scroll to bottom
          nextTick(() => { scrollToBottomInChatLog() })
        })
        .finally(() => {
          //Esconde a loading screen
          Vue.prototype.$isLoading(false) 
        })
    }

    

    //Reenvia uma mensagem
    const resendMessage = messageData => {
      //Exibe o spinner enquanto a mensagem é enviada
      activeChat.value.chat.chat.find(c => c.id === messageData.messageId).loadingSpinner = true
      store.dispatch('app-chat/resendMessage', { messageData: messageData })
        .then(response => {
          //Se a mensagem foi reenviada com sucesso
          if(response.data.resendMessageData.sendSuccess == true) {
            //Marca a mensagem como enviada dinamicamente
            activeChat.value.chat.chat.find(c => c.id === response.data.resendMessageData.messageId).sendSuccess = true
            activeChat.value.chat.chat.find(c => c.id === response.data.resendMessageData.messageId).status_message_chat_id = response.data.resendMessageData.statusMessageChatId
            /*
            toast({
              component: ToastificationContent,
              props: {
                title: 'Mensagem enviada com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
            */
          } //Se a mensagem NÃO foi enviada com sucesso e NÃO é uma mensagem template
          else if(response.data.resendMessageData.sendSuccess == false && response.data.resendMessageData.isTemplateMessage == false) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Falha ao reenviar a mensagem',
                text: 'Verifique sua conexão está conectado à internet e se o canal utilizado está conectado ao Whatsapp.',
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            })
          }
        })
        .finally(() => {
          //Esconde o spinner
          activeChat.value.chat.chat.find(c => c.id === messageData.messageId).loadingSpinner = false 
        })
        
    }

    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    // User Profile Sidebar
    // ? Will contain all details of profile user (e.g. settings, about etc.)
    const profileUserData = ref({})
    // ? Will contain id, name and avatar & status
    const profileUserDataMinimal = ref({})

    const shallShowUserProfileSidebar = ref(false)
    const showUserProfileSidebar = () => {
      store.dispatch('app-chat/getProfileUser')
        .then(response => {
          profileUserData.value = response.data.profileUser
          shallShowUserProfileSidebar.value = true
        })
    }

    // Active Chat Contact Details
    const shallShowActiveChatContactSidebar = ref(false)

    // UI + SM Devices
    // Left Sidebar Responsiveness
    const { mqShallShowLeftSidebar } = useResponsiveAppLeftSidebarVisibility()
    const startConversation = () => {
      if (store.state.app.windowWidth < $themeBreakpoints.lg) {
        mqShallShowLeftSidebar.value = true
      }
    }

    //Seta todas as mensagens não lidas como lidas
    const playNotficationNewChatSound = () => {
      var audioFile = new Audio('/notification-sounds/newChat.mp3')
      var resp = audioFile.play()

      if (resp !== undefined) {
          resp.then(_ => {
              audioFile.play()
          }).catch(error => {
            //show error
          });
      }
    }

    //Seta todas as mensagens não lidas como lidas
    const readAllUnseenMessage = chatId => {
      store.dispatch('app-chat/readAllUnseenMessage', { id: chatId })
        .then(() => {
        })
    }

    //Traz a situação do atendimento do chat ativo em relação ao operador
    const situationServiceOperator = chatId => {
      store.dispatch('app-chat/situationServiceOperator', { id: chatId })
        .then(response => {
          console.log('situation service')
          //Atualiza o status do atendimento para com o operador
          activeChat.value.chat.statusService = response.data.situationService
        })
    }

    //Usuário logado no sistema
    const userLogged = ref({})

    // mounted
    onMounted(() => {
      store.dispatch('app-chat/getUserLogged')
        .then(response => {
          //Pega o usuário logado
          userLogged.value = JSON.parse(JSON.stringify(response.data))
          
          //Escuta as mensagens enviadas pelos contatos
          Echo.private('user.'+userLogged.value.id).listen('.SendMessage', (newMessageData) => {
            console.log('mensagem chegando')
            console.log(newMessageData)
            //Se o chat estiver ativo
            if(activeChat.value.contact) {
              //Caso a mensagem tenha sido enviada pelo contato cujo chat está ativo ou tenha sido enviada pelo Gestor ou se for uma gravação de ligação
              if(activeChat.value.contact.id == newMessageData.message.senderId || 
                (activeChat.value.contact.id == newMessageData.message.contactId && newMessageData.message.type_user_id == 4) ||
                (activeChat.value.contact.id == newMessageData.message.contactId && newMessageData.message.type_message_chat_id == 9) || 
                (activeChat.value.contact.id == newMessageData.message.contactId && newMessageData.message.senderId == 3)) {
                
                //Torna as mensagens do chat como lidas
                readAllUnseenMessage(activeChat.value.chat.id)
                activeChat.value.chat.chat.push(newMessageData.message)
                // Scroll to bottom
                nextTick(() => { scrollToBottomInChatLog() })
              }
            }
            else {
              // Exibe a última mensagem enviada pelo contato
              const contact = pendingContacts.value.find(c => c.id === newMessageData.message.senderId)
              
              if(contact) {
                //Caso o contato não esteja como online
                if(contact.status != 'online') {
                  contact.status = 'online'
                }
                contact.chat.lastMessage = newMessageData.message
                //Incrementa a quantidade de mensagens não lidas
                contact.chat.cha_unseen_messages++
              }              
            }
            //fetchChatAndContacts()
            //Se o usuário NÃO está aplicando filtro nos chats ativos
            if(searchActiveChatsAppliedFilter.value == false) {
              fetchActiveChats( {offset: offsetActiveChats.value, skip: false} )
            }
            //Se o usuário NÃO está aplicando filtro nos chats pendentes
            if(searchPendingChatsAppliedFilter.value == false) {
              fetchPendingChats( {offset: offsetPendingChats.value, skip: false} )
            }
          })

          .listen('.UpdateService', (data) => {
            //console.log('atualiza serviço')
            //console.log(data)
            //console.log('dados chat ativo')
            //console.log(activeChat.value.contact)
            //Se o chat que estiver ativo é o mesmo contato que teve a situação alterada
            if(activeChat.value.contact) {
              if(activeChat.value.contact.id == data.serviceData.contactId) {
                activeChat.value.chat.statusService = data.serviceData.situationService

                //Se o usuário logado NÃO for o responsável pelo atendimento e o atendimento está ativo
                if(!(data.serviceData.responsibleUserIdService == userLogged.value.id) && data.serviceData.situationService == 'activeService') {
                  //Habilita o chat para que o usuário possa se comunicar com contato 
                  activeChat.value.chat.statusService = 'blockService'
                }
                //Se o usuário NÃO faz parte do departamento responsável pelo atendimento e o serviço está pendente 
                else if(!(data.serviceData.responsibleUserIdService == userLogged.value.id) && data.serviceData.situationService == 'pending') {
                  //Habilita o chat para que o usuário possa capturar o atendimento
                  activeChat.value.chat.statusService = 'blockService'
                }
              }
            }

            //Caso o departamento do operador ou o próprio operador seja o responsável pelo atendimento e a notificação sonora esteja habilitada para 
            //o usuário, envia uma notificação sonora
            if( (((data.serviceData.responsibleUserIdService == userLogged.value.id) && data.serviceData.situationService == 'pending') 
                || ((data.serviceData.responsibleUserIdService == userLogged.value.id) && data.serviceData.situationService == 'activeService')) 
                && userLogged.value.audio_notification_chat == 1 ) {
              playNotficationNewChatSound()
            }
          })

          //Escuta as mensagens enviadas pelos contatos
          .listen('.UpdateChat', () => {
            //console.log('UpdateChat')
            //Se o usuário NÃO está aplicando filtro nos chats ativos
            if(searchActiveChatsAppliedFilter.value == false) {
              fetchActiveChats( {offset: offsetActiveChats.value, skip: false} )
            }
            //Se o usuário NÃO está aplicando filtro nos chats pendentes
            if(searchPendingChatsAppliedFilter.value == false) {
              fetchPendingChats( {offset: offsetPendingChats.value, skip: false} )
            }
          }) //Atualiza a situação de uma determinada mensagem
          .listen('.UpdateStatusMessage', (statusMessageData) => {
            //console.log('statusMessageData')
            //console.log(statusMessageData)
            //Se a mensagem foi enfileirada, enviada ou entregue, marca o status como sucesso
            if(statusMessageData.statusId == 1 || statusMessageData.statusId == 2 || statusMessageData.statusId == 3) {
              activeChat.value.chat.chat.find(c => c.id === statusMessageData.messageId).sendSuccess = true
            }
            //Se houve algum erro ao enviar a mensagem 
            else if(statusMessageData.statusId == 4) {
              activeChat.value.chat.chat.find(c => c.id === statusMessageData.messageId).sendSuccess = false
            }
            //console.log('statusMessageData')
            //console.log(statusMessageData)
            //Atualiza o status da mensagem
            activeChat.value.chat.chat.find(c => c.id === statusMessageData.messageId).status_message_chat_id = statusMessageData.statusId
          })
        })
    })

    //###### TRANSFERÊNCIA DE ATENDIMENTO ######

    const blankTransfer = {
      department: '',
      user: '',
      chatId: '',
      serviceId: '',
      situationService: '',
    }
    const transferData = ref(JSON.parse(JSON.stringify(blankTransfer)))
    //Limpa os dados do popup
    const clearTransferData = () => {
      transferData.value = JSON.parse(JSON.stringify(blankTransfer))
    }
    const transferService = (transferData) => {
      console.log(transferData)
      store.dispatch('app-chat/transferService', { transferData: transferData })
      .then(() => {
        showSendMessageArea.value = false
        activeChat.value.chat.statusService = 'blockService'
      })
    }

    //######### NOVA MENSAGEM RÁPIDA ##########
    const blankNewQuickMessage = {
      id: null,
      title: '',
      content: '',
      buttonLabel: [],
      callActions: [],
      typeQuickMessageId: 1,
      listLabel: '',
      buttonDescription: [],
    }
    const newQuickMessageData = ref(JSON.parse(JSON.stringify(blankNewQuickMessage)))
    //Limpa os dados do popup
    const clearNewQuickMessageData = () => {
      newQuickMessageData.value = JSON.parse(JSON.stringify(blankNewQuickMessage))
    }

    //Abre o modal já preenchida para atualização da mensagem rápida
    const handleQuickMessageClick = (quickMessageData) => {
      console.log(quickMessageData)
      newQuickMessageData.value = quickMessageData
    }

    const fileQuickMessageMedia = ref(null)
    //Traz os dados do arquivo upado
    const handleFileUploadQuickMessage = (fileData) => {
      fileQuickMessageMedia.value = fileData
    }

    const addQuickMessage = messageData => {
      const formData = new FormData()
      formData.append('name', 'ivahy.jpg')
      formData.append('file', fileQuickMessageMedia.value)

      formData.append('messageData', JSON.stringify(messageData))

      const config = {
          headers: {
            'content-type': 'multipart/form-data'
          }
      }

      //Limpa a variável que armazena a mídia
      fileQuickMessageMedia.value = null

      store.dispatch('app-chat/addQuickMessage', formData, config)
        .then(() => {
          // eslint-disable-next-line no-use-before-define
        fetchQuickMessages(5, 1)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Mensagem rápida adicionada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Não foi possível salvar a mensagem rápida',
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          })
        })
    }

    const updateQuickMessage = messageData => {
      store.dispatch('app-chat/updateQuickMessage', { quickMessage: messageData })
        .then(() => {
          // eslint-disable-next-line no-use-before-define
          fetchQuickMessages()
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

    //###################### TAGS ##########################################
    const blankTag = {
      contactId: '',
      tagId: '',
    }
    const tagData = ref(JSON.parse(JSON.stringify(blankTag)))

    const updateTag = tagData => {
      store.dispatch('app-chat/updateTag', { tagData: tagData, serviceData: activeChat.value.chatContactData.service })
        .then(response => {
          // eslint-disable-next-line no-use-before-define
          // Altera a tag na tela para o valor alterado pelo operador
          activeChat.value.contact.tags = response.data.tags
          toast({
            component: ToastificationContent,
            props: {
              title: 'Tag atualizada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //#################### NEW CONTACT ##############################################
    const blankContact = {
      phoneNumber: '',
      name: '',
    }
    const newContactData = ref(JSON.parse(JSON.stringify(blankContact)))
    //Limpa os dados do popup
    const clearNewContactData = () => {
      newContactData.value = JSON.parse(JSON.stringify(blankContact))
    }

    //Altera a variável que está sendo vigiada durante a pesquisa
    const setSearchContact = (searchData) => {
        searchContacts.value = searchData
    }

    const addContact = contactData => {
      store.dispatch('app-chat/addContact', contactData)
        .then(response => {
          // eslint-disable-next-line no-use-before-define
          //fetchChatAndContacts()
          fetchContacts()
          toast({
            component: ToastificationContent,
            props: {
              title: response.data.message? response.data.message : 'Contato adicionado com sucesso!',
              icon: 'CheckIcon',
              variant: response.data.message? 'danger' : 'success',
            },
          })
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Não foi possível salvar o contato',
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          })
        })
    }

    //#################################### USER ############################################
    //Seta todas as mensagens não lidas como lidas
    const setUserSituation = situationId => {
      store.dispatch('app-chat/updateUserSituation', { 
        userId: userdata.id, 
        situationId: situationId, 
      })
        .then(response => {
          //Atualiza o status do avatar que fica ao lado da barra de pesquisa
          profileUserDataMinimal.value.situation_user_id = response.data.situation_user_id
          toast({
            component: ToastificationContent,
            props: {
              title: 'Status atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const loadingMessages = ref(false)

    //Traz as mensagens do usuário de acordo com a rolagem do chat para cima
    const fetchMessagesChat = offset => {
      //Exibi o spinner
      loadingMessages.value = true
      store.dispatch('app-chat/fetchMessagesChat', { chatId: activeChat.value.chat.id, offset: offset } )
        .then(response => {
          //Esconde o spinner
          loadingMessages.value = false
          //Se existem atendimentos para ser exibidos
          if(response.data.length > 0) {
              if(offset > 0) {
                //Insere cada novo atendimento carregado no array de serviços
                response.data.map(function(message, key) {
                  //Adiciona o atributo loadingSpinner como falso. Utilizado no momento do reenvio de alguma mensagem cujo envio falhou
                  message.loadingSpinner = false
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

    //Coloca o contato na blacklist de campanha
    const addContactBlacklistCampaign = (contactId, campaignId) => {
      Swal.fire({
        title: 'Blacklist',
        text: "Você realmente quer adicionar esse contato na blacklist de campanha?",
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
          //Coloca o contato na blacklist de campanha
          store.dispatch('app-chat/addContactBlacklistCampaign', { 
            contactId: contactId, 
            campaignId: campaignId, 
          })
            .then(response => {
              toast({
                component: ToastificationContent,
                props: {
                  title: 'Contato adicionado a blacklist com sucesso!',
                  icon: 'CheckIcon',
                  variant: 'success',
                },
              })
            })
        }
      })
    }


    //################################# TEMPLATE MESSAGES #######################################
    const newTemplateMessage = {
      tem_name: '',
      category: '',
      language: '',
      parameters: [],
      callActions: [],
      typeButton: '',
      templateButtons: [],
      buttonLabel: [],
      footer: null,
      typeHeader: '',
      header: null,
      mediaHeader: null,
      variablesTags: [],
      body: '',
      channel: '',
    }
    
    const newTemplateMessageData = ref(JSON.parse(JSON.stringify(newTemplateMessage)))
    
    const templateMessage = {
      template: [],
    }

    const templateMessageData = ref(JSON.parse(JSON.stringify(templateMessage)))
    
    const clearTemplateData = () => {
      newTemplateMessageData.value = JSON.parse(JSON.stringify(newTemplateMessage))
    }

    //Abre o modal já preenchida para atualização da mensagem rápida
    const handleTemplateMessageClick = (templateData) => {
      console.log('templateData')
      console.log(templateData)
      newTemplateMessageData.value = templateData
    }

    //Pega a mensagem rápida selecionada
    const setTemplateMessage = (templateData) => {
      templateMessageData.value = templateData.template[0]
      console.log('dados envio template')
      console.log(templateData)
      sendMessage()
    }

    const errorTemplateName = ref(false)

    //Verifica se o nome digitado para o template já está sendo usado por outro template ativo
    const checkTemplateNameExist = templateName => {
      //Se foi digitado entre 12 e 14 caracteres e não seja o próprio número atual do canal
      if(templateName.length >= 3) {

        console.log('template name');
        console.log(templateName);
        
        store.dispatch('app-chat/checkTemplateNameExist', { templateName: templateName })
        .then(response => {
          console.log(response.data.error)
          //Habilita o erro ou não
          errorTemplateName.value = response.data.error
        })
        .catch(error => {
        })
      }
    }

    const fileTemplateMedia = ref(null)
    //Traz os dados do arquivo upado
    const handleFileUploadTemplate = (fileData) => {
      fileTemplateMedia.value = fileData
    }

    const addTemplateMessage = messageData => {
      const formData = new FormData()
      formData.append('name', 'ivahy.jpg')
      formData.append('file', fileTemplateMedia.value)

      formData.append('messageData', JSON.stringify(messageData))

      const config = {
          headers: {
            'content-type': 'multipart/form-data'
          }
      }

      //Limpa a variável que armazena a mídia
      fileTemplateMedia.value = null
      //Chama o componente de loading screen
      Vue.prototype.$isLoading(true)
      store.dispatch('app-chat/addTemplateMessage', formData, config)
        .then(response => {
          //Caso não tenha ocorrido nenhum erro ao criar o template
          if(response.data.error == false) {
            fetchTemplates('', '', 10, 1)
            toast({
              component: ToastificationContent,
              props: {
                text: response.data.message,
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }
          else {
            fetchTemplates('', '', 10, 1)
            toast({
              component: ToastificationContent,
              props: {
                text: response.data.message,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            },
            {
              timeout: 8000,
            })
          }
          
          
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Não foi possível criar o template',
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

    const removeTemplate = ( {id: id, templateName: templateName} ) => {
      //Chama o componente de loading screen
      Vue.prototype.$isLoading(true)
      store.dispatch('app-chat/removeTemplate', 
      { 
        id: id,
        templateName: templateName,
        channel_id: activeChat.value.chatContactData.service.channel_id
      })
        .then(response => {
          //Se não houve erro ao enviar a mensagem
          if(response.data.error == false) {
            fetchTemplates('', '', 10, 1)
            toast({
              component: ToastificationContent,
              props: {
                title: 'Template removido com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }
          else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Não foi possível remover o template',
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            })
          }
        })
        .finally(() => {
          //Esconde a loading screen
          Vue.prototype.$isLoading(false) 
        })
    }

    const templateItems = ref([])
    const totalTemplateItems = ref(0)

    //Traz os templates cadastrados de acordo com o filtro aplicado
    const fetchTemplates = (category, status, perPage, currentPage, updateTemplates)  => {
      //Traz as mensagens rápidas cadastradas
      store.dispatch('app-chat/fetchTemplates', { 
        category: category,
        status: status,
        perPage: perPage,
        page: currentPage,
        updateTemplate: updateTemplates,
        channelId: activeChat.value.chatContactData.channel_id,
        })
        .then(response => {
          templateItems.value = response.data.templates
          totalTemplateItems.value = response.data.totalTemplates
        })
    }


    //########################## TEXT CHAT ################################
    const enlivenChat = boolean => {
      blurChat.value = boolean
    }



    //########################## CONTACTS #################################

    //Função que bloqueia um usuário
    const blockContact = contactId => {
      Swal.fire({
        title: 'Bloqueio de Contato',
        text: "Você tem certeza que deseja bloquear esse contato?",
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
          //Bloqueia o contato
          store.dispatch('app-chat/blockContact', { contactId: contactId })
          .then(response => {
            // Coloca o contato como bloqueado na tela
            activeChat.value.contact.blocked = true
            toast({
              component: ToastificationContent,
              props: {
                title: 'Contato bloqueado com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          })
        }
      })
    }

    //Desbloquear um usuário
    const unlockContact = contactId => {
      Swal.fire({
        title: 'Desbloqueio de Contato',
        text: "Você tem certeza que deseja desbloquear esse contato?",
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
          //Bloqueia o contato
          store.dispatch('app-chat/unlockContact', { contactId: contactId })
          .then(response => {
            //Coloca o contato como desbloquado na tela
            activeChat.value.contact.blocked = null

            toast({
              component: ToastificationContent,
              props: {
                title: 'Contato desbloqueado com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          })
        }
      })
    }

    const {
      fetchContacts,
      searchContacts,
      contactsSearch,
    } = useContactsList()

    //Carrega os X últimos contatos adicionados ao banco de dados
    fetchContacts()


    //Atualiza os dados do contato na tela
    const setContact = contactData => {
      activeChat.value.contact = contactData
      //Atualiza o nome do contato na barra laterial esquerda (de chats)
      chatsContacts.value.find(c => c.id === contactData.id).con_name = contactData.con_name
    }

    //Atualiza os dados de endereço do contato na tela
    const setAddressContact = addressData => {
      console.log('setAddressContact addressData')
      console.log(addressData)
      activeChat.value.contact.addresses = addressData
    }

    const blankContactData = {
      cep: '',
      street: '',
      number: '',
      address_complement: '',
      district: '',
      city: '',
      state: '',
      country: '',
    }

    const clearContactData = ref(JSON.parse(JSON.stringify(blankContactData)))


    //############################## CHAT OBSERVATIONS #####################################
    const chatObservations = ref([])
    //Mostra ou não o botão exibir mais atendimentos
    const hiddenButtonChatObservations = ref(false)

    //Traz os atendimentos associados a um chat de um contato de pouco em pouco, de acordo com o clique do usuário
    const fetchChatObservations = ({chatId: chatId, offset: offset}) => {
      store.dispatch('app-chat/fetchChatObservations', { chatId: chatId, offset: offset } )
        .then(response => {
          //Se existem atendimentos para ser exibidos
          if(response.data.chatObservations.length > 0) {
            if(offset == 0) {
              chatObservations.value = response.data.chatObservations
            }
            else {
              //Insere cada novo atendimento carregado no array de serviços
              response.data.chatObservations.map(function(service, key) {
                chatObservations.value.push(service)
              });
            }
          }
          else {
            //Esconde o botão que carrega mais atendimentos
            hiddenButtonChatObservations.value = true
          }
        })
    }

    const addChatObservation = observation => {
      
      //Limpa a variável que armazena a mídia
      fileQuickMessageMedia.value = null

      store.dispatch('app-chat/addChatObservation', {
          observation: observation,
          chatId: activeChat.value.chat.id
        })
        .then(() => {
        fetchChatObservations({chatId: activeChat.value.chat.id, offset: 0} )
        toast({
            component: ToastificationContent,
            props: {
              title: 'Observação adicionada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Não foi possível salvar a observação',
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          })
        })
    }

    
    const removeChatObservation = observationId => {
      Swal.fire({
        title: 'Remoção de Observação',
        text: "Você realmente quer remover essa observação?",
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
          store.dispatch('app-chat/removeChatObservation', { id: observationId })
          .then(() => {
            fetchChatObservations({chatId: activeChat.value.chat.id, offset: 0} )
            toast({
              component: ToastificationContent,
              props: {
                title: 'Observação Removida',
                text: 'Observação removida com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          })
        }
      })
    }

    const callPhone = (phoneNumber, extensionNumber, extensionId) => {
      store.dispatch('app-chat/callPhone', { phoneNumber: phoneNumber, extensionNumber: extensionNumber, extensionId: extensionId })
        .then(response => {
          
        })
    }

    //Função usada para arrastar e enviar arquivos
    const dropFile = (event) => {
      file.value = event.dataTransfer.files[0]
      
      sendMessage()
    }

    //Envia um print
    const handleFilePaste = (event) => {
      //console.log('event.clipboardData')
      //console.log(event.clipboardData)
      if(event.clipboardData.files.length > 0) {
        file.value = event.clipboardData.files[0]
        //Se for uma imagem
        if(file.value.type == 'image/png') {
          sendMessage()
        }
      }
    }
    
    return {
      handleFilePaste,
      // Filters
      // formatDate,

      // useChat
      resolveAvatarBadgeVariant,

      // Chat & Contacts
      chatsContacts,
      pendingContacts,
      contacts,
      showSendMessageArea,

      // Single Chat
      refChatLogPS,
      activeChat,
      chatInputMessage,
      file,
      openChatOfContact,
      sendMessage,
      resendMessage,
      handleFileUpload,
      onResult,
      startService,
      closeService,
      confirmText,
      fetchMessagesChat,
      hiddenButtonMoreMessage,
      loadingMessages,
      situationServiceOperator,

      callPhone,

      //User logged
      userLogged,

      // Profile User Minimal Data
      profileUserDataMinimal,

      // User Profile Sidebar
      profileUserData,
      shallShowUserProfileSidebar,
      showUserProfileSidebar,

      // Active Chat Contact Details
      shallShowActiveChatContactSidebar,

      // UI
      perfectScrollbarSettings,

      // UI + SM Devices
      startConversation,
      mqShallShowLeftSidebar,

      //Calendar
      refCalendar,
      isCalendarOverlaySidebarActive,
      event,
      clearEventData,
      addEvent,
      updateEvent,
      removeEvent,
      fetchEvents,
      refetchEvents,
      calendarOptions,

      //Transfer
      clearTransferData,
      transferService,
      transferData,

      //Quick Message List
      quickMessageData,
      setQuickMessage,

      //New Quick Message
      newQuickMessageData,
      clearNewQuickMessageData,
      addQuickMessage,
      handleFileUploadQuickMessage,
      fetchQuickMessages,
      quickMessageItems,
      updateQuickMessage,
      removeQuickMessage,
      handleQuickMessageClick,

      //Tag
      updateTag,
      tagData,

      //New Contact
      newContactData,
      clearNewContactData,
      addContact,
      addContactBlacklistCampaign,

      //User
      setUserSituation,

      //Template message
      addTemplateMessage,
      handleTemplateMessageClick,
      newTemplateMessageData,
      fetchTemplates,
      templateItems,
      totalTemplateItems,
      totalQuickMessagesItems,
      setTemplateMessage,
      templateMessageData,
      removeTemplate,
      clearTemplateData,
      checkTemplateNameExist,
      errorTemplateName,
      handleFileUploadTemplate,

      fetchActiveChats,
      hiddenButtonActiveChats,
      searchActiveChats,
      setSearchChat,
      fetchPendingChats,
      searchPendingChats,
      hiddenButtonPendingChats,

      // Search Query
      searchContacts,
      contactsSearch,
      setSearchContact,

      blockContact,
      unlockContact,

      setContact,
      setAddressContact,
      clearContactData,

      urlBaseStorage,

      userExtension,

      fetchChatObservations,
      addChatObservation,
      removeChatObservation,
      chatObservations,
      hiddenButtonChatObservations,

      blurChat,
      enlivenChat,

      messageReply,
      setMessageReply,
      closeReplyBox,

      dropFile,

      cancelAudioRecordSend,
      clickAudioRecord,
      showCancelAudioRecordButton,
    }
  },
}
</script>

<style lang="scss" scoped>
  .audio-recorder {
    height: 45px; 
    width: 45px; 
    background-color: #7367f0;
  }
  .fab-wrapper {
    position: absolute !important;
    right: 10px !important;
    bottom: -23px !important;
  }
  .padding-bottom-2 {
    padding-bottom: 2px;
  }
  .input-group-text {
    white-space: normal !important;
  }
</style>

<style>
@import "~@core/css/float-actions-button/animate.min.css";
@import "~@core/css/float-actions-button/material-icons.css";
</style>

<style lang="scss">
@import "~@core/scss/base/pages/app-chat.scss";
@import "~@core/scss/base/pages/app-chat-list.scss";
@import '@core/scss/vue/libs/vue-select.scss';



#btn-emoji-default {
  height: auto !important;
  width: 25px !important;
  margin: 0 !important;
}
#btn-emoji-default > div > img.emoji {
  width: 17px !important;
  height: 17px !important;
}
.input-group-text {
    white-space: normal !important;
  }

.fab-main {
  padding: 22px !important;
}

//Esconde o scroll do input de texto
#input-send-message::-webkit-scrollbar {
  display: none; //Para Chrome
}

//Esconde o scroll do input de texto
#input-send-message {
    -ms-overflow-style: none; /* for Internet Explorer, Edge */
    scrollbar-width: none; /* for Firefox */
    overflow-y: scroll; 
}

.unselectable {
    -webkit-user-select: none;
    -webkit-touch-callout: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    color: #cc0000;
  }
</style>
