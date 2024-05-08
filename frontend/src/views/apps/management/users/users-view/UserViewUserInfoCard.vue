<template>
  <b-card>
    <div 
      class="d-flex justify-content-between align-items-center"
      style="margin-top: -10px"
    >
      <h4 >
      </h4>
      <feather-icon
        icon="EditIcon"
        size="18"
        class="cursor-pointer"
        v-b-modal.modal-edit-user-information
        @click="numberWithoutDdi"
      />
    </div>
    <b-row>

      <!-- User Info: Left col -->
      <b-col
        cols="21"
        xl="6"
        class="d-flex justify-content-between flex-column"
      >
        <!-- User Avatar & Action Buttons -->
        <div class="d-flex justify-content-start">
          <b-avatar
            :src="userData.avatar"
            :text="avatarText(userData.name)"
            :variant="'light-'+resolveUserRoleVariant(userData.roles[0]? userData.roles[0].id : null)"
            size="104px"
            rounded
          />
          <div class="d-flex flex-column ml-1">
            <div class="mb-1">
              <h4 class="mb-0">
                {{ userData.name }} 
                <b-badge 
                  :variant="userData.situation_user_id == 1 ? 'success' : 'danger'"
                  v-if="userData.situation_user_id"
                  style="font-size: 10px"
                >
                  <span 
                    class="card-text"
                    v-if="userData.situation_user_id == 1"
                  >
                    Online
                  </span>
                  <span 
                    class="card-text"
                    v-if="userData.situation_user_id == 2"
                  >
                    Offline
                  </span>
                </b-badge>
              </h4>
              <b-badge 
                variant="light-secondary"
                v-if="userData.roles[0]"
              >
                <span class="card-text">{{ userData.roles[0].rol_name }}</span>
              </b-badge>
            </div>
          </div>
        </div>

        <!-- User Stats -->
        <div class="d-flex align-items-center mt-2">
          <div class="d-flex align-items-center mr-2">
            <b-avatar
              variant="light-success"
              rounded
            >
              <feather-icon
                icon="TrendingUpIcon"
                size="18"
              />
            </b-avatar>
            <div class="ml-1">
              <h5 class="mb-0">
                {{userData.amountService}}
              </h5>
              <small>{{ $t('user.userViewUserInfoCard.totalServices') }}</small>
            </div>
          </div>
          <div class="d-flex align-items-center">
            <b-avatar
              variant="light-warning"
              rounded
            >
              <feather-icon
                icon="StarIcon"
                size="18"
              />
            </b-avatar>
            <div class="ml-1">
              <h5 class="mb-0">
                {{userData.rating}}
              </h5>
              <small>{{ $t('user.userViewUserInfoCard.rating') }}</small>
            </div>
          </div>
        </div>
      </b-col>

      <!-- Right Col: Table -->
      <b-col
        cols="12"
        xl="6"
      >
        <table class="mt-2 mt-xl-0 w-100">
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="PhoneIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('user.userViewUserInfoCard.phone') }}</span>
            </th>
            <td 
            class="pb-50"
              v-if="userData.phone"
            >
              {{ userData.phone | VMask(' +## (##) #####-####') }}
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
              <span class="font-weight-bold">{{ $t('user.userViewUserInfoCard.email') }}</span>
            </th>
            <td class="pb-50">
              <span v-if="userData.email"> {{ userData.email }} </span>
              <span v-else> - </span>
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="UsersIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('user.userViewUserInfoCard.gender') }}</span>
            </th>
            <td class="pb-50 text-capitalize">
              {{ userData.gender.gen_description }}
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="CalendarIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('user.userViewUserInfoCard.birthday') }}</span>
            </th>
            <td class="pb-50 text-capitalize">
              <span v-if="userData.birthday"> {{ formatDate(userData.birthday) }} </span>
              <span v-else> - </span>
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="FileTextIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('user.userViewUserInfoCard.cpf') }}</span>
            </th>
            <td 
            class="pb-50"
              v-if="userData.cpf"
            >
              {{ userData.cpf | VMask('###.###.###-##') }}
            </td>
            <td v-else> 
              -
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="ExternalLinkIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('user.userViewUserInfoCard.link') }}</span>
            </th>
            <td class="pb-50">
              <a :href="userData.link" target="_blank">{{ userData.link }}</a>
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="UserPlusIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('user.userViewUserInfoCard.created') }}</span>
            </th>
            <td class="pb-50">
              {{ formatDateTime(userData.created_at) }}
            </td>
          </tr>
        </table>
      </b-col>
    </b-row>
    
    <!-- Form para editar dados do usuário -->
    <b-modal
      id="modal-edit-user-information"
      :title="$t('user.userViewUserInfoCard.editUserInformation')"
      hide-footer
      size="sm"
    >
      <user-modal-edit-user-handler
        :user="userData"
        :clear-user-data="clearUserData"
        @update-user-information="updateUserInformation"
        @hide-modal="hideModal"
      />
    </b-modal>
  </b-card>
</template>

<script>
import {
  BCard, BButton, BAvatar, BRow, BCol, BBadge,
} from 'bootstrap-vue'
import {
  ref
} from '@vue/composition-api'
import { avatarText, formatDate, formatDateTime } from '@core/utils/filter'
import store from '@/store'
import userModalEditUserHandler from './user-modal-edit-user-handler/UserModalEditUserHandler.vue'
import useUsersList from '../useUsersList'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { VueMaskFilter } from 'v-mask'
import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)

export default {
  components: {
    BCard, 
    BButton, 
    BRow, 
    BCol, 
    BAvatar, 
    BBadge,

    //Modal
    userModalEditUserHandler,
  },
  props: {
    userData: {
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
      console.log(modalName)
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
  },
  setup(props, {emit}) {
    const { resolveUserRoleVariant } = useUsersList()

    //Toast Notification
    const toast = useToast()

    const blankContact = {
    }
    const contactData = ref(JSON.parse(JSON.stringify(blankContact)))
    //Limpa os dados do popup
    const clearUserData = () => {
      contactData.value = JSON.parse(JSON.stringify(blankContact))
    }

    //Atualiza os dados do contato
    const updateUserInformation = userData => {
      store.dispatch('app-user/updateUserInformation', { userData: userData })
        .then(() => {
          emit('get-user')  

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

    //Cria um atributo na prop com o número do telefone sem o DDI
    const numberWithoutDdi = () => {
      props.userData.phoneFormatted = props.userData.phone.slice(2)
    }

    return {
      avatarText,
      resolveUserRoleVariant,

      clearUserData,
      updateUserInformation,
      numberWithoutDdi,

      formatDate, 
      formatDateTime,
    }
  },
}
</script>

<style>

</style>
