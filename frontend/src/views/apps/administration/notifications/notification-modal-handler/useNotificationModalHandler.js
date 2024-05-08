import { ref, computed, watch } from '@vue/composition-api'

export default function useDepartmentModalHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const notificationLocal = ref(JSON.parse(JSON.stringify(props.notification.value)))
  
  const resetTransferLocal = () => {
    notificationLocal.value = JSON.parse(JSON.stringify(props.notification.value))
  }
  watch(props.notification, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const notificationData = JSON.parse(JSON.stringify(notificationLocal))
    //Manda os dados para serem processados
     emit('send-notification', notificationData.value)

    //Fecha o modal DE adição de usuário
    emit('hide-modal', 'modal-send-notification')
  }

  return {
    notificationLocal,

    resetTransferLocal,

    onSubmit,
  }
}
