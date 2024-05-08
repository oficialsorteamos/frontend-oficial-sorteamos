<template>
  <div class="navbar-container d-flex content align-items-center">

    <!-- Nav Menu Toggler -->
    <ul class="nav navbar-nav d-xl-none">
      <li class="nav-item">
        <b-link
          class="nav-link"
          @click="toggleVerticalMenuActive"
        >
          <feather-icon
            icon="MenuIcon"
            size="21"
          />
        </b-link>
      </li>
    </ul>

    <!-- Left Col -->
    <!--
    <div class="bookmark-wrapper align-items-center flex-grow-1 d-none d-lg-flex">
    -->
      <!-- Bookmarks Container -->
    <!--
      <bookmarks />
    </div>
    -->

    <b-navbar-nav class="nav align-items-center ml-auto">
      <locale />
      <dark-Toggler class="d-none d-lg-block" />
      <!--
      <search-bar />
      -->
      <alert-dropdown 
        v-if="$can('alert_notification', 'notification')"
        @send-alert="sendAlertNotification"
      />
      <notification-dropdown />
      <user-dropdown 
        @logout-user="logout"
      />
    </b-navbar-nav>
  </div>
</template>

<script>
import {
  BLink, BNavbarNav,
} from 'bootstrap-vue'
import {
  ref, onUnmounted,
} from '@vue/composition-api'
import Bookmarks from './components/Bookmarks.vue'
import Locale from './components/Locale.vue'
import SearchBar from './components/SearchBar.vue'
import DarkToggler from './components/DarkToggler.vue'
import AlertDropdown from './components/AlertDropdown.vue'
import NotificationDropdown from './components/NotificationDropdown.vue'
import UserDropdown from './components/UserDropdown.vue'
import store from '@/store'
import userStoreModule from '@/views/apps/management/users/userStoreModule'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { initialAbility } from '@/libs/acl/config'
import useJwt from '@/auth/jwt/useJwt'
import Vue from 'vue'
import router from '@/router'

export default {
  components: {
    BLink,

    // Navbar Components
    BNavbarNav,
    Bookmarks,
    Locale,
    SearchBar,
    DarkToggler,
    AlertDropdown,
    NotificationDropdown,
    UserDropdown,
  },
  props: {
    toggleVerticalMenuActive: {
      type: Function,
      default: () => {},
    },
  },
  setup() {
    const USER_APP_STORE_MODULE_NAME = 'app-user'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    //Toast Notification
    const toast = useToast()

    //Envia um alerta para usuários selecionados
    const sendAlertNotification = alertData => {
      store.dispatch('app-user/sendAlertNotification', { alertData: alertData })
        .then(() => {
          //emit('get-user')  

          toast({
            component: ToastificationContent,
            props: {
              title: 'Alerta enviado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //Envia um alerta para usuários selecionados
    const logout = userData => {
      console.log('chamou logout')
      store.dispatch('app-user/logout', { userData: userData })
        .then(() => {
          // Remove userData from localStorage
          // ? You just removed token from localStorage. If you like, you can also make API call to backend to blacklist used token
          localStorage.removeItem(useJwt.jwtConfig.storageTokenKeyName)
          localStorage.removeItem(useJwt.jwtConfig.storageRefreshTokenKeyName)

          // Remove userData from localStorage
          localStorage.removeItem('userData')

          // Reset ability
          Vue.prototype.$ability.update(initialAbility)

          // Redirect to login page
          router.push({ name: 'auth-login' })
        })
    }

    return {
      sendAlertNotification,
      logout,
    }
  }

}
</script>
