import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'
import { title } from '@core/utils/filter'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {
  // Use toast
  const toast = useToast()

  const refTagsCalendarListTable = ref(null)


  // Table Handlers
  const tableColumnsCalendar = [
    { key: 'tag_name', label: 'Nome', sortable: true },
    { key: 'tag_description', label: 'Descrição', sortable: true },
    { key: 'tag_color', label: 'Pré-visualização', sortable: true },
    { key: 'tag_status', label: 'Status', sortable: true },
    { key: 'actions', label: "Ações" },
  ]
  const perPageCalendar = ref(10)
  const totalTagsCalendar = ref(0)
  const currentPageCalendar = ref(1)
  const perPageOptionsCalendar = [10, 25, 50, 100]
  const searchQueryCalendar = ref('')
  const sortByCalendar = ref('id')
  const isSortDirDescCalendar = ref(true)

  //Exibe as informações de paginação
  const dataMetaCalendar = computed(() => {
    const localItemsCount = refTagsCalendarListTable.value ? refTagsCalendarListTable.value.localItems.length : 0
    return {
      from: perPageCalendar.value * (currentPageCalendar.value - 1) + (localItemsCount ? 1 : 0),
      to: perPageCalendar.value * (currentPageCalendar.value - 1) + localItemsCount,
      of: totalTagsCalendar.value,
    }
  })

  const refetchDataCalendar = () => {
    refTagsCalendarListTable.value.refresh()
  }

  watch([currentPageCalendar, perPageCalendar, searchQueryCalendar], () => {
    refetchData()
  })

  const fetchCalendarTags = (ctx, callback) => {
    store
      .dispatch('app-tag/fetchTags', {
        q: searchQueryCalendar.value,
        perPage: perPageCalendar.value,
        page: currentPageCalendar.value,
        typeTag: 2, //Agenda
      })
      .then(response => {
        const { tags, total } = response.data
        callback(tags)
        totalTagsCalendar.value = total
      })
      .catch((e) => {
        
      })
  }

  // *===============================================---*
  // *--------- UI ---------------------------------------*
  // *===============================================---*

  const resolveTagCalendarStatusVariant = status => {
    if (status === 'pending') return 'warning'
    if (status === 'A') return 'success'
    if (status === 'I') return 'secondary'
    return 'primary'
  }

  return {
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

    resolveTagCalendarStatusVariant,
    refetchDataCalendar,

  }
}
