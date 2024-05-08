import { ref, computed, watch } from '@vue/composition-api'

export default function useDepartmentModalHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const paymentLocal = ref(JSON.parse(JSON.stringify(props.payment.value)))
  
  const resetTransferLocal = () => {
    paymentLocal.value = JSON.parse(JSON.stringify(props.payment.value))
  }
  watch(props.payment, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const userData = JSON.parse(JSON.stringify(paymentLocal))
    //Manda os dados para serem processados
     emit('add-credit', userData.value)

    //Fecha o modal DE adição de usuário
    emit('hide-modal', 'modal-add-credit')
  }

  return {
    paymentLocal,

    resetTransferLocal,

    onSubmit,
  }
}
