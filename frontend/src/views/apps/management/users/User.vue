<template>

  <div>
    <b-card 
      no-body
      class="p-1"
    >
      <b-card-header class="pb-50">
        <h5>
          {{ $t('user.filter') }}
        </h5>
      </b-card-header>
      <b-card-body>
        <div class="d-flex align-items-center justify-content-end">
          <b-form-input
            v-model="searchQuery"
            class="d-inline-block mr-1"
            :placeholder="$t('user.search')"
          />
          <b-button
            variant="dark"
            v-b-modal.modal-add-user
          >
            <span class="text-nowrap">{{ $t('user.addUser') }}</span>
          </b-button>
        </div>
      </b-card-body>
    </b-card>

    <!-- Table Container Card -->
    <b-card
      no-body
      class="mb-0"
    >

      <div class="m-2">

        <!-- Table Top -->
        <b-row>

          <!-- Per Page -->
          <b-col
            cols="6"
            md="6"
            class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
          >
            <label>{{ $t('user.show') }}</label>
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>{{ $t('user.entries') }}</label>
          </b-col>

          <!-- Search -->
          <b-col
            cols="12"
            md="6"
          >
            <div
              class="pt-1 pr-2 text-right"
            >
              <b-badge variant="light-primary">
                {{ totalUsers }} usuários ativos de {{ totalCurrentUserQuota }} contratados para o mês atual
              </b-badge>
            </div>
          </b-col>
        </b-row>
      </div>
      <!-- Contatos cadastrados -->
      <!--
      <b-row
        class="p-1"
      >
        <b-col
          lg="3"
          md="5"
          v-for="contact in contacts"
          :key="contact.id"
          ref="refUserListTable"
        >
          <card-advance-profile 
            :contact="contact"
          />
        </b-col>
      </b-row>
      -->

      
      <b-table
        ref="refUserListTable"
        class="position-relative"
        :items="fetchUsers"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        :empty-text="$t('user.noUserFound')"
        :sort-desc.sync="isSortDirDesc"
      >

        
        <template #cell(name)="data">
          <b-media vertical-align="center">
            <template #aside>
              <b-avatar
                size="32"
                :src="data.item.avatar"
                :text="avatarText(data.item.name)"
                :variant="'light-'+ resolveUserRoleVariant(data.item.roles[0]? data.item.roles[0].id : null)"
                :to="{ name: 'apps-management-users-view', params: { id: data.item.id } }"
              />
            </template>
            <b-link
              :to="{ name: 'apps-management-users-view', params: { id: data.item.id } }"
              class="font-weight-bold d-block text-nowrap"
            >
              {{ data.item.name }}
            </b-link>
            <small class="text-muted">{{ data.item.email }}</small>
          </b-media>
        </template>

        <!-- Perfis -->
        <template #cell(roles)="data">
          <div class="text-nowrap"
            v-for="role in data.item.roles"
            :key="role.id"
          >
            <span class="align-text-top">{{ role.rol_name }}</span>
          </div>
        </template>
        
        <!-- Departamentos -->
        <template #cell(departments)="data">
          <div class="text-nowrap"
            v-for="department in data.item.departments"
            :key="department.id"
          >
            <span class="align-text-top">{{ department.dep_name }}</span>
          </div>
        </template>

        <!--
        <template #cell(status)="data">
          <b-badge
            pill
            :variant="`light-${resolveDepartmentStatusVariant(data.item.status)}`"
            class="text-capitalize"
          >
            {{ data.item.status == 'A'? $t('user.active') : $t('user.inactive') }}
          </b-badge>
        </template>
        -->

        <template #cell(situation_user_id)="data">
          <b-badge
            pill
            :variant="`light-${resolveDepartmentSituationVariant(data.item.situation_user_id)}`"
            class="text-capitalize"
          >
            {{ data.item.situation_user_id == 1? 'Online' : 'Offline' }}
          </b-badge>
        </template>

        
        <template #cell(actions)="data">
          <b-dropdown
            variant="link"
            no-caret
            :right="$store.state.appConfig.isRTL"
          >

            <template #button-content>
              <feather-icon
                icon="MoreVerticalIcon"
                size="16"
                class="align-middle text-body"
              />
            </template>

            <b-dropdown-item 
              :to="{ name: 'apps-management-users-view', params: { id: data.item.id } }"
            >
              <feather-icon icon="FileTextIcon" />
              <span class="align-middle ml-50">{{ $t('user.details') }}</span>
            </b-dropdown-item>
            <b-dropdown-item
              @click="removeUser(data.item.id)"
            >
              <feather-icon icon="TrashIcon" />
              <span class="align-middle ml-50">{{ $t('user.delete') }}</span>
            </b-dropdown-item>
          </b-dropdown>
          <!-- Form para cadastro de um novo canal -->
          <b-modal
            :id="'modal-edit-department-'+data.item.id"
            :title="$t('user.editDepartment')"
            hide-footer
            size="lg"
          >
            <department-modal-handler
              :user="data.item"
              :clear-contact-data="clearDepartmentData"
              @add-user="addUser"
              @update-department="updateDepartment"
              @hide-modal="hideModal"
            />
          </b-modal>
        </template>
      </b-table>
      



      <div class="mx-2 mb-2">
        <b-row>

          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-start"
          >
            <span class="text-muted">{{ $t('user.showing') }} {{ dataMeta.from }} {{ $t('user.to') }} {{ dataMeta.to }} {{ $t('user.of') }} {{ dataMeta.of }} {{ $t('user.entries') }}</span>
          </b-col>
          <!-- Pagination -->
          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-end"
          >

            <b-pagination
              v-model="currentPage"
              :total-rows="totalUsers"
              :per-page="perPage"
              first-number
              last-number
              class="mb-0 mt-1 mt-sm-0"
              prev-class="prev-item"
              next-class="next-item"
            >
              <template #prev-text>
                <feather-icon
                  icon="ChevronLeftIcon"
                  size="18"
                />
              </template>
              <template #next-text>
                <feather-icon
                  icon="ChevronRightIcon"
                  size="18"
                />
              </template>
            </b-pagination>
          </b-col>
        </b-row>
      </div>
    </b-card>
    <!-- Form para cadastro de um novo usuário -->
    <b-modal
      id="modal-add-user"
      :title="$t('user.addUser')"
      hide-footer
      size="lg"
    >
      <user-modal-handler
        :user="userData"
        :clear-contact-data="clearDepartmentData"
        :loading="loading"
        @get-address-user="getAddressUser"
        @add-user="addUser"
        @hide-modal="hideModal"
      />
    </b-modal>
  </div>
</template>

<script>
import {
  BCard, BRow, BCol, BFormInput, BButton, BTable, BMedia, BAvatar, BLink,
  BBadge, BDropdown, BDropdownItem, BPagination, BCardBody, BCardHeader,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import store from '@/store'
import { ref, onUnmounted } from '@vue/composition-api'
import { avatarText } from '@core/utils/filter'
import useUsersList from './useUsersList'
import userStoreModule from './userStoreModule'
import UserModalHandler from './user-modal-handler/UserModalHandler.vue'
import Vue from 'vue'
import Swal from 'sweetalert2'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BCard,
    BRow,
    BCol,
    BFormInput,
    BButton,
    BTable,
    BMedia,
    BAvatar,
    BLink,
    BBadge,
    BDropdown,
    BDropdownItem,
    BPagination,
    BCardBody,
    BCardHeader,

    vSelect,
    UserModalHandler,
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
  setup() {
    const USER_APP_STORE_MODULE_NAME = 'app-user'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()

    const blankUser = {
      cpf: '',
      name: '',
      birthday: '',
      gender: '',
      email: '',
      phone_number: '',
      cep: '',
      street: '',
      number: '',
      address_complement: '',
      district: '',
      city: '',
      state: '',
      country: '',
      department: '',
      role: '',
      username: '',
      password: '',
      confirm_password: '',
    }
    const userData = ref(JSON.parse(JSON.stringify(blankUser)))
    //Limpa os dados do popup
    const clearDepartmentData = () => {
      userData.value = JSON.parse(JSON.stringify(blankUser))
    }

    //Adiciona um departmento
    const addUser = userData => {

      //Se adição desse usuário for gerar cobrança
      if(totalUsers.value == totalCurrentUserQuota.value) {
        Swal.fire({
          title: 'Cobrança pelo Novo Usuário',
          text: "A adição desse usuário irá gerar uma nova cobrança. Tem certeza que deseja continuar?",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sim',
          customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-danger ml-1',
          },
          buttonsStyling: false,
        }).then(result => {
          //Caso o usuário queira que o contato avalie o atendimento 
          if (result.value) {
            Vue.prototype.$isLoading(true)
            store.dispatch('app-user/addUser', userData)
            .then(response => {

              console.log('response adduser')
              console.log(response)
              refetchData()

              if(!response.data.error) {
                toast({
                  component: ToastificationContent,
                  props: {
                    title: 'Usuário adicionado com sucesso!',
                    icon: 'CheckIcon',
                    variant: 'success',
                  },
                })
              } //Se existe algum erro ao cadastrar o usuário
              else {
                toast({
                  component: ToastificationContent,
                  props: {
                    title: 'Erro ao adicionar o usuário',
                    text: response.data.error,
                    icon: 'AlertTriangleIcon',
                    variant: 'danger',
                  },
                },
                {
                  timeout: 8000,
                })
              }
              
            })
            .finally(() => {
              //Esconde a loading screen
              Vue.prototype.$isLoading(false) 
            })
          } 
        })
      }
      else {
        Vue.prototype.$isLoading(true)
        store.dispatch('app-user/addUser', userData)
        .then(response => {  
          refetchData()
          if(!response.data.error) {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Usuário adicionado com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          } //Se existe algum erro ao cadastrar o usuário
          else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Erro ao adicionar o usuário',
                text: response.data.error,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            },
            {
              timeout: 8000,
            })
          }
        })
        .finally(() => {
          //Esconde a loading screen
          Vue.prototype.$isLoading(false) 
        })
      }
    }

    //Atualiza os dados do departamento
    const updateDepartment = departmentData => {
      store.dispatch('app-user/updateDepartment', { departmentData: departmentData })
        .then(() => {  
          refetchData()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Departmento atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const removeUser = userId => {
      Swal.fire({
        title: 'Remover Usuário',
        text: "Você tem certeza que deseja remover esse usuário?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        customClass: {
          confirmButton: 'btn btn-primary',
          cancelButton: 'btn btn-outline-danger ml-1',
        },
        buttonsStyling: false,
      }).then(result => {
            //Caso o usuário queira que o contato avalie o atendimento 
            if (result.value) {
              store.dispatch('app-user/removeUser', { id: userId })
                .then(() => {
                  refetchData()
                  toast({
                    component: ToastificationContent,
                    props: {
                      title: 'Usuário removido com sucesso!',
                      icon: 'CheckIcon',
                      variant: 'success',
                    },
                  })
                })
            } 
          })
    }

    const loading = ref(false)
    
    //Busca o endereço com base no CEP digitado
    const getAddressUser = cep => {
      //Se foi digitado todos os caracteres do CEP
      if(cep.length == 9) {
        //Mostra o spinner
        loading.value = true

        store.dispatch('app-user/getAddressUser', { cep: cep })
        .then(response => {
          //Se não houve erro ao buscar o endereço
          if(!response.data.error) { 
            userData.value.street = response.data.address.logradouro 
            userData.value.district = response.data.address.bairro 
            //userData.value.address_complement = response.data.complemento 
            userData.value.city = response.data.address.localidade 
            userData.value.state = response.data.state 
            userData.value.country = response.data.country

          }
          else {
            userData.value.street = null 
            userData.value.number = null 
            userData.value.district = null 
            //addressLocal.value.address_complement = response.data.complemento 
            userData.value.city = null 
            userData.value.state = null 
            userData.value.country = null
            toast({
              component: ToastificationContent,
              props: {
                title: response.data.error,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            })
          }
           
          //console.log(response.data)

          //Esconde o spinner
          loading.value = false
        })
        .catch(error => {
        })
      }
    }

    const isAddNewUserSidebarActive = ref(false)

    const statusOptions = [
      { label: 'Pending', value: 'pending' },
      { label: 'Active', value: 'active' },
      { label: 'Inactive', value: 'inactive' },
    ]

    const {
      fetchUsers,
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
      totalCurrentUserQuota,

      // UI
      resolveDepartmentStatusVariant,
      resolveUserRoleVariant,
      resolveDepartmentSituationVariant,

    } = useUsersList()

    fetchUsers()
    
    return {

      // Sidebar
      isAddNewUserSidebarActive,

      fetchUsers,
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
      clearDepartmentData,
      userData,
      addUser,
      updateDepartment,
      removeUser,
      getAddressUser,
      totalCurrentUserQuota,

      // Filter
      avatarText,

      // UI
      resolveDepartmentStatusVariant,
      resolveUserRoleVariant,
      resolveDepartmentSituationVariant,

      statusOptions,

      //Spinner
      loading,

    }
  },
}
</script>

<style lang="scss" scoped>
.per-page-selector {
  width: 90px;
}
</style>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
