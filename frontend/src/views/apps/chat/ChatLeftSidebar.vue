<template>
  <div class="sidebar-left">
    <div class="sidebar">

      <!-- Logged In User Profile Sidebar -->
      <user-profile-sidebar
        :shall-show-user-profile-sidebar="shallShowUserProfileSidebar"
        :profile-user-data="profileUserData"
        @close-sidebar="$emit('update:shall-show-user-profile-sidebar', false)"
        @set-user-situation="setUserSituation"
      />

      <!-- Sidebar Content -->
      <div
        class="sidebar-content"
        :class="{'show': mqShallShowLeftSidebar}"
      >

        <!-- Sidebar close icon -->
        <span class="sidebar-close-icon">
          <feather-icon
            icon="XIcon"
            size="16"
            @click="$emit('update:mq-shall-show-left-sidebar', false)"
          />
        </span>

        <!-- Header -->
        <div class="chat-fixed-search">
          <div class="d-flex align-items-center w-100">
            <div class="sidebar-profile-toggle">
              <b-avatar
                size="42"
                class="cursor-pointer badge-minimal avatar-border-2"
                :src="'../../../../'+profileUserMinimalData.avatar"
                :variant="profileUserMinimalData.avatar == null ? 'light-primary' : 'transparent'"
                :text="avatarText(profileUserMinimalData.name)"
                badge
                :badge-variant="profileUserMinimalData.situation_user_id == 1 ? 'success' : 'danger'"
                @click.native="$emit('show-user-profile')"
              />
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-between mt-1">
          <h6 class="section-label ml-1">
            {{ $t('chat.chatLeftSidebar.contacts') }}
          </h6>
          <!-- Se não for um bloco final -->
          <feather-icon icon="PlusIcon" 
            size="17"
            class="cursor-pointer d-sm-block d-none mr-1"
            @click="openModal"
            v-b-tooltip.hover.v-secondary
            :title="$t('chat.chatLeftSidebar.addNewContact')"
          />
        </div>
        <!-- ScrollArea: Chat & Contacts -->
        <vue-perfect-scrollbar
          :settings="perfectScrollbarSettings"
          class="chat-user-list-wrapper list-group scroll-area p-1"
        >
          <app-collapse
            type="shadow"
          >
            <!-- CHATS ATIVOS -->
            <b-card
              no-body
              :class="{'open': true}"
              @mouseenter="collapseOpen(true, 1)"
              @mouseleave="collapseClose"
            >
              <b-card-header
                :class="{'collapsed': !visibleChatActive}"
                :aria-expanded="visibleChatActive ? 'true' : 'false'"
                :aria-controls="collapseItemID"
                role="tab"
                data-toggle="collapse"
                @click="updateVisible(!visibleChatActive, 1)"
              >
                <slot name="header">
                  <span class="lead collapse-title chat-list-title text-success">
                    {{ $t('chat.chatLeftSidebar.chatActive') }}
                    <b-badge
                      pill
                      variant="light-success"
                    >
                      {{chatsContacts.totalActiveChats}}
                    </b-badge>
                  </span>
                </slot>
              </b-card-header>
                <b-collapse
                  :id="collapseItemID"
                  v-model="visibleChatActive"
                  :accordion="accordion"
                  role="tabpanel"
                >
                  <!-- Search -->
                  <b-input-group 
                    class="input-group-merge ml-1 round mr-1 mb-1"
                    style="width: 92%;"
                  >
                    <b-input-group-prepend is-text>
                      <feather-icon
                        icon="SearchIcon"
                        class="text-muted"
                      />
                    </b-input-group-prepend>
                    <b-form-input
                      v-model="searchActiveChatsData"
                      :placeholder="$t('chat.chatLeftSidebar.search')+'...'"
                      @keyup="$emit('set-search-chat',{ searchData: searchActiveChatsData, typeSearch: 1 } )"
                    />
                  </b-input-group>
                  <!-- Chats Title -->
                  <div 
                    v-if="chatsContacts.length"
                    class="scroll-chat"
                  >
                    <!-- Chats -->
                    <ul 
                      class="chat-users-list chat-list media-list" 
                      style="text-align: left;"
                    >
                      <chat-contact
                        v-for="(contact, index) in chatsContacts"
                        :key="index"
                        :user="contact"
                        :is-active-chat="activeChatContactId === contact.id ? true : false"
                        :base-url-storage="baseUrlStorage"
                        tag="li"
                        :class="{'active': activeChatContactId === contact.id}"
                        is-chat-contact
                        @click="$emit('open-chat', {contactData: contact, blurChatData: false})"
                      />
                    </ul>
                    <b-button
                      variant="outline-primary"
                      class="btn-icon rounded-circle mb-1 mt-1"
                      @click="$emit('fetch-active-chats', {offset: offsetActiveChats, skip: true} ); sumOffset(1);"
                      :hidden="hiddenButtonActiveChats"
                      v-b-tooltip.hover.v-secondary
                      :title="$t('contacts.contactViewUserTimelineCard.showMore')"            
                    >
                      <feather-icon 
                        icon="PlusIcon" 
                        size="20"
                      />
                    </b-button>
                  </div>
                  <div 
                    v-else
                    class="text-center mb-1"
                  >
                    <b-badge
                      pill
                      variant="light-secondary"
                    >
                      {{ $t('chat.chatLeftSidebar.noActiveChat') }}
                    </b-badge>
                  </div>
                </b-collapse>
            </b-card>

            <!-- CHATS PENDENTES -->
            <b-card
              no-body
              :class="{'open': true}"
              @mouseenter="collapseOpen"
              @mouseleave="collapseClose"
            >
              <b-card-header
                :class="{'collapsed': !visibleChatPending}"
                :aria-expanded="visibleChatPending ? 'true' : 'false'"
                :aria-controls="collapseItemID"
                role="tab"
                data-toggle="collapse"
                @click="updateVisible(!visibleChatPending, 2)"
              >
                <slot name="header">
                  <span 
                    class="lead collapse-title chat-list-title text-warning">
                    {{ $t('chat.chatLeftSidebar.pendingChats') }}
                    <b-badge
                      pill
                      variant="light-warning"
                    >
                      {{pendingContacts.totalPendingChats}}
                    </b-badge>
                  </span>
                </slot>
              </b-card-header>
              <b-collapse
                :id="collapseItemID"
                v-model="visibleChatPending"
                :accordion="accordion"
                role="tabpanel"
              >
                <!-- Search -->
                <b-input-group 
                  class="input-group-merge ml-1 round mr-1 mb-1"
                  style="width: 92%;"
                >
                  <b-input-group-prepend is-text>
                    <feather-icon
                      icon="SearchIcon"
                      class="text-muted"
                    />
                  </b-input-group-prepend>
                  <b-form-input
                    v-model="searchPendingChatsData"
                    :placeholder="$t('chat.chatLeftSidebar.search')+'...'"
                    @keyup="$emit('set-search-chat',{ searchData: searchPendingChatsData, typeSearch: 2 } )"
                  />
                </b-input-group>
                <div 
                  v-if="pendingContacts.length"
                  class="scroll-chat"
                >
                  <!-- Chats -->
                  <ul 
                    class="chat-users-list chat-list media-list"
                    style="text-align: left;"  
                  >
                    <chat-contact
                      v-for="contact in pendingContacts"
                      :key="contact.id"
                      :user="contact"
                      :base-url-storage="baseUrlStorage"
                      tag="li"
                      :class="{'active': activeChatContactId === contact.id}"
                      is-chat-contact
                      @click="$emit('open-chat', {contactData: contact, blurChatData: false})"
                    />
                  </ul>
                  <b-button
                    variant="outline-primary"
                    class="btn-icon rounded-circle mb-1 mt-1"
                    @click="$emit('fetch-pending-chats', {offset: offsetPendingChats, skip: true} ); sumOffset(2);"
                    :hidden="hiddenButtonPendingChats"
                    v-b-tooltip.hover.v-secondary
                    :title="$t('contacts.contactViewUserTimelineCard.showMore')"            
                  >
                    <feather-icon 
                      icon="PlusIcon" 
                      size="20"
                    />
                  </b-button>
                </div>
                <div 
                  v-else
                  class="text-center mb-1"
                >
                  <b-badge
                    pill
                    variant="light-secondary"
                  >
                    {{ $t('chat.chatLeftSidebar.noPendingChat') }}
                  </b-badge>
                </div>
              </b-collapse>
            </b-card>

            <!-- CONTATOS -->
            <b-card
              no-body
              :class="{'open': true}"
              @mouseenter="collapseOpen"
              @mouseleave="collapseClose"
            >
              <b-card-header
                :class="{'collapsed': !visibleContacts}"
                :aria-expanded="visibleContacts ? 'true' : 'false'"
                :aria-controls="collapseItemID"
                role="tab"
                data-toggle="collapse"
                @click="updateVisible(!visibleContacts, 3)"
              >
                <slot name="header">
                  <span class="lead collapse-title chat-list-title text-black">{{ $t('chat.chatLeftSidebar.contacts') }}</span>
                </slot>
              </b-card-header>
              <b-collapse
                :id="collapseItemID"
                v-model="visibleContacts"
                :accordion="accordion"
                role="tabpanel"
              >
                <!-- Search -->
                <b-input-group 
                  class="input-group-merge ml-1 round mr-1 mb-1"
                  style="width: 92%;"
                >
                  <b-input-group-prepend is-text>
                    <feather-icon
                      icon="SearchIcon"
                      class="text-muted"
                    />
                  </b-input-group-prepend>
                  <b-form-input
                    v-model="searchContact"
                    :placeholder="$t('chat.chatLeftSidebar.search')+'...'"
                    @keyup="$emit('set-search-contact', searchContact)"
                  />
                </b-input-group>
                <div 
                  v-if="contacts.length"
                  class="scroll-chat"
                >
                  <!-- Contacts -->
                  <ul 
                    class="chat-users-list contact-list media-list pb-3"
                    style="text-align: left;"
                  >
                    <chat-contact
                      v-for="contact in contacts"
                      :key="contact.id"
                      :user="contact"
                      :base-url-storage="baseUrlStorage"
                      tag="li"
                      style="margin-top: -30px"
                      @click="$emit('open-chat', {contactData: contact, blurChatData: true})"
                    />
                  </ul>
                </div>
              </b-collapse>
            </b-card>
          </app-collapse>
        </vue-perfect-scrollbar>
      </div>
    </div>
  </div>
</template>

<script>
import {
  BAvatar, BInputGroup, BInputGroupPrepend, BFormInput, BBadge, VBTooltip, BCollapse, BCard, BCardBody, BCardHeader, BButton,
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import { ref, computed } from '@vue/composition-api'
import { avatarText } from '@core/utils/filter'
import ChatContact from './ChatContact.vue'
import UserProfileSidebar from './UserProfileSidebar.vue'
import useContactsList from './useContactsList'
import AppCollapse from '@core/components/app-collapse/AppCollapse.vue'
import AppCollapseItem from '@core/components/app-collapse/AppCollapseItem.vue'
import { v4 as uuidv4 } from 'uuid'

export default {
  components: {

    // BSV
    BAvatar,
    BInputGroup,
    BInputGroupPrepend,
    BFormInput,
    BBadge,
    BCollapse,
    BCard,
    BCardBody,
    BCardHeader,
    BButton,

    // 3rd party
    VuePerfectScrollbar,

    // SFC
    ChatContact,
    UserProfileSidebar,

    AppCollapse,
    AppCollapseItem,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  props: {
    chatsContacts: {
      type: Array,
      required: true,
    },
    pendingContacts: {
      type: Array,
      required: true,
    },
    contacts: {
      type: Array,
      required: true,
    },
    shallShowUserProfileSidebar: {
      type: Boolean,
      required: true,
    },
    profileUserData: {
      type: Object,
      required: true,
    },
    profileUserMinimalData: {
      type: Object,
      required: true,
    },
    activeChatContactId: {
      type: Number,
      default: null,
    },
    mqShallShowLeftSidebar: {
      type: Boolean,
      required: true,
    },
    hiddenButtonActiveChats: {
      type: Boolean,
      required: true,
    },
    searchActiveChats: {
      type: String,
      required: true,
    },
    hiddenButtonPendingChats: {
      type: Boolean,
      required: true,
    },
    searchPendingChats: {
      type: String,
      required: true,
    },
    baseUrlStorage: {
      type: String,
      required: true,
    },
  },
  data: () => {
    return {
      visibleChatActive: true,
      visibleChatPending: true,
      visibleContacts: true,
      offsetActiveChats: 1,
      offsetPendingChats: 1,
      searchActiveChatsData: '',
      searchPendingChatsData: '',
      searchContact: '',
    }
  },
  computed: {
    accordion() {
      return this.$parent.accordion ? `accordion-${this.$parent.collapseID}` : null
    },
  },
  methods: {
    //Método para quando um mensagem rápida é selecionada
    openModal() {
      //Abre o modal para cadastro de mensagem rápida
      this.$emit('open-modal', 'modal-new-contact')  
    },
    setUserSituation(situationId) {
      //Abre o modal para cadastro de mensagem rápida
      this.$emit('set-user-situation', situationId)  
    },
    updateVisible(val = true, typeChat) {
      if(typeChat == 1) {
        this.visibleChatActive = val
      }
      else if(typeChat == 2) {
        this.visibleChatPending = val
      }
      else if(typeChat == 3) {
        this.visibleContacts= val
      }
      
      this.$emit('visible', val)
    },
    collapseOpen(val = true, typeChat) {
      if (this.openOnHover) this.updateVisible(true, typeChat)
    },
    collapseClose(val = true, typeChat) {
      if (this.openOnHover) this.updateVisible(false, typeChat)
    },
    sumOffset(typeService) {
      //Se for chats ativos
      if(typeService == 1) {
        this.offsetActiveChats += 1
      }
      //Se for chats pendentes
      else if(typeService == 2) {
        this.offsetPendingChats += 1
      }
    },
  },
  created() {
    this.collapseItemID = uuidv4()
    this.visible = this.isVisible
  },
  setup(props) {
    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    const resolveChatContact = userId => props.contacts.find(contact => contact.id === userId)


    //const searchFilterFunction = contact => contact.fullName.toLowerCase().includes(searchQuery.value.toLowerCase())
    //const filteredChatsContacts = computed(() => props.chatsContacts.filter(searchFilterFunction))
    //const filteredPendingContacts = computed(() => props.pendingContacts.filter(searchFilterFunction))  

    /*const {
      fetchContacts,
      searchQuery,
      contactsSearch,
    } = useContactsList()
    */
    //fetchContacts()

    return {
      // Search Query
      //searchQuery,
      //contactsSearch,

      // UI
      resolveChatContact,
      perfectScrollbarSettings,
      avatarText,
    }
  },
}
</script>
<style>
  .scroll-chat {
    max-height: 500px; 
    min-height: 180px; 
    overflow-y: scroll;
    text-align: center;
  }
  span.lead.collapse-title {
    font-size: 18px;
  }
  span.lead.collapse-title.chat-list-title {
    margin: 0.5rem 0.5rem 0.2rem;
  }

  /* Altera a largura da área de contatos */
  .chat-application .sidebar-content .chat-user-list-wrapper {
    width: 400px !important;
  }
  [dir=ltr] .chat-application .sidebar-content {
    width: 405px;
  }

  /* Alterações no scrollbar */
  ::-webkit-scrollbar {
    /*height: 12px;*/
    width: 5px;
    background: #ededed;
  }
  ::-webkit-scrollbar-thumb {
    background: #d3d3d3;
    
  }
  ::-webkit-scrollbar-corner {
      background: #d3d3d3;
  }

</style>
