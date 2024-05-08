<template>
  <b-tabs
    vertical
    content-class="col-12 col-md-9 mt-1 mt-md-0"
    pills
    nav-wrapper-class="col-md-3 col-12"
    nav-class="nav-left"
  >

    <!-- general tab -->
    <b-tab active>

      <!-- title -->
      <template #title>
        <feather-icon
          icon="UserIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">General</span>
      </template>

      <user-account-setting-general
        v-if="options.general"
        :general-data="options.general"
        :user="user"
        :clear-user-data="clearUserData"
        @update-user-information="updateUserInformation"
        @upload-photo="uploadPhoto"
      />
    </b-tab>
    <!--/ general tab -->

    <!-- change password tab -->
    <b-tab>

      <!-- title -->
      <template #title>
        <feather-icon
          icon="LockIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">Change Password</span>
      </template>

      <user-account-setting-password 
        :user="user"
        :clear-user-data="clearUserData"
        @update-user-access="updateUserAccessDetailAccount"
      />
    </b-tab>
    <!--/ change password tab -->

    <!-- info -->
    <!--
    <b-tab>

      <template #title>
        <feather-icon
          icon="InfoIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">Information</span>
      </template>

      <account-setting-information
        v-if="options.info"
        :information-data="options.info"
      />
    </b-tab>
    -->
    <!-- notification -->
    <b-tab>

      <!-- title -->
      <template #title>
        <feather-icon
          icon="BellIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">Notifications</span>
      </template>

      <user-account-setting-notification
        :user="user"
        :clear-user-data="clearUserData"
        @update-user-notification="updateUserNotification"
      />
    </b-tab>
  </b-tabs>
</template>

<script>
import { BTabs, BTab } from 'bootstrap-vue'
import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import userStoreModule from '../partnerStoreModule'
import UserAccountSettingGeneral from './user-account-setting-general/UserAccountSettingGeneral.vue'
import UserAccountSettingPassword from './user-account-setting-password/UserAccountSettingPassword.vue'
import AccountSettingInformation from './AccountSettingInformation.vue'
import UserAccountSettingNotification from './user-account-setting-notification/UserAccountSettingNotification.vue'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BTabs,
    BTab,
    UserAccountSettingGeneral,
    UserAccountSettingPassword,
    AccountSettingInformation,
    UserAccountSettingNotification,
  },
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      options: {},
    }
  },
  beforeCreate() {
    this.$http.get('/account-setting/data').then(res => { this.options = res.data })
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
  },
  setup(props) {

    const USER_APP_STORE_MODULE_NAME = 'app-user'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    //Toast Notification
    const toast = useToast()

    const blankUser = {

    }
    const contactData = ref(JSON.parse(JSON.stringify(blankUser)))
    //Limpa os dados do popup
    const clearUserData = () => {
      contactData.value = JSON.parse(JSON.stringify(blankUser))
    }

    //Atualiza os dados do contato
    const updateUserInformation = userData => {
      store.dispatch('app-user/updateUserInformation', { userData: userData })
        .then(() => {
          //emit('get-user')  

          toast({
            component: ToastificationContent,
            props: {
              title: 'Usuário atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const photo = ref('')
    
    //Faz o upload da foto de avatar do usuário
    const uploadPhoto = (event) => {
      photo.value = event.target.files[0]
      
      const formData = new FormData()
      formData.append('name', 'ivahy.jpg')
      formData.append('file', photo.value)
      formData.append('userId', props.user.id)
      
      const config = {
          headers: {
            'content-type': 'multipart/form-data'
          }
      }
      store.dispatch('app-user/uploadPhoto', formData, config)
        .then(() => {
          //emit('get-contact')

          toast({
            component: ToastificationContent,
            props: {
              title: 'Avatar atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
      })
    }

    //Atualiza os dados de acesso e detalhes da conta o usuário
    const updateUserAccessDetailAccount = userData => {
      store.dispatch('app-user/updateUserAccessDetail', { userData: userData })
        .then(response => {
          //emit('get-user')
          //Caso a senha atual de confirmação tenha sido digitada incorretamente
          if(response.data.error) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'A senha atual digitada não confere!',
                icon: 'CheckIcon',
                variant: 'danger',
              },
            })  
          }
          else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Senha atualizada com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }
        })
    }

    //Atualiza os dados de acesso e detalhes da conta o usuário
    const updateUserNotification = userData => {
      store.dispatch('app-user/updateUserNotification', { userData: userData })
        .then(response => {
          //emit('get-user')
          toast({
            component: ToastificationContent,
            props: {
              title: 'Configurações de notificação atualizadas com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }


    return {
      clearUserData,
      updateUserInformation,
      uploadPhoto,
      updateUserAccessDetailAccount,
      updateUserNotification,
    }
  }
}
</script>
