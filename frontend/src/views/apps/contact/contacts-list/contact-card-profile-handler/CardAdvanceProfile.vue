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
    class="d-flex align-items-center flex-sm-row-reverse"
    :style="skin == 'light'? 'background-color: white' : ''"
  >
    <template>
      <b-dropdown
        variant="link"
        no-caret
        :right="$store.state.appConfig.isRTL"
        dropleft
      >

        <template #button-content>
          <feather-icon
            icon="MoreVerticalIcon"
            size="16"
            class="align-middle text-body"
          />
        </template>
        <!--
        <b-dropdown-item :to="{ name: 'apps-contacts-view', params: { id: contact.id } }">
          <feather-icon icon="FileTextIcon" />
          <span class="align-middle ml-50">Details</span>
        </b-dropdown-item>
        -->
        <b-dropdown-item :to="{ name: 'apps-contacts-edit', params: { id: contact.id } }">
          <feather-icon icon="EditIcon" />
          <span class="align-middle ml-50">{{ $t('contacts.cardAdvanceProfile.edit') }}</span>
        </b-dropdown-item>

        <b-dropdown-item>
          <feather-icon icon="TrashIcon" />
          <span class="align-middle ml-50">{{ $t('contacts.cardAdvanceProfile.delete') }}</span>
        </b-dropdown-item>
      </b-dropdown>
    </template>
    <span
      v-if="contact.blocked"
    >
      <feather-icon
        icon="SlashIcon"
        size="16"
        class="cursor-pointer d-sm-block d-none"
        v-b-tooltip.hover.v-secondary
        :title="$t('contacts.cardAdvanceProfile.unlockContact')"
        @click="unlockContact(contact.id)"
        stroke="#FF9F43"
      />
    </span>
    <span
      v-else
    >
      <feather-icon
        icon="SlashIcon"
        size="16"
        class="cursor-pointer d-sm-block d-none"
        v-b-tooltip.hover.v-secondary
        :title="$t('contacts.cardAdvanceProfile.blockContact')"
        @click="blockContact(contact.id)"
      />
    </span>
    
    <!--
    <feather-icon icon="EyeIcon" 
      size="17"
      class="cursor-pointer d-sm-block d-none mr-1"
      :to="{ name: 'apps-contacts-edit', params: { id: contact.id } }"
      
    />
    -->
  </div>
  <b-card
    img-alt="Profile Cover Photo"
    img-top
    :class="skin == 'light'? 'text-center rounded' : 'text-center rounded border-primary'"
  >
    <div class="profile-image-wrapper">
      <div class="profile-image p-0">
        <!--
        <b-avatar
          size="114"
          variant="light"
          :src="require('@/assets/images/portrait/small/avatar-s-9.jpg')"
        />
        -->
        <b-avatar
          size="114"
          :src="contact.con_avatar? baseUrlStorage+contact.con_avatar : null"
          :badge="contact.avatar"
          class="badge-minimal"
          :variant="contact.avatar != null ? 'transparent' : 'light-'+contact.avatarColor"
          :text="contact.initialsName != null ? contact.initialsName : 'CL'"
        />
      </div>
    </div>
    <h3 
      v-b-tooltip.hover.v-secondary 
      :title="contact.con_name"
      v-if="contact.con_name"
    >
      {{ contact.con_name.length > 25? contact.con_name.substring(0,25)+'...' : contact.con_name }} 
    </h3>
    <h3 
      v-b-tooltip.hover.v-secondary 
      v-else
    >
      -
    </h3>
    <h6 class="text-muted">
      <ul class="list-unstyled">
        <li class="mb-1">
          <feather-icon
            icon="MapPinIcon"
            size="16"
            class="mr-10"
          /> 
          <span class="align-middle" v-if="contact.state"> {{contact.state.sta_name}} </span>
          <span class="align-middle" v-else> - </span>
        </li>
        <li>
          <feather-icon
            icon="PhoneIcon"
            size="16"
            class="mr-10"
          /> 
          <span class="align-middle"> {{ contact.con_phone | VMask(' +## (##) #####-####') }} </span>
        </li>
      </ul>
    </h6>
    <p
      v-if="contact.blocked"
    >
      <b-badge
        class="profile-badge"
        variant="danger"
      >
        {{ $t('contacts.cardAdvanceProfile.blocked') }}
      </b-badge>
    </p>
    <p
      v-else
    >
      <b-badge
        class="profile-badge"
        variant="success"
      >
        {{ $t('contacts.cardAdvanceProfile.active') }}
      </b-badge>
    </p>
    <!-- Se o contato tiver alguma tag -->
    <span v-if="contact.tags.length > 0">
      <!-- Para cada tag -->
      <span 
        v-for="tag in contact.tags"
        :key="tag.id"
      >
        <b-badge
        class="profile-badge"
        :style="'margin-right: 6px; margin-bottom: 3px; background-color: '+tag.tag_color"
      >
        {{tag.tag_name}}
      </b-badge>
      </span>
    </span>
    <span v-else>
      <b-badge
        class="profile-badge"
        variant="light-secondary"
      >
        {{ $t('contacts.cardAdvanceProfile.none') }}
      </b-badge>
    </span>
    <hr class="mb-2">

    <!-- follower projects rank -->
    <div class="justify-content-between align-items-center">
      <div class="text-center">
        <h6 class="text-muted font-weight-bolder">
          {{ $t('contacts.cardAdvanceProfile.services') }}
        </h6>
        <h3 class="mb-0">
          {{contact.amountServices}}
        </h3>
      </div>
      <!--
      <div>
        <h6 class="text-muted font-weight-bolder">
          Projects
        </h6>
        <h3 class="mb-0">
          156
        </h3>
      </div>
      <div>
        <h6 class="text-muted font-weight-bolder">
          Rank
        </h6>
        <h3 class="mb-0">
          23
        </h3>
      </div>
      -->
    </div>
    <!--/ follower projects rank -->
  </b-card>
  </div>
</template>

<script>
import { BCard, BAvatar, BBadge, BDropdown, BDropdownItem, VBTooltip, } from 'bootstrap-vue'
import { VueMaskFilter } from 'v-mask'
import useAppConfig from '@core/app-config/useAppConfig'
import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)

export default {
  components: {
    BCard,
    BBadge,
    BAvatar,
    BDropdown,
    BDropdownItem,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  props: {
    contact: {
      type: Object,
      required: true,
    },
    blockContact: {
      type: Function,
      required: true,
    },
    unlockContact: {
      type: Function,
      required: true,
    },
    baseUrlStorage: {
      type: String,
      required: true,
    },
  },
  setup() {
    const { skin } = useAppConfig()

    return {
      skin,
    }
  }
}
</script>
