<template>
  <b-card>
    <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
    <validation-observer
      #default="{ handleSubmit }"
      ref="refFormObserver"
    >
      <!-- form -->
      <b-form
        enctype="multipart/form-data"
        @submit.prevent="handleSubmit(onSubmit)"
      >
        <b-row>
          <!-- new password -->
          <b-col md="12">
            <validation-provider
              #default="{ errors }"
              :name="$t('campaign.campaignSettingForwarding.department')"
              rules="required"
            >
              <b-form-group
                :label="$t('campaign.campaignSettingForwarding.departmentLabel')"
                label-for="vue-select"
                :state="errors.length > 0 ? false:null"
              >
                <v-select
                  id="vue-select"
                  v-model="settingsLocal.settings[0].department"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="departments"
                  :getOptionLabel="departments => departments.dep_name"
                  transition=""
                />
                <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                  {{ errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>

          <!-- Distribuição Igualitária -->
          <b-col
            md="12"
          >
            <validation-provider
              #default="{ errors }"
              :name="$t('channel.channelModalEditOptionHandler.fairDistribution')"
              rules=""
            >
              <b-form-group
                :label="$t('channel.channelModalEditOptionHandler.fairDistribution')"
                label-for="vue-select"
              >
                <v-select
                  id="vue-select"
                  v-model="settingsLocal.settings[0].fair_distribution"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="fairDistributions"
                  :getOptionLabel="fairDistributions => fairDistributions.fai_name"
                  transition=""
                />
                <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                  {{ errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
        </b-row>
        <div
          class="w-100 border-secondary rounded mb-2 p-1 mt-2"
        >
          <b-badge
            variant="light-secondary"
            class="badge-glow mb-1 p-1"
          >
            <span
              style="font-size: 12px"
            >
              {{ $t('campaign.campaignSettingForwarding.addChatbots') }}
            </span>
          </b-badge>
          <b-row>
            <b-col sm="5">
              <b-form-group
                :label="$t('campaign.campaignSettingForwarding.channels')"
                label-for="vue-select"
              >
                <v-select
                  id="vue-select"
                  v-model="optionSelected"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="channels"
                  :selectable="(option) => !option.inUse"
                  :getOptionLabel="channels => channels.cha_name"
                  transition=""
                >
                  <template #option="{ cha_name, infoMessage }">
                    <b>{{ cha_name }}</b>
                    <br />
                    <cite>{{ infoMessage }}</cite>
                  </template>
                </v-select>
              </b-form-group>
            </b-col>
            <b-col sm="5">
              <b-form-group
                :label="$t('campaign.campaignSettingForwarding.chatbots')"
                label-for="vue-select"
                v-if="optionSelected"
              >
                <v-select
                  id="vue-select"
                  v-model="settingsLocal.chatbotsCampaign"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="chatbotsCampaign"
                  :getOptionLabel="chatbotsCampaign => chatbotsCampaign.cha_name"
                  transition=""
                >
                  <template #option="{cha_name, cha_description }">
                    <b>{{ cha_name }}</b>
                    <br />
                    <cite>{{ cha_description }}</cite>
                  </template>
                </v-select>
              </b-form-group>
            </b-col>
            <b-col sm="2">
              <b-button
                variant="dark"
                class="btn-icon mb-2"
                v-b-tooltip.hover.v-secondary
                :title="$t('campaign.campaignSettingForwarding.addChatbotChannel')"
                :disabled="!optionSelected || !settingsLocal.chatbotsCampaign"
                @click="addChannelChatbotCampaign(optionSelected, settingsLocal.chatbotsCampaign); optionSelected=null; settingsLocal.chatbotsCampaign=[]; fetchChannelsChatbots()"
              >
                <feather-icon 
                  icon="PlusIcon" 
                  size="18"
                />
              </b-button>
            </b-col>
          </b-row>
          
          <b-table
            ref="refUserListTable"
            class="position-relative mt-2"
            :items="channelsChatbotsData"
            responsive
            :fields="tableColumns"
            primary-key="ser_protocol_number"
            :sort-by.sync="sortBy"
            show-empty
            :empty-text="$t('campaign.campaignSettingForwarding.noChatbotsFound')"
            :sort-desc.sync="isSortDirDesc"
          >
            <template #cell(channel)="data">
              <span
                class="font-weight-bold d-block text-nowrap"
              >
                {{ data.item.channel.cha_name.length > 20? data.item.channel.cha_name.substring(0,20)+'...' : data.item.channel.cha_name }}
              </span>
            </template>

            <template #cell(chatbot)="data">
              <span
                class="font-weight-bold d-block text-nowrap"
              >
                {{ data.item.chatbot.cha_name.length > 20? data.item.chatbot.cha_name.substring(0,20)+'...' : data.item.chatbot.cha_name }}
              </span>
            </template>

            <!-- Ações -->
            <template #cell(actions)="row">
              <feather-icon
                icon="TrashIcon"
                size="16"
                class="cursor-pointer"
                v-if="row"
                v-b-tooltip.hover.v-secondary
                :title="$t('campaign.campaignSettingForwarding.removeChatbot')"
                @click="confirmSRemoveChannel(row.item.id)"
              />
            </template>
          </b-table>
        </div>
        <b-row>
          <!-- buttons -->
          <b-col cols="12">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mt-1 mr-1"
              type="submit"
            >
              {{ $t('campaign.campaignSettingForwarding.update') }}
            </b-button>
          </b-col>
          <!--/ buttons -->
        </b-row>
      </b-form>
    </validation-observer>
  </b-card>
</template>

<script>
import {
  BButton, BForm, BFormGroup, BFormInput, BRow, BCol, BCard, BInputGroup, BInputGroupAppend, BFormInvalidFeedback, BTable, VBTooltip,
  BBadge,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import useCampaignSettingForwarding from './useCampaignSettingForwarding'
import { togglePasswordVisibility } from '@core/mixins/ui/forms'
import useChannelsChatbotsList from './useChannelsChatbotsList'
import axios from '@axios'
import vSelect from 'vue-select'
import Swal from 'sweetalert2'

export default {
  components: {
    BButton,
    BForm,
    BFormGroup,
    BFormInput,
    BTable,
    BRow,
    BCol,
    BCard,
    BInputGroup,
    BInputGroupAppend,
    BFormInvalidFeedback,
    BBadge,
    vSelect,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    'b-tooltip': VBTooltip,
    Ripple,
  },
  props: {
    campaign: {
      type: Object,
      required: true,
    },
    channels: {
      type: Array,
      required: false,
    },
    fetchChannelsChatbots: {
      type: Function,
      required: true,
    },
  },
  mixins: [togglePasswordVisibility],
  data() {
    return {
      optionSelected: '',
      departments: [],
      channels_chatbot: [],
      chatbotsCampaign: [],
      fairDistributions: [],
    }
  },
  created() { 
    //Traz as frequências de operação
    axios
      .get('/api/management/department/fetch-departments')
      .then(response => {
        console.log(response.data)
        this.departments = response.data.departments
      });

    //Traz as configurações de transferência igualitária
    axios
      .get('/api/chat/get-fair-distribution-by-campaign/'+this.campaign.id)
      .then(response => {
        this.fairDistributions = response.data
      });
  },
  watch: {
    optionSelected(optionSelected) {
      this.settingsLocal.channels_chatbot = optionSelected
      //Caso exista algum departamento selecionado
      if(optionSelected) {
        //Traz os chatbots que possuem um determinado canal associado para serem inseridos em uma campanha
        axios
          //.get('/api/system/user/get-user/')
          .get(`/api/campaign/fetch-chatbots-campaign/${optionSelected.id}`)
          .then(response => {
            console.log(response.data)
            this.chatbotsCampaign = response.data.chatbotsCampaign
          })
      }
    },
  },
  setup(props, {emit}) {

    const {
      settingsLocal,
      resetUserLocal,
      // UI
      onSubmit,
    } = useCampaignSettingForwarding(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
    } = formValidation(resetUserLocal, props.clearContactData)

    const {
      fetchChannelsChatbotsCampaign,
      addChannelChatbotCampaign,
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      channelsChatbotsData,

    } = useChannelsChatbotsList()

    fetchChannelsChatbotsCampaign(props.campaign.id)


    const confirmSRemoveChannel = channelChatbotId => {
      Swal.fire({
        title: 'Remoção',
        text: "Tem certeza que deseja remover o robô associado a esse canal?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Cancelar',
        customClass: {
          confirmButton: 'btn btn-primary',
          cancelButton: 'btn btn-outline-danger ml-1',
        },
        buttonsStyling: false,
      }).then(result => {
        if (result.value) {
          //Chama a função que atualiza o status do canal
          emit('remove-channel-chabot-campaign', channelChatbotId)
          //Recarrega os canais que possuem chatbots associados
          fetchChannelsChatbotsCampaign(props.campaign.id)
        }
      })
    }

    return {
      settingsLocal,
      resetUserLocal,
      // UI
      onSubmit,

      refFormObserver,
      getValidationState,

      fetchChannelsChatbotsCampaign,
      addChannelChatbotCampaign,
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      channelsChatbotsData,
      confirmSRemoveChannel,
    }
  }
}
</script>
