<template>
  <component :is="channels === undefined ? 'div' : 'b-card'"
    :style="skin == 'light'? 'background-color: #f6f6f6' : ''"
  >
    <b-button
      variant="primary"
      class="btn-icon rounded-circle mb-1"
      @click="isTaskHandlerSidebarActive= true;"
      v-b-tooltip.hover.v-secondary
      v-b-modal.modal-add-channel
      :title="$t('channel.addNewChannel')"
    >
      <feather-icon 
        icon="PlusIcon" 
        size="20"
      />
    </b-button>


    <div 
      :style="skin == 'light'? 'background-color: white' : ''"
    >

        <!-- Table Top -->
        <b-row>

          <!-- Per Page -->
          <b-col
            cols="8"
            md="8"
            class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
          >
          <!--
            <label>{{ $t('user.show') }}</label>
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>{{ $t('user.entries') }}</label>
            -->
          </b-col>
  
          <!-- Search -->
          <b-col
            cols="12"
            md="4"
          >
            <div
              class="pt-1 pr-2 pb-2 text-right"
            >
              <b-badge variant="light-primary" class="mb-1">
                {{ totalActiveOfficialChannel }} canais OFICIAIS ativos
              </b-badge>
              <b-badge variant="light-success">
                {{ totalActiveUnofficialChannel }} canais NÃO OFICIAIS ativos
              </b-badge>
            </div>
          </b-col>
        </b-row>
      </div>

    <!-- First Row -->
    <b-row v-for="channel in channels"
        :key="channel.dataField"
        class="mb-1">
      <b-col
        cols="12"
        xl="4"
        lg="8"
        md="7"
      >
        <channel-view-info-card 
          :channel="channel"
          :official-channel-parameter="officialChannelParameter"
          :unofficial-channel-parameter="unofficialChannelParameter"
          class="h-100"
          @fetch-channels="fetchChannels"
          @update-status-channel="updateStatusChannel"
          @start-session="startSession"
          @close-session="closeSession"
          @set-channel="setChannel"
          @set-channel-subscription="setChannelSubscription"
        />
      </b-col>
      <b-col
        cols="12"
        xl="8"
        lg="8"
        md="7"
        class="scroll-col"
      >
        <channel-details-tab-card 
          :channel="channel"
          :hidden-button-service="hiddenButtonService"
          @fetch-channels="fetchChannels"
          class="h-100"

        />
      </b-col>
      <!-- Form para conectar um canal -->
      <b-modal
        :id="'modal-connect-channel-'+channel.id"
        :title="$t('channel.connectChannel')"
        hide-footer
        size="lg"
      >
        <channel-modal-connect-channel-handler
          :channel="channel"
          :clear-contact-data="clearContactData"
          @add-channel="addChannel"
          @hide-modal="hideModal"
          @update-status-channel="updateStatusChannel"
        />
      </b-modal>
    </b-row>
    <!-- Form para cadastro de um novo canal -->
    <b-modal
      id="modal-add-channel"
      :title="$t('channel.editChannel')"
      hide-footer
      size="lg"
    >
      <channel-modal-edit-channel-handler
        :channel="channelData"
        :clear-contact-data="clearContactData"
        :official-channel-parameter="officialChannelParameter"
        :unofficial-channel-parameter="unofficialChannelParameter"
        @add-channel="addChannel"
        @hide-modal="hideModal"
      />
    </b-modal>
  </component>
</template>

<script>
import {
  BTab, BTabs, BCard, BAlert, BLink, BRow, BCol, BBadge, BButton, VBTooltip,
} from 'bootstrap-vue'
import { ref, toRefs } from '@vue/composition-api'
import ChannelViewInfoCard from './ChannelViewInfoCard.vue'
import useAppConfig from '@core/app-config/useAppConfig'
import ChannelDetailsTabCard from './ChannelDetailsTabCard.vue'
import ChannelModalEditChannelHandler from './channel-modal-edit-channel-handler/ChannelModalEditChannelHandler.vue'
import ChannelModalConnectChannelHandler from './channel-modal-connect-channel-handler/ChannelModalConnectChannelHandler.vue'
import useChannel from './useChannel'

export default {
  components: {
    BTab,
    BTabs,
    BCard,
    BAlert,
    BLink,
    BRow,
    BCol,
    BBadge,
    BButton,

    ChannelViewInfoCard,
    ChannelDetailsTabCard,

    ChannelModalEditChannelHandler,
    ChannelModalConnectChannelHandler,
  },

  directives: {
    'b-tooltip': VBTooltip,
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
  },
  setup(props, {emit}) {

    const { skin } = useAppConfig()
    const {
      fetchChannels,
      fetchServices,
      addChannel,
      updateStatusChannel,
      startSession,
      closeSession,

      channels,
      servicesData,
      hiddenButtonService,
      totalQuotaOfficialChannel,
      totalQuotaUnofficialChannel,
      totalActiveOfficialChannel,
      totalActiveUnofficialChannel,
      officialChannelParameter,
      unofficialChannelParameter,
    } = useChannel(toRefs(props), emit)

    
    fetchChannels()
    

    const channel = ref(null)

    const blankChannel = {
      cha_name: '',
      cha_description: '',
      cha_company_name: '',
      cha_company_email: '',
      cha_company_site: '',
      cha_company_address: '',
      cha_phone_number: '',
      cha_app_name_api: '',
      cha_api_official: false,
      api: '',
      whatsapp_business_account_id: '',
      cha_app_id_api: '',
      cha_channel_id_api: '',
      cha_session_token: '',
    }
    const channelData = ref(JSON.parse(JSON.stringify(blankChannel)))
    //Limpa os dados do popup
    const clearContactData = () => {
      channelData.value = JSON.parse(JSON.stringify(blankContact))
    }

    //Atualiza na tela o atributo de renovação automática
    const setChannel = channelData => {
      channels.value.find(c => c.id === channelData.id).cha_automatic_subscription_renewal = channelData.cha_automatic_subscription_renewal
    }

    //Atualiza os dados do cartão utilizado na assinatura do cartão
    const setChannelSubscription = channelData => {
      channels.value.find(c => c.id === channelData.id).subscription = channelData.subscription
    }


    return {
      channels,
      channelData,
      servicesData,
      totalQuotaOfficialChannel,
      totalQuotaUnofficialChannel,
      totalActiveOfficialChannel,
      totalActiveUnofficialChannel,
      officialChannelParameter,
      unofficialChannelParameter,

      fetchServices,
      hiddenButtonService,
      addChannel,
      updateStatusChannel,
      startSession,
      closeSession,

      fetchChannels,
      clearContactData,
      setChannel,
      setChannelSubscription,

      skin,
    }
  },
}
</script>

<style>
  .scroll-col {
    max-height: 500px; 
    overflow-y: scroll;
  }
</style>
