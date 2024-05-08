<template>
  <!-- Need to add height inherit because Vue 2 don't support multiple root ele -->
  <div style="height: inherit">
    <div
      class="body-content-overlay"
      :class="{'show': mqShallShowLeftSidebar}"
      @click="mqShallShowLeftSidebar = false"
    />
    <div class="todo-app-list">

      <!-- App Searchbar Header -->
      <!--
      <div class="app-fixed-search d-flex align-items-center">

        
        <div class="sidebar-toggle d-block d-lg-none ml-1">
          <feather-icon
            icon="MenuIcon"
            size="21"
            class="cursor-pointer"
            @click="mqShallShowLeftSidebar = true"
          />
        </div>
        -->
        <!-- Searchbar -->
        <div class="d-flex align-content-center justify-content-between w-100">
          <b-input-group class="input-group-merge">
            <b-input-group-prepend is-text>
              <feather-icon
                icon="SearchIcon"
                class="text-muted"
              />
            </b-input-group-prepend>
            <b-form-input
              :value="searchQuery"
              :placeholder="$t('chatbot.chatbotBlocsStructureHandler.searchBloc')"
              @input="updateRouteQuery"
            />
          </b-input-group>
        </div>
        
        <!-- Dropdown -->
        <!--
        <div class="dropdown">
          <b-dropdown
            variant="link"
            no-caret
            toggle-class="p-0 mr-1"
            right
          >
            <template #button-content>
              <feather-icon
                icon="MoreVerticalIcon"
                size="16"
                class="align-middle text-body"
              />
            </template>
            <b-dropdown-item @click="resetSortAndNavigate">
              Reset Sort
            </b-dropdown-item>
            <b-dropdown-item :to="{ name: $route.name, query: { ...$route.query, sort: 'title-asc' } }">
              Sort A-Z
            </b-dropdown-item>
            <b-dropdown-item :to="{ name: $route.name, query: { ...$route.query, sort: 'title-desc' } }">
              Sort Z-A
            </b-dropdown-item>
            <b-dropdown-item :to="{ name: $route.name, query: { ...$route.query, sort: 'assignee' } }">
              Sort Assignee
            </b-dropdown-item>
            <b-dropdown-item :to="{ name: $route.name, query: { ...$route.query, sort: 'due-date' } }">
              Sort Due Date
            </b-dropdown-item>
          </b-dropdown>
        </div>
      </div>
      -->
      <!-- Todo List -->
      <vue-perfect-scrollbar
        :settings="perfectScrollbarSettings"
        class="todo-task-list-wrapper list-group scroll-area"
      >
        <draggable
          v-model="blocs"
          handle=".draggable-task-handle"
          tag="ul"
          class="todo-task-list media-list"
        >
          <li
            v-for="bloc in blocs"
            :key="bloc.id"
            class="todo-item"
            :style="skin == 'dark'? 'background-color: #283046' : ''"
          >
            <div class="todo-title-wrapper">
              <h6 class="section-label" style="position: absolute;">
                <!--
                <b-badge 
                  variant="light-success" 
                  v-if="bloc.typeBlocId == 2"
                  rounded
                >
                  {{ $t('chatbot.chatbotBlocsStructureHandler.initialBloc') }}
                </b-badge>
                <b-badge 
                  variant="light-danger" 
                  v-else-if="bloc.typeBlocId == 3"
                >
                  {{ $t('chatbot.chatbotBlocsStructureHandler.finalBloc') }}
                </b-badge>
                <b-badge 
                  variant="light-warning" 
                  v-else-if="bloc.typeBlocId == 4"
                >
                  {{ $t('chatbot.chatbotBlocsStructureHandler.evaluation') }}
                </b-badge>
                
                <b-badge 
                  variant="light-secondary" 
                  v-if="!bloc.template_id"
                >
                  {{ $t('chatbot.chatbotBlocsStructureHandler.simpleText') }}
                </b-badge>
                <b-badge 
                  variant="light-primary" 
                  v-if="bloc.template_id"
                >
                  {{ $t('chatbot.chatbotBlocsStructureHandler.template') }}
                </b-badge>
                -->
                <span
                  class="bullet bullet-md bullet-success"
                  v-if="bloc.typeBlocId == 2"
                  v-b-tooltip.hover.v-secondary
                  :title="$t('chatbot.chatbotBlocsStructureHandler.initialBloc')"
                />
                <span
                  class="bullet bullet-md bullet-danger"
                  v-if="bloc.typeBlocId == 3"
                  v-b-tooltip.hover.v-secondary
                  :title="$t('chatbot.chatbotBlocsStructureHandler.finalBloc')"
                />
                <span
                  class="bullet bullet-md bullet-warning"
                  v-if="bloc.typeBlocId == 4"
                  v-b-tooltip.hover.v-secondary
                  :title="$t('chatbot.chatbotBlocsStructureHandler.evaluation')"
                />
                <span
                  class="bullet bullet-md bullet-primary"
                  v-if="bloc.typeBlocId == 1"
                  v-b-tooltip.hover.v-secondary
                  :title="$t('chatbot.chatbotBlocsStructureHandler.template')"
                />
              </h6>
              <div class="mt-2 mb-1" style="width: 85%;">
                
                <div 
                  class="text-center font-weight-bold"
                  style="font-size: 16px"
                >
                    {{ bloc.title }}
                </div>

                <span
                  v-if="bloc.quick_message_id"
                >
                  <div 
                    @click="handleBlocClick(bloc)"
                    class="cursor-pointer"
                  >
                    <quick-message-display
                      :quick-message-data="bloc.quick_message"
                      :base-url-storage="baseUrlStorage"
                    />
                  </div>
                </span>
                <span
                  v-else
                >
                  <div 
                    @click="handleBlocClick(bloc)"
                    class="cursor-pointer"
                  >
                    <template-display-message
                      :template-data="bloc"
                    />
                  </div>
                </span>
                

                
              </div>
              <!--
              <div class="todo-item-action">
                <div class="badge-wrapper mr-1">
                  <b-badge
                    v-for="tag in task.tags"
                    :key="tag"
                    pill
                    :variant="`light-${resolveTagVariant(tag)}`"
                    class="text-capitalize"
                  >
                    {{ tag }}
                  </b-badge>
                </div>
                <small class="text-nowrap text-muted mr-1">{{ formatDate(task.dueDate, { month: 'short', day: 'numeric'}) }}</small>
                <b-avatar
                  v-if="task.assignee"
                  size="32"
                  :src="task.assignee.avatar"
                  :variant="`light-${resolveAvatarVariant(task.tags)}`"
                  :text="avatarText(task.assignee.fullName)"
                />
                <b-avatar
                  v-else
                  size="32"
                  variant="light-secondary"
                >
                  <feather-icon
                    icon="UserIcon"
                    size="16"
                  />
                </b-avatar>
              </div>
              -->
              <b-card class="mt-5 w-100" style="margin-bottom: 12px">
                <div class="d-flex justify-content-between">
                  <h6 class="section-label mb-1">
                    {{ $t('chatbot.chatbotBlocsStructureHandler.actions') }}
                  </h6>
                  <!-- Se não for um bloco final -->
                  <feather-icon icon="PlusIcon" 
                    size="17"
                    class="cursor-pointer d-sm-block d-none mr-1"
                    v-if="bloc.actions.length == 1 && bloc.typeBlocId != 3 && bloc.typeBlocId != 4 && ((bloc.actions[0].action_id == 6) || (bloc.actions[0].action_id == 4 && bloc.actions[0].key == null) || (bloc.actions[0].action_id == 2 && bloc.actions[0].blo_free_key == 1)) "
                    stroke="#FF9F43"
                    v-b-tooltip.hover.v-secondary
                    :title="$t('chatbot.chatbotBlocsStructureHandler.informationAddAction')"
                  />
                  <feather-icon icon="PlusIcon" 
                    size="17"
                    class="cursor-pointer d-sm-block d-none mr-1"
                    @click="clearActionData(); setBlocId(bloc.id); openModal('modal-action-'+bloc.id)"
                    v-else-if="bloc.typeBlocId != 3 && bloc.typeBlocId != 4"
                  />
                </div>
                <b-row 
                  v-for="action in bloc.actions"
                  :key="action.id"
                >
                  <b-col
                    md="4"
                    class="margin-top-column"
                  >
                    <strong>{{ $t('chatbot.chatbotBlocsStructureHandler.key') }}: </strong> {{action.key}}
                  </b-col>
                  <b-col
                    md="7"
                    class="margin-top-column"
                  >
                    <strong>{{ $t('chatbot.chatbotBlocsStructureHandler.action') }}: </strong>
                    <!-- Se for uma ação de transferência -->
                    <span v-if="action.action_id == 1"> 
                      Transferência para o departamento de <u>{{action.department.dep_name}}</u>
                    </span>
                    <span v-if="action.action_id == 2"> 
                      Chamada do bloco de <u>{{action.destination_bloc.cha_title}}</u>
                    </span>
                    <span v-if="action.action_id == 3"> 
                      Rastreamento de pedido/produto
                    </span>
                    <span v-if="action.action_id == 4">
                      <span
                        v-if="action.key != '' && action.key != null"
                      >
                        Finalização do atendimento
                      </span>
                      <span
                        v-else
                      >
                        Finalização do atendimento sem chave
                      </span>
                      
                    </span>
                    <span v-if="action.action_id == 6"> 
                      Chamada sequencial de <u>{{action.destination_bloc.cha_title}}</u>
                    </span>
                    <span v-if="action.action_id == 7"> 
                      Transferência Igualitária através da configuração <u>{{ action.fair_distribution? action.fair_distribution.fai_name : '' }}</u>
                    </span> 
                  </b-col>
                  <b-col
                    md="1"
                    class="mb-1"
                  >
                    <span>
                      <b-dropdown
                        variant="link"
                        toggle-class="text-decoration-none"
                        no-caret
                        dropleft
                      >
                        <template v-slot:button-content>
                          <feather-icon
                            icon="MoreVerticalIcon"
                            size="16"
                            class="text-body align-middle mr-25"
                          />
                        </template>
                        <b-dropdown-item
                          class="dropdown-item-action"
                          @click="handleActionClick(action); setBlocId(action.id); openModal('modal-action-'+bloc.id)"
                        >
                          <feather-icon
                            icon="Edit2Icon"
                            
                          />
                          <span>{{ $t('chatbot.chatbotBlocsStructureHandler.edit') }}</span>
                        </b-dropdown-item>
                        <b-dropdown-item
                          class="dropdown-item-action"
                          @click="removeAction(action.id)"
                        >
                          <feather-icon
                            icon="TrashIcon"
                            class="mr-50"
                          />
                          <span>{{ $t('chatbot.chatbotBlocsStructureHandler.delete') }}</span>
                        </b-dropdown-item>
                      </b-dropdown>
                    </span>
                  </b-col>
                </b-row>
                <!-- Se não houver ações cadastradas -->
                <div
                  class="no-results"
                  :class="{'show': !bloc.actions.length}"
                >
                  <h5>{{ $t('chatbot.chatbotBlocsStructureHandler.noActionsFound') }}</h5>
                </div>
              </b-card>
              <b-modal
                :id="'modal-action-'+bloc.id"
                :title="$t('chatbot.chatbotBlocsStructureHandler.actionRegister')"
                hide-footer
              >
                <!-- select 2 demo -->
                <chatbot-modal-action-handler
                  :action="action"
                  :chatbot="chatbot"
                  :bloc="bloc"
                  :clear-action-data="clearActionData"
                  @add-action="addAction"
                  @update-action="updateAction"
                  @hide-modal="hideModal"
                />
              </b-modal>
            </div>
            
          </li>
        </draggable>
        <div
          class="no-results"
          :class="{'show': !blocs.length}"
        >
          <h5>{{ $t('chatbot.chatbotBlocsStructureHandler.noBlocsFound') }}</h5>
        </div>
      </vue-perfect-scrollbar>
    </div>

    <!-- Bloc Handler -->
    <chatbot-add-bloc-handler-sidebar
      v-model="isTaskHandlerSidebarActive"
      :chatbot="chatbot"
      :bloc="bloc"
      :blocs="blocs"
      :clear-bloc-data="clearBlocData"
      :show-first-bloc-checkbox="showFirstBlocCheckbox"
      :base-url-storage="baseUrlStorage"
      @remove-bloc="removeBloc"
      @add-bloc="addBloc"
      @update-bloc="updateBloc"
    />

    <!-- Sidebar -->
    <portal to="content-renderer-sidebar-left">
      <todo-left-sidebar
        :task-tags="blocTags"
        :is-task-handler-sidebar-active.sync="isTaskHandlerSidebarActive"
        :class="{'show': mqShallShowLeftSidebar}"
        @set-first-bloc="setFirstBloc"
      />
    </portal>
  </div>
</template>

<script>
import store from '@/store'
import {
  ref, watch, computed, onUnmounted,
} from '@vue/composition-api'
import {
  BFormInput, BInputGroup, BInputGroupPrepend, BDropdown, BDropdownItem,
  BFormCheckbox, BBadge, BAvatar, BCard, BTable, BRow, BCol, BModal, BForm, VBTooltip,
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import draggable from 'vuedraggable'
import axios from '@axios'
import { formatDate, avatarText } from '@core/utils/filter'
import { useRouter } from '@core/utils/utils'
import useAppConfig from '@core/app-config/useAppConfig'
import { useResponsiveAppLeftSidebarVisibility } from '@core/comp-functions/ui/app'
import TodoLeftSidebar from '../../TodoLeftSidebar.vue'
import todoStoreModule from '../../chatbotStoreModule'
import ChatbotAddBlocHandlerSidebar from './chatbot-add-bloc-handler/ChatbotAddBlocHandlerSidebar.vue'
import ChatbotModalActionHandler from '../../ChatbotModalActionHandler.vue'
import TemplateDisplayMessage from '../../../management/templates/TemplateDisplayMessage.vue'
import QuickMessageDisplay from '../../../management/quick-messages/QuickMessageDisplay.vue'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BFormInput,
    BInputGroup,
    BInputGroupPrepend,
    BDropdown,
    BDropdownItem,
    BFormCheckbox,
    BBadge,
    BAvatar,
    draggable,
    VuePerfectScrollbar,
    BCard, 
    BTable, 
    BRow, 
    BCol,
    BModal,
    BForm,
    BBadge,

    // App SFC
    TodoLeftSidebar,
    ChatbotAddBlocHandlerSidebar,
    ChatbotModalActionHandler,

    TemplateDisplayMessage,
    QuickMessageDisplay,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    return {
      blocId: null,
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
    openModal(modalName) {
      //Abre o modal para escolha do modelo
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
  },
  setup() {
    const CHATBOT_APP_STORE_MODULE_NAME = 'app-chatbot'

    // Register module
    if (!store.hasModule(CHATBOT_APP_STORE_MODULE_NAME)) store.registerModule(CHATBOT_APP_STORE_MODULE_NAME, todoStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(CHATBOT_APP_STORE_MODULE_NAME)) store.unregisterModule(CHATBOT_APP_STORE_MODULE_NAME)
    })

    //Toast Notification
    const toast = useToast()
    const { skin } = useAppConfig()

    const { route, router } = useRouter()
    const routeSortBy = computed(() => route.value.query.sort)
    const routeQuery = computed(() => route.value.query.q)
    const routeParams = computed(() => route.value.params)
    watch(routeParams, () => {
      // eslint-disable-next-line no-use-before-define
      fetchBlocs()
    })

    const chatbot = ref({})
    const blocs = ref([])

    const sortOptions = [
      'latest',
      'title-asc',
      'title-desc',
      'assignee',
      'due-date',
    ]
    const sortBy = ref(routeSortBy.value)
    watch(routeSortBy, val => {
      if (sortOptions.includes(val)) sortBy.value = val
      else sortBy.value = val
    })
    const resetSortAndNavigate = () => {
      const currentRouteQuery = JSON.parse(JSON.stringify(route.value.query))

      delete currentRouteQuery.sort

      router.replace({ name: route.name, query: currentRouteQuery }).catch(() => {})
    }

    const blankBloc = {
      title: '',
      body: '',
      template: [],
      quickMessage: [],
      typeMessageSelected: null,
      firstBloc: false,
      finalBloc: false,
      evaluationBloc: false,
      cha_send_option_error_message: 1,
    }
    const bloc = ref(JSON.parse(JSON.stringify(blankBloc)))
    const clearBlocData = () => {
      bloc.value = JSON.parse(JSON.stringify(blankBloc))
    }

    const addBloc = val => {
      store.dispatch('app-chatbot/addBloc', val)
        .then(() => {
          // eslint-disable-next-line no-use-before-define
          fetchBlocs()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Bloco Adicionado',
              text: 'O bloco foi adicionado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }
    const removeBloc = () => {
      store.dispatch('app-chatbot/removeBloc', { id: bloc.value.id })
        .then(() => {
          // eslint-disable-next-line no-use-before-define
          fetchBlocs()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Bloco Removido',
              text: 'O bloco foi removido com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }
    const updateBloc = blocData => {
      store.dispatch('app-chatbot/updateBloc', { bloc: blocData })
        .then(() => {
          // eslint-disable-next-line no-use-before-define
          fetchBlocs()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Bloco Atualizado',
              text: 'O bloco foi atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const blankAction = {
      mainBlocId: '',
      key: '',
      action: '',
      department: '',
      bloc: '',
    }
    const action = ref(JSON.parse(JSON.stringify(blankAction)))
    //Limpa os dados do popup
    const clearActionData = () => {
      action.value = JSON.parse(JSON.stringify(blankAction))
    }
    const addAction = (actionData) => {
    store.dispatch('app-chatbot/addAction', { action: actionData })
      .then(() => {
        fetchBlocs()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Ação Adicionada',
            text: 'Ação adicionada com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
    }
    const updateAction = actionData => {
      store.dispatch('app-chatbot/updateAction', { action: actionData })
        .then(() => {
          fetchBlocs()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Ação Atualizada',
              text: 'Ação atualizada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }
    const removeAction = (id) => {
      store.dispatch('app-chatbot/removeAction', { id: id })
        .then(() => {
          fetchBlocs()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Ação Removida',
              text: 'Ação removida com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    const isTaskHandlerSidebarActive = ref(false)

    const showFirstBlocCheckbox = ref(false)
    const setFirstBloc = actionData => {
      console.log(blocs.value.length)
      if(blocs.value.length > 0) {
        showFirstBlocCheckbox.value = true
      }
      else {
        showFirstBlocCheckbox.value = false
      }
    }
    

    const blocTags = [
      //{ title: 'Texto Simples', color: 'secondary' },
      { title: 'Mensagem', color: 'primary' },
      { title: 'Bloco Inicial', color: 'success' },
      { title: 'Bloco Final', color: 'danger' },
      { title: 'Bloco de Avaliação', color: 'warning' },
      //{ title: 'Rastreamento', color: 'warning', route: { name: 'apps-chatbot-tag', params: { tag: 'medium' } } },
      /*{ title: 'High', color: 'danger', route: { name: 'apps-chatbot-tag', params: { tag: 'high' } } },
      { title: 'Update', color: 'info', route: { name: 'apps-chatbot-tag', params: { tag: 'update' } } },*/
    ]

    const resolveTagVariant = tag => {
      if (tag === 'team') return 'primary'
      if (tag === 'low') return 'success'
      if (tag === 'medium') return 'warning'
      if (tag === 'high') return 'danger'
      if (tag === 'update') return 'info'
      return 'primary'
    }

    const resolveAvatarVariant = tags => {
      if (tags.includes('high')) return 'primary'
      if (tags.includes('medium')) return 'warning'
      if (tags.includes('low')) return 'success'
      if (tags.includes('update')) return 'danger'
      if (tags.includes('team')) return 'info'
      return 'primary'
    }

    // Search Query
    const searchQuery = ref(routeQuery.value)
    watch(routeQuery, val => {
      searchQuery.value = val
    })
    // eslint-disable-next-line no-use-before-define
    watch([searchQuery, sortBy], () =>
    {
      //Se o usuário diigitou mais de 3 caracteres na pesquisa
      if(searchQuery.length == 0 || searchQuery.value == undefined || searchQuery.length > 3) {
        fetchBlocs()
      }
    })

    const updateRouteQuery = val => {
      const currentRouteQuery = JSON.parse(JSON.stringify(route.value.query))

      if (val) currentRouteQuery.q = val
      else delete currentRouteQuery.q

      router.replace({ name: route.name, query: currentRouteQuery })
    }

    const baseUrlStorage = ref('')

    const fetchBlocs = () => {
      store.dispatch('app-chatbot/fetchBlocs', {
        q: searchQuery.value,
        filter: router.currentRoute.params.filter,
        chatbotId: router.currentRoute.params.id,
        //tag: searchTag.value,
        sortBy: sortBy.value,
      })
        .then(response => {
          blocs.value = response.data.blocs
          baseUrlStorage.value = response.data.baseUrlStorage
        })
    }

    fetchBlocs()

    //Abre o sidebar já preenchida para atualização do bloco
    const handleBlocClick = blocData => {
      bloc.value = blocData
      console.log('handleBlocClick')
      console.log(bloc)
      isTaskHandlerSidebarActive.value = true
      setFirstBloc()
    }
    //Abre o modal já preenchida para atualização da ação
    const handleActionClick = (actionData) => {
      console.log(actionData)
      action.value = actionData
      action.value.action = actionData.type_action
      action.value.bloc = actionData.destination_bloc
      //isTaskHandlerSidebarActive.value = true
    }

    // Single Task isCompleted update
    const updateTaskIsCompleted = taskData => {
      // eslint-disable-next-line no-param-reassign
      taskData.isCompleted = !taskData.isCompleted
      updateTask(taskData)
    }

    //Traz o chatbot associado a essa estrutura
    const getChatbot = () => {
      axios
      .get('/api/chatbot/get-chatbot/'+router.currentRoute.params.id)
      .then(response => {
        console.log(response.data)
        chatbot.value = response.data
        //this.statusTemplate = response.data
      });
    }
    getChatbot()

    const { mqShallShowLeftSidebar } = useResponsiveAppLeftSidebarVisibility()

    return {
      chatbot,
      bloc,
      blocs,
      removeBloc,
      addBloc,
      updateBloc,
      clearBlocData,
      blocTags,
      searchQuery,
      fetchBlocs,
      perfectScrollbarSettings,
      updateRouteQuery,
      resetSortAndNavigate,
      showFirstBlocCheckbox,
      setFirstBloc,

      baseUrlStorage,

      action,
      addAction,
      updateAction,
      clearActionData,
      removeAction,

      // UI
      resolveTagVariant,
      resolveAvatarVariant,
      isTaskHandlerSidebarActive,

      // Click Handler
      handleBlocClick,
      handleActionClick,

      // Filters
      formatDate,
      avatarText,

      // Single Task isCompleted update
      updateTaskIsCompleted,

      // Left Sidebar Responsive
      mqShallShowLeftSidebar,

      skin,
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
    //cursor: move;

    .todo-task-list .todo-item:hover & {
      visibility: visible;
    }
}
[dir] .chatbot-application .content-area-wrapper .content-right .todo-task-list-wrapper .todo-task-list li {
  cursor: default;
}
.dropdown-item-action  {
  margin: 0% !important; 
  padding: 0% !important
}
.margin-top-column {
  margin-top: 10px;
}
</style>

<style lang="scss">
@import "~@core/scss/base/pages/app-chatbot.scss";
</style>
