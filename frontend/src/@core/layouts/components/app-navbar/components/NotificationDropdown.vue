<template>
  <b-nav-item-dropdown
    class="dropdown-notification mr-25"
    menu-class="dropdown-menu-media"
    right
  >
    <template #button-content>
      <feather-icon
        :badge="totalUnreadNotifications > 0? totalUnreadNotifications : null"
        badge-classes="bg-danger"
        class="text-body"
        icon="BellIcon"
        size="21"
        @click="markNotificationAsRead"
      />
    </template>

    <!-- Header -->
    <li class="dropdown-menu-header">
      <div class="dropdown-header d-flex">
        <h4 class="notification-title mb-0 mr-auto">
          {{ $t('notification.notificationDropdown.notifications') }}
        </h4>
        <b-badge
          pill
          variant="light-primary"
          v-if="totalNewNotifications > 0"
        >
          {{totalNewNotifications}} {{ $t('notification.notificationDropdown.new') }}  
        </b-badge>
      </div>
    </li>

    <!-- Notifications -->
    <vue-perfect-scrollbar
      :settings="perfectScrollbarSettings"
      class="scrollable-container media-list scroll-area"
      tagname="li"
    >
      <span
        v-if="notifications.length > 0"
      >
        <!-- Account Notification -->
        <b-link
          v-for="notification in notifications"
          :key="notification.id"
        >
          <b-media>
            <template #aside>
              <b-avatar
                size="32"
                :src="'../../../../../../../../'+notification.data.imageUrl"
                :text="notification.data.imageUrl"
                variant="light-info"
              />
            </template>
            <p class="media-heading">
              <span class="font-weight-bolder">
                <span 
                  v-if="notification.data"
                >
                  {{ notification.data.title }} {{notification.data.senderName? '('+notification.data.senderName+')' : ''}}
                </span>
                <span v-else>
                  {{notification}}
                </span>
              </span>
            </p>
            <small class="notification-text">
              <span 
                v-if="notification.data"
                v-html="notification.data.message"
              >
              </span>
            </small>
          </b-media>
        </b-link>
      </span>
      <span
        v-else
      >
        <h6
          class="p-1 text-center"
        >
          {{ $t('notification.notificationDropdown.noNotifications') }}
        </h6>
      </span>
      <!-- System Notification Toggler -->
      <!--
      <div class="media d-flex align-items-center">
        <h6 class="font-weight-bolder mr-auto mb-0">
          System Notifications
        </h6>
        <b-form-checkbox
          :checked="true"
          switch
        />
      </div>
      -->
      <!-- System Notifications -->
      <!--
      <b-link
        v-for="notification in systemNotifications"
        :key="notification.subtitle"
      >
        <b-media>
          <template #aside>
            <b-avatar
              size="32"
              :variant="notification.type"
            >
              <feather-icon :icon="notification.icon" />
            </b-avatar>
          </template>
          <p class="media-heading">
            <span class="font-weight-bolder">
              {{ notification.title }}
            </span>
          </p>
          <small class="notification-text">{{ notification.subtitle }}</small>
        </b-media>
      </b-link>
      -->
    </vue-perfect-scrollbar>

    <!-- Cart Footer -->
    <li class="dropdown-menu-footer">
      <b-button
        v-ripple.400="'rgba(255, 255, 255, 0.15)'"
        variant="primary"
        block
        v-if="notifications.length > 0"
        :to="{ name: 'apps-notifications' }"
      >
        Read all notifications
      </b-button>
    </li>
  </b-nav-item-dropdown>
</template>

<script>
import {
  BNavItemDropdown, BBadge, BMedia, BLink, BAvatar, BButton, BFormCheckbox,
} from 'bootstrap-vue'
import {
  ref, onUnmounted, onMounted,
} from '@vue/composition-api'
import store from '@/store'
import userStoreModule from '@/views/apps/management/users/userStoreModule'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import Ripple from 'vue-ripple-directive'

export default {
  components: {
    BNavItemDropdown,
    BBadge,
    BMedia,
    BLink,
    BAvatar,
    VuePerfectScrollbar,
    BButton,
    BFormCheckbox,
  },
  directives: {
    Ripple,
  },
  setup() {
    const USER_APP_STORE_MODULE_NAME = 'app-user'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    const totalUnreadNotifications = ref([{}])
    const totalNewNotifications = ref(0)
    const notifications = ref({})
    //Usuário logado no sistema
    const userLogged = ref({})
    userLogged.value = JSON.parse(localStorage.getItem('userData'))

    /*let notifications = 
      [
        {
          id: 123456,
          data: {
            imageUrl: '12345',
            title: 'testesssss',
            message: '1 messagem',
          }
        },
        {
          id: 1234545,
          data: {
            imageUrl: '12345',
            title: 'testesssss',
            message: '1 messagem',
          }
        }
      ]*/
    
    

    //Traz as notificações de um usuário
    onMounted(() => {
      store.dispatch('app-user/fetchUserNotification', { userId: userLogged.value.id })
        .then(response => {
          notifications.value = response.data.notifications
          totalUnreadNotifications.value = response.data.totalUnreadNotifications
          totalNewNotifications.value = response.data.totalUnreadNotifications
        })
    })

    //Marca todas as notificações como lidas
    const markNotificationAsRead = () => {
      store.dispatch('app-user/markNotificationAsRead', { userId: userLogged.value.id })
        .then(() => {
          //Coloca a quantidade de notificações não lidas como 0
          totalUnreadNotifications.value = 0
        })
    }
    

    Echo.private('App.Models.Management.User.'+userLogged.value.id)
    .notification((notification) => {
      let newNotification = {
          id: notification.id,
          data: {
            imageUrl: notification.imageUrl, 
            title: notification.title +' ('+notification.senderName+')', 
            message: notification.message, 
          }
        }
      
        notifications.value.unshift(newNotification)

        //notifications.value = [{}]

        totalUnreadNotifications.value++
        totalNewNotifications.value++
        
        console.log('formato')
        console.log(notifications)
        console.log('nova notificação')
        console.log(notification)
    })


    const perfectScrollbarSettings = {
      maxScrollbarLength: 60,
      wheelPropagation: false,
    }

    return {
      notifications,
      totalUnreadNotifications,
      totalNewNotifications,
      perfectScrollbarSettings,

      markNotificationAsRead,
    }
  },
}
</script>

<style>

</style>
