import { ref, computed, watch } from '@vue/composition-api'

export default function useDepartmentModalHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const roleLocal = ref(JSON.parse(JSON.stringify(props.role.value)))
  
  const resetTransferLocal = () => {
    roleLocal.value = JSON.parse(JSON.stringify(props.role.value))
  }
  watch(props.role, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const departmentData = JSON.parse(JSON.stringify(roleLocal))
    //Manda os dados para serem processados
    if (props.department.value.id) emit('update-department', departmentData.value)
    else emit('add-department', departmentData.value)

    //Fecha o modal associado a um canal específico
    emit('hide-modal', 'modal-edit-department-'+props.department.value.id)
    emit('hide-modal', 'modal-add-department')
  }

  return {
    roleLocal,

    resetTransferLocal,

    onSubmit,
  }
}
