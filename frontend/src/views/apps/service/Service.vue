<template>
  <!-- Need to add height inherit because Vue 2 don't support multiple root ele -->
  <div style="height: inherit">
    <b-card 
      no-body
      class="p-1"
    >
      <span
        class="align-self-end"
      >
        <b-button
          variant="primary"
          class="btn-icon mb-1"
          v-b-tooltip.hover.v-secondary
          v-b-modal.modal-add-service
          :title="$t('services.add')"
        >
          <feather-icon 
            icon="PlusIcon" 
            size="16"
          />
        </b-button>

        <b-button
          variant="dark"
          class="btn-icon mb-1"
          v-b-tooltip.hover.v-secondary
          v-b-modal.modal-settings
          :title="$t('services.settings')"
        >
          <feather-icon 
            icon="SettingsIcon" 
            size="16"
          />
        </b-button>
      </span>
      <b-card-header class="pb-50">
        <h5>
          {{ $t('contacts.contactsList.filters') }}
        </h5>
      </b-card-header>
      <b-card-body>
        <b-row>
          <b-col
            lg="12"
            md="12"
          >
            <div class="d-flex align-items-center justify-content-end">
              <b-form-input
                v-model="searchQuery"
                class="d-inline-block mr-1"
                :placeholder="$t('contacts.contactsList.search')"
              />
            </div>
          </b-col>
        </b-row>
        <b-row
          class="mt-1"
        >
          <b-col
            lg="3"
            md="3"
          >
            <b-form-group
              :label="$t('services.departments')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="departmentFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="departments"
                :getOptionLabel="departments => departments.dep_name"
                :reduce="departments => departments.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="3"
            md="3"
          >
            <b-form-group
              :label="$t('services.users')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="userFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="users"
                :getOptionLabel="users => users.name"
                :reduce="users => users.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="3"
            md="3"
          >
            <b-form-group
              :label="$t('services.channels')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="channelFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="channels"
                :getOptionLabel="channels => channels.cha_name"
                :reduce="channels => channels.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="2"
            md="2"
          >
            <b-form-group
              :label="$t('services.origin')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="originFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="optionCampaign"
                :getOptionLabel="optionCampaign => optionCampaign.label"
                :reduce="optionCampaign => optionCampaign.code"
                transition=""
              />
            </b-form-group>
          </b-col>
        </b-row>
        <b-row
          class="mt-1"
        >
          <b-col
            lg="2"
            md="2"
          >
            <b-button
            variant="primary"
            @click="clearFilter"
          >
            <span class="text-nowrap">{{ $t('services.clear') }}</span>
          </b-button>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>
    <b-row>
      <!-- Autoatendimentos -->
      <b-col
        lg="4"
        md="6"
      >
        <card-services-in-progress 
          :title="$t('services.selfServices')"
          :color="'primary'"
          :services-data="selfServices.selfServices? selfServices.selfServices : [] "
          :total-services="selfServices.totalSelfServices? selfServices.totalSelfServices : 0 "
          :hidden-button-service="hiddenButtonSelfService"
          :base-url-storage="baseUrlStorage"
          @load-self-services="fetchSelfServicesProgress"
          @transfer-service="transferService"
          @close-service="closeService"
          :situationService="1"
        />
      </b-col>
      <!-- Atendimentos pendentes -->
      <b-col
        lg="4"
        md="6"
      >
        <card-services-in-progress 
          :title="$t('services.pendingService')"
          :color="'warning'"
          :services-data="pendingServices.pendingServices? pendingServices.pendingServices : []"
          :total-services="pendingServices.totalPendingServices? pendingServices.totalPendingServices : 0 "
          :hidden-button-service="hiddenButtonPendingService"
          :base-url-storage="baseUrlStorage"
          @load-pending-services="fetchPendingServicesProgress"
          @transfer-service="transferService"
          @close-service="closeService"
          :situationService="2"
        />
      </b-col>
      <!-- Atendimentos ativos -->
      <b-col
        lg="4"
        md="6"
      >
        <card-services-in-progress 
          :title="$t('services.inAttendance')"
          :color="'success'"
          :services-data="activeServices.activeServices? activeServices.activeServices : []"
          :total-services="activeServices.totalActiveServices? activeServices.totalActiveServices : 0 "
          :hidden-button-service="hiddenButtonActiveService"
          :base-url-storage="baseUrlStorage"
          @load-active-services="fetchActiveServicesProgress"
          @transfer-service="transferService"
          @close-service="closeService"
          :situationService="3"
        />
      </b-col>
    </b-row>

    <b-modal
      id="modal-add-service"
      :title="$t('services.serviceModalAddServiceHandler.addService')"
      hide-footer
      size="sm"
    >
      <service-modal-add-service-handler
        :service="serviceData"
        :clear-service-data="clearServiceData"
        @add-service="addService"
        @hide-modal="hideModal"
      />
    </b-modal>

    <!-- Contêm as configurações dos atendimentos-->
    <b-modal
      id="modal-settings"
      :title="$t('campaign.cardAdvanceProfile.addSettings')"
      hide-footer
      size="xl"
    >
      <!-- select 2 demo -->
      <service-modal-settings-handler
        @hide-modal="hideModal"
      />
    </b-modal>
  </div>
</template>

<script>
import store from '@/store'
import {
  ref, onUnmounted, watch,
} from '@vue/composition-api'
import { BRow, BCol, BCard, BCardHeader, BCardBody, BFormInput, BFormGroup, BButton, VBTooltip,} from 'bootstrap-vue'
import { formatDate, avatarText } from '@core/utils/filter'
import serviceStoreModule from './serviceStoreModule'
import CardServicesInProgress from './CardServicesInProgress.vue'
import ServiceModalSettingsHandler from './service-modal-settings-handler/ServiceModalSettingsHandler.vue'
import ServiceModalAddServiceHandler from './service-modal-add-service-handler/ServiceModalAddServiceHandler.vue'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import axios from '@axios'
import vSelect from 'vue-select'
import Vue from 'vue'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BCardHeader,
    BCardBody,
    BFormInput,
    BFormGroup,
    BButton,

    CardServicesInProgress,
    vSelect,
    ServiceModalSettingsHandler,
    ServiceModalAddServiceHandler
    
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    return {
      blocId: null,
      departments: [],
      users: [],
      channels: [],
      baseUrlStorage: '',
      optionCampaign: [{label: 'Campanha', code: 'C'}, {label: 'Sem Campanha', code: 'S'}, {label: 'URA Reversa', code: 'U'}],
    }
  },
  methods: {
    setBlocId(id) {
      this.blocId = id
    },
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },

    // Metodo anterior para esconder a modal
    // hideModal(bool) {
    //   //Fecha o Modal
    //   this.$root.$emit('bv::hide::modal', 'modal-action', '#btnShow')
    // },
  },
  created() { 
    //Traz os departamentos cadastrados
    axios
      .get('/api/management/department/fetch-departments/')
      .then(response => {
        //console.log(response.data)
        this.departments = response.data.departments
      })
    
    //Traz os usuários cadastrados
    axios
      .get('/api/management/user/get-operators/')
      .then(response => {
        //console.log(response.data)
        this.users = response.data.users
      })
    
    //Traz os canais cadastrados
    axios
      .get('/api/management/channel/fetch-channels/')
      .then(response => {
        //console.log(response.data)
        this.channels = response.data.channels
      })

    axios
      .get('/api/chat/get-base-url-storage/')
      .then(response => {
        //console.log(response.data)
        this.baseUrlStorage = response.data.baseUrlStorage
      })
  },
  setup() {
    const SERVICE_APP_STORE_MODULE_NAME = 'app-service'

    // Register module
    if (!store.hasModule(SERVICE_APP_STORE_MODULE_NAME)) store.registerModule(SERVICE_APP_STORE_MODULE_NAME, serviceStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(SERVICE_APP_STORE_MODULE_NAME)) store.unregisterModule(SERVICE_APP_STORE_MODULE_NAME)
    })

    //Toast Notification
    const toast = useToast()

    const searchQuery = ref('')
    const departmentFilter = ref('')
    const userFilter = ref('')
    const channelFilter = ref('')
    const originFilter = ref('')

    const servicesInProgress = ref([])
    
    const selfServices = ref([])
    const pendingServices = ref([])
    const activeServices = ref([])
    
    const hiddenButtonSelfService = ref(false)
    const hiddenButtonPendingService = ref(false)
    const hiddenButtonActiveService = ref(false)

    const offsetSelfService = ref(0)
    const offsetPendingService = ref(0)
    const offsetActiveService = ref(0)

    const skipSelfService = ref(0)
    const skipPendingService = ref(0)
    const skipActiveService = ref(0)

    const searchServicesActive = ref(false)

    const blankService = {
      channel: '',
      contacts: [],
      department: '',
      user: ''
    }

    const serviceData = ref(JSON.parse(JSON.stringify(blankService)))

    //Limpa os dados do popup
    const clearServiceData = () => {
      serviceData.value = JSON.parse(JSON.stringify(blankService))
    }

    //watch([currentPage, perPage, searchQuery, roleFilter, planFilter, statusFilter], () => {
    watch([searchQuery, departmentFilter, userFilter, channelFilter, originFilter], () => {
      if( ((searchQuery.value != '' && searchQuery.value != null) && searchQuery.value.length > 3) || (departmentFilter.value != '' && departmentFilter.value != null) 
        || (userFilter.value != '' && userFilter.value != null) || (channelFilter.value != '' && channelFilter.value != null) || (originFilter.value != '' && originFilter.value != null)) {
        
        fetchServicesInProgress()

        //Esconde os botão de "ver mais" enquanto o usuário estiver filtrando os atendimentos 
        hiddenButtonSelfService.value = true
        hiddenButtonPendingService.value = true
        hiddenButtonActiveService.value = true

        //Guarda como algum filtro foi aplicado
        searchServicesActive.value = true
        
      }
      else if((searchQuery.value == '' || searchQuery.value == null) && (departmentFilter.value == '' || departmentFilter.value == null) 
              && (userFilter.value == '' || userFilter.value == null) && (channelFilter.value == '' || channelFilter.value == null) && (originFilter.value == '' || originFilter.value == null)) {
        fetchSelfServicesProgress( {offset: offsetSelfService.value, skip: false} )
        fetchPendingServicesProgress( {offset: offsetPendingService.value, skip: false} )
        fetchActiveServicesProgress( {offset: offsetActiveService.value, skip: false} )

        //Guarda como nenhum filtro foi aplicado
        searchServicesActive.value = false
      }
    })

    const addService = (serviceData) => {
      store.dispatch('app-service/addService', serviceData)
        .then(() => {
          toast({
              component: ToastificationContent,
              props: {
                title: 'Atendimento criado com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Erro ao criar atendimento!',
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          })
        })
    }

    const fetchServicesInProgress = (ctx, callback) => {
      Vue.prototype.$isLoading(true)
      store
        .dispatch('app-service/fetchServicesInProgress', {
          q: searchQuery.value,
          department: departmentFilter.value,
          user: userFilter.value,
          channel: channelFilter.value,
          origin: originFilter.value,
        })
        .then(response => {
          selfServices.value.selfServices = []
          selfServices.value.selfServices = response.data.selfServices

          pendingServices.value.pendingServices = response.data.pendingServices

          activeServices.value.activeServices = response.data.activeServices
          //contacts.value = response.data.contacts
          //console.log(response.data)
          //totalUsers.value = response.data.total
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Error fetching contacts list',
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          })
        })
        .finally(() => {
          //Esconde a loading screen
          Vue.prototype.$isLoading(false) 
        })
    }

    //const variationSelfService = ref(0)

    const fetchSelfServicesProgress = ( {offset: offset, skip: skip} ) => {
      offsetSelfService.value = offset
      store.dispatch('app-service/fetchSelfServicesProgress', { offset: offset, skip: skip} )
        .then(response => {
          //Se existem atendimentos para ser exibidos
          if(response.data.selfServices.length > 0) {
            //Se um novo atendimento chegou no painel de atendimentos
            if(response.data.skip == null) {
              //Calcula a diferença entre a quantidade de autoatendimentos atual menos a quantidade de autoatendimentos de quando a página foi carregada
              //skipSelfService.value = response.data.selfServices.length - variationSelfService.value
              //Esvazia o array de serviços
              selfServices.value.selfServices = []
            } //Se o botão de "mostrar mais" foi clicado
            else {
              skipSelfService.value = 0
            }

            if(offset == 0) {
              selfServices.value = response.data
              //variationSelfService.value = response.data.selfServices.length
            }
            else {
              
              selfServices.value.totalSelfServices = response.data.totalSelfServices

              //Insere cada novo atendimento carregado no array de serviços
              response.data.selfServices.map(function(service, key) {
                selfServices.value.selfServices.push(service)
              });
            }

            //Se já foram exibidos todos os chats em autoatendimento (total de autoatendimento é igual ao total de autoatendimentos carregados)
            if(response.data.totalSelfServices == selfServices.value.selfServices.length) {
              //Esconde o botão que carrega mais atendimentos
              hiddenButtonSelfService.value = true  
            } //Exibe o botão
            else {
              hiddenButtonSelfService.value = false
            }
          }
          else {
            //Esconde o botão que carrega mais atendimentos
            hiddenButtonSelfService.value = true
            selfServices.value.selfServices = []
          }
        })
    }
    fetchSelfServicesProgress( {offset: 0, skip: true} )

    //###########################################################################################

    const fetchPendingServicesProgress = ( {offset: offset, skip: skip} ) => {
      offsetPendingService.value = offset
      store.dispatch('app-service/fetchPendingServicesProgress', { offset: offset, skip: skip} )
        .then(response => {
          //Se existem atendimentos para ser exibidos
          if(response.data.pendingServices.length > 0) {

            //Se um novo atendimento chegou no painel de atendimentos
            if(response.data.skip == null) {
              //Acrescenta em 1 a quantidade de contatos que serão pulados da próxima vez que o botão "Ver Mais" for clicado
              //skipPendingService.value++
              //Esvazia o array de serviços
              pendingServices.value.pendingServices = []
            }
            else {
              skipPendingService.value = 0
            }

            //Se for os primeirios atendimentos carregados
            if(offset == 0) {
              pendingServices.value = response.data
            }
            else {
              pendingServices.value.totalPendingServices = response.data.totalPendingServices

              //Insere cada novo atendimento carregado no array de serviços
              response.data.pendingServices.map(function(service, key) {
                pendingServices.value.pendingServices.push(service)
              });
            }

            //Se já foram exibidos todos os chats em autoatendimento (total de autoatendimento é igual ao total de autoatendimentos carregados)
            if(response.data.totalPendingServices == pendingServices.value.pendingServices.length) {
              //Esconde o botão que carrega mais atendimentos
              hiddenButtonPendingService.value = true  
            }
            else { //Exibe o botão
              hiddenButtonPendingService.value = false
            }
          }
          else {
            //Esconde o botão que carrega mais atendimentos
            hiddenButtonPendingService.value = true
          }
        })
    }
    fetchPendingServicesProgress( {offset: 0, skip: true} )

    //##############################################################################################

    const fetchActiveServicesProgress = ( {offset: offset, skip: skip} ) => {
      offsetActiveService.value = offset
      store.dispatch('app-service/fetchActiveServicesProgress', { offset: offset, skip: skip, extraSkipValue: skipActiveService.value} )
        .then(response => {
          //Se existem atendimentos para ser exibidos
          if(response.data.activeServices.length > 0) {
            //Se um novo atendimento chegou no painel de atendimentos
              if(response.data.skip == null) {
                //Acrescenta em 1 a quantidade de contatos que serão pulados da próxima vez que o botão "Ver Mais" for clicado
                skipActiveService.value++
                //Esvazia o array de serviços
                activeServices.value.activeServices = []
              }
              else {
                skipActiveService.value = 0
              }

            //Se forem os primeiros atendimentos carregados
            if(offset == 0) {
              activeServices.value = response.data
            }
            else {
              activeServices.value.totalActiveServices = response.data.totalActiveServices

              //Insere cada novo atendimento carregado no array de serviços
              response.data.activeServices.map(function(service, key) {
                activeServices.value.activeServices.push(service)
              });
            }

            //Se já foram exibidos todos os chats em autoatendimento (total de autoatendimento é igual ao total de autoatendimentos carregados)
            if(response.data.totalActiveServices == activeServices.value.activeServices.length) {
              //Esconde o botão que carrega mais atendimentos
              hiddenButtonActiveService.value = true  
            } //Exibe o botão de mais atendimentos
            else {
              hiddenButtonActiveService.value = false
            }
          }
          else {
            //Esconde o botão que carrega mais atendimentos
            hiddenButtonActiveService.value = true
          }
        })
    }
    fetchActiveServicesProgress({offset: 0, skip: true})

    //Pega os dados do usuário no localStorage
    const userdata = JSON.parse(localStorage.getItem('userData'))
    //Escuta as mudanças de status dos atendimentos (autoatendimento, pendentes e em atendimento)
    Echo.private('user.'+userdata.id).listen('.UpdateServiceProgress', () => {
      //Se nenhum filtro foi aplicado pelo usuário
      if(searchServicesActive.value == false) {
        //Atualiza a tela de atendimentos
        fetchSelfServicesProgress( {offset: offsetSelfService.value, skip: false} )
        fetchPendingServicesProgress( {offset: offsetPendingService.value, skip: false} )
        fetchActiveServicesProgress( {offset: offsetActiveService.value, skip: false} )
      }
    })

    //Transfere um atendimento para um departamento ou usuário
    const transferService = (transferData) => {
      //console.log(transferData)
    store.dispatch('app-service/transferService', { transferData: transferData })
      .then(() => {
        //Se algum filtro foi aplicado
        if(searchServicesActive.value == true) {
          fetchServicesInProgress()
        }
        else {
          fetchSelfServicesProgress( {offset: offsetSelfService.value, skip: false} )
          fetchPendingServicesProgress( {offset: offsetPendingService.value, skip: false} )
          fetchActiveServicesProgress( {offset: offsetActiveService.value, skip: false} )
        }
        toast({
            component: ToastificationContent,
            props: {
              title: 'Atendimento transferido com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
      })
    }

    //Fecha o atendimento em questão
    const closeService = ({ chatId, sendEvaluation }) => {
      store.dispatch('app-service/closeService', { chatId: chatId, sendEvaluation: sendEvaluation })
        .then(() => {
          //Atualiza a tela de atendimentos
          //fetchServicesInProgress()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Atendimento fechado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const clearFilter = () => {
      searchQuery.value = ''
      departmentFilter.value = ''
      userFilter.value = ''
      channelFilter.value = ''
      originFilter.value = ''
    }

    return {
      servicesInProgress,
      fetchServicesInProgress,
      transferService,
      closeService,
      selfServices,
      pendingServices,
      activeServices,

      fetchSelfServicesProgress,
      fetchPendingServicesProgress,
      fetchActiveServicesProgress,

      hiddenButtonSelfService,
      hiddenButtonPendingService,
      hiddenButtonActiveService,

      searchQuery,
      departmentFilter,
      userFilter,
      channelFilter,
      originFilter,

      clearFilter,

      clearServiceData,
      serviceData,
      addService

    }
  },
}
</script>

<style lang="scss" scoped>
.draggable-task-handle {
position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    visibility: hidden;
    cursor: move;

    .todo-task-list .todo-item:hover & {
      visibility: visible;
    }
}

.dropdown-item-action  {
  margin: 0% !important; 
  padding: 0% !important
}
</style>

<style lang="scss">

</style>
