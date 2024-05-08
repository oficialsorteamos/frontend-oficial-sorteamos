<template>
  <div
    class="chat-profile-sidebar"
    :class="{'show': shallShowUserProfileSidebar}"
  >
    <!-- Header -->
    <header class="chat-profile-header">
      <span class="close-icon">
        <feather-icon
          icon="XIcon"
          @click="$emit('close-sidebar')"
        />
      </span>

      <div class="header-profile-sidebar">
        <div class="avatar-border">
          <b-avatar
            class="badge-medium"
            size="70"
            :src="'../../../../'+profileUserData.avatar"
            :text="avatarText(profileUserData.name)"
            :variant="profileUserData.avatar != null ? 'transparent' : 'light-primary'"
            :badge-variant="profileUserData.situation_user_id == 1? 'success' : 'danger'"
            badge
          />
        </div>
        <h4 class="chat-user-name">
          {{ profileUserData.name }} 
        </h4>
        <b-badge
          pill
          variant="light-secondary"
          v-if="profileUserData.roles"
        >
          {{profileUserData.roles[0].rol_name}}
        </b-badge>
      </div>
    </header>

    <!-- User Details -->
    <vue-perfect-scrollbar
      :settings="perfectScrollbarSettings"
      class="profile-sidebar-area scroll-area"
    >

      <!-- About -->
      <!--
      <h6 class="section-label mb-1">
        About
      </h6>
      <div class="about-user">
        <b-form-textarea
          v-model="profileUserData.about"
          placeholder="Something about yourself..."
          rows="5"
        />
      </div>
      -->
      <!-- Status -->
      <h6 class="section-label mb-1 mt-1">
        Status
      </h6>
      <b-form-radio-group
        id="user-status-options"
        stacked
        @change="setUserSituation"
      >
        <!-- :options="userStatusOptions" -->
        <b-form-radio
          v-model="profileUserData.situation_user_id"
          value="1"
          class="custom-control-success"
          @click="setUserSituation(1)"
        >
          Online
        </b-form-radio>
        <b-form-radio
          v-model="profileUserData.situation_user_id"
          value="2"
          class="custom-control-danger"
          @click="setUserSituation(2)"
        >
          Offline
        </b-form-radio>
      </b-form-radio-group>

      <!-- Settings -->
      <!--
      <h6 class="section-label mb-1 mt-2">
        Settings
      </h6>
      <ul
        v-if="profileUserData.settings"
        class="list-unstyled"
      >
      -->
        <!-- Two Step Auth -->
        <!--
        <li class="d-flex justify-content-between align-items-center mb-1">
          <div class="d-flex align-items-center">
            <feather-icon
              icon="CheckSquareIcon"
              size="18"
              class="mr-75"
            />
            <span class="align-middle">Two-step Verification</span>
          </div>
          <b-form-checkbox
            v-model="profileUserData.settings.isTwoStepAuthVerificationEnabled"
            switch
          />
        </li>
        -->
        <!-- Notifications -->
        <!--
        <li class="d-flex justify-content-between align-items-center mb-1">
          <div class="d-flex align-items-center">
            <feather-icon
              icon="BellIcon"
              size="18"
              class="mr-75"
            />
            <span class="align-middle">Notification</span>
          </div>
          <b-form-checkbox
            v-model="profileUserData.settings.isNotificationsOn"
            switch
          />
        </li>
        -->
        <!-- Invite Friends -->
        <!--
        <li class="mb-1 d-flex align-items-center cursor-pointer">
          <feather-icon
            icon="UserIcon"
            class="mr-75"
            size="18"
          />
          <span class="align-middle">Invite Friends</span>
        </li>
        -->
        <!-- Delete Account -->
        <!--
        <li class="d-flex align-items-center cursor-pointer">
          <feather-icon
            icon="TrashIcon"
            class="mr-75"
            size="18"
          />
          <span class="align-middle">Delete Account</span>
        </li>
        
      </ul>

      <div class="mt-3">
        <b-button variant="primary">
          Logout
        </b-button>
      </div>
      -->
    </vue-perfect-scrollbar>
  </div>
</template>

<script>
import {
  BAvatar, BFormTextarea, BFormRadioGroup, BFormRadio, BFormCheckbox, BButton, BBadge,
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import { avatarText } from '@core/utils/filter'

export default {
  components: {
    BAvatar,
    BFormTextarea,
    BFormRadioGroup,
    BFormRadio,
    BFormCheckbox,
    BButton,
    BBadge,
    VuePerfectScrollbar,
  },
  props: {
    shallShowUserProfileSidebar: {
      type: Boolean,
      required: true,
    },
    profileUserData: {
      type: Object,
      required: true,
    },

  },
   methods: {
    setUserSituation(situationId) {
      //Emit para atualizar o status do usuário
      this.$emit('set-user-situation', situationId)
    },
   },
  setup() {
    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    return {
      perfectScrollbarSettings,
      avatarText,
    }
  },
}
</script>

<style lang="scss" scoped>
#user-status-options ::v-deep {
  .custom-radio {
    margin-bottom: 1rem;
  }
}
</style>
