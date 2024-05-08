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

      <campaign-setting-channel
        v-if="options.general"
        :general-data="options.general"
        :channels="campaign"
        @update-channel="updateChannelCampaign"
      />
    </b-tab>
    <!--/ general tab -->

    <!-- Frequência de operação da campanha -->
    <!-- Se NÃO for uma campanha de LIGAÇÃO VIA WHATSAPP -->
    <b-tab
      v-if="campaign.campaign_type_id != 4"
    >
      <template #title>
        <feather-icon
          icon="ActivityIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('campaign.campaignModalSettingsHandler.frequency') }}</span>
      </template>

      <campaign-setting-frequency 
        :campaign="campaign"
        @update-frequency="updateOperatingFrequency"
      />
    </b-tab>

    <!-- Setor para onde os contatos da campanha serão encaminhados -->
    <b-tab>
      <template #title>
        <feather-icon
          icon="RepeatIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('campaign.campaignModalSettingsHandler.forwarding') }}</span>
      </template>

      <campaign-setting-forwarding 
        :campaign="campaign"
        :channels="channelsCampaign"
        :fetch-channels-chatbots="fetchChannelsChatbots"
        @update-forwarding="updateForwarding"
        @remove-channel-chabot-campaign="removeChannelChabotCampaign"
      />
    </b-tab>

    <!-- Horários de operação -->
    <b-tab
      v-if="campaign.campaign_type_id != 4"
    >
      <template #title>
        <feather-icon
          icon="ClockIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('campaign.campaignModalSettingsHandler.schedules') }}</span>
      </template>

      <campaign-setting-operating-hour
        :campaign="campaign"
        @update-operating-hours="updateOperatingHours"
      />
    </b-tab>
  </b-tabs>
</template>

<script>
import { BTabs, BTab } from 'bootstrap-vue'
import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import CampaignSettingChannel from './campaign-setting-channel-general/CampaignSettingChannel.vue'
import CampaignSettingFrequency from './campaign-setting-frequency/CampaignSettingFrequency.vue'
import CampaignSettingOperatingHour from './campaign-setting-operating-hour/CampaignSettingOperatingHour.vue'
import CampaignSettingForwarding from './campaign-setting-forwarding/CampaignSettingForwarding.vue'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BTabs,
    BTab,
    CampaignSettingChannel,
    CampaignSettingFrequency,
    CampaignSettingOperatingHour,
    CampaignSettingForwarding,
  },
  props: {
    campaign: {
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
    const updateChannelCampaign = channelData => {
      console.log('dados os canais')
      console.log(channelData.channels)
      store.dispatch('app-campaign/updateChannelCampaign', channelData)
        .then(response => {
          props.campaign.channels = response.data.channels
          //Atualiza a lista de pendências de configuração da campanha, se houver
          props.campaign.pendencies = response.data.campaign.pendencies
          //Atualiza a lista de canais que podem ter chatbot associado na campanha
          fetchChannelsChatbots()          
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

    //Atualiza a frequência de operação de uma campanha
    const updateOperatingFrequency = frequencyData => {
      store.dispatch('app-campaign/updateOperatingFrequency', frequencyData)
        .then(response => {
          props.campaign.settings[0].operating_frequency = response.data.operatingFrequency
          props.campaign.settings[0].number_shot_frequency = response.data.numberShotFrequency
          toast({
            component: ToastificationContent,
            props: {
              title: 'Frequência de operação da campanha atualizada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //Atualiza os dados de acesso e detalhes da conta o usuário
    const updateOperatingHours = hourData => {
      store.dispatch('app-campaign/updateOperatingHours', hourData)
        .then(response => {
          props.campaign.operating_hours = response.data.operatingHours
          //Atualiza a lista de pendências de configuração da campanha, se houver
          props.campaign.pendencies = response.data.campaign.pendencies
          toast({
            component: ToastificationContent,
            props: {
              title: 'Horários de operação de campanha atualizados com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //Atualiza a frequência de operação de uma campanha
    const updateForwarding = forwardingData => {
      store.dispatch('app-campaign/updateForwarding', forwardingData)
        .then(response => {
          props.campaign.settings[0].department = response.data.department
          //Atualiza a lista de pendências de configuração da campanha, se houver
          props.campaign.pendencies = response.data.campaign.pendencies
          toast({
            component: ToastificationContent,
            props: {
              title: 'Departamento para encaminhamento de contatos atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const channelsCampaign = ref([])

    const fetchChannelsChatbots = () => {
      store.dispatch('app-campaign/fetchChannelsChatbots', {
        campaignId: props.campaign.id, 
        })
        .then(response => {
          channelsCampaign.value = response.data.channels
        })
    }

    fetchChannelsChatbots()

    //Remove um chatbot associado a um canal em uma campanha
  const removeChannelChabotCampaign = channelChatbotId => {
    store.dispatch('app-campaign/removeChannelChatbotCampaign', { id: channelChatbotId })
      .then(() => {
        fetchChannelsChatbots()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Chatbot removido com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
  }

    return {
      updateOperatingFrequency,
      updateOperatingHours,
      updateChannelCampaign,
      updateForwarding,

      channelsCampaign,
      fetchChannelsChatbots,
      removeChannelChabotCampaign,
    }
  }
}
</script>
