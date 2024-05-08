import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'
import { title } from '@core/utils/filter'
import Swal from 'sweetalert2'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {
  // Use toast
  const toast = useToast()

  const refUserListTable = ref(null)

  const contacts = ref([])

  // Table Handlers
  const tableColumns = [
    { key: 'user', sortable: true },
    { key: 'email', sortable: true },
    { key: 'role', sortable: true },
    {
      key: 'currentPlan',
      label: 'Plan',
      formatter: title,
      sortable: true,
    },
    { key: 'status', sortable: true },
    { key: 'actions' },
  ]
  const perPage = ref(12)
  const totalUsers = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [12, 24, 48, 96]
  const searchQuery = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)
  const roleFilter = ref(null)
  const planFilter = ref(null)
  const statusFilter = ref(null)

  const dataMeta = computed(() => {
    const localItemsCount = 2
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalUsers.value,
    }
  })

  const refetchData = () => {
    //refUserListTable.value.refresh()
    fetchContacts()
  }

  watch([currentPage, perPage, searchQuery, roleFilter, planFilter, statusFilter], () => {
    if(searchQuery.value != '') {
      if(searchQuery.value.length > 3) {
        refetchData()
      }
    }
    else {
      refetchData()
    }
  })

  const fetchContacts = (ctx, callback) => {
    store
      /*.dispatch('app-user/fetchUsers', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        sortBy: sortBy.value,
        sortDesc: isSortDirDesc.value,
        role: roleFilter.value,
        plan: planFilter.value,
        status: statusFilter.value,
      })*/
      .dispatch('app-contact/fetchContacts', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        /*sortBy: sortBy.value,
        sortDesc: isSortDirDesc.value,
        role: roleFilter.value,
        plan: planFilter.value,
        status: statusFilter.value,*/
      })
      .then(response => {
        //const { users, total } = response.data
        contacts.value = response.data.contacts
        console.log(response.data)
        //callback(users)
        totalUsers.value = response.data.total
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
  }

  //Função que bloqueia um usuário
  const blockContact = contactId => {
    Swal.fire({
      title: 'Bloqueio de Contato',
      text: "Você tem certeza que deseja bloquear esse contato?",
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
        //Bloqueia o contato
        store.dispatch('app-contact/blockContact', { contactId: contactId })
        .then(response => {
          console.log('contact blocked')
          console.log(response)
          contacts.value.find(c => c.id == response.data.contactId).blocked = true
          toast({
            component: ToastificationContent,
            props: {
              title: 'Contato bloqueado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
      }
    })
  }

  //Desbloquear um usuário
  const unlockContact = contactId => {
    Swal.fire({
      title: 'Desbloqueio de Contato',
      text: "Você tem certeza que deseja desbloquear esse contato?",
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
        //Bloqueia o contato
        store.dispatch('app-contact/unlockContact', { contactId: contactId })
        .then(response => {
          console.log('contact unlock')
          console.log(response)

          contacts.value.find(c => c.id == response.data.contactId).blocked = null

          toast({
            component: ToastificationContent,
            props: {
              title: 'Contato desbloqueado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
      }
    })
  }

  // *===============================================---*
  // *--------- UI ---------------------------------------*
  // *===============================================---*

  const resolveUserRoleVariant = role => {
    if (role === 'subscriber') return 'primary'
    if (role === 'author') return 'warning'
    if (role === 'maintainer') return 'success'
    if (role === 'editor') return 'info'
    if (role === 'admin') return 'danger'
    return 'primary'
  }

  const resolveUserRoleIcon = role => {
    if (role === 'subscriber') return 'UserIcon'
    if (role === 'author') return 'SettingsIcon'
    if (role === 'maintainer') return 'DatabaseIcon'
    if (role === 'editor') return 'Edit2Icon'
    if (role === 'admin') return 'ServerIcon'
    return 'UserIcon'
  }

  const resolveUserStatusVariant = status => {
    if (status === 'pending') return 'warning'
    if (status === 'active') return 'success'
    if (status === 'inactive') return 'secondary'
    return 'primary'
  }

  return {
    fetchContacts,
    blockContact,
    unlockContact,
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
    contacts,

    resolveUserRoleVariant,
    resolveUserRoleIcon,
    resolveUserStatusVariant,
    refetchData,

    // Extra Filters
    roleFilter,
    planFilter,
    statusFilter,
  }
}
