<template>
  <!--
  <b-card
    :img-src="require('@/assets/images/banner/banner-12.jpg')"
    img-alt="Profile Cover Photo"
    img-top
    class="card-profile"
  >
  -->
  <div>
  <div 
    class="d-flex justify-content-between flex-column pt-1 pr-1 pb-1"
    :style="skin == 'light'? 'background-color: white' : ''"
  >
    <b-row>
      <b-col
        cols="12"
        md="5"
        style="margin-left: 35px;"
      >
        <!-- Se for um cartão de crédito -->
        <b-badge
          class="profile-badge"
          variant="primary"
          v-if="card.type_card_id == 1"
        >
          {{ $t('card.cardProfile.creditCard') }}
        </b-badge>
        <!-- Se for um cartão de débito -->
        <b-badge
          class="profile-badge"
          variant="dark"
          v-if="card.type_card_id == 2"
        >
          {{ $t('card.cardProfile.debitCard') }}
        </b-badge>
        <!-- Botão para deletar canal (Só exibe se o canal estiver desabilitado) -->
        <feather-icon icon="StarIcon" 
          size="20"
          class="cursor-pointer ml-1"
          style="margin-bottom: 2px;"
          v-b-tooltip.hover.v-secondary
          :title="$t('card.cardProfile.mainCard')"
          v-if="card.car_main"
          stroke="#ff9f43"
        />
      </b-col>
      
      <b-col
        cols="12"
        md="6"
        class="d-flex justify-content-end"
      >
        <!-- Se não for um bloco final -->
        <feather-icon icon="EditIcon" 
          size="17"
          class="cursor-pointer d-sm-block d-none mr-1"
          @click="openModal('modal-edit-card-'+card.id);"
        />
        <!-- Botão para deletar canal (Só exibe se o canal estiver desabilitado) -->
        <feather-icon icon="TrashIcon" 
          size="17"
          class="cursor-pointer d-sm-block d-none"
          v-b-tooltip.hover.v-secondary
          :title="$t('card.cardProfile.removeCard')"
          @click="removeCard(card.id)"
        />
      </b-col>
    </b-row>
  </div>
  <b-card
    img-alt="Profile Cover Photo"
    img-top
  >
    <!-- Interactive Card -->
    <vue-paycard :value-fields="card" :labels="labels" />

    <div class="d-flex justify-content-between">
      <h6 class="section-label mb-1 mt-4">
        {{ $t('card.cardProfile.holderData') }}
      </h6>
    </div>
    <b-row>
      <b-col
        cols="12"
        xl="12"
      >
        <table class="mt-2 mt-xl-0 w-100">
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="UsersIcon"
                class="mr-75"
              />
              <span class="font-weight-bold"> {{ $t('card.cardProfile.name') }}</span>
            </th>
            <td class="pb-50 text-capitalize">
              {{ card.holder_info.car_name }}
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="PhoneIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('card.cardProfile.phone') }}</span>
            </th>
            <td 
            class="pb-50"
              v-if="card.holder_info.car_phone"
            >
              {{ card.holder_info.car_phone | VMask(' (##) #####-####') }}
            </td>
            <td v-else> 
              -
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="MailIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('card.cardProfile.email') }}</span>
            </th>
            <td class="pb-50">
              <span v-if="card.holder_info.car_email"> {{ card.holder_info.car_email }} </span>
              <span v-else> - </span>
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="FileTextIcon"
                class="mr-50"
              />
              <span class="font-weight-bold">{{ card.holder_info.car_cpf? $t('card.cardProfile.cpf') : $t('card.cardProfile.cnpj') }}</span>
            </th>
            <td 
            class="pb-50"
              v-if="card.holder_info.car_cpf"
            >
              {{ card.holder_info.car_cpf | VMask('###.###.###-##') }}
            </td>
            <td v-else> 
              {{ card.holder_info.car_cnpj | VMask('##.###.###/####-##') }}
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="MapPinIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('card.cardProfile.postalCode') }}</span>
            </th>
            <td class="pb-50">
              <span v-if="card.holder_info.car_postal_code">  {{ card.holder_info.car_postal_code | VMask('##.###-###')  }} {{ card.holder_info.car_address_number? ', Nº '+ card.holder_info.car_address_number : '' }} </span>
              <span v-else> - </span>
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="UserPlusIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('card.cardProfile.createdAt') }}</span>
            </th>
            <td class="pb-50 text-capitalize">
              <span v-if="card.holder_info.created_at"> {{ formatDate(card.holder_info.created_at) }}</span>
              <span v-else> - </span>
            </td>
          </tr>
        </table>
      </b-col>
    </b-row>
    <!-- Modal para editar de um novo cartão de crédito -->
    <b-modal
      :id="'modal-edit-card-'+card.id"
      title="Edit Card"
      hide-footer
      size="lg"
    >
      <card-modal-add-card-handler
        :card="card"
        :clear-contact-data="clearCardData"
        @update-card="updateCard"
        @hide-modal="hideModal"
      />
    </b-modal>    
  </b-card>
  </div>
</template>

<script>
import { BCard, BAvatar, BBadge, BDropdown, BDropdownItem, VBTooltip, BTooltip, BRow, BCol,} from 'bootstrap-vue'
import store from '@/store'
import axios from '@axios'
import { formatDate } from '@core/utils/filter'
import { $themeColors } from '@themeConfig'
import useAppConfig from '@core/app-config/useAppConfig'
import CardModalAddCardHandler from './card-modal-add-card-handler/CardModalAddCardHandler.vue'
import { VuePaycard } from "vue-paycard"
import { VueMaskFilter } from 'v-mask'
import useCard from './useCard'
import Swal from 'sweetalert2'
import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BCard,
    BBadge,
    BAvatar,
    BDropdown,
    BDropdownItem,
    BTooltip,
    BRow,
    BCol,

    VuePaycard,
    CardModalAddCardHandler,

  },
  props: {
    card: {
      type: Object,
      required: true,
    },
    fetchCards: {
      type: Function,
      required: true,
    },
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    return {
      inputFields: {
        cardNumber: 'v-card-number',
        cardName: 'v-card-name',
        cardMonth: 'v-card-month',
        cardYear: 'v-card-year',
        cardCvv: 'v-card-cvv'
      },
      labels : {
        cardName : "NOME NO CARTÃO",
        cardHolder : "Nome Titular",
        cardMonth : "MM",
        cardYear : "AA",
        cardExpires : "Expira",
        cardCvv : "CVV",
      },
    }
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
  setup(props) {
    //Toast Notification
    const toast = useToast()
    const { skin } = useAppConfig()

    //Remove uma mensagem da campanha
    const removeCard = campaignId => {
      
      Swal.fire({
        title: 'Remover Cartão',
        text: "Você realmente quer remover esse cartão?",
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
          store.dispatch('app-card/removeCard', { id: campaignId })
          .then(() => {
            //Carrega as campanhas cadastradas
            props.fetchCards()
            toast({
              component: ToastificationContent,
              props: {
                title: 'Cartão removido com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          })
        }
      })
    }

    const clearCardData = () => {
      //cardData.value = JSON.parse(JSON.stringify(blankCard))
    }

    const updateCard = messageData => {
      store.dispatch('app-card/updateCard', messageData)
      .then(response => {
        // eslint-disable-next-line no-use-before-define
        props.fetchCards()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Cartão atualizado com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
    }


    return {
      updateCard,
      clearCardData,
      removeCard,

      formatDate,
      skin,
    }
  }
}
</script>
