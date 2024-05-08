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
     emit('add-user', userData.value)

    //Fecha o modal DE adição de usuário
    emit('hide-modal', 'modal-add-user')
  }

  return {
    userLocal,

    resetTransferLocal,

    onSubmit,
  }
}
