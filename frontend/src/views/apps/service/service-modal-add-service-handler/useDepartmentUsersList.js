import { ref, watch } from '@vue/composition-api'
import store from '@/store'


export default function useDepartmentUsersList() {

  const departmentUsers = ref([])
  const departmentSelected = ref()

  watch(departmentSelected, () => {
    if (departmentSelected.value != null) getDepartmentUsers()
  })

  const getDepartmentUsers = (ctx, callback) => {
    store
      .dispatch('app-service/getUserDepartmentTransfer', {
        id: departmentSelected.value.id
      })
      .then(response => {
        departmentUsers.value = response.data.users
      })
  }

  return {
    departmentSelected,
    departmentUsers,
  }
}
