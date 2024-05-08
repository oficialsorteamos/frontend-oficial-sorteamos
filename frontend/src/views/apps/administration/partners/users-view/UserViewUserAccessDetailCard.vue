<template>
  <b-card
    class="card-transaction"
    no-body
  >
    <b-card-header>
      <b-card-title>{{ $t('user.userViewUserAccessDetailCard.accessDetailsAccount') }}</b-card-title>
      <feather-icon
        icon="EditIcon"
        size="18"
        class="cursor-pointer"
        v-b-modal.modal-edit-access-detail
      />
    </b-card-header>

    <b-card-body>
      <div
        class="transaction-item mt-3"
      >
        <b-media no-body>
          <b-media-aside>
            <b-avatar
              rounded
              size="42"
              variant="light-primary"
            >
              <feather-icon
                size="18"
                icon="HomeIcon"
              />
            </b-avatar>
          </b-media-aside>
          <b-media-body>
            <h6 class="transaction-title">
              {{ $t('user.userViewUserAccessDetailCard.departments') }}
            </h6>
            <small
              v-if="userData.roles"
            >
              <span
                v-for="(department, index) in userData.departments"
                :key="department.id"
              >
                <!-- Se houver mais de um departmento para o usuário -->
                <span v-if="userData.departments.length > 1">
                  <!-- Se for o primeiro departamento da lista -->
                  <span v-if="index == 0"> 
                    {{ department.dep_name }}, 
                  </span>
                  <span v-else> 
                    {{ department.dep_name }}
                  </span>
                </span>
                <!-- Existir apenas um departamento para o usuário -->
                <span v-else> 
                  {{ department.dep_name }}
                </span>
              </span>
            </small>
            <!-- Se não existir departmento para o usuário -->
            <small
              v-else
            >
              -
            </small>
          </b-media-body>
        </b-media>
      </div>
      <div
        class="transaction-item"
      >
        <b-media no-body>
          <b-media-aside>
            <b-avatar
              rounded
              size="42"
              variant="light-warning"
            >
              <feather-icon
                size="18"
                icon="UnlockIcon"
              />
            </b-avatar>
          </b-media-aside>
          <b-media-body>
            <h6 class="transaction-title">
              {{ $t('user.userViewUserAccessDetailCard.roles') }}
            </h6>
            <small
              v-if="userData.roles"
            >
              <span
                v-for="(role, index) in userData.roles"
                :key="role.id"
              >
                <!-- Se houber mais de um perfil para o usuário -->
                <span v-if="userData.roles.length > 1">
                  <!-- Se for o primeiro perfil da lista -->
                  <span v-if="index == 0"> 
                    {{ role.rol_name }}, 
                  </span>
                  <span v-else> 
                    {{ role.rol_name }}
                  </span>
                </span>
                <!-- Existir apenas um perfil para o usuário -->
                <span v-else> 
                  {{ role.rol_name }}
                </span>
              </span>
            </small>
            <!-- Se não existir perfil para o usuário -->
            <small
              v-else
            >
              -
            </small>
          </b-media-body>
        </b-media>
      </div>
      <div
        class="transaction-item"
      >
        <b-media no-body>
          <b-media-aside>
            <b-avatar
              rounded
              size="42"
              variant="light-danger"
            >
              <feather-icon
                size="18"
                icon="UserIcon"
              />
            </b-avatar>
          </b-media-aside>
          <b-media-body>
            <h6 class="transaction-title">
              {{ $t('user.userViewUserAccessDetailCard.username') }}
            </h6>
            <small
              v-if="userData.username"
            >
              {{userData.username}}
            </small>
            <small
              v-else
            >
              -
            </small>
          </b-media-body>
        </b-media>
      </div>
    </b-card-body>
    <!-- Form para cadastro de um novo usuário -->
    <b-modal
      id="modal-edit-access-detail"
      :title="$t('user.userViewUserAccessDetailCard.editAccessDetailsAccount')"
      hide-footer
      size="lg"
    >
      <user-modal-edit-access-detail-handler
        :user="userData"
        :clear-user-data="clearUserData"
        @update-user-access-detail-account="updateUserAccessDetailAccount"
        @hide-modal="hideModal"
      />
    </b-modal>
  </b-card>
</template>

<script>
import {
  BCard, BCardHeader, BCardTitle, BCardBody, BMediaBody, BMedia, BMediaAside, BAvatar,
} from 'bootstrap-vue'
import {
  ref
} from '@vue/composition-api'
import store from '@/store'
import UserModalEditAccessDetailHandler from './user-modal-edit-access-detail-handler/UserModalEditAccessDetailHandler.vue'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BCard,
    BCardHeader,
    BCardTitle,
    BCardBody,
    BMediaBody,
    BMedia,
    BMediaAside,
    BAvatar,

    //Modal
    UserModalEditAccessDetailHandler,
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

    //Toast Notification
    const toast = useToast()

    const blankUser = {

    }
    const contactData = ref(JSON.parse(JSON.stringify(blankUser)))
    //Limpa os dados do popup
    const clearUserData = () => {
      contactData.value = JSON.parse(JSON.stringify(blankUser))
    }

    //Atualiza os dados de acesso e detalhes da conta o usuário
    const updateUserAccessDetailAccount = userData => {
      store.dispatch('app-user/updateUserAccessDetail', { userData: userData })
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

    return {
      clearUserData,
      updateUserAccessDetailAccount,

    }
  }
}
</script>
