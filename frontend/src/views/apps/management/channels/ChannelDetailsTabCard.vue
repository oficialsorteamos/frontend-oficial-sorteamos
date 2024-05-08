<template>
  <b-card>
    <b-tabs>
      <b-tab active>
        <template #title>
          <feather-icon icon="ClockIcon" />
          <span>{{ $t('channel.channelDetailsTabCard.temporization') }}</span>
        </template>

        <b-card
          class="card-transaction"
          no-body
        >
          <b-card-header>
            <b-card-title></b-card-title>
            <feather-icon
              icon="EditIcon"
              size="18"
              class="cursor-pointer"
              @click="openModal('modal-edit-parameter-'+channel.id)"
            />
          </b-card-header>

          <b-card-body>
            <div
              v-for="parameter in channel.parameters"
              :key="parameter.id"
              class="transaction-item"
            >
            <!-- Se a categoria do parâmetro for de TEMPORIZAÇÃO -->
              <span
                class="w-100"
                v-if="parameter.type_parameter_channel.category_id == 1"
              >
                <b-row>
                  <b-col
                    cols="10"
                    xl="10"
                    lg="10"
                    md="10"
                  >
                    <b-media 
                      no-body
                    >
                      <b-media-aside>
                        <b-avatar
                          rounded
                          size="42"
                          variant="info"
                        >
                          <feather-icon
                            size="18"
                            icon="ClockIcon"
                          />
                        </b-avatar>
                      </b-media-aside>
                      <b-media-body>
                        <h6 class="transaction-title">
                          {{ parameter.type_parameter_channel.typ_name }}
                        </h6>
                        <small>{{ parameter.type_parameter_channel.typ_description }}</small>
                      </b-media-body>
                    </b-media>
                  </b-col>
                  <b-col
                    cols="2"
                    xl="2"
                    lg="2"
                    md="2"
                    class="mt-2"
                  >
                    <span
                      class="font-weight-bolder text-success"
                    >
                      {{ parameter.par_value }} 
                    </span>
                    <span
                      class="font-weight-bolder "
                    >
                      {{ $t('channel.channelDetailsTabCard.seconds') }} 
                    </span>
                  </b-col>
                </b-row>
              </span>
            </div>
          </b-card-body>
        </b-card>
      </b-tab>
      <b-tab
      >
        <template #title>
          <feather-icon icon="ToolIcon" />
          <span>{{ $t('channel.channelDetailsTabCard.options') }}</span>
        </template>

        <b-card
          class="card-transaction"
          no-body
        >
          <b-card-header>
            <b-card-title></b-card-title>
            <feather-icon
              icon="EditIcon"
              size="18"
              class="cursor-pointer"
              @click="openModal('modal-edit-option-'+channel.id)"
            />
          </b-card-header>

          <b-card-body>
            <div
              v-for="parameter in channel.parameters"
              :key="parameter.id"
              class="transaction-item"
              style="display: inherit !important"
            >
              <!-- Se a categoria do parâmetro for de OPÇÃO -->
              <span
                v-if="parameter.type_parameter_channel.category_id == 2"
              >
                <b-row>
                  <b-col
                    cols="10"
                    xl="10"
                    lg="10"
                    md="10"
                  >
                    <b-media 
                      no-body
                    >
                      <b-media-aside>
                        <b-avatar
                          rounded
                          size="42"
                          variant="secondary"
                        >
                          <feather-icon
                            size="18"
                            icon="ToolIcon"
                          />
                        </b-avatar>
                      </b-media-aside>
                      <b-media-body>
                        <h6 class="transaction-title">
                          {{ parameter.type_parameter_channel.typ_name }}
                        </h6>
                        <small>{{ parameter.type_parameter_channel.typ_description }}</small>
                      </b-media-body>
                    </b-media>
                  </b-col>
                  <b-col
                    cols="2"
                    xl="2"
                    lg="2"
                    md="2"
                    class="mt-2 text-right"
                    
                  >
                    <span
                      v-if="parameter.type_parameter_channel_id == 3"
                    >
                      <span
                        class="font-weight-bolder text-primary mr-5"
                        v-if="parameter.department"
                      >
                        {{ parameter.department.dep_name }}
                      </span>
                      <span 
                        v-else
                      >
                        {{ $t('channel.channelDetailsTabCard.none') }}
                      </span>
                    </span>
                    <span
                      v-if="parameter.type_parameter_channel_id == 4"
                    >
                      <span
                        class="font-weight-bolder text-primary mr-5"
                        v-if="parameter.fair_distribution"
                      >
                        {{ parameter.fair_distribution.fai_name }}
                      </span>
                      <span 
                        v-else
                      >
                        {{ $t('channel.channelDetailsTabCard.none') }}
                      </span>
                    </span>
                    
                  </b-col>
                </b-row>
              </span>
            </div>
          </b-card-body>
        </b-card>
      </b-tab>
      <!--
      <b-tab disabled>
        <template #title>
          <feather-icon icon="EyeOffIcon" />
          <span>Disabled</span>
        </template>

        <b-card-text>
          Halvah dessert fruitcake toffee oat cake tart oat cake topping jelly beans. Pudding sweet pie pastry lemon drops jujubes danish pie gingerbread. Liquorice powder wafer.
        </b-card-text>
      </b-tab>
      <b-tab>
        <template #title>
          <feather-icon icon="UserIcon" />
          <span>Account</span>
        </template>

        <b-card-text>
          Chocolate croissant cupcake croissant jelly donut. Cheesecake toffee apple pie chocolate bar biscuit tart croissant. Lemon drops danish cookie. Oat cake macaroon icing tart lollipop cookie sweet bear claw.
        </b-card-text>
        <b-card-text>
          Carrot cake dragée chocolate. Lemon drops ice cream wafer gummies dragée. Chocolate bar liquorice cheesecake cookie chupa chups marshmallow oat cake biscuit. Dessert toffee fruitcake ice cream powder tootsie roll cake.
        </b-card-text>
      </b-tab>
      -->
    </b-tabs>

    <!-- Form para cadastro de um novo canal -->
    <b-modal
      :id="'modal-edit-parameter-'+channel.id"
      :title="$t('channel.channelDetailsTabCard.editParameters')"
      hide-footer
      size="sm"
    >
      <channel-modal-edit-parameter-handler
        :channel="channel"
        :clear-contact-data="clearParametersData"
        @update-parameters-channel="updateParametersChannel"
        @hide-modal="hideModal"
      />
    </b-modal>
    <!-- Form para cadastro de um novo canal -->
    <b-modal
      :id="'modal-edit-option-'+channel.id"
      :title="$t('channel.channelDetailsTabCard.editOptions')"
      hide-footer
      size="sm"
    >
      <channel-modal-edit-option-handler
        :channel="channel"
        :clear-contact-data="clearParametersData"
        @update-parameters-channel="updateParametersChannel"
        @hide-modal="hideModal"
      />
    </b-modal>
  </b-card>
</template>

<script>
import {
  ref
} from '@vue/composition-api'
import store from '@/store'
import { BTabs, BTab, BCardText, BCard, BCardHeader, BCardTitle, BCardBody, BRow, BCol,
  BMediaBody, BMedia, BMediaAside, BAvatar, } from 'bootstrap-vue'
import ChannelModalEditParameterHandler from './channel-modal-edit-parameter-handler/ChannelModalEditParameterHandler.vue'
import ChannelModalEditOptionHandler from './channel-modal-edit-option-handler/ChannelModalEditOptionHandler.vue'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'


export default {
  components: {
    BCardText,
    BTabs,
    BTab,
    BCard,
    BCardHeader,
    BCardTitle,
    BCardBody,
    BMediaBody, 
    BMedia, 
    BMediaAside, 
    BAvatar,
    BRow,
    BCol,

    ChannelModalEditParameterHandler,
    ChannelModalEditOptionHandler,
  },
  props: {
    channel: {
      type: Object,
      required: true,
    },
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    openModal(modalName) {
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
  },
  setup(_, {emit}) {
    const toast = useToast()

    const blankParameters = {
      cha_name: '',
      cha_company_email: '',
    }
    const parametersData = ref(JSON.parse(JSON.stringify(blankParameters)))
    //Limpa os dados do popup
    const clearParametersData = () => {
      parametersData.value = JSON.parse(JSON.stringify(blankParameters))
    }

    //Atualiza os dados do contato
    const updateParametersChannel = parametersChannelData => {
      store.dispatch('app-channel/updateParametersChannel', { parametersChannelData: parametersChannelData })
        .then(() => {  
          emit('fetch-channels')
          toast({
            component: ToastificationContent,
            props: {
              title: 'Parâmetros atualizados com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    return {
      updateParametersChannel,
      clearParametersData,
    }
  }
}
</script>