<template>

  <div>
    <b-button
          variant="primary"
          class="btn-icon rounded-circle mb-1"
          @click="isTaskHandlerSidebarActive= true;"
          v-b-tooltip.hover.v-secondary
          v-b-modal.modal-add-tag
          :title="$t('tag.addNewTag')"
        >
          <feather-icon 
            icon="PlusIcon" 
            size="20"
          />
        </b-button>

    <!-- Table Container Card -->
    <b-card
      no-body
      class="mb-2"
    >
      <b-card-title class="mx-1 mt-1">
        {{ $t('tag.contactTags') }} 
      </b-card-title>
      <div class="m-2">
        <!-- Table Top -->
        <b-row>
          <!-- Per Page -->
          <b-col
            cols="12"
            md="6"
            class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
          >
            <label>{{ $t('tag.show') }}</label>
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>{{ $t('tag.entries') }}</label>
          </b-col>

          <!-- Search -->
          <b-col
            cols="12"
            md="6"
          >
            <!--
            <div class="d-flex align-items-center justify-content-end">
              <b-form-input
                v-model="searchQuery"
                class="d-inline-block mr-1"
                placeholder="Search..."
              />
              <b-button
                variant="primary"
                @click="isAddNewUserSidebarActive = true"
              >
                <span class="text-nowrap">Add User</span>
              </b-button>
            </div>
            -->
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
        ref="refTagsContactListTable"
        class="position-relative"
        :items="fetchContactTags"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        :empty-text="$t('tag.noTagFound')"
        :sort-desc.sync="isSortDirDesc"
      >

        
        <template #cell(tag_name)="data">
          <div class="text-nowrap">
            <span class="align-text-top text-capitalize"><strong> {{ data.item.tag_name }} </strong></span>
          </div>
        </template>

        
        <template #cell(tag_description)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ data.item.tag_description }}</span>
          </div>
        </template>

        <template #cell(tag_color)="data">
          <b-badge
            pill
            :style="'background-color:'+data.item.tag_color"
            class="text-capitalize"
          >
            {{ data.item.tag_name }}
          </b-badge>
        </template>

        <template #cell(tag_status)="data">
          <b-badge
            pill
            :variant="`light-${resolveDepartmentStatusVariant(data.item.tag_status)}`"
            class="text-capitalize"
          >
            {{ data.item.tag_status == 'A'? $t('tag.active') : $t('tag.inactive') }}
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
              @click="openModal('modal-edit-tag-'+data.item.id)"
            >
              <feather-icon icon="EditIcon" />
              <span class="align-middle ml-50">{{ $t('tag.edit') }}</span>
            </b-dropdown-item>
            <b-dropdown-item 
              @click="removeTag(data.item.id)"
            >
              <feather-icon icon="TrashIcon" />
              <span class="align-middle ml-50">{{ $t('tag.remove') }}</span>
            </b-dropdown-item>
            <!--
            <b-dropdown-item
              @click="removeDepartment(data.item.id)"
            >
              <feather-icon icon="TrashIcon" />
              <span class="align-middle ml-50">Delete</span>
            </b-dropdown-item>
            -->
          </b-dropdown>
          <!-- Form para cadastro de um novo canal -->
          <b-modal
            :id="'modal-edit-tag-'+data.item.id"
            :title="$t('tag.editTag')"
            hide-footer
            size="sm"
          >
            <tag-modal-handler
              :tag="data.item"
              :clear-tag-data="clearTagData"
              @add-department="addTag"
              @update-tag="updateTag"
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
            <span class="text-muted">{{ $t('tag.showing') }} {{ dataMeta.from }} {{ $t('tag.to') }} {{ dataMeta.to }} {{ $t('tag.of') }} {{ dataMeta.of }} {{ $t('tag.entries') }}</span>
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

    <!-- Table Container Card -->
    <b-card
      no-body
      class="mb-0 mt-2"
    >
      <b-card-title class="mx-1 mt-1">
        {{ $t('tag.calendarTags') }}
      </b-card-title>
      <div class="m-2">
        <!-- Table Top -->
        <b-row>
          <!-- Per Page -->
          <b-col
            cols="12"
            md="6"
            class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
          >
            <label>{{ $t('tag.show') }}</label>
            <v-select
              v-model="perPageCalendar"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptionsCalendar"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>{{ $t('tag.entries') }}</label>
          </b-col>

          <!-- Search -->
          <b-col
            cols="12"
            md="6"
          >
            <!--
            <div class="d-flex align-items-center justify-content-end">
              <b-form-input
                v-model="searchQuery"
                class="d-inline-block mr-1"
                placeholder="Search..."
              />
              <b-button
                variant="primary"
                @click="isAddNewUserSidebarActive = true"
              >
                <span class="text-nowrap">Add User</span>
              </b-button>
            </div>
            -->
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
        ref="refTagsCalendarListTable"
        class="position-relative"
        :items="fetchCalendarTags"
        responsive
        :fields="tableColumnsCalendar"
        primary-key="id"
        :sort-by.sync="sortByCalendar"
        show-empty
        :empty-text="$t('tag.noTagFound')"
        :sort-desc.sync="isSortDirDescCalendar"
      >

        
        <template #cell(tag_name)="data">
          <div class="text-nowrap">
            <span class="align-text-top text-capitalize"><strong> {{ data.item.tag_name }} </strong></span>
          </div>
        </template>

        
        <template #cell(tag_description)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ data.item.tag_description }}</span>
          </div>
        </template>

        <template #cell(tag_color)="data">
          <b-badge
            pill
            :style="'background-color:'+data.item.tag_color"
            class="text-capitalize"
          >
            {{ data.item.tag_name }}
          </b-badge>
        </template>

        <template #cell(tag_status)="data">
          <b-badge
            pill
            :variant="`light-${resolveDepartmentStatusVariant(data.item.tag_status)}`"
            class="text-capitalize"
          >
            {{ data.item.tag_status == 'A'? $t('tag.acive') : $t('tag.inactive') }}
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
              @click="openModal('modal-edit-tag-'+data.item.id)"
            >
              <feather-icon icon="EditIcon" />
              <span class="align-middle ml-50">{{ $t('tag.edit') }}</span>
            </b-dropdown-item>
            <b-dropdown-item 
              @click="removeTag(data.item.id)"
            >
              <feather-icon icon="TrashIcon" />
              <span class="align-middle ml-50">{{ $t('tag.remove') }}</span>
            </b-dropdown-item>
            <!--
            <b-dropdown-item
              @click="removeDepartment(data.item.id)"
            >
              <feather-icon icon="TrashIcon" />
              <span class="align-middle ml-50">Delete</span>
            </b-dropdown-item>
            -->
          </b-dropdown>
          <!-- Form para cadastro de um novo canal -->
          <b-modal
            :id="'modal-edit-tag-'+data.item.id"
            :title="$t('tag.editTag')"
            hide-footer
            size="sm"
          >
            <tag-modal-handler
              :tag="data.item"
              :clear-tag-data="clearTagData"
              @add-department="addTag"
              @update-tag="updateTag"
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
            <span class="text-muted">{{ $t('tag.showing') }} {{ dataMetaCalendar.from }} {{ $t('tag.to') }} {{ dataMetaCalendar.to }} {{ $t('tag.of') }} {{ dataMetaCalendar.of }} {{ $t('tag.entries') }}</span>
          </b-col>
          <!-- Pagination -->
          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-end"
          >

            <b-pagination
              v-model="currentPageCalendar"
              :total-rows="totalTagsCalendar"
              :per-page="perPageCalendar"
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
    <!-- Form para cadastro de um novo canal -->
    <b-modal
      id="modal-add-tag"
      :title="$t('tag.addTag')"
      hide-footer
      size="sm"
    >
      <tag-modal-handler
        :tag="tagData"
        :clear-tag-data="clearTagData"
        @add-tag="addTag"
        @hide-modal="hideModal"
      />
    </b-modal>
  </div>
</template>

<script>
import {
  BCard, BRow, BCol, BFormInput, BButton, BTable, BMedia, BAvatar, BLink,
  BBadge, BDropdown, BDropdownItem, BPagination, BCardBody, BCardHeader, BCardTitle, VBTooltip,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import store from '@/store'
import { ref, onUnmounted } from '@vue/composition-api'
import { avatarText } from '@core/utils/filter'
import useTagsContactList from './useTagsContactList'
import useTagsCalendarList from './useTagsCalendarList'
import tagStoreModule from './tagStoreModule'
import TagModalHandler from './tag-modal-handler/TagModalHandler.vue'
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
    BCardTitle,

    vSelect,
    TagModalHandler,
  },
  directives: {
    'b-tooltip': VBTooltip,
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
    const TAG_APP_STORE_MODULE_NAME = 'app-tag'

    // Register module
    if (!store.hasModule(TAG_APP_STORE_MODULE_NAME)) store.registerModule(TAG_APP_STORE_MODULE_NAME, tagStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(TAG_APP_STORE_MODULE_NAME)) store.unregisterModule(TAG_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()

    const blankTag = {
      tag_name: '',
      tag_description: '',
      tag_color: '#7e7cde',
      tag_status: '',
      type_tag: '',
    }
    const tagData = ref(JSON.parse(JSON.stringify(blankTag)))
    //Limpa os dados do popup
    const clearTagData = () => {
      tagData.value = JSON.parse(JSON.stringify(blankTag))
    }

    //Adiciona um departmento
    const addTag = tagData => {
      store.dispatch('app-tag/addTag', { tagData: tagData })
        .then(() => {  
          refetchData()
          refetchDataCalendar()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Tag adicionada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //Atualiza os dados do departamento
    const updateTag = tagData => {
      store.dispatch('app-tag/updateTag', { tagData: tagData })
        .then(() => {  
          refetchData()
          refetchDataCalendar()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Tag atualizada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //Remove um departamento
    const removeTag = tagId => {
      store.dispatch('app-tag/removeTag', { id: tagId })
        .then(() => {
          refetchData()
          refetchDataCalendar()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Tag removida com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const isAddNewUserSidebarActive = ref(false)

    const statusOptions = [
      { label: 'Pending', value: 'pending' },
      { label: 'Active', value: 'active' },
      { label: 'Inactive', value: 'inactive' },
    ]

    const {
      fetchContactTags,
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refTagsContactListTable,
      refetchData,

      // UI
      resolveDepartmentStatusVariant,

    } = useTagsContactList()

    const {
      fetchCalendarTags,
      tableColumnsCalendar,
      perPageCalendar,
      currentPageCalendar,
      totalTagsCalendar,
      dataMetaCalendar,
      perPageOptionsCalendar,
      searchQueryCalendar,
      sortByCalendar,
      isSortDirDescCalendar,
      refTagsCalendarListTable,
      refetchDataCalendar,

      // UI
      resolveTagCalendarStatusVariant,

    } = useTagsCalendarList()

    fetchContactTags()
    fetchCalendarTags()
    
    return {

      // Sidebar
      isAddNewUserSidebarActive,

      fetchContactTags,
      fetchCalendarTags,
      tableColumns,
      tableColumnsCalendar,
      perPage,
      perPageCalendar,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refTagsContactListTable,
      refTagsCalendarListTable,
      refetchData,
      clearTagData,
      tagData,
      addTag,
      updateTag,
      removeTag,

      currentPageCalendar,
      totalTagsCalendar,
      dataMetaCalendar,
      perPageOptionsCalendar,
      searchQueryCalendar,
      sortByCalendar,
      isSortDirDescCalendar,
      refetchDataCalendar,

      // Filter
      avatarText,

      // UI
      resolveDepartmentStatusVariant,
      resolveTagCalendarStatusVariant,

      statusOptions,

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
