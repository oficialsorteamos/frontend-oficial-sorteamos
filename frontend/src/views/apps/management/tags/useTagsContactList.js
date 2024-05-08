import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'
import { title } from '@core/utils/filter'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {
  // Use toast
  const toast = useToast()

  const refTagsContactListTable = ref(null)


  // Table Handlers
  const tableColumns = [
    { key: 'tag_name', label: 'Nome', sortable: true },
    { key: 'tag_description', label: 'Descrição', sortable: true },
    { key: 'tag_color', label: 'Pré-visualização', sortable: true },
    { key: 'tag_status', label: 'Status', sortable: true },
    { key: 'actions', label: "Ações" },
  ]
  const perPage = ref(10)
  const totalUsers = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [10, 25, 50, 100]
  const searchQuery = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)

  //Exibe as informações de paginação
  const dataMeta = computed(() => {
    const localItemsCount = refTagsContactListTable.value ? refTagsContactListTable.value.localItems.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalUsers.value,
    }
  })

  const refetchData = () => {
    refTagsContactListTable.value.refresh()
  }

  watch([currentPage, perPage, searchQuery], () => {
    refetchData()
  })

  const fetchContactTags = (ctx, callback) => {
    store
      .dispatch('app-tag/fetchTags', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        typeTag: 1, //Contato
      })
      .then(response => {
        const { tags, total } = response.data
        callback(tags)
        totalUsers.value = total
      })
      .catch((e) => {
        
      })
  }

  // *===============================================---*
  // *--------- UI ---------------------------------------*
  // *===============================================---*

  const resolveDepartmentStatusVariant = status => {
    if (status === 'pending') return 'warning'
    if (status === 'A') return 'success'
    if (status === 'I') return 'secondary'
    return 'primary'
  }

  return {
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

    resolveDepartmentStatusVariant,
    refetchData,

  }
}
