<template>
  <component
    :is="tag"
    v-on="$listeners"
  >
    <b-avatar
      size="42"
      :src="user.con_avatar? baseUrlStorage+user.con_avatar : null"
      :badge="isChatContact"
      class="badge-minimal"
      :style="user.service? 'margin-top: 30px;' : 'margin-top: 25px;'"
      :badge-variant="resolveAvatarBadgeVariant(user.status)"
      :variant="user.avatar != null ? 'transparent' : 'light-'+user.avatarColor"
      :text="user.initialsName != null ? user.initialsName : 'CL'"
    />
    <div class="chat-info flex-grow-1">
      <span style="margin-left: -50px; margin-bottom: 60px">
        <span
          v-if="isChatContact && user.service"
        >
          <b-badge
            pill
            variant="primary"
            v-if="user.cha_status == 'A'"
          >
            {{ user.cha_name}}
          </b-badge>
          <b-badge
            pill
            variant="danger"
            v-else
            v-b-tooltip.hover.v-secondary
            title="Canal desconectado"
          >
            {{ user.cha_name}}
            <feather-icon icon="AlertTriangleIcon" />
          </b-badge>
          <b-badge
            pill
            variant="light-danger"
            v-if="user.cam_name"
            :title="user.cam_name"
            v-b-popover.hover.top="user.cam_description"
          >
            C
          </b-badge>
          
          <b-badge
            pill
            variant="success"
            v-if="user.ser_new_service"
            :title="'Novo Atendimento'"
          >
            Novo
          </b-badge>
        </span>
        
      </span>
      <h5 
        class="mb-0"
        style="margin-top: 4px"
        v-b-tooltip.hover.v-secondary
        :title="user.con_name.length > 30? user.con_name: ''"
      >
        {{ user.con_name.length > 30? user.con_name.substring(0,30)+'...' : user.con_name }}
      </h5>
      <!-- Última mensagem que aparece ao lado da foto do contato -->
      <p 
        class="card-text text-truncate" 
        style="margin-bottom: 0rem !important"
        v-if="isChatContact && user.chat.lastMessage">
        <span v-if="user.chat.lastMessage.type_message_chat_id === 1">
          {{user.chat.lastMessage.mes_message}}
        </span>
        <span v-else-if="user.chat.lastMessage.type_message_chat_id === 2">
          <feather-icon icon="MusicIcon" /> {{ $t('chat.chatContact.audio') }}
        </span>
        <span v-else-if="user.chat.lastMessage.type_message_chat_id === 3 || user.chat.lastMessage.type_message_chat_id === 7">
          <feather-icon icon="ImageIcon" /> {{ $t('chat.chatContact.image') }}
        </span>
        <span v-else-if="user.chat.lastMessage.type_message_chat_id === 4">
          <feather-icon icon="VideoIcon" /> {{ $t('chat.chatContact.movie') }}
        </span>
        <span v-else-if="user.chat.lastMessage.type_message_chat_id === 5">
          <feather-icon icon="FileIcon" /> {{user.chat.lastMessage.mes_media_original_name}}
        </span>
        <span v-else-if="user.chat.lastMessage.type_message_chat_id === 6">
          <feather-icon icon="UserIcon" /> {{ $t('chat.chatContact.contact') }}
        </span>
        <span v-else-if="user.chat.lastMessage.type_message_chat_id === 8">
          <feather-icon icon="MapPinIcon" /> {{ $t('chat.chatContact.location') }}
        </span>
        <span v-else-if="user.chat.lastMessage.type_message_chat_id === 9">
          <feather-icon icon="PhoneOutgoingIcon" /> {{ $t('chat.chatContact.call') }}
        </span>
      </p>
      <p class="card-text text-truncate" v-else>
        {{ user.con_phone | VMask(' +## (##) #####-####') }}
      </p>
      <small 
        v-if="isChatContact && user.service"
        class="text-truncate card-text"
        style="font-weight: bolder"
      >
        {{ $t('chat.chatContact.protocol') }} Nº: {{user.service.ser_protocol_number}}
      </small>
    </div>
    <div
      v-if="isChatContact"
      class="chat-meta text-nowrap"
      style="margin-top: 5px"
    >
      <small class="float-right mb-25 chat-time" v-if="user.chat.lastMessage">{{ formatDateToMonthShort(user.chat.lastMessage.created_at, { hour: 'numeric', minute: 'numeric' }) }}</small>
      <b-badge
        v-if="user.chat.cha_unseen_messages && !isActiveChat"
        pill
        variant="primary"
        class="float-right"
      >
        {{ user.chat.cha_unseen_messages }}
      </b-badge>
    </div>
  </component>
</template>

<script>
import { BAvatar, BBadge, VBPopover, VBTooltip, } from 'bootstrap-vue'
import { formatDateToMonthShort } from '@core/utils/filter'
import useChat from './useChat'

export default {
  components: {
    BAvatar,
    BBadge,
  },
  directives: {
    'b-tooltip': VBTooltip,
    'b-popover': VBPopover,
  },
  props: {
    tag: {
      type: String,
      default: 'div',
    },
    user: {
      type: Object,
      required: true,
    },
    isChatContact: {
      type: Boolean,
      default: false,
    },
    isActiveChat: {
      type: Boolean,
      default: false,
    },
    baseUrlStorage: {
      type: String,
      required: true,
    },
  },
  setup() {
    const { resolveAvatarBadgeVariant } = useChat()
    return { formatDateToMonthShort, resolveAvatarBadgeVariant }
  },
}
</script>

<style>

</style>
