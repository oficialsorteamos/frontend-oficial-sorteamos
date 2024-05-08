<template>
  <b-nav-item-dropdown
    class="dropdown-cart mr-25"
    menu-class="dropdown-menu-media"
    right
    @show="fetchItems"
  >
    <template #button-content>
      <feather-icon
        :badge="$store.state['app-ecommerce'].cartItemsCount"
        class="text-body"
        icon="MessageCircleIcon"
        size="21"
        v-b-modal.modal-user-alert-notification
        v-b-tooltip.hover.v-secondary
        title="Alert Notification"
      />

      <!-- Modal com o form para cadastro de evento na agenda -->
      <b-modal
        id="modal-user-alert-notification"
        :title="$t('notification.alertDropdown.alertMessage')"
        hide-footer
        size="sm"
      >
        <user-modal-alert-notification-handler
          :user="userLogged"
          @send-alert-notification="sendAlert"
        />
      </b-modal>
    </template>
  </b-nav-item-dropdown>
</template>

<script>
import {
  BNavItemDropdown, BBadge, BMedia, BLink, BImg, BFormSpinbutton, BButton, VBTooltip,
} from 'bootstrap-vue'
import {
  ref,
} from '@vue/composition-api'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import Ripple from 'vue-ripple-directive'
import UserModalAlertNotificationHandler from '@/views/apps/management/users/user-modal-alert-notification-handler/UserModalAlertNotificationHandler.vue'

export default {
  components: {
    BNavItemDropdown,
    BBadge,
    BMedia,
    BLink,
    BImg,
    BFormSpinbutton,
    VuePerfectScrollbar,
    BButton,

    UserModalAlertNotificationHandler,
  },
  directives: {
    Ripple,
    'b-tooltip': VBTooltip,
  },
  data() {
    return {
      items: [],
      perfectScrollbarSettings: {
        maxScrollbarLength: 60,
        wheelPropagation: false,
      },
    }
  },
  computed: {
    totalAmount() {
      let total = 0
      this.items.forEach(i => { total += i.price })
      return total
    },
  },
  methods: {
    fetchItems() {
      this.$store.dispatch('app-ecommerce/fetchCartProducts')
        .then(response => {
          this.items = response.data.products
        })
    },
    removeItemFromCart(productId) {
      this.$store.dispatch('app-ecommerce/removeProductFromCart', { productId })
        .then(() => {
          const itemIndex = this.items.findIndex(p => p.id === productId)
          this.items.splice(itemIndex, 1)

          // Update count in cart items state
          this.$store.commit('app-ecommerce/UPDATE_CART_ITEMS_COUNT', this.items.length)
        })
    },
  },
  setup(props, {emit}) {

    //Usuário logado no sistema
    const userLogged = ref({})
    userLogged.value = JSON.parse(localStorage.getItem('userData'))

    const sendAlert = alertData => {
      console.log('chamou send alert')
      emit('send-alert', alertData)  
    }
    

    return {
      userLogged,
      sendAlert,
    }
  }
}
</script>

<style lang="scss" scoped>
.dropdown-cart {
  .media {
    .media-aside {
      align-items: center;
    }
  }
}
</style>
