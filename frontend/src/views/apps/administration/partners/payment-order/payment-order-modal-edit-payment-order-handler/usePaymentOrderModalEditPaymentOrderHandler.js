import { ref, computed, watch } from '@vue/composition-api'

export default function useDepartmentModalHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const paymentOrderLocal = ref(JSON.parse(JSON.stringify(props.paymentOrder.value)))
  
  const resetTransferLocal = () => {
    paymentOrderLocal.value = JSON.parse(JSON.stringify(props.paymentOrder.value))
  }
  watch(props.paymentOrder, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const paymentOrderData = JSON.parse(JSON.stringify(paymentOrderLocal))
    //Manda os dados para serem processados
    emit('update-payment-order', paymentOrderData.value)

    //Fecha o modal associado a um canal específico
    emit('hide-modal', 'modal-edit-payment-order-'+props.paymentOrder.value.id)
  }

  return {
    paymentOrderLocal,

    resetTransferLocal,

    onSubmit,
  }
}
