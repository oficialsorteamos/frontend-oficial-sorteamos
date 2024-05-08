<template>
  <b-nav-item-dropdown
    right
    toggle-class="d-flex align-items-center dropdown-user-link"
    class="dropdown-user"
  >
    <template #button-content>
      <div class="d-sm-flex d-none user-nav">
        <p class="user-name font-weight-bolder mb-0">
          {{ userLogged.name || userLogged.username }}
        </p>
        <span 
          class="user-status" 
          v-if="userLogged.roles"
        >
          {{ userLogged.roles[0].rol_name }}
        </span>
      </div>
      <b-avatar
        size="40"
        :src="userLogged.avatar? baseUrlStorage+userLogged.avatar : null"
        variant="light-primary"
        badge
        class="badge-minimal"
        :badge-variant="userLogged.situation_user_id == 1? 'success' : 'danger'"
      >
        <feather-icon
          v-if="!userLogged.name"
          icon="UserIcon"
          size="22"
        />
      </b-avatar>
    </template>

    <!-- Modal com o form para cadastro de evento na agenda -->
    <b-modal
      id="modal-user-edit"
      title="Profile"
      hide-footer
      size="lg"
    >
      <user-navbar-edit-modal-handler
        :user="userLogged"
      />
    </b-modal>

    <b-dropdown-item
      v-b-modal.modal-user-edit
    >
      <feather-icon
        size="16"
        icon="UserIcon"
        class="mr-50"
      />
      <span>Perfil</span>
    </b-dropdown-item>
    <!--
    <b-dropdown-item
      :to="{ name: 'apps-email' }"
      link-class="d-flex align-items-center"
    >
      <feather-icon
        size="16"
        icon="MailIcon"
        class="mr-50"
      />
      <span>Inbox</span>
    </b-dropdown-item>
    <b-dropdown-item
      :to="{ name: 'apps-chatbot' }"
      link-class="d-flex align-items-center"
    >
      <feather-icon
        size="16"
        icon="CheckSquareIcon"
        class="mr-50"
      />
      <span>Task</span>
    </b-dropdown-item>
    
    <b-dropdown-item
      :to="{ name: 'apps-chat' }"
      link-class="d-flex align-items-center"
    >
      <feather-icon
        size="16"
        icon="MessageSquareIcon"
        class="mr-50"
      />
      <span>Chat</span>
    </b-dropdown-item>
    -->

    <b-dropdown-divider />
    <!--
    <b-dropdown-item
      :to="{ name: 'pages-account-setting' }"
      link-class="d-flex align-items-center"
    >
      <feather-icon
        size="16"
        icon="SettingsIcon"
        class="mr-50"
      />
      <span>Settings</span>
    </b-dropdown-item>
    <b-dropdown-item
      :to="{ name: 'pages-pricing' }"
      link-class="d-flex align-items-center"
    >
      <feather-icon
        size="16"
        icon="CreditCardIcon"
        class="mr-50"
      />
      <span>Pricing</span>
    </b-dropdown-item>
    <b-dropdown-item
      :to="{ name: 'pages-faq' }"
      link-class="d-flex align-items-center"
    >
      <feather-icon
        size="16"
        icon="HelpCircleIcon"
        class="mr-50"
      />
      <span>FAQ</span>
    </b-dropdown-item>
    -->
    <b-dropdown-item
      link-class="d-flex align-items-center"
      @click="logout"
    >
      <feather-icon
        size="16"
        icon="LogOutIcon"
        class="mr-50"
      />
      <span>Sair</span>
    </b-dropdown-item></b-nav-item-dropdown>
</template>

<script>
import {
  BNavItemDropdown, BDropdownItem, BDropdownDivider, BAvatar, BModal,
} from 'bootstrap-vue'
import {
  ref,
} from '@vue/composition-api'
import axios from '@axios'
import { initialAbility } from '@/libs/acl/config'
import useJwt from '@/auth/jwt/useJwt'
import { avatarText } from '@core/utils/filter'
import UserNavbarEditModalHandler from '@/views/apps/management/users/user-navbar-edit-modal-handler/UserNavbarEditModalHandler.vue'

export default {
  components: {
    BNavItemDropdown,
    BDropdownItem,
    BDropdownDivider,
    BAvatar,
    BModal,

    UserNavbarEditModalHandler,
  },
  data() {
    return {
      avatarText,
      baseUrlStorage: '',
    }
  },
  created() { 
    //Traz a URL pública do Storage
    axios
      .get('/api/chat/get-base-url-storage/')
      .then(response => {
        //console.log(response.data)
        this.baseUrlStorage = response.data.baseUrlStorage
      })
  },
  methods: {
    logout() {
      let user = JSON.parse(localStorage.getItem('userData'))
      this.$emit('logout-user', user)
    },
  },
  setup() {

    //Usuário logado no sistema
    const userLogged = ref({})
    userLogged.value = JSON.parse(localStorage.getItem('userData'))
    
    
    return {
      userLogged
    }
  }
}
</script>
