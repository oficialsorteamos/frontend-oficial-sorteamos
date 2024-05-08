import { ref, computed, watch } from '@vue/composition-api'

export default function useDepartmentModalHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  
  const userLocal = ref(JSON.parse(JSON.stringify(props.user.value)))
  
  const resetTransferLocal = () => {
    userLocal.value = JSON.parse(JSON.stringify(props.user.value))
  }
  watch(props.user, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const userData = JSON.parse(JSON.stringify(userLocal))
    //Manda os dados para serem processados
    if (props.user.value.id) emit('update-user-access-detail-account', userData.value)
    else emit('add-user', userData.value)

    emit('hide-modal', 'modal-edit-access-detail')
  }

  return {
    userLocal,

    resetTransferLocal,

    onSubmit,
  }
}
