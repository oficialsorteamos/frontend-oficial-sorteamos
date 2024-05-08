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
          icon="RadioIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('campaign.campaignModalSettingsHandler.channels') }}</span>
      </template>

      <chatbot-setting-channel
        v-if="options.general"
        :general-data="options.general"
        :chatbot="chatbot"
        @update-channel="updateChannelChatbot"
      />
    </b-tab>
    <!--/ general tab -->
  </b-tabs>
</template>

<script>
import { BTabs, BTab } from 'bootstrap-vue'
import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import ChatbotSettingChannel from './chatbot-setting-channel-general/ChatbotSettingChannel.vue'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BTabs,
    BTab,
    ChatbotSettingChannel,
  },
  props: {
    chatbot: {
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

    //Toast Notification
    const toast = useToast()

    //########################## SETTINGS ##############################
    const updateChannelChatbot = channelData => {
      console.log('dados os canais')
      console.log(channelData.channels)
      store.dispatch('app-chatbot/updateChannelChatbot', channelData)
        .then(response => {
          props.chatbot.channels = response.data.channels
          toast({
            component: ToastificationContent,
            props: {
              title: 'Configurações atualizadas com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }


    return {
      updateChannelChatbot,
    }
  }
}
</script>
